<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Onboarding extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->model('onboarding_model');
    }
    public function index($token = '')
    {
        $this->load->view('onboarding');
    }

    public function staff($token)
    {

        $token_array = $this->db->get_where('tblonboard_tokens', array('token' => $token))->row_array();

        if (!isset($token_array)) {
            echo 'Page has been expired';
            die;
        } elseif ($token_array['expiry_time'] < time()) {
            echo 'Page has been expired';
            die;
        } else {
            $data = $this->db->get_where('tblstaff', array('staffid' => $token_array['staffid']))->row_array();
            $data['token'] = $token;
            $this->load->view('onboarding', $data);
        }
    }

    public function personal_details($token = '')
    {

        $staffid = $this->input->post('id');

        $firstname = $this->input->post('Fname');
        $lastname = $this->input->post('Lname');
        $birthday = $this->input->post('DOB');
        $doj = $this->input->post('DOJ');
        $phonenumber = $this->input->post('mnumber');
        $contact_2 = $this->input->post('tnumber');
        $current_address = $this->input->post('address');
        $city = $this->input->post('city');
        $pin_code = $this->input->post('pin');
        $sex = $this->input->post('sex');
        $marital_status = $this->input->post('martial_status');
        $email = $this->input->post('email');

        $active = 0;
        $datecreated = date('Y-m-d H:i:s');


        $data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'birthday' => $birthday,
            'doj' => $doj,
            'phonenumber' => $phonenumber,
            'contact_2' => $contact_2,
            'current_address' => $current_address,
            'city' => $city,
            'sex' => $sex,
            'marital_status' => $marital_status,
            'pin_code' => $pin_code,
            'active' => $active,
            'datecreated' => $datecreated,
            'personal_email' => $email,
        );

        $check_staff =  $this->db->get_where('tblstaff', ['personal_email' => $email])->row_array();
        if (!$token) {
            if (!$check_staff) {

                // commenting to stop auto incrementing the emp id
                // $last_emp_data = $this->db->query('SELECT * FROM tblstaff ORDER BY staffid DESC LIMIT 1')->row();
                // $empid = $last_emp_data->staff_identifi + 1;
                // $data['staff_identifi'] =  $empid;
                $this->db->insert('tblstaff', $data);
                $staffid = $this->db->insert_id();
                $this->onboarding_model->insert_into_tblshift($staffid);
                $this->onboarding_model->add_shift_mon_to_sat($staffid);
                $generated_token = $this->onboarding_model->generate_token_and_insert($staffid);
                $this->onboarding_model->send_onboarding_email($data, $staffid);


                $this->onboarding_model->add_onboarding_data_into_tblinfo($data, $staffid);


                echo json_encode(array('staffid' => $staffid, 'token' => $generated_token));
                // echo $token ; die;
            } else {
                echo 'used';
            }
        } else {
            // echo $token;die;
            $onboard_data =  $this->db->get_where('tblonboard_tokens', array('token' => $token))->row_array();
            // print_r($onboard_data);die;
            $staffid = $onboard_data['staffid'];
            $added_email = $check_staff['personal_email'];
            $this->db->update('tblstaff', $data, "staffid = $staffid");
            if ($this->db->affected_rows() > 0) {
                echo  json_encode(array('staffid' => $staffid));
            }
        }
    }

    public function official_details()
    {

        // print_r($_FILES);die;
        $staffid = $this->input->post('staffid');
        $data = array(
            'permanent_address' => $this->input->post('permanent_address'),
            'bank_ac_no' => $this->input->post('ac_number'),
            'bank_name' => $this->input->post('bank_name'),
            'bank_address' => $this->input->post('bank_address'),
            'isfc_code' => $this->input->post('IFSC'),
            'relation' => $this->input->post('relation'),
            'referr_type' => $this->input->post('referr_type'),
            'pan' => $this->input->post('pan_number'),
            'blood_group' => $this->input->post('blood'),
            'e_contact' => $this->input->post('emergency_number'),
            'e_person' => $this->input->post('emergency_name')
        );


        if ($_FILES && $_FILES['aadhar']['name']) {
            $data['identification'] = $this->onboarding_model->upload_onboarding_data($staffid, 'aadhar');
        }


        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }

    public function family_members_details()
    {
        // echo 'hello in form 3 controller'; die;
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        $data['child_relation'] = json_encode($data['child_relation']);
        $data['child_name'] = json_encode($data['child_name']);
        $data['child_DOB'] = json_encode($data['child_DOB']);
        $data['child_residing'] = json_encode($data['child_residing']);

        // echo "<pre>";
        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }

    public function qualification_details()
    {
        // echo 'hello in form 3 controller'; die;
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();


        if ($_FILES && $_FILES['_10_marksheet']['name']) {

            $data['_10_marksheet'] = $this->onboarding_model->upload_onboarding_data($staffid, '_10_marksheet');
        }
        if ($_FILES && $_FILES['_12_marksheet']['name']) {

            $data['_12_marksheet'] = $this->onboarding_model->upload_onboarding_data($staffid, '_12_marksheet');
        }


        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }

    public function graduation_details()
    {
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        if ($_FILES && $_FILES['graduation_marksheet']['name']) {

            $data['graduation_marksheet'] = $this->onboarding_model->upload_onboarding_data($staffid, 'graduation_marksheet');
        }

        if ($_FILES && $_FILES['post_graduation_marksheet']['name']) {

            $data['post_graduation_marksheet'] = $this->onboarding_model->upload_onboarding_data($staffid, 'post_graduation_marksheet');
        }

        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }
    public function work_experience_details()
    {
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        if ($_FILES && $_FILES['previous_uploads']['name']) {

            $data['previous_uploads'] = $this->onboarding_model->upload_onboarding_data($staffid, 'previous_uploads');
        }

        if ($_FILES && $_FILES['before_uploads']['name']) {

            $data['before_uploads'] = $this->onboarding_model->upload_onboarding_data($staffid, 'before_uploads');
        }

        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }
    public function declaration_details()
    {
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        if ($_FILES && $_FILES['declaration_signature']['name']) {

            $data['declaration_signature'] = $this->onboarding_model->upload_onboarding_data($staffid, 'declaration_signature');
        }

        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }

    public function background_verification_details()
    {
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            echo $staffid;
        }
    }

    public function agreement_details()
    {
        $staffid = $this->input->post('staffid');
        $data = $this->input->post();

        if ($_FILES && $_FILES['agreement_signature']['name']) {
            $data['agreement_signature'] = $this->onboarding_model->upload_onboarding_data($staffid, 'agreement_signature');
        }

        // print_r($data);die;
        $this->db->update('tblstaff', $data, "staffid = $staffid ");

        if ($this->db->affected_rows() > 0) {
            $this->onboarding_model->create_credientials_for_candidate($staffid);
            $this->onboarding_model->send_onboarding_finished_email($data);
            echo $staffid;
        }
    }
}
