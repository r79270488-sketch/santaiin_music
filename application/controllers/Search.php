<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {

	public function index($pencarian)
	{
        $replace = str_replace('-',' ', $pencarian);

        $putfile = autoSearchSitemap(strtolower($replace));
       
        
        $youtubepencarian = getCachedYoutubeSearch($replace);
        
        $titleParameter = ucwords($replace);
        $resultCount = is_array($youtubepencarian) ? count($youtubepencarian) : 0;

        $datas['music'] = $youtubepencarian;
        $datas['title_parameter'] = $titleParameter;
        $data['title'] = 'Pencarian MP3 | '.$titleParameter;
        $data['meta_title'] = 'Download Lagu '.$titleParameter.' MP3 Terbaru';
        $data['meta_description'] = 'Download dan dengarkan lagu '.$titleParameter.' MP3, daftar musik terkait, lirik, dan rekomendasi lagu terbaru di Santaiin MP3.';
        $data['keywords'] = $replace;
        $data['canonical_url'] = search_permalink($replace);
        $data['robots_meta'] = $resultCount > 0 ? 'index, follow' : 'noindex, follow';

        $this->load->view('themes/v1/base/header',$data);
        $this->load->view('themes/v1/search', $datas);
        $this->load->view('themes/v1/base/footer',$data);
    }
    

    public function cari()
    {
        $param = $this->input->get('q');
        $replace = str_replace(' ','-', $param);
        $direct = 'music/'.$replace;
        if(isset($param)){
            
            redirect(base_url($direct));
            
        }
    }
}
