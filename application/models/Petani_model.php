<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Petani_model extends CI_Model{

  private $_table = "tbl_simtr_petani";
  public $id_petani;
  public $id_kelompok;
  public $nama_petani;
  public $luas;
  public $scan_ktp;
  public $scan_kk;

  public function rules_petani(){
    return [
      [
        "field" => "namaPetani",
        "label" => "Nama Petani",
        "rules" => "required",
        "errors" => ["required" => "Nama petani belum diinput"]
      ],
      [
        "field" => "fileGpxKebun",
        "label" => "Pilih file",
        "rules" => "required",
        "errors" => ["required" => "File gpx belum dipilih"]
      ]
    ];
  }

  public function simpan($petani, $idKelompok){
    $this->id_kelompok = $idKelompok;
    $this->nama_petani = $petani->nama_petani;
    $this->luas = $petani->luas;
    $this->scan_ktp = $petani->scanKtp;
    $this->scan_kk = $petani->scanKk;
    $this->db->insert($this->_table, $this);
    return $this->db->insert_id();
  }

  public function getAll(){
    return json_encode($this->db->get($this->_table)->result());
  }

  public function getPetaniByIdKelompok($idKelompok){
    return json_encode($this->db->select("*")->from($this->_table)->where("id_kelompok", $idKelompok)->get()->result());
  }

}
