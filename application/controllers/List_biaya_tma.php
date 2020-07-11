<?php defined('BASEPATH') OR exit('No direct script access allowed');

class List_biaya_tma extends CI_Controller{

  public function __construct(){
    parent:: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("kelompoktani_model");
    $this->load->model("transaksitma_model");
    $this->load->model("transaksi_model");
    $this->load->model("dokumen_model");
    $this->load->model("global_scope");
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $this->load->helper('file');
  }

  public function index(){
    if ($this->session->userdata('id_user') == false){
      redirect('login');
    } else {
      $data['pageTitle'] = "Penelusuran Pengajuan Biaya TMA";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/List_biaya_tma.js").'");';
  }

  public function getCurl($request){
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

  public function getAllPbtma(){
    if($this->session->userdata("jabatan") == "Kepala Sub Bagian"){
      $afd = "";
    } else {
      $afd = $this->session->userdata("afd");
    }
    $request = array(
      "afd"=>$afd,
      "tahun_giling"=>$this->input->get("tahun_giling"),
      "priv_level"=>$this->session->userdata("jabatan")
    );
    $array_pbtma =  json_decode($this->transaksi_model->getAllPbtma($request));
    $array_dataTable = [];
    //================ Crosscheck integritas data SIMPG =========================
    $simpg_address = json_decode($this->global_scope->getSimpgEnv());
    $url = "getGroupSptaByIdPbtma?id_pbtma=";
    foreach ($array_pbtma as $pbtma) {
      $request = [
        "db_server" => $simpg_address,
        "url" => $url.$pbtma->id_dokumen
      ];
      $data_simpg = json_decode($this->getCurl($request));
      if(sizeof($data_simpg) > 0){
        $jml_netto = (int)$data_simpg[0]->netto;
        if($jml_netto > 0){
          if ($jml_netto = $pbtma->netto){
            $pbtma->valid = 1;
          } else {
            $pbtma->valid = 0;
          }
          array_push($array_dataTable, $pbtma);
        }
      }
    }
    echo json_encode($array_dataTable);
    //==========================================================================
  }

  public function validasiDokumen(){
    $this->dokumen_model->validasi();
  }

  public function validasiAskep(){
    $this->dokumen_model->validasiAskep();
  }

  public function loadContent(){
    $container =
    '
    <div class="page">
      <div class="row">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="table-responsive col-12">
                <table id="tblListBiayaTma" class="table table-card table-striped table-sm text-nowrap">
                  <thead>
                    <tr>
                      <th class="w-1">No.</th>
                      <th>No. Dokumen</th>
                      <th>Tgl. Dibuat</th>
                      <th>Jml. Tebu</th>
                      <th>Total Biaya</th>
                      <th>Periode Transaksi</th>
                      <th>Status</th>
                      <th class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot class="bg-gray">
                    <tr>
                      <th class="w-1"></th>
                      <th><font color="white" size="3">Total</font></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                    </tr>
                  </tfoot>
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



?>
