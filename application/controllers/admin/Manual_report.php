<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Manual_report extends AdminController
{

    public function index(){

      
        
        $this->load->view('admin/manual_report');
    }

}
