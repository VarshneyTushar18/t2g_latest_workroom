<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Birthdays extends App_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Birthday_model');  // Load model from the module
    }

    public function index()
    {
        $this->load->view('birthdays_view');
    }

    // AJAX request to fetch birthdays and anniversaries data
    public function get_data()
    {
        $employees = [];

        // Fetch today's birthdays and anniversaries
        $upcoming_anniversaries = $this->Birthday_model->get_upcoming_anniversaries(7);


        // echo '<pre>';
        // print_r($upcoming_anniversaries);

        foreach ($upcoming_anniversaries as $row) {
            if (($row->staffid)) {
                $img = ($row->sex == 'Female') ? base_url('assets/images/female.png') : base_url('assets/images/male.png');
                $employees[] = [
                    'name' =>  get_staff_full_name($row->staffid), // Assuming your table has a 'name' column
                    'department' => get_job_position_by_staffid($row->staffid), // Assuming a 'department' column exists
                    'emp_id' => get_staff_emp_id($row->staffid),
                    'birthday' => $row->birthday, // The 'birthday' date column
                    'workAnniversary' => $row->doj_combined, // The 'anniversary' date column
                    'img' => $img // Static image for now
                ];
            }
        }

        echo json_encode($employees);
    }
}
