<?php



function generateSummary($employeeData)
{



    // Initialize an empty summary array

    $summary = array();



    // Process the employee data to calculate break and total hours

    foreach ($employeeData as $data) {

        $employeeName = strip_tags($data[0]);

        $taskName = strip_tags($data[1]);

        $time = (float) strip_tags($data[8]);



        // Calculate the break and total hours based on task name

        if (!isset($summary[$employeeName])) {

            $summary[$employeeName] = array(

                'break_hours' => 0,

                'total_hours' => 0,

            );
        }



        if (contains('break', $taskName)) {

            $summary[$employeeName]['break_hours'] += $time;
        }



        $summary[$employeeName]['total_hours'] += $time;
    }



    // Display the summary

    return $summary;
}



function contains($needle, $haystack)
{

    return stripos($haystack, $needle) !== false;
}





function decimalToTime($decimalHours)
{

    $hours = floor($decimalHours);

    $minutes = ($decimalHours - $hours) * 60;

    $formattedTime = sprintf("%02d:%02d", $hours, $minutes);

    return $formattedTime;
}



function changeSubjectAccordingtoPeriod($periodText, $periodto, $periodfrom)
{

    if ($periodText == 'Today') {

        $period = date("m/d/Y");
    } else if ($periodText == 'This Month Logged Time') {

        $period = date("F") . ', ' . date("Y");
    } else if ($periodText == 'Last Month Logged Time') {

        $period = date("F", strtotime('last month')) . ', ' . date("Y", strtotime('last month'));
    } else if ($periodText == 'Last Week Logged Time') {

        $last_week_start = date("m/d/Y", strtotime("this week"));

        $last_week_end = date("m/d/Y", strtotime("this week +6 days"));

        $period = 'From ' . $last_week_start . ' To ' . $last_week_end;
    } else if ($periodText == 'This Week Logged Time') {

        $this_week_start = date("m/d/Y", strtotime("this week"));

        $this_week_end = date("m/d/Y", strtotime("this week +6 days"));

        $period = 'From ' . $this_week_start . ' To ' . $this_week_end;
    } else {

        $periodfrom = date("m/d/Y", strtotime($periodfrom));

        $periodto = date("m/d/Y", strtotime($periodto));

        $period = ($periodfrom == $periodto) ? $periodfrom : 'From ' . $periodfrom . ' To ' . $periodto;
    }

    return $period;
}

function attendance_permission($id = '')
{

    $CI = &get_instance();

    if ($id == '') {
        $id = get_staff_user_id();
    }

    // Super HR / HR role: full leave/attendance manage rights in HRMS
    if (is_super_hr($id) || is_HR($id)) {
        return true;
    }

    if ((int) $id === 178 || (int) $id === 202) {
        return true;
    }

    $leaveArr = $CI->db->query("SELECT manageleave FROM tblstaff_info WHERE staffid = " . (int) $id)->result_array();

    if ($leaveArr) {
        return $leaveArr[0]['manageleave'];
    } else {
        return false;
    }
}

function view_only_permission($id = '')
{
    $CI = &get_instance();

    if ($id == '') {
        $id = get_staff_user_id();
    }
    $leaveArr = $CI->db->query("SELECT attendance_view_edit FROM tblstaff_info WHERE staffid = $id")->result_array();
    return $leaveArr[0]['attendance_view_edit'];
}



// this function check if staff is present in customer admin database
// function is_manager()
// {
//     $CI = &get_instance();
//     $check_customer_query = $CI->db->query("SELECT staff_id FROM tblcustomer_admins WHERE staff_id = " . get_staff_user_id())->result_array();
//     // print_r($check_customer_query); die;
//     return ($check_customer_query == true);
// }

function exceed_time_report()
{
    $CI = &get_instance();

    $CI->load->model('projects_model');
    $projects = $CI->projects_model->get();

    $projectTmp = '';
    foreach ($projects as $project) {
        $estimated_time_in_seconds = 3600 * (float) $project['estimated_hours'];

        $project_total_logged_time = $CI->projects_model->total_logged_time($project['id']);

        if (!$estimated_time_in_seconds || $project_total_logged_time - $estimated_time_in_seconds < 0) {
            $exc_time = '00:00';
        } else {
            $exc_time = seconds_to_time_format($project_total_logged_time - $estimated_time_in_seconds);

            $projectTmp .= '<tr>
            <td>' . $project['id'] . '</td>
            <td>' . $project['name'] . '</td>
            <td>' . $exc_time . '</td>
            <td>' . $exc_time . '</td>
            </tr>';
        }
    }
    // print_r($projectTmp);die;
    return $projectTmp;
}

