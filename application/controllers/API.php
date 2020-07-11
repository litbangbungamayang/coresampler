<?php defined('BASEPATH') OR exit('No direct script access allowed');


  class API extends CI_Controller{

    public function __construct(){
      parent:: __construct();
      $this->load->model("kelompoktani_model");
      $this->load->helper('url');
      $this->load->helper('form');
      $this->load->helper('html');
      $this->load->helper('file');
    }

    public function test(){
      $kelompoktani = $this->kelompoktani_model;
      echo json_encode('MASUK');
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

  }
?>
