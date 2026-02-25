<?php

class Staff_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }


  function login_check_password()
  {      

     $password = $_POST['password'];
     $email =    $_POST['email'];
     
     $this->db->where('active', 1);
    $this->db->where('email', $email);
    $query = $this->db->get('tblstaff');
    $row = $query->row();
    $staff_id = $row->staffid;
   // print_r($row);die;
  // $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $email_match = $row->email;
    $date =  date("Y-m-d");
    $hash = $row->password;
        if (password_verify($password, $hash)) {
			//echo "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 200 MINUTE)";die;
             //$this->db->where(date('Y-m-d','date'), $date);
               // $query = "select DATE_FORMAT(`date`,'%Y-%m-%d') AS date ,staff_id,type_check from `tblcheck_in_out` where staff_id = '".$staff_id."' AND DATE_FORMAT(`date`,'%Y-%m-%d') ='".$date."'";
               // $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE)";
				 //$query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE )";
				 $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL '20' HOUR) ORDER by date desc LIMIT 1";
                $query = $this->db->query($query)->result_array(); 
               return $query;
            //return TRUE;
        }
        else {
           return FALSE;
        }
      

    }
}
  

 ?>