function send_exceeding_email($projectTmp)
{

    // print_r($projectTmp); die;
    $CI = &get_instance();

    $CI->load->library('email');
    $CI->email->set_mailtype("html");
    $CI->email->from('noreply@tech2globe.com', 'Tech2globe');
    $CI->email->to('bhavya.khanna@tech2globe.in');
    $subject = 'The Following Staff is exceeding Task limit';

    $message = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project Status Update</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            h2 {
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Project Status Update</h2>
            <p>Dear Team,</p>
            
            <p>We would like to inform you about the status of the following project:</p>
            
            <table>
                <tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>Exceeding Time</th>
                    <th>Staff</th>
                </tr>
                ' . $projectTmp . '
            </table>
    
            <p>Please review the details and take necessary actions accordingly. If you have any questions or need further clarification, feel free to reach out.</p>
    
            <p>Thank you.</p>
        </div>
    </body>
    </html>
    ';

    // print_r($message);die;
    $CI->email->subject($subject);
    $CI->email->message($message);

    if ($CI->email->send()) {
        echo 'mail sent';
    } else {
        echo $CI->email->print_debugger();
    }
}

//function to get staff email id based on staffid
function get_staff_email_id($staffid)
{
    $CI = &get_instance();
    $staff_data = $CI->db->query('select email from tblstaff where staffid =' . $staffid)->result_array();
    if (!empty($staff_data)) {
        return $staff_data[0]['email'];
    } else {
        return '';
    }
}

/**
 * get department by staffid
 * @param  integer $id_staff 
 * @return array           
 */
function get_department_by_staffid($id_staff)
{
    $CI = &get_instance();
    $CI->db->where('staffid', $id_staff);
    $departments = $CI->db->get(db_prefix() . 'staff_departments')->result_array();

    // print_r($departments); die;
    $department_ids = array_column($departments, 'departmentid');
    $department_ids_str = implode(',', $department_ids);

    if ($department_ids_str) {
        $sql = "SELECT * FROM " . db_prefix() . "departments WHERE departmentid IN ($department_ids_str)";
        return $CI->db->query($sql)->result_array();
    }

    return array();
}

function get_department_name_by_departmentid($id)
{
    $CI = &get_instance();
   
    // Use query binding to prevent SQL injection
    $sql = "SELECT name FROM tbldepartments WHERE departmentid = ?";
    $dep_name_obj = $CI->db->query($sql, [$id])->row();

    // Check if a result exists
    return $dep_name_obj ? $dep_name_obj->name : null;
}

function get_doccategory_name_by_categoryid($id)
{
    $CI = &get_instance();
   
    // Use query binding to prevent SQL injection
    $sql = "SELECT name FROM tbldocument_upload_category WHERE id = ?";
    $cat_name_obj = $CI->db->query($sql, [$id])->row();

    // Check if a result exists
    return $cat_name_obj ? $cat_name_obj->name : null;
}

function get_staff_emp_id($staffid = '')
{

    if ($staffid == '') {
        $staffid = get_staff_user_id();
    }

    $CI = &get_instance();

    $query = "SELECT COALESCE(staff_identifi,empid) as employee_id , tblstaff.staffid FROM tblstaff  left JOIN tblstaff_info on tblstaff.staffid = tblstaff_info.staffid where tblstaff.staffid = $staffid";
    // $query = "SELECT staff_identifi from tblstaff WHERE staffid = $staffid";

    $empid_obj = $CI->db->query($query)->row();

    if ($empid_obj) {

        return $empid_obj->employee_id;
    }
}


/**
 * get role by role id
 * @param  integer $id_staff 
 * @return string           
 */

function get_role_name($id)
{
    $roles_names = '';
    $CI = &get_instance();


    $CI->db->where('roleid', $id);
    $CI->db->select('name');
    $roles = $CI->db->get(db_prefix() . 'roles')->row();

    if ($roles) {
        $roles_names .= $roles->name;
    }
    return $roles_names;
}

function is_in_managers_list()
{
    $CI = &get_instance();

    $query = "SELECT team_manage from tblstaff";
    $data = $CI->db->query($query)->result_array();
    $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

    $managers = [];

    foreach ($manager_ids as $manager_id) {

        $managers[$manager_id] = get_staff_full_name($manager_id);
    }

    $staff_id = get_staff_user_id();


    if (array_key_exists($staff_id, $managers)) {
        return true;
    } else {
        return false;
    }
}


function in_the_department($departmentName)
{
    $departments = get_department_by_staffid(get_staff_user_id());

    // print_r($departments);
    $staff_all_departments = array_column($departments, 'name');


    foreach ($staff_all_departments as $department) {
        // echo $department . "\n";
        if (strpos($department, $departmentName) !== false) {
            return true;
        }
    }
    return false;
}
function is_in_managers_name()
{
    $CI = &get_instance();
    $staff_id = get_staff_user_id();
    $query = "SELECT email,team_manage from tblstaff where staffid=" . $staff_id;
    $data = $CI->db->query($query)->result_array();
    $team_manage = $data[0]['team_manage'];
    if ($team_manage != 0) {
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $id = $manager_ids[0];
        $query_manager = "SELECT email,team_manage from tblstaff where staffid=" . $id;
        $datas = $CI->db->query($query_manager)->result_array();
        $email = $datas[0]['email'];

        return $email;
    } else {
        echo '';
    }
}

function is_in_managers_name_fname_lname()
{
    $CI = &get_instance();
    $staff_id = get_staff_user_id();
    $query = "SELECT email,team_manage from tblstaff where staffid=" . $staff_id;
    $data = $CI->db->query($query)->result_array();
    $team_manage = $data[0]['team_manage'];
    if ($team_manage != 0) {
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $id = $manager_ids[0];
        $query_manager = "SELECT email,firstname,lastname,team_manage from tblstaff where staffid=" . $id;
        $datas = $CI->db->query($query_manager)->result_array();
        $fname_lname = $datas[0]['firstname'] . ' ' . $datas[0]['lastname'];
        return $fname_lname;
    } else {
        echo '';
    }
}

function managers_id()
{
    $CI = &get_instance();
    $staff_id = get_staff_user_id();
    $query = "SELECT staffid,team_manage from tblstaff where staffid=" . $staff_id;
    $data = $CI->db->query($query)->result_array();
    $team_manage = $data[0]['team_manage'];
    if ($team_manage != 0) {
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $id = $manager_ids[0];
        $query_manager = "SELECT staffid,team_manage from tblstaff where staffid=" . $id;
        $datas = $CI->db->query($query_manager)->result_array();
        $staffid = $datas[0]['staffid'];
        return $staffid;
    } else {
        echo '';
    }
}

/**
 * Single lookup for reporting manager id/email/name (avoids 3× staff queries on leave page).
 */
function timesheets_get_reporting_manager_info($staff_id = null)
{
    $CI = &get_instance();
    $staff_id = (int) ($staff_id ?: get_staff_user_id());
    $info = ['id' => '', 'email' => '', 'name' => ''];

    $row = $CI->db->select('team_manage')
        ->where('staffid', $staff_id)
        ->get(db_prefix() . 'staff')
        ->row();

    if (!$row || empty($row->team_manage)) {
        return $info;
    }

    $manager = $CI->db->select('staffid, email, firstname, lastname')
        ->where('staffid', (int) $row->team_manage)
        ->get(db_prefix() . 'staff')
        ->row();

    if ($manager) {
        $info['id'] = $manager->staffid;
        $info['email'] = $manager->email;
        $info['name'] = trim($manager->firstname . ' ' . $manager->lastname);
    }

    return $info;
}

function check_staff_is_interviewer($staffid = '')
{
    if ($staffid == '') {
        $staffid = get_staff_user_id();
    }

    $check_staff_sql = "
    SELECT
        COUNT(*) AS count
    FROM
        tblrec_interview
    WHERE
        FIND_IN_SET($staffid, interviewer) > 0";

    $CI = &get_instance();

    $result = $CI->db->query($check_staff_sql)->row()->count;


    return $result;
}

function get_earned_leaves($staffid)
{
    $CI = &get_instance();
    $staffid = (int) $staffid;
    if ($staffid <= 0) {
        return 0;
    }

    if (!isset($CI->staff_model)) {
        $CI->load->model('staff_model');
    }

    $info = $CI->db->select('doj')->where('staffid', $staffid)->get(db_prefix() . 'staff_info')->row();
    $doj = $info->doj ?? null;

    return (float) $CI->staff_model->calculateEarnedLeaves(
        $doj,
        $staffid,
        (int) date('n'),
        (int) date('Y')
    );
}


function get_all_managers()
{
    $CI = &get_instance();

    $query = "SELECT team_manage from tblstaff where active = 1";
    $data = $CI->db->query($query)->result_array();
    $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));
    if (empty($manager_ids)) {
        return [];
    }

    $managers_id_str = implode(',', array_map('intval', $manager_ids));

    $managers = $CI->db->query("select *,CONCAT(firstname,' ',lastname) as full_name from tblstaff where staffid in ($managers_id_str) and active = 1")->result_array();

    return $managers;
}

