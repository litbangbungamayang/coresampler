<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_AU58 extends CI_Controller{
  public function __construct(){
    parent:: __construct();
    //if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model('kelompoktani_model');
    $this->load->model('transaksi_model');
    $this->load->model('user_model');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $this->load->library('ciqrcode');
  }

  public function index(){
    date_default_timezone_set('Asia/Jakarta');
    $dataTransaksi =  json_decode($this->transaksi_model->getAu58ByNoTransaksi());
    $data['pageTitle'] = "";
    $data['content'] = $this->loadContent($dataTransaksi);
    $this->load->view('main_view', $data);
  }

  public function getAu58ByNoTransaksi(){
    echo $this->transaksi_model->getAu58ByNoTransaksi();
  }

  public function loadContent($dataTransaksi){
    $nama_asisten = json_decode($this->user_model->getNamaAsistenByAfd($dataTransaksi[0]->id_afd))->nama_user;
    $validasiQr = '';
    $opsiCetak = '';
    if(!is_null($dataTransaksi[0]->tgl_validasi_bagian)){
      $validasi = true;
      $params['data'] = site_url().'/Verifikasi?id_dokumen='.$dataTransaksi[0]->id_au58.'&tgl_validasi_bagian='.$dataTransaksi[0]->tgl_validasi_bagian; //data yang akan di jadikan QR CODE
      $params['level'] = 'H'; //H=High
      $params['size'] = 1;
      ob_start();
      $this->ciqrcode->generate($params);
      $qrcode = ob_get_contents();
      ob_end_clean();
      $validasiQr = '<img src="data:image/png;base64,'.base64_encode($qrcode).'" />';
      $opsiCetak = '<a href="#" class="btn btn-primary" onclick="javascript:window.print();"><i class="fe fe-printer"></i> Cetak </a>';
    }

    $contentBahan = "";
    $nomor = 1;
    foreach($dataTransaksi as $bahan){
      $contentBahan .= '<tr><td style="text-align: center; border-width: 1px 0px 1px 0px;">'.$nomor.'</td><td>'.$bahan->jenis_bahan.' '.$bahan->nama_bahan.'</td><td>'.$bahan->satuan.'</td><td style="text-align: right; border-width: 0px 0px 1px 0px;">'.number_format($bahan->kuanta,2,".",",").'</td></tr>';
      $nomor ++;
    }
    $container =
    '
      <style>
          @media screen
        {
          .noPrint{}
          .noScreen{display:none;}
        }

          @media print
        {
          .noPrint{display:none;}
          .noScreen{}
        }
      </style>
      <div class="page">
        <div class="container">
          <div class="card">
            <div class="card-header">
              <div class="card-options">
                <a href="javascript:history.back()" class="btn btn-primary" onclick="" style="margin-right: 10px;"><i class="fe fe-corner-down-left"></i> Kembali </a>
                '.$opsiCetak.'
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                  <h5>PT. BUMA CIMA NUSANTARA<br>
                  PABRIK GULA BUNGAMAYANG</h5>
                </div>
                <div class="col-6 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                  <h4>BON PERMINTAAN DAN <br>
                  PENGELUARAN BARANG</h4>
                </div>
                <div class="col-3 text-left" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid; padding-top: 10px">
                  No. '.$dataTransaksi[0]->no_transaksi.'<br>
                  Tgl. '.$dataTransaksi[0]->tgl_transaksi.'
                </div>
              </div>
              <style>
                .table-bordered td, .table-bordered th{
                  border-color: black !important;
                  color: black;
                }
              </style>
              <div class="row" style="border-width: 0px 1px 0px 1px; border-color: black; border-style: solid;">
                <table class="table table-bordered">
                  <thead>
                    <tr class="text-center">
                      <th style="width: 20px; border-width: 1px 0px 1px 0px;">No.</th>
                      <th>Nama Barang</th>
                      <th>Satuan</th>
                      <th style="border-width: 0px 0px 1px 0px;">Banyaknya</th>
                    </tr>
                  </thead>
                  <tbody>
                    '.$contentBahan.'
                  </tbody>
                </table>
              </div>
              <div class="row">
                <div class="col-12 text-left" style="border-width: 0px 1px 1px 1px; border-color: black; border-style: solid;">
                  Barang untuk dikirim kepada Kelompok Tani : <b>'.$dataTransaksi[0]->nama_kelompok.'</b>; No. Kontrak : '.$dataTransaksi[0]->no_kontrak.'; Desa '.$dataTransaksi[0]->nama_wilayah.'; Luas : '.number_format($dataTransaksi[0]->luas,2,".",",").' Ha
                </div>
              </div>
              <div class="row">
                <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; height: 120px">
                  Diminta oleh<br>'.$nama_asisten.'<br>'.$validasiQr.'
                </div>
                <div class="col-3 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid;">
                  Diterima oleh
                </div>
                <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                  Disetujui oleh
                </div>
                <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                  Dikeluarkan oleh
                </div>
              </div>
              <div class="row">
                '.date("dmY-His").'
              </div>
              <br>
              <div class="noScreen">
                <div class="row">
                  <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                    <h5>PT. BUMA CIMA NUSANTARA<br>
                    PABRIK GULA BUNGAMAYANG</h5>
                  </div>
                  <div class="col-6 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                    <h4>BON PERMINTAAN DAN <br>
                    PENGELUARAN BARANG</h4>
                  </div>
                  <div class="col-3 text-left" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid; padding-top: 10px">
                    No. '.$dataTransaksi[0]->no_transaksi.'<br>
                    Tgl. '.$dataTransaksi[0]->tgl_transaksi.'
                  </div>
                </div>
                <style>
                  .table-bordered td, .table-bordered th{
                    border-color: black !important;
                    color: black;
                  }
                </style>
                <div class="row" style="border-width: 0px 1px 0px 1px; border-color: black; border-style: solid;">
                  <table class="table table-bordered">
                    <thead>
                      <tr class="text-center">
                        <th style="width: 20px">No.</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Banyaknya</th>
                      </tr>
                    </thead>
                    <tbody>
                      '.$contentBahan.'
                    </tbody>
                  </table>
                </div>
                <div class="row">
                  <div class="col-12 text-left" style="border-width: 0px 1px 1px 1px; border-color: black; border-style: solid;">
                    Barang untuk dikirim kepada Kelompok Tani : <b>'.$dataTransaksi[0]->nama_kelompok.'</b>; No. Kontrak : '.$dataTransaksi[0]->no_kontrak.'; Desa '.$dataTransaksi[0]->nama_wilayah.'; Luas : '.number_format($dataTransaksi[0]->luas,2,".",",").' Ha
                  </div>
                </div>
                <div class="row">
                  <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; height: 120px">
                    Diminta oleh<br>'.$nama_asisten.'<br>'.$validasiQr.'
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid;">
                    Diterima oleh
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                    Disetujui oleh
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                    Dikeluarkan oleh
                  </div>
                </div>
                <div class="row">
                  '.date("dmY-His").'
                </div>
              </div>
              <br>
              <div class="noScreen">
                <div class="row">
                  <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                    <h5>PT. BUMA CIMA NUSANTARA<br>
                    PABRIK GULA BUNGAMAYANG</h5>
                  </div>
                  <div class="col-6 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid; padding-top: 10px">
                    <h4>BON PERMINTAAN DAN <br>
                    PENGELUARAN BARANG</h4>
                  </div>
                  <div class="col-3 text-left" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid; padding-top: 10px">
                    No. '.$dataTransaksi[0]->no_transaksi.'<br>
                    Tgl. '.$dataTransaksi[0]->tgl_transaksi.'
                  </div>
                </div>
                <style>
                  .table-bordered td, .table-bordered th{
                    border-color: black !important;
                    color: black;
                  }
                </style>
                <div class="row" style="border-width: 0px 1px 0px 1px; border-color: black; border-style: solid;">
                  <table class="table table-bordered">
                    <thead>
                      <tr class="text-center">
                        <th style="width: 20px">No.</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Banyaknya</th>
                      </tr>
                    </thead>
                    <tbody>
                      '.$contentBahan.'
                    </tbody>
                  </table>
                </div>
                <div class="row">
                  <div class="col-12 text-left" style="border-width: 0px 1px 1px 1px; border-color: black; border-style: solid;">
                    Barang untuk dikirim kepada Kelompok Tani : <b>'.$dataTransaksi[0]->nama_kelompok.'</b>; No. Kontrak : '.$dataTransaksi[0]->no_kontrak.'; Desa '.$dataTransaksi[0]->nama_wilayah.'; Luas : '.number_format($dataTransaksi[0]->luas,2,".",",").' Ha
                  </div>
                </div>
                <div class="row">
                  <div class="col-3 text-center" style="border-width: 1px 0px 1px 1px; border-color: black; border-style: solid; height: 120px">
                    Diminta oleh<br>'.$nama_asisten.'<br>'.$validasiQr.'
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 1px; border-color: black; border-style: solid;">
                    Diterima oleh
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                    Disetujui oleh
                  </div>
                  <div class="col-3 text-center" style="border-width: 1px 1px 1px 0px; border-color: black; border-style: solid;">
                    Dikeluarkan oleh
                  </div>
                </div>
                <div class="row">
                  '.date("dmY-His").'
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
