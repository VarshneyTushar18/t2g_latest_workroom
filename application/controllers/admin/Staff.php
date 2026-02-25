<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

class Staff extends AdminController
{
    /* List all staff members */
    public function index()
    {
        if (!has_permission('staff', '', 'view')) {
            access_denied('staff');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff');
        }
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title'] = _l('staff_members');
        $this->load->view('admin/staff/manage', $data);
    }

    /* Add new staff member or edit existing */
    public function member($id = '')
    {
        if (!has_permission('staff', '', 'view')) {
            access_denied('staff');
        }
        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        if ($this->input->post()) {
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $data['password'] = $this->input->post('password', false);

            if ($id == '') {
                if (!has_permission('staff', '', 'create')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);
                if ($id) {
                    handle_staff_profile_image_upload($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('staff/member/' . $id));
                }
            } else {
                if (!has_permission('staff', '', 'edit')) {
                    access_denied('staff');
                }
                handle_staff_profile_image_upload($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                redirect(admin_url('staff/member/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('staff_member_lowercase'));
        } else {
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member'] = $member;
            $title = $member->firstname . ' ' . $member->lastname;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to'] = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
            $data['timesheets'] = $data['logged_time']['timesheets'];
        }
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['roles'] = $this->roles_model->get();
        $data['user_notes'] = $this->misc_model->get_notes($id, 'staff');
        $data['departments'] = $this->departments_model->get();
        $data['title'] = $title;

        $this->load->view('admin/staff/member', $data);
    }

    /* Get role permission for specific role id */
    public function role_changed($id)
    {
        if (!has_permission('staff', '', 'view')) {
            ajax_access_denied('staff');
        }

        echo json_encode($this->roles_model->get($id)->permissions);
    }

    public function save_dashboard_widgets_order()
    {
        hooks()->do_action('before_save_dashboard_widgets_order');

        $post_data = $this->input->post();
        foreach ($post_data as $container => $widgets) {
            if ($widgets == 'empty') {
                $post_data[$container] = [];
            }
        }
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', serialize($post_data));
    }

    public function save_dashboard_widgets_visibility()
    {
        hooks()->do_action('before_save_dashboard_widgets_visibility');

        $post_data = $this->input->post();
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', serialize($post_data['widgets']));
    }

    public function reset_dashboard()
    {
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', null);
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', null);

        redirect(admin_url());
    }

    public function save_hidden_table_columns()
    {
        hooks()->do_action('before_save_hidden_table_columns');
        $data = $this->input->post();
        $id = $data['id'];
        $hidden = isset($data['hidden']) ? $data['hidden'] : [];
        update_staff_meta(get_staff_user_id(), 'hidden-columns-' . $id, json_encode($hidden));
    }

    public function change_language($lang = '')
    {
        hooks()->do_action('before_staff_change_language', $lang);

        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'staff', ['default_language' => $lang]);
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url());
        }
    }

    public function timesheets()
    {
        $data['view_all'] = false;
        if ((staff_can('view-timesheets', 'reports') && $this->input->get('view') == 'all') || is_in_managers_list()) {
            $data['staff_members_with_timesheets'] = $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . 'taskstimers WHERE staff_id !=' . get_staff_user_id())->result_array();
            $data['view_all'] = true;
        }
		
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_timesheets', ['view_all' => $data['view_all']]);
        }

        if ($data['view_all'] == false) {
            unset($data['view_all']);
        }

        /* New staffs variable for multiselect search filter */
        // $data['staffs'] = $this->staff_model->get_staff_timekeeping_applicable_object();
        if (is_admin()) {
            $data['staffs'] = $this->staff_model->get_staff_timekeeping_applicable_object();
        } else {
            $data['staffs'] = $this->staff_model->get_staff_based_on_department();
        }

