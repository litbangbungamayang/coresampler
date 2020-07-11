<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Geocode_model extends CI_Model {

  private $_table = "tbl_simtr_geocode";
  private $id_geocode;
  private $id_petani;
  private $trackpoint;

  public function tambah(){
    $post = $this->input->post();
    $lat = $post["lat"];
    $long = $post["long"];
    $this->id_petani = $post["id_petani"];
    $this->trackpoint = "POINT(".$lat." ".$long.")";
    $this->db->insert($this->_table, $this);
  }

  public function simpan($lat, $long, $idPetani){
    $this->id_petani = $idPetani;
    $this->trackpoint = "ST_GeomFromText('POINT(".$lat." ".$long.")'";
    $this->db->query("INSERT INTO tbl_simtr_geocode (id_petani, trackpoint) VALUES ($idPetani, ST_GeomFromText('POINT($lat $long)'))");
  }

}
