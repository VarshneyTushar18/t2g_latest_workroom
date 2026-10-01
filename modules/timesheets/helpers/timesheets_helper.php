<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('after_email_templates', 'add_timesheets_email_templates');



/**

 * Check whether column exists in a table

 * Custom function because Codeigniter is caching the tables and this is causing issues in migrations

 * @param  string $column column name to check

 * @param  string $table table name to check

 * @return boolean

 */



function get_timesheets_option($name) {

	$CI = &get_instance();

	static $options = [];

	$name = trim($name);

	if (!array_key_exists($name, $options)) {

		$CI->db->select('option_val');

		$CI->db->where('option_name', $name);

		$row = $CI->db->get(db_prefix() . 'timesheets_option')->row();

		$options[$name] = $row ? $row->option_val : '';

	}



	return hooks()->apply_filters('get_timesheets_option', $options[$name], $name);

}



/**

 * row timesheets options exist

 * @param  string $name

 * @return

 */

function row_timesheets_options_exist($name) {

	$CI = &get_instance();

	$i = count($CI->db->query('Select * from ' . db_prefix() . 'timesheets_option where option_name = ' . $name)->result_array());

	if ($i == 0) {

		return 0;

	}

	if ($i > 0) {

		return 1;

	}

}



/**

 * handle timesheets attachments array

 * @param  int $staffid

 * @param  string $index_name

 * @return

 */

function handle_timesheets_attachments_array($staffid, $index_name = 'attachments') {

	$uploaded_files = [];

	$path = TIMESHEETS_MODULE_UPLOAD_FOLDER . '/' . $staffid . '/';

	$CI = &get_instance();

	if (isset($_FILES[$index_name]['name'])

		&& ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {

		if (!is_array($_FILES[$index_name]['name'])) {

			$_FILES[$index_name]['name'] = [$_FILES[$index_name]['name']];

			$_FILES[$index_name]['type'] = [$_FILES[$index_name]['type']];

			$_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];

			$_FILES[$index_name]['error'] = [$_FILES[$index_name]['error']];

			$_FILES[$index_name]['size'] = [$_FILES[$index_name]['size']];

		}



		_file_attachments_index_fix($index_name);

		for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {

			// Get the temp file path

			$tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];



			// Make sure we have a filepath

			if (!empty($tmpFilePath) && $tmpFilePath != '') {

				if (_perfex_upload_error($_FILES[$index_name]['error'][$i])

					|| !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {

					continue;

				}



				_maybe_create_upload_path($path);

				$filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);

				$newFilePath = $path . $filename;



				// Upload the file into the temp dir

				if (move_uploaded_file($tmpFilePath, $newFilePath)) {

					array_push($uploaded_files, [

						'file_name' => $filename,

						'filetype' => $_FILES[$index_name]['type'][$i],

					]);

					if (is_image($newFilePath)) {

						create_img_thumb($path, $filename);

					}

				}

			}

		}

	}



	if (count($uploaded_files) > 0) {

		return $uploaded_files;

	}



	return false;

}



/**

 * render timesheets yes/no option

 * @param  int $option_value

 * @param  string $label

 * @param  string $tooltip

 * @param  string $replace_yes_text

 * @param  string $replace_no_text

 * @param  string $replace_1

 * @param  string $replace_0

 * @return

 */

