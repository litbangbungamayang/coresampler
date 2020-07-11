<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Transaksitma_model extends CI_Model{

  private $_table = "tbl_simtr_transaksitma";
  public $id_pbtma;
  public $jml_netto;
  public $jml_biaya;
  public $afd;

  public function simpan($request){
    $this->id_pbtma = $request["id_pbtma"];
    $this->jml_netto = $request["jml_netto"];
    $this->jml_biaya = $request["jml_biaya"];
    $this->afd = $request["afd"];
    $this->db->insert($this->_table, $this);
  }

  public function getAllPbtma($request){
    $priv_level = $this->session->userdata("jabatan");
    $query =
    "
      select
        dok.no_dokumen, date_format(dok.tgl_buat, '%d-%m-%Y %k:%i:%s') as tgl_buat,
        sum(trans_tma.jml_netto) as jml_netto,
        sum(trans_tma.jml_biaya) as jml_biaya,
        catatan as periode, dok.tgl_validasi_bagian, dok.tgl_validasi_kasubbag,
        ? as priv_level
      from tbl_simtr_transaksitma trans_tma
        join tbl_dokumen dok on trans_tma.id_pbtma = dok.id_dokumen
      where trans_tma.afd = ? and trans_tma.tahun_giling like concat('%', ?, '%')
      group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($request["afd"]))->result());
  }

}
