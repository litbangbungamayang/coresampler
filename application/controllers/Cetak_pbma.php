<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cetak_pbma extends CI_Controller{
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
    $dataTransaksi = json_decode($this->transaksi_model->detailPbma());
    $data['pageTitle'] = "";
    $data['content'] = $this->loadContent($dataTransaksi);
    $this->load->view('main_view', $data);
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
      $dataQr = site_url().'/Verifikasi?id_dokumen='.$dataTransaksi[0]->id_pbma.'&tgl_validasi_bagian='.$dataTransaksi[0]->tgl_validasi_bagian;
      $qrAsisten = $this->generateQr($dataQr);
      $qrAsisten = '<img src="data:image/png;base64,'.base64_encode($qrAsisten).'" />';
    }
    if(!is_null($dataTransaksi[0]->tgl_validasi_kasubbag)){
      $dataQr = site_url().'/Verifikasi?id_dokumen='.$dataTransaksi[0]->id_pbma.'&tgl_validasi_kasubbag='.$dataTransaksi[0]->tgl_validasi_kasubbag;
      $qrAskep = $this->generateQr($dataQr);
      $qrAskep = '<img src="data:image/png;base64,'.base64_encode($qrAskep).'" />';
    }

    if(!is_null($dataTransaksi[0]->tgl_validasi_bagian) && !is_null($dataTransaksi[0]->tgl_validasi_kasubbag)){
      $opsiCetak = '<a href="#" class="btn btn-primary" onclick="javascript:window.print();"><i class="fe fe-printer"></i> Cetak </a>';
    }
    $dataDesa = json_decode($this->transaksi_model->getDesaByIdPbma($dataTransaksi[0]->id_pbma));
    $nama_asisten = json_decode($this->user_model->getNamaAsistenByAfd($dataTransaksi[0]->id_afd))->nama_user;
    $nama_askep = json_decode($this->user_model->getNamaAskepByAfd($dataTransaksi[0]->id_afd))->nama_user;

    $tblContent = '';
    $nomor = 1;
    $subUrea = 0;
    $subTsp = 0;
    $subKcl = 0;
    $subMuat = 0;
    $subAngkut = 0;
    $subTotalBiaya = 0;
    $totalUrea = 0;
    $totalTsp = 0;
    $totalKcl = 0;
    $totalMuat = 0;
    $totalAngkut = 0;
    $totalBiaya = 0;
    foreach($dataDesa as $desa){
      foreach($dataTransaksi as $transaksi){
        if($transaksi->nama_wilayah == $desa->nama_wilayah){
          $tblContent .=
          "<tr>
            <td class='text-center'>$nomor</td>
            <td>$transaksi->nama_kelompok</td>
            <td class='text-center'>$transaksi->no_kontrak</td>
            <td class='text-right'>$transaksi->luas</td>
            <td class='text-center'>$transaksi->tgl_transaksi</td>
            <td class='text-right'>".number_format($transaksi->urea,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->tsp,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->kcl,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->jml,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->biaya_muat,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->biaya_angkut,0,".",",")."</td>
            <td class='text-right'>".number_format($transaksi->total_biaya,0,".",",")."</td>
          </tr>";
          $subUrea = $subUrea + $transaksi->urea;
          $subTsp = $subTsp + $transaksi->tsp;
          $subKcl = $subKcl + $transaksi->kcl;
          $subMuat = $subMuat + $transaksi->biaya_muat;
          $subAngkut = $subAngkut + $transaksi->biaya_angkut;
          $subTotalBiaya = $subTotalBiaya + $transaksi->total_biaya;
          $nomor ++;
        }
      }
      $totalUrea = $totalUrea + $subUrea;
      $totalTsp = $totalTsp + $subTsp;
      $totalKcl = $totalKcl + $subKcl;
      $totalMuat = $totalMuat + $subMuat;
      $totalAngkut = $totalAngkut + $subAngkut;
      $totalBiaya = $totalBiaya + $subTotalBiaya;
      $tblContent .=
      "<tr class='bg-gray-lighter text-black font-weight-bold'>
        <td class='text-center'></td>
        <td class='text-right'>SUB TOTAL</td>
        <td class='text-center'>$desa->nama_wilayah</td>
        <td class='text-right'></td>
        <td class='text-center'></td>
        <td class='text-right'>".number_format($subUrea,0,".",",")."</td>
        <td class='text-right'>".number_format($subTsp,0,".",",")."</td>
        <td class='text-right'>".number_format($subKcl,0,".",",")."</td>
        <td class='text-right'>".number_format($subUrea + $subTsp + $subKcl,0,".",",")."</td>
        <td class='text-right'>".number_format($subMuat,0,".",",")."</td>
        <td class='text-right'>".number_format($subAngkut,0,".",",")."</td>
        <td class='text-right'>".number_format($subTotalBiaya,0,".",",")."</td>
      </tr>";
      $subUrea = 0;
      $subTsp = 0;
      $subKcl = 0;
      $subMuat = 0;
      $subAngkut = 0;
      $subTotalBiaya = 0;
    }
    $tblContent .=
    "<tr class='bg-gray-light text-black font-weight-bold'>
      <td class='text-center'></td>
      <td class='text-center' colspan='4'>TOTAL PENGAJUAN</td>
      <td class='text-right'>".number_format($totalUrea,0,".",",")."</td>
      <td class='text-right'>".number_format($totalTsp,0,".",",")."</td>
      <td class='text-right'>".number_format($totalKcl,0,".",",")."</td>
      <td class='text-right'>".number_format($totalUrea + $totalTsp + $totalKcl,0,".",",")."</td>
      <td class='text-right'>".number_format($totalMuat,0,".",",")."</td>
      <td class='text-right'>".number_format($totalAngkut,0,".",",")."</td>
      <td class='text-right'>".number_format($totalBiaya,0,".",",")."</td>
    </tr>";
    $container =
    '
      <div class="page">
        <div class="container">
          <div class="card">
            <div class="card-header">
              <div class="card-options">
                <a href="List_pbma" class="btn btn-primary" onclick="" style="margin-right: 10px;"><i class="fe fe-corner-down-left"></i> Kembali </a>
                '.$opsiCetak.'
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-6">
                  <p class="h3">Rekapitulasi Biaya Pupuk</p>
                  <p class="h5">Periode '.$dataTransaksi[0]->catatan.'</p>
                  <p>
                    No. Dokumen  <b>'.$dataTransaksi[0]->no_dokumen.'</b><br>
                    Tgl. Dokumen <b>'.date_format(date_create($dataTransaksi[0]->tgl_buat), "d-m-Y H:i:s").'</b>
                  </p>
                </div>
                <div class="col-6">
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
                    <th class="text-right">Urea</th>
                    <th class="text-right">TSP</th>
                    <th class="text-right">KCL</th>
                    <th class="text-right">Jml. Pupuk</th>
                    <th class="text-right">Biaya Muat</th>
                    <th class="text-right">Biaya Angkut</th>
                    <th class="text-right">Total Biaya</th>
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