function check_staff_active($staffid)
{

    $CI = &get_instance();

    $CI->db->where("staffid", $staffid);
    $query = $CI->db->get('tblstaff');

    $data = $query->row_array();

    if ($data['active'] == '1') {
        return true;
    }

    return false;
}

function get_all_department_names($staffid = '')
{
    if ($staffid == '') {
        $staffid = get_staff_user_id();
    }

    $CI = &get_instance();

    $departments_sql =  "SELECT
                            GROUP_CONCAT(
                                tblstaff_departments.departmentid
                            ) as departmentids
                        FROM
                            tblstaff_departments
                        WHERE
                            staffid = $staffid
                        ";

    $department_ids = $CI->db->query($departments_sql)->row();

    $ids = $department_ids->departmentids;

    $department_names_sql = "select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $ids . ")";

    $department_names = $CI->db->query($department_names_sql)->row()->department_names;

    return $department_names;
}


function get_job_position_by_staffid($id)
{
    $CI = &get_instance();

    if (is_numeric($id)) {
        $CI->db->select('jp.position_name as position');
        $CI->db->from('tblstaff ts');
        $CI->db->join('tblhr_job_position jp', 'ts.job_position = jp.position_id    ', 'left');

        $CI->db->where('ts.staffid', $id);
        return $CI->db->get()->row()->position;
    }
}

