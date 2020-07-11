<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor_model extends CI_Model{

  private $_table = "tbl_simtr_vendor";
  public $nama_vendor;
  public $npwp_vendor;
  public $alamat_vendor;
  public $alamat_2_vendor;
  public $nama_kontak;
  public $telp_kontak;

  public function getAllVendor(){
    return json_encode($this->db->query("select * from tbl_simtr_vendor")->result());
  }

  public function simpan(){
    $post = $this->input->post();
    $this->nama_vendor = strtoupper($post["nama_vendor"]);
    $this->npwp_vendor = $post["npwp_vendor"];
    $this->alamat_vendor = strtoupper($post["alamat_vendor"]);
    $this->alamat_2_vendor = strtoupper($post["alamat_2_vendor"]);
    $this->nama_kontak = strtoupper($post["nama_kontak"]);
    $this->telp_kontak = strtoupper($post["telp_kontak"]);
    $this->db->insert($this->_table, $this);
    return $this->db->insert_id();
  }

  public function edit(){
    $post = $this->input->post();
    $this->nama_vendor = strtoupper($post["nama_vendor"]);
    $this->npwp_vendor = $post["npwp_vendor"];
    $this->alamat_vendor = strtoupper($post["alamat_vendor"]);
    $this->alamat_2_vendor = strtoupper($post["alamat_2_vendor"]);
    $this->nama_kontak = strtoupper($post["nama_kontak"]);
    $this->telp_kontak = strtoupper($post["telp_kontak"]);
    return $this->db->where("id_vendor", $post["id_vendor"])->update($this->_table, $this);
  }

  public function getVendorById(){
    $id = $this->input->get("id_vendor");
    return json_encode($this->db->query("select * from tbl_simtr_vendor where id_vendor = ?", array($id))->row());
  }

  public function hapus($id_vendor = null){
    if (is_null($id_vendor)) $id_vendor = $this->input->post("id_vendor");
    return $this->db->delete($this->_table, array('id_vendor' => $id_vendor));
  }

}
