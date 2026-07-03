<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{
    public function index()
    {
        $popularSongs = [];
        $newReleases = [];

        try {
            $popularSongs = getCachedYoutubePopularMusic(12, 'ID');
        } catch (Throwable $e) {
            log_message('error', 'Cached popular music error: ' . $e->getMessage());
            $popularSongs = [];
        }

        try {
            $newReleases = getCachedAppleNewReleases('id', 12);
        } catch (Throwable $e) {
            log_message('error', 'Cached Apple new releases error: ' . $e->getMessage());
            $newReleases = [];
        }

        $data['popularSongs'] = $popularSongs;
        $data['newReleases'] = $newReleases;

        // Biar view lama tetap aman kalau masih pakai variable ini
        $data['trending'] = $popularSongs;
        $data['indonesia'] = $newReleases;

        $data['title'] = 'Santaiin MP3 - Lagu Terbaru dan Musik Trending';
        $data['meta_title'] = 'Santaiin MP3 - Download Lagu Terbaru dan Musik Trending';
        $data['meta_description'] = 'Cari lagu terbaru, musik trending, viral TikTok, dangdut, pop Indonesia, dan rilis terbaru dengan halaman cepat dari cache Santaiin MP3.';
        $data['keywords'] = 'download mp3, lagu terbaru, lagu viral, musik trending, lagu indonesia, lagu barat, top songs';
        $data['canonical_url'] = base_url();

        // Matikan dulu saat testing
        // $this->output->cache(5);

        $this->load->view('themes/v1/base/header', $data);
        $this->load->view('themes/v1/home', $data);
        $this->load->view('themes/v1/base/footer', $data);
    }
}
