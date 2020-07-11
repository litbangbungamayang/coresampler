<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rdkk_view extends CI_Controller{
  public function __construct(){
    parent:: __construct();
    $this->load->model('kelompoktani_model');
    $this->load->model('wilayah_model');
    $this->load->model('petani_model');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
  }

  public function index(){
    if ($this->session->userdata('id_user') == false){
      redirect('login');
    } else {
      $kelompoktani = $this->kelompoktani_model;
      $dataKelompok = json_decode($kelompoktani->getKelompokById());
      $data['pageTitle'] = "";
      $data['content'] = $this->loadContent($dataKelompok);
      $this->load->view('main_view', $data);
    }
  }

  public function loadContent($dataKelompok){
    $wilayah = $this->wilayah_model;
    $petani = $this->petani_model;
    $kecamatan = json_decode($wilayah->getKecByDesa($dataKelompok->id_wilayah))[0]->nama_wilayah;
    $kabupaten = json_decode($wilayah->getNamaKabupatenByIdDesa($dataKelompok->id_wilayah))[0]->nama_wilayah;
    $kategori = "";
    switch ($dataKelompok->kategori){
      case 1 :
        $kategori = "PC";
        break;
      case 2 :
        $kategori = "RT 1";
        break;
      case 3 :
        $kategori = "RT 2";
        break;
      case 4 :
        $kategori = "RT 3";
        break;
    }
    $arrayPetani = $petani->getPetaniByIdKelompok($dataKelompok->id_kelompok);
    $arrayPetani = json_decode($arrayPetani);
    $tPetani = "";
    foreach($arrayPetani as $petani){
      $tPetani .= '<tr><td>'.$petani->nama_petani.'</td><td class="text-right">'.$petani->luas.' Ha</td></tr>';
    }
    $container =
    '
      <div class="page">
        <div class="container">
          <div class="card">
            <div class="card-header">
              <div class="card-options">
                <a href="rdkk_all" class="btn btn-primary" onclick="" style="margin-right: 10px;"><i class="fe fe-corner-down-left"></i> Kembali </a>
                <a href="#" class="btn btn-primary" onclick="javascript:window.print();"><i class="fe fe-printer"></i> Cetak </a>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-6">
                  <h3>'.$dataKelompok->nama_kelompok.' / '.$dataKelompok->no_kontrak.'</h3>
                  <p>
                    '.$dataKelompok->no_ktp.'<br>
                    <b>DESA '.$dataKelompok->nama_wilayah.' '.$kecamatan.' '.$kabupaten.'</b><br>
                    '.$dataKelompok->nama_varietas.'<br>
                    <b>'.$dataKelompok->mt.' / '.$kategori.'</b><br>
                    '.$dataKelompok->luas.' Ha<br>
                  </p>
                </div>
                <div class="col-6 text-right">
                  <img src="data:image/jpg;base64,'.$dataKelompok->scan_ktp.'" height="150px"/>
                </div>
              </div>
              <div class="row">
                <div class="table-responsive push">
                  <table class="table table-bordered col-6" >
                    <tr>
                      <th class="text-center">Nama Petani</th><th class="text-center">Luas Area</th>
                    </tr>
                  '.$tPetani.'
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
