<?php
// API YT = AIzaSyCZeyepaUrGejdBzhAfr23NCQtgLkuhTFo

defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {

	public function index()
	{
	   $data['videoId'] = $this->input->get('id');
	   $data['judulVideo'] = $this->input->get('title');

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
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Invalid video ID'
			]));
			return;
		}
		if ($format !== 'mp4') $format = 'mp3';

		$authKey = $this->_getAuthKey();
		if (!$authKey) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Failed to get auth key'
			]));
			return;
		}

		$initUrl = 'https://a.ymcdn.org/api/v1/init?a=' . urlencode($authKey) . '&_=' . time();
		$initRaw = $this->_fetchUrl($initUrl);
		if (!$initRaw) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Init network error'
			]));
			return;
		}
		$initData = json_decode($initRaw, true);
		if (!$initData) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Init bad response: ' . substr($initRaw, 0, 200)
			]));
			return;
		}
		if (!empty($initData['error'])) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Init error: ' . ($initData['message'] ?? json_encode($initData))
			]));
			return;
		}
		if (empty($initData['convertURL'])) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'error' => 1, 'message' => 'Init no convertURL: ' . substr($initRaw, 0, 300)
			]));
			return;
		}

		$result = $this->_doConvert($initData['convertURL'], $videoId, $format);
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	private function _doConvert($convertUrl, $videoId, $format)
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

		if (!empty($data['progressURL']) && empty($data['downloadURL'])) {
			return ['error' => 1, 'message' => 'Conversion in progress, try again'];
		}

		if (empty($data['downloadURL'])) {
			if (!empty($data['progressURL'])) {
				$polls = 0;
				while ($polls < 30) {
					$progressData = $this->_apiRequest($data['progressURL']);
					if ($progressData && isset($progressData['progress']) && $progressData['progress'] >= 3) {
						break;
					}
					$polls++;
					sleep(2);
				}
			}

			$url = $convertUrl . '&v=' . urlencode($videoId) . '&f=' . $format;
			$data = $this->_apiRequest($url);
			if (!$data || empty($data['downloadURL'])) {
				return ['error' => 1, 'message' => 'Conversion timeout'];
			}
		}

		return [
			'error' => 0,
			'downloadURL' => $data['downloadURL'],
			'title' => $data['title'] ?? ''
		];
	}

	private function _getAuthKey()
	{
		$html = $this->_fetchUrl('https://api.ytmp3.biz/button/');
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
		return substr($key, 0, 32);
	}

	private function _apiRequest($url)
	{
		$sep = (strpos($url, '?') !== false) ? '&' : '?';
		$url .= $sep . '_=' . time();
		$res = $this->_fetchUrl($url);
		return $res ? json_decode($res, true) : null;
	}

	private function _fetchUrl($url)
	{
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_TIMEOUT => 30,
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
				'timeout' => 30,
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
}
