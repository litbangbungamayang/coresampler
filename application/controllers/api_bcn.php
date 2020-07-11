<?php
//if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
$request_headers        = apache_request_headers();
print_r ($request_headers);
$http_origin            = $request_headers['Referer'];
print_r ("origin: ".$http_origin);
$allowed_http_origins   = array(
                            "http://simtr.bcn.web.id",
                            "http://localhost"
                          );
if (in_array($http_origin, $allowed_http_origins)){
    header("Access-Control-Allow-Origin: " . $http_origin);
};
*/

class Api_bcn extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTimbang(){
    	$this->load->model('api_bcn_model');
    	$kode_blok = $this->input->get("kode_blok");
    	$tgl_timbang = $this->input->get("tgl_timbang");
      $request = array('kode_blok' => $kode_blok, 'tgl_timbang' => $tgl_timbang);
    	echo $this->api_bcn_model->getDataTimbang($request);
    }

    public function getDataTimbangPeriode(){
      $this->load->model("api_bcn_model");
      $tgl_timbang_awal = $this->input->get("tgl_timbang_awal");
      $tgl_timbang_akhir = $this->input->get("tgl_timbang_akhir");
      $request = array("tgl_timbang_awal" => $tgl_timbang_awal, "tgl_timbang_akhir" => $tgl_timbang_akhir);
      echo $this->api_bcn_model->getDataTimbangPeriode($request);
    }

    public function getDataTimbangPeriodeGroup(){
      $this->load->model("api_bcn_model");
      $tgl_timbang_awal = $this->input->get("tgl_timbang_awal");
      $tgl_timbang_akhir = $this->input->get("tgl_timbang_akhir");
      $afd = $this->input->get("afd");
      $tahun_giling = $this->input->get("tahun_giling");
      $request = array("tgl_timbang_awal" => $tgl_timbang_awal,
        "tgl_timbang_akhir" => $tgl_timbang_akhir, "afd" => $afd, "tahun_giling"=>$tahun_giling);
      echo $this->api_bcn_model->getDataTimbangPeriodeGroup($request);
    }

    public function getDataTimbangPerSpta(){
      $this->load->model("api_bcn_model");
      $tgl_timbang_awal = $this->input->get("tgl_timbang_awal");
      $tgl_timbang_akhir = $this->input->get("tgl_timbang_akhir");
      $afd = $this->input->get("afd");
      $request = array("tgl_timbang_awal" => $tgl_timbang_awal, "tgl_timbang_akhir" => $tgl_timbang_akhir, "afd" => $afd);
      echo $this->api_bcn_model->getDataTimbangPerSpta($request);
    }

    public function updateSimpgFlag(){
      $this->load->model("api_bcn_model");
      $tgl_timbang = $this->input->get("tgl_timbang");
      $kode_blok = $this->input->get("kode_blok");
      $request = array("tgl_timbang" => $tgl_timbang, "kode_blok" => $kode_blok);
      echo $this->api_bcn_model->updateSimpgFlag($request);
    }

    public function setPbtma(){
      $this->load->model("api_bcn_model");
      $id_dokumen = $this->input->post("id_dokumen");
      $data_pbtma = json_decode($this->input->post("array_data"));
      $this->db->trans_begin();
      foreach ($data_pbtma as $spta){
        $request = array("id_pbtma"=>$id_dokumen, "id"=>$spta->id);
        $this->api_bcn_model->updateIdPbtma($request);
      }
      if ($this->db->trans_status()){
        $this->db->trans_commit();
        echo json_encode("SUCCESS");
      } else {
        echo json_encode("FAILED");
      }
    }

    public function getAllPetakKebunByKepemilikan(){
      $this->load->model("api_bcn_model");
      $kepemilikan = "";
      $post_kepemilikan = $this->input->get("kepemilikan");
      $tahun_giling = $this->input->get("tahun_giling");
      switch($post_kepemilikan){
        case "ts":
          $kepemilikan = "ts-hg";
          break;
        case "tr":
          $kepemilikan = "TR-KR";
          break;
        case "tsi":
          $kepemilikan = "ts-ip";
          break;
      }
      $request = array("kepemilikan" => $kepemilikan, "tahun_giling" => $tahun_giling);
      echo $this->api_bcn_model->getAllPetakKebunByKepemilikan($request);
    }

    public function getAllSptaByAfd(){
      $this->load->model("api_bcn_model");
      $id_afd = $this->input->get("id_afd");
      $request = array("id_afd" => $id_afd);
      echo $this->api_bcn_model->getAllSptaByAfd($request);
    }

}