function render_timesheets_yes_no_option($option_value, $label, $tooltip = '', $replace_yes_text = '', $replace_no_text = '', $replace_1 = '', $replace_0 = '') {

	ob_start();?>

    <div class="form-group">

        <label for="<?php echo html_entity_decode($option_value); ?>" class="control-label clearfix">

            <?php echo ($tooltip != '' ? '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . _l($tooltip, '', false) . '"></i> ' : '') . _l($label, '', false); ?>

        </label>

        <div class="radio radio-primary radio-inline">

            <input type="radio" id="y_opt_1_<?php echo html_entity_decode($label); ?>" name="timesheets_setting[<?php echo html_entity_decode($option_value); ?>]" value="<?php echo html_entity_decode($replace_1) == '' ? 1 : $replace_1; ?>" <?php if (get_timesheets_option($option_value) == ($replace_1 == '' ? '1' : $replace_1)) {

		echo 'checked';

	}?>>

            <label for="y_opt_1_<?php echo html_entity_decode($label); ?>">

                <?php echo html_entity_decode($replace_yes_text) == '' ? _l('settings_yes') : $replace_yes_text; ?>

            </label>

        </div>

        <div class="radio radio-primary radio-inline">

            <input type="radio" id="y_opt_2_<?php echo html_entity_decode($label); ?>" name="timesheets_setting[<?php echo html_entity_decode($option_value); ?>]" value="<?php echo html_entity_decode($replace_0) == '' ? 0 : $replace_0; ?>" <?php if (get_timesheets_option($option_value) == ($replace_0 == '' ? '0' : $replace_0)) {

		echo 'checked';

	}?>>

            <label for="y_opt_2_<?php echo html_entity_decode($label); ?>">

                <?php echo html_entity_decode($replace_no_text) == '' ? _l('settings_no') : $replace_no_text; ?>

            </label>

        </div>

    </div>

    <?php

$settings = ob_get_contents();

	ob_end_clean();

	echo html_entity_decode($settings);

}



/**

 * timesheets reformat currency asset

 * @param  int $value

 * @return

 */

function timesheets_reformat_currency_asset($value) {

	return str_replace(',', '', $value);

}



/**

 * get type of leave name

 * @param  int $id

 * @return

 */

function get_type_of_leave_name($id) {

	$name = '';

	switch ($id) {

	case 1:

		$name = _l('sick_leave');

		break;

	case 2:

		$name = _l('maternity_leave');

		break;

	case 3:

		$name = _l('private_work_with_pay');

		break;

	case 4:

		$name = _l('private_work_without_pay');

		break;

	case 5:

		$name = _l('child_sick');

		break;

	case 6:

		$name = _l('power_outage');

		break;

	case 7:

		$name = _l('meeting_or_studying');

		break;

	case 8:

		$name = _l('annual_leave');

		break;

	}

	return $name;

}



/**

 * handle requisition attachments

 * @param  int $id

 * @return

 */

function handle_requisition_attachments($id) {

	if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {

		header('HTTP/1.0 400 Bad error');

		echo _perfex_upload_error($_FILES['file']['error']);

		die;

	}

	$path = TIMESHEETS_MODULE_UPLOAD_FOLDER . '/requisition_leave/' . $id . '/';

	$CI = &get_instance();



	if (isset($_FILES['file']['name'])) {

		hooks()->do_action('before_upload_expense_attachment', $id);

		// Get the temp file path

		$tmpFilePath = $_FILES['file']['tmp_name'];

		// Make sure we have a filepath

		if (!empty($tmpFilePath) && $tmpFilePath != '') {

			_maybe_create_upload_path($path);

			$filename = $_FILES['file']['name'];

			$newFilePath = $path . $filename;

			// Upload the file into the temp dir

			if (move_uploaded_file($tmpFilePath, $newFilePath)) {

				$attachment = [];

				$attachment[] = [

					'file_name' => $filename,

					'filetype' => $_FILES['file']['type'],

				];



				$rs = $CI->misc_model->add_attachment_to_database($id, 'requisition', $attachment);

				return $rs;

			}

		}

	}

}

if (!function_exists('add_timesheets_email_templates')) {

	/**

	 * Init appointly email templates and assign languages

	 * @return void

	 */

	function add_timesheets_email_templates() {

		$CI = &get_instance();



		$data['timesheets_attendance_mgt_templates'] = $CI->emails_model->get(['type' => 'timesheets_attendance_mgt', 'language' => 'english']);



		$CI->load->view('timesheets/email_templates', $data);

	}

}

/**

 * crawl get

 * @param  string &$curl

 * @param  string $link

 * @param  string $header

 * @return string

 */

function crawl_get(&$curl, $link, $header = null) {

	$cookie_file = dirname(__FILE__) . '/' . 'cookie.txt';

	curl_setopt($curl, CURLOPT_URL, $link);

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($curl, CURLOPT_AUTOREFERER, true);

	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);

	curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);

	curl_setopt($curl, CURLOPT_COOKIESESSION, true);

	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36');

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($curl, CURLOPT_TIMEOUT, 120);

	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);

	curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

	if (isset($header)) {

		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

	}

	return curl_exec($curl);

}

/**

 * address2geo

 * @param  string

 * @return json

 */

function address2geo($address) {

	$googlemap_api_key = '';

	$api_key = get_timesheets_option('googlemap_api_key');

	if ($api_key) {

		$googlemap_api_key = $api_key;

	}

	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . rawurlencode($address) . "&key=" . $googlemap_api_key;

	$curl = curl_init();

	$curlData = crawl_get($curl, $url);

	$geo = json_decode($curlData);

	if (isset($geo) && isset($geo->results[0])) {

		return json_encode($geo->results[0]->geometry->location);

	}

	return '';

}

/**

 * get workplace name

 * @param  $workplace_id

 * @return string $name

 */

function get_workplace_name($workplace_id) {

	$CI = &get_instance();

	$data = $CI->timesheets_model->get_workplace($workplace_id);

	$name = '';

	if ($data) {

		$name = $data->name;

	}

	return $name;

}



/**

 * list timesheet permisstion

 * @return [type]

 */

function list_timesheet_permisstion() {

	$timesheet_permission = [];

	// Attendance

	$timesheet_permission[] = 'attendance_management';

	// Leave

	$timesheet_permission[] = 'leave_management';

	// Route

	$timesheet_permission[] = 'route_management';

	// Additional timesheets

	$timesheet_permission[] = 'additional_timesheets_management';

	// Work Shift Table

	$timesheet_permission[] = 'table_shiftwork_management';

	// Report

	$timesheet_permission[] = 'report_management';

	// Workplace

	$timesheet_permission[] = 'table_workplace_management';

	return $timesheet_permission;

}



/**

 * timesheet get staff id permissions

 * @return array

 */

function timesheet_get_staff_id_permissions() {

	$CI = &get_instance();

	$array_staff_id = [];

	$index = 0;



	$str_permissions = '';

	foreach (list_timesheet_permisstion() as $per_key => $per_value) {

		if (strlen($str_permissions) > 0) {

			$str_permissions .= ",'" . $per_value . "'";

		} else {

			$str_permissions .= "'" . $per_value . "'";

		}



	}



	$sql_where = "SELECT distinct staff_id FROM " . db_prefix() . "staff_permissions

        where feature IN (" . $str_permissions . ")

        ";



	$staffs = $CI->db->query($sql_where)->result_array();



	if (count($staffs) > 0) {

		foreach ($staffs as $key => $value) {

			$array_staff_id[$index] = $value['staff_id'];

			$index++;

		}

	}

	return $array_staff_id;

}



/**

 * get staff id not permissions

 * @return array

 */

