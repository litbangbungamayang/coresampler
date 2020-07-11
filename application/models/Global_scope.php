<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Global_scope extends CI_Model{

  public $server_env = "LOCAL";

  public function getSimpgEnv(){
    if($this->server_env == "LOCAL"){
      return json_encode("http://localhost/simpg/index.php/api_bcn/", JSON_UNESCAPED_SLASHES, 1);
    } else {
      return json_encode("http://simpgbuma.ptpn7.com/index.php/api_buma/", JSON_UNESCAPED_SLASHES, 1);
    }
  }

  public function getServerEnv(){
    return $this->server_env;
  }

}

?>
