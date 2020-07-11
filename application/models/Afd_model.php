<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Afd_model extends CI_Model {

  private $_table = "tbl_afd";
  public $id_afd;
  public $id_subbagian;
  public $id_user;
  public $nama_afd;

  public function getAfdByIdUser($userid){
    return $this->db->from($this->_table)->where("id_user",$userid)->get()->row();
  }

}
