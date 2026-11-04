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
    /* List all staff members — one screen only (same as Manage Saturday) */
    public function index()
    {
		 if (!attendance_permission()) {

            access_denied('timesheets');
        }
		redirect(admin_url('holiday/manageHoliday'));
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

	/**
	 * @return int[]
	 */
	protected function parse_staff_ids_from_request()
	{
		$raw = $this->input->post('staffids');
		if (!is_array($raw)) {
			$single = (int) $this->input->post('staffid');
			return $single > 0 ? [$single] : [];
		}

		$ids = [];
		foreach ($raw as $id) {
			$id = (int) $id;
			if ($id > 0) {
				$ids[$id] = $id;
			}
		}

		return array_values($ids);
	}

	/**
	 * Assigned Saturday leave dates for a staff member in a month (Y-m).
	 */
	public function get_calendar_saturdays()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$staff_ids = $this->parse_staff_ids_from_request();
		$month = trim((string) $this->input->post('month')); // YYYY-MM

		if (empty($staff_ids) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
			echo json_encode(['ok' => false, 'dates' => [], 'error' => 'staff and month required']);
			return;
		}

		$from = $month . '-01';
		$to = date('Y-m-t', strtotime($from));
		$intersection = null;

		foreach ($staff_ids as $staff_id) {
			$rows = $this->db->select('saturday_date')
				->from(db_prefix() . 'holiday')
				->where('staffid', $staff_id)
				->where('saturday_date >=', $from)
				->where('saturday_date <=', $to)
				->get()
				->result_array();

			$set = [];
			foreach ($rows as $row) {
				$set[$row['saturday_date']] = true;
			}

			if ($intersection === null) {
				$intersection = $set;
			} else {
				foreach ($intersection as $date => $_) {
					if (!isset($set[$date])) {
						unset($intersection[$date]);
					}
				}
			}
		}

		$dates = $intersection ? array_keys($intersection) : [];

		echo json_encode([
			'ok' => true,
			'dates' => $dates,
			'from' => $from,
			'to' => $to,
			'staff_count' => count($staff_ids),
		]);
	}

	/**
	 * Toggle assign/unassign a Saturday leave by calendar click (date only, no time).
	 */
	public function toggle_saturday_leave()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$staff_id = (int) $this->input->post('staffid');
		$department_id = (int) $this->input->post('department_id');
		$saturday_date = trim((string) $this->input->post('saturday_date'));

		if ($staff_id <= 0 || $department_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $saturday_date)) {
			echo json_encode(['ok' => false, 'error' => 'Invalid staff, department or date']);
			return;
		}

		// Only allow Saturdays
		if ((int) date('N', strtotime($saturday_date)) !== 6) {
			echo json_encode(['ok' => false, 'error' => 'Only Saturdays can be assigned']);
			return;
		}

		$existing = $this->db->where('staffid', $staff_id)
			->where('saturday_date', $saturday_date)
			->get(db_prefix() . 'holiday')
			->row();

		if ($existing) {
			$this->db->where('id', $existing->id)->delete(db_prefix() . 'holiday');
			echo json_encode([
				'ok' => true,
				'action' => 'removed',
				'saturday_date' => $saturday_date,
				'message' => 'Saturday leave removed',
			]);
			return;
		}

		$this->db->insert(db_prefix() . 'holiday', [
			'staffid' => $staff_id,
			'department_id' => $department_id,
			'from_month' => $saturday_date,
			'to_month' => $saturday_date,
			'saturday_date' => $saturday_date,
			'status' => 1,
		]);

		echo json_encode([
			'ok' => true,
			'action' => 'assigned',
			'saturday_date' => $saturday_date,
			'message' => 'Saturday leave assigned',
		]);
	}

	/**
	 * Manual save: sync Saturday leaves for one staff in one month (YYYY-MM).
	 * Posted dates = final assigned set for that month (date only).
	 */
	public function save_saturday_leaves()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$staff_ids = $this->parse_staff_ids_from_request();
		$department_id = (int) $this->input->post('department_id');
		$month = trim((string) $this->input->post('month')); // YYYY-MM
		$dates = $this->input->post('dates');

		if (empty($staff_ids) || $department_id <= 0 || !preg_match('/^\d{4}-\d{2}$/', $month)) {
			echo json_encode(['ok' => false, 'error' => 'Invalid staff, department or month']);
			return;
		}

		if (!is_array($dates)) {
			$dates = [];
		}

		$from = $month . '-01';
		$to = date('Y-m-t', strtotime($from));
		$wanted = [];

		foreach ($dates as $date) {
			$date = trim((string) $date);
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
				continue;
			}
			if ($date < $from || $date > $to) {
				continue;
			}
			if ((int) date('N', strtotime($date)) !== 6) {
				continue;
			}
			$wanted[$date] = true;
		}

		$total_added = 0;
		$total_removed = 0;

		foreach ($staff_ids as $staff_id) {
			$existing_rows = $this->db->select('id, saturday_date')
				->from(db_prefix() . 'holiday')
				->where('staffid', $staff_id)
				->where('saturday_date >=', $from)
				->where('saturday_date <=', $to)
				->get()
				->result_array();

			$existing = [];
			foreach ($existing_rows as $row) {
				$existing[$row['saturday_date']] = (int) $row['id'];
			}

			foreach ($existing as $date => $id) {
				if (!isset($wanted[$date])) {
					$this->db->where('id', $id)->delete(db_prefix() . 'holiday');
					$total_removed++;
				}
			}

			foreach ($wanted as $date => $_) {
				if (isset($existing[$date])) {
					continue;
				}
				$this->db->insert(db_prefix() . 'holiday', [
					'staffid' => $staff_id,
					'department_id' => $department_id,
					'from_month' => $date,
					'to_month' => $date,
					'saturday_date' => $date,
					'status' => 1,
				]);
				$total_added++;
			}
		}

		$staff_label = count($staff_ids) === 1
			? '1 employee'
			: count($staff_ids) . ' employees';

		echo json_encode([
			'ok' => true,
			'added' => $total_added,
			'removed' => $total_removed,
			'dates' => array_keys($wanted),
			'staff_count' => count($staff_ids),
			'message' => 'Saturday leaves saved for ' . $staff_label . ' (' . $total_added . ' added, ' . $total_removed . ' removed)',
		]);
	}

	public function getHolidayStaffData()
		{
		$data = array();
		$staff_ids = $this->parse_staff_ids_from_request();
        $month =  (int) $this->input->post('month');
		$year =  (int) $this->input->post('year');

		if (empty($staff_ids) || $month < 1 || $month > 12 || $year < 2000) {
			echo json_encode([]);
			return;
		}

		$from = sprintf('%04d-%02d-01', $year, $month);
		$to = date('Y-m-t', strtotime($from));

		$this->db->select('h.staffid, h.department_id, h.saturday_date, h.status, d.name as department_name, s.firstname as staff_firstname, s.lastname as staff_lastname');
		$this->db->from(db_prefix() . 'holiday h');
		$this->db->join(db_prefix() . 'departments d', 'h.department_id = d.departmentid', 'left');
		$this->db->join(db_prefix() . 'staff s', 'h.staffid = s.staffid', 'left');
		$this->db->where('h.saturday_date >=', $from);
		$this->db->where('h.saturday_date <=', $to);
		$this->db->where_in('h.staffid', $staff_ids);
		$this->db->order_by('h.saturday_date', 'ASC');
		$query = $this->db->get();

		foreach ($query->result_array() as $row) {
			$data[] = [
				'staffid' => $row['staffid'],
				'department_id' => $row['department_id'],
				'saturday_date' => $row['saturday_date'],
				'status' => $row['status'],
				'department_name' => $row['department_name'],
				'staff_firstname' => $row['staff_firstname'],
				'staff_lastname' => $row['staff_lastname'],
			];
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

	/**
	 * Company holiday calendar — view for all staff; manage for HR / Super Admin.
	 */
	public function calendar()
	{
		$data['title'] = 'Holiday Calendar';
		$data['year'] = (int) ($this->input->get('year') ?: date('Y'));
		$data['can_manage'] = $this->holiday_model->can_manage_company_holidays();
		$data['holidays'] = $this->holiday_model->get_company_holidays_for_year($data['year']);
		$this->load->view('admin/holiday/holiday_calendar', $data);
	}

	public function get_holidays_json()
	{
		$year = (int) ($this->input->get('year') ?: $this->input->post('year') ?: date('Y'));
		$holidays = $this->holiday_model->get_company_holidays_for_year($year);
		$mapped = [];
		foreach ($holidays as $h) {
			$mapped[] = [
				'id' => (int) $h['id'],
				'date' => $h['display_date'],
				'break_date' => $h['break_date'],
				'name' => $h['off_reason'],
				'type' => $h['off_type'],
				'repeat_by_year' => (int) ($h['repeat_by_year'] ?? 0),
			];
		}
		echo json_encode([
			'success' => true,
			'year' => $year,
			'can_manage' => $this->holiday_model->can_manage_company_holidays(),
			'holidays' => $mapped,
		]);
	}

	public function save_holiday()
	{
		if (!$this->holiday_model->can_manage_company_holidays()) {
			echo json_encode(['success' => false, 'message' => 'Only HR or Admin can manage holidays.']);
			return;
		}

		$result = $this->holiday_model->save_company_holiday([
			'id' => $this->input->post('id'),
			'break_date' => $this->input->post('break_date'),
			'leave_reason' => $this->input->post('leave_reason'),
			'leave_type' => $this->input->post('leave_type'),
			'repeat_by_year' => $this->input->post('repeat_by_year'),
		]);
		echo json_encode($result);
	}

	public function delete_holiday($id = 0)
	{
		if (!$this->holiday_model->can_manage_company_holidays()) {
			echo json_encode(['success' => false, 'message' => 'Only HR or Admin can manage holidays.']);
			return;
		}

		$id = (int) ($id ?: $this->input->post('id'));
		$ok = $this->holiday_model->delete_company_holiday($id);
		echo json_encode([
			'success' => $ok,
			'message' => $ok ? 'Holiday deleted.' : 'Could not delete holiday.',
		]);
	}

	public function upload_holidays()
	{
		if (!$this->holiday_model->can_manage_company_holidays()) {
			echo json_encode(['success' => false, 'message' => 'Only HR or Admin can manage holidays.']);
			return;
		}

		if (empty($_FILES['holiday_file']['name'])) {
			echo json_encode(['success' => false, 'message' => 'Please choose a CSV or Excel file.']);
			return;
		}

		$name = $_FILES['holiday_file']['name'];
		$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
			echo json_encode(['success' => false, 'message' => 'Only .csv, .xlsx or .xls files are allowed.']);
			return;
		}

		$tmp = $_FILES['holiday_file']['tmp_name'];
		$result = $this->holiday_model->import_company_holidays_file($tmp, $ext);
		echo json_encode([
			'success' => true,
			'inserted' => $result['inserted'],
			'skipped' => $result['skipped'],
			'errors' => array_slice($result['errors'], 0, 20),
			'message' => $result['inserted'] . ' holiday(s) added, ' . $result['skipped'] . ' skipped.',
		]);
	}

	public function download_holiday_template()
	{
		if (!$this->holiday_model->can_manage_company_holidays()) {
			access_denied('holiday');
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=holiday_upload_template.csv');
		$out = fopen('php://output', 'w');
		fputcsv($out, ['date', 'name', 'type', 'repeat_by_year']);
		fputcsv($out, [date('Y') . '-01-26', 'Republic Day', 'holiday', '1']);
		fputcsv($out, [date('Y') . '-08-15', 'Independence Day', 'holiday', '1']);
		fputcsv($out, [date('Y') . '-10-02', 'Gandhi Jayanti', 'holiday', '1']);
		fclose($out);
		exit;
	}

	/**
	 * Manage WFH allotment — same UX as Manage Saturday (department, multi-staff, calendar).
	 */
	public function manageWfh()
	{
		if (!attendance_permission()) {
			access_denied('timesheets');
		}

		$this->holiday_model->ensure_staff_wfh_day_table();
		$data['departments'] = $this->departments_model->get_staff_departments();
		$data['staffs'] = $this->holiday_model->get_staff_based_on_department();
		$this->load->view('admin/holiday/manage_wfh', $data);
	}

	/**
	 * Assigned WFH dates for staff in a month (intersection when multiple staff selected).
	 */
	public function get_calendar_wfh_days()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$this->holiday_model->ensure_staff_wfh_day_table();
		$staff_ids = $this->parse_staff_ids_from_request();
		$month = trim((string) $this->input->post('month'));

		if (empty($staff_ids) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
			echo json_encode(['ok' => false, 'dates' => [], 'error' => 'staff and month required']);
			return;
		}

		$from = $month . '-01';
		$to = date('Y-m-t', strtotime($from));
		$intersection = null;

		foreach ($staff_ids as $staff_id) {
			$set = $this->holiday_model->get_staff_wfh_allotment_dates($staff_id, $from, $to);
			if ($intersection === null) {
				$intersection = $set;
			} else {
				foreach ($intersection as $date => $_) {
					if (!isset($set[$date])) {
						unset($intersection[$date]);
					}
				}
			}
		}

		echo json_encode([
			'ok' => true,
			'dates' => $intersection ? array_keys($intersection) : [],
			'from' => $from,
			'to' => $to,
			'staff_count' => count($staff_ids),
		]);
	}

	/**
	 * Sync WFH allotment for one/many staff in a month (posted dates = final set).
	 */
	public function save_wfh_days()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$this->holiday_model->ensure_staff_wfh_day_table();
		$staff_ids = $this->parse_staff_ids_from_request();
		$department_id = (int) $this->input->post('department_id');
		$month = trim((string) $this->input->post('month'));
		$dates = $this->input->post('dates');

		if (empty($staff_ids) || $department_id <= 0 || !preg_match('/^\d{4}-\d{2}$/', $month)) {
			echo json_encode(['ok' => false, 'error' => 'Invalid staff, department or month']);
			return;
		}

		if (!is_array($dates)) {
			$dates = [];
		}

		$from = $month . '-01';
		$to = date('Y-m-t', strtotime($from));
		$wanted = [];

		foreach ($dates as $date) {
			$date = trim((string) $date);
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
				continue;
			}
			if ($date < $from || $date > $to) {
				continue;
			}
			// Sunday is weekly off — not assignable as WFH workday.
			if ((int) date('N', strtotime($date)) === 7) {
				continue;
			}
			$wanted[$date] = true;
		}

		$assigned_by = (int) get_staff_user_id();
		$now = date('Y-m-d H:i:s');
		$total_added = 0;
		$total_removed = 0;

		foreach ($staff_ids as $staff_id) {
			$existing_rows = $this->db->select('id, wfh_date')
				->from(db_prefix() . 'staff_wfh_day')
				->where('staffid', $staff_id)
				->where('wfh_date >=', $from)
				->where('wfh_date <=', $to)
				->get()
				->result_array();

			$existing = [];
			foreach ($existing_rows as $row) {
				$existing[$row['wfh_date']] = (int) $row['id'];
			}

			foreach ($existing as $date => $id) {
				if (!isset($wanted[$date])) {
					$this->db->where('id', $id)->delete(db_prefix() . 'staff_wfh_day');
					$this->remove_wfh_timesheet_marker($staff_id, $date);
					$total_removed++;
				}
			}

			foreach ($wanted as $date => $_) {
				if (isset($existing[$date])) {
					continue;
				}
				$this->db->insert(db_prefix() . 'staff_wfh_day', [
					'staffid' => $staff_id,
					'department_id' => $department_id,
					'wfh_date' => $date,
					'status' => 1,
					'assigned_by' => $assigned_by,
					'date_added' => $now,
				]);
				$this->upsert_wfh_timesheet_marker($staff_id, $date);
				$total_added++;
			}
		}

		$staff_label = count($staff_ids) === 1
			? '1 employee'
			: count($staff_ids) . ' employees';

		echo json_encode([
			'ok' => true,
			'added' => $total_added,
			'removed' => $total_removed,
			'dates' => array_keys($wanted),
			'staff_count' => count($staff_ids),
			'message' => 'WFH days saved for ' . $staff_label . ' (' . $total_added . ' added, ' . $total_removed . ' removed)',
		]);
	}

	public function getWfhStaffData()
	{
		if (!attendance_permission()) {
			ajax_access_denied();
		}

		$this->holiday_model->ensure_staff_wfh_day_table();
		$data = [];
		$staff_ids = $this->parse_staff_ids_from_request();
		$month = (int) $this->input->post('month');
		$year = (int) $this->input->post('year');

		if (empty($staff_ids) || $month < 1 || $month > 12 || $year < 2000) {
			echo json_encode([]);
			return;
		}

		$from = sprintf('%04d-%02d-01', $year, $month);
		$to = date('Y-m-t', strtotime($from));

		$this->db->select('w.staffid, w.department_id, w.wfh_date, w.status, d.name as department_name, s.firstname as staff_firstname, s.lastname as staff_lastname');
		$this->db->from(db_prefix() . 'staff_wfh_day w');
		$this->db->join(db_prefix() . 'departments d', 'w.department_id = d.departmentid', 'left');
		$this->db->join(db_prefix() . 'staff s', 'w.staffid = s.staffid', 'left');
		$this->db->where('w.wfh_date >=', $from);
		$this->db->where('w.wfh_date <=', $to);
		$this->db->where_in('w.staffid', $staff_ids);
		$this->db->where('w.status', 1);
		$this->db->order_by('w.wfh_date', 'ASC');
		$query = $this->db->get();

		foreach ($query->result_array() as $row) {
			$data[] = [
				'staffid' => $row['staffid'],
				'department_id' => $row['department_id'],
				'wfh_date' => $row['wfh_date'],
				'status' => $row['status'],
				'department_name' => $row['department_name'],
				'staff_firstname' => $row['staff_firstname'],
				'staff_lastname' => $row['staff_lastname'],
			];
		}

		echo json_encode($data);
	}

	protected function upsert_wfh_timesheet_marker($staff_id, $date)
	{
		$staff_id = (int) $staff_id;
		$date = date('Y-m-d', strtotime((string) $date));
		if ($staff_id <= 0 || $date === '' || $date === '1970-01-01') {
			return;
		}

		$row = $this->db->where('staff_id', $staff_id)
			->where('date_work', $date)
			->get(db_prefix() . 'timesheets_timesheet')
			->row();
		if ($row && !in_array(strtoupper(trim((string) $row->type)), ['', 'AB', 'WFH'], true)) {
			return;
		}
		if ($row) {
			$this->db->where('id', $row->id)->update(db_prefix() . 'timesheets_timesheet', ['type' => 'WFH']);
			return;
		}
		$this->db->insert(db_prefix() . 'timesheets_timesheet', [
			'staff_id' => $staff_id,
			'date_work' => $date,
			'type' => 'WFH',
			'add_from' => (int) get_staff_user_id(),
			'value' => 0,
		]);
	}

	protected function remove_wfh_timesheet_marker($staff_id, $date)
	{
		$staff_id = (int) $staff_id;
		$date = date('Y-m-d', strtotime((string) $date));
		if ($staff_id <= 0 || $date === '') {
			return;
		}

		$this->db->where('staff_id', $staff_id)
			->where('date_work', $date)
			->where('type', 'WFH')
			->where('value', 0)
			->delete(db_prefix() . 'timesheets_timesheet');
	}

}
