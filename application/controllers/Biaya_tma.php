<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Biaya_tma extends CI_Controller{

  public function __construct(){
    parent:: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("kelompoktani_model");
    $this->load->model("biayatma_model");
    $this->load->model("transaksitma_model");
    $this->load->model("transaksi_model");
    $this->load->model("bahan_model");
    $this->load->model("dokumen_model");
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $this->load->helper('file');
    $this->simpg_address_live = "http://simpgbuma.ptp7.com/index.php/api_buma/";
    $this->simpg_address_local = "http://localhost/simpg/index.php/api_bcn/";
    $this->server_env = "LOCAL";
  }

  public function index(){
    $data['pageTitle'] = "Pengajuan Biaya TMA";
    $data['content'] = $this->loadContent();
    $data['script'] = $this->loadScript();
    $this->load->view('main_view', $data);
  }

  function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Biaya_tma.js").'");';
  }

  function buatPbtma(){
    //$postData = $this->input->post("dataPost");
    //$jsonData = json_decode($postData);
    $postData = ($this->session->userdata("proses_spta"));
    $tipe_dokumen = $this->input->post("tipe_dokumen");
    $catatan = $this->input->post("catatan");
    $tahun_giling = $this->input->post("tahun_giling");
    $data_kelompok_post = json_decode($this->input->post("data_kelompok"));
    //---------- Buat dokumen PBTMA baru -----------
    $id_pbtma = $this->dokumen_model->simpan($tipe_dokumen, $catatan);
    //----------------------------------------------
    /*
    $this->id_bahan = $post["id_bahan"];
    $this->id_aktivitas = $post["id_aktivitas"];
    $this->id_kelompoktani = $post["id_kelompoktani"];
    $this->id_vendor = $post["id_vendor"];
    $this->kode_transaksi = $post["kode_transaksi"];
    $this->no_transaksi = $post["no_transaksi"];
    $this->kuanta = $post["kuanta_bahan"];
    $this->rupiah = $post["rupiah_bahan"];
    $this->catatan = strtoupper($post["catatan"]);
    $this->tahun_giling = $post["tahun_giling"];
    $no_transaksi = "TR"."-".$arrayPermintaanPerawatan[0]->id_kelompok."-".$arrayPermintaanPerawatan[0]->tahun_giling."-".date("YmdHis");
    */
    // INPUT ke Tabel Transaksi ================================================
    $no_transaksi = "TR-TMA-".$data_kelompok_post[0]->tahun_giling."-".date("YmdHis");
    $this->db->trans_begin();
    foreach ($data_kelompok_post as $value) {
      $id_kelompok = json_decode($this->kelompoktani_model->getKelompokByKodeBlok($value->kode_blok))->id_kelompok;
      $data_transaksi = [
        "id_bahan" => 0,
        "id_aktivitas" => $value->id_wilayah,
        "id_kelompoktani" => $id_kelompok,
        "id_vendor" => 0,
        "kode_transaksi" => 2,
        "no_transaksi" => $no_transaksi,
        "kuanta_bahan" => $value->netto,
        "rupiah_bahan" => $value->jml_biaya,
        "catatan" => $catatan,
        "tahun_giling" => $value->tahun_giling
      ];
      $this->transaksi_model->simpan($data_transaksi);
      $this->transaksi_model->updateIdPbtma($id_pbtma, $no_transaksi);
    }
    if($this->db->trans_status()){
      $this->db->trans_commit();
      //==========================================================================
      // Kalau berhasil simpan data transaksi, baru update simtr_status di simpg
      $db_server = "";
      if($this->server_env == "LOCAL"){
        $db_server = $this->simpg_address_local;
      } else {
        $db_server = $this->simpg_address_live;
      }
      $data_to_post = array(
        "array_data" => $postData,
        "id_dokumen" => $id_pbtma
      );
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $db_server."setPbtma",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($data_to_post),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => "SIMTR"
      ));
      $response = curl_exec($curl);
      $error = curl_error($curl);
      //$response = json_decode($response);
      //-------------- Buat record di tabel transaksitma ------------------------
      // Sementara ini nggak perlu bro! kita kan nyimpen di table transaksi
      /*
      if (json_decode($response) == "SUCCESS"){
        $jml_netto = $this->session->userdata("nilai_pbtma")["jml_netto"];
        $jml_biaya = $this->session->userdata("nilai_pbtma")["jml_biaya"];
        $id_afd = $this->session->userdata("afd");
        $request = array("id_pbtma"=>$id_pbtma, "jml_netto"=>$jml_netto, "jml_biaya"=>$jml_biaya,
          "afd"=>$id_afd);
        $this->transaksitma_model->simpan($request);
      }
      */
      //-------------------------------------------------------------------------
      echo $response;
      curl_close($curl);
    }
  }

  function setSptaUtkProses($dataSpta){
    $this->session->set_userdata("proses_spta", $dataSpta);
  }

  function getSptaUtkProses(){
    echo $this->session->userdata("proses_spta");
  }

  function setNilaiPbtma($dataPbtma){
    $this->session->set_userdata("nilai_pbtma", $dataPbtma);
  }

  function getNilaiPbtma(){
    echo $this->session->userdata("nilai_pbtma");
  }

  function getCurl($request){
    $db_server = $request["db_server"];
    $url = str_replace(" ", "", $request["url"]);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $db_server.$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return $response; // output as json encoded
  }

  function getApiDataTimbangPeriodeGroup(){
    $tgl_timbang_awal = $this->input->get("tgl_timbang_awal");
    $tgl_timbang_akhir = $this->input->get("tgl_timbang_akhir");
    $tahun_giling = $this->input->get("tahun_giling");
    $id_afd = $this->session->userdata("afd");
    $db_server = "";
    if($this->server_env == "LOCAL"){
      $db_server = $this->simpg_address_local;
    } else {
      $db_server = $this->simpg_address_live;
    }
    $request = array("db_server"=>$db_server,
    "url"=>"getDataTimbangPeriodeGroup?tgl_timbang_awal=".$tgl_timbang_awal.
      "&tgl_timbang_akhir=".$tgl_timbang_akhir."&afd=".$id_afd."&tahun_giling=".$tahun_giling);
    $response = json_decode($this->getCurl($request));
    //var_dump($response);
    $dataResponse = [];
    if (!is_null($response)){
      //DATA PER SPTA ---------------
      $req_spta = array("db_server"=>$db_server, "url"=>"getDataTimbangPerSpta?tgl_timbang_awal="
        .$tgl_timbang_awal."&tgl_timbang_akhir=".$tgl_timbang_akhir."&afd=".$id_afd);
      $response_spta = $this->getCurl($req_spta);
      $this->setSptaUtkProses($response_spta);
      //====================================
      $jml_tebu = 0;
      $jml_biaya = 0;
      for($i = 0; $i < sizeof($response); $i++){
        $dataKelompok = json_decode($this->kelompoktani_model->getKelompokByKodeBlok($response[$i]->kode_blok));
        $dataBiayaTma = json_decode($this->biayatma_model->getBiayaTmaByIdWilayah($dataKelompok->id_wilayah));
        $dataElement = [
          "kode_blok" => $dataKelompok->kode_blok,
          "no_kontrak" => $dataKelompok->no_kontrak,
          "id_wilayah" => $dataKelompok->id_wilayah,
          "nama_wilayah" => $dataKelompok->nama_wilayah,
          "nama_kelompok" => $dataKelompok->nama_kelompok,
          "netto" => $response[$i]->netto,
          "tgl_timbang" => $response[$i]->tgl_timbang,
          "biaya" => (is_null($dataBiayaTma)) ? null : $dataBiayaTma->biaya,
          "jml_biaya" => (is_null($dataBiayaTma)) ? null : ($dataBiayaTma->biaya)*($response[$i]->netto)/1000,
          "tahun_giling" => $dataKelompok->tahun_giling
        ];
        array_push($dataResponse, $dataElement);
        $jml_tebu += $response[$i]->netto;
        $jml_biaya += (is_null($dataBiayaTma)) ? 0 : ($dataBiayaTma->biaya)*($response[$i]->netto)/1000;
      }
      $data_pbtma = array("jml_netto"=>$jml_tebu, "jml_biaya"=>$jml_biaya);
      $this->setNilaiPbtma($data_pbtma);
      ///var_dump($data_pbtma);
    }
    echo(json_encode($dataResponse));
  }

  public function loadContent(){
    $id_afd = $this->session->userdata("afd");
    $container =
    '
    <script>var id_afd = '.$id_afd.';</script>
    <div class="page">
      <div class="row">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="table-responsive col-12">
                <div class="alert alert-danger">Perhatian! <br>Data berikut berdasarkan data dari SIMPG, peralihan tanggal timbang dilakukan otomatis setiap jam  <b>06.00</b>
                  <br><b>Perhatikan tanggal tebu masuk yang dipilih!</b>
                </div>
                <table id="tblTebuMasukSkrg" class="table card-table table-vcenter text-nowrap datatable table-sm compact" style="width: 100%;">
                  <thead>
                    <tr>
                      <th class="w-1">No.</th>
                      <th>Kode Blok</th>
                      <th>No. Kontrak</th>
                      <th>Nama Kelompok</th>
                      <th>Wilayah</th>
                      <th>Netto</th>
                      <th>Tgl. Timbang</th>
                      <th>Biaya TMA</th>
                      <th>Jml. Biaya</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot class="bg-gray">
                    <tr>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th><font color="white">Total Tebu</font></th>
                      <th></th>
                      <th></th>
                      <th><font color="white">Total Biaya</font></th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div class="col-12">
                <div class="text-right">
                    <button id="btnBuatPBTMA" type="button" style="margin-right: 30px; width: 200px;" class="btn btn-outline-primary">Buat Pengajuan Biaya TMA</button>
                </div>
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
