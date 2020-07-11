<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dailyari_model extends CI_Model{

  public function getDataDaily($tglTimbang){
    //$query = "call get_rend_cs(?)";
    //return json_encode($this->db->query($query, array($tglTimbang))->result());
    $request = array("db_server"=>"http://simpgbuma.ptpn7.com/index.php/api_bcn/",
    "url"=>"getDataDaily?tglTimbang=".$tglTimbang);
    return ($this->getCurl($request));
  }

  public function getLaporanAri($tglTimbang){
    //$query = "call laporan_ari(?)";
    //return json_encode($this->db->query($query, array($tglTimbang))->result());
    $request = array("db_server"=>"http://simpgbuma.ptpn7.com/index.php/api_bcn/",
    "url"=>"getLaporanAri?tglTimbang=".$tglTimbang);
    return ($this->getCurl($request));

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

}
