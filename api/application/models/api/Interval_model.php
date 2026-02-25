<?php

class Interval_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }

  public function get_staff($email){
    
        $this->db->where('tblstaff.email',$email);
        $this->db->select('tblstaff.staffid,tblstaff.email,tbltimeinterval.time_interval');
        $this->db->from('tblstaff');
        $this->db->join('tbltimeinterval', 'tbltimeinterval.staffid = tblstaff.staffid');
        $query = $this->db->get();
        //print_r($query);die;

        return $query->result();
  }

 
  public function get_all_staff(){

        $this->db->select('tblstaff.staffid,tblstaff.email,tbltimeinterval.time_interval');
        $this->db->from('tblstaff');
        $this->db->join('tbltimeinterval', 'tbltimeinterval.staffid = tblstaff.staffid');
        $query = $this->db->get();

    return $query->result();
  }


}
  

 ?>
