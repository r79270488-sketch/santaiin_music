<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scrape_log_model extends CI_Model
{
    private $enabled = false;

    public function __construct()
    {
        parent::__construct();
        $this->enabled = $this->databaseConfigured();

        if ($this->enabled) {
            try {
                $this->load->database();
            } catch (Throwable $e) {
                $this->enabled = false;
            }
        }
    }

    public function write($source, $job, $status, $message = '', $itemsFound = 0, $itemsSaved = 0, $startedAt = null)
    {
        if (!$this->enabled) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        return (bool) $this->db->insert('scrape_logs', [
            'source' => $source,
            'job' => $job,
            'status' => $status,
            'message' => $message,
            'items_found' => (int) $itemsFound,
            'items_saved' => (int) $itemsSaved,
            'started_at' => $startedAt ?: $now,
            'finished_at' => $now,
            'created_at' => $now,
        ]);
    }

    public function cleanup($days = 30)
    {
        if (!$this->enabled) {
            return 0;
        }

        $cutoff = date('Y-m-d H:i:s', time() - ((int) $days * 86400));
        $this->db->where('created_at <', $cutoff)->delete('scrape_logs');

        return $this->db->affected_rows();
    }

    public function recent($limit = 20)
    {
        if (!$this->enabled) {
            return [];
        }

        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit(max(1, min(100, (int) $limit)))
            ->get('scrape_logs')
            ->result_array();
    }

    private function databaseConfigured()
    {
        $active_group = 'default';
        $query_builder = TRUE;
        $db = [];
        include APPPATH . 'config/database.php';

        return !empty($db[$active_group]['database'])
            && !empty($db[$active_group]['username']);
    }
}
