<?php defined('BASEPATH') OR exit('No direct access script allowed!');

class Kelompok extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->model("kelompok_model");
        $this->load->library('form_validation');
    }
    
    public function index(){
        $data["kelompok"] = $this->kelompok_model->getAll();
        $this->load->view("admin/kelompok/list", $data);
    }
    
    public function add(){
        $kelompok = $this->kelompok_model;
        $validation = $this->form_validation;
        $validation->set_rules($kelompok->rules());
        if($validation->run()){
            $kelompok->save();
            $this->session->set_flashdata('success', 'Berhasil disimpan!');
        }
        
        $this->load->view("admin/kelompok/new_form");
    }
    
    public function edit($id_kelompok = null){
        if (!isset($id_kelompok)) {
            redirect('admin/kelompok');
        }
        $kelompok = $this->kelompok_model;
        $validation = $this->form_validation;
        $validation->set_rules($kelompok->rules());
        
        if ($validation->run()) {
            $kelompok->update();
            $this->session->set_flashdata('success', 'Berhasil disimpan');
        }

        $data["kelompok"] = $kelompok->getById($id_kelompok);
        if (!$data["kelompok"]) {
            show_404();
        }

        $this->load->view("admin/kelompok/edit_form", $data);
    }
    
    public function delete($id_kelompok =null){
        if (!isset($id_kelompok)) {
            show_404();
        }

        if ($this->kelompok_model->delete($id_kelompok)) {
            redirect(site_url('admin/kelompok'));
        }
    }
}
