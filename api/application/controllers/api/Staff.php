<?php

require APPPATH.'libraries/REST_Controller.php';

class Staff extends REST_Controller{

  public function __construct(){

    parent::__construct();
    //load database
    $this->load->database();
    $this->load->model(array("api/staff_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }
  
public function index_post(){
{
    //echo APPPATH;die;
    $email = $this->security->xss_clean($this->input->post("email"));
    $password = $this->security->xss_clean($this->input->post("password"));
    
    $this->form_validation->set_rules("email", "Email", "required|valid_email");
    $this->form_validation->set_rules("password", "Password", "required");
    
   $staff = $this->staff_model->login_check_password($email,$password);
  // print_r($staff);die;
   if ($staff === false) {
       $this->response(array(
           "check-in" => false,
           "success" => 0,
           "message" => "Wrong email or password"
       ), REST_Controller::HTTP_NOT_FOUND);
       return;
   }

   if (empty($staff)) {
       $this->response(array(
           "check-in" => false,
           "success" => 1,
           "message" => "Please check in on T2G Workroom or biometric device first."
       ), REST_Controller::HTTP_OK);
       return;
   }

   if (!empty($staff[1]['type_check'])) {
       $this->response(array(
           "check-in" => false,
           "success" => 1,
           "message" => "Checkout Successfully"
       ), REST_Controller::HTTP_OK);
       return;
   }

   if (!empty($staff[0]['type_check']) && !empty($staff[1]['type_check'])) {
       $this->response(array(
           "check-in" => false,
           "success" => 0,
           "message" => "Wrong Details"
       ), REST_Controller::HTTP_NOT_FOUND);
       return;
   }

   $this->response(array(
       "check-in" => true,
       "success" => 1,
       "message" => "Login Successfully"
   ), REST_Controller::HTTP_OK);
                
    
 /*  if($staff == TRUE){
 
        $this->response(array(
            "check-in"=>false,
            "success" => 1,
            "message" => "Login Successfully"
          ), REST_Controller::HTTP_OK);
   }else{
        $this->response(array(
        "success" => 0,
        "message" => "Wrong Details"
      ) , REST_Controller::HTTP_NOT_FOUND); 
       
   }*/
}
    
}
    

 
  // GET: <project_url>/index.php/student
  public function index_get($email){
    // list data method
    //echo "This is GET Method";
  // $email =  $this->input->get("email");
   $email = $this->security->xss_clean($this->input->get("email"));
   print_r($email);die;
   $query = ["email","all"];
    if($query == "email"){
         echo 'emaildddd';
         $staff = $this->staff_model->get_staff();
    }else{
        echo 'all';
         $staff = $this->staff_model->get_all_staff();
    }
    die;
    if(count($staff) > 0){

      $this->response(array(
        "active" => 1,
        "message" => "Staff found",
        "data" => $staff
      ), REST_Controller::HTTP_OK);
    }else{

      $this->response(array(
        "active" => 0,
        "message" => "No Staff found",
        "data" => $staff
      ), REST_Controller::HTTP_NOT_FOUND);
    }



  }
}

 ?>
