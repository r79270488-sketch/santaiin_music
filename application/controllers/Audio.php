<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audio extends CI_Controller
{
    public function preview()
    {
        $this->load->helper('site');
        $query = trim((string) $this->input->get('q', true));

        if ($query === '' || strlen($query) > 180) {
            return $this->json([
                'ok' => false,
                'message' => 'Query audio tidak valid.',
            ], 400);
        }

        $cacheFile = APPPATH . 'cache/audio_preview_' . md5(strtolower($query)) . '.json';
        $ttl = 7 * 24 * 60 * 60;

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $cached = json_decode((string) @file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $this->json($cached);
            }
        }

        $preview = $this->findPreview($query);

        $payload = [
            'ok' => $preview !== '',
            'previewUrl' => $preview,
            'message' => $preview !== '' ? 'Preview audio tersedia.' : 'Preview audio belum tersedia.',
        ];

        @file_put_contents($cacheFile, json_encode($payload), LOCK_EX);

        return $this->json($payload);
    }

    private function findPreview($query)
    {
        $items = getItunesSearch($query, 'id', 5);

        foreach ($items as $item) {
            if (!empty($item['previewUrl'])) {
                return $item['previewUrl'];
            }
        }

        return '';
    }

    private function json($payload, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
