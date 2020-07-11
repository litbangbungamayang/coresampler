<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_tma extends CI_Controller{
  public function __construct(){
    parent :: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("transaksi_model");
    $this->load->model("biayatma_model");
    $this->load->library('form_validation');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
  }

  public function index(){
    $data['pageTitle'] = "Administrasi Biaya TMA";
    $data['content'] = $this->loadContent();
    $data['script'] = $this->loadScript();
    $this->load->view('main_view', $data);
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Admin_tma.js").'");';
  }

  public function addBiayaTma(){
    $post = $this->input->post();
    $tahun_giling = $post["tahun_giling"];
    $id_wilayah = $post["id_wilayah"];
    $biaya = $post["biaya"];
    if(is_null(json_decode($this->biayatma_model->cekDuplikat($post)))){
      echo $this->biayatma_model->simpan($post);
    } else {
      echo json_encode("DUPLIKAT");
    }
  }

  public function hapusDataById(){
    $post = $this->input->post();
    $record_count = sizeof(json_decode($this->biayatma_model->getTransaksiByIdBiayaTma($post["id_biayatma"])));
    if($record_count == 0){
      echo $this->biayatma_model->hapusData($post);
    } else {
      echo json_encode("EXIST");
    }
  }

  public function getAllBiayaTma(){
    echo $this->biayatma_model->getAllBiayaTma();
  }

  public function getBiayaTmaById(){
    echo $this->biayatma_model->getBiayaTmaById();
  }

  public function getBiayaTmaByIdWilayah(){
    echo $this->biayatma_model->getBiayaTmaByIdWilayah();
  }

  public function editBiayaTma(){
    $post = $this->input->post();
    if(is_null(json_decode($this->biayatma_model->cekDuplikat($post)))){
      $record_count = sizeof(json_decode($this->biayatma_model->getTransaksiByIdBiayaTma($post["id_biayatma"])));
      if($record_count == 0){
        echo $this->biayatma_model->editBiayaTma($post);
      } else {
        echo json_encode("EXIST");
      }
    } else {
      echo json_encode("DUPLIKAT");
    }
  }

  public function editBiayaTmaWilayahTetap(){
    $post = $this->input->post();
    $record_count = sizeof(json_decode($this->biayatma_model->getTransaksiByIdBiayaTma($post["id_biayatma"])));
    if($record_count == 0){
      echo $this->biayatma_model->editBiayaTma($post);
    } else {
      echo json_encode("EXIST");
    }
  }

  public function loadContent(){
    $content =
    '
      <div class="page">
        <div class="row">
          <div class="card">
            <div class="card-body">
              <div class="row" style="margin-bottom: 10px; margin-left: 0px">
                <button type="button" id="btnTambahTma" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddTma"> + Tambah Biaya TMA</button>
              </div>
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblBiayaTma" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Tahun Giling</th>
                        <th>Nama Desa</th>
                        <th>Kabupaten</th>
                        <th>Biaya TMA per Ton Tebu</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
    ';
    $content_footer =
    '
          </div>
        </div>
      </div>
    ';
    $currYear = strval(date("Y"));
    $optionText = "";
    for ($x = $currYear; $x <= $currYear + 4; $x++){
      $optionText .= '<option value="'.$x.'">'.$x.'</option>';
    }
    $content_dialogAddBahan =
    '
      <div class="modal fade" id="dialogAddTma">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Data Biaya TMA</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddTma">
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grTahunGiling">
                      <label class="form-label">Tahun Giling</label>
                      <select name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">
                      '.$optionText.'
                      </select>
                      <div class="invalid-feedback">Tahun giling belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grNamaKab">
                      <label class="form-label">Kabupaten</label>
                      <select name="namaKab" id="namaKab" class="custom-control custom-select" placeholder="Pilih nama kabupaten"></select>
                      <div class="invalid-feedback">Kabupaten belum dipilih!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaDesa">
                      <label class="form-label">Desa</label>
                      <select name="namaDesa" id="namaDesa" class="custom-control custom-select" placeholder="Pilih nama desa"></select>
                      <div class="invalid-feedback">Desa belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grBiaya">
                      <label class="form-label">Biaya TMA per ton tebu</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="biaya" name="biaya" placeholder="Biaya per ton tebu">
                      <div class="invalid-feedback" id="fbNamaBahan">Biaya belum diinput!</div>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanBiayaTma" class="btn btn-primary btn-block" name="" >Simpan data biaya TMA</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content.$content_footer.$content_dialogAddBahan;
  }
}
