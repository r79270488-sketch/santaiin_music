<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['music_cache_driver'] = 'auto'; // auto, redis, file
$config['music_cache_prefix'] = 'music:';
$config['music_cache_path'] = APPPATH . 'cache/music_data/';
$config['music_cache_ttl_popular'] = 60 * 60;
$config['music_cache_ttl_search'] = 6 * 60 * 60;
$config['music_live_fallback'] = TRUE;

$config['music_redis_host'] = '127.0.0.1';
$config['music_redis_port'] = 6379;
$config['music_redis_timeout'] = 1.0;
$config['music_redis_database'] = 0;
$config['music_redis_password'] = '';

$config['music_worker_delay_min_ms'] = 500;
$config['music_worker_delay_max_ms'] = 2000;
$config['music_worker_max_requests'] = 60;

$config['music_admin_token'] = 'ead3929d7d3a46e53c64e942b6e5462bfd95f38acb5f1ecf';
