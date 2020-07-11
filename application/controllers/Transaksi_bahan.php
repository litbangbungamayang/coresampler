<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_bahan extends CI_Controller{
  public function __construct(){
    parent :: __construct();
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
      $data['pageTitle'] = "Transaksi Bahan Gudang";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function addTransaksi(){
    echo $this->transaksi_model->simpan();
  }

  public function getTransaksiByKode(){
    echo $this->transaksi_model->getTransaksiByKode();
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Transaksi_bahan.js").'");';
  }

  public function getTransaksiMasukByTahunGiling(){
    echo $this->transaksi_model->getTransaksiMasukByTahunGiling();
  }

  public function getTransaksiKeluarByIdKelompok(){
    echo $this->transaksi_model->getTransaksiKeluarByIdKelompok();
  }

  public function cekStokBahanByIdBahan(){
    echo $this->transaksi_model->cekStokBahanByIdBahan();
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
                <button type="button" id="btnTambahTransaksi" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddTransaksi"> + Buat Transaksi Barang Masuk</button>
              </div>
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblTransaksi" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th class="text-center">Nama Bahan</th>
                        <th class="text-center">Nama Vendor</th>
                        <th class="text-center">Nomor Kontrak</th>
                        <th class="text-center">Transaksi</th>
                        <th class="text-center">Kuanta</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Tahun Giling</th>
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
    $content_dialogAddTransaksi =
    '
      <div class="modal fade" id="dialogAddTransaksi">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Data Transaksi Bahan Gudang</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddBahan">
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grTahunGiling">
                      <label class="form-label">Tahun Giling</label>
                      <select name="tahun_giling" id="tahun_giling" class="custom-control custom-select" placeholder="Pilih tahun giling">
                        <option value="">Pilih tahun giling</option>
                      '.$optionText.'
                      </select>
                      <div class="invalid-feedback">Tahun giling belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grNamaBahan">
                      <label class="form-label">Nama Bahan</label>
                      <select name="nama_bahan" id="nama_bahan" class="custom-control custom-select" placeholder="Pilih Bahan">
                      </select>
                      <div class="invalid-feedback">Nama bahan belum diisi!</div>
                    </div>
                    <div class="form-group" id="grKuanta">
                      <label class="form-label">Kuanta</label>
                      <div class="row">
                        <div class="col-md-12 col-lg-8">
                          <input type="text" style="text-transform: uppercase;" class="form-control text-right" id="kuanta_bahan" name="kuanta_bahan" placeholder="Kuanta bahan">
                          <div class="invalid-feedback">Kuanta bahan belum diisi!</div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                          <input type="text" style="text-transform: uppercase;" class="form-control" id="satuan_bahan" name="satuan_bahan" placeholder="Satuan" disabled>
                        </div>
                      </div>
                      <div class="invalid-feedback">Kuanta bahan belum diisi!</div>
                    </div>

                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaVendor">
                      <label class="form-label">Nama Vendor</label>
                      <select name="nama_vendor" id="nama_vendor" class="custom-control custom-select" placeholder="Pilih Vendor">
                      </select>
                      <div class="invalid-feedback">Nama vendor belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grRupiah">
                      <label class="form-label">Nilai</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control text-right" id="rupiah_bahan" name="rupiah_bahan" placeholder="Nilai bahan">
                      <div class="invalid-feedback">Rupiah bahan belum diisi!</div>
                    </div>
                    <div class="form-group" id="grNomor">
                      <label class="form-label">Nomor Tanda Terima</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control text-right" id="catatan" name="catatan" placeholder="Nomor tanda terima bahan">
                      <div class="invalid-feedback">Nomor tanda terima bahan belum diisi!</div>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanTransaksi" class="btn btn-primary btn-block" name="" ><i class="fe fe-save"></i> Simpan Data Transaksi</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content_header.$content_1.$content_footer.$content_dialogAddTransaksi;
  }
}