function timesheet_get_staff_id_not_permissions() {

	$CI = &get_instance();

	$CI->db->where('admin != ', 1);

	// hide inactive staff
	$CI->db->where('active', 1);


	if (count(timesheet_get_staff_id_permissions()) > 0) {

		$CI->db->where_not_in('staffid', timesheet_get_staff_id_permissions());

	}

	return $CI->db->get(db_prefix() . 'staff')->result_array();



}



/**

 * get status modules

 * @param  string $module_name

 * @return boolean

 */

function timesheet_get_status_modules($module_name) {

	$CI = &get_instance();



	$sql = 'select * from ' . db_prefix() . 'modules where module_name = "' . $module_name . '" AND active =1 ';

	$module = $CI->db->query($sql)->row();

	if ($module) {

		return true;

	} else {

		return false;

	}

}

/**

 *[timesheet staff manager query

 * @param  string $permission

 * @param  string $column

 * @param  string $and

 * @return string

 */

function timesheet_staff_manager_query($permission, $column = 'staffid', $and = 'AND') {

	$query = '';

	$CI = &get_instance();

	// adding custom made attendence manage permission function attendance_permission()

	// if (!is_admin() && !has_permission($permission, '', 'view')) {
	if (!is_admin() && !has_permission($permission, '', 'view') && !attendance_permission()) {

		$space = '';

		if ($and != '') {

			$space = ' ';

		}

		if (timesheet_get_status_modules('hr_profile') == true) {

			$CI->load->model('hr_profile/hr_profile_model');

			$list_staff = $CI->hr_profile_model->get_staff_by_manager();

			$list_staff = implode(',', $list_staff);

			$query = $space . $and . $space . $column . ' IN (' . $list_staff . ')';

		} else {

			$query = $space . $and . $space . $column . ' = ' . get_staff_user_id() . '';

		}

	}

	return $query;

}

if (!function_exists('cal_days_in_month')) {

	define('CAL_GREGORIAN', 0);

	function cal_days_in_month($calendar, $month, $year) {

		return date('t', mktime(0, 0, 0, $month, 1, $year));

	}

}

/**

 * get client IP

 * @return string

 */

function get_client_ip() {

	//whether ip is from the share internet

	$ip = '';

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

		$ip = $_SERVER['HTTP_CLIENT_IP'];

	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

	} else {

		$ip = $_SERVER['REMOTE_ADDR'];

	}

	return $ip;

}

/**
 * HR / Admin can view all employees in leave & attendance views.
 */
function timesheets_hr_can_view_all_staff($staff_id = '')
{
	return is_admin($staff_id) || is_HR($staff_id) || is_super_hr($staff_id);
}

/**
 * Staff IDs the current user may view (self + direct/indirect reports).
 * Returns null when HR/Admin (all active staff).
 *
 * @param int|string $viewer_id
 * @return array<int>|null
 */
function timesheets_get_team_staff_ids($viewer_id = '')
{
	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;

	if (timesheets_hr_can_view_all_staff($viewer_id)) {
		return null;
	}

	$ids = [$viewer_id];

	if (timesheet_get_status_modules('hr_profile') == true) {
		$CI = &get_instance();
		$CI->load->model('hr_profile/hr_profile_model');
		$team = $CI->hr_profile_model->get_staff_by_manager();
		if (is_array($team) && count($team) > 0) {
			$ids = array_merge($ids, array_map('intval', $team));
		}
	} else {
		$CI = &get_instance();
		$reports = $CI->db->select('staffid')
			->from(db_prefix() . 'staff')
			->where('team_manage', $viewer_id)
			->where('active', 1)
			->get()
			->result_array();
		foreach ($reports as $row) {
			$ids[] = (int) $row['staffid'];
		}
	}

	// Always keep the viewer — managers/HODs must see their own attendance.
	$ids[] = $viewer_id;

	return array_values(array_unique(array_filter(array_map('intval', $ids))));
}

/**
 * True when user manages at least one other active staff member.
 */
function timesheets_is_team_manager($staff_id = '')
{
	$staff_id = ($staff_id === '') ? (int) get_staff_user_id() : (int) $staff_id;

	if (timesheets_hr_can_view_all_staff($staff_id)) {
		return false;
	}

	$team = timesheets_get_team_staff_ids($staff_id);

	return is_array($team) && count($team) > 1;
}

/**
 * May the viewer open this employee's leave/attendance data?
 */
function timesheets_can_view_staff($target_staff_id, $viewer_id = '')
{
	$target_staff_id = (int) $target_staff_id;
	if ($target_staff_id <= 0) {
		return false;
	}

	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;

	if ($target_staff_id === $viewer_id) {
		return true;
	}

	if (timesheets_hr_can_view_all_staff($viewer_id)) {
		return true;
	}

	$team = timesheets_get_team_staff_ids($viewer_id);

	return is_array($team) && in_array($target_staff_id, $team, true);
}

/**
 * Named staff who may approve attendance regularization (in addition to HR / Super Admin roles).
 * Kept for reference; final approval is role-based (HR / Super Admin) only.
 *
 * @return int[]
 */
function timesheets_attendance_approval_staff_ids()
{
	return [144, 1194, 178];
}

/**
 * Final approval of attendance regularization — HR Admin / Super HR / Admin / Super Admin only.
 * HOD / Manager cannot final-approve.
 *
 * @param int|string $staff_id
 * @return bool
 */
