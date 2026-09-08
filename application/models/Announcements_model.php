<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Announcements_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get announcements
     * @param  string $id    optional id
     * @param  array  $where perform where
     * @param  string $limit
     * @return mixed
     */
    public function get($id = '', $where = [], $limit = '')
    {
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('announcementid', $id);

            return $this->db->get(db_prefix() . 'announcements')->row();
        }

        if (count($where) == 0 && $limit == '') {
            $announcements = $this->app_object_cache->get('all-user-announcements');
            if (!$announcements && !is_array($announcements)) {
                $this->_annoucements_query();
                $announcements = $this->db->get(db_prefix() . 'announcements')->result_array();
                $this->app_object_cache->add('all-user-announcements', $announcements);
            }
        } else {
            $this->_annoucements_query();

            if (is_numeric($limit)) {
                $this->db->limit($limit);
            }

            $announcements = $this->db->get(db_prefix() . 'announcements')->result_array();
        }

        return $announcements;
    }

    /**
     * Get total dismissed announcements for logged in user
     * @return mixed
     */
    public function get_total_undismissed_announcements()
    {
        if (!is_logged_in()) {
            return 0;
        }

        $staff  = is_client_logged_in() ? 0 : 1;
        $userid = is_client_logged_in() ? get_contact_user_id() : get_staff_user_id();

        $sql = 'SELECT COUNT(*) as total_undismissed FROM ' . db_prefix() . 'announcements WHERE announcementid NOT IN (SELECT announcementid FROM ' . db_prefix() . 'dismissed_announcements WHERE staff=' . $staff . ' AND userid=' . $userid . ')';
        if ($staff == 1) {
            $sql .= ' AND showtostaff=1';
        } else {
            $sql .= ' AND showtousers=1';
        }

        return $this->db->query($sql)->row()->total_undismissed;
    }

    /**
     * Ensure optional audience columns exist (stores last send targets).
     */
    public function ensure_email_audience_columns()
    {
        if (!$this->db->table_exists(db_prefix() . 'announcements')) {
            return;
        }
        if (!$this->db->field_exists('email_roles', db_prefix() . 'announcements')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'announcements`
                ADD `email_roles` VARCHAR(255) NULL DEFAULT NULL AFTER `userid`');
        }
        if (!$this->db->field_exists('email_departments', db_prefix() . 'announcements')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'announcements`
                ADD `email_departments` VARCHAR(255) NULL DEFAULT NULL AFTER `email_roles`');
        }
    }

    public function ensure_email_roles_column()
    {
        $this->ensure_email_audience_columns();
    }

    /**
     * @param $_POST array
     * @return Insert ID
     * Add new announcement calling this function
     */
    public function add($data)
    {
        $this->ensure_email_audience_columns();

        $data['dateadded'] = date('Y-m-d H:i:s');

        if (isset($data['showname'])) {
            $data['showname'] = 1;
        } else {
            $data['showname'] = 0;
        }
        if (isset($data['showtostaff'])) {
            $data['showtostaff'] = 1;
        } else {
            $data['showtostaff'] = 0;
        }
        if (isset($data['showtousers'])) {
            $data['showtousers'] = 1;
        } else {
            $data['showtousers'] = 0;
        }
        $data['message'] = $data['message'];
        $data['userid']  = get_staff_full_name(get_staff_user_id());

        if (!isset($data['email_roles'])) {
            $data['email_roles'] = null;
        }
        if (!isset($data['email_departments'])) {
            $data['email_departments'] = null;
        }

        $data = hooks()->apply_filters('before_announcement_added', $data);

        $this->db->insert(db_prefix() . 'announcements', $data);
        $insert_id = $this->db->insert_id();

        hooks()->do_action('announcement_created', $insert_id);

        log_activity('New Announcement Added [' . $data['name'] . ']');

        return $insert_id;
    }

    /**
     * @param  $_POST array
     * @param  integer
     * @return boolean
     * This function updates announcement
     */
    public function update($data, $id)
    {
        $this->ensure_email_audience_columns();

        $data['showname']    = isset($data['showname']) ? 1 : 0;
        $data['showtostaff'] = isset($data['showtostaff']) ? 1 : 0;
        $data['showtousers'] = isset($data['showtousers']) ? 1 : 0;

        $data['message'] = $data['message'];

        $data = hooks()->apply_filters('before_announcement_updated', $data, $id);

        $this->db->where('announcementid', $id);
        $this->db->update(db_prefix() . 'announcements', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('announcement_updated', $id);

            log_activity('Announcement Updated [' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Send announcement email to staff by department + role selection.
     *
     * @param object $announcement
     * @param array  $email_roles
     * @param array  $email_departments
     * @return array{sent:int,no_recipients:bool,failed:bool}
     */
    public function send_announcement_emails($announcement, $email_roles = [], $email_departments = [])
    {
        $result = ['sent' => 0, 'no_recipients' => false, 'failed' => false];

        if (!$announcement || empty($email_roles) || empty($email_departments)) {
            $result['no_recipients'] = true;
            return $result;
        }

        $emails = $this->get_staff_emails_for_audience($email_roles, $email_departments);
        if (empty($emails)) {
            $result['no_recipients'] = true;
            return $result;
        }

        $from_email = get_option('smtp_email');
        if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
            $from_email = 'noreply@t2gworkroom.com';
        }
        $from_name = get_option('companyname') ?: 'Tech2globe Workroom';

        $subject = (string) $announcement->name;
        $body    = '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5;">';
        $body   .= '<p><strong>' . html_escape($subject) . '</strong></p>';
        $body   .= $announcement->message;
        if (!empty($announcement->showname) && !empty($announcement->userid)) {
            $body .= '<p style="margin-top:20px;color:#666;"><em>' . _l('announcement_from') . ' '
                . html_escape($announcement->userid) . '</em></p>';
        }
        $body .= '<p style="margin-top:16px;"><a href="' . admin_url('announcements/view/' . $announcement->announcementid) . '">View announcement in Workroom</a></p>';
        $body .= '</div>';

        $this->load->library('email');

        $chunks = array_chunk($emails, 40);
        $any_ok = false;
        foreach ($chunks as $chunk) {
            try {
                $this->email->clear(true);
                $this->email->initialize();
                $this->email->set_mailtype('html');
                $this->email->from($from_email, $from_name);
                $this->email->to($from_email);
                $this->email->bcc($chunk);
                $this->email->subject($subject);
                $this->email->message($body);
                if ($this->email->send(false)) {
                    $result['sent'] += count($chunk);
                    $any_ok = true;
                } else {
                    log_activity('Announcement email batch failed: ' . $this->email->print_debugger(['headers']));
                    $result['failed'] = true;
                }
            } catch (Exception $e) {
                log_activity('Announcement email exception: ' . $e->getMessage());
                $result['failed'] = true;
            }
        }

        if (!$any_ok && $result['sent'] === 0) {
            $result['failed'] = true;
        }

        return $result;
    }

    /**
     * Active staff emails matching department + role filters.
     *
     * @param array $email_roles
     * @param array $email_departments
     * @return array
     */
    public function get_staff_emails_for_audience($email_roles, $email_departments)
    {
        $email_roles = array_values(array_filter(array_map('strval', (array) $email_roles)));
        $email_departments = array_values(array_filter(array_map('strval', (array) $email_departments)));

        if (empty($email_roles) || empty($email_departments)) {
            return [];
        }

        $this->db->distinct();
        $this->db->select(db_prefix() . 'staff.email');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where(db_prefix() . 'staff.active', 1);
        $this->db->where(db_prefix() . 'staff.email !=', '');
        $this->db->where(db_prefix() . 'staff.email IS NOT NULL', null, false);

        if (!in_array('all', $email_departments, true)) {
            $department_ids = array_values(array_filter(array_map('intval', $email_departments)));
            if (empty($department_ids)) {
                return [];
            }
            $this->db->join(
                db_prefix() . 'staff_departments',
                db_prefix() . 'staff_departments.staffid = ' . db_prefix() . 'staff.staffid',
                'inner'
            );
            $this->db->where_in(db_prefix() . 'staff_departments.departmentid', $department_ids);
        }

        if (!in_array('all', $email_roles, true)) {
            $role_ids = array_values(array_filter(array_map('intval', $email_roles)));
            if (empty($role_ids)) {
                return [];
            }
            $this->db->where_in(db_prefix() . 'staff.role', $role_ids);
        }

        $rows = $this->db->get()->result_array();
        $emails = [];
        foreach ($rows as $row) {
            $email = trim((string) $row['email']);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * @deprecated Use get_staff_emails_for_audience()
     */
    public function get_staff_emails_for_roles($email_roles)
    {
        return $this->get_staff_emails_for_audience($email_roles, ['all']);
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Announcement
     * All Dimissed announcements from database will be cleaned
     */
    public function delete($id)
    {
        hooks()->do_action('before_delete_announcement', $id);

        $this->db->where('announcementid', $id);
        $this->db->delete(db_prefix() . 'announcements');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('announcementid', $id);
            $this->db->delete(db_prefix() . 'dismissed_announcements');

            hooks()->do_action('announcement_deleted', $id);

            log_activity('Announcement Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function set_announcements_as_read_except_last_one($user_id, $staff = false)
    {
        $lastAnnouncement = $this->db->query('SELECT announcementid FROM ' . db_prefix() . 'announcements WHERE ' . (!$staff ? 'showtousers' : 'showtostaff') . ' = 1 AND announcementid = (SELECT MAX(announcementid) FROM ' . db_prefix() . 'announcements)')->row();
        if ($lastAnnouncement) {
            // Get all announcements and set it to read.
            $this->db->select('announcementid')
                ->from(db_prefix() . 'announcements')
                ->where((!$staff ? 'showtousers' : 'showtostaff'), 1)
                ->where('announcementid !=', $lastAnnouncement->announcementid);

            $announcements = $this->db->get()->result_array();
            foreach ($announcements as $announcement) {
                $this->db->insert(db_prefix() . 'dismissed_announcements', [
                        'announcementid' => $announcement['announcementid'],
                        'staff'          => (bool) $staff,
                        'userid'         => $user_id,
                    ]);
            }
        }
    }

    private function _annoucements_query()
    {
        if (is_client_logged_in()) {
            $this->db->where('showtousers', 1);
        } elseif (is_staff_logged_in()) {
            $this->db->where('showtostaff', 1);
        }
        $this->db->order_by('dateadded', 'desc');
    }
}
