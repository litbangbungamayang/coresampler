<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Rdkk_add extends CI_Controller{

  public function __construct(){
    parent :: __construct();
    $this->load->model("wilayah_model");
    $this->load->model("kelompoktani_model");
    $this->load->model("masatanam_model");
    $this->load->model("varietas_model");
    $this->load->model("petani_model");
    $this->load->model("geocode_model");
    $this->load->library('form_validation');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->helper('form');
    $this->load->helper('html');
    $arrayPetani = array();
  }

  public function index(){
    if ($this->session->userdata('id_user') == false){
			redirect('login');
    } else {
      if ($this->session->flashdata('error_message') == ''){
        $this->session->set_flashdata('error_div', 'display: none');
      }
      if ($this->session->flashdata('notif_msg') == ''){
        $this->session->set_flashdata('notif_div', 'display: none');
      }
      $data['pageTitle'] = "Pendaftaran RDKK";
      $data['content'] = $this->loadContent();
      $data['script'] = $this->loadScript();
      $this->load->view('main_view', $data);
    }
  }

  public function cekModel(){
    $wilayah = $this->wilayah_model;
    $kelompoktani = $this->kelompoktani_model;
    $petani = $this->petani_model;
    //echo $wilayah->getNamaKabupatenByIdDesa("120106AD");
    echo "<img src='data:image/jpeg;base64,'$kelompoktani->getAll().'>";
  }

  public function getDesaByKabupaten(){
    $wilayah = $this->wilayah_model;
    echo $wilayah->getDesaByKabupaten($this->input->get("idKab"));
  }

  public function getAllKabupaten(){
    $wilayah = $this->wilayah_model;
    echo $wilayah->getAllKabupaten();
  }

  public function getKecByDesa(){
    $wilayah = $this->wilayah_model;
    echo $wilayah->getKecByDesa($this->input->get("idDesa"));
  }

  public function getArrayPetani(){
    $arrayPetani = json_decode($this->input->post("petani"));
    $this->session->set_flashdata("arrayPetani", $arrayPetani);
  }

  public function getDeskripsiDesaByIdKabupaten(){
    echo $this->wilayah_model->getDeskripsiDesaByIdKabupaten($this->input->get("idKab"));
  }

  public function tambahData(){
    $arrayPetani = $this->session->flashdata("arrayPetani");
    //var_dump($arrayPetani[0]->scanKtp);
    /*
    [1]=> object(stdClass)#19 (5) {
    ["id_petani"]=> NULL
    ["id_kelompok"]=> NULL
    ["nama_petani"]=> string(3) "bbb"
    ["luas"]=> float(7.6164900333385)
    ["gps"]=> array(1) {...}
    ["scanKtp"]
    ["scanKk"]
    */
    $MAX_IMAGE_SIZE = 200; //in kB
    $petanimodel = $this->petani_model;
    $kelompoktani = $this->kelompoktani_model;
    $geocode = $this->geocode_model;
    $validation = $this->form_validation;
    $validation->set_rules($kelompoktani->rules());
    if (empty($_FILES["scanKtp"]["name"])) $validation->set_rules("scanKtp", "Scan KTP", "required", ["required"=>"Scan KTP belum ada!"]);
    if (empty($_FILES["scanKk"]["name"])) $validation->set_rules("scanKk", "Scan KK", "required", ["required"=>"Scan KK belum ada!"]);
    if (empty($_FILES["scanSurat"]["name"])) $validation->set_rules("scanSurat", "Scan KTP", "required", ["required"=>"Scan Surat Pernyataan belum ada!"]);
    if ($validation->run() && !empty($arrayPetani)){
      if (!empty($_FILES["scanKtp"]["name"]) && !empty($_FILES["scanKk"]["name"]) && !empty($_FILES["scanSurat"]["name"])) {
        $errSize = '';
        $errType = '';
        foreach($_FILES as $key=>$file){
          $namaFile = '';
          switch ($key){
            case "scanKtp":
              $namaFile = "Scan KTP";
              break;
            case "scanKk":
              $namaFile = "Scan KK";
              break;
            case "scanSurat":
              $namaFile = "Scan Surat Pernyataan";
              break;
          }
          if (($_FILES[$key]["size"]) > $MAX_IMAGE_SIZE*1024){
            //array_push($errArray, "Ukuran file ".$namaFile." tidak sesuai!");
            var_dump(mime_content_type($_FILES[$key]["tmp_name"]));
            $errSize .= "<div> File ".$namaFile." melebihi ukuran file maksimum (200kB)! </div>";
          }
          if (mime_content_type($_FILES[$key]["tmp_name"]) != "image/jpeg"){
            $errType .= "<div> Format ".$namaFile." tidak sesuai! Format diinput : ".mime_content_type($_FILES[$key]["tmp_name"]) ."</div>";
          }
          $this->session->set_flashdata('error_message', $errType.$errSize);
          $this->session->set_flashdata('error_div', '');
        }
        if ($errType.$errSize == ""){
          $this->db->trans_begin();
          $idKelompok = $kelompoktani->simpan();
          foreach($arrayPetani as $petani){
            $idPetani = $petanimodel->simpan($petani, $idKelompok);
            foreach($petani->gps[0] as $poin){
              $geocode->simpan($poin[0], $poin[1], $idPetani);
            }
          }
          if ($this->db->trans_status()){
            $this->db->trans_commit();
            $successMsg = "<div> Data berhasil disimpan! </div>";
            $this->session->set_flashdata('notif_msg', $successMsg);
            $this->session->set_flashdata('notif_div', '');
            redirect('/Rdkk_add');
          } else {
            $this->db->trans_rollback();
          }
        }
      }
    } else {
      if (empty($arrayPetani)){
        $errMsg = "<div> Data petani belum diinput! </div>";
        $this->session->set_flashdata("error_message", $errMsg);
        $this->session->set_flashdata("error_div", "");
      }
    }
    $this->index();
  }

  public function loadContent(){
    $currYear = strval(date("Y"));
    $optionYear = "";
    for ($x = $currYear; $x <= $currYear + 4; $x++){
      $optionYear .= '<option value="'.$x.'">'.$x.'</option>';
    }
    $listMasaTanam = $this->masatanam_model->getAll();
    $listVarietas = $this->varietas_model->getAll();
    $loadListVarietas = '';
    $loadListMasaTanam = '';
    foreach($listMasaTanam as $masaTanam):
      $loadListMasaTanam .= '<option value="'.$masaTanam->masa_tanam.'">'.$masaTanam->masa_tanam.'</option>';
    endforeach;
    foreach($listVarietas as $varietas):
      $loadListVarietas .= '<option value="'.$varietas->id_varietas.'">'.$varietas->nama_varietas.'</option>';
    endforeach;
    $content_header =
    '
      <div class="page">
        <div class="row">
          <div class="card">
            <div style="'.$this->session->flashdata('error_div').'" class="card-body">
                <div class="alert alert-danger">'.$this->session->flashdata('error_message').'</div>
            </div>
            <div class="alert alert-success alert-dismissible" style="'.$this->session->flashdata('notif_div').'">'.$this->session->flashdata('notif_msg').'
              <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
            </div>
            <div class="card-header">
              <h3 class="card-title"> Data Kelompok Tani </h3>
            </div>

    ';
    $content_1 =
    '
            <form action="'.site_url('Rdkk_add/tambahData').'" method="post" enctype="multipart/form-data">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 col-lg-5">
                  <div class="form-group" id="grNamaKelompok">
                    <label class="form-label">Nama Kelompok</label>
                    <input type="text" style="text-transform: uppercase;" class="'.(form_error('namaKelompok') != NULL ? "form-control is-invalid" : "form-control").'" id="namaKelompok" name="namaKelompok" placeholder="Nama Kelompok Tani">
                    <div class="invalid-feedback">'.form_error('namaKelompok').'</div>
                  </div>
                  <div class="form-group" id="grKtp">
                    <label class="form-label">No. KTP</label>
                    <input type="text" style="text-transform: uppercase;" class="'.(form_error('noKtp') != NULL ? "form-control is-invalid" : "form-control").'" id="noKtp" name="noKtp" placeholder="No. KTP">
                    <div class="invalid-feedback">'.form_error('noKtp').'</div>
                  </div>
                  <div class="form-group" id="grKab">
                    <label class="form-label">Kabupaten</label>
                    <select name="namaKab" id="namaKab" class="custom-control custom-select" placeholder="">
                    </select>
                  </div>
                  <div class="form-group" id="grNamaDesa">
                    <label class="form-label">Nama Desa<i id="iconLoading" style="margin-left: 10px" class="fa fa-spinner fa-spin"></i></label>
                    <select name="namaDesa" id="namaDesa" class="custom-control custom-select loading '.(form_error('namaDesa') != NULL ? "is-invalid" : "").'" placeholder=""></select>
                    <div class="invalid-feedback">'.form_error('namaDesa').'</div>
                  </div>
                  <div class="form-group" id="grTahunGiling">
                    <label class="form-label">Tahun Giling</label>
                    <select name="tahun_giling" id="tahun_giling" class="custom-control custom-select '.(form_error('tahun_giling') != NULL ? "is-invalid" : "").'" placeholder="Pilih tahun giling">
                      <option value="">Pilih tahun giling</option>
                      '.$optionYear.'
                    </select>
                    <div class="invalid-feedback">'.form_error('tahun_giling').'</div>
                  </div>
                  <div class="form-group" id="grMasaTanam">
                    <label class="form-label">Masa Tanam</label>
                    <select name="masaTanam" id="masaTanam" class="custom-control custom-select '.(form_error('masaTanam') != NULL ? "is-invalid" : "").'" placeholder="Pilih masa tanam">
                      <option value="">Pilih masa tanam</option>
                      '.$loadListMasaTanam.'
                    </select>
                    <div class="invalid-feedback">'.form_error('masaTanam').'</div>
                  </div>
                  <div class="form-group" id="grVarietas">
                    <label class="form-label">Varietas</label>
                    <select name="varietas" id="varietas" class="custom-control custom-select '.(form_error('varietas') != NULL ? "is-invalid" : "").'" placeholder="Pilih varietas">
                      <option value="">Pilih varietas</option>
                      '.$loadListVarietas.'
                    </select>
                    <div class="invalid-feedback">'.form_error('varietas').'</div>
                  </div>
                </div>

                <div class="col-md-6 col-lg-4">
                  <div class="form-group" id="grKategori">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" id="kategori" class="custom-control custom-select '.(form_error('kategori') != NULL ? "is-invalid" : "").'" placeholder="Pilih kategori">
                      <option value="">Pilih kategori</option>
                      <option value="1">PC</option>
                      <option value="2">Ratoon 1</option>
                      <option value="3">Ratoon 2</option>
                      <option value="4">Ratoon 3</option>
                    </select>
                    <div class="invalid-feedback">'.form_error('kategori').'</div>
                  </div>
                  <div class="form-group" id="grUploadKtp">
                    <div class="form-label">Scan Image KTP Kelompok</div>
                    <div class="custom-file">
                      <input id="scanKtp" accept= ".jpeg,.jpg" type="file" class="custom-file-input '.(form_error('scanKtp') != NULL ? "is-invalid" : "").'" name="scanKtp">
                      <label id="lblScanKtp" class="custom-file-label">Pilih file</label>
                      <div style="" class="invalid-feedback" id="fbScanKtp">'.form_error('scanKtp').'</div>
                    </div>
                  </div>
                  <div class="form-group" id="grUploadKk">
                    <div class="form-label">Scan Image KK</div>
                    <div class="custom-file">
                      <input id="scanKk" type="file" accept=".jpeg,.jpg" class="custom-file-input '.(form_error('scanKk') != NULL ? "is-invalid" : "").'" name="scanKk">
                      <label class="custom-file-label" id="lblScanKk">Pilih file</label>
                      <div style="" class="invalid-feedback" id="fbScanKk">'.form_error('scanKk').'</div>
                    </div>
                  </div>
                  <div class="form-group" id="grUploadPernyataan">
                    <div class="form-label">Scan Image Surat Pernyataan</div>
                    <div class="custom-file">
                      <input id="scanSurat" type="file" accept=".jpeg,.jpg" class="custom-file-input '.(form_error('scanSurat') != NULL ? "is-invalid" : "").'" name="scanSurat">
                      <label class="custom-file-label" id="lblScanSurat">Pilih file</label>
                      <div style="" class="invalid-feedback" id="fbScanSurat">'.form_error('scanSurat').'</div>
                    </div>
                  </div>
                  <div class="form-group" id="grWarning">
                    <div class="alert alert-primary">File yang diterima berupa file .jpeg atau .jpg dengan ukuran <b>maks. 200kB</b></div>
                  </div>
                  <div class="form-group" id="grWarning">
                    <div class="alert alert-danger"><b>Perhatikan penulisan nama kelompok / petani !</b> Tidak diperkenankan menggunakan tanda baca "." (titik) untuk menyingkat nama.</div>
                  </div>
                </div>
              </div>
            </div>
    ';
    $content_2 =
    '
            <div class="card-header">
              <h3 class="card-title"> Data Petani </h3>
            </div>
            <div class="card-body">
              <div class="row" style="margin-bottom: 10px; margin-left: 0px">
                <button type="button" id="btnTambahPetani" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#dialogAddPetani"> + Tambah Petani</button>
              </div>
              <div class="row"></div>
              <div class="row">
                <div class="table-responsive col-md-6">
                  <table id="tblPetani" class="table card-table table-vcenter text-nowrap datatable table-sm">
                    <thead>
                      <tr>
                        <th class="w-1">No.</th>
                        <th>Nama Petani</th>
                        <th>Luas Areal</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="dataPetani">
                    </tbody>
                    <tfoot id="footerPetani">
                      <tr>
                        <th class="w-1"></th>
                        <th>Total luas</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
    ';
    $content_footer =
    '
            <div class="card-footer text-right">
              <div class="d-flex">
                <button type="submit" id="btnSimpan" class="btn btn-primary ml-auto" onclick="" name="dataKelompok">Simpan data</button>
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    $content_dialogAddPetani =
    '
      <div class="modal fade" id="dialogAddPetani">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Tambah data petani</h4>
              <button class="close" data-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-danger" id="errMsg">
              </div>
              <form id="formAddPetani" action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grNamaPetani">
                      <label class="form-label">Nama Petani</label>
                      <input type="text" style="text-transform: uppercase;" class="form-control" id="namaPetani" name="namaPetani" placeholder="Nama Petani">
                      <div class="invalid-feedback" id="fbNamaPetani">Nama petani belum diinput!</div>
                    </div>
                    <div class="form-group" id="grUploadPeta">
                      <div class="form-label">File GPX area kebun</div>
                      <div class="custom-file">
                        <input type="file" accept=".gpx" class="custom-file-input" name="fileGpxKebun" id="fileGpxKebun">
                        <label class="custom-file-label" id="lblFileGpxKebun" name="lblFileGpxKebun">Pilih file</label>
                        <div class="invalid-feedback" id="fbFileGpx"></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-6">
                    <div class="form-group" id="grUploadKtpPetani">
                      <div class="form-label">Scan Image KTP Petani</div>
                      <div class="custom-file">
                        <input id="scanKtpPetani" accept= ".jpeg,.jpg" type="file" class="custom-file-input '.(form_error('scanKtpPetani') != NULL ? "is-invalid" : "").'" name="scanKtpPetani">
                        <label id="lblScanKtpPetani" class="custom-file-label">Pilih file</label>
                        <div style="" class="invalid-feedback" id="fbScanKtpPetani">'.form_error('scanKtpPetani').'</div>
                      </div>
                    </div>
                    <div class="form-group" id="grUploadKkPetani">
                      <div class="form-label">Scan Image KK Petani</div>
                      <div class="custom-file">
                        <input id="scanKkPetani" accept= ".jpeg,.jpg" type="file" class="custom-file-input '.(form_error('scanKkPetani') != NULL ? "is-invalid" : "").'" name="scanKkPetani">
                        <label id="lblScanKkPetani" class="custom-file-label">Pilih file</label>
                        <div style="" class="invalid-feedback" id="fbScanKkPetani">'.form_error('scanKkPetani').'</div>
                      </div>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnSimpanPetani" class="btn btn-primary btn-block" name="submit" >Simpan data petani</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    ';
    return $content_header.$content_1.$content_2.$content_footer.$content_dialogAddPetani;
  }

  public function readGpxValue(){
    include('./assets/geoPHP/geoPHP.inc');
    $uploadedData = $_FILES['gpx']['tmp_name'];
    var_dump($uploadedData);
    $gpx = simplexml_load_file($uploadedData);
    //var_dump($gpx);
    $gpxValue = file_get_contents($uploadedData);
    $geometry = geoPHP::load($gpxValue,'gpx');
    var_dump($geometry->area());
    echo json_encode("output OK");
  }

  public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Rdkk_add.js").'");';
  }
}