function timesheets_can_final_approve_attendance($staff_id = '')
{
	$staff_id = ($staff_id === '') ? (int) get_staff_user_id() : (int) $staff_id;
	if ($staff_id <= 0) {
		return false;
	}

	if (function_exists('is_admin') && is_admin($staff_id)) {
		return true;
	}

	$role = get_staff_role_slug($staff_id);

	return in_array($role, ['hr', 'super hr', 'admin', 'super admin'], true);
}

/**
 * Who may approve / reject attendance regularization (additional timesheets) as final decision.
 * Alias of timesheets_can_final_approve_attendance().
 *
 * @param int|string $staff_id
 * @return bool
 */
function timesheets_can_approve_attendance($staff_id = '')
{
	return timesheets_can_final_approve_attendance($staff_id);
}

/**
 * True when viewer is the applicant's HOD / team manager (not HR final approver).
 *
 * @param int $creator_staff_id regularization applicant
 * @param int|string $viewer_id
 * @return bool
 */
function timesheets_can_hod_review_regularisation($creator_staff_id, $viewer_id = '')
{
	$creator_staff_id = (int) $creator_staff_id;
	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;
	if ($creator_staff_id <= 0 || $viewer_id <= 0 || $creator_staff_id === $viewer_id) {
		return false;
	}

	// Final approvers use Approve / Reject, not Forward.
	if (timesheets_can_final_approve_attendance($viewer_id)) {
		return false;
	}

	$CI = &get_instance();
	$row = $CI->db->select('team_manage')
		->from(db_prefix() . 'staff')
		->where('staffid', $creator_staff_id)
		->get()
		->row();
	if ($row && (int) ($row->team_manage ?? 0) === $viewer_id) {
		return true;
	}

	// Manager role with this employee in their team list.
	if (function_exists('timesheets_can_view_staff') && timesheets_can_view_staff($creator_staff_id, $viewer_id)) {
		if (function_exists('timesheets_hr_can_view_all_staff') && timesheets_hr_can_view_all_staff($viewer_id)) {
			return false;
		}
		return true;
	}

	return false;
}

/**
 * Staff who may approve / reject leave applications.
 * Only: Sarabjeet Singh, Admin, Super Admin, HR / Super HR (not team managers).
 *
 * @return int[]
 */
function timesheets_leave_approval_staff_ids()
{
	static $ids = null;
	if ($ids !== null) {
		return $ids;
	}

	$CI = &get_instance();
	$named = [178]; // Sarabjeet Singh
	$rows = $CI->db->select('staffid')
		->from(db_prefix() . 'staff')
		->where('active', 1)
		->group_start()
			->where('admin', 1)
			->or_where_in('role', [22, 24, 28, 30]) // Admin, HR, Super Admin, Super HR
			->or_where_in('staffid', $named)
		->group_end()
		->get()
		->result_array();

	$ids = array_values(array_unique(array_map('intval', array_column($rows, 'staffid'))));
	sort($ids);

	return $ids;
}

/**
 * Super Admin leave approvers for HR / Accounts applicants (Harpreet + Super Admin role).
 *
 * @return int[]
 */
function timesheets_super_admin_leave_approver_ids()
{
	static $ids = null;
	if ($ids !== null) {
		return $ids;
	}

	$CI = &get_instance();
	$named = [1]; // Harpreet Singh
	$rows = $CI->db->select('staffid')
		->from(db_prefix() . 'staff')
		->where('active', 1)
		->group_start()
			->where_in('staffid', $named)
			->or_where('role', 28) // Super Admin
		->group_end()
		->get()
		->result_array();

	$ids = array_values(array_unique(array_map('intval', array_column($rows, 'staffid'))));
	sort($ids);
	if (empty($ids)) {
		$ids = [1];
	}

	return $ids;
}

/**
 * HR / Accounts (Finance) staff leave must be approved only by Super Admin (Harpreet).
 *
 * @param int|string $staff_id applicant
 * @return bool
 */
function timesheets_staff_is_hr_or_accounts($staff_id)
{
	$staff_id = (int) $staff_id;
	if ($staff_id <= 0) {
		return false;
	}

	if (!function_exists('get_department_by_staffid')) {
		$CI = &get_instance();
		$CI->load->helper('custom');
	}

	$departments = get_department_by_staffid($staff_id);
	if (!is_array($departments) || empty($departments)) {
		return false;
	}

	foreach ($departments as $dept) {
		$name = strtolower(trim((string) ($dept['name'] ?? '')));
		if ($name === '') {
			continue;
		}
		// HR, HR Ops, HR Recruitment, Finance, Accounts
		if (
			preg_match('/\bhr\b/', $name)
			|| strpos($name, 'human resource') !== false
			|| strpos($name, 'account') !== false
			|| strpos($name, 'finance') !== false
		) {
			return true;
		}
	}

	return false;
}

/**
 * Approver staff IDs for a specific leave applicant.
 *
 * @param int|string $applicant_staff_id
 * @return int[]
 */
function timesheets_leave_approver_ids_for_applicant($applicant_staff_id)
{
	$applicant_staff_id = (int) $applicant_staff_id;
	if ($applicant_staff_id > 0 && timesheets_staff_is_hr_or_accounts($applicant_staff_id)) {
		return timesheets_super_admin_leave_approver_ids();
	}

	return timesheets_leave_approval_staff_ids();
}

/**
 * Who may approve / reject leave applications.
 * For HR / Accounts applicants: Super Admin (Harpreet) only.
 *
 * @param int|string $staff_id approver
 * @param int|string $applicant_staff_id optional leave applicant
 * @return bool
 */
