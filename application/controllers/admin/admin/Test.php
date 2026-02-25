<?php


defined('BASEPATH') or exit('No direct script access allowed');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// require_once 'google-client/vendor/autoload.php';

class Test extends AdminController
{



    private $client;
    private $service;
    public function __construct()
    {

        parent::__construct();

        $this->load->model('birthday_model');
        // $this->client = new Google_Client();
        // $this->client->setAuthConfig('google-client/hr-recruit-cv-429106-f3626e643c56.json');
        // $this->client->addScope(Google_Service_Drive::DRIVE);
        // $this->service = new Google_Service_Drive($this->client);
    }
    public function index()
    {


        echo '<pre>';

        // print_r(get_all_managers());die;

        $less_than_9_hrs_data = $this->less_than_9_hrs_monthly_summary();
        $working_hours_summary = $this->working_hours_summary();

        $this->daily_report_email($working_hours_summary,$less_than_9_hrs_data);die;

        $managers_id = array_column(get_all_managers(),'staffid');
        $staffs = array();

        // $date = Date('Y-m-d');
        $date = '2024-12-26' ;

        foreach($managers_id as $manager_id){
            $get_managers_team_members = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id and active = 1";
            $staffs_under_manager = $this->db->query($get_managers_team_members)->result_array();

            $subject = get_staff_full_name($manager_id) . ', Staff Timesheet report : ' . $date;
            // print_r($staffs_under_manager);
            foreach($staffs_under_manager as $staffid){
                $staffid = $staffid['staffid'];
                $project_task_query = "SELECT tblprojects.name as project_name,tblprojects.description as project_description,tbltasks.name as task_name,tbltasks.description as task_description,FROM_UNIXTIME(start_time,'%Y-%m-%d %H:%i:%s') as start_time,FROM_UNIXTIME(end_time,'%Y-%m-%d %H:%i:%s') as end_time, FROM_UNIXTIME(start_time,'%Y-%m-%d') as currdate,FROM_UNIXTIME(start_time,'%H:%i:%s') as start_time,FROM_UNIXTIME(end_time,'%H:%i:%s') as end_time,tblstaff.firstname,tblstaff.lastname,tblstaff.staff_identifi FROM `tblprojects` LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid where FROM_UNIXTIME(start_time,'%Y-%m-%d') = '2024-12-19' AND tbltaskstimers.staff_id=$staffid;";

                $staffs[$manager_id][$staffid]=$this->db->query($project_task_query)->result_array();

                $totalSeconds = 0;
                foreach($staffs[$manager_id][$staffid] as $task){
                    $start = strtotime($task['start_time']);
                    $end = strtotime($task['end_time']);
                    $totalSeconds += ($end - $start);

                
                }

                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $staffs[$manager_id][$staffid]['total_time'] = [
                    'hours' => $hours,
                    'minutes' => $minutes
                ];
            }

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
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
            ';


            
            foreach ($staffs[$manager_id] as $staff_id => $staff_datas) {
                

                $message .= '
                    
    
                <h2>'.get_staff_full_name($staff_id). ' - '. get_staff_emp_id($staff_id) .'</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Staff member</th>
                            <th>Task</th>
                            <th>Project name</th>
                            <th>Start time</th>
                            <th>End time</th>
    
                        </tr>
                    </thead>
                <tbody>
                ';
                foreach($staff_datas as $arr_key => $staff_data){

                    if(is_int($arr_key)){

                        if(isset($staff_data['task_name'])){
                            $message .= "<tr>
                            <td>" . get_staff_full_name($staff_id) . "</td>
                            <td>" . $staff_data['task_name'] . "</td>
                            <td>" . $staff_data['project_name'] . "</td>
                            <td>" . $staff_data['start_time'] . "</td>
                            <td>" . $staff_data['end_time'] . "</td>
            
                            </tr>";

                        } 

                    } else{
                        $message .= "<tr>
                            <td> Total time </td>
                            <td>" . $staff_data['hours'] . ' hours and ' .$staff_data['minutes']. " minutes </td>
            
                            </tr>";
                    }

                }
                $message .= "</tbody></table>";
            }

            $message .= "</body></html>";


            $this->email->to('niraj.lal.rahi@outlook.com');
            $this->email->cc(array('ishita.rathi@tech2globe.in'));
            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent';
                $arr = $this->email->print_debugger();
                print_r($arr);
            }
            $this->email->clear();


        }
        print_r($staffs);
        die;

    }

    public function attendance_data(){
        $query = 'SELECT tblprojects.name as project_name,tblprojects.description as project_description,tbltasks.name as task_name,tbltasks.description as task_description,FROM_UNIXTIME(start_time,"%Y-%m-%d %H:%i:%s") as start_time,FROM_UNIXTIME(end_time,"%Y-%m-%d %H:%i:%s") as end_time, FROM_UNIXTIME(start_time,"%Y-%m-%d") as currdate,FROM_UNIXTIME(start_time," %H:%i:%s") as start_time,FROM_UNIXTIME(end_time," %H:%i:%s") as end_time,tblstaff.firstname,tblstaff.lastname,tblstaff.staff_identifi FROM `tblprojects` LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid where FROM_UNIXTIME(start_time,"%Y-%m-%d") = "2023-05-25" AND tbltaskstimers.staff_id=21;';
        $data = $this->db->query($query)->row_array();
        echo '<pre>';
        print_r($data);die;
    }

    public function previous_week_dates()
    {

        // Get today's date
        $today = new DateTime();

        // Calculate the start date (Monday) of the previous week
        $startDate = clone $today;
        $startDate->modify('monday last week');

        // Calculate the end date (Sunday) of the previous week
        $endDate = clone $today;
        $endDate->modify('sunday last week');

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->modify('+1 day')); // Increment by 1 day

        $lastWeekDates = [];
        foreach ($period as $date) {
            $lastWeekDates[] = $date->format('Y-m-d'); // Format date as needed
        }

        return $lastWeekDates;
    }

    public function check_in_out_details()
    {
        $dates = $this->previous_week_dates();
        $managers = get_all_managers();
        $data = array();
        $format = 'Y-m-d H:i:s';
        foreach ($managers as $manager) {
            $id = $manager['staffid'];
            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');



            $staff_data = array();
            foreach ($team_staffids as $team_staffid) {

                foreach ($dates as $date) {

                    $query1 = "SELECT * FROM tblcheck_in_out where date(date) = '$date' and staff_id = $team_staffid and type_check = 1";
                    $check_in_data = $this->db->query($query1)->row_array();
                    $is_night_shift = is_night_shift($team_staffid); // Custom function to determine shift type

                    if ($is_night_shift) {
                        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
                        $query2 = "SELECT * FROM tblcheck_in_out WHERE DATE(date) = '$next_date' AND staff_id = $team_staffid AND type_check = 2";
                    } else {
                        $query2 = "SELECT * FROM tblcheck_in_out WHERE DATE(date) = '$date' AND staff_id = $team_staffid AND type_check = 2";
                    }

                    $check_out_data = $this->db->query($query2)->row_array();

                    if (
                        (!empty($check_in_data) && isset($check_in_data['date']) && $check_in_data['date']) ||
                        (!empty($check_out_data) && isset($check_out_data['date']) && $check_out_data['date'])
                    ) {

                        $formatted_date1 = null;
                        $formatted_date2 = null;

                        if (!empty($check_in_data) && isset($check_in_data['date']) && $check_in_data['date']) {
                            // Code to handle when check-in date is present
                            $datetime1 = $check_in_data['date'];
                            $formatted_date1 = DateTime::createFromFormat($format, $datetime1);
                        }
                        if (!empty($check_out_data) && isset($check_out_data['date']) && $check_out_data['date']) {
                            // Code to handle when check-out date is present
                            $datetime2 = $check_out_data['date'];
                            $formatted_date2 = DateTime::createFromFormat($format, $datetime2);
                        }

                        if ($formatted_date1 && $formatted_date2) {
                            $interval = $formatted_date1->diff($formatted_date2);
                            $hours_diff =  $interval->format('%h');
                            $minutes_diff = $interval->format('%i');

                            $time_diff = sprintf('%02d:%02d', $hours_diff, $minutes_diff);
                            $check_in_time = $formatted_date1->format('h:i:s A');
                            $check_out_time = $formatted_date2->format('h:i:s A');

                            $staff_data[$team_staffid][$date]['check_in'] = $check_in_time;
                            $staff_data[$team_staffid][$date]['check_out'] = $check_out_time;
                            $staff_data[$team_staffid][$date]['working_hrs'] = $time_diff;
                        } else {
                            // Debugging output if format parsing fails
                            if (!$formatted_date1) {
                                $staff_data[$team_staffid][$date]['check_in'] = '';
                            } else {
                                $check_in_time = $formatted_date1->format('h:i:s A');
                                $staff_data[$team_staffid][$date]['check_in'] = $check_in_time;
                            }
                            if (!$formatted_date2) {
                                $staff_data[$team_staffid][$date]['check_out'] = '';
                            } else {
                                $check_out_time = $formatted_date2->format('h:i:s A');
                                $staff_data[$team_staffid][$date]['check_out'] = $check_out_time;
                            }
                            $staff_data[$team_staffid][$date]['working_hrs'] = '';
                        }
                    }
                }
            }

            $data[$id] = $staff_data;
        }

        print_r($data);
        // die;
        return $data;
    }


    public function send_weekly_attendance_mail($data)
    {
        $this->email->set_mailtype("html");
        $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
        $dates = $this->previous_week_dates();
        foreach ($data as $manager_id => $manager_data) {
            $this->email->to('bhavyakhanna.tech2globe@gmail.com');
            // $this->email->to('sarabjeet@tech2globe.net');
            // $this->email->cc(array('ishan.negi@tech2globe.in'));

            // $this->email->cc('naved.ahamad@tech2globe.in');

            // $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "T2GWorkroom - Staff weekly attendance report : from " . reset($dates) . " to " . end($dates);

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
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
            ';




            $message .= '
                

            <h2>Weekly Report</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>';

            foreach ($dates as $date) {
                $message .= '<th>(' . $date . ') ' . date('D', strtotime($date)) . '</th>';
            }

            $message .=        '</tr>
                </thead>
            <tbody>
            ';
            foreach ($manager_data as $staff_id => $staff_data) {
                $message .= '<tr><td>' . get_staff_full_name($staff_id) . '-' . get_staff_emp_id($staff_id) . '</td>';
                foreach ($dates as $date) {
                    if (isset($staff_data[$date])) {
                        if (date('D', strtotime($date)) == "Sun") {
                            $message .= '<td>HO</td>';
                        } else {
                            $message .= '<td>Punch in - ' . $staff_data[$date]['check_in'] . '<br/> Punch out - ' . $staff_data[$date]['check_out'] . ' <br/><b>Shift hrs - <b>' . $staff_data[$date]['working_hrs'] . '</td>';
                        }
                    } else {
                        if (date('D', strtotime($date)) == "Sun") {
                            $message .= '<td>HO</td>';
                        } else {
                            $status = $this->get_attendance_status($staff_id, $date);
                            $message .= '<td>' . $status . '</td>';
                        }
                    }
                }
                $message .= '</tr>';
            }
            $message .= "</tbody></table></body></html>";

            // echo '<pre>';
            print_r($message);
            // die;
            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent';
            }

            $this->email->clear();

            // die;
            // Optional: Add a delay between each email to avoid server throttling or spam flags
            // sleep(1); // Wait 1 second before sending the next email

        }
    }

    public function get_attendance_status($staff_id, $date)
    {
        $status = $this->db->query("SELECT type FROM tbltimesheets_timesheet WHERE staff_id = $staff_id and date_work = '$date'")->row_array();
        return $status['type'];
    }
    public function is_mobile()
    {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $mobile_agents = array('iphone', 'android', 'blackberry', 'ipod', 'opera mini', 'windows phone', 'mobile');

        foreach ($mobile_agents as $device) {
            if (strpos($user_agent, $device) !== false) {
                return true;
            }
        }

        return false;
    }


    public function is_staff_present($staffid)
    {
        // getting previous date for testing purposes
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT * FROM `tblcheck_in_out` WHERE DATE(date) = '$date' and staff_id = $staffid and type_check = 1";

        // echo $query;

        $today_present_data = $this->db->query($query)->result_array();

        // return  $today_present_data;

        if (!empty($today_present_data)) {

            return 1;

            // if ($today_present_data[0]['type'] == 'AB') {
            //     return 0;
            // } else {
            //     return 1;
            // }
        }

        return 0;
    }
    public function get_staff_working_hrs($staffid)
    {
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT * FROM tbltimesheets_timesheet WHERE staff_id = $staffid AND date_work = '$date'";

        $today_present_data = $this->db->query($query)->result_array();

        if ($today_present_data) {
            // return $today_present_data[0]['value'] ?? 0;
            if ($today_present_data[0]['value']) {
                return $today_present_data[0]['value'];
            } else {
                return null;
            }
        } else {
            return 0;
        }
    }

    public function check_working_hrs($hrs, &$nine_plus, &$five_to_nine, &$zero_to_five)
    {

        switch (true) {
            case $hrs <= 5 && $hrs > 0:
                $zero_to_five++;
                break;
            case $hrs > 5 && $hrs < 9:
                $five_to_nine++;
                break;
            case $hrs >= 9:
                $nine_plus++;
                break;
        }
    }

    public function managers_list()
    {
        $query = "SELECT team_manage from tblstaff where active = 1";
        $data = $this->db->query($query)->result_array();
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $managers = [];

        foreach ($manager_ids as $manager_id) {

            if (check_staff_active($manager_id)) {
                $managers[$manager_id] = get_staff_full_name($manager_id);
            }
        }

        return $managers;
    }

    public function working_hours_summary()
    {

        // $query = "SELECT * FROM tblstaff LEFT JOIN tblstaff_info ON tblstaff.staffid = tblstaff_info.staffid where manageLeave = 1";

        // $data = $this->db->query($query)->result_array();

        $data = $this->managers_list();

        echo '<pre>';

        $working_hours_summary = array();

        foreach ($data as $manager_id => $manager_name) {


            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];
            $nine_plus = 0;
            $five_to_nine = 0;
            $zero_to_five = 0;
            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid]['attendance'] = ($team_staffid == 1 || $team_staffid == 144) ? 1 : $this->is_staff_present($team_staffid);
                $working_hrs = $this->get_staff_working_hrs($team_staffid);
                $staff_data[$team_staffid]['working_hrs'] = $working_hrs;
                $this->check_working_hrs($working_hrs, $nine_plus, $five_to_nine, $zero_to_five);
            }
            $working_hours_summary[$manager_id]['team_data'] = $staff_data;

            $working_hours_summary[$manager_id]['date'] = date('Y-m-d', strtotime("-1 days"));
            $working_hours_summary[$manager_id]['team_count'] = count($staff_data);
            $present = array_sum(array_column($staff_data, 'attendance'));
            $working_hours_summary[$manager_id]['present'] = $present;
            $working_hours_summary[$manager_id]['absent'] = $working_hours_summary[$manager_id]['team_count'] - $present;
            $occupancy = $working_hours_summary[$manager_id]['team_count'] ? ($working_hours_summary[$manager_id]['present'] / $working_hours_summary[$manager_id]['team_count']) * 100 : 0;
            $working_hours_summary[$manager_id]['occupancy'] = round($occupancy, 2);
            $working_hours_summary[$manager_id]['nine_plus'] = $nine_plus;
            $working_hours_summary[$manager_id]['five_to_nine'] = $five_to_nine;
            $working_hours_summary[$manager_id]['zero_to_five'] = $zero_to_five;
        }



        // print_r($working_hours_summary); die;
        return $working_hours_summary;
    }
    public function daily_report_email($working_hrs_summary, $less_than_nine_summary)
    {

        echo "<pre> working hours - \n";
        print_r($working_hrs_summary);
        echo "\nabsent data - \n";

        print_r($less_than_nine_summary);

        $date = date('m/d/Y', strtotime("-1 days"));

        try {

            $this->email->set_mailtype("html");
            $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
            $this->email->to('bhavyakhanna.tech2globe@gmail.com');
            // $this->email->to('sarabjeet@tech2globe.net');
            // $this->email->cc(array('ishan.negi@tech2globe.in'));

            $this->email->cc('naved.ahamad@tech2globe.in');

            // $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Daily Report - T2GWorkroom - $date ";

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
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
            ';


            // $count = 1;
            // foreach ($working_hrs_summary as $manager_id => $manager_data) {
            //     $message .= "<tr>
            //     <td>" . $count++ . "</td>
            //     <td>" . get_staff_full_name($manager_id) . "</td>
            //     <td>" . $manager_data['team_count'] . "</td>
            //     <td>" . $manager_data['occupancy'] . "%</td>
            //     </tr>";
            // }



            $message .= '
                

            <h2>Today\'s Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Cluster</th>
                        <th>Team Count</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Occupancy</th>

                    </tr>
                </thead>
            <tbody>
            ';

            $count = 1;
            foreach ($working_hrs_summary as $manager_id => $manager_data) {
                $message .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . get_staff_full_name($manager_id) . "</td>
                <td>" . $manager_data['team_count'] . "</td>
                <td>" . $manager_data['present'] . "</td>
                <td>" . $manager_data['absent'] . "</td>
                <td>" . $manager_data['occupancy'] . "%</td>

                </tr>";
            }


            // $message .= ' </tbody> </table>
            // <h2>Working Hours Summary</h2>
            // <table>
            //         <thead>
            //             <tr>
            //                 <th>S.No.</th>
            //                 <th>Cluster</th>
            //                 <th>Team Count</th>
            //                 <th>9+ hours</th>
            //                 <th>5 to 9 hours</th> 
            //                 <th>0 to 5 hours</th> 
            //             </tr>
            //         </thead>
            //     <tbody>';

            // $count = 1;
            // foreach ($working_hrs_summary as $manager_id => $manager_data) {
            //     $message .= "<tr>
            //     <td>" . $count++ . "</td>
            //     <td>" . get_staff_full_name($manager_id) . "</td>
            //     <td>" . $manager_data['team_count'] . "</td>
            //     <td>" . $manager_data['nine_plus'] . "</td>
            //     <td>" . $manager_data['five_to_nine'] . "</td>
            //     <td>" . $manager_data['zero_to_five'] . "</td>
            //     </tr>";
            // }

            $message .= "</tbody></table>
            <h2>Absent Employees Summary</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Reporting Person</th>
                            <th>Department</th> 
                             
                        </tr>
                    </thead>
                <tbody>";


            foreach ($less_than_nine_summary as $key => $less_than_nine_data) {
                $sno = $key + 1;
                $message .= "<tr>
                <td>" . $sno . "</td>
                <td>" . $less_than_nine_data['emp_id'] . "</td>
                <td>" . $less_than_nine_data['employee'] . "</td>
                <td>" . $less_than_nine_data['manager_name'] . "</td>
                <td>" . $less_than_nine_data['department_name'] . "</td>
               
                </tr>";
            }



            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent';
                $arr = $this->email->print_debugger();
                print_r($arr);
            }

            log_message('error', 'Workroom report email sent!');
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }
    public function less_than_9_hrs_monthly_summary()
    {


        // $less_than_9_hrs_monthly_summary = array();

        $date = date('Y-m-d', strtotime("-1 days"));


        $month = date('m', strtotime($date));

        // $query = "SELECT staff_id,COUNT(staff_id) AS monthly_count ,departmentid,team_manage,staff_identifi  FROM tbltimesheets_timesheet LEFT JOIN tblstaff_departments ON tbltimesheets_timesheet.staff_id = tblstaff_departments.staffid LEFT JOIN tblstaff ON tbltimesheets_timesheet.staff_id = tblstaff.staffid WHERE month(date_work) = $month AND value < 9 AND value > 0 GROUP BY staff_id, departmentid, team_manage, staff_identifi; ;";

        $query = "SELECT
            staffid,
            firstname,
            lastname,
            staff_identifi,
            team_manage,
            (
            SELECT
                GROUP_CONCAT(
                    tblstaff_departments.departmentid
                )
            FROM
                tblstaff_departments
            WHERE
                staffid = tblstaff.staffid
        ) AS departmentids
        FROM
            `tblstaff`
        LEFT JOIN tblcheck_in_out ON tblstaff.staffid = tblcheck_in_out.staff_id AND DATE(DATE) = '$date' AND type_check = 1
        WHERE
            tblcheck_in_out.staff_id IS NULL AND active = 1 AND(
            SELECT
                COUNT(sd.departmentid)
            FROM
                tblstaff_departments sd
            WHERE
                sd.staffid = tblstaff.staffid
        ) > 0 AND tblstaff.staffid != 1
        ";

        $less_than_9_hrs_data = $this->db->query($query)->result_array();



        foreach ($less_than_9_hrs_data as &$data) {
            $data['employee'] = get_staff_full_name($data['staffid']);

            $data['department_name'] = $this->db->query("select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $data['departmentids'] . ')')->row()->department_names;



            $data['manager_name'] = get_staff_full_name($data['team_manage']);
            $data['emp_id'] = $data['staff_identifi'];
            // $data['manager_name'] = 
            // print_r(array_column($arr,'staffid')); 

        }


        return $less_than_9_hrs_data;
    }


    function uploadImageToDrive($filePath, $service, $folderId)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => basename($filePath),
            'parents' => [$folderId]
        ]);
        $content = file_get_contents($filePath);
        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => mime_content_type($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
        return $file->id;
    }

    function updateFileIdInDatabase($fileId, $imageName)
    {

        $this->db->where('asset_image', $imageName);
        $this->db->update('tblassets', ['file_id' => $fileId]);
    }



    function asset_image_script()
    {

        // Folder ID where you want to upload images
        $folderId = '1VUrGQn8e9kBpoMpYmcLTUwztfAGLwnjo';

        // Path to the directory containing images
        $imagesDirectory = 'modules/assets/uploads';

        // Fetch image names from the database
        $query = "SELECT asset_image FROM tblassets WHERE file_id IS NULL AND asset_image IS NOT NULL"; // Adjust the query as needed
        $result = $this->db->query($query);

        if ($result->num_rows() > 0) {
            foreach ($result->result() as $row) {
                $imageName = $row->asset_image;
                $filePath = $imagesDirectory . '/' . $imageName;

                if (file_exists($filePath)) {
                    $fileId = $this->uploadImageToDrive($filePath, $this->service, $folderId);
                    $this->updateFileIdInDatabase($fileId, $imageName);
                    echo "Uploaded and updated file ID for: " . $imageName . "<br>";
                } else {
                    echo "File not found: " . $filePath . "<br>";
                }
            }
        } else {
            echo "No images to upload.<br>";
        }

        $this->db->close();
    }



    // function check_compliances($staffs){


    //     $data = [];
    //     foreach ($staffs as $staff){
    //         if(!$staff['appointment_letter']||$staff['appointment_letter']=='no' || !$staff['coi_letter']||$staff['coi_letter']=='no' || !$staff['nda']||$staff['nda']=='no'|| !$staff['policy_document']||$staff['policy_document']=='no'|| !$staff['appointment_letter']||$staff['appointment_letter']=='no'|| !$staff['bio_enroll']||$staff['bio_enroll']=='no'|| !$staff['join_kit']||$staff['join_kit']=='no'|| !$staff['id_card']||$staff['id_card']=='no'|| !$staff['bgv']||$staff['bgv']=='no'){
    //             $data[] = $staff;
    //         }
    //     }

    //     return $data;

    // }

    function check_document_uploaded($staffs)
    {


        $data = [];

        foreach ($staffs as $staff) {
            if (!$staff['identification'] || !$staff['_10_marksheet'] || !$staff['_12_marksheet'] || !$staff['declaration_signature'] || !$staff['appointment_letter'] || $staff['appointment_letter'] == 'no' || !$staff['coi_letter'] || $staff['coi_letter'] == 'no' || !$staff['nda'] || $staff['nda'] == 'no' || !$staff['policy_document'] || $staff['policy_document'] == 'no' || !$staff['appointment_letter'] || $staff['appointment_letter'] == 'no' || !$staff['bio_enroll'] || $staff['bio_enroll'] == 'no' || !$staff['join_kit'] || $staff['join_kit'] == 'no' || !$staff['id_card'] || $staff['id_card'] == 'no' || !$staff['bgv'] || $staff['bgv'] == 'no') {
                $data[] = $staff;
            }
        }

        return $data;
    }


    function staff_list_compliances_and_documents()
    {
        $staffs = $this->db->query('SELECT
            *,
            (
            SELECT
                GROUP_CONCAT(
                    tblstaff_departments.departmentid
                )
            FROM
                tblstaff_departments
            WHERE
                staffid = tblstaff.staffid
        ) AS departmentids
        FROM
            `tblstaff`
        
        WHERE active = 1 AND staffid != 1')->result_array();

        foreach ($staffs as &$data) {

            if ($data['departmentids']) {

                $data['department_name'] = $this->db->query("select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $data['departmentids'] . ')')->row()->department_names;
            } else {
                $data['department_name'] = '';
            }
        }

        $data = $this->check_document_uploaded($staffs);

        return $data;
    }

    public function hr_docs_biweekly_report($staffs)
    {

        $date = date('m/d/Y');


        try {

            $this->email->set_mailtype("html");
            $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
            // $this->email->to('hr@tech2globe.com');
            $this->email->to('bhavyakhanna.tech2globe@gmail.com');
            $this->email->cc('naved.ahamad@tech2globe.in');

            // $this->email->cc(array('megha.anand@tech2globe.com', 'sarabjeet@tech2globe.com', 'sarabjeet@tech2globe.net', 'harpreet@tech2globe.com'));
            // $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Staff list for documents not uploaded- T2GWorkroom - $date ";

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
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
            
            ';







            $message .= "
            <h2>Documents Not Uploaded</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Reporting Person</th>
                            <th>Department</th> 
                             
                        </tr>
                    </thead>
                <tbody>";


            foreach ($staffs as $key => $staff) {
                $sno = $key + 1;
                $message .= "<tr>
                <td>" . $sno . "</td>
                <td>" . $staff['staff_identifi'] . "</td>
                <td>" . $staff['firstname'] . ' ' . $staff['lastname'] . "</td>
                <td>" . get_staff_full_name($staff['team_manage']) . "</td>
                <td>" . $staff['department_name'] . "</td>
               
                </tr>";
            }



            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent';
            }

            log_message('error', 'Workroom report email sent!');
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }

    public function get_birthday_and_anniversary()
    {

        $date = date('m-d');

        $data = $this->birthday_model->get_upcoming_anniversaries();
        foreach ($data as $staff) {

            echo '<pre>';
            print_r($staff);


            $formatted_birthday = '';
            $formatted_doj = '';
            if ($staff->birthday) {
                $birth_datetime = new DateTime($staff->birthday);
                $formatted_birthday = $birth_datetime->format('m-d');
            }

            if ($staff->doj) {
                $doj_datetime = new DateTime($staff->doj);
                $formatted_doj = $doj_datetime->format('m-d');
            }






            $currentDateTime = new DateTime();

            $staff_email = $staff->email;
            $reporting_person_email = get_staff_email_id($staff->team_manage);


            $imagePath = staff_profile_image_url($staff->staffid); // Local path to your image
            $imageData = base64_encode(file_get_contents($imagePath));
            $src = 'data:image/jpeg;base64,' . $imageData;
            echo 'formatted_birthday - ' . $formatted_birthday;

            if ($date == $formatted_birthday) {

                try {

                    $this->email->set_mailtype("html");
                    $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
                    // $this->email->to($staff_email);
                    $this->email->to('bhavyakhanna.tech2globe@gmail.com');
                    $this->email->bcc(array('naved.ahamad@tech2globe.in'));

                    // $this->email->to('sarabjeet@tech2globe.net');
                    // $this->email->cc(array($reporting_person_email, 'hr@tech2globe.com'));

                    // $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

                    // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
                    $subject = "Wishing you a very Happy Birthday! " . get_staff_full_name($staff->staffid);

                    $message = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Performance review</title>
                        <style>
                            h5 {
                                margin: 5px;
                            }

                            .card-header {
                                background-color: #1E293B;
                                color: white;
                                text-align: center;
                            }



                            .icon {
                                font-size: 30px;
                                color: #1E293B;
                            }

                            .event-title {

                                font-weight: bold;
                            }

                            .event-department {
                                color: #6c757d;

                            }

                            .event-message {
                                color: #0F172B;

                            }

                            .event-icon {
                                color: #1E293B;
                                margin-right: 10px;
                            }

                            .birthday .event-icon {
                                color: #ff4081;
                            }

                            .anniversary .event-icon {
                                color: #1E90FF;
                            }

                            .full-view {
                                background-color: #f9fafb;
                                padding: 30px;
                                border-radius: 10px;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                           
                            .anniversary-view {
                                background-color: #E1F5FE;
                                background-image: url("https://www.transparenttextures.com/patterns/my-little-plaid.png");
                            }

                            .full-view img {
                                width: 120px;
                                height: 120px;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 4px solid #1E293B;
                            }

                            .full-view.birthday-view img {
                                border: 3px solid #ff4081;
                            }

                            .full-view.anniversary-view img {
                                border: 3px solid #1E90FF;
                            }

                            .full-view .employee-name {

                                font-weight: bold;
                                color: #1E293B;
                                margin-top: 10px;
                            }



                            .d-flex {
                                display: flex;
                            }

                            .full-view .employee-dept {
                                color: #6c757d;

                            }

                            .full-view.birthday-view .employee-dept,
                            .full-view.birthday-view .employee-date,
                            .full-view.birthday-view .greeting-message,
                            .full-view.birthday-view .employee-name {
                                color: #ff4081;
                            }

                            .full-view.anniversary-view .employee-dept,
                            .full-view.anniversary-view .employee-date,
                            .full-view.anniversary-view .greeting-message,
                            .full-view.anniversary-view .employee-name {
                                color: #1E90FF;
                            }

                            .full-view .greeting-message {

                                color: #0F172B;
                                margin-top: 20px;
                                font-style: italic;
                            }


                            .full-view .emoji {
                                font-size: 2rem;
                            }

                            .list-group-item:hover {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .list-group-item:active {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .event-title {
                                display: flex;
                                justify-content: space-between;
                                align-items: baseline;
                            }

                            .event-date {

                                color: #6c757d;
                            }

                            .list-group-item.current {
                                background-color: #f1f5f9
                            }
                    </style>
                    </head>
                    <body>
                    
                    ';

                    $message .= '
                        <div style="width:100%; text-align:center; padding:20px; background-color:#f4f4f4; background-image: url(\'https://www.transparenttextures.com/patterns/arches.png\');" class=" anniversary-view">
                            <div style="max-width:600px; margin:auto; background-color:#fff; padding:20px; border-radius:8px;">
                                <div style="text-align:center;">
                                    <img src="' . $src . '" alt="Employee Image" style="border-radius:50%; width:120px; height:120px; margin-bottom:15px;">
                                </div>
                                <div style="font-family:Arial, sans-serif; text-align:center; color:#333;">
                                    <h3 style="margin:0; padding:0; font-size:20px; font-weight:bold;">' . get_staff_full_name($staff->staffid) . ' (' . get_staff_emp_id($staff->staffid) . ')' . '</h3>
                                    <p style="margin:5px 0; font-size:16px; color:#555;">' . get_job_position_by_staffid($staff->staffid) . '</p>
                                    <p style="margin:5px 0; font-size:16px; color:#888;">' . date('j-F') . '</p>
                                    <p style="margin:10px 0; font-size:18px; color:#333;">
                                        🎉 Wishing you a very Happy Birthday, ' . get_staff_full_name($staff->staffid) . '! 🎂</br> Enjoy your special day and have a wonderful year ahead! 🥳🎈
                                    </p>
                                    <p style="font-size:24px;">🎂🎁🎈</p>
                                </div>
                            </div>
                        </div>
                    ';
                    $this->email->subject($subject);
                    $this->email->message($message);
                    if ($this->email->send()) {
                        echo 'mail sent';
                    } else {
                        echo 'mail not sent';
                    }

                    log_message('error', 'Workroom report email sent!');

                    $this->email->clear();

                    // Optional: Add a delay between each email to avoid server throttling or spam flags
                    sleep(1); // Wait 1 second before sending the next email

                } catch (phpmailerException $e) {
                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
                } catch (Exception $e) {
                    echo $e->getMessage(); //Boring error messages from anything else!
                }
            }


            echo 'formatted_doj - ' . $formatted_doj;
            if ($date == $formatted_doj) {
                $interval = $doj_datetime->diff($currentDateTime);
                try {

                    $this->email->set_mailtype("html");
                    $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
                    $this->email->to('bhavyakhanna.tech2globe@gmail.com');
                    // $this->email->to($staff_email);
                    $this->email->bcc(array('naved.ahamad@tech2globe.in'));

                    // $this->email->to('sarabjeet@tech2globe.net');
                    // $this->email->cc(array($reporting_person_email, 'hr@tech2globe.com'));
                    // $this->email->to('sarabjeet@tech2globe.net');
                    // $this->email->cc(array('ishan.negi@tech2globe.in'));
                    // $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

                    // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
                    $subject = "Congratulations on completing $interval->y year(s) with us! " . get_staff_full_name($staff->staffid);

                    $message = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Performance review</title>
                        <style>
                            h5 {
                                margin: 5px;
                            }

                            .card-header {
                                background-color: #1E293B;
                                color: white;
                                text-align: center;
                            }



                            .icon {
                                font-size: 30px;
                                color: #1E293B;
                            }

                            .event-title {

                                font-weight: bold;
                            }

                            .event-department {
                                color: #6c757d;

                            }

                            .event-message {
                                color: #0F172B;

                            }

                            .event-icon {
                                color: #1E293B;
                                margin-right: 10px;
                            }

                            .birthday .event-icon {
                                color: #ff4081;
                            }

                            .anniversary .event-icon {
                                color: #1E90FF;
                            }

                            .full-view {
                                background-color: #f9fafb;
                                padding: 30px;
                                border-radius: 10px;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                            .birthday-view {
                                background-color: #FFE4E1;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                         

                            .full-view img {
                                width: 120px;
                                height: 120px;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 4px solid #1E293B;
                            }

                            .full-view.birthday-view img {
                                border: 3px solid #ff4081;
                            }

                            .full-view.anniversary-view img {
                                border: 3px solid #1E90FF;
                            }

                            .full-view .employee-name {

                                font-weight: bold;
                                color: #1E293B;
                                margin-top: 10px;
                            }



                            .d-flex {
                                display: flex;
                            }

                            .full-view .employee-dept {
                                color: #6c757d;

                            }

                            .full-view.birthday-view .employee-dept,
                            .full-view.birthday-view .employee-date,
                            .full-view.birthday-view .greeting-message,
                            .full-view.birthday-view .employee-name {
                                color: #ff4081;
                            }

                            .full-view.anniversary-view .employee-dept,
                            .full-view.anniversary-view .employee-date,
                            .full-view.anniversary-view .greeting-message,
                            .full-view.anniversary-view .employee-name {
                                color: #1E90FF;
                            }

                            .full-view .greeting-message {

                                color: #0F172B;
                                margin-top: 20px;
                                font-style: italic;
                            }


                            .full-view .emoji {
                                font-size: 2rem;
                            }

                            .list-group-item:hover {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .list-group-item:active {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .event-title {
                                display: flex;
                                justify-content: space-between;
                                align-items: baseline;
                            }

                            .event-date {

                                color: #6c757d;
                            }

                            .list-group-item.current {
                                background-color: #f1f5f9
                            }
                    </style>
                    </head>
                    <body>
                    
                    ';

                    $message .= '
                        <div style="width:100%; text-align:center; padding:20px; background-color:#E1F5FE; background-image: url(\'https://www.transparenttextures.com/patterns/my-little-plaid.png\');" class="anniversary-view">
                            <div style="max-width:600px; margin:auto; background-color:#fff; padding:20px; border-radius:8px;">
                                <div style="text-align:center;">
                                    <img src="' . $src  . '" alt="Employee Image" style="border-radius:50%; width:120px; height:120px; margin-bottom:15px;">
                                </div>
                                <div style="font-family:Arial, sans-serif; text-align:center; color:#333;">
                                    <h3 style="margin:0; padding:0; font-size:20px; font-weight:bold;">' . get_staff_full_name($staff->staffid) . ' (' . get_staff_emp_id($staff->staffid) . ')' . '</h3>
                                    <p style="margin:5px 0; font-size:16px; color:#555;">' . get_job_position_by_staffid($staff->staffid) . '</p>
                                    <p style="margin:5px 0; font-size:16px; color:#888;">' . date('j-F') . '</p>
                                    <p style="margin:10px 0; font-size:18px; color:#333;">
                                        🎊 Congratulations on your ' . $interval->y . '-year work anniversary, ' . get_staff_full_name($staff->staffid) . '! 🎉<br>
                                        Thank you for being such a valuable part of our team. 🎖️
                                    </p>
                                    <p style="font-size:24px;">🎂🎁🎈</p>
                                </div>
                            </div>
                        </div>
                    ';

                    $this->email->subject($subject);
                    $this->email->message($message);
                    if ($this->email->send()) {
                        echo 'mail sent';
                    } else {
                        echo 'mail not sent';
                    }

                    $this->email->clear();

                    // Optional: Add a delay between each email to avoid server throttling or spam flags
                    sleep(1); // Wait 1 second before sending the next email

                    log_message('error', 'Workroom report email sent!');
                } catch (phpmailerException $e) {
                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
                } catch (Exception $e) {
                    echo $e->getMessage(); //Boring error messages from anything else!
                }
            }
        }
    }

    public function send_help_email($fromEmail, $fromName, $messageText)
{
    $subject = 'Help Request';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Help Request</title>
    </head>
    <body>
        <h3>Help Request Received</h3>
        <p><strong>From:</strong> '.$fromName.' ('.$fromEmail.')</p>
        <p><strong>Message:</strong></p>
        <p>'.$messageText.'</p>
    </body>
    </html>
    ';

    $this->email->from('no-reply@tech2globe.in', 'Help Desk'); // MUST match SMTP domain
    $this->email->to('ishita.rathi@tech2globe.in');               // change if needed
    $this->email->cc(array('krniraj007@gmail.com')); 
    $this->email->reply_to($fromEmail, $fromName);
    $this->email->subject($subject);
    $this->email->message($message);

    if ($this->email->send()) {
        return true;
    } else {
        log_message('error', $this->email->print_debugger());
        return false;
    }
}

}
