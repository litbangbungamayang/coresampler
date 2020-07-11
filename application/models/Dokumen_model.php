<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Dokumen_model extends CI_Model{

  private $_table = "tbl_dokumen";
  public $id_dokumen;
  public $no_dokumen;
  public $tipe_dokumen;
  public $id_user;
  public $tgl_buat;
  public $tgl_validasi_bagian;
  public $catatan;

  public function rules(){
    return [
      [
        'field' => 'no_dokumen',
        'label' => 'no_dokumen',
        'rules' => 'required'
      ],
      [
        'field' => 'keterangan',
        'label' => 'keterangan',
        'rules' => 'required'
      ],
      [
        'field' => 'kode_rekening',
        'label' => 'kode_rekening',
        'rules' => 'required'
      ]
    ];
  }

  public function getAll(){
    return $this->db->get($this->_table)->result();
  }

  public function getById($id_dokumen){
    return $this->db->get_where($this->_table, ["id_dokumen" => $id_dokumen])->row();
  }

  public function getByBagian($id_bagian){
    return $this->db->get_where($this->_table, ["id_bagian" => $id_bagian])->row();
  }

  public function getBySubbagian($id_subbagian){
    return $this->db->get_where($this->_table, ["id_subbagian" => $id_subbagian])->row();
  }

  public function simpan($tipe_dokumen = null, $catatan = null){
    if(is_null($tipe_dokumen) || is_null($catatan)){
      $post = $this->input->post();
      $tipe_dokumen = $post["tipe_dokumen"];
      $catatan = $post["catatan"];
    }
    $this->tipe_dokumen = $tipe_dokumen;
    $this->catatan = $catatan;
    $this->id_user = $this->session->userdata('id_user');
    $this->db->insert($this->_table, $this);
    $last_id = $this->db->insert_id();
    $nomor_dokumen = $tipe_dokumen."-".$last_id;
    $this->db->set('no_dokumen', $nomor_dokumen)->where('id_dokumen', $last_id)->update($this->_table);
    return $last_id;
  }

  public function validasi(){
    $id_dokumen = $this->input->post("id_dokumen");
    $query = "select * from tbl_dokumen where id_dokumen = ?";
    $data_dokumen = $this->db->query($query, array($id_dokumen))->row();
    if($this->session->userdata("jabatan") == "Asisten Bagian"){
      $query = "update tbl_dokumen set tgl_validasi_bagian = now() where id_dokumen = ?";
      $this->db->query($query, array($id_dokumen));
      echo "SUCCESS";
    } else {
      echo "FAILED";
    }
  }

  public function validasiAskep(){
    $id_dokumen = $this->input->post("id_dokumen");
    $query = "select * from tbl_dokumen where id_dokumen = ?";
    $data_dokumen = $this->db->query($query, array($id_dokumen))->row();
    if($this->session->userdata("jabatan") == "Kepala Sub Bagian"){
      $query = "update tbl_dokumen set tgl_validasi_kasubbag = now() where id_dokumen = ?";
      $this->db->query($query, array($id_dokumen));
      echo "SUCCESS";
    } else {
      echo "FAILED";
    }
  }

  public function batalkanPbma(){
    $id_dokumen = $this->input->post("id_dokumen");
    $query = "update tbl_simtr_transaksi set id_pbma = null where id_pbma = ?";
    $this->db->query($query, array($id_dokumen));
  }

  public function batalkanPpk(){
    $id_dokumen = $this->input->post("id_dokumen");
    $query = "update tbl_simtr_transaksi set id_ppk = null where id_ppk = ?";
    $this->db->query($query, array($id_dokumen));
  }

  public function batalkanPbp(){
    $id_dokumen = $this->input->post("id_dokumen");
    $query = "update tbl_simtr_transaksi set id_pbp = null where id_pbp = ?";
    $this->db->query($query, array($id_dokumen));
    return json_encode("SUCCESS");
  }

  public function update(){
    $post = $this->input->post();
    $this->id_dokumen = $post["id_dokumen"];
    $this->tgl_validasi_bagian = $post["tgl_validasi_bagian"];
    $this->tgl_validasi_tuk = $post["tgl_validasi_tuk"];
    $this->tgl_validasi_gm = $post["tgl_validasi_gm"];
    $this->tgl_terima_tuk = $post["tgl_terima_tuk"];
    $this->tgl_terima_gm = $post["tgl_terima_gm"];
    $this->db->update($this->_table, $this, array('id_dokumen' => $post['id_dokumen']));
  }

  public function hapus($id_dokumen){
    return $this->db->delete($this->_table, array('id_dokumen' => $post['id_dokumen']));
  }

}
