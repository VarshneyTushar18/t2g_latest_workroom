<?php


defined('BASEPATH') or exit('No direct script access allowed');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

class Inactive_staff_report extends AdminController
{

    public function index()
    {

        // $this->manager();
        $consecutive_three_days = $this->consecutive_three_days();
        // $working_hours_summary = $this->working_hours_summary();
        // $less_than_9_hrs_data = $this->less_than_9_hrs_monthly_summary();

        // $this->daily_report_email($working_hours_summary,$less_than_9_hrs_data);
        $this->consecutive_three_days_email($consecutive_three_days);
        redirect('/admin/manual_report');
    }

    public function managers_list()
    {
        $query = "SELECT team_manage from tblstaff";
        $data = $this->db->query($query)->result_array();
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $managers = [];

        foreach ($manager_ids as $manager_id) {

            $managers[$manager_id] = get_staff_full_name($manager_id);
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

            // echo get_staff_full_name($manager_id);
            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];
            $nine_plus = 0;
            $five_to_nine = 0;
            $zero_to_five = 0;
            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid]['attendance'] = $this->is_staff_present($team_staffid);
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
            $occupancy = $working_hours_summary[$manager_id]['team_count'] ? ($working_hours_summary[$manager_id]['present'] /  $working_hours_summary[$manager_id]['team_count']) * 100 : 0;
            $working_hours_summary[$manager_id]['occupancy'] = round($occupancy, 2);
            $working_hours_summary[$manager_id]['nine_plus'] = $nine_plus;
            $working_hours_summary[$manager_id]['five_to_nine'] = $five_to_nine;
            $working_hours_summary[$manager_id]['zero_to_five'] = $zero_to_five;
        }

        echo '<pre>';
        // print_r($working_hours_summary);
        // die;

        return $working_hours_summary;
    }

    public function is_staff_present($staffid)
    {
        // getting previous date for testing purposes
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT * FROM tbltimesheets_timesheet WHERE staff_id = $staffid AND date_work = '$date'";

        // echo $query;

        $today_present_data = $this->db->query($query)->result_array();

        // return  $today_present_data;

        if ($today_present_data) {

            if ($today_present_data[0]['type'] == 'AB') {
                return 0;
            } else {
                return 1;
            }
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
            case $hrs > 5  && $hrs < 9:
                $five_to_nine++;
                break;
            case $hrs >= 9:
                $nine_plus++;
                break;
        }
    }

    public function less_than_9_hrs_monthly_summary()
    {


        // $less_than_9_hrs_monthly_summary = array();

        $date = date('Y-m-d', strtotime("-1 days"));

        $month = date('m', strtotime($date));

        // $query = "SELECT staff_id,COUNT(staff_id) AS monthly_count ,departmentid,team_manage,staff_identifi  FROM tbltimesheets_timesheet LEFT JOIN tblstaff_departments ON tbltimesheets_timesheet.staff_id = tblstaff_departments.staffid LEFT JOIN tblstaff ON tbltimesheets_timesheet.staff_id = tblstaff.staffid WHERE month(date_work) = $month AND value < 9 AND value > 0 GROUP BY staff_id ;";

        // $query = "SELECT staff_id,COUNT(staff_id) AS monthly_count ,departmentid,team_manage,staff_identifi  FROM tbltimesheets_timesheet LEFT JOIN tblstaff_departments ON tbltimesheets_timesheet.staff_id = tblstaff_departments.staffid LEFT JOIN tblstaff ON tbltimesheets_timesheet.staff_id = tblstaff.staffid WHERE month(date_work) = $month AND value < 9 AND value > 0 GROUP BY staff_id, departmentid, team_manage, staff_identifi; ;";
        $query = "SELECT 
            tbltimesheets_timesheet.staff_id,
            COUNT(tbltimesheets_timesheet.staff_id) AS monthly_count,
            GROUP_CONCAT(DISTINCT tblstaff_departments.departmentid) AS departmentids,
            GROUP_CONCAT(DISTINCT tblstaff.team_manage) AS team_manage_list,
            GROUP_CONCAT(DISTINCT tblstaff.staff_identifi) AS staff_identifi_list
        FROM 
            tbltimesheets_timesheet 
        LEFT JOIN 
            tblstaff_departments 
            ON tbltimesheets_timesheet.staff_id = tblstaff_departments.staffid 
        LEFT JOIN 
            tblstaff 
            ON tbltimesheets_timesheet.staff_id = tblstaff.staffid 
        WHERE 
            MONTH(tbltimesheets_timesheet.date_work) = $month 
            AND tbltimesheets_timesheet.value < 9 
            AND tbltimesheets_timesheet.value > 0 
        GROUP BY 
            tbltimesheets_timesheet.staff_id;
        ";

        $less_than_9_hrs_data = $this->db->query($query)->result_array();

        // print_r($less_than_9_hrs_data); die;

        foreach ($less_than_9_hrs_data as &$data) {
            $data['employee'] = get_staff_full_name($data['staff_id']);

            $data['department_name'] = $this->db->query("select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $data['departmentids'] . ')')->row()->department_names;



            $data['manager_name'] = get_staff_full_name($data['team_manage_list']);
            $data['emp_id'] = $data['staff_identifi_list'];
            // $data['manager_name'] = 
            // print_r(array_column($arr,'staffid')); 

        }

        // print_r($less_than_9_hrs_data); die;

        return $less_than_9_hrs_data;
    }

    public function get_staff_emp_id($staffid)
    {

        $query = "SELECT empid from tblstaff_info WHERE staffid = $staffid";

        $empid_obj = $this->db->query($query)->row();

        if ($empid_obj) {

            return $empid_obj->empid;
        }
        return '';
    }




    public function daily_report_email($working_hrs_summary, $less_than_nine_summary)
    {
        // print_r($less_than_nine_summary);
        $date = date('m/d/Y', strtotime("-1 days"));

        try {

            $this->email->set_mailtype("html");
            $this->email->from('noreply_workroom@tech2globe.com', 'Tech2globe');
            $this->email->to('sarabjeet@tech2globe.com');
            $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','bhavyakhanna.tech2globe@gmail.com'));
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
            <h2>Today\'s Occupancy</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Head Count</th>
                            <th>Team Count</th>
                            <th>Today\'s Occupancy</th> 
                        </tr>
                    </thead>
                <tbody>
            ';


            $count = 1;
            foreach ($working_hrs_summary as  $manager_id => $manager_data) {
                $message .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . get_staff_full_name($manager_id) . "</td>
                <td>" . $manager_data['team_count'] . "</td>
                <td>" . $manager_data['occupancy'] . "%</td>
                </tr>";
            }



            $message  .= '
                </tbody>
            </table>

            <h2>Today\'s Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Cluster</th>
                        <th>Team Count</th>
                        <th>Total Present</th>
                        <th>Total Absent</th>
                    </tr>
                </thead>
            <tbody>
            ';

            $count = 1;
            foreach ($working_hrs_summary as  $manager_id => $manager_data) {
                $message .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . get_staff_full_name($manager_id) . "</td>
                <td>" . $manager_data['team_count'] . "</td>
                <td>" . $manager_data['present'] . "</td>
                <td>" . $manager_data['absent'] . "</td>
                </tr>";
            }


            $message .= ' </tbody> </table>
            <h2>Working Hours Summary</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Cluster</th>
                            <th>Team Count</th>
                            <th>9+ hours</th>
                            <th>5 to 9 hours</th> 
                            <th>0 to 5 hours</th> 
                        </tr>
                    </thead>
                <tbody>';

            $count = 1;
            foreach ($working_hrs_summary as  $manager_id => $manager_data) {
                $message .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . get_staff_full_name($manager_id) . "</td>
                <td>" . $manager_data['team_count'] . "</td>
                <td>" . $manager_data['nine_plus'] . "</td>
                <td>" . $manager_data['five_to_nine'] . "</td>
                <td>" . $manager_data['zero_to_five'] . "</td>
                </tr>";
            }

            $message .= "</tbody></table>
            <h2>Less Than 9 Hours Summary</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Manager Name</th>
                            <th>Department</th> 
                            <th>Monthly Count</th> 
                        </tr>
                    </thead>
                <tbody>";


            foreach ($less_than_nine_summary as  $key => $less_than_nine_data) {
                $sno = $key + 1;
                $message .= "<tr>
                <td>" . $sno . "</td>
                <td>" . $less_than_nine_data['emp_id'] . "</td>
                <td>" . $less_than_nine_data['employee'] . "</td>
                <td>" . $less_than_nine_data['manager_name'] . "</td>
                <td>" . $less_than_nine_data['department_name'] . "</td>
                <td>" . $less_than_nine_data['monthly_count'] . "</td>
                </tr>";
            }



            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }

    public function consecutive_three_days()
    {
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT staffid, firstname,  lastname, email, role , last_login FROM tblstaff WHERE last_login < NOW() - INTERVAL 3 DAY and active = 1;";

        $staff = $this->db->query($query)->result_array();

        foreach ($staff as $key => $value) {

            $staff[$key]["full_name"] = $staff[$key]["firstname"] . ' ' . $staff[$key]["lastname"];
            $staff[$key]["role"] = get_role_name($staff[$key]["role"]);
            unset($staff[$key]['firstname']);
            unset($staff[$key]['lastname']);
        }

        return $staff;
    }

    public function consecutive_three_days_email($consecutive_three_days)
    {

        $date = date('m/d/Y', strtotime("-1 days"));

        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
            $this->email->to('sarabjeet@tech2globe.com');
            $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','bhavyakhanna.tech2globe@gmail.com'));
          
            // $this->email->to('bhavyakhanna.tech2globe@gmail.com');
            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Inactive Staffs - T2GWorkroom - $date ";

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
                <h3>Employees that did not Login for 3 consecutive days :</h3>
                <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th> 
                        </tr>
                    </thead>
                    <tbody>
                ';


            foreach ($consecutive_three_days as $key => $value) {
                $sno = $key + 1;
                $message .= "<tr>
                    <td>" . $sno . "</td>
                    <td>" . $value['full_name'] . "</td>
                    <td>" . $value['email'] . "</td>
                    <td>" . $value['role'] . "</td>
                    <td>" . $value['last_login'] . "</td>
                    </tr>";
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
