<?php defined('BASEPATH') OR exit('No direct script access allowed');


  class Rdkk_list extends CI_Controller{

    public function __construct(){
      parent:: __construct();
      if ($this->session->userdata('id_user') == false) redirect('login');
      $this->load->model("kelompoktani_model");
      $this->load->model("transaksi_model");
      $this->load->model("aktivitas_model");
      $this->load->model("bahan_model");
      $this->load->model("dokumen_model");
      $this->load->library('form_validation');
      $this->load->library('upload');
      $this->load->helper('url');
      $this->load->helper('form');
      $this->load->helper('html');
      $this->load->helper('file');
    }

    public function index(){
      if ($this->session->userdata('id_user') == false){
  			redirect('login');
      } else {
        $data['pageTitle'] = "Penelusuran Data Kelompok Tani";
        $data['content'] = $this->loadContent();
        $data['script'] = $this->loadScript();
        $this->load->view('main_view', $data);
      }
    }

    public function test(){
      $kelompoktani = $this->kelompoktani_model;
      //echo $kelompoktani->getAllKelompok();
      echo json_encode("MASUK");
    }

    public function getAllKelompok(){
      $kelompoktani = $this->kelompoktani_model;
      echo $kelompoktani->getAllKelompok();
    }

    public function getKelompokByTahun(){
      echo $this->kelompoktani_model->getKelompokByTahun();
    }

    public function getKelompokById(){
      echo $this->kelompoktani_model->getKelompokById();
    }

    public function getKelompokByKodeBlok(){
      echo $this->kelompoktani_model->getKelompokByKodeBlok();
    }

    public function addTransaksiPupuk(){
      echo $this->transaksi_model->simpan();
    }

    public function getHargaSatuan(){
      echo $this->transaksi_model->getHargaSatuanByIdBahan();
    }

    public function getAktivitasBibit(){
      echo $this->aktivitas_model->getBibit();
    }

    public function getArrayPermintaanPerawatan(){
      $arrayErrorMsg = array();
      $arrayPermintaanPerawatan = json_decode($this->input->post("perawatan"));
      date_default_timezone_set("Asia/Jakarta");
      $no_transaksi = "TR"."-".$arrayPermintaanPerawatan[0]->id_kelompok."-".$arrayPermintaanPerawatan[0]->tahun_giling."-".date("YmdHis");
      $transPostData = array();
      foreach($arrayPermintaanPerawatan as $permintaanPerawatan){
        $postData = array(
          "id_bahan" => $permintaanPerawatan->id_bahan,
          "id_aktivitas" => $permintaanPerawatan->id_aktivitas,
          "id_kelompoktani" => $permintaanPerawatan->id_kelompok,
          "id_vendor" => 0,
          "kode_transaksi" => $permintaanPerawatan->kode_transaksi,
          "kuanta_bahan" => $permintaanPerawatan->kuanta,
          "rupiah_bahan" => $permintaanPerawatan->rupiah,
          "no_transaksi" => $no_transaksi,
          "tahun_giling" => $permintaanPerawatan->tahun_giling,
          "catatan" => NULL
        );
        $aktivitas = json_decode($this->aktivitas_model->getAktivitasById($permintaanPerawatan->id_aktivitas));
        // Cek permintaan bibit atau perawatan -------------
        if ($aktivitas->jenis_aktivitas == "BIBIT"){
          $transaksiYll = json_decode($this->transaksi_model->getTransaksiByIdKelompokJenisAktivitas($permintaanPerawatan->id_kelompok, "BIBIT"))->kuanta;
          $tipe_dokumen = "BBT";
        } else if($aktivitas->jenis_aktivitas == "PERAWATAN"){
          $transaksiYll = json_decode($this->transaksi_model->getTransaksiByIdKelompokIdAktivitas($permintaanPerawatan->id_kelompok, $permintaanPerawatan->id_aktivitas))->kuanta;
          $tipe_dokumen = "PPK";
        }
        // -------------------------------------------------
        $kelompok = json_decode($this->kelompoktani_model->getKelompokById($permintaanPerawatan->id_kelompok));
        if(($transaksiYll + $permintaanPerawatan->kuanta) <= $kelompok->luas){
          $transPostData[] = $postData;
          $msg = "Transaksi perawatan ".$aktivitas->nama_aktivitas." berhasil ditambahkan.";
          $arrayErrorMsg[] = $msg;
        } else {
          $msg =  "Transaksi perawatan ".$aktivitas->nama_aktivitas." tidak dapat dilakukan karena total luas yang diminta melebihi luas terdaftar.\r\n".
          "Permintaan sebelumnya = ".$transaksiYll." Ha, diminta = ".$permintaanPerawatan->kuanta." Ha, luas terdaftar = ".$kelompok->luas." Ha.";
          $arrayErrorMsg[] = $msg;
        }
      }
      if(sizeof($transPostData) > 0){
        $this->db->trans_begin();
        foreach($transPostData as $postData){
          $this->transaksi_model->simpan($postData);
        }
        $id_dokumen = $this->dokumen_model->simpan($tipe_dokumen, "-");
        $this->transaksi_model->updateIdPpk($id_dokumen, $no_transaksi);
        if($this->db->trans_status()){
          $this->db->trans_commit();
        } else {
          $msg =  "Terdapat error transaksi mysql! Method getArrayPermintaanPerawatan.";
          $arrayErrorMsg[] = $msg;
          $this->db->trans_rollback();
        }
      }
      echo json_encode($arrayErrorMsg);
    }

    public function getArrayPermintaanPupuk(){
      $arrayPermintaanPupuk = json_decode($this->input->post("pupuk"));
      date_default_timezone_set('Asia/Jakarta');
      $no_transaksi = "TR"."-".$arrayPermintaanPupuk[0]->id_kelompok."-".$arrayPermintaanPupuk[0]->tahun_giling."-".date("YmdHis");
      $transPostData = array();
      $biayaAngkutData = array();
      $biayaMuatData = array();
      foreach($arrayPermintaanPupuk as $permintaanPupuk){
        $stokBahan = json_decode($this->transaksi_model->cekStokBahanByIdBahan($permintaanPupuk->id_bahan));
        $kelompok = json_decode($this->kelompoktani_model->getKelompokById($permintaanPupuk->id_kelompok));
        $bahan = json_decode($this->bahan_model->getBahanById($permintaanPupuk->id_bahan));
        $postData = array(
          "id_bahan" => $permintaanPupuk->id_bahan,
          "id_aktivitas" => 0,
          "id_kelompoktani" => $permintaanPupuk->id_kelompok,
          "id_vendor" => 0,
          "kode_transaksi" => $permintaanPupuk->kode_transaksi,
          "kuanta_bahan" => $permintaanPupuk->kuanta,
          "rupiah_bahan" => $permintaanPupuk->rupiah,
          "no_transaksi" => $no_transaksi,
          "tahun_giling" => $permintaanPupuk->tahun_giling,
          "catatan" => NULL
        );
        $biayaAngkut = array(
          "id_bahan" => $permintaanPupuk->id_bahan,
          "id_aktivitas" => 0,
          "id_kelompoktani" => $permintaanPupuk->id_kelompok,
          "id_vendor" => 0,
          "kode_transaksi" => $permintaanPupuk->kode_transaksi,
          "kuanta_bahan" => 0,
          "rupiah_bahan" => $permintaanPupuk->kuanta * $bahan->biaya_angkut,
          "no_transaksi" => $no_transaksi,
          "tahun_giling" => $permintaanPupuk->tahun_giling,
          "catatan" => "BIAYA ANGKUT TRANSAKSI NOMOR $no_transaksi"
        );
        $biayaMuat = array(
          "id_bahan" => $permintaanPupuk->id_bahan,
          "id_aktivitas" => 0,
          "id_kelompoktani" => $permintaanPupuk->id_kelompok,
          "id_vendor" => 0,
          "kode_transaksi" => $permintaanPupuk->kode_transaksi,
          "kuanta_bahan" => 0,
          "rupiah_bahan" => $permintaanPupuk->kuanta * $bahan->biaya_muat,
          "no_transaksi" => $no_transaksi,
          "tahun_giling" => $permintaanPupuk->tahun_giling,
          "catatan" => "BIAYA MUAT TRANSAKSI NOMOR $no_transaksi"
        );
        $maksRequest = round(($bahan->dosis_per_ha * $kelompok->luas)/50)*50;
        $transaksiYll = json_decode($this->transaksi_model->getTransaksiByIdKelompokIdBahan($permintaanPupuk->id_kelompok, $permintaanPupuk->id_bahan))->kuanta;
        if ($stokBahan[0]->total_kuanta >= $permintaanPupuk->kuanta){
          if ($permintaanPupuk->kuanta <= ($maksRequest - $transaksiYll)){
              $transPostData[] = $postData;
              $biayaAngkutData[] = $biayaAngkut;
              $biayaMuatData[] = $biayaMuat;
          } else {
            echo "Permintaan pupuk $permintaanPupuk->nama_bahan sudah melebihi dosis yang ditetapkan! \r\nDiminta : ".number_format($permintaanPupuk->kuanta, 0, ".", ",")." ".$permintaanPupuk->satuan
            ."\r\nTransaksi sebelumnya : ".number_format($transaksiYll, 0, ".", ",")." ".$permintaanPupuk->satuan
            ."\r\nBatas permintaan : ".number_format($maksRequest, 0, ".", ",")." ".$permintaanPupuk->satuan;
          }
        } else {
          echo "Stok gudang tidak mencukupi untuk melayani permintaan ini. \r\nStok tersedia untuk bahan ".
          $permintaanPupuk->nama_bahan." sebanyak ".number_format($stokBahan[0]->total_kuanta, 0, ".", ",")." ".$permintaanPupuk->satuan;
        }
      }
      $this->db->trans_begin();
      foreach($transPostData as $postData){
        $this->transaksi_model->simpan($postData);
      }
      foreach($biayaAngkutData as $biayaAngkut){
        $this->transaksi_model->simpan($biayaAngkut);
      }
      foreach($biayaMuatData as $biayaMuat){
        $this->transaksi_model->simpan($biayaMuat);
      }
      $tipe_dokumen = "AU58";
      $id_dokumen = $this->dokumen_model->simpan($tipe_dokumen, "-");
      $this->transaksi_model->updateIdAu58($id_dokumen, $no_transaksi);
      if($this->db->trans_status()){
        $this->db->trans_commit();
        echo "Transaksi berhasil.";
      } else {
        echo "Terdapat error transaksi mysql! Method getArrayPermintaanPupuk.";
        $this->db->trans_rollback();
      }
    }

    function loadScript(){
      return '$.getScript("'.base_url("/assets/app_js/Rdkk_list.js").'");';
    }

    function loadContent(){
      $container =
      '
      <div class="page">
        <div class="row">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="table-responsive col-md-12">
                  <table id="tblList" class="table card-table table-vcenter text-nowrap datatable table-md compact">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Nama Kelompok</th>
                        <th>No. Kontrak</th>
                        <th>Kategori</th>
                        <th>KTG</th>
                        <th>Desa</th>
                        <th>MT</th>
                        <th>Varietas</th>
                        <th>Luas</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="dataPetani">
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      ';
      $content_dialogAddPermintaanPupuk =
      '
      <div class="modal fade" id="dialogAddPermintaanPupuk">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Permintaan Pupuk</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddPermintaanPupuk">
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <div class="card card-collapsed" id="card_tblTransaksi">
                      <div class="card-status bg-green"></div>
                      <div class="card-header">
                        <div>Transaksi yang lalu</div>
                        <div class="card-options">
                          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                        </div>
                      </div>
                      <div class="card-body">
                        <table id="tblPupuk" class="table card-table table-vcenter text-nowrap datatable table-lg" style="width: 100%;">
                          <thead>
                            <tr>
                              <th class="w-1">No.</th>
                              <th>No. Transaksi</th>
                              <th>Tgl. Transaksi</th>
                              <th>Jenis Pupuk</th>
                              <th>Luas</th>
                              <th>Kuanta</th>
                              <th>AU58</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaKelompok" style="margin-top: 25px;">
                      <label class="form-label">Nama Kelompok</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="namaKelompok" disabled>
                    </div>
                    <div class="form-group" id="grJenisPupuk">
                      <label class="form-label">Jenis pupuk yang diminta</label>
                      <select name="jenis_bahan" id="jenis_pupuk" class="custom-control custom-select" placeholder="Pilih jenis pupuk">
                        <option value="">Pilih jenis pupuk</option>
                      </select>
                      <div class="invalid-feedback">Jenis pupuk belum dipilih!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grLuas" style="margin-top: 25px;">
                      <label class="form-label">Luas Area</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="luas" disabled>
                    </div>
                    <div class="form-group" id="grLuasDiminta"">
                      <label class="form-label">Luas aplikasi pupuk</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="luas_aplikasi">
                      <div class="invalid-feedback">Luas aplikasi pupuk belum diisi!</div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div style="margin-bottom: 20px;"><button type="button" id="btnTambahPupuk" class="btn btn-outline-primary btn-sm" > + Tambahkan Permintaan</button></div>
                    <label class="form-label">Draft Permintaan Pupuk</label>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-12" style="margin-bottom: 20px">
                    <table id="tblPermintaanPupuk" class="table card-table table-vcenter text-nowrap datatable table-lg" style="width: 100%;">
                      <thead>
                        <tr>
                          <th class="w-1">No.</th>
                          <th>Jenis Pupuk</th>
                          <th>Luas Aplikasi</th>
                          <th>Kuanta</th>
                          <th>Nilai Rupiah</th>
                          <th></th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                <div class="row">
                    <button type="button" id="btnSimpanPermintaanPupuk" class="btn btn-primary btn-block" name="submit" ><i class="fe fe-save"></i> Ajukan Permintaan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      ';
      $content_dialogAddPerawatan =
      '
      <div class="modal fade" id="dialogAddPerawatan">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Permintaan Perawatan Kebun</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddPermintaanPerawatan">
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <div class="card card-collapsed" id="card_tblPerawatan">
                      <div class="card-status bg-green"></div>
                      <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                        <div>Transaksi yang lalu</div>
                        <div class="card-options">
                          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                        </div>
                      </div>
                      <div class="card-body">
                      <div class="row">
                        <table id="tblPerawatan" class="table card-table table-vcenter text-nowrap datatable table-sm" style="width: 100%;font-size: 95%">
                          <thead>
                            <tr>
                              <th class="w-1">No.</th>
                              <th>No. Transaksi</th>
                              <th>Tgl. Transaksi</th>
                              <th>Jenis Aktivitas</th>
                              <th>Kuanta</th>
                              <th>Rupiah</th>
                              <th>Dok.</th>
                            </tr>
                          </thead>
                          <tfoot class="bg-gray">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><font color="white" size="3">Jumlah s.d.</font></th>
                            <th></th>
                            <th></th>
                            <th></th>
                          </tfoot>
                        </table>
                      </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaKelompok" style="margin-top: 25px;">
                      <label class="form-label">Nama Kelompok</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="perawatan_namaKelompok" disabled>
                    </div>
                    <div class="form-group" id="grJenisAktivitas">
                      <label class="form-label">Perawatan kebun yang diminta</label>
                      <select name="jenis_aktivitas" id="jenis_aktivitas" class="custom-control custom-select" placeholder="Pilih jenis aktivitas">
                        <option value="">Pilih jenis aktivitas</option>
                      </select>
                      <div class="invalid-feedback">Jenis aktivitas belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grLuasDiminta"">
                      <label class="form-label">Luas perawatan yang diminta</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="perawatan_luasDiminta">
                      <div class="invalid-feedback">Luas perawatan belum diisi!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grLuas" style="margin-top: 25px;">
                      <label class="form-label">Luas Area</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="perawatan_luas" disabled>
                    </div>
                    <div class="form-group" id="grBiayaPerHa"">
                      <label class="form-label">Biaya per hektar</label>
                      <input type="text" style="" class="form-control" id="perawatan_biaya" disabled>
                    </div>
                    <div class="form-group" id="grBiayaPerHa"">
                      <label class="form-label">Jumlah biaya</label>
                      <input type="text" style="" class="form-control" id="perawatan_jmlBiaya" disabled>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div style="margin-bottom: 20px;"><button type="button" id="btnTambahPerawatan" class="btn btn-outline-primary btn-sm" > + Tambahkan Permintaan</button></div>
                    <label class="form-label">Draft Permintaan Perawatan Kebun</label>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-12" style="margin-bottom: 20px">
                    <table id="tblPermintaanPerawatan" class="table card-table table-vcenter text-nowrap datatable table-lg" style="width: 100%;">
                      <thead>
                        <tr>
                          <th class="w-1">No.</th>
                          <th>Jenis Aktivitas</th>
                          <th>Luas Aplikasi</th>
                          <th>Biaya per Ha.</th>
                          <th>Jml. Biaya</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th class="w-1"></th>
                          <th></th>
                          <th></th>
                          <th>Total Biaya</th>
                          <th></th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
                <div class="row">
                    <button type="button" id="btnSimpanPermintaanPerawatan" class="btn btn-primary btn-block" name="submit" ><i class="fe fe-save"></i> Ajukan Permintaan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      ';
      $content_dialogAddPermintaanBibit =
      '
      <div class="modal fade" id="dialogAddPermintaanBibit">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Permintaan Bibit</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddPermintaanBibit">
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <div class="card card-collapsed" id="card_tblPerawatan">
                      <div class="card-status bg-green"></div>
                      <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                        <div>Transaksi yang lalu</div>
                        <div class="card-options">
                          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                        </div>
                      </div>
                      <div class="card-body">
                        <table id="tblBibit" class="table card-table table-vcenter text-nowrap datatable table-md" style="width: 100%;">
                          <thead>
                            <tr>
                              <th class="w-1">No.</th>
                              <th>No. Transaksi</th>
                              <th>Tgl. Transaksi</th>
                              <th>Asal Bibit</th>
                              <th>Luas Tanam</th>
                              <th>Rupiah</th>
                              <th>Surat Permintaan</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaKelompok" style="margin-top: 25px;">
                      <label class="form-label">Nama Kelompok</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="namaKelompokBibit" disabled>
                    </div>
                    <div class="form-group" id="grJenisPupuk">
                      <label class="form-label">Asal bibit</label>
                      <select name="asalBibit" id="asalBibit" class="custom-control custom-select" placeholder="Pilih asal bibit">
                        <option value="">Pilih asal bibit</option>
                      </select>
                      <div class="invalid-feedback">Jenis bibit belum dipilih!</div>
                    </div>
                    <div class="form-group" id="grLuasDiminta"">
                      <label class="form-label">Luas tanam yang diminta</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="luasBibitDiminta">
                      <div class="invalid-feedback">Luas tanam belum diisi!</div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grLuas" style="margin-top: 25px;">
                      <label class="form-label">Luas Area</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="luasBakuBibit" disabled>
                    </div>
                    <div class="form-group" id="grVarietas"">
                      <label class="form-label">Varietas Bibit</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="varBibit" disabled>
                      <div class="invalid-feedback">Varietas bibit belum ada!</div>
                    </div>
                    <div class="form-group" id="grBiayaPerHa"">
                      <label class="form-label">Biaya per hektar</label>
                      <input type="text" style="" class="form-control" id="biayaBibit" disabled>
                    </div>
                    <div class="form-group" id="grBiayaPerHa"">
                      <label class="form-label">Total Biaya Bibit</label>
                      <input type="text" style="" class="form-control" id="totalBiayaBibit" disabled>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <button type="button" id="btnSimpanPermintaanBibit" class="btn btn-primary btn-block" name="submit" ><i class="fe fe-save"></i> Ajukan Permintaan Bibit</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      ';
      $content_dialogAddPermintaanTma =
      '
      <div class="modal fade" id="dialogAddPermintaanTma">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Permintaan Biaya TMA</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <form id="formAddPermintaanTma">
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <div class="card card-collapsed" id="card_tblPerawatan">
                      <div class="card-status bg-green"></div>
                      <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                        <div>Tebu masuk yang lalu</div>
                        <div class="card-options">
                          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                        </div>
                      </div>
                      <div class="card-body">
                        <table id="tblBibit" class="table card-table table-vcenter text-nowrap datatable table-md" style="width: 100%;">
                          <thead>
                            <tr>
                              <th class="w-1">No.</th>
                              <th>No. Transaksi</th>
                              <th>Tgl. Transaksi</th>
                              <th>Asal Bibit</th>
                              <th>Luas Tanam</th>
                              <th>Rupiah</th>
                              <th>Surat Permintaan</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <div class="card" id="card_tblPerawatan">
                      <div class="card-status bg-red"></div>
                      <div class="card-header">
                        <div>Tebu masuk belum diverifikasi</div>
                      </div>
                      <div class="card-body">
                        <table id="tblTebuMasukSkrg" class="table card-table table-vcenter text-nowrap datatable table-sm compact" style="width: 100%;">
                          <thead>
                            <tr>
                              <th class="w-1">No.</th>
                              <th>No. SPTA</th>
                              <th>Tgl. Timbang</th>
                              <th>No. Truk</th>
                              <th>Bruto</th>
                              <th>Tarra</th>
                              <th>Netto</th>
                              <th>Rafaksi</th>
                              <th>Berat Setelah Rafaksi</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      ';
      return $container.$content_dialogAddPermintaanPupuk.$content_dialogAddPerawatan.$content_dialogAddPermintaanBibit;
    }

  }
?>
