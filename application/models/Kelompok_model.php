<?php defined('BASEPATH') OR exit('No direct script allowed!');

class Kelompok_model extends CI_Model
{
    private $_table = "tb_kelompok";
    
    public $id_kelompok;
    public $no_kontrak;
    public $nama_kelompok;
    public $ktp;
    
    public function rules(){
        return [
          [
              'field' => 'no_kontrak',
              'label' => 'No. Kontrak',
              'rules' => 'required'
          ],
          [
              'field' => 'nama_kelompok',
              'label' => 'Nama Kelompok',
              'rules' => 'required'
          ],
          [
              'field' => 'ktp',
              'label' => 'No. KTP',
              'rules' => 'required'
          ]
        ];
    }
    
    public function getAll(){
        return $this->db->get($this->_table)->result();
    }
    
    public function getById($id){
        return $this->db->get_where($this->_table, ["id_kelompok" => $id])->row();
    }
    
    public function save(){
        $post = $this->input->post();
        $this->nama_kelompok = $post["nama_kelompok"];
        $this->no_kontrak = $post["no_kontrak"];
        $this->ktp = $post["ktp"];
        $this->db->insert($this->_table, $this);
    }
    
    public function update(){
        $post = $this->input->post();
        $this->id_kelompok = $post["id_kelompok"];
        $this->no_kontrak = $post["no_kontrak"];
        $this->nama_kelompok = $post["nama_kelompok"];
        $this->ktp = $post["ktp"];
        $this->db->update($this->_table, $this, array('id_kelompok' => $post['id_kelompok']));
    }
    
    public function delete($id){
        return $this->db->delete($this->_table, array('id_kelompok' => $id));
    }
}