function get_holiday_dates()
{
    $CI = &get_instance();
    $holidays = $CI->db->get(" tblday_off ")->result_array();

    $holiday_dates = array_column($holidays, 'break_date');
    return $holiday_dates;
}

function get_satuday_off_dates($staffid = '')
{

    if($staffid==''){
        $staffid = get_staff_user_id();
    }
    $CI = &get_instance();
    $CI->db->where('staffid = ' , $staffid);
    $saturdays = $CI->db->get("tblholiday ")->result_array();

    $sat_dates = array_column($saturdays, 'saturday_date');
    return $sat_dates;
}

function is_night_shift($staff_id)
{
    $department = get_department_by_staffid($staff_id);


    if(!empty($department) && $department){

        $department_name = $department[0]['name'] ?? '';
        if(strpos($department_name,'Night')){
            return true;
        }
    }
  

    return false;
 
}

function is_leader() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "leader";
}

function is_associate() {
    $CI = &get_instance();
    $CI->load->database();

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    if (!$role) {
        return false;
    }

    $role_name = strtolower(trim(get_role_name($role->role)));

    return $role_name === 'associate' || $role_name === 'asscociate';
}

function is_super_admin() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "super admin";
}

function is_admin2() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "admin";
}

function is_manager() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "manager";
}

/**
 * Create / send SOP — managers (incl. Manager-* roles) and system admins only.
 */
function can_create_sop($staff_id = '')
{
    if (is_admin($staff_id)) {
        return true;
    }

    $role = get_staff_role_slug($staff_id);
    if (in_array($role, ['super admin', 'admin'], true)) {
        return true;
    }

    return pedma_role_is_manager_family($role);
}

function can_manage_task_templates()
{
    return is_admin() || is_admin2() || is_super_admin();
}

/**
 * Table / CSV data export: Super Admin, Admin, Manager (+ Manager-* roles).
 */
function can_export_table_data($staff_id = '')
{
    if (is_admin($staff_id)) {
        return true;
    }

    $role = get_staff_role_slug($staff_id);

    if (in_array($role, ['super admin', 'admin'], true)) {
        return true;
    }

    return pedma_role_is_manager_family($role);
}

/**
 * PEDMA access matrix (latest company sheet)
 * - PEDMA / Old PEDMA: all roles except Sales
 * - Manage PEDMA Evaluation: Super Admin, Admin, Manager only
 * - Manage KRA: Super Admin, Admin, Manager only
 * - Manager-* roles (Manager - Data, Manager - HR, etc.) count as Manager
 */
function get_staff_role_slug($staff_id = '')
{
    if ($staff_id === '') {
        $staff_id = get_staff_user_id();
    }

    $CI = &get_instance();
    $CI->db->select('role')
        ->from('tblstaff')
        ->where('staffid', $staff_id);

    $row = $CI->db->get()->row();

    if (!$row) {
        return '';
    }

    return strtolower(trim(get_role_name($row->role)));
}

function pedma_role_is_manager_family($role)
{
    $role = strtolower(trim((string) $role));
    if ($role === '') {
        return false;
    }

    return $role === 'manager' || strpos($role, 'manager') === 0 || strpos($role, 'manager -') !== false;
}

