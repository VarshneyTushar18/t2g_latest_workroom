<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Onboarding_model extends App_Model

{
    public function generate_token_and_insert($staffid)
    {
        $token = md5(uniqid(rand(), true));  // Create a unique token
        $expiry_time = time() + (24 * 60 * 60 * 10);

        $data = array(
            'staffid' => $staffid,
            'token' => $token,
            'expiry_time' => $expiry_time
        );
        $this->db->insert('onboard_tokens', $data);
        return $token;
    }

    public function send_onboarding_email($data, $staffid)
    {

        $token_array = $this->db->get_where('tblonboard_tokens', array('staffid' => $staffid))->row_array();
        $this->load->library('email');
        $this->email->set_mailtype("html");
        $this->email->from('noreply@tech2globe.com', 'Tech2globe');
        $this->email->to($data['personal_email']);
        $subject = ' Your Onboarding Process Has Begun - Complete Now!';

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Your Onboarding Process Has Begun</title>
        </head>
        <body>
            <p>Dear ' . $data['firstname'] . ' ' . $data['lastname'] . ',</p>
            <p>We hope this email finds you well. We are thrilled to inform you that your onboarding process at Tech2Globe has officially started! We are excited to welcome you to our team and look forward to working together.</p>

            <p>To complete your onboarding, please click on the following link: ' . base_url('/onboarding/staff/') . $token_array['token'] . '</p>
            
            <p>Please note that the link provided is valid for the next 10 days. If you need to close the onboarding form and return to it later, you can do so, but keep in mind that the link will expire after the 10 days period
            </p>
           
        
            <p><em>Kind Regards,<br>
            Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function upload_onboarding_data($staffid, $name)
    {
        if (!is_dir('uploads/onboarding_data/' . $staffid)) {
            mkdir('uploads/onboarding_data/' . $staffid, 0777, TRUE);
        }

        $config['upload_path'] = 'uploads/onboarding_data/' . $staffid;
        $config['max_size'] = 5000;
        $config['allowed_types'] = 'gif|jpg|png|docx|pdf|jpeg';

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($name)) {
            $error = array('error' => $this->upload->display_errors());
            return $error;
        } else {
            $img_arr = array('upload_data' => $this->upload->data());
            return $img_arr['upload_data']['file_name'];
        }
    }

    public function send_onboarding_finished_email($data)
    {


        $this->db->select('personal_email');

        $to_mail_obj = $this->db->get_where('tblstaff', array('staffid' => $data['staffid']))->row();

        $this->email->set_mailtype("html");
        $this->email->from('noreply@tech2globe.com', 'Tech2globe');
        $this->email->to($to_mail_obj->personal_email);
        $subject = ' Your Onboarding Process is Completed!';

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Your Onboarding Process is Completed</title>
        </head>
        <body>
            <p>Dear ' . $to_mail_obj->firstname . ' ' . $to_mail_obj->lastname . ',</p>
            <p>We hope this email finds you well. We are thrilled to inform you that your onboarding process at Tech2Globe is completed! We are excited to welcome you to our team and look forward to working together.</p>
        
            <p><em>Kind Regards,<br>
            Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function add_onboarding_data_into_tblinfo($data, $staffid)
    {
        $this->db->insert('tblstaff_info', array(
            'empid' => $data['staff_identifi'],
            'doj' =>  date("m/d/Y", strtotime($data['doj'])),
            'staffid' => $staffid
        ));
    }


    public function insert_into_tblshift($staffid)
    {
        // $sql = "SELECT * FROM tblwork_shift ORDER BY date_create LIMIT 1";
        $sql = "SELECT * FROM tblwork_shift WHERE date_create = (SELECT MAX(date_create) FROM tblwork_shift)";

        $latest_record = $this->db->query($sql)->result_array();

        // print_r($latest_record); die; 
        $latest_date = $latest_record[0]['date_create'];
        $staffs_in_shift = $latest_record[0]['staff'] . ',' . $staffid;

        $this->db->set('staff', $staffs_in_shift);
        $this->db->where('date_create', $latest_date);
        $this->db->update('tblwork_shift');
    }

    public function add_shift_mon_to_sat($staffid)
    {
        for ($i = 1; $i <= 6; $i++) {
            $data = array(
                'staff_id' => $staffid,
                'number' => $i,
                'shift_id' => 1,
                'work_shift_id' => 8
            );

            $this->db->insert('tblwork_shift_detail_number_day', $data);

        }
    }

    public function create_credientials_for_candidate($staffid){
        
        $data = $this->db->get_where('tblstaff', array('staffid' => $staffid))->row();

        if ($data && empty($data->email)) {
            $fname = $data->firstname;
            $lname = $data->lastname;
        
            $this->db->select('staffid');
            $this->db->from('tblstaff');
            $this->db->where('firstname', $fname);
            $this->db->where('lastname', $lname);
            $row_count = $this->db->count_all_results();

            if($row_count > 1){
                $email = strtolower($fname).".".strtolower($lname).$row_count."@tech2globe.in";
            }else{
                $email = strtolower($fname).".".strtolower($lname)."@tech2globe.in";
            }

            $password = app_hash_password("tech2globe");

            $this->db->select_max("staff_identifi");
            $query = $this->db->get("tblstaff");
            $empid = $query->row()->staff_identifi;

            $data = [
                "email" => $email,
                "password" => $password,
                "staff_identifi" => $empid+1
            ];

            $this->db->update('tblstaff', $data, "staffid = $staffid ");

            // Snapshot interval: every 10 minutes
            $seconds = 10 * 60;
            $formattedTime = gmdate("H:i:s", $seconds);

            $emtData = [
                'staffid' => $staffid,
                'time_interval' => $formattedTime
            ];

            $this->db->insert('tbltimeinterval', $emtData);
        }
        
    }
}
