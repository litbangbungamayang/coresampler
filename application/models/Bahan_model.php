<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bahan_model extends CI_Model{

  private $_table = "tbl_simtr_bahan";
  public $nama_bahan;
  public $jenis_bahan;
  public $satuan;
  public $dosis_per_ha;
  public $tahun_giling;

  public function rules(){
    return [
      [
        "field"=>"nama_bahan",
        "label"=>"Nama Bahan",
        "rules"=>"required",
        "errors"=>["required"=>"Nama bahan belum diinput!"]
      ],
      [
        "field"=>"jenis_bahan",
        "label"=>"Jenis Bahan",
        "rules"=>"required",
        "errors"=>["required"=>"Jenis bahan belum dipilih!"]
      ],
      [
        "field"=>"satuan",
        "label"=>"Satuan",
        "rules"=>"required",
        "errors"=>["required"=>"Satuan belum dipilih!"]
      ]
    ];
  }

  public function simpan(){
    $post = $this->input->post();
    $this->nama_bahan = strtoupper($post["nama_bahan"]);
    $this->jenis_bahan = $post["jenis_bahan"];
    $this->satuan = $post["satuan"];
    $this->dosis_per_ha = $post["dosis"];
    $this->tahun_giling = $post["tahun_giling"];
    $this->biaya_muat = $post["biaya_muat"];
    $this->biaya_angkut = $post["biaya_angkut"];
    $this->db->insert($this->_table, $this);
    return $this->db->insert_id();
  }

  public function edit(){
    $post = $this->input->post();
    $this->nama_bahan = strtoupper($post["nama_bahan"]);
    $this->jenis_bahan = $post["jenis_bahan"];
    $this->satuan = $post["satuan"];
    $this->dosis_per_ha = $post["dosis"];
    $this->tahun_giling = $post["tahun_giling"];
    return $this->db->where("id_bahan", $post["id_bahan"])->update($this->_table, $this);
  }

  public function getAllBahan(){
    return json_encode($this->db->query("
      SELECT * FROM tbl_simtr_bahan
    ")->result());
  }

  public function getBahanByTahunGiling(){
    $tahun_giling = $this->input->get("tahun_giling");
    return json_encode($this->db->from("tbl_simtr_bahan")->where("tahun_giling", $tahun_giling)->get()->result());
  }

  public function getBahanByJenisTahunGiling(){
    $tahun_giling = $this->input->get("tahun_giling");
    $jenis_bahan = $this->input->get("jenis_bahan");
    return json_encode($this->db->from("tbl_simtr_bahan")->where("tahun_giling",
      $tahun_giling)->where("jenis_bahan", $jenis_bahan)->get()->result());
  }

  public function getBahanById($id_bahan = null){
    if (is_null($id_bahan)) $id_bahan = $this->input->get("idBahan");
    return json_encode($this->db->query("
      SELECT * FROM tbl_simtr_bahan WHERE id_bahan = ?
    ", array($id_bahan))->row());
  }

  public function getBahanByJenis(){
    $jenis_bahan = $this->input->get("jenis_bahan");
    return json_encode($this->db->from("tbl_simtr_bahan")->where("jenis_bahan", $jenis_bahan)->get()->result());
  }

  public function hapus($id_bahan = null){
    if (is_null($id_bahan)) $id_bahan = $this->input->post("id_bahan");
    return $this->db->delete($this->_table, array('id_bahan' => $id_bahan));
  }

}