function pedma_has_evaluation_role_access($staff_id = '')
{
    if (is_admin($staff_id)) {
        return true;
    }

    $role = get_staff_role_slug($staff_id);

    if (in_array($role, ['super admin', 'admin'], true)) {
        return true;
    }

    // Assigned manager role family only (not Leader/Associate/HR/IT)
    return pedma_role_is_manager_family($role);
}

function pedma_has_kra_role_access($staff_id = '')
{
    return can_create_pedma_kra($staff_id);
}

/**
 * Create / manage KRA definitions: system admin, Admin, Super Admin, and HR only (not managers).
 */
function can_create_pedma_kra($staff_id = '')
{
    if ($staff_id === '' || $staff_id === null) {
        $staff_id = get_staff_user_id();
    }

    if (is_admin($staff_id) || is_super_admin() || is_admin2()) {
        return true;
    }

    return is_HR($staff_id);
}

function can_view_own_pedma($staff_id = '')
{
    // Performance → PEDMA (own scores): all roles except Sales
    $role = get_staff_role_slug($staff_id);

    return $role !== '' && $role !== 'sales';
}

function can_manage_pedma($staff_id = '')
{
    // Manage PEDMA Dashboard: Super Admin / Admin / Manager / HR
    return pedma_has_evaluation_role_access($staff_id) || is_HR();
}

function can_evaluate_pedma($staff_id = '')
{
    // Evaluation: Super Admin / Admin / Manager only
    return pedma_has_evaluation_role_access($staff_id);
}

function pedma_is_hod_role($staff_id = '')
{
    $role = get_staff_role_slug($staff_id);

    return $role === 'leader' || $role === 'hod' || strpos($role, 'head of department') !== false;
}

/**
 * Which PEDMA comment columns the current profile may see and edit.
 * Admin and Super Admin see all three. Manager, HR, and HOD (Leader) see only their own.
 */
function pedma_comment_sections($staff_id = '')
{
    $role = get_staff_role_slug($staff_id);
    if (is_admin($staff_id) || in_array($role, ['super admin', 'admin'], true)) {
        return ['manager', 'hr', 'hod'];
    }

    $sections = [];
    if (pedma_role_is_manager_family($role)) {
        $sections[] = 'manager';
    }
    if (is_HR($staff_id)) {
        $sections[] = 'hr';
    }
    if (pedma_is_hod_role($staff_id)) {
        $sections[] = 'hod';
    }

    return $sections;
}

function can_open_pedma_evaluation($staff_id = '')
{
    return can_evaluate_pedma($staff_id) || is_HR($staff_id) || pedma_is_hod_role($staff_id);
}

/**
 * Overall PEDMA feedback is entered by managers only (not HR/HOD).
 */
function pedma_can_edit_overall_feedback($staff_id = '')
{
    if (pedma_can_edit_all_pedma_eval($staff_id)) {
        return true;
    }

    return pedma_role_is_manager_family(get_staff_role_slug($staff_id));
}

/**
 * Admin / Super Admin may edit any KRA rating or comment column.
 */
function pedma_can_edit_all_pedma_eval($staff_id = '')
{
    if ($staff_id === '' || $staff_id === null) {
        $staff_id = get_staff_user_id();
    }

    return is_admin($staff_id) || is_super_admin() || is_admin2();
}

/**
 * @param string $section manager|hod|hr
 */
function pedma_can_edit_overall_section($section, $staff_id = '')
{
    if (pedma_can_edit_all_pedma_eval($staff_id)) {
        return true;
    }

    $section = strtolower(trim((string) $section));
    if ($section === 'manager') {
        return pedma_role_is_manager_family(get_staff_role_slug($staff_id));
    }
    if ($section === 'hod') {
        return pedma_is_hod_role($staff_id);
    }
    if ($section === 'hr') {
        return is_HR($staff_id);
    }

    return false;
}

/**
 * Legacy overall_feedback plus split columns for employee display / emails.
 *
 * @param array<string, mixed>|object $row
 */
function pedma_format_overall_feedback_display($row)
{
    if (is_object($row)) {
        $row = (array) $row;
    }

    $manager = trim((string) ($row['overall_manager_feedback'] ?? ''));
    $hod = trim((string) ($row['overall_hod_feedback'] ?? ''));
    $hr = trim((string) ($row['overall_hr_feedback'] ?? ''));
    $legacy = trim(strip_tags((string) ($row['overall_feedback'] ?? '')));

    if ($manager === '' && $hod === '' && $hr === '' && $legacy !== '') {
        $manager = $legacy;
    }

    $parts = [];
    if ($manager !== '') {
        $parts[] = '<p><strong>Manager feedback</strong><br>' . nl2br(htmlspecialchars($manager, ENT_QUOTES, 'UTF-8')) . '</p>';
    }
    if ($hod !== '') {
        $parts[] = '<p><strong>HOD feedback</strong><br>' . nl2br(htmlspecialchars($hod, ENT_QUOTES, 'UTF-8')) . '</p>';
    }
    if ($hr !== '') {
        $parts[] = '<p><strong>HR feedback</strong><br>' . nl2br(htmlspecialchars($hr, ENT_QUOTES, 'UTF-8')) . '</p>';
    }

    if ($parts === []) {
        return $legacy !== '' ? $legacy : '—';
    }

    return implode('', $parts);
}

