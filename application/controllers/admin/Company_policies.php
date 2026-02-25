<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Company_policies extends AdminController
{
    public function index()
    {


        // $policy_data = $this->db->get('tblprivacy_policy_documents')->result_array();
        $policy_data = $this->db->query('SELECT * FROM `tblprivacy_policy_documents` ORDER BY `tblprivacy_policy_documents`.`date_created` DESC LIMIT 1')->result_array();
        // print_r($policy_data);
        // die;
        $data['pdf_file'] = $policy_data;
        $this->load->view('admin/privacy_policy', $data);
    }
}
