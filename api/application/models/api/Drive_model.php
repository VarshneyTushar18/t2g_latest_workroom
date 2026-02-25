<?php

class Drive_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }

 public function insert($data){
       
       $this->staff_id    = $data['staff_id']; // please read the below note
       $this->file_id  = $data['file_id'];
       $this->directory_id = $data['directory_id'];
       $this->parent_directory_id = $data['parent_directory_id'];
       $this->status = $data['status'];
       if($this->db->insert('tblstaff_drive_data',$this))
       {    
           return 'Data is inserted successfully';
       }
         else
       {
           return "Error has occured";
       }
   }
}
  

 ?>