/**
 * @return string Combined plain text stored in overall_feedback for backward compatibility.
 */
function pedma_build_combined_overall_feedback($manager, $hod, $hr)
{
    $parts = [];
    $manager = trim((string) $manager);
    $hod = trim((string) $hod);
    $hr = trim((string) $hr);
    if ($manager !== '') {
        $parts[] = "Manager feedback:\n" . $manager;
    }
    if ($hod !== '') {
        $parts[] = "HOD feedback:\n" . $hod;
    }
    if ($hr !== '') {
        $parts[] = "HR feedback:\n" . $hr;
    }

    return implode("\n\n", $parts);
}

/**
 * Fixed rating scale for every PEDMA KRA (achieved score is always out of this value).
 */
function pedma_kra_rating_max()
{
    return 5;
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function pedma_normalize_kra_data_rows($rows)
{
    if (!is_array($rows)) {
        return [];
    }

    $max = pedma_kra_rating_max();
    foreach ($rows as &$row) {
        if (!is_array($row)) {
            continue;
        }
        $row['max_score'] = $max;
        if (!empty($row['kpiData']) && is_array($row['kpiData'])) {
            foreach ($row['kpiData'] as &$kpi) {
                if (is_array($kpi)) {
                    $kpi['max_score'] = $max;
                }
            }
            unset($kpi);
        }
    }
    unset($row);

    return $rows;
}

/**
 * @param object|null $kra
 * @return object|null
 */
function pedma_normalize_kra_record($kra)
{
    if ($kra === null || !is_object($kra)) {
        return $kra;
    }

    $max = pedma_kra_rating_max();
    $kra->max_score = $max;
    if (!empty($kra->kpi_data) && is_array($kra->kpi_data)) {
        foreach ($kra->kpi_data as $kpi) {
            if (is_object($kpi)) {
                $kpi->max_score = $max;
            }
        }
    }

    return $kra;
}

/**
 * @param array<int, object> $kraList
 * @return array<int, object>
 */
function pedma_normalize_kra_list($kraList)
{
    if (!is_array($kraList)) {
        return [];
    }

    foreach ($kraList as &$kra) {
        pedma_normalize_kra_record($kra);
    }
    unset($kra);

    return $kraList;
}

/**
 * @param array<string, mixed> $row tblstaff_performance row
 */
function pedma_normalize_performance_row_kra_data(array &$row)
{
    if (empty($row['kra_data'])) {
        return;
    }

    $decoded = json_decode($row['kra_data'], true);
    if (!is_array($decoded)) {
        return;
    }

    $row['kra_data'] = json_encode(pedma_normalize_kra_data_rows($decoded));
}

function can_manage_pedma_kra($staff_id = '')
{
    return can_create_pedma_kra($staff_id);
}

function can_access_manage_pedma_menu($staff_id = '')
{
    // Do NOT show Manage PEDMA to all members
    return pedma_has_evaluation_role_access($staff_id) || is_HR();
}

/**
 * Grade filter table on Manage PEDMA: Admin / Super Admin / HR only.
 */
function can_view_pedma_grade_filter($staff_id = '')
{
    if (is_admin($staff_id)) {
        return true;
    }

    if (is_HR()) {
        return true;
    }

    $role = get_staff_role_slug($staff_id);

    return in_array($role, ['super admin', 'admin'], true);
}

/**
 * PEDMA manager reminder emails + popup start from this date (next month after Aug 2026 go-live).
 */
function pedma_eval_reminders_start_date()
{
    return '2026-09-01';
}

function pedma_eval_reminders_are_active($asOfDate = null)
{
    $asOf = $asOfDate ? date('Y-m-d', strtotime($asOfDate)) : date('Y-m-d');

    return $asOf >= pedma_eval_reminders_start_date();
}

function is_HR($staff_id = '') {
    $role = get_staff_role_slug($staff_id);

    // Super HR inherits all existing HR role gates (menus, biometric, etc.)
    return in_array($role, ['hr', 'super hr'], true);
}

/**
 * Super HR: admin-level access within HRMS modules only (not system admin).
 */
function is_super_hr($staff_id = '')
{
    return get_staff_role_slug($staff_id) === 'super hr';
}

/**
 * Permission features Super HR can fully use (like admin), excluding finance/setup/etc.
 */
function hrms_permission_features()
{
    return [
        // Timesheets / attendance / leave / shifts
        'attendance_management',
        'leave_management',
        'route_management',
        'additional_timesheets_management',
        'table_shiftwork_management',
        'report_management',
        'table_workplace_management',
        'timesheets_shift_management',
        'timesheets_shift_categories_management',
        'setting_management',
        // HR Profile
        'hrm_dashboard',
        'staffmanage_orgchart',
        'hrm_reception_staff',
        'hrm_hr_records',
        'staffmanage_job_position',
        'staffmanage_training',
        'hr_manage_q_a',
        'hrm_contract',
        'hrm_dependent_person',
        'hrm_procedures_for_quitting_work',
        'hrm_report',
        'hrm_setting',
        // Recruitment
        'recruitment',
        // Staff list needed to manage employees in HRMS
        'staff',
        // HR-adjacent
        'pedma',
        'reports',
    ];
}

/**
 * staff_can filter: grant all capabilities on HRMS features for Super HR / HR.
 */
function super_hr_staff_can_filter($retVal, $capability, $feature, $staff_id)
{
    if ($retVal) {
        return true;
    }
    // Super HR = full HRMS. Regular HR also gets global "view" on HRMS lists
    // (role often only has view_own which hides other employees).
    if (is_super_hr($staff_id)) {
        if ($feature && in_array($feature, hrms_permission_features(), true)) {
            return true;
        }
        return $retVal;
    }
    if (is_HR($staff_id) && $feature && in_array($feature, hrms_permission_features(), true)) {
        // Give HR global view (and view_own) so they can see all HRMS lists
        if (in_array($capability, ['view', 'view_own'], true)) {
            return true;
        }
    }

    return $retVal;
}

if (function_exists('hooks') && empty($GLOBALS['super_hr_staff_can_filter_registered'])) {
    $GLOBALS['super_hr_staff_can_filter_registered'] = true;
    hooks()->add_filter('staff_can', 'super_hr_staff_can_filter', 10, 4);
}

function is_IT() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "it";
}

