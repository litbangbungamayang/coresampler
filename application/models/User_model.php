<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

  private $_table = "tb_users";
  public $username;
  public $password;

  public function login_rules(){
    return [
      [
        'field'=>'uname',
        'label'=>'Login Name',
        'rules'=>'required',
        'errors'=> ['required'=>'Username belum diinput']
      ],
      [
        'field'=>'pwd',
        'label'=>'Password',
        'rules'=>'required',
        'errors'=> ['required'=>'Password belum diinput']
      ]
    ];
  }

  public function login($uname, $pwd){
    return $this->db->get_where($this->_table, array('username'=>$uname, 'password'=>$pwd), 1, 0)->row();
  }

}
