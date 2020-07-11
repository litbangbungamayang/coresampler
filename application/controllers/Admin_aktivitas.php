<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_aktivitas extends CI_Controller{
  public function __construct(){
    parent :: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("bahan_model");
    $this->load->model("transaksi_model");
    $this->load->model("dosis_model");
    $this->load->model("aktivitas_model");
    $this->load->library('form_validation');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
  }

  public function index(){
    if ($this->session->flashdata('error_message') == ''){
      $this->session->set_flashdata('error_div', 'display: none');
    }
    if ($this->session->flashdata('notif_msg') == ''){
      $this->session->set_flashdata('notif_div', 'display: none');
    }
    $data['pageTitle'] = "Administrasi Aktivitas";
    $data['content'] = $this->loadContent();
    $data['script'] = $this->loadScript();
    $this->load->view('main_view', $data);
  }

  public function addAktivitas(){
    echo $this->aktivitas_model->simpan();
  }

  public function updateAktivitas(){
    echo $this->aktivitas_model->updateAktivitas();
  }

  public function hapusAktivitas(){
    $id_aktivitas = $this->input->post("id_aktivitas");
    $transaksi = json_decode($this->transaksi_model->getTransaksiByIdAktivitas($id_aktivitas));
    if (sizeof($transaksi) == 0){
      if ($this->aktivitas_model->hapusAktivitas($id_aktivitas)) echo "Data aktivitas berhasil dihapus!";
    } else {
      echo "Terdapat transaksi dengan nama aktivitas yang akan dihapus. Proses menghapus dibatalkan.";
    }
  }

  public function getAktivitasById(){
    echo $this->aktivitas_model->getAktivitasById();
  }

  public function getAktivitasByTahunGiling(){
    echo $this->aktivitas_model->getAktivitasByTahunGiling();
  }

  public function getAllAktivitas(){
    echo $this->aktivitas_model->getAllAktivitas();
  }

  private function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Admin_aktivitas.js").'");';
  }

  private function loadContent(){
    $currYear = strval(date("Y"));
    $optionText = "";
    for ($x = $currYear; $x <= $currYear + 4; $x++){
      $optionText .= '<option value="'.$x.'">'.$x.'</option>';
    }

    $content_header =
    '
      <div class="page">
        <div class="row">
          <div class="card">
            <div style="'.$this->session->flashdata('error_div').'" class="card-body">
                <div class="alert alert-danger">'.$this->session->flashdata('error_message').'</div>
            </div>
            <div class="alert alert-success alert-dismissible" style="'.$this->session->flashdata('notif_div').'">'.$this->session->flashdata('notif_msg').'
              <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
            </div>
    ';
    $content =
    '
            <div class="card-body">
              <div class="row" style="margin-bottom: 10px; margin-left: 0px">
                <button type="button" id="btnTambahAktivitas" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddAktivitas"> + Tambah Aktivitas</button>
              </div>
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblAktivitas" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Nama Aktivitas</th>
                        <th>Tahun Giling</th>
                        <th>Biaya per Ha.</th>
                        <th></th>
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
        <div>
      </div>
    ';
    $content_dialogAddAktivitas =
    '
      <div class="modal fade" id="dialogAddAktivitas">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Data Aktivitas</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddAktivitas">
                <div class="row">
                  <div class="col-md-12 col-lg-3">
                    <div class="form-group" id="grTahunGiling">
                      <label class="form-label">Tahun Giling</label>
                      <select name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">
                      '.$optionText.'
                      </select>
                      <div class="invalid-feedback">Tahun giling belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grNamaAktivitas">
                      <label class="form-label">Nama Aktivitas</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="nama_aktivitas" name="nama_aktivitas" placeholder="Nama Aktivitas">
                      <div class="invalid-feedback" id="fbNamaAktivitas">Nama aktivitas belum diinput!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grKategori">
                      <label class="form-label">Kategori khusus</label>
                      <select name="kategori" id="kategori" class="custom-control custom-select" placeholder="Pilih kategori">
                        <option value="PC">PC</option>
                        <option value="RT">Ratoon</option>
                        <option value="ALL">Semua</option>
                      </select>
                      <div class="invalid-feedback">Kategori belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grBiaya">
                      <label class="form-label">Biaya per Hektar</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="biaya" name="biaya" placeholder="Biaya per Ha.">
                      <div class="invalid-feedback" id="fbNamaBahan">Biaya belum diinput!</div>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanAktivitas" class="btn btn-primary btn-block" name="" >Simpan data aktivitas</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content_header.$content.$content_footer.$content_dialogAddAktivitas;
  }

}