        $data['logged_time'] = $this->staff_model->get_logged_time_data(get_staff_user_id());
        $data['title'] = '';
        $this->load->view('admin/staff/timesheets', $data);
    }

    public function delete()
    {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }

        if (has_permission('staff', '', 'delete')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }

        redirect(admin_url('staff'));
    }

    /* When staff edit his profile */
    public function edit_profile()
    {
        hooks()->do_action('edit_logged_in_staff_profile');

        if ($this->input->post()) {
            handle_staff_profile_image_upload();
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $success = $this->staff_model->update_profile($data, get_staff_user_id());

            if ($success) {
                set_alert('success', _l('staff_profile_updated'));
            }

            redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
        }
        $member = $this->staff_model->get(get_staff_user_id());
        $this->load->model('departments_model');
        $data['member'] = $member;
        $data['departments'] = $this->departments_model->get();
        $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);
        $data['title'] = $member->firstname . ' ' . $member->lastname;
        $this->load->view('admin/staff/profile', $data);
    }

    /* Remove staff profile image / ajax */
    public function remove_staff_profile_image($id = '')
    {
        $staff_id = get_staff_user_id();
        if (is_numeric($id) && (has_permission('staff', '', 'create') || has_permission('staff', '', 'edit'))) {
            $staff_id = $id;
        }
        hooks()->do_action('before_remove_staff_profile_image');
        $member = $this->staff_model->get($staff_id);
        if (file_exists(get_upload_path_by_type('staff') . $staff_id)) {
            delete_dir(get_upload_path_by_type('staff') . $staff_id);
        }
        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix() . 'staff', [
            'profile_image' => null,
        ]);

        if (!is_numeric($id)) {
            redirect(admin_url('staff/edit_profile/' . $staff_id));
        } else {
            redirect(admin_url('staff/member/' . $staff_id));
        }
    }

    /* When staff change his password */
    public function change_password_profile()
    {
        if ($this->input->post()) {
            $response = $this->staff_model->change_password($this->input->post(null, false), get_staff_user_id());
            if (is_array($response) && isset($response[0]['passwordnotmatch'])) {
                set_alert('danger', _l('staff_old_password_incorrect'));
            } else {
                if ($response == true) {
                    set_alert('success', _l('staff_password_changed'));
                } else {
                    set_alert('warning', _l('staff_problem_changing_password'));
                }
            }
            redirect(admin_url('staff/edit_profile'));
        }
    }

    /* View public profile. If id passed view profile by staff id else current user*/
    public function profile($id = '')
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }

        hooks()->do_action('staff_profile_access', $id);

        $data['logged_time'] = $this->staff_model->get_logged_time_data($id);
        $data['staff_p'] = $this->staff_model->get($id);

        if (!$data['staff_p']) {
            blank_page('Staff Member Not Found', 'danger');
        }

        $this->load->model('departments_model');
        $data['staff_departments'] = $this->departments_model->get_staff_departments($data['staff_p']->staffid);
        $data['departments'] = $this->departments_model->get();
        $data['title'] = _l('staff_profile_string') . ' - ' . $data['staff_p']->firstname . ' ' . $data['staff_p']->lastname;
        // notifications
        $total_notifications = total_rows(db_prefix() . 'notifications', [
            'touserid' => get_staff_user_id(),
        ]);
        $data['total_pages'] = ceil($total_notifications / $this->misc_model->get_notifications_limit());
        $this->load->view('admin/staff/myprofile', $data);
    }

    /* Change status to staff active or inactive / ajax */
    public function change_staff_status($id, $status)
    {
        if (has_permission('staff', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->staff_model->change_staff_status($id, $status);
            }
        }
    }

    /* Logged in staff notifications*/
    public function notifications()
    {
        $this->load->model('misc_model');
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $offset = ($page * $this->misc_model->get_notifications_limit());
            $this->db->limit($this->misc_model->get_notifications_limit(), $offset);
            $this->db->where('touserid', get_staff_user_id());
            $this->db->order_by('date', 'desc');
            $notifications = $this->db->get(db_prefix() . 'notifications')->result_array();
            $i = 0;
            foreach ($notifications as $notification) {
                if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0) || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                    if ($notification['fromuserid'] != 0) {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('staff/profile/' . $notification['fromuserid']) . '">' . staff_profile_image($notification['fromuserid'], [
                            'staff-profile-image-small',
                            'img-circle',
                            'pull-left',
                        ]) . '</a>';
                    } else {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('clients/client/' . $notification['fromclientid']) . '">
                    <img class="client-profile-image-small img-circle pull-left" src="' . contact_profile_image_url($notification['fromclientid']) . '"></a>';
                    }
                } else {
                    $notifications[$i]['profile_image'] = '';
                    $notifications[$i]['full_name'] = '';
                }
                $additional_data = '';
                if (!empty($notification['additional_data'])) {
                    $additional_data = unserialize($notification['additional_data']);
                    $x = 0;
                    foreach ($additional_data as $data) {
                        if (strpos($data, '<lang>') !== false) {
                            $lang = get_string_between($data, '<lang>', '</lang>');
                            $temp = _l($lang);
                            if (strpos($temp, 'project_status_') !== false) {
                                $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                                $temp = $status['name'];
                            }
                            $additional_data[$x] = $temp;
                        }
                        $x++;
                    }
                }
                $notifications[$i]['description'] = _l($notification['description'], $additional_data);
                $notifications[$i]['date'] = time_ago($notification['date']);
                $notifications[$i]['full_date'] = $notification['date'];
                $i++;
            } //$notifications as $notification
            echo json_encode($notifications);
            die;
        }
    }

    public function update_two_factor()
    {
        $fail_reason = _l('set_two_factor_authentication_failed');
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('two_factor_auth', _l('two_factor_auth'), 'required');

            if ($this->input->post('two_factor_auth') == 'google') {
                $this->form_validation->set_rules('google_auth_code', _l('google_authentication_code'), 'required');
            }

            if ($this->form_validation->run() !== false) {
                $two_factor_auth_mode = $this->input->post('two_factor_auth');
                $id = get_staff_user_id();
                if ($two_factor_auth_mode == 'google') {
                    $this->load->model('Authentication_model');
                    $secret = $this->input->post('secret');
                    $success = $this->authentication_model->set_google_two_factor($secret);
                    $fail_reason = _l('set_google_two_factor_authentication_failed');
                } elseif ($two_factor_auth_mode == 'email') {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 1]);
                } else {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 0]);
                }
                if ($success) {
                    set_alert('success', _l('set_two_factor_authentication_successful'));
                    redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
                }
            }
        }
        set_alert('danger', $fail_reason);
        redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
    }

    public function verify_google_two_factor()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->load->model('authentication_model');
            $is_success = $this->authentication_model->is_google_two_factor_code_valid($data['code'], $data['secret']);
            $result = [];

            header('Content-Type: application/json');
            if ($is_success) {
                $result['status'] = 'success';
                $result['message'] = _l('google_2fa_code_valid');;

                echo json_encode($result);
                die;
            }

            $result['status'] = 'failed';
            $result['message'] = _l('google_2fa_code_invalid');;

            echo json_encode($result);
            die;
        }
    }

    public function save_completed_checklist_visibility()
    {
        hooks()->do_action('before_save_completed_checklist_visibility');

        $post_data = $this->input->post();
        if (is_numeric($post_data['task_id'])) {
            update_staff_meta(get_staff_user_id(), 'task-hide-completed-items-' . $post_data['task_id'], $post_data['hideCompleted']);
        }
    }


    // custom controller to add and send details of staff/employee
    public function add_details($id = '')
    {

        if ($this->input->post()) {
            $data = $this->input->post();

            $shiftTimeInHr = 9;
            $data['shiftend'] = date('H:i', (strtotime($data['shiftstart']) + 60 * 60 * $shiftTimeInHr));

            $staff_info_data = $this->staff_model->get_tbl_info_data($data['staffid']);
            if ($staff_info_data) {
                $id = $this->staff_model->update_details($data);
                if ($id) {
                    // die($id);
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                    redirect(admin_url('staff/add_details/'));
                }
            } else {

                $id = $this->staff_model->add_details($data);
                if ($id) {
                    // die($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('staff/add_details/'));
                }
            }

            // if ($id == '') {

            //     $shiftTimeInHr = 9;
            //     $data['shiftend'] = date('H:i', (strtotime($data['shiftstart']) + 60 * 60 * $shiftTimeInHr));
            //     $id = $this->staff_model->add_details($data);
            //     if ($id) {
            //         // die($id);
            //         set_alert('success', _l('added_successfully', _l('staff_member')));
            //         redirect(admin_url('staff/add_details/'));
            //     }
            // }
        }
        $data['staffs'] = $this->staff_model->get_staff_timekeeping_applicable_object();
        $data['roles'] = $this->roles_model->get();
        $this->load->view('admin/staff/add_details', $data);
    }

    public function leave_balance()
    {
        //        ini_set('display_errors', 1);
        //        ini_set('display_startup_errors', 1);
        //        error_reporting(E_ALL);
	//print_r($data);die;
        $selectedMonth = Date('m');
        $currentYear = Date('Y');
        $selectedYear = '';
        if ($this->input->post()) {
            $data = $this->input->post();
            $selectedMonth = $data['range'];
            $selectedYear = $data['year'];
        }

        if ($selectedYear == '') {
            $selectedYear = $currentYear;
        }
		
        // $monthQuery = ($selectedMonth == 0) ? '' : " WHERE (MONTH(tbltimesheets_requisition_leave.start_time) = ? AND YEAR(tbltimesheets_requisition_leave.start_time) = ?) OR tbltimesheets_requisition_leave.start_time IS NULL";

        // $sql = "SELECT tblstaff_info.empid, tblstaff_info.staffid , tbltimesheets_requisition_leave.type_of_leave_text	,MONTH(tbltimesheets_requisition_leave.start_time) AS month, DAY(LAST_DAY(tbltimesheets_requisition_leave.start_time)) AS number_of_days, SUM(tbltimesheets_requisition_leave.number_of_leaving_day) AS Leaves_taken, MIN(tbltimesheets_requisition_leave.number_of_days) AS remaining_leaves , tblstaff_info.leave_earned , tblstaff_info.doj
        // FROM tblstaff_info
        // INNER JOIN tbltimesheets_requisition_leave ON tblstaff_info.staffid=tbltimesheets_requisition_leave.staff_id " . $monthQuery . " GROUP BY tblstaff_info.staffid ;";

        $addQuery = (is_admin(get_staff())) ? ' WHERE tblstaff.active = 1 ' : " WHERE tblstaff.staffid=" . get_staff()->staffid;


        if (is_admin()) {
            $addQuery = ' WHERE tblstaff.active = 1 ';
        } else if (attendance_permission()) {
            $all_departments_array = get_department_by_staffid(get_staff_user_id());
            $all_departments_id = array_column($all_departments_array, 'departmentid');
            $all_departments_id_str = implode(",", $all_departments_id);
            $addQuery = " WHERE tblstaff_departments.departmentid IN ($all_departments_id_str) AND tblstaff.active = 1 ";
            // echo $addQuery ; die;
        } else {
            $addQuery = " WHERE tblstaff.staffid=" . get_staff()->staffid;
        }
		
        $sqlAdmin = "SELECT
        tblstaff.staffid,
        tblstaff_info.empid,
        tblstaff_info.doj,
        SUM(
            CASE
                WHEN
                    (
                        MONTH(tbltimesheets_requisition_leave.start_time) = ?
                        AND YEAR(tbltimesheets_requisition_leave.start_time) = ?
                    )
                THEN tbltimesheets_requisition_leave.number_of_leaving_day
                ELSE 0
            END
        ) AS Leaves_taken
        FROM
            tblstaff_info
        LEFT JOIN tbltimesheets_requisition_leave ON tblstaff_info.staffid = tbltimesheets_requisition_leave.staff_id
        RIGHT JOIN tblstaff ON tblstaff_info.staffid = tblstaff.staffid LEFT JOIN tblstaff_departments ON tblstaff_departments.staffid = tblstaff.staffid " . $addQuery . " GROUP BY
            tblstaff.staffid, tblstaff_info.empid, tblstaff_info.doj";


        if ($selectedMonth == 0) {
            for ($month_in_number = 1; $month_in_number <= 12; $month_in_number++) {
                //                $data['table_data'][$month_in_number] = array();

                $data['table_data'][$month_in_number] = $this->db->query($sqlAdmin, [$month_in_number, $selectedYear])->result_array();
				//print_r($data['table_data'][$month_in_number]);
                foreach ($data['table_data'][$month_in_number] as &$leaveData) {
                    //                    print_r($leaveData); die;
                    $leaveData['carry_forward'] = $this->staff_model->carryForward($leaveData['staffid'], $leaveData['doj'], $month_in_number, $selectedYear);
                    $leaveData['earned_leave'] = $this->staff_model->calculateEarnedLeaves($leaveData['doj']);
                    $leaveData['monthly_leaves'] = $this->staff_model->monthlyLeaveBalance($leaveData['staffid'], $leaveData['doj'], $month_in_number, $selectedYear);
					$leaveData['leave_taken'] = $this->staff_model->monthlyLeave($leaveData['staffid'], $month_in_number, $selectedYear);
					$leaveData['status'] = $this->staff_model->monthlystatus($leaveData['staffid'], $month_in_number, $selectedYear);
					$leaveData['status_approve'] = $this->staff_model->status_approve($leaveData['staffid'], $month_in_number, $selectedYear);
                }
            }
        } else {

            // Prepare the query and bind the selected month parameter if needed
            //            $data['table_data'] = $this->db->query($sqlAdmin, [$selectedMonth, $selectedYear])->result_array();
            //            foreach ($data['table_data'] as &$leaveData) {
            //                $leaveData['carry_forward'] = $this->staff_model->carryForward($leaveData['staffid'], $leaveData['doj'], $selectedMonth);
            //                $leaveData['earned_leave'] = $this->staff_model->calculateEarnedLeaves($leaveData['doj']);
            //                $leaveData['monthly_leaves'] = $this->staff_model->monthlyLeaveBalance($leaveData['staffid'], $leaveData['doj'], $selectedMonth);
            //            }
            $month_in_number = $selectedMonth;
            $data['table_data'][$month_in_number] = $this->db->query($sqlAdmin, [$month_in_number, $selectedYear])->result_array();
	
            // print_r($data['table_data'][$month_in_number]);die;
            foreach ($data['table_data'][$month_in_number] as &$leaveData) {
                $leaveData['carry_forward'] = $this->staff_model->carryForward($leaveData['staffid'], $leaveData['doj'], $month_in_number, $selectedYear);
                $leaveData['earned_leave'] = $this->staff_model->calculateEarnedLeaves($leaveData['doj']);
                $leaveData['monthly_leaves'] = $this->staff_model->monthlyLeaveBalance($leaveData['staffid'], $leaveData['doj'], $month_in_number, $selectedYear);
				$leaveData['leave_taken'] = $this->staff_model->monthlyLeave($leaveData['staffid'], $month_in_number, $selectedYear);
				$leaveData['status'] = $this->staff_model->monthlystatus($leaveData['staffid'], $month_in_number, $selectedYear);
				$leaveData['status_approve'] = $this->staff_model->status_approve($leaveData['staffid'], $month_in_number, $selectedYear);
				//print_r($leaveData);
            }
        }


        $data['currentMonth'] = $selectedMonth;
        $data['currentYear'] = $currentYear;
        $data['selectedYear'] = $selectedYear;
		
		

	//print_r($data);die;


        $this->load->view('admin/staff/leave_balance', $data);
    }

    public function get_leave_data()
    {
        $sql = "SELECT * FROM tblstaff_info
        INNER JOIN tbltimesheets_requisition_leave ON tblstaff_info.staffid=tbltimesheets_requisition_leave.staff_id;";

        $query = $this->db->query($sql);
        $data = $query->result_array();

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function get_custom_staff_details()
    {

        $staffId = $this->input->post('staffid');

        $data = $this->staff_model->get_tbl_info_data($staffId);

        // Send the data as JSON
        echo json_encode($data);
    }
    /*  Performance of Employee List  */
    public function pedma_admin()
    {


        // if (!is_manager()) {
        if (!attendance_permission()) {

            access_denied('timesheets');
        }

        $this->load->model('departments_model');

        $data['departments'] = $this->departments_model->get_staff_departments();
        $isManager = $this->db->query('SELECT manageleave from tblstaff_info WHERE staffid = ' . get_staff_user_id())->result_array();

        $staffs = $this->staff_model->get_staff_based_on_department();

        $data['staffs'] = $staffs;

        $data['title'] = _l('timesheets');

        $this->load->view('admin/staff/pedma_admin', $data);
    }

    public function pedma_admin_old()
    {


        // if (!is_manager()) {
        if (!attendance_permission()) {

            access_denied('timesheets');
        }

        $this->load->model('departments_model');

        $data['departments'] = $this->departments_model->get_staff_departments();
        $isManager = $this->db->query('SELECT manageleave from tblstaff_info WHERE staffid = ' . get_staff_user_id())->result_array();

        // if ($isManager[0]['manageleave'] == 1) {
        $staffs = $this->staff_model->get_staff_based_on_department();

        // echo '<pre>';
        // print_r($staffs);die;
        // } else {
        //     $staffs = $this->staff_model->get('', ['active' => 1]);
        // }

        $data['staffs'] = $staffs;

        $data['title'] = _l('timesheets');

        // if (is_admin()) {
        //     $data['staffs']  = $this->staff_model->get('', ['active' => 1]);
        // }

        $this->load->view('admin/staff/pedma_admin_old', $data);
    }

    public function pedma_admin_kra()
    {


        // if (!is_manager()) {
        if (!attendance_permission()) {

            access_denied('timesheets');
        }

        $data['kra'] = $this->staff_model->get_kra_based_on_department();

        $this->load->view('admin/staff/pedma_admin_kra', $data);
    }

    public function pedma_admin_kra_action($id = '')
    {
        if (!attendance_permission()) {

            access_denied('timesheets');
        }

        if ($this->input->method() === 'post') {
            
            $type = $this->input->post('kraType');
            $kraMaxScore = $this->input->post('kraMaxScore');
            $kpiName = $this->input->post('kpiName');
            $kpiDescription = $this->input->post('kpiDescription');
            $kpiScore = $this->input->post('kpiScore');

            if(!empty($kpiScore)){
                $kpiScoreSum = array_sum($kpiScore);

                if($kpiScoreSum < $kraMaxScore){
                    set_alert('warning', 'Total KPI scores cannot less than the KRA Max Score.');
                    if($id != ''){
                        redirect(admin_url('staff/pedma_admin_kra_action/'.$id));
                    }else{
                        redirect(admin_url('staff/pedma_admin_kra_action'));
                    }
                }else if($kpiScoreSum > $kraMaxScore){
                    set_alert('warning', 'Total KPI scores cannot exceed the KRA Max Score.');
                    if($id != ''){
                        redirect(admin_url('staff/pedma_admin_kra_action/'.$id));
                    }else{
                        redirect(admin_url('staff/pedma_admin_kra_action'));
                    }
                }
            }

            $data = array(
                'departmentid' => $this->input->post('departments'),
                'type' => $type,
                'name' => $this->input->post('kraName'),
                'description' => $this->input->post('kraDescription'),
                'max_score' => $kraMaxScore,
            );
            
            if($id != ''){
                $kraid = $this->staff_model->update_tblstaff_performance_kra($data,$id);
            }else{
                $kraid = $this->staff_model->insert_into_tblstaff_performance_kra($data);
            }

            if(!empty($kraid) && !empty($kpiScore)){

                for($i = 0; $i < count($kpiName); $i++){
                    $data = array(
                        'kra_id' => $kraid,
                        'name' => $kpiName[$i],
                        'description' => $kpiDescription[$i],
                        'max_score' => $kpiScore[$i]
                    );

                    $kpiid = $this->staff_model->insert_into_tblstaff_performance_kpi($data);
                }

                if($kpiid && $id == ''){
                    set_alert('success', 'KRA Added Successfully.');
                    redirect(admin_url('staff/pedma_admin_kra'));
                }else{
                    set_alert('success', 'KRA Updated Successfully.');
                    redirect(admin_url('staff/pedma_admin_kra'));
                }
            }else if($kraid != 0){

                if($id != ''){
                    set_alert('success', 'KRA Updated Successfully.');
                    redirect(admin_url('staff/pedma_admin_kra'));
                }else{
                    set_alert('success', 'KRA Added Successfully.');
                    redirect(admin_url('staff/pedma_admin_kra'));
                }
            }else{
                set_alert('warning', 'Something went wrong.');
                redirect(admin_url('staff/pedma_admin_kra_action'));
            }
        }        

        $this->load->model('departments_model');

        $data['departments'] = $this->departments_model->get_staff_departments();

        if($id != ''){
            $data['kra'] = $this->staff_model->get_kra_based_on_id($id);
            $data['kpi'] = $this->staff_model->get_kpi_based_on_kraid($id);

            $this->load->view('admin/staff/pedma_admin_kra_action', $data);
        }else{
            $this->load->view('admin/staff/pedma_admin_kra_action', $data);
        }
    }

    public function pedma()
    {
        $id = get_staff_user_id(); 
        // $performance = $this->db->get_where('tblstaff_performance', ['staffid' => $id]);

        $performance = $this->db->from('tblstaff_performance')->where('staffid', $id)->where('status !=',0)->order_by('date_created')->get();

        $data['staffid'] = $id;
        $data['performance_values'] = $performance->result_array();
        
        $this->load->view('admin/staff/pedma', $data);
    }

    public function manage_pedma()
    {
        
		$staff_id = $this->staff_model->get_kra_based_on_department();

        $data['result'] = $this->departments_model->get_staff_departments();
        $this->load->view('admin/staff/manage_pedma', $data);
    }

    public function manage_pedma_staff()
	{
		$id = $this->input->post('data');
		//$id = get_staff_user_id(); 
		
        // $performance = $this->db->get_where('tblstaff_performance', ['staffid' => $id]);
		
        $performance = $this->db->from('tblstaff_performance')->where('staffid', $id)->where('status !=',0)->order_by('date_created')->get();

        // $data['staffid'] = $id;
        $data['performance_values'] = $performance->result_array();
		
		echo json_encode($data['performance_values']);
	}

    public function pedma_old()
    {
        $id = get_staff_user_id();
        $performance = $this->db->get_where('tblstaff_performance2', ['staffid' => $id]);

        $data['performance_values'] = $performance->result_array();
        $this->load->view('admin/staff/pedma_old', $data);
    }

    public function monthNameToNumber($monthName) {
        // Convert the month name to lowercase and trim any spaces for consistency
        $monthName = strtolower(trim($monthName));
    
        // Array of months in lowercase
        $months = [
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12
        ];
    
        // Check if the month name exists in the array and return the corresponding number
        return isset($months[$monthName]) ? $months[$monthName] : "Invalid month name";
    }
    
    public function pedma_staff_reply() {
        $staffid = $this->input->post('staffid');
        $comment = $this->input->post('comment');
        $monthName = $this->input->post('month');
        $year = $this->input->post('year');
        $score = $this->input->post('score');
    
        $monthNumber = $this->monthNameToNumber($monthName);
    
        // Check if the monthNumber is valid before proceeding
        if (is_numeric($monthNumber) && $monthNumber >= 1 && $monthNumber <= 12) {
            // Format monthNumber to ensure it is always two digits
            $formattedMonth = str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
            $date_created = "{$year}-{$formattedMonth}-01";
        }

        $affected_rows = $this->staff_model->update_tblstaff_performance_staffComment($staffid, $date_created, $comment);
        if ($affected_rows > 0) {
            set_alert('success', 'Message Sent Successfully.');
            $this->staff_model->send_performance_email_reply($staffid, $comment, $monthName, $year, $score);
            redirect(admin_url('staff/pedma'));
        }

    }
    
    // get staff data in ajax
    public function get_staff_json()
    {
        $staffId = $this->input->post('staffid');

        $data[] = $this->staff_model->get('', ['active' => 1, 'staffid' => $staffId]);
        $data[] = $this->staff_model->get_department_by_staffid_staff_model($staffId);
        $data[0][0]['job_name'] = get_job_position_by_staffid($staffId);

        echo json_encode($data);
    }

    public function staff_performance()
    {
        $staffid =  $this->input->post('staffid');
        $date_created =  $this->input->post('performance_month');
        $type =  $this->input->post('kraOption');
        $kraData1 =  $this->input->post('kraData1');
        $kraData2 =  $this->input->post('kraData2');
        $average1 = $this->input->post('avg_score1');
        $average2 = $this->input->post('avg_score2');
        $overall_feedback =  $this->input->post('overall_feedback');
        $status =  $this->input->post('status');

        $staff_name = get_staff_full_name($staffid);

        if($type == "default"){
            $kraData = json_encode($kraData1);
            $average = $average1;
        }else{
            $kraData = json_encode($kraData2);
            $average = $average2;
        }

        $data = array(
            'staffid' => $staffid,
            'type' => $type,
            'kra_data' => $kraData,
            'avg_score' => $average,
            'overall_feedback' => $overall_feedback,
            'status' => $status,
            'date_created' => $date_created . '-01'
        );

        if ($this->staff_model->check_if_review_added($staffid, $date_created)) {

            // echo 'update';
            $affected_rows = $this->staff_model->update_tblstaff_performance($staffid, $date_created, $data);
            if ($affected_rows > 0) {
                set_alert('success', 'Review Updated Successfully.');
                if($status == 1){
                    $this->staff_model->send_performance_email($staffid, $data);
                }
                log_activity('Review Updated Successfully [Staff Name: ' . $staff_name . ', KRA Type: ' . $type . ', Score: ' . $average . ', Status: ' . ($status == 0) ? 'Draft' : (($status == 1) ? 'Publish' : 'Update') . ']');

                redirect(admin_url('staff/pedma_admin'));
            }
        } else {

            $id = $this->staff_model->insert_into_tblstaff_performance($data);

            if ($id) {
                set_alert('success', 'Review Added Successfully.');
                if($status == 1){
                    $this->staff_model->send_performance_email($staffid, $data);
                }
                log_activity('Review Added Successfully [Staff Name: ' . $staff_name . ', KRA Type: ' . $type . ', Score: ' . $average . ', Status: ' . ($status == 0 ? 'Draft' : 'Publish') . ']');

                redirect(admin_url('staff/pedma_admin'));
            }
        }
    }

    public function get_staff_performance_month()
    {
        // print_r($_POST);die;
        $search_date =  $this->input->post('date');

        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', get_staff_user_id());
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);

        $staff_performance_data = $this->db->get()->result_array();
        echo json_encode($staff_performance_data[0]);
    }

    public function get_staff_performance_month_old()
    {
        // print_r($_POST);die;
        $search_date =  $this->input->post('date');

        $this->db->select('*');
        $this->db->from('tblstaff_performance2');
        $this->db->where('staffid', get_staff_user_id());
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);

        $staff_performance_data = $this->db->get()->result_array();
        echo json_encode($staff_performance_data[0]);
    }

    public function get_previous_month_custom_kra_data(){
        $search_date =  $this->input->post('date');


        $staff_id = $this->input->post('staffid');
        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', $staff_id);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);
        $staff_performance_data = $this->db->get()->result_array();

        echo json_encode($staff_performance_data[0]);
    }

    public function get_staff_department_json()
    {
        $departmentid = $this->input->post('department');
        $data[] = $this->staff_model->get_staff_based_on_department($departmentid);
        echo json_encode($data[0]);
    }

    public function get_staff_performance_month_and_id()
    {
        $search_date =  $this->input->post('month');


        $staff_id = $this->input->post('staffid');
        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', $staff_id);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);
        $staff_performance_data = $this->db->get()->result_array();

        echo json_encode($staff_performance_data[0]);
    }

    public function get_staff_performance_month_and_id_old()
    {
        $search_date =  $this->input->post('month');


        $staff_id = $this->input->post('staffid');
        $this->db->select('*');
        $this->db->from('tblstaff_performance2');
        $this->db->where('staffid', $staff_id);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);
        $staff_performance_data = $this->db->get()->result_array();

        echo json_encode($staff_performance_data[0]);
    }

    public function get_kra_based_on_department_json(){

        $departmentid = $this->input->post('department');
        $data['default_kra'] = $this->staff_model->get_default_kra_based_on_department($departmentid);
        $data['custom_kra'] = $this->staff_model->get_custom_kra_based_on_department($departmentid);

        echo json_encode($data);
    }

    public function update_score_by_fatal_error(){
        $staff_id = $this->input->post('staffid');
        $score = $this->input->post('scoreAdjustment');
        $months = $this->input->post('selectedMonths');
        $comment = $this->input->post('fatal_comment');
    
        $data = array(
            'fatal_error_score' => $score,
        );
    
        for($i = 0; $i < count($months); $i++){
            $formatted_date = $months[$i] . "-01";
            $this->db->where('staffid', $staff_id);
            $this->db->where('date_created', $formatted_date);
            $status = $this->db->update('tblstaff_performance', $data);
        }

        if($status){
            $this->staff_model->send_fatal_error_performance_email($staff_id, $score, $months, $comment);
            echo json_encode(array('status' => true));
        }
    }
    
    public function update_score_by_add_on(){
        $staff_id = $this->input->post('staffid');
        $score = $this->input->post('scoreAdjustment');
        $months = $this->input->post('selectedMonths');
        $comment = $this->input->post('addOn_comment');
    
        $data = array(
            'add_on_score' => $score,
        );
    
        for($i = 0; $i < count($months); $i++){
            $formatted_date = $months[$i] . "-01";
            $this->db->where('staffid', $staff_id);
            $this->db->where('date_created', $formatted_date);
            $status = $this->db->update('tblstaff_performance', $data);
        }

        if($status){
            $this->staff_model->send_add_on_performance_email($staff_id, $score, $months, $comment);
            echo json_encode(array('status' => true));
        }
    }
}
