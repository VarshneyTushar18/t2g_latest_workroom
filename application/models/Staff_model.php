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
    // $active: 1 = active (default), 0 = inactive
    public function get_staff_based_on_department($id = '', $active = 1)
    {
        $this->load->model('departments_model');
        $active = ((int) $active === 0) ? 0 : 1;

        if ($id) {
            // Specific department selected (Evaluation page AJAX):
            // return staff of that department by active status.
            $query = 'SELECT tblstaff.*, tblstaff_departments.departmentid
                      FROM tblstaff
                      INNER JOIN tblstaff_departments ON tblstaff.staffid = tblstaff_departments.staffid
                      WHERE tblstaff.active = ' . (int) $active . '
                        AND tblstaff_departments.departmentid = ' . (int) $id . '
                      ORDER BY tblstaff.firstname ASC, tblstaff.lastname ASC';
            $rows = $this->db->query($query)->result_array();

            $unique = [];
            $seen = [];
            foreach ($rows as $row) {
                if (isset($seen[$row['staffid']])) {
                    continue;
                }
                $seen[$row['staffid']] = true;
                $unique[] = $row;
            }

            return $unique;
        }

        $departments = $this->departments_model->get_staff_departments();
        if (empty($departments)) {
            return [];
        }

        $dept_ids = array_map('intval', array_column($departments, 'departmentid'));
        $dept_ids = array_filter($dept_ids);
        if (empty($dept_ids)) {
            return [];
        }

        $ids_list = implode(',', $dept_ids);
        $query = 'SELECT DISTINCT s.staffid, s.firstname, s.lastname, s.email, s.active, sd.departmentid
                  FROM ' . db_prefix() . 'staff s
                  INNER JOIN ' . db_prefix() . 'staff_departments sd ON s.staffid = sd.staffid
                  WHERE s.active = ' . (int) $active . '
                    AND sd.departmentid IN (' . $ids_list . ')
                  ORDER BY s.firstname ASC, s.lastname ASC';

        return $this->db->query($query)->result_array();
    }

    /**
     * For PEDMA Evaluation: managers only see assigned team (team_manage).
     * Super Admin / Admin keep department-wide list.
     */
    public function filter_staff_for_pedma_evaluation($staff_list = [])
    {
        if (!is_array($staff_list)) {
            return [];
        }

        if (is_admin() || is_super_admin() || is_admin2()) {
            return $staff_list;
        }

        $manager_id = (int) get_staff_user_id();
        $filtered = [];

        foreach ($staff_list as $staff) {
            $team_manage = isset($staff['team_manage']) ? (int) $staff['team_manage'] : 0;
            if ($team_manage === $manager_id) {
                $filtered[] = $staff;
            }
        }

        return $filtered;
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

    /**
     * Employment category for leave rates: fte | intern | wfh | contractual
     */
    public function get_employment_category($staffid)
    {
        $staffid = (int) $staffid;
        if ($staffid <= 0) {
            return 'fte';
        }

        static $has_category_col = null;
        if ($has_category_col === null) {
            $has_category_col = $this->db->field_exists('employment_category', db_prefix() . 'staff_info');
        }
        if (!$has_category_col) {
            return 'fte';
        }

        $row = $this->db->select('employment_category')
            ->where('staffid', $staffid)
            ->get(db_prefix() . 'staff_info')
            ->row();
        $cat = strtolower(trim((string) ($row->employment_category ?? 'fte')));
        if (!in_array($cat, ['fte', 'intern', 'wfh', 'contractual'], true)) {
            return 'fte';
        }

        return $cat;
    }

    /**
     * Batch employment categories for many staff (avoids N+1 on leave balance).
     * @return array<int,string> staffid => fte|intern|wfh|contractual
     */
    public function get_employment_categories_batch(array $staff_ids)
    {
        $out = [];
        $staff_ids = array_values(array_filter(array_map('intval', $staff_ids)));
        foreach ($staff_ids as $sid) {
            $out[$sid] = 'fte';
        }
        if (!$staff_ids) {
            return $out;
        }

        static $has_category_col = null;
        if ($has_category_col === null) {
            $has_category_col = $this->db->field_exists('employment_category', db_prefix() . 'staff_info');
        }
        if (!$has_category_col) {
            return $out;
        }

        $rows = $this->db->select('staffid, employment_category')
            ->where_in('staffid', $staff_ids)
            ->get(db_prefix() . 'staff_info')
            ->result_array();
        foreach ($rows as $row) {
            $sid = (int) $row['staffid'];
            $cat = strtolower(trim((string) ($row['employment_category'] ?? 'fte')));
            $out[$sid] = in_array($cat, ['fte', 'intern', 'wfh', 'contractual'], true) ? $cat : 'fte';
        }

        return $out;
    }

    /**
     * True once resignation procedure is submitted (any approval state).
     */
    public function staff_has_submitted_resignation($staffid)
    {
        $staffid = (int) $staffid;
        if ($staffid <= 0 || !$this->db->table_exists(db_prefix() . 'hr_list_staff_quitting_work')) {
            return false;
        }

        static $cache = [];
        if (array_key_exists($staffid, $cache)) {
            return $cache[$staffid];
        }

        $cache[$staffid] = (int) $this->db->where('staffid', $staffid)
            ->count_all_results(db_prefix() . 'hr_list_staff_quitting_work') > 0;

        return $cache[$staffid];
    }

    /**
     * Batch resignation flags.
     * @return array<int,bool>
     */
    public function get_resigned_staff_map(array $staff_ids)
    {
        $staff_ids = array_values(array_filter(array_map('intval', $staff_ids)));
        $map = [];
        foreach ($staff_ids as $sid) {
            $map[$sid] = false;
        }
        if (!$staff_ids || !$this->db->table_exists(db_prefix() . 'hr_list_staff_quitting_work')) {
            return $map;
        }

        $rows = $this->db->select('staffid')
            ->where_in('staffid', $staff_ids)
            ->group_by('staffid')
            ->get(db_prefix() . 'hr_list_staff_quitting_work')
            ->result_array();
        foreach ($rows as $row) {
            $map[(int) $row['staffid']] = true;
        }

        return $map;
    }

    /**
     * FY carry-forward cap: WFH 5, others (FTE/Intern) 10.
     */
    public function get_leave_carry_forward_cap($staffid)
    {
        return $this->get_employment_category($staffid) === 'wfh' ? 5.0 : 10.0;
    }

    /**
     * 1 = first calendar month of employment, 2 = second, etc. 0 = before DOJ.
     */
    public function employment_month_number($doj, $month, $year)
    {
        $month = (int) $month;
        $year = (int) $year;
        if ($month < 1 || $month > 12 || empty($doj) || $doj === '0000-00-00' || !strtotime($doj)) {
            return 0;
        }

        $doj_y = (int) date('Y', strtotime($doj));
        $doj_m = (int) date('n', strtotime($doj));
        $n = (($year - $doj_y) * 12) + ($month - $doj_m) + 1;

        return $n;
    }

    /**
     * Base monthly rate from category + tenure (no first/second-month adjustment).
     */
    public function get_base_monthly_leave_rate($staffid = 0, $doj = null, $as_of_date = null)
    {
        $staffid = (int) $staffid;
        if ($staffid > 0 && $this->staff_has_submitted_resignation($staffid)) {
            return 0.0;
        }

        $category = $staffid > 0 ? $this->get_employment_category($staffid) : 'fte';
        if ($category === 'intern' || $category === 'wfh' || $category === 'contractual') {
            return 1.0;
        }

        // Full-time: 1.25 until 2 years completed, then 1.75
        if (empty($doj) || $doj === '0000-00-00' || !strtotime($doj)) {
            if ($staffid > 0) {
                $info = $this->db->select('doj')->where('staffid', $staffid)->get(db_prefix() . 'staff_info')->row();
                $doj = $info->doj ?? null;
            }
        }
        if (empty($doj) || $doj === '0000-00-00' || !strtotime($doj)) {
            return 1.25;
        }

        $as_of = $as_of_date ?: date('Y-m-d');
        $tenureYears = floor((strtotime($as_of) - strtotime($doj)) / (365 * 24 * 60 * 60));
        if ($tenureYears >= 2) {
            return 1.75;
        }

        return 1.25;
    }

    /**
     * Monthly earned-leave credit for a staff member.
     * Policy:
     * - First employment month: 0
     * - Second month: first + second month entitlement (2 x base rate)
     * - Thereafter: base rate (intern/WFH 1, FTE 1.25 / 1.75 after 2 years)
     * - After resignation: 0
     *
     * @param string|null $doj
     * @param int         $staffid
     * @param int|null    $month
     * @param int|null    $year
     */
    function calculateEarnedLeaves($doj, $staffid = 0, $month = null, $year = null)
    {
        $staffid = (int) $staffid;
        $month = $month === null ? (int) date('n') : (int) $month;
        $year = $year === null ? (int) date('Y') : (int) $year;

        if ($staffid > 0 && $this->staff_has_submitted_resignation($staffid)) {
            return 0.0;
        }

        if ((empty($doj) || $doj === '0000-00-00' || !strtotime($doj)) && $staffid > 0) {
            $info = $this->db->select('doj')->where('staffid', $staffid)->get(db_prefix() . 'staff_info')->row();
            $doj = $info->doj ?? null;
        }

        $as_of = sprintf('%04d-%02d-%02d', $year, $month, min(28, (int) date('j')));
        $base = $this->get_base_monthly_leave_rate($staffid, $doj, $as_of);

        $emp_month = $this->employment_month_number($doj, $month, $year);
        if ($emp_month <= 0) {
            return 0.0;
        }
        if ($emp_month === 1) {
            return 0.0;
        }
        if ($emp_month === 2) {
            // Credit month-1 + month-2 together in the second month.
            return round($base * 2, 2);
        }

        return round($base, 2);
    }

    /**
     * Clear leave balance when resignation is submitted.
     */
    public function zero_leave_balance_on_resignation($staffid)
    {
        $staffid = (int) $staffid;
        if ($staffid <= 0 || !$this->db->table_exists(db_prefix() . 'timesheets_requisition_leave')) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'timesheets_requisition_leave', [
            'staff_id'      => $staffid,
            'subject'       => 'Leave balance cleared on resignation',
            'start_time'    => $now,
            'end_time'      => $now,
            'datecreated'   => $now,
            'carry_forward' => 0,
            'leave_balance' => 0,
            'status'        => 1,
        ]);

        return (bool) $this->db->insert_id();
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
	
	/**
	 * Opening EL balance on the 1st of $month/$year.
	 *
	 * Correct roll-forward (matches HR expectation):
	 *   opening(this month) = opening(prev) + earned(prev) − approved EL taken(prev)
	 * Example: balance 3, take 2 → remain 1 → next month opening 1, then +1.25 earned.
	 *
	 * Prefer DOJ roll-forward (earn − taken each month) so Leave Balance matches View Details.
	 * Stale day-1 type_of_leave='0' snapshots often store a wrong opening (e.g. 2.5) and must
	 * NOT short-circuit the month when DOJ exists — that caused CF 2.5 vs table opening 6.25.
	 * Snapshots are only used when DOJ is missing (legacy seed).
	 * Do NOT reuse carry_forward from the latest leave application (that column stores month
	 * opening, not remaining after deduction).
	 */
	 function carryForward($staffid, $doj, $month, $year)
    {
        $staffid = (int) $staffid;
        $month = (int) $month;
        $year = (int) $year;
        if ($staffid <= 0 || $month < 1 || $month > 12 || $year < 2000) {
            return 0;
        }

        if ($this->staff_has_submitted_resignation($staffid)) {
            return 0;
        }

        static $cache = [];
        $cache_key = $staffid . '-' . $year . '-' . $month;
        if (array_key_exists($cache_key, $cache)) {
            return $cache[$cache_key];
        }

        if ((empty($doj) || $doj === '0000-00-00' || !strtotime($doj)) && $staffid > 0) {
            $info = $this->db->select('doj')->where('staffid', $staffid)->get(db_prefix() . 'staff_info')->row();
            $doj = $info->doj ?? null;
        }

        $firstDayOfTheMonth = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $el_keys = $this->earned_leave_type_keys_sql();
        $has_doj = !empty($doj) && $doj !== '0000-00-00' && strtotime($doj);

        if ($has_doj) {
            // Authoritative path: open at DOJ with 0, roll earn − taken through previous month.
            $carry = 0.0;
            $cursor_y = (int) date('Y', strtotime($doj));
            $cursor_m = (int) date('n', strtotime($doj));
        } else {
            // No DOJ: latest monthly opening snapshot (HR seed on day 1).
            $snap = $this->db->query(
                "SELECT carry_forward, start_time
                 FROM tbltimesheets_requisition_leave
                 WHERE staff_id = ?
                   AND start_time <= ?
                   AND DAY(start_time) = 1
                   AND (type_of_leave = '0' OR type_of_leave = 0 OR type_of_leave = '')
                 ORDER BY start_time DESC, id DESC
                 LIMIT 1",
                [$staffid, $firstDayOfTheMonth]
            )->row();

            if ($snap) {
                $carry = (float) ($snap->carry_forward ?? 0);
                $cursor_y = (int) date('Y', strtotime($snap->start_time));
                $cursor_m = (int) date('n', strtotime($snap->start_time));
            } else {
                $legacy = $this->db->query(
                    "SELECT carry_forward FROM tbltimesheets_requisition_leave
                     WHERE staff_id = ? AND start_time <= ?
                     ORDER BY id DESC LIMIT 1",
                    [$staffid, $firstDayOfTheMonth]
                )->row();
                $carry = $legacy ? (float) ($legacy->carry_forward ?? 0) : 0.0;
                if ($month === 4) {
                    $cap = $this->get_leave_carry_forward_cap($staffid);
                    if ($carry > $cap) {
                        $carry = $cap;
                    }
                }
                $cache[$cache_key] = $carry;
                return $carry;
            }

            // Snapshot for this exact month is the opening only when we have no DOJ to recompute.
            if ($cursor_y === $year && $cursor_m === $month) {
                if ($month === 4) {
                    $cap = $this->get_leave_carry_forward_cap($staffid);
                    if ($carry > $cap) {
                        $carry = $cap;
                    }
                }
                $cache[$cache_key] = $carry;
                return $carry;
            }
        }

        // Preload approved EL taken from snapshot/DOJ month through previous month.
        $range_start = sprintf('%04d-%02d-01', $cursor_y, $cursor_m);
        $prev = $this->previous_calendar_month($month, $year);
        $range_end = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $prev['year'], $prev['month'])));

        $taken_by_ym = [];
        if (strtotime($range_start) <= strtotime($range_end)) {
            $taken_rows = $this->db->query(
                "SELECT YEAR(start_time) AS y, MONTH(start_time) AS m,
                        COALESCE(SUM(number_of_leaving_day), 0) AS taken
                 FROM tbltimesheets_requisition_leave
                 WHERE staff_id = ?
                   AND status = 1
                   AND type_of_leave IN ($el_keys)
                   AND start_time >= ?
                   AND start_time < ?
                 GROUP BY YEAR(start_time), MONTH(start_time)",
                [$staffid, $range_start . ' 00:00:00', $firstDayOfTheMonth]
            )->result_array();
            foreach ($taken_rows as $row) {
                $taken_by_ym[(int) $row['y'] . '-' . (int) $row['m']] = (float) $row['taken'];
            }
        }

        $is_resigned = false;
        $category = $this->get_employment_category($staffid);
        $override_table = $this->ensure_earned_leave_override_table();
        $override_rows = $this->db->select('month, year, earned_days')
            ->from($override_table)
            ->where('staff_id', $staffid)
            ->where('year >=', $cursor_y)
            ->where('year <=', $year)
            ->get()
            ->result_array();
        $overrides = [];
        foreach ($override_rows as $row) {
            $overrides[(int) $row['year'] . '-' . (int) $row['month']] = (float) $row['earned_days'];
        }

        $y = $cursor_y;
        $m = $cursor_m;
        while ($y < $year || ($y === $year && $m < $month)) {
            $ym = $y . '-' . $m;
            $as_of = sprintf('%04d-%02d-%02d', $y, $m, min(28, (int) date('j')));
            if (isset($overrides[$ym])) {
                $earned = (float) $overrides[$ym];
            } else {
                $earned = (float) $this->calculateEarnedLeavesFast(
                    $doj,
                    $staffid,
                    $m,
                    $y,
                    $is_resigned,
                    $category,
                    $as_of
                );
            }
            $taken = $taken_by_ym[$ym] ?? 0.0;
            $carry = round($carry + $earned - $taken, 2);

            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
            // Cap when entering April (FY boundary).
            if ($m === 4) {
                $cap = $this->get_leave_carry_forward_cap($staffid);
                if ($carry > $cap) {
                    $carry = $cap;
                }
            }
        }

        if ($month === 4) {
            $cap = $this->get_leave_carry_forward_cap($staffid);
            if ($carry > $cap) {
                $carry = $cap;
            }
        }

        $cache[$cache_key] = $carry;
        return $carry;
    }

    /**
     * Storage keys that count as earned leave for deduction / carry math.
     */
    public function earned_leave_type_keys()
    {
        return ['earned-leave', '8', 'Leave', 'annual_leave', 'planned_leaves'];
    }

    public function earned_leave_type_keys_sql()
    {
        $keys = [];
        foreach ($this->earned_leave_type_keys() as $k) {
            $keys[] = $this->db->escape($k);
        }
        return implode(',', $keys);
    }

    public function previous_calendar_month($month, $year)
    {
        $month = (int) $month;
        $year = (int) $year;
        if ($month <= 1) {
            return ['month' => 12, 'year' => $year - 1];
        }
        return ['month' => $month - 1, 'year' => $year];
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
		$staffid = (int) $staffid;
		$month = (int) $month;
		$year = (int) $year;
		$coutn_taken_leave = 0;
		if ($staffid <= 0 || $month < 1 || $month > 12 || $year < 2000) {
			return 0;
		}

		$monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";
		 $sql = "SELECT number_of_leaving_day FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND end_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=1";
        
        $query = $this->db->query($sql);
	
        $sum_of_leave = $query ? $query->result_array() : [];
		for($i=0;$i<count($sum_of_leave);$i++){
			$coutn_taken_leave += (float) $sum_of_leave[$i]['number_of_leaving_day'];
		}
		
        return $coutn_taken_leave;
		
	}
	function status_approve($staffid, $month, $year)
	{
		 $staffid = (int) $staffid;
		 $month = (int) $month;
		 $year = (int) $year;
		 if ($staffid <= 0 || $month < 1 || $month > 12 || $year < 2000) {
			return 0;
		 }

		 $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";
		 $sql = "SELECT count(status) as count_status FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status IN(2,0)";
        
        $query = $this->db->query($sql);
        $sum_of_status = $query ? $query->row() : null;
        return $sum_of_status->count_status ?? 0;
		
	}
	function monthlystatus($staffid, $month, $year)
    {
		$staffid = (int) $staffid;
		$month = (int) $month;
		$year = (int) $year;
		if ($staffid <= 0 || $month < 1 || $month > 12 || $year < 2000) {
			return 0;
		}
		
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        $day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $lastDayOfTheMonth = "$year-$monthFormatted-$day";
		 $firstDayOfTheMonth = "$year-$monthFormatted-01";

	   $sql = "SELECT count(status) as sum FROM tbltimesheets_requisition_leave WHERE staff_id = $staffid AND  start_time BETWEEN '$firstDayOfTheMonth' AND '$lastDayOfTheMonth' AND status=4";
        
        $query = $this->db->query($sql);
        $sum_of_leaves = $query ? $query->row() : null;
        return $sum_of_leaves->sum ?? 0;
		
	}
	function monthlyLeaveBalance($staffid, $doj, $month, $year)
    {
		$staffid = (int) $staffid;
		$month = (int) $month;
		$year = (int) $year;
		if ($staffid <= 0 || $month < 1 || $month > 12 || $year < 2000) {
			return 0;
		}

		$carry = (float) $this->carryForward($staffid, $doj, $month, $year);
		$earned = (float) $this->calculateEarnedLeaves($doj, $staffid, $month, $year);
		$overrides = $this->get_earned_leave_overrides_batch([$staffid], $month, $year);
		if (isset($overrides[$staffid])) {
			$earned = (float) $overrides[$staffid];
		}
		$taken = (float) $this->monthlyLeave($staffid, $month, $year);

		return $this->compute_monthly_leave_balance($carry, $earned, $taken);
    }

    /**
     * Batch-enrich leave balance rows (replaces per-staff query loops).
     *
     * @param array $rows
     * @param int   $month
     * @param int   $year
     * @return array
     */
    public function enrich_leave_balance_month(array $rows, $month, $year)
    {
        if (empty($rows)) {
            return $rows;
        }

        $month = (int) $month;
        $year = (int) $year;
        if ($month < 1 || $month > 12 || $year < 2000) {
            return $rows;
        }

        $staff_ids = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['staffid'] ?? 0);
            if ($sid > 0) {
                $staff_ids[$sid] = $sid;
            }
        }
        if (empty($staff_ids)) {
            return $rows;
        }

        $id_list = implode(',', array_values($staff_ids));
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDay = "$year-$monthFormatted-01 00:00:00";
        $lastDay = "$year-$monthFormatted-$day";
        $lastDayTs = "$lastDay 00:00:00";

        $carry_forward = [];
        $leave_balance = [];
        $leave_taken = [];
        $absent_status = [];
        $status_approve = [];

        // Latest leave_balance column (legacy display only; real balance recomputed below).
        $q = $this->db->query(
            "SELECT r.staff_id, r.leave_balance
            FROM tbltimesheets_requisition_leave r
            INNER JOIN (
                SELECT staff_id, MAX(id) AS max_id
                FROM tbltimesheets_requisition_leave
                WHERE staff_id IN ($id_list) AND start_time <= ?
                GROUP BY staff_id
            ) latest ON r.id = latest.max_id",
            [$lastDayTs]
        );
        foreach ($q->result_array() as $r) {
            $leave_balance[(int) $r['staff_id']] = $r['leave_balance'];
        }

        // Approved earned-leave days only (LOP / other types must not reduce EL balance).
        // Use half-open datetime range so full last day is included.
        $range_start = $firstDay;
        $range_end_exclusive = date('Y-m-d H:i:s', strtotime($lastDay . ' +1 day'));
        $el_keys = $this->earned_leave_type_keys_sql();
        $q = $this->db->query(
            "SELECT staff_id, SUM(number_of_leaving_day) AS total
            FROM tbltimesheets_requisition_leave
            WHERE staff_id IN ($id_list)
              AND start_time >= ? AND start_time < ?
              AND status = 1
              AND type_of_leave IN ($el_keys)
            GROUP BY staff_id",
            [$range_start, $range_end_exclusive]
        );
        foreach ($q->result_array() as $r) {
            $leave_taken[(int) $r['staff_id']] = (float) $r['total'];
        }

        $q = $this->db->query(
            "SELECT staff_id,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS absent_total,
                SUM(CASE WHEN status IN (2, 0) THEN 1 ELSE 0 END) AS pending_total
            FROM tbltimesheets_requisition_leave
            WHERE staff_id IN ($id_list)
              AND start_time >= ? AND start_time < ?
              AND status IN (0, 2, 4)
            GROUP BY staff_id",
            [$range_start, $range_end_exclusive]
        );
        foreach ($q->result_array() as $r) {
            $sid = (int) $r['staff_id'];
            $absent_status[$sid] = (int) $r['absent_total'];
            $status_approve[$sid] = (int) $r['pending_total'];
        }

        $earned_overrides = $this->get_earned_leave_overrides_batch(array_values($staff_ids), $month, $year);

        // Reuse resignation + category maps across months in the same request (All Months).
        static $staff_ctx_cache = [];
        $ctx_key = implode(',', array_values($staff_ids));
        if (!isset($staff_ctx_cache[$ctx_key])) {
            $staff_ctx_cache[$ctx_key] = [
                'resigned'   => $this->get_resigned_staff_map(array_values($staff_ids)),
                'categories' => $this->get_employment_categories_batch(array_values($staff_ids)),
            ];
        }
        $resigned = $staff_ctx_cache[$ctx_key]['resigned'];
        $categories = $staff_ctx_cache[$ctx_key]['categories'];
        $as_of = sprintf('%04d-%02d-%02d', $year, $month, min(28, (int) date('j')));

        $timesheets_model = null;
        if ($year === 2026 && is_dir(module_dir_path('timesheets'))) {
            $CI = &get_instance();
            $CI->load->model('timesheets/timesheets_model');
            $timesheets_model = $CI->timesheets_model;
        }

        foreach ($rows as &$leaveData) {
            $sid = (int) $leaveData['staffid'];
            $default_earned = $this->calculateEarnedLeavesFast(
                $leaveData['doj'] ?? null,
                $sid,
                $month,
                $year,
                !empty($resigned[$sid]),
                $categories[$sid] ?? 'fte',
                $as_of
            );

            $seeded = null;
            if ($timesheets_model && method_exists($timesheets_model, 'resolve_excel_seeded_earned_leave_month')) {
                $seeded = $timesheets_model->resolve_excel_seeded_earned_leave_month($sid, $year, $month, [
                    'doj'              => $leaveData['doj'] ?? null,
                    'resigned'         => !empty($resigned[$sid]),
                    'category'         => $categories[$sid] ?? 'fte',
                    'earned_override'  => $earned_overrides[$sid] ?? null,
                    'taken_this_month' => $leave_taken[$sid] ?? 0,
                ]);
            }

            if ($seeded !== null) {
                $leaveData['carry_forward'] = $seeded['carry_forward'];
                $leaveData['earned_leave'] = $seeded['monthly_earn'];
                $leaveData['leave_taken'] = $seeded['consumed'];
                $leaveData['leave_balance'] = $seeded['balance'];
            } else {
                // True opening = prev closing (earned − EL taken), not stale application column.
                $leaveData['carry_forward'] = $this->carryForward($sid, $leaveData['doj'] ?? null, $month, $year);
                $leaveData['earned_leave'] = isset($earned_overrides[$sid])
                    ? (float) $earned_overrides[$sid]
                    : $default_earned;
                $leaveData['leave_taken'] = $leave_taken[$sid] ?? 0;
                $leaveData['leave_balance'] = $this->compute_monthly_leave_balance(
                    $leaveData['carry_forward'],
                    $leaveData['earned_leave'],
                    $leaveData['leave_taken']
                );
            }

            $leaveData['earned_leave_is_override'] = isset($earned_overrides[$sid]);
            $leaveData['earned_leave_default'] = $default_earned;
            $leaveData['monthly_leaves'] = $leave_balance[$sid] ?? 0;
            $leaveData['status'] = $absent_status[$sid] ?? 0;
            $leaveData['status_approve'] = $status_approve[$sid] ?? 0;
        }
        unset($leaveData);

        return $rows;
    }

    /**
     * Enrich leave-balance rows for months 1..$through_month in one pass.
     * Avoids 12× full carryForward rebuilds (All Months view).
     *
     * @return array<int,array> month => enriched staff rows
     */
    public function enrich_leave_balance_year(array $rows, $year, $through_month = 12)
    {
        $year = (int) $year;
        $through_month = (int) $through_month;
        if ($through_month < 1) {
            $through_month = 1;
        }
        if ($through_month > 12) {
            $through_month = 12;
        }
        if (empty($rows) || $year < 2000) {
            return [];
        }

        $staff_ids = [];
        $by_staff = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['staffid'] ?? 0);
            if ($sid <= 0) {
                continue;
            }
            $staff_ids[$sid] = $sid;
            $by_staff[$sid] = $row;
        }
        if (empty($staff_ids)) {
            return [];
        }

        $id_list = implode(',', array_values($staff_ids));
        $year_start = sprintf('%04d-01-01 00:00:00', $year);
        $year_end_exclusive = sprintf('%04d-01-01 00:00:00', $year + 1);
        $el_keys = $this->earned_leave_type_keys_sql();

        $taken_by_staff_month = [];
        $taken_rows = $this->db->query(
            "SELECT staff_id, MONTH(start_time) AS m,
                    COALESCE(SUM(number_of_leaving_day), 0) AS total
             FROM tbltimesheets_requisition_leave
             WHERE staff_id IN ($id_list)
               AND start_time >= ? AND start_time < ?
               AND status = 1
               AND type_of_leave IN ($el_keys)
             GROUP BY staff_id, MONTH(start_time)",
            [$year_start, $year_end_exclusive]
        )->result_array();
        foreach ($taken_rows as $r) {
            $taken_by_staff_month[(int) $r['staff_id']][(int) $r['m']] = (float) $r['total'];
        }

        $absent_by_staff_month = [];
        $pending_by_staff_month = [];
        $status_rows = $this->db->query(
            "SELECT staff_id, MONTH(start_time) AS m,
                    SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS absent_total,
                    SUM(CASE WHEN status IN (2, 0) THEN 1 ELSE 0 END) AS pending_total
             FROM tbltimesheets_requisition_leave
             WHERE staff_id IN ($id_list)
               AND start_time >= ? AND start_time < ?
               AND status IN (0, 2, 4)
             GROUP BY staff_id, MONTH(start_time)",
            [$year_start, $year_end_exclusive]
        )->result_array();
        foreach ($status_rows as $r) {
            $sid = (int) $r['staff_id'];
            $m = (int) $r['m'];
            $absent_by_staff_month[$sid][$m] = (int) $r['absent_total'];
            $pending_by_staff_month[$sid][$m] = (int) $r['pending_total'];
        }

        $override_table = $this->ensure_earned_leave_override_table();
        $override_rows = $this->db->select('staff_id, month, earned_days')
            ->from($override_table)
            ->where_in('staff_id', array_values($staff_ids))
            ->where('year', $year)
            ->where('month >=', 1)
            ->where('month <=', $through_month)
            ->get()
            ->result_array();
        $overrides = [];
        foreach ($override_rows as $row) {
            $overrides[(int) $row['staff_id']][(int) $row['month']] = (float) $row['earned_days'];
        }

        $resigned = $this->get_resigned_staff_map(array_values($staff_ids));
        $categories = $this->get_employment_categories_batch(array_values($staff_ids));

        // Opening CF for January (or first month) once per staff.
        $opening = [];
        $start_month_by_staff = [];
        foreach ($by_staff as $sid => $row) {
            if (!empty($resigned[$sid])) {
                $opening[$sid] = 0.0;
                $start_month_by_staff[$sid] = 1;
                continue;
            }
            $doj = $row['doj'] ?? null;
            $start_m = 1;
            if (!empty($doj) && $doj !== '0000-00-00' && strtotime($doj)) {
                $doj_y = (int) date('Y', strtotime($doj));
                $doj_m = (int) date('n', strtotime($doj));
                if ($doj_y > $year) {
                    $start_m = $through_month + 1; // not employed this year
                } elseif ($doj_y === $year) {
                    $start_m = $doj_m;
                }
            }
            $start_month_by_staff[$sid] = $start_m;
            // Opening for first displayed month = true CF into that month.
            $opening[$sid] = (float) $this->carryForward($sid, $doj, max(1, $start_m), $year);
        }

        $timesheets_model = null;
        if ($year === 2026 && is_dir(module_dir_path('timesheets'))) {
            $CI = &get_instance();
            $CI->load->model('timesheets/timesheets_model');
            $timesheets_model = $CI->timesheets_model;
        }

        $out = [];
        $running = $opening;
        for ($month = 1; $month <= $through_month; $month++) {
            $as_of = sprintf('%04d-%02d-%02d', $year, $month, min(28, (int) date('j')));
            $month_rows = [];
            foreach ($by_staff as $sid => $base_row) {
                $start_m = (int) ($start_month_by_staff[$sid] ?? 1);
                if ($month < $start_m) {
                    continue; // before DOJ — omit so CF/Balance don't look "blank"
                }
                $leaveData = $base_row;
                $default_earned = $this->calculateEarnedLeavesFast(
                    $base_row['doj'] ?? null,
                    $sid,
                    $month,
                    $year,
                    !empty($resigned[$sid]),
                    $categories[$sid] ?? 'fte',
                    $as_of
                );
                $taken = (float) ($taken_by_staff_month[$sid][$month] ?? 0);

                $seeded = null;
                if ($timesheets_model && method_exists($timesheets_model, 'resolve_excel_seeded_earned_leave_month')) {
                    $seeded = $timesheets_model->resolve_excel_seeded_earned_leave_month($sid, $year, $month, [
                        'doj'              => $base_row['doj'] ?? null,
                        'resigned'         => !empty($resigned[$sid]),
                        'category'         => $categories[$sid] ?? 'fte',
                        'earned_overrides' => $overrides[$sid] ?? [],
                        'taken_by_month'   => $taken_by_staff_month[$sid] ?? [],
                        'taken_this_month' => $taken,
                    ]);
                }

                if ($seeded !== null) {
                    $cf = (float) $seeded['carry_forward'];
                    $earned = (float) $seeded['monthly_earn'];
                    $taken = (float) $seeded['consumed'];
                    $balance = (float) $seeded['balance'];
                } else {
                    $cf = (float) ($running[$sid] ?? 0);
                    if ($month === 4) {
                        $cap = $this->get_leave_carry_forward_cap($sid);
                        if ($cf > $cap) {
                            $cf = $cap;
                        }
                    }
                    $earned = isset($overrides[$sid][$month])
                        ? (float) $overrides[$sid][$month]
                        : $default_earned;
                    $balance = $this->compute_monthly_leave_balance($cf, $earned, $taken);
                }

                $leaveData['carry_forward'] = $cf;
                $leaveData['earned_leave'] = $earned;
                $leaveData['earned_leave_is_override'] = isset($overrides[$sid][$month]);
                $leaveData['earned_leave_default'] = $default_earned;
                $leaveData['monthly_leaves'] = $balance;
                $leaveData['leave_taken'] = $taken;
                $leaveData['status'] = $absent_by_staff_month[$sid][$month] ?? 0;
                $leaveData['status_approve'] = $pending_by_staff_month[$sid][$month] ?? 0;
                $leaveData['leave_balance'] = $balance;

                $month_rows[] = $leaveData;
                // Next month opening = this month closing.
                $running[$sid] = $balance;
            }
            if (!empty($month_rows)) {
                $out[$month] = $month_rows;
            }
        }

        return $out;
    }

    /**
     * Same earned-leave math as calculateEarnedLeaves, without per-row DB hits.
     */
    public function calculateEarnedLeavesFast($doj, $staffid, $month, $year, $is_resigned, $category, $as_of)
    {
        $staffid = (int) $staffid;
        $month = (int) $month;
        $year = (int) $year;
        if ($staffid > 0 && $is_resigned) {
            return 0.0;
        }

        $category = in_array($category, ['fte', 'intern', 'wfh', 'contractual'], true) ? $category : 'fte';
        if ($category === 'intern' || $category === 'wfh' || $category === 'contractual') {
            $base = 1.0;
        } else {
            $base = 1.25;
            if (!empty($doj) && $doj !== '0000-00-00' && strtotime($doj)) {
                $tenureYears = floor((strtotime($as_of) - strtotime($doj)) / (365 * 24 * 60 * 60));
                if ($tenureYears >= 2) {
                    $base = 1.75;
                }
            }
        }

        $emp_month = $this->employment_month_number($doj, $month, $year);
        if ($emp_month <= 0 || $emp_month === 1) {
            return 0.0;
        }
        if ($emp_month === 2) {
            return round($base * 2, 2);
        }

        return round($base, 2);
    }

    /**
     * Standard monthly leave balance for report display.
     */
    public function compute_monthly_leave_balance($carry_forward, $earned_leave, $leave_taken)
    {
        return round((float) $carry_forward + (float) $earned_leave - (float) $leave_taken, 2);
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

    /**
     * Send PEDMA-related mail using configured SMTP From (required for AWS SES).
     * Returns false if no valid recipients / send failed; never throws.
     */
    private function send_pedma_mail($to, $subject, $message, $cc = [], $reply_to = null)
    {
        $to = array_values(array_filter(array_map('trim', (array) $to)));
        $cc = array_values(array_filter(array_map('trim', (array) $cc)));
        $to = array_values(array_filter($to, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
        $cc = array_values(array_filter($cc, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
        // Avoid duplicate addresses across to/cc
        $cc = array_values(array_diff($cc, $to));

        if (empty($to)) {
            log_activity('PEDMA email skipped (no valid To): ' . $subject);
            return false;
        }

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
            if (!empty($reply_to) && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
                $this->email->reply_to($reply_to);
            }
            $this->email->subject($subject);
            $this->email->message($message);
            $ok = (bool) $this->email->send(false);
            if (!$ok) {
                log_activity('PEDMA email failed: ' . $subject . ' | ' . $this->email->print_debugger(['headers']));
            }
            return $ok;
        } catch (Exception $e) {
            log_activity('PEDMA email exception: ' . $subject . ' | ' . $e->getMessage());
            return false;
        }
    }

    public function send_performance_email($staffid, $data)
    {
        $time = strtotime($data['date_created']);
        $year = date('Y', $time);
        $month = date('F', $time);

        $user_email = get_staff_email_id($data['staffid']);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $subject = 'Performance Feedback - ' . $month . ' ' . $year . ' - ' . get_staff_full_name($data['staffid']);

        $message = '<p>Dear ' . html_escape(get_staff_full_name($data['staffid'])) . ',</p>';
        $message .= '<p>Your monthly review has been submitted by your manager. Below are the details:</p>';
        $message .= '<p><b>Employee Name:</b> ' . html_escape(get_staff_full_name($data['staffid'])) . '</p>';
        $message .= '<p><b>Employee ID:</b> ' . html_escape((string) get_staff_emp_id($data['staffid'])) . '</p>';
        $message .= '<p><b>Average Score:</b> ' . html_escape((string) $data['avg_score']) . '%</p>';
        $message .= '<p><b>Overall Feedback:</b> ' . $data['overall_feedback'] . '</p>';
        $message .= '<p><b>Click here for more details:</b> <a href="' . admin_url('staff/pedma') . '">' . admin_url('staff/pedma') . '</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe</em></p>';

        return $this->send_pedma_mail(
            $user_email,
            $subject,
            $message,
            ['sarabjeet@tech2globe.net', $manager_email, 'hr@tech2globe.com']
        );
    }

    public function send_performance_email_reply($staffid, $comment, $month, $year, $score)
    {
        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        // Prefer assigned manager if available
        $staff = $this->db->select('team_manage')->where('staffid', (int) $staffid)->get(db_prefix() . 'staff')->row();
        if ($staff && (int) $staff->team_manage > 0) {
            $assigned_manager_email = get_staff_email_id((int) $staff->team_manage);
            if (!empty($assigned_manager_email)) {
                $manager_email = $assigned_manager_email;
            }
        }

        $subject = 'Performance Feedback Reply - ' . $month . ' ' . $year . ' - ' . get_staff_full_name($staffid);

        $message = '<p>Hello,</p>';
        $message .= '<p>An employee has replied to their monthly PEDMA review. Details below:</p>';
        $message .= '<p><b>Employee Name:</b> ' . html_escape(get_staff_full_name($staffid)) . '</p>';
        $message .= '<p><b>Employee ID:</b> ' . html_escape((string) get_staff_emp_id($staffid)) . '</p>';
        $message .= '<p><b>Average Score:</b> ' . html_escape((string) $score) . '</p>';
        $message .= '<p><b>Comment:</b> ' . nl2br(html_escape((string) $comment)) . '</p>';
        $message .= '<p><em>Kind Regards,<br>' . html_escape(get_staff_full_name($staffid)) . '</em></p>';

        return $this->send_pedma_mail(
            ['sarabjeet@tech2globe.net', $manager_email],
            $subject,
            $message,
            ['hr@tech2globe.com'],
            $user_email
        );
    }

    public function send_fatal_error_performance_email($staffid, $score, $months, $comment)
    {
        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $subject = 'Performance Score Adjustment - ' . get_staff_full_name($staffid);

        $message = '<p>Dear ' . html_escape(get_staff_full_name($staffid)) . ',</p>';
        $message .= '<p>Your monthly review has been adjusted by your manager. Below are the details:</p>';
        $message .= '<p><b>Employee Name:</b> ' . html_escape(get_staff_full_name($staffid)) . '</p>';
        $message .= '<p><b>Employee ID:</b> ' . html_escape((string) get_staff_emp_id($staffid)) . '</p>';
        $message .= '<p><b>Reduction Score Percentage:</b> ' . html_escape((string) $score) . '%</p>';
        $message .= '<p><b>Effected Months:</b> ' . html_escape(implode(', ', (array) $months)) . '</p>';
        $message .= '<p><b>Reason:</b> ' . nl2br(html_escape((string) $comment)) . '</p>';
        $message .= '<p><b>Click here for more details:</b> <a href="' . admin_url('staff/pedma') . '">' . admin_url('staff/pedma') . '</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe</em></p>';

        return $this->send_pedma_mail(
            $user_email,
            $subject,
            $message,
            ['sarabjeet@tech2globe.net', $manager_email, 'hr@tech2globe.com']
        );
    }

    public function send_add_on_performance_email($staffid, $score, $months, $comment)
    {
        $user_email = get_staff_email_id($staffid);
        $manager_email = get_staff_email_id(get_staff_user_id());
        $subject = 'Performance Score Adjustment - ' . get_staff_full_name($staffid);

        $message = '<p>Dear ' . html_escape(get_staff_full_name($staffid)) . ',</p>';
        $message .= '<p>Your monthly review has been adjusted by your manager. Below are the details:</p>';
        $message .= '<p><b>Employee Name:</b> ' . html_escape(get_staff_full_name($staffid)) . '</p>';
        $message .= '<p><b>Employee ID:</b> ' . html_escape((string) get_staff_emp_id($staffid)) . '</p>';
        $message .= '<p><b>Add On Score Percentage:</b> ' . html_escape((string) $score) . '%</p>';
        $message .= '<p><b>Effected Months:</b> ' . html_escape(implode(', ', (array) $months)) . '</p>';
        $message .= '<p><b>Reason:</b> ' . nl2br(html_escape((string) $comment)) . '</p>';
        $message .= '<p><b>Click here for more details:</b> <a href="' . admin_url('staff/pedma') . '">' . admin_url('staff/pedma') . '</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe</em></p>';

        return $this->send_pedma_mail(
            $user_email,
            $subject,
            $message,
            ['sarabjeet@tech2globe.net', $manager_email, 'hr@tech2globe.com']
        );
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
            $this->db->where('kra.type', '0');
        }
    
        // Execute the query
        $kraResult = $this->db->get()->result();

        // If department has no Default KRAs configured, fall back to that
        // department's Custom KRAs so Default option still auto-fills criteria.
        if (empty($kraResult) && !empty($departmentId)) {
            $kraResult = $this->get_custom_kra_based_on_department($departmentId);
        }
    
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

    public function ensure_pedma_feedback_ack_columns()
    {
        if (!$this->db->table_exists('tblstaff_performance')) {
            return;
        }

        if (!$this->db->field_exists('feedback_accepted', 'tblstaff_performance')) {
            $this->db->query('ALTER TABLE `tblstaff_performance`
                ADD `feedback_accepted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `staff_comment`,
                ADD `feedback_accepted_at` DATETIME NULL DEFAULT NULL AFTER `feedback_accepted`');
        }
    }

    /**
     * Pending PEDMA reports awaiting employee response.
     * feedback_accepted: 0=pending, 1=accepted, 2=need meeting
     */
    public function get_pending_pedma_ack($staffid)
    {
        $this->ensure_pedma_feedback_ack_columns();

        $this->db->from('tblstaff_performance');
        $this->db->where('staffid', (int) $staffid);
        $this->db->where('status !=', 0);
        $this->db->where('feedback_accepted', 0);
        $this->db->where('overall_feedback IS NOT NULL', null, false);
        $this->db->where("TRIM(overall_feedback) !=", '');
        $this->db->order_by('date_created', 'DESC');
        $rows = $this->db->get()->result_array();

        $pending = [];
        foreach ($rows as $row) {
            $plain = trim(html_entity_decode(strip_tags((string) $row['overall_feedback']), ENT_QUOTES, 'UTF-8'));
            if ($plain === '' || $plain === '-') {
                continue;
            }
            $pending[] = $row;
        }

        return $pending;
    }

    public function respond_pedma_feedback($staffid, $month, $status)
    {
        $this->ensure_pedma_feedback_ack_columns();
        $status = (int) $status;
        if (!in_array($status, [1, 2], true)) {
            return ['success' => false, 'accepted_at' => null, 'status' => 0, 'row' => null];
        }

        $this->db->where('staffid', (int) $staffid);
        $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $month);
        $this->db->where('status !=', 0);
        $row = $this->db->get('tblstaff_performance')->row();

        if (!$row) {
            return ['success' => false, 'accepted_at' => null, 'status' => 0, 'row' => null];
        }

        if ((int) $row->feedback_accepted === $status) {
            return [
                'success'     => true,
                'accepted_at' => $row->feedback_accepted_at,
                'status'      => (int) $row->feedback_accepted,
                'row'         => $row,
            ];
        }
        if ((int) $row->feedback_accepted !== 0) {
            return [
                'success'     => true,
                'accepted_at' => $row->feedback_accepted_at,
                'status'      => (int) $row->feedback_accepted,
                'row'         => $row,
            ];
        }

        $accepted_at = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $row->id);
        $this->db->update('tblstaff_performance', [
            'feedback_accepted'    => $status,
            'feedback_accepted_at' => $accepted_at,
        ]);

        $ok = $this->db->affected_rows() > 0;
        if ($ok) {
            $row->feedback_accepted = $status;
            $row->feedback_accepted_at = $accepted_at;
        }

        return [
            'success'     => $ok,
            'accepted_at' => $ok ? $accepted_at : null,
            'status'      => $ok ? $status : 0,
            'row'         => $ok ? $row : null,
        ];
    }

    public function acknowledge_pedma_feedback($staffid, $month)
    {
        return $this->respond_pedma_feedback($staffid, $month, 1);
    }

    public function pedma_need_meeting($staffid, $month)
    {
        $result = $this->respond_pedma_feedback($staffid, $month, 2);
        if ($result['success'] && $result['row']) {
            $this->send_pedma_need_meeting_email($staffid, $result['row']);
        }

        return $result;
    }

    public function send_pedma_need_meeting_email($staffid, $row)
    {
        $staffid = (int) $staffid;
        $staff = $this->db->select('team_manage, email, firstname, lastname')->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
        if (!$staff) {
            return false;
        }

        $manager_id = (int) $staff->team_manage;
        $manager_email = $manager_id > 0 ? get_staff_email_id($manager_id) : '';
        if (empty($manager_email)) {
            $manager_email = 'hr@tech2globe.com';
        }

        $time  = strtotime($row->date_created);
        $year  = date('Y', $time);
        $month = date('F', $time);
        $ascore = (float) $row->avg_score;
        $fscore = !empty($row->fatal_error_score) ? ((float) $row->fatal_error_score / 100) * $ascore : 0;
        $addscore = !empty($row->add_on_score) ? ((float) $row->add_on_score / 100) * $ascore : 0;
        $nscore = number_format($ascore - $fscore + $addscore, 2);

        $employee_name = get_staff_full_name($staffid);
        $emp_id = get_staff_emp_id($staffid);

        $message = '<p>Hello,</p>';
        $message .= '<p><b>' . html_escape($employee_name) . '</b> has requested a meeting regarding their PEDMA feedback and is not accepting it as-is.</p>';
        $message .= '<p><b>Employee Name:</b> ' . html_escape($employee_name) . '</p>';
        $message .= '<p><b>Employee ID:</b> ' . html_escape((string) $emp_id) . '</p>';
        $message .= '<p><b>Month:</b> ' . html_escape($month . ' ' . $year) . '</p>';
        $message .= '<p><b>Score:</b> ' . html_escape($nscore) . '%</p>';
        $message .= '<p><b>Overall Feedback:</b><br>' . $row->overall_feedback . '</p>';
        $message .= '<p><a href="' . admin_url('staff/manage_pedma') . '">Open Manage PEDMA</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

        return $this->send_pedma_mail(
            $manager_email,
            'PEDMA Need Meeting - ' . $month . ' ' . $year . ' - ' . $employee_name,
            $message,
            ['hr@tech2globe.com', 'sarabjeet@tech2globe.net'],
            get_staff_email_id($staffid)
        );
    }

    /**
     * Previous calendar month in YYYY-MM (PEDMA evaluation cycle).
     */
    public function get_pedma_eval_target_month($asOfDate = null)
    {
        $ts = $asOfDate ? strtotime($asOfDate) : time();
        return date('Y-m', strtotime('first day of previous month', $ts));
    }

    /**
     * Active team members assigned to a manager who still need published PEDMA for a month.
     */
    public function get_manager_pending_pedma_team($manager_id, $monthYm = null)
    {
        $manager_id = (int) $manager_id;
        if ($manager_id <= 0) {
            return [];
        }

        if ($monthYm === null || !preg_match('/^\d{4}-\d{2}$/', $monthYm)) {
            $monthYm = $this->get_pedma_eval_target_month();
        }

        $this->db->select('staffid, firstname, lastname, email, staff_identifi');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('active', 1);
        $this->db->where('team_manage', $manager_id);
        $team = $this->db->get()->result_array();
        if (empty($team)) {
            return [];
        }

        $pending = [];
        foreach ($team as $member) {
            $staffid = (int) $member['staffid'];
            $this->db->from(db_prefix() . 'staff_performance');
            $this->db->where('staffid', $staffid);
            $this->db->where("DATE_FORMAT(date_created, '%Y-%m')=", $monthYm);
            $this->db->where('status', 1); // published only counts as filled
            $exists = $this->db->count_all_results() > 0;
            if (!$exists) {
                $pending[] = [
                    'staffid'        => $staffid,
                    'name'           => trim($member['firstname'] . ' ' . $member['lastname']),
                    'email'          => $member['email'],
                    'staff_identifi' => $member['staff_identifi'],
                ];
            }
        }

        return $pending;
    }

    /**
     * Managers (role family) who still have pending PEDMA evaluations for a month.
     */
    public function get_managers_with_pending_pedma_eval($monthYm = null)
    {
        if ($monthYm === null || !preg_match('/^\d{4}-\d{2}$/', $monthYm)) {
            $monthYm = $this->get_pedma_eval_target_month();
        }

        $this->db->select('staffid, firstname, lastname, email, role');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('active', 1);
        $this->db->where('email !=', '');
        $staffRows = $this->db->get()->result_array();

        $managers = [];
        foreach ($staffRows as $row) {
            $staffid = (int) $row['staffid'];
            if (!function_exists('can_evaluate_pedma') || !can_evaluate_pedma($staffid)) {
                continue;
            }
            // Remind managers with a team; skip pure admins with no assignees
            $pending = $this->get_manager_pending_pedma_team($staffid, $monthYm);
            if (empty($pending)) {
                continue;
            }
            $managers[] = [
                'staffid'  => $staffid,
                'name'     => trim($row['firstname'] . ' ' . $row['lastname']),
                'email'    => $row['email'],
                'role'     => $row['role'],
                'pending'  => $pending,
                'pending_count' => count($pending),
                'month'    => $monthYm,
            ];
        }

        return $managers;
    }

    public function send_pedma_evaluation_reminder_email($manager, $dayOfMonth)
    {
        if (empty($manager['email']) || empty($manager['pending'])) {
            return false;
        }

        $monthYm = $manager['month'];
        $monthLabel = date('F Y', strtotime($monthYm . '-01'));
        $pendingCount = (int) $manager['pending_count'];
        $evalUrl = admin_url('staff/pedma_admin');

        $listHtml = '<ul>';
        foreach ($manager['pending'] as $member) {
            $listHtml .= '<li>' . html_escape($member['name']);
            if (!empty($member['staff_identifi'])) {
                $listHtml .= ' (#' . html_escape($member['staff_identifi']) . ')';
            }
            $listHtml .= '</li>';
        }
        $listHtml .= '</ul>';

        $subject = 'PEDMA Evaluation Reminder (' . $monthLabel . ') - ' . $pendingCount . ' pending';
        $message = '<p>Dear ' . html_escape($manager['name']) . ',</p>';
        $message .= '<p>This is a reminder to complete <b>PEDMA Evaluation</b> for <b>' . html_escape($monthLabel) . '</b>.</p>';
        $message .= '<p>You still have <b>' . $pendingCount . '</b> team member(s) without a published evaluation:</p>';
        $message .= $listHtml;
        if ((int) $dayOfMonth === 1) {
            $message .= '<p><b>Schedule:</b> Reminder day 1 of the month.</p>';
        } elseif ((int) $dayOfMonth === 5) {
            $message .= '<p><b>Schedule:</b> Reminder day 5 of the month.</p>';
        } elseif ((int) $dayOfMonth === 10) {
            $message .= '<p><b>Schedule:</b> Final email reminder for this month. After today, Workroom will show a daily popup until evaluations are completed (no further emails).</p>';
        }
        $message .= '<p><a href="' . $evalUrl . '">Open PEDMA Evaluation</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

        return $this->send_pedma_mail(
            $manager['email'],
            $subject,
            $message,
            ['hr@tech2globe.com', 'sarabjeet@tech2globe.net']
        );
    }

    /**
     * PEDMA grade bands for dashboard filtering.
     * D is below 70 (user scale A+/A/B/C then D).
     */
    public function pedma_grade_score_bounds($grade)
    {
        $grade = strtoupper(trim((string) $grade));
        $map = [
            'A+' => [98, 100],
            'A'  => [90, 97.999],
            'B'  => [80, 89.999],
            'C'  => [70, 79.999],
            'D'  => [0, 69.999],
        ];

        return isset($map[$grade]) ? $map[$grade] : null;
    }

    public function compute_pedma_final_score_row($row)
    {
        $ascore = (float) (is_array($row) ? ($row['avg_score'] ?? 0) : ($row->avg_score ?? 0));
        $fatal = is_array($row) ? ($row['fatal_error_score'] ?? null) : ($row->fatal_error_score ?? null);
        $addon = is_array($row) ? ($row['add_on_score'] ?? null) : ($row->add_on_score ?? null);
        $fscore = !empty($fatal) ? (((float) $fatal) / 100) * $ascore : 0;
        $addscore = !empty($addon) ? (((float) $addon) / 100) * $ascore : 0;

        return round($ascore - $fscore + $addscore, 2);
    }

    public function extract_pedma_hr_notes($kra_data_json)
    {
        $notes = [];
        if (!$kra_data_json) {
            return '';
        }
        $kra = json_decode($kra_data_json, true);
        if (!is_array($kra)) {
            return '';
        }
        foreach ($kra as $item) {
            if (!empty($item['hr_remarks'])) {
                $prefix = !empty($item['name']) ? ($item['name'] . ': ') : '';
                $notes[] = $prefix . $item['hr_remarks'];
            }
            if (!empty($item['kpiData']) && is_array($item['kpiData'])) {
                foreach ($item['kpiData'] as $kpi) {
                    if (!empty($kpi['hr_remarks'])) {
                        $prefix = !empty($kpi['name']) ? ($kpi['name'] . ': ') : '';
                        $notes[] = $prefix . $kpi['hr_remarks'];
                    }
                }
            }
        }

        return implode(' | ', $notes);
    }

    public function pedma_score_to_grade($score)
    {
        $score = (float) $score;
        if ($score >= 98) {
            return 'A+';
        }
        if ($score >= 90) {
            return 'A';
        }
        if ($score >= 80) {
            return 'B';
        }
        if ($score >= 70) {
            return 'C';
        }

        return 'D';
    }

    /**
     * List published PEDMA rows within month range.
     * Optional grade filter; optional department filter.
     * Empty grade = all scores.
     */
    public function get_pedma_rows_by_grade($grade, $fromMonth, $toMonth, $departmentId = '', $staffActive = '1')
    {
        $grade = strtoupper(trim((string) $grade));
        $bounds = $grade !== '' ? $this->pedma_grade_score_bounds($grade) : [0, 100];

        if ($bounds === null || !preg_match('/^\d{4}-\d{2}$/', $fromMonth) || !preg_match('/^\d{4}-\d{2}$/', $toMonth)) {
            return [];
        }

        // Department listing requires a department (or grade for admin/HR).
        if ($grade === '' && ($departmentId === '' || $departmentId === null)) {
            return [];
        }

        if ($fromMonth > $toMonth) {
            $tmp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $tmp;
        }

        $fromDate = $fromMonth . '-01';
        $toDate = date('Y-m-t', strtotime($toMonth . '-01'));

        $this->db->select('p.id, p.staffid, p.avg_score, p.fatal_error_score, p.add_on_score, p.kra_data, p.overall_feedback, p.date_created, p.status, s.firstname, s.lastname, s.email, s.staff_identifi, s.active');
        $this->db->from('tblstaff_performance p');
        $this->db->join('tblstaff s', 's.staffid = p.staffid', 'inner');
        $this->db->where('p.status !=', 0);
        if ($staffActive === '0' || $staffActive === '1') {
            $this->db->where('s.active', (int) $staffActive);
        }
        $this->db->where('p.date_created >=', $fromDate);
        $this->db->where('p.date_created <=', $toDate);

        if ($departmentId !== '' && $departmentId !== null) {
            $this->db->where('p.staffid IN (SELECT staffid FROM tblstaff_departments WHERE departmentid = ' . (int) $departmentId . ')', null, false);
        }

        $this->db->order_by('p.date_created', 'DESC');
        $rows = $this->db->get()->result_array();

        $min = $bounds[0];
        $max = $bounds[1];
        $out = [];
        $deptCache = [];

        foreach ($rows as $row) {
            $score = $this->compute_pedma_final_score_row($row);
            if ($score < $min || $score > $max) {
                continue;
            }

            $sid = (int) $row['staffid'];
            if (!isset($deptCache[$sid])) {
                $deptRows = $this->db->select('d.name')
                    ->from('tblstaff_departments sd')
                    ->join('tbldepartments d', 'd.departmentid = sd.departmentid', 'inner')
                    ->where('sd.staffid', $sid)
                    ->get()
                    ->result_array();
                $names = array_column($deptRows, 'name');
                $deptCache[$sid] = implode(', ', $names);
            }

            $rowGrade = $this->pedma_score_to_grade($score);
            $out[] = [
                'performance_id' => (int) $row['id'],
                'staffid'        => $sid,
                'emp_id'         => $row['staff_identifi'] !== null && $row['staff_identifi'] !== '' ? $row['staff_identifi'] : (string) $sid,
                'emp_name'       => trim($row['firstname'] . ' ' . $row['lastname']),
                'email'          => $row['email'],
                'department'     => $deptCache[$sid],
                'score'          => number_format($score, 2, '.', ''),
                'grade'          => $grade !== '' ? $grade : $rowGrade,
                'hr_notes'       => $this->extract_pedma_hr_notes($row['kra_data']),
                'month'          => date('Y-m', strtotime($row['date_created'])),
                'month_label'    => date('F Y', strtotime($row['date_created'])),
                'date_created'   => $row['date_created'],
            ];
        }

        return $out;
    }

    public function send_pedma_schedule_meeting_email($staffid, $monthLabel, $score, $grade)
    {
        $staff = $this->get($staffid);
        if (!$staff || empty($staff->email)) {
            return false;
        }

        $subject = 'PEDMA Meeting Scheduled - ' . $monthLabel . ' (Grade ' . $grade . ')';
        $message = '<p>Dear ' . html_escape(trim($staff->firstname . ' ' . $staff->lastname)) . ',</p>';
        $message .= '<p>HR/Admin has requested a PEDMA discussion meeting for <b>' . html_escape($monthLabel) . '</b>.</p>';
        $message .= '<p><b>Score:</b> ' . html_escape($score) . '% &nbsp;|&nbsp; <b>Grade:</b> ' . html_escape($grade) . '</p>';
        $message .= '<p>Please coordinate a suitable meeting slot with your manager / HR.</p>';
        $message .= '<p><a href="' . admin_url('staff/pedma') . '">Open your PEDMA</a></p>';
        $message .= '<p><em>Kind Regards,<br>Tech2globe Workroom</em></p>';

        return $this->send_pedma_mail(
            $staff->email,
            $subject,
            $message,
            ['hr@tech2globe.com']
        );
    }

    /**
     * Ensure override table exists (safe on live if migration not run yet).
     */
    public function ensure_earned_leave_override_table()
    {
        $table = db_prefix() . 'staff_earned_leave_override';
        if ($this->db->table_exists($table)) {
            return $table;
        }

        $this->db->query('CREATE TABLE `' . $table . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `staff_id` INT(11) NOT NULL,
            `month` TINYINT(2) NOT NULL,
            `year` SMALLINT(4) NOT NULL,
            `earned_days` DECIMAL(6,2) NOT NULL DEFAULT 0,
            `updated_by` INT(11) NOT NULL DEFAULT 0,
            `date_updated` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `staff_month_year` (`staff_id`, `month`, `year`),
            KEY `idx_year_month` (`year`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        return $table;
    }

    /**
     * @param array $staff_ids
     * @param int   $month
     * @param int   $year
     * @return array<int,float> staff_id => earned_days
     */
    public function get_earned_leave_overrides_batch(array $staff_ids, $month, $year)
    {
        $staff_ids = array_values(array_filter(array_map('intval', $staff_ids)));
        $month = (int) $month;
        $year = (int) $year;
        if (empty($staff_ids) || $month < 1 || $month > 12 || $year < 2000) {
            return [];
        }

        $table = $this->ensure_earned_leave_override_table();
        $rows = $this->db->select('staff_id, earned_days')
            ->from($table)
            ->where_in('staff_id', $staff_ids)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['staff_id']] = (float) $row['earned_days'];
        }

        return $map;
    }

    /**
     * Save manual earned leave for one staff/month.
     */
    public function save_earned_leave_override($staff_id, $month, $year, $earned_days, $updated_by = 0)
    {
        $staff_id = (int) $staff_id;
        $month = (int) $month;
        $year = (int) $year;
        $earned_days = round((float) $earned_days, 2);
        $updated_by = (int) ($updated_by ?: get_staff_user_id());

        if ($staff_id <= 0 || $month < 1 || $month > 12 || $year < 2000 || $earned_days < 0) {
            return false;
        }

        $table = $this->ensure_earned_leave_override_table();
        $existing = $this->db->where('staff_id', $staff_id)
            ->where('month', $month)
            ->where('year', $year)
            ->get($table)
            ->row();

        $payload = [
            'earned_days' => $earned_days,
            'updated_by' => $updated_by,
            'date_updated' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update($table, $payload);
            return true;
        }

        $payload['staff_id'] = $staff_id;
        $payload['month'] = $month;
        $payload['year'] = $year;
        $this->db->insert($table, $payload);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Bulk save same earned leave for multiple staff.
     *
     * @return int number saved
     */
    public function bulk_save_earned_leave_overrides(array $staff_ids, $month, $year, $earned_days, $updated_by = 0)
    {
        $saved = 0;
        foreach (array_unique(array_map('intval', $staff_ids)) as $staff_id) {
            if ($staff_id > 0 && $this->save_earned_leave_override($staff_id, $month, $year, $earned_days, $updated_by)) {
                $saved++;
            }
        }

        return $saved;
    }
}
