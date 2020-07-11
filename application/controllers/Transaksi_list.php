<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Transaksi_list extends CI_Controller{

  var $kelompok;

  public function __construct(){
    parent:: __construct();
    if ($this->session->userdata('id_user') == false) redirect('login');
    $this->load->model("kelompoktani_model");
    $this->load->model("transaksi_model");
    $this->load->model("bahan_model");
    $this->load->library('form_validation');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $this->load->helper('file');
    $this->kelompok = "";
  }

  public function index(){
    $id_kelompok = $this->input->get("id_kelompok");
    $this->kelompok = json_decode($this->kelompoktani_model->getKelompokById($id_kelompok));
    $data["pageTitle"] = "Transaksi Kelompok";
    $data["content"] = $this->loadContent();
    $data["script"] = $this->loadScript();
    $this->load->view("main_view", $data);
  }

  function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Transaksi_list.js").'");';
  }

  public function loadContent(){
    $kategori = "";
    switch ($this->kelompok->kategori){
      case 1 :
        $kategori = "PC";
        break;
      case 2 :
        $kategori = "RT1";
        break;
      case 3 :
        $kategori = "RT2";
        break;
      case 4 :
        $kategori = "RT3";
        break;
    }
    $container =
    '
    <script type="text/javascript">var id_kelompok = '.$this->kelompok->id_kelompok.';</script>
    <div class="page">
      <div class="row">
        <div class="card">
          <div class="card-header">
            KELOMPOK '.$this->kelompok->nama_kelompok.'<br>'.$this->kelompok->no_kontrak.'<br>'.$kategori.' / '.$this->kelompok->mt.'
            <br>'.$this->kelompok->nama_varietas.'<br>'.number_format($this->kelompok->luas, 2, ".", ",").' Ha / Desa '.$this->kelompok->nama_wilayah.'
            <div class="card-options"><a href="#" class="btn btn-primary" onclick="javascript:history.back();" style="margin-right: 10px;"><i class="fe fe-corner-down-left"></i> Kembali </a></div>
          </div>
          <div class="card-body">
            <div class="col-md-12 col-lg-12">
              <div class="card card-collapsed" id="card_transPupuk">
                <div class="card-status bg-green"></div>
                <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                  <div class="card-title">Transaksi Permintaan Pupuk</div>
                  <div class="card-options">
                    <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive col-md-12 col-lg-12">
                    <table id="tblTransPupuk" class="table card-table table-vcenter text-nowrap datatable table-xl" style="width: 100%;">
                      <thead>
                        <tr>
                          <th class="w-1">No.</th>
                          <th>No. Transaksi</th>
                          <th>Tgl. Transaksi</th>
                          <th>Jenis Pupuk</th>
                          <th>Luas</th>
                          <th>Kuanta</th>
                          <th>Rupiah</th>
                          <th>Biaya Muat</th>
                          <th>Biaya Angkut</th>
                          <th>AU58</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-12">
              <div class="card card-collapsed" id="card_transBibit">
                <div class="card-status bg-yellow"></div>
                <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                  <div class="card-title">Transaksi Permintaan Bibit</div>
                  <div class="card-options">
                    <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive col-md-12 col-lg-12">
                    <table id="tblTransBibit" class="table card-table table-vcenter text-nowrap datatable table-xl" style="width: 100%;">
                      <thead>
                        <tr>
                          <th class="w-1">No.</th>
                          <th>No. Transaksi</th>
                          <th>Tgl. Transaksi</th>
                          <th>Uraian</th>
                          <th>Luas Tanam</th>
                          <th>Rupiah</th>
                          <th>Dokumen</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-12">
              <div class="card card-collapsed" id="card_tblTransaksi">
                <div class="card-status bg-blue"></div>
                <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                  <div class="card-title">Transaksi Perawatan Kebun</div>
                  <div class="card-options">
                    <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                  </div>
                </div>
                <div class="card-body">
                  <table id="tblTransPerawatan" class="table card-table table-vcenter text-nowrap datatable table-lg compact" style="width: 100%">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>No. Transaksi</th>
                        <th>Tgl. Transaksi</th>
                        <th>Jenis Aktivitas</th>
                        <th>Kuanta</th>
                        <th>Rupiah</th>
                        <th>Bon Perawatan</th>
                      </tr>
                    </thead>
                    <tfoot class="bg-blue-light">
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
            <div class="col-md-12 col-lg-12">
              <div class="card card-collapsed" id="card_tblTebangan">
                <div class="card-status bg-orange"></div>
                <div class="card-header" data-toggle="card-collapse" style="cursor: pointer">
                  <div class="card-title">Transaksi Tebang-Muat-Angkut</div>
                  <div class="card-options">
                    <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                  </div>
                </div>
                <div class="card-body">
                  <table id="tblTransPerawatan" class="table card-table table-vcenter text-nowrap datatable table-lg" style="width: 100%">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>No. SPTA</th>
                        <th>No. Truk</th>
                        <th>Tgl. Timbang</th>
                        <th>Netto Timbang</th>
                        <th>Rafaksi</th>
                        <th>Kuanta Rafaksi</th>
                        <th>Netto Final</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
    ';

    $footer =
    '
          </div>
        </div>
      </div>
    </div>
    ';

    return $container.$footer;
  }

}

?>
