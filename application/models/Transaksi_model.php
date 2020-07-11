<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model{

  private $_table = "tbl_simtr_transaksi";
  public $id_bahan;
  public $id_kelompoktani;
  public $id_vendor;
  public $kode_transaksi;
  public $kuanta;
  public $rupiah;
  public $tgl_transaksi;
  public $catatan;
  public $tahun_giling;

  public function getTransaksiByIdBahan($id_bahan = null){
    if (is_null($id_bahan)) $id_bahan = $this->input->get("id_bahan");
    return json_encode($this->db->query("select * from tbl_simtr_transaksi where id_bahan = ?", array($id_bahan))->result());
  }

  public function getTransaksiByIdVendor($id_vendor = null){
    if (is_null($id_vendor)) $id_vendor = $this->input->get("id_vendor");
    return json_encode($this->db->query("select * from tbl_simtr_transaksi where id_vendor = ?", array($id_vendor))->result());
  }

  public function getTransaksiByIdAktivitas($id_aktivitas = null){
    if (is_null($id_aktivitas)) $id_aktivitas = $this->input->get("id_aktivitas");
    return json_encode($this->db->select("*")->from($this->_table)->where("id_aktivitas", $id_aktivitas)->get()->result());
  }

  public function postPbma($id_dokumen = null, $tgl_awal = null, $tgl_akhir = null, $id_afd = null){
    if(!is_null($id_dokumen) && !is_null($tgl_awal) && !is_null($tgl_akhir) && !is_null($id_afd)){
      $query =
      "update tbl_simtr_transaksi trn
      join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
      set trn.id_pbma=?
      where trn.tgl_transaksi >= ? and trn.tgl_transaksi <= date_add(?, interval 1 day)
      and trn.id_pbma is null and trn.kode_transaksi = 2 and trn.kuanta = 0 and trn.id_bahan <> 0 and trn.rupiah <> 0
      and kt.id_afd = ?
      and trn.catatan like 'BIAYA%'";
      return json_encode($this->db->query($query, array($id_dokumen, $tgl_awal, $tgl_akhir, $id_afd)));
    }
  }

  public function postPbp($id_dokumen = null, $tgl_awal = null, $tgl_akhir = null, $id_afd = null){
    if(!is_null($id_dokumen) && !is_null($tgl_awal) && !is_null($tgl_akhir) && !is_null($id_afd)){
      $query =
      "update tbl_simtr_transaksi trn
      join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
      set trn.id_pbp=?
      where trn.tgl_transaksi >= ? and trn.tgl_transaksi <= date_add(?, interval 1 day)
      and trn.id_pbp is null and trn.kode_transaksi = 2 and trn.kuanta <> 0 and trn.id_aktivitas <> 0 and trn.rupiah <> 0
      and kt.id_afd = ?";
      return json_encode($this->db->query($query, array($id_dokumen, $tgl_awal, $tgl_akhir, $id_afd)));
    }
  }

  public function getTransaksiMasukByTahunGiling(){
    $tahun_giling = $this->input->get("tahun_giling");
    $query =
    "select
	     INV.id_transaksi, BAHAN.nama_bahan, INV.kode_transaksi, INV.kuanta, BAHAN.satuan,
       INV.rupiah, INV.tgl_transaksi, INV.tahun_giling, VENDOR.nama_vendor, INV.catatan
    from tbl_simtr_transaksi INV
    join tbl_simtr_vendor VENDOR on VENDOR.id_vendor = INV.id_vendor
    join tbl_simtr_bahan BAHAN on BAHAN.id_bahan = INV.id_bahan
    where INV.kode_transaksi = 1 AND INV.tahun_giling = ?";
    return json_encode($this->db->query($query, array($tahun_giling))->result());
  }

  public function getAu58ByNoTransaksi(){
    $no_transaksi = $this->input->get("no_transaksi");
    $id_kelompok = $this->input->get("id_kelompok");
    $query = "select
      	KT.nama_kelompok, KT.no_kontrak,
          (case when KT.kategori = 1 then 'PC' when KT.kategori = 2 then 'RT1'
      		when KT.kategori = 3 then 'RT2'
              when KT.kategori = 4 then 'RT3' end) as kategori,
        KT.id_afd, PT.luas, WIL.nama_wilayah, TRANS.no_transaksi, TRANS.tgl_transaksi,
        BAHAN.jenis_bahan, BAHAN.nama_bahan, TRANS.kuanta, BAHAN.satuan,
        TRANS.id_au58, dok.tgl_validasi_bagian
      from tbl_simtr_kelompoktani KT
      join
      	(select distinct PT.id_kelompok, sum(PT.luas) as luas from tbl_simtr_petani PT
      		join tbl_simtr_kelompoktani KT on KT.id_kelompok = PT.id_kelompok
              where KT.id_kelompok = ?) PT on PT.id_kelompok = KT.id_kelompok
      join tbl_simtr_transaksi TRANS on TRANS.id_kelompoktani = KT.id_kelompok
      join tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
      join tbl_simtr_bahan BAHAN on BAHAN.id_bahan = TRANS.id_bahan
      join tbl_dokumen dok on dok.id_dokumen = TRANS.id_au58
      where TRANS.no_transaksi = ? and TRANS.kuanta > 0
      group by TRANS.id_transaksi";
      return json_encode($this->db->query($query, array($id_kelompok, $no_transaksi))->result());
  }

  public function getTransaksiAktivitasByNoTransaksi($no_transaksi = null, $id_kelompok = null){
    if (is_null($no_transaksi) || is_null($id_kelompok)){
      $no_transaksi = $this->input->get("no_transaksi");
      $id_kelompok = $this->input->get("id_kelompok");
    }
    $query =
      "select
      	KT.nama_kelompok, KT.no_kontrak, KT.id_afd, KT.tahun_giling, KT.mt, vrt.nama_varietas,
          (case when KT.kategori = 1 then 'PC' when KT.kategori = 2 then 'RT1'
      		when KT.kategori = 3 then 'RT2'
              when KT.kategori = 4 then 'RT3' end) as kategori,
      	PT.luas, WIL.nama_wilayah, TRANS.no_transaksi, TRANS.tgl_transaksi, AKT.nama_aktivitas, AKT.biaya, TRANS.kuanta, TRANS.rupiah,
        dok.no_dokumen, dok.tgl_buat, dok.tgl_validasi_bagian, dok.tgl_validasi_kasubbag, TRANS.id_ppk,
        AKT.jenis_aktivitas, AKT.tunai
      from tbl_simtr_kelompoktani KT
      join
      	(select distinct PT.id_kelompok, sum(PT.luas) as luas from tbl_simtr_petani PT
      		join tbl_simtr_kelompoktani KT on KT.id_kelompok = PT.id_kelompok
              where KT.id_kelompok = ?) PT on PT.id_kelompok = KT.id_kelompok
      join tbl_simtr_transaksi TRANS on TRANS.id_kelompoktani = KT.id_kelompok
      join tbl_simtr_wilayah WIL on WIL.id_wilayah = KT.id_desa
      join tbl_aktivitas AKT on AKT.id_aktivitas = TRANS.id_aktivitas
      join tbl_dokumen dok on dok.id_dokumen = TRANS.id_ppk
      join tbl_varietas vrt on vrt.id_varietas = KT.id_varietas
      where TRANS.no_transaksi = ?
      group by TRANS.id_transaksi";
      return json_encode($this->db->query($query, array($id_kelompok, $no_transaksi))->result());
  }

  public function cekStokBahanByIdBahan($id_bahan = null){
    if (is_null($id_bahan)) $id_bahan = $this->input->get("id_bahan");
    $query =
    "
      select
         INV.id_bahan, BAHAN.nama_bahan, sum(case kode_transaksi when 1 then kuanta when 2 then -kuanta end) as total_kuanta,
         BAHAN.satuan, BAHAN.jenis_bahan
      from tbl_simtr_transaksi INV
      join tbl_simtr_bahan BAHAN on BAHAN.id_bahan = INV.id_bahan
      join tbl_simtr_umum UMUM on UMUM.tahun_giling = INV.tahun_giling
      where INV.id_bahan = ?
      group by id_bahan
    ";
    return json_encode($this->db->query($query, array($id_bahan))->result());
  }

  public function getTransaksiKeluarByIdKelompok(){
    $id_kelompok = $this->input->get("id_kelompok");
    $query =
    "
    select
      TRANS.id_transaksi, TRANS.id_kelompoktani, BAHAN.id_bahan, BAHAN.nama_bahan, BAHAN.satuan,
      TRANS.no_transaksi, TRANS.kuanta, TRANS.rupiah, TRANS.tgl_transaksi, BAHAN.biaya_muat, BAHAN.biaya_angkut,
      (ROUND(TRANS.kuanta/BAHAN.dosis_per_ha, 2)) as luas_aplikasi
    from tbl_simtr_transaksi TRANS
    join tbl_simtr_bahan BAHAN on BAHAN.id_bahan = TRANS.id_bahan
    where TRANS.id_kelompoktani = ? and TRANS.kode_transaksi = 2  and BAHAN.jenis_bahan = 'PUPUK' and TRANS.kuanta > 0
    ";
    return json_encode($this->db->query($query, array($id_kelompok))->result());
  }

  public function getTransaksiAktivitasByIdKelompok(){
    $id_kelompok = $this->input->get("id_kelompok");
    $jenis_aktivitas = $this->input->get("jenis_aktivitas");
    $query =
    "
    select
      TRANS.id_transaksi, TRANS.id_kelompoktani, AKTV.id_aktivitas, AKTV.nama_aktivitas, AKTV.biaya, TRANS.no_transaksi, TRANS.kuanta, TRANS.rupiah, TRANS.tgl_transaksi
    from tbl_simtr_transaksi TRANS
    join tbl_aktivitas AKTV on AKTV.id_aktivitas = TRANS.id_aktivitas
    where TRANS.id_kelompoktani = ? and TRANS.kode_transaksi = 2 and AKTV.jenis_aktivitas = ?
    ";
    return json_encode($this->db->query($query, array($id_kelompok, $jenis_aktivitas))->result());
  }

  public function getTransaksiByIdKelompokIdBahan($id_kelompok = null, $id_bahan = null){
    if (is_null($id_kelompok) || is_null($id_bahan)){
      $id_kelompok = $this->input->get("id_kelompok");
      $id_bahan = $this->input->get("id_bahan");
    }
    $query =
    "
      select sum(TRANS.kuanta) as kuanta
      from tbl_simtr_transaksi TRANS
      where TRANS.id_kelompoktani = ? and TRANS.id_bahan = ?
    ";
    return json_encode($this->db->query($query, array($id_kelompok, $id_bahan))->row());
  }

  public function getTransaksiByIdKelompokIdAktivitas($id_kelompok = null, $id_aktivitas = null){
    if (is_null($id_kelompok) || is_null($id_aktivitas)){
      $id_kelompok = $this->input->get("id_kelompok");
      $id_aktivitas = $this->input->get("id_aktivitas");
    }
    $query =
    "
    select sum(TRANS.kuanta) as kuanta
    from tbl_simtr_transaksi TRANS
    where TRANS.id_kelompoktani = ? and TRANS.id_aktivitas = ?
    ";
    return json_encode($this->db->query($query, array($id_kelompok, $id_aktivitas))->row());
  }

  public function getTransaksiByIdKelompokJenisAktivitas($id_kelompok = null, $jenis_aktivitas = null){
    $query =
    "
    select sum(trn.kuanta) as kuanta
    from tbl_simtr_transaksi trn
      join tbl_aktivitas akt on akt.id_aktivitas = trn.id_aktivitas
    where id_kelompoktani = ? and akt.jenis_aktivitas = ?
    ";
    return json_encode($this->db->query($query, array($id_kelompok, $jenis_aktivitas))->row());
  }

  public function simpan($data_transaksi = null){
    if (is_null($data_transaksi)){
      $post = $this->input->post();
    } else {
      $post = $data_transaksi;
    }
    $this->id_bahan = $post["id_bahan"];
    $this->id_aktivitas = $post["id_aktivitas"];
    $this->id_kelompoktani = $post["id_kelompoktani"];
    $this->id_vendor = $post["id_vendor"];
    $this->kode_transaksi = $post["kode_transaksi"];
    $this->no_transaksi = $post["no_transaksi"];
    $this->kuanta = $post["kuanta_bahan"];
    $this->rupiah = $post["rupiah_bahan"];
    $this->catatan = strtoupper($post["catatan"]);
    $this->tahun_giling = $post["tahun_giling"];
    $this->id_user = $this->session->userdata('id_user');
    $this->db->insert($this->_table, $this);
    return $this->db->insert_id();
  }

  public function updateIdAu58($id_au58 = null, $no_transaksi = null){
    $query = "update tbl_simtr_transaksi set id_au58 = ? where no_transaksi = ? and kuanta > 0";
    $this->db->query($query, array($id_au58, $no_transaksi));
  }

  public function updateIdPbtma($id_pbtma = null, $no_transaksi = null){
    $query = "update tbl_simtr_transaksi set id_pbtma = ? where no_transaksi = ? and kuanta > 0";
    $this->db->query($query, array($id_pbtma, $no_transaksi));
  }

  public function updateIdPpk($id_ppk = null, $no_transaksi = null){
    $query = "update tbl_simtr_transaksi set id_ppk = ? where no_transaksi = ? and id_aktivitas <> 0";
    $this->db->query($query, array($id_ppk, $no_transaksi));
  }

  public function getHargaSatuanByIdBahan($id_bahan = null){
    if (is_null($id_bahan)) $id_bahan = $this->input->get("id_bahan");
    $query =
    "
    select (jml_rupiah/jml_kuanta) as harga_unit from
      (select sum(kuanta) as jml_kuanta, sum(rupiah) as jml_rupiah from tbl_simtr_transaksi
        where kode_transaksi = 1 and id_bahan = ?) total
    ";
    return json_encode($this->db->query($query, array($id_bahan))->result());
  }

  public function getTransaksiBahanByIdKelompokNamaBahanPeriode($id_kelompok = null, $nama_bahan = null){
    if(is_null($id_kelompok) || is_null($nama_bahan)){
      $id_kelompok = $this->input->get("id_kelompok");
      $nama_bahan = $this->input->get("nama_bahan");
    }
    $query =
    "select
      TRANS.id_transaksi, TRANS.id_bahan, TRANS.id_kelompoktani, TRANS.no_transaksi,
      TRANS.kuanta, TRANS.rupiah, TRANS.tgl_transaksi, TRANS.tahun_giling, BHN.tahun_giling,
      BHN.satuan, BHN.biaya_muat, BHN.biaya_angkut
    from tbl_simtr_transaksi TRANS
    join tbl_simtr_bahan BHN on BHN.id_bahan = TRANS.id_bahan
    where BHN.nama_bahan = ? and BHN.tahun_giling = TRANS.tahun_giling and
      TRANS.id_kelompoktani = ? and TRANS.tgl_transaksi >= '2020-01-01' and
    	TRANS.tgl_transaksi <= '2020-01-15'";
    return json_encode($this->db->query($query, array($nama_bahan, $id_kelompok))->result());
  }

  public function getRekapMuatAngkutPupuk($tgl_awal = null, $tgl_akhir = null, $tahun_giling = null){
    if(is_null($tgl_awal) || is_null($tgl_akhir) || is_null($tahun_giling)){
      $tgl_awal = $this->input->get("tgl_awal");
      $tgl_akhir = $this->input->get("tgl_akhir");
      $tahun_giling = $this->input->get("tahun_giling");
    }
    $query =
    "
    select
    	TRANS.id_transaksi, TRANS.id_bahan, TRANS.id_kelompoktani, TRANS.no_transaksi,
    	sum(TRANS.kuanta) as total_pupuk, TRANS.rupiah, TRANS.tgl_transaksi, TRANS.tahun_giling,
    	BHN.satuan,
      TRANS.kuanta*BHN.biaya_muat as biaya_muat,
      TRANS.kuanta*BHN.biaya_angkut as biaya_angkut
    from tbl_simtr_transaksi TRANS
    join tbl_simtr_bahan BHN on BHN.id_bahan = TRANS.id_bahan
    where BHN.tahun_giling = TRANS.tahun_giling and
    	TRANS.tgl_transaksi >= ? and
    	TRANS.tgl_transaksi <= date_add(?, interval 1 day) and
      TRANS.tahun_giling like concat('%', ?, '%') and TRANS.kuanta > 0
    group by TRANS.id_kelompoktani
    ";
    return json_encode($this->db->query($query, array($tgl_awal, $tgl_akhir, $tahun_giling))->result());
  }

  public function getRekapPupukByNamaBahan($tgl_awal = null, $tgl_akhir = null, $nama_bahan = null, $id_kelompok = null){
    if(is_null($id_kelompok) || is_null($tgl_awal) || is_null($tgl_akhir) || is_null($nama_bahan)){
      $id_kelompok = $this->input->get("id_kelompok");
      $tgl_awal = $this->input->get("tgl_awal");
      $tgl_akhir = $this->input->get("tgl_akhir");
      $nama_bahan = $this->input->get("nama_bahan");
    }
    $query =
    "select
      TRANS.id_transaksi, TRANS.id_bahan, TRANS.id_kelompoktani, TRANS.no_transaksi,
      TRANS.kuanta, TRANS.rupiah, TRANS.tgl_transaksi, TRANS.tahun_giling, BHN.tahun_giling,
      BHN.satuan, BHN.biaya_muat, BHN.biaya_angkut
    from tbl_simtr_transaksi TRANS
    join tbl_simtr_bahan BHN on BHN.id_bahan = TRANS.id_bahan
    where BHN.nama_bahan = ? and
      TRANS.id_kelompoktani = ? and
      TRANS.tgl_transaksi >= ? and
    	TRANS.tgl_transaksi <= date_add(?, interval 1 day)
    ";
    return json_encode($this->db->query($query, array($nama_bahan, $id_kelompok, $tgl_awal, $tgl_akhir))->result());
  }

  public function getTransaksiByKode($kode_transaksi = null){
    if (is_null($kode_transaksi)) $kode_transaksi = $this->input->get("kode_transaksi");
    $query =
    "select
	     INV.id_transaksi, BAHAN.nama_bahan, INV.kode_transaksi, INV.kuanta, BAHAN.satuan,
       INV.rupiah, INV.tgl_transaksi, INV.tahun_giling, VENDOR.nama_vendor, INV.catatan
    from tbl_simtr_transaksi INV
    join tbl_simtr_vendor VENDOR on VENDOR.id_vendor = INV.id_vendor
    join tbl_simtr_bahan BAHAN on BAHAN.id_bahan = INV.id_bahan
    where INV.kode_transaksi = ?";
    return json_encode($this->db->query($query, array($kode_transaksi))->result());
  }

  public function getRekapBiayaPerawatan($tgl_awal = null, $tgl_akhir = null, $tahun_giling = null){
    $afdeling = $this->session->userdata('afd');
    if(is_null($tgl_awal) || is_null($tgl_akhir) || is_null($tahun_giling)){
      $tgl_awal = $this->input->get("tgl_awal");
      $tgl_akhir = $this->input->get("tgl_akhir");
      $tahun_giling = $this->input->get("tahun_giling");
    }
    if(is_null($afdeling)){
      $afdeling = "";
    }
    $query =
    "
    select
    	trans.id_kelompoktani,
      if (length(kt.nama_kelompok) > 20, concat(substring(kt.nama_kelompok,1,17), '...'), kt.nama_kelompok) as nama_kelompok,
      kt.no_kontrak,
      kt.tahun_giling,
      wil.nama_wilayah,
      date_format(trans.tgl_transaksi, '%d-%m-%Y') as tgl_transaksi,
      ( select
    		SUM(PT.luas) as luas
    	FROM tbl_simtr_kelompoktani kt
    		JOIN tbl_simtr_petani PT on PT.id_kelompok = kt.id_kelompok
    	WHERE EXISTS
    			(SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
    		and kt.id_kelompok = trans.id_kelompoktani
    		group by kt.id_kelompok
    	) as luas,
    	( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi trn_2
        join tbl_aktivitas akt on akt.id_aktivitas = trn_2.id_aktivitas
    		where id_kelompoktani = trans.id_kelompoktani
        and akt.tunai = 1
    	) as jml_perawatan
    from tbl_simtr_transaksi trans
    join tbl_simtr_kelompoktani kt on trans.id_kelompoktani = kt.id_kelompok
    join tbl_aktivitas akt on akt.id_aktivitas = trans.id_aktivitas
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    where trans.kode_transaksi = 2 and trans.tgl_transaksi >= ?
    and trans.tgl_transaksi <= date_add(?, interval 1 day) and trans.tahun_giling like concat('%', ?, '%')
    and trans.id_pbp is null and kt.id_afd like concat('%', ?, '%') and trans.id_aktivitas <> 0
    and akt.tunai = 1
    group by wil.nama_wilayah, trans.id_kelompoktani
    ";
    return json_encode($this->db->query($query, array($tgl_awal, $tgl_akhir, $tahun_giling, $afdeling))->result());
  }

  public function getRekapBiayaMuatAngkutPupuk($tgl_awal = null, $tgl_akhir = null, $tahun_giling = null){
    $afdeling = $this->session->userdata('afd');
    if(is_null($tgl_awal) || is_null($tgl_akhir) || is_null($tahun_giling)){
      $tgl_awal = $this->input->get("tgl_awal");
      $tgl_akhir = $this->input->get("tgl_akhir");
      $tahun_giling = $this->input->get("tahun_giling");
    }
    if(is_null($afdeling)){
      $afdeling = "";
    }
    $query =
    "
    select
    	trans.id_kelompoktani,
      if (length(kt.nama_kelompok) > 20, concat(substring(kt.nama_kelompok,1,17), '...'), kt.nama_kelompok) as nama_kelompok,
      kt.no_kontrak,
      kt.tahun_giling,
      wil.nama_wilayah,
      date_format(trans.tgl_transaksi, '%d-%m-%Y') as tgl_transaksi,
      ( select
  			SUM(PT.luas) as luas
  		FROM tbl_simtr_kelompoktani kt
  			JOIN tbl_simtr_petani PT on PT.id_kelompok = kt.id_kelompok
  		WHERE EXISTS
    			(SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
    		and kt.id_kelompok = trans.id_kelompoktani
    		group by kt.id_kelompok
    	) as luas,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'UREA'
    	) as urea,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'KCL'
    	) as kcl,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'TSP'
    	) as tsp,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and trans_2.kuanta > 0
    	) as jml,
        ( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA MUAT%'
    	) as biaya_muat,
      ( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA ANGKUT%'
    	) as biaya_angkut,
      ( select sum(rupiah) as total_biaya
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA%'
    	) as total_biaya
    from tbl_simtr_transaksi trans
    join tbl_simtr_kelompoktani kt on trans.id_kelompoktani = kt.id_kelompok
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    where trans.kode_transaksi = 2 and trans.tgl_transaksi >= ?
    and trans.tgl_transaksi <= date_add(?, interval 1 day) and trans.tahun_giling like concat('%', ?, '%')
    and trans.id_pbma is null and kt.id_afd like concat('%', ?, '%') and trans.id_bahan <> 0
	  and kuanta = 0
    group by wil.nama_wilayah, trans.id_kelompoktani
    ";
    return json_encode($this->db->query($query, array($tgl_awal, $tgl_akhir, $tahun_giling, $afdeling))->result());
  }

  public function detailPbma(){
    $id_pbma = $this->input->get("id_pbma");
    $query =
    "
    select
      dok.no_dokumen,
      dok.tgl_buat,
      dok.tgl_validasi_bagian,
      dok.tgl_validasi_kasubbag,
      dok.catatan,
      trans.id_pbma,
    	trans.id_kelompoktani,
      if (length(kt.nama_kelompok) > 20, concat(substring(kt.nama_kelompok,1,17), '...'), kt.nama_kelompok) as nama_kelompok,
      kt.no_kontrak,
      kt.tahun_giling,
      kt.id_afd,
      wil.nama_wilayah,
      date_format(trans.tgl_transaksi, '%d-%m-%Y') as tgl_transaksi,
      ( select
  			SUM(PT.luas) as luas
  		FROM tbl_simtr_kelompoktani kt
  			JOIN tbl_simtr_petani PT on PT.id_kelompok = kt.id_kelompok
  		WHERE EXISTS
    			(SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
    		and kt.id_kelompok = trans.id_kelompoktani
    		group by kt.id_kelompok
    	) as luas,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'UREA'
    	) as urea,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'KCL'
    	) as kcl,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and bhn.nama_bahan = 'TSP'
    	) as tsp,
      ( select sum(kuanta)
    		from tbl_simtr_transaksi trans_2
            join tbl_simtr_bahan bhn on trans_2.id_bahan = bhn.id_bahan
            where trans_2.id_kelompoktani = trans.id_kelompoktani
            and trans_2.kuanta > 0
    	) as jml,
        ( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA MUAT%'
    	) as biaya_muat,
      ( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA ANGKUT%'
    	) as biaya_angkut,
      ( select sum(rupiah) as total_biaya
    		from tbl_simtr_transaksi
            where id_kelompoktani = trans.id_kelompoktani
            and catatan like '%BIAYA%'
    	) as total_biaya
    from tbl_simtr_transaksi trans
    join tbl_simtr_kelompoktani kt on trans.id_kelompoktani = kt.id_kelompok
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    join tbl_dokumen dok on dok.id_dokumen = trans.id_pbma
    where trans.id_pbma = ?
    group by wil.nama_wilayah, trans.id_kelompoktani
    ";
    return json_encode($this->db->query($query, array($id_pbma))->result());
  }

  public function detailPbtma(){
    $id_pbtma = $this->input->get("id_pbtma");
    $query =
    "
    select
      dok.no_dokumen,
      dok.tgl_buat,
      dok.tgl_validasi_bagian,
      dok.tgl_validasi_kasubbag,
      dok.catatan,
      trans.id_pbtma,
      trans.id_kelompoktani,
      if (length(kt.nama_kelompok) > 20, concat(substring(kt.nama_kelompok,1,17), '...'), kt.nama_kelompok) as nama_kelompok,
      kt.no_kontrak,
      kt.tahun_giling,
      kt.id_afd,
      wil.nama_wilayah,
      date_format(trans.tgl_transaksi, '%d-%m-%Y') as tgl_transaksi,
      ( select
    		SUM(PT.luas) as luas
    	FROM tbl_simtr_kelompoktani kt
    		JOIN tbl_simtr_petani PT on PT.id_kelompok = kt.id_kelompok
    	WHERE EXISTS
    			(SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
    		and kt.id_kelompok = trans.id_kelompoktani
    		group by kt.id_kelompok
    	) as luas,
      sum(trans.kuanta) as netto,
      sum(trans.rupiah) as biaya
      from tbl_simtr_transaksi trans
      join tbl_simtr_kelompoktani kt on trans.id_kelompoktani = kt.id_kelompok
      join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
      join tbl_dokumen dok on dok.id_dokumen = trans.id_pbtma
      where trans.id_pbtma = ?
      group by kt.id_kelompok
    ";
    return json_encode($this->db->query($query, array($id_pbtma))->result());
  }

  public function detailPbp(){
    $id_pbp = $this->input->get("id_pbp");
    $query =
    "
    select
      dok.no_dokumen,
      dok.tgl_buat,
      dok.tgl_validasi_bagian,
      dok.tgl_validasi_kasubbag,
      dok.catatan,
      trans.id_pbma,
      trans.id_kelompoktani,
      if (length(kt.nama_kelompok) > 20, concat(substring(kt.nama_kelompok,1,17), '...'), kt.nama_kelompok) as nama_kelompok,
      kt.no_kontrak,
      kt.tahun_giling,
      kt.id_afd,
      wil.nama_wilayah,
      date_format(trans.tgl_transaksi, '%d-%m-%Y') as tgl_transaksi,
      trans.id_pbp,
      ( select
    		SUM(PT.luas) as luas
    	FROM tbl_simtr_kelompoktani kt
    		JOIN tbl_simtr_petani PT on PT.id_kelompok = kt.id_kelompok
    	WHERE EXISTS
    			(SELECT * FROM tbl_simtr_geocode GEO WHERE GEO.id_petani = PT.id_petani)
    		and kt.id_kelompok = trans.id_kelompoktani
    		group by kt.id_kelompok
    	) as luas,
    	( select sum(rupiah) as rupiah
    		from tbl_simtr_transaksi trans_2
        join tbl_aktivitas akt on akt.id_aktivitas = trans_2.id_aktivitas
    		where trans_2.id_kelompoktani = trans.id_kelompoktani
    		and trans_2.id_aktivitas <> 0 and akt.tunai = 1
    	) as jml_perawatan
    from tbl_simtr_transaksi trans
    join tbl_simtr_kelompoktani kt on trans.id_kelompoktani = kt.id_kelompok
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    join tbl_dokumen dok on dok.id_dokumen = trans.id_pbp
    where trans.id_pbp = ?
    group by wil.nama_wilayah, trans.id_kelompoktani
    ";
    return json_encode($this->db->query($query, array($id_pbp))->result());
  }

  public function getAllPbma(){
    $priv_level = $this->session->userdata("jabatan");
    $id_afd = $this->session->userdata("afd");
    if(is_null($id_afd)){
      $id_afd = "";
    }
    $tahun_giling = $this->input->get("tahun_giling");
    $query =
    "
    select dok.id_dokumen, dok.no_dokumen, dok.tipe_dokumen, date_format(dok.tgl_buat, '%d-%m-%Y %k:%i:%s') as tgl_buat,
      dok.tgl_validasi_bagian, dok.tgl_validasi_kasubbag,
    	sum(trn.rupiah) as total,
      concat(date_format(min(trn.tgl_transaksi), '%d-%M-%Y'), ' s.d ',
        date_format(max(trn.tgl_transaksi), '%d-%M-%Y')) as periode,
      dok.catatan, ? as priv_level
    from tbl_dokumen dok
    join tbl_simtr_transaksi trn on dok.id_dokumen = trn.id_pbma
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    where trn.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%')
    group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd))->result());
  }

  public function getAllPbp(){
    $priv_level = $this->session->userdata("jabatan");
    $id_afd = $this->session->userdata("afd");
    if(is_null($id_afd)){
      $id_afd = "";
    }
    $tahun_giling = $this->input->get("tahun_giling");
    $query =
    "
    select dok.id_dokumen, dok.no_dokumen, dok.tipe_dokumen, date_format(dok.tgl_buat, '%d-%m-%Y %k:%i:%s') as tgl_buat,
      dok.tgl_validasi_bagian, dok.tgl_validasi_kasubbag,
    	sum(trn.rupiah) as total,
      concat(date_format(min(trn.tgl_transaksi), '%d-%M-%Y'), ' s.d ',
        date_format(max(trn.tgl_transaksi), '%d-%M-%Y')) as periode,
      dok.catatan, ? as priv_level
    from tbl_dokumen dok
    join tbl_simtr_transaksi trn on dok.id_dokumen = trn.id_pbp
    join tbl_aktivitas akt on akt.id_aktivitas = trn.id_aktivitas
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    where trn.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%')
    and akt.tunai = 1
    group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd))->result());
  }

  public function getAllPbtma($request){
    if(!is_null($request)){
      $id_afd = $request["afd"];
      $tahun_giling = $request["tahun_giling"];
      $priv_level = $request["priv_level"];
      $query =
      "
      select
        dok.id_dokumen, dok.no_dokumen, dok.tipe_dokumen,
        date_format(dok.tgl_buat, '%d-%m-%Y %k:%i:%s') as tgl_buat,
        dok.tgl_validasi_bagian, dok.tgl_validasi_kasubbag,
        sum(trn.rupiah) as total, sum(trn.kuanta) as netto,
        dok.catatan, ? as priv_level
      from tbl_dokumen dok
      join tbl_simtr_transaksi trn on trn.id_pbtma = dok.id_dokumen
      join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
      where trn.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%')
      group by dok.id_dokumen
      ";
      return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd))->result());
    }
  }

  public function getDesaByIdPbtma($id_pbtma){
    if(!is_null($id_pbtma)){
      $query =
      "
      select
        wil.nama_wilayah
      from tbl_simtr_transaksi trn
      join tbl_dokumen dok on dok.id_dokumen = trn.id_pbtma
      join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
      join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
      where dok.id_dokumen = ?
      group by wil.nama_wilayah
      ";
      return json_encode($this->db->query($query, array($id_pbtma))->result());
    }
  }

  public function getDesaByIdPbma($id_pbma = null){
    if(is_null($id_pbma)){
      $id_pbma = $this->input->get("id_pbma");
    }
    $query =
    "
    select
      wil.nama_wilayah
    from tbl_simtr_transaksi trn
    join tbl_dokumen dok on dok.id_dokumen = trn.id_pbma
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    where dok.id_dokumen = ?
    group by wil.nama_wilayah
    ";
    return json_encode($this->db->query($query, array($id_pbma))->result());
  }

  public function getDesaByIdPbp($id_pbp = null){
    if(is_null($id_pbp)){
      $id_pbma = $this->input->get("id_pbp");
    }
    $query =
    "
    select
      wil.nama_wilayah
    from tbl_simtr_transaksi trn
    join tbl_dokumen dok on dok.id_dokumen = trn.id_pbp
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    join tbl_simtr_wilayah wil on wil.id_wilayah = kt.id_desa
    where dok.id_dokumen = ?
    group by wil.nama_wilayah
    ";
    return json_encode($this->db->query($query, array($id_pbp))->result());
  }

  public function getAllAu58(){
    $priv_level = $this->session->userdata("jabatan");
    $id_afd = $this->session->userdata("afd");
    if(is_null($id_afd)){
      $id_afd = "";
    }
    $tahun_giling = $this->input->get("tahun_giling");
    $query =
    "
    select
      dok.id_dokumen,
      dok.no_dokumen,
      date_format(dok.tgl_buat, '%d-%m-%Y %H:%s') as tgl_buat,
      dok.id_user,
      dok.tgl_validasi_bagian,
      sum(trn.kuanta) as jml_kuanta,
      sum(trn.rupiah) as jml_rupiah,
      trn.tahun_giling,
      kt.id_kelompok,
      kt.no_kontrak,
      kt.nama_kelompok,
      kt.id_afd,
      ? as priv_level,
      trn.no_transaksi
    from tbl_dokumen dok
    join tbl_simtr_transaksi trn on trn.id_au58 = dok.id_dokumen
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    where kt.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%')
    group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd))->result());
  }

  public function getAllPpk(){
    $priv_level = $this->session->userdata("jabatan");
    $id_afd = $this->session->userdata("afd");
    if(is_null($id_afd)){
      $id_afd = "";
    }
    $tahun_giling = $this->input->get("tahun_giling");
    $tgl_awal = $this->input->get("tgl_awal");
    $tgl_akhir = $this->input->get("tgl_akhir");
    $query =
    "
    select
      dok.id_dokumen,
      dok.no_dokumen,
      date_format(dok.tgl_buat, '%d-%m-%Y %H:%s') as tgl_buat,
      dok.id_user,
      dok.tgl_validasi_bagian,
      dok.tgl_validasi_kasubbag,
      sum(trn.kuanta) as jml_kuanta,
      sum(trn.rupiah) as jml_rupiah,
      trn.tahun_giling,
      kt.id_kelompok,
      kt.no_kontrak,
      kt.nama_kelompok,
      kt.id_afd,
      ? as priv_level,
      trn.no_transaksi
    from tbl_dokumen dok
    join tbl_simtr_transaksi trn on trn.id_ppk = dok.id_dokumen
    join tbl_aktivitas akt on akt.id_aktivitas = trn.id_aktivitas
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    where kt.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%') and
    trn.tgl_transaksi >= ? and trn.tgl_transaksi <= date_add(?, interval 1 day)
    and akt.tunai = 1
    group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd, $tgl_awal, $tgl_akhir))->result());
  }

  public function getAllPermohonanBibit(){
    $priv_level = $this->session->userdata("jabatan");
    $id_afd = $this->session->userdata("afd");
    if(is_null($id_afd)){
      $id_afd = "";
    }
    $tahun_giling = $this->input->get("tahun_giling");
    $tgl_awal = $this->input->get("tgl_awal");
    $tgl_akhir = $this->input->get("tgl_akhir");
    $query =
    "
    select
      dok.id_dokumen,
      dok.no_dokumen,
      date_format(dok.tgl_buat, '%d-%m-%Y %H:%s') as tgl_buat,
      dok.id_user,
      dok.tgl_validasi_bagian,
      dok.tgl_validasi_kasubbag,
      sum(trn.kuanta) as jml_kuanta,
      sum(trn.rupiah) as jml_rupiah,
      trn.tahun_giling,
      kt.id_kelompok,
      kt.no_kontrak,
      kt.nama_kelompok,
      kt.id_afd,
      ? as priv_level,
      trn.no_transaksi
    from tbl_dokumen dok
    join tbl_simtr_transaksi trn on trn.id_ppk = dok.id_dokumen
    join tbl_aktivitas akt on akt.id_aktivitas = trn.id_aktivitas
    join tbl_simtr_kelompoktani kt on kt.id_kelompok = trn.id_kelompoktani
    where kt.tahun_giling like concat('%', ?, '%') and kt.id_afd like concat('%', ?, '%') and
    trn.tgl_transaksi >= ? and trn.tgl_transaksi <= date_add(?, interval 1 day)
    and akt.tunai = 0 and akt.jenis_aktivitas = 'BIBIT'
    group by dok.id_dokumen
    ";
    return json_encode($this->db->query($query, array($priv_level, $tahun_giling, $id_afd, $tgl_awal, $tgl_akhir))->result());
  }

}