function timesheets_can_approve_leave($staff_id = '', $applicant_staff_id = '')
{
	$staff_id = ($staff_id === '') ? (int) get_staff_user_id() : (int) $staff_id;
	if ($staff_id <= 0) {
		return false;
	}

	$applicant_staff_id = (int) $applicant_staff_id;
	if ($applicant_staff_id > 0 && timesheets_staff_is_hr_or_accounts($applicant_staff_id)) {
		return in_array($staff_id, timesheets_super_admin_leave_approver_ids(), true)
			|| get_staff_role_slug($staff_id) === 'super admin';
	}

	if (in_array($staff_id, timesheets_leave_approval_staff_ids(), true)) {
		return true;
	}

	$role = get_staff_role_slug($staff_id);

	return in_array($role, ['admin', 'hr', 'super hr', 'super admin'], true)
		|| is_admin($staff_id);
}

/**
 * Final leave approval — Super HR / HR / Admin / Super Admin (not HOD/managers).
 *
 * @param int|string $staff_id
 * @param int|string $applicant_staff_id
 * @return bool
 */
function timesheets_can_final_approve_leave($staff_id = '', $applicant_staff_id = '')
{
	return timesheets_can_approve_leave($staff_id, $applicant_staff_id);
}

/**
 * HOD / Manager may forward leave to Super HR (or reject), but cannot final-approve.
 *
 * @param int $applicant_staff_id
 * @param int|string $viewer_id
 * @return bool
 */
function timesheets_can_hod_forward_leave($applicant_staff_id, $viewer_id = '')
{
	$applicant_staff_id = (int) $applicant_staff_id;
	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;
	if ($applicant_staff_id <= 0 || $viewer_id <= 0 || $applicant_staff_id === $viewer_id) {
		return false;
	}

	// Final approvers use Approve, not Forward.
	if (timesheets_can_final_approve_leave($viewer_id, $applicant_staff_id)) {
		return false;
	}

	$CI = &get_instance();
	$row = $CI->db->select('team_manage')
		->from(db_prefix() . 'staff')
		->where('staffid', $applicant_staff_id)
		->get()
		->row();
	if ($row && (int) ($row->team_manage ?? 0) === $viewer_id) {
		return true;
	}

	if (function_exists('timesheets_can_view_staff') && timesheets_can_view_staff($applicant_staff_id, $viewer_id)) {
		if (function_exists('timesheets_hr_can_view_all_staff') && timesheets_hr_can_view_all_staff($viewer_id)) {
			return false;
		}
		return true;
	}

	return false;
}

/**
 * Active Super HR staff IDs (for forward notifications).
 *
 * @return int[]
 */
function timesheets_super_hr_staff_ids()
{
	static $ids = null;
	if ($ids !== null) {
		return $ids;
	}

	$CI = &get_instance();
	$rows = $CI->db->select('staffid')
		->from(db_prefix() . 'staff')
		->where('active', 1)
		->where('role', 30) // Super HR
		->get()
		->result_array();

	$ids = array_values(array_unique(array_map('intval', array_column($rows, 'staffid'))));
	if (empty($ids)) {
		// Fallback: any staff with Super HR slug
		$all = $CI->db->select('staffid, role')->from(db_prefix() . 'staff')->where('active', 1)->get()->result_array();
		foreach ($all as $s) {
			if (get_staff_role_slug((int) $s['staffid']) === 'super hr') {
				$ids[] = (int) $s['staffid'];
			}
		}
		$ids = array_values(array_unique($ids));
	}

	// No Super HR assigned yet — notify leave final approvers (Sarabjeet / HR / Admin).
	if (empty($ids) && function_exists('timesheets_leave_approval_staff_ids')) {
		$ids = timesheets_leave_approval_staff_ids();
	}

	return $ids;
}

/**
 * Staff picker on leave/attendance pages (HR = all, manager = team only).
 */
function timesheets_user_can_pick_staff()
{
	$uid = (int) get_staff_user_id();

	return timesheets_hr_can_view_all_staff($uid) || timesheets_is_team_manager($uid);
}

/**
 * Target work span for a full day before grace (minutes). Policy: 9 hours.
 *
 * @return int
 */
function timesheets_present_target_minutes()
{
	return 540; // 9h
}

/**
 * Grace minutes subtracted from present target (late tolerance on span rule).
 *
 * @return int
 */
function timesheets_present_grace_minutes()
{
	return 5;
}

/**
 * Minimum first-IN→last-OUT span (minutes) for a full present day (P).
 * Present when span >= target − grace (9h − 5m = 8h 55m).
 *
 * @return int
 */
function timesheets_required_span_minutes()
{
	return timesheets_present_target_minutes() - timesheets_present_grace_minutes();
}

/**
 * Minimum span (minutes) for a half-day (HD).
 *
 * @return int
 */
function timesheets_half_day_min_span_minutes()
{
	return 300; // 5h
}

/**
 * Minimum actual work hours for a half-day (HD).
 *
 * @return float
 */
function timesheets_half_day_min_hours()
{
	return timesheets_half_day_min_span_minutes() / 60;
}

/**
 * Approved Work From Home on a calendar date (leave or timesheet WFH).
 */
