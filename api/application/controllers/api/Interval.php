<?php

require APPPATH.'libraries/REST_Controller.php';

class Interval extends REST_Controller{

  public function __construct(){

    parent::__construct();
    //load database
    $this->load->database();
    $this->load->model(array("api/interval_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }
  
public function index_post(){
{
   $email = $this->input->post("email");
if($email==true){
   $staff = $this->interval_model->get_staff($email);
    $this->response(array(
        "message" => "Single Staff Data",
        "data" => $staff
      ), REST_Controller::HTTP_OK);
}else{
   $staff = $this->interval_model->get_all_staff();
     $this->response(array(
        "message" => "All Staff Data",
        "data" => $staff
      ), REST_Controller::HTTP_OK);
}
  
}
    
}
}   


 ?>
