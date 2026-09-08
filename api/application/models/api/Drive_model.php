<?php

class Drive_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }

 public function insert($data){
       $row = [
         'staff_id' => $data['staff_id'],
         'file_id' => $data['file_id'],
         'directory_id' => $data['directory_id'],
         'parent_directory_id' => isset($data['parent_directory_id']) ? $data['parent_directory_id'] : null,
         'status' => isset($data['status']) ? $data['status'] : 0,
       ];

       if ($row['staff_id'] === null || $row['staff_id'] === '' || $row['file_id'] === null || $row['file_id'] === '' || $row['directory_id'] === null || $row['directory_id'] === '') {
           return 'Missing required fields';
       }

       if($this->db->insert('tblstaff_drive_data', $row))
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
