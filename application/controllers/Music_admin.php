<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Music_admin extends CI_Controller
{
    public function index()
    {
        $this->load->helper('site');
        if (!$this->allowed()) {
            show_404();
        }

        $this->load->model('Song_model', 'song_model');
        $this->load->model('Scrape_log_model', 'scrape_log_model');

        $stats = $this->song_model->stats();
        $logs = $this->scrape_log_model->recent(20);

        $this->output->set_content_type('text/html; charset=UTF-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Music Data Admin</title>';
        echo '<style>body{font-family:Arial,sans-serif;max-width:980px;margin:32px auto;padding:0 16px;color:#172033}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;text-align:left}code{background:#f1f3f7;padding:2px 4px;border-radius:4px}.ok{color:#087f5b}.bad{color:#c92a2a}</style>';
        echo '</head><body>';
        echo '<h1>Music Data Admin</h1>';
        echo '<h2>Status</h2>';
        echo '<p>Database: <strong class="' . ($stats['enabled'] ? 'ok' : 'bad') . '">' . ($stats['enabled'] ? 'enabled' : 'not configured') . '</strong></p>';
        echo '<p>Total songs: <strong>' . (int) $stats['total_songs'] . '</strong></p>';
        echo '<p>YouTube songs: <strong>' . (int) $stats['youtube_songs'] . '</strong></p>';
        echo '<p>Last scrape: <strong>' . html_escape($stats['last_scraped_at'] ?: '-') . '</strong></p>';
        echo '<h2>Cron</h2>';
        echo '<p><code>php ' . html_escape(FCPATH) . 'index.php worker crontab</code></p>';
        echo '<h2>Recent Logs</h2>';
        echo '<table><thead><tr><th>Time</th><th>Source</th><th>Job</th><th>Status</th><th>Found</th><th>Saved</th><th>Message</th></tr></thead><tbody>';
        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td>' . html_escape($log['created_at']) . '</td>';
            echo '<td>' . html_escape($log['source']) . '</td>';
            echo '<td>' . html_escape($log['job']) . '</td>';
            echo '<td>' . html_escape($log['status']) . '</td>';
            echo '<td>' . (int) $log['items_found'] . '</td>';
            echo '<td>' . (int) $log['items_saved'] . '</td>';
            echo '<td>' . html_escape($log['message']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</body></html>';
    }

    private function allowed()
    {
        $token = (string) musicDataConfig('music_admin_token', '');
        if ($token !== '' && hash_equals($token, (string) $this->input->get('token'))) {
            return true;
        }

        $ip = $this->input->ip_address();
        return in_array($ip, ['127.0.0.1', '::1'], true);
    }
}
