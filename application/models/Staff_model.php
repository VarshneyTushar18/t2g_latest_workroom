<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Staff_model extends App_Model

{

    public function delete($id, $transfer_data_to)

    {

        if (!is_numeric($transfer_data_to)) {

            return false;
        }



        if ($id == $transfer_data_to) {

            return false;
        }



        hooks()->do_action('before_delete_staff_member', [

            'id'               => $id,

            'transfer_data_to' => $transfer_data_to,

        ]);



        $name           = get_staff_full_name($id);

        $transferred_to = get_staff_full_name($transfer_data_to);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'estimates', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('sale_agent', $id);

        $this->db->update(db_prefix() . 'estimates', [

            'sale_agent' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'invoices', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('sale_agent', $id);

        $this->db->update(db_prefix() . 'invoices', [

            'sale_agent' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'expenses', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'notes', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('userid', $id);

        $this->db->update(db_prefix() . 'newsfeed_post_comments', [

            'userid' => $transfer_data_to,

        ]);



        $this->db->where('creator', $id);

        $this->db->update(db_prefix() . 'newsfeed_posts', [

            'creator' => $transfer_data_to,

        ]);



        $this->db->where('staff_id', $id);

        $this->db->update(db_prefix() . 'projectdiscussions', [

            'staff_id' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'projects', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'creditnotes', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('staff_id', $id);

        $this->db->update(db_prefix() . 'credits', [

            'staff_id' => $transfer_data_to,

        ]);



        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'project_files', [

            'staffid' => $transfer_data_to,

        ]);



        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'proposal_comments', [

            'staffid' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'proposals', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'templates', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'task_comments', [

            'staffid' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->where('is_added_from_contact', 0);

        $this->db->update(db_prefix() . 'tasks', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'files', [

            'staffid' => $transfer_data_to,

        ]);



        $this->db->where('renewed_by_staff_id', $id);

        $this->db->update(db_prefix() . 'contract_renewals', [

            'renewed_by_staff_id' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'task_checklist_items', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('assigned', $id);

        $this->db->update(db_prefix() . 'task_checklist_items', [

            'assigned' => $transfer_data_to,

        ]);



        $this->db->where('finished_from', $id);

        $this->db->update(db_prefix() . 'task_checklist_items', [

            'finished_from' => $transfer_data_to,

        ]);



        $this->db->where('admin', $id);

        $this->db->update(db_prefix() . 'ticket_replies', [

            'admin' => $transfer_data_to,

        ]);



        $this->db->where('admin', $id);

        $this->db->update(db_prefix() . 'tickets', [

            'admin' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'leads', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('assigned', $id);

        $this->db->update(db_prefix() . 'leads', [

            'assigned' => $transfer_data_to,

        ]);



        $this->db->where('staff_id', $id);

        $this->db->update(db_prefix() . 'taskstimers', [

            'staff_id' => $transfer_data_to,

        ]);



        $this->db->where('addedfrom', $id);

        $this->db->update(db_prefix() . 'contracts', [

            'addedfrom' => $transfer_data_to,

        ]);



        $this->db->where('assigned_from', $id);

        $this->db->where('is_assigned_from_contact', 0);

        $this->db->update(db_prefix() . 'task_assigned', [

            'assigned_from' => $transfer_data_to,

        ]);



        $this->db->where('responsible', $id);

        $this->db->update(db_prefix() . 'leads_email_integration', [

            'responsible' => $transfer_data_to,

        ]);



        $this->db->where('responsible', $id);

        $this->db->update(db_prefix() . 'web_to_lead', [

            'responsible' => $transfer_data_to,

        ]);



        $this->db->where('responsible', $id);

        $this->db->update(db_prefix() . 'estimate_request_forms', [

            'responsible' => $transfer_data_to,

        ]);



        $this->db->where('assigned', $id);

        $this->db->update(db_prefix() . 'estimate_requests', [

            'assigned' => $transfer_data_to,

        ]);



        $this->db->where('created_from', $id);

        $this->db->update(db_prefix() . 'subscriptions', [

            'created_from' => $transfer_data_to,

        ]);



        $this->db->where('notify_type', 'specific_staff');

        $web_to_lead = $this->db->get(db_prefix() . 'web_to_lead')->result_array();



        foreach ($web_to_lead as $form) {

            if (!empty($form['notify_ids'])) {

                $staff = unserialize($form['notify_ids']);

                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {

                    unset($staff[$key]);

                    $staff = serialize(array_values($staff));

                    $this->db->where('id', $form['id']);

                    $this->db->update(db_prefix() . 'web_to_lead', [

                        'notify_ids' => $staff,

                    ]);
                }
            }
        }



        $this->db->where('notify_type', 'specific_staff');

        $estimate_requests = $this->db->get(db_prefix() . 'estimate_request_forms')->result_array();



        foreach ($estimate_requests as $form) {

            if (!empty($form['notify_ids'])) {

                $staff = unserialize($form['notify_ids']);

                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {

                    unset($staff[$key]);

                    $staff = serialize(array_values($staff));

                    $this->db->where('id', $form['id']);

                    $this->db->update(db_prefix() . 'estimate_request_forms', [

                        'notify_ids' => $staff,

                    ]);
                }
            }
        }





        $this->db->where('id', 1);

        $leads_email_integration = $this->db->get(db_prefix() . 'leads_email_integration')->row();



        if ($leads_email_integration->notify_type == 'specific_staff') {

            if (!empty($leads_email_integration->notify_ids)) {

                $staff = unserialize($leads_email_integration->notify_ids);

                if (is_array($staff) && in_array($id, $staff) && ($key = array_search($id, $staff)) !== false) {

                    unset($staff[$key]);

                    $staff = serialize(array_values($staff));

                    $this->db->where('id', 1);

                    $this->db->update(db_prefix() . 'leads_email_integration', [

                        'notify_ids' => $staff,

                    ]);
                }
            }
        }



        $this->db->where('assigned', $id);

        $this->db->update(db_prefix() . 'tickets', [

            'assigned' => 0,

        ]);



        $this->db->where('staff', 1);

        $this->db->where('userid', $id);

        $this->db->delete(db_prefix() . 'dismissed_announcements');



        $this->db->where('userid', $id);

        $this->db->delete(db_prefix() . 'newsfeed_comment_likes');



        $this->db->where('userid', $id);

        $this->db->delete(db_prefix() . 'newsfeed_post_likes');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'customer_admins');



        $this->db->where('fieldto', 'staff');

        $this->db->where('relid', $id);

        $this->db->delete(db_prefix() . 'customfieldsvalues');



        $this->db->where('userid', $id);

        $this->db->delete(db_prefix() . 'events');



        $this->db->where('touserid', $id);

        $this->db->delete(db_prefix() . 'notifications');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'user_meta');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'project_members');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'project_notes');



        $this->db->where('creator', $id);

        $this->db->or_where('staff', $id);

        $this->db->delete(db_prefix() . 'reminders');



        $this->db->where('staffid', $id);

        $this->db->delete(db_prefix() . 'staff_departments');



        $this->db->where('staffid', $id);

        $this->db->delete(db_prefix() . 'todos');



        $this->db->where('staff', 1);

        $this->db->where('user_id', $id);

        $this->db->delete(db_prefix() . 'user_auto_login');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'staff_permissions');



        $this->db->where('staffid', $id);

        $this->db->delete(db_prefix() . 'task_assigned');



        $this->db->where('staffid', $id);

        $this->db->delete(db_prefix() . 'task_followers');



        $this->db->where('staff_id', $id);

        $this->db->delete(db_prefix() . 'pinned_projects');



        $this->db->where('staffid', $id);

        $this->db->delete(db_prefix() . 'staff');

        log_activity('Staff Member Deleted [Name: ' . $name . ', Data Transferred To: ' . $transferred_to . ']');



        hooks()->do_action('staff_member_deleted', [

            'id'               => $id,

            'transfer_data_to' => $transfer_data_to,

        ]);



        return true;
    }



    /**

     * Get staff member/s

     * @param  mixed $id Optional - staff id

     * @param  mixed $where where in query

     * @return mixed if id is passed return object else array

     */

    public function get($id = '', $where = [])

    {

        $select_str = '*,CONCAT(firstname,\' \',lastname) as full_name';



        // Used to prevent multiple queries on logged in staff to check the total unread notifications in core/AdminController.php

        if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {

            $select_str .= ',(SELECT COUNT(*) FROM ' . db_prefix() . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
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

        $this->db->order_by('firstname', 'asc');



        return $this->db->get(db_prefix() . 'staff')->result_array();
    }


    // custom function to get staff with their department id
    public function get_staff_based_on_department($id = '')
    {
        $this->load->model('departments_model');
        if ($id) {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1 and tblstaff_departments.departmentid = ' . $id;
        } else {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;';
        }
        $all_staff_data = $this->db->query($query)->result();
        $departments = $this->departments_model->get_staff_departments();

        $staff_with_same_department = [];
        $start = false;

        /* foreach will loop through all active staff data then first for loop will check whether 
        that staff is present in the array. if it is present then variable will be true 
        in second loop we loop through all the departments that staff is part of . if staff is part of 
        department that logged in staff is part of then it will be inserted in the array . Also it will be
        inserted only if it is false . This is to prevent inserting of the duplicate staff */

        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array)$staff_data;
                }
            }
            $start = false;
        }

        return $staff_with_same_department;
    }

    // this will change column name of staffid to staff_id
    public function _get_staff_based_on_department()
    {
        $this->load->model('departments_model');
        $all_staff_data = $this->db->query('select *,tblstaff.staffid as staff_id from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;')->result();
        $departments = $this->departments_model->get_staff_departments();
        $staff_with_same_department = [];
        $start = false;
        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array)$staff_data;
                }
            }
        }

        return $staff_with_same_department;
    }
    /**

     * Get staff permissions

     * @param  mixed $id staff id

     * @return array

     */

    public function get_staff_permissions($id)

    {

        // Fix for version 2.3.1 tables upgrade

        if (defined('DOING_DATABASE_UPGRADE')) {

            return [];
        }



        $permissions = $this->app_object_cache->get('staff-' . $id . '-permissions');



        if (!$permissions && !is_array($permissions)) {

            $this->db->where('staff_id', $id);

            $permissions = $this->db->get('staff_permissions')->result_array();



            $this->app_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }



        return $permissions;
    }



    /**

     * Add new staff member

     * @param array $data staff $_POST data

     */

    public function add($data)

    {

        if (isset($data['fakeusernameremembered'])) {

            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {

            unset($data['fakepasswordremembered']);
        }



        // First check for all cases if the email exists.

        $data = hooks()->apply_filters('before_create_staff_member', $data);



        $this->db->where('email', $data['email']);

        $email = $this->db->get(db_prefix() . 'staff')->row();



        if ($email) {

            die('Email already exists');
        }



        $data['admin'] = 0;



        if (is_admin()) {

            if (isset($data['administrator'])) {

                $data['admin'] = 1;

                unset($data['administrator']);
            }
        }



        $send_welcome_email = true;

        $original_password  = $data['password'];

        if (!isset($data['send_welcome_email'])) {

            $send_welcome_email = false;
        } else {

            unset($data['send_welcome_email']);
        }



        $data['password']    = app_hash_password($data['password']);

        $data['datecreated'] = date('Y-m-d H:i:s');

        if (isset($data['departments'])) {

            $departments = $data['departments'];

            unset($data['departments']);
        }



        $permissions = [];

        if (isset($data['permissions'])) {

            $permissions = $data['permissions'];

            unset($data['permissions']);
        }



        if (isset($data['custom_fields'])) {

            $custom_fields = $data['custom_fields'];

            unset($data['custom_fields']);
        }



        if ($data['admin'] == 1) {

            $data['is_not_staff'] = 0;
        }



        $this->db->insert(db_prefix() . 'staff', $data);

        $staffid = $this->db->insert_id();

        if ($staffid) {

            $slug = $data['firstname'] . ' ' . $data['lastname'];



            if ($slug == ' ') {

                $slug = 'unknown-' . $staffid;
            }



            if ($send_welcome_email == true) {

                send_mail_template('staff_created', $data['email'], $staffid, $original_password);
            }



            $this->db->where('staffid', $staffid);

            $this->db->update(db_prefix() . 'staff', [

                'media_path_slug' => slug_it($slug),

            ]);



            if (isset($custom_fields)) {

                handle_custom_fields_post($staffid, $custom_fields);
            }

            if (isset($departments)) {

                foreach ($departments as $department) {

                    $this->db->insert(db_prefix() . 'staff_departments', [

                        'staffid'      => $staffid,

                        'departmentid' => $department,

                    ]);
                }
            }



            // Delete all staff permission if is admin we dont need permissions stored in database (in case admin check some permissions)

            $this->update_permissions($data['admin'] == 1 ? [] : $permissions, $staffid);



            log_activity('New Staff Member Added [ID: ' . $staffid . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');



            // Get all announcements and set it to read.

            $this->db->select('announcementid');

            $this->db->from(db_prefix() . 'announcements');

            $this->db->where('showtostaff', 1);

            $announcements = $this->db->get()->result_array();

            foreach ($announcements as $announcement) {

                $this->db->insert(db_prefix() . 'dismissed_announcements', [

                    'announcementid' => $announcement['announcementid'],

                    'staff'          => 1,

                    'userid'         => $staffid,

                ]);
            }

            hooks()->do_action('staff_member_created', $staffid);



            return $staffid;
        }



        return false;
    }



    /**

     * Update staff member info

     * @param  array $data staff data

     * @param  mixed $id   staff id

     * @return boolean

     */

    public function update($data, $id)

    {

        if (isset($data['fakeusernameremembered'])) {

            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {

            unset($data['fakepasswordremembered']);
        }



        $data = hooks()->apply_filters('before_update_staff_member', $data, $id);



        if (is_admin()) {

            if (isset($data['administrator'])) {

                $data['admin'] = 1;

                unset($data['administrator']);
            } else {

                if ($id != get_staff_user_id()) {

                    if ($id == 1) {

                        return [

                            'cant_remove_main_admin' => true,

                        ];
                    }
                } else {

                    return [

                        'cant_remove_yourself_from_admin' => true,

                    ];
                }

                $data['admin'] = 0;
            }
        }



        $affectedRows = 0;

        if (isset($data['departments'])) {

            $departments = $data['departments'];

            unset($data['departments']);
        }



        $permissions = [];

        if (isset($data['permissions'])) {

            $permissions = $data['permissions'];

            unset($data['permissions']);
        }



        if (isset($data['custom_fields'])) {

            $custom_fields = $data['custom_fields'];

            if (handle_custom_fields_post($id, $custom_fields)) {

                $affectedRows++;
            }

            unset($data['custom_fields']);
        }

        if (empty($data['password'])) {

            unset($data['password']);
        } else {

            $data['password']             = app_hash_password($data['password']);

            $data['last_password_change'] = date('Y-m-d H:i:s');
        }





        // if (isset($data['two_factor_auth_enabled'])) {

        //     $data['two_factor_auth_enabled'] = 1;

        // } else {

        //     $data['two_factor_auth_enabled'] = 0;

        // }



        if (isset($data['is_not_staff'])) {

            $data['is_not_staff'] = 1;
        } else {

            $data['is_not_staff'] = 0;
        }



        if (isset($data['admin']) && $data['admin'] == 1) {

            $data['is_not_staff'] = 0;
        }



        $this->load->model('departments_model');

        $staff_departments = $this->departments_model->get_staff_departments($id);

        if (sizeof($staff_departments) > 0) {

            if (!isset($data['departments'])) {

                $this->db->where('staffid', $id);

                $this->db->delete(db_prefix() . 'staff_departments');
            } else {

                foreach ($staff_departments as $staff_department) {

                    if (isset($departments)) {

                        if (!in_array($staff_department['departmentid'], $departments)) {

                            $this->db->where('staffid', $id);

                            $this->db->where('departmentid', $staff_department['departmentid']);

                            $this->db->delete(db_prefix() . 'staff_departments');

                            if ($this->db->affected_rows() > 0) {

                                $affectedRows++;
                            }
                        }
                    }
                }
            }

            if (isset($departments)) {

                foreach ($departments as $department) {

                    $this->db->where('staffid', $id);

                    $this->db->where('departmentid', $department);

                    $_exists = $this->db->get(db_prefix() . 'staff_departments')->row();

                    if (!$_exists) {

                        $this->db->insert(db_prefix() . 'staff_departments', [

                            'staffid'      => $id,

                            'departmentid' => $department,

                        ]);

                        if ($this->db->affected_rows() > 0) {

                            $affectedRows++;
                        }
                    }
                }
            }
        } else {

            if (isset($departments)) {

                foreach ($departments as $department) {

                    $this->db->insert(db_prefix() . 'staff_departments', [

                        'staffid'      => $id,

                        'departmentid' => $department,

                    ]);

                    if ($this->db->affected_rows() > 0) {

                        $affectedRows++;
                    }
                }
            }
        }





        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'staff', $data);



        if ($this->db->affected_rows() > 0) {

            $affectedRows++;
        }



        if ($this->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $permissions), $id)) {

            $affectedRows++;
        }



        if ($affectedRows > 0) {

            hooks()->do_action('staff_member_updated', $id);

            log_activity('Staff Member Updated [ID: ' . $id . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');



            return true;
        }



        return false;
    }



    public function update_permissions($permissions, $id)

    {

        $this->db->where('staff_id', $id);

        $this->db->delete('staff_permissions');



        $is_staff_member = is_staff_member($id);



        foreach ($permissions as $feature => $capabilities) {

            foreach ($capabilities as $capability) {



                // Maybe do this via hook.

                if ($feature == 'leads' && !$is_staff_member) {

                    continue;
                }



                $this->db->insert('staff_permissions', ['staff_id' => $id, 'feature' => $feature, 'capability' => $capability]);
            }
        }



        return true;
    }



    public function update_profile($data, $id)

    {

        $data = hooks()->apply_filters('before_staff_update_profile', $data, $id);



        if (empty($data['password'])) {

            unset($data['password']);
        } else {

            $data['password']             = app_hash_password($data['password']);

            $data['last_password_change'] = date('Y-m-d H:i:s');
        }



        if (isset($data['two_factor_auth_enabled'])) {

            $data['two_factor_auth_enabled'] = 1;
        } else {

            $data['two_factor_auth_enabled'] = 0;
        }





        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'staff', $data);

        if ($this->db->affected_rows() > 0) {

            hooks()->do_action('staff_member_profile_updated', $id);

            log_activity('Staff Profile Updated [Staff: ' . get_staff_full_name($id) . ']');



            return true;
        }



        return false;
    }



    /**

     * Change staff passwordn

     * @param  mixed $data   password data

     * @param  mixed $userid staff id

     * @return mixed

     */

    public function change_password($data, $userid)

    {

        $data = hooks()->apply_filters('before_staff_change_password', $data, $userid);



        $member = $this->get($userid);

        // CHeck if member is active

        if ($member->active == 0) {

            return [

                [

                    'memberinactive' => true,

                ],

            ];
        }



        // Check new old password

        if (!app_hasher()->CheckPassword($data['oldpassword'], $member->password)) {

            return [

                [

                    'passwordnotmatch' => true,

                ],

            ];
        }



        $data['newpasswordr'] = app_hash_password($data['newpasswordr']);



        $this->db->where('staffid', $userid);

        $this->db->update(db_prefix() . 'staff', [

            'password'             => $data['newpasswordr'],

            'last_password_change' => date('Y-m-d H:i:s'),

        ]);

        if ($this->db->affected_rows() > 0) {

            log_activity('Staff Password Changed [' . $userid . ']');



            return true;
        }



        return false;
    }



    /**

     * Change staff status / active / inactive

     * @param  mixed $id     staff id

     * @param  mixed $status status(0/1)

     */

    public function change_staff_status($id, $status)

    {

        $status = hooks()->apply_filters('before_staff_status_change', $status, $id);



        $this->db->where('staffid', $id);

        $this->db->update(db_prefix() . 'staff', [

            'active' => $status,

        ]);



        log_activity('Staff Status Changed [StaffID: ' . $id . ' - Status(Active/Inactive): ' . $status . ']');
    }



    public function get_logged_time_data($id = '', $filter_data = [])

    {

        if ($id == '') {

            $id = get_staff_user_id();
        }

        $result['timesheets'] = [];

        $result['total']      = [];

        $result['this_month'] = [];



        $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day

        $last_day_this_month  = date('Y-m-t 23:59:59');



        $result['last_month'] = [];

        $first_day_last_month = date('Y-m-01', strtotime('-1 MONTH')); // hard-coded '01' for first day

        $last_day_last_month  = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));



        $result['this_week'] = [];

        $first_day_this_week = date('Y-m-d', strtotime('monday this week'));

        $last_day_this_week  = date('Y-m-d 23:59:59', strtotime('sunday this week'));



        $result['last_week'] = [];



        $first_day_last_week = date('Y-m-d', strtotime('monday last week'));

        $last_day_last_week  = date('Y-m-d 23:59:59', strtotime('sunday last week'));



        $this->db->select('task_id,start_time,end_time,staff_id,' . db_prefix() . 'taskstimers.hourly_rate,name,' . db_prefix() . 'taskstimers.id,rel_id,rel_type, billed');

        $this->db->where('staff_id', $id);

        $this->db->join(db_prefix() . 'tasks', db_prefix() . 'tasks.id = ' . db_prefix() . 'taskstimers.task_id', 'left');

        $timers           = $this->db->get(db_prefix() . 'taskstimers')->result_array();

        $_end_time_static = time();



        $filter_period = false;

        if (isset($filter_data['period-from']) && $filter_data['period-from'] != '' && isset($filter_data['period-to']) && $filter_data['period-to'] != '') {

            $filter_period = true;

            $from          = to_sql_date($filter_data['period-from']);

            $from          = date('Y-m-d', strtotime($from));

            $to            = to_sql_date($filter_data['period-to']);

            $to            = date('Y-m-d', strtotime($to));
        }



        foreach ($timers as $timer) {

            $start_date = date('Y-m-d', $timer['start_time']);



            $end_time    = $timer['end_time'];

            $notFinished = false;

            if ($timer['end_time'] == null) {

                $end_time    = $_end_time_static;

                $notFinished = true;
            }



            $total = $end_time - $timer['start_time'];



            $result['total'][]     = $total;

            $timer['total']        = $total;

            $timer['end_time']     = $end_time;

            $timer['not_finished'] = $notFinished;



            if ($start_date >= $first_day_this_month && $start_date <= $last_day_this_month) {

                $result['this_month'][] = $total;

                if (isset($filter_data['this_month']) && $filter_data['this_month'] != '') {

                    $result['timesheets'][$timer['id']] = $timer;
                }
            }

            if ($start_date >= $first_day_last_month && $start_date <= $last_day_last_month) {

                $result['last_month'][] = $total;

                if (isset($filter_data['last_month']) && $filter_data['last_month'] != '') {

                    $result['timesheets'][$timer['id']] = $timer;
                }
            }

            if ($start_date >= $first_day_this_week && $start_date <= $last_day_this_week) {

                $result['this_week'][] = $total;

                if (isset($filter_data['this_week']) && $filter_data['this_week'] != '') {

                    $result['timesheets'][$timer['id']] = $timer;
                }
            }

            if ($start_date >= $first_day_last_week && $start_date <= $last_day_last_week) {

                $result['last_week'][] = $total;

                if (isset($filter_data['last_week']) && $filter_data['last_week'] != '') {

                    $result['timesheets'][$timer['id']] = $timer;
                }
            }



            if ($filter_period == true) {

                if ($start_date >= $from && $start_date <= $to) {

                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
        }

        $result['total']      = array_sum($result['total']);

        $result['this_month'] = array_sum($result['this_month']);

        $result['last_month'] = array_sum($result['last_month']);

        $result['this_week']  = array_sum($result['this_week']);

        $result['last_week']  = array_sum($result['last_week']);



        return $result;
    }





    /* Adding new staff function from timesheet module */

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





        /* commented this caused the so look staff that had particular role set  */

        // if ($timekeeping_applicable_object && $timekeeping_applicable_object != '' && $timekeeping_applicable_object != null) {

        // 	$where .= 'find_in_set(role, "' . $timekeeping_applicable_object . '")';

        // }



        /* commented because this was causing non tl,admin,manager to only view themself in timesheet */

        // if($for_cronjob == false){

        // 	if ($where != '') {

        // 		$where .= timesheet_staff_manager_query('attendance_management');

        // 	} else {

        // 		$where .= timesheet_staff_manager_query('attendance_management', 'staffid', '');

        // 	}

        // }

        if ($where != '') {

            $where .= ' and active = 1';
        } else {

            $where .= ' active = 1';
        }

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {

            $this->db->where($where);

            $this->db->order_by('firstname', 'ASC');
        }



        // die($where);

        $result = $this->db->get(db_prefix() . 'staff')->result_array();

        return $result;
    }

    public function get_tbl_info_data($id)
    {
        // Fetch data from the database based on your logic
        // For example:
        $get_info_data = $this->db->query("SELECT * from tblstaff_info WHERE staffid = $id");

        return $get_info_data->result_array();
    }

    public function add_details($data)
    {



        // print_r($data); die;

        // $data['leave_earned'] = $this->calculateEarnedLeaves($data['doj']);


        $this->db->insert(db_prefix() . 'staff_info', $data);



        $staffid = $this->db->insert_id();



        return $staffid;
    }

    public function update_details($data)
    {



        // print_r($data); die;

        // $data['leave_earned'] = $this->calculateEarnedLeaves($data['doj']);

        $this->db->where('staffid', $data['staffid']);
        $this->db->update(db_prefix() . 'staff_info', $data);

        return $this->db->affected_rows();
    }

    function calculateEarnedLeaves($doj)
    {
        $currentDate = Date('Y-m-d');
        $tenureInYears = strtotime($currentDate) - strtotime($doj);
        $tenureInYears = floor($tenureInYears / (365 * 24 * 60 * 60));

        if ($tenureInYears > 2) {
            $leaveRate = 1.75; // Less than 2 years
        } else {
            $leaveRate = 1.25; // 2 years or more
        }
        return $leaveRate;
    }
    /*function carryForward($staffid, $doj, $month, $year)
    {
        // $prevMonth = ($month == 1) ? 12 : $month - 1;
        // $prevMonthFormatted = str_pad($prevMonth, 2, '0', STR_PAD_LEFT);

        // $prevYear = ($month == 1) ? $year - 1 : $year;
        // $prevDate = "$prevYear-$prevMonthFormatted-31 00:00:00";
        // $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        // $leaveRate = $this->calculateEarnedLeaves($doj);
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day 00:00:00";

        $totalLeavesPreviousMonth = ($this->noOfMonthsFromTo($doj, $lastDayOfTheMonth)-1) * $leaveRate;
	
        $firstDayOfTheMonth = "$year-$monthFormatted-01 00:00:00";
        // echo $firstDayOfTheMonth ; die;
        // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND MONTH(start_time) < $month ";

        $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time < '$firstDayOfTheMonth' ";


        // echo $sql; die;
        $query = $this->db->query($sql);
        $sum_of_leaves = $query->row()->sum_of_leaves;
        return $totalLeavesPreviousMonth - $sum_of_leaves;
    }*/
	
	 function carryForward($staffid, $doj, $month, $year)
    {
        // $prevMonth = ($month == 1) ? 12 : $month - 1;
        // $prevMonthFormatted = str_pad($prevMonth, 2, '0', STR_PAD_LEFT);

        // $prevYear = ($month == 1) ? $year - 1 : $year;
        // $prevDate = "$prevYear-$prevMonthFormatted-31 00:00:00";
        // $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        // $leaveRate = $this->calculateEarnedLeaves($doj);
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day 00:00:00";

       // $totalLeavesPreviousMonth = ($this->noOfMonthsFromTo($doj, $lastDayOfTheMonth)-1) * $leaveRate;
	
        $firstDayOfTheMonth = "$year-$monthFormatted-01 00:00:00";
		
        // echo $firstDayOfTheMonth ; die;
        // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND MONTH(start_time) < $month ";
		
        $sql = "SELECT carry_forward FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time <= '$firstDayOfTheMonth' ORDER by id DESC LIMIT 1";
		
        // echo $sql; die;
        $query = $this->db->query($sql);
        $sum_of_leaves = $query->row();
		//print_r($sum_of_leaves);
        return $sum_of_leaves->carry_forward;
    }
	

  /*  function monthlyLeaveBalance($staffid, $doj, $month, $year)
    {
		
		
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day 00:00:00";

      

        $totalLeavesEarned = $this->noOfMonthsFromTo($doj, $lastDayOfTheMonth) * $leaveRate;

        // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND MONTH(start_time) <= $month;";
        $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND start_time <= '$lastDayOfTheMonth'";
        
        $query = $this->db->query($sql);
        $sum_of_leaves = $query->row()->sum_of_leaves;
        
        // return $totalLeavesEarned ;
        // return " staff id - ". $staffid . " , total leaves earned -" . $totalLeavesEarned  ; 
        
        // echo $sum_of_leaves; die;
        // echo  $totalLeavesEarned . " - " . $sum_of_leaves; die;
        return $totalLeavesEarned - $sum_of_leaves;
    }*/
	public function sumArray($array) {
		$total = 0;
		foreach ($array as $value) {
			$total += $value;
		}
		return $array;
	}
	function monthlyLeave($staffid, $month, $year)
	{
		$monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        //$leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";
		//echo "SELECT count(id) as count_status FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=2 AND status=0";die;
		 $sql = "SELECT number_of_leaving_day FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND end_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=1";
        
        $query = $this->db->query($sql);
	
        $sum_of_leave = $query->result_array();
		//echo count($sum_of_leave);
		for($i=0;$i<count($sum_of_leave);$i++){
	//	print_r($sum_of_leave[$i]['number_of_leaving_day']);
			$coutn_taken_leave += $sum_of_leave[$i]['number_of_leaving_day'];
			//echo $coutn_taken_leave;
		}
		
		/*foreach($sum_of_leave as $leave_taken){
			echo $leave_taken['number_of_leaving_day']+$leave_taken['number_of_leaving_day'];
			
		}*/
			//print_r($sum_of_leave);
		//print_r($sum_of_leaves);die;
        // return $totalLeavesEarned ;
        // return " staff id - ". $staffid . " , total leaves earned -" . $totalLeavesEarned  ; 
        
        // echo $sum_of_leaves; die;
        // echo  $totalLeavesEarned . " - " . $sum_of_leaves; die;
        return $coutn_taken_leave;
		
	}
	function status_approve($staffid, $month, $year)
	{
		 $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        //$leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";
		//echo "SELECT count(id) as count_status FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=2 AND status=0";die;
		 $sql = "SELECT count(status) as count_status FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status IN(2,0)";
        
        $query = $this->db->query($sql);
        $sum_of_status = $query->row();
		//print_r($sum_of_leaves);die;
        // return $totalLeavesEarned ;
        // return " staff id - ". $staffid . " , total leaves earned -" . $totalLeavesEarned  ; 
        
        // echo $sum_of_leaves; die;
        // echo  $totalLeavesEarned . " - " . $sum_of_leaves; die;
        return $sum_of_status->count_status;
		
	}
	function monthlystatus($staffid, $month, $year)
    {
		
		
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        //$leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";

        //$totalLeavesEarned = $this->noOfMonthsFromTo($doj, $lastDayOfTheMonth) * $leaveRate;

        // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND MONTH(start_time) <= $month;";
       // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND start_time <= '$lastDayOfTheMonth'";
	 //echo "SELECT count(status) as sum FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  date(start_time) BETWEEN $firstDayOfTheMonth AND $lastDayOfTheMonth AND status=4";die;
	   $sql = "SELECT count(status) as sum FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=4";
        
        $query = $this->db->query($sql);
        $sum_of_leaves = $query->row();
		//print_r($sum_of_leaves);die;
        // return $totalLeavesEarned ;
        // return " staff id - ". $staffid . " , total leaves earned -" . $totalLeavesEarned  ; 
        
        // echo $sum_of_leaves; die;
        // echo  $totalLeavesEarned . " - " . $sum_of_leaves; die;
        return $sum_of_leaves->sum;
		
	}
	function monthlyLeaveBalance($staffid, $doj, $month, $year)
    {
		
		
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $leaveRate = $this->calculateEarnedLeaves($doj);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day 00:00:00";
      

        //$totalLeavesEarned = $this->noOfMonthsFromTo($doj, $lastDayOfTheMonth) * $leaveRate;

        // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND MONTH(start_time) <= $month;";
       // $sql = "SELECT SUM(number_of_leaving_day) as sum_of_leaves FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND start_time <= '$lastDayOfTheMonth'";
	   $sql = "SELECT number_of_days,number_of_leaving_day,carry_forward,leave_balance FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time <= '$lastDayOfTheMonth' ORDER by id DESC LIMIT 1";
        
        $query = $this->db->query($sql);
        $sum_of_leaves = $query->row();
		//print_r($sum_of_leaves);die;
        
        // return $totalLeavesEarned ;
        // return " staff id - ". $staffid . " , total leaves earned -" . $totalLeavesEarned  ; 
        
        // echo $sum_of_leaves; die;
        // echo  $totalLeavesEarned . " - " . $sum_of_leaves; die;
        return $sum_of_leaves->leave_balance;
		
	}

    function noOfMonthsFromTo($doj, $date)
    {
        // $doj = str_replace('/', '-', $doj);
        $doj = date("Y-m-d", strtotime($doj));
        $currentDate = new DateTime($date);
        $doj = new DateTime($doj);

        $interval = $doj->diff($currentDate);
        $totalMonths = $interval->y * 12 + $interval->m;
        return $totalMonths+1;
    }

    public function get_department_by_staffid_staff_model($id_staff)
    {
        $this->db->where('staffid', $id_staff);
        $departments = $this->db->get(db_prefix() . 'staff_departments')->result_array();
        $w = '0';
        if (isset($departments[0]['departmentid'])) {
            $w = $departments[0]['departmentid'];
        }
        return $this->db->query('select * from ' . db_prefix() . 'departments where departmentid = ' . $w)->row();
    }


   public function insert_into_tblstaff_performance($data)
    {
        $this->db->insert('tblstaff_performance', $data);
        return $this->db->insert_id();
    }

    public function insert_into_tblstaff_performance_kra($data)
    {
        $this->db->insert('tblstaff_performance_kra', $data);
        return $this->db->insert_id();
    }

    public function update_tblstaff_performance_kra($data, $kraid)
    {
        $this->db->set($data);
        $this->db->where('id', $kraid);
        $this->db->update('tblstaff_performance_kra');
        
        // Delete KPIs related to KRA
        $this->db->where('kra_id', $kraid);
        $this->db->delete('tblstaff_performance_kpi');

        return $kraid;
    }

    public function insert_into_tblstaff_performance_kpi($data)
    {
        $this->db->insert('tblstaff_performance_kpi', $data);
        return $this->db->insert_id();
    }

    public function send_performance_email($staffid, $data)
    {

        $time = strtotime($data['date_created']);
        $year = date('Y', $time);
        $month = date('F', $time);

        $user_email = get_staff_email_id($data['staffid']);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $this->email->set_mailtype("html");
        $this->email->from('noreply@tech2globe.com', 'Tech2globe');
        $this->email->to($user_email);
        $this->email->cc(array('sarabjeet@tech2globe.net',$manager_email));
        $subject = ' Performance Feedback - ' . $month . ' ' . $year . ' -' . get_staff_full_name($data['staffid']);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Performance review</title>
        </head>
        <body>
            <p> Dear ' . get_staff_full_name($data['staffid']) . ',</p>
            <p>Your monthly review has been submitted by your manager. Below are the details:</p></br></br>
            <p><b>Employee Name : </b>' . get_staff_full_name($data['staffid']) . ' </p>
            <p><b>Employee ID:  </b>' . get_staff_emp_id($data['staffid']) . ' </p>
            <p><b>Average Score : </b>' . $data['avg_score'] . '%</p></br>
            <p><b>Overall Feedback:  </b>' . $data['overall_feedback'] . ' </p>
            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/staff/pedma </p></br>

            <p><em>Kind Regards,<br>
            Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_performance_email_reply($staffid, $comment, $month, $year, $score)
    {

        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $this->email->set_mailtype("html");
        $this->email->from($user_email, get_staff_full_name($staffid));
        $this->email->to(array('sarabjeet@tech2globe.net',$manager_email));
        $this->email->cc(array('hr@tech2globe.com'));
        $subject = ' Performance Feedback Reply - ' . $month . ' ' . $year . ' -' . get_staff_full_name($staffid);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Performance review</title>
        </head>
        <body>
            <p> Hello Sir,</p>
            <p>My monthly review has been submitted by our manager. Below are the details:</p></br></br>
            <p><b>Employee Name : </b>' . get_staff_full_name($staffid) . ' </p>
            <p><b>Employee ID:  </b>' . get_staff_emp_id($staffid) . ' </p>
            <p><b>Average Score : </b>' . $score . '</p></br>

            <p><b>Comment : </b>' . $comment . '</p></br>
            

            <p><em>Kind Regards,<br>
            '. get_staff_full_name($staffid) .'</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_fatal_error_performance_email($staffid, $score, $months, $comment)
    {

        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $this->email->set_mailtype("html");
        $this->email->from('noreply@tech2globe.com', 'Tech2globe');
        $this->email->to($user_email);
        $this->email->cc(array('sarabjeet@tech2globe.net',$manager_email));
        $subject = ' Performance Score Adjustment - ' . get_staff_full_name($staffid);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Performance score adjustment</title>
        </head>
        <body>
            <p> Dear ' . get_staff_full_name($staffid) . ',</p>
            <p>Your monthly review has been adjusted by your manager. Below are the details:</p></br></br>
            <p><b>Employee Name : </b>' . get_staff_full_name($staffid) . ' </p>
            <p><b>Employee ID:  </b>' . get_staff_emp_id($staffid) . ' </p>
            <p><b>Reduction Score Percentage : </b>' . $score . '%</p></br>
            <p><b>Effected Months :  </b>' . implode(', ',$months) . ' </p>
            <p><b>Reason : </b>' . $comment . '</p></br>
            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/staff/pedma </p></br>

            <p><em>Kind Regards,<br>
            Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_add_on_performance_email($staffid, $score, $months, $comment)
    {

        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $this->email->set_mailtype("html");
        $this->email->from('noreply@tech2globe.com', 'Tech2globe');
        $this->email->to($user_email);
        $this->email->cc(array('sarabjeet@tech2globe.net',$manager_email));
        $subject = ' Performance Score Adjustment - ' . get_staff_full_name($staffid);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Performance score adjustment</title>
        </head>
        <body>
            <p> Dear ' . get_staff_full_name($staffid) . ',</p>
            <p>Your monthly review has been adjusted by your manager. Below are the details:</p></br></br>
            <p><b>Employee Name : </b>' . get_staff_full_name($staffid) . ' </p>
            <p><b>Employee ID:  </b>' . get_staff_emp_id($staffid) . ' </p>
            <p><b>Add On Score Percentage : </b>' . $score . '%</p></br>
            <p><b>Effected Months :  </b>' . implode(', ',$months) . ' </p>
            <p><b>Reason : </b>' . $comment . '</p></br>
            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/staff/pedma </p></br>

            <p><em>Kind Regards,<br>
            Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function check_if_review_added($staffid, $date)
    {
        $this->db->select('*');
        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', $staffid);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $date);
        $staff_performance_data = $this->db->get()->result_array();
        return $staff_performance_data;
    }
    public function update_tblstaff_performance($staffid, $date_created, $data)
    {
        $this->db->set($data);
        $this->db->where('staffid', $staffid);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $date_created);
        $this->db->update('tblstaff_performance');

        return $this->db->affected_rows();
    }

    public function get_kra_based_on_department() {
        $this->load->model('departments_model');
        $departmentsId = $this->departments_model->get_staff_departments('', true);
    
        // Initialize the query and bind parameters
        if (!empty($departmentsId)) {
            // Prepare a safe query using bindings
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where_in('kra.departmentid', $departmentsId);
            $this->db->order_by('kra.id', 'DESC');
        } else {
            // If no departments are found, set departmentid to NULL in the query
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where('kra.departmentid IS NULL', null, false);
        }
    
        // Execute the query
        $kraResult = $this->db->get()->result();
    
        // Loop through each KRA and get associated KPI and Department Name
        foreach ($kraResult as &$kra) {
            // Get KPI data for each KRA
            $this->db->select('kpi.*');
            $this->db->from('tblstaff_performance_kpi kpi');
            $this->db->where('kpi.kra_id', $kra->id);
            $kpiResult = $this->db->get()->result();
    
            // Attach KPI data to the KRA
            $kra->kpi_data = !empty($kpiResult) ? $kpiResult : null;
    
            // Get department name for the KRA's departmentid
            $this->db->select('name');
            $this->db->from('tbldepartments');
            $this->db->where('departmentid', $kra->departmentid);
            $depResult = $this->db->get()->row();
    
            // Attach department name to the KRA
            $kra->departmentName = !empty($depResult) ? $depResult->name : null;
        }
    
        return $kraResult;
    }

    public function get_default_kra_based_on_department($departmentId) {
        
        // Initialize the query and bind parameters
        if (!empty($departmentId)) {
            // Prepare a safe query using bindings
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where('kra.departmentid', $departmentId);
            $this->db->where('kra.type', '0');
        } else {
            // If no departments are found, set departmentid to NULL in the query
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where('kra.departmentid IS NULL', null, false);
        }
    
        // Execute the query
        $kraResult = $this->db->get()->result();
    
        return $kraResult;
    }

    public function get_custom_kra_based_on_department($departmentId) {
        
        // Initialize the query and bind parameters
        if (!empty($departmentId)) {
            // Prepare a safe query using bindings
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where('kra.departmentid', $departmentId);
            $this->db->where('kra.type', '1');
        } else {
            // If no departments are found, set departmentid to NULL in the query
            $this->db->select('kra.*');
            $this->db->from('tblstaff_performance_kra kra');
            $this->db->where('kra.departmentid IS NULL', null, false);
        }
    
        // Execute the query
        $kraResult = $this->db->get()->result();

        // Loop through each KRA and get associated KPI and Department Name
        foreach ($kraResult as &$kra) {
            // Get KPI data for each KRA
            $this->db->select('kpi.*');
            $this->db->from('tblstaff_performance_kpi kpi');
            $this->db->where('kpi.kra_id', $kra->id);
            $kpiResult = $this->db->get()->result();
    
            // Attach KPI data to the KRA
            $kra->kpi_data = !empty($kpiResult) ? $kpiResult : null;
        }
    
        return $kraResult;
    }
    
    public function get_kra_based_on_id($id){
        $this->db->select('*');
        $this->db->from('tblstaff_performance_kra');
        $this->db->where('id', $id);

        $kraResult = $this->db->get()->result();

        return $kraResult;
    }   

    public function get_kpi_based_on_kraid($id){
        $this->db->select('*');
        $this->db->from('tblstaff_performance_kpi');
        $this->db->where('kra_id', $id);

        $kpiResult = $this->db->get()->result();

        return $kpiResult;
    }

    public function update_tblstaff_performance_staffComment($staffid, $date_created, $comment)
    {
        $data = [
            'staff_comment' => $comment,
        ];
      
        $this->db->set($data);
        $this->db->where('staffid', $staffid);
        $this->db->where("date_created", $date_created);
        $this->db->update('tblstaff_performance');

        // Return the number of affected rows
        return $this->db->affected_rows();
    }

    
}