function timesheets_is_staff_wfh_on_date($staff_id, $date)
{
	$staff_id = (int) $staff_id;
	$date = date('Y-m-d', strtotime((string) $date));
	if ($staff_id <= 0 || $date === '') {
		return false;
	}

	$CI = &get_instance();
	$row = $CI->db->query(
		'SELECT id FROM ' . db_prefix() . 'timesheets_requisition_leave
		 WHERE staff_id = ?
		   AND status = 1
		   AND type_of_leave IN ("work-from-home", "WFH", "wfh")
		   AND DATE(start_time) <= ?
		   AND DATE(end_time) >= ?
		 LIMIT 1',
		[$staff_id, $date, $date]
	)->row();
	if ($row) {
		return true;
	}

	$ts = $CI->db->query(
		'SELECT date_work FROM ' . db_prefix() . 'timesheets_timesheet
		 WHERE staff_id = ? AND date_work = ? AND UPPER(TRIM(type)) = "WFH"
		 LIMIT 1',
		[$staff_id, $date]
	)->row();

	return $ts ? true : false;
}

/**
 * Minimum displayed work hours when span qualifies as Present.
 *
 * @return float
 */
function timesheets_present_min_hours()
{
	return timesheets_required_span_minutes() / 60;
}

/**
 * Target span hours for regularization suggestions (first IN → last OUT).
 *
 * @return float
 */
function timesheets_required_work_hours()
{
	return timesheets_required_span_minutes() / 60;
}

/**
 * Classify attendance from first-IN→last-OUT span in whole minutes (floor).
 *
 * @param int|null $span_minutes
 * @param bool     $punch_missing
 * @return array{status:string,type:?string,issue:string,can_regularise:bool}
 */
function timesheets_classify_span_minutes($span_minutes, $punch_missing = false)
{
	if ($punch_missing) {
		return [
			'status' => 'punch_missing',
			'type' => null,
			'issue' => 'Punch in/out incomplete',
			'can_regularise' => true,
		];
	}

	$span_minutes = $span_minutes === null ? null : (int) $span_minutes;
	$present_min = timesheets_required_span_minutes();
	$half_min = timesheets_half_day_min_span_minutes();

	if ($span_minutes === null || $span_minutes <= 0) {
		return [
			'status' => 'absent',
			'type' => 'AB',
			'issue' => 'No attendance recorded',
			'can_regularise' => true,
		];
	}

	if ($span_minutes >= $present_min) {
		return [
			'status' => 'ok',
			'type' => 'P',
			'issue' => '',
			'can_regularise' => false,
		];
	}

	if ($span_minutes >= $half_min) {
		$target = timesheets_present_target_minutes();
		$grace = timesheets_present_grace_minutes();

		return [
			'status' => 'half_day',
			'type' => 'HD',
			'issue' => 'Half day (span ' . $span_minutes . ' min — present needs ' . $present_min . ' min = '
				. intdiv($target, 60) . 'h with ' . $grace . ' min grace)',
			'can_regularise' => true,
		];
	}

	return [
		'status' => 'absent',
		'type' => 'AB',
		'issue' => 'Less than ' . $half_min . ' min (' . $span_minutes . ' min) — counted as Absent',
		'can_regularise' => true,
	];
}

/**
 * Map actual working hours to attendance type code.
 *
 * @param float|string $hours
 * @return string P|HD|AB
 */
function timesheets_attendance_code_from_hours($hours)
{
	$hours = (float) $hours;
	if ($hours + 0.001 >= timesheets_present_min_hours()) {
		return 'P';
	}
	if ($hours + 0.001 >= timesheets_half_day_min_hours()) {
		return 'HD';
	}

	return 'AB';
}

/**
 * Leave days tied to an attendance day status (for balance control).
 * Absent = 1, Half day = 0.5, Present / full day = 0.
 *
 * @param string     $status
 * @param float|null $hours
 * @param string     $code
 * @return float
 */
function timesheets_leave_days_for_attendance_status($status = '', $hours = null, $code = '')
{
	$status = strtolower(trim((string) $status));
	$code = strtoupper(trim((string) $code));
	$hours = $hours === null || $hours === '' ? null : (float) $hours;

	if ($status === 'ok' || $status === 'regularised' || $status === 'pending' || $code === 'P') {
		return 0.0;
	}
	if ($status === 'half_day' || $code === 'HD' || $code === 'PHD' || $code === 'UHD') {
		return 0.5;
	}
	if ($status === 'absent' || $status === 'punch_missing' || $code === 'AB' || $code === 'A') {
		return 1.0;
	}

	if ($hours !== null) {
		$code_from_hours = timesheets_attendance_code_from_hours($hours);
		if ($code_from_hours === 'P') {
			return 0.0;
		}
		if ($code_from_hours === 'HD') {
			return 0.5;
		}
		return 1.0;
	}

	return 1.0;
}

/**
 * @deprecated Use timesheets_leave_days_for_attendance_status()
 */
function timesheets_leave_days_for_regularisation($status = '', $hours = null, $code = '')
{
	return timesheets_leave_days_for_attendance_status($status, $hours, $code);
}

/**
 * Format decimal work hours for display, including minutes (e.g. 0.5 → "0.5h (30m)", 8.25 → "8.25h (8h 15m)").
 *
 * @param float|string $hours
 * @return string
 */
function timesheets_format_work_hours($hours)
{
	$h = (float) $hours;
	if ($h <= 0) {
		return '0h';
	}

	$total_mins = (int) round($h * 60);
	$hh = intdiv($total_mins, 60);
	$mm = $total_mins % 60;
	$decimal = rtrim(rtrim(number_format($h, 2, '.', ''), '0'), '.');
	if ($decimal === '') {
		$decimal = '0';
	}

	if ($hh > 0 && $mm > 0) {
		return $decimal . 'h (' . $hh . 'h ' . $mm . 'm)';
	}
	if ($hh > 0 && $mm === 0) {
		return $decimal . 'h';
	}

	return $decimal . 'h (' . $mm . 'm)';
}

