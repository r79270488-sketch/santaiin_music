<?php
// API YT = AIzaSyCZeyepaUrGejdBzhAfr23NCQtgLkuhTFo

// URL
defined('BASEPATH') OR exit('No direct script access allowed');

class Sitemap extends CI_Controller {

	public function index()
	{
		$this->output->set_content_type('text/xml');

		$urls = [
			[
				'loc' => base_url(),
				'lastmod' => date('Y-m-d'),
			],
		];

		try {
			$this->load->model('Song_model', 'song_model');
			if ($this->song_model->isEnabled()) {
				$urls = array_merge($urls, $this->song_model->sitemapRows(2000));
			}
		} catch (Throwable $e) {
			log_message('error', 'Sitemap DB unavailable: ' . $e->getMessage());
		}

		foreach ($this->keywordUrls(120) as $url) {
			$urls[] = $url;
		}

		$data['urls'] = $this->uniqueUrls($urls);
        $this->load->view('themes/v1/sitemap',$data);
    }

	private function keywordUrls($limit)
	{
		$files = [
			FCPATH . 'keywoard/kw1.txt',
			FCPATH . 'keywoard/sitemap.txt',
		];
		$items = [];

		foreach ($files as $file) {
			if (!is_file($file)) {
				continue;
			}

			foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $keyword) {
				$keyword = trim($keyword);
				if ($keyword === '') {
					continue;
				}

				$items[] = [
					'loc' => search_permalink(strtolower($keyword)),
					'lastmod' => date('Y-m-d', filemtime($file)),
				];

				if (count($items) >= $limit) {
					break 2;
				}
			}
		}

		return $items;
	}

	private function uniqueUrls($urls)
	{
		$unique = [];
		foreach ($urls as $url) {
			if (empty($url['loc'])) {
				continue;
			}

			$unique[$url['loc']] = $url;
		}

		return array_values($unique);
	}
    

}
