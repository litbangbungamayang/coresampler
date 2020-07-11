<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dosis_model extends CI_Model{

  private $_table = "tbl_simtr_dosis";
  public $id_aktivitas;
  public $id_bahan;
  public $dosis;

  public function simpan(){
    $post = $this->input->post();
    $this->id_aktivitas = $post["id_aktivitas"];
    $this->id_bahan = $post["id_bahan"];
    $this->dosis = $post["dosis"];
  }

}
