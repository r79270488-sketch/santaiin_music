<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Detail extends CI_Controller
{
  public function index($id_video = "")
  {
    if (empty($id_video)) {
      show_404();
    }

    $id = substr($id_video, 0, 11);
    $title_id = substr($id_video, 12);

    if (strlen($id) !== 11) {
      show_404();
    }

    $title = get_title($title_id);

    if (empty($title)) {
      $title = str_replace("-", " ", $title_id);
    }

    $data["id_video"] = $id;
    $data["title_meta"] = $title;
    $data["title"] = $title;
    $data["keywords"] =
      "Download MP3 Gratis,download lagu gratis, download lagu terbaru, download lagu populer, download lagu dangdut, download lagu pop indonesia," .
      $title;

    $text = "{Anda|Kamu|Kalian|Agan} dapat {mendengarkan|dengerin|streaming} music dan download lagu mp3 <strong>$title</strong> secara gratis di situs.";
    $data["spin_text"] = spin($text);

    $this->load->view("themes/v1/base/header", $data);
    $this->load->view("themes/v1/detail", $data);
    $this->load->view("themes/v1/base/footer", $data);
  }
}

