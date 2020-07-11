<?php
//if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: dirg
 * Date: 3/13/2018
 * Time: 5:02 PM
 */
class Api_bcn_model extends CI_Model
{
    function __construct()
    {
    }

	public function getDataTimbang($input){
  	$query =
    "
    select
      spta.no_spat, fld.kode_blok, fld.deskripsi_blok, (timb.netto_final) as netto, date_format(spta.tgl_timbang, '%d-%m-%Y') as tgl_timbang,
      cs.RAFAKSI, cs.HK, cs.KNPP, cs.NNPP
    from t_timbangan timb
      join t_spta spta on spta.id = timb.id_spat
      join t_selektor sel on sel.id_spta = spta.id
      join TBL_CORELAB cs on cs.NUMERATOR = spta.no_spat
        join sap_field fld on fld.kode_blok = spta.kode_blok
    where spta.tgl_timbang = ? and spta.kode_blok = ?
    ";
  	return json_encode($this->db->query($query, array($input["tgl_timbang"], $input["kode_blok"]))->result());
  }

  public function getDataTimbangPeriode($request){
    $query =
    "
      select *, date_format(spta.tgl_timbang, '%d-%m-%Y') as tgl_timbang from t_timbangan timb
        join t_spta spta on spta.id = timb.id_spat
        join t_selektor sel on sel.id_spta = spta.id
        join TBL_CORELAB cs on cs.NUMERATOR = spta.no_spat
      where spta.tgl_timbang >= ? and spta.tgl_timbang <= ?
    ";
    return json_encode($this->db->query($query, array($request["tgl_timbang_awal"], $request["tgl_timbang_akhir"]))->result());
  }

  public function getDataTimbangPeriodeGroup($request){
    $query =
    "
    select
    	spta.no_spat, fld.kode_blok, fld.deskripsi_blok, sum(timb.netto_final) as netto, date_format(spta.tgl_timbang, '%d-%m-%Y') as tgl_timbang,
      cs.RAFAKSI, cs.HK, cs.KNPP, cs.NNPP
    from t_timbangan timb
    	join t_spta spta on spta.id = timb.id_spat
    	join t_selektor sel on sel.id_spta = spta.id
    	join TBL_CORELAB cs on cs.NUMERATOR = spta.no_spat
      join sap_field fld on fld.kode_blok = spta.kode_blok
    where spta.tgl_timbang >= ?
      and spta.tgl_timbang <= ?
      and spta.kode_affd = concat('AFD',?)
      and spta.simtr_status = 0
      and fld.mature like concat('%',?,'%')
    group by spta.tgl_timbang, fld.kode_blok
    ";
    return json_encode($this->db->query($query, array($request["tgl_timbang_awal"],
      $request["tgl_timbang_akhir"], $request["afd"], $request["tahun_giling"]))->result());
  }

  public function getDataTimbangPerSpta($request){
    $query =
    "
    select * from t_spta where tgl_timbang >= ? and tgl_timbang <= ? and kode_affd = concat('AFD',?) and simtr_status = 0
    ";
    return json_encode($this->db->query($query, array($request["tgl_timbang_awal"], $request["tgl_timbang_akhir"], $request["afd"]))->result());
  }

  public function updateIdPbtma($request){
    $query =
    "
    update t_spta set simtr_status = 1, simtr_id_pbtma = ?, simtr_tgl = now()
    where id = ?
    ";
    return json_encode($this->db->query($query, array($request["id_pbtma"], $request["id"])));
    //return json_encode("OK");
  }

  public function getAllPetakKebunByKepemilikan($request){
    $query =
    "
      select *, vts.nama_varietas as nama_varietas
      from sap_field fld
        join sap_m_varietas vts on fld.kode_varietas = vts.id_varietas
      where kepemilikan = ? and fld.mature = ?
    ";
    return json_encode($this->db->query($query, array($request["kepemilikan"], $request["tahun_giling"]))->result());
  }

  public function getAllSptaByAfd($request){
    $query =
    "
      select *
      from t_spta spta
        join sap_field fld on fld.kode_blok = spta.kode_blok
      where fld.divisi = ?
    ";
    return json_encode($this->db->query($query, array($request["id_afd"]))->result());
  }

}
