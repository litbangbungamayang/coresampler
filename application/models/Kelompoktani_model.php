<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Kelompoktani_model extends CI_Model{

  private $_table = "tbl_simtr_kelompoktani";
  public $id_kelompok;
  public $nama_kelompok;
  public $no_kontrak;
  public $kode_blok;
  public $no_ktp;
  public $id_desa;
  public $mt;
  public $tahun_giling;
  public $kategori;
  public $id_varietas;
  public $scan_ktp;
  public $scan_kk;
  public $scan_surat;
  public $id_user;
  public $id_afd;

  public function rules(){
    return [
      [
        "field" => "namaKelompok",
        "label" => "Nama Kelompok",
        "rules" => "required",
        "errors" => ["required" => "Nama kelompok belum diinput!"]
      ],
      [
        "field" => "namaDesa",
        "label" => "ID Desa",
        "rules" => "required",
        "errors" => ["required" => "Desa belum dipilih!"]
      ],
      [
        "field" => "masaTanam",
        "label" => "Masa Tanam",
        "rules" => "required",
        "errors" => ["required" => "Masa tanam belum dipilih!"]
      ],
      [
        "field" => "tahun_giling",
        "label" => "Tahun Giling",
        "rules" => "required",
        "errors" => ["required" => "Tahun giling belum dipilih!"]
      ],
      [
        "field" => "kategori",
        "label" => "Kategori",
        "rules" => "required",
        "errors" => ["required" => "Kategori belum dipilih!"]
      ],
      [
        "field" => "varietas",
        "label" => "ID Varietas",
        "rules" => "required",
        "errors" => ["required" => "Varietas belum dipilih"]
      ],
      [
        "field" => "noKtp",
        "label" => "No. KTP",
        "rules" => "required",
        "errors" => ["required" => "No. KTP belum diinput!"]
      ]
    ];
  }

  public function getAll(){
    return base64_encode($this->db->query("SELECT scan_ktp from tbl_simtr_kelompoktani")->row()->scan_ktp);
  }

  public function simpan(){
    $post = $this->input->post();
    $this->nama_kelompok = strtoupper($post["namaKelompok"]);
    $this->id_desa = $post["namaDesa"];
    $this->mt = $post["masaTanam"];
    $this->tahun_giling = $post["tahun_giling"];
    $this->no_ktp = $post["noKtp"];
    $this->kategori = $post["kategori"];
    $this->id_varietas = $post["varietas"];
    $this->scan_ktp = file_get_contents($_FILES["scanKtp"]["tmp_name"]);
    $this->scan_kk = file_get_contents($_FILES["scanKk"]["tmp_name"]);
    $this->scan_surat = file_get_contents($_FILES["scanSurat"]["tmp_name"]);
    $afdeling = $this->session->userdata('afd');
    $id_user = $this->session->userdata('id_user');
    $this->id_user = $id_user;
    $this->id_afd = $afdeling;
    //$this->db->trans_begin();
    $this->db->insert($this->_table, $this);
    $lastId = $this->db->insert_id();
    $tahun_giling = substr($this->tahun_giling,2);
    $noKontrak = $afdeling."-".$this->kategori.$tahun_giling."-".str_pad($lastId, 4, "0", STR_PAD_LEFT);
    $kode_blok = $tahun_giling.$afdeling.$this->kategori.str_pad($lastId, 4, "0", STR_PAD_LEFT);
    //$this->db->set('no_kontrak', $noKontrak)->where('id_kelompok', $lastId)->update($this->_table);
    $this->db->set(array('no_kontrak' => $noKontrak, 'kode_blok' => $kode_blok))->where('id_kelompok', $lastId)->update($this->_table);
    return $lastId;
  }

  public function getAllKelompok(){
    $afdeling = $this->session->userdata('afd');
    if (empty($afdeling))$afdeling = "%";
    return json_encode($this->db->query("
      SELECT DISTINCT
        KT.id_kelompok, KT.nama_kelompok, KT.no_ktp, KT.no_kontrak, KT.mt, KT.kategori, WIL.nama_wilayah, SUM(PT.luas) as luas,
        VAR.nama_varietas, KT.tahun_giling, WIL.id_wilayah
      FROM tbl_simtr_kelompoktani KT
        JOIN tbl_simtr_petani PT on PT.id_kelompok = KT.id_kelompok
        JOIN tbl_varietas VAR on KT.id_varietas = VAR.id_varietas
        JOIN tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
        WHERE EXISTS
  	     (SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
        AND KT.no_kontrak LIKE CONCAT(?,'-%')
      GROUP BY KT.id_kelompok
    ", array($afdeling))->result());
  }

  public function getAllKelompokOrderDesa(){
    $afdeling = $this->session->userdata('afd');
    if (empty($afdeling))$afdeling = "%";
    return json_encode($this->db->query("
      SELECT DISTINCT
        KT.id_kelompok, KT.nama_kelompok, KT.no_ktp, KT.no_kontrak, KT.mt, KT.kategori, WIL.nama_wilayah, SUM(PT.luas) as luas,
        VAR.nama_varietas, KT.tahun_giling
      FROM tbl_simtr_kelompoktani KT
        JOIN tbl_simtr_petani PT on PT.id_kelompok = KT.id_kelompok
        JOIN tbl_varietas VAR on KT.id_varietas = VAR.id_varietas
        JOIN tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
        WHERE EXISTS
  	     (SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
        AND KT.no_kontrak LIKE CONCAT(?,'-%')
      GROUP BY KT.id_kelompok, WIL.nama_wilayah
    ", array($afdeling))->result());
  }

  public function getKelompokByTahun(){
    $afdeling = $this->session->userdata('afd');
    if (empty($afdeling))$afdeling = "%";
    $tahun_giling = $this->input->get("tahun_giling");
    return json_encode($this->db->query("
      SELECT DISTINCT
        KT.id_kelompok, KT.nama_kelompok, KT.no_kontrak, KT.mt, KT.kategori, WIL.nama_wilayah, SUM(PT.luas) as luas,
        VAR.nama_varietas, KT.tahun_giling
      FROM tbl_simtr_kelompoktani KT
        JOIN tbl_simtr_petani PT on PT.id_kelompok = KT.id_kelompok
        JOIN tbl_varietas VAR on KT.id_varietas = VAR.id_varietas
        JOIN tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
        WHERE EXISTS
  	     (SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
        AND KT.no_kontrak LIKE CONCAT(?,'-%') AND KT.tahun_giling = ?
      GROUP BY KT.id_kelompok
    ", array($afdeling, $tahun_giling))->result());
  }

  public function getKelompokById($id_kelompok = null){
    if (is_null($id_kelompok)) $id_kelompok = $this->input->get("id_kelompok");
    return json_encode($this->db->query("
      SELECT DISTINCT
        KT.id_kelompok, KT.nama_kelompok, KT.no_ktp, TO_BASE64(KT.scan_ktp) as scan_ktp, KT.no_kontrak, KT.mt, KT.kategori, WIL.id_wilayah, WIL.nama_wilayah, SUM(PT.luas) as luas,
        VAR.nama_varietas, KT.tahun_giling, KT.kode_blok
      FROM tbl_simtr_kelompoktani KT
        JOIN tbl_simtr_petani PT on PT.id_kelompok = KT.id_kelompok
        JOIN tbl_varietas VAR on KT.id_varietas = VAR.id_varietas
        JOIN tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
        WHERE EXISTS
  	     (SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
        AND KT.id_kelompok = ?
      GROUP BY KT.id_kelompok
    ", array($id_kelompok))->row());
  }

  public function getKelompokByKodeBlok($kode_blok = null){
    if (is_null($kode_blok)) $kode_blok = $this->input->get("kode_blok");
    return json_encode($this->db->query("
      SELECT DISTINCT
        KT.id_kelompok, KT.nama_kelompok, KT.no_ktp, TO_BASE64(KT.scan_ktp) as scan_ktp, KT.no_kontrak, KT.mt, KT.kategori, WIL.id_wilayah, WIL.nama_wilayah, SUM(PT.luas) as luas,
        VAR.nama_varietas, KT.tahun_giling, KT.kode_blok
      FROM tbl_simtr_kelompoktani KT
        JOIN tbl_simtr_petani PT on PT.id_kelompok = KT.id_kelompok
        JOIN tbl_varietas VAR on KT.id_varietas = VAR.id_varietas
        JOIN tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
        WHERE EXISTS
  	     (SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
        AND KT.kode_blok = ?
      GROUP BY KT.id_kelompok
    ", array($kode_blok))->row());
  }

  public function ubah(){
    $post = $this->input->post();
    $this->id_kelompok = $post["id_kelompok"];
    $this->nama_kelompok = $post["nama_kelompok"];
    $this->no_kontrak = $post["no_kontrak"];
    $this->id_desa = $post["id_desa"];
    $this->mt = $post["mt"];
    $this->kategori = post["kategori"];
    $this->id_varietas = $post["id_varietas"];
    $this->scan_ktp = $post["scan_ktp"];
    $this->scan_kk = $post["scan_kk"];
    $this->scan_surat = $post["scan_surat"];
    $this->db->update($this->_table, array('id_kelompok' => $post["id_kelompok"]));
  }
}
