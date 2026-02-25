<?php


defined('BASEPATH') or exit('No direct script access allowed');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

class Hr_recruit_report extends AdminController
{

    public function index()
    {

        // print_r($this->input->post()); die;

        if ($this->input->post()) {
            $from = date("Y-m-d", strtotime($this->input->post('from')));
            $to = date("Y-m-d", strtotime($this->input->post('to')));

            $candidates_data = $this->get_candidates_data($from, $to);
            $this->hr_recruit_email($candidates_data, $from, $to);
        }

        // redirect('/admin/manual_report');
    }

    public function get_hr_data()
    {
        $get_hr_department = "SELECT
            GROUP_CONCAT(departmentid SEPARATOR ', ') AS staffids
        FROM
            `tbldepartments`
        WHERE NAME LIKE
            '%hr%'";
        $hr_department_ids = $this->db->query($get_hr_department)->row()->staffids;


        $get_hr_name_query = "SELECT
            DISTINCT(firstname) as name
        FROM
            `tblstaff`
        LEFT JOIN `tblstaff_departments` ON tblstaff.staffid = tblstaff_departments.staffid
        WHERE
            departmentid IN($hr_department_ids) AND active = 1 AND admin = 0;";


        $hr_names = $this->db->query($get_hr_name_query)->result_array();
        return $hr_names;
    }

    public function get_candidates_data($from, $to)
    {


        $hr_names = $this->get_hr_data();

        // $date = date('Y-m-d' , strtotime("-1 days"));   

        $candidates_data = array();

        foreach ($hr_names as $hr_name) {
            //  $search_candidate_query = "SELECT
            //     tblrec_job_position.position_name,
            //     COUNT(tblrec_candidate.candidate_code) AS candidates
            // FROM
            //     tblrec_job_position
            // LEFT JOIN 
            //     tblrec_campaign 
            //     ON tblrec_job_position.position_id = tblrec_campaign.cp_position
            // LEFT JOIN 
            //     tblrec_candidate 
            //     ON tblrec_candidate.rec_campaign = tblrec_campaign.cp_id
            //     AND tblrec_candidate.candidate_code LIKE '%" . $hr_name['name'] . "%'
            //     AND DATE(tblrec_candidate.DATE_ADD) BETWEEN '" . $from . "' AND '" . $to . "'
            // GROUP BY
            //     tblrec_job_position.position_name
            // ORDER BY 
            //     tblrec_job_position.position_id   
            //     ";
            
            $search_candidate_query = "SELECT
                COUNT(candidate_code) AS candidates,
                MAX(added_from) AS staff_id
            FROM
                tblrec_candidate
            WHERE
                candidate_code LIKE '%" . $hr_name['name'] . "%' AND date_add BETWEEN '" . $from . "' AND '" . $to . "'";

            $candidates_data[$hr_name['name']]  =  $this->db->query($search_candidate_query)->result_array();
        }

        // print_r(array_column($candidates_data,'position_name'));

        // print_r($candidates_data);die;
        return $candidates_data;
    }

    public function hr_recruit_email($candidates_data, $from, $to)
    {

        // $get_job_position_query = "select position_name from tblrec_job_position;";

        // $job_positions = $this->db->query($get_job_position_query)->result_array();


        // print_r($job_positions); die;
        // $date = date('m/d/Y' , strtotime("-1 days"));  

        
        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply_workroom@tech2globe.com', 'Tech2globe');
            // $this->email->to('bhavyakhanna.tech2globe@gmail.com');
            $this->email->to(get_staff_email_id(get_staff_user_id()));
            $this->email->cc(array('megha.anand@tech2globe.com','sarabjeet@tech2globe.com'));
            $subject = "HR Recruitment Report ($from to $to)";

            $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>HR recruit Report</title>
                    <style>
                        table {
                            width: 70%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid black;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #2600bd;
                            color : white;
                        }

                        td {
                            color : black;
                        }
                    </style>
                </head>
                <body>
                <h3>1. Candidate Summary </h3>
                <table>
                    <thead>
                        <tr>
                            <th>S No</th>
							<th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Total count</th>
						</tr>
                    </thead>
                    <tbody>
                ';


            // foreach($job_positions as $job_position){
            //     $message .= '<th>'.$job_position['position_name'].'</th>';
            // }

            $sno = 1;
            foreach ($candidates_data as $hr => $data_per_hr) {
                $message .= '<tr><td>' . $sno . '</td>';
                $count = 0;
                foreach ($data_per_hr as $data) {
                    // $message .= '<td>'.$data['candidates'].'</td>';
                    $message .= '<td>' . get_staff_emp_id($data['staff_id']) . '</td>';

                    $message .= '<td>' . $hr . '</td>';

                    $count += $data['candidates'];
                }
                $message .= '<td>' . $count . '</td></tr>';
                $sno++;
            }




            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'success';
                die;
            } else {
                echo 'fail';
                die;
            }
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }
}
