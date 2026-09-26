<?php

defined('BASEPATH') or exit('No direct script access allowed');



/**

 * timesheets modelf

 */

class timesheets_model extends app_model
{
	/** Request-scoped caches for Manage Attendance grid (avoids N+1 queries). */
	protected $att_cache_hour_shift = [];
	protected $att_cache_holiday = [];
	protected $att_cache_shift_work = [];
	protected $att_cache_shift_type = [];
	protected $att_cache_staff_row = [];
	protected $att_cache_staff_depts = [];
	/** @var array|null null = not preloaded */
	protected $att_day_off_rows = null;

	/** Hours from first check-in during which all punches belong to the same attendance session. */
	protected const ATTENDANCE_SESSION_WINDOW_HOURS = 13;

	public function __construct()
	{

		parent::__construct();
	}



	/**

	 * get staff

	 * @param  string $id

	 * @param  array  $where

	 * @return array

	 */

	/*public function check_approval_details_staff($staff_id){

		$this->db->where('staff_id', $staff_id);
	
		$staff_member = $this->db->get(db_prefix() . 'staff')->result_array();
		
		$firstname =  $staff_member[0]['firstname'];
		
		

		return $firstname;
		 
	 }*/
	public function get_staff($id = '', $where = [])
	{

		$select_str = '*,concat(firstname," ",lastname) as full_name';



		if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {

			$select_str .= ',(select count(*) from ' . db_prefix() . 'notifications where touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (select count(*) from ' . db_prefix() . 'todos where finished=0 and staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
		}



		$this->db->select($select_str);

		$this->db->where($where);



		if (is_numeric($id)) {

			$this->db->where('staffid', $id);

			$staff = $this->db->get(db_prefix() . 'staff')->row();



			if ($staff) {

				$staff->permissions = $this->get_staff_permissions($id);
			}



			return $staff;
		}

		$this->db->order_by('firstname', 'desc');



		return $this->db->get(db_prefix() . 'staff')->result_array();
	}

	/**

	 * get staff role

	 * @param  integer $staff_id

	 * @return object

	 */

	public function get_staff_role($staff_id)
	{



		return $this->db->query('select r.name

			from ' . db_prefix() . 'staff as s

			left join ' . db_prefix() . 'roles as r on r.roleid = s.role

			where s.staffid =' . $staff_id)->row();
	}



	/**

	 * get department name

	 * @param   $departmentid

	 * @return

	 */

	public function get_department_name($departmentid)
	{

		return $this->db->query('select ' . db_prefix() . 'departments.name from ' . db_prefix() . 'departments where departmentid = ' . $departmentid)->result_array();
	}

	public function check_type($rel_type)
	{

		$rel_types = $_REQUEST['rel_type_filter'][0];
		//echo 'SELECT  * FROM `tbltimesheets_requisition_leave` WHERE MONTH(`start_time`) =' . $rel_types;die;
		return $this->db->query('SELECT  * FROM `tbltimesheets_requisition_leave` WHERE MONTH(`start_time`) =' . $rel_types)->result_array();
	}


	/**

	 * get month

	 * @return month

	 */

	public function get_month()
	{

		$date = getdate();

		$date_1 = mktime(0, 0, 0, ($date['mon'] - 5), 1, $date['year']);

		$date_2 = mktime(0, 0, 0, ($date['mon'] - 4), 1, $date['year']);

		$date_3 = mktime(0, 0, 0, ($date['mon'] - 3), 1, $date['year']);

		$date_4 = mktime(0, 0, 0, ($date['mon'] - 2), 1, $date['year']);

		$date_5 = mktime(0, 0, 0, ($date['mon'] - 1), 1, $date['year']);

		$date_6 = mktime(0, 0, 0, $date['mon'], 1, $date['year']);

		$date_7 = mktime(0, 0, 0, ($date['mon'] + 1), 1, $date['year']);

		$date_8 = mktime(0, 0, 0, ($date['mon'] + 2), 1, $date['year']);

		$date_9 = mktime(0, 0, 0, ($date['mon'] + 3), 1, $date['year']);

		$date_10 = mktime(0, 0, 0, ($date['mon'] + 4), 1, $date['year']);

		$date_11 = mktime(0, 0, 0, ($date['mon'] + 5), 1, $date['year']);

		$date_12 = mktime(0, 0, 0, ($date['mon'] + 6), 1, $date['year']);

		$month = [
			'1' => ['id' => date('Y-m-d', $date_1), 'name' => date('m/y', $date_1)],

			'2' => ['id' => date('Y-m-d', $date_2), 'name' => date('m/y', $date_2)],

			'3' => ['id' => date('Y-m-d', $date_3), 'name' => date('m/y', $date_3)],

			'4' => ['id' => date('Y-m-d', $date_4), 'name' => date('m/y', $date_4)],

			'5' => ['id' => date('Y-m-d', $date_5), 'name' => date('m/y', $date_5)],

			'6' => ['id' => date('Y-m-d', $date_6), 'name' => date('m/y', $date_6)],

			'7' => ['id' => date('Y-m-d', $date_7), 'name' => date('m/y', $date_7)],

			'8' => ['id' => date('Y-m-d', $date_8), 'name' => date('m/y', $date_8)],

			'9' => ['id' => date('Y-m-d', $date_9), 'name' => date('m/y', $date_9)],

			'10' => ['id' => date('Y-m-d', $date_10), 'name' => date('m/y', $date_10)],

			'11' => ['id' => date('Y-m-d', $date_11), 'name' => date('m/y', $date_11)],

			'12' => ['id' => date('Y-m-d', $date_12), 'name' => date('m/y', $date_12)],

		];

		return $month;
	}

	/**

	 * set leave

	 * @param object $data

	 */

	public function set_leave($data)
	{

		$affectedrows = 0;

		$date_create = date('Y-m-d H:i:s');

		$current_year = date('Y');

		$creator = get_staff_user_id();

		foreach (json_decode($data['leave_of_the_year_data']) as $key => $value) {

			if ($value[0] != null) {

				$this->db->where('staffid', $value[0]);

				$this->db->where('year', $data['start_year_for_annual_leave_cycle']);

				$this->db->where('type_of_leave', $data['type_of_leave']);

				$data_staff_leave = $this->db->get(db_prefix() . 'timesheets_day_off')->row();

				if ($data_staff_leave) {

					$data_update['total'] = $value[4];

					$data_update['year'] = $data['start_year_for_annual_leave_cycle'];

					$data_update['type_of_leave'] = $data['type_of_leave'];

					$data_update['staffid'] = $value[0];

					if ($data_staff_leave->year != $current_year) {

						$data_update['remain'] = $value[4];

						$data_update['days_off'] = 0;
					} else {

						$data_update['remain'] = $value[4] - $data_staff_leave->days_off;
					}

					$this->db->where('id', $data_staff_leave->id);

					$this->db->update(db_prefix() . 'timesheets_day_off', $data_update);

					$affectedrows++;
				} else {

					$data_update['total'] = $value[4];

					$data_update['remain'] = $value[4];

					$data_update['days_off'] = 0;

					$data_update['year'] = $data['start_year_for_annual_leave_cycle'];

					$data_update['type_of_leave'] = $data['type_of_leave'];

					$data_update['staffid'] = $value[0];

					$this->db->insert(db_prefix() . 'timesheets_day_off', $data_update);

					$affectedrows++;
				}
			}
		}



		if (isset($data['start_year_for_annual_leave_cycle'])) {

			$this->db->where('option_name', 'start_year_for_annual_leave_cycle');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['start_year_for_annual_leave_cycle'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'start_year_for_annual_leave_cycle');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => date('Y'),

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		if (isset($data['type_of_leave'])) {

			$this->db->where('option_name', 'type_of_leave_selected');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['type_of_leave'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'type_of_leave_selected');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => 8,

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		return $affectedrows;
	}

	/**

	 * add_day_off

	 * @param  array $data

	 */

	public function add_day_off($data)
	{

		$department = '';

		$position = '';

		$repeat_by_year = 0;



		if (isset($data['department'])) {

			$department = implode(',', $data['department']);
		}

		if (isset($data['position'])) {

			$position = implode(',', $data['position']);
		}

		if (isset($data['repeat_by_year'])) {

			$repeat_by_year = $data['repeat_by_year'];
		}



		$this->db->insert(db_prefix() . 'day_off', [

			'off_reason' => $data['leave_reason'],

			'off_type' => $data['leave_type'],

			'break_date' => $this->format_date($data['break_date']),

			'department' => $department,

			'position' => $position,

			'repeat_by_year' => $repeat_by_year,

			'add_from' => get_staff_user_id(),

		]);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			return $insert_id;
		}

		return 0;
	}

	/**

	 * update day off

	 * @param   array $data

	 * @param   int $id

	 * @return   bool

	 */

	public function update_day_off($data, $id)
	{

		$department = '';

		$position = '';

		$repeat_by_year = 0;



		if (isset($data['department'])) {

			$department = implode(',', $data['department']);
		}

		if (isset($data['position'])) {

			$position = implode(',', $data['position']);
		}

		if (isset($data['repeat_by_year'])) {

			$repeat_by_year = $data['repeat_by_year'];
		}



		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'day_off', [

			'off_reason' => $data['leave_reason'],

			'off_type' => $data['leave_type'],

			'break_date' => $this->format_date($data['break_date']),

			'department' => $department,

			'position' => $position,

			'repeat_by_year' => $repeat_by_year,

			'add_from' => get_staff_user_id(),



		]);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * get break dates

	 * @param  string $type

	 * @return array

	 */

	public function get_break_dates($type = '')
	{

		if ($type != '') {

			$this->db->where('off_type', $type);

			return $this->db->get(db_prefix() . 'day_off')->result_array();
		} else {

			return $this->db->get(db_prefix() . 'day_off')->result_array();
		}
	}

	/**

	 * delete day off

	 * @param  $id

	 * @return bool

	 */

	public function delete_day_off($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'day_off');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * get_requisition

	 * @param  $type

	 * @return

	 */

	public function get_requisition($type)
	{

		return $this->db->query('select rq.id, rq.name, rq.addedfrom, rq.date_create, rq.approval_deadline, rq.status

			from ' . db_prefix() . 'request rq

			left join ' . db_prefix() . 'request_type rqt on rqt.id = rq.request_type_id

			where rqt.related_to = ' . $type)->result_array();
	}



	/**

	 * add work shift

	 * @param object $data

	 * @return integer

	 */

	public function add_work_shift($data)
	{

		unset($data['id']);

		if (!$this->check_format_date_ymd($data['from_date'])) {

			$data_insert['from_date'] = to_sql_date($data['from_date']);
		} else {

			$data_insert['from_date'] = $data['from_date'];
		}

		if (!$this->check_format_date_ymd($data['to_date'])) {

			$data_insert['to_date'] = to_sql_date($data['to_date']);
		} else {

			$data_insert['to_date'] = $data['to_date'];
		}

		if (isset($data['department'])) {

			$data_insert['department'] = implode(',', $data['department']);
		}

		if (isset($data['role'])) {

			$data_insert['position'] = implode(',', $data['role']);
		}

		if (isset($data['staff'])) {

			$data_insert['staff'] = implode(',', $data['staff']);
		}

		if (isset($data['type_shiftwork'])) {

			$data_insert['type_shiftwork'] = $data['type_shiftwork'];
		}

		$data_insert['shift_code'] = strtotime(date('Y-m-d'));

		$data_insert['shift_name'] = isset($data['shift_reason']) ? $data['shift_reason'] : '';

		$data_insert['shift_type'] = '';

		$data_insert['date_create'] = date('Y-m-d');

		$data_insert['add_from'] = get_staff_user_id();

		$this->db->insert(db_prefix() . 'work_shift', $data_insert);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			$new_list_id = [];

			$staff_id_list = [];

			$has_staff = false;

			if (isset($data['staff'])) {

				$has_staff = true;

				foreach ($data['staff'] as $key => $staff_id) {

					if (!in_array($staff_id, $staff_id_list)) {

						$staff_id_list[] = $staff_id;
					}
				}
			}



			$data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'], $has_staff);

			$list_date = $this->get_list_date($data_insert['from_date'], $data_insert['to_date']);

			$list_data_shift = [];

			if ($data['type_shiftwork'] == 'repeat_periodically') {



				$data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'], $has_staff);

				if ($has_staff == true) {

					foreach ($staff_id_list as $key => $staffid) {

						foreach ($data_shift_hanson as $h => $r_item) {

							if ($staffid == $r_item['staff_id']) {

								for ($i = 1; $i <= 7; $i++) {

									$shift_id = $r_item[$i];

									if ($shift_id != '') {

										$data_detail['staff_id'] = $staffid;

										$data_detail['number'] = $i;

										$data_detail['shift_id'] = $shift_id;

										$data_detail['work_shift_id'] = $insert_id;

										$this->db->insert(db_prefix() . 'work_shift_detail_number_day', $data_detail);
									}
								}
							}
						}
					}
				} else {

					foreach ($data_shift_hanson as $h => $r_item) {

						for ($i = 1; $i <= 7; $i++) {

							$shift_id = $r_item[$i];

							if ($shift_id != '') {

								$data_detail['staff_id'] = '';

								$data_detail['number'] = $i;

								$data_detail['shift_id'] = $shift_id;

								$data_detail['work_shift_id'] = $insert_id;

								$this->db->insert(db_prefix() . 'work_shift_detail_number_day', $data_detail);
							}
						}
					}
				}
			} elseif ($data['type_shiftwork'] == 'by_absolute_time') {



				$staff_id_list = [];

				$has_staff = false;

				if (isset($data['staff'])) {

					$has_staff = true;

					foreach ($data['staff'] as $key => $staff_id) {

						if (!in_array($staff_id, $staff_id_list)) {

							$staff_id_list[] = $staff_id;
						}
					}
				}

				$data_shift_hanson = $this->get_shift_by_absolute_time($data['shifts_detail'], $list_date, $has_staff);

				if ($has_staff == true) {

					foreach ($staff_id_list as $key => $staffid) {

						foreach ($data_shift_hanson as $h => $r_item) {

							if ($staffid == $r_item['staff_id']) {

								foreach ($list_date as $k => $date) {

									$shift_id = $r_item[date('Y-m-d', strtotime($date))];

									if ($shift_id != '' && (int) $shift_id > 0) {

										$data_detail['staff_id'] = $staffid;

										$data_detail['date'] = $date;

										$data_detail['shift_id'] = $shift_id;

										$data_detail['work_shift_id'] = $insert_id;

										$this->db->insert(db_prefix() . 'work_shift_detail', $data_detail);
									}
								}
							}
						}
					}
				} else {

					foreach ($data_shift_hanson as $h => $r_item) {

						foreach ($list_date as $k => $date) {

							$shift_id = $r_item[date('Y-m-d', strtotime($date))];

							if ($shift_id != '' && (int) $shift_id > 0) {

								$data_detail['staff_id'] = '';

								$data_detail['date'] = $date;

								$data_detail['shift_id'] = $shift_id;

								$data_detail['work_shift_id'] = $insert_id;

								$this->db->insert(db_prefix() . 'work_shift_detail', $data_detail);
							}
						}
					}
				}
			}

			return $insert_id;
		}
	}


	/**

	 * get approval id by comment

	 * @param  integer $id

	 * @return  array

	 */




	public function update_approval_comment($data)
	{

		$staff_quit_job = $data['staff_id'];
		$user_id = $_POST['userid'];
		$datas['staff_id'] =   $_POST['userid'];
		$datas['leave_id'] = $_POST['leave_id'];
		$leave_type = $_POST['leave_type'];
		$datas['approval_comment'] = $_POST['approval_comment'];
		$data['start_time'] = $_POST['start_time'];

		$data['end_time'] = $_POST['end_time'];
		$data['userid'] = $_POST['userid'];

		$datas['created_at'] = date('Y-m-d H:i:s');
		$datas['updated_at'] = date('Y-m-d H:i:s');

		//$data_leave['leave_id'] = $_POST['leave_id'];
		$data_leave['status'] = 1;
		$start_t = explode(' ', $data['start_time']);
		$start_tt = $start_t[0];

		$end_t = explode(' ', $data['end_time']);
		$end_tt = $end_t[0];

		//$datas_attd['leave_type']= $_POST['leave_type'];
		$datas_attd['staff_id'] = $_POST['userid'];
		$datas_attd['add_from'] = $data['staff_id'];
		$datas_attd['type'] = 'P';
		$datas_attd['value'] = '9.00';


		$diff = date_diff(date_create($end_tt), date_create($start_tt));
		$datediff = $diff->format("%a");
		//echo 'SELECT * FROM  tbltimesheets_timesheet WHERE staff_id = '.$user_id.' AND date_work BETWEEN "'.$data['start_time'].'" AND "'.$data['end_time'].'"  AND type in("AB","HD")';die;
		/*if($leave_type == 'present-leaves'){
			echo 'SELECT * FROM  tbltimesheets_timesheet WHERE staff_id = '.$user_id.' AND type_of_leave = "present-leaves" AND date_work BETWEEN "'.$data['start_time'].'" AND "'.$data['end_time'].'"';die;
		}die;*/

		$query = $this->db->query('SELECT * FROM  tbltimesheets_timesheet WHERE staff_id = ' . $user_id . ' AND date_work BETWEEN "' . $data['start_time'] . '" AND "' . $data['end_time'] . '"  AND type in("AB","HD")')->result_array();

		$count = count($query);
		//print_r($_GET);
		//print_r($data);die;
		if ($count > 0) {
			//echo 'update';die;
			foreach ($query as $queryy) {
				$id = $queryy['id'];
				if ($leave_type == 'present') {
					//echo 'UPDATE tbltimesheets_timesheet SET staff_id="'.$datas_attd['staff_id'].'",add_form = "'.$datas_attd['add_form'].'",type ="'.$datas_attd['type'].'" ,value="'.$datas_attd['value'].'" WHERE id='.$id;die;
					$query = 'UPDATE tbltimesheets_timesheet SET staff_id="' . $datas_attd['staff_id'] . '",add_from = "' . $datas_attd['add_from'] . '",type ="' . $datas_attd['type'] . '" ,value="' . $datas_attd['value'] . '" WHERE id=' . $id;
					$r = $this->db->query($query);
					$this->db->where('id', $datas['leave_id']);
					$test = $this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_leave);
					// $this->db->where('id', $id);
					//	$test = $this->db->update(db_prefix() . 'tbltimesheets_timesheet', $datas_attd);	
					//		print_r($test);die;
					$this->db->insert(db_prefix() . 'leave_comment', $datas);
					$insert_id = $this->db->insert_id();
					return $insert_id;
				} else {
					$query = $this->db->query('Delete from tbltimesheets_timesheet WHERE id = ' . $id);

					$this->db->where('id', $datas['leave_id']);
					$test = $this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_leave);
					$this->db->insert(db_prefix() . 'leave_comment', $datas);

					$insert_id = $this->db->insert_id();
					return $insert_id;
				}
			}
		} else {

			//$query = $this->db->query('SELECT * FROM  tbltimesheets_requisition_leave WHERE staff_id = ' . $user_id . ' AND start_time BETWEEN "' . $data['start_time'] . '" AND "' . $data['end_time'] . '"  AND status in("4")')->result_array();
			//	print_r($query[0]);die;
		//	$id  = $query[0]['id'];
		//	$query = $this->db->query('Delete from tbltimesheets_requisition_leave WHERE id = ' . $id);
			$test = $this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_leave);
			$this->db->insert(db_prefix() . 'leave_comment', $datas);

			$insert_id = $this->db->insert_id();
			return $insert_id;
		}




		//return $this->db->get(db_prefix() . 'staff_departments')->result_array();
	}


	public function update_reject_comment($data)
	{

		$staff_quit_job = $data['staff_id'];

		$dataa['leave_id'] = $_POST['leave_id'];


		$dataa['rejection_comment'] = $_POST['rejection_comment'];



		$dataa['created_at'] = date('Y-m-d H:i:s');
		$dataa['updated_at'] = date('Y-m-d H:i:s');
		$data_leave['status'] = 2;
		// Keep original day count for history/display (do not wipe to 0).
		$this->db->where('id', $data['leave_id']);
		$test = $this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_leave);

		//print_r($dataa);die;
		$this->db->insert(db_prefix() . 'leave_comment', $dataa);

		$insert_id = $this->db->insert_id();

		return $insert_id;


		//return $this->db->get(db_prefix() . 'staff_departments')->result_array();
	}


	/**

	 *  get staff id by department

	 * @param  $id

	 * @return

	 */

	public function get_staff_id_by_department($id)
	{

		$this->db->select('staffid');

		$this->db->where('departmentid', $id);

		return $this->db->get(db_prefix() . 'staff_departments')->result_array();
	}

	/**

	 * get staff id by role

	 * @param  integer $id

	 * @return  array

	 */

	public function get_staff_id_by_role($id)
	{

		$this->db->select('staffid');

		$this->db->where('role', $id);

		return $this->db->get(db_prefix() . 'staff')->result_array();
	}

	/**

	 * get list date

	 * @param   $from_date

	 * @param   $to_date

	 */

	public function get_list_date($from_date, $to_date)
	{

		$list_date = [];

		if (strtotime($from_date) > strtotime($to_date)) {

			return [];
		}

		$i = 0;

		$to_date_s = '';

		$to_date = date('Y-m-d', strtotime($to_date));

		while ($to_date_s != $to_date) {

			$next_date = date('Y-m-d', strtotime($from_date . ' +' . $i . ' day'));

			$list_date[] = $next_date;

			$to_date_s = $next_date;

			$i++;
		}

		return $list_date;
	}

	/**
	 * Off-day for sandwich leave:
	 * - Sunday always
	 * - Saturday only if this staff has that date in tblholiday (1st/3rd Sat off etc.)
	 * - Company holidays in tblday_off
	 * Working Saturdays (e.g. 2nd/4th) are NOT sandwich off-days.
	 *
	 * @param string $date Y-m-d
	 * @param int $staff_id
	 * @return bool
	 */
	public function is_sandwich_off_day($date, $staff_id = 0)
	{
		$date = date('Y-m-d', strtotime($date));
		$w = (int) date('w', strtotime($date));

		if ($w === 0) {
			return true;
		}

		if ($w === 6) {
			$staff_id = (int) $staff_id;
			if ($staff_id <= 0) {
				return false;
			}
			$row = $this->db->query(
				'SELECT id FROM ' . db_prefix() . 'holiday
				 WHERE staffid = ? AND saturday_date = ?
				 LIMIT 1',
				[$staff_id, $date]
			)->row();

			return !empty($row);
		}

		$md = date('m-d', strtotime($date));
		$row = $this->db->query(
			'SELECT id FROM ' . db_prefix() . 'day_off
			 WHERE break_date = ?
			    OR (repeat_by_year = 1 AND DATE_FORMAT(break_date, "%m-%d") = ?)
			 LIMIT 1',
			[$date, $md]
		)->row();

		return !empty($row);
	}

	/**
	 * Whether a date inside this leave application is a full leave day.
	 * Half-day: start Session 2 only, or end Session 1 only.
	 */
	public function is_full_day_within_leave_application($date, $start_date, $end_date, $start_session, $end_session)
	{
		$date = date('Y-m-d', strtotime($date));
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$start_session = (int) $start_session ?: 1;
		$end_session = (int) $end_session ?: 2;

		if ($date < $start_date || $date > $end_date) {
			return false;
		}

		if ($start_date === $end_date) {
			return ($start_session === 1 && $end_session === 2);
		}

		if ($date === $start_date && $start_session === 2) {
			return false;
		}
		if ($date === $end_date && $end_session === 1) {
			return false;
		}

		return true;
	}

	/**
	 * Staff has pending/approved leave covering a date (for adjacent sandwich).
	 */
	public function staff_has_leave_covering_date($staff_id, $date, $exclude_leave_id = 0)
	{
		return $this->staff_leave_coverage_on_date($staff_id, $date, $exclude_leave_id) !== 'none';
	}

	/**
	 * Coverage strength on a date from other leave rows: none|half|full.
	 * Half-day leave types and single-day 0.5 applications count as half.
	 */
	public function staff_leave_coverage_on_date($staff_id, $date, $exclude_leave_id = 0)
	{
		$staff_id = (int) $staff_id;
		$date = date('Y-m-d', strtotime($date));
		if ($staff_id <= 0 || !$date) {
			return 'none';
		}

		$sql = 'SELECT id, type_of_leave, start_time, end_time, number_of_leaving_day
			FROM ' . db_prefix() . 'timesheets_requisition_leave
			WHERE staff_id = ?
			  AND status IN (0, 1)
			  AND type_of_leave NOT IN ("present")
			  AND DATE(start_time) <= ?
			  AND DATE(end_time) >= ?';
		$params = [$staff_id, $date, $date];
		if ((int) $exclude_leave_id > 0) {
			$sql .= ' AND id != ?';
			$params[] = (int) $exclude_leave_id;
		}

		$rows = $this->db->query($sql, $params)->result_array();
		if (empty($rows)) {
			return 'none';
		}

		$best = 'none';
		foreach ($rows as $row) {
			$type = (string) ($row['type_of_leave'] ?? '');
			if (in_array($type, ['half-days', 'unpaid-half-days', 'short-leaves'], true)) {
				$best = ($best === 'full') ? 'full' : 'half';
				continue;
			}

			$s = date('Y-m-d', strtotime($row['start_time']));
			$e = date('Y-m-d', strtotime($row['end_time']));
			$days = (float) ($row['number_of_leaving_day'] ?? 0);

			if ($s === $e) {
				if ($days > 0 && $days < 1) {
					$best = ($best === 'full') ? 'full' : 'half';
					continue;
				}
				return 'full';
			}

			if ($date !== $s && $date !== $e) {
				return 'full';
			}

			if ($days >= 1) {
				return 'full';
			}

			$best = ($best === 'full') ? 'full' : 'half';
		}

		return $best;
	}

	/**
	 * Nearest non-off day before/after a date.
	 */
	public function nearest_working_day($date, $staff_id, $direction = 'before')
	{
		$date = date('Y-m-d', strtotime($date));
		$step = ($direction === 'after') ? '+1 day' : '-1 day';
		$probe = date('Y-m-d', strtotime($date . ' ' . $step));
		for ($i = 0; $i < 14; $i++) {
			if (!$this->is_sandwich_off_day($probe, $staff_id)) {
				return $probe;
			}
			$probe = date('Y-m-d', strtotime($probe . ' ' . $step));
		}

		return $probe;
	}

	/**
	 * Calculate leave days with sandwich policy.
	 * Off days (Sun, staff Sat-off, holidays) between leave are counted only when
	 * BOTH adjacent working days are FULL leave days.
	 * Half-day Friday + Monday => no sandwich. Full Friday + Monday => sandwich.
	 *
	 * @return array
	 */
	public function calculate_leave_days_with_sandwich($staff_id, $start_date, $end_date, $start_session = 1, $end_session = 2, $exclude_leave_id = 0)
	{
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$original_start = $start_date;
		$original_end = $end_date;
		$start_session = (int) $start_session ?: 1;
		$end_session = (int) $end_session ?: 2;
		$staff_id = (int) $staff_id;

		$result = [
			'days' => 0,
			'sandwich_days' => 0,
			'sandwich_dates' => [],
			'message' => '',
			'start' => $start_date,
			'end' => $end_date,
		];

		if (!$start_date || !$end_date || strtotime($end_date) < strtotime($start_date)) {
			return $result;
		}

		$sandwich_dates = [];
		$extra_sandwich_outside = 0;

		// Adjacent leave BEFORE start — only if this leave starts as a FULL day (Session 1).
		if ($start_session === 1) {
			$probe = date('Y-m-d', strtotime($start_date . ' -1 day'));
			$block = [];
			while ($this->is_sandwich_off_day($probe, $staff_id)) {
				$block[] = $probe;
				$probe = date('Y-m-d', strtotime($probe . ' -1 day'));
				if (count($block) > 14) {
					break;
				}
			}
			if ($block && $this->staff_leave_coverage_on_date($staff_id, $probe, $exclude_leave_id) === 'full') {
				foreach ($block as $d) {
					$sandwich_dates[$d] = true;
				}
				$extra_sandwich_outside += count($block);
			}
		}

		// Adjacent leave AFTER end — only if this leave ends as a FULL day (Session 2).
		if ($end_session === 2) {
			$probe = date('Y-m-d', strtotime($end_date . ' +1 day'));
			$block = [];
			while ($this->is_sandwich_off_day($probe, $staff_id)) {
				$block[] = $probe;
				$probe = date('Y-m-d', strtotime($probe . ' +1 day'));
				if (count($block) > 14) {
					break;
				}
			}
			if ($block && $this->staff_leave_coverage_on_date($staff_id, $probe, $exclude_leave_id) === 'full') {
				foreach ($block as $d) {
					$sandwich_dates[$d] = true;
				}
				$extra_sandwich_outside += count($block);
			}
		}

		$list = $this->get_list_date($original_start, $original_end);
		$calendar_days = count($list);

		// Group contiguous off days inside the applied range; sandwich the whole
		// block only when BOTH bordering working days are FULL leave days.
		// Example: Fri half + Sat/Sun off + Mon => no sandwich.
		// Example: Fri full + Sat/Sun off + Mon => sandwich.
		$excluded_off_days = 0;
		$i = 0;
		$n = count($list);
		while ($i < $n) {
			$d = $list[$i];
			if ($d === $original_start || $d === $original_end || !$this->is_sandwich_off_day($d, $staff_id)) {
				$i++;
				continue;
			}

			$block = [];
			while ($i < $n) {
				$cur = $list[$i];
				if ($cur === $original_start || $cur === $original_end || !$this->is_sandwich_off_day($cur, $staff_id)) {
					break;
				}
				$block[] = $cur;
				$i++;
			}
			if (!$block) {
				continue;
			}

			$prev = $this->nearest_working_day($block[0], $staff_id, 'before');
			$next = $this->nearest_working_day($block[count($block) - 1], $staff_id, 'after');
			$prev_full = $this->is_full_day_within_leave_application(
				$prev,
				$original_start,
				$original_end,
				$start_session,
				$end_session
			);
			$next_full = $this->is_full_day_within_leave_application(
				$next,
				$original_start,
				$original_end,
				$start_session,
				$end_session
			);

			if ($prev_full && $next_full) {
				foreach ($block as $bd) {
					$sandwich_dates[$bd] = true;
				}
			} else {
				$excluded_off_days += count($block);
			}
		}

		$days = (float) $calendar_days;
		if ($calendar_days === 1) {
			if ($start_session === $end_session) {
				$days = 0.5;
			} elseif ($start_session < $end_session) {
				$days = 1.0;
			} else {
				$days = 0.5;
			}
		} else {
			if ($start_session === 2) {
				$days -= 0.5;
			}
			if ($end_session === 1) {
				$days -= 0.5;
			}
		}

		// Remove off days that did not qualify for sandwich.
		$days -= (float) $excluded_off_days;
		// Add off days outside the applied range that sandwich with other leave.
		$days += (float) $extra_sandwich_outside;

		if ($days < 0) {
			$days = 0;
		}
		$days = round($days * 2) / 2;

		$sandwich_list = array_keys($sandwich_dates);
		sort($sandwich_list);
		$sandwich_count = (float) count($sandwich_list);

		$message = '';
		if ($sandwich_count > 0) {
			$labels = [];
			foreach ($sandwich_list as $d) {
				$labels[] = date('d M', strtotime($d));
			}
			$message = 'Sandwich leave applied: ' . $sandwich_count . ' weekend/holiday day(s) counted (' . implode(', ', $labels) . ').';
		}

		$result['days'] = $days;
		$result['sandwich_days'] = $sandwich_count;
		$result['sandwich_dates'] = $sandwich_list;
		$result['message'] = $message;
		$result['start'] = $original_start;
		$result['end'] = $original_end;

		return $result;
	}

	/**

	 * get shift repeat periodically

	 * @param  string  $shifts_detail

	 * @param  boolean $has_staff

	 * @return array

	 */

	public function get_shift_repeat_periodically($shifts_detail, $has_staff = true)
	{

		$shifts_detail = explode(',', $shifts_detail);

		$es_detail = [];

		$row = [];

		$rq_val = [];

		$header = [];

		if ($has_staff == true) {

			$header[] = 'staff_id';

			$header[] = 'staff';

			$header[] = '1';

			$header[] = '2';

			$header[] = '3';

			$header[] = '4';

			$header[] = '5';

			$header[] = '6';

			$header[] = '7';

			for ($i = 0; $i < count($shifts_detail); $i++) {

				$row[] = $shifts_detail[$i];

				if ((($i + 1) % 9) == 0) {

					$rq_val[] = array_combine($header, $row);

					$row = [];
				}
			}
		} else {

			$header[] = '1';

			$header[] = '2';

			$header[] = '3';

			$header[] = '4';

			$header[] = '5';

			$header[] = '6';

			$header[] = '7';

			for ($i = 0; $i < count($shifts_detail); $i++) {

				$row[] = $shifts_detail[$i];

				if ((($i + 1) % 7) == 0) {

					$rq_val[] = array_combine($header, $row);

					$row = [];
				}
			}
		}

		return $rq_val;
	}

	/**

	 * [get_shift_by_absolute_time

	 * @param  integer $shifts_detail

	 * @param  array $list_date

	 * @param  boolean $has_staff

	 * @return integer

	 */



	public function get_shift_by_absolute_time($shifts_detail, $list_date, $has_staff = true)
	{

		$shifts_detail = explode(',', $shifts_detail);

		$es_detail = [];

		$row = [];

		$rq_val = [];

		$header = [];

		if ($has_staff == true) {

			$header[] = 'staff_id';

			$header[] = 'staff';

			$total_date = 0;

			foreach ($list_date as $date) {

				$header[] = $date;

				$total_date++;
			}



			for ($i = 0; $i < count($shifts_detail); $i++) {

				$row[] = $shifts_detail[$i];



				if ((($i + 1) % ($total_date + 2)) == 0) {

					$rq_val[] = array_combine($header, $row);

					$row = [];
				}
			}
		} else {

			$total_date = 0;

			foreach ($list_date as $date) {

				$header[] = $date;

				$total_date++;
			}

			for ($i = 0; $i < count($shifts_detail); $i++) {

				$row[] = $shifts_detail[$i];

				if ((($i + 1) % $total_date) == 0) {

					$rq_val[] = array_combine($header, $row);

					$row = [];
				}
			}
		}

		return $rq_val;
	}

	/**

	 * update work shift

	 * @param   $data

	 * @return  boolean

	 */

	public function update_work_shift($data)
	{

		if (!$this->check_format_date_ymd($data['from_date'])) {

			$data_insert['from_date'] = to_sql_date($data['from_date']);
		} else {

			$data_insert['from_date'] = $data['from_date'];
		}

		if (!$this->check_format_date_ymd($data['to_date'])) {

			$data_insert['to_date'] = to_sql_date($data['to_date']);
		} else {

			$data_insert['to_date'] = $data['to_date'];
		}

		if (isset($data['department'])) {

			$data_insert['department'] = implode(',', $data['department']);
		} else {

			$data_insert['department'] = '';
		}

		if (isset($data['role'])) {

			$data_insert['position'] = implode(',', $data['role']);
		} else {

			$data_insert['position'] = '';
		}

		if (isset($data['staff'])) {

			$data_insert['staff'] = implode(',', $data['staff']);
		} else {

			$data_insert['staff'] = '';
		}

		if (isset($data['type_shiftwork'])) {

			$data_insert['type_shiftwork'] = $data['type_shiftwork'];
		}

		$data_insert['shift_code'] = strtotime(date('Y-m-d'));

		$data_insert['shift_name'] = '';

		$data_insert['shift_type'] = '';

		$data_insert['date_create'] = date('Y-m-d');

		$data_insert['add_from'] = get_staff_user_id();

		$old_type_shiftwork = '';

		$data_old = $this->get_work_shift($data['id']);

		if ($data_old) {

			$old_type_shiftwork = $data_old->type_shiftwork;
		}

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'work_shift', $data_insert);



		$new_list_id = [];

		$staff_id_list = [];



		$has_staff = false;

		if (isset($data['staff'])) {

			$has_staff = true;

			foreach ($data['staff'] as $key => $staff_id) {

				if (!in_array($staff_id, $staff_id_list)) {

					$staff_id_list[] = $staff_id;
				}
			}
		}



		$list_date = $this->get_list_date($data_insert['from_date'], $data_insert['to_date']);



		$list_data_shift = [];

		if ($data['type_shiftwork'] == 'repeat_periodically') {

			$this->db->where('work_shift_id', $data['id']);

			$this->db->delete(db_prefix() . 'work_shift_detail_number_day');

			$data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'], $has_staff);

			if ($has_staff == true) {

				foreach ($staff_id_list as $key => $staffid) {

					foreach ($data_shift_hanson as $h => $r_item) {

						if ($staffid == $r_item['staff_id']) {

							for ($i = 1; $i <= 7; $i++) {

								$shift_id = $r_item[$i];

								if ($shift_id != '') {

									$data_detail['number'] = $i;

									$data_detail['staff_id'] = $staffid;

									$data_detail['shift_id'] = $shift_id;

									$data_detail['work_shift_id'] = $data['id'];

									$this->db->insert(db_prefix() . 'work_shift_detail_number_day', $data_detail);
								}
							}
						}
					}
				}
			} else {

				foreach ($data_shift_hanson as $h => $r_item) {

					for ($i = 1; $i <= 7; $i++) {

						$shift_id = $r_item[$i];

						if ($shift_id != '' && $shift_id != 0) {

							$data_detail['staff_id'] = '';

							$data_detail['number'] = $i;

							$data_detail['shift_id'] = $shift_id;

							$data_detail['work_shift_id'] = $data['id'];

							$this->db->insert(db_prefix() . 'work_shift_detail_number_day', $data_detail);
						}
					}
				}
			}

			if ($old_type_shiftwork != 'repeat_periodically') {

				$this->db->where('work_shift_id', $data['id']);

				$this->db->delete(db_prefix() . 'work_shift_detail');
			}
		} elseif ($data['type_shiftwork'] == 'by_absolute_time') {

			$this->db->where('work_shift_id', $data['id']);

			$this->db->delete(db_prefix() . 'work_shift_detail');

			$data_shift_hanson = $this->get_shift_by_absolute_time($data['shifts_detail'], $list_date, $has_staff);

			if ($has_staff == true) {

				foreach ($staff_id_list as $key => $staffid) {

					foreach ($data_shift_hanson as $h => $r_item) {

						if ($staffid == $r_item['staff_id']) {

							foreach ($list_date as $k => $date) {

								$shift_id = $r_item[date('Y-m-d', strtotime($date))];

								if ($shift_id != '' && $shift_id != 0) {

									$data_detail['staff_id'] = $staffid;

									$data_detail['date'] = $date;

									$data_detail['shift_id'] = $shift_id;

									$data_detail['work_shift_id'] = $data['id'];

									$this->db->insert(db_prefix() . 'work_shift_detail', $data_detail);
								}
							}
						}
					}
				}
			} else {

				foreach ($data_shift_hanson as $h => $r_item) {

					foreach ($list_date as $k => $date) {

						$shift_id = $r_item[date('Y-m-d', strtotime($date))];

						if ($shift_id != '' && $shift_id != 0) {

							$data_detail['date'] = $date;

							$data_detail['staff_id'] = 0;

							$data_detail['shift_id'] = $shift_id;

							$data_detail['work_shift_id'] = $data['id'];

							$this->db->insert(db_prefix() . 'work_shift_detail', $data_detail);
						}
					}
				}
			}

			if ($old_type_shiftwork != 'by_absolute_time') {

				$this->db->where('work_shift_id', $data['id']);

				$this->db->delete(db_prefix() . 'work_shift_detail_number_day');
			}
		}

		return true;
	}

	/**

	 * get data edit shift

	 * @param  integer $work_shift

	 * @return array

	 */

	public function get_data_edit_shift($work_shift)
	{

		$this->db->where('id', $work_shift);

		$shift = $this->db->get(db_prefix() . 'work_shift')->row();

		$data['shifts_detail'] = $shift->shifts_detail;

		if (isset($data['shifts_detail'])) {

			$data['shifts_detail'] = explode(',', $data['shifts_detail']);

			$shifts_detail_col = ['detail', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday_even', 'saturday_odd', 'sunday'];

			$row = [];

			$shifts_detail = [];

			for ($i = 0; $i < count($data['shifts_detail']); $i++) {

				$row[] = $data['shifts_detail'][$i];

				if ((($i + 1) % 9) == 0) {

					$shifts_detail[] = array_combine($shifts_detail_col, $row);

					$row = [];
				}
			}

			unset($data['shifts_detail']);
		}

		return $shifts_detail;
	}



	/**

	 * get shift

	 * @param integer $id

	 * @return object or array

	 */

	public function get_shifts($id = '')
	{

		if ($id != '') {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'work_shift')->row();
		} else {

			return $this->db->get(db_prefix() . 'work_shift')->result_array();
		}
	}

	public function delete_shift($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'work_shift');

		if ($this->db->affected_rows() > 0) {

			$this->db->where('work_shift_id', $id);

			$this->db->delete(db_prefix() . 'work_shift_detail');

			$this->db->where('work_shift_id', $id);

			$this->db->delete(db_prefix() . 'work_shift_detail_number_day');

			return true;
		}

		return false;
	}

	/**

	 * get timesheets ts by month

	 * @param integer $month

	 * @param integer $year

	 * @param integer $staffid

	 * @return array

	 */

	public function get_timesheets_ts_by_month($month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;
		$from_date = sprintf('%04d-%02d-01', $year, $month);
		$to_date = date('Y-m-t', strtotime($from_date));

		$check_latch_timesheet = $this->check_latch_timesheet($month . '-' . $year);

		if ($check_latch_timesheet) {
			$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date_work between ' . $this->db->escape($from_date) . ' and ' . $this->db->escape($to_date) . ' and latch = 1';
		} else {
			$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date_work between ' . $this->db->escape($from_date) . ' and ' . $this->db->escape($to_date);
		}

		return $this->db->query($query)->result_array();
	}



	/**

	 * get timesheets ts by month and staff id for manager and users- by bhavya

	 * @param integer $month

	 * @param integer $year

	 * @param integer $staffid

	 * @return array

	 */

	public function get_timesheets_ts_by_month_for_managers_and_users($month, $year, $all_staff)
	{
		$staffids = array();

		if (attendance_permission()) {
			foreach ($all_staff as $staff) {
				$staffids[] = $staff['staffid'];
			}
		} else {
			$staffids[] = get_staff_user_id();
		}


		$staffids = array_map('intval', $staffids);
		$staffids_in_str = implode(',', $staffids);
		if ($staffids_in_str === '') {
			return [];
		}

		$month = (int) $month;
		$year = (int) $year;
		$from_date = sprintf('%04d-%02d-01', $year, $month);
		$to_date = date('Y-m-t', strtotime($from_date));

		$check_latch_timesheet = $this->check_latch_timesheet($month . '-' . $year);

		if ($check_latch_timesheet) {
			$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date_work between ' . $this->db->escape($from_date) . ' and ' . $this->db->escape($to_date) . ' AND latch = 1 AND staff_id IN (' . $staffids_in_str . ')';
		} else {
			$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date_work between ' . $this->db->escape($from_date) . ' and ' . $this->db->escape($to_date) . ' AND staff_id IN (' . $staffids_in_str . ')';
		}

		return $this->db->query($query)->result_array();
	}

	/**

	 * get ts by date and staff

	 * @param  $date

	 * @param  $staff

	 * @return

	 */

	public function get_ts_by_date_and_staff($date, $staff)
	{

		$this->db->where('date_work', $date);

		$this->db->where('staff_id', $staff);

		return $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();
	}



	/**

	 * staff chart by age

	 */

	public function staff_chart_by_age()
	{

		$staffs = $this->staff_model->get();



		$chart = [];

		$status_1 = ['name' => _l('18-24'), 'color' => '#777', 'y' => 0, 'z' => 100];

		$status_2 = ['name' => _l('25-29'), 'color' => '#fc2d42', 'y' => 0, 'z' => 100];

		$status_3 = ['name' => _l('30 - 39'), 'color' => '#03a9f4', 'y' => 0, 'z' => 100];

		$status_4 = ['name' => _l('40-60'), 'color' => '#ff6f00', 'y' => 0, 'z' => 100];



		foreach ($staffs as $staff) {



			$diff = date_diff(date_create(), date_create($staff['birthday']));

			$age = $diff->format('%y');



			if ($age >= 18 && $age <= 24) {

				$status_1['y'] += 1;
			} elseif ($age >= 25 && $age <= 29) {

				$status_2['y'] += 1;
			} elseif ($age >= 30 && $age <= 39) {

				$status_3['y'] += 1;
			} elseif ($age >= 40 && $age <= 60) {

				$status_4['y'] += 1;
			}
		}

		if ($status_1['y'] > 0) {

			array_push($chart, $status_1);
		}

		if ($status_2['y'] > 0) {

			array_push($chart, $status_2);
		}

		if ($status_3['y'] > 0) {

			array_push($chart, $status_3);
		}

		if ($status_4['y'] > 0) {

			array_push($chart, $status_4);
		}



		return $chart;
	}



	/**

	 * get_timesheets_ts_by_year

	 * @param integre $staff_id

	 * @param integre $year

	 * @return integre

	 */

	public function get_timesheets_ts_by_year($staff_id, $year)
	{



		return $this->db->query('select * from ' . db_prefix() . 'timesheets_timesheet where year(date_work) = ' . $year . ' and staff_id = ' . $staff_id . ' order by date_work asc ')->result_array();
	}

	/**

	 * get request leave

	 * @param  integer $id

	 */

	public function get_request_leave($id = '')
	{

		$this->load->model('staff_model');

		if ($id == '') {

			return $this->db->get(db_prefix() . 'timesheets_requisition_leave')->result_array();
		} else {

			$this->db->where('id', $id);

			$requisition = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

			if ($requisition) {

				$staff = $this->staff_model->get($requisition->staff_id);

				if ($staff) {

					$requisition->email = $staff->email;



					$requisition->name = $this->getdepartment_name($requisition->staff_id)->name;

					$requisition->name_role = $this->get_staff_role($requisition->staff_id)->name;
				}

				$requisition->attachments = $this->get_requisition_attachments($id);
			}

			return $requisition;
		}
	}

	/**

	 * getdepartment name

	 * @param  integer $staffid

	 * @return object

	 */

	public function getdepartment_name($staffid)
	{

		return $this->db->query('select s.staffid, d.departmentid ,d.name

			from ' . db_prefix() . 'staff as s

			left join ' . db_prefix() . 'staff_departments as sd on sd.staffid = s.staffid

			left join ' . db_prefix() . 'departments d on d.departmentid = sd.departmentid

			where s.staffid in (' . $staffid . ')

			order by d.departmentid,s.staffid')->row();
	}

	/**

	 * get requisition attachments

	 * @param  integer $id

	 * @param  integer $attachment_id

	 * @param  array  $where

	 * @return array

	 */

	public function get_requisition_attachments($id = '', $attachment_id = '', $where = [])
	{

		$this->db->where($where);

		$idishash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;

		if (is_numeric($attachment_id) || $idishash) {

			$this->db->where($idishash ? 'attachment_key' : 'id', $attachment_id);



			return $this->db->get(db_prefix() . 'files')->row();
		}

		$this->db->where('rel_id', $id);

		$this->db->where('rel_type', 'requisition');

		$this->db->order_by('dateadded', 'desc');



		return $this->db->get(db_prefix() . 'files')->result_array();
	}

	/**

	 * get number of days off

	 * @param  integer $staffid

	 * @return integer

	 */

	public function get_number_of_days_off($staffid = 0)
	{

		if ($staffid == 0) {

			$staffid = get_staff_user_id();
		}

		$staff = $this->staff_model->get($staffid);



		$leave_position = $this->get_timesheets_option_api('timesheets_leave_position');

		// $leave_contract_type = $this->get_timesheets_option_api('timesheets_leave_contract_type');

		// $leave_start_date = $this->get_timesheets_option_api('timesheets_leave_start_date');

		// $max_leave_in_year = $this->get_timesheets_option_api('timesheets_max_leave_in_year');

		// $start_leave_from_month = $this->get_timesheets_option_api('timesheets_start_leave_from_month');

		// $start_leave_to_month = $this->get_timesheets_option_api('timesheets_start_leave_to_month');



		// $add_new_leave_month_from_date = $this->get_timesheets_option_api('timesheets_add_new_leave_month_from_date');



		// $accumulated_leave_to_month = $this->get_timesheets_option_api('timesheets_accumulated_leave_to_month');

		// $leave_contract_sign_day = $this->get_timesheets_option_api('timesheets_leave_contract_sign_day');

		// $add_leave_after_probationary_period = $this->get_timesheets_option_api('add_leave_after_probationary_period');

		// $start_date_seniority = $this->get_timesheets_option_api('timesheets_start_date_seniority');

		// $seniority_year = $this->get_timesheets_option_api('timesheets_seniority_year');

		// $seniority_year_leave = $this->get_timesheets_option_api('timesheets_seniority_year_leave');

		// $next_year = $this->get_timesheets_option_api('timesheets_next_year');

		// $next_year_leave = $this->get_timesheets_option_api('timesheets_next_year_leave');



		$alow_borrow_leave = $this->get_timesheets_option_api('alow_borrow_leave');

		if ($leave_position != '') {

			$leave_position = explode(', ', $leave_position);

			if (!in_array($staff->role, $leave_position)) {

				return 0;
			}
		}



		$count = 0;

		$day_off = $this->get_day_off($staffid, '', 8);

		if ($day_off) {

			if ($alow_borrow_leave == 1) {

				$count = $day_off->total;
			}
		}

		return $count;
	}

	/**

	 * check whether column exists in a table

	 * custom function because codeigniter is caching the tables and this is causing issues in migrations

	 * @param  string $column column name to check

	 * @param  string $table table name to check

	 * @return boolean

	 */



	public function get_timesheets_option_api($name)
	{

		$options = [];

		$val = '';

		$name = trim($name);



		if (!isset($options[$name])) {

			// is not auto loaded

			$this->db->select('option_val');

			$this->db->where('option_name', $name);

			$row = $this->db->get(db_prefix() . 'timesheets_option')->row();

			if ($row) {

				$val = $row->option_val;
			}
		} else {

			$val = $options[$name];
		}



		return $val;
	}



	/**

	 * gets the day off.

	 * @param      integer  $staffid  the staffid

	 * @param      integer  $year     the year

	 * @return     <type>          the day off.

	 */

	public function get_day_off($staffid = 0, $year = 0, $type_of_leave = '')
	{

		if ($staffid == 0 || $staffid == '') {

			$staffid = get_staff_user_id();
		}

		if ($year == 0 || $year == '') {

			$year = date('Y');
		}

		if ($type_of_leave == '') {

			$type_of_leave = '8';
		}

		$day_off = $this->db->query('select * from ' . db_prefix() . 'timesheets_day_off where staffid = ' . $staffid . ' and year = ' . $year . ' and type_of_leave = "' . $type_of_leave . '"')->row();

		return $day_off;
	}

	/**

	 * check_approval_details

	 * @param   $rel_id

	 * @param   $rel_type

	 * @return   bool

	 */

	public function check_approval_details($rel_id, $rel_type)
	{

		$this->db->where('rel_id', $rel_id);

		$this->db->where('rel_type', $rel_type);

		$approve_status = $this->db->get(db_prefix() . 'timesheets_approval_details')->result_array();

		if ($approve_status && count($approve_status) > 0) {

			$count = count($approve_status);

			foreach ($approve_status as $value) {

				if ($value['approve'] == 2) {

					return 'reject';
				}

				if ($value['approve'] == 0) {

					$value['staffid'] = explode(', ', $value['staffid']);

					return $value;
				}
			}

			return true;
		}

		return false;
	}

	/**

	 * get list approval details

	 * @param  integer $rel_id

	 * @param  string $rel_type

	 * @return  array

	 */

	public function get_list_approval_details($rel_id, $rel_type)
	{

		$this->db->select('*');

		$this->db->where('rel_id', $rel_id);

		$this->db->where('rel_type', $rel_type);

		return $this->db->get(db_prefix() . 'timesheets_approval_details')->result_array();
	}



	/**

	 * cancel request

	 * @param object $data

	 * @param  integer $staffid

	 * @return

	 */

	public function cancel_request($data, $staffid = '')
	{

		$rel_id = $data['rel_id'];

		$rel_type = $data['rel_type'];

		$data_update = [];



		$this->db->where('rel_id', $rel_id);

		$this->db->where('rel_type', $rel_type);

		$this->db->delete(db_prefix() . 'timesheets_approval_details');



		switch (strtolower($rel_type)) {



			case 'leave':

				$data_update['status'] = 0;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_update);



				$this->db->where('id', $rel_id);

				$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();



				$st = $requisition_leave->start_time;

				$et = $requisition_leave->end_time;



				if ($staffid != '') {

					$staff_id = $staffid;
				} else {

					$staff_id = get_staff_user_id();
				}



				$type = '';

				switch ($requisition_leave->type_of_leave) {

					case 1:

						$type = 'SI';

						break;

					case 2:

						$type = 'M';

						break;

					case 3:

						$type = 'R';

						break;

						// case 4:

						// 	$type = 'P';

						// 	break;

					case 6:

						$type = 'PO';

						break;

					case 7:

						$type = 'ME';

						break;

					case 8:

						$type = 'AL';



						$day_off = $this->get_day_off($requisition_leave->staff_id);

						// Number of leaving day

						$dd = $requisition_leave->number_of_leaving_day;

						$update_days_off = $day_off->days_off - $dd;

						$update_remain = $day_off->total - $update_days_off;

						$this->db->where('staffid', $requisition_leave->staff_id);

						$this->db->where('year', date('Y'));

						$this->db->update(db_prefix() . 'timesheets_day_off', [

							'remain' => $update_remain,

							'days_off' => $update_days_off,

						]);

						break;
				}



				$this->db->where('relate_id', $rel_id);

				$this->db->where('relate_type', 'leave');

				$this->db->delete(db_prefix() . 'timesheets_timesheet');



				return true;

				break;

			default:

				return false;

				break;
		}
	}

	/**

	 * get value by time

	 * @param  integer $st

	 * @param  string $et

	 * @param  string $staff_id

	 * @return  decimal

	 */

	public function get_value_by_time($st, $et = '', $staff_id = '')
	{



		if ($staff_id == '') {

			$staffid = get_staff_user_id();
		} else {

			$staffid = $staff_id;
		}



		$work_shift = $this->get_data_edit_shift_by_staff($staffid);

		$date_time = $this->get_date_time($work_shift);



		$time = strtotime($st);



		$lunch_break = 0;



		$work_time = 0;



		$t = 0;



		$staff_sc = $this->timesheets_model->get_staff_shift_applicable_object();

		$list_staff_sc = [];

		foreach ($staff_sc as $key => $value) {

			$list_staff_sc[] = $value['staffid'];
		}



		if (in_array($staffid, $list_staff_sc)) {

			$shift = $this->timesheets_model->get_shiftwork_sc_date_and_staff(date('Y-m-d', $time), $staffid);

			if (isset($shift)) {

				$work_shift = $this->timesheets_model->get_shift_sc($shift);



				$ws_day = '<li class="list-group-item justify-content-between">' . _l('work_times') . ': ' . $work_shift->time_start_work . ' - ' . $work_shift->time_end_work . '</li><li class="list-group-item justify-content-between">' . _l('lunch_break') . ': ' . $work_shift->start_lunch_break_time . ' - ' . $work_shift->end_lunch_break_time . '</li>';



				$work_time = (strtotime($work_shift->time_end_work) - strtotime($work_shift->time_start_work)) / 60;

				$lunch_break = (strtotime($work_shift->end_lunch_break_time) - strtotime($work_shift->start_lunch_break_time)) / 60;



				if (strtotime(date('H:i:s', strtotime($st))) < strtotime($work_shift->time_start_work . ':00')) {

					$stime = strtotime($work_shift->time_start_work . ':00');
				} else {

					$stime = strtotime(date('H:i:s', strtotime($st)));
				}



				if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

					$_st = strtotime(date('H:i:s', strtotime($st)));

					$_et = strtotime(date('H:i:s', strtotime($et)));


					// commenting this code to make start and end time more than shift hour
					//					if ($_st < strtotime($work_shift->time_start_work . ':00')) {
					//
					//						$_st = strtotime($work_shift->time_start_work . ':00');
					//					} elseif ($_st > strtotime($work_shift->start_lunch_break_time . ':00') && $_st < strtotime($work_shift->end_lunch_break_time . ':00')) {
					//
					//						$_st = strtotime($work_shift->end_lunch_break_time . ':00');
					//					}

					//					if ($_et > strtotime($work_shift->time_end_work . ':00')) {
					//
					//						$_et = strtotime($work_shift->time_end_work . ':00');
					//					}

					if (strtotime($work_shift->start_lunch_break_time . ':00') > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

						$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
					} else {

						$t = ($_et - $_st) / 3600;
					}
				} else {



					if (strtotime($work_shift->start_lunch_break_time . ':00') > $stime) {

						$t = (strtotime($work_shift->time_end_work . ':00') - $stime) / 3600 - ($lunch_break / 60);
					} else {

						$t = (strtotime($work_shift->time_end_work . ':00') - $stime) / 3600;
					}
				}
			}
		} else {

			switch (date('n', $time)) {

				case 1:

					$lunch_break = $date_time['lunch_break']['monday'];

					$work_time = $date_time['work_time']['monday'];



					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['monday'])) {

						$stime = strtotime($date_time['late_for_work']['monday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}



					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));

						if ($_st < strtotime($date_time['late_for_work']['monday'])) {

							$_st = strtotime($date_time['late_for_work']['monday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['monday']) && $_st < strtotime($date_time['start_afternoon_shift']['monday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['monday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['monday'])) {

							$_et = strtotime($date_time['come_home_early']['monday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['monday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {



						if (strtotime($date_time['start_lunch_break_time']['monday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['monday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['monday']) - $stime) / 3600;
						}
					}



					break;

				case 2:

					$lunch_break = $date_time['lunch_break']['tuesday'];

					$work_time = $date_time['work_time']['tuesday'];



					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['tuesday'])) {

						$stime = strtotime($date_time['late_for_work']['tuesday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}

					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));

						if ($_st < strtotime($date_time['late_for_work']['tuesday'])) {

							$_st = strtotime($date_time['late_for_work']['tuesday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['tuesday']) && $_st < strtotime($date_time['start_afternoon_shift']['tuesday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['tuesday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['tuesday'])) {

							$_et = strtotime($date_time['come_home_early']['tuesday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['tuesday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {

						if (strtotime($date_time['start_lunch_break_time']['tuesday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['tuesday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['tuesday']) - $stime) / 3600;
						}
					}

					break;

				case 3:

					$lunch_break = $date_time['lunch_break']['wednesday'];

					$work_time = $date_time['work_time']['wednesday'];



					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['wednesday'])) {

						$stime = strtotime($date_time['late_for_work']['wednesday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}



					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));

						if ($_st < strtotime($date_time['late_for_work']['wednesday'])) {

							$_st = strtotime($date_time['late_for_work']['wednesday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['wednesday']) && $_st < strtotime($date_time['start_afternoon_shift']['wednesday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['wednesday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['wednesday'])) {

							$_et = strtotime($date_time['come_home_early']['wednesday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['wednesday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {



						if (strtotime($date_time['start_lunch_break_time']['wednesday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['wednesday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['wednesday']) - $stime) / 3600;
						}
					}

					break;

				case 4:

					$lunch_break = $date_time['lunch_break']['thursday'];

					$work_time = $date_time['work_time']['thursday'];



					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['thursday'])) {

						$stime = strtotime($date_time['late_for_work']['thursday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}

					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));

						if ($_st < strtotime($date_time['late_for_work']['thursday'])) {

							$_st = strtotime($date_time['late_for_work']['thursday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['thursday']) && $_st < strtotime($date_time['start_afternoon_shift']['thursday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['thursday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['thursday'])) {

							$_et = strtotime($date_time['come_home_early']['thursday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['thursday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {

						if (strtotime($date_time['start_lunch_break_time']['thursday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['thursday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['thursday']) - $stime) / 3600;
						}
					}

					break;

				case 5:

					$lunch_break = $date_time['lunch_break']['friday'];

					$work_time = $date_time['work_time']['friday'];

					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['friday'])) {

						$stime = strtotime($date_time['late_for_work']['friday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}

					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));



						if ($_st < strtotime($date_time['late_for_work']['friday'])) {

							$_st = strtotime($date_time['late_for_work']['friday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['friday']) && $_st < strtotime($date_time['start_afternoon_shift']['friday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['friday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['friday'])) {

							$_et = strtotime($date_time['come_home_early']['friday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['friday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {



						if (strtotime($date_time['start_lunch_break_time']['friday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['friday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['friday']) - $stime) / 3600;
						}
					}

					break;

				case 6:

					if ((date('d', $time) % 2) == 1) {

						$lunch_break = $date_time['lunch_break']['saturday_odd'];

						$work_time = $date_time['work_time']['saturday_odd'];



						if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['saturday_odd'])) {

							$stime = strtotime($date_time['late_for_work']['saturday_odd']);
						} else {

							$stime = strtotime(date('H:i:s', strtotime($st)));
						}



						if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

							$_st = strtotime(date('H:i:s', strtotime($st)));

							$_et = strtotime(date('H:i:s', strtotime($et)));

							if ($_st < strtotime($date_time['late_for_work']['saturday_odd'])) {

								$_st = strtotime($date_time['late_for_work']['saturday_odd']);
							} elseif ($_st > strtotime($date_time['start_lunch_break_time']['saturday_odd']) && $_st < strtotime($date_time['start_afternoon_shift']['saturday_odd'])) {

								$_st = strtotime($date_time['start_afternoon_shift']['saturday_odd']);
							}

							if ($_et > strtotime($date_time['come_home_early']['saturday_odd'])) {

								$_et = strtotime($date_time['come_home_early']['saturday_odd']);
							}

							if (strtotime($date_time['start_lunch_break_time']['saturday_odd']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

								$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
							} else {

								$t = ($_et - $_st) / 3600;
							}
						} else {

							if (strtotime($date_time['start_lunch_break_time']['saturday_odd']) > $stime) {

								$t = (strtotime($date_time['come_home_early']['saturday_odd']) - $stime) / 3600 - ($lunch_break / 60);
							} else {

								$t = (strtotime($date_time['come_home_early']['saturday_odd']) - $stime) / 3600;
							}
						}
					} elseif ((date('d', $time) % 2) == 0) {

						$lunch_break = $date_time['lunch_break']['saturday_even'];

						$work_time = $date_time['work_time']['saturday_even'];



						if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['saturday_even'])) {

							$stime = strtotime($date_time['late_for_work']['saturday_even']);
						} else {

							$stime = strtotime(date('H:i:s', strtotime($st)));
						}



						if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

							$_st = strtotime(date('H:i:s', strtotime($st)));

							$_et = strtotime(date('H:i:s', strtotime($et)));

							if ($_st < strtotime($date_time['late_for_work']['saturday_even'])) {

								$_st = strtotime($date_time['late_for_work']['saturday_even']);
							} elseif ($_st > strtotime($date_time['start_lunch_break_time']['saturday_even']) && $_st < strtotime($date_time['start_afternoon_shift']['saturday_even'])) {

								$_st = strtotime($date_time['start_afternoon_shift']['saturday_even']);
							}

							if ($_et > strtotime($date_time['come_home_early']['saturday_even'])) {

								$_et = strtotime($date_time['come_home_early']['saturday_even']);
							}

							if (strtotime($date_time['start_lunch_break_time']['saturday_even']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et) {

								$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
							} else {

								$t = ($_et - $_st) / 3600;
							}
						} else {

							if (strtotime($date_time['start_lunch_break_time']['saturday_even']) > $stime) {

								$t = (strtotime($date_time['come_home_early']['saturday_even']) - $stime) / 3600 - ($lunch_break / 60);
							} else {

								$t = (strtotime($date_time['come_home_early']['saturday_even']) - $stime) / 3600;
							}
						}
					}

					break;

				case 7:

					$lunch_break = $date_time['lunch_break']['sunday'];

					$work_time = $date_time['work_time']['sunday'];



					if (strtotime(date('H:i:s', strtotime($st))) < strtotime($date_time['late_for_work']['sunday'])) {

						$stime = strtotime($date_time['late_for_work']['sunday']);
					} else {

						$stime = strtotime(date('H:i:s', strtotime($st)));
					}

					if (date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))) {

						$_st = strtotime(date('H:i:s', strtotime($st)));

						$_et = strtotime(date('H:i:s', strtotime($et)));

						if ($_st < strtotime($date_time['late_for_work']['sunday'])) {

							$_st = strtotime($date_time['late_for_work']['sunday']);
						} elseif ($_st > strtotime($date_time['start_lunch_break_time']['sunday']) && $_st < strtotime($date_time['start_afternoon_shift']['sunday'])) {

							$_st = strtotime($date_time['start_afternoon_shift']['sunday']);
						}

						if ($_et > strtotime($date_time['come_home_early']['sunday'])) {

							$_et = strtotime($date_time['come_home_early']['sunday']);
						}

						if (strtotime($date_time['start_lunch_break_time']['sunday']) > $stime) {

							$t = ($_et - $_st) / 3600 - ($lunch_break / 60);
						} else {

							$t = ($_et - $_st) / 3600;
						}
					} else {

						if (strtotime($date_time['start_lunch_break_time']['sunday']) > $stime) {

							$t = (strtotime($date_time['come_home_early']['sunday']) - $stime) / 3600 - ($lunch_break / 60);
						} else {

							$t = (strtotime($date_time['come_home_early']['sunday']) - $stime) / 3600;
						}
					}

					break;
			}
		}

		return number_format($t, 2);
	}

	/**

	 * check choose when approving

	 * @param   $related

	 */

	public function check_choose_when_approving($related)
	{

		$this->db->select('choose_when_approving');

		$this->db->where('related', $related);

		$rs = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();

		if ($rs) {

			return $rs->choose_when_approving;
		} else {

			return 0;
		}
	}

	/**

	 * send request approve

	 * @param  array $data

	 * @param  integer $staff_id

	 * @return bool

	 */

	public function send_request_approve($data, $staff_id = '')
	{

		if (!isset($data['status'])) {

			$data['status'] = '';
		}

		$staff_addedfrom = $data['addedfrom'];

		$date_send = date('Y-m-d H:i:s');



		$data_new = $this->get_approve_setting($data['rel_type'], true, $staff_addedfrom);

		$data_setting = $this->get_approve_setting($data['rel_type'], false, $staff_addedfrom);

		if (!$data_new) {

			// Regularization must not auto-approve when approval chain is missing.
			if ($data['rel_type'] === 'additional_timesheets') {
				log_message('error', 'additional_timesheets approval chain not configured — request left pending (rel_id ' . $data['rel_id'] . ')');
				return false;
			}

			$this->update_approve_request($data['rel_id'], $data['rel_type'], 1);

			return false;
		}

		$this->delete_approval_details($data['rel_id'], $data['rel_type']);

		$list_staff = $this->staff_model->get();

		$list = [];



		$sender = $staff_addedfrom;



		foreach ($data_new as $value) {

			$row = [];

			$row['notification_recipient'] = $data_setting->notification_recipient;

			$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $data_setting->number_day_approval . ' day'));



			if ($value->approver == 'specific_personnel' || $value->approver == 'staff') {

				$row['staffid'] = $value->staff;

				$row['date_send'] = $date_send;

				$row['rel_id'] = $data['rel_id'];

				$row['rel_type'] = $data['rel_type'];

				$row['sender'] = $sender;

				$this->db->insert(db_prefix() . 'timesheets_approval_details', $row);
			} else {

				$value->staff_addedfrom = $staff_addedfrom;

				$value->rel_type = $data['rel_type'];

				$value->rel_id = $data['rel_id'];



				$approve_value = $this->get_staff_id_by_approve_value($value, $value->approver);



				if (is_numeric($approve_value) && $approve_value > 0) {

					$approve_value = $this->staff_model->get($approve_value)->email;
				} else {



					$this->db->where('rel_id', $data['rel_id']);

					$this->db->where('rel_type', $data['rel_type']);

					$this->db->delete(db_prefix() . 'timesheets_approval_details');



					return $value->approver;
				}

				$row['approve_value'] = $approve_value;



				$staffid = $this->get_staff_id_by_approve_value($value, $value->approver);



				if (empty($staffid)) {

					$this->db->where('rel_id', $data['rel_id']);

					$this->db->where('rel_type', $data['rel_type']);

					$this->db->delete(db_prefix() . 'timesheets_approval_details');



					return $value->approver;
				}

				$row['staffid'] = $staffid;

				$row['date_send'] = $date_send;

				$row['rel_id'] = $data['rel_id'];

				$row['rel_type'] = $data['rel_type'];

				$row['sender'] = $sender;

				$this->db->insert(db_prefix() . 'timesheets_approval_details', $row);
			}
		}

		// Leave: never leave team managers as approvers — allowlist only.
		if (($data['rel_type'] ?? '') !== 'additional_timesheets') {
			$this->assign_leave_approvers_only((int) $data['rel_id'], $data['rel_type'], (int) $staff_addedfrom);
		}

		return true;
	}

	/**
	 * Leave approval: only Sarabjeet / HR / Admin / Super Admin (not team managers).
	 * Replaces approval_details with a single OR-list of allowlisted staff.
	 */
	public function assign_leave_approvers_only($rel_id, $rel_type, $applicant_staff_id = 0)
	{
		$rel_id = (int) $rel_id;
		if ($rel_id <= 0 || $rel_type === '' || $rel_type === 'additional_timesheets') {
			return false;
		}

		$this->load->helper('timesheets/timesheets');
		$applicant_staff_id = (int) $applicant_staff_id;
		if ($applicant_staff_id <= 0) {
			$leave = $this->db->select('staff_id')->where('id', $rel_id)->get(db_prefix() . 'timesheets_requisition_leave')->row();
			$applicant_staff_id = (int) ($leave->staff_id ?? 0);
		}

		$approver_ids = timesheets_leave_approver_ids_for_applicant($applicant_staff_id);
		if (empty($approver_ids)) {
			return false;
		}

		$staffid_csv = implode(', ', $approver_ids);
		$notif_csv = implode(',', $approver_ids);
		$this->delete_approval_details($rel_id, $rel_type);

		$this->db->insert(db_prefix() . 'timesheets_approval_details', [
			'rel_id' => $rel_id,
			'rel_type' => $rel_type,
			'staffid' => $staffid_csv,
			'approve' => 0,
			'date_send' => date('Y-m-d H:i:s'),
			'sender' => (int) ($applicant_staff_id ?: get_staff_user_id()),
			'notification_recipient' => $notif_csv,
			'approval_deadline' => date('Y-m-d', strtotime('+5 day')),
		]);

		return (bool) $this->db->insert_id();
	}

	/**
	 * Notify reporting manager when a team member applies for leave (info only — they cannot approve).
	 */
	public function notify_manager_leave_application($rel_id, $applicant_staff_id)
	{
		$rel_id = (int) $rel_id;
		$applicant_staff_id = (int) $applicant_staff_id;
		if ($rel_id <= 0 || $applicant_staff_id <= 0) {
			return false;
		}

		$staff = $this->db->select('team_manage, firstname, lastname, email')
			->where('staffid', $applicant_staff_id)
			->get(db_prefix() . 'staff')
			->row();
		if (!$staff || (int) $staff->team_manage <= 0) {
			return false;
		}

		$manager_id = (int) $staff->team_manage;
		$this->load->helper('timesheets/timesheets');
		// If HOD is also a final leave approver, they already get the approver email — don't send a second one.
		$approver_ids = timesheets_leave_approver_ids_for_applicant($applicant_staff_id);
		if (in_array($manager_id, $approver_ids, true)) {
			return false;
		}

		$manager = $this->db->select('staffid, email, firstname, lastname')
			->where('staffid', $manager_id)
			->where('active', 1)
			->get(db_prefix() . 'staff')
			->row();
		if (!$manager || empty($manager->email)) {
			return false;
		}

		$leave = $this->db->where('id', $rel_id)->get(db_prefix() . 'timesheets_requisition_leave')->row();
		if (!$leave) {
			return false;
		}

		$employee_name = trim($staff->firstname . ' ' . $staff->lastname);
		$manager_name = trim($manager->firstname . ' ' . $manager->lastname);
		$subject_leave = trim((string) ($leave->subject ?? 'Leave application'));
		$from = !empty($leave->start_time) ? _d(date('Y-m-d', strtotime($leave->start_time))) : '-';
		$to = !empty($leave->end_time) ? _d(date('Y-m-d', strtotime($leave->end_time))) : $from;
		$days = (string) ($leave->number_of_leaving_day ?? $leave->number_of_days ?? '');
		$link = admin_url('timesheets/requisition_detail/' . $rel_id);

		// In-app notification (view only — manager is not in approver list).
		add_notification([
			'description' => 'not_staff_leave_application',
			'touserid' => $manager_id,
			'fromuserid' => $applicant_staff_id,
			'link' => 'timesheets/requisition_detail/' . $rel_id,
			'additional_data' => serialize([$employee_name, $subject_leave]),
		]);
		pusher_trigger_notification([$manager_id]);

		$mail_subject = 'Leave application by ' . $employee_name . ' — please Forward or Reject';
		$message = '<p>Dear ' . html_escape($manager_name) . ',</p>';
		$message .= '<p><b>' . html_escape($employee_name) . '</b> has applied for leave. Please review and Forward to Super HR, or Reject.</p>';
		$message .= '<p><b>Subject:</b> ' . html_escape($subject_leave) . '</p>';
		$message .= '<p><b>From:</b> ' . html_escape($from) . '<br><b>To:</b> ' . html_escape($to) . '</p>';
		if ($days !== '') {
			$message .= '<p><b>Days:</b> ' . html_escape($days) . '</p>';
		}
		$message .= '<p><b>Note:</b> Please <b>Forward</b> this leave to Super HR (or Reject). Final approval is done by Super HR / HR / Sarabjeet Singh (or Super Admin Harpreet for HR &amp; Accounts).</p>';
		$message .= '<p><a href="' . html_escape($link) . '">View leave application</a></p>';
		$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}

		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->initialize();
			$this->email->set_mailtype('html');
			$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
			$this->email->to($manager->email);
			$this->email->subject($mail_subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(false);
			if ($ok) {
				log_activity('Leave manager notify sent: leave #' . $rel_id . ' to ' . $manager->email);
			} else {
				log_activity('Leave manager notify failed: leave #' . $rel_id);
			}
			return $ok;
		} catch (Exception $e) {
			log_activity('Leave manager notify exception: #' . $rel_id . ' | ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Email leave approvers (Sarabjeet / HR / Admin / Super Admin) on new leave application.
	 */
	public function send_leave_application_approver_emails($rel_id, $rel_type, $applicant_staff_id = 0)
	{
		$rel_id = (int) $rel_id;
		$applicant_staff_id = (int) $applicant_staff_id;
		if ($rel_id <= 0) {
			return false;
		}

		$this->load->helper('timesheets/timesheets');
		$approver_ids = timesheets_leave_approver_ids_for_applicant($applicant_staff_id);
		if (empty($approver_ids)) {
			$details = $this->check_approval_details($rel_id, $rel_type);
			if (isset($details['staffid']) && is_array($details['staffid'])) {
				$approver_ids = array_map('intval', $details['staffid']);
			}
		}

		$leave = $this->get_request_leave($rel_id);
		if (!$leave) {
			return false;
		}

		if ($applicant_staff_id <= 0) {
			$applicant_staff_id = (int) $leave->staff_id;
		}

		$staff_name = get_staff_full_name($applicant_staff_id);
		$subject_leave = trim((string) ($leave->subject ?? 'Leave application'));
		$from = !empty($leave->start_time) ? _d(date('Y-m-d', strtotime($leave->start_time))) : '-';
		$to = !empty($leave->end_time) ? _d(date('Y-m-d', strtotime($leave->end_time))) : $from;
		$days = (string) ($leave->number_of_leaving_day ?? $leave->number_of_days ?? '');
		$link = admin_url('timesheets/requisition_detail/' . $rel_id);

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}

		$sent = 0;
		$this->load->library('email');

		foreach ($approver_ids as $approver_id) {
			$approver_id = (int) $approver_id;
			if ($approver_id <= 0 || $approver_id === $applicant_staff_id) {
				continue;
			}

			$email = $this->get_staff_email($approver_id);
			if ($email === '' || $email === null) {
				continue;
			}

			$approver_name = get_staff_full_name($approver_id);
			$mail_subject = 'Leave approval required: ' . $staff_name . ' — ' . $subject_leave;
			$message = '<p>Dear ' . html_escape($approver_name) . ',</p>';
			$message .= '<p><b>' . html_escape($staff_name) . '</b> has applied for leave and requires your approval.</p>';
			$message .= '<p><b>Subject:</b> ' . html_escape($subject_leave) . '</p>';
			$message .= '<p><b>From:</b> ' . html_escape($from) . '<br><b>To:</b> ' . html_escape($to) . '</p>';
			if ($days !== '') {
				$message .= '<p><b>Days:</b> ' . html_escape($days) . '</p>';
			}
			$message .= '<p><a href="' . html_escape($link) . '">Review &amp; approve leave application</a></p>';
			$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

			try {
				$this->email->clear(true);
				$this->email->initialize();
				$this->email->set_mailtype('html');
				$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
				$this->email->to($email);
				$this->email->subject($mail_subject);
				$this->email->message($message);
				if ((bool) $this->email->send(false)) {
					$sent++;
					log_activity('Leave approver email sent: leave #' . $rel_id . ' to ' . $email);
				} else {
					log_activity('Leave approver email failed: leave #' . $rel_id . ' to ' . $email);
				}
			} catch (Exception $e) {
				log_activity('Leave approver email exception: #' . $rel_id . ' | ' . $e->getMessage());
			}
		}

		return $sent > 0;
	}

	/**
	 * Write leave approve/reject to Utilities → Activity Log (who decided, for whom).
	 */
	public function log_leave_decision_activity($leave_id, $approved, $comment = '', $decided_by = 0)
	{
		$leave_id = (int) $leave_id;
		if ($leave_id <= 0) {
			return;
		}

		$leave = $this->get_request_leave($leave_id);
		if (!$leave) {
			return;
		}

		$decided_by = (int) ($decided_by ?: get_staff_user_id());
		$decider_name = $decided_by > 0 ? get_staff_full_name($decided_by) : 'HR / Super Admin';
		// Never show informal "Sir" label in logs / UI.
		$decider_name = preg_replace('/\s+sir\b/i', '', (string) $decider_name);
		$decider_name = trim(preg_replace('/\s+/', ' ', $decider_name));
		if (strcasecmp($decider_name, 'Sarabjeet') === 0) {
			$decider_name = 'Sarabjeet Singh';
		}

		$applicant_name = get_staff_full_name((int) $leave->staff_id);
		$subject = trim((string) ($leave->subject ?? 'Leave application'));
		$from = !empty($leave->start_time) ? date('d-M-Y', strtotime($leave->start_time)) : '-';
		$to = !empty($leave->end_time) ? date('d-M-Y', strtotime($leave->end_time)) : $from;
		$days = (string) ($leave->number_of_leaving_day ?? $leave->number_of_days ?? '');
		$action = $approved ? 'APPROVED' : 'REJECTED';
		$comment = trim((string) $comment);

		$desc = 'Leave ' . $action . ' by ' . $decider_name
			. ' [Leave Id: ' . $leave_id
			. ', Staff: ' . $applicant_name
			. ', Subject: ' . $subject
			. ', From: ' . $from
			. ', To: ' . $to;
		if ($days !== '' && (float) $days > 0) {
			$desc .= ', Days: ' . $days;
		}
		$desc .= ']';
		if ($comment !== '') {
			$desc .= ' Comment: ' . $comment;
		}

		log_activity($desc);
	}

	/**
	 * Email + in-app notify the employee when leave is approved or rejected.
	 *
	 * @param int $leave_id
	 * @param bool $approved true = approved, false = rejected
	 * @param string $comment
	 * @param int $decided_by staffid of approver
	 */
	public function notify_leave_decision_to_employee($leave_id, $approved, $comment = '', $decided_by = 0)
	{
		$leave_id = (int) $leave_id;
		if ($leave_id <= 0) {
			return false;
		}

		$decided_by = (int) ($decided_by ?: get_staff_user_id());
		$this->log_leave_decision_activity($leave_id, $approved, $comment, $decided_by);

		$leave = $this->get_request_leave($leave_id);
		if (!$leave || empty($leave->staff_id)) {
			return false;
		}

		$employee = $this->db->select('staffid, email, firstname, lastname')
			->where('staffid', (int) $leave->staff_id)
			->where('active', 1)
			->get(db_prefix() . 'staff')
			->row();
		if (!$employee || empty($employee->email)) {
			log_activity('Leave decision email skipped: no employee email for leave #' . $leave_id);
			return false;
		}

		$decider_name = $decided_by > 0 ? get_staff_full_name($decided_by) : 'HR / Admin';
		$decider_name = preg_replace('/\s+sir\b/i', '', (string) $decider_name);
		$decider_name = trim(preg_replace('/\s+/', ' ', $decider_name));
		if (strcasecmp($decider_name, 'Sarabjeet') === 0) {
			$decider_name = 'Sarabjeet Singh';
		}
		$employee_name = trim($employee->firstname . ' ' . $employee->lastname);
		$subject_leave = trim((string) ($leave->subject ?? 'Leave application'));
		$from = !empty($leave->start_time) ? _d(date('Y-m-d', strtotime($leave->start_time))) : '-';
		$to = !empty($leave->end_time) ? _d(date('Y-m-d', strtotime($leave->end_time))) : $from;
		$days = (string) ($leave->number_of_leaving_day ?? $leave->number_of_days ?? '');
		$link = admin_url('timesheets/requisition_detail/' . $leave_id);
		$comment = trim((string) $comment);
		$status_label = $approved ? 'approved' : 'rejected';

		add_notification([
			'description' => $approved ? 'notify_send_approve' : 'notify_send_rejected',
			'touserid' => (int) $employee->staffid,
			'fromuserid' => $decided_by,
			'link' => 'timesheets/requisition_detail/' . $leave_id,
			'additional_data' => serialize([$subject_leave]),
		]);
		pusher_trigger_notification([(int) $employee->staffid]);

		$mail_subject = 'Leave application ' . $status_label . ': ' . $subject_leave;
		$message = '<p>Dear ' . html_escape($employee_name) . ',</p>';
		$message .= '<p>Your leave application has been <b>' . html_escape($status_label) . '</b> by ' . html_escape($decider_name) . '.</p>';
		$message .= '<p><b>Subject:</b> ' . html_escape($subject_leave) . '</p>';
		$message .= '<p><b>From:</b> ' . html_escape($from) . '<br><b>To:</b> ' . html_escape($to) . '</p>';
		if ($days !== '' && (float) $days > 0) {
			$message .= '<p><b>Days:</b> ' . html_escape($days) . '</p>';
		}
		if ($comment !== '') {
			$message .= '<p><b>Comment:</b> ' . nl2br(html_escape($comment)) . '</p>';
		}
		$message .= '<p><a href="' . html_escape($link) . '">View leave application</a></p>';
		$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}

		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->initialize();
			$this->email->set_mailtype('html');
			$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
			$this->email->to($employee->email);
			$this->email->subject($mail_subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(false);
			if ($ok) {
				log_activity('Leave ' . $status_label . ' email sent: leave #' . $leave_id . ' to ' . $employee->email);
			} else {
				log_activity('Leave ' . $status_label . ' email failed: leave #' . $leave_id . ' to ' . $employee->email);
			}
			return $ok;
		} catch (Exception $e) {
			log_activity('Leave decision email exception: #' . $leave_id . ' | ' . $e->getMessage());
			return false;
		}
	}

	/**

	 * get approve setting

	 * @param  integer $type

	 * @param  boolean $only_setting

	 * @param  string  $staff_id

	 * @return bool

	 */

	public function get_approve_setting($type, $only_setting = true, $staff_id = '')
	{

		if ($staff_id == '') {

			$staff_id = get_staff_user_id();
		}

		$this->load->model('departments_model');

		$staff = $this->staff_model->get($staff_id);

		$departments = $this->departments_model->get_staff_departments($staff_id, true);



		$where_job_position = '';

		if ($staff) {

			if ($staff->role != '' && $staff->role != 0) {

				$where_job_position = 'find_in_set(' . $staff->role . ',job_positions)';
			} else {

				$where_job_position = '(job_positions is null OR job_positions = "")';
			}
		}



		$where_departments = '';

		foreach ($departments as $key => $value) {

			if ($where_departments != '') {

				$where_departments .= ' OR find_in_set(' . $value . ',departments)';
			} else {

				$where_departments = 'find_in_set(' . $value . ',departments)';
			}
		}



		if ($where_departments != '') {

			$where_departments = '(' . $where_departments . ')';
		} else {

			$where_departments = '(departments is null OR departments = "")';
		}

		$this->db->select('*');

		if ($where_job_position != '' && $where_departments != '') {

			$this->db->where($where_job_position . ' AND ' . $where_departments . ' AND related="' . $type . '"');
		}

		$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();

		if ($approval_setting) {

			if ($only_setting == false) {

				return $approval_setting;
			} else {

				return json_decode($approval_setting->setting);
			}
		} else {

			$this->db->select('*');

			$this->db->where('related', $type);

			if ($where_job_position != '') {

				$this->db->where($where_job_position . ' AND (departments is null OR departments = "") AND related="' . $type . '"');
			}

			$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();

			if ($approval_setting) {

				if ($only_setting == false) {

					return $approval_setting;
				} else {

					return json_decode($approval_setting->setting);
				}
			} else {

				$this->db->select('*');

				if ($where_departments != '') {

					$this->db->where($where_departments . ' AND (job_positions is null OR job_positions = "") AND related="' . $type . '"');
				}

				$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();

				if ($approval_setting) {

					if ($only_setting == false) {

						return $approval_setting;
					} else {

						return json_decode($approval_setting->setting);
					}
				} else {

					$this->db->select('*');

					$this->db->where('(departments is null OR departments = "") AND (job_positions is null OR job_positions = "") AND related="' . $type . '"');

					$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();

					if ($approval_setting) {

						if ($only_setting == false) {

							return $approval_setting;
						} else {

							return json_decode($approval_setting->setting);
						}
					}
				}
			}
		}

		return false;
	}

	/**

	 * delete_approval_details

	 * @param  integer $rel_id

	 * @param  integer $rel_type

	 * @return  bool

	 */

	public function delete_approval_details($rel_id, $rel_type)
	{

		$this->db->where('rel_id', $rel_id);

		$this->db->where('rel_type', $rel_type);

		$this->db->delete(db_prefix() . 'timesheets_approval_details');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * update approval details

	 * @param  integer $id

	 * @param  array $data

	 * @return bool

	 */

	public function update_approval_details($id, $data)
	{

		$data['date'] = date('Y-m-d H:i:s');

		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'timesheets_approval_details', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * update approve request

	 * @param  integer $rel_id

	 * @param  integer $rel_type

	 * @param  integer $status

	 * @param  string $staffid

	 * @return integer

	 */

	public function update_approve_request($rel_id, $rel_type, $status, $staffid = '')
	{

		$data_update = [];

		$find_a_case = false;

		switch (strtolower($rel_type)) {

			case 'late':

				$data_update['status'] = $status;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_update);

				if ($status == 1) {

					//Get

					$this->db->where('id', $rel_id);

					$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

					if ($requisition_leave) {

						$start_time = $requisition_leave->start_time;

						$check_latch_timesheet = $this->timesheets_model->check_latch_timesheet(date('m-Y', strtotime($start_time)));

						if (!$check_latch_timesheet) {

							$staffid = $requisition_leave->staff_id;

							// Get current hour in shift

							$shift_info = $this->get_info_hour_shift_staff($staffid, date('Y-m-d', strtotime($start_time)));



							if ($shift_info->start_working != '') {

								// Caculate hour

								$value_ts = $this->get_hour($shift_info->start_working, date('H:i:s', strtotime($start_time)));

								if ($value_ts > 0) {

									// Save to timesheets

									$this->db->insert(db_prefix() . 'timesheets_timesheet', [

										'staff_id' => $staffid,

										'date_work' => date('Y-m-d', strtotime($start_time)),

										'value' => $value_ts,

										'add_from' => $staffid,

										'relate_id' => $rel_id,

										'relate_type' => 'leave',

										'type' => 'L',

									]);

									// // Save to timesheets

								}
							}
						}
					}
				}

				return true;

				break;

			case 'early':

				$data_update['status'] = $status;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_update);

				if ($status == 1) {

					//Get

					$this->db->where('id', $rel_id);

					$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

					if ($requisition_leave) {

						$start_time = $requisition_leave->start_time;

						$check_latch_timesheet = $this->timesheets_model->check_latch_timesheet(date('m-Y', strtotime($start_time)));

						if (!$check_latch_timesheet) {

							$staffid = $requisition_leave->staff_id;

							// Get current hour in shift

							$shift_info = $this->get_info_hour_shift_staff($staffid, date('Y-m-d', strtotime($start_time)));

							if ($shift_info->end_working != '') {

								// Caculate hour

								$value_ts = $this->get_hour($shift_info->end_working, date('H:i:s', strtotime($start_time)));

								if ($value_ts > 0) {

									// Save to timesheets

									$this->db->insert(db_prefix() . 'timesheets_timesheet', [

										'staff_id' => $staffid,

										'date_work' => date('Y-m-d', strtotime($start_time)),

										'value' => $value_ts,

										'add_from' => $staffid,

										'relate_id' => $rel_id,

										'relate_type' => 'leave',

										'type' => 'E',

									]);

									// // Save to timesheets

								}
							}
						}
					}
				}

				break;

			case 'go_out':

				$data_update['status'] = $status;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_update);

				return true;

				break;

			case 'go_on_bussiness':

				$data_update['status'] = $status;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data_update);

				if ($status == 1) {

					$this->db->where('id', $rel_id);

					$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();



					$start_time = strtotime($requisition_leave->start_time);

					$end_time = strtotime($requisition_leave->end_time);



					$st = date('Y-m-d', $start_time);

					$et = date('Y-m-d', $end_time);



					if ($staffid != '') {

						$staff_id = $staffid;
					} else {

						$staff_id = get_staff_user_id();
					}



					$type = 'B';

					if ($requisition_leave->end_time != '') {

						for ($i = 0; $i < 5; $i++) {

							if (strtotime($st) <= strtotime($et)) {

								$this->db->insert(db_prefix() . 'timesheets_timesheet', [

									'staff_id' => $requisition_leave->staff_id,

									'date_work' => $st,

									'value' => 0,

									'add_from' => $staff_id,

									'relate_id' => $rel_id,

									'relate_type' => 'leave',

									'type' => $type,

								]);

								$st = date('Y-m-d', strtotime($st . ' + 1 days'));

								$i = 0;
							} else {

								$i = 10;
							}
						}
					} else {

						$this->db->insert(db_prefix() . 'timesheets_timesheet', [

							'staff_id' => $requisition_leave->staff_id,

							'date_work' => $st,

							'value' => 0,

							'add_from' => $staff_id,

							'relate_id' => $rel_id,

							'relate_type' => 'leave',

							'type' => $type,

						]);
					}
				}

				return true;

				break;

			case 'additional_timesheets':

				$data_update['status'] = $status;

				$this->db->where('id', $rel_id);

				$this->db->update(db_prefix() . 'timesheets_additional_timesheet', $data_update);

				if ($status == 1) {

					$this->db->where('id', $rel_id);

					$additional_timesheet = $this->db->get(db_prefix() . 'timesheets_additional_timesheet')->row();

					// Adjust leave balance for this day AFTER computing new hours
					// (Absent→1, Half→0.5, Present→0). Do not blindly deduct leave.
					// Always sync approved regularization into timesheet (even if month is latched).
					// Previously latched months were skipped → approved day still showed Absent until manual fix.
					$data_addts = $this->get_additional_timesheets($rel_id);

					if ($data_addts) {
						$day = date('Y-m-d', strtotime($data_addts->additional_day));
						$is_latched = $this->check_latch_timesheet(date('m-Y', strtotime($day)));
						$value = (float) ($data_addts->timekeeping_value ?? 0);
						if ($value <= 0 && !empty($data_addts->time_in) && !empty($data_addts->time_out)) {
							$value = $this->calculate_regularisation_hours(
								$data_addts->time_in,
								$data_addts->time_out,
								$day,
								(int) $data_addts->creator,
								true
							);
						}
						// Do not invent a full day (9h) for short / minute-level regularizations.
						if ($value < 0) {
							$value = 0;
						}
						$value = round((float) $value, 2);

						try {
							$this->adjust_leave_balance_for_approved_regularisation((int) $rel_id, $value);
						} catch (Throwable $e) {
							log_activity('Regularization leave adjust failed #' . (int) $rel_id . ': ' . $e->getMessage());
						}

						$ts_payload = [
							'value' => $value,
							'relate_id' => $rel_id,
							'relate_type' => 'additional_timesheet',
							'type' => 'P',
						];
						if ($is_latched) {
							$ts_payload['latch'] = 1;
						}

						// Replace absent / incomplete marks with Present for that day.
						$this->db->where('staff_id', (int) $data_addts->creator);
						$this->db->where('date_work', $day);
						$this->db->where_in('type', ['AB', 'A', 'HD', 'W', 'P', 'p']);
						$existing = $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();
						$updated = false;
						foreach ($existing as $row) {
							if (!$updated && strtoupper((string) $row['type']) === 'P') {
								$this->db->where('id', (int) $row['id']);
								$this->db->update(db_prefix() . 'timesheets_timesheet', $ts_payload);
								$updated = true;
							} else {
								$this->db->where('id', (int) $row['id'])->delete(db_prefix() . 'timesheets_timesheet');
							}
						}
						if (!$updated) {
							$this->db->insert(db_prefix() . 'timesheets_timesheet', array_merge([
								'staff_id' => $data_addts->creator,
								'date_work' => $day,
								'add_from' => get_staff_user_id() ?: $data_addts->creator,
							], $ts_payload));
						}
					}
				}



				return true;

				break;

			default:

				$this->update_requisition_after_approve($rel_id, $status, $rel_type);

				break;
		}
	}

	/**

	 * add requisition ajax

	 * @param array $data

	 */

	
public function add_requisition_ajax($data)
	{

		$staff_quit_job = $data['staff_id'];

		$data['start_time'] = $this->timesheets_model->format_date_time($data['start_time']);

		$data['end_time'] = $this->timesheets_model->format_date_time($data['end_time']);

		if ($data['start_time'] == '') {
			$data['start_time'] = $data['end_time'];
		}
		if ($data['end_time'] == '') {
			$data['end_time'] = $data['start_time'];
		}




		$data['datecreated'] = date('Y-m-d H:i:s');

		if (isset($data['used_to'])) {

			$used_to = $data['used_to'];

			unset($data['used_to']);
		}


		if (isset($data['request_date'])) {

			$request_date = to_sql_date($data['request_date']);

			unset($data['request_date']);
		}



		if (isset($data['received_date'])) {

			$received_date = $data['received_date'];

			unset($data['received_date']);
		}

		if (isset($data['carry_forward'])) {

			$carry_forward = $data['carry_forward'];

			unset($data['carry_forward']);
		}

		if (isset($data['leave_balance'])) {

			$leave_balance = $data['leave_balance'];

			unset($data['leave_balance']);
		}




		/*$day_off = $this->timesheets_model->get_day_off($staff_quit_job, '', $data['type_of_leave']);

		$number_day_off = 0;

		if ($day_off != null) {

			$number_day_off = $day_off->remain;

			
		}*/

		if (isset($data['type_of_leave'])) {

			$type_of_leave = $data['type_of_leave'];
			//unset($data['type_of_leave']);

		}
		// Keep server-computed values from the controller (remaining after deduct).
		// Never overwrite from POST — form fields hold pre-deduct balance / original leave type.
		if (isset($carry_forward)) {
			$data['carry_forward'] = $carry_forward;
		}
		if (isset($leave_balance)) {
			$data['leave_balance'] = $leave_balance;
		}
		if (!isset($type_of_leave) || $type_of_leave === '') {
			$type_of_leave = $data['type_of_leave'] ?? '';
		} else {
			$data['type_of_leave'] = $type_of_leave;
		}
		$start_time = $data['end_time']; // or your date as well
		$start_t = explode(' ', $start_time);
		$start_tt = $start_t[0];
		$end_time = $data['start_time'];
		$end_t = explode(' ', $end_time);
		$end_tt = $end_t[0];
		$diff = date_diff(date_create($end_tt), date_create($start_tt));
		$datediff = $diff->format("%a");
		//echo $datediff;die;
		//$datediff = $start_time - $end_time;	

		if ($type_of_leave == 'unpaid-half-days') {
			$data['start_time'] = '';
			$number_day_off = 0.5;
			$data['number_of_leaving_day'] = $number_day_off;
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);


			$this->db->insert(db_prefix() . 'timesheets_timesheet', ['staff_id' => $data['staff_id'], 'date_work' => $data['end_time'], 'type' => 'HD', 'add_from' => $data['staff_id']]);
			$insert_id = $this->db->insert_id();
			//$data['number_of_days'] = $number_day_off;

		} elseif ($type_of_leave == 'half-days') {
			$data['start_time'] = '';
			$number_day_off = 0;
			$data['number_of_leaving_day'] = $number_day_off;
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
			$insert_id = $this->db->insert_id();

			$end_datetime = new DateTime($data['end_time']);
			$timesheet_data = array(
				'staff_id' => $staff_quit_job,
				'type' => 'HD',
				'date_work' => $end_datetime->format('Y-m-d'), // Insert date in 'Y-m-d' format
				'add_from' => $staff_quit_job
			);
			// Insert into timesheet table
			$this->db->insert('tbltimesheets_timesheet', $timesheet_data);

			// $data['number_of_days'] = $number_day_off;
		} elseif ($type_of_leave == 'unplanned_leaves') {
			$data['start_time'] = $data['start_time'];
			$number_day_off = 1;
			// $data['number_of_days'] =  round($datediff / (60 * 60 * 24))+$number_day_off;
			$data['number_of_leaving_day'] =  $datediff + $number_day_off;
			$start_time = $data['start_time'];
			$date_ex = explode(' ', $start_time);
			$date_start = $date_ex[0];
			$end_time = $data['end_time'];
			$date_end = explode(' ', $end_time);
			$end_date = $date_end[0];
			//print_r($data);die;
			//echo 'SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = '.$staff_quit_job.' AND type_of_leave = 4 AND start_time BETWEEN  "'.$date_start.'" AND "'.$end_date.'" AND end_time BETWEEN "'.$date_start.'" AND "'.$end_date.'"';die;			
			$query = $this->db->query('SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = ' . $staff_quit_job . ' AND type_of_leave = 4 AND date(start_time) BETWEEN  "' . $date_start . '" AND "' . $end_date . '" AND date(end_time) BETWEEN "' . $date_start . '" AND "' . $end_date . '"')->result_array();
			//print_r($query);die;
			$count = count($query);
			//echo $count;
			//echo $date_start.'<br>';
			//echo $end_date.'<br>';
			if ($count > 0 and ($date_start == $end_date)) {
				////echo 'update';die;
				foreach ($query as $queryy) {
					$id = $queryy['id'];
					$query = $this->db->query('UPDATE tbltimesheets_requisition_leave SET subject = "' . $data['subject'] . '", reason= "' . $data['reason'] . '", handover_recipients= "' . $data['handover_recipients'] . '", followers_id= "' . $data['followers_id'] . '", type_of_leave_text= "' . $data['type_of_leave_text'] . '", status= "0", datecreated= "' . $data['datecreated'] . '", type_of_leave= "' . $data['type_of_leave'] . '" WHERE id = ' . $id);
				}
			} else {
				//echo 'insert';die;
				$data['status'] = 0;
				$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
				$ins_ul = $this->db->insert_id();
				// Convert start_time and end_time to DateTime objects
				$start_datetime = new DateTime($start_time);
				$end_datetime = new DateTime($end_time);

				// Loop through each date between start_time and end_time
				for ($date_timesheet = $start_datetime; $date_timesheet <= $end_datetime; $date_timesheet->modify('+1 day')) {
					// Prepare the data to insert into timesheet table
					$timesheet_data = array(
						'staff_id' => $staff_quit_job,
						'type' => 'UL',
						'date_work' => $date_timesheet->format('Y-m-d'), // Insert date in 'Y-m-d' format
						'add_from' => $staff_quit_job
					);



					// Insert into timesheet table
					$this->db->insert('tbltimesheets_timesheet', $timesheet_data);
				}



				return $ins_ul;
				//echo 'data not available';
			}
		}
		elseif ($type_of_leave == 'paternity-leaves') {
			$data['start_time'] = $data['start_time'];
			$number_day_off = 1;
			// $data['number_of_days'] =  round($datediff / (60 * 60 * 24))+$number_day_off;
			$data['number_of_leaving_day'] =  $datediff + $number_day_off;
			$start_time = $data['start_time'];
			$date_ex = explode(' ', $start_time);
			$date_start = $date_ex[0];
			$end_time = $data['end_time'];
			$date_end = explode(' ', $end_time);
			$end_date = $date_end[0];
			//print_r($data);die;
			//echo 'SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = '.$staff_quit_job.' AND type_of_leave = 4 AND start_time BETWEEN  "'.$date_start.'" AND "'.$end_date.'" AND end_time BETWEEN "'.$date_start.'" AND "'.$end_date.'"';die;			
			$query = $this->db->query('SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = ' . $staff_quit_job . ' AND type_of_leave = 4 AND date(start_time) BETWEEN  "' . $date_start . '" AND "' . $end_date . '" AND date(end_time) BETWEEN "' . $date_start . '" AND "' . $end_date . '"')->result_array();
			//print_r($query);die;
			$count = count($query);
			//echo $count;
			//echo $date_start.'<br>';
			//echo $end_date.'<br>';
			if ($count > 0 and ($date_start == $end_date)) {
				////echo 'update';die;
				foreach ($query as $queryy) {
					$id = $queryy['id'];
					$query = $this->db->query('UPDATE tbltimesheets_requisition_leave SET subject = "' . $data['subject'] . '", reason= "' . $data['reason'] . '", handover_recipients= "' . $data['handover_recipients'] . '", followers_id= "' . $data['followers_id'] . '", type_of_leave_text= "' . $data['type_of_leave_text'] . '", status= "0", datecreated= "' . $data['datecreated'] . '", type_of_leave= "' . $data['type_of_leave'] . '" WHERE id = ' . $id);
				}
			} else {
				//echo 'insert';die;
				$data['status'] = 0;
				$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
				$ins_ul = $this->db->insert_id();
				// Convert start_time and end_time to DateTime objects
				$start_datetime = new DateTime($start_time);
				$end_datetime = new DateTime($end_time);

				// Loop through each date between start_time and end_time
				for ($date_timesheet = $start_datetime; $date_timesheet <= $end_datetime; $date_timesheet->modify('+1 day')) {
					// Prepare the data to insert into timesheet table
					$timesheet_data = array(
						'staff_id' => $staff_quit_job,
						'type' => 'PAL',
						'date_work' => $date_timesheet->format('Y-m-d'), // Insert date in 'Y-m-d' format
						'add_from' => $staff_quit_job
					);



					// Insert into timesheet table
					$this->db->insert('tbltimesheets_timesheet', $timesheet_data);
				}



				return $ins_ul;
				//echo 'data not available';
			}
		}elseif ($type_of_leave == 'maternity-leaves') {
			$data['start_time'] = $data['start_time'];
			$number_day_off = 1;
			// $data['number_of_days'] =  round($datediff / (60 * 60 * 24))+$number_day_off;
			$data['number_of_leaving_day'] =  $datediff + $number_day_off;
			$start_time = $data['start_time'];
			$date_ex = explode(' ', $start_time);
			$date_start = $date_ex[0];
			$end_time = $data['end_time'];
			$date_end = explode(' ', $end_time);
			$end_date = $date_end[0];
			//print_r($data);die;
			//echo 'SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = '.$staff_quit_job.' AND type_of_leave = 4 AND start_time BETWEEN  "'.$date_start.'" AND "'.$end_date.'" AND end_time BETWEEN "'.$date_start.'" AND "'.$end_date.'"';die;			
			$query = $this->db->query('SELECT * FROM tbltimesheets_requisition_leave WHERE staff_id = ' . $staff_quit_job . ' AND type_of_leave = 4 AND date(start_time) BETWEEN  "' . $date_start . '" AND "' . $end_date . '" AND date(end_time) BETWEEN "' . $date_start . '" AND "' . $end_date . '"')->result_array();
			//print_r($query);die;
			$count = count($query);
			//echo $count;
			//echo $date_start.'<br>';
			//echo $end_date.'<br>';
			if ($count > 0 and ($date_start == $end_date)) {
				////echo 'update';die;
				foreach ($query as $queryy) {
					$id = $queryy['id'];
					$query = $this->db->query('UPDATE tbltimesheets_requisition_leave SET subject = "' . $data['subject'] . '", reason= "' . $data['reason'] . '", handover_recipients= "' . $data['handover_recipients'] . '", followers_id= "' . $data['followers_id'] . '", type_of_leave_text= "' . $data['type_of_leave_text'] . '", status= "0", datecreated= "' . $data['datecreated'] . '", type_of_leave= "' . $data['type_of_leave'] . '" WHERE id = ' . $id);
				}
			} else {
				//echo 'insert';die;
				$data['status'] = 0;
				$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
				$ins_ul = $this->db->insert_id();
				// Convert start_time and end_time to DateTime objects
				$start_datetime = new DateTime($start_time);
				$end_datetime = new DateTime($end_time);

				// Loop through each date between start_time and end_time
				for ($date_timesheet = $start_datetime; $date_timesheet <= $end_datetime; $date_timesheet->modify('+1 day')) {
					// Prepare the data to insert into timesheet table
					$timesheet_data = array(
						'staff_id' => $staff_quit_job,
						'type' => 'MAL',
						'date_work' => $date_timesheet->format('Y-m-d'), // Insert date in 'Y-m-d' format
						'add_from' => $staff_quit_job
					);



					// Insert into timesheet table
					$this->db->insert('tbltimesheets_timesheet', $timesheet_data);
				}



				return $ins_ul;
				//echo 'data not available';
			}
		} 
			elseif ($type_of_leave == 'short-leaves') {
			$data['start_time'] = '';
			$number_day_off = 0;
			$data['number_of_leaving_day'] = $number_day_off;
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
			$insert_id = $this->db->insert_id();
			$end_datetime = new DateTime($data['end_time']);
			$timesheet_data = array(
				'staff_id' => $staff_quit_job,
				'type' => 'SHL',
				'date_work' => $end_datetime->format('Y-m-d'), // Insert date in 'Y-m-d' format
				'add_from' => $staff_quit_job
			);
			// Insert into timesheet table
			$this->db->insert('tbltimesheets_timesheet', $timesheet_data);
			// $data['number_of_days'] = $number_day_off;
		} elseif ($type_of_leave == 'saturday-leaves') {
			$data['start_time'] = '';
			$number_day_off = 0;
			$data['number_of_leaving_day'] = $number_day_off;
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
			$insert_id = $this->db->insert_id();
			$end_datetime = new DateTime($data['end_time']);
			$timesheet_data = array(
				'staff_id' => $staff_quit_job,
				'type' => 'SL',
				'date_work' => $end_datetime->format('Y-m-d'), // Insert date in 'Y-m-d' format
				'add_from' => $staff_quit_job
			);
			// Insert into timesheet table
			$this->db->insert('tbltimesheets_timesheet', $timesheet_data);



			// $data['number_of_days'] = $number_day_off;
		} elseif ($type_of_leave == 'present') {
			$number_day_off = 0;
			$data['number_of_leaving_day'] = $number_day_off;
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
			$insert_id = $this->db->insert_id();
			// $data['number_of_days'] = $number_day_off;

			$start_datetime = new DateTime($data['start_time']);
			$end_datetime = new DateTime($data['end_time']);

			// Loop through each date between start_time and end_time
			for ($date_timesheet = $start_datetime; $date_timesheet <= $end_datetime; $date_timesheet->modify('+1 day')) {
				// Prepare the data to insert into timesheet table
				$timesheet_data = array(
					'staff_id' => $staff_quit_job,
					'type' => 'P',
					'date_work' => $date_timesheet->format('Y-m-d'), // Insert date in 'Y-m-d' format
					'add_from' => $staff_quit_job
				);



				// Insert into timesheet table
				$this->db->insert('tbltimesheets_timesheet', $timesheet_data);
			}

		} else {
			$data['start_time'] = $data['start_time'];
			// Prefer form-calculated days (supports Session 1 / Session 2 half-days).
			if (!isset($data['number_of_leaving_day']) || $data['number_of_leaving_day'] === '' || !is_numeric($data['number_of_leaving_day'])) {
				$number_day_off = 1;
				$data['number_of_leaving_day'] = $datediff + $number_day_off;
			}
			$data['status'] = 0;
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
			$insert_id = $this->db->insert_id();
			// Sync attendance sheet for leave days (current + previous month, replaces AB/P).
			$ts_type = $this->resolve_leave_timesheet_type('', $data['type_of_leave'] ?? 'earned-leave');
			$this->upsert_leave_days_to_timesheet(
				(int) $staff_quit_job,
				date('Y-m-d', strtotime($data['start_time'])),
				date('Y-m-d', strtotime($data['end_time'])),
				$ts_type,
				(int) $insert_id,
				(float) $data['number_of_leaving_day']
			);
		}
		$check_proccess = $this->get_approve_setting($type, true, $staff_quit_job);

		//print_r($data);die;

		if ($check_proccess) {

			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);

			$insert_id = $this->db->insert_id();

			if ($insert_id) {

				handle_requisition_attachments($insert_id);

				if ($data['rel_type'] == 4) {

					foreach ($used_to as $key => $val) {

						$this->db->insert(db_prefix() . 'timesheets_go_bussiness_advance_payment', [

							'requisition_leave' => $insert_id,

							'used_to' => $val,

							'amoun_of_money' => timesheets_reformat_currency_asset($amoun_of_money[$key]),

							'request_date' => $request_date,

							'advance_payment_reason' => $advance_payment_reason,

						]);
					}
				}

				return $insert_id;
			} else {

				return false;
			}
		} else {

			//	$data['status'] = 0;

			//	$this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);

			//	$insert_id = $this->db->insert_id();

			if ($insert_id) {



				handle_requisition_attachments($insert_id);

				if ($data['rel_type'] == 4) {

					foreach ($used_to as $key => $val) {

						$this->db->insert(db_prefix() . 'timesheets_go_bussiness_advance_payment', [

							'requisition_leave' => $insert_id,

							'used_to' => $val,

							'amoun_of_money' => timesheets_reformat_currency_asset($amoun_of_money[$key]),

							'request_date' => $request_date,

							'advance_payment_reason' => $advance_payment_reason,

						]);
					}
				}

				$this->update_approve_request($insert_id, $type, $data['status']);

				return $insert_id;
			} else {

				return false;
			}
		}
	}



	/**

	 * delete requisition

	 * @param  integer $id

	 */

	public function delete_requisition($id)
	{

		$this->db->where('id', $id);

		$this->db->where('status', 1);

		$data_requisition = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

		if ($data_requisition) {

			if ($data_requisition->rel_type == '4') {

				$this->db->where('requisition_leave', $id);

				$this->db->delete(db_prefix() . 'timesheets_go_bussiness_advance_payment');
			}

			if ($data_requisition->rel_type == '1') {



				$type_of_leave_data = $this->get_type_of_leave_id($data_requisition->type_of_leave_text);



				// Get total leave in year of staff

				$day_off = $this->get_day_off($data_requisition->staff_id, '', $type_of_leave_data->type_id);

				// Number of leaving day

				$dd = $data_requisition->number_of_leaving_day;



				$update_days_off = abs($day_off->days_off - $dd);

				$update_remain = abs($day_off->total - $update_days_off);



				$this->db->where('type_of_leave', $type_of_leave_data->type_id);

				$this->db->where('staffid', $data_requisition->staff_id);

				$this->db->where('year', date('Y'));

				$this->db->update(db_prefix() . 'timesheets_day_off', [

					'remain' => $update_remain,

					'days_off' => $update_days_off,

				]);
			}

			$this->db->where('relate_type', 'leave');

			$this->db->delete(db_prefix() . 'timesheets_timesheet');
		}



		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_requisition_leave');

		if ($this->db->affected_rows() > 0) {

			return true;
		}



		return false;
	}



	/**

	 * get option val

	 * @return object

	 */

	public function get_option_val()
	{

		$this->db->select('option_val');

		$this->db->from('timesheets_option');

		$where_opt = 'option_name = "leave_according_process"';



		$this->db->where($where_opt);

		$query = $this->db->get()->row();

		return $query;
	}

	/**

	 * get staff shift applicable object

	 * @return array

	 */

	public function get_staff_shift_applicable_object()
	{

		$shift_applicable_object = [];

		$this->db->select('option_val');

		$this->db->where('option_name', 'shift_applicable_object');

		$row = $this->db->get(db_prefix() . 'timesheets_option')->row();

		if ($row) {

			if ($row->option_val != '') {

				$shift_applicable_object = explode(',', $row->option_val);
			} else {

				return [];
			}
		}



		$where = '';

		if ($shift_applicable_object) {

			foreach ($shift_applicable_object as $key => $value) {

				if ($where == '') {

					$where = '(role = ' . $value;
				} else {

					$where .= ' or role = ' . $value;
				}
			}
		}



		if ($where != '') {

			$where .= ')';
		}



		if ($where == '') {

			$where .= '(select count(*) from ' . db_prefix() . 'staff_contract where staff = ' . db_prefix() . 'staff.staffid and date_format(start_valid, "%Y-%m") <="' . date('y-m') . '" and if(end_valid = null, date_format(end_valid, "%Y-%m") >="' . date('Y-m') . '",1=1)) > 0 and status_work="working" and active=1';
		} else {

			$where .= ' and (select count(*) from ' . db_prefix() . 'staff_contract where staff = ' . db_prefix() . 'staff.staffid and date_format(start_valid, "%Y-%m") <="' . date('Y-m') . '" and if(end_valid = null, date_format(end_valid, "%Y-%m") >="' . date('Y-m') . '",1=1)) > 0 and status_work="working" and active=1';
		}

		$this->db->where($where);



		return $this->db->get(db_prefix() . 'staff')->result_array();
	}

	/**

	 * check latch timesheet

	 * @param  integer $month

	 * @return bool

	 */

	public function check_latch_timesheet($month)
	{

		if ($month != '') {

			$this->db->where('month_latch', $month);

			$count = $this->db->count_all_results(db_prefix() . 'timesheets_latch_timesheet');



			if ($count > 0) {

				return true;
			} else {

				return false;
			}
		}



		return false;
	}

	/**

	 * get staff timekeeping applicable object

	 * @return array

	 */

	public function get_staff_timekeeping_applicable_object($for_cronjob = false)
	{

		$data_timekeeping_form = get_timesheets_option('timekeeping_form');



		$timekeeping_applicable_object = [];

		if ($data_timekeeping_form == 'timekeeping_task') {

			if (get_timesheets_option('timekeeping_task_role') != '') {

				$timekeeping_applicable_object = get_timesheets_option('timekeeping_task_role');
			}
		} elseif ($data_timekeeping_form == 'timekeeping_manually') {

			if (get_timesheets_option('timekeeping_manually_role') != '') {

				$timekeeping_applicable_object = get_timesheets_option('timekeeping_manually_role');
			}
		} elseif ($data_timekeeping_form == 'csv_clsx') {

			if (get_timesheets_option('csv_clsx_role') != '') {

				$timekeeping_applicable_object = get_timesheets_option('csv_clsx_role');
			}
		}

		$where = '';

		if ($timekeeping_applicable_object && $timekeeping_applicable_object != '' && $timekeeping_applicable_object != null) {

			$where .= 'find_in_set(role, "' . $timekeeping_applicable_object . '")';
		}

		if ($for_cronjob == false) {

			if ($where != '') {

				$where .= timesheet_staff_manager_query('attendance_management');
			} else {

				$where .= timesheet_staff_manager_query('attendance_management', 'staffid', '');
			}
		}

		if ($where != '') {

			$where .= ' and active = 1';
		} else {

			$where .= ' active = 1';
		}

		if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {

			$this->db->where($where);

			$this->db->order_by('firstname', 'ASC');
		}

		$result = $this->db->get(db_prefix() . 'staff')->result_array();

		return $result;
	}

	/**

	 * unlatch timesheet

	 * @param  integer $month

	 * @return bool

	 */

	public function unlatch_timesheet($month)
	{

		if ($month != '') {

			$this->db->where('month_latch', $month);

			$this->db->delete(db_prefix() . 'timesheets_latch_timesheet');

			if ($this->db->affected_rows() > 0) {

				$m = date('m', strtotime('01-' . $month));

				$y = date('Y', strtotime('01-' . $month));

				$this->db->where('month(date_work) = ' . $m . ' and year(date_work) = ' . $y . ' and type="NS"');

				$this->db->delete(db_prefix() . 'timesheets_timesheet');

				$this->db->where('month(date_work) = ' . $m . ' and year(date_work) = ' . $y);

				$this->db->update(db_prefix() . 'timesheets_timesheet', ['latch' => 0]);

				return true;
			}

			return false;
		}



		return false;
	}

	/**

	 * get hour check in out staff

	 * @param  integer $staff_id

	 * @param  date $datetime

	 * @return integer

	 */

	public function get_hour_check_in_out_staff($staff_id, $datetime)
	{

		$list_check_in_out = $this->db->query('select date from ' . db_prefix() . 'check_in_out where staff_id = ' . $staff_id . ' and date(date) = \'' . $datetime . '\'')->result_array();

		$hour = 0;

		$lunch_time = 0;

		if (isset($list_check_in_out[0]['date']) && isset($list_check_in_out[1]['date'])) {

			$d1 = $this->format_date_time($list_check_in_out[0]['date']);

			$d2 = $this->format_date_time($list_check_in_out[1]['date']);



			$time_in = strtotime(date('H:i:s', strtotime($d1)));

			$time_out = strtotime(date('H:i:s', strtotime($d2)));



			$list_shift = $this->get_shift_work_staff_by_date($staff_id, $datetime);

			foreach ($list_shift as $ss) {

				$data_shift_type = $this->timesheets_model->get_shift_type($ss);



				$time_in_ = $time_in;

				$time_out_ = $time_out;

				if ($data_shift_type) {

					$d1 = $this->format_date_time($data_shift_type->time_start_work);

					$d2 = $this->format_date_time($data_shift_type->time_end_work);

					$d3 = $this->format_date_time($data_shift_type->start_lunch_break_time);

					$d4 = $this->format_date_time($data_shift_type->end_lunch_break_time);



					$start_work = strtotime(date('H:i:s', strtotime($d1)));

					$end_work = strtotime(date('H:i:s', strtotime($d2)));

					$start_lunch_break = strtotime(date('H:i:s', strtotime($d3)));

					$end_lunch_break = strtotime(date('H:i:s', strtotime($d4)));



					/* this was making sure that our check in and check out falls within range of shift */
					// if ($time_in < $start_work && $time_out > $start_work) {

					// 	$time_in_ = $start_work;
					// }



					// if ($time_out > $end_work && $time_in < $end_work) {

					// 	$time_out_ = $end_work;
					// }

					if ($time_out < $start_work) {

						continue;
					}

					if ($time_out_ >= $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					$hour += round(abs($time_out_ - $time_in_) / (60 * 60), 2);
				}
			}
		}



		$result = abs($lunch_time - $hour);

		if ($result == 0) {

			return '';
		}

		return $result;
	}

	/**

	 * report by leave statistics

	 */

	public function report_by_leave_statistics()
	{

		$months_report = $this->input->post('months_report');

		$custom_date_select = '';

		if ($months_report != '') {



			if (is_numeric($months_report)) {

				// last month

				if ($months_report == '1') {

					$beginmonth = date('y-m-01', strtotime('first day of last month'));

					$endmonth = date('y-m-t', strtotime('last day of last month'));
				} else {

					$months_report = (int) $months_report;

					$months_report--;

					$beginmonth = date('y-m-01', strtotime("-$months_report month"));

					$endmonth = date('y-m-t');
				}



				$custom_date_select = '(hrl.start_time between "' . $beginmonth . '" and "' . $endmonth . '")';
			} elseif ($months_report == 'this_month') {

				$custom_date_select = '(hrl.start_time between "' . date('y-m-01') . '" and "' . date('y-m-t') . '")';
			} elseif ($months_report == 'this_year') {

				$custom_date_select = '(hrl.start_time between "' .

					date('Y-m-d', strtotime(date('y-01-01'))) .

					'" and "' .

					date('Y-m-d', strtotime(date('y-12-31'))) . '")';
			} elseif ($months_report == 'last_year') {

				$custom_date_select = '(hrl.start_time between "' .

					date('Y-m-d', strtotime(date(date('y', strtotime('last year')) . '-01-01'))) .

					'" and "' .

					date('Y-m-d', strtotime(date(date('y', strtotime('last year')) . '-12-31'))) . '")';
			} elseif ($months_report == 'custom') {

				$from_date = to_sql_date($this->input->post('report_from'));

				$to_date = to_sql_date($this->input->post('report_to'));

				if ($from_date == $to_date) {

					$custom_date_select = 'hrl.start_time ="' . $from_date . '"';
				} else {

					$custom_date_select = '(hrl.start_time between "' . $from_date . '" and "' . $to_date . '")';
				}
			}
		}



		$chart = [];

		$dpm = $this->departments_model->get();

		foreach ($dpm as $d) {

			$chart['categories'][] = $d['name'];



			$chart['sick_leave'][] = $this->count_type_leave($d['departmentid'], 1, $custom_date_select);

			$chart['maternity_leave'][] = $this->count_type_leave($d['departmentid'], 2, $custom_date_select);

			$chart['private_work_with_pay'][] = $this->count_type_leave($d['departmentid'], 3, $custom_date_select);

			$chart['private_work_without_pay'][] = $this->count_type_leave($d['departmentid'], 4, $custom_date_select);

			$chart['child_sick'][] = $this->count_type_leave($d['departmentid'], 5, $custom_date_select);

			$chart['power_outage'][] = $this->count_type_leave($d['departmentid'], 6, $custom_date_select);

			$chart['meeting_or_studying'][] = $this->count_type_leave($d['departmentid'], 7, $custom_date_select);
		}



		return $chart;
	}



	/**

	 * count type leave

	 * @param   integer $department

	 * @param   integer $type

	 * @param   date  $custom_date_select

	 * @return array

	 */

	public function count_type_leave($department, $type, $custom_date_select)
	{



		if ($custom_date_select != '') {

			$query = $this->db->query('select hrl.id, hrl.subject from ' . db_prefix() . 'timesheets_requisition_leave hrl left join ' . db_prefix() . 'staff_departments sd on sd.staffid = hrl.staff_id where sd.departmentid = ' . $department . ' and hrl.type_of_leave = ' . $type . ' and ' . $custom_date_select)->result_array();
		} else {

			$query = $this->db->query('select hrl.id, hrl.subject from ' . db_prefix() . 'timesheets_requisition_leave hrl left join ' . db_prefix() . 'staff_departments sd on sd.staffid = hrl.staff_id where sd.departmentid = ' . $department . ' and hrl.type_of_leave = ' . $type)->result_array();
		}



		return count($query);
	}

	/**

	 * count timekeeping by month

	 * @param  integer $staffid

	 * @param  integer $month

	 * @return integer

	 */

	public function count_timekeeping_by_month($staffid, $month)
	{

		if ($staffid != '' && $staffid != 0) {

			$month = date('m-y', strtotime($month));



			$check_latch_timesheet = $this->check_latch_timesheet($month);



			if (!$check_latch_timesheet) {

				return 0;
			}



			$this->db->where('date_format(date_work, "%m-%y") = "' . $month . '" and staff_id = ' . $staffid);

			$timekeeping = $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();

			$count_timekeeping = 0;

			$count_result = 0;

			foreach ($timekeeping as $key => $value) {

				// if ($value['type'] == 'W'  $value['type'] == 'P' || $value['type'] == 'H' || $value['type'] == 'R' || $value['type'] == 'CT' || $value['type'] == 'CD') {
				if ($value['type'] == 'W' || $value['type'] == 'P' || $value['type'] == 'H' || $value['type'] == 'R' || $value['type'] == 'CT' || $value['type'] == 'CD' || $value['type'] == 'HD') {


					$count_timekeeping += $value['value'];
				}
			}



			$work_shift = $this->get_data_edit_shift_by_staff($staffid);



			$lunch_break = (($work_shift[3]['monday'][0] - $work_shift[2]['monday'][0]) * 60) + ($work_shift[3]['monday'][1] - $work_shift[2]['monday'][1]);

			$work_time = (($work_shift[1]['monday'][0] - $work_shift[0]['monday'][0]) * 60) + ($work_shift[1]['monday'][1] - $work_shift[0]['monday'][1]);



			$count_result += $count_timekeeping / (round($work_time - $lunch_break) / 60);

			return $count_result;
		} else {

			return 1;
		}
	}



	/**

	 * get timesheets task ts by month

	 * @param  date $date

	 * @return array

	 */

	public function get_timesheets_task_ts_by_month($date)
	{

		$query = 'select date_format(from_unixtime(`start_time`), \'%y-%m-%d\') as date_work, date_format(from_unixtime(`end_time`), \'%y-%m-%d\') as end_date, staff_id, start_time, end_time from ' . db_prefix() . 'taskstimers where date_format(from_unixtime(`start_time`), \'%y-%m-%d\') <= \'' . $date . '\' and date_format(from_unixtime(`end_time`), \'%y-%m-%d\') >= \'' . $date . '\'';

		$data_task = $this->db->query($query)->result_array();
	}

	/**

	 * get ts by task and staff

	 * @param  date $date

	 * @param  imteger $staff

	 * @return array

	 */

	public function get_ts_by_task_and_staff($date, $staff)
	{

		$query = 'select date_format(from_unixtime(`start_time`), \'%y-%m-%d\') as date_work, date_format(from_unixtime(`end_time`), \'%y-%m-%d\') as end_date, staff_id, start_time, end_time from ' . db_prefix() . 'taskstimers where date_format(from_unixtime(`start_time`), \'%y-%m-%d\') <= \'' . $date . '\' and date_format(from_unixtime(`end_time`), \'%y-%m-%d\') >= \'' . $date . '\' and staff_id = ' . $staff;

		$data_timesheets = $this->db->query($query)->result_array();
	}

	/**

	 * [check_format_date_ymd

	 * @param  date $date

	 * @return boolean

	 */

	public function check_format_date_ymd($date)
	{

		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {

			return true;
		} else {

			return false;
		}
	}

	/**

	 * check format date

	 * @param  date $date

	 * @return boolean

	 */

	public function check_format_date($date)
	{

		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s(0|[0-1][0-9]|2[0-4]):?((0|[0-5][0-9]):?(0|[0-5][0-9])|6000|60:00)$/", $date)) {

			return true;
		} else {

			return false;
		}
	}

	/**

	 * get_role

	 * @param  integer $roleid

	 * @return object or array

	 */

	public function get_role($roleid)
	{

		$this->db->where('roleid', $roleid);

		return $this->db->get(db_prefix() . 'roles')->row();
	}



	/**

	 * leave of the year

	 * @param  integer $staff_id

	 * @return object or array

	 */

	public function leave_of_the_year($staff_id = '')
	{

		if ($staff_id != '') {

			$this->db->where('staff_id', $staff_id);

			return $this->db->get(db_prefix() . 'leave_of_the_year')->row();
		} else {

			return $this->db->get(db_prefix() . 'leave_of_the_year')->result_array();
		}
	}

	/**

	 * get staff query

	 * @param  string $query

	 * @return array

	 */

	public function get_staff_query($query = '')
	{

		if ($query != '') {

			$query = ' where ' . $query;
		}

		return $this->db->query('select * from ' . db_prefix() . 'staff' . $query . ' order by firstname desc')->result_array();
	}

	/**

	 * add shift type

	 * @param integer

	 */

	public function add_shift_type($data)
	{

		if (isset($data['time_start'])) {

			if (!$this->check_format_date_ymd($data['time_start'])) {

				$data['time_start'] = to_sql_date($data['time_start']);
			}
		}

		if (isset($data['time_end'])) {

			if (!$this->check_format_date_ymd($data['time_end'])) {

				$data['time_end'] = to_sql_date($data['time_end']);
			}
		}

		$this->db->insert(db_prefix() . 'shift_type', $data);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			return $insert_id;
		}

		return 0;
	}

	/**

	 * update shift type

	 * @param integer

	 */

	public function update_shift_type($data)
	{

		if (isset($data['time_start'])) {

			if (!$this->check_format_date_ymd($data['time_start'])) {

				$data['time_start'] = to_sql_date($data['time_start']);
			}
		}

		if (isset($data['time_end'])) {

			if (!$this->check_format_date_ymd($data['time_end'])) {

				$data['time_end'] = to_sql_date($data['time_end']);
			}
		}

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'shift_type', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete shift type

	 * @param  integer

	 * @return bool

	 */

	public function delete_shift_type($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'shift_type');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * get shift type

	 * @param  integer

	 * @return bool

	 */

	public function get_shift_type($id = '')
	{

		if ($id != '') {
			$id = (int) $id;
			if (array_key_exists($id, $this->att_cache_shift_type)) {
				return $this->att_cache_shift_type[$id];
			}

			$this->db->where('id', $id);

			$row = $this->db->get(db_prefix() . 'shift_type')->row();
			$this->att_cache_shift_type[$id] = $row;

			return $row;
		} else {

			return $this->db->get(db_prefix() . 'shift_type')->result_array();
		}
	}

	/**

	 * get staff shift list

	 * @return db

	 */

	public function get_staff_shift_list()
	{

		return $this->db->query('select distinct(staff_id) from ' . db_prefix() . 'work_shift_detail')->result_array();
	}



	/**

	 * get data staff shift list

	 * @return db

	 */

	public function get_data_staff_shift_list($staff_id)
	{

		return $this->db->query('select * from ' . db_prefix() . 'work_shift_detail where staff_id = ' . $staff_id)->result_array();
	}

	/**

	 * check in

	 * @param  array $data

	 * @return integer

	 */

	public function check_in($data)
	{

		// Check valid IP 

		$enable_check_valid_ip = get_timesheets_option('timekeeping_enable_valid_ip');

		if ($enable_check_valid_ip && $enable_check_valid_ip == 1) {

			$client_ip = '';

			if (isset($data['ip_address'])) {

				$client_ip = $data['ip_address'];

				unset($data['ip_address']);
			} else {

				$client_ip = get_client_ip();
			}

			if ($client_ip != '') {

				$ip_list = $this->get_valid_ip();

				if ($ip_list && count($ip_list) > 0) {

					$registered = false;

					foreach ($ip_list as $key => $row) {

						if ($client_ip == $row['ip']) {

							$registered = !$registered;

							break;
						}
					}

					if (!$registered) {

						// Access denie

						return 5;
					}
				} else {

					// Access denie

					return 5;
				}
			} else {

				// Cannot get client IP address

				return 6;
			}
		}

		// 

		$id_admin = 0;

		$date = '';

		$affectedrows = 0;

		if (!isset($data['date'])) {

			$data['date'] = date('Y-m-d');

			$date = $data['date'];
		}

		if (!isset($data['staff_id'])) {

			$data['staff_id'] = get_staff_user_id();
		}

		if ($data['edit_date'] != '') {

			$temp = $this->format_date_time($data['edit_date']);

			$split_date = explode(' ', $temp);

			$date = $split_date[0];

			$data['date'] = $temp;
		} else {

			$date = date('Y-m-d');

			$data['date'] = $date . ' ' . date('H:i:s');
		}

		unset($data['edit_date']);



		if (($date != '') && ($data['staff_id'] != '')) {

			$check_more = '';

			$count_st = 0;

			$data_setting_coordinates = get_timesheets_option('allow_attendance_by_coordinates');

			if ($data_setting_coordinates && $data_setting_coordinates == 1) {

				$check_more = 'check_coordinates';

				$count_st++;
			}

			$data_setting_rooute = get_timesheets_option('allow_attendance_by_route');

			if ($data_setting_rooute && $data_setting_rooute == 1) {

				$check_more = 'check_route';

				$count_st++;
			}

			$point_id = '';

			$workplace_id = '';

			if ($check_more != '') {

				if (isset($data['location_user'])) {

					$data_location = explode(',', $data['location_user']);

					if (isset($data_location[0]) && isset($data_location[1])) {

						$latitude = $data_location[0];

						$longitude = $data_location[1];

						if ($count_st == 2) {

							if (isset($data['point_id'])) {

								if ($data['point_id'] != '') {

									$point_id = $data['point_id'];
								}
							}

							if ($point_id == '') {

								$point_id = $this->get_next_point($data['staff_id'], $date, $latitude, $longitude)->id;
							}

							if ($point_id == '') {

								$check_more = 'check_coordinates';
							}
						}

						switch ($check_more) {

							case 'check_route':

								// Attendance by route point

								// Get geolocation of this route point and caculation distance to location of you

								// If valid will return route id to insert in check_in_out table

								// Else return error:

								// Error 2: Current location is not allowed to attendance

								// Error 3: Location information is unknown

								if ($point_id == '') {

									if (isset($data['point_id'])) {

										if ($data['point_id'] != '') {

											$point_id = $data['point_id'];
										}
									}

									if ($point_id == '') {

										$point_id = $this->get_next_point($data['staff_id'], $date, $latitude, $longitude)->id;
									}
								}

								if ($point_id != '') {

									$route_point_latitude = '';

									$route_point_longitude = '';

									$max_distance = '';

									$data_route_point = $this->get_route_point($point_id);

									if ($data_route_point) {

										$route_point_latitude = $data_route_point->latitude;

										$route_point_longitude = $data_route_point->longitude;

										$max_distance = $data_route_point->distance;
									}

									if ($latitude != '' && $longitude != '' && $route_point_latitude != '' && $route_point_longitude != '' && $max_distance != '') {

										$cal_distance = $this->compute_distance($route_point_latitude, $route_point_longitude, $latitude, $longitude);

										if ((float) $cal_distance > (float) $max_distance) {

											// Invalid distance

											// Error 2: Current location is not allowed to attendance

											return 2;
										}
									} else {

										// Error 3: Location information is unknown

										return 3;
									}
								} else {

									// Error 4: Route point is unknown

									return 4;
								}

								break;

							case 'check_coordinates':

								// Attendance by geolocation

								$res_coordinates = $this->check_attendance_by_coordinates($data['staff_id'], $latitude, $longitude);

								$error = $res_coordinates->error_code;

								$workplace_id = $res_coordinates->workplace_id;

								if ($error == 2 || $error == 3) {

									// Error 2: Current location is not allowed to attendance

									// Error 3: Location information is unknown

									return $error;
								}

								break;
						}
					} else {

						// Error 3: Location information is unknown

						return 3;
					}
				} else {

					// Error 3: Location information is unknown

					return 3;
				}
			}

			$send_notify = false;

			if (isset($data['send_notify']) && $data['send_notify'] == 1) {

				$send_notify = true;

				unset($data['send_notify']);
			}

			$data['route_point_id'] = $point_id;

			$data['workplace_id'] = $workplace_id;

			unset($data['location_user']);

			unset($data['point_id']);

			if (isset($data['ip_address'])) {

				unset($data['ip_address']);
			}

			$this->db->insert(db_prefix() . 'check_in_out', $data);

			$insert_id = $this->db->insert_id();

			if ($insert_id) {

				$affectedrows++;
			}



			$this->add_check_in_out_value_to_timesheet($data['staff_id'], $date);

			if ($affectedrows > 0) {

				if ($send_notify) {

					$staff_receive = get_timesheets_option('attendance_notice_recipient');

					if ($staff_receive && $staff_receive != '' && $staff_array_id = explode(',', $staff_receive)) {

						$type_check_email = '';

						$type_check_notify = '';

						if ($data['type_check'] == 1) {

							$type_check_email = strtolower(_l('checked_out'));

							$type_check_notify = strtolower(_l('checked_out_at'));
						} else {

							$type_check_email = strtolower(_l('checked_in'));

							$type_check_notify = strtolower(_l('checked_in_at'));
						}

						foreach ($staff_array_id as $key => $staffid) {

							$email = $this->get_staff_email($staffid);

							if ($email != '') {

								$staff_name = get_staff_full_name($data['staff_id']);

								$data_send_mail = new stdClass();

								$data_send_mail->receiver = $email;

								$data_send_mail->staff_name = $staff_name;

								$data_send_mail->type_check = $type_check_email;

								$data_send_mail->date_time = _d($data['date']);

								$template = mail_template('attendance_notice', 'timesheets', $data_send_mail);

								$template->send();

								$this->notifications($staffid, 'timesheets/requisition_manage', $type_check_notify . ' ' . _d($data['date']));
							}
						}

						// Send email to customer when staff check in/out at customer location

						if ($check_more == 'check_route' && (get_timesheets_option('allow_employees_to_create_work_points') == 1)) {

							$customer_email = $this->get_customer_email_route_point($point_id);

							if ($customer_email != '') {

								$staff_name = get_staff_full_name($data['staff_id']);

								$data_send_mail = new stdClass();

								$data_send_mail->receiver = $customer_email;

								$data_send_mail->staff_name = $staff_name;

								$data_send_mail->type_check = $type_check_email;

								$data_send_mail->date_time = _d($data['date']);

								$template = mail_template('attendance_notice', 'timesheets', $data_send_mail);

								$template->send();
							}
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	/**

	 * check_ts check checked in and checked out

	 * @param  integer $staff_id

	 * @param  date $date

	 * @return stdclass

	 */

	public function check_ts($staff_id, $date)
	{

		$check_in = 0;

		$check_out = 0;

		$date_check_in = '';

		$date_check_out = '';

		$data_check_in = $this->db->query('select id, date from ' . db_prefix() . 'check_in_out where staff_id = ' . $staff_id . ' and date(date) = \'' . $date . '\' and type_check = 1')->row();

		if ($data_check_in) {

			$check_in = $data_check_in->id;

			$date_check_in = $data_check_in->date;
		}

		$data_check_out = $this->db->query('select id, date from ' . db_prefix() . 'check_in_out where staff_id = ' . $staff_id . ' and date(date) = \'' . $date . '\' and type_check = 2')->row();

		if ($data_check_out) {

			$check_out = $data_check_out->id;

			$date_check_out = $data_check_out->date;
		}

		$data_check_result = new stdclass();

		$data_check_result->check_in = $check_in;

		$data_check_result->check_out = $check_out;

		$data_check_result->date_check_in = $date_check_in;

		$data_check_result->date_check_out = $date_check_out;

		return $data_check_result;
	}

	/**

	 * get shift data

	 * @param  integer $id

	 * @return stdclass

	 */

	public function get_shift_data($id)
	{

		$data_shift_res = new stdclass();

		$data_shift_res->name = '';

		$data_shift_res->color = '';

		$data_shift_res->description = '';

		$data_shift_res->datecreated = '';



		$data_shift_res->time_start_work = '';

		$data_shift_res->time_end_work = '';



		$data_shift_res->start_lunch_break_time = '';

		$data_shift_res->end_lunch_break_time = '';



		$data_shift_res->start_work_hour = '';

		$data_shift_res->end_work_hour = '';



		$data_shift_res->start_lunch_hour = '';

		$data_shift_res->end_lunch_hour = '';



		$this->db->where('id', $id);

		$data_shift = $this->db->get(db_prefix() . 'shift_type')->row();

		if ($data_shift) {

			$data_shift_res->name = $data_shift->shift_type_name;

			$data_shift_res->color = $data_shift->color;

			$data_shift_res->description = $data_shift->description;

			$data_shift_res->datecreated = $data_shift->datecreated;



			$time_start_sp = explode(' ', $data_shift->time_start_work);

			$time_end_sp = explode(' ', $data_shift->time_end_work);

			$start_lunch_sp = explode(' ', $data_shift->start_lunch_break_time);

			$end_lunch_sp = explode(' ', $data_shift->end_lunch_break_time);



			$data_shift_res->time_start_work = $data_shift->time_start_work;

			$data_shift_res->time_end_work = $data_shift->time_end_work;



			$data_shift_res->start_lunch_break_time = $data_shift->start_lunch_break_time;

			$data_shift_res->end_lunch_break_time = $data_shift->end_lunch_break_time;



			$data_shift_res->start_work_hour = isset($time_start_sp[1]) ? $time_start_sp[1] : '';

			$data_shift_res->end_work_hour = isset($time_end_sp[1]) ? $time_end_sp[1] : '';



			$data_shift_res->start_lunch_hour = isset($start_lunch_sp[1]) ? $start_lunch_sp[1] : '';

			$data_shift_res->end_lunch_hour = isset($end_lunch_sp[1]) ? $end_lunch_sp[1] : '';
		}

		return $data_shift_res;
	}

	/**

	 * get hour shift staff

	 * @param  integer $staff_id

	 * @param  integer $date

	 * @return integer

	 */

	public function get_hour_shift_staff($staff_id, $date)
	{
		$cache_key = $staff_id . '|' . $date;
		if (array_key_exists($cache_key, $this->att_cache_hour_shift)) {
			return $this->att_cache_hour_shift[$cache_key];
		}

		$result = 0;

		$data_shift_list = $this->get_shift_work_staff_by_date($staff_id, $date);

		foreach ($data_shift_list as $ss) {

			$data_shift_type = $this->get_shift_type($ss);

			if ($data_shift_type) {

				$hour = $this->get_hour($data_shift_type->time_start_work, $data_shift_type->time_end_work);

				$lunch_hour = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);

				$result += abs($hour - $lunch_hour);
			}
		}

		$this->att_cache_hour_shift[$cache_key] = $result;

		return $result;
	}

	/**

	 * get hour

	 * @param date $date1

	 * @param date $date2

	 * @return decimal

	 */

	public function get_hour($date1, $date2)
	{

		$result = 0;

		if ($date1 != '' && $date2 != '') {

			$timestamp1 = strtotime($date1);

			$timestamp2 = strtotime($date2);

			$result = number_format(abs($timestamp2 - $timestamp1) / (60 * 60), 2);
		}

		return $result;
	}

	/**

	 * format date

	 * @param  date $date

	 * @return date

	 */

	public function format_date($date)
	{

		if (!$this->check_format_date_ymd($date)) {

			$date = to_sql_date($date);
		}

		return $date;
	}



	/**

	 * format date time

	 * @param  date $date

	 * @return date

	 */

	public function format_date_time($date)
	{

		if (!$this->check_format_date($date)) {

			$date = to_sql_date($date, true);
		}

		return $date;
	}

	/**

	 * get workshiftms

	 * @param integer $id

	 * @return integer

	 */

	public function get_workshiftms($id)
	{

		$this->db->where('id', $id);

		return $this->db->get(db_prefix() . 'work_shift')->row();
	}

	/**

	 * get id shift type by date and master id

	 * @param  integer $staff_id

	 * @param  integer $date

	 * @param  integer $work_shift_id

	 * @return integer

	 */

	public function get_id_shift_type_by_date_and_master_id($staff_id, $date, $work_shift_id)
	{

		if ($staff_id != '' && $date != '' && $work_shift_id != '') {

			$this->db->where('staff_id', $staff_id);

			$this->db->where('date', $date);

			$this->db->where('work_shift_id', $work_shift_id);

			$this->db->select('shift_id');

			$data = $this->db->get(db_prefix() . 'work_shift_detail')->row();

			if ($data) {

				return $data->shift_id;
			} else {

				return 0;
			}
		} else {

			return 0;
		}
	}



	/**

	 * gets the total standard workload.

	 *

	 * @param      int  $staffid  the staffid

	 * @param      date  $f_date   the f date

	 * @param      date  $t_date   the t date

	 *

	 * @return     array   the staff select.

	 */

	public function get_total_work_time($staffid, $f_date, $t_date)
	{

		$total = 0;

		while (strtotime($f_date) <= strtotime($t_date)) {

			$standard_workload = $this->get_hour_shift_staff($staffid, $f_date);

			$total += $standard_workload;



			$f_date = date('Y-m-d', strtotime('+1 day', strtotime($f_date)));
		}



		return $total;
	}



	/**

	 * get_shift_type_id_by_number_day

	 * @param  int $work_shift_id

	 * @param  int $number

	 * @param  int $staff_id

	 * @return int object

	 */

	public function get_shift_type_id_by_number_day($work_shift_id, $number, $staff_id = '')
	{

		if ($work_shift_id != '') {

			if ($staff_id != '') {

				$query = 'select shift_id from ' . db_prefix() . 'work_shift_detail_number_day where work_shift_id = ' . $work_shift_id . ' and number = \'' . $number . '\' and staff_id = ' . $staff_id;

				return $this->db->query($query)->row();
			} else {

				$query = 'select shift_id from ' . db_prefix() . 'work_shift_detail_number_day where work_shift_id = ' . $work_shift_id . ' and number = \'' . $number . '\'';

				return $this->db->query($query)->row();
			}
		}
	}



	public function get_first_staff_work_shift($work_shift_id)
	{

		if ($work_shift_id) {

			if ($work_shift_id != '') {

				$this->db->where('work_shift_id', $work_shift_id);

				return $this->db->get(db_prefix() . 'work_shift_detail')->row();
			}
		}
	}

	/**

	 * gets the number day.

	 *

	 * @param      string   $from_date  the from date

	 * @param      string   $to_date    to date

	 *

	 * @return     integer  the number day.

	 */

	public function get_number_day($from_date, $to_date, $staffid)
	{

		$count = 0;

		if ($to_date == '') {

			$to_date = date('Y-m-d');
		}

		for ($i = 0; $i < 5; $i++) {

			if (strtotime($from_date) <= strtotime($to_date)) {

				if ($this->get_hour_shift_staff($staffid, $from_date)) {

					$count++;
				}



				$from_date = date('Y-m-d', strtotime($from_date . ' + 1 days'));

				$i = 0;
			} else {

				$i = 10;
			}
		}

		return $count;
	}



	/**

	 * delete shift staff by day name

	 * @param   int $work_shift_id

	 * @param   string $day_name

	 * @param   int $staff_id

	 */

	public function get_shift_staff_by_day_name($work_shift_id, $day_name, $staff_id = '')
	{

		if ($work_shift_id != '' && $day_name != '') {

			if ($staff_id == '') {

				$this->db->where('number', $day_name);

				$this->db->where('work_shift_id', $work_shift_id);

				return $this->db->get(db_prefix() . 'work_shift_detail_number_day')->row();
			} else {

				$this->db->where('staff_id', $staff_id);

				$this->db->where('number', $day_name);

				$this->db->where('work_shift_id', $work_shift_id);

				return $this->db->get(db_prefix() . 'work_shift_detail_number_day')->row();
			}
		}
	}

	/**

	 * convert_day_to_number

	 * @param  int $day

	 * @return  int

	 */

	public function convert_day_to_number($day)
	{

		switch ($day) {

			case 'mon':

				return '1';

			case 'tue':

				return '2';

			case 'wed':

				return '3';

			case 'thu':

				return '4';

			case 'fri':

				return '5';

			case 'sat':

				return '6';

			case 'sun':

				return '7';
		}
	}



	/**

	 * get shift staff by date

	 * @param   int $work_shift_id

	 * @param   string $day_name

	 * @param   int $staff_id

	 */

	public function get_shift_staff_by_date($work_shift_id, $date, $staff_id = '')
	{

		if ($work_shift_id != '' && $date != '') {

			if ($staff_id == '') {

				$this->db->where('date', $date);

				$this->db->where('work_shift_id', $work_shift_id);

				return $this->db->get(db_prefix() . 'work_shift_detail')->row();
			} else {

				$this->db->where('staff_id', $staff_id);

				$this->db->where('date', $date);

				$this->db->where('work_shift_id', $work_shift_id);

				return $this->db->get(db_prefix() . 'work_shift_detail')->row();
			}
		}
	}

	/**

	 * get_work_shift

	 * @param  int $id

	 * @return object or array object

	 */

	public function get_work_shift($id = "")
	{

		if ($id != '') {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'work_shift')->row();
		} else {

			return $this->db->get(db_prefix() . 'work_shift')->result_array();
		}
	}



	/**

	 * get day off staff by date

	 * @param  integer $staff

	 * @param  date $date

	 * @return array

	 */

	public function get_day_off_staff_by_date($staff, $date)
	{

		if ($staff != '' && $date != '') {

			$this->db->where('staffid', $staff);

			$this->db->select('role');

			$role_data = $this->db->get(db_prefix() . 'staff')->row();

			$role_id = 0;

			$add_query = '';

			if ($role_data) {

				if ($role_data->role != '') {

					if ($role_data->role != null) {

						$add_query .= 'find_in_set(' . $role_data->role . ',position)';
					}
				}
			}

			$add_query = '';

			$data_department = $this->departments_model->get_staff_departments($staff, true);

			if ($data_department) {

				$department_query = '';

				foreach ($data_department as $key => $value) {

					if ($department_query == '') {

						$department_query .= 'find_in_set(' . $value . ', department)';
					} else {

						$department_query .= ' or find_in_set(' . $value . ', department)';
					}
				}



				if ($department_query != '') {

					if ($add_query != '') {

						$add_query = '(' . $add_query . ' OR (' . $department_query . '))';
					} else {

						$add_query = $department_query;
					}
				}
			}



			if ($add_query != '') {

				$add_query = $add_query . ' and';
			}



			$query = 'select * from ' . db_prefix() . 'day_off where ' . $add_query . ' break_date = \'' . $date . '\' and repeat_by_year = 0';

			$query2 = 'select * from ' . db_prefix() . 'day_off where ' . $add_query . ' day(break_date) = day(\'' . $date . '\') and month(break_date) = month(\'' . $date . '\') and repeat_by_year = 1';

			$result = $this->db->query($query)->result_array();

			$result2 = $this->db->query($query2)->result_array();

			if (!($result && $result2)) {

				$query = 'select * from ' . db_prefix() . 'day_off where department="" and position="" and break_date = \'' . $date . '\' and repeat_by_year = 0';

				$query2 = 'select * from ' . db_prefix() . 'day_off where department="" and position="" and day(break_date) = day(\'' . $date . '\') and month(break_date) = month(\'' . $date . '\') and repeat_by_year = 1';

				$result = $this->db->query($query)->result_array();

				$result2 = $this->db->query($query2)->result_array();
			}

			$list_shift_id = [];

			foreach ($result as $key => $value) {

				$list_shift_id[] = $value;
			}

			foreach ($result2 as $key => $value) {

				$list_shift_id[] = $value;
			}

			return $list_shift_id;
		}
	}

	/**

	 * get_staffid_ts_by_year

	 * @param  string $month_array

	 * @return array

	 */

	public function get_staffid_ts_by_year($month_array)
	{

		$string = 'select * from ' . db_prefix() . 'timesheets_timesheet where year(date_work)="' . $month_array . '"';

		return $this->db->query($string)->result_array();
	}

	/**

	 * get staffid ts by month

	 * @param  integer $month

	 * @return array

	 */

	public function get_staffid_ts_by_month($month)
	{

		$string = 'select * from ' . db_prefix() . 'timesheets_timesheet where month(date_work)="' . $month . '"';

		return $this->db->query($string)->result_array();
	}

	/**

	 * getstaff

	 * @param  string $id

	 * @param  array  $where

	 * @return array

	 */

	public function getstaff($id = '', $where = [])
	{

		$select_str = '*,concat(firstname," ",lastname) as full_name';



		if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {

			$select_str .= ',(select count(*) from ' . db_prefix() . 'notifications where touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (select count(*) from ' . db_prefix() . 'todos where finished=0 and staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
		}

		$this->db->select($select_str);

		$this->db->where($where);



		if (is_numeric($id)) {

			$this->db->where('staffid', $id);

			$staff = $this->db->get(db_prefix() . 'staff')->row();



			if ($staff) {

				$staff->permissions = $this->get_staff_permissions($id);
			}



			return $staff;
		}

		$this->db->join(db_prefix() . 'timesheets_timesheet', db_prefix() . 'timesheets_timesheet.id = (select ' . db_prefix() . 'timesheets_timesheet.id from ' . db_prefix() . 'timesheets_timesheet where ' . db_prefix() . 'timesheets_timesheet.staff_id = ' . db_prefix() . 'staff.staffid limit 1)', 'left');



		$this->db->order_by('firstname', 'ASC');



		return $this->db->get(db_prefix() . 'staff')->result_array();
	}

	/**

	 * fetch all timesheet

	 */

	function fetch_all_timesheet()
	{

		$this->db->select('*');

		$this->db->order_by('id');

		$this->db->from('timesheets_timesheet');

		$this->db->join('staff', 'timesheets_timesheet.staff_id = staff.staffid');

		return $this->db->get();
	}

	/**

	 * get timesheets option

	 * @param  string $option

	 * @return object or array

	 */

	public function get_timesheets_option($option)
	{

		if ($option != '') {

			$this->db->where('option_name', $option);

			return $this->db->get(db_prefix() . 'timesheets_option')->row();
		} else {

			return $this->db->get(db_prefix() . 'timesheets_option')->result_array();
		}
	}

	/**

	 * get data edit shift by staff

	 * @param  integer $staff

	 * @param  date $date

	 * @return array

	 */

	public function get_shift_work_staff_by_date($staff, $date = '')
	{
		if ($date == '') {
			$date = date('Y-m-d');
		}

		$cache_key = $staff . '|' . $date;
		if (array_key_exists($cache_key, $this->att_cache_shift_work)) {
			return $this->att_cache_shift_work[$cache_key];
		}

	   $this->load->model('departments_model');

		if (!array_key_exists($staff, $this->att_cache_staff_row)) {
			$this->att_cache_staff_row[$staff] = $this->staff_model->get($staff);
		}
		$nv = $this->att_cache_staff_row[$staff];

		if (!array_key_exists($staff, $this->att_cache_staff_depts)) {
			$this->att_cache_staff_depts[$staff] = $this->departments_model->get_staff_departments($staff, true);
		}
		$dpm = $this->att_cache_staff_depts[$staff];

		$sql_dpm = '';

		if ($dpm) {

			foreach ($dpm as $key => $value) {

				if ($sql_dpm == '') {

					$sql_dpm .= 'find_in_set(' . $value . ', department)';
				} else {

					$sql_dpm .= ' or find_in_set(' . $value . ', department)';
				}
			}
		}



		$sql_where = 'find_in_set(' . $staff . ', staff)';

		$this->db->where($sql_where);

		$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

		$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();



		if (!$shift) {

			if ($sql_dpm != '' && ($nv) && ($nv->role != 0 && $nv->role != '')) {

				$this->db->where('find_in_set(' . $nv->role . ', position)');

				$this->db->where('(' . $sql_dpm . ')');

				$this->db->where('(staff = "" or staff is null)');

				$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

				$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();

				if (!$shift) {

					$this->db->where('(position = "" or position is null)');

					$this->db->where('(' . $sql_dpm . ')');

					$this->db->where('(staff = "" or staff is null)');

					$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

					$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();



					if (!$shift) {

						$this->db->where('find_in_set(' . $nv->role . ', position)');

						$this->db->where('(department = "" or department is null)');

						$this->db->where('(staff = "" or staff is null)');

						$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

						$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();



						if (!$shift) {

							$this->db->where('(position = "" or position is null)');

							$this->db->where('(department = "" or department is null)');

							$this->db->where('(staff = "" or staff is null)');

							$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

							$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();
						}
					}
				}
			} elseif ($sql_dpm == '' && ($nv) && ($nv->role != 0 && $nv->role != '')) {



				$this->db->where('find_in_set(' . $nv->role . ', position)');

				$this->db->where('(department = "" or department is null)');

				$this->db->where('(staff = "" or staff is null)');

				$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

				$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();



				if (!$shift) {

					$this->db->where('(position = "" or position is null)');

					$this->db->where('(department = "" or department is null)');

					$this->db->where('(staff = "" or staff is null)');

					$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

					$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();
				}
			} elseif ($sql_dpm != '' && ($nv) && ($nv->role == 0 || $nv->role == '')) {



				$this->db->where('(position = "" or position is null)');

				$this->db->where('(' . $sql_dpm . ')');

				$this->db->where('(staff = "" or staff is null)');

				$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

				$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();

				if (!$shift) {

					$this->db->where('(position = "" or position is null)');

					$this->db->where('(department = "" or department is null)');

					$this->db->where('(staff = "" or staff is null)');

					$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

					$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();
				}
			} elseif ($sql_dpm == '' && ($nv) && ($nv->role == 0 || $nv->role == '')) {

				$this->db->where('(position = "" or position is null)');

				$this->db->where('(department = "" or department is null)');

				$this->db->where('(staff = "" or staff is null)');

				$this->db->where('("' . $date . '" >=  from_date and "' . $date . '" <= to_date)');

				$shift = $this->db->get(db_prefix() . 'work_shift')->result_array();
			}
		}

		$list_shift_id = [];

		$day_number = (int) date('d', strtotime($date));

		$Day = (int) date('N', strtotime($date));

		if ($shift) {

			foreach ($shift as $key => $value) {

				if ($value['type_shiftwork'] == 'by_absolute_time') {

					$this->db->where('(staff_id = ' . $staff . ' or staff_id = 0)');

					$this->db->where('date', $date);

					$this->db->where('work_shift_id', $value['id']);

					$shift_detail = $this->db->get(db_prefix() . 'work_shift_detail')->row();

					if ($shift_detail) {

						if (!in_array($shift_detail->shift_id, $list_shift_id)) {

							$list_shift_id[] = $shift_detail->shift_id;
						}
					}
				} else {

					// Weekday pattern repeats in a month — cache per staff/day-of-week/shift.
					$detail_key = 'nd|' . $staff . '|' . $Day . '|' . $value['id'];
					if (!array_key_exists($detail_key, $this->att_cache_shift_work)) {
						$this->db->where('(staff_id = ' . $staff . ' or staff_id = 0)');
						$this->db->where('number', $Day);
						$this->db->where('work_shift_id', $value['id']);
						$this->att_cache_shift_work[$detail_key] = $this->db->get(db_prefix() . 'work_shift_detail_number_day')->row();
					}
					$shift_detail = $this->att_cache_shift_work[$detail_key];

					if ($shift_detail) {

						if (!in_array($shift_detail->shift_id, $list_shift_id)) {

							$list_shift_id[] = $shift_detail->shift_id;
						}
					}
				}
			}
		}

		$this->att_cache_shift_work[$cache_key] = $list_shift_id;

		return $list_shift_id;
	}



	/**

	 * get shift work staff by date

	 * @param   int $staff

	 * @param   date $date

	 * @return  array

	 */

	public function get_shift_work_staff_by_date_2($staff, $date)
	{

		if ($staff != '' && $date != '') {

			$this->db->where('staffid', $staff);

			$this->db->select('role');

			$role_data = $this->db->get(db_prefix() . 'staff')->row();

			$role_id = 0;

			if ($role_data) {

				$role_id = $role_data->role;
			}

			$data_department = $this->db->query('select * from ' . db_prefix() . 'staff_departments where staffid = ' . $staff)->result_array();

			$list_department = '';

			foreach ($data_department as $key => $group) {

				$list_department .= ' find_in_set(' . $group['departmentid'] . ',department) or';
			}

			$day_name = date('d', strtotime($date));

			$day_number = $this->convert_day_to_number(strtolower($day_name));

			$query = 'select shift_id from ' . db_prefix() . 'work_shift_detail_number_day where (work_shift_id in (select id from ' . db_prefix() . 'work_shift where ((' . $list_department . ' position = ' . $role_id . ')  or (department = "" and position = "" and position = "")) and type_shiftwork = \'repeat_periodically\' ) or staff_id = ' . $staff . ') and number = ' . $day_number;

			$query2 = 'select shift_id from ' . db_prefix() . 'work_shift_detail where (work_shift_id in (select id from ' . db_prefix() . 'work_shift where ((' . $list_department . ' position = ' . $role_id . ') or (department = "" and position = "" and position = "")) and type_shiftwork = \'by_absolute_time\' ) or staff_id = ' . $staff . ') and date = \'' . $date . '\'';

			$result = $this->db->query($query)->result_array();

			$result2 = $this->db->query($query2)->result_array();

			$list_shift_id = [];

			foreach ($result as $key => $value) {

				if (!in_array($value['shift_id'], $list_shift_id)) {

					$list_shift_id[] = $value['shift_id'];
				}
			}

			foreach ($result2 as $key => $value) {

				if (!in_array($value['shift_id'], $list_shift_id)) {

					$list_shift_id[] = $value['shift_id'];
				}
			}

			return $list_shift_id;
		}
	}



	/**

	 * send mail

	 * @param  array $data

	 * @param  integer $staffid

	 */

	public function send_mail($data, $staffid = '')
	{

		if ($staffid == '') {

			$staff_id = $staffid;
		} else {

			$staff_id = get_staff_user_id();
		}

		$this->load->model('emails_model');

		if (!isset($data['status'])) {

			$data['status'] = '';
		}

		$get_staff_enter_charge_code = '';

		$mes = 'notify_send_request_approve_project';

		$staff_addedfrom = 0;

		$additional_data = $data['rel_type'];

		$object_type = $data['rel_type'];

		$type = '';

		$link = '';

		$rel_type = strtolower($data['rel_type']);

		switch ($rel_type) {

			case 'hr_planning':

				$hrplanning = $this->get_proposal_hrplanning($data['rel_id']);

				$staff_addedfrom = '';

				$additional_data = '';

				if ($hrplanning) {

					$staff_addedfrom = $hrplanning->requester;

					$additional_data = $hrplanning->proposal_name;
				}

				$type = _l('hr_planning');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve_hr_planning_proposal';

				$mes_approve = 'notify_send_approve_hr_planning_proposal';

				$mes_reject = 'notify_send_rejected_hr_planning_proposal';

				$link = 'timesheets/hr_planning?tab=hr_planning_proposal#' . $data['rel_id'];

				break;



			case 'candidate_evaluation':

				$this->load->model('recruitment/recruitment_model');



				$candidate = $this->recruitment_model->get_candidates($data['candidate']);

				$additional_data = '';

				if ($recruitment) {

					$staff_addedfrom = $candidate->cp_add_from;

					$additional_data = $candidate->candidate_name;
				}



				$type = _l('interview_result');



				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'recruitment/candidate/' . $data['candidate'] . '?evaluation=' . $data['rel_id'];

				break;



			case 'recruitment_campaign':

				$this->load->model('recruitment/recruitment_model');

				$staff_addedfrom = $data['addedfrom'];

				$recruitment = $this->recruitment_model->get_campaign_by_id($data['rel_id']);

				$additional_data = '';

				if ($recruitment) {

					$additional_data = $recruitment->campaign_name;
				}



				$type = _l('recruitment_campaign');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'recruitment/recruitment_campaign/' . $data['rel_id'];



				break;

			case 'leave':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('leave');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'maternity_leave':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('leave');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'private_work_without_pay':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('leave');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'sick_leave':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('leave');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'late':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('late');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'early':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('early');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'go_out':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('go_out');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'go_on_bussiness':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('go_on_bussiness');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			case 'additional_timesheets':

				$additional_timesheets = $this->get_additional_timesheets($data['rel_id']);

				$data['addedfrom'] = $additional_timesheets->creator;

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = '';

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve_additional_timesheets';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $data['rel_id'];

				break;

			case 'recruitment_proposal':

				$this->load->model('recruitment/recruitment_model');

				$additional_data = '';

				$staff_addedfrom = $data['addedfrom'];

				$proposal = $this->recruitment_model->get_rec_proposal($data['rel_id']);

				if ($proposal) {

					$additional_data = $proposal->proposal_name;
				}

				$type = _l('recruitment_proposal');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'recruitment/recruitment_proposal/' . $data['rel_id'];

				break;

			case 'quit_job':

				$staff_addedfrom = $data['addedfrom'];

				$additional_data = _l('quit_job');

				$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

				$mes = 'notify_send_request_approve';

				$mes_approve = 'notify_send_approve';

				$mes_reject = 'notify_send_rejected';

				$link = 'timesheets/requisition_detail/' . $data['rel_id'];

				break;

			default:

				if ($data['rel_type'] != '' && $data['rel_id'] != '') {

					$staff_addedfrom = $data['addedfrom'];

					$additional_data = _l('leave');

					$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

					$mes = 'notify_send_request_approve';

					$mes_approve = 'notify_send_approve';

					$mes_reject = 'notify_send_rejected';

					$link = 'timesheets/requisition_detail/' . $data['rel_id'];
				}

				break;
		}

		$check_approve_status = $this->check_approval_details($data['rel_id'], $data['rel_type'], $data['status']);

		if (isset($check_approve_status['staffid'])) {

			if (!in_array($staff_id, $check_approve_status['staffid'])) {

				foreach ($check_approve_status['staffid'] as $value) {

					$staff = $this->staff_model->get($value);

					$notified = add_notification([

						'description' => $mes,

						'touserid' => $staff->staffid,

						'link' => $link,

						'additional_data' => serialize([

							$additional_data,

						]),

					]);

					if ($notified) {

						pusher_trigger_notification([$staff->staffid]);
					}

					$email = $this->get_staff_email($value);

					if ($email != '') {

						$staff_request = '';

						$data_request_leave = $this->get_request_leave($data['rel_id']);

						if ($data_request_leave) {

							$staff_request = $data_request_leave->staff_id;
						}

						$data_send_mail['receiver'] = $email;

						$data_send_mail['approver'] = get_staff_full_name($value);

						$data_send_mail['staff_name'] = get_staff_full_name($staff_request);

						$data_send_mail['link'] = admin_url('timesheets/requisition_detail/' . $data['rel_id']);

						$template = mail_template('send_request_approval', 'timesheets', array_to_object($data_send_mail));

						$template->send();
					}
				}
			}
		}



		if (isset($data['approve'])) {

			if ($data['approve'] == 1) {

				$mes = $mes_approve;

				$mes_email = 'email_send_approve';
			} else {

				$mes = $mes_reject;

				$mes_email = 'email_send_rejected';
			}



			$staff = $this->staff_model->get($staff_addedfrom);

			$notified = add_notification([

				'description' => $mes,

				'touserid' => $staff->staffid,

				'link' => $link,

				'additional_data' => serialize([

					$additional_data,

				]),

			]);

			if ($notified) {

				pusher_trigger_notification([$staff->staffid]);
			}

			$this->emails_model->send_simple_email($staff->email, _l('approval_notification'), _l($mes_email, $type . ' <a href="' . admin_url($link) . '">' . $additional_data . '</a> ') . ' ' . _l('by_staff', get_staff_full_name($staff_id)));

			foreach ($list_approve_status as $key => $value) {

				$value['staffid'] = explode(', ', $value['staffid']);

				if ($value['approve'] == 1 && !in_array(get_staff_user_id(), $value['staffid'])) {

					foreach ($value['staffid'] as $staffid) {

						$staff = $this->staff_model->get($staffid);

						$notified = add_notification([

							'description' => $mes,

							'touserid' => $staff->staffid,

							'link' => $link,

							'additional_data' => serialize([

								$additional_data,

							]),

						]);

						if ($notified) {

							pusher_trigger_notification([$staff->staffid]);
						}



						$this->emails_model->send_simple_email($staff->email, _l('approval_notification'), _l($mes_email, $type . ' <a href="' . admin_url($link) . '">' . $additional_data . '</a>') . ' ' . _l('by_staff', get_staff_full_name($staff_id)));
					}
				}
			}



			$mes_approve_n = 'notify_send_approve_n';

			$mes_reject_n = 'notify_send_rejected_n';



			if ($data['approve'] == 1) {

				$mes_ar_n = $mes_approve_n;
			} else {

				$mes_ar_n = $mes_reject_n;
			}



			$this->db->select('*');

			$this->db->where('related', $data['rel_type']);

			$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();



			if ($approval_setting) {

				$notification_recipient = $approval_setting->notification_recipient;

				$arr_notification_recipient = explode(",", $notification_recipient);
			} else {

				$arr_notification_recipient = [];
			}



			if (count($arr_notification_recipient) > 0) {



				$mail_template = 'send-request-approve';



				if (!in_array($staff_id, $arr_notification_recipient)) {

					foreach ($arr_notification_recipient as $value1) {



						$notified = add_notification([

							'description' => $mes_ar_n,

							'touserid' => $value1,

							'link' => $link,

							'additional_data' => serialize([

								$additional_data,

							]),

						]);



						if ($notified) {

							pusher_trigger_notification([$value1]);
						}



						$email = $this->get_staff_email($value1);

						if ($email != '') {

							$this->emails_model->send_simple_email($email, _l('approval_notification'), _l($mes_email, $type . ' <a href="' . admin_url($link) . '">' . $additional_data . '</a>') . ' ' . _l('by_staff', get_staff_full_name($staff_id)));
						}
					}
				}
			}
		}
	}

	/**

	 * send notifi handover recipients

	 * @param  array $data

	 * @return array

	 */

	public function send_notifi_handover_recipients($data)
	{

		$this->load->model('emails_model');

		if (!isset($data['status'])) {

			$data['status'] = '';
		}



		$get_staff_enter_charge_code = '';

		$mes = 'notify_send_request_approve_project';

		$staff_addedfrom = 0;

		$additional_data = $data['rel_type'];

		$object_type = $data['rel_type'];



		$staff_addedfrom = $data['addedfrom'];

		$additional_data = _l('leave');

		$list_approve_status = $this->get_list_approval_details($data['rel_id'], $data['rel_type']);

		$mes = 'chosen_you_to_handover_recipients_requisition';



		$mes_approve = 'notify_send_approve';

		$mes_reject = 'notify_send_rejected';

		$link = 'timesheets/requisition_detail/' . $data['rel_id'];



		$this->db->select('*');

		$this->db->where('id', $data['rel_id']);

		$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

		if ($requisition_leave) {

			$staff_handover_recipients = $requisition_leave->handover_recipients;

			$additional_data = $requisition_leave->subject;
		} else {

			$staff_handover_recipients = 'false';
		}



		if (is_numeric($staff_handover_recipients)) {



			$mail_template = 'send-request-approve';



			if (get_staff_user_id() != $staff_handover_recipients) {

				$notified = add_notification([

					'description' => $mes,

					'touserid' => $staff_handover_recipients,

					'link' => $link,

					'additional_data' => serialize([

						$additional_data,

					]),

				]);



				if ($notified) {

					pusher_trigger_notification([$staff_handover_recipients]);
				}
			}
		}
	}

	/**

	 * send notification recipient

	 * @param  array $data

	 */

	public function send_notification_recipient($data)
	{

		$this->load->model('emails_model');

		if (!isset($data['status'])) {

			$data['status'] = '';
		}



		$mes = 'send_request';

		$link = 'timesheets/requisition_detail/' . $data['rel_id'];



		$this->db->select('*');

		$this->db->where('id', $data['rel_id']);

		$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

		if ($requisition_leave) {

			$additional_data = $requisition_leave->subject;
		} else {

			$additional_data = '';
		}



		if (isset($data['staff_approve']) && isset($data['approve']) && is_numeric($data['approve'])) {

			if ($data['approve'] == 1) {

				$mes = $additional_data . ' ' . _l('ts_approved_by') . ' ' . get_staff_full_name($data['staff_approve']);
			} else {

				$mes = $additional_data . ' ' . _l('ts_rejected_by') . ' ' . get_staff_full_name($data['staff_approve']);
			}
		}



		$this->db->select('*');

		$this->db->where('related', "leave");

		$approval_setting = $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();



		if ($approval_setting) {

			$notification_recipient = $approval_setting->notification_recipient;

			$arr_notification_recipient = explode(",", $notification_recipient);
		} else {

			$arr_notification_recipient = [];
		}



		if (count($arr_notification_recipient) > 0) {



			$mail_template = 'send-request-approve';

			if (!in_array(get_staff_user_id(), $arr_notification_recipient)) {

				foreach ($arr_notification_recipient as $value) {



					$notified = add_notification([

						'description'     => $mes,

						'touserid'        => $value,

						'link'            => $link,

						'additional_data' => serialize([

							$additional_data,

						]),

					]);



					if ($notified) {

						pusher_trigger_notification([$value]);
					}
				}
			}
		}
	}

	/**

	 * get date time

	 * @param  integer $work_shift

	 * @return array

	 */

	public function get_date_time($work_shift)
	{

		$day = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'sunday', 'saturday_odd', 'saturday_even'];

		$date_return = [];

		foreach ($day as $value) {

			$date_return['lunch_break'][$value] = (($work_shift[3][$value][0] - $work_shift[2][$value][0]) * 60) + ($work_shift[3][$value][1] - $work_shift[2][$value][1]);

			$date_return['work_time'][$value] = (($work_shift[1][$value][0] - $work_shift[0][$value][0]) * 60) + ($work_shift[1][$value][1] - $work_shift[0][$value][1]);

			$date_return['late_for_work'][$value] = $work_shift[0][$value][0] . ':' . $work_shift[0][$value][1] . ':00';

			$date_return['start_lunch_break_time'][$value] = $work_shift[2][$value][0] . ':' . $work_shift[2][$value][1] . ':00';

			$date_return['come_home_early'][$value] = $work_shift[1][$value][0] . ':' . $work_shift[1][$value][1] . ':00';

			$date_return['late_latency_allowed'][$value] = $work_shift[4][$value][0] . ':' . $work_shift[4][$value][1] . ':00';

			$date_return['start_afternoon_shift'][$value] = $work_shift[3][$value][0] . ':' . $work_shift[3][$value][1] . ':00';
		}

		return $date_return;
	}

	/**

	 * gets the overtime setting.

	 *

	 * @param      string  $id     the identifier

	 *

	 * @return     <type>  the overtime setting.

	 */

	public function get_overtime_setting($id = '')
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_overtime_setting')->row();
		}



		return $this->db->get(db_prefix() . 'timesheets_overtime_setting')->result_array();
	}
	public function add_overtime_setting($data)
	{

		$this->db->insert(db_prefix() . 'timesheets_overtime_setting', $data);

		$insert_id = $this->db->insert_id();

		return $insert_id;
	}

	/**

	 * update overtime setting

	 * @param  array $data

	 * @param  integer $id

	 * @return bool

	 */

	public function update_overtime_setting($data, $id)
	{

		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'timesheets_overtime_setting', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete overtime setting

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_overtime_setting($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_overtime_setting');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * get additional timesheets

	 * @param  integer $id

	 * @return array

	 */

	public function get_additional_timesheets($id = '')
	{

		if (is_numeric($id)) {

			$this->db->select('*');

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		}



		if (!is_admin() && !has_permission('additional_timesheets_management', '', 'view')) {

			$this->db->where(' ' . get_staff_user_id() . ' in (select staffid from ' . db_prefix() . 'timesheets_approval_details where rel_type = "additional_timesheets" and rel_id = ' . db_prefix() . 'timesheets_additional_timesheet.id)');
		}

		$this->db->order_by('id', 'desc');

		return $this->db->get(db_prefix() . 'timesheets_additional_timesheet')->result_array();
	}

	/**
	 * Save admin rejection reason on a regularization request.
	 */
	public function save_additional_timesheet_rejection($id, $comment)
	{
		$id = (int) $id;
		$comment = trim((string) $comment);
		if ($id <= 0 || $comment === '') {
			return false;
		}

		$this->ensure_additional_timesheet_rejection_column();
		$this->ensure_additional_timesheet_hod_forward_columns();

		$tbl = db_prefix() . 'timesheets_additional_timesheet';
		$this->db->where('id', $id);
		return $this->db->update($tbl, [
			'rejection_comment' => $comment,
			'status' => 2,
		]);
	}

	/**
	 * HOD forwards a pending regularization to HR (does not approve).
	 */
	public function forward_additional_timesheet_to_hr($id, $hod_staff_id)
	{
		$id = (int) $id;
		$hod_staff_id = (int) $hod_staff_id;
		if ($id <= 0 || $hod_staff_id <= 0) {
			return false;
		}

		$this->ensure_additional_timesheet_hod_forward_columns();

		$row = $this->db->where('id', $id)->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		if (!$row || (int) $row->status !== 0) {
			return false;
		}
		if ((int) ($row->hod_forwarded ?? 0) === 1) {
			return true; // already forwarded
		}

		$this->db->where('id', $id);
		$ok = $this->db->update(db_prefix() . 'timesheets_additional_timesheet', [
			'hod_forwarded' => 1,
			'hod_forwarded_by' => $hod_staff_id,
			'hod_forwarded_at' => date('Y-m-d H:i:s'),
		]);

		if ($ok) {
			$this->send_regularisation_forwarded_to_hr_email($id);
		}

		return (bool) $ok;
	}

	/**
	 * Ensure hod_forwarded columns exist on additional timesheet table.
	 */
	public function ensure_additional_timesheet_hod_forward_columns()
	{
		static $checked = false;
		if ($checked) {
			return;
		}
		$checked = true;

		$opt_key = 'ts_addl_has_hod_forward';
		if ((string) get_option($opt_key) === '1') {
			return;
		}

		$tbl = db_prefix() . 'timesheets_additional_timesheet';
		if (!$this->db->field_exists('hod_forwarded', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded` TINYINT(1) NOT NULL DEFAULT 0');
		}
		if (!$this->db->field_exists('hod_forwarded_by', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded_by` INT NULL');
		}
		if (!$this->db->field_exists('hod_forwarded_at', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded_at` DATETIME NULL');
		}
		update_option($opt_key, '1');
	}

	/**
	 * Local/older DBs may lack rejection_comment — add it once when needed.
	 */
	public function ensure_additional_timesheet_rejection_column()
	{
		static $checked = false;
		if ($checked) {
			return;
		}
		$checked = true;

		// Persist across requests — field_exists hits information_schema every page otherwise.
		$opt_key = 'ts_addl_has_rejection_comment';
		if ((string) get_option($opt_key) === '1') {
			return;
		}

		$tbl = db_prefix() . 'timesheets_additional_timesheet';
		if (!$this->db->field_exists('rejection_comment', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `rejection_comment` TEXT NULL');
		}
		update_option($opt_key, '1');
	}

	/**
	 * Track leave deducted when regularization is approved.
	 */
	public function ensure_additional_timesheet_leave_columns()
	{
		static $checked = false;
		if ($checked) {
			return;
		}
		$checked = true;

		$opt_key = 'ts_addl_has_leave_deduct_cols';
		if ((string) get_option($opt_key) === '1') {
			return;
		}

		$tbl = db_prefix() . 'timesheets_additional_timesheet';
		if (!$this->db->field_exists('leave_days_deducted', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `leave_days_deducted` DECIMAL(5,2) NULL DEFAULT NULL');
		}
		if (!$this->db->field_exists('leave_requisition_id', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `leave_requisition_id` INT NULL DEFAULT NULL');
		}
		if (!$this->db->field_exists('leave_type_deducted', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `leave_type_deducted` VARCHAR(50) NULL DEFAULT NULL');
		}
		update_option($opt_key, '1');
	}

	/**
	 * On regularization approve: adjust leave balance for that day based on corrected hours.
	 * Example: Absent already took 1 leave → regularize to half-day hours → leave becomes 0.5
	 * (restore 0.5). Present (>=8.0h) → leave becomes 0.
	 *
	 * @param int   $request_id
	 * @param float $after_hours approved regularization work hours
	 * @return array
	 */
	public function adjust_leave_balance_for_approved_regularisation($request_id, $after_hours = null)
	{
		$this->load->helper('timesheets/timesheets');
		$this->ensure_additional_timesheet_leave_columns();

		$request_id = (int) $request_id;
		$result = [
			'success' => false,
			'before_days' => 0.0,
			'after_days' => 0.0,
			'delta' => 0.0,
			'leave_id' => 0,
			'message' => '',
		];
		if ($request_id <= 0) {
			$result['message'] = 'Invalid regularization request.';
			return $result;
		}

		$row = $this->db->where('id', $request_id)->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		if (!$row) {
			$result['message'] = 'Regularization request not found.';
			return $result;
		}

		$staff_id = (int) $row->creator;
		$day = date('Y-m-d', strtotime($row->additional_day));
		if ($staff_id <= 0 || !$day || $day === '1970-01-01') {
			$result['message'] = 'Invalid staff/date for leave adjustment.';
			return $result;
		}

		// Original attendance before overwrite.
		$ts = $this->db->where('staff_id', $staff_id)
			->where('date_work', $day)
			->order_by('id', 'DESC')
			->get(db_prefix() . 'timesheets_timesheet')
			->row();
		$ts_type = strtoupper((string) ($ts->type ?? ''));
		$before_hours = is_numeric($ts->value ?? null) ? (float) $ts->value : 0.0;
		$before_days = timesheets_leave_days_for_attendance_status('', $before_hours, $ts_type);

		if ($after_hours === null || $after_hours === '') {
			$after_hours = is_numeric($row->timekeeping_value ?? null) ? (float) $row->timekeeping_value : 0.0;
		}
		$after_hours = (float) $after_hours;
		$after_code = timesheets_attendance_code_from_hours($after_hours);
		$after_days = timesheets_leave_days_for_attendance_status('', $after_hours, $after_code);

		// Prefer actual leave already booked for this day (manager-marked / prior leave).
		$day_leaves = $this->db
			->where('staff_id', $staff_id)
			->where('status', 1)
			->where('start_time >=', $day . ' 00:00:00')
			->where('start_time <=', $day . ' 23:59:59')
			->order_by('id', 'DESC')
			->get(db_prefix() . 'timesheets_requisition_leave')
			->result_array();

		$existing_leave = null;
		$booked_days = 0.0;
		foreach ($day_leaves as $leave_row) {
			$subj = strtolower((string) ($leave_row['subject'] ?? ''));
			$type = strtolower((string) ($leave_row['type_of_leave'] ?? ''));
			$is_attendance_linked = (
				strpos($subj, 'regularization') !== false
				|| strpos($subj, 'leave marked by manager') !== false
				|| strpos($subj, 'half day marked by manager') !== false
				|| strpos($subj, 'attendance leave') !== false
				|| in_array($type, ['earned-leave', 'loss-of-pay', '8', 'leave'], true)
			);
			if (!$is_attendance_linked) {
				continue;
			}
			$booked_days += (float) ($leave_row['number_of_leaving_day'] ?? 0);
			if ($existing_leave === null) {
				$existing_leave = $leave_row;
			}
		}
		if ($booked_days > 0) {
			$before_days = round($booked_days, 2);
		}

		$delta = round($after_days - $before_days, 2);
		$result['before_days'] = $before_days;
		$result['after_days'] = $after_days;
		$result['delta'] = $delta;

		if (abs($delta) < 0.001 && $existing_leave) {
			$this->db->where('id', $request_id)->update(db_prefix() . 'timesheets_additional_timesheet', [
				'leave_days_deducted' => $after_days,
				'leave_requisition_id' => (int) $existing_leave['id'],
				'leave_type_deducted' => (string) ($existing_leave['type_of_leave'] ?? ''),
			]);
			$result['success'] = true;
			$result['leave_id'] = (int) $existing_leave['id'];
			$result['message'] = 'Leave balance already matches corrected day (' . $after_days . ').';
			return $result;
		}

		$year = (int) date('Y', strtotime($day));
		$month = (int) date('m', strtotime($day));
		$el = $this->get_synced_leave_balance($staff_id, 'earned-leave', $year, $month);
		$el_balance = (float) ($el['balance'] ?? 0);
		$leave_type = 'earned-leave';
		if ($after_days > 0 && ($el_balance + 0.001 < $after_days) && $before_days <= 0) {
			$leave_type = 'loss-of-pay';
		} elseif ($existing_leave && !empty($existing_leave['type_of_leave'])) {
			$leave_type = (string) $existing_leave['type_of_leave'];
			if ($leave_type === '8') {
				$leave_type = 'earned-leave';
			}
			if (!in_array($leave_type, ['earned-leave', 'loss-of-pay'], true)) {
				$leave_type = ($el_balance + $before_days + 0.001 >= $after_days) ? 'earned-leave' : 'loss-of-pay';
			}
		}

		$subject = 'Attendance leave adjusted by regularization';
		if ($after_days <= 0) {
			$subject = 'Attendance leave restored by regularization';
		} elseif ($after_days == 0.5) {
			$subject = 'Half day leave adjusted by regularization';
		} elseif ($after_days >= 1) {
			$subject = 'Absent leave adjusted by regularization';
		}

		$reason = trim((string) ($row->reason ?? ''));
		$reason = $reason !== ''
			? ('Regularization adjustment: ' . $reason)
			: ('Leave adjusted for ' . $day . ' after regularization (' . $before_days . ' → ' . $after_days . ')');

		$leave_id = 0;
		if ($existing_leave) {
			$leave_id = (int) $existing_leave['id'];
			if ($after_days <= 0) {
				$this->db->where('id', $leave_id)->update(db_prefix() . 'timesheets_requisition_leave', [
					'number_of_leaving_day' => 0,
					'number_of_days' => 0,
					'status' => 2,
					'subject' => $subject,
					'reason' => $reason,
				]);
				foreach ($day_leaves as $leave_row) {
					$lid = (int) $leave_row['id'];
					if ($lid === $leave_id) {
						continue;
					}
					$subj = strtolower((string) ($leave_row['subject'] ?? ''));
					if (
						strpos($subj, 'regularization') !== false
						|| strpos($subj, 'leave marked by manager') !== false
						|| strpos($subj, 'half day marked by manager') !== false
						|| strpos($subj, 'attendance leave') !== false
					) {
						$this->db->where('id', $lid)->update(db_prefix() . 'timesheets_requisition_leave', [
							'number_of_leaving_day' => 0,
							'number_of_days' => 0,
							'status' => 2,
							'subject' => $subject,
							'reason' => $reason,
						]);
					}
				}
			} else {
				$this->db->where('id', $leave_id)->update(db_prefix() . 'timesheets_requisition_leave', [
					'number_of_leaving_day' => $after_days,
					'number_of_days' => $after_days,
					'type_of_leave' => $leave_type,
					'type_of_leave_text' => $leave_type === 'earned-leave' ? 'Earned Leave' : 'Loss Of Pay',
					'subject' => $subject,
					'reason' => $reason,
					'status' => 1,
				]);
				foreach ($day_leaves as $leave_row) {
					$lid = (int) $leave_row['id'];
					if ($lid === $leave_id) {
						continue;
					}
					$subj = strtolower((string) ($leave_row['subject'] ?? ''));
					if (
						strpos($subj, 'regularization') !== false
						|| strpos($subj, 'leave marked by manager') !== false
						|| strpos($subj, 'half day marked by manager') !== false
						|| strpos($subj, 'attendance leave') !== false
					) {
						$this->db->where('id', $lid)->update(db_prefix() . 'timesheets_requisition_leave', [
							'number_of_leaving_day' => 0,
							'number_of_days' => 0,
							'status' => 2,
							'reason' => 'Merged into leave #' . $leave_id . ' after regularization',
						]);
					}
				}
			}
		} elseif ($after_days > 0) {
			$this->db->insert(db_prefix() . 'timesheets_requisition_leave', [
				'staff_id' => $staff_id,
				'subject' => $subject,
				'start_time' => $day . ' 00:00:00',
				'end_time' => $day . ' 23:59:59',
				'reason' => $reason,
				'type_of_leave' => $leave_type,
				'type_of_leave_text' => $leave_type === 'earned-leave' ? 'Earned Leave' : 'Loss Of Pay',
				'number_of_leaving_day' => $after_days,
				'number_of_days' => $after_days,
				'status' => 1,
				'datecreated' => date('Y-m-d H:i:s'),
			]);
			$leave_id = (int) $this->db->insert_id();
		}

		$this->db->where('id', $request_id)->update(db_prefix() . 'timesheets_additional_timesheet', [
			'leave_days_deducted' => $after_days,
			'leave_requisition_id' => $leave_id > 0 ? $leave_id : null,
			'leave_type_deducted' => $leave_id > 0 ? $leave_type : null,
		]);

		$result['success'] = true;
		$result['leave_id'] = $leave_id;
		if ($delta < 0) {
			$result['message'] = 'Leave restored: ' . abs($delta) . ' day(s) returned to balance (' . $before_days . ' → ' . $after_days . ').';
		} elseif ($delta > 0) {
			$result['message'] = 'Leave adjusted: +' . $delta . ' day(s) (' . $before_days . ' → ' . $after_days . ').';
		} else {
			$result['message'] = 'No leave balance change for this regularization.';
		}
		log_activity('Regularization leave adjust #' . $request_id . ': ' . $result['message']);
		return $result;
	}

	/**
	 * @deprecated Use adjust_leave_balance_for_approved_regularisation()
	 */
	public function deduct_leave_for_approved_regularisation($request_id)
	{
		return $this->adjust_leave_balance_for_approved_regularisation($request_id, null);
	}

	/**
	 * Ensure hod_forwarded columns exist on leave requisition table.
	 */
	public function ensure_leave_hod_forward_columns()
	{
		static $checked = false;
		if ($checked) {
			return;
		}
		$checked = true;

		$opt_key = 'ts_leave_has_hod_forward';
		if ((string) get_option($opt_key) === '1') {
			return;
		}

		$tbl = db_prefix() . 'timesheets_requisition_leave';
		if (!$this->db->field_exists('hod_forwarded', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded` TINYINT(1) NOT NULL DEFAULT 0');
		}
		if (!$this->db->field_exists('hod_forwarded_by', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded_by` INT NULL');
		}
		if (!$this->db->field_exists('hod_forwarded_at', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forwarded_at` DATETIME NULL');
		}
		if (!$this->db->field_exists('hod_forward_comment', $tbl)) {
			$this->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `hod_forward_comment` TEXT NULL');
		}
		update_option($opt_key, '1');
	}

	/**
	 * HOD / Manager forwards a pending leave to Super HR (does not approve).
	 */
	public function forward_leave_to_super_hr($leave_id, $hod_staff_id, $comment = '')
	{
		$leave_id = (int) $leave_id;
		$hod_staff_id = (int) $hod_staff_id;
		if ($leave_id <= 0 || $hod_staff_id <= 0) {
			return false;
		}

		$this->ensure_leave_hod_forward_columns();

		$row = $this->db->where('id', $leave_id)->get(db_prefix() . 'timesheets_requisition_leave')->row();
		if (!$row || (int) $row->status !== 0) {
			return false;
		}
		if ((int) ($row->hod_forwarded ?? 0) === 1) {
			return true;
		}

		$this->db->where('id', $leave_id);
		$ok = $this->db->update(db_prefix() . 'timesheets_requisition_leave', [
			'hod_forwarded' => 1,
			'hod_forwarded_by' => $hod_staff_id,
			'hod_forwarded_at' => date('Y-m-d H:i:s'),
			'hod_forward_comment' => trim((string) $comment),
		]);

		if ($ok) {
			$this->send_leave_forwarded_to_super_hr_email($leave_id);
		}

		return (bool) $ok;
	}

	/**
	 * Notify HR / Super HR that HOD forwarded a leave for final approval.
	 * To: HR / Super HR / final approvers | CC: HOD + employee.
	 */
	public function send_leave_forwarded_to_super_hr_email($leave_id)
	{
		$leave_id = (int) $leave_id;
		$row = $this->db->where('id', $leave_id)->get(db_prefix() . 'timesheets_requisition_leave')->row();
		if (!$row) {
			return false;
		}

		$this->load->helper('timesheets/timesheets');
		$applicant_id = (int) $row->staff_id;
		if (timesheets_staff_is_hr_or_accounts($applicant_id)) {
			$recipient_ids = timesheets_super_admin_leave_approver_ids();
		} else {
			$recipient_ids = timesheets_super_hr_staff_ids();
			if (empty($recipient_ids)) {
				$recipient_ids = timesheets_leave_approver_ids_for_applicant($applicant_id);
			}
			// Always include final leave approvers (HR / Admin / Sarabjeet) as To.
			$recipient_ids = array_values(array_unique(array_merge(
				$recipient_ids,
				timesheets_leave_approver_ids_for_applicant($applicant_id)
			)));
		}

		$to = [];
		foreach ($recipient_ids as $sid) {
			$staff = $this->db->select('email')->where('staffid', (int) $sid)->where('active', 1)->get(db_prefix() . 'staff')->row();
			if ($staff && !empty($staff->email) && filter_var($staff->email, FILTER_VALIDATE_EMAIL)) {
				$to[] = $staff->email;
			}
		}
		$to = array_values(array_unique($to));
		if (empty($to)) {
			$to = ['hr@tech2globe.com'];
		}
		// Ensure HR mailbox is always on the To line.
		if (!in_array('hr@tech2globe.com', $to, true)) {
			$to[] = 'hr@tech2globe.com';
		}

		$employee_name = get_staff_full_name($applicant_id);
		$employee_email = function_exists('get_staff_email_id') ? get_staff_email_id($applicant_id) : '';
		if ($employee_email === '' || $employee_email === null) {
			$emp_row = $this->db->select('email')->where('staffid', $applicant_id)->get(db_prefix() . 'staff')->row();
			$employee_email = $emp_row->email ?? '';
		}

		$hod_id = (int) ($row->hod_forwarded_by ?? 0);
		$hod_name = $hod_id > 0 ? get_staff_full_name($hod_id) : 'HOD';
		$hod_email = '';
		if ($hod_id > 0) {
			$hod_email = function_exists('get_staff_email_id') ? get_staff_email_id($hod_id) : '';
			if ($hod_email === '' || $hod_email === null) {
				$hod_row = $this->db->select('email')->where('staffid', $hod_id)->get(db_prefix() . 'staff')->row();
				$hod_email = $hod_row->email ?? '';
			}
		}

		$cc = [];
		if (!empty($hod_email) && filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
			$cc[] = $hod_email;
		}
		if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
			$cc[] = $employee_email;
		}
		// Keep Sarabjeet in CC if not already a To recipient.
		if (!in_array('sarabjeet@tech2globe.net', $to, true)) {
			$cc[] = 'sarabjeet@tech2globe.net';
		}
		$cc = array_values(array_unique(array_diff(
			array_filter($cc, function ($e) {
				return filter_var($e, FILTER_VALIDATE_EMAIL);
			}),
			$to
		)));

		$from = !empty($row->start_time) ? _d(date('Y-m-d', strtotime($row->start_time))) : '-';
		$to_date = !empty($row->end_time) ? _d(date('Y-m-d', strtotime($row->end_time))) : $from;
		$days = (string) ($row->number_of_leaving_day ?? $row->number_of_days ?? '');
		$link = admin_url('timesheets/requisition_detail/' . $leave_id);
		$fwd_comment = trim((string) ($row->hod_forward_comment ?? ''));

		$subject = 'Leave Forwarded to HR / Super HR - ' . $employee_name;
		$message = '<p>Dear HR / Super HR,</p>';
		$message .= '<p><b>' . html_escape($hod_name) . '</b> has forwarded a leave application for final approval.</p>';
		$message .= '<p><b>Employee:</b> ' . html_escape($employee_name) . '</p>';
		$message .= '<p><b>Subject:</b> ' . html_escape((string) ($row->subject ?? '')) . '</p>';
		$message .= '<p><b>From:</b> ' . html_escape($from) . '<br><b>To:</b> ' . html_escape($to_date) . '</p>';
		if ($days !== '') {
			$message .= '<p><b>Days:</b> ' . html_escape($days) . '</p>';
		}
		if ($fwd_comment !== '') {
			$message .= '<p><b>Manager comment:</b> ' . nl2br(html_escape($fwd_comment)) . '</p>';
		}
		$message .= '<p><b>Approve / Reject:</b> <a href="' . html_escape($link) . '">' . html_escape($link) . '</a></p>';
		$message .= '<p><em>This email is also copied to the HOD and the employee.</em></p>';
		$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

		foreach ($recipient_ids as $sid) {
			add_notification([
				'description' => 'leave_forwarded_to_super_hr',
				'touserid' => (int) $sid,
				'fromuserid' => $hod_id ?: 0,
				'link' => 'timesheets/requisition_detail/' . $leave_id,
				'additional_data' => serialize([$employee_name, $hod_name]),
			]);
		}
		if (!empty($recipient_ids)) {
			pusher_trigger_notification($recipient_ids);
		}
		if ($applicant_id > 0) {
			add_notification([
				'description' => 'Your leave was forwarded to HR / Super HR',
				'touserid' => $applicant_id,
				'fromuserid' => $hod_id ?: get_staff_user_id(),
				'link' => 'timesheets/requisition_detail/' . $leave_id,
				'additional_data' => serialize([$hod_name]),
			]);
			pusher_trigger_notification([$applicant_id]);
		}

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}

		$ok = false;
		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->initialize();
			$this->email->set_mailtype('html');
			$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
			$this->email->to($to);
			if (!empty($cc)) {
				$this->email->cc($cc);
			}
			$this->email->subject($subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(false);
			if ($ok) {
				log_activity('Leave forward emailed: leave #' . $leave_id . ' To=' . implode(',', $to) . ' CC=' . implode(',', $cc));
			} else {
				log_activity('Leave forward email failed: leave #' . $leave_id);
			}
		} catch (Throwable $e) {
			log_activity('Leave forward email failed #' . $leave_id . ': ' . $e->getMessage());
		}

		return $ok;
	}

	/**
	 * Notify HR that HOD forwarded a regularization for final approval.
	 * Also informs the employee and keeps HOD in CC.
	 */
	public function send_regularisation_forwarded_to_hr_email($request_id)
	{
		$request_id = (int) $request_id;
		$row = $this->db->where('id', $request_id)->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		if (!$row) {
			return false;
		}

		$creator_id = (int) $row->creator;
		$employee_name = get_staff_full_name($creator_id);
		$employee_email = function_exists('get_staff_email_id') ? get_staff_email_id($creator_id) : '';
		$hod_id = (int) ($row->hod_forwarded_by ?? 0);
		$hod_name = $hod_id > 0 ? get_staff_full_name($hod_id) : 'HOD';
		$hod_email = $hod_id > 0 && function_exists('get_staff_email_id') ? get_staff_email_id($hod_id) : '';
		$day = !empty($row->additional_day) ? _d($row->additional_day) : (string) $row->additional_day;
		$link = admin_url('timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id);

		$to = ['hr@tech2globe.com'];
		$cc = ['sarabjeet@tech2globe.net'];
		if (!empty($hod_email) && filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
			$cc[] = $hod_email;
		}
		if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
			$cc[] = $employee_email;
		}
		$cc = array_values(array_unique(array_diff(
			array_filter($cc, function ($e) {
				return filter_var($e, FILTER_VALIDATE_EMAIL);
			}),
			$to
		)));

		$subject = 'Regularization Forwarded to Super HR - ' . $employee_name . ' - ' . $day;
		$message = '<p>Dear Super HR,</p>';
		$message .= '<p><b>' . html_escape($hod_name) . '</b> has forwarded an attendance regularization request for final approval.</p>';
		$message .= '<p><b>Employee:</b> ' . html_escape($employee_name) . '</p>';
		$message .= '<p><b>Date:</b> ' . html_escape($day) . '</p>';
		$message .= '<p><b>Requested Time In:</b> ' . html_escape((string) ($row->time_in ?? '')) . '</p>';
		$message .= '<p><b>Requested Time Out:</b> ' . html_escape((string) ($row->time_out ?? '')) . '</p>';
		$message .= '<p><b>Approve / Reject:</b> <a href="' . html_escape($link) . '">' . html_escape($link) . '</a></p>';
		$message .= '<p><em>This email is also copied to the HOD and the employee.</em></p>';

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@tech2globe.com';
		}

		$ok = false;
		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
			$this->email->to($to);
			if (!empty($cc)) {
				$this->email->cc($cc);
			}
			$this->email->subject($subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(true);
		} catch (Throwable $e) {
			log_activity('Regularization forward email failed #' . $request_id . ': ' . $e->getMessage());
		}

		// Dedicated note to employee: HOD has forwarded your request.
		if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
			try {
				$emp_subject = 'Your regularization was forwarded by HOD - ' . $day;
				$emp_msg = '<p>Dear ' . html_escape($employee_name) . ',</p>';
				$emp_msg .= '<p>Your attendance regularization for <b>' . html_escape($day) . '</b> has been <b>forwarded</b> by <b>' . html_escape($hod_name) . '</b> to Super HR for final approval.</p>';
				$emp_msg .= '<p><b>Time In:</b> ' . html_escape((string) ($row->time_in ?? '')) . '<br>';
				$emp_msg .= '<b>Time Out:</b> ' . html_escape((string) ($row->time_out ?? '')) . '</p>';
				$emp_msg .= '<p>You will receive another email once Super HR / HR approves or rejects it.</p>';
				$emp_msg .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

				$this->email->clear(true);
				$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
				$this->email->to($employee_email);
				if (!empty($hod_email) && filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
					$this->email->cc($hod_email);
				}
				$this->email->subject($emp_subject);
				$this->email->message($emp_msg);
				$this->email->send(true);
			} catch (Throwable $e) {
				log_activity('Regularization forward employee email failed #' . $request_id . ': ' . $e->getMessage());
			}
		}

		if ($creator_id > 0) {
			add_notification([
				'description' => 'Your regularization was forwarded to Super HR',
				'touserid' => $creator_id,
				'fromuserid' => $hod_id ?: get_staff_user_id(),
				'link' => 'timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id,
			]);
			pusher_trigger_notification([$creator_id]);
		}

		return $ok;
	}

	/**
	 * Email employee + HOD when regularization is finally approved or rejected.
	 *
	 * @param int  $request_id
	 * @param bool $approved
	 * @param string $comment
	 * @param int $decided_by
	 * @return bool
	 */
	public function notify_regularisation_decision($request_id, $approved, $comment = '', $decided_by = 0)
	{
		$request_id = (int) $request_id;
		if ($request_id <= 0) {
			return false;
		}

		$row = $this->db->where('id', $request_id)->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		if (!$row) {
			return false;
		}

		$creator_id = (int) $row->creator;
		$employee_name = get_staff_full_name($creator_id);
		$employee_email = function_exists('get_staff_email_id') ? get_staff_email_id($creator_id) : '';

		$hod_id = 0;
		$hod_email = '';
		$staff = $this->db->select('team_manage')->where('staffid', $creator_id)->get(db_prefix() . 'staff')->row();
		if ($staff && (int) $staff->team_manage > 0) {
			$hod_id = (int) $staff->team_manage;
			$hod_email = function_exists('get_staff_email_id') ? get_staff_email_id($hod_id) : '';
		}
		if ($hod_id <= 0 && !empty($row->hod_forwarded_by)) {
			$hod_id = (int) $row->hod_forwarded_by;
			$hod_email = function_exists('get_staff_email_id') ? get_staff_email_id($hod_id) : '';
		}

		$decided_by = (int) ($decided_by ?: get_staff_user_id());
		$decider_name = $decided_by > 0 ? get_staff_full_name($decided_by) : 'HR / Admin';
		$day = !empty($row->additional_day) ? _d($row->additional_day) : (string) $row->additional_day;
		$status_label = $approved ? 'approved' : 'rejected';
		$comment = trim((string) $comment);
		$link = admin_url('timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id);

		// In-app notifications
		$notify_ids = [];
		if ($creator_id > 0) {
			add_notification([
				'description' => $approved
					? 'notify_send_creator_additional_timesheet_approved'
					: 'notify_send_creator_additional_timesheet_rejected',
				'touserid' => $creator_id,
				'fromuserid' => $decided_by,
				'link' => 'timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id,
			]);
			$notify_ids[] = $creator_id;
		}
		if ($hod_id > 0 && $hod_id !== $decided_by && $hod_id !== $creator_id) {
			add_notification([
				'description' => 'Regularization ' . $status_label . ' for ' . $employee_name,
				'touserid' => $hod_id,
				'fromuserid' => $decided_by,
				'link' => 'timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id,
			]);
			$notify_ids[] = $hod_id;
		}
		if (!empty($notify_ids)) {
			pusher_trigger_notification(array_values(array_unique($notify_ids)));
		}

		$to = [];
		if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
			$to[] = $employee_email;
		}
		$cc = ['hr@tech2globe.com', 'sarabjeet@tech2globe.net'];
		if (!empty($hod_email) && filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
			// HOD gets the decision email (as To if employee missing, else CC)
			if (empty($to)) {
				$to[] = $hod_email;
			} else {
				$cc[] = $hod_email;
			}
		}
		$to = array_values(array_unique(array_filter($to, function ($e) {
			return filter_var($e, FILTER_VALIDATE_EMAIL);
		})));
		$cc = array_values(array_unique(array_diff(
			array_filter($cc, function ($e) {
				return filter_var($e, FILTER_VALIDATE_EMAIL);
			}),
			$to
		)));

		if (empty($to)) {
			log_activity('Regularization decision email skipped (no recipients): #' . $request_id);
			return false;
		}

		$subject = 'Attendance Regularization ' . ucfirst($status_label) . ' - ' . $employee_name . ' - ' . $day;
		$message = '<p>Dear ' . html_escape($employee_name) . ',</p>';
		$message .= '<p>Your attendance regularization for <b>' . html_escape($day) . '</b> has been <b>' . html_escape($status_label) . '</b> by ' . html_escape($decider_name) . '.</p>';
		$message .= '<p><b>Time In:</b> ' . html_escape((string) ($row->time_in ?? '')) . '<br>';
		$message .= '<b>Time Out:</b> ' . html_escape((string) ($row->time_out ?? '')) . '</p>';
		if ($comment !== '') {
			$message .= '<p><b>Comment:</b> ' . nl2br(html_escape($comment)) . '</p>';
		}
		$message .= '<p><a href="' . html_escape($link) . '">View request</a></p>';
		$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';
		if ($hod_id > 0) {
			$message .= '<p><small>This email is also shared with your HOD.</small></p>';
		}

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}

		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->initialize();
			$this->email->set_mailtype('html');
			$this->email->from($from_email, get_option('companyname') ?: 'Tech2globe Workroom');
			$this->email->to($to);
			if (!empty($cc)) {
				$this->email->cc($cc);
			}
			$this->email->subject($subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(false);
			if ($ok) {
				log_activity('Regularization ' . $status_label . ' email sent: #' . $request_id . ' to ' . implode(',', $to) . (empty($cc) ? '' : ' cc ' . implode(',', $cc)));
			} else {
				log_activity('Regularization ' . $status_label . ' email failed: #' . $request_id);
			}
			return $ok;
		} catch (Throwable $e) {
			log_activity('Regularization decision email exception #' . $request_id . ': ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Email HOD + HR + Sarabjeet when an employee applies for attendance regularization (GreytHR-style).
	 */
	public function send_regularisation_application_email($request_id, $staff_id)
	{
		$request_id = (int) $request_id;
		$staff_id = (int) $staff_id;
		if ($request_id <= 0 || $staff_id <= 0) {
			return false;
		}

		$row = $this->db->where('id', $request_id)->get(db_prefix() . 'timesheets_additional_timesheet')->row();
		if (!$row) {
			return false;
		}

		$employee_name = get_staff_full_name($staff_id);
		$emp_id = function_exists('get_staff_emp_id') ? (string) get_staff_emp_id($staff_id) : '';
		$employee_email = function_exists('get_staff_email_id') ? get_staff_email_id($staff_id) : '';

		$manager_email = '';
		$manager_name = 'Manager';
		$staff = $this->db->select('team_manage')->where('staffid', $staff_id)->get(db_prefix() . 'staff')->row();
		if ($staff && (int) $staff->team_manage > 0) {
			$manager_id = (int) $staff->team_manage;
			$manager_email = get_staff_email_id($manager_id);
			$manager_name = get_staff_full_name($manager_id);
		}

		$to = [];
		$cc = ['hr@tech2globe.com', 'sarabjeet@tech2globe.net'];
		if (!empty($manager_email) && filter_var($manager_email, FILTER_VALIDATE_EMAIL)) {
			$to[] = $manager_email;
		} else {
			// No HOD mapped — send to HR as primary
			$to[] = 'hr@tech2globe.com';
			$cc = ['sarabjeet@tech2globe.net'];
			$manager_name = 'HR';
		}
		// Employee (user) always gets a copy of regularization emails.
		if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
			$cc[] = $employee_email;
		}

		$to = array_values(array_unique(array_filter($to, function ($e) {
			return filter_var($e, FILTER_VALIDATE_EMAIL);
		})));
		$cc = array_values(array_unique(array_filter($cc, function ($e) {
			return filter_var($e, FILTER_VALIDATE_EMAIL);
		})));
		$cc = array_values(array_diff($cc, $to));

		if (empty($to)) {
			log_activity('Regularization email skipped (no recipients): request #' . $request_id);
			return false;
		}

		$day = !empty($row->additional_day) ? _d($row->additional_day) : (string) $row->additional_day;
		$time_in = (string) ($row->time_in ?? '');
		$time_out = (string) ($row->time_out ?? '');
		$reason = (string) ($row->reason ?? '');
		$link = admin_url('timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id=' . $request_id);

		$subject = 'Attendance Regularization Request - ' . $employee_name . ' - ' . $day;

		$message = '<p>Dear ' . html_escape($manager_name) . ',</p>';
		$message .= '<p><b>' . html_escape($employee_name) . '</b> has applied for attendance regularization. Please review and <b>Forward to Super HR</b>, or Reject.</p>';
		$message .= '<p><b>Employee Name:</b> ' . html_escape($employee_name) . '</p>';
		if ($emp_id !== '') {
			$message .= '<p><b>Employee ID:</b> ' . html_escape($emp_id) . '</p>';
		}
		$message .= '<p><b>Date:</b> ' . html_escape($day) . '</p>';
		$message .= '<p><b>Requested Time In:</b> ' . html_escape($time_in) . '</p>';
		$message .= '<p><b>Requested Time Out:</b> ' . html_escape($time_out) . '</p>';
		$message .= '<p><b>Reason:</b><br>' . nl2br(html_escape($reason)) . '</p>';
		$message .= '<p><b>Note:</b> Final approval is done by Super HR / HR. Managers Forward or Reject only.</p>';
		$message .= '<p><b>Review:</b> <a href="' . html_escape($link) . '">' . html_escape($link) . '</a></p>';
		$message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

		$from_email = get_option('smtp_email');
		if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
			$from_email = 'noreply@t2gworkroom.com';
		}
		$from_name = get_option('companyname') ?: 'Tech2globe Workroom';

		try {
			$this->load->library('email');
			$this->email->clear(true);
			$this->email->initialize();
			$this->email->set_mailtype('html');
			$this->email->from($from_email, $from_name);
			$this->email->to($to);
			if (!empty($cc)) {
				$this->email->cc($cc);
			}
			if (!empty($employee_email) && filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
				$this->email->reply_to($employee_email);
			}
			$this->email->subject($subject);
			$this->email->message($message);
			$ok = (bool) $this->email->send(false);
			if (!$ok) {
				log_activity('Regularization email failed: #' . $request_id . ' | ' . $this->email->print_debugger(['headers']));
			} else {
				log_activity('Regularization email sent: #' . $request_id . ' to ' . implode(',', $to) . (empty($cc) ? '' : ' cc ' . implode(',', $cc)));
			}
			return $ok;
		} catch (Exception $e) {
			log_activity('Regularization email exception: #' . $request_id . ' | ' . $e->getMessage());
			return false;
		}
	}

	/**

	 * get vacation days of the year

	 * @param  integer $staff_id

	 * @return integer $year

	 */

	public function get_requisition_number_of_day_off($staff_id, $year = false)
	{

		if ($year == false) {

			$year = date('Y');
		}

		$result_total_day_off = $this->get_day_off_by_year($staff_id, $year);

		if ($result_total_day_off) {

			$total_day_off_in_year = (float) $result_total_day_off->total;

			$total_day_off_allowed_in_year = (float) $result_total_day_off->remain;
		} else {

			$total_day_off_in_year = 0;

			$total_day_off_allowed_in_year = 0;
		}

		$data = [];

		$data['total_day_off_in_year'] = $total_day_off_in_year;

		$status_leave = $this->timesheets_model->get_number_of_days_off($staff_id);

		$data['total_day_off_allowed_in_year'] = 0;

		$data['total_day_off'] = 0;

		if ($result_total_day_off != null) {

			$data['total_day_off_allowed_in_year'] = $status_leave - ($result_total_day_off->total - $result_total_day_off->remain);

			if ($data['total_day_off_allowed_in_year'] < 0) {

				$data['total_day_off_allowed_in_year'] = 0;
			}

			$data['total_day_off'] = $result_total_day_off->total - $result_total_day_off->remain;
		}

		return $data;
	}



	/**

	 * get date leave in month

	 * @param  [int] $staff_id

	 * @return [y-mm] $month

	 */

	public function get_date_leave_in_month($staff_id, $month)
	{

		if ($staff_id != '' && $staff_id != 0) {

			$this->db->where('date_format(date_work, "%Y-%m") = "' . $month . '" and staff_id = ' . $staff_id . ' and type = \'al\'');

			$timekeeping = $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();

			$count_timekeeping = 0;

			$count_result = 0;

			foreach ($timekeeping as $key => $value) {

				$hour_shift = $this->get_hour_shift_staff($staff_id, $value['date_work']);

				if ($hour_shift > 0) {

					$count_result += $value['value'] / $hour_shift;
				}
			}

			return number_format($count_result, 2);
		} else {

			return 1;
		}
	}



	/**

	 * gets the day off by year.

	 *

	 * @param      <type>  $staffid  the staffid

	 * @param      <type>  $year     the year

	 *

	 * @return     <type>  the day off by year.

	 */

	public function get_day_off_by_year($staffid, $year)
	{

		$this->db->where('staffid', $staffid);

		$this->db->where('year', $year);

		return $this->db->get(db_prefix() . 'timesheets_day_off')->row();
	}



	/**

	 * get allowance type

	 * @param  integer $id

	 * @return object or array

	 */

	public function get_allowance_type($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('type_id', $id);



			return $this->db->get(db_prefix() . 'allowance_type')->row();
		}



		if ($id == false) {

			return $this->db->get(db_prefix() . 'allowance_type')->result_array();
		}
	}

	/**

	 * update allowance type

	 * @param  $data

	 * @param  $id

	 * @return  boolean

	 */

	public function update_allowance_type($data, $id)
	{

		$data['allowance_val'] = reformat_currency($data['allowance_val']);

		$this->db->where('type_id', $id);

		$this->db->update(db_prefix() . 'allowance_type', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete allowance type

	 * @param  $id

	 * @return  boolean

	 */

	public function delete_allowance_type($id)
	{

		$this->db->where('type_id', $id);

		$this->db->delete(db_prefix() . 'allowance_type');

		if ($this->db->affected_rows() > 0) {

			return true;
		}



		return false;
	}

	/**

	 * get salary form

	 * @param  boolean $id

	 * @return object or array

	 */

	public function get_salary_form($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('form_id', $id);



			return $this->db->get(db_prefix() . 'salary_form')->row();
		}



		if ($id == false) {

			return $this->db->query('select * from ' . db_prefix() . 'salary_form')->result_array();
		}
	}

	/**

	 * add salary form

	 * @param array $data

	 * @return object or array

	 */

	public function add_salary_form($data)
	{

		$data['salary_val'] = reformat_currency($data['salary_val']);

		$this->db->insert(db_prefix() . 'salary_form', $data);

		$insert_id = $this->db->insert_id();

		return $insert_id;
	}

	/**

	 * update salary form

	 * @param  array $data

	 * @param  integer $id

	 * @return boolean

	 */

	public function update_salary_form($data, $id)
	{

		$data['salary_val'] = reformat_currency($data['salary_val']);

		$this->db->where('form_id', $id);

		$this->db->update(db_prefix() . 'salary_form', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete salary form

	 * @param $id

	 * @return boolean

	 */

	public function delete_salary_form($id)
	{

		$this->db->where('form_id', $id);

		$this->db->delete(db_prefix() . 'salary_form');

		if ($this->db->affected_rows() > 0) {

			return true;
		}



		return false;
	}



	/**

	 * get province

	 * @return [type]

	 */

	public function get_province()
	{

		return $this->db->get(db_prefix() . 'province_city')->result_array();
	}

	public function get_procedure_retire($id = '')
	{

		if ($id == '') {

			return $this->db->get(db_prefix() . 'procedure_retire')->result_array();
		} else {

			$this->db->where('procedure_retire_id', $id);

			return $this->db->get(db_prefix() . 'procedure_retire')->result_array();
		}
	}

	/**

	 * get staff info

	 * @param  integer $staffid

	 * @return integer

	 */

	public function get_staff_info($staffid)
	{

		$this->db->where('staffid', $staffid);

		$results = $this->db->get(db_prefix() . 'staff')->row();

		return $results;
	}



	/**

	 * timesheets setting get allowance key

	 * @return array

	 */

	public function timesheets_setting_get_allowance_key()
	{

		$allowance_no_taxable = json_decode(get_timesheets_option('allowance_no_taxable'), true);

		$arr_allowance_key = [];

		if ($allowance_no_taxable) {

			foreach ($allowance_no_taxable as $allowance_key) {

				array_push($arr_allowance_key, $allowance_key['allowance_name']);
			}
		}

		return $arr_allowance_key;
	}



	/**

	 * get approval process

	 * @param  integer $id

	 * @return object array

	 */

	public function get_approval_process($id = '')
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_approval_setting')->row();
		}

		return $this->db->get(db_prefix() . 'timesheets_approval_setting')->result_array();
	}



	/**

	 * get_timesheet

	 * @param  integer $staffid

	 * @param  date $from_date

	 * @param  date $to_date

	 * @return array

	 */

	public function get_timesheet($staffid = '', $from_date, $to_date)
	{

		return $this->db->query('select * from ' . db_prefix() . 'timesheets_timesheet where staff_id = ' . $staffid . ' and date_work between \'' . $from_date . '\' and \'' . $to_date . '\'')->result_array();
	}

	/**

	 * report by working hours

	 */

	public function report_by_working_hours()
	{

		$months_report = $this->input->post('months_report');



		// $custom_date_select = '';

		// $custom_date_select1 = '';

		// if ($months_report != '') {

		// 	if (is_numeric($months_report)) {

		// 		// last month

		// 		if ($months_report == '1') {

		// 			$beginmonth = date('Y-m-01', strtotime('first day of last month'));

		// 			$endmonth   = date('Y-m-t', strtotime('last day of last month'));

		// 		} else {

		// 			$months_report = (int) $months_report;

		// 			$months_report--;

		// 			$beginmonth = date('Y-m-01', strtotime("-$months_report month"));

		// 			$endmonth   = date('Y-m-t');

		// 		}

		// 		$custom_date_select = '(ht.date_work between "' . $beginmonth . '" and "' . $endmonth . '")';

		// 		$custom_date_select1 = '(ht.additional_day between "' . $beginmonth . '" and "' . $endmonth . '")';

		// 	} elseif ($months_report == 'this_month') {

		// 		$custom_date_select = '(ht.date_work between "' . date('Y-m-01') . '" and "' . date('Y-m-t') . '")';

		// 		$custom_date_select1 = '(ht.additional_day between "' . date('Y-m-01') . '" and "' . date('Y-m-t') . '")';

		// 	} elseif ($months_report == 'this_year') {

		// 		$custom_date_select = '(ht.date_work between "' .

		// 		date('Y-m-d', strtotime(date('Y-01-01'))) .

		// 		'" and "' .

		// 		date('Y-m-d', strtotime(date('Y-12-31'))) . '")';



		// 		$custom_date_select1 = '(ht.additional_day between "' .

		// 		date('Y-m-d', strtotime(date('Y-01-01'))) .

		// 		'" and "' .

		// 		date('Y-m-d', strtotime(date('Y-12-31'))) . '")';

		// 	} elseif ($months_report == 'last_year') {

		// 		$custom_date_select = '(ht.date_work between "' .

		// 		date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .

		// 		'" and "' .

		// 		date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';



		// 		$custom_date_select1 = '(ht.additional_day between "' .

		// 		date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .

		// 		'" and "' .

		// 		date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';

		// 	} elseif ($months_report == 'custom') {

		// 		$from_date = to_sql_date($this->input->post('report_from'));

		// 		$to_date   = to_sql_date($this->input->post('report_to'));

		// 		if ($from_date == $to_date) {

		// 			$custom_date_select =  'ht.date_work ="' . $from_date . '"';

		// 			$custom_date_select1 =  'ht.additional_day ="' . $from_date . '"';

		// 		} else {

		// 			$custom_date_select = '(ht.date_work between "' . $from_date . '" and "' . $to_date . '")';

		// 			$custom_date_select1 = '(ht.additional_day between "' . $from_date . '" and "' . $to_date . '")';

		// 		}

		// 	}



		// }



		if ($months_report == 'this_month') {

			$from_date = date('Y-m-01');

			$to_date = date('Y-m-t');
		}



		if ($months_report == '1') {

			$from_date = date('Y-m-01', strtotime('first day of last month'));

			$to_date = date('Y-m-t', strtotime('last day of last month'));
		}



		if ($months_report == 'this_year') {

			$from_date = date('Y-m-d', strtotime(date('Y-01-01')));

			$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
		}



		if ($months_report == 'last_year') {

			$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));

			$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
		}



		if ($months_report == '3') {

			$months_report--;

			$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));

			$to_date = date('Y-m-t');
		}



		if ($months_report == '6') {

			$months_report--;

			$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));

			$to_date = date('Y-m-t');
		}



		if ($months_report == '12') {

			$months_report--;

			$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));

			$to_date = date('Y-m-t');
		}



		if ($months_report == 'custom') {

			$from_date = $this->timesheets_model->format_date($this->input->post('report_from'));

			$to_date = $this->timesheets_model->format_date($this->input->post('report_to'));
		}



		$staffquery = '';

		$staff = $this->get_staff_timekeeping_applicable_object();

		foreach ($staff as $k => $staffitem) {

			$staffquery .= $staffitem['staffid'] . ',';
		}

		if ($staffquery != '') {

			$staffquery = ' and staffid in (' . rtrim($staffquery, ',') . ')';
		}



		$data_timekeeping_form = get_timesheets_option('timekeeping_form');

		$data_timesheet = [];



		// $custom_date_select = ' AND '.$custom_date_select.$staffquery;

		// $custom_date_select1 = ' AND '.$custom_date_select1.$staffquery;



		$chart = [];

		$dpm = $this->departments_model->get();

		foreach ($dpm as $d) {

			$staff_list = $this->db->query('select staffid, firstname, lastname from ' . db_prefix() . 'staff where staffid in (SELECT staffid FROM ' . db_prefix() . 'staff_departments where departmentid = ' . $d['departmentid'] . ')' . $staffquery)->result_array();

			$data_timesheet = [];

			if ($data_timekeeping_form == 'timekeeping_task') {

				$data_timesheet = $this->timesheets_model->get_attendance_task($staff_list, '', '', $from_date, $to_date);
			} else {

				$data_timesheet = $this->timesheets_model->get_attendance_manual($staff_list, '', '', $from_date, $to_date);
			}



			$total = 0;

			foreach ($data_timesheet['staff_row_tk'] as $row_tk) {

				foreach ($row_tk as $value) {

					$split_value = explode(':', $value);

					if (isset($split_value[0])) {

						// if ($split_value[0] == 'W') {
						if ($split_value[0] == 'P') {

							$total += isset($split_value[1]) ? $split_value[1] : 0;
						}
					}
				}
			}

			$chart['categories'][] = $d['name'];

			$chart['total_work_hours'][] = $total;

			$chart['total_work_hours_approved'][] = $this->count_work_hours_approve($d['departmentid'], $from_date, $to_date);
		}

		return $chart;
	}



	/**

	 * count work hours approve

	 * @param  integer $department

	 * @param  date $custom_date_select1

	 * @return integer

	 */

	public function count_work_hours_approve($department, $from_date, $to_date)
	{

		$list_app = $this->db->query('select ht.creator, ht.additional_day, ht.timekeeping_value from ' . db_prefix() . 'timesheets_additional_timesheet ht left join ' . db_prefix() . 'staff_departments sd on sd.staffid = ht.creator where sd.departmentid = ' . $department . ' and ht.status = 1 and ht.additional_day between "' . $from_date . '" and "' . $to_date . '"')->result_array();

		$sum = 0;

		if (count($list_app) > 0) {

			foreach ($list_app as $lis) {

				if (is_numeric($lis['timekeeping_value'])) {

					$sum += $lis['timekeeping_value'];
				}
			}
		}

		return $sum;
	}

	/**

	 * add approval process

	 * @param array $data

	 * @return boolean

	 */

	public function add_approval_process($data)
	{

		unset($data['approval_setting_id']);



		if (isset($data['staff'])) {

			$setting = [];

			foreach ($data['staff'] as $key => $value) {

				$node = [];

				$node['approver'] = (isset($data['approver'][$key]) ? $data['approver'][$key] : 'specific_personnel');

				$node['staff'] = $data['staff'][$key];



				$setting[] = $node;
			}

			unset($data['approver']);

			unset($data['staff']);
		}



		if (!isset($data['choose_when_approving'])) {

			$data['choose_when_approving'] = 0;
		}



		if (isset($data['departments'])) {

			$data['departments'] = implode(',', $data['departments']);
		} else {

			$data['departments'] = '';
		}



		if (isset($data['job_positions'])) {

			$data['job_positions'] = implode(',', $data['job_positions']);
		} else {

			$data['job_positions'] = '';
		}



		$data['setting'] = json_encode($setting);



		if (isset($data['notification_recipient'])) {

			$data['notification_recipient'] = implode(",", $data['notification_recipient']);
		} else {

			$data['notification_recipient'] = '';
		}



		$this->db->insert(db_prefix() . 'timesheets_approval_setting', $data);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			return true;
		}

		return false;
	}

	/**

	 * update approval process

	 * @param  integer $id

	 * @param  array $data

	 * @return boolean

	 */

	public function update_approval_process($id, $data)
	{

		if (isset($data['staff'])) {

			$setting = [];

			foreach ($data['staff'] as $key => $value) {

				$node = [];

				$node['approver'] = (isset($data['approver'][$key]) ? $data['approver'][$key] : 'specific_personnel');

				$node['staff'] = $data['staff'][$key];



				$setting[] = $node;
			}

			unset($data['approver']);

			unset($data['staff']);
		}



		if (!isset($data['choose_when_approving'])) {

			$data['choose_when_approving'] = 0;
		}



		$data['setting'] = json_encode($setting);



		if (isset($data['departments'])) {

			$data['departments'] = implode(',', $data['departments']);
		} else {

			$data['departments'] = '';
		}



		if (isset($data['job_positions'])) {

			$data['job_positions'] = implode(',', $data['job_positions']);
		} else {

			$data['job_positions'] = '';
		}



		if (isset($data['notification_recipient'])) {

			$data['notification_recipient'] = implode(",", $data['notification_recipient']);
		} else {

			$data['notification_recipient'] = '';
		}



		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'timesheets_approval_setting', $data);



		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete approval setting

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_approval_setting($id)
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			$this->db->delete(db_prefix() . 'timesheets_approval_setting');



			if ($this->db->affected_rows() > 0) {

				return true;
			}
		}

		return false;
	}

	public function setting_timekeeper($data)
	{

		$affectedrows = 0;

		if (isset($data['timekeeping_task_role'])) {



			$timekeeping_task_role = implode(',', $data['timekeeping_task_role']);



			$this->db->where('option_name', 'timekeeping_task_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $timekeeping_task_role,

			]);
		} else {

			$this->db->where('option_name', 'timekeeping_task_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '',

			]);
		}

		if ($this->db->affected_rows() > 0) {

			$affectedrows++;
		}



		if (isset($data['timekeeping_manually_role'])) {

			$timekeeping_manually_role = implode(',', $data['timekeeping_manually_role']);

			$this->db->where('option_name', 'timekeeping_manually_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $timekeeping_manually_role,

			]);
		} else {

			$this->db->where('option_name', 'timekeeping_manually_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '',

			]);
		}

		if ($this->db->affected_rows() > 0) {

			$affectedrows++;
		}



		if (isset($data['csv_clsx_role'])) {



			$csv_clsx_role = implode(',', $data['csv_clsx_role']);



			$this->db->where('option_name', 'csv_clsx_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $csv_clsx_role,

			]);
		} else {

			$this->db->where('option_name', 'csv_clsx_role');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '',

			]);
		}

		if ($this->db->affected_rows() > 0) {

			$affectedrows++;
		}



		if (isset($data['timekeeping_form'])) {



			$timekeeping_form = $data['timekeeping_form'];



			$this->db->where('option_name', 'timekeeping_form');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $timekeeping_form,

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		if ($affectedrows > 0) {

			return true;
		}

		return false;
	}



	public function edit_timesheets($data, $staffid = '')
	{

		if ($staffid != '') {

			$staff_id = $staffid;
		} else {

			$staff_id = get_staff_user_id();
		}

		$additional_day = $data->additional_day;

		$data_ts = $this->normalize_time_hm($data->time_in);
		$data_te = $this->normalize_time_hm($data->time_out);



		$time_in = $additional_day . ' ' . $data_ts;

		$time_out = $additional_day . ' ' . $data_te;

		$staff = $this->staff_model->get($data->creator);

		$data_new = [];



		if ($data->time_in != '' && $data->time_out != '') {

			$data_work_time = $this->get_hour_shift_staff($staff_id, $data->additional_day);

			if ($data_work_time != 0) {

				$this->db->where('staff_id', $staff_id);

				$this->db->where('date_work', $data->additional_day);

				// $this->db->where('type', 'W');
				$this->db->where('type', 'P');


				$tslv = $this->db->get(db_prefix() . 'timesheets_timesheet')->row();

				if ($tslv) {

					$new_value = $tslv->value + $data->timekeeping_value;
					// commenting so that time can be edited more than shift hours

					//					if ($new_value > $data_work_time) {
					//
					//						$new_value = $data_work_time;
					//					}

					$this->db->where('id', $tslv->id);

					$this->db->update(db_prefix() . 'timesheets_timesheet', ['value' => $new_value]);
				} else {

					$this->automatic_insert_timesheets($staff_id, $time_in, $time_out);
				}
			}
		} else {

			if ($data->timekeeping_value > 0) {

				$data_work_time = $this->get_hour_shift_staff($staff_id, $data->additional_day);

				if ($data_work_time != 0) {



					$this->db->where('staff_id', $staff_id);

					$this->db->where('date_work', $data->additional_day);

					// $this->db->where('type', 'w');
					$this->db->where('type', 'p');

					$tslv = $this->db->get(db_prefix() . 'timesheets_timesheet')->row();

					if ($tslv) {

						$new_value = $tslv->value + $data->timekeeping_value;
						// commenting so that time can be edited more than shift hours
						//						if ($new_value > $data_work_time) {
						//
						//							$new_value = $data_work_time;
						//						}

						$this->db->where('id', $tslv->id);

						$this->db->update(db_prefix() . 'timesheets_timesheet', ['value' => $new_value]);
					} else {

						$this->db->insert(db_prefix() . 'timesheets_timesheet', [

							'value' => $data->timekeeping_value,

							// 'type' => 'w',
							'type' => 'p',

							'staff_id' => $staff_id,

							'add_from' => get_staff_user_id(),

							'date_work' => $data->additional_day,

						]);
					}
				}
			}
		}

		return true;
	}

	/**

	 * add additional timesheets

	 * @param array $data

	 * @param integer $staffid

	 */

	/**
	 * Lunch break duration in minutes for a staff member on a date.
	 *
	 * @param string   $date
	 * @param int|null $staff_id
	 * @return int
	 */
	public function get_rest_time($date, $staff_id = '')
	{
		if ($staff_id === '' || $staff_id === null) {
			$staff_id = get_staff_user_id();
		}

		$staff_id = (int) $staff_id;
		if ($staff_id <= 0) {
			return 0;
		}

		$sql_date = to_sql_date($date) ?: $date;
		$shift_info = $this->get_info_hour_shift_staff($staff_id, $sql_date);
		$break_hours = (float) ($shift_info->lunch_break_hour ?? 0);

		if ($break_hours <= 0) {
			$lunch_start = trim((string) ($shift_info->start_lunch_break ?? ''));
			$lunch_end = trim((string) ($shift_info->end_lunch_break ?? ''));
			if ($lunch_start !== '' && $lunch_end !== '' && $lunch_start !== '00:00:00' && $lunch_end !== '00:00:00') {
				$break_hours = max(0, (strtotime('1970-01-01 ' . substr($lunch_end, 0, 8)) - strtotime('1970-01-01 ' . substr($lunch_start, 0, 8))) / 3600);
			}
		}

		if ($break_hours <= 0) {
			$break_hours = 0.5;
		}

		return (int) round($break_hours * 60);
	}

	/**
	 * Normalize HH:MM or HH:MM:SS to HH:MM:SS for strtotime.
	 *
	 * @param string $time
	 * @return string
	 */
	public function normalize_time_hm($time)
	{
		$time = trim((string) $time);
		if ($time === '' || strtolower($time) === 'null') {
			return '';
		}
		if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m)) {
			return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], isset($m[3]) ? (int) $m[3] : 0);
		}

		return $time;
	}

	/**
	 * Seconds since midnight for a time string.
	 *
	 * @param string $time
	 * @return int|false
	 */
	public function time_to_seconds_of_day($time)
	{
		$norm = $this->normalize_time_hm($time);
		if ($norm === '') {
			return false;
		}
		$ts = strtotime('1970-01-01 ' . $norm);
		if ($ts === false) {
			return false;
		}

		return (int) $ts - (int) strtotime('1970-01-01 00:00:00');
	}

	/**
	 * Worked hours between time in/out, with minute precision.
	 * Lunch break is subtracted only when the interval overlaps the lunch window.
	 *
	 * @param string     $time_in
	 * @param string     $time_out
	 * @param string     $date
	 * @param int|string $staff_id
	 * @param bool       $apply_lunch_break
	 * @return float hours (2 decimal places)
	 */
	public function calculate_regularisation_hours($time_in, $time_out, $date = '', $staff_id = '', $apply_lunch_break = true)
	{
		$in_sec = $this->time_to_seconds_of_day($time_in);
		$out_sec = $this->time_to_seconds_of_day($time_out);
		if ($in_sec === false || $out_sec === false || $out_sec <= $in_sec) {
			return 0.0;
		}

		$worked_sec = $out_sec - $in_sec;

		if ($apply_lunch_break) {
			$staff_id = ($staff_id === '' || $staff_id === null) ? (int) get_staff_user_id() : (int) $staff_id;
			$sql_date = to_sql_date($date) ?: $date;
			$shift_info = $staff_id > 0 && $sql_date
				? $this->get_info_hour_shift_staff($staff_id, $sql_date)
				: null;

			$lunch_start = trim((string) ($shift_info->start_lunch_break ?? '12:00:00'));
			$lunch_end = trim((string) ($shift_info->end_lunch_break ?? '12:30:00'));
			if ($lunch_start === '' || $lunch_start === '00:00:00') {
				$lunch_start = '12:00:00';
			}
			if ($lunch_end === '' || $lunch_end === '00:00:00') {
				$lunch_end = '12:30:00';
			}

			$ls = $this->time_to_seconds_of_day($lunch_start);
			$le = $this->time_to_seconds_of_day($lunch_end);
			if ($ls !== false && $le !== false && $le > $ls) {
				// Overlap between work interval and lunch window (seconds).
				$overlap = max(0, min($out_sec, $le) - max($in_sec, $ls));
				$worked_sec -= $overlap;
			}
		}

		if ($worked_sec < 0) {
			$worked_sec = 0;
		}

		// Keep minute precision (e.g. 15 min = 0.25h, 45 min = 0.75h).
		return round($worked_sec / 3600, 2);
	}

	public function add_additional_timesheets($data, $staffid = '')
	{

		if ($staffid == '') {

			$staff_id = get_staff_user_id();
		} else {

			$staff_id = $staffid;
		}

		$time_in = ($data['time_in'] ?? '') === 'null' ? '' : ($data['time_in'] ?? '');
		$time_out = ($data['time_out'] ?? '') === 'null' ? '' : ($data['time_out'] ?? '');
		$time_in = $this->normalize_time_hm($time_in);
		$time_out = $this->normalize_time_hm($time_out);
		// Store as HH:MM for display consistency.
		if ($time_in !== '' && preg_match('/^(\d{2}:\d{2}):\d{2}$/', $time_in, $m)) {
			$time_in = $m[1];
		}
		if ($time_out !== '' && preg_match('/^(\d{2}:\d{2}):\d{2}$/', $time_out, $m)) {
			$time_out = $m[1];
		}

		$timekeeping_type = !empty($data['timekeeping_type']) ? $data['timekeeping_type'] : 'p';
		$timekeeping_value = $data['timekeeping_value'] ?? '';

		if (($timekeeping_value === '0' || $timekeeping_value === '' || $timekeeping_value === null) && $time_in !== '' && $time_out !== '') {
			$sql_date = to_sql_date($data['additional_day']) ?: $data['additional_day'];
			// Type p = present/attendance: subtract lunch only if range overlaps lunch.
			// Type W/other = raw duration (minutes supported).
			$apply_lunch = (strtolower((string) $timekeeping_type) === 'p');
			$timekeeping_value = $this->calculate_regularisation_hours($time_in, $time_out, $sql_date, $staff_id, $apply_lunch);
		}

		$timekeeping_value = (float) $timekeeping_value;
		if ($timekeeping_value < 0) {
			$timekeeping_value = 0;
		}
		// 2 decimals so minutes are kept (0.25 = 15m, 0.5 = 30m, 1.75 = 1h 45m).
		$timekeeping_value = number_format($timekeeping_value, 2, '.', '');

		$insert = [
			'additional_day' => to_sql_date($data['additional_day']),
			'time_in' => $time_in,
			'time_out' => $time_out,
			'timekeeping_value' => $timekeeping_value,
			'timekeeping_type' => $timekeeping_type,
			'reason' => $data['reason'] ?? '',
			'creator' => $staff_id,
			'status' => '0',
			'approver' => 0,
		];

		$this->db->insert(db_prefix() . 'timesheets_additional_timesheet', $insert);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			$checks = $this->check_choose_when_approving('additional_timesheets');

			if ($checks == 0) {

				$data_new = [
					'rel_id' => $insert_id,
					'rel_type' => 'additional_timesheets',
					'addedfrom' => $staff_id,
				];

				$success = $this->send_request_approve($data_new, $staff_id);

				if ($success === true) {
					$this->send_mail($data_new);
				}
			}

			return $insert_id;
		}

		return false;
	}

	/**

	 * get hour shift staff

	 * @param  integer $staff_id

	 * @param  integer $date

	 * @return integer

	 */

	public function get_info_hour_shift_staff($staff_id, $date)
	{

		$result = new stdclass();

		$result->woking_hour = 0;

		$result->lunch_break_hour = 0;



		$result->start_working = '';

		$result->end_working = '';

		$result->start_lunch_break = '';

		$result->end_lunch_break = '';



		$woking_hour = 0;

		$lunch_break_hour = 0;



		$data_shift_list = $this->get_shift_work_staff_by_date($staff_id, $date);

		foreach ($data_shift_list as $ss) {

			$data_shift_type = $this->get_shift_type($ss);

			if ($data_shift_type) {

				$woking_hour = $this->get_hour($data_shift_type->time_start_work, $data_shift_type->time_end_work);

				$lunch_break_hour = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);

				$result->start_working = $data_shift_type->time_start_work;

				$result->end_working = $data_shift_type->time_end_work;

				$result->start_lunch_break = $data_shift_type->start_lunch_break_time;

				$result->end_lunch_break = $data_shift_type->end_lunch_break_time;

				$result->woking_hour = abs($woking_hour - $lunch_break_hour);

				$result->lunch_break_hour = $lunch_break_hour;
			}
		}

		return $result;
	}



	/**

	 * gets the file requisition.

	 *

	 * @param      int   $id      the identifier

	 * @param      boolean  $rel_id  the relative identifier

	 *

	 * @return     object   the file requisition.

	 */

	public function get_file_requisition($id, $rel_id = false)
	{

		if (is_client_logged_in()) {

			$this->db->where('visible_to_customer', 1);
		}

		$this->db->where('id', $id);

		$file = $this->db->get(db_prefix() . 'files')->row();



		if ($file && $rel_id) {

			if ($file->rel_id != $rel_id) {

				return $file;
			}
		}



		return $file;
	}



	/**

	 * automatic insert timesheets

	 *

	 * @param      int   $staffid   the staffid

	 * @param      integer  $time_in   the time in

	 * @param      integer  $time_out  the time out

	 *

	 * @return     boolean

	 */

	public function automatic_insert_timesheets($staffid, $time_in, $time_out)
	{



		$date_work = date('Y-m-d', strtotime($time_in));

		$work_time = $this->get_hour_shift_staff($staffid, $date_work);

		$affectedrows = 0;

		if ($work_time > 0 && $work_time != '') {

			$list_shift = $this->get_shift_work_staff_by_date($staffid, $date_work);



			$d1 = strtotime($this->format_date_time($time_in));

			$d2 = strtotime($this->format_date_time($time_out));

			if ($d1 > $d2) {

				$temp = $time_in;

				$time_in = $time_out;

				$time_out = $temp;
			}

			$hour1 = explode(' ', $time_in);

			$hour2 = explode(' ', $time_out);

			$time_in = strtotime($hour1[1]);

			$time_out = strtotime($hour2[1]);



			$hour = 0;

			$late = 0;

			$early = 0;

			$lunch_time = 0;

			foreach ($list_shift as $shift) {

				$data_shift_type = $this->timesheets_model->get_shift_type($shift);



				$time_in_ = $time_in;

				$time_out_ = $time_out;



				if ($data_shift_type) {

					$start_work = strtotime($data_shift_type->time_start_work);

					$end_work = strtotime($data_shift_type->time_end_work);

					$start_lunch_break = strtotime($data_shift_type->start_lunch_break_time);

					$end_lunch_break = strtotime($data_shift_type->end_lunch_break_time);

					if ($time_out < $start_work) {

						continue;
					}



					if ($time_out > $start_lunch_break && $time_out < $end_lunch_break) {

						$time_out_ = $start_lunch_break;
					}



					if ($time_in > $start_lunch_break && $time_in < $end_lunch_break) {

						$time_in_ = $end_lunch_break;
					}



					if ($time_in_ < $start_lunch_break && $time_out_ > $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					if ($time_in_ == $start_lunch_break && $time_out_ == $end_lunch_break) {

						continue;
					}

					if ($time_in_ == $start_lunch_break && $time_out_ > $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					if ($time_in_ < $start_lunch_break && $time_out_ == $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}



					/* commenting to make sure start and end time are within shift range
					if ($time_in < $start_work && $time_out > $start_work) {

						$time_in_ = $start_work;
					} elseif ($time_in > $start_work && $time_out > $start_work) {

						if ($time_in >= $start_lunch_break && $time_in <= $end_lunch_break) {

							$time_in = $start_lunch_break;
						}

						$lunch_time_s = 0;

						if ($time_in > $end_lunch_break) {

							$lunch_time_s = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
						}

						$late += round(abs($time_in - $start_work) / (60 * 60), 2) - $lunch_time_s;
					}



					if ($time_out > $end_work && $time_in < $end_work) {

						$time_out_ = $end_work;
					} elseif ($time_out < $end_work && $time_in < $end_work) {

						if ($time_out >= $start_lunch_break && $time_out <= $end_lunch_break) {

							$time_out = $end_lunch_break;
						}

						$lunch_time_s = 0;

						if ($time_out < $end_lunch_break) {

							$lunch_time_s = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
						}

						$early += round(abs($time_out - $end_work) / (60 * 60), 2) - $lunch_time_s;
					} */

					$hour += round(abs($time_out_ - $time_in_) / (60 * 60), 2);
				}
			}

			$value = abs($hour - $lunch_time);

			$this->db->where('date_work', $date_work);

			$this->db->where('staff_id', $staffid);

			$this->db->where('type', 'L');

			$this->db->delete(db_prefix() . 'timesheets_timesheet');



			$this->db->where('date_work', $date_work);

			$this->db->where('staff_id', $staffid);

			$this->db->where('type', 'E');

			$this->db->delete(db_prefix() . 'timesheets_timesheet');



			$this->db->where('date_work', $date_work);

			$this->db->where('staff_id', $staffid);

			// $this->db->where('type', 'W');
			$this->db->where('type', 'P');


			$this->db->delete(db_prefix() . 'timesheets_timesheet');


			// Writing conditions for marking attendence on leave calendar

			// if ($value >= 5 && $value < 7) {

			// 	$this->db->insert(db_prefix() . 'timesheets_timesheet',

			// 		[

			// 			'value' => $value,

			// 			'date_work' => $date_work,

			// 			'staff_id' => $staffid,

			// 			// 'type' => 'W',
			// 			'type' => 'HD',

			// 			'add_from' => get_staff_user_id(),

			// 		]);



			// 	$insert_id = $this->db->insert_id();



			// 	if ($insert_id) {

			// 		$affectedrows++;

			// 	}

			// } else if ($value < 5) {

			// 	$this->db->insert(db_prefix() . 'timesheets_timesheet',

			// 		[

			// 			'value' => $value,

			// 			'date_work' => $date_work,

			// 			'staff_id' => $staffid,

			// 			// 'type' => 'W',
			// 			'type' => 'AB',

			// 			'add_from' => get_staff_user_id(),

			// 		]);



			// 	$insert_id = $this->db->insert_id();



			// 	if ($insert_id) {

			// 		$affectedrows++;

			// 	}

			// } else if ($value > 7) {

			// 	$this->db->insert(db_prefix() . 'timesheets_timesheet',

			// 		[

			// 			'value' => $value,

			// 			'date_work' => $date_work,

			// 			'staff_id' => $staffid,

			// 			// 'type' => 'W',
			// 			'type' => 'present',

			// 			'add_from' => get_staff_user_id(),

			// 		]);



			// 	$insert_id = $this->db->insert_id();



			// 	if ($insert_id) {

			// 		$affectedrows++;

			// 	}

			// }



			if ($late > 0) {

				$this->db->insert(
					db_prefix() . 'timesheets_timesheet',

					[

						'value' => $late,

						'date_work' => $date_work,

						'staff_id' => $staffid,

						'type' => 'L',

						'add_from' => get_staff_user_id(),

					]
				);



				$insert_id = $this->db->insert_id();

				if ($insert_id) {

					$affectedrows++;
				}
			}



			if ($early > 0) {

				$this->db->insert(
					db_prefix() . 'timesheets_timesheet',

					[

						'value' => $early,

						'date_work' => $date_work,

						'staff_id' => $staffid,

						'type' => 'E',

						'add_from' => get_staff_user_id(),

					]
				);

				$insert_id = $this->db->insert_id();

				if ($insert_id) {

					$affectedrows++;
				}
			}
		}

		if ($affectedrows > 0) {

			return true;
		}

		return false;
	}



	/**

	 * adds an update timesheet.

	 *

	 * @param      object   $data           the data

	 * @param      boolean  $is_timesheets  indicates if timesheets

	 *

	 * @return     integer

	 */

	public function add_update_timesheet($data, $is_timesheets = false)
	{

		// $type_valid = ['AL', 'W', 'U', 'HO', 'E', 'L', 'B', 'SI', 'M', 'ME', 'NS', 'P'];
		$type_valid = ['AL', 'W', 'U', 'HO', 'E', 'L', 'B', 'SI', 'M', 'ME', 'NS', 'P', 'AB', 'HD','SL','SHL','PL','UL'];


		$results = 0;

		foreach ($data as $row) {

			foreach ($row as $key => $val) {

				if ($key != 'staff_id' && $key != 'staff_name') {

					$ts = explode(";", $val);

					// by default we are giving this value true . so the value is getting deleted from database. therefore, we are not updating , we are inserting most of our values.
					if ($is_timesheets === true) {

						$this->db->where('staff_id', $row['staff_id']);

						$this->db->where('date_work', $key);

						$this->db->delete(db_prefix() . 'timesheets_timesheet');
					}

					foreach ($ts as $ex) {

						$value = explode(':', trim($ex));

						// if ((isset($value[0]) && ctype_alpha($value[0]) && in_array(strtoupper($value[0]), $type_valid)) && (isset($value[1]) && is_numeric($value[1]))) {
						if ((isset($value[0]) && ctype_alpha($value[0]) && in_array(strtoupper($value[0]), $type_valid))) {

							$this->db->where('staff_id', $row['staff_id']);

							$this->db->where('date_work', $key);

							$this->db->where('type', strtoupper($value[0]));

							$isset = $this->db->get(db_prefix() . 'timesheets_timesheet')->row();



							$attendence_type = strtoupper(isset($value[0]) ? $value[0] : $ex);

							if ($isset) {

								// this condition is not running most of time , probably its not running at all.
								$this->db->where('staff_id', $row['staff_id']);

								$this->db->where('date_work', $key);

								$this->db->where('type', strtoupper($value[0]));

								$this->db->update(db_prefix() . 'timesheets_timesheet', [

									'value' => isset($value[1]) ? $value[1] : '',

									'add_from' => get_staff_user_id(),

									'type' => $attendence_type,

								]);

								if ($this->db->affected_rows() > 0) {

									$results++;
								}
							} else {

								// this is where our most of the action happens.

								$this->db->where('staff_id', $row['staff_id']);

								$this->db->where('start_time', $key);

								// $is_leave_set variable will check if there is any leave for that particular day and staff on requisition table.

								$is_leave_set = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();


								if ($val != '') {


									if (!$is_leave_set) {
										if ($attendence_type == 'AB') {
											$this->db->insert(db_prefix() . 'timesheets_requisition_leave', [

												'staff_id' => $row['staff_id'],
												'start_time' => $key,
												'end_time' => $key,
												'status' => 1,
												'subject' => 'Leave marked by Manager',
												'number_of_leaving_day' => 1

											]);
										} else if ($attendence_type == 'HD') {

											$this->db->insert(db_prefix() . 'timesheets_requisition_leave', [

												'staff_id' => $row['staff_id'],
												'start_time' => $key,
												'end_time' => $key,
												'status' => 1,
												'subject' => 'Half Day marked by Manager',
												'number_of_leaving_day' => 0.5

											]);
										}
									} else {
										if ($attendence_type == 'P') {
											$this->db->where('staff_id', $row['staff_id']);

											$this->db->where('start_time', $key);

											$this->db->update(db_prefix() . 'timesheets_requisition_leave', [

												'number_of_leaving_day' => 0

											]);
										} else if ($attendence_type == 'AB') {
											$this->db->where('staff_id', $row['staff_id']);

											$this->db->where('start_time', $key);

											$this->db->update(db_prefix() . 'timesheets_requisition_leave', [

												'number_of_leaving_day' => 1

											]);
										} else if ($attendence_type == 'HD') {
											$this->db->where('staff_id', $row['staff_id']);

											$this->db->where('start_time', $key);

											$this->db->update(db_prefix() . 'timesheets_requisition_leave', [

												'number_of_leaving_day' => 0.5

											]);
										} else if ($attendence_type == 'HO') {
											$this->db->where('staff_id', $row['staff_id']);

											$this->db->where('start_time', $key);

											$this->db->update(db_prefix() . 'timesheets_requisition_leave', [

												'number_of_leaving_day' => 0

											]);
										}
									}




									$this->db->insert(db_prefix() . 'timesheets_timesheet', [

										'staff_id' => $row['staff_id'],

										'date_work' => $key,

										'value' => isset($value[1]) ? $value[1] : '',

										'add_from' => get_staff_user_id(),

										'type' => strtoupper(isset($value[0]) ? $value[0] : $ex),

									]);

									$insert_id = $this->db->insert_id();

									if ($insert_id) {

										$results++;
									}
								}
							}



							if ($val == '') {

								$this->db->where('staff_id', $row['staff_id']);

								$this->db->where('date_work', $key);

								$this->db->delete(db_prefix() . 'timesheets_timesheet');

								if ($this->db->affected_rows() > 0) {

									$results++;
								}
							}
						}
					}
				}
			}
		}
		// die;

		return $results;
	}



	/**

	 * latch timesheet

	 *

	 * @param      string   $month  the month

	 *

	 * @return     boolean

	 */

	public function latch_timesheet($month)
	{

		if ($month != '') {

			$this->db->insert(db_prefix() . 'timesheets_latch_timesheet', [

				'month_latch' => $month,

			]);

			$insert_id = $this->db->insert_id();



			if ($insert_id) {

				$m = date('m', strtotime('01-' . $month));

				$y = date('y', strtotime('01-' . $month));

				$this->db->where('month(date_work) = ' . $m . ' and year(date_work) = ' . $y);

				$this->db->update(db_prefix() . 'timesheets_timesheet', ['latch' => 1]);



				return true;
			} else {

				return false;
			}
		}



		return false;
	}



	/**

	 * gets the taskstimers.

	 *

	 * @param      int  $task_id   the task identifier

	 * @param      int  $staff_id  the staff identifier

	 *

	 * @return     array  the taskstimers.

	 */

	public function get_taskstimers($staff_id, $where)
	{

		$this->db->where($where);

		$this->db->where('staff_id', $staff_id);

		$this->db->select('*, CASE

			WHEN end_time is NULL THEN (' . time() . '-start_time) / 60 / 60

			ELSE (end_time-start_time) / 60 / 60

			END as total_logged_time, from_unixtime(start_time, \'%Y-%m-%d %H:%i:%s\') as start_time, from_unixtime(end_time, \'%Y-%m-%d %H:%i:%s\') as end_time');

		$this->db->order_by('id', 'desc');

		return $this->db->get(db_prefix() . 'taskstimers')->result_array();
	}



	public function get_data_insert_timesheets($staffid, $time_in, $time_out)
	{

		$date_work = date('Y-m-d', strtotime($time_in));

		$work_time = $this->get_hour_shift_staff($staffid, $date_work);

		$affectedrows = 0;

		$hour = 0;

		$late = 0;

		$early = 0;

		$lunch_time = 0;

		if ($work_time > 0 && $work_time != '') {

			$list_shift = $this->get_shift_work_staff_by_date($staffid, $date_work);

			$d1 = strtotime($this->format_date_time($time_in));

			$d2 = strtotime($this->format_date_time($time_out));

			if ($d1 > $d2) {

				$temp = $time_in;

				$time_in = $time_out;

				$time_out = $temp;
			}

			$hour1 = explode(' ', $time_in);

			$hour2 = explode(' ', $time_out);

			$time_in = strtotime($hour1[1]);

			$time_out = strtotime($hour2[1]);

			foreach ($list_shift as $shift) {

				$data_shift_type = $this->timesheets_model->get_shift_type($shift);

				$time_in_ = $time_in;

				$time_out_ = $time_out;

				if ($data_shift_type) {



					$start_work = strtotime($data_shift_type->time_start_work);

					$end_work = strtotime($data_shift_type->time_end_work);

					$start_lunch_break = strtotime($data_shift_type->start_lunch_break_time);

					$end_lunch_break = strtotime($data_shift_type->end_lunch_break_time);

					if ($time_out < $start_work) {

						continue;
					}



					/*
                    making start time and end time outside shift range
					if ($time_in < $start_work && $time_out > $start_work) {

						$time_in_ = $start_work;
					} elseif ($time_in > $start_work && $time_out > $start_work) {

						$late += round(abs($time_in - $start_work) / (60 * 60), 2);
					}



					if ($time_out > $end_work && $time_in < $end_work) {

						$time_out_ = $end_work;
					} elseif ($time_out < $end_work && $time_in < $end_work) {

						$early += round(abs($time_out - $end_work) / (60 * 60), 2);
					}
                    */


					if ($time_out_ >= $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					$hour += round(abs($time_out_ - $time_in_) / (60 * 60), 2);
				}
			}
		}



		$value = abs($hour - $lunch_time);

		$data = [];

		$data['work'] = $value;

		$data['early'] = $early;

		$data['late'] = $late;

		return $data;
	}



	public function import_timesheets($data)
	{

		foreach ($data as $key => $value) {

			$test = $this->check_ts($value['staffid'], date('Y-m-d', strtotime($value['time_in'])));

			if ($test->check_in != 0) {

				$this->db->where('id', $test->check_in);

				$this->db->update(db_prefix() . 'check_in_out', ['date' => $value['time_in']]);
			} else {

				$this->db->insert(
					db_prefix() . 'check_in_out',

					[
						'staff_id' => $value['staffid'],

						'date' => $value['time_in'],

						'type_check' => 1
					]
				);
			}



			if ($test->check_out != 0) {

				$this->db->where('id', $test->check_out);

				$this->db->update(db_prefix() . 'check_in_out', ['date' => $value['time_out']]);
			} else {

				$this->db->insert(
					db_prefix() . 'check_in_out',

					[
						'staff_id' => $value['staffid'],

						'date' => $value['time_out'],

						'type_check' => 2
					]
				);
			}

			$this->automatic_insert_timesheets($value['staffid'], $value['time_in'], $value['time_out']);
		}



		return true;
	}



	/**

	 * delete additional timesheets

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_additional_timesheets($id)
	{



		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_additional_timesheet');

		if ($this->db->affected_rows() > 0) {

			$this->db->where('relate_id', $id);

			$this->db->where('relate_type', 'additional_timesheet');

			$this->db->delete(db_prefix() . 'timesheets_timesheet');

			return true;
		}

		return false;
	}



	/**

	 * delete timesheets attchement file for any

	 *

	 * @param      <type>   $attachment_id  the attachment identifier

	 *

	 * @return     boolean  ( description_of_the_return_value )

	 */

	public function delete_timesheets_attachment_file($attachment_id)
	{

		$deleted = false;

		$attachment = $this->get_timesheets_attachments_delete($attachment_id);

		if ($attachment) {

			if (empty($attachment->external)) {

				unlink(timesheets_module_upload_folder . '/requisition_leave/' . $attachment->rel_id . '/' . $attachment->file_name);
			}

			$this->db->where('id', $attachment->id);

			$this->db->delete(db_prefix() . 'files');

			if ($this->db->affected_rows() > 0) {

				$deleted = true;

				log_activity('attachment deleted [requisition leave id: ' . $attachment->rel_id . ']');
			}



			if (is_dir(timesheets_module_upload_folder . '/requisition_leave/' . $attachment->rel_id)) {

				// check if no attachments left, so we can delete the folder also

				$other_attachments = list_files(timesheets_module_upload_folder . '/requisition_leave/' . $attachment->rel_id);

				if (count($other_attachments) == 0) {

					// okey only index.html so we can delete the folder also

					delete_dir(timesheets_module_upload_folder . '/requisition_leave/' . $attachment->rel_id);
				}
			}
		}



		return $deleted;
	}



	/**

	 * gets the timesheets attachments delete.

	 *

	 * @param      int  $id     the identifier

	 *

	 * @return     object  the timesheets attachments delete.

	 */

	public function get_timesheets_attachments_delete($id)
	{



		if (is_numeric($id)) {

			$this->db->where('id', $id);



			return $this->db->get(db_prefix() . 'files')->row();
		} else {

			return [];
		}
	}



	/**

	 * get list check in/out

	 * @param  $date

	 * @param  string $staffid

	 * @return

	 */

	public function get_list_check_in_out($date, $staffid = '', $route_point_id = '')
	{

		if ($staffid != '') {

			$this->db->where('staff_id', $staffid);
		}

		if ($route_point_id != '') {

			$this->db->where('route_point_id', $route_point_id);
		}

		$this->db->where('date(date) = "' . $date . '"');

		$this->db->order_by('id');

		return $this->db->get(db_prefix() . 'check_in_out')->result_array();
	}

	/**

	 * get ts staff by date

	 * @param  integer $staff_id

	 * @param  date $date_work

	 */

	public function get_ts_staff_by_date($staff_id, $date_work)
	{

		return $this->db->query('select * from ' . db_prefix() . 'timesheets_timesheet where staff_id = ' . $staff_id . ' and date_work = \'' . $date_work . '\'')->result_array();
	}

	/**

	 * merge timesheet

	 * @param  string $string

	 * @param  string $max_hour

	 * @return string

	 */

	public function merge_ts($string, $max_hour, $type_valid)
	{

		if ($string != '') {

			$array = explode(';', $string);

			$list_type = [];

			foreach ($array as $key => $value) {

				$value = trim($value);

				$split = explode(':', $value);

				if (isset($split[0]) && isset($split[1])) {

					if (count($list_type) == 0) {

						array_push($list_type, $split[0]);
					} elseif (!in_array($split[0], $list_type)) {

						array_push($list_type, $split[0]);
					}
				} else {

					if ((isset($split[0]) && ctype_alpha($split[0]) && in_array(strtoupper($split[0]), $type_valid))) {

						return $string;
					}
				}
			}

			$array_result = [];

			foreach ($list_type as $key => $type) {

				$type = str_replace(' ', '', $type);

				$total = 0;

				foreach ($array as $key => $value) {

					$split = explode(':', trim($value));



					if ((isset($split[0]) && ctype_alpha($split[0]) && in_array(strtoupper($split[0]), $type_valid)) && (isset($split[1]) && is_numeric($split[1]))) {

						if (str_replace(' ', '', $split[0]) == $type) {

							$total += $split[1];
						}
					}
				}
				//              commenting it so that it will take hrs beyond the shift
				//				if ($total > $max_hour) {
				//
				//					$total = $max_hour;
				//				}

				if ($total > 0) {

					$array_result[] = $type . ':' . $total;
				}
			}

			if (count($array_result) > 0) {

				return implode('; ', $array_result);
			} else {

				return '';
			}
		} else {

			return '';
		}
	}

	/**

	 * get staff member/s

	 * @param  mixed $id optional - staff id

	 * @param  mixed $where where in query

	 * @return mixed if id is passed return object else array

	 */

	public function get_staff_list($where = '')
	{

		return $this->db->query('select * from ' . db_prefix() . 'staff ' . $where)->result_array();
	}

	/**

	 * gets the go bussiness advance payment.

	 *

	 * @param      <type>  $request_leave  the request leave

	 */

	public function get_go_bussiness_advance_payment($request_leave)
	{

		$this->db->where('requisition_leave', $request_leave);

		return $this->db->get(db_prefix() . 'timesheets_go_bussiness_advance_payment')->result_array();
	}

	/**

	 * advance payment update     * @param  $id   integer

	 * @param  $data array

	 * @return boolean

	 */

	public function advance_payment_update($id, $data)
	{

		$data['amount_received'] = timesheets_reformat_currency_asset($data['amount_received']);

		$data['received_date'] = to_sql_date($data['received_date']);

		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'timesheets_requisition_leave', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * check_holiday

	 * @param  integer $staffid

	 * @param  dagte $date

	 * @return object

	 */

	public function check_holiday($staffid, $date)
	{
		$cache_key = $staffid . '|' . $date;
		if (array_key_exists($cache_key, $this->att_cache_holiday)) {
			return $this->att_cache_holiday[$cache_key];
		}

		$department_id = '';

		$role_id = '';

		if (!array_key_exists($staffid, $this->att_cache_staff_depts)) {
			$this->load->model('departments_model');
			$this->att_cache_staff_depts[$staffid] = $this->departments_model->get_staff_departments($staffid, true);
		}
		$departments = $this->att_cache_staff_depts[$staffid];

		if (!array_key_exists($staffid, $this->att_cache_staff_row)) {
			$this->att_cache_staff_row[$staffid] = $this->staff_model->get($staffid);
		}
		$staff_data = $this->att_cache_staff_row[$staffid];

		if ($departments) {

			$department_id = $departments[0];
		}

		if ($staff_data) {

			$role_id = $staff_data->role;
		}

		// Fast path: match against preloaded day_off rows for the attendance month.
		if (is_array($this->att_day_off_rows)) {
			$data = $this->match_holiday_from_preload($date, $department_id, $role_id);
			$this->att_cache_holiday[$cache_key] = $data;
			return $data;
		}

		$add_query = '';

		if ($department_id != '' && $role_id != '') {

			$add_query .= 'and (find_in_set(' . $departments[0] . ',department) or find_in_set(' . $role_id . ', position))';
		}

		if ($department_id == '' && $role_id != '') {

			$add_query .= 'and find_in_set(' . $role_id . ', position)';
		}

		if ($department_id != '' && $role_id == '') {

			$add_query .= 'and find_in_set(' . $departments[0] . ',department)';
		}

		$data = $this->db->query('select * from ' . db_prefix() . 'day_off where break_date = \'' . $date . '\' ' . $add_query . ' and repeat_by_year = 0 limit 1')->row();

		if (!$data) {

			$data = $this->db->query('select * from ' . db_prefix() . 'day_off where break_date = \'' . $date . '\' and department = \'\' and position = \'\' and repeat_by_year = 0 limit 1')->row();

			if (!$data) {

				$data = $this->db->query('select * from ' . db_prefix() . 'day_off where day(break_date) = day(\'' . $date . '\') and month(break_date) = month(\'' . $date . '\') ' . $add_query . ' and repeat_by_year = 1 limit 1')->row();

				if (!$data) {

					$data = $this->db->query('select * from ' . db_prefix() . 'day_off where day(break_date) = day(\'' . $date . '\') and month(break_date) = month(\'' . $date . '\') and department = \'\' and position = \'\' and repeat_by_year = 1 limit 1')->row();
				}
			}
		}

		$this->att_cache_holiday[$cache_key] = $data;

		return $data;
	}

	/**
	 * Load day_off rows once for attendance grid rendering.
	 */
	public function preload_day_offs_for_attendance($from_date, $to_date)
	{
		$from = $this->db->escape($from_date);
		$to = $this->db->escape($to_date);
		$sql = 'select * from ' . db_prefix() . 'day_off where (repeat_by_year = 0 and break_date between ' . $from . ' and ' . $to . ') or repeat_by_year = 1';
		$this->att_day_off_rows = $this->db->query($sql)->result_array();
	}

	/**
	 * In-memory holiday match (same priority as check_holiday SQL path).
	 */
	protected function match_holiday_from_preload($date, $department_id, $role_id)
	{
		$rows = $this->att_day_off_rows ?: [];
		$day = (int) date('d', strtotime($date));
		$month = (int) date('m', strtotime($date));

		$find_in_set = function ($needle, $haystack) {
			if ($needle === '' || $needle === null) {
				return false;
			}
			$list = array_filter(array_map('trim', explode(',', (string) $haystack)), 'strlen');
			return in_array((string) $needle, $list, true);
		};

		$matches_scope = function ($row) use ($department_id, $role_id, $find_in_set) {
			$has_dept = ($row['department'] ?? '') !== '';
			$has_pos = ($row['position'] ?? '') !== '';
			if (!$has_dept && !$has_pos) {
				return 'global';
			}
			$ok = false;
			if ($has_dept && $department_id !== '' && $find_in_set($department_id, $row['department'])) {
				$ok = true;
			}
			if ($has_pos && $role_id !== '' && $find_in_set($role_id, $row['position'])) {
				$ok = true;
			}
			return $ok ? 'scoped' : false;
		};

		$pick = function ($repeat_by_year, $want_scoped) use ($rows, $date, $day, $month, $matches_scope) {
			foreach ($rows as $row) {
				if ((int) $row['repeat_by_year'] !== (int) $repeat_by_year) {
					continue;
				}
				if ($repeat_by_year) {
					$bd = strtotime($row['break_date']);
					if ((int) date('d', $bd) !== $day || (int) date('m', $bd) !== $month) {
						continue;
					}
				} else {
					if ($row['break_date'] !== $date) {
						continue;
					}
				}
				$scope = $matches_scope($row);
				if ($want_scoped && $scope === 'scoped') {
					return (object) $row;
				}
				if (!$want_scoped && $scope === 'global') {
					return (object) $row;
				}
			}
			return null;
		};

		$data = $pick(0, true);
		if (!$data) {
			$data = $pick(0, false);
		}
		if (!$data) {
			$data = $pick(1, true);
		}
		if (!$data) {
			$data = $pick(1, false);
		}

		return $data;
	}

	/**

	 * get staff email

	 * @param  integer $staffid

	 * @return string $email

	 */

	public function get_staff_email($staffid)
	{

		$this->db->where('staffid', $staffid);

		$this->db->select('email');

		$email = '';

		$data = $this->db->get(db_prefix() . 'staff')->row();

		if ($data) {

			$email = $data->email;
		}

		return $email;
	}

	/**

	 * default settings

	 * @param  array $data

	 * @return boolean

	 */

	public function default_settings($data)
	{

		$affectedrows = 0;

		if (isset($data['attendance_notice_recipient'])) {

			$attendance_notice_recipient = implode(',', $data['attendance_notice_recipient']);

			$this->db->where('option_name', 'attendance_notice_recipient');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $attendance_notice_recipient,

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'attendance_notice_recipient');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['allows_updating_check_in_time'])) {

			$this->db->where('option_name', 'allows_updating_check_in_time');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['allows_updating_check_in_time'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'allows_updating_check_in_time');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['allows_to_choose_an_older_date'])) {

			$this->db->where('option_name', 'allows_to_choose_an_older_date');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['allows_to_choose_an_older_date'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'allows_to_choose_an_older_date');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['allow_attendance_by_coordinates'])) {

			$this->db->where('option_name', 'allow_attendance_by_coordinates');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['allow_attendance_by_coordinates'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'allow_attendance_by_coordinates');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['allow_attendance_by_route'])) {

			$this->db->where('option_name', 'allow_attendance_by_route');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['allow_attendance_by_route'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'allow_attendance_by_route');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['googlemap_api_key'])) {

			$this->db->where('option_name', 'googlemap_api_key');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['googlemap_api_key'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['auto_checkout'])) {

			$this->db->where('option_name', 'auto_checkout');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['auto_checkout'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'auto_checkout');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		if (isset($data['auto_checkout_type'])) {

			$this->db->where('option_name', 'auto_checkout_type');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['auto_checkout_type'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'auto_checkout_type');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['auto_checkout_value'])) {

			$this->db->where('option_name', 'auto_checkout_value');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['auto_checkout_value'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		if (isset($data['send_notification_if_check_in_forgotten'])) {

			$this->db->where('option_name', 'send_notification_if_check_in_forgotten');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['send_notification_if_check_in_forgotten'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'send_notification_if_check_in_forgotten');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['send_notification_if_check_in_forgotten_value'])) {

			$this->db->where('option_name', 'send_notification_if_check_in_forgotten_value');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['send_notification_if_check_in_forgotten_value'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}



		if (isset($data['start_month_for_annual_leave_cycle'])) {

			$this->db->where('option_name', 'start_month_for_annual_leave_cycle');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['start_month_for_annual_leave_cycle'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'start_month_for_annual_leave_cycle');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '1',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['hour_notification_approval_exp'])) {

			$this->db->where('option_name', 'hour_notification_approval_exp');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['hour_notification_approval_exp'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['send_email_check_in_out_customer_location'])) {

			$this->db->where('option_name', 'send_email_check_in_out_customer_location');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['send_email_check_in_out_customer_location'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'send_email_check_in_out_customer_location');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if (isset($data['allow_employees_to_create_work_points'])) {

			$this->db->where('option_name', 'allow_employees_to_create_work_points');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['allow_employees_to_create_work_points'],

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		} else {

			$this->db->where('option_name', 'allow_employees_to_create_work_points');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);

			if ($this->db->affected_rows() > 0) {

				$affectedrows++;
			}
		}

		if ($affectedrows > 0) {

			return true;
		}

		return false;
	}

	/**

	 * notifications

	 * @param  integer id staff

	 * @param  string $link

	 * @param  string $description

	 */

	public function notifications($id_staff, $link, $description)
	{

		$notifiedusers = [];

		$id_userlogin = get_staff_user_id();



		$notified = add_notification([

			'fromuserid' => $id_userlogin,

			'description' => $description,

			'link' => $link,

			'touserid' => $id_staff,

			'additional_data' => serialize([

				$description,

			]),

		]);

		if ($notified) {

			array_push($notifiedusers, $id_staff);
		}

		pusher_trigger_notification($notifiedusers);
	}

	/**

	 * add check in out value to timesheet

	 * @param integer $staff_id 

	 */

	public function add_check_in_out_value_to_timesheet($staff_id, $date)
	{

		$data_check_in_out = $this->get_list_check_in_out($date, $staff_id);

		$check_in_date = '';

		$check_out_date = '';

		$total_work_hours = 0;

		$next_key = '';

		foreach ($data_check_in_out as $key => $value) {

			if ((int) $value['type_check'] === 2) {

				$check_out_date = $value['date'];

				if ($next_key !== '' && (int) $next_key === (int) $key) {

					if ($check_out_date != '' && $check_in_date != '') {

						$data_hour = $this->get_hour($check_in_date, $check_out_date);

						$total_work_hours += $data_hour;
					}
				}
			}

			if ((int) $value['type_check'] === 1) {

				$check_in_date = $value['date'];

				$next_key = $key + 1;
			}
		}

		$data_ts = $this->get_ts_staff($staff_id, $date, 'P');

		$date_work = date('Y-m-d', strtotime($date));
		if (!$date_work || $date_work === '1970-01-01') {
			$date_work = $date;
		}

		$this->load->helper('timesheets/timesheets');
		$present_min = timesheets_present_min_hours();
		$half_day_min = timesheets_half_day_min_hours();

		if ($total_work_hours + 0.001 >= $present_min) {
			if ($data_ts) {

				$this->db->where('id', $data_ts->id);

				$this->db->update(db_prefix() . 'timesheets_timesheet', [

					'value' => $total_work_hours,

					// 'type' => 'W',
					'type' => 'P',


				]);

				if ($this->db->affected_rows() > 0) {

					return true;
				}
			} else {

				$data_insert['staff_id'] = $staff_id;

				// Prefer the attendance date being processed (not a raw punch datetime).
				$data_insert['date_work'] = $date_work;

				// $data_insert['type'] = 'W';
				$data_insert['type'] = 'P';


				$data_insert['add_from'] = ((get_staff_user_id() && get_staff_user_id() != 0 && get_staff_user_id() != '') ? get_staff_user_id() : $staff_id);

				$data_insert['value'] = $total_work_hours;

				$this->db->insert(db_prefix() . 'timesheets_timesheet', $data_insert);

				$insert_id = $this->db->insert_id();

				if ($insert_id) {

					return true;
				}
			}
		} else if ($total_work_hours + 0.001 >= $half_day_min && $total_work_hours + 0.001 < $present_min) {
			if ($data_ts) {

				$this->db->where('id', $data_ts->id);

				$this->db->update(db_prefix() . 'timesheets_timesheet', [

					'value' => $total_work_hours,

					// 'type' => 'W',
					'type' => 'HD',


				]);

				if ($this->db->affected_rows() > 0) {

					return true;
				}
			} else {

				$data_insert['staff_id'] = $staff_id;

				$data_insert['date_work'] = $date_work;

				// $data_insert['type'] = 'W';
				$data_insert['type'] = 'HD';


				$data_insert['add_from'] = ((get_staff_user_id() && get_staff_user_id() != 0 && get_staff_user_id() != '') ? get_staff_user_id() : $staff_id);

				$data_insert['value'] = $total_work_hours;

				$this->db->insert(db_prefix() . 'timesheets_timesheet', $data_insert);

				$insert_id = $this->db->insert_id();
				// Attendance short-day only — do NOT invent leave applications.
				if ($insert_id) {

					return true;
				}
			}
		} else if ($total_work_hours + 0.001 < $half_day_min && $total_work_hours > 0) {
			if ($data_ts) {

				$this->db->where('id', $data_ts->id);

				$this->db->update(db_prefix() . 'timesheets_timesheet', [

					'value' => $total_work_hours,

					// 'type' => 'W',
					'type' => 'AB',


				]);

				if ($this->db->affected_rows() > 0) {

					return true;
				}
			} else {

				$data_insert['staff_id'] = $staff_id;

				$data_insert['date_work'] = $date_work;

				// $data_insert['type'] = 'W';
				$data_insert['type'] = 'AB';


				$data_insert['add_from'] = ((get_staff_user_id() && get_staff_user_id() != 0 && get_staff_user_id() != '') ? get_staff_user_id() : $staff_id);

				$data_insert['value'] = $total_work_hours;

				$this->db->insert(db_prefix() . 'timesheets_timesheet', $data_insert);

				// Attendance code only — do NOT invent leave applications against EL.
				$insert_id = $this->db->insert_id();

				if ($insert_id) {

					return true;
				}
			}
		} else {

			if ($data_ts) {

				$this->db->where('id', $data_ts->id);

				$this->db->delete(db_prefix() . 'timesheets_timesheet');

				if ($this->db->affected_rows() > 0) {

					return true;
				}
			}
		}

		return false;
	}



	/**

	 * get timesheet staff

	 * @param  $date

	 * @param  $staff

	 * @param  $type

	 * @return

	 */

	public function get_ts_staff($staff, $date, $type = '')
	{

		if ($type != '') {

			$this->db->where('type', $type);
		}

		$this->db->where('date_work', $date);

		$this->db->where('staff_id', $staff);

		return $this->db->get(db_prefix() . 'timesheets_timesheet')->row();
	}

	/**

	 * get_client_ip

	 * @return string $ipaddress

	 */

	public function get_client_ip()
	{

		$ipaddress = '';

		if (isset($_SERVER['HTTP_CLIENT_IP'])) {

			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['HTTP_X_FORWARDED'])) {

			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {

			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if (isset($_SERVER['HTTP_FORWARDED'])) {

			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {

			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {

			$ipaddress = 'UNKNOWN';
		}



		return $ipaddress;
	}

	/**

	 * get location

	 * @param  string $ip

	 * @return json

	 */

	public function get_location($ip = '')
	{

		if ($ip == '') {

			$ip = $this->get_client_ip();
		}

		return json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
	}



	/**

	 * get workplace

	 * @param  integer $id

	 * @return object or object array

	 */

	public function get_workplace($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_workplace')->row();
		}

		if ($id == false) {

			return $this->db->get(db_prefix() . 'timesheets_workplace')->result_array();
		}
	}

	/**

	 * add workplace

	 * @param array $data

	 */

	public function add_workplace($data)
	{

		if (!isset($data['default'])) {

			$data['default'] = 0;
		}

		if ($data['default'] == 1) {

			$this->db->where('id != ' . $data['id']);

			$this->db->update(db_prefix() . 'timesheets_workplace', ['default' => 0]);
		} else {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_workplace')->row();

			if (!$data_saved) {

				$data['default'] = 1;
			} else {

				if ($data_saved->id == $data['id']) {

					$data['default'] = 1;
				}
			}
		}

		$this->db->insert(db_prefix() . 'timesheets_workplace', $data);

		return $this->db->insert_id();
	}

	/**

	 * update workplace

	 * @param  array $data

	 * @return boolean

	 */

	public function update_workplace($data)
	{

		if (!isset($data['default'])) {

			$data['default'] = 0;
		}

		if ($data['default'] == 1) {

			$this->db->where('id != ' . $data['id']);

			$this->db->update(db_prefix() . 'timesheets_workplace', ['default' => 0]);
		} else {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_workplace')->row();

			if (!$data_saved) {

				$data['default'] = 1;
			} else {

				if ($data_saved->id == $data['id']) {

					$data['default'] = 1;
				}
			}
		}

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'timesheets_workplace', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete workplace

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_workplace($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_workplace');

		if ($this->db->affected_rows() > 0) {

			$data_assign = $this->get_workplace_assign_by_workplace_id($id);

			foreach ($data_assign as $key => $value) {

				$this->delete_workplace_assign($value['id']);
			}

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_workplace')->row();

			if (!$data_saved) {

				$first_row = $this->db->get(db_prefix() . 'timesheets_workplace')->row();

				$this->db->where('id', $first_row->id);

				$this->db->update(db_prefix() . 'timesheets_workplace', ['default' => 1]);
			}

			return true;
		}

		return false;
	}

	/**

	 * get workplace assign

	 * @param  integer $id

	 * @return object or object array

	 */

	public function get_workplace_assign($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_workplace_assign')->row();
		}

		if ($id == false) {

			return $this->db->get(db_prefix() . 'timesheets_workplace_assign')->result_array();
		}
	}

	/**

	 * add workplace assign

	 * @param array $data

	 */

	public function add_workplace_assign($data)
	{

		$workplace_id = '';

		$affectedrows = 0;

		if (isset($data['workplace_id'])) {

			if ($data['workplace_id'] == '') {

				$this->db->where('default', 1);

				$this->db->select('id');

				$data_wp = $this->db->get(db_prefix() . 'timesheets_workplace')->row();

				$workplace_id = $data_wp->id;
			} else {

				$workplace_id = $data['workplace_id'];
			}
		}

		if ($workplace_id != '') {

			foreach ($data['staffid'] as $key => $staffid) {

				$this->db->where('staffid', $staffid);

				$data_saved = $this->db->get(db_prefix() . 'timesheets_workplace_assign')->row();

				if ($data_saved) {

					$this->db->where('id', $data_saved->id);

					$this->db->update(db_prefix() . 'timesheets_workplace_assign', ['workplace_id' => $workplace_id]);

					if ($this->db->affected_rows() > 0) {

						$affectedrows++;
					}
				} else {



					$this->db->insert(db_prefix() . 'timesheets_workplace_assign', ['staffid' => $staffid, 'workplace_id' => $workplace_id]);

					if (is_numeric($this->db->insert_id())) {

						$affectedrows++;
					}
				}
			}
		}

		if ($affectedrows != 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete workplace assign

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_workplace_assign($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_workplace_assign');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * get workplace assign by workplace id

	 * @param  integer $id

	 * @return object or object array

	 */

	public function get_workplace_assign_by_workplace_id($workplace_id)
	{

		$this->db->where('workplace_id', $workplace_id);

		return $this->db->get(db_prefix() . 'timesheets_workplace_assign')->result_array();
	}

	/**

	 * compute distance

	 * @param  string  $lat1

	 * @param  string  $lng1

	 * @param  string  $lat2

	 * @param  string  $lng2

	 * @param  integer $radius

	 * @return float (m)

	 */

	public function compute_distance($lat1, $lng1, $lat2, $lng2, $radius = 6378137)
	{

		static $x = M_PI / 180;

		$lat1 *= $x;

		$lng1 *= $x;

		$lat2 *= $x;

		$lng2 *= $x;

		$distance = 2 * asin(sqrt(pow(sin(($lat1 - $lat2) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lng1 - $lng2) / 2), 2)));

		return $distance * $radius;
	}

	/**

	 * get current location

	 * @return object

	 */

	public function get_current_location()
	{

		$data = $this->get_location();

		$obj = new stdclass();

		$obj->latitude = '';

		$obj->longitude = '';

		if (isset($data->loc)) {

			$data_latitude_longitude = explode(',', $data->loc);

			if (isset($data_latitude_longitude[0]) && isset($data_latitude_longitude[1])) {

				$obj->latitude = $data_latitude_longitude[0];

				$obj->longitude = $data_latitude_longitude[1];
			}
		}

		return $obj;
	}

	/**

	 * get location staff

	 * @param  integer $staffid

	 * @return integer

	 */

	public function get_location_staff($staffid)
	{

		$latitude = '';

		$longitude = '';

		$distance = '';

		$workplace_id = '';

		$this->db->where('staffid', $staffid);

		$data = $this->db->get(db_prefix() . 'timesheets_workplace_assign')->row();

		if ($data) {

			$data_workplace = $this->get_workplace($data->workplace_id);

			if ($data_workplace) {

				$latitude = $data_workplace->latitude;

				$longitude = $data_workplace->longitude;

				$distance = $data_workplace->distance;

				$workplace_id = $data->workplace_id;
			}
		}

		$obj = new stdClass();

		$obj->latitude = $latitude;

		$obj->longitude = $longitude;

		$obj->distance = $distance;

		$obj->workplace_id = $workplace_id;

		return $obj;
	}

	/**

	 * check attendance by coordinates

	 * @param  integer $staffid

	 * @param  string $cur_latitude

	 * @param  string $cur_longitude

	 * @return integer

	 */

	public function check_attendance_by_coordinates($staffid, $cur_latitude, $cur_longitude)
	{

		$latitude = '';

		$longitude = '';

		$max_distance = '';

		$workplace_id = '';

		$obj = new stdClass();

		$obj->error_code = 0;

		$obj->workplace_id = '';

		$data_location = $this->get_location_staff($staffid);

		if ($data_location) {

			$latitude = $data_location->latitude;

			$longitude = $data_location->longitude;

			$max_distance = $data_location->distance;

			$workplace_id = $data_location->workplace_id;
		}

		if ($latitude != '' && $longitude != '' && $cur_latitude != '' && $cur_longitude != '' && $max_distance != '') {

			$cal_distance = $this->compute_distance($latitude, $longitude, $cur_latitude, $cur_longitude);

			if ($cal_distance <= $max_distance) {

				// Valid distance

				$obj->error_code = 1;

				$obj->workplace_id = $workplace_id;
			} else {

				// Invalid distance

				$obj->error_code = 2;

				$obj->workplace_id = $workplace_id;
			}
		} else {

			$obj->error_code = 3;

			$obj->workplace_id = $workplace_id;
		}

		// No time attendance according to location

		return $obj;
	}



	/**

	 * get ts by date and staff leave

	 * @param  $date

	 * @param  $staff

	 * @return array object

	 */

	public function get_ts_by_date_and_staff_leave($date, $staff)
	{

		$this->db->where('(relate_type = "leave")');

		$this->db->where('date_work', $date);

		$this->db->where('staff_id', $staff);

		return $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();
	}

	/**

	 * delete mass workplace assign

	 * @param  array $data

	 * @return boolean

	 */

	public function delete_mass_workplace_assign($data)
	{

		$affectedrows = 0;

		if (isset($data['mass_delete'])) {

			if ($data['mass_delete'] == 'on') {

				if ($data['check_id'] != '') {

					$list_id = explode(',', $data['check_id']);

					foreach ($list_id as $key => $id) {

						$res = $this->delete_workplace_assign($id);

						if ($res == true) {

							$affectedrows++;
						}
					}
				}
			}
		}

		if ($affectedrows != 0) {

			return true;
		}

		return false;
	}

	/**

	 * get route point

	 * @param  integer $id

	 * @return object or array object

	 */

	public function get_route_point($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_route_point')->row();
		}

		if ($id == false) {

			return $this->db->get(db_prefix() . 'timesheets_route_point')->result_array();
		}
	}



	/**

	 * add route_point

	 * @param array $data

	 */

	public function add_route_point($data)
	{

		if (isset($data['related_to'])) {

			// If related is workplace

			if ($data['related_to'] == 2) {

				$data['related_id'] = $data['related_id2'];
			}
		}

		unset($data['related_id2']);



		if (!isset($data['default'])) {

			$data['default'] = 0;
		}

		if ($data['default'] == 1) {

			$this->db->where('id != ' . $data['id']);

			$this->db->update(db_prefix() . 'timesheets_route_point', ['default' => 0]);
		} else {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

			if (!$data_saved) {

				$data['default'] = 1;
			} else {

				if ($data_saved->id == $data['id']) {

					$data['default'] = 1;
				}
			}
		}

		$this->db->insert(db_prefix() . 'timesheets_route_point', $data);

		return $this->db->insert_id();
	}

	/**

	 * update route_point

	 * @param  array $data

	 * @return boolean

	 */

	public function update_route_point($data)
	{

		if (isset($data['related_to'])) {

			// If related is workplace

			if ($data['related_to'] == 2) {

				$data['related_id'] = $data['related_id2'];
			}
		}

		unset($data['related_id2']);

		if (!isset($data['default'])) {

			$data['default'] = 0;
		}

		if ($data['default'] == 1) {

			$this->db->where('id != ' . $data['id']);

			$this->db->update(db_prefix() . 'timesheets_route_point', ['default' => 0]);
		} else {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

			if (!$data_saved) {

				$data['default'] = 1;
			} else {

				if ($data_saved->id == $data['id']) {

					$data['default'] = 1;
				}
			}
		}

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'timesheets_route_point', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete route_point

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_route_point($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_route_point');

		if ($this->db->affected_rows() > 0) {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

			if (!$data_saved) {

				$first_row = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

				$this->db->where('id', $first_row->id);

				$this->db->update(db_prefix() . 'timesheets_route_point', ['default' => 1]);
			}

			return true;
		}

		return false;
	}

	/**

	 * get route

	 * @param  integer $id

	 * @return object or array object

	 */

	public function get_route($id = false)
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_route')->row();
		}

		if ($id == false) {

			return $this->db->get(db_prefix() . 'timesheets_route')->result_array();
		}
	}



	/**

	 * add route

	 * @param array $data

	 */

	public function add_route($data)
	{

		$data_detail = json_decode($data['data_hanson']);

		$new_rowdb = [];

		foreach ($data_detail as $key => $row) {

			$staffid = $row[0];

			for ($i = 1; $i < count($row) - 2; $i++) {

				$cell_data = $row[$i + 1];

				if ($cell_data != '') {

					$date_col = $data['month'] . '-' . $i;



					// explode route point string to find id by name

					// and create into a row to save database



					$ex_route_point = explode(', ', $cell_data);

					$not_to_be_in_order = true;

					foreach ($ex_route_point as $r_key => $route_point) {

						$name = '';

						$obj = $this->get_content_between_character($route_point, '(', ')');

						if ($obj != '') {

							$name = $obj->remain;

							if ($r_key == 0) {

								$not_to_be_in_order = false;
							}
						} else {

							$name = $route_point;
						}

						$order = 0;

						if ($not_to_be_in_order == false) {

							$order = ($r_key + 1);
						}

						$route_point_id = $this->get_route_point_id_by_name($name);

						if ($route_point_id != '') {

							array_push($new_rowdb, array(

								'staffid' => $staffid,

								'route_point_id' => $route_point_id,

								'date_work' => $date_col,

								'order' => $order,

							));
						}
					}

					// end row

				}
			}

			$this->db->where('staffid', $staffid);

			$this->db->where('date_format(date_work, "%Y-%m") = "' . $data['month'] . '"');

			$this->db->delete(db_prefix() . 'timesheets_route');
		}

		$this->db->insert_batch(db_prefix() . 'timesheets_route', $new_rowdb);

		if (is_numeric($this->db->insert_id())) {

			return true;
		}

		return false;
	}

	/**

	 * get route point id by name

	 * @param  string $name

	 * @return integer

	 */

	public function get_route_point_id_by_name($name)
	{

		$id = '';

		$this->db->where('name', $name);

		$data = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

		if ($data) {

			$id = $data->id;
		}

		return $id;
	}



	/**

	 * update route

	 * @param  array $data

	 * @return boolean

	 */

	public function update_route($data)
	{

		if (isset($data['related_to'])) {

			// If related is workplace

			if ($data['related_to'] == 2) {

				$data['related_id'] = $data['related_id2'];
			}
		}

		unset($data['related_id2']);

		if (!isset($data['default'])) {

			$data['default'] = 0;
		}

		if ($data['default'] == 1) {

			$this->db->where('id != ' . $data['id']);

			$this->db->update(db_prefix() . 'timesheets_route', ['default' => 0]);
		} else {

			$this->db->where('default', 1);

			$data_saved = $this->db->get(db_prefix() . 'timesheets_route')->row();

			if (!$data_saved) {

				$data['default'] = 1;
			} else {

				if ($data_saved->id == $data['id']) {

					$data['default'] = 1;
				}
			}
		}

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'timesheets_route', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}

	/**

	 * delete route

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_route($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_route');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * get content between character

	 * @param  $str

	 * @param  $start_char

	 * @param  $end_char

	 * @return string

	 */

	public function get_content_between_character($str, $start_char, $end_char)
	{

		$start_index = '';

		$end_index = '';

		for ($i = (strlen($str) - 1); $i >= 0; $i--) {

			if ($start_index == '') {

				if ($str[$i] == $end_char) {

					$start_index = $i;
				}
			} else {

				if ($str[$i] == $start_char) {

					$end_index = $i;
				}
			}
		}



		if ($end_index != '' && $start_index != '') {

			$obj = new stdClass();

			$obj->content = substr($str, ($end_index + 1), (($start_index - 1) - $end_index));

			$obj->remain = trim(substr($str, 0, $end_index));

			return $obj;
		}

		return '';
	}



	public function get_route_text($staffid, $date_work, $where = '')
	{

		$this->db->where('staffid', $staffid);

		$this->db->where('date_work', $date_work);

		if ($where != '') {

			$this->db->where($where);
		}

		$this->db->order_by('order', 'ASC');

		$data = $this->db->get(db_prefix() . 'timesheets_route')->result_array();



		$result = '';

		$list_route_id = [];

		$has_order = true;

		foreach ($data as $key => $value) {

			if ($value['route_point_id'] && $value['route_point_id'] != '') {

				$route = $this->get_route_point($value['route_point_id']);

				if ($route) {

					if ($key == 0 && $value['order'] == 0) {

						$has_order = false;
					}

					$result .= $route->name . '' . (($has_order == true) ? ' (' . $value['order'] . ')' : '') . ', ';

					$list_route_id[] = $value['route_point_id'];
				}
			}
		}

		if ($result != '') {

			$result = rtrim($result, ', ');
		}

		$obj = new stdClass();

		$obj->result = $result;

		$obj->list_route_id = $list_route_id;

		return $obj;
	}

	public function get_route_by_fillter($staffid = '', $date_work = '')
	{

		if ($staffid != '') {

			$this->db->where('staffid', $staffid);
		} else {

			$this->db->where('staffid', get_staff_user_id());
		}

		if ($date_work != '') {

			$this->db->where('date_work', $date_work);
		} else {

			$this->db->where('date_work', date('Y-m-d'));
		}

		$this->db->order_by('order', 'ASC');

		return $this->db->get(db_prefix() . 'timesheets_route')->result_array();
	}

	public function get_next_point($staff_id, $date, $user_latitude, $user_longitude)
	{

		$return_id = '';

		$type = 'order';

		$data_route = $this->get_route_by_fillter($staff_id, $date);

		if ($data_route) {

			$unorrdered = false;

			if (($unorrdered == false) && ($data_route[0]['order'] == 0)) {

				$unorrdered = true;
			}

			if ($unorrdered == false) {

				// Route in order



				foreach ($data_route as $key => $rowitem) {

					// Check if current date of this route point not yet check in and check out

					// Return this route point id

					$valid_check = $this->check_full_check_in_out_route_point($date, $staff_id, $rowitem['route_point_id']);

					if ($valid_check == false) {

						$return_id = $rowitem['route_point_id'];

						break;
					}

					if ($valid_check == true) {

						continue;
					}
				}
			} else {

				// Route not in order

				$type = 'unorrdered';

				$list_distance = [];

				$min_distance = 0;

				foreach ($data_route as $key => $rowitem) {

					$data_route_point = $this->get_route_point($rowitem['route_point_id']);

					if ($data_route_point) {

						$latitude = $data_route_point->latitude;

						$longitude = $data_route_point->longitude;

						// Caculate distance

						if ($user_latitude != '' && $user_longitude != '' && $latitude != '' && $longitude != '') {

							$cal_distance = $this->compute_distance($user_latitude, $user_longitude, $latitude, $longitude);

							if ($cal_distance <= $data_route_point->distance) {

								if ($min_distance == 0 && $cal_distance > 0) {

									$min_distance = $cal_distance;

									$return_id = $rowitem['route_point_id'];
								} else {

									if ($cal_distance < $min_distance) {

										$min_distance = $cal_distance;

										$return_id = $rowitem['route_point_id'];
									}
								}
							}
						}
					}
				}
			}
		}

		$obj = new stdClass();

		$obj->type = $type;

		$obj->id = $return_id;

		return $obj;
	}

	/**

	 * check full check in out route point

	 * @param  date $date

	 * @param  integer $staff_id

	 * @param  integer $route_point_id

	 * @return boolean

	 */

	public function check_full_check_in_out_route_point($date, $staff_id, $route_point_id)
	{

		$valid_check = 0;

		$return_id = '';

		$check = $this->get_list_check_in_out($date, $staff_id, $route_point_id);

		$next_key = 0;

		foreach ($check as $key_val => $val) {

			if (($valid_check == 0) && ($val['type_check'] == 1)) {

				$valid_check = 1;

				$next_key = ($key_val + 1);
			}

			if (($next_key == $key_val) && ($valid_check == 1) && ($val['type_check'] == 2)) {

				$valid_check++;

				break;
			}
		}

		if ($valid_check != 2) {

			return false;
		}

		if ($valid_check == 2) {

			// Is full check

			return true;
		}
	}

	/**

	 * check exist route point name

	 * @param  string $name

	 * @return boolean

	 */

	public function check_exist_route_point_name($name, $id = '')
	{

		if ($id != '') {

			$this->db->where('id !=' . $id);
		}

		$this->db->where('name', $name);

		$res = $this->db->get(db_prefix() . 'timesheets_route_point')->row();

		if ($res) {

			return true;
		}

		return false;
	}

	/**

	 * staff at same route

	 * @param  integer $route

	 * @param  integer $staffid

	 * @param  date $date

	 * @return array

	 */

	public function staff_at_same_route($route, $staffid, $date)
	{

		$list_staff = $this->db->query('select distinct(staffid) as staffid from ' . db_prefix() . 'timesheets_route where staffid != ' . $staffid . ' and date_work=\'' . $date . '\'')->result_array();

		$list = [];

		foreach ($list_staff as $key => $staff) {

			$this->db->where('staffid', $staff['staffid']);

			$this->db->where('date_work', $date);

			$this->db->order_by('order', 'ASC');

			$list_date = $this->db->get(db_prefix() . 'timesheets_route')->result_array();

			if ($list_date) {

				$list[] = $list_date;
			}
		}

		$staffid = [];

		$count = count($route);



		$list1 = [];

		$length_test = 0;

		foreach ($list as $key => $value) {

			if (count($value) == $count) {

				$list1[] = $value;
			}
		}



		$list_result = [];

		foreach ($list1 as $lkey => $item) {

			$count_valid = 0;

			$staffid = '';

			foreach ($route as $rkey => $value) {

				if (($value['order'] == $item[$rkey]['order']) && ($value['route_point_id'] == $item[$rkey]['route_point_id'])) {

					$staffid = $item[$rkey]['staffid'];

					$count_valid++;
				}
			}

			if ($count_valid) {

				$list_result[] = $staffid;
			}
		}

		return $list_result;
	}

	/**

	 * get check in out by route point

	 * @param  integer $staff_id

	 * @param  date $date

	 * @param  integer $route_point_id

	 * @return array

	 */

	public function get_check_in_out_by_route_point($staff_id, $date, $route_point_id)
	{

		return $this->db->query('SELECT * FROM ' . db_prefix() . 'check_in_out where date(' . db_prefix() . 'check_in_out.date) = \'' . $date . '\' and staff_id = ' . $staff_id . ' and route_point_id = ' . $route_point_id)->result_array();
	}

	/**

	 * choose approver

	 * @param  array $data

	 * @return boolean

	 */

	public function choose_approver($data)
	{

		$date_send = date('Y-m-d H:i:s');

		$this->delete_approval_details($data['rel_id'], $data['rel_type']);

		$list_staff = $this->staff_model->get();

		$list = [];

		$staff_addedfrom = $data['addedfrom'];

		$sender = get_staff_user_id();

		$data_setting = $this->get_approve_setting($data['rel_type'], false, $staff_addedfrom);

		$row = [];



		$row['notification_recipient'] = isset($data_setting->notification_recipient) ? $data_setting->notification_recipient : null;

		$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $data_setting->number_day_approval . ' day'));

		$row['staffid'] = $data['staffid'];

		$row['date_send'] = $date_send;

		$row['rel_id'] = $data['rel_id'];

		$row['rel_type'] = $data['rel_type'];

		$row['sender'] = $sender;



		$this->db->insert(db_prefix() . 'timesheets_approval_details', $row);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			return true;
		} else {

			return false;
		}
	}



	/**

	 * get attendance task

	 * @param  array $staffs_list

	 * @param  string $month

	 * @param  string $year

	 * @param  string $from_date

	 * @param  string $to_date

	 * @return array

	 */

	public function get_attendance_task($staffs_list, $month = '', $year = '', $from_date = '', $to_date = '')
	{

		$data['staff_row_tk'] = [];

		$data['staff_row_tk_detailt'] = [];



		if ($month != '' && $year != '') {

			$from_date = $year . '-' . $month . '-01';

			$to_date = $year . '-' . $month . '-' . date('t', strtotime($from_date));
		}



		$list_date = $this->get_list_date($from_date, $to_date);

		foreach ($staffs_list as $s) {

			$ts_date = '';

			$ts_ts = '';

			$result_tb = [];



			$taskstimesWhere = '';

			if ($from_date != '' && $to_date != '') {

				$taskstimesWhere = 'IF(end_time IS NOT NULL,(((from_unixtime(start_time, \'%Y-%m-%d\') <= "' . $from_date . '") and FROM_UNIXTIME(end_time, \'%Y-%m-%d\') >= "' . $from_date . '") or (from_unixtime(start_time, \'%Y-%m-%d\') <= "' . $to_date . '" and FROM_UNIXTIME(end_time, \'%Y-%m-%d\') >= "' . $to_date . '") or (from_unixtime(start_time, \'%Y-%m-%d\') > "' . $from_date . '" and FROM_UNIXTIME(end_time, \'%Y-%m-%d\') < "' . $to_date . '")), (from_unixtime(start_time, \'%Y-%m-%d\') <= "' . $from_date . '" or (from_unixtime(start_time, \'%Y-%m-%d\') > "' . $from_date . '" and from_unixtime(start_time, \'%Y-%m-%d\') <= "' . $to_date . '")))';
			} elseif ($from_date != '') {

				$taskstimesWhere = '(from_unixtime(start_time, \'%Y-%m-%d\') >= "' . $from_date . '" or IF(FROM_UNIXTIME(end_time, \'%Y-%m-%d\') IS NOT NULL, FROM_UNIXTIME(end_time, \'%Y-%m-%d\') >= "' . $from_date . '",  1=1))';
			} elseif ($to_date != '') {

				$taskstimesWhere = '(from_unixtime(start_time, \'%Y-%m-%d\') <= "' . $to_date . '" or IF(FROM_UNIXTIME(end_time, \'%Y-%m-%d\') IS NOT NULL,FROM_UNIXTIME(end_time, \'%Y-%m-%d\') <= "' . $to_date . '", 1=1))';
			}

			$list_taskstimers = $this->get_taskstimers($s['staffid'], $taskstimesWhere);

			$dt_ts = [];

			$dt_ts_detail = [];

			$dt_ts = [_l('staff_id') => $s['staffid'], _l('staff') => $s['firstname'] . ' ' . $s['lastname']];

			foreach ($list_date as $key => $value) {

				$maints = strtotime($value);

				$date_s = date('D d', $maints);

				$working_hour = 0;

				foreach ($list_taskstimers as $tk => $task) {

					$startts = strtotime(date('Y-m-d', strtotime($task['start_time'])));

					$endts = strtotime(date('Y-m-d', strtotime($task['end_time'])));

					if (($maints >= $startts) && ($maints <= $endts)) {

						if ($startts == $endts) {

							$working_hour += $task['total_logged_time'];
						} else {

							$hour_in_date = $this->get_hour_range_between_date($task['start_time'], $task['end_time'], $value);

							if (isset($hour_in_date[0]) && isset($hour_in_date[1])) {

								$working_hour += $this->get_hour($value . ' ' . $hour_in_date[0], $value . ' ' . $hour_in_date[1]);
							}
						}
					}
				}



				$check_holiday = $this->check_holiday($s['staffid'], $value);

				$result_lack = '';

				if (!$check_holiday) {

					$has_business_trip = false;

					$ts_lack = '';

					if ($working_hour > 0) {

						// $ts_lack .= 'W:' . round($working_hour, 1) . '; ';
						$ts_lack .= 'P:' . round($working_hour, 1) . '; ';
					}

					$ts_type = $this->get_ts_by_date_and_staff_leave($value, $s['staffid']);

					if ($ts_type) {

						foreach ($ts_type as $kts => $ts_row) {

							if ($ts_row['type'] == 'B') {

								$has_business_trip = true;

								break;
							}

							$ts_lack .= $ts_row['type'] . ':' . round($ts_row['value'], 1) . '; ';
						}
					}



					$total_lack = $ts_lack;

					if ($total_lack != '') {

						$total_lack = rtrim($total_lack, '; ');
					}

					if ($has_business_trip == true) {

						$result_lack = 'B';
					} else {

						$result_lack = $total_lack;
					}
				} else {

					if ($check_holiday->off_type == 'holiday') {

						$result_lack = "HO";
					}

					if ($check_holiday->off_type == 'event_break') {

						$result_lack = "EB";
					}

					if ($check_holiday->off_type == 'unexpected_break') {

						$result_lack = "UB";
					}
				}

				$dt_ts[$date_s] = $result_lack;

				$dt_ts_detail[$value] = $result_lack;
			}

			$data['staff_row_tk'][] = $dt_ts;

			$data['staff_row_tk_detailt'][] = $dt_ts_detail;
		}

		return $data;
	}



	public function get_hour_range_between_date($date1, $date2, $curr_date)
	{

		$list_date = $this->get_list_date(date('Y-m-d', strtotime($date1)), date('Y-m-d', strtotime($date2)));

		$start_hour = date('H:i:s', strtotime($date1));

		$end_hour = date('H:i:s', strtotime($date2));



		$list_hour = [];

		$count = count($list_date);

		foreach ($list_date as $key => $date) {

			if ($date == $curr_date) {

				$list_hour = '';

				if ($key == 0) {

					$list_hour = [$start_hour, '23:59:59'];
				} elseif (($key + 1) == $count) {

					$list_hour = ['00:00:00', $end_hour];
				} else {

					$list_hour = ['00:00:00', '23:59:59'];
				}

				return $list_hour;
			}
		}
	}



	/**

	 * get attendance manual

	 * @param  [type] $staffs_list

	 * @param  string $month

	 * @param  string $year

	 * @param  string $from_date

	 * @param  string $to_date

	 * @return [type]

	 */

	// 

	public function get_attendance_manual($staffs_list, $month = '', $year = '', $from_date = '', $to_date = '')
	{
		$type_valid = ['AL', 'W', 'U', 'HO', 'E', 'L', 'B', 'SI', 'M', 'ME', 'NS', 'P', 'AB', 'HD', 'PL', 'UL', 'SL', 'PHD', 'UHD', 'SHL', 'HL'];

		$data['staff_row_tk'] = [];
		$data['staff_row_tk_detailt'] = [];

		if ($month != '' && $year != '') {
			$from_date = "$year-$month-01";
			$to_date = "$year-$month-" . date('t', strtotime($from_date));
			// Always scope to filtered staff (even admins) — loading the whole month for all staff is very slow.
			$data_ts = $this->get_timesheets_ts_by_month_for_managers_and_users($month, $year, $staffs_list);
		} elseif ($from_date != '' && $to_date != '') {
			$data_ts = $this->get_timesheets_between_date_for_staff_list($from_date, $to_date, $staffs_list);
		} else {
			$data_ts = [];
		}

		$list_date = $this->get_list_date($from_date, $to_date);
		$this->preload_day_offs_for_attendance($from_date, $to_date);
		$data_map = [];

		foreach ($data_ts as $ts) {
			$date_work = $ts['date_work'];
			$staff_id = $ts['staff_id'];
			$date_str = date('D d', strtotime($date_work));

			if (!isset($data_map[$staff_id])) {
				$data_map[$staff_id] = [];
			}

			if (!isset($data_map[$staff_id][$date_str])) {
				$data_map[$staff_id][$date_str] = [];
			}

			$data_map[$staff_id][$date_str][] = $ts;
		}

		foreach ($staffs_list as $s) {
			$staff_id = $s['staffid'];
			$staff_name = $s['firstname'] . ' ' . $s['lastname'];
			$dt_ts = [_l('staff_id') => $staff_id, _l('staff') => $staff_name];
			$dt_ts_detail = [];

			if (isset($data_map[$staff_id])) {
				foreach ($data_map[$staff_id] as $date => $ts_list) {
					$ts_info = [];
				
					foreach ($ts_list as $ts) {
						//echo "<pre>";print_r($ts);
						$type = $ts['type'];
						$value = $ts['value'];
						if ($type == 'HO' || $type == 'M') {
							$ts_info[] = $type;
						} else {
							$ts_info[] = $type . (($value != '' && $value > 0) ? ':' . round($value, 2) : '');
						}
					}
					$dt_ts[$date] = implode('; ', $ts_info);
				}
			}

			foreach ($list_date as $date) {
				$date_s = date('D d', strtotime($date));
				$max_hour = $this->get_hour_shift_staff($staff_id, $date);
				$check_holiday = $this->check_holiday($staff_id, $date);
				$result_lack = '';

				if ($max_hour > 0) {
					if (!$check_holiday) {
						$ts_lack = isset($dt_ts[$date_s]) ? $dt_ts[$date_s] . '; ' : '';
						$total_lack = rtrim($ts_lack, '; ');
						$result_lack = $this->merge_ts($total_lack, $max_hour, $type_valid);
					} else {
						switch ($check_holiday->off_type) {
							case 'holiday':
								$result_lack = 'HO';
								break;
							case 'event_break':
								$result_lack = 'EB';
								break;
							case 'unexpected_break':
								$result_lack = 'UB';
								break;
						}
					}
				} else {
					$result_lack = 'HO';
				}

				$dt_ts[$date_s] = $result_lack;
				$dt_ts_detail[$date] = $result_lack;
			}

			$data['staff_row_tk'][] = $dt_ts;
			$data['staff_row_tk_detailt'][] = $dt_ts_detail;
		}

		return $data;
	}

	/**
	 * Timesheet rows between dates for a staff list (attendance filter scoped).
	 */
	public function get_timesheets_between_date_for_staff_list($from_date, $to_date, $staffs_list)
	{
		$staffids = [];
		foreach ($staffs_list as $staff) {
			$staffids[] = (int) (is_array($staff) ? $staff['staffid'] : $staff->staffid);
		}
		$staffids = array_filter(array_unique($staffids));
		if (!$staffids) {
			return [];
		}
		$in = implode(',', $staffids);
		$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date(date_work) between "' . $this->db->escape_str($from_date) . '" and "' . $this->db->escape_str($to_date) . '" AND staff_id IN (' . $in . ')';
		return $this->db->query($query)->result_array();
	}

	public function get_attendance_manual_export($staffs_list, $month = '', $year = '', $from_date = '', $to_date = '')
	{
		$type_valid = ['AL', 'W', 'U', 'HO', 'E', 'L', 'B', 'SI', 'M', 'ME', 'NS', 'P', 'AB', 'HD', 'PL', 'UL', 'SL', 'PHD', 'UHD', 'SHL', 'HL'];

		$data['staff_row_tk'] = [];
		$data['staff_row_tk_detailt'] = [];

		// Fetch the data based on month/year or date range
		if ($month != '' && $year != '') {
			$from_date = "$year-$month-01";
			$to_date = "$year-$month-" . date('t', strtotime($from_date));
			$data_ts = is_admin() ?
				$this->get_timesheets_ts_by_month($month, $year) :
				$this->get_timesheets_ts_by_month_for_managers_and_users($month, $year, $staffs_list);
		} elseif ($from_date != '' && $to_date != '') {
			$data_ts = is_admin() ?
				$this->get_timesheets_between_date($from_date, $to_date) :
				$this->get_timesheets_between_date_of_staff($from_date, $to_date);
		}

		$list_date = $this->get_list_date($from_date, $to_date);
		$data_map = [];

		// Store dates as 'Y-m-d' to allow sorting
		foreach ($data_ts as $ts) {
			$date_work = $ts['date_work'];
			$staff_id = $ts['staff_id'];
			$date_str = date('Y-m-d', strtotime($date_work)); // Change date key format to 'Y-m-d'

			if (!isset($data_map[$staff_id])) {
				$data_map[$staff_id] = [];
			}

			if (!isset($data_map[$staff_id][$date_str])) {
				$data_map[$staff_id][$date_str] = [];
			}

			$data_map[$staff_id][$date_str][] = $ts;
		}

		foreach ($staffs_list as $s) {
			$staff_id = $s['staffid'];
			$staff_name = $s['firstname'] . ' ' . $s['lastname'];
			
			$dt_ts_detail = [];

			if (isset($data_map[$staff_id])) {
				foreach ($data_map[$staff_id] as $date => $ts_list) {
					$ts_info = [];
					foreach ($ts_list as $ts) {
						$type = $ts['type'];
						$value = $ts['value'];
						if ($type == 'HO' || $type == 'M') {
							$ts_info[] = $type;
						} else {
							$ts_info[] = $type . (($value != '' && $value > 0) ? ':' . round($value, 2) : '');
						}
					}
					$dt_ts[$date] = implode('; ', $ts_info);
				}

				
			}

			foreach ($list_date as $date) {
				$date_s = date('Y-m-d', strtotime($date)); // Store dates as 'Y-m-d'
				$max_hour = $this->get_hour_shift_staff($staff_id, $date);
				$check_holiday = $this->check_holiday($staff_id, $date);
				$result_lack = '';

				if ($max_hour > 0) {
					if (!$check_holiday) {
						$ts_lack = isset($dt_ts[$date_s]) ? $dt_ts[$date_s] . '; ' : '';
						$total_lack = rtrim($ts_lack, '; ');
						$result_lack = $this->merge_ts($total_lack, $max_hour, $type_valid);
					} else {
						switch ($check_holiday->off_type) {
							case 'holiday':
								$result_lack = 'HO';
								break;
							case 'event_break':
								$result_lack = 'EB';
								break;
							case 'unexpected_break':
								$result_lack = 'UB';
								break;
						}
					}
				} else {
					$result_lack = 'HO';
				}

				// Sort the dates to maintain chronological order
				
				$dt_ts[$date_s] = $result_lack;
				$dt_ts_detail[$date] = $result_lack;
			}

			ksort($dt_ts);

			
			// Convert back to 'D d' format for display purposes
			$dt_ts_display = [];
			foreach ($dt_ts as $date_key => $value) {
				$date_display = date('D d', strtotime($date_key)); // Convert back to 'D d'
				$dt_ts_display[$date_display] = $value;
			}

			
			$dt_ts_display = [_l('staff_id') => $staff_id, _l('staff') => $staff_name] + $dt_ts_display;
			
			$data['staff_row_tk'][] = $dt_ts_display;
			$data['staff_row_tk_detailt'][] = $dt_ts_detail;
		}


		
		return $data;
	}




	
	/**

	 * get list month

	 * @param   $from_date

	 * @param   $to_date

	 */

	public function get_list_month($from_date, $to_date)
	{

		$start = new DateTime($from_date);

		$start->modify('first day of this month');

		$end = new DateTime($to_date);

		$end->modify('first day of next month');

		$interval = DateInterval::createFromDateString('1 month');

		$period = new DatePeriod($start, $interval, $end);

		$result = [];

		foreach ($period as $dt) {

			$result[] = $dt->format("Y-m-01");
		}

		return $result;
	}



	/**

	 * get timesheets ts by from date and to date

	 * @param integer $from_date

	 * @param integer $to_date

	 * @return array

	 */

	public function get_timesheets_between_date($from_date, $to_date)
	{

		$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where date(date_work) between "' . $from_date . '" and "' . $to_date . '"';

		return $this->db->query($query)->result_array();
	}


	/**

	 * get timesheets ts by from date and to date of particular staff - created by bhavya

	 * @param integer $from_date

	 * @param integer $to_date

	 * @return array

	 */

	public function get_timesheets_between_date_of_staff($from_date, $to_date)
	{

		$query = 'select * from ' . db_prefix() . 'timesheets_timesheet where staff_id = ' . get_staff_user_id() . ' date(date_work) between "' . $from_date . '" and "' . $to_date . '"';

		return $this->db->query($query)->result_array();
	}

	/**

	 * round to next hour

	 * @param  string $datestring 

	 * @param  integer $max_hour   

	 * @return date             

	 */

	function round_to_next_hour($datestring, $max_hour)
	{

		$nextHour = strtotime($datestring . ' +' . $max_hour . ' hours');

		return date('Y-m-d H:i:s', $nextHour);
	}



	/**

	 * get hour auto checkout type

	 * @param  string $type 

	 * @return array       

	 */

	public function get_hour_auto_checkout_type($type)
	{

		$staffs = $this->get_staff_timekeeping_applicable_object(true);

		$result_list = [];

		$auto_checkout_value = 1;

		$data_auto_checkout_value = get_timesheets_option('auto_checkout_value');

		if ($data_auto_checkout_value) {

			$auto_checkout_value = $data_auto_checkout_value;
		}

		$current_date = date('Y-m-d');

		foreach ($staffs as $skey => $staff) {

			$hour_info = $this->get_info_hour_shift_staff($staff['staffid'], $current_date);

			if ($hour_info && $hour_info->woking_hour > 0) {

				$check_result = $this->check_check_out($staff['staffid'], $current_date);

				if ($hour_info->end_working != '' && $check_result->result) {

					$end_work_hour = $hour_info->end_working;

					$time_checkout = '';

					$start_time = '';

					$time_checkout = $current_date . ' ' . $end_work_hour;

					if ($type == 1) {

						//after x hours of end shift

						$start_time = $time_checkout;
					} elseif ($type == 2) {

						//after checkin +x hour

						$start_time = $check_result->date;
					} else {

						//login time +x hour

						$start_time = $staff['last_login'];
					}

					if ($time_checkout != '' && $start_time != '') {

						$effective_time = $this->round_to_next_hour($start_time, $auto_checkout_value);

						if (strtotime($effective_time) > strtotime($current_date . ' 23:59:59')) {

							$effective_time = $current_date . ' 23:59:59';
						}

						$result_list[] = array(

							'staffid' => $staff['staffid'],

							'checkout_date' => $current_date,

							'time_checkout' => date('Y-m-d H:i:s'),

							'effective_time' => $effective_time,

						);
					}
				}
			}
		}

		return $result_list;
	}



	/**

	 * check check out

	 * @param  integer $staff 

	 * @param  date $date  

	 * @return object        

	 */

	function check_check_out($staff, $date)
	{

		$obj = new stdClass();

		$type_check_in_out = '';

		$data_check_in_out = $this->db->query('select id, type_check, ' . db_prefix() . 'check_in_out.date from ' . db_prefix() . 'check_in_out where staff_id = ' . $staff . ' and date(' . db_prefix() . 'check_in_out.date) = "' . $date . '" order by id desc limit 1')->row();

		if ($data_check_in_out) {

			if ($data_check_in_out->type_check == 2) {

				//Has check out

				$obj->date = $data_check_in_out->date;

				$obj->result = false;
			}

			if ($data_check_in_out->type_check == 1) {

				//Has check in

				$obj->date = $data_check_in_out->date;

				$obj->result = true;
			}
		} else {

			$obj->date = '';

			$obj->result = false;
		}

		return $obj;
	}



	/**

	 * get bussiness trip info

	 * @param  date $date 

	 * @return object       

	 */

	public function get_bussiness_trip_info($date)
	{

		return $this->db->query('select * from ' . db_prefix() . 'timesheets_requisition_leave where rel_type = 4 and status = 1 and "' . $date . '" between date(start_time) and date(end_time)')->row();
	}



	/**

	 * report by working hours

	 */

	public function report_leave_by_department()
	{

		$months_report = $this->input->post('months_report');

		$custom_date_select = '';

		$custom_date_select1 = '';

		$from_date = date('Y-m-01');

		$to_date = date('Y-m-t');

		if ($months_report != '') {

			if (is_numeric($months_report)) {

				// last month

				if ($months_report == '1') {

					$from_date = date('Y-m-01', strtotime('first day of last month'));

					$to_date = date('Y-m-t', strtotime('last day of last month'));
				} else {

					$months_report = (int) $months_report;

					$months_report--;

					$from_date = date('Y-m-01', strtotime("-$months_report month"));

					$to_date = date('Y-m-t');
				}
			} elseif ($months_report == 'this_month') {

				$from_date = date('Y-m-01');

				$to_date = date('Y-m-t');
			} elseif ($months_report == 'this_year') {

				$from_date = date('Y-m-d', strtotime(date('Y-01-01')));

				$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
			} elseif ($months_report == 'last_year') {

				$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));

				$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
			} elseif ($months_report == 'custom') {

				$from_date = to_sql_date($this->input->post('report_from'));

				$to_date = to_sql_date($this->input->post('report_to'));
			}
		}

		$chart = [];

		$dpm = $this->departments_model->get();



		$list_leave_type = [

			['id' => 1, 'name' => _l('Leave')],

			['id' => 2, 'name' => _l('late')],

			['id' => 6, 'name' => _l('early')],

			['id' => 3, 'name' => _l('Go_out')],

			['id' => 4, 'name' => _l('Go_on_bussiness')],

		];



		foreach ($dpm as $d) {

			$chart['categories'][] = $d['name'];
		}



		$list_type = [];

		foreach ($list_leave_type as $type) {

			$list_temp = [];

			foreach ($dpm as $d) {

				$list_temp[] = $this->count_leave_by_department($d['departmentid'], $type['id'], $from_date, $to_date);
			}

			$list_type[] = ['name' => $type['name'], 'data' => $list_temp];
		}

		$chart['series'] = $list_type;

		return $chart;
	}

	/**

	 * count leave by department

	 * @param  string $departmentid

	 * @param  string $type

	 * @param  string $from_date

	 * @param  string $to_date

	 * @return integer $count

	 */

	public function count_leave_by_department($departmentid, $type, $from_date, $to_date)
	{

		$count = 0;

		$query = 'select count(1) as count from ' . db_prefix() . 'timesheets_requisition_leave where rel_type = ' . $type . ' and staff_id in (select staffid from ' . db_prefix() . 'staff_departments where departmentid = ' . $departmentid . ') and ((date(start_time) between "' . $from_date . '" and "' . $to_date . '") or (date(end_time) between "' . $from_date . '" and "' . $to_date . '"))';

		$data = $this->db->query($query)->row();

		if ($data) {

			$count = $data->count;
		}

		return (int) $count;
	}

	/**

	 * report ratio check in out by workplace

	 */

	public function report_ratio_check_in_out_by_workplace()
	{

		$months_report = $this->input->post('months_report');

		$custom_date_select = '';

		$custom_date_select1 = '';

		$from_date = date('Y-m-01');

		$to_date = date('Y-m-t');

		if ($months_report != '') {

			if (is_numeric($months_report)) {

				// last month

				if ($months_report == '1') {

					$from_date = date('Y-m-01', strtotime('first day of last month'));

					$to_date = date('Y-m-t', strtotime('last day of last month'));
				} else {

					$months_report = (int) $months_report;

					$months_report--;

					$from_date = date('Y-m-01', strtotime("-$months_report month"));

					$to_date = date('Y-m-t');
				}
			} elseif ($months_report == 'this_month') {

				$from_date = date('Y-m-01');

				$to_date = date('Y-m-t');
			} elseif ($months_report == 'this_year') {

				$from_date = date('Y-m-d', strtotime(date('Y-01-01')));

				$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
			} elseif ($months_report == 'last_year') {

				$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));

				$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
			} elseif ($months_report == 'custom') {

				$from_date = to_sql_date($this->input->post('report_from'));

				$to_date = to_sql_date($this->input->post('report_to'));
			}
		}

		$chart = [];

		$list_type = [

			['id' => 1, 'name' => _l('check_in')],

			['id' => 2, 'name' => _l('check_out')],

		];



		$data_workplace = $this->timesheets_model->get_workplace();

		foreach ($data_workplace as $d) {

			$chart['categories'][] = $d['name'];
		}



		$list_serial = [];

		foreach ($list_type as $type) {

			$list_temp = [];

			foreach ($data_workplace as $d) {

				$list_temp[] = $this->count_check_by_workplace($d['id'], $type['id'], $from_date, $to_date);
			}

			$list_serial[] = ['name' => $type['name'], 'data' => $list_temp];
		}

		$chart['series'] = $list_serial;

		return $chart;
	}

	/**

	 * count check by workplace

	 * @param  string $workplace_id

	 * @param  string $type

	 * @param  string $from_date

	 * @param  string $to_date

	 * @return integer $count

	 */

	public function count_check_by_workplace($workplace_id, $type, $from_date, $to_date)
	{

		$count = 0;

		$query = 'select count(1) as count from ' . db_prefix() . 'check_in_out where workplace_id = ' . $workplace_id . ' and type_check = ' . $type . ' and date(date) between "' . $from_date . '" and "' . $to_date . '"';

		$data = $this->db->query($query)->row();

		if ($data) {

			$count = $data->count;
		}

		return (int) $count;
	}

	/**

	 * report of leave by month

	 */

	public function report_of_leave_by_month()
	{

		$months_report = $this->input->post('months_report');

		$custom_date_select = '';

		$custom_date_select1 = '';

		$from_date = date('Y-m-01');

		$to_date = date('Y-m-t');

		if ($months_report != '') {

			if (is_numeric($months_report)) {

				// last month

				if ($months_report == '1') {

					$from_date = date('Y-m-01', strtotime('first day of last month'));

					$to_date = date('Y-m-t', strtotime('last day of last month'));
				} else {

					$months_report = (int) $months_report;

					$months_report--;

					$from_date = date('Y-m-01', strtotime("-$months_report month"));

					$to_date = date('Y-m-t');
				}
			} elseif ($months_report == 'this_month') {

				$from_date = date('Y-m-01');

				$to_date = date('Y-m-t');
			} elseif ($months_report == 'this_year') {

				$from_date = date('Y-m-d', strtotime(date('Y-01-01')));

				$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
			} elseif ($months_report == 'last_year') {

				$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));

				$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));
			} elseif ($months_report == 'custom') {

				$from_date = to_sql_date($this->input->post('report_from'));

				$to_date = to_sql_date($this->input->post('report_to'));
			}
		}

		$chart = [];



		$list_month = $this->get_list_month($from_date, $to_date);



		$list_leave_type = [

			['id' => 1, 'name' => _l('Leave')],

			['id' => 2, 'name' => _l('late')],

			['id' => 6, 'name' => _l('early')],

			['id' => 3, 'name' => _l('Go_out')],

			['id' => 4, 'name' => _l('Go_on_bussiness')],

		];



		foreach ($list_month as $d) {

			$dateObj = DateTime::createFromFormat('!m', date('m', strtotime($d)));

			$monthName = $dateObj->format('F');

			$chart['categories'][] = $monthName;
		}



		$list_type = [];

		foreach ($list_leave_type as $type) {

			$list_temp = [];

			foreach ($list_month as $d) {

				$list_temp[] = $this->count_leave_by_month(date('Y-m', strtotime($d)), $type['id']);
			}

			$list_type[] = ['name' => $type['name'], 'data' => $list_temp];
		}

		$chart['series'] = $list_type;

		return $chart;
	}

	/**

	 * count leave by month

	 * @param  string $year_month

	 * @param  string $type

	 * @return  integer $count

	 */

	public function count_leave_by_month($year_month, $type)
	{

		$count = 0;

		$query = 'select count(1) as count from ' . db_prefix() . 'timesheets_requisition_leave where rel_type = ' . $type . ' and "' . $year_month . '" between date_format(start_time, "%Y-%m") and date_format(end_time, "%Y-%m")';

		$data = $this->db->query($query)->row();

		if ($data) {

			$count = $data->count;
		}

		return (int) $count;
	}



	function round_to_next_minutes($datestring, $max_hour)
	{

		$nextHour = strtotime($datestring . ' +' . $max_hour . ' minute');

		return date('Y-m-d H:i:s', $nextHour);
	}



	public function get_datetime_send_notification_forgotten_value($minute)
	{

		$staffs = $this->get_staff_timekeeping_applicable_object(true);

		$result_list = [];

		$current_date = date('Y-m-d');

		foreach ($staffs as $skey => $staff) {

			if (!$this->check_log_send_notify($staff['staffid'], 1, $current_date, 'check_in')) {

				$hour_info = $this->get_info_hour_shift_staff($staff['staffid'], $current_date);

				if ($hour_info && $hour_info->woking_hour > 0) {

					$check_result = $this->has_check_in($staff['staffid'], $current_date);

					if (!$check_result) {

						if ($hour_info->start_working != '') {

							$start_datetime = $current_date . ' ' . $hour_info->start_working;

							$effective_time = $this->round_to_next_minutes($start_datetime, $minute);

							$result_list[] = array(

								'staffid' => $staff['staffid'],

								'effective_time' => $effective_time,

							);
						}
					}
				}
			}
		}

		return $result_list;
	}

	function has_check_in($staff, $date)
	{

		$data_check_in_out = $this->db->query('select id from ' . db_prefix() . 'check_in_out where staff_id = ' . $staff . ' and date(' . db_prefix() . 'check_in_out.date) = "' . $date . '" and type_check = 1 order by id desc limit 1')->row();

		if ($data_check_in_out) {

			return true;
		}

		return false;
	}

	public function send_mail_remider_check_in($staffid)
	{

		$email = $this->get_staff_email($staffid);

		if ($email != '') {

			$this->add_log_send_notify($staffid, 1, date('Y-m-d'), 'check_in');

			$staff_name = get_staff_full_name($staffid);

			$data_send_mail['receiver'] = $email;

			$data_send_mail['staff_name'] = $staff_name;

			$data_send_mail['date_time'] = _d(date('Y-m-d'));

			$template = mail_template('remind_user_check_in', 'timesheets', array_to_object($data_send_mail));

			$template->send();

			$this->notifications($staffid, 'timesheets/timekeeping', _l('remind_you_to_check_in_today_to_record_the_start_time_of_the_shift') . ' ' . _d(date('Y-m-d')));
		}
	}

	public function add_log_send_notify($staffid, $status, $date, $type)
	{

		$this->db->insert(db_prefix() . 'timesheets_log_send_notify', [

			'staffid' => $staffid,

			'sent' => $status,

			'date' => $date,

			'type' => $type,

		]);
	}



	public function check_log_send_notify($staffid, $status, $date, $type)
	{

		$this->db->where('staffid', $staffid);

		$this->db->where('date', $date);

		$this->db->where('sent', $status);

		$this->db->where('type', $type);

		$data = $this->db->get(db_prefix() . 'timesheets_log_send_notify')->row();

		if ($data) {

			return true;
		}

		return false;
	}

	public function get_data_attendance_export($month_filter, $department_filter, $role_filter, $staff_filter)
	{

		$data = $this->input->post();

		$date_ts = $this->format_date($month_filter . '-01');

		$date_ts_end = $this->format_date($month_filter . '-' . date('t'));

		$year = date('Y', strtotime($date_ts));

		$g_month = date('m', strtotime($date_ts));

		$month_filter = date('Y-m', strtotime($date_ts));



		$querystring = 'active=1';

		$department = $department_filter;

		$job_position = $role_filter;



		$month_filter = date('m-Y', strtotime($date_ts));

		$data['check_latch_timesheet'] = $this->check_latch_timesheet($month_filter);

		$staff = '';

		if (isset($staff_filter)) {

			$staff = $staff_filter;
		}

		$staff_querystring = '';

		$job_position_querystring = '';

		$department_querystring = '';

		$month_year_querystring = '';



		if ($department != '') {

			$arrdepartment = $this->staff_model->get('', 'staffid in (select ' . db_prefix() . 'staff_departments.staffid from ' . db_prefix() . 'staff_departments where departmentid = ' . $department . ')');
			
			$temp = '';

			foreach ($arrdepartment as $value) {

				$temp = $temp . $value['staffid'] . ',';
			}

			$temp = rtrim($temp, ",");

			$department_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		}

		if ($job_position != '') {

			$job_position_querystring = 'role = "' . $job_position . '"';
		}

		if ($staff != '') {

			$temp = '';

			$araylengh = count($staff);

			for ($i = 0; $i < $araylengh; $i++) {

				$temp = $temp . $staff[$i];

				if ($i != $araylengh - 1) {

					$temp = $temp . ',';
				}
			}

			$staff_querystring = 'FIND_IN_SET(staffid, "' . $temp . '")';
		} else {

			$data_timekeeping_form = get_timesheets_option('timekeeping_form');



			$timekeeping_applicable_object = [];

			if ($data_timekeeping_form == 'timekeeping_task') {

				if (get_timesheets_option('timekeeping_task_role') != '') {

					$timekeeping_applicable_object = get_timesheets_option('timekeeping_task_role');
				}
			} elseif ($data_timekeeping_form == 'timekeeping_manually') {

				if (get_timesheets_option('timekeeping_manually_role') != '') {

					$timekeeping_applicable_object = get_timesheets_option('timekeeping_manually_role');
				}
			} elseif ($data_timekeeping_form == 'csv_clsx') {

				if (get_timesheets_option('csv_clsx_role') != '') {

					$timekeeping_applicable_object = get_timesheets_option('csv_clsx_role');
				}
			}

			$staff_querystring != '';

			if ($role_filter != '') {

				$staff_querystring .= 'role = ' . $role_filter;
			} else {

				if ($timekeeping_applicable_object) {

					if ($timekeeping_applicable_object != '') {

						$staff_querystring .= 'FIND_IN_SET(role, "' . $timekeeping_applicable_object . '")';
					}
				}
			}
		}



		$arrQuery = array($staff_querystring, $department_querystring, $month_year_querystring, $job_position_querystring, $querystring);

		$newquerystring = '';

		foreach ($arrQuery as $string) {

			if ($string != '') {

				$newquerystring = $newquerystring . $string . ' AND ';
			}
		}



		$newquerystring = rtrim($newquerystring, "AND ");

		if ($newquerystring == '') {

			$newquerystring = [];
		}



		$days_in_month = cal_days_in_month(CAL_GREGORIAN, $g_month, $year);

		if ($year != '') {

			$month_new = (string) $g_month;

			if (strlen($month_new) == 1) {

				$month_new = '0' . $month_new;
			}

			$g_month = $month_new;
		}



		$data['departments'] = $this->departments_model->get();

		$data['staffs_li'] = $this->staff_model->get();


		$data['roles'] = $this->roles_model->get();

		$data['job_position'] = $this->roles_model->get();

		$data['positions'] = $this->roles_model->get();




		$data['shifts'] = $this->get_shifts();



		$data['day_by_month_tk'] = [];

		$data['day_by_month_tk'][] = _l('staff_id');

		$data['day_by_month_tk'][] = _l('staff');



		$data['set_col_tk'] = [];

		$data['set_col_tk'][] = ['data' => _l('staff_id'), 'type' => 'text'];

		$data['set_col_tk'][] = ['data' => _l('staff'), 'type' => 'text', 'readOnly' => true, 'width' => 200];



		for ($d = 1; $d <= $days_in_month; $d++) {

			$time = mktime(12, 0, 0, $g_month, $d, (int) $year);

			if (date('m', $time) == $g_month) {

				array_push($data['day_by_month_tk'], date('D d', $time));

				array_push($data['set_col_tk'], ['data' => date('D d', $time), 'type' => 'text']);
			}
		}



		$data['day_by_month_tk'] = $data['day_by_month_tk'];



		$data_map = [];

		$data_timekeeping_form = get_timesheets_option('timekeeping_form');

		$staff_row_tk = [];

		$staffs = $this->getStaff('', $newquerystring);

		$data['staffs_setting'] = $this->staff_model->get();

		$data['staffs'] = $staffs;

		if ($data_timekeeping_form == 'timekeeping_task' && $data['check_latch_timesheet'] == false) {

			$result = $this->get_attendance_task($staffs, $g_month, $year);

			$staff_row_tk = $result['staff_row_tk'];
		} else {

			if ($data['check_latch_timesheet'] == false) {

				// $result = $this->get_attendance_manual($staffs, $g_month, $year);
				$result = $this->get_attendance_manual_export($staffs, $g_month, $year);

				$staff_row_tk = $result['staff_row_tk'];
			}
		}

		// print_r($staff_row_tk);die;
		return $staff_row_tk;
	}



	/**

	 * automatic insert timesheets

	 * @param      int   $staffid   the staffid

	 * @param      datetime  $time_in   the time in

	 * @param      datetime  $time_out  the time out

	 * @return     boolean

	 */

	public function calculate_attendance_timesheets($staffid, $time_in, $time_out)
	{

		$obj = new stdclass();

		$obj->working_hour = 0;

		$obj->late_hour = 0;

		$obj->early_hour = 0;

		$date_work = date('Y-m-d', strtotime($time_in));

		$work_time = $this->get_hour_shift_staff($staffid, $date_work);

		$affectedrows = 0;

		if ($work_time > 0 && $work_time != '') {

			$list_shift = $this->get_shift_work_staff_by_date($staffid, $date_work);



			$d1 = strtotime($this->format_date_time($time_in));

			$d2 = strtotime($this->format_date_time($time_out));

			if ($d1 > $d2) {

				$temp = $time_in;

				$time_in = $time_out;

				$time_out = $temp;
			}

			$hour1 = explode(' ', $time_in);

			$hour2 = explode(' ', $time_out);

			$time_in = strtotime($hour1[1]);

			$time_out = strtotime($hour2[1]);



			$hour = 0;

			$late = 0;

			$early = 0;

			$lunch_time = 0;

			foreach ($list_shift as $shift) {

				$data_shift_type = $this->timesheets_model->get_shift_type($shift);



				$time_in_ = $time_in;

				$time_out_ = $time_out;



				if ($data_shift_type) {

					$start_work = strtotime($data_shift_type->time_start_work);

					$end_work = strtotime($data_shift_type->time_end_work);

					$start_lunch_break = strtotime($data_shift_type->start_lunch_break_time);

					$end_lunch_break = strtotime($data_shift_type->end_lunch_break_time);

					if ($time_out < $start_work) {

						continue;
					}

					if ($time_out > $start_lunch_break && $time_out < $end_lunch_break) {

						$time_out_ = $start_lunch_break;
					}

					if ($time_in > $start_lunch_break && $time_in < $end_lunch_break) {

						$time_in_ = $end_lunch_break;
					}

					if ($time_in_ < $start_lunch_break && $time_out_ > $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					if ($time_in_ == $start_lunch_break && $time_out_ == $end_lunch_break) {

						continue;
					}

					if ($time_in_ == $start_lunch_break && $time_out_ > $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}

					if ($time_in_ < $start_lunch_break && $time_out_ == $end_lunch_break) {

						$lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					}


					/* this code was making time in an timeout adjust to time shift */

					// if ($time_in < $start_work && $time_out > $start_work) {

					// 	$time_in_ = $start_work;
					// } elseif ($time_in > $start_work && $time_out > $start_work) {

					// 	if ($time_in >= $start_lunch_break && $time_in <= $end_lunch_break) {

					// 		$time_in = $start_lunch_break;
					// 	}

					// 	$lunch_time_s = 0;

					// 	if ($time_in > $end_lunch_break) {

					// 		$lunch_time_s = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					// 	}

					// 	$late += round(abs($time_in - $start_work) / (60 * 60), 2) - $lunch_time_s;
					// }



					// if ($time_out > $end_work && $time_in < $end_work) {

					// 	$time_out_ = $end_work;
					// } elseif ($time_out < $end_work && $time_in < $end_work) {

					// 	if ($time_out >= $start_lunch_break && $time_out <= $end_lunch_break) {

					// 		$time_out = $end_lunch_break;
					// 	}

					// 	$lunch_time_s = 0;

					// 	if ($time_out < $end_lunch_break) {

					// 		$lunch_time_s = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
					// 	}

					// 	$early += round(abs($time_out - $end_work) / (60 * 60), 2) - $lunch_time_s;
					// }

					$hour += round(abs($time_out_ - $time_in_) / (60 * 60), 2);
				}
			}

			$value = abs($hour - $lunch_time);

			$obj->working_hour = $value;

			$obj->late_hour = $late;

			$obj->early_hour = $early;

			return $obj;
		}
	}



	/**

	 * get next shift date

	 * @param  integer  $staff_id

	 * @param  integer  $date

	 * @param  integer $count

	 */

	public function get_next_shift_date($staff_id, $date, $count = 0)
	{

		if ($count == 30) {

			return '';
		}

		$count++;

		$date = $this->format_date($date);


		$data_work_time = $this->get_hour_shift_staff($staff_id, $date);

		$data_day_off = $this->get_day_off_staff_by_date($staff_id, $date);



		if ($data_work_time > 0 && count($data_day_off) == 0) {

			return $date;
		} else {

			$next_date = date('Y-m-d', strtotime($date . ' +1 day'));

			return $this->get_next_shift_date($staff_id, $next_date, $count);
		}
	}



	/**

	 * get date leave

	 * @return object

	 */

	public function get_date_leave()
	{

		$start_month_for_annual_leave_cycle = '1';

		$start_month = get_timesheets_option('start_month_for_annual_leave_cycle');

		if ($start_month) {

			$start_month_for_annual_leave_cycle = $start_month;
		}

		$start_year_for_annual_leave_cycle = date('Y');

		$data_option = get_timesheets_option('start_year_for_annual_leave_cycle');

		if ($data_option) {

			$start_year_for_annual_leave_cycle = $data_option;
		}

		$from_date = $start_year_for_annual_leave_cycle . '-' . (strlen($start_month_for_annual_leave_cycle) == 1 ? '0' : '') . $start_month_for_annual_leave_cycle . '-01';

		$to_date = date('Y-m-d', strtotime('+11 month', strtotime($from_date)));

		$obj = new stdclass();

		$obj->from_date = $from_date;

		$obj->ending_date = date('Y-m', strtotime($to_date)) . '-' . date('t', strtotime($to_date));

		return $obj;
	}



	/**
	 * Leave balance cards (Zoho-style) per leave type for a staff member.
	 *
	 * @param int      $staff_id
	 * @param int|null $year
	 * @return array
	 */
	public function get_staff_leave_balance_cards($staff_id, $year = null, $display_month = null)
	{
		$staff_id = (int) $staff_id;
		if ($staff_id <= 0) {
			return [];
		}

		$year = $year === null ? (int) date('Y') : (int) $year;
		$display_month = $display_month !== null ? (int) $display_month : 0;
		$month = $display_month > 0 ? $display_month : (int) date('m');

		$display_slugs = [
			'loss-of-pay' => 'Loss Of Pay',
			'earned-leave' => 'Earned Leave',
			'restricted-holiday' => 'Restricted Holiday',
		];

		$type_map = [];
		foreach ($this->get_type_of_leave() as $type) {
			$type_map[$type['slug']] = $type['type_name'];
		}

		$consumed_rows = $this->db->query(
			'SELECT type_of_leave, COALESCE(SUM(number_of_leaving_day), 0) AS consumed
			FROM ' . db_prefix() . 'timesheets_requisition_leave
			WHERE staff_id = ? AND status = 1 AND YEAR(start_time) = ?
			GROUP BY type_of_leave',
			[$staff_id, $year]
		)->result_array();

		$consumed_by_type = [];
		foreach ($consumed_rows as $row) {
			$consumed_by_type[$row['type_of_leave']] = (float) $row['consumed'];
		}

		$slug_keys = [];
		foreach ($display_slugs as $slug => $_label) {
			$slug_keys[$slug] = $this->get_leave_type_storage_keys($slug);
		}

		$resolve_consumed = function ($slug) use ($consumed_by_type, $slug_keys) {
			$total = 0;
			foreach ($slug_keys[$slug] as $key) {
				if (isset($consumed_by_type[$key])) {
					$total += (float) $consumed_by_type[$key];
				}
			}
			return $total;
		};

		$this->load->model('staff_model');
		$staff_info = $this->db->select('doj')->where('staffid', $staff_id)->get(db_prefix() . 'staff_info')->row();
		$doj = $staff_info->doj ?? null;

		$day_off_rows = $this->db->where('staffid', $staff_id)
			->where('year', $year)
			->get(db_prefix() . 'timesheets_day_off')
			->result();
		$day_off_by_type = [];
		foreach ($day_off_rows as $row) {
			$day_off_by_type[$row->type_of_leave] = $row;
		}

		$is_resigned = $this->staff_model->staff_has_submitted_resignation($staff_id);
		$category = $this->staff_model->get_employment_category($staff_id);
		$as_of = sprintf('%04d-%02d-%02d', $year, $month, min(28, (int) date('j')));
		$earned_carry = (float) $this->staff_model->carryForward($staff_id, $doj, $month, $year);
		$earned_rate = (float) $this->staff_model->calculateEarnedLeavesFast(
			$doj,
			$staff_id,
			$month,
			$year,
			$is_resigned,
			$category,
			$as_of
		);
		$earned_overrides = $this->staff_model->get_earned_leave_overrides_batch([$staff_id], $month, $year);
		if (isset($earned_overrides[$staff_id])) {
			$earned_rate = (float) $earned_overrides[$staff_id];
		}

		// Always resolve current-month EL consumption so dashboard/apply/leave-balance stay aligned.
		$el_keys = $slug_keys['earned-leave'];
		$this->db->select('COALESCE(SUM(number_of_leaving_day), 0) AS consumed', false)
			->from(db_prefix() . 'timesheets_requisition_leave')
			->where('staff_id', $staff_id)
			->where_in('type_of_leave', $el_keys)
			->where('status', 1)
			->where('MONTH(start_time)', $month, false)
			->where('YEAR(start_time)', $year, false);
		$row_mc = $this->db->get()->row();
		$month_el_consumed = $row_mc ? (float) $row_mc->consumed : 0;

		$cards = [];
		foreach ($display_slugs as $slug => $default_label) {
			if ($slug === 'restricted-holiday' && !isset($type_map[$slug]) && empty($consumed_by_type[$slug])) {
				if (empty($day_off_by_type[$slug])) {
					continue;
				}
			}

			$consumed = $resolve_consumed($slug);
			$day_off = $day_off_by_type[$slug] ?? null;
			$granted = 0;
			$balance = 0;

			$monthly_earn = 0.0;
			if ($slug === 'loss-of-pay') {
				// Same month scope as EL — do not show year LOP as if taken again this month.
				$lop_keys = $slug_keys['loss-of-pay'];
				$this->db->select('COALESCE(SUM(number_of_leaving_day), 0) AS consumed', false)
					->from(db_prefix() . 'timesheets_requisition_leave')
					->where('staff_id', $staff_id)
					->where_in('type_of_leave', $lop_keys)
					->where('status', 1)
					->where('MONTH(start_time)', $month, false)
					->where('YEAR(start_time)', $year, false);
				$row_lop = $this->db->get()->row();
				$consumed = $row_lop ? (float) $row_lop->consumed : 0;
				$granted = 0;
				$balance = round(0 - $consumed, 2);
			} elseif ($slug === 'earned-leave') {
				// Had (carry + this month earn) − taken = remain. Next month = remain + that month's earn.
				$monthly_earn = round((float) $earned_rate, 2);
				$consumed = $month_el_consumed;
				$granted = round((float) $earned_carry + $monthly_earn, 2); // "had" this month
				$balance = $this->staff_model->compute_monthly_leave_balance($earned_carry, $earned_rate, $month_el_consumed);
			} elseif ($day_off && $day_off->total !== null && $day_off->total !== '') {
				$granted = (float) $day_off->total;
				$balance = (float) ($day_off->remain ?? ($granted - $consumed));
			} else {
				$granted = 0;
				$balance = round(0 - $consumed, 2);
				if (in_array($slug, ['comp-off', 'work-from-home'], true)) {
					$balance = 0;
				}
			}

			$progress = 0;
			if ($granted > 0) {
				$progress = min(100, round(($consumed / $granted) * 100));
			} elseif ($consumed > 0) {
				$progress = 100;
			}

			$cards[] = [
				'slug' => $slug,
				'label' => $type_map[$slug] ?? $default_label,
				'granted' => $granted,
				'balance' => $balance,
				'consumed' => $consumed,
				'progress' => $progress,
				'carry_forward' => ($slug === 'earned-leave') ? round((float) $earned_carry, 2) : 0,
				'monthly_earn' => ($slug === 'earned-leave') ? $monthly_earn : 0,
			];
		}

		return $cards;
	}

	/**
	 * Resolve leave balance for apply/save — same numbers as Leave Management cards.
	 *
	 * @return array{balance:float,carry_forward:float,granted:float,consumed:float,slug:string}
	 */
	public function get_synced_leave_balance($staff_id, $type_slug = 'earned-leave', $year = null, $month = null)
	{
		$staff_id = (int) $staff_id;
		$year = $year === null ? (int) date('Y') : (int) $year;
		$month = $month === null ? (int) date('m') : (int) $month;
		$slug = trim((string) $type_slug);
		if ($slug === '' || is_numeric($slug)) {
			$slug = 'earned-leave';
		}
		$aliases = [
			'planned_leaves' => 'earned-leave',
			'present' => 'earned-leave',
			'AL' => 'earned-leave',
			'earned_leave' => 'earned-leave',
			'annual_leave' => 'earned-leave',
			'8' => 'earned-leave',
			'Leave' => 'earned-leave',
			'lop' => 'loss-of-pay',
			'LOP' => 'loss-of-pay',
			'unpaid-leave' => 'loss-of-pay',
			'WFH' => 'work-from-home',
			'wfh' => 'work-from-home',
		];
		if (isset($aliases[$slug])) {
			$slug = $aliases[$slug];
		}

		$cards = $this->get_staff_leave_balance_cards($staff_id, $year, $month);
		$match = null;
		foreach ($cards as $card) {
			if (($card['slug'] ?? '') === $slug) {
				$match = $card;
				break;
			}
		}
		if (!$match) {
			foreach ($cards as $card) {
				if (($card['slug'] ?? '') === 'earned-leave') {
					$match = $card;
					$slug = 'earned-leave';
					break;
				}
			}
		}

		return [
			'slug' => $slug,
			'balance' => (float) ($match['balance'] ?? 0),
			'carry_forward' => (float) ($match['carry_forward'] ?? 0),
			'granted' => (float) ($match['granted'] ?? 0),
			'monthly_earn' => (float) ($match['monthly_earn'] ?? 0),
			'consumed' => (float) ($match['consumed'] ?? 0),
		];
	}

	/**
	 * DB values used in type_of_leave for a leave slug (legacy + custom).
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_leave_type_storage_keys($slug)
	{
		$slug = (string) $slug;
		$keys = [$slug];

		$legacy_map = [
			'earned-leave' => ['8', 'Leave', 'annual_leave', 'planned_leaves'],
			'loss-of-pay' => ['loss-of-pay', 'LOP'],
			'comp-off' => ['comp-off'],
			'work-from-home' => ['work-from-home', 'WFH'],
		];
		if (isset($legacy_map[$slug])) {
			$keys = array_merge($keys, $legacy_map[$slug]);
		}

		$row = $this->db->where('slug', $slug)->get(db_prefix() . 'timesheets_type_of_leave')->row();
		if ($row) {
			$keys[] = (string) $row->id;
			if (!empty($row->slug)) {
				$keys[] = (string) $row->slug;
			}
		}

		return array_values(array_unique(array_filter($keys)));
	}

	/**
	 * Whether staff was employed during a calendar month.
	 */
	protected function staff_employed_in_month($doj, $month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;
		if ($month < 1 || $month > 12 || empty($doj) || $doj === '0000-00-00') {
			return true;
		}
		$doj_ts = strtotime((string) $doj);
		if (!$doj_ts) {
			return true;
		}
		$month_end = strtotime(date('Y-m-t', strtotime("$year-$month-01")));

		return $doj_ts <= $month_end;
	}

	/**
	 * Full leave-type report for "View Details" modal (summary, chart, transactions).
	 *
	 * @param int    $staff_id
	 * @param string $slug
	 * @param int|null $year
	 * @param int|null $display_month  When set (1-12), summary matches leave balance for that month.
	 * @return array|null
	 */
	public function get_staff_leave_type_detail_report($staff_id, $slug, $year = null, $display_month = null)
	{
		$staff_id = (int) $staff_id;
		$year = $year === null ? (int) date('Y') : (int) $year;
		$display_month = $display_month !== null ? (int) $display_month : 0;
		if ($staff_id <= 0 || $slug === '') {
			return null;
		}

		$cards = $this->get_staff_leave_balance_cards(
			$staff_id,
			$year,
			$display_month > 0 ? $display_month : null
		);
		$card = null;
		foreach ($cards as $c) {
			if ($c['slug'] === $slug) {
				$card = $c;
				break;
			}
		}

		$type_map = [];
		foreach ($this->get_type_of_leave() as $type) {
			$type_map[$type['slug']] = $type['type_name'];
		}
		$labels = [
			'loss-of-pay' => 'Loss Of Pay',
			'comp-off' => 'Comp - Off',
			'earned-leave' => 'Earned Leave',
			'restricted-holiday' => 'Restricted Holiday',
			'work-from-home' => 'Work From Home',
		];
		$label = $type_map[$slug] ?? ($labels[$slug] ?? ucwords(str_replace('-', ' ', $slug)));

		$this->load->model('staff_model');
		$staff_info = $this->db->select('doj')->where('staffid', $staff_id)->get(db_prefix() . 'staff_info')->row();
		$doj = $staff_info->doj ?? null;
		$current_month = (int) date('m');
		$current_year = (int) date('Y');
		// Never show next/future month leave given — cap at today for the current year.
		if ($year > $current_year) {
			$through_month = 0;
		} elseif ($year === $current_year) {
			$requested = $display_month > 0 ? $display_month : $current_month;
			$through_month = min($requested, $current_month);
		} else {
			$through_month = $display_month > 0 ? $display_month : 12;
		}
		$type_keys = $this->get_leave_type_storage_keys($slug);

		$opening_balance = 0;
		$granted = $card ? (float) $card['granted'] : 0;
		$availed = 0;
		$available = $card ? (float) $card['balance'] : 0;
		$monthly_earn_rate = $card ? (float) ($card['monthly_earn'] ?? 0) : 0;

		$monthly_consumed = $this->db->query(
			'SELECT MONTH(start_time) AS m, COALESCE(SUM(number_of_leaving_day), 0) AS consumed
			FROM ' . db_prefix() . 'timesheets_requisition_leave
			WHERE staff_id = ? AND status = 1 AND YEAR(start_time) = ?
			AND type_of_leave IN (' . implode(',', array_fill(0, count($type_keys), '?')) . ')
			GROUP BY MONTH(start_time)',
			array_merge([$staff_id, $year], $type_keys)
		)->result_array();
		$consumed_by_month = [];
		foreach ($monthly_consumed as $row) {
			$consumed_by_month[(int) $row['m']] = (float) $row['consumed'];
		}

		if ($slug === 'earned-leave') {
			// Month-scoped summary: opening (had from prior close) + this month earn − taken = remain.
			$opening_balance = $through_month > 0
				? (float) $this->staff_model->carryForward($staff_id, $doj, $through_month, $year)
				: 0;
			$month_rate = 0.0;
			if ($through_month > 0 && $this->staff_employed_in_month($doj, $through_month, $year)) {
				$overrides = $this->staff_model->get_earned_leave_overrides_batch([$staff_id], $through_month, $year);
				$month_rate = isset($overrides[$staff_id])
					? (float) $overrides[$staff_id]
					: (float) $this->staff_model->calculateEarnedLeaves($doj, $staff_id, $through_month, $year);
			}
			$granted = round($month_rate, 2);
			$monthly_earn_rate = $granted;
			$availed = round((float) ($consumed_by_month[$through_month] ?? 0), 2);
			$available = $through_month > 0
				? (float) $this->staff_model->monthlyLeaveBalance($staff_id, $doj, $through_month, $year)
				: 0;
			// Force proper maths: remain = opening + granted − taken (same as card "had − taken").
			$available = round($opening_balance + $granted - $availed, 2);
		} else {
			foreach ($consumed_by_month as $m => $val) {
				if ((int) $m <= $through_month) {
					$availed += (float) $val;
				}
			}
			$availed = round($availed, 2);
			$day_off = $this->db->where('staffid', $staff_id)->where('year', $year)->where('type_of_leave', $slug)->get(db_prefix() . 'timesheets_day_off')->row();
			if ($day_off && $day_off->total !== null && $day_off->total !== '') {
				$granted = (float) $day_off->total;
				$available = (float) ($day_off->remain ?? ($granted - $availed));
			}
		}

		$lapsed = round(max(0, $opening_balance + $granted - $availed - $available), 2);

		$monthly = [];
		$year_opening = $slug === 'earned-leave'
			? (float) $this->staff_model->carryForward($staff_id, $doj, 1, $year)
			: 0;
		$running_balance = $year_opening;
		$month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		for ($m = 1; $m <= 12; $m++) {
			$month_granted = 0;
			$consumed = 0;
			$balance_end = null;
			$opening_m = null;
			$is_elapsed = ($m <= $through_month);

			if ($is_elapsed) {
				$consumed = $consumed_by_month[$m] ?? 0;
				if ($slug === 'earned-leave' && $this->staff_employed_in_month($doj, $m, $year)) {
					$opening_m = $running_balance;
					$overrides = $this->staff_model->get_earned_leave_overrides_batch([$staff_id], $m, $year);
					$month_granted = isset($overrides[$staff_id])
						? (float) $overrides[$staff_id]
						: (float) $this->staff_model->calculateEarnedLeaves($doj, $staff_id, $m, $year);
					// closing = opening + granted − consumed
					$running_balance = round($opening_m + $month_granted - $consumed, 2);
					$balance_end = $running_balance;
				} elseif ($slug === 'earned-leave') {
					$opening_m = $running_balance;
					$balance_end = $running_balance;
				} elseif ($slug !== 'earned-leave' && $m === 1 && $granted > 0) {
					$opening_m = 0;
					$month_granted = $granted;
					$running_balance = round($granted - $consumed, 2);
					$balance_end = $running_balance;
				} elseif ($slug !== 'earned-leave') {
					$opening_m = $running_balance;
					$running_balance = round($running_balance - $consumed, 2);
					$balance_end = $running_balance;
				}
			}

			$monthly[] = [
				'month' => $m,
				'label' => $month_names[$m - 1] . ' ' . substr((string) $year, -2),
				'full_label' => date('F Y', mktime(0, 0, 0, $m, 1, $year)),
				'opening' => $opening_m !== null ? round($opening_m, 2) : null,
				'granted' => round($month_granted, 2),
				'consumed' => round($consumed, 2),
				'balance' => $balance_end,
				'is_elapsed' => $is_elapsed,
			];
		}

		// Full year-to-date leave applications (all months up to through_month).
		$transactions_query = $this->db->select('id, start_time, end_time, number_of_leaving_day, status, reason, subject, datecreated, type_of_leave_text')
			->from(db_prefix() . 'timesheets_requisition_leave')
			->where('staff_id', $staff_id)
			->where_in('type_of_leave', $type_keys)
			->where('YEAR(start_time)', $year, false);
		if ($through_month > 0 && $through_month < 12) {
			$transactions_query->where('MONTH(start_time) <=', $through_month, false);
		}
		$transactions = $transactions_query
			->order_by('datecreated', 'DESC')
			->order_by('id', 'DESC')
			->limit(200)
			->get()
			->result_array();

		foreach ($transactions as &$tx) {
			$tx['transaction_type'] = $this->leave_transaction_type_label($tx['status']);
			$tx['posted_on'] = !empty($tx['datecreated']) ? $tx['datecreated'] : $tx['start_time'];
			$tx['remarks'] = trim((string) ($tx['subject'] ?? ''));
			if ($tx['remarks'] === '' && !empty($tx['type_of_leave_text'])) {
				$tx['remarks'] = trim((string) $tx['type_of_leave_text']);
			}
		}
		unset($tx);

		// Earned Leave: one grant / carry-forward row per month (Jan → through_month).
		if ($slug === 'earned-leave') {
			$history = $this->build_earned_leave_history_transactions(
				$staff_id,
				$year,
				$through_month,
				$opening_balance,
				$doj
			);
			$transactions = array_merge($history, $transactions);
			usort($transactions, function ($a, $b) {
				$ta = strtotime($a['posted_on'] ?? $a['start_time'] ?? '') ?: 0;
				$tb = strtotime($b['posted_on'] ?? $b['start_time'] ?? '') ?: 0;
				if ($ta === $tb) {
					return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
				}
				return $tb <=> $ta;
			});
		}

		$period_label = (string) $year;
		if ($through_month > 0) {
			$period_label = $through_month === 1
				? date('F Y', mktime(0, 0, 0, 1, 1, $year))
				: ('Jan – ' . date('M Y', mktime(0, 0, 0, $through_month, 1, $year)));
		}

		return [
			'slug' => $slug,
			'label' => $label,
			'year' => $year,
			'display_month' => $display_month,
			'through_month' => $through_month,
			'period_label' => $period_label,
			'summary' => [
				'available_balance' => round($available, 2),
				'opening_balance' => round($opening_balance, 2),
				'granted' => round($granted, 2),
				'monthly_earn' => round($monthly_earn_rate, 2),
				'had' => round($opening_balance + $granted, 2),
				'availed' => round($availed, 2),
				'lapsed' => $lapsed,
			],
			'monthly' => $monthly,
			'transactions' => $transactions,
		];
	}

	/**
	 * Synthetic grant / carry-forward rows for Earned Leave history (month-to-month).
	 *
	 * @param int      $staff_id
	 * @param int      $year
	 * @param int      $through_month  Inclusive month end (1-12)
	 * @param float    $opening_balance  Carry into January
	 * @param string|null $doj
	 * @return array
	 */
	protected function build_earned_leave_history_transactions($staff_id, $year, $through_month, $opening_balance, $doj)
	{
		$staff_id = (int) $staff_id;
		$year = (int) $year;
		$through = (int) $through_month;
		$rows = [];

		if ($through <= 0) {
			return $rows;
		}

		$default_rate = (float) $this->staff_model->calculateEarnedLeaves($doj, $staff_id, 1, $year);
		for ($m = 1; $m <= $through; $m++) {
			if (!$this->staff_employed_in_month($doj, $m, $year)) {
				continue;
			}
			$month_start = sprintf('%04d-%02d-01 00:00:00', $year, $m);
			$overrides = $this->staff_model->get_earned_leave_overrides_batch([$staff_id], $m, $year);
			$month_granted = isset($overrides[$staff_id])
				? (float) $overrides[$staff_id]
				: (float) $this->staff_model->calculateEarnedLeaves($doj, $staff_id, $m, $year);
			if ($m === 1 && (float) $opening_balance > 0) {
				$rows[] = [
					'id' => 0,
					'start_time' => $month_start,
					'end_time' => $month_start,
					'number_of_leaving_day' => round((float) $opening_balance, 2),
					'status' => 10,
					'reason' => 'Brought forward from previous year/month',
					'subject' => 'Carry Forward',
					'datecreated' => $month_start,
					'type_of_leave_text' => '',
					'transaction_type' => 'Carry Forward',
					'posted_on' => $month_start,
					'remarks' => 'Opening balance',
				];
			}
			if ($month_granted > 0) {
				$rows[] = [
					'id' => 0,
					'start_time' => $month_start,
					'end_time' => $month_start,
					'number_of_leaving_day' => round($month_granted, 2),
					'status' => 11,
					'reason' => 'Monthly earned leave credit',
					'subject' => 'Monthly accrual',
					'datecreated' => $month_start,
					'type_of_leave_text' => '',
					'transaction_type' => 'Granted',
					'posted_on' => $month_start,
					'remarks' => date('F Y', mktime(0, 0, 0, $m, 1, $year)) . ' accrual',
				];
			}
		}

		return $rows;
	}

	/**
	 * @param int|string $status
	 * @return string
	 */
	public function leave_transaction_type_label($status)
	{
		switch ((int) $status) {
			case 0:
				return 'Pending';
			case 1:
				return 'Availed';
			case 2:
				return 'Rejected';
			case 3:
				return 'Withdrawn';
			case 10:
				return 'Carry Forward';
			case 11:
				return 'Granted';
			default:
				return 'Absent';
		}
	}

	/**
	 * Approved leave rows for one leave type (card "View Details").
	 *
	 * @param int    $staff_id
	 * @param string $slug
	 * @param int    $year
	 * @return array
	 */
	public function get_staff_leave_type_details($staff_id, $slug, $year = null)
	{
		$staff_id = (int) $staff_id;
		$year = $year === null ? (int) date('Y') : (int) $year;
		if ($staff_id <= 0 || $slug === '') {
			return [];
		}

		return $this->db->select('start_time, end_time, number_of_leaving_day, status')
			->from(db_prefix() . 'timesheets_requisition_leave')
			->where('staff_id', $staff_id)
			->where('type_of_leave', $slug)
			->where('status', 1)
			->where('YEAR(start_time)', $year, false)
			->order_by('start_time', 'DESC')
			->limit(20)
			->get()
			->result_array();
	}

	/**

	 * get current date off

	 * @param  integer $staff_id

	 * @return integer

	 */

	public function get_current_date_off($staff_id, $type_of_leave = '')
	{

		$obj = new stdclass();

		$obj->number_day_off = 0;

		$obj->days_off = 0;

		$data_date = $this->get_date_leave();

		$current_date = date('Y-m-d');

		$year = date('Y');

		if ((strtotime($current_date) >= strtotime($data_date->from_date)) && (strtotime($current_date) <= strtotime($data_date->ending_date))) {

			$day_off = $this->get_day_off($staff_id, $year, $type_of_leave);

			if ($day_off != null) {

				$obj->number_day_off = $day_off->remain;

				/*if ($obj->number_day_off < 0) {

					$obj->number_day_off = 0;
				}*/

				$obj->days_off = $day_off->days_off;

				if ($obj->days_off > $day_off->total) {

					$obj->days_off = $day_off->total;
				}
			}
		}

		return $obj;
	}



	/**

	 * get list leave application

	 * @param  array $data

	 */

	public function get_list_leave_application($data)
	{

		//$from_date = $data['start'];
		$from_date = $data['start'];
		$to_date = $data['end'];

		$user_id = get_staff_user_id();

		$where = '';



		if (!is_admin() && !is_HR() && !is_super_hr() && !has_permission('leave_management', '', 'view')) {

			$add_query = ' IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 8,"Leave",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 2,"maternity_leave",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 4,"private_work_without_pay",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 1,"sick_leave", IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 0,' . db_prefix() . 'timesheets_requisition_leave.type_of_leave_text,"")))))';



			$type_of_leave = 'IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 1,' . $add_query . ', IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 2,"late", IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 3,"Go_out", IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 4,"Go_on_bussiness", IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 5,"quit_job", IF(' . db_prefix() . 'timesheets_requisition_leave.rel_type = 6,"early",""))))))';

			$where = ' AND ((' . $user_id . ' in (select staffid from ' . db_prefix() . 'timesheets_approval_details where rel_type = ' . $type_of_leave . ' and rel_id = ' . db_prefix() . 'timesheets_requisition_leave.id) or ' . db_prefix() . 'timesheets_requisition_leave.staff_id = ' . $user_id . ')' . timesheet_staff_manager_query('leave_management', 'staff_id', 'OR') . ')';
		}



		if (isset($data['status'])) {

			$where_status = '';

			$status = $data['status'];

			foreach ($status as $statues) {

				if ($status != '') {

					if ($where_status == '') {

						$where_status .= ' AND (status = "' . $statues . '"';
					} else {

						$where_status .= ' OR status = "' . $statues . '"';
					}
				}
			}

			if ($where_status != '') {

				$where_status .= ')';

				$where .= $where_status;
			}
		}



		if (isset($data['department'])) {

			$where_dpm = '';

			$department = $data['department'];

			foreach ($department as $statues) {

				if ($department != '') {

					if ($where_dpm == '') {

						$where_dpm = ' AND (staff_id IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ' . $statues . ')';
					} else {

						$where_dpm .= ' OR staff_id IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ' . $statues . ')';
					}
				}
			}

			if ($where_dpm != '') {

				$where_dpm .= ')';

				$where .= $where_dpm;
			}
		}

		if (isset($data['rel_type'])) {

			$where_rel_type = '';

			$rel_type = $data['rel_type'];

			foreach ($rel_type as $statues) {

				if ($rel_type != '') {

					if ($where_rel_type == '') {

						$where_rel_type .= ' AND (rel_type = "' . $statues . '"';
					} else {

						$where_rel_type .= ' OR rel_type = "' . $statues . '"';
					}
				}
			}

			if ($where_rel_type != '') {

				$where_rel_type .= ')';

				$where .= $where_rel_type;
			}
		}

		if (isset($data['chose'])) {

			$chose = $data['chose'];

			$sql_where = '';

			if ($chose != 'all') {

				$sql_where .= ' AND (' . get_staff_user_id() . ' IN (SELECT staffid FROM ' . db_prefix() . 'timesheets_approval_details where ' . db_prefix() . 'timesheets_approval_details.rel_type IN ("Leave","maternity_leave","private_work_without_pay","sick_leave","late","early","Go_out","Go_on_bussiness") AND ' . db_prefix() . 'timesheets_approval_details.rel_id = ' . db_prefix() . 'timesheets_requisition_leave.id ))';
			}

			if ($sql_where != '') {

				$where .= $sql_where;
			}
		}


		//echo 'select * from ' . db_prefix() . 'timesheets_requisition_leave where ((date(start_time) between "' . $from_date . '" and "' . $to_date . '") or (date(end_time) between "' . $from_date . '" and "' . $to_date . '"))' . $where;die;

		$total_query = 'select * from ' . db_prefix() . 'timesheets_requisition_leave where ((date(start_time) between "' . $from_date . '" and "' . $to_date . '") or (date(end_time) between "' . $from_date . '" and "' . $to_date . '"))' . $where;

		return $this->db->query($total_query)->result_array();
	}



	/**

	 * get calendar leave data

	 * @param  array $data

	 * @return array

	 */

	public function get_calendar_leave_data_bkp($data)
	{

		$data_calendar = [];

		$data_leave_application = $this->get_list_leave_application($data);


		foreach ($data_leave_application as $data_row) {

			$staff = '';

			if ($data_row['staff_id'] != '') {

				$staff = get_staff_full_name($data_row['staff_id']);
			}

			$from_date = date('Y-m-d', strtotime($data_row['start_time']));

			$to_date = date('Y-m-d', strtotime($data_row['end_time']));

			$color = "#43c2e8";

			if ($data_row['status'] == 1) {

				$color = "#60c259";
			}

			if ($data_row['status'] == 2) {

				$color = "#db5c53";
			}



			$list_date = $this->get_list_date($from_date, $to_date);

			foreach ($list_date as $date) {

				if ($this->valid_date($data_row['staff_id'], $date)) {

					$data_calendar[] = [

						'title' => $data_row['subject'],

						'color' => $color,

						'_tooltip' => $data_row['subject'] . ' -' . _l('staff') . ': ' . $staff . ' -' . _l('From_Date') . ': ' . _d($from_date) . ' -' . _l('To_Date') . ': ' . _d($to_date),

						'url' => admin_url('timesheets/requisition_detail/' . $data_row['id']),

						'date' => $date,

						'start' => $date,

						'end' => $date,

					];
				}
			}
		}

		return $data_calendar;
	}

	// Function to get all the dates in given range
	/*	public function getDatesFromRange($start, $end, $format = 'Y-m-d'){
			
			// Declare an empty array
			$array = array();
			
			// Variable that store the date interval
			// of period 1 day
			$interval = new DateInterval('P1D');
			$realEnd = new DateTime($end);
			$realEnd->add($interval);
			$period = new DatePeriod(new DateTime($start), $interval, $realEnd);
			
			// Use loop to store date into array
			foreach($period as $date){
				$array[] = $date->format($format);

			}
			
			// Return the array elements
			return $array;

		}*/

	public function check_in_out_find($start_time, $end_time)
	{
		$start = $start_time ?: ($this->input->get('start') ?? '');
		$end = $end_time ?: ($this->input->get('end') ?? '');

		if ($start === '' || $end === '') {
			return [];
		}

		$st_date = date('Y-m-d', strtotime($start));
		$en_date = date('Y-m-d', strtotime($end));

		if (!$st_date || !$en_date || $st_date === '1970-01-01' || $en_date === '1970-01-01') {
			return [];
		}

		$attendance_data = $this->db->query(
			'SELECT * FROM ' . db_prefix() . 'check_in_out WHERE DATE(date) BETWEEN ? AND ? AND staff_id = ?',
			[$st_date, $en_date, get_staff_user_id()]
		)->result_array();

		return $attendance_data ?: [];
	}



	// Function to get all the dates in given range
	public function getDatesFromRange($start, $end, $format = 'Y-m-d')
	{

		// Declare an empty array
		$array = array();

		// Variable that store the date interval
		// of period 1 day
		$interval = new DateInterval('P1D');
		$realEnd = new DateTime($end);
		$realEnd->add($interval);
		$period = new DatePeriod(new DateTime($start), $interval, $realEnd);

		// Use loop to store date into array
		foreach ($period as $date) {
			$array[] = $date->format($format);
		}

		// Return the array elements
		return $array;
	}
		public function get_calendar_leave_data($data)
	{
		//print_r($_GET);
		//echo "<pre>";
		//print_r($_POST);
		$user_id = get_staff_user_id();
		//print_r($user_id);
		$data_calendar = [];

		$data_leave_application = $this->get_list_leave_application($data);
		

		$start = $_GET['start'];
		$start_s = explode('T', $start);
		$start_date = $start_s[0];
		$end = $_GET['end'];
		$end_s = explode('T', $end);
		$end_date = $end_s[0];
		//echo 'select * from tblholiday where saturday_date between "' . $start_date . '" AND "' . $end_date . '" AND staffid="'.$user_id.'"';die;
		//echo 'select * from tblday_off where break_date between "' . $start_date . '" AND "' . $end_date . '"';die;'
		
		$attendance_data = $this->db->query('select * from tbltimesheets_timesheet where date_work between "' . $start_date . '" AND "' . $end_date . '" AND staff_id = ' . get_staff_user_id())->result_array();
		$attendance_holiday = $this->db->query('select * from tblday_off where break_date between "' . $start_date . '" AND "' . $end_date . '"')->result_array();
		
		//echo ''select * from tblholiday where saturday_date between "' . $start_date . '" AND "' . $end_date . '" AND staffid="'.$user_id.'"'';die;
		$attendance_sat_holiday = $this->db->query('select * from tblholiday where saturday_date between "' . $start_date . '" AND "' . $end_date . '" AND staffid="'.$user_id.'"')->result_array();
		
		//print_r($attendance_sat_holiday);
		//echo !empty($attendance_sat_holiday);
		

		//$attendance_data_leave = $this->db->query('select * from tbltimesheets_requisition_leave where staff_id = ' . get_staff_user_id())->result_array();
		//$attendance_datas = $this->db->query('select b.id,b.staff_id,b.date_work,b.value,b.type,b.add_from,a.id,a.staff_id,a.subject,a.start_time,a.end_time,a.reason,a.type_of_leave,a.number_of_days,a.followers_id,a.status,a.type_of_leave,a.number_of_leaving_day,a.type_of_leave_text,a.carry_forward,a.leave_balance,c.approval_comment,c.rejection_comment,c.staff_id,c.leave_id from tbltimesheets_timesheet as b inner join tbltimesheets_requisition_leave as a on b.staff_id = a.staff_id left join tblleave_comment as c on c.leave_id = a.id where b.date_work = a.start_time AND b.staff_id = ' . get_staff_user_id())->result_array();
		//echo '<pre>'; print_r($attendance_data);
		 $working_hours = 'Working Hours - ';
		 
		 if(empty($attendance_holiday)){
			$data_calendars_holiday[] = [

				'title' => '',

				'color' => '',



				// 'url' => admin_url('timesheets/requisition_detail/' . $data_row['id']),

				//'_tooltip' => 'Working Hours : ' . $data_row['value'],
				//'date' => $date,
				'start' => '',

				'end' => '',

			];
		 }else{
		foreach ($attendance_holiday as $datas) {
			
			$color = '#2ECC71';

			$type_of_leave = $datas['off_type'];
			$fes_name = $datas['off_reason'];
			$start_date = $datas['break_date'];
			//print_r($attendance_data);
			if ($type_of_leave == 'holiday') {
				$type_of_leave = 'HO';
				$color = "#fc0";
				$working_hours = 'Holiday - ';
				// $title = "Half day";
			}
			
			// $firstDate = DateTime::createFromFormat('Y-m-d H:i:s', $start_first)->format('Y-m-d');
			// $secondDate=DateTime::createFromFormat('Y-m-d H:i:s', $end_first)->format('Y-m-d');

			$data_calendars_holiday[] = [

				//'title' => $type_of_leave . ':' . $fes_name,
				'title' => $fes_name,

				'color' => $color,
				
				'_tooltip' => $working_hours .  $fes_name,

				'start' => $start_date,

				'end' => $start_date,

			];
		}
		 }
		if(empty($data_leave_application)){
			$data_calendars[] = [

				'title' => '',

				'color' => '',



				// 'url' => admin_url('timesheets/requisition_detail/' . $data_row['id']),

				//'_tooltip' => 'Working Hours : ' . $data_row['value'],
				//'date' => $date,
				'start' => '',

				'end' => '',

			];
		}else{
			//$data_leave_application = $this->get_list_leave_application($data);
		foreach ($data_leave_application as $datas) {
		
			$status = $datas['status'];
			if ($status == 0) {
				$status =  'Pending';
			} else if ($status == 1) {
				$status = 'Approved';
			} else if ($status == 2) {
				$status = 'Rejected';
			} else {
				$status = 'Absent';
			}

			$start_time = $datas['start_time'];
			$start_time_em = explode(' ', $start_time);
			$start = $start_time_em[0];
			$end_time = $datas['end_time'];
			$end_time_em = explode(' ', $end_time);
			$end = $end_time_em[0];


			/*	$attendance_datass = $this->db->query('SELECT * FROM tbltimesheets_requisition_leave WHERE start_time BETWEEN "'.$start.'" AND "'.$end.'" AND end_time BETWEEN "'.$start.'" AND "'.$end.'" AND staff_id = "'.get_staff_user_id().'"')->result_array();
		
		//$count = count($attendance_datass);
		foreach($attendance_datass as $dataaa){
			//print_R($dataaa);
			//echo "first".$dataaa['start_time'];
			//echo "<br> ";
		//	echo "second".$start_time;
			if($dataaa['start_time'] == $start_time){
				$type_of_leave = $dataaa['type_of_leave'];
			}
			else{
				$type_of_leave = 'AB';
				$color = "#E74C3C";
			}
		}*/
			if ($start == '0000-00-00') {
				$start = $end;
			}

			$color = '#2ECC71';

			$type_of_leave = $datas['type_of_leave'];

			//print_r($type_of_leave);
			if ($type_of_leave == 'planned_leaves') {
				$type_of_leave = 'PL';
				$color = "#008000";
				// $title = "Half day";
			} elseif ($type_of_leave == 'unplanned_leaves') {
				$type_of_leave = 'UL';
				$color = "#B2BEB5";
				// $title = "Half day";
			}
			elseif ($type_of_leave == 'paternity-leaves') {
				$type_of_leave = 'PAL';
				$color = "#06c";
				// $title = "Half day";
			}elseif ($type_of_leave == 'Holiday') {
				$type_of_leave = 'HO';
				$color = "#fc0";
				// $title = "Half day";
			} elseif ($type_of_leave == 'Holiday') {
				$type_of_leave = 'HO';
				$color = "#fc0";
				// $title = "Half day";
			} elseif ($type_of_leave == 'holiday-leaves') {
				$type_of_leave = 'HL';
				$color = "#FFFF00";
				// $title = "Half day";
			} elseif ($type_of_leave == 'half-days') {
				$type_of_leave = 'PHL';
				$color = "#FFA500";
				// $title = "Half day";
			} elseif ($type_of_leave == 'unpaid-half-days') {
				$type_of_leave = 'UHL';
				$color = "#800080";
				// $title = "Half day";
			} elseif ($type_of_leave == 'short-leaves') {
				$type_of_leave = 'SHL';
				$color = "#F1C40F";
				// $title = "Half day";
			} elseif ($type_of_leave == '4') {
				$type_of_leave = 'AB';
				$color = "#E74C3C";
				// $title = "Half day";
			} else {


				//$attendance_datass = $this->db->query('SELECT * FROM tbltimesheets_requisition_leave WHERE start_time BETWEEN "'.$start.'" AND "'.$end.'" AND end_time BETWEEN "'.$start.'" AND "'.$end.'" AND type_of_leave="4" AND staff_id = "'.get_staff_user_id().'"')->result_array();
				//print_r($attendance_datass);die;
				//foreach($attendance_datass as $dataaa){
				//	print_R($dataaa);
				//}
				$type_of_leave = '';
				$color = "transparent";
				$number_of_leaving_day = ' ';
				$status = ' ';
			}

			$number_of_leaving_day = $datas['number_of_leaving_day'];
			$start_first = $datas['start_time'];
			$end_first = $datas['end_time'];

			// $firstDate = DateTime::createFromFormat('Y-m-d H:i:s', $start_first)->format('Y-m-d');
			// $secondDate=DateTime::createFromFormat('Y-m-d H:i:s', $end_first)->format('Y-m-d');
			//echo (empty($data_leave_application));
		
			$data_calendars[] = [

				'title' => $type_of_leave . ':' . $number_of_leaving_day . '(' . $status . ')',

				'color' => $color,



				// 'url' => admin_url('timesheets/requisition_detail/' . $data_row['id']),

				//'_tooltip' => 'Working Hours : ' . $data_row['value'],
				//'date' => $date,
				'start' => $start,

				'end' => $end_first,

			];
		
		}
	}
		foreach ($attendance_data as $data_row) {


			$staff = '';
			$staff_id = $data_row['staff_id'];

			if ($data_row['staff_id'] != '') {

				$staff = get_staff_full_name($data_row['staff_id']);
			}

			$from_date = date('Y-m-d', strtotime($data_row['date_work']));

			$to_date = date('Y-m-d', strtotime($data_row['date_work']));

			$date_work = $data_row['date_work'];
           
			//print_R($data_row);
			//echo 'SELECT * FROM tblcheck_in_out WHERE date BETWEEN "'.$date_work.'" AND "'.$date_work.'"  AND staff_id = "'.$staff_id;
            $working_hours = 'Working Hours - ';
			$color = "#2ECC71";
			// $title = "Present";

			if ($data_row['type'] == 'HD') {

				$color = "#F1C40F";
				$data_row['value'] = 'Half Day';
				$working_hours = 'Leave - ';
				// $title = "Half day";
			}

			if ($data_row['type'] == 'AB') {
				//$data_row['type'] = '';
				$color = "#E74C3C";
				$data_row['value'] = 'Absent';
				$working_hours = 'Leave - ';
				// $title = "Absent";

			}

			if ($data_row['type'] == 'HO') {

				$color = "#fc0";
				$data_row['value'] = 'Holiday';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'PL') {

				$color = "#008000";
				$data_row['value'] = 'Planned';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'PAL') {

				$color = "#06c";
				$data_row['value'] = 'Paternity Leave';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'UL') {

				$color = "#B2BEB5";
				$data_row['value'] = 'Unplanned';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'SL') {

				$color = "#030";
				$data_row['value'] = 'Saturday';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'PHL') {

				$color = "#FFA500";
				$data_row['value'] = 'Paid Half Day';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'UHL') {

				$color = "#800080";
				$data_row['value'] = 'Unpaid Half Day';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}
			if ($data_row['type'] == 'SLH') {

				$color = "#BDC3C7";
				$data_row['value'] = 'Sort Half Day';
				$working_hours = 'Leave - ';
				// $title = "Holiday";

			}


			//$end = $data_row['enddate'];
			//$nextday= strtotime("$end +1 day");
			//$end_date = date("Y-m-d", $nextday);
			//strtotime("$mindate +1 day");

			//$list_date = $this->get_list_date($from_date, $to_date);

			$list_date = $this->get_list_date($from_date, $to_date);
			//foreach ($list_date as $date) {
			foreach ($list_date as $date) {

				/*
					echo 'SELECT * FROM tblcheck_in_out WHERE date(date) BETWEEN "'.$date.'" AND "'.$date.'"  AND staff_id = '.$staff_id;die;
					$res  = $this->db->query('SELECT * FROM tblcheck_in_out WHERE date(date) BETWEEN "'.$from_date.'" AND "'.$to_date.'"  AND staff_id = '.$staff_id)->result_array();;
					$check_in = $res[0]['date'];
					$check_in_explode = explode(' ',$check_in);
					$check_in_time = date("h:i	 a",strtotime($check_in_explode[1]));
					
					$check_out = $res[1]['date'];
					$check_out_explode = explode(' ',$check_out);
					$check_out_time = date("h:i a",strtotime($check_out_explode[1]));*/

				//$check_in_out_find = $this->check_in_out_find($from_date, $to_date);

				if ($this->valid_date($data_row['staff_id'], $date)) {

					//print_r($data_row['enddate']);echo '<br/>';
					
					$data_calendar[] = [
						
						'title' => $data_row['type'] . ':' . $data_row['value'],

						'color' => $color,
						'status' => $status,

						'_tooltip' => $working_hours . $data_row['value'],
						//. '<br/> Check In '. ' - '. $check_in_time. '<br/> Check Out - '.$check_out_time,

						// 'url' => admin_url('timesheets/requisition_detail/' . $data_row['id']),

						'date' => $date,

						'start' => $data_row['date_work'],

						'end' => $data_row['enddate'],

					];
				}
			}
		}
		if($attendance_sat_holiday != null){
		foreach ($attendance_sat_holiday as $datass) {
			
			$color = '#2ECC71';

			$type_of_sat_leave = 'Saturday';
			$start_date = $datass['saturday_date'];
			
				$type_of_leave = 'SL';
				$color = "#030";
				$working_hours = 'Holiday - ';
			

			// $firstDate = DateTime::createFromFormat('Y-m-d H:i:s', $start_first)->format('Y-m-d');
			// $secondDate=DateTime::createFromFormat('Y-m-d H:i:s', $end_first)->format('Y-m-d');

			$data_calendars_sat_holiday[] = [

				//'title' => $type_of_leave . ':' . $fes_name,
				'title' => $type_of_leave. ':' . $type_of_sat_leave,

				'color' => $color,
				
				'_tooltip' => $working_hours .  $type_of_sat_leave,

				'start' => $start_date,

				'end' => $start_date,

			];
		 }
		}else{
			$data_calendars_sat_holiday[] = [

				//'title' => $type_of_leave . ':' . $fes_name,
				'title' => '',

				'color' => '',
				
				'_tooltip' => '',

				'start' => '',

				'end' => '',

			];
		}
		function removeDuplicates($array)
		{
			$uniqueArray = [];

			foreach ($array as $value) {
				// Check if the value is already in $uniqueArray
				if (!in_array($value, $uniqueArray)) {
					$uniqueArray[] = $value;
				}
			}

			return $uniqueArray;
		}


		//echo '<pre>'; 
		//print_r($data_calendars);die;
		if($data_calendars){
			//print_r($data_calendars_holiday);
			$merge =  array_merge($data_calendar, $data_calendars,$data_calendars_holiday,$data_calendars_sat_holiday);
			
			
		} else{
			//$merge = array_merge($data_calendar,$data_calendars_sat_holiday);
			$merge = $data_calendar;
			
		}
		//$unique_matrix = array_unique($merge);


		$new_array = array();
		foreach ($merge as $v) {
			$date_key = strtotime($v['start']);
			if (!isset($new_array[$date_key])) {
				$new_array[$date_key] = array_merge($v);
			}
			$new_array[$date_key];
		}
		ksort($new_array); // sort by date
		$new_array = array_values($new_array); // remove unix time keys
		//print_r($new_array);
		return $new_array;
	}



	/**

	 * delete permission

	 * @param  integer $id

	 * @return boolean

	 */

	public function delete_permission($id)
	{

		$str_permissions = '';

		foreach (list_timesheet_permisstion() as $per_key => $per_value) {

			if (strlen($str_permissions) > 0) {

				$str_permissions .= ",'" . $per_value . "'";
			} else {

				$str_permissions .= "'" . $per_value . "'";
			}
		}

		$sql_where = " feature IN (" . $str_permissions . ") ";

		$this->db->where('staff_id', $id);

		$this->db->where($sql_where);

		$this->db->delete(db_prefix() . 'staff_permissions');



		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * get staff id by approve value

	 * @param  array $data

	 * @param  array $approve_value

	 * @return array

	 */

	public function get_staff_id_by_approve_value($data, $approve_value)
	{

		$this->load->model('departments_model');

		$list_staff = $this->staff_model->get();

		$list = [];

		$staffid = [];



		if ($approve_value == 'head_of_department') {

			$staffid = $this->departments_model->get_staff_departments($data->staff_addedfrom)[0]['manager_id'];
		} elseif ($approve_value == 'direct_manager') {

			$staffid = $this->staff_model->get($data->staff_addedfrom)->team_manage;
		}

		return $staffid;
	}

	/**

	 * add timesheet leave

	 * @param string $type

	 * @param integer $rel_id

	 */

	public function add_timesheet_leave($type, $rel_id)
	{

		$this->db->where('id', $rel_id);

		$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

		if (!$requisition_leave) {
			return false;
		}

		$staffid = (int) ($requisition_leave->staff_id ?: get_staff_user_id());
		$number_of_day = (float) $requisition_leave->number_of_leaving_day;
		$type_code = $this->resolve_leave_timesheet_type($type, $requisition_leave->type_of_leave ?? '');

		if ($requisition_leave->start_time == '' || $requisition_leave->end_time == '') {
			return false;
		}

		$start_time = date('Y-m-d', strtotime($requisition_leave->start_time));
		$end_time = date('Y-m-d', strtotime($requisition_leave->end_time));

		return $this->upsert_leave_days_to_timesheet(
			$staffid,
			$start_time,
			$end_time,
			$type_code,
			(int) $rel_id,
			$number_of_day
		);
	}

	/**
	 * Map leave slug / approve type to attendance sheet code.
	 */
	public function resolve_leave_timesheet_type($type, $type_of_leave = '')
	{
		$type = strtolower(trim((string) $type));
		$slug = strtolower(trim((string) $type_of_leave));
		$map = [
			'earned-leave' => 'EL',
			'loss-of-pay' => 'LOP',
			'leave' => 'AL',
			'al' => 'AL',
			'el' => 'EL',
			'pl' => 'PL',
			'lop' => 'LOP',
			'sick_leave' => 'SL',
			'maternity_leave' => 'ML',
		];
		if (isset($map[$slug])) {
			return $map[$slug];
		}
		if (isset($map[$type])) {
			return $map[$type];
		}
		if ($type !== '' && strlen($type) <= 4) {
			return strtoupper($type);
		}
		$custom = $this->get_custom_leave_by_slug($slug ?: $type);
		if ($custom && !empty($custom->symbol)) {
			return strtoupper((string) $custom->symbol);
		}
		return 'PL';
	}

	/**
	 * Write leave onto attendance sheet for each working day in range.
	 * Works for current + previous month (and latched months).
	 * Replaces conflicting marks (AB/A/P/HD/etc.) so the calendar shows leave.
	 */
	public function upsert_leave_days_to_timesheet($staff_id, $start_ymd, $end_ymd, $type_code, $rel_id = 0, $number_of_leaving_day = null)
	{
		$staff_id = (int) $staff_id;
		$start_ymd = date('Y-m-d', strtotime($start_ymd));
		$end_ymd = date('Y-m-d', strtotime($end_ymd));
		$type_code = strtoupper(trim((string) $type_code));
		if ($staff_id <= 0 || !$start_ymd || !$end_ymd || $type_code === '') {
			return false;
		}
		if (strtotime($end_ymd) < strtotime($start_ymd)) {
			$tmp = $start_ymd;
			$start_ymd = $end_ymd;
			$end_ymd = $tmp;
		}

		$list_date = $this->get_list_date($start_ymd, $end_ymd);
		$work_days = [];
		foreach ($list_date as $day) {
			$work_time = (float) $this->get_hour_shift_staff($staff_id, $day);
			$day_off = $this->get_day_off_staff_by_date($staff_id, $day);
			if ($work_time > 0 && count($day_off) == 0) {
				$work_days[] = ['date' => $day, 'hours' => $work_time > 0 ? $work_time : 9];
			}
		}
		if (empty($work_days)) {
			// Still mark calendar days if shift data missing (common for backdated months).
			foreach ($list_date as $day) {
				$dow = (int) date('N', strtotime($day));
				if ($dow >= 6) {
					continue; // skip Sat/Sun when no shift info
				}
				$work_days[] = ['date' => $day, 'hours' => 9];
			}
		}

		$remaining = $number_of_leaving_day !== null ? (float) $number_of_leaving_day : count($work_days);
		$conflict_types = ['AB', 'A', 'HD', 'P', 'p', 'W', 'PL', 'EL', 'LOP', 'AL', 'UL', 'SL', 'UHL', 'PHL'];

		foreach ($work_days as $row) {
			$day = $row['date'];
			$hours = (float) $row['hours'];
			if ($remaining <= 0) {
				break;
			}
			$value = ($remaining < 1) ? ($hours * $remaining) : $hours;
			$is_latched = $this->check_latch_timesheet(date('m-Y', strtotime($day)));

			$this->db->where('staff_id', $staff_id);
			$this->db->where('date_work', $day);
			$this->db->where_in('type', $conflict_types);
			$existing = $this->db->get(db_prefix() . 'timesheets_timesheet')->result_array();
			foreach ($existing as $ex) {
				$this->db->where('id', (int) $ex['id'])->delete(db_prefix() . 'timesheets_timesheet');
			}

			$payload = [
				'staff_id' => $staff_id,
				'date_work' => $day,
				'value' => $value,
				'add_from' => get_staff_user_id() ?: $staff_id,
				'relate_id' => (int) $rel_id,
				'relate_type' => 'leave',
				'type' => $type_code,
			];
			if ($is_latched) {
				$payload['latch'] = 1;
			}
			$this->db->insert(db_prefix() . 'timesheets_timesheet', $payload);
			$remaining -= ($remaining < 1) ? $remaining : 1;
		}

		return true;
	}

	/**
	 * Leave apply window: current month + previous month only.
	 */
	public function leave_date_in_allowed_window($ymd)
	{
		$ts = strtotime($ymd);
		if ($ts === false) {
			return false;
		}
		$ym = date('Y-m', $ts);
		$current_ym = date('Y-m');
		$prev_ym = date('Y-m', strtotime('first day of last month'));
		return ($ym === $current_ym || $ym === $prev_ym);
	}

	/**

	 * send notify approval expiration

	 * @param  string $curr_date

	 */

	function send_notify_approval_expiration($curr_date)
	{

		if (!$this->check_log_send_notify(0, 1, $curr_date, 'approval_expiration')) {

			$this->add_log_send_notify(0, 1, $curr_date, 'approval_expiration');

			$this->db->where('approval_deadline', $curr_date);

			$this->db->where('approve is null');

			$data_approve_detail = $this->db->get(db_prefix() . 'timesheets_approval_details')->result_array();

			foreach ($data_approve_detail as $key => $detail) {

				$approve_id = $detail['staffid'];

				$link = 'timesheets/requisition_detail/' . $detail['rel_id'];

				$this->notifications($approve_id, $link, _l('ts_there_is_an_expired_approval_request_please_approve_it'));
			}
		}
	}



	/**

	 * add type of leave

	 * @param array $data

	 */

	public function add_type_of_leave($data)
	{

		$this->db->insert(db_prefix() . 'timesheets_type_of_leave', $data);

		$insert_id = $this->db->insert_id();

		if ($insert_id) {

			return $insert_id;
		}

		return 0;
	}



	/**

	 * update type of leave

	 * @param array $data

	 */

	public function update_type_of_leave($data)
	{

		$this->db->where('id', $data['id']);

		$this->db->update(db_prefix() . 'timesheets_type_of_leave', $data);

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * get type of leave

	 * @param  string $id

	 * @return array or object

	 */

	public function get_type_of_leave($id = '')
	{

		if (is_numeric($id)) {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();
		} else {

			return $this->db->get(db_prefix() . 'timesheets_type_of_leave')->result_array();
		}
	}

	/**

	 * get custom leave name by slug

	 * @param  string $slug

	 * @return string

	 */

	public function get_custom_leave_name_by_slug($slug = '')
	{

		$slug_name = '';

		$this->db->where('slug', $slug);

		$data = $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();

		if ($data) {

			$slug_name = $data->type_name;
		}

		return $slug_name;
	}

	/**

	 * get custom leave name by symbol

	 * @param  string $slug

	 * @return string

	 */

	public function get_custom_leave_name_by_symbol($symbol = '')
	{

		$symbol_name = '';

		$this->db->where('symbol', $symbol);

		$data = $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();

		if ($data) {

			$symbol_name = $data->type_name;
		}

		return $symbol_name;
	}

	/**

	 * get custom leave by slug

	 * @param  string $slug

	 * @return string

	 */

	public function get_custom_leave_by_slug($slug = '')
	{

		$this->db->where('slug', $slug);

		return $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();
	}



	/**

	 * delete type of leave

	 * @param  integer $id

	 * @return integer

	 */

	public function delete_type_of_leave($id)
	{

		$this->db->where('id', $id);

		$this->db->delete(db_prefix() . 'timesheets_type_of_leave');

		if ($this->db->affected_rows() > 0) {

			return true;
		}

		return false;
	}



	/**

	 * check duplicate character type of leave

	 * @param  string $character

	 * @param  integer $id

	 * @return boolean

	 */

	public function check_duplicate_character_type_of_leave($character, $id = '')
	{

		$res = false;

		$this->db->where('symbol', $character);

		if ($id != '') {

			$this->db->where('id != ' . $id);
		}

		$data = $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();

		if ($data) {

			$res = true;
		}

		return $res;
	}



	/**

	 * check duplicate slug type of leave

	 * @param  string $slug

	 * @param  integer $id

	 * @return boolean

	 */

	public function check_duplicate_slug_type_of_leave($slug, $id = '')
	{

		$res = false;

		$this->db->where('slug', $slug);

		if ($id != '') {

			$this->db->where('id != ' . $id);
		}

		$data = $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();

		if ($data) {

			$res = true;
		}

		return $res;
	}



	/**

	 * get list approver

	 * @param  integer $rel_id

	 * @param  string $rel_type

	 * @return  array

	 */

	public function get_list_approver($rel_id, $rel_type)
	{

		$result = [];

		$this->db->select('staffid');

		$this->db->where('rel_id', $rel_id);

		$this->db->where('rel_type', $rel_type);

		$data = $this->db->get(db_prefix() . 'timesheets_approval_details')->result_array();

		foreach ($data as $key => $row) {

			$result[] = $row['staffid'];
		}

		return $result;
	}

	/**

	 * valid_date

	 * @param  integer $staffid

	 * @param  date $date

	 * @return boolean

	 */

	public function valid_date($staffid, $date)
	{

		$max_hour = $this->get_hour_shift_staff($staffid, $date);

		$check_holiday = $this->check_holiday($staffid, $date);

		$result_lack = '';

		if ($max_hour > 0) {

			if (!$check_holiday) {

				return true;
			} else {

				// Is holiday

				return false;
			}

			return true;
		}

		return false;
	}

	/**

	 * notify create new leave

	 * @param  integer $rel_id

	 * @param  string $rel_type

	 * @param  integer $status

	 * @return integer

	 */

	public function notify_create_new_leave($rel_id, $rel_type)
	{

		$additional_data = '';

		$rel_type = strtolower($rel_type);

		switch ($rel_type) {

			case 'leave':

				$additional_data = _l('leave');

				break;

			case 'maternity_leave':

				$additional_data = _l('leave');

				break;

			case 'private_work_without_pay':

				$additional_data = _l('leave');

				break;

			case 'sick_leave':

				$additional_data = _l('sick_leave');

				break;

			case 'late':

				$additional_data = _l('late');

				break;

			case 'early':

				$additional_data = _l('early');

				break;

			case 'go_out':

				$additional_data = _l('go_out');

				break;

			case 'go_on_bussiness':

				$additional_data = _l('go_on_bussiness');

				break;

			case 'additional_timesheets':

				$additional_data = _l('additional_timesheets');

				break;

			default:

				if ($rel_type != '' && $rel_id != '') {

					$this->db->select('type_of_leave_text');

					$this->db->where('id', $rel_id);

					$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

					if ($requisition_leave) {

						$slug = $requisition_leave->type_of_leave_text;

						$this->db->where('slug', $slug);

						$data_type_custom = $this->db->get(db_prefix() . 'timesheets_type_of_leave')->row();

						if ($data_type_custom) {

							$additional_data = $data_type_custom->type_name;
						}
					}
				}

				break;
		}

		$check_approve_status = $this->check_approval_details($rel_id, $rel_type);

		if (isset($check_approve_status['notification_recipient'])) {

			$notification_recipient = explode(',', $check_approve_status['notification_recipient']);

			$link = admin_url('timesheets/requisition_detail/' . $rel_id);

			foreach ($notification_recipient as $recipient_id) {

				$recipient_id = (int) trim($recipient_id);
				if ($recipient_id <= 0) {
					continue;
				}

				$notified_rc = add_notification([

					'description' => 'created_a_new_leave_application',

					'touserid' => $recipient_id,

					'link' => 'timesheets/requisition_detail/' . $rel_id,

					'additional_data' => serialize([

						$additional_data,

					]),

				]);

				if ($notified_rc) {

					pusher_trigger_notification([$recipient_id]);
				}

				// Email is sent by send_leave_application_approver_emails() / send_mail() on apply.
			}
		}
	}



	/**

	 * set leave

	 * @param object $data

	 */

	public function set_valid_ip($data)
	{

		if (isset($data['timekeeping_enable_valid_ip'])) {

			$this->db->where('option_name', 'timekeeping_enable_valid_ip');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => $data['timekeeping_enable_valid_ip']

			]);
		} else {

			$this->db->where('option_name', 'timekeeping_enable_valid_ip');

			$this->db->update(db_prefix() . 'timesheets_option', [

				'option_val' => '0',

			]);
		}

		$list_client_ip = json_decode($data['list_ip_data']);

		$list_db_ip = $this->get_valid_ip();

		$affectedrows = 0;



		$this->db->from(db_prefix() . 'timesheets_valid_ip');

		$this->db->truncate();



		foreach ($list_client_ip as $key => $value) {

			if (isset($value[0]) && ($value[0] != null)) {

				$data_update['ip'] = $value[0];

				$this->db->insert(db_prefix() . 'timesheets_valid_ip', $data_update);

				$affectedrows++;
			}
		}



		return $affectedrows;
	}



	/**

	 * get valid ip

	 * @param  integer

	 * @return array or object

	 */

	public function get_valid_ip($id = '')
	{

		if ($id != '') {

			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'timesheets_valid_ip')->row();
		} else {

			return $this->db->get(db_prefix() . 'timesheets_valid_ip')->result_array();
		}
	}



	/**

	 * get customer email route point

	 * @param  string $id 

	 * @return      

	 */

	public function get_customer_email_route_point($id = '')
	{

		$email = '';

		$data = $this->db->query('select email from ' . db_prefix() . 'timesheets_route_point a left join ' . db_prefix() . 'clients b on a.related_id = b.userid left join ' . db_prefix() . 'contacts c on b.userid = c.userid and is_primary = 1 where a.id = ' . $id . ' and a.related_to = 1')->row();

		if ($data) {

			$email = $data->email;
		}

		return $email;
	}



	/**

	 * update requisition after approve

	 * @param  integer $id            

	 * @param  integer $status        

	 * @param  string $type_of_leave 

	 */

	public function update_requisition_after_approve($id, $status, $type_of_leave)
	{

		$type_of_leave_data = $this->get_type_of_leave_id($type_of_leave);

		$this->db->where('id', $id);

		$this->db->update(db_prefix() . 'timesheets_requisition_leave', ['status' => $status]);

		if ($status == 1) {

			$this->db->where('id', $id);

			$requisition_leave = $this->db->get(db_prefix() . 'timesheets_requisition_leave')->row();

			if ($requisition_leave) {

				// Get total leave in year of staff

				$day_off = $this->get_day_off($requisition_leave->staff_id, '', $type_of_leave_data->type_id);

				// Number of leaving day

				$dd = $requisition_leave->number_of_leaving_day;



				$update_days_off = abs($day_off->days_off + $dd);

				$update_remain = abs($day_off->total) - $update_days_off;



				$this->db->where('type_of_leave', $type_of_leave_data->type_id);

				$this->db->where('staffid', $requisition_leave->staff_id);

				$this->db->where('year', date('Y'));

				$this->db->update(db_prefix() . 'timesheets_day_off', [

					'remain' => abs($update_remain),

					'days_off' => $update_days_off,

				]);

				$this->add_timesheet_leave($type_of_leave_data->timesheet_type, $id);
			}
		}
	}



	/**

	 * get type of leave id

	 * @param  string $type 

	 */

	public function get_type_of_leave_id($type)
	{

		$obj = new stdClass();

		$obj->type_id = 0;

		$obj->timesheet_type = '';

		switch (strtolower($type)) {

			case 'leave':

				$obj->type_id = 8;

				$obj->timesheet_type = 'AL';

				break;

			case 'maternity_leave':

				$obj->type_id = 2;

				$obj->timesheet_type = 'M';

				// break;

				// case 'private_work_without_pay':

				// $obj->type_id = 4;

				// $obj->timesheet_type = 'P';

				break;

			case 'sick_leave':

				$obj->type_id = 1;

				$obj->timesheet_type = 'SI';

				break;

			default:

				$data_custome_type = $this->get_custom_leave_by_slug($type);

				if ($data_custome_type) {

					$obj->type_id = $type;

					$obj->timesheet_type = $data_custome_type->symbol;
				}

				break;
		}

		return $obj;
	}



	/**

	 * login

	 * @param  string $email    

	 * @param  string $password 

	 * @return boolean           

	 */

	public function login($email, $password)

	{

		$this->load->model('staff_model');

		$user = $this->staff_model->get('', ['email' => $email]);

		if ($user) {

			// Email is okey lets check the password now

			if (app_hasher()->CheckPassword($password, $user[0]['password'])) {

				if ($user[0]['active'] == 0) {

					return [

						'status' => false,

						'message' => 'Inactive user.',

					];
				} else {

					$user_data = [

						'staff_user_id'   => $user[0]['staffid'],

						'staff_logged_in' => true,

					];



					$this->session->set_userdata($user_data);



					$user[0]['permissions'] = $this->staff_model->get_staff_permissions($user[0]['staffid']);

					return $user[0];
				}
			} else {

				return false;
			}
		} else {

			return false;
		}

		return true;
	}

	/**

	 * logout

	 */

	public function logout()

	{

		$this->session->unset_userdata('staff_user_id');

		$this->session->unset_userdata('staff_logged_in');



		$this->session->sess_destroy();
	}

	/**

	 * check token logout

	 * @param  string $token 

	 * @return object or boolean        

	 */

	public function check_token_logout($token)

	{

		$this->db->where('token', $token);

		$user = $this->db->get(db_prefix() . 'staff')->row();

		if ($user) {

			return $user;
		}

		return false;
	}

	//    writing custom code to get latest check in and check out data
	public function get_latest_check_in_out()
	{
		$this->db->where('staff_id', get_staff_user_id())->order_by('date', 'desc')->limit(2);
		return $this->db->get(db_prefix() . 'check_in_out')->result_array();
	}

	/**
	 * GreytHR-style attendance regularisation calendar for one staff member.
	 *
	 * @param int $staff_id
	 * @param int $year
	 * @param int $month
	 * @return array
	 */
	public function get_attendance_regularisation_calendar($staff_id, $year, $month)
	{
		$staff_id = (int) $staff_id;
		$year = (int) $year;
		$month = (int) $month;
		if ($staff_id <= 0 || $month < 1 || $month > 12) {
			return ['days' => [], 'required_hours' => 9, 'month' => sprintf('%04d-%02d', $year, $month)];
		}

		$this->ensure_additional_timesheet_rejection_column();

		$this->load->helper('timesheets/timesheets');
		$required_hours = timesheets_required_work_hours();
		$present_min_hours = timesheets_present_min_hours();
		$half_day_min_hours = timesheets_half_day_min_hours();
		$from = sprintf('%04d-%02d-01', $year, $month);
		$to = date('Y-m-t', strtotime($from));
		$days_in_month = (int) date('t', strtotime($from));
		$today = date('Y-m-d');

		// Leave codes only (HD = half-day attendance, not leave — must stay regularisable).
		$leave_types = ['AL', 'PL', 'SL', 'UHL', 'PHL', 'UL', 'ML', 'CO', 'LOP', 'EL', 'L', 'WFH'];

		$regs = $this->db->query(
			'SELECT id, additional_day, time_in, time_out, timekeeping_value, status, reason, rejection_comment
			FROM ' . db_prefix() . 'timesheets_additional_timesheet
			WHERE creator = ? AND additional_day BETWEEN ? AND ?
			ORDER BY id DESC',
			[$staff_id, $from, $to]
		)->result_array();

		$reg_by_date = [];
		foreach ($regs as $row) {
			$d = date('Y-m-d', strtotime($row['additional_day']));
			if (!isset($reg_by_date[$d])) {
				$reg_by_date[$d] = $row;
			}
		}

		$ts_rows = $this->db->query(
			'SELECT date_work, value, type FROM ' . db_prefix() . 'timesheets_timesheet
			WHERE staff_id = ? AND date_work BETWEEN ? AND ?',
			[$staff_id, $from, $to]
		)->result_array();
		$ts_by_date = [];
		foreach ($ts_rows as $row) {
			$ts_by_date[$row['date_work']] = $row;
		}

		$cio_rows = $this->db->query(
			'SELECT DATE(date) AS d, type_check, TIME(date) AS t, date
			FROM ' . db_prefix() . 'check_in_out
			WHERE staff_id = ? AND DATE(date) BETWEEN ? AND ?
			ORDER BY date ASC',
			[$staff_id, $from, $to]
		)->result_array();
		$cio_by_date = [];
		foreach ($cio_rows as $row) {
			$cio_by_date[$row['d']][] = $row;
		}

		$bio_by_date = [];
		$bio_daily_by_date = [];
		if (file_exists(APPPATH . 'models/Biometric_model.php')) {
			$this->load->model('biometric_model');
			$swipes = $this->biometric_model->get_swipes([
				'from' => $from,
				'to' => $to,
				'staff' => $staff_id,
			]);
			foreach ($swipes as $sw) {
				if (empty($sw['swipe_sort'])) {
					continue;
				}
				$d = substr($sw['swipe_sort'], 0, 10);
				$bio_by_date[$d][] = [
					'time' => substr($sw['swipe_sort'], 11, 8),
					'type' => strtoupper($sw['in_out'] ?? ''),
					'source' => 'Biometric',
					'door' => $sw['door'] ?? 'Biometrics swipe',
				];
			}

			$staff_code_row = $this->db->query(
				'SELECT s.staff_identifi, i.empid
				 FROM ' . db_prefix() . 'staff s
				 LEFT JOIN ' . db_prefix() . 'staff_info i ON i.staffid = s.staffid
				 WHERE s.staffid = ?
				 LIMIT 1',
				[$staff_id]
			)->row();
			$codes = [];
			if ($staff_code_row) {
				foreach (['staff_identifi', 'empid'] as $field) {
					$code = trim((string) ($staff_code_row->{$field} ?? ''));
					if ($code !== '') {
						$codes[$code] = true;
					}
				}
			}
			$codes = array_keys($codes);
			if (!empty($codes)) {
				$month_suffix = date('M-Y', strtotime($from));
				$placeholders = implode(',', array_fill(0, count($codes), '?'));
				$params = $codes;
				$params[] = '%-' . $month_suffix;
				$bio_daily_rows = $this->db->query(
					'SELECT attendance_date, a_in_time, a_out_time, punch_records
					FROM ' . db_prefix() . 'biometric_report
					WHERE TRIM(employee_code) IN (' . $placeholders . ') AND attendance_date LIKE ?',
					$params
				)->result_array();
				foreach ($bio_daily_rows as $row) {
					$att_dt = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
					if (!$att_dt) {
						continue;
					}
					$ymd = $att_dt->format('Y-m-d');
					if (!isset($bio_daily_by_date[$ymd])
						|| $this->biometric_model->biometric_row_completeness_score($row)
							> $this->biometric_model->biometric_row_completeness_score($bio_daily_by_date[$ymd])) {
						$bio_daily_by_date[$ymd] = $row;
					}
				}
			}
		}

		$present_types = ['P', 'W', 'SI', 'B', 'ME', 'NS', 'E'];

		$month_shift_info = $this->get_info_hour_shift_staff($staff_id, $from);

		$sat_dates = [];
		$sat_rows = $this->db->select('saturday_date')
			->from(db_prefix() . 'holiday')
			->where('staffid', $staff_id)
			->where('saturday_date >=', $from)
			->where('saturday_date <=', $to)
			->get()
			->result_array();
		foreach ($sat_rows as $row) {
			$sat_dates[date('Y-m-d', strtotime($row['saturday_date']))] = true;
		}

		$off_dates = [];
		$off_rows = $this->db->select('break_date, off_type, off_reason')
			->from(db_prefix() . 'day_off')
			->where('break_date >=', $from)
			->where('break_date <=', $to)
			->get()
			->result_array();
		foreach ($off_rows as $row) {
			$off_dates[$row['break_date']] = $row;
		}

		$wfh_dates = $this->get_staff_wfh_dates_in_range($staff_id, $from, $to);

		$range_from = date('Y-m-d', strtotime($from . ' -1 day'));
		$range_to = date('Y-m-d', strtotime($to . ' +1 day'));
		$timeline_events = $this->collect_attendance_timeline_events($staff_id, $range_from, $range_to, $wfh_dates);
		$attendance_sessions = $this->build_attendance_sessions($timeline_events, self::ATTENDANCE_SESSION_WINDOW_HOURS);
		$sessions_by_start = $this->index_attendance_sessions_by_start_date($attendance_sessions);
		$session_event_at = [];
		foreach ($attendance_sessions as $att_session) {
			foreach ($att_session['events'] as $ev) {
				$session_event_at[(int) ($ev['at'] ?? 0)] = true;
			}
		}

		$days = [];
		for ($d = 1; $d <= $days_in_month; $d++) {
			$date = sprintf('%04d-%02d-%02d', $year, $month, $d);
			$dow = (int) date('N', strtotime($date));
			$is_weekend = ($dow >= 6);
			$is_future = ($date > $today);

			$ts = $ts_by_date[$date] ?? null;
			$ts_type = strtoupper((string) ($ts['type'] ?? ''));
			// Working Saturdays can be AB/HD or other attendance types; don't blanket-block weekends.
			$weekend_blocks_reg = false;
			$reg = $reg_by_date[$date] ?? null;

			$cio_for_day = $cio_by_date[$date] ?? [];
			$is_wfh_day = !empty($wfh_dates[$date]);
			$attendance_source = 'none';
			$session = $sessions_by_start[$date] ?? null;

			if ($is_wfh_day) {
				$attendance_source = 'wfh';
				$punch = $this->analyze_day_punches($cio_for_day, [], 'WFH');
			} elseif ($session && !empty($session['events'])) {
				$has_bio = false;
				foreach ($session['events'] as $ev) {
					if (($ev['source'] ?? '') === 'Biometric') {
						$has_bio = true;
						break;
					}
				}
				$attendance_source = $has_bio ? 'biometric' : 'workroom';
				$punch = $this->analyze_session_punches($session['events']);
			} else {
				$cio_for_day = array_values(array_filter($cio_for_day, function ($row) use ($session_event_at) {
					$at = strtotime($row['date'] ?? '');
					return $at && empty($session_event_at[$at]);
				}));
				$day_bio = $this->build_calendar_biometric_events_for_date($date, $bio_by_date, $bio_daily_by_date);
				$day_bio = array_values(array_filter($day_bio, function ($row) use ($date, $session_event_at) {
					$at = strtotime($date . ' ' . substr($row['time'] ?? '00:00:00', 0, 8));
					return $at && empty($session_event_at[$at]);
				}));
				if (!empty($day_bio)) {
					$cio_for_day = [];
					$attendance_source = 'biometric';
				} elseif (!empty($cio_for_day)) {
					$attendance_source = 'workroom';
				}
				$punch = $this->analyze_day_punches(
					$cio_for_day,
					$day_bio,
					'Workroom'
				);
			}
			$day_swipes = $punch['swipes'];
			$hours = $punch['hours'];
			if ($hours <= 0 && $ts && is_numeric($ts['value'])) {
				$hours = (float) $ts['value'];
			}

			$status = 'neutral';
			$can_regularise = false;
			$issues = [];

			if ($is_future) {
				$status = 'future';
			} elseif (isset($sat_dates[$date])) {
				$status = 'saturday_leave';
			} elseif (isset($off_dates[$date])) {
				$status = 'holiday';
			} elseif ($reg) {
				if ((int) $reg['status'] === 1) {
					$status = 'regularised';
					// Use approved regularization times so the day shows as Present with correct hours.
					$reg_in = trim((string) ($reg['time_in'] ?? ''));
					$reg_out = trim((string) ($reg['time_out'] ?? ''));
					if ($reg_in !== '' && $reg_out !== '') {
						$in_ts = strtotime('1970-01-01 ' . substr($reg_in, 0, 8));
						$out_ts = strtotime('1970-01-01 ' . substr($reg_out, 0, 8));
						if ($out_ts > $in_ts) {
							$hours = max($hours, ($out_ts - $in_ts) / 3600);
						}
						$punch['check_in'] = substr($reg_in, 0, 5);
						$punch['check_out'] = substr($reg_out, 0, 5);
						$punch['punch_missing'] = false;
					}
					if (is_numeric($reg['timekeeping_value'] ?? null) && (float) $reg['timekeeping_value'] > 0) {
						$hours = max($hours, (float) $reg['timekeeping_value']);
					}
					$issues = [];
				} elseif ((int) $reg['status'] === 2) {
					$status = 'rejected';
					$can_regularise = true;
					$rej = trim((string) ($reg['rejection_comment'] ?? ''));
					$issues[] = $rej !== ''
						? ('Regularization rejected: ' . $rej)
						: 'Previous regularization was rejected';
				} else {
					$status = 'pending';
				}
			} elseif ($ts_type === 'HO' || $ts_type === 'M' || $ts_type === 'H') {
				$status = 'holiday';
			} elseif ($ts && in_array($ts_type, $leave_types, true) && $ts_type !== 'AB') {
				$status = 'leave';
			} elseif ($ts_type === 'AB') {
				$has_in = !empty($punch['check_in']) || !empty($punch['ins']);
				$has_out = !empty($punch['check_out']) || !empty($punch['outs']);
				if ($date === $today && $has_in) {
					// Cron may pre-mark AB; if employee already punched in today, show Present.
					$status = 'ok';
					if (!$has_out) {
						$issues[] = 'Checked in — out punch pending';
					}
					$can_regularise = false;
				} elseif ($has_in && !$has_out) {
					$status = 'short_hours';
					$issues[] = 'Punch in/out incomplete';
					$can_regularise = !$weekend_blocks_reg;
				} else {
					$status = 'absent';
					$issues[] = 'Marked absent on attendance record';
					$can_regularise = !$weekend_blocks_reg;
				}
			} elseif (in_array($ts_type, $present_types, true)) {
				// Prefer Biometric/paired punch hours; timesheet value is fallback only.
				if ($hours <= 0 && is_numeric($ts['value'] ?? null)) {
					$hours = (float) $ts['value'];
				}
				if ($hours > 0 && $hours < $required_hours) {
					$status = 'short_hours';
					$issues[] = 'Less than ' . $required_hours . ' hours (' . round($hours, 2) . 'h)';
					$can_regularise = true;
				} else {
					$status = 'ok';
				}
			} elseif ($ts_type === 'HD') {
				if ($hours <= 0 && is_numeric($ts['value'] ?? null)) {
					$hours = (float) $ts['value'];
				}
				$status = 'short_hours';
				if ($hours > 0 && $hours < $required_hours) {
					$issues[] = 'Less than ' . $required_hours . ' hours (' . round($hours, 2) . 'h)';
					$can_regularise = true;
				}
			} elseif ($is_weekend && $hours <= 0 && empty($punch['ins']) && empty($punch['outs'])) {
				$status = 'weekend';
			} else {
				if ($punch['punch_missing']) {
					$has_in = !empty($punch['check_in']) || !empty($punch['ins']);
					$has_out = !empty($punch['check_out']) || !empty($punch['outs']);
					// Still at work today: IN without OUT is Present, not Absent.
					if ($date === $today && $has_in && !$has_out) {
						$status = 'ok';
						$issues[] = 'Checked in — out punch pending';
						$can_regularise = false;
					} elseif ($has_in && !$has_out) {
						// Past day with only IN: exception (needs out / regularization), not full Absent.
						$status = 'short_hours';
						$issues[] = 'Punch in/out incomplete';
						$can_regularise = !$weekend_blocks_reg;
					} else {
						$status = 'punch_missing';
						$issues[] = 'Punch in/out incomplete';
						$can_regularise = true;
					}
				} elseif ($hours > 0 && $hours < $required_hours) {
					$status = 'short_hours';
					$issues[] = 'Less than ' . $required_hours . ' hours (' . round($hours, 2) . 'h)';
					$can_regularise = true;
				} elseif ($hours >= $required_hours) {
					$status = 'ok';
				} elseif (!empty($punch['swipes'])) {
					$status = 'punch_missing';
					$issues[] = 'Punch in/out incomplete';
					$can_regularise = !$weekend_blocks_reg;
				} else {
					$status = 'neutral';
				}
			}

			$shift_info = $month_shift_info;
			$detail = $this->build_attendance_day_detail($punch, $shift_info, $hours, $required_hours);
			$work_hours = (float) ($detail['actual_work_hrs_num'] ?? $hours);
			$has_in = !empty($punch['check_in']) || !empty($punch['ins']);
			$has_out = !empty($punch['check_out']) || !empty($punch['outs']);
			// Final hour bands on actual working hours: <5 Absent, 5–7.99 Half day, 8.0+ Present.
			// Today with any IN: never force Absent/HD mid-day (day still open).
			if (!in_array($status, ['future', 'weekend', 'holiday', 'leave', 'saturday_leave', 'pending', 'regularised'], true)) {
				if ($date === $today && $has_in) {
					$status = 'ok';
					$issues = $has_out ? [] : ['Checked in — out punch pending'];
					$can_regularise = false;
				} else {
					$classified = $this->classify_attendance_by_hours($work_hours, $present_min_hours, $half_day_min_hours);
					if ($classified !== null) {
						$status = $classified['status'];
						if (!empty($classified['issue'])) {
							$issues = [$classified['issue']];
						}
						if (isset($classified['can_regularise'])) {
							$can_regularise = $classified['can_regularise'] && !$weekend_blocks_reg;
						}
					}
				}
			}
			$code = $this->map_attendance_display_code($status, $ts_type, $work_hours);

			// Regularization window: current month + previous month only.
			$current_ym = date('Y-m');
			$prev_ym = date('Y-m', strtotime('first day of last month'));
			$date_ym = date('Y-m', strtotime($date));
			$within_reg_window = ($date_ym === $current_ym || $date_ym === $prev_ym);
			if (!$within_reg_window) {
				$can_regularise = false;
			}

			$days[] = [
				'date' => $date,
				'day' => $d,
				'weekday' => date('D', strtotime($date)),
				'status' => $status,
				'code' => $code,
				'status_label' => $this->map_attendance_status_label($status, $ts_type, $code),
				'hours' => round($work_hours, 2),
				'required_hours' => $required_hours,
				'present_min_hours' => $present_min_hours,
				'half_day_min_hours' => $half_day_min_hours,
				'check_in' => $punch['check_in'],
				'check_out' => $punch['check_out'],
				'punch_missing' => $punch['punch_missing'],
				'issues' => $issues,
				'can_regularise' => $can_regularise && !$is_future && (!$reg || (int) $reg['status'] === 2) && $within_reg_window,
				'within_reg_window' => $within_reg_window,
				'leave_days_if_regularised' => timesheets_leave_days_for_attendance_status($status, $work_hours, $code),
				'leave_days_current' => timesheets_leave_days_for_attendance_status($status, $work_hours, $code),
				'timesheet_type' => $ts_type,
				'shift_start' => $detail['shift_start'],
				'shift_end' => $detail['shift_end'],
				'shift_scheme' => $detail['shift_scheme'],
				'first_in' => $detail['first_in'],
				'last_out' => $detail['last_out'],
				'late_in' => $detail['late_in'],
				'early_out' => $detail['early_out'],
				'total_work_hrs' => $detail['total_work_hrs'],
				'break_hrs' => $detail['break_hrs'],
				'actual_work_hrs' => $detail['actual_work_hrs'],
				'sessions' => $detail['sessions'],
				'swipes' => $day_swipes,
				'attendance_source' => $attendance_source,
				'regularisation' => $reg ? [
					'id' => (int) $reg['id'],
					'status' => (int) $reg['status'],
					'time_in' => $reg['time_in'],
					'time_out' => $reg['time_out'],
					'reason' => $reg['reason'],
					'rejection_comment' => $reg['rejection_comment'] ?? '',
				] : null,
			];
		}

		return [
			'days' => $days,
			'required_hours' => $required_hours,
			'present_min_hours' => $present_min_hours,
			'half_day_min_hours' => $half_day_min_hours,
			'month' => sprintf('%04d-%02d', $year, $month),
			'staff_id' => $staff_id,
		];
	}

	/**
	 * @param array $cio_rows
	 * @param array $bio_rows
	 * @return array
	 */
	/**
	 * Dates with approved WFH (leave or timesheet WFH code) in range.
	 *
	 * @return array<string, true>
	 */
	public function get_staff_wfh_dates_in_range($staff_id, $from, $to)
	{
		$staff_id = (int) $staff_id;
		$from = date('Y-m-d', strtotime($from));
		$to = date('Y-m-d', strtotime($to));
		$dates = [];
		if ($staff_id <= 0 || $from === '' || $to === '') {
			return $dates;
		}

		$rows = $this->db->query(
			'SELECT start_time, end_time FROM ' . db_prefix() . 'timesheets_requisition_leave
			 WHERE staff_id = ?
			   AND status = 1
			   AND type_of_leave IN ("work-from-home", "WFH", "wfh")
			   AND DATE(end_time) >= ?
			   AND DATE(start_time) <= ?',
			[$staff_id, $from, $to]
		)->result_array();
		foreach ($rows as $row) {
			$cursor = strtotime(date('Y-m-d', strtotime($row['start_time'])));
			$end = strtotime(date('Y-m-d', strtotime($row['end_time'])));
			if ($cursor === false || $end === false) {
				continue;
			}
			while ($cursor <= $end) {
				$d = date('Y-m-d', $cursor);
				if ($d >= $from && $d <= $to) {
					$dates[$d] = true;
				}
				$cursor = strtotime('+1 day', $cursor);
			}
		}

		$ts_rows = $this->db->query(
			'SELECT date_work FROM ' . db_prefix() . 'timesheets_timesheet
			 WHERE staff_id = ? AND date_work BETWEEN ? AND ? AND UPPER(TRIM(type)) = "WFH"',
			[$staff_id, $from, $to]
		)->result_array();
		foreach ($ts_rows as $row) {
			$dates[$row['date_work']] = true;
		}

		return $dates;
	}

	protected function biometric_report_row_has_punches($row)
	{
		if (!is_array($row) || empty($row)) {
			return false;
		}
		if (trim((string) ($row['punch_records'] ?? '')) !== '') {
			return true;
		}
		if ($this->normalize_bio_time($row['a_in_time'] ?? '') !== '') {
			return true;
		}
		if ($this->normalize_bio_time($row['a_out_time'] ?? '') !== '') {
			return true;
		}

		return false;
	}

	/**
	 * Biometric swipe rows for one calendar day (punch_records first, then sheet in/out).
	 *
	 * @param string $date Y-m-d
	 * @param array $bio_by_date
	 * @param array $bio_daily_by_date
	 * @return array<int, array{time:string,type:string,source:string,door:string}>
	 */
	protected function build_calendar_biometric_events_for_date($date, array $bio_by_date, array $bio_daily_by_date)
	{
		$from_daily = [];
		if (isset($bio_daily_by_date[$date])) {
			$bio_day = $bio_daily_by_date[$date];
			$from_daily = $this->parse_biometric_punch_records((string) ($bio_day['punch_records'] ?? ''));
			if (empty($from_daily)) {
				$in_time = $this->normalize_bio_time($bio_day['a_in_time'] ?? '');
				$out_time = $this->normalize_bio_time($bio_day['a_out_time'] ?? '');
				if ($in_time !== '') {
					$from_daily[] = [
						'time' => $in_time,
						'type' => 'IN',
						'source' => 'Biometric',
						'door' => 'Biometrics summary',
					];
				}
				if ($out_time !== '') {
					$from_daily[] = [
						'time' => $out_time,
						'type' => 'OUT',
						'source' => 'Biometric',
						'door' => 'Biometrics summary',
					];
				}
			}
		}

		$from_swipes = $bio_by_date[$date] ?? [];
		// Prefer whichever source has more punch events (avoids stale duplicate bio rows).
		if (count($from_swipes) > count($from_daily)) {
			return $from_swipes;
		}
		if (!empty($from_daily)) {
			return $from_daily;
		}

		return $from_swipes;
	}

	/**
	 * Dates (Y-m-d) in a month that have biometric punches for one staff member.
	 *
	 * @return array<string, true>
	 */
	public function get_biometric_punch_dates_for_staff($staff_id, $from, $to)
	{
		$staff_id = (int) $staff_id;
		$dates = [];
		if ($staff_id <= 0 || !file_exists(APPPATH . 'models/Biometric_model.php')) {
			return $dates;
		}

		$this->load->model('biometric_model');
		if (!method_exists($this->biometric_model, 'get_swipes')) {
			return $dates;
		}

		$swipes = $this->biometric_model->get_swipes([
			'from' => $from,
			'to' => $to,
			'staff' => $staff_id,
		]);
		$wfh_dates = $this->get_staff_wfh_dates_in_range($staff_id, $from, $to);

		foreach ($swipes as $sw) {
			if (!empty($sw['swipe_sort'])) {
				$d = substr($sw['swipe_sort'], 0, 10);
				if (empty($wfh_dates[$d])) {
					$dates[$d] = true;
				}
			}
		}

		$staff_code_row = $this->db->query(
			'SELECT s.staff_identifi, i.empid
			 FROM ' . db_prefix() . 'staff s
			 LEFT JOIN ' . db_prefix() . 'staff_info i ON i.staffid = s.staffid
			 WHERE s.staffid = ?
			 LIMIT 1',
			[$staff_id]
		)->row();
		$codes = [];
		if ($staff_code_row) {
			foreach (['staff_identifi', 'empid'] as $field) {
				$code = trim((string) ($staff_code_row->{$field} ?? ''));
				if ($code !== '') {
					$codes[$code] = true;
				}
			}
		}
		$codes = array_keys($codes);
		if (!empty($codes)) {
			$from_ts = strtotime($from);
			$to_ts = strtotime($to);
			if ($from_ts !== false && $to_ts !== false) {
				$month_suffixes = [];
				$cursor = strtotime(date('Y-m-01', $from_ts));
				$end = strtotime(date('Y-m-01', $to_ts));
				while ($cursor !== false && $end !== false && $cursor <= $end) {
					$month_suffixes[] = '%-' . date('M-Y', $cursor);
					$cursor = strtotime('+1 month', $cursor);
				}
				if (!empty($month_suffixes)) {
					$placeholders = implode(',', array_fill(0, count($codes), '?'));
					$like_sql = implode(' OR ', array_map(function ($suffix) {
						return 'attendance_date LIKE ' . $this->db->escape($suffix);
					}, $month_suffixes));
					$params = $codes;
					$rows = $this->db->query(
						'SELECT attendance_date, punch_records, a_in_time, a_out_time
						 FROM ' . db_prefix() . 'biometric_report
						 WHERE TRIM(employee_code) IN (' . $placeholders . ') AND (' . $like_sql . ')',
						$params
					)->result_array();
					foreach ($rows as $row) {
						if (!$this->biometric_report_row_has_punches($row)) {
							continue;
						}
						$att_dt = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
						if (!$att_dt) {
							continue;
						}
						$d = $att_dt->format('Y-m-d');
						if ($d >= $from && $d <= $to && empty($wfh_dates[$d])) {
							$dates[$d] = true;
						}
					}
				}
			}
		}

		return $dates;
	}

	/**
	 * Merge workroom + biometric punches into a single timeline (full datetime).
	 *
	 * @param array<string, true> $wfh_dates
	 * @return array<int, array{at:int,time:string,type:string,source:string,door:string}>
	 */
	protected function collect_attendance_timeline_events($staff_id, $range_from, $range_to, array $wfh_dates)
	{
		$staff_id = (int) $staff_id;
		$events = [];

		if (file_exists(APPPATH . 'models/Biometric_model.php')) {
			$this->load->model('biometric_model');
			$bio_swipe_count_by_date = [];
			$swipes = $this->biometric_model->get_swipes([
				'from' => $range_from,
				'to' => $range_to,
				'staff' => $staff_id,
			]);
			foreach ($swipes as $sw) {
				if (empty($sw['swipe_sort'])) {
					continue;
				}
				$at = strtotime($sw['swipe_sort']);
				if (!$at) {
					continue;
				}
				$d = date('Y-m-d', $at);
				if (!empty($wfh_dates[$d])) {
					continue;
				}
				$type = strtoupper((string) ($sw['in_out'] ?? ''));
				if ($type !== 'IN' && $type !== 'OUT') {
					continue;
				}
				$events[] = [
					'at' => $at,
					'time' => date('H:i:s', $at),
					'type' => $type,
					'source' => 'Biometric',
					'door' => $sw['door'] ?? 'Biometrics swipe',
				];
				$bio_swipe_count_by_date[$d] = ($bio_swipe_count_by_date[$d] ?? 0) + 1;
			}

			$staff_code_row = $this->db->query(
				'SELECT s.staff_identifi, i.empid
				 FROM ' . db_prefix() . 'staff s
				 LEFT JOIN ' . db_prefix() . 'staff_info i ON i.staffid = s.staffid
				 WHERE s.staffid = ?
				 LIMIT 1',
				[$staff_id]
			)->row();
			$codes = [];
			if ($staff_code_row) {
				foreach (['staff_identifi', 'empid'] as $field) {
					$code = trim((string) ($staff_code_row->{$field} ?? ''));
					if ($code !== '') {
						$codes[$code] = true;
					}
				}
			}
			$codes = array_keys($codes);
			if (!empty($codes)) {
				$month_keys = [];
				$cursor = strtotime($range_from);
				$end = strtotime($range_to);
				while ($cursor && $end && $cursor <= $end) {
					$month_keys[date('M-Y', $cursor)] = true;
					$cursor = strtotime('+1 month', strtotime(date('Y-m-01', $cursor)));
				}
				$placeholders = implode(',', array_fill(0, count($codes), '?'));
				foreach (array_keys($month_keys) as $month_suffix) {
					$params = $codes;
					$params[] = '%-' . $month_suffix;
					$bio_daily_rows = $this->db->query(
						'SELECT attendance_date, a_in_time, a_out_time, punch_records
						FROM ' . db_prefix() . 'biometric_report
						WHERE TRIM(employee_code) IN (' . $placeholders . ') AND attendance_date LIKE ?',
						$params
					)->result_array();
					foreach ($bio_daily_rows as $row) {
						if (!$this->biometric_report_row_has_punches($row)) {
							continue;
						}
						$att_dt = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
						if (!$att_dt) {
							continue;
						}
						$ymd = $att_dt->format('Y-m-d');
						if ($ymd < $range_from || $ymd > $range_to || !empty($wfh_dates[$ymd])) {
							continue;
						}
						$punch_rows = $this->parse_biometric_punch_records((string) ($row['punch_records'] ?? ''));
						$daily_count = count($punch_rows);
						if ($daily_count === 0) {
							$daily_count = (!empty($row['a_in_time']) ? 1 : 0) + (!empty($row['a_out_time']) ? 1 : 0);
						}
						$swipe_count = (int) ($bio_swipe_count_by_date[$ymd] ?? 0);
						if ($swipe_count > 0 && $swipe_count >= $daily_count && $daily_count > 0) {
							continue;
						}
						if (empty($punch_rows)) {
							$in_time = $this->normalize_bio_time($row['a_in_time'] ?? '');
							$out_time = $this->normalize_bio_time($row['a_out_time'] ?? '');
							if ($in_time !== '') {
								$punch_rows[] = ['time' => $in_time, 'type' => 'IN', 'source' => 'Biometric', 'door' => 'Biometrics summary'];
							}
							if ($out_time !== '') {
								$punch_rows[] = ['time' => $out_time, 'type' => 'OUT', 'source' => 'Biometric', 'door' => 'Biometrics summary'];
							}
						}
						foreach ($punch_rows as $pr) {
							$type = strtoupper((string) ($pr['type'] ?? ''));
							if ($type !== 'IN' && $type !== 'OUT') {
								continue;
							}
							$at = strtotime($ymd . ' ' . substr($pr['time'], 0, 8));
							if (!$at) {
								continue;
							}
							$events[] = [
								'at' => $at,
								'time' => date('H:i:s', $at),
								'type' => $type,
								'source' => 'Biometric',
								'door' => $pr['door'] ?? 'Biometrics swipe',
							];
						}
					}
				}
			}
		}

		$bio_calendar_dates = [];
		foreach ($events as $ev) {
			$bio_calendar_dates[date('Y-m-d', $ev['at'])] = true;
		}

		$cio_rows = $this->db->query(
			'SELECT date, type_check
			FROM ' . db_prefix() . 'check_in_out
			WHERE staff_id = ? AND DATE(date) BETWEEN ? AND ?
			ORDER BY date ASC',
			[$staff_id, $range_from, $range_to]
		)->result_array();
		foreach ($cio_rows as $row) {
			$at = strtotime($row['date']);
			if (!$at) {
				continue;
			}
			$d = date('Y-m-d', $at);
			if (!empty($wfh_dates[$d]) || !empty($bio_calendar_dates[$d])) {
				continue;
			}
			$events[] = [
				'at' => $at,
				'time' => date('H:i:s', $at),
				'type' => ((int) $row['type_check'] === 2) ? 'OUT' : 'IN',
				'source' => 'Workroom',
				'door' => 'Workroom check-in/out',
			];
		}

		usort($events, function ($a, $b) {
			return ($a['at'] ?? 0) <=> ($b['at'] ?? 0);
		});

		$deduped = [];
		foreach ($events as $ev) {
			$type = $ev['type'] ?? '';
			if ($type !== 'IN' && $type !== 'OUT') {
				$deduped[] = $ev;
				continue;
			}
			$keep = true;
			if (!empty($deduped)) {
				$prev = $deduped[count($deduped) - 1];
				if (($prev['type'] ?? '') === $type && abs(($ev['at'] ?? 0) - ($prev['at'] ?? 0)) <= 120) {
					if (($ev['source'] ?? '') === 'Biometric' && ($prev['source'] ?? '') === 'Workroom') {
						$deduped[count($deduped) - 1] = $ev;
						$keep = false;
					} else {
						$keep = false;
					}
				}
			}
			if ($keep) {
				$deduped[] = $ev;
			}
		}

		return $deduped;
	}

	/**
	 * @param array<int, array{at:int,time:string,type:string,source:string,door:string}> $events
	 * @return array<int, array{start_at:int,start_date:string,end_at:int,events:array}>
	 */
	protected function build_attendance_sessions(array $events, $window_hours)
	{
		$window_sec = max(1, (float) $window_hours) * 3600;
		$sessions = [];
		$n = count($events);
		$i = 0;

		while ($i < $n) {
			while ($i < $n && ($events[$i]['type'] ?? '') !== 'IN') {
				$i++;
			}
			if ($i >= $n) {
				break;
			}

			$start_at = (int) $events[$i]['at'];
			$end_at = $start_at + (int) $window_sec;
			$session_events = [];

			while ($i < $n && (int) $events[$i]['at'] <= $end_at) {
				$session_events[] = $events[$i];
				$i++;
			}

			if (!empty($session_events)) {
				$sessions[] = [
					'start_at' => $start_at,
					'start_date' => date('Y-m-d', $start_at),
					'end_at' => $end_at,
					'events' => $session_events,
				];
			}
		}

		return $sessions;
	}

	/**
	 * @param array<int, array{start_at:int,start_date:string,end_at:int,events:array}> $sessions
	 * @return array<string, array{start_at:int,start_date:string,end_at:int,events:array}>
	 */
	protected function index_attendance_sessions_by_start_date(array $sessions)
	{
		$by = [];
		foreach ($sessions as $session) {
			$d = $session['start_date'] ?? '';
			if ($d === '') {
				continue;
			}
			if (!isset($by[$d]) || count($session['events']) > count($by[$d]['events'])) {
				$by[$d] = $session;
			}
		}

		return $by;
	}

	/**
	 * Hours/break from a 13h attendance session (first IN anchor, break = OUT→IN gaps).
	 *
	 * @param array<int, array{at:int,time:string,type:string,source:string,door:string}> $events
	 */
	protected function analyze_session_punches(array $events)
	{
		if (empty($events)) {
			return $this->analyze_day_punches([], [], 'Workroom');
		}

		usort($events, function ($a, $b) {
			return ($a['at'] ?? 0) <=> ($b['at'] ?? 0);
		});

		$deduped = [];
		foreach ($events as $ev) {
			$type = $ev['type'] ?? '';
			if ($type !== 'IN' && $type !== 'OUT') {
				$deduped[] = $ev;
				continue;
			}
			$keep = true;
			if (!empty($deduped)) {
				$prev = $deduped[count($deduped) - 1];
				if (($prev['type'] ?? '') === $type && abs(($ev['at'] ?? 0) - ($prev['at'] ?? 0)) <= 120) {
					$keep = false;
				}
			}
			if ($keep) {
				$deduped[] = $ev;
			}
		}
		$events = $deduped;

		$first_in_at = null;
		foreach ($events as $ev) {
			if (($ev['type'] ?? '') === 'IN') {
				$first_in_at = (int) $ev['at'];
				break;
			}
		}
		if ($first_in_at !== null) {
			$events = array_values(array_filter($events, function ($ev) use ($first_in_at) {
				if (($ev['type'] ?? '') !== 'OUT') {
					return true;
				}
				return (int) ($ev['at'] ?? 0) > $first_in_at;
			}));
		}

		$ins = [];
		$outs = [];
		foreach ($events as $ev) {
			if (($ev['type'] ?? '') === 'OUT') {
				$outs[] = $ev;
			} elseif (($ev['type'] ?? '') === 'IN') {
				$ins[] = $ev;
			}
		}

		$check_in = $ins ? date('H:i', (int) $ins[0]['at']) : '';
		$check_out = $outs ? date('H:i', (int) $outs[count($outs) - 1]['at']) : '';

		$break_hrs = 0.0;
		for ($j = 0, $c = count($events); $j < $c - 1; $j++) {
			if (($events[$j]['type'] ?? '') === 'OUT' && ($events[$j + 1]['type'] ?? '') === 'IN') {
				$break_hrs += max(0, ((int) $events[$j + 1]['at'] - (int) $events[$j]['at']) / 3600);
			}
		}

		$span_hrs = 0.0;
		if ($check_in && $check_out && !empty($ins[0]['at']) && !empty($outs[count($outs) - 1]['at'])) {
			$in_at = (int) $ins[0]['at'];
			$out_at = (int) $outs[count($outs) - 1]['at'];
			if ($out_at > $in_at) {
				$span_hrs = ($out_at - $in_at) / 3600;
			}
		}

		$hours = max(0, $span_hrs - $break_hrs);
		if ($hours > 16) {
			$hours = 16;
			$span_hrs = min($span_hrs, 16 + $break_hrs);
		}

		$punch_missing = false;
		if (count($events) > 0) {
			if ((count($ins) > 0 && count($outs) === 0) || (count($outs) > 0 && count($ins) === 0)) {
				$punch_missing = true;
			} elseif (count($ins) !== count($outs)) {
				$punch_missing = true;
			}
		}

		$swipes = [];
		foreach ($events as $ev) {
			$swipes[] = [
				'time' => date('H:i:s', (int) ($ev['at'] ?? 0)),
				'type' => $ev['type'] ?: '—',
				'source' => $ev['source'] ?? '',
				'door' => $ev['door'] ?? '',
			];
		}

		$ins_times = array_map(function ($ev) {
			return date('H:i:s', (int) $ev['at']);
		}, $ins);
		$outs_times = array_map(function ($ev) {
			return date('H:i:s', (int) $ev['at']);
		}, $outs);

		return [
			'ins' => $ins_times,
			'outs' => $outs_times,
			'check_in' => $check_in,
			'check_out' => $check_out,
			'hours' => $hours,
			'break_hrs' => round($break_hrs, 2),
			'span_hrs' => round($span_hrs, 2),
			'break_from_punches' => true,
			'punch_missing' => $punch_missing,
			'swipes' => $swipes,
		];
	}

	protected function analyze_day_punches($cio_rows, $bio_rows, $workroom_source = 'Workroom')
	{
		if (!empty($bio_rows)) {
			$cio_rows = [];
		}

		$workroom_source = trim((string) $workroom_source);
		if ($workroom_source === '') {
			$workroom_source = 'Workroom';
		}
		$workroom_door = ($workroom_source === 'WFH') ? 'Workroom (WFH)' : 'Workroom check-in/out';

		$events = [];

		foreach ($cio_rows as $row) {
			$events[] = [
				'time' => $row['t'],
				'type' => ((int) $row['type_check'] === 2) ? 'OUT' : 'IN',
				'source' => $workroom_source,
				'door' => $workroom_door,
			];
		}

		foreach ($bio_rows as $row) {
			$events[] = [
				'time' => $row['time'],
				'type' => strtoupper((string) ($row['type'] ?? '')),
				'source' => $row['source'] ?? 'Biometric',
				'door' => $row['door'] ?? 'Biometrics swipe',
			];
		}

		usort($events, function ($a, $b) {
			return strcmp($a['time'], $b['time']);
		});

		// Drop near-duplicate punches (same type within 2 minutes) — bio + Workroom often double-log.
		$deduped = [];
		foreach ($events as $ev) {
			$type = $ev['type'];
			if ($type !== 'IN' && $type !== 'OUT') {
				$deduped[] = $ev;
				continue;
			}
			$ts = strtotime('1970-01-01 ' . substr($ev['time'], 0, 8));
			$keep = true;
			if (!empty($deduped)) {
				$prev = $deduped[count($deduped) - 1];
				if ($prev['type'] === $type) {
					$prev_ts = strtotime('1970-01-01 ' . substr($prev['time'], 0, 8));
					if (abs($ts - $prev_ts) <= 120) {
						$keep = false;
					}
				}
			}
			if ($keep) {
				$deduped[] = $ev;
			}
		}
		$events = $deduped;

		$ins = [];
		$outs = [];
		foreach ($events as $ev) {
			if ($ev['type'] === 'OUT') {
				$outs[] = $ev['time'];
			} elseif ($ev['type'] === 'IN') {
				$ins[] = $ev['time'];
			}
		}

		$check_in = $ins ? $ins[0] : '';
		$check_out = $outs ? $outs[count($outs) - 1] : '';

		// Ignore OUT punches that occur before the first IN (stale Workroom / night-shift bleed).
		if ($check_in && $check_out) {
			$in_ts = strtotime('1970-01-01 ' . substr($check_in, 0, 8));
			$out_ts = strtotime('1970-01-01 ' . substr($check_out, 0, 8));
			if ($in_ts !== false && $out_ts !== false && $out_ts <= $in_ts) {
				$check_out = '';
				$outs = array_values(array_filter($outs, function ($t) use ($in_ts) {
					$ts = strtotime('1970-01-01 ' . substr($t, 0, 8));
					return $ts !== false && $ts > $in_ts;
				}));
				if ($outs) {
					$check_out = $outs[count($outs) - 1];
				}
			}
		}

		if (!$check_in && !empty($ins)) {
			$check_in = $ins[0];
		}
		if (!empty($ins)) {
			$first_in_ts = strtotime('1970-01-01 ' . substr($ins[0], 0, 8));
			if ($first_in_ts !== false) {
				$events = array_values(array_filter($events, function ($ev) use ($first_in_ts) {
					if (($ev['type'] ?? '') !== 'OUT') {
						return true;
					}
					$ts = strtotime('1970-01-01 ' . substr($ev['time'], 0, 8));
					return $ts === false || $ts > $first_in_ts;
				}));
				$outs = [];
				foreach ($events as $ev) {
					if (($ev['type'] ?? '') === 'OUT') {
						$outs[] = $ev['time'];
					}
				}
				$check_out = $outs ? $outs[count($outs) - 1] : '';
			}
		}
		if (!$check_out && count($events) > 1) {
			// Prefer last OUT; if day ends on IN with no later OUT, use last event only if OUT.
			$check_out = $outs ? $outs[count($outs) - 1] : '';
			if ($check_out === '' && $events[count($events) - 1]['type'] === 'OUT') {
				$check_out = $events[count($events) - 1]['time'];
			}
		} elseif (!$check_out && count($events) === 1 && $events[0]['type'] === 'OUT') {
			$check_out = $events[0]['time'];
		}

		// Card / status hours: ONLY first IN → last OUT (ignore intermediate punches).
		$hours = 0.0;
		$span_hrs = 0.0;
		$break_hrs = 0.0;
		if ($check_in && $check_out) {
			$in_ts = strtotime('1970-01-01 ' . substr($check_in, 0, 8));
			$out_ts = strtotime('1970-01-01 ' . substr($check_out, 0, 8));
			if ($out_ts > $in_ts) {
				$span_hrs = ($out_ts - $in_ts) / 3600;
				$hours = $span_hrs;
			}
		}

		// Guard against absurd values from bad punch data.
		if ($hours > 16) {
			$hours = 16;
			$span_hrs = min($span_hrs, 16);
		}

		$punch_missing = false;
		if (count($events) > 0) {
			if ((count($ins) > 0 && count($outs) === 0) || (count($outs) > 0 && count($ins) === 0)) {
				$punch_missing = true;
			} elseif (count($ins) !== count($outs)) {
				$punch_missing = true;
			}
		}

		$swipes = [];
		foreach ($events as $ev) {
			$swipes[] = [
				'time' => substr($ev['time'], 0, 8),
				'type' => $ev['type'] ?: '—',
				'source' => $ev['source'],
				'door' => $ev['door'],
			];
		}

		return [
			'ins' => $ins,
			'outs' => $outs,
			'check_in' => $check_in ? substr($check_in, 0, 5) : '',
			'check_out' => $check_out ? substr($check_out, 0, 5) : '',
			'hours' => $hours,
			'break_hrs' => round($break_hrs, 2),
			'span_hrs' => round($span_hrs, 2),
			'punch_missing' => $punch_missing,
			'swipes' => $swipes,
		];
	}

	/**
	 * Parse Biometric punch_records string into swipe rows.
	 * Example: "10:48 (in), 11:21 (out), 11:26 (in), ..."
	 *
	 * @param string $punch_records
	 * @return array
	 */
	protected function parse_biometric_punch_records($punch_records)
	{
		$punch_records = trim((string) $punch_records);
		if ($punch_records === '') {
			return [];
		}

		$rows = [];
		$parts = preg_split('/\s*,\s*/', $punch_records);
		foreach ($parts as $punch) {
			$punch = trim($punch);
			if ($punch === '') {
				continue;
			}
			if (!preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)\s*(?:\()?[\s\-]*(in|out)(?:\))?/i', $punch, $m)
				&& !preg_match('/\b(in|out)\b\s*[:\-]?\s*(\d{1,2}:\d{2}(?::\d{2})?)/i', $punch, $m2)) {
				continue;
			}
			if (!empty($m)) {
				$time = $m[1];
				$dir = strtoupper($m[2]);
			} else {
				$dir = strtoupper($m2[1]);
				$time = $m2[2];
			}
			if (strlen($time) === 5) {
				$time .= ':00';
			}
			$rows[] = [
				'time' => $time,
				'type' => $dir,
				'source' => 'Biometric',
				'door' => 'Biometrics swipe',
			];
		}

		return $rows;
	}

	protected function normalize_bio_time($time)
	{
		$time = trim((string) $time);
		if ($time === '' || $time === '-' || $time === '00:00:00' || $time === '00:00') {
			return '';
		}

		$ts = strtotime($time);
		if ($ts !== false) {
			return date('H:i:s', $ts);
		}

		if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $time, $m)) {
			$t = $m[1];
			return strlen($t) === 5 ? $t . ':00' : $t;
		}

		return '';
	}

	protected function normalize_shift_time($time, $fallback = '')
	{
		$time = trim((string) $time);
		if ($time === '' || $time === '00:00:00' || $time === '00:00') {
			return $fallback;
		}

		return substr($time, 0, 5);
	}

	protected function map_attendance_display_code($status, $ts_type = '', $hours = 0)
	{
		$this->load->helper('timesheets/timesheets');
		$type_map = [
			'AL' => 'EL',
			'EL' => 'EL',
			'L' => 'EL',
			'PL' => 'EL',
			'SL' => 'SL',
			'HO' => 'HO',
			'HL' => 'HO',
			'ML' => 'MAL',
			'CO' => 'CO',
			'UHL' => 'UHD',
			'PHL' => 'PHD',
			'HD' => 'HD',
			'UL' => 'UL',
			'LOP' => 'LOP',
			'AB' => 'AB',
			'SHL' => 'SHL',
			'PAL' => 'PAL',
		];

		if ($status === 'saturday_leave') {
			return 'SL';
		}
		if ($status === 'holiday') {
			return 'HO';
		}
		if ($status === 'weekend') {
			return 'O';
		}
		// Hour-based status wins over stale timesheet AB / rejected regularization.
		if ($status === 'half_day') {
			return 'HD';
		}
		if ($status === 'ok' || $status === 'regularised') {
			return 'P';
		}
		if ($status === 'pending') {
			return 'P';
		}
		if ($status === 'short_hours') {
			return timesheets_attendance_code_from_hours($hours);
		}
		if ($status === 'absent') {
			return 'AB';
		}
		if ($status === 'punch_missing') {
			return timesheets_attendance_code_from_hours($hours);
		}
		// Rejected regularization: still show real hours result, not forced Absent.
		if ($status === 'rejected') {
			return timesheets_attendance_code_from_hours($hours);
		}
		// Only map leave-type timesheet codes (never let AB/P override punch hours).
		$leave_ts = ['AL', 'EL', 'L', 'PL', 'SL', 'UL', 'LOP', 'CO', 'ML', 'UHL', 'PHL', 'SHL', 'PAL', 'HO', 'HL'];
		if ($status === 'leave' || ($ts_type !== '' && in_array(strtoupper((string) $ts_type), $leave_ts, true))) {
			$key = strtoupper((string) $ts_type);
			return $type_map[$key] ?? 'EL';
		}

		return '';
	}

	/**
	 * Company rule: >= present min = Present, >= half-day min = Half day, below = Absent.
	 *
	 * @param float $hours
	 * @param float $present_min_hours
	 * @param float $half_day_min_hours
	 * @return array|null
	 */
	protected function classify_attendance_by_hours($hours, $present_min_hours = 8.0, $half_day_min_hours = 5.0)
	{
		$hours = (float) $hours;
		$present_min_hours = (float) $present_min_hours;
		$half_day_min_hours = (float) $half_day_min_hours;
		if ($hours <= 0) {
			return [
				'status' => 'absent',
				'issue' => 'No attendance recorded',
				'can_regularise' => true,
			];
		}
		if ($hours + 0.001 >= $present_min_hours) {
			return [
				'status' => 'ok',
				'issue' => '',
				'can_regularise' => false,
			];
		}
		if ($hours + 0.001 >= $half_day_min_hours) {
			return [
				'status' => 'half_day',
				'issue' => 'Half day (' . round($hours, 2) . 'h — present needs ' . $present_min_hours . 'h)',
				'can_regularise' => true,
			];
		}

		return [
			'status' => 'absent',
			'issue' => 'Less than ' . $half_day_min_hours . 'h (' . round($hours, 2) . 'h) — counted as Absent',
			'can_regularise' => true,
		];
	}

	protected function map_attendance_status_label($status, $ts_type = '', $code = '')
	{
		if ($status === 'punch_missing') {
			return 'Incomplete punch';
		}
		if ($status === 'short_hours' && $code === 'P') {
			return 'Present (check hours)';
		}
		if ($status === 'half_day' || $code === 'HD') {
			return 'Half day';
		}

		$labels = [
			'P' => 'Present',
			'AB' => 'Absent',
			'HD' => 'Half day',
			'HO' => 'Holiday',
			'UL' => 'Unplanned leave',
			'PL' => 'Planned leave',
			'SL' => 'Saturday leave',
			'SHL' => 'Short leave',
			'PHD' => 'Paid half day',
			'UHD' => 'Unpaid half day',
			'PAL' => 'Paternity leave',
			'MAL' => 'Maternity leave',
			'O' => 'Off',
			'EL' => 'Earned leave',
			'LOP' => 'Loss of pay',
			'L' => 'Earned leave',
		];

		if ($code && isset($labels[$code])) {
			return $labels[$code];
		}

		if ($status === 'pending') {
			return 'Regularization pending';
		}
		if ($status === 'regularised') {
			return 'Regularized';
		}

		return $labels[$code] ?? ucfirst(str_replace('_', ' ', $status));
	}

	protected function build_attendance_day_detail($punch, $shift_info, $hours, $required_hours)
	{
		$shift_start = $this->normalize_shift_time($shift_info->start_working ?? '', '09:30');
		$shift_end = $this->normalize_shift_time($shift_info->end_working ?? '', '18:30');
		$lunch_start = $this->normalize_shift_time($shift_info->start_lunch_break ?? '', '13:00');
		$lunch_end = $this->normalize_shift_time($shift_info->end_lunch_break ?? '', '13:30');

		if (!empty($punch['break_from_punches'])) {
			$break_hrs = (float) ($punch['break_hrs'] ?? 0);
		} else {
			// Break = shift lunch only (legacy calendar-day punches).
			$break_hrs = (float) ($shift_info->lunch_break_hour ?? 0);
			if ($break_hrs <= 0) {
				$break_hrs = max(0, (strtotime('1970-01-01 ' . $lunch_end) - strtotime('1970-01-01 ' . $lunch_start)) / 3600);
			}
			if ($break_hrs <= 0) {
				$break_hrs = 0.5;
			}
		}

		$first_in = $punch['check_in'] ?: '—';
		$last_out = $punch['check_out'] ?: '—';
		$late_in = '—';
		$early_out = '—';

		if ($punch['check_in'] && strtotime($punch['check_in']) > strtotime($shift_start)) {
			$late_mins = round((strtotime($punch['check_in']) - strtotime($shift_start)) / 60);
			$late_in = $late_mins . ' min';
		}
		if ($punch['check_out'] && strtotime($punch['check_out']) < strtotime($shift_end)) {
			$early_mins = round((strtotime($shift_end) - strtotime($punch['check_out'])) / 60);
			$early_out = $early_mins . ' min';
		}

		// Total = first IN → last OUT. Actual = total − lunch break.
		$span = (float) ($punch['span_hrs'] ?? 0);
		if ($span <= 0) {
			$span = max(0, (float) $hours);
		}
		$actual = max(0, $span - $break_hrs);

		$sessions = [
			[
				'label' => 'Session 1 (' . $shift_start . ' - ' . $lunch_start . ')',
				'first_in' => ($first_in !== '—' && strtotime($first_in) <= strtotime($lunch_start)) ? $first_in : '—',
				'last_out' => ($last_out !== '—' && strtotime($last_out) <= strtotime($lunch_end)) ? $last_out : '—',
			],
			[
				'label' => 'Session 2 (' . $lunch_end . ' - ' . $shift_end . ')',
				'first_in' => ($first_in !== '—' && strtotime($first_in) >= strtotime($lunch_end)) ? $first_in : (($last_out !== '—' && strtotime($last_out) > strtotime($lunch_start)) ? $first_in : '—'),
				'last_out' => ($last_out !== '—' && strtotime($last_out) > strtotime($lunch_start)) ? $last_out : '—',
			],
		];

		return [
			'shift_start' => $shift_start,
			'shift_end' => $shift_end,
			'shift_scheme' => 'General',
			'first_in' => $first_in,
			'last_out' => $last_out,
			'late_in' => $late_in,
			'early_out' => $early_out,
			'actual_work_hrs_num' => round($actual, 2),
			'total_work_hrs' => $span > 0 ? round($span, 2) . 'h' : '—',
			'break_hrs' => $break_hrs > 0 ? round($break_hrs, 2) . 'h' : '—',
			'actual_work_hrs' => $actual > 0 ? round($actual, 2) . 'h' : '—',
			'sessions' => $sessions,
		];
	}
}
