<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Interview_form_model extends App_Model

{
    public function email_or_phone_exists($email, $phone)
    {
        $this->db->where('email', $email);
        $this->db->or_where('phonenumber', $phone);
        $query = $this->db->get('tblrec_candidate'); // 'users' is the name of your table

        // Return true if a row is found, otherwise false
        return $query->num_rows() > 0;
    }

    public function get_user_by_email_or_phone($email, $phone) {
        $this->db->where('email', $email);
        $this->db->or_where('phonenumber', $phone);
        $query = $this->db->get('tblrec_candidate'); // 'users' is the name of your table
        
        // Return the user record if found, otherwise return null
        return $query->row_array(); // Return as an associative array
    }

    public function send_mail_to_hr_for_new_candidate($data){

        $this->email->set_mailtype("html");
        $this->email->from('noreply_workroom@tech2globe.com', 'T2G Workroom');
       // $this->email->to('hr@tech2globe.com');
		$this->email->to('naved.ahamad1@tech2globe.net');

        $subject = 'A new Candidate has filled the Interview Form';

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Interview Form</title>
        </head>

        <body>
            <p>Hello HR</p>

            <p>'.$data['candidate_name'].' '.$data['last_name'].' has filled the Interview of form on '.date('Y-m-d').'.</p></br></br>


            <p><b>You can check the employee details over :</b> '.admin_url('recruitment/candidate_profile').' </p></br>

            <p><em>Kind Regards,<br>
            T2G Workroom</em></p>

        </body>
        </html>
        ';
		 $this->email->subject($subject);
        $this->email->message($message);
		if ($this->email->send()) {
					echo 'Mail sent';
				} else {
					echo 'Mail not sent';
					echo $this->email->print_debugger();
				}

				$this->email->clear();
       
    }
}
