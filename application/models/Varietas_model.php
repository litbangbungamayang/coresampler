<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Varietas_model extends CI_Model{

  private $_table = "tbl_varietas";
  public $id_varietas;
  public $nama_varietas;
  public $tipe_kemasakan;

  public function getAll(){
    return $this->db->get($this->_table)->result();
  }
}
