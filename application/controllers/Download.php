<?php
// API YT = AIzaSyCZeyepaUrGejdBzhAfr23NCQtgLkuhTFo

defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {
	const DOWNLOAD_CACHE_TTL = 0;
	const PROXY_TOKEN_TTL = 300;
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

		$cookieFile = $this->_providerCookiePath(md5($videoId . '|' . $format . '|' . microtime(true) . '|' . mt_rand()));
		$authKey = $this->_getAuthKey();
		if (!$authKey) {
			$this->_json([
				'error' => 1, 'message' => 'Failed to get auth key'
			]);
			return;
		}

		$initUrl = 'https://a.ymcdn.org/api/v1/init?a=' . urlencode($authKey) . '&_=' . time();
		$initRaw = $this->_fetchUrl($initUrl, 12, $cookieFile);
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

		$result = $this->_doConvert($initData['convertURL'], $videoId, $format, 4, $cookieFile);
		if (empty($result['error']) && empty($result['pending']) && !empty($result['downloadURL'])) {
			$result['downloadURL'] = $this->_createProxyUrl($result['downloadURL'], $result['title'] ?? $videoId, $format, $cookieFile);
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

		$cookieFile = $this->_providerCookiePath(md5($videoId . '|' . $format . '|' . microtime(true) . '|' . mt_rand()));
		$authKey = $this->_getAuthKey();
		if (!$authKey) show_error('Auth failed');

		$initRaw = $this->_fetchUrl('https://a.ymcdn.org/api/v1/init?a=' . urlencode($authKey) . '&_=' . time(), 12, $cookieFile);
		if (!$initRaw) show_error('Init failed');
		$initData = json_decode($initRaw, true);
		if (!$initData || empty($initData['convertURL'])) show_error('Init failed');

		$result = $this->_doConvert($initData['convertURL'], $videoId, $format, 8, $cookieFile);
		if (!empty($result['pending'])) show_error('Download belum siap. Silakan kembali dan klik Download lagi.');
		if (!empty($result['error'])) show_error($result['message']);

		redirect($this->_createProxyUrl($result['downloadURL'], $result['title'] ?? $videoId, $format, $cookieFile));
	}

	public function proxy()
	{
		$token = (string) $this->input->get('token');
		if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) show_404();

		$payload = $this->_getProxyPayload($token);
		if (!$payload || empty($payload['url'])) show_404();

		$this->_streamDownload($payload['url'], $payload['title'] ?? 'download', $payload['format'] ?? 'mp3', $payload['cookieFile'] ?? '');
	}

	private function _doConvert($convertUrl, $videoId, $format, $maxPolls = 0, $cookieFile = null)
	{
		$url = $convertUrl . '&v=' . urlencode($videoId) . '&f=' . $format;
		$data = $this->_apiRequest($url, $cookieFile);
		if (!$data || !empty($data['error'])) {
			return ['error' => 1, 'message' => 'Convert failed: ' . ($data['error'] ?? 'unknown')];
		}

		$redirects = 0;
		while (!empty($data['redirect']) && $redirects < 5) {
			$url = $data['redirectURL'] . '&v=' . urlencode($videoId) . '&f=' . $format;
			$data = $this->_apiRequest($url, $cookieFile);
			if (!$data || !empty($data['error'])) {
				return ['error' => 1, 'message' => 'Redirect failed'];
			}
			$redirects++;
		}

		if (empty($data['downloadURL']) && !empty($data['progressURL'])) {
			$polls = 0;
			while ($polls < $maxPolls) {
				sleep(1);
				$progressData = $this->_apiRequest($data['progressURL'], $cookieFile);
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
				$data = $this->_apiRequest($url, $cookieFile);
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

	private function _apiRequest($url, $cookieFile = null)
	{
		$sep = (strpos($url, '?') !== false) ? '&' : '?';
		$url .= $sep . '_=' . time();
		$res = $this->_fetchUrl($url, 12, $cookieFile);
		return $res ? json_decode($res, true) : null;
	}

	private function _fetchUrl($url, $timeout = 12, $cookieFile = null)
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
			if ($cookieFile) {
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
			}
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
		if (self::DOWNLOAD_CACHE_TTL <= 0) {
			return null;
		}

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

	private function _createProxyUrl($downloadUrl, $title, $format, $cookieFile = '')
	{
		$token = md5($downloadUrl . '|' . microtime(true) . '|' . mt_rand());
		$payload = [
			'url' => $downloadUrl,
			'title' => $title,
			'format' => $format,
			'cookieFile' => $cookieFile,
			'createdAt' => time()
		];

		@file_put_contents($this->_proxyTokenPath($token), json_encode($payload), LOCK_EX);

		return 'download/proxy?token=' . $token;
	}

	private function _getProxyPayload($token)
	{
		$cacheFile = $this->_proxyTokenPath($token);
		if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) >= self::PROXY_TOKEN_TTL) {
			return null;
		}

		$payload = json_decode((string) @file_get_contents($cacheFile), true);
		if (!$payload || empty($payload['url']) || strpos($payload['url'], 'https://ydl.ymcdn.org/') !== 0) {
			return null;
		}

		return $payload;
	}

	private function _streamDownload($downloadUrl, $title, $format, $cookieFile = '')
	{
		$extension = $format === 'mp4' ? 'mp4' : 'mp3';
		$filename = $this->_safeFilename($title, $extension);

		if (function_exists('curl_init')) {
			$tempFile = $this->_cachePath('download_tmp_' . md5($downloadUrl . '|' . microtime(true)) . '.bin');
			$fp = @fopen($tempFile, 'wb');
			if (!$fp) {
				show_error('Download temporary file failed', 500);
				return;
			}

			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => $downloadUrl,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_CONNECTTIMEOUT => 8,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
				CURLOPT_REFERER => 'https://api.ytmp3.biz/',
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HEADER => false,
				CURLOPT_FILE => $fp,
			]);
			$normalizedCookieFile = str_replace('\\', '/', (string) $cookieFile);
			$cookiePrefix = str_replace('\\', '/', APPPATH . 'cache/download_cookie_');
			if ($normalizedCookieFile && strpos($normalizedCookieFile, $cookiePrefix) === 0 && file_exists($cookieFile)) {
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
				$cookieHeader = $this->_providerCookieHeader($cookieFile);
				if ($cookieHeader !== '') {
					curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);
				}
			}

			$ok = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);
			fclose($fp);

			if ($ok === false || $httpCode >= 400 || !$this->_isValidDownloadFile($tempFile)) {
				siteLogMessage('error', 'Download proxy failed: HTTP ' . $httpCode . ' ' . $error . ' URL: ' . $downloadUrl);
				@unlink($tempFile);
				show_error('Link download dari provider sedang error. Silakan kembali dan klik Download lagi.', 502);
				return;
			}

			header('Content-Type: ' . ($extension === 'mp4' ? 'video/mp4' : 'audio/mpeg'));
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Length: ' . filesize($tempFile));
			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');

			$read = @fopen($tempFile, 'rb');
			if ($read) {
				while (!feof($read)) {
					echo fread($read, 8192);
					if (ob_get_level() > 0) {
						@ob_flush();
					}
					flush();
				}
				fclose($read);
			}
			@unlink($tempFile);
			return;
		}

		redirect($downloadUrl);
	}

	private function _isValidDownloadFile($path)
	{
		if (!file_exists($path) || filesize($path) < 1024) {
			return false;
		}

		$fp = @fopen($path, 'rb');
		if (!$fp) {
			return false;
		}

		$head = fread($fp, 128);
		fclose($fp);

		if (stripos($head, 'An error occurred') !== false || stripos($head, '<html') !== false) {
			return false;
		}

		return true;
	}

	private function _safeFilename($title, $extension)
	{
		$title = html_entity_decode((string) $title, ENT_QUOTES, 'UTF-8');
		$title = preg_replace('/[^A-Za-z0-9 ._-]+/', '', $title);
		$title = preg_replace('/\s+/', ' ', $title);
		$title = trim($title);
		if ($title === '') {
			$title = 'download';
		}

		return substr($title, 0, 120) . '.' . $extension;
	}

	private function _downloadCachePath($videoId, $format)
	{
		return $this->_cachePath('download_' . md5($videoId . '|' . $format) . '.json');
	}

	private function _proxyTokenPath($token)
	{
		return $this->_cachePath('download_proxy_' . $token . '.json');
	}

	private function _providerCookiePath($token)
	{
		return $this->_cachePath('download_cookie_' . $token . '.txt');
	}

	private function _providerCookieHeader($cookieFile)
	{
		$lines = @file($cookieFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!$lines) {
			return '';
		}

		$cookies = [];
		foreach ($lines as $line) {
			if ($line === '' || $line[0] === '#') {
				if (strpos($line, '#HttpOnly_') !== 0) {
					continue;
				}
				$line = substr($line, 10);
			}

			$parts = preg_split('/\s+/', $line);
			if (count($parts) < 7) {
				continue;
			}

			$name = $parts[5];
			$value = $parts[6];
			if ($name !== '' && $value !== '') {
				$cookies[] = $name . '=' . $value;
			}
		}

		return implode('; ', $cookies);
	}

	private function _cachePath($name)
	{
		return APPPATH . 'cache/' . $name;
	}
}