/**
 * Dropdown list for staff picker.
 *
 * @param int|string $viewer_id
 * @return array
 */
function timesheets_get_viewable_staff_list($viewer_id = '')
{
	$CI = &get_instance();
	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;

	if (timesheets_hr_can_view_all_staff($viewer_id)) {
		$staff = $CI->db->select('staffid, firstname, lastname')
			->where('active', 1)
			->order_by('firstname', 'ASC')
			->get(db_prefix() . 'staff')
			->result_array();

		return timesheets_sort_viewable_staff_self_first($staff, $viewer_id);
	}

	$ids = timesheets_get_team_staff_ids($viewer_id);
	if (!is_array($ids) || !$ids) {
		return [];
	}

	$staff = $CI->db->select('staffid, firstname, lastname')
		->where_in('staffid', $ids)
		->where('active', 1)
		->order_by('firstname', 'ASC')
		->get(db_prefix() . 'staff')
		->result_array();

	return timesheets_sort_viewable_staff_self_first($staff, $viewer_id);
}

/**
 * Put the logged-in viewer first in staff pickers (Me).
 */
function timesheets_sort_viewable_staff_self_first(array $staff, $viewer_id = '')
{
	$viewer_id = ($viewer_id === '') ? (int) get_staff_user_id() : (int) $viewer_id;
	usort($staff, function ($a, $b) use ($viewer_id) {
		$aid = (int) ($a['staffid'] ?? 0);
		$bid = (int) ($b['staffid'] ?? 0);
		if ($aid === $viewer_id && $bid !== $viewer_id) {
			return -1;
		}
		if ($bid === $viewer_id && $aid !== $viewer_id) {
			return 1;
		}

		return strcasecmp(
			trim(($a['firstname'] ?? '') . ' ' . ($a['lastname'] ?? '')),
			trim(($b['firstname'] ?? '') . ' ' . ($b['lastname'] ?? ''))
		);
	});

	return $staff;
}

/**
 * Leave form "CC to" options for the applying staff:
 * - all active staff in the same department(s) as that user
 * - plus active HR / Super HR (always available to CC)
 * Excludes the applying staff themselves.
 *
 * @param int|string $staff_id
 * @return array
 */
function timesheets_get_leave_cc_staff_list($staff_id = '')
{
	$CI = &get_instance();
	$staff_id = ($staff_id === '') ? (int) get_staff_user_id() : (int) $staff_id;
	if ($staff_id <= 0) {
		return [];
	}

	$ids = [];

	// Same department(s) as the applying user.
	$dept_rows = $CI->db->select('departmentid')
		->where('staffid', $staff_id)
		->get(db_prefix() . 'staff_departments')
		->result_array();
	$dept_ids = array_values(array_unique(array_filter(array_map('intval', array_column($dept_rows, 'departmentid')))));
	if ($dept_ids) {
		$dept_staff = $CI->db->select('staffid')
			->from(db_prefix() . 'staff_departments')
			->where_in('departmentid', $dept_ids)
			->get()
			->result_array();
		foreach ($dept_staff as $row) {
			$ids[] = (int) $row['staffid'];
		}
	}

	// Always include HR / Super HR.
	$hr_q = $CI->db->select(db_prefix() . 'staff.staffid')
		->from(db_prefix() . 'staff')
		->join(db_prefix() . 'roles', db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role', 'left')
		->where(db_prefix() . 'staff.active', 1)
		->group_start()
			->where_in(db_prefix() . 'staff.role', [24, 30])
			->or_where_in('LOWER(' . db_prefix() . 'roles.name)', ['hr', 'super hr'])
		->group_end()
		->get()
		->result_array();
	foreach ($hr_q as $row) {
		$ids[] = (int) $row['staffid'];
	}

	// Also include staff assigned to any department whose name contains "HR".
	$hr_dept_staff = $CI->db->select(db_prefix() . 'staff_departments.staffid')
		->from(db_prefix() . 'staff_departments')
		->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'staff_departments.departmentid', 'inner')
		->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'staff_departments.staffid', 'inner')
		->where(db_prefix() . 'staff.active', 1)
		->like(db_prefix() . 'departments.name', 'HR')
		->get()
		->result_array();
	foreach ($hr_dept_staff as $row) {
		$ids[] = (int) $row['staffid'];
	}

	$ids = array_values(array_unique(array_filter($ids, static function ($id) use ($staff_id) {
		return (int) $id > 0 && (int) $id !== (int) $staff_id;
	})));

	if (!$ids) {
		return [];
	}

	return $CI->db->select('staffid, firstname, lastname')
		->where_in('staffid', $ids)
		->where('active', 1)
		->order_by('firstname', 'ASC')
		->get(db_prefix() . 'staff')
		->result_array();
}

/**
 * Build navbar punch context (Workroom + Biometric). Cached per staff ~90s to cut DB load on every admin page.
 *
 * @return array<string, mixed>
 */
