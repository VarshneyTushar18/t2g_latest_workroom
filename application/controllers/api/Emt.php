<?php



defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

class Emt extends AdminController
{
   public function __construct(){
         parent::__construct();
         $this->load->model('api/Emt_model'); //Load the Model here   

 }
public function index(){
    //echo APPPATH;die;
//print_r("hello");die;
	//$email = "naved.ahamad@tech2globe.in";
	//$password = "Naved@123";
   $email = $this->input->post("email");
    $password = $this->input->post("password");
    //$this->form_validation->set_rules("email", "Email", "required|valid_email");
  //  $this->form_validation->set_rules("password", "Password", "required");
	print_r($email);
	print_r($password);die;
   $staff = $this->Emt_model->login_check_password($email,$password);
   print_r($staff);die;
   if($staff){
           if(!empty($staff[1]['type_check'])){
                      $this->response(array(
                    "check-in"=>false,
                    "success" => 1,
                    "message" => "Checkout Successfully"
                  ), REST_Controller::HTTP_OK);
                    }elseif($staff[0]['type_check'] && !empty($staff[1]['type_check'])){
                         $this->response(array(
                         "check-in"=>false,
                        "success" => 0,
                        "message" => "Wrong Details"
                      ) , REST_Controller::HTTP_NOT_FOUND); 
                    }else{
                         $this->response(array(
                         "check-in"=>true,
                        "success" => 1,
                        "message" => "Login Successfully"
                      ) , REST_Controller::HTTP_OK);
                    }
               
               }else{
                   $this->response(array(
                        "success" => 1,
                         "message" => "Checkout Successfully"
                      ) , REST_Controller::HTTP_OK);
               }
                
}
    
}


