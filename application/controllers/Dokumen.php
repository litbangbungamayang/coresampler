<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Dokumen extends CI_Controller{

  public function __construct(){
    parent :: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("dokumen_model");
    $this->load->library('form_validation');
    $this->load->helper('url');
  }

  public function simpan(){
    echo $this->dokumen_model->simpan();
  }

}
