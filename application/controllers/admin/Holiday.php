<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

class Holiday extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		// $this->load->library('GoogleDriveHelper');
		$this->load->model('holiday_model');
		$this->load->model('departments_model');
		// $this->load->model('recruitment_model');

	}
    /* List all staff members */
    public function index()
    {
		 if (!attendance_permission()) {

            access_denied('timesheets');
        }
		 $this->load->model('departments_model');
		// $this->load->model('holiday_model');
        $data['departments'] = $this->departments_model->get_staff_departments();
		$staffs = $this->holiday_model->get_staff_based_on_department();
		$data['staffs'] = $staffs;
		 $this->load->view('admin/holiday/holiday_view', $data);
    }
	public function get_staff_department_json()
    {
        $departmentid = $this->input->post('department');
        $data[] = $this->holiday_model->get_staff_based_on_department($departmentid);
        echo json_encode($data[0]);
    }

 // get staff data in ajax
    public function get_staff_json()
    {
        $staffId = $this->input->post('staffid');

        $data[] = $this->staff_model->get('', ['active' => 1, 'staffid' => $staffId]);
        $data[] = $this->staff_model->get_department_by_staffid_staff_model($staffId);

        $data[0][0]['job_name'] = hr_profile_job_name_by_id($data[0][0]['job_position']);

        echo json_encode($data);
    }	

 function getSaturdaysInRange($from_month, $to_month) {
	 
        $saturdays = [];
        $start = new DateTime($from_month);
        $end = new DateTime($to_month);
        $end->modify('last day of this month'); // Ensure end of month range for calculations

        while ($start <= $end) {
            $month = $start->format('F Y'); // Get month and year as key
            if (!isset($saturdays[$month])) {
                $saturdays[$month] = [];
                // Get all Saturdays for the month
                $firstDayOfMonth = new DateTime($start->format('Y-m-01'));
                $saturday = clone $firstDayOfMonth;

                // Find the first Saturday
                if ($saturday->format('N') != 6) {
                    $saturday->modify('next Saturday');
                }

                // Collect the 1st, 2nd, 3rd, and 4th Saturdays if within range
                for ($i = 0; $i < 5; $i++) {
                    $currentSaturday = clone $saturday;
                    $currentSaturday->modify("+{$i} weeks");

                    if ($currentSaturday->format('m') === $firstDayOfMonth->format('m') && 
                        $currentSaturday >= new DateTime($from_month) &&
                        $currentSaturday <= new DateTime($to_month)) {
                        $key = ($i + 1) . 'st';
                        if ($i == 1) $key = '2nd';
                        if ($i == 2) $key = '3rd';
                        if ($i == 3) $key = '4th';
						if ($i == 4) $key = '5th';

                        $saturdays[$month][$key] = $currentSaturday->format('Y-m-d');
                    }
                }
            }

            // Move to the next month
            $start->modify('first day of next month');
        }

        return $saturdays;
    }
	/* function getSaturdaysUsingDate($month, $year) {
            $saturdays = [];
            $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            for ($day = 1; $day <= $numDays; $day++) {
                $timestamp = mktime(0, 0, 0, $month, $day, $year);
                if (date('w', $timestamp) == 6) { // 6 represents Saturday
                    $saturdays[] = date('l, F j, Y', $timestamp);
                }
            }
            return $saturdays;
        }
		*/
		 // Method 2: Using DateTime and DateInterval
      /*  function getSaturdaysUsingDateTime($month, $year) {
            $saturdays = [];
            // Create a DateTime object for the first day of the month
            $date = new DateTime("$year-$month-01");

            // Modify the date to the first Saturday
            if ($date->format('w') != 6) {
                $date->modify('next Saturday');
            }

            // Loop through the month, adding each Saturday to the array
            while ($date->format('n') == $month) {
                $saturdays[] = $date->format('Y-m-d');
                $date->modify('+1 week');
            }

            return $saturdays;
        }*/
	public function get_staff_to_month()
    {
		//print_r($_POST);die;

		$staff_id = $this->input->post('staffid');
        $from_month =  $this->input->post('from_month');
		$to_month =  $this->input->post('to_month');
		$data = array();
			$querys= $this->db->query('SELECT * FROM tblholiday WHERE staffid ="'.$staff_id.'" AND saturday_date between "'.$from_month.'" AND "'.$to_month.'"');
			foreach ($querys->result() as $row)
				{
					//print_r($row->saturday_date);
					$data[] = array($row->saturday_date);
					
				}
				//print_r($data);die;
			 if($data == null){
				// echo 'hello';
				if (strtotime($from_month) > strtotime($to_month)) {
					echo "<p style='color:red;'>Error: Start date must be before or equal to the end date.</p>";
				} else {
					$saturdays = $this->getSaturdaysInRange($from_month, $to_month);
					$sat = array();
					
					if (!empty($saturdays)) {	
									//echo !empty($data);
							foreach ($saturdays as $month => $dates) {
							$sat[] = array('month'=>$month,'1-sat'=>$dates['1st'],'2-sat'=>$dates['2nd'],'3-sat'=>$dates['3rd'],'4-sat'=>$dates['4th'],'5-sat'=>$dates['5th']);
							
						}

						
					} else {
						echo "No Saturdays found in the selected range.";
					}
					
					echo json_encode($sat);
					
				}
			 }else{
				// echo 'else';
				 //print_r($data);die;
				 echo json_encode($data);
				//$sat['saturday'] = $data; 
			 }
      
    }
	 public function get_staff_calculate_saturday()
    {
		 $data = array();
		
		 $sat = $this->input->post('sat');
		
		 $countval = count($sat);
		 for($i=0; $i < $countval; $i++) {
			 if($sat[$i]!=null){
				 
			$data[] = array(
            'staffid'=>$this->input->post('staffid'),
            'department_id' => $this->input->post('department_id'),
            'from_month' => $this->input->post('from_month'),
            'to_month' => $this->input->post('to_month'),
			'saturday_date' => $this->input->post('sat')[$i],
			'status' => $this->input->post('status')
           );
		  
		   $this->db->query("INSERT INTO tblholiday (staffid, department_id, from_month,to_month,saturday_date,status) VALUES ('".$data[$i]['staffid']."', '".$data[$i]['department_id']."', '".$data[$i]['from_month']."','".$data[$i]['to_month']."','".$data[$i]['saturday_date']."','".$data[$i]['status']."')");
			 }else{
				 echo "null value";
			 }
	
    }
		$data['success'] = 'Successfully added data';
		echo json_encode($data);
	 //$this->load->view('admin/holiday/holiday_view', $data);
    }
	
	public function manageHoliday()
		{
		
        $data['departments'] = $this->departments_model->get_staff_departments();
		
		$staffs = $this->holiday_model->get_staff_based_on_department();
		$data['staffs'] = $staffs;
		$this->load->view('admin/holiday/manage_holiday',$data);
		}
		
		
	public function getHolidayStaffData()
		{
			//print_R($_POST);die;
		$data = array();
		$staff_id = $this->input->post('staffid');
        $month =  $this->input->post('month');
		$year =  $this->input->post('year');
		
			//print_r($saturdays);die;
			
			//echo 'SELECT * FROM `tblholiday` WHERE MONTH(`saturday_date`)= "'.$month.'" AND staffid ="'.$staff_id.'"';die;
			
			$query= $this->db->query('SELECT tblholiday.staffid,tblholiday.department_id,tblholiday.saturday_date,tblholiday.status,tbldepartments.departmentid ,tbldepartments.name,tblstaff.staffid,tblstaff.firstname,tblstaff.lastname FROM `tblholiday` left join tbldepartments on tblholiday.department_id = tbldepartments.departmentid left join tblstaff on tblholiday.staffid=tblstaff.staffid WHERE MONTH(`saturday_date`)= "'.$month.'" AND tblholiday.staffid ="'.$staff_id.'"');
			
			foreach ($query->result() as $row)
			{
			//print_r($row);
			$data[] = array(
            'staffid'=>$row->staffid,
            'department_id' => $row->department_id,
            'saturday_date' => $row->saturday_date, 
			'status' => $row->status,
			'department_name' => $row->name,
			'staff_firstname' => $row->firstname,
			'staff_lastname' => $row->lastname
           );
		   
			}
		
			echo json_encode($data);
        
	}
    public function manageHolidayStaff()
    {
       
			//echo json_encode($data);
        $this->load->view('admin/holiday/staffholiday');
    }
	
	 public function manageHolidayStaffData()
    {
		
        $id = get_staff_user_id(); 
		$month =  $this->input->post('month');
		$year =  $this->input->post('year');

        // $performance = $this->db->get_where('tblstaff_performance', ['staffid' => $id]);
		//echo 'SELECT tblholiday.staffid,tblholiday.department_id,tblholiday.saturday_date,tblholiday.status,tbldepartments.departmentid ,tbldepartments.name,tblstaff.staffid,tblstaff.firstname,tblstaff.lastname FROM `tblholiday` left join tbldepartments on tblholiday.department_id = tbldepartments.departmentid left join tblstaff on tblholiday.staffid=tblstaff.staffid WHERE MONTH(`saturday_date`)= "'.$month.'" AND tblholiday.staffid ="'.$id.'"';die;
		$query= $this->db->query('SELECT tblholiday.staffid,tblholiday.department_id,tblholiday.saturday_date,tblholiday.status,tbldepartments.departmentid ,tbldepartments.name,tblstaff.staffid,tblstaff.firstname,tblstaff.lastname FROM `tblholiday` left join tbldepartments on tblholiday.department_id = tbldepartments.departmentid left join tblstaff on tblholiday.staffid=tblstaff.staffid WHERE MONTH(`saturday_date`)= "'.$month.'" AND tblholiday.staffid ="'.$id.'"');
					
			foreach ($query->result() as $row)
			{
			//print_r($row);
			$data[] = array(
            'staffid'=>$row->staffid,
            'department_id' => $row->department_id,
            'saturday_date' => $row->saturday_date, 
			'status' => $row->status,
			'department_name' => $row->name,
			'staff_firstname' => $row->firstname,
			'staff_lastname' => $row->lastname
           );
		   
			}
			//print_r($data);
			echo json_encode($data);
       // $this->load->view('admin/holiday/staffholiday', $data);
    }

}
