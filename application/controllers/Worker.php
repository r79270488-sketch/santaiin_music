<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Worker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->load->helper('site');
        $this->load->model('Song_model', 'song_model');
        $this->load->model('Scrape_log_model', 'scrape_log_model');
    }

    public function sync_music($limit = 25, $region = 'ID')
    {
        $startedAt = date('Y-m-d H:i:s');
        $limit = max(1, min(50, (int) $limit));
        $region = strtoupper($region ?: 'ID');

        if (!$this->song_model->isEnabled()) {
            $this->line('Database belum dikonfigurasi. Isi application/config/database.php lalu jalankan migration SQL.');
            return;
        }

        try {
            $this->workerDelay();
            $items = getYoutubePopularMusic($limit, $region);
            $saved = $this->song_model->upsertMany($items, 'youtube');
            musicCacheDeleteByPrefix('youtube:popular');
            musicCacheDeleteByPrefix('youtube:search');

            $message = 'sync_music completed region=' . $region;
            $this->scrape_log_model->write('youtube', 'sync_music', 'success', $message, count($items), $saved, $startedAt);
            $this->line($message . ' found=' . count($items) . ' saved=' . $saved);
        } catch (Throwable $e) {
            $this->scrape_log_model->write('youtube', 'sync_music', 'error', $e->getMessage(), 0, 0, $startedAt);
            $this->line('sync_music error: ' . $e->getMessage());
        }
    }

    public function sync_search($query = '', $limit = 20)
    {
        $startedAt = date('Y-m-d H:i:s');
        $query = trim(str_replace('-', ' ', (string) $query));
        $limit = max(1, min(50, (int) $limit));

        if ($query === '') {
            $this->line('Usage: php index.php worker sync_search "lagu terbaru" 20');
            return;
        }

        if (!$this->song_model->isEnabled()) {
            $this->line('Database belum dikonfigurasi. Isi application/config/database.php lalu jalankan migration SQL.');
            return;
        }

        try {
            $this->workerDelay();
            $items = getYoutubeSearch($query);
            if (count($items) > $limit) {
                $items = array_slice($items, 0, $limit);
            }

            $saved = $this->song_model->upsertMany($items, 'youtube');
            musicCacheDeleteByPrefix('youtube:search');

            $message = 'sync_search completed query=' . $query;
            $this->scrape_log_model->write('youtube', 'sync_search', 'success', $message, count($items), $saved, $startedAt);
            $this->line($message . ' found=' . count($items) . ' saved=' . $saved);
        } catch (Throwable $e) {
            $this->scrape_log_model->write('youtube', 'sync_search', 'error', $e->getMessage(), 0, 0, $startedAt);
            $this->line('sync_search error: ' . $e->getMessage());
        }
    }

    public function sync_apple($limit = 25, $country = 'id')
    {
        $startedAt = date('Y-m-d H:i:s');
        $limit = max(1, min(50, (int) $limit));
        $country = strtolower($country ?: 'id');

        try {
            $this->workerDelay();
            $items = getAppleNewReleases($country, $limit);
            musicCacheSet('apple:new_releases:' . $country . ':' . min($limit, 12), array_slice($items, 0, 12), musicDataConfig('music_cache_ttl_popular', 3600));
            $this->scrape_log_model->write('apple', 'sync_apple', 'success', 'sync_apple completed country=' . $country, count($items), count($items), $startedAt);
            $this->line('sync_apple completed found=' . count($items));
        } catch (Throwable $e) {
            $this->scrape_log_model->write('apple', 'sync_apple', 'error', $e->getMessage(), 0, 0, $startedAt);
            $this->line('sync_apple error: ' . $e->getMessage());
        }
    }

    public function refresh_keywords()
    {
        $startedAt = date('Y-m-d H:i:s');

        try {
            $keywords = collectAutoKeywords();
            if (!empty($keywords)) {
                refreshKeywordFile(FCPATH . 'keywoard/kw1.txt', $keywords, 100);
                refreshKeywordFile(FCPATH . 'keywoard/sitemap.txt', $keywords, 50);
            }

            $this->scrape_log_model->write('music', 'refresh_keywords', 'success', 'refresh_keywords completed', count($keywords), count($keywords), $startedAt);
            $this->line('refresh_keywords completed keywords=' . count($keywords));
        } catch (Throwable $e) {
            $this->scrape_log_model->write('music', 'refresh_keywords', 'error', $e->getMessage(), 0, 0, $startedAt);
            $this->line('refresh_keywords error: ' . $e->getMessage());
        }
    }

    public function refresh_old_content($limit = 50)
    {
        $startedAt = date('Y-m-d H:i:s');
        $limit = max(1, min(200, (int) $limit));

        if (!$this->song_model->isEnabled()) {
            $this->line('Database belum dikonfigurasi. Isi application/config/database.php lalu jalankan migration SQL.');
            return;
        }

        try {
            $stale = $this->song_model->staleYoutube(24, $limit);
            $saved = 0;

            foreach ($stale as $row) {
                $this->workerDelay();
                $items = getYoutubeSearch($row['title']);
                $saved += $this->song_model->upsertMany($items, 'youtube');
            }

            musicCacheDeleteByPrefix('youtube:');
            $message = 'refresh_old_content completed';
            $this->scrape_log_model->write('youtube', 'refresh_old_content', 'success', $message, count($stale), $saved, $startedAt);
            $this->line($message . ' stale=' . count($stale) . ' saved=' . $saved);
        } catch (Throwable $e) {
            $this->scrape_log_model->write('youtube', 'refresh_old_content', 'error', $e->getMessage(), 0, 0, $startedAt);
            $this->line('refresh_old_content error: ' . $e->getMessage());
        }
    }

    public function cleanup_cache($maxAgeHours = 48)
    {
        $maxAge = max(1, (int) $maxAgeHours) * 3600;
        $patterns = [
            APPPATH . 'cache/music_data/*.json',
            APPPATH . 'cache/search_*.json',
            APPPATH . 'cache/youtube_popular_music_*.json',
            APPPATH . 'cache/download_proxy_*.json',
            APPPATH . 'cache/download_cookie_*.txt',
            APPPATH . 'cache/download_tmp_*.bin',
        ];
        $deleted = 0;

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                    @unlink($file);
                    $deleted++;
                }
            }
        }

        $this->line('cleanup_cache deleted=' . $deleted);
    }

    public function cleanup_logs($days = 30)
    {
        $deleted = $this->scrape_log_model->cleanup($days);
        $this->line('cleanup_logs deleted=' . $deleted);
    }

    public function crontab()
    {
        $this->line('0 * * * * php ' . FCPATH . 'index.php worker sync_music 25 ID');
        $this->line('5 * * * * php ' . FCPATH . 'index.php worker sync_apple 25 id');
        $this->line('*/30 * * * * php ' . FCPATH . 'index.php worker sync_search "lagu terbaru" 20');
        $this->line('0 3 * * * php ' . FCPATH . 'index.php worker refresh_old_content 50');
        $this->line('5 3 * * * php ' . FCPATH . 'index.php worker refresh_keywords');
        $this->line('15 3 * * * php ' . FCPATH . 'index.php worker cleanup_cache 48');
        $this->line('30 3 * * * php ' . FCPATH . 'index.php worker cleanup_logs 30');
    }

    private function workerDelay()
    {
        $min = (int) musicDataConfig('music_worker_delay_min_ms', 500);
        $max = (int) musicDataConfig('music_worker_delay_max_ms', 2000);
        $delay = mt_rand(min($min, $max), max($min, $max));
        usleep($delay * 1000);
    }

    private function line($message)
    {
        echo $message . PHP_EOL;
    }
}
