<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_music_data_tables extends CI_Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS songs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source VARCHAR(32) NOT NULL,
            source_id VARCHAR(64) NOT NULL,
            slug VARCHAR(191) NULL,
            title VARCHAR(255) NOT NULL,
            artist VARCHAR(255) NULL,
            thumbnail TEXT NULL,
            duration INT UNSIGNED NOT NULL DEFAULT 0,
            published_at DATETIME NULL,
            last_scraped_at DATETIME NULL,
            meta_title VARCHAR(255) NULL,
            meta_description TEXT NULL,
            status VARCHAR(24) NOT NULL DEFAULT 'active',
            views BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_songs_source_source_id (source, source_id),
            UNIQUE KEY uq_songs_slug (slug),
            KEY idx_songs_published_at (published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS scrape_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source VARCHAR(32) NOT NULL,
            job VARCHAR(64) NOT NULL,
            status VARCHAR(24) NOT NULL,
            message TEXT NULL,
            items_found INT UNSIGNED NOT NULL DEFAULT 0,
            items_saved INT UNSIGNED NOT NULL DEFAULT 0,
            started_at DATETIME NULL,
            finished_at DATETIME NULL,
            created_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_scrape_logs_source_job_created_at (source, job, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down()
    {
        $this->load->dbforge();
        $this->dbforge->drop_table('scrape_logs', TRUE);
        $this->dbforge->drop_table('songs', TRUE);
    }
}
