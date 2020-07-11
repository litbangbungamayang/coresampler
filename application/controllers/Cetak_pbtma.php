<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cetak_pbtma extends CI_Controller{
  public function __construct(){
    parent :: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("transaksi_model");
    $this->load->model("user_model");
    $this->load->library('ciqrcode');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
  }

  public function index(){
    date_default_timezone_set('Asia/Jakarta');
    $dataTransaksi = json_decode($this->transaksi_model->detailPbtma());
    $data['pageTitle'] = "";
    $data['content'] = $this->loadContent($dataTransaksi);
    $data['script'] = $this->loadScript();
    $this->load->view('main_view', $data);
    //print_r($dataTransaksi[0]->id_pbp);
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/List_biaya_tma.js").'");';
  }

  public function generateQr($data){
    $params['data'] = $data;
    $params['level'] = 'H'; //H=High
    $params['size'] = 1;
    ob_start();
    $this->ciqrcode->generate($params);
    $qrcode = ob_get_contents();
    ob_end_clean();
    return $qrcode;
  }

  public function loadContent($dataTransaksi){
    $qrAsisten = '';
    $qrAskep = '';
    $opsiCetak = '';
    if(!is_null($dataTransaksi[0]->tgl_validasi_bagian)){
      $dataQr = site_url().'/Verifikasi?id_dokumen='.$dataTransaksi[0]->id_pbtma.'&tgl_validasi_bagian='.$dataTransaksi[0]->tgl_validasi_bagian;
      $qrAsisten = $this->generateQr($dataQr);
      $qrAsisten = '<img src="data:image/png;base64,'.base64_encode($qrAsisten).'" />';
    }
    if(!is_null($dataTransaksi[0]->tgl_validasi_kasubbag)){
      $dataQr = site_url().'/Verifikasi?id_dokumen='.$dataTransaksi[0]->id_pbtma.'&tgl_validasi_kasubbag='.$dataTransaksi[0]->tgl_validasi_kasubbag;
      $qrAskep = $this->generateQr($dataQr);
      $qrAskep = '<img src="data:image/png;base64,'.base64_encode($qrAskep).'" />';
    }

    if(!is_null($dataTransaksi[0]->tgl_validasi_bagian) && !is_null($dataTransaksi[0]->tgl_validasi_kasubbag)){
      $opsiCetak = '<a href="#" class="btn btn-primary" onclick="cetak();"><i class="fe fe-printer"></i> Cetak </a>';
    }
    $dataDesa = json_decode($this->transaksi_model->getDesaByIdPbtma($dataTransaksi[0]->id_pbtma)); //BISA DIPAKAI JUGA UNTUK PBP
    $nama_asisten = json_decode($this->user_model->getNamaAsistenByAfd($dataTransaksi[0]->id_afd))->nama_user;
    $nama_askep = json_decode($this->user_model->getNamaAskepByAfd($dataTransaksi[0]->id_afd))->nama_user;
    $tblContent = '';
    $nomor = 1;
    $totalBiaya = 0;
    $totalNetto = 0;
    $subBiaya = 0;
    $subNetto = 0;
    foreach ($dataDesa as $desa) {
      foreach($dataTransaksi as $transaksi){
        if($transaksi->nama_wilayah == $desa->nama_wilayah){
          $tblContent .=
          "<tr>
            <td class='text-center'>$nomor</td>
            <td>$transaksi->nama_kelompok</td>
            <td class='text-center'>$transaksi->no_kontrak</td>
            <td class='text-right'>$transaksi->luas</td>
            <td class='text-center'>$transaksi->tgl_transaksi</td>
            <td class='text-right'>".number_format(($transaksi->netto)/1000,2,".",",")."</td>
            <td class='text-right'>Rp".number_format($transaksi->biaya,0,".",",")."</td>
          </tr>";
          $subBiaya = $subBiaya + $transaksi->biaya;
          $subNetto = $subNetto + $transaksi->netto;
          $nomor ++;
        }
      }
      $totalBiaya = $totalBiaya + $subBiaya;
      $totalNetto = $totalNetto + $subNetto;
      $tblContent .=
      "<tr class='bg-gray-lighter text-black font-weight-bold'>
        <td></td>
        <td></td>
        <td>SUB TOTAL</td>
        <td colspan='2' >$desa->nama_wilayah</td>
        <td class='text-right'>".number_format($subNetto/1000,2,".",",")."</td>
        <td class='text-right'>Rp".number_format($subBiaya,0,".",",")."</td>
      </tr>";
      $subBiaya = 0;
      $subNetto = 0;
    }
    $tblContent .=
    "<tr class='bg-gray-light text-black font-weight-bold'>
      <td class='text-center' colspan='5'>TOTAL PENGAJUAN</td>
      <td class='text-right'>".number_format($totalNetto/1000,2,".",",")."</td>
      <td class='text-right'>Rp".number_format($totalBiaya,0,".",",")."</td>
    </tr>";

    $container =
    '
      <div class="page">
        <div class="container">
          <div class="card">
            <div class="card-header">
              <div class="card-options">
                <a href="List_biaya_tma" class="btn btn-primary" onclick="" style="margin-right: 10px;"><i class="fe fe-corner-down-left"></i> Kembali </a>
                '.$opsiCetak.'
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-6">
                  <p class="h3">Rekapitulasi Biaya TMA</p>
                  <p class="h5">Periode '.$dataTransaksi[0]->catatan.'</p>
                  <p>
                    No. Dokumen  <b>'.$dataTransaksi[0]->no_dokumen.'</b><br>
                    Tgl. Dokumen <b>'.date_format(date_create($dataTransaksi[0]->tgl_buat), "d-m-Y H:i:s").'</b>
                  </p>
                </div>
                <div class="col-6 text-right">
                  <div class="row justify-content-end" style="height: 120px">
                    <div class="col-4 text-center border pb-4">Dibuat oleh<br>'.$nama_asisten.'<br>
                      '.$qrAsisten.'
                    </div>
                    <div class="col-4 text-center border pb-4" style="margin-right: 10px">Disetujui oleh<br>'.$nama_askep.'<br>
                      '.$qrAskep.'
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-responsive push">
                <table class="table table-bordered table-sm compact">
                  <tr>
                    <th class="text-center" style="width: 1%">No</th>
                    <th>Kelompok</th>
                    <th class="text-center">No. Kontrak</th>
                    <th class="text-right">Luas</th>
                    <th class="text-center">Tgl. Transaksi</th>
                    <th class="text-right">Ton Tebu</th>
                    <th class="text-right">Biaya TMA</th>
                  </tr>
                  '.$tblContent.'
                </table>
              </div>
              <div class="row px-3">
                <small>'.date("dmY-His").'</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    ';
    return $container;
  }
}
