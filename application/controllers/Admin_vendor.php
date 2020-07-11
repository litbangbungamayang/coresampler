<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_vendor extends CI_Controller{
  public function __construct(){
    parent :: __construct();
    $this->load->model("vendor_model");
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
      $data['pageTitle'] = "Administrasi Vendor";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Admin_vendor.js").'");';
  }

  public function getAllVendor(){
    echo $this->vendor_model->getAllVendor();
  }

  public function getVendorById(){
      echo $this->vendor_model->getVendorById();
  }

  public function editVendor(){
    $this->vendor_model->edit();
  }

  public function addVendor(){
    $this->vendor_model->simpan();
  }

  public function hapusVendor(){
    $id_vendor = $this->input->post("id_vendor");
    $transaksi = json_decode($this->transaksi_model->getTransaksiByIdVendor($id_vendor));
    if (sizeof($transaksi) == 0){
      if ($this->vendor_model->hapus($id_vendor)) echo "Data bahan berhasil dihapus!";
    } else {
      echo "Terdapat transaksi dengan nama vendor yang akan dihapus. Proses menghapus dihentikan.";
    }
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
                <button type="button" id="btnTambahVendor" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddVendor"> + Tambah Vendor</button>
              </div>
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblVendor" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Nama Vendor</th>
                        <th>Nama Kontak</th>
                        <th>Telp. Kontak</th>
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
    $content_dialogAddBahan =
    '
      <div class="modal fade" id="dialogAddVendor">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Data Vendor</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddVendor">
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaVendor">
                      <label class="form-label">Nama Vendor</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="nama_vendor" name="nama_vendor" placeholder="Nama Vendor">
                      <div class="invalid-feedback" id="fbNamaVendor">Nama vendor belum diinput!</div>
                    </div>
                    <div class="form-group" id="grNpwp">
                      <label class="form-label">NPWP</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="npwp_vendor" name="npwp_vendor" placeholder="NPWP Vendor">
                      <div class="invalid-feedback" id="fbNamaVendor">NPWP vendor belum diinput!</div>
                    </div>
                    <div class="form-group" id="grAlamat1">
                      <label class="form-label">Alamat Vendor</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="alamat_vendor" name="alamat_vendor" placeholder="Alamat Vendor">
                      <div class="invalid-feedback" id="fbAlamatVendor">Alamat vendor belum diinput!</div>
                    </div>
                    <div class="form-group" id="grAlamat2">
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="alamat_2_vendor" name="alamat_2_vendor" placeholder="Alamat Vendor">
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaKontak">
                      <label class="form-label">Nama Kontak</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="nama_kontak" name="nama_kontak" placeholder="Nama Kontak">
                      <div class="invalid-feedback" id="fbNamaKontak">Nama kontak belum diinput!</div>
                    </div>
                    <div class="form-group" id="grTelpKontak">
                      <label class="form-label">Telp. Kontak</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="telp_kontak" name="telp_kontak" placeholder="Telp. Kontak">
                      <div class="invalid-feedback" id="fbTelpKontak">Nomor telepon kontak belum diinput!</div>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanVendor" class="btn btn-primary btn-block" name="" >Simpan data vendor</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content_header.$content_1.$content_footer.$content_dialogAddBahan;
  }

}
