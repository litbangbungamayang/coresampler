<?php defined('BASEPATH') OR exit('No direct script access allowed');

class List_au58 extends CI_Controller{
  public function __construct(){
    parent:: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("kelompoktani_model");
    $this->load->model("transaksi_model");
    $this->load->model("aktivitas_model");
    $this->load->model("bahan_model");
    $this->load->model("dokumen_model");
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $this->load->helper('file');
  }

  public function index(){
    if ($this->session->userdata('id_user') == false){
      redirect('login');
    } else {
      $data['pageTitle'] = "Rincian AU58";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/List_au58.js").'");';
  }

  public function getAllAu58(){
    echo $this->transaksi_model->getAllAu58();
  }

  public function getDesaByIdPbma($id_pbma = null){
      echo $this->transaksi_model->getDesaByIdPbma($id_pbma);
  }

  public function validasiDokumen(){
    $this->dokumen_model->validasi();
  }

  public function loadContent(){
    // <table id="tblListPupuk" class="table card-table table-vcenter text-nowrap datatable table-sm">
    $container =
    '
    <div class="page">
      <div class="row">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="table-responsive col-12">
                <table id="tblListAu58" class="table table-card table-striped table-sm text-nowrap">
                  <thead>
                    <tr>
                      <th class="w-1">No.</th>
                      <th>No. Dokumen</th>
                      <th>Tgl. Dibuat</th>
                      <th>Nama Kelompok</th>
                      <th>No. Kontrak</th>
                      <th>Total Kuanta</th>
                      <th>Status</th>
                      <th class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot class="bg-gray">
                    <tr>
                      <th class="w-1"></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th><font color="white" size="3">Total kuanta s.d </font></th>
                      <th></th>
                      <th></th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    ';
    return $container;
  }
}

?>
