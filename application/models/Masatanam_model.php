<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Masatanam_model extends CI_Model{

  private $_table = "tbl_simtr_masatanam";
  public $masa_tanam;

  public function getAll(){
    return $this->db->get($this->_table)->result();
  }
}
