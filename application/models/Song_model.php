<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Song_model extends CI_Model
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
                siteLogMessage('error', 'Song_model database init failed: ' . $e->getMessage());
            }
        }
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function upsertMany($items, $source = 'youtube')
    {
        if (!$this->enabled || empty($items)) {
            return 0;
        }

        $saved = 0;
        foreach ($items as $item) {
            if ($this->upsert($item, $source)) {
                $saved++;
            }
        }

        return $saved;
    }

    public function upsert($item, $source = 'youtube')
    {
        if (!$this->enabled) {
            return false;
        }

        $sourceId = $item['source_id'] ?? $item['id'] ?? $item['youtube_id'] ?? '';
        $title = trim($item['title'] ?? $item['judul'] ?? '');

        if ($sourceId === '' || $title === '') {
            return false;
        }

        $artist = trim($item['artist'] ?? $item['uploader'] ?? $item['channel'] ?? '');
        $thumbnail = $item['thumbnail'] ?? $item['thumbnails'] ?? '';
        $duration = (int) ($item['duration'] ?? 0);
        $publishedAt = $this->mysqlDate($item['published_at'] ?? $item['publishedAt'] ?? null);
        $now = date('Y-m-d H:i:s');
        $slug = $this->uniqueStableSlug($sourceId, $title);
        $metaTitle = $item['meta_title'] ?? ('Download Lagu ' . $title . ' MP3');
        $metaDescription = $item['meta_description'] ?? ('Download lagu ' . $title . ' MP3 gratis hanya untuk review.');

        $sql = "INSERT INTO songs
            (source, source_id, slug, title, artist, thumbnail, duration, published_at, last_scraped_at, meta_title, meta_description, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)
            ON DUPLICATE KEY UPDATE
                slug = VALUES(slug),
                title = VALUES(title),
                artist = VALUES(artist),
                thumbnail = VALUES(thumbnail),
                duration = VALUES(duration),
                published_at = COALESCE(VALUES(published_at), published_at),
                last_scraped_at = VALUES(last_scraped_at),
                meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description),
                status = 'active',
                updated_at = VALUES(updated_at)";

        return (bool) $this->db->query($sql, [
            $source,
            $sourceId,
            $slug,
            $title,
            $artist,
            $thumbnail,
            $duration,
            $publishedAt,
            $now,
            $metaTitle,
            $metaDescription,
            $now,
            $now,
        ]);
    }

    public function getPopularYoutube($limit = 12)
    {
        if (!$this->enabled) {
            return [];
        }

        $limit = max(1, min(50, (int) $limit));

        $rows = $this->db
            ->where('source', 'youtube')
            ->where('status', 'active')
            ->order_by('published_at IS NULL', 'ASC', false)
            ->order_by('published_at', 'DESC')
            ->order_by('updated_at', 'DESC')
            ->limit($limit)
            ->get('songs')
            ->result_array();

        return $this->toListItems($rows);
    }

    public function searchYoutube($query, $limit = 20)
    {
        if (!$this->enabled) {
            return [];
        }

        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $limit = max(1, min(50, (int) $limit));
        $terms = preg_split('/\s+/', $query);

        $this->db
            ->from('songs')
            ->where('source', 'youtube')
            ->where('status', 'active');

        foreach ($terms as $term) {
            $term = trim($term);
            if ($term === '') {
                continue;
            }

            $this->db->group_start()
                ->like('title', $term)
                ->or_like('artist', $term)
                ->group_end();
        }

        $rows = $this->db
            ->order_by('published_at IS NULL', 'ASC', false)
            ->order_by('published_at', 'DESC')
            ->order_by('updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();

        return $this->toListItems($rows);
    }

    public function findYoutubeById($youtubeId)
    {
        if (!$this->enabled || $youtubeId === '') {
            return null;
        }

        $row = $this->db
            ->where('source', 'youtube')
            ->where('source_id', $youtubeId)
            ->where('status', 'active')
            ->limit(1)
            ->get('songs')
            ->row_array();

        return $row ?: null;
    }

    public function staleYoutube($hours = 24, $limit = 50)
    {
        if (!$this->enabled) {
            return [];
        }

        $cutoff = date('Y-m-d H:i:s', time() - ((int) $hours * 3600));

        return $this->db
            ->where('source', 'youtube')
            ->where('status', 'active')
            ->group_start()
                ->where('last_scraped_at IS NULL', null, false)
                ->or_where('last_scraped_at <', $cutoff)
            ->group_end()
            ->order_by('last_scraped_at', 'ASC')
            ->limit(max(1, min(200, (int) $limit)))
            ->get('songs')
            ->result_array();
    }

    public function stats()
    {
        if (!$this->enabled) {
            return [
                'enabled' => false,
                'total_songs' => 0,
                'youtube_songs' => 0,
                'last_scraped_at' => null,
            ];
        }

        $total = (int) $this->db->count_all('songs');
        $youtube = (int) $this->db->where('source', 'youtube')->count_all_results('songs');
        $last = $this->db
            ->select_max('last_scraped_at')
            ->get('songs')
            ->row_array();

        return [
            'enabled' => true,
            'total_songs' => $total,
            'youtube_songs' => $youtube,
            'last_scraped_at' => $last['last_scraped_at'] ?? null,
        ];
    }

    private function toListItems($rows)
    {
        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'id' => $row['source_id'],
                'judul' => $row['title'],
                'artist' => $row['artist'],
                'description' => $row['meta_description'] ?? '',
                'thumbnails' => $row['thumbnail'],
                'uploader' => $row['artist'],
                'duration' => (int) $row['duration'],
                'url_detail' => single_permalink($row['source_id'], $row['title']),
            ];
        }

        return $items;
    }

    private function uniqueStableSlug($sourceId, $title)
    {
        return url_title($title, '-', true) . '-' . strtolower($sourceId);
    }

    private function mysqlDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $time = strtotime($value);
        if (!$time) {
            return null;
        }

        return date('Y-m-d H:i:s', $time);
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
