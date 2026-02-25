<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Upload_task_projects extends AdminController
{

    public function __construct()
    {

        parent::__construct();

        $this->load->model('upload_task_projects');
    }



    public function index()
    {

        echo 'helllo';
    }

    public function exportProjectData()
    {

        $project = $this->upload_task_projects->get_project_data();
        print_r($project);die;

        
     
    }
}
