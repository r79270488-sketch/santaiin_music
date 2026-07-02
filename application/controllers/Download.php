<?php
// API YT = AIzaSyCZeyepaUrGejdBzhAfr23NCQtgLkuhTFo

defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {
	const DOWNLOAD_CACHE_TTL = 900;
	const AUTH_CACHE_TTL = 1800;

	public function index()
	{
	   $data['videoId'] = $this->input->get('id');
	   $data['judulVideo'] = $this->input->get('title');
	   $data['downloadType'] = strtolower(trim((string) $this->input->get('type')));

		$data['title'] = 'Download Lagu | '.$this->input->get('title');
		$data['keywords'] = $this->input->get('title');
		$expires = gmdate('D, d M Y H:i:s', time() + 300).' GMT';
		header('Cache-Control: private, max-age=300, stale-while-revalidate=60');
		header('Pragma: cache');
		header('Expires: '.$expires);
		$this->output
			->set_header('Cache-Control: private, max-age=300, stale-while-revalidate=60')
			->set_header('Pragma: cache')
			->set_header('Expires: '.$expires);
		$this->load->view('themes/v1/base/header',$data);
		$this->load->view('themes/v1/download',$data);
		$this->load->view('themes/v1/base/footer',$data);
	}

	public function fetch()
	{
		$videoId = $this->input->get('id');
		$format = $this->input->get('format');
		if (empty($videoId) || strlen($videoId) !== 11) {
			$this->_json([
				'error' => 1, 'message' => 'Invalid video ID'
			]);
			return;
		}
		if ($format !== 'mp4') $format = 'mp3';

		$cached = $this->_getCachedDownload($videoId, $format);
		if ($cached) {
			$this->_json($cached);
			return;
		}

		$authKey = $this->_getAuthKey();
		if (!$authKey) {
			$this->_json([
				'error' => 1, 'message' => 'Failed to get auth key'
			]);
			return;
		}

		$initUrl = 'https://a.ymcdn.org/api/v1/init?a=' . urlencode($authKey) . '&_=' . time();
		$initRaw = $this->_fetchUrl($initUrl);
		if (!$initRaw) {
			$this->_json([
				'error' => 1, 'message' => 'Init network error'
			]);
			return;
		}
		$initData = json_decode($initRaw, true);
		if (!$initData || !empty($initData['error']) || empty($initData['convertURL'])) {
			$this->_json([
				'error' => 1, 'message' => 'Init failed'
			]);
			return;
		}

		$result = $this->_doConvert($initData['convertURL'], $videoId, $format, 4);
		if (empty($result['error']) && empty($result['pending']) && !empty($result['downloadURL'])) {
			$this->_setCachedDownload($videoId, $format, $result);
		}
		$this->_json($result);
	}

	public function direct()
	{
		$videoId = $this->input->get('id');
		$format = $this->input->get('format');
		if (empty($videoId) || strlen($videoId) !== 11) show_404();
		if ($format !== 'mp4') $format = 'mp3';

		$cached = $this->_getCachedDownload($videoId, $format);
		if ($cached && !empty($cached['downloadURL'])) {
			redirect($cached['downloadURL']);
		}

		$authKey = $this->_getAuthKey();
		if (!$authKey) show_error('Auth failed');

		$initRaw = $this->_fetchUrl('https://a.ymcdn.org/api/v1/init?a=' . urlencode($authKey) . '&_=' . time());
		if (!$initRaw) show_error('Init failed');
		$initData = json_decode($initRaw, true);
		if (!$initData || empty($initData['convertURL'])) show_error('Init failed');

		$result = $this->_doConvert($initData['convertURL'], $videoId, $format, 8);
		if (!empty($result['pending'])) show_error('Download belum siap. Silakan kembali dan klik Download lagi.');
		if (!empty($result['error'])) show_error($result['message']);
		$this->_setCachedDownload($videoId, $format, $result);

		redirect($result['downloadURL']);
	}

	public function proxy()
	{
		$downloadUrl = $this->input->get('url');
		if (empty($downloadUrl)) show_404();
		redirect($downloadUrl);
	}

	private function _doConvert($convertUrl, $videoId, $format, $maxPolls = 0)
	{
		$url = $convertUrl . '&v=' . urlencode($videoId) . '&f=' . $format;
		$data = $this->_apiRequest($url);
		if (!$data || !empty($data['error'])) {
			return ['error' => 1, 'message' => 'Convert failed: ' . ($data['error'] ?? 'unknown')];
		}

		$redirects = 0;
		while (!empty($data['redirect']) && $redirects < 5) {
			$url = $data['redirectURL'] . '&v=' . urlencode($videoId) . '&f=' . $format;
			$data = $this->_apiRequest($url);
			if (!$data || !empty($data['error'])) {
				return ['error' => 1, 'message' => 'Redirect failed'];
			}
			$redirects++;
		}

		if (empty($data['downloadURL']) && !empty($data['progressURL'])) {
			$polls = 0;
			while ($polls < $maxPolls) {
				sleep(1);
				$progressData = $this->_apiRequest($data['progressURL']);
				if ($progressData && !empty($progressData['downloadURL'])) {
					$data = $progressData;
					break;
				}
				if ($progressData && isset($progressData['progress']) && $progressData['progress'] >= 3) {
					break;
				}
				$polls++;
			}

			if (empty($data['downloadURL'])) {
				$url = $convertUrl . '&v=' . urlencode($videoId) . '&f=' . $format;
				$data = $this->_apiRequest($url);
			}
		}

		if (empty($data['downloadURL'])) {
			return [
				'error' => 0,
				'pending' => 1,
				'message' => 'Download sedang disiapkan'
			];
		}

		return [
			'error' => 0,
			'downloadURL' => $data['downloadURL'],
			'title' => $data['title'] ?? ''
		];
	}

	private function _getAuthKey()
	{
		$cacheFile = $this->_cachePath('download_auth_key.txt');
		if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < self::AUTH_CACHE_TTL) {
			$cachedKey = trim((string) @file_get_contents($cacheFile));
			if (strlen($cachedKey) === 32) {
				return $cachedKey;
			}
		}

		$html = $this->_fetchUrl('https://api.ytmp3.biz/button/', 8);
		if (!$html) {
			siteLogMessage('error', 'Download: button page fetch failed');
			return '';
		}

		preg_match('/var json\s*=\s*JSON\.parse\(\'(.+?)\'\)/', $html, $m);
		if (empty($m[1])) {
			siteLogMessage('error', 'Download: regex failed, html=' . substr($html, 0, 500));
			return '';
		}

		$j = json_decode($m[1], true);
		if (!$j || count($j) < 3) {
			siteLogMessage('error', 'Download: json decode failed');
			return '';
		}

		list($codes, $reverse, $map) = $j;
		$key = '';
		$len = count($codes);
		for ($n = 0; $n < $len; $n++) {
			$key .= chr($codes[$n] - $map[$len - 1 - $n]);
		}
		if ($reverse) $key = strrev($key);
		$key = substr($key, 0, 32);
		if (strlen($key) === 32) {
			@file_put_contents($cacheFile, $key, LOCK_EX);
		}
		return $key;
	}

	private function _apiRequest($url)
	{
		$sep = (strpos($url, '?') !== false) ? '&' : '?';
		$url .= $sep . '_=' . time();
		$res = $this->_fetchUrl($url);
		return $res ? json_decode($res, true) : null;
	}

	private function _fetchUrl($url, $timeout = 12)
	{
		$timeout = max(3, (int) $timeout);
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
				CURLOPT_REFERER => 'https://api.ytmp3.biz/',
				CURLOPT_SSL_VERIFYPEER => false,
			]);
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);
			if ($result === false || $result === '') {
				if ($error) siteLogMessage('error', 'cURL error: ' . $error . ' URL: ' . $url);
				return false;
			}
			return $result;
		}

		$ctx = stream_context_create([
			'http' => [
				'ignore_errors' => true,
				'timeout' => $timeout,
				'follow_location' => true,
				'max_redirects' => 5,
				'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\nReferer: https://api.ytmp3.biz/\r\nAccept: */*\r\n"
			],
			'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
		]);
		$result = @file_get_contents($url, false, $ctx);
		if ($result === false) return false;
		return $result;
	}

	private function _json($payload)
	{
		if (ob_get_level() > 0 && ob_get_length()) {
			@ob_clean();
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($payload));
	}

	private function _getCachedDownload($videoId, $format)
	{
		$cacheFile = $this->_downloadCachePath($videoId, $format);
		if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) >= self::DOWNLOAD_CACHE_TTL) {
			return null;
		}

		$cache = json_decode((string) @file_get_contents($cacheFile), true);
		if (!$cache || empty($cache['downloadURL'])) {
			return null;
		}

		return [
			'error' => 0,
			'cached' => 1,
			'downloadURL' => $cache['downloadURL'],
			'title' => isset($cache['title']) ? $cache['title'] : ''
		];
	}

	private function _setCachedDownload($videoId, $format, $result)
	{
		if (empty($result['downloadURL'])) {
			return;
		}

		$data = [
			'downloadURL' => $result['downloadURL'],
			'title' => isset($result['title']) ? $result['title'] : '',
			'createdAt' => time()
		];

		@file_put_contents($this->_downloadCachePath($videoId, $format), json_encode($data), LOCK_EX);
	}

	private function _downloadCachePath($videoId, $format)
	{
		return $this->_cachePath('download_' . md5($videoId . '|' . $format) . '.json');
	}

	private function _cachePath($name)
	{
		return APPPATH . 'cache/' . $name;
	}
}