function is_sales() {
    $CI = &get_instance(); // Get CodeIgniter instance
    $CI->load->database(); // Load database

    $staff_id = get_staff_user_id();

    $CI->db->select('role')
           ->from('tblstaff')
           ->where('staffid', $staff_id);

    $role = $CI->db->get()->row();

    return $role && strtolower(get_role_name($role->role)) === "sales";
}

/**
 * Snapshot / PEDMA: Admin, Super Admin, HR, and HOD see all departments; managers see only their department(s).
 */
function snapshot_can_view_all_departments($staff_id = '')
{
    if ($staff_id === '' || $staff_id === null) {
        $staff_id = get_staff_user_id();
    }

    if (is_admin($staff_id) || is_super_admin() || is_admin2()) {
        return true;
    }

    if (is_HR($staff_id)) {
        return true;
    }

    return pedma_is_hod_role($staff_id);
}

/**
 * @return int[]|null null = no department restriction
 */
function snapshot_viewer_department_ids($staff_id = '')
{
    if (snapshot_can_view_all_departments($staff_id)) {
        return null;
    }

    if ($staff_id === '' || $staff_id === null) {
        $staff_id = get_staff_user_id();
    }

    $ids = [];
    foreach (get_department_by_staffid($staff_id) as $dept) {
        $id = (int) ($dept['departmentid'] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

/**
 * Normalize department filter from query string for the current viewer.
 *
 * @return int 0 = all allowed departments, >0 specific department, -1 = no access
 */
function snapshot_resolve_department_id($requestedDepartmentId, $staff_id = '')
{
    $requestedDepartmentId = (int) $requestedDepartmentId;
    if (snapshot_can_view_all_departments($staff_id)) {
        return max(0, $requestedDepartmentId);
    }

    $scope = snapshot_viewer_department_ids($staff_id);
    if ($scope === []) {
        return -1;
    }

    if ($requestedDepartmentId > 0) {
        return in_array($requestedDepartmentId, $scope, true) ? $requestedDepartmentId : (int) $scope[0];
    }

    return 0;
}

/**
 * @param array<int, object|array<string, mixed>> $departments
 * @return array<int, object|array<string, mixed>>
 */
function filter_snapshot_departments_for_viewer($departments, $staff_id = '')
{
    if (snapshot_can_view_all_departments($staff_id)) {
        return $departments;
    }

    $scope = snapshot_viewer_department_ids($staff_id);
    if ($scope === []) {
        return [];
    }

    $allowed = array_flip($scope);
    $out = [];
    foreach ($departments as $dept) {
        $id = is_object($dept) ? (int) ($dept->departmentid ?? 0) : (int) ($dept['departmentid'] ?? 0);
        if ($id > 0 && isset($allowed[$id])) {
            $out[] = $dept;
        }
    }

    return $out;
}

function snapshot_staff_id_in_viewer_scope($targetStaffId, $staff_id = '')
{
    $targetStaffId = (int) $targetStaffId;
    if ($targetStaffId < 1) {
        return false;
    }

    if (snapshot_can_view_all_departments($staff_id)) {
        return true;
    }

    $scope = snapshot_viewer_department_ids($staff_id);
    if ($scope === []) {
        return false;
    }

    foreach (get_department_by_staffid($targetStaffId) as $dept) {
        if (in_array((int) ($dept['departmentid'] ?? 0), $scope, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Departments the current user may pick in PEDMA / Snapshot filters.
 *
 * @return array<int, array<string, mixed>>
 */
function pedma_departments_for_viewer($staff_id = '')
{
    $CI = &get_instance();
    $CI->load->model('departments_model');

    return filter_snapshot_departments_for_viewer($CI->departments_model->get(), $staff_id);
}

function pedma_department_id_allowed($departmentId, $staff_id = '')
{
    $departmentId = (int) $departmentId;
    if ($departmentId <= 0) {
        return false;
    }

    if (snapshot_can_view_all_departments($staff_id)) {
        return true;
    }

    $scope = snapshot_viewer_department_ids($staff_id);

    return $scope !== [] && in_array($departmentId, $scope, true);
}

/**
 * @param array<int, array<string, mixed>> $rows PEDMA list rows (must include staffid)
 * @return array<int, array<string, mixed>>
 */
function pedma_filter_rows_by_viewer_scope(array $rows, $staff_id = '')
{
    if (snapshot_can_view_all_departments($staff_id)) {
        return $rows;
    }

    $out = [];
    foreach ($rows as $row) {
        $sid = (int) ($row['staffid'] ?? 0);
        if ($sid > 0 && snapshot_staff_id_in_viewer_scope($sid, $staff_id)) {
            $out[] = $row;
        }
    }

    return $out;
}

function pedma_staff_id_in_viewer_scope($targetStaffId, $staff_id = '')
{
    return snapshot_staff_id_in_viewer_scope($targetStaffId, $staff_id);
}

/**
 * Snapshot desktop client capture interval (seconds between screenshots).
 */
function snapshot_capture_interval_seconds()
{
    return 600;
}

/**
 * Interval stored in tbltimeinterval.time_interval (H:i:s, duration not clock time).
 */
function snapshot_capture_interval_time()
{
    return gmdate('H:i:s', snapshot_capture_interval_seconds());
}

/**
 * Ensure one tbltimeinterval row per staff at the standard capture interval.
 */
function snapshot_ensure_staff_timeinterval($staffId)
{
    $staffId = (int) $staffId;
    if ($staffId < 1) {
        return;
    }

    $CI = &get_instance();
    $CI->load->database();

    $table = db_prefix() . 'timeinterval';
    $interval = snapshot_capture_interval_time();

    $row = $CI->db->where('staffid', $staffId)->get($table)->row();
    if ($row) {
        return;
    }

    $CI->db->insert($table, [
        'staffid'       => $staffId,
        'time_interval' => $interval,
    ]);
}

/**
 * Admin/manager may edit per-staff snapshot capture intervals.
 */
function can_manage_snapshot_interval($staff_id = '')
{
    if (is_admin($staff_id) || is_super_admin() || is_IT() || is_admin2()) {
        return true;
    }

    return is_manager($staff_id);
}

/**
 * Normalize interval to H:i:s duration for tbltimeinterval (60s–3600s).
 */
function snapshot_normalize_interval_time($time)
{
    $time = trim((string) $time);
    if ($time === '') {
        return false;
    }

    if (preg_match('/^\d+:\d{2}:\d{2}$/', $time)) {
        $parts = explode(':', $time);
        $seconds = ((int) $parts[0]) * 3600 + ((int) $parts[1]) * 60 + (int) $parts[2];
    } elseif (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
        $parts = explode(':', $time);
        $seconds = ((int) $parts[0]) * 60 + (int) $parts[1];
    } elseif (ctype_digit($time)) {
        $seconds = (int) $time;
    } else {
        return false;
    }

    if ($seconds < 60 || $seconds > 3600) {
        return false;
    }

    return gmdate('H:i:s', $seconds);
}
