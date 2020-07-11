<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Daily_ari extends CI_Controller {

	public function __construct(){
			parent::__construct();
			$this->load->model("dailyari_model");
			$this->load->helper(array('url', 'html'));
			$this->load->library('session');
	}

	public function index()
	{
		if ($this->session->userdata('username') == false){
			redirect('login');
		} else {
      $data['pageTitle'] = 'Rekapitulasi Analisa Harian';
			$data['content'] = $this->loadContent();
			$data['script'] = $this->loadScript();
			$this->load->view('main_view', $data);
		}
	}

	public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Daily_ari.js").'");';
  }

	public function test(){
		echo "Function OK";
	}

	public function getDataDaily(){
		$tglTimbang = $this->input->get("tglTimbang");
		echo $this->dailyari_model->getDataDaily($tglTimbang);
	}

	public function getLaporanAri(){
		$tglTimbang = $this->input->get("tglTimbang");
		$spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
		$objLaporan = json_decode($this->dailyari_model->getLaporanAri($tglTimbang));
		//var_dump($objLaporan);
		$data_ari = (array)$objLaporan;
		$data_kolom = (array)$objLaporan[0];
		for($i = 0; $i < sizeof($data_kolom); $i++){
      $nama_kolom = array_keys($data_kolom);
      $sheet->setCellValueByColumnAndRow($i+1, 1, $nama_kolom[$i]);
    }
		for($baris = 0; $baris < sizeof($data_ari); $baris++){
      for($kolom = 0; $kolom < sizeof($data_kolom); $kolom++){
        $nama_kolom = array_keys($data_kolom);
        $isi = ((array)$data_ari[$baris]);
        $sheet->setCellValueByColumnAndRow($kolom+1,$baris+2,$isi[$nama_kolom[$kolom]]);
      }
    }
		$writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="demo.xlsx"');
    header('Cache-Control: max-age=0');
    header('Expires: Fri, 11 Nov 2011 11:11:11 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    ob_start();
    $writer->save('php://output');
    $xlsData = ob_get_contents();
    ob_end_clean();
    $response =  array(
        'op' => 'ok',
        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
    );
    die(json_encode($response));
	}

	public function loadContent(){
		$content_header =
		'
      <div class="page">
				<div class="row">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-md-12 col-lg-3">
									<div class="form-group">
										<label class="form-label">Tanggal Timbang</label>
										<input autocomplete="off" type="text" class="form-control text-left" placeholder="Tanggal Awal" id="dtpAwal" style=""></input>
	                  <div class="invalid-feedback">Tanggal analisa belum dipilih!</div>
									</div>
								</div>
								<div class="col-md-12 col-lg-2">
									<div class="card">
										<div class="card-status bg-blue"></div>
										<div class="card-body p-3 text-center">
											<div class="text-muted mb-0">TON TEBU</div>
											<div class="h4 m-0" id="ton_tebu">-</div>
										</div>
									</div>
								</div>
								<div class="col-md-12 col-lg-2">
									<div class="card">
										<div class="card-status bg-blue"></div>
										<div class="card-body p-3 text-center">
											<div class="text-muted mb-0">HABLUR ANALISA</div>
											<div class="h4 m-0" id="hablur_analisa">-</div>
										</div>
									</div>
								</div>
								<div class="col-md-12 col-lg-2">
									<div class="card">
										<div class="card-status bg-blue"></div>
										<div class="card-body p-3 text-center">
											<div class="text-muted mb-0">REND. ANALISA</div>
											<div class="h4 m-0" id="rend_analisa">-</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12 col-lg-3">
									<button id="btnProsesData" type="button" class="btn btn-outline-primary">Cek Hasil Analisa</button>
									<button id="btnDownload" type="button" class="btn btn-icon btn-cyan"><i class="fe fe-save"></i></button>
								</div>
							</div>
						</div>
					</div>
				</div>
      </div>
		';
		return $content_header;
	}

}
