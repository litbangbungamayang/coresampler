<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Biayatma_model extends CI_Model{

  private $_table = "tbl_simtr_biayatma";
  public $tahun_giling;
  public $id_wilayah;
  public $biaya;

  public function simpan($post = null){
    if(is_null($post))$post = $this->input->post();
    $this->id_wilayah = $post["id_wilayah"];
    $this->tahun_giling = $post["tahun_giling"];
    $this->biaya = $post["biaya"];
    $this->db->insert($this->_table, $this);
    return $this->db->insert_id();
  }

  public function cekDuplikat($get = null){
    if(is_null($get))$get = $this->input->get();
    $tahun_giling = $get["tahun_giling"];
    $id_wilayah = $get["id_wilayah"];
    $query = "select * from tbl_simtr_biayatma where tahun_giling = ? and id_wilayah = ?";
    return json_encode($this->db->query($query, array($tahun_giling, $id_wilayah))->row());
  }

  public function getAllBiayaTma(){
    $tahun_giling = $this->input->get("tahun_giling");
    $query =
    "
    select
    	tma.id_biayatma,
    	tma.id_wilayah,
      left(tma.id_wilayah, 4) as id_kabupaten,
      concat('DESA ', wil.nama_wilayah, ' ', kec.nama_wilayah) as deskripsi,
      kab.nama_wilayah as kabupaten,
      tma.biaya,
      tma.tahun_giling
    from tbl_simtr_biayatma tma
    join tbl_simtr_wilayah wil on tma.id_wilayah = wil.id_wilayah
    join tbl_simtr_wilayah kec on left(kec.id_wilayah, 6) = left(tma.id_wilayah, 6)
    join tbl_simtr_wilayah kab on left(kab.id_wilayah, 4) = left(tma.id_wilayah, 4)
    where kab.level = 2 and kec.level = 3 and tma.tahun_giling like concat('%', ?, '%')
    ";
    return json_encode($this->db->query($query, array($tahun_giling))->result());
  }

  public function getBiayaTmaById(){
    $id_biaya = $this->input->get("id_biayatma");
    $query =
    "
    select
    	tma.id_biayatma,
    	tma.id_wilayah,
      kab.id_wilayah as id_kabupaten,
      concat('DESA ', wil.nama_wilayah, ' ', kec.nama_wilayah) as deskripsi,
      kab.nama_wilayah as kabupaten,
      tma.biaya,
      tma.tahun_giling
    from tbl_simtr_biayatma tma
    join tbl_simtr_wilayah wil on tma.id_wilayah = wil.id_wilayah
    join tbl_simtr_wilayah kec on left(kec.id_wilayah, 6) = left(tma.id_wilayah, 6)
    join tbl_simtr_wilayah kab on left(kab.id_wilayah, 4) = left(tma.id_wilayah, 4)
    where kab.level = 2 and kec.level = 3 and tma.id_biayatma = ?
    ";
    return json_encode($this->db->query($query, array($id_biaya))->row());
  }

  public function getBiayaTmaByIdWilayah($id_wilayah = null){
    if(is_null($id_wilayah))$id_wilayah = $this->input->get("id_wilayah");
    $query =
    "
    select
    	tma.id_biayatma,
    	tma.id_wilayah,
      kab.id_wilayah as id_kabupaten,
      concat('DESA ', wil.nama_wilayah, ' ', kec.nama_wilayah) as deskripsi,
      kab.nama_wilayah as kabupaten,
      tma.biaya,
      tma.tahun_giling
    from tbl_simtr_biayatma tma
    join tbl_simtr_wilayah wil on tma.id_wilayah = wil.id_wilayah
    join tbl_simtr_wilayah kec on left(kec.id_wilayah, 6) = left(tma.id_wilayah, 6)
    join tbl_simtr_wilayah kab on left(kab.id_wilayah, 4) = left(tma.id_wilayah, 4)
    where kab.level = 2 and kec.level = 3 and tma.id_wilayah = ?
    ";
    return json_encode($this->db->query($query, array($id_wilayah))->row());
  }

  public function editBiayaTma($post = null){
    if(is_null($post)) $post = $this->input->post();
    $query = "update tbl_simtr_biayatma set tahun_giling = ?, id_wilayah = ?, biaya = ? where id_biayatma = ?";
    $this->db->query($query, array($post["tahun_giling"], $post["id_wilayah"], $post["biaya"], $post["id_biayatma"]));
    return json_encode($this->db->affected_rows());
  }

  public function getTransaksiByIdBiayaTma($id_biayatma = null){
    if(is_null($id_biayatma))$id_biayatma = $this->input->get("id_biayatma");
    $query = "select * from tbl_simtr_transaksi trn where id_biayatma = ?";
    return json_encode($this->db->query($query, array($id_biayatma))->result());
  }

  public function hapusData($post = null){
    if(is_null($post)) $post = $this->input->post();
    $query = "delete from tbl_simtr_biayatma where id_biayatma = ?";
    $this->db->query($query, array($post["id_biayatma"]));
    return json_encode($this->db->affected_rows());
  }

}
