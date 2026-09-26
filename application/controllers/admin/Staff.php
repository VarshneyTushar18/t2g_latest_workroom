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
        $this->load->helper('timesheets/timesheets');

        $selectedMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $selectedYear = $currentYear;

        // Accept Apply form (POST) and staff-picker reload (GET).
        $rangeIn = $this->input->post('range');
        if ($rangeIn === null || $rangeIn === false || $rangeIn === '') {
            $rangeIn = $this->input->get('range');
        }
        $yearIn = $this->input->post('year');
        if ($yearIn === null || $yearIn === false || $yearIn === '') {
            $yearIn = $this->input->get('year');
        }
        if ($rangeIn !== null && $rangeIn !== false && $rangeIn !== '') {
            $selectedMonth = (int) $rangeIn;
        }
        if ($yearIn !== null && $yearIn !== false && $yearIn !== '') {
            $selectedYear = (int) $yearIn;
        }
        if ($selectedYear < 2000) {
            $selectedYear = $currentYear;
        }
        if ($selectedMonth < 0 || $selectedMonth > 12) {
            $selectedMonth = (int) date('n');
        }

        $view_staff_id = (int) get_staff_user_id();
        $picked_staff = $this->input->post('staff_id');
        if ($picked_staff === null || $picked_staff === false || $picked_staff === '') {
            $picked_staff = $this->input->get('staff_id');
        }
        $filter_one_staff = false;
        if ($picked_staff !== null && $picked_staff !== false && $picked_staff !== '' && timesheets_can_view_staff((int) $picked_staff)) {
            $view_staff_id = (int) $picked_staff;
            $filter_one_staff = true;
        }

        $team_ids = timesheets_get_team_staff_ids();
        $is_hr = timesheets_hr_can_view_all_staff();

        // Full company report is expensive (carry-forward per employee). Only when HR asks (?all=1).
        $want_all = false;
        $allIn = $this->input->post('all');
        if ($allIn === null || $allIn === false || $allIn === '') {
            $allIn = $this->input->get('all');
        }
        if ($is_hr && !$filter_one_staff && ((string) $allIn === '1')) {
            $want_all = true;
        }

        // Default open: always one person (self) — not the full company table.
        if (!$filter_one_staff && !$want_all) {
            $view_staff_id = (int) get_staff_user_id();
            $filter_one_staff = true;
        }

        if ($filter_one_staff) {
            // Staff picker selected — show that employee's report rows.
            $addQuery = ' WHERE tblstaff.staffid = ' . (int) $view_staff_id . ' ';
        } elseif ($want_all) {
            $addQuery = ' WHERE tblstaff.active = 1 ';
        } elseif (is_array($team_ids) && count($team_ids) > 1) {
            $addQuery = ' WHERE tblstaff.staffid IN (' . implode(',', array_map('intval', $team_ids)) . ') AND tblstaff.active = 1 ';
        } else {
            $addQuery = ' WHERE tblstaff.staffid=' . (int) get_staff_user_id();
        }

        $data = [];
        $data['table_data'] = [];

        // Slim staff list only — leave totals are computed in enrich_leave_balance_month.
        $sqlStaff = "SELECT
            tblstaff.staffid,
            tblstaff.firstname,
            tblstaff.lastname,
            tblstaff_info.empid,
            tblstaff_info.doj
        FROM tblstaff
        LEFT JOIN tblstaff_info ON tblstaff_info.staffid = tblstaff.staffid
        " . $addQuery;

        $staff_rows = $this->db->query($sqlStaff)->result_array();
        if (!is_array($staff_rows)) {
            $staff_rows = [];
        }

        // All Months: only through current month for the current year (skip future months).
        $through_month = 12;
        if ((int) $selectedYear === (int) date('Y')) {
            $through_month = max(1, (int) date('n'));
        }

        if ($selectedMonth == 0) {
            // One year pass instead of 12× full carry rebuilds.
            $data['table_data'] = $this->staff_model->enrich_leave_balance_year(
                $staff_rows,
                $selectedYear,
                $through_month
            );
        } else {
            $month_in_number = (int) $selectedMonth;
            $data['table_data'][$month_in_number] = $this->staff_model->enrich_leave_balance_month(
                $staff_rows,
                $month_in_number,
                $selectedYear
            );
        }

        $data['currentMonth'] = $selectedMonth;
        $data['currentYear'] = $currentYear;
        $data['selectedYear'] = $selectedYear;
        $data['report_row_count'] = 0;
        foreach ($data['table_data'] as $monthRows) {
            $data['report_row_count'] += is_array($monthRows) ? count($monthRows) : 0;
        }

        $data['leave_balance_cards'] = [];
        $data['leave_balance_year'] = (int) $selectedYear;
        $data['userid'] = $view_staff_id;
        $data['can_pick_staff'] = timesheets_user_can_pick_staff();
        // Slim picker seed; full list loads via AJAX after paint.
        $data['staff_list'] = [];
        if ($data['can_pick_staff']) {
            $me = $this->db->select('staffid, firstname, lastname')->where('staffid', $view_staff_id)->get(db_prefix() . 'staff')->row_array();
            $data['staff_list'] = $me ? [$me] : [];
        }
        $data['is_team_manager'] = timesheets_is_team_manager();
        $data['is_hr_viewer'] = $is_hr;
        $data['want_all_report'] = $want_all;
        $data['can_edit_earned_leave'] = $data['is_hr_viewer'];
        $data['filter_one_staff'] = $filter_one_staff;

        // Cards first — All Months summary uses current EL CF/balance from here.
        $card_month = ((int) $selectedMonth > 0) ? (int) $selectedMonth : $through_month;
        $data['leave_balance_month'] = (int) $card_month;
        if (is_dir(module_dir_path('timesheets'))) {
            $this->load->model('timesheets/timesheets_model');
            $data['leave_balance_cards'] = $this->timesheets_model->get_staff_leave_balance_cards(
                $view_staff_id,
                (int) $selectedYear,
                $card_month
            );
        }

        $data['summary'] = null;
        if ($selectedMonth != 0 && isset($data['table_data'][$selectedMonth])) {
            foreach ($data['table_data'][$selectedMonth] as $row) {
                if ((int) $row['staffid'] !== $view_staff_id) {
                    continue;
                }
                $data['summary'] = [
                    'carry_forward' => $row['carry_forward'],
                    'leave_taken' => $row['leave_taken'],
                    'earned_leave' => $row['earned_leave'],
                    'leave_balance' => $row['leave_balance'],
                    'month' => (int) $selectedMonth,
                    'month_name' => date('F', mktime(0, 0, 0, (int) $selectedMonth, 1)),
                ];
                break;
            }
        } elseif ($selectedMonth == 0) {
            // All Months: always show CURRENT carry forward + leave balance.
            // Leave taken in any month (incl. previous) adjusts this same running balance.
            $taken_ytd = 0.0;
            $earned_ytd = 0.0;
            $cf = null;
            $bal = null;
            foreach ($data['table_data'] as $m => $month_rows) {
                if (!is_array($month_rows)) {
                    continue;
                }
                foreach ($month_rows as $row) {
                    if ((int) $row['staffid'] !== $view_staff_id) {
                        continue;
                    }
                    $taken_ytd += (float) ($row['leave_taken'] ?? 0);
                    $earned_ytd += (float) ($row['earned_leave'] ?? 0);
                    if ((int) $m === (int) $through_month) {
                        $cf = $row['carry_forward'];
                        $bal = $row['leave_balance'];
                    }
                }
            }
            foreach ($data['leave_balance_cards'] as $card) {
                if (($card['slug'] ?? '') !== 'earned-leave') {
                    continue;
                }
                // Prefer live card values so CF/Balance never "disappear" on All Months.
                $cf = $card['carry_forward'] ?? $cf;
                $bal = $card['balance'] ?? $bal;
                break;
            }
            if ($cf === null) {
                $cf = 0;
            }
            if ($bal === null) {
                $bal = 0;
            }
            $data['summary'] = [
                'carry_forward' => $cf,
                'leave_taken' => round($taken_ytd, 2),
                'earned_leave' => round($earned_ytd, 2),
                'leave_balance' => $bal,
                'month' => 0,
                'month_name' => 'All months (current)',
            ];
        }

        $this->load->view('admin/staff/leave_balance', $data);
    }

    /**
     * HR: bulk set earned leave by department (like Manage Saturday).
     */
    public function manage_earned_leave()
    {
        $this->load->helper('timesheets/timesheets');
        if (!timesheets_hr_can_view_all_staff()) {
            access_denied('manage_earned_leave');
        }

        $this->load->model('departments_model');

        $data['title'] = 'Manage Earned Leave';
        $data['departments'] = $this->departments_model->get();
        $data['staffs'] = [];
        $data['current_year'] = (int) date('Y');
        $data['current_month'] = (int) date('m');

        $this->load->view('admin/staff/manage_earned_leave', $data);
    }

    /**
     * AJAX: save earned leave for one or many staff for a month.
     */
    public function save_earned_leave_bulk()
    {
        $this->load->helper('timesheets/timesheets');
        if (!timesheets_hr_can_view_all_staff()) {
            ajax_access_denied();
        }

        $staff_ids = $this->input->post('staffids');
        if (!is_array($staff_ids)) {
            $single = (int) $this->input->post('staff_id');
            $staff_ids = $single > 0 ? [$single] : [];
        }
        $staff_ids = array_values(array_filter(array_map('intval', $staff_ids)));

        $month = (int) $this->input->post('month');
        $year = (int) $this->input->post('year');
        $earned_days = $this->input->post('earned_days');

        if (empty($staff_ids) || $month < 1 || $month > 12 || $year < 2000 || $earned_days === null || $earned_days === '') {
            echo json_encode(['success' => false, 'message' => 'Staff, month, year and earned days are required.']);
            die;
        }

        $saved = $this->staff_model->bulk_save_earned_leave_overrides($staff_ids, $month, $year, $earned_days);

        echo json_encode([
            'success' => $saved > 0,
            'message' => $saved > 0
                ? ('Updated earned leave for ' . $saved . ' employee(s).')
                : 'Could not save earned leave.',
            'saved' => $saved,
        ]);
        die;
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
        if (!can_evaluate_pedma()) {
            access_denied('pedma');
        }

        $this->load->model('departments_model');

        $data['departments'] = $this->departments_model->get_staff_departments();
        $isManager = $this->db->query('SELECT manageleave from tblstaff_info WHERE staffid = ' . get_staff_user_id())->result_array();

        // Show department staff list (do not over-filter by team_manage,
        // otherwise Employee dropdown becomes empty after department select).
        $data['staffs'] = $this->staff_model->get_staff_based_on_department();

        $data['title'] = _l('timesheets');

        $this->load->view('admin/staff/pedma_admin', $data);
    }

    public function pedma_admin_old()
    {
        if (!can_evaluate_pedma()) {
            access_denied('pedma');
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
        if (!can_manage_pedma_kra()) {
            access_denied('pedma');
        }

        $data['kra'] = $this->staff_model->get_kra_based_on_department();

        $this->load->view('admin/staff/pedma_admin_kra', $data);
    }

    public function pedma_admin_kra_action($id = '')
    {
        if (!can_manage_pedma_kra()) {
            access_denied('pedma');
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
        if (!can_view_own_pedma()) {
            access_denied('pedma');
        }

        $this->staff_model->ensure_pedma_feedback_ack_columns();

        $id = get_staff_user_id(); 
        // $performance = $this->db->get_where('tblstaff_performance', ['staffid' => $id]);

        $performance = $this->db->from('tblstaff_performance')->where('staffid', $id)->where('status !=',0)->order_by('date_created')->get();

        $data['staffid'] = $id;
        $data['performance_values'] = $performance->result_array();
        
        $this->load->view('admin/staff/pedma', $data);
    }

    public function manage_pedma()
    {
        if (!can_manage_pedma()) {
            access_denied('pedma');
        }

		$staff_id = $this->staff_model->get_kra_based_on_department();

        $data['result'] = $this->departments_model->get_staff_departments();
        $this->load->view('admin/staff/manage_pedma', $data);
    }

    public function manage_pedma_staff()
	{
        if (!can_manage_pedma()) {
            access_denied('pedma');
        }

		$id = $this->input->post('data');
		//$id = get_staff_user_id(); 
		
        // $performance = $this->db->get_where('tblstaff_performance', ['staffid' => $id]);
		
        $performance = $this->db->from('tblstaff_performance')->where('staffid', $id)->where('status !=',0)->order_by('date_created')->get();

        // $data['staffid'] = $id;
        $data['performance_values'] = $performance->result_array();

        // Include staff join date so UI can compute "days of data" accurately
        // for employees who joined mid-period.
        $joinDate = null;
        $staffInfo = $this->db->select('doj')
            ->from('tblstaff_info')
            ->where('staffid', (int) $id)
            ->get()
            ->row_array();
        if (!empty($staffInfo['doj'])) {
            $joinDate = date('Y-m-d', strtotime($staffInfo['doj']));
        } else {
            $staffRow = $this->db->select('datecreated')
                ->from('tblstaff')
                ->where('staffid', (int) $id)
                ->get()
                ->row_array();
            if (!empty($staffRow['datecreated'])) {
                $joinDate = date('Y-m-d', strtotime($staffRow['datecreated']));
            }
        }

        if ($joinDate) {
            foreach ($data['performance_values'] as &$row) {
                $row['staff_join_date'] = $joinDate;
            }
            unset($row);
        }
		
		echo json_encode($data['performance_values']);
	}

    public function manage_pedma_by_grade()
    {
        header('Content-Type: application/json');

        $grade = trim((string) $this->input->post('grade'));
        $from = $this->input->post('from');
        $to = $this->input->post('to');
        $department = $this->input->post('department');
        $staffActive = (string) $this->input->post('staff_active');

        // Grade filter: Admin / Super Admin / HR only.
        // Department-only list (no grade): any Manage PEDMA user.
        if ($grade !== '') {
            if (!can_view_pedma_grade_filter()) {
                echo json_encode(['success' => false, 'message' => 'Access denied', 'rows' => []]);
                return;
            }
        } elseif (!can_manage_pedma()) {
            echo json_encode(['success' => false, 'message' => 'Access denied', 'rows' => []]);
            return;
        }

        if ($grade === '' && ($department === '' || $department === null)) {
            echo json_encode(['success' => false, 'message' => 'Select a department.', 'rows' => []]);
            return;
        }

        $rows = $this->staff_model->get_pedma_rows_by_grade($grade, $from, $to, $department, $staffActive);

        $avg = 0;
        if (count($rows)) {
            $sum = 0;
            foreach ($rows as $r) {
                $sum += (float) $r['score'];
            }
            $avg = round($sum / count($rows), 2);
        }

        echo json_encode([
            'success' => true,
            'grade' => strtoupper((string) $grade),
            'mode' => $grade !== '' ? 'grade' : 'department',
            'count' => count($rows),
            'avg_score' => $avg,
            'rows' => $rows,
        ]);
    }

    public function pedma_schedule_meeting()
    {
        header('Content-Type: application/json');

        if (!can_manage_pedma()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $staffid = (int) $this->input->post('staffid');
        $monthLabel = trim((string) $this->input->post('month_label'));
        $score = trim((string) $this->input->post('score'));
        $grade = trim((string) $this->input->post('grade'));

        if ($staffid <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid employee.']);
            return;
        }

        $ok = $this->staff_model->send_pedma_schedule_meeting_email($staffid, $monthLabel ?: 'selected period', $score, $grade);

        echo json_encode([
            'success' => (bool) $ok,
            'message' => $ok ? 'Meeting request email sent to the employee.' : 'Could not send meeting email.',
        ]);
    }

    public function pedma_old()
    {
        if (!can_view_own_pedma()) {
            access_denied('pedma');
        }

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
        if (!can_view_own_pedma()) {
            access_denied('pedma');
        }

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

    public function pedma_acknowledge_feedback()
    {
        if (!can_view_own_pedma()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        header('Content-Type: application/json');

        $staffid = (int) get_staff_user_id();
        $month   = trim((string) $this->input->post('month')); // YYYY-MM

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            echo json_encode(['success' => false, 'message' => 'Invalid month selected.']);
            return;
        }

        $this->staff_model->ensure_pedma_feedback_ack_columns();
        $result = $this->staff_model->acknowledge_pedma_feedback($staffid, $month);

        echo json_encode([
            'success' => (bool) $result['success'],
            'message' => $result['success']
                ? 'Thank you. You have accepted this feedback.'
                : 'Could not accept feedback. Please try again.',
            'accepted_at' => $result['accepted_at'],
            'status' => isset($result['status']) ? (int) $result['status'] : ($result['success'] ? 1 : 0),
        ]);
    }

    public function pedma_need_meeting()
    {
        if (!can_view_own_pedma()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        header('Content-Type: application/json');

        $staffid = (int) get_staff_user_id();
        $month   = trim((string) $this->input->post('month'));

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            echo json_encode(['success' => false, 'message' => 'Invalid month selected.']);
            return;
        }

        $this->staff_model->ensure_pedma_feedback_ack_columns();
        $result = $this->staff_model->pedma_need_meeting($staffid, $month);

        echo json_encode([
            'success' => (bool) $result['success'],
            'message' => $result['success']
                ? 'Your manager has been notified. A meeting request was sent.'
                : 'Could not send meeting request. Please try again.',
            'accepted_at' => $result['accepted_at'],
            'status' => isset($result['status']) ? (int) $result['status'] : ($result['success'] ? 2 : 0),
        ]);
    }

    public function pedma_pending_ack()
    {
        header('Content-Type: application/json');

        if (!is_staff_logged_in() || !can_view_own_pedma()) {
            echo json_encode(['success' => true, 'pending' => []]);
            return;
        }

        $this->staff_model->ensure_pedma_feedback_ack_columns();
        $rows = $this->staff_model->get_pending_pedma_ack(get_staff_user_id());
        $pending = [];

        foreach ($rows as $row) {
            $ascore = (float) $row['avg_score'];
            $fscore = !empty($row['fatal_error_score']) ? ((float) $row['fatal_error_score'] / 100) * $ascore : 0;
            $addscore = !empty($row['add_on_score']) ? ((float) $row['add_on_score'] / 100) * $ascore : 0;
            $nscore = number_format($ascore - $fscore + $addscore, 2);
            $time = strtotime($row['date_created']);
            $pending[] = [
                'id' => (int) $row['id'],
                'month_key' => date('Y-m', $time),
                'month_label' => date('F', $time),
                'year_label' => date('Y', $time),
                'score' => $nscore,
                'overall_feedback' => $row['overall_feedback'],
            ];
        }

        echo json_encode(['success' => true, 'pending' => $pending]);
    }

    /**
     * Manager PEDMA evaluation reminder payload (after 10th of month).
     */
    public function pedma_eval_pending_reminder()
    {
        header('Content-Type: application/json');

        if (!is_staff_logged_in() || !can_evaluate_pedma()) {
            echo json_encode(['success' => true, 'show' => false, 'pending' => []]);
            return;
        }

        if (function_exists('pedma_eval_reminders_are_active') && !pedma_eval_reminders_are_active()) {
            echo json_encode([
                'success' => true,
                'show'    => false,
                'pending' => [],
                'reason'  => 'starts_' . (function_exists('pedma_eval_reminders_start_date') ? pedma_eval_reminders_start_date() : '2026-09-01'),
            ]);
            return;
        }

        $day = (int) date('j');
        if ($day <= 10) {
            echo json_encode(['success' => true, 'show' => false, 'pending' => [], 'reason' => 'before_10th']);
            return;
        }

        $monthYm = $this->staff_model->get_pedma_eval_target_month();
        $pending = $this->staff_model->get_manager_pending_pedma_team(get_staff_user_id(), $monthYm);

        echo json_encode([
            'success'     => true,
            'show'        => count($pending) > 0,
            'month'       => $monthYm,
            'month_label' => date('F Y', strtotime($monthYm . '-01')),
            'pending'     => $pending,
        ]);
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
        if (!can_evaluate_pedma()) {
            access_denied('pedma');
        }

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

        // "previous" is a UI template choice — persist as the underlying default/custom type.
        if ($type == 'previous') {
            $type = $this->input->post('previous_kra_save_type');
            if ($type !== 'default' && $type !== 'custom') {
                $type = !empty($kraData2) ? 'custom' : 'default';
            }
        }

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
            'date_created' => $date_created . '-01',
            // Manager changed the report — employee must re-acknowledge
            'feedback_accepted' => 0,
            'feedback_accepted_at' => null,
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

    public function get_previous_month_custom_kra_data()
    {
        $search_date = $this->input->post('date');
        $staff_id = $this->input->post('staffid');

        if (empty($search_date) || empty($staff_id)) {
            echo json_encode(null);
            return;
        }

        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', $staff_id);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get()->row_array();

        echo json_encode(!empty($row) ? $row : null);
    }

    public function get_staff_department_json()
    {
        $departmentid = $this->input->post('department');
        $active = $this->input->post('staff_active');
        if ($active === null || $active === '') {
            $active = 1;
        }
        $staffs = $this->staff_model->get_staff_based_on_department($departmentid, $active);

        // Keep department employees visible. Prefer assigned team when available,
        // but fall back to full department list so dropdown is never empty.
        $assigned = $this->staff_model->filter_staff_for_pedma_evaluation($staffs);
        if (count($assigned) > 0) {
            $staffs = $assigned;
        }

        echo json_encode(array_values($staffs));
    }

    public function get_staff_performance_month_and_id()
    {
        $search_date = $this->input->post('month');
        $staff_id = $this->input->post('staffid');

        if (empty($search_date) || empty($staff_id)) {
            echo json_encode(null);
            return;
        }

        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', $staff_id);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $search_date);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get()->row_array();

        echo json_encode(!empty($row) ? $row : null);
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