function timesheets_admin_navbar_punch_context($cooldown_hours = 13)
{
	$CI = &get_instance();
	$staff_id = (int) get_staff_user_id();
	$cache_key = 'ts_navbar_punch_' . $staff_id;
	$cached = $CI->session->userdata($cache_key);
	if (is_array($cached) && !empty($cached['expires']) && (int) $cached['expires'] > time() && !empty($cached['data'])) {
		return $cached['data'];
	}

	// Auto-checkout stale open check-ins at most once every 5 minutes per user (cron also handles this).
	$throttle_key = 'ts_process_checkin_' . $staff_id;
	$last_checkin_run = (int) $CI->session->userdata($throttle_key);
	if ($last_checkin_run < time() - 300) {
		try {
			$CI->load->model('cron_model');
			if (method_exists($CI->cron_model, 'processCheckinForStaff')) {
				$CI->cron_model->processCheckinForStaff($staff_id);
			}
		} catch (Throwable $e) {
		}
		$CI->session->set_userdata($throttle_key, time());
	}

	$CI->load->model('timesheets/timesheets_model');
	$data_check_in_out = $CI->timesheets_model->get_latest_check_in_out();

	$html_list = '';
	$time_from_checkin = 999;
	$type_check_in_out = '';

	if (!empty($data_check_in_out[0]['date'])) {
		$last_type = (int) ($data_check_in_out[0]['type_check'] ?? 0);
		$last_date = $data_check_in_out[0]['date'];
		$last_ts = strtotime($last_date);

		if ($last_type === 1) {
			$hours = (time() - $last_ts) / 3600;
			if ($hours >= 0 && $hours < $cooldown_hours) {
				$type_check_in_out = 1;
				$time_from_checkin = $hours;
				$html_list = '<span class="header-workroom-pill header-source-pill" title="Workroom web check-in"><span class="header-source-tag">Workroom</span> Check in : ' . date('H:i:s', $last_ts) . '</span>';
			} else {
				$type_check_in_out = 2;
				$time_from_checkin = 999;
				$html_list = '';
			}
		} elseif ($last_type === 2) {
			$in_date = !empty($data_check_in_out[1]['date']) ? $data_check_in_out[1]['date'] : $last_date;
			$in_ts = strtotime($in_date);
			$hours = (time() - $in_ts) / 3600;

			if ($hours >= 0 && $hours < $cooldown_hours) {
				$type_check_in_out = 2;
				$time_from_checkin = $hours;
				$html_list = '';
				if (!empty($data_check_in_out[1]['date'])) {
					$html_list .= '<span class="header-workroom-pill header-source-pill" title="Workroom web check-in"><span class="header-source-tag">Workroom</span> Check in : ' . date('H:i:s', $in_ts) . '</span> ';
				}
				$html_list .= '<span class="header-workroom-pill header-workroom-pill-out header-source-pill" title="Workroom web check-out"><span class="header-source-tag">Workroom</span> Check out : ' . date('H:i:s', $last_ts) . '</span>';
			} else {
				$type_check_in_out = 2;
				$time_from_checkin = 999;
				$html_list = '';
			}
		}
	}

	$biometric_navbar_active = false;
	$biometric_checkin_label = '';
	$biometric_is_checked_out = false;
	$biometric_last_out_ts = 0;
	$biometric_break_summary = null;

	try {
		$on_wfh_today = function_exists('timesheets_is_staff_wfh_on_date')
			&& timesheets_is_staff_wfh_on_date($staff_id, date('Y-m-d'));

		$CI->load->model('biometric_model');
		$biometric_row = null;
		if (!$on_wfh_today && method_exists($CI->biometric_model, 'get_staff_active_punch')) {
			$biometric_row = $CI->biometric_model->get_staff_active_punch($staff_id, $cooldown_hours);
		} elseif (!$on_wfh_today) {
			$biometric_row = $CI->biometric_model->get_staff_today_punch($staff_id);
		}
		if (is_array($biometric_row)) {
			$bio_summary = $CI->biometric_model->get_biometric_punch_summary($biometric_row);
			if ($bio_summary) {
				$biometric_navbar_active = true;
				if (method_exists($CI->biometric_model, 'get_navbar_first_checkin_display')) {
					$biometric_checkin_label = (string) $CI->biometric_model->get_navbar_first_checkin_display($biometric_row);
				} else {
					$biometric_checkin_label = (string) ($bio_summary['first_time_formatted'] ?? '');
				}
				if ($biometric_checkin_label === '' && !empty($bio_summary['first_ts'])) {
					$biometric_checkin_label = date('H:i:s', (int) $bio_summary['first_ts']);
				}
				$biometric_is_checked_out = $bio_summary['is_checked_out'];
				$biometric_last_out_ts = $bio_summary['last_ts'];
				if (method_exists($CI->biometric_model, 'get_biometric_break_summary')) {
					$biometric_break_summary = $CI->biometric_model->get_biometric_break_summary($biometric_row);
				}
			}
		}
	} catch (Throwable $e) {
		$biometric_navbar_active = false;
		$biometric_checkin_label = '';
		$biometric_break_summary = null;
	}

	$data = [
		'allows_updating_check_in_time' => 0,
		'html_list' => $html_list,
		'time_from_checkin' => $time_from_checkin,
		'type_check_in_out' => $type_check_in_out,
		'biometric_navbar_active' => $biometric_navbar_active,
		'biometric_checkin_label' => $biometric_checkin_label,
		'biometric_is_checked_out' => $biometric_is_checked_out,
		'biometric_last_out_ts' => $biometric_last_out_ts,
		'biometric_break_summary' => $biometric_break_summary,
	];

	if (function_exists('get_timesheets_option')) {
		$data_allows = get_timesheets_option('allows_updating_check_in_time');
		if ($data_allows !== null && $data_allows !== '') {
			$data['allows_updating_check_in_time'] = $data_allows;
		}
	}

	$CI->session->set_userdata($cache_key, [
		'expires' => time() + 90,
		'data'    => $data,
	]);

	return $data;
}


