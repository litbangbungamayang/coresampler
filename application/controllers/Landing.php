<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Landing extends CI_Controller {

	public function __construct(){
			parent::__construct();
			$this->load->model("dashboard_model");
			$this->load->helper(array('url', 'html'));
			$this->load->library('session');
	}

	public function index()
	{
		if ($this->session->userdata('username') == false){
			redirect('login');
		} else {
			$data['content'] = $this->loadContent();
			$data['script'] = $this->loadScript();
			$this->load->view('main_view', $data);
		}
	}

	public function loadScript(){
    return '$.getScript("'.base_url("/assets/app_js/Landing.js").'");';
  }

	public function loadContent(){
		$content_header =
		'
		';
		return $content_header;
	}

	function logout(){
		$data = array('login'=>'','uname'=>'','uid'=>'');
		$this->session->unset_userdata($data);
		$this->session->sess_destroy();
		redirect('/');
	}

}
