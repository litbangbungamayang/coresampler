<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_bahan extends CI_Controller{
  public function __construct(){
    parent :: __construct();
    //if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("bahan_model");
    $this->load->model("transaksi_model");
    $this->load->library('form_validation');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
  }

  public function index(){
    if ($this->session->userdata('id_user') == false){
			redirect('login');
    } else {
      if ($this->session->flashdata('error_message') == ''){
        $this->session->set_flashdata('error_div', 'display: none');
      }
      if ($this->session->flashdata('notif_msg') == ''){
        $this->session->set_flashdata('notif_div', 'display: none');
      }
      $data['pageTitle'] = "Administrasi Bahan";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Admin_bahan.js").'");';
  }

  public function actions(){
    echo($this->input->get("edit_data"));
  }

  public function addBahan(){
    $this->bahan_model->simpan();
  }

  public function editBahan(){
    $this->bahan_model->edit();
  }

  public function hapusBahan(){
    $id_bahan = $this->input->post("id_bahan");
    $transaksi = json_decode($this->transaksi_model->getTransaksiByIdBahan($id_bahan));
    //var_dump(sizeof($transaksi));
    if (sizeof($transaksi) == 0){
      if ($this->bahan_model->hapus($id_bahan)) echo "Data bahan berhasil dihapus!";
    } else {
      echo "Terdapat transaksi dengan nama bahan yang akan dihapus. Proses menghapus dihentikan.";
    }
  }

  public function getAllBahan(){
    echo $this->bahan_model->getAllBahan();
  }

  public function getBahanById(){
    echo $this->bahan_model->getBahanById();
  }

  public function getBahanByJenis(){
    echo $this->bahan_model->getBahanByJenis();
  }

  public function getBahanByJenisTahunGiling(){
    echo $this->bahan_model->getBahanByJenisTahunGiling();
  }

  public function getBahanByTahunGiling(){
    echo $this->bahan_model->getBahanByTahunGiling();
  }

  public function loadContent(){
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
    $content_1 =
    '
            <div class="card-body">
              <div class="row" style="margin-bottom: 10px; margin-left: 0px">
                <button type="button" id="btnTambahBahan" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddBahan"> + Tambah Bahan</button>
              </div>
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblBahan" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Nama Bahan</th>
                        <th>Jenis Bahan</th>
                        <th>Satuan</th>
                        <th>Dosis per Ha.</th>
                        <th>Biaya Muat</th>
                        <th>Biaya Angkut</th>
                        <th>Tahun Giling</th>
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
      <div class="modal fade" id="dialogAddBahan">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Data Bahan Gudang</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddBahan">
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaBahan">
                      <label class="form-label">Nama Bahan</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="nama_bahan" name="nama_bahan" placeholder="Nama Bahan">
                      <div class="invalid-feedback" id="fbNamaBahan">Nama bahan belum diinput!</div>
                    </div>
                    <div class="form-group" id="grSatuan">
                      <label class="form-label">Satuan</label>
                      <select name="satuan" id="satuan" class="custom-control custom-select" placeholder="Pilih satuan">
                        <option value="">Pilih satuan</option>
                        <option value="LITER">Liter</option>
                        <option value="KG">Kg</option>
                      </select>
                      <div class="invalid-feedback">Satuan belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grTahunGiling">
                      <label class="form-label">Tahun Giling</label>
                      <select name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">
                      '.$optionText.'
                      </select>
                      <div class="invalid-feedback">Tahun giling belum dipilih!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grJenisBahan">
                      <label class="form-label">Jenis Bahan</label>
                      <select name="jenis_bahan" id="jenis_bahan" class="custom-control custom-select" placeholder="Pilih jenis bahan">
                        <option value="">Pilih jenis bahan</option>
                        <option value="PUPUK">Pupuk</option>
                        <option value="HERBISIDA">Herbisida</option>
                        <option value="ZPK">ZPK</option>
                        <option value="BAHAN KIMIA LAIN">Bahan kimia lain</option>
                      </select>
                      <div class="invalid-feedback">Jenis bahan belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grDosis">
                      <label class="form-label">Dosis per Ha.</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="dosis" name="dosis" placeholder="Dosis Bahan">
                      <div class="invalid-feedback" id="fbNamaBahan">Dosis bahan belum diinput!</div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 col-lg-6">
                    <div class="form-group" id="grBiayaMuat">
                      <label class="form-label">Biaya Muat-Bongkar (Rp per satuan)</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="biaya_muat" name="biaya_muat" placeholder="Biaya muat">
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-6">
                    <div class="form-group" id="grBiayaAngkut">
                      <label class="form-label">Biaya Angkut (Rp per satuan)</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="biaya_angkut" name="biaya_angkut" placeholder="Biaya angkut">
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanBahan" class="btn btn-primary btn-block" name="" >Simpan data bahan</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content_header.$content_1.$content_footer.$content_dialogAddBahan;
  }
}
