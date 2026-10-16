<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sop_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('announcements_model');
        $this->ensure_schema();
    }

    public function ensure_schema()
    {
        $table = db_prefix() . 'announcements';
        if (!$this->db->table_exists($table)) {
            return;
        }
        if (method_exists($this->announcements_model, 'ensure_email_audience_columns')) {
            $this->announcements_model->ensure_email_audience_columns();
        }
        $afterCol = $this->db->field_exists('email_departments', $table) ? 'email_departments' : 'userid';
        if (!$this->db->field_exists('email_staffids', $table)) {
            $this->db->query('ALTER TABLE `' . $table . '`
                ADD `email_staffids` VARCHAR(500) NULL DEFAULT NULL AFTER `' . $afterCol . '`');
            $afterCol = 'email_staffids';
        }
        if (!$this->db->field_exists('is_sop', $table)) {
            $afterCol = $this->db->field_exists('email_staffids', $table) ? 'email_staffids' : $afterCol;
            $this->db->query('ALTER TABLE `' . $table . '`
                ADD `is_sop` TINYINT(1) NOT NULL DEFAULT 0 AFTER `' . $afterCol . '`');
        }
    }

    /**
     * @return object|null
     */
    public function get($id)
    {
        $this->db->where('is_sop', 1);
        $this->db->where('announcementid', (int) $id);

        return $this->db->get(db_prefix() . 'announcements')->row();
    }

    /**
     * SOPs visible to the current user (sent list for admins, inbox for recipients).
     */
    public function get_for_user($staffId = null)
    {
        $staffId = $staffId ? (int) $staffId : (int) get_staff_user_id();
        $rows = $this->db->where('is_sop', 1)
            ->order_by('dateadded', 'DESC')
            ->get(db_prefix() . 'announcements')
            ->result();

        if (is_admin()) {
            return $rows;
        }

        $visible = [];
        foreach ($rows as $row) {
            if ($this->staff_is_recipient($staffId, $row)) {
                $visible[] = $row;
            }
        }

        return $visible;
    }

    /**
     * Unread SOP count for staff (recipient-only, uses dismissed_announcements as read state).
     */
    public function count_unread_for_staff($staffId = null)
    {
        $staffId = (int) ($staffId ?: get_staff_user_id());
        if ($staffId < 1 || !$this->db->field_exists('is_sop', db_prefix() . 'announcements')) {
            return 0;
        }

        $dismissed = $this->get_dismissed_announcement_ids_for_staff($staffId);
        $rows = $this->db->where('is_sop', 1)
            ->order_by('dateadded', 'DESC')
            ->get(db_prefix() . 'announcements')
            ->result();

        $count = 0;
        foreach ($rows as $row) {
            if (!$this->staff_is_recipient($staffId, $row)) {
                continue;
            }
            if (!in_array((int) $row->announcementid, $dismissed, true)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Mark one SOP as read for the current staff member (recipients only).
     */
    public function mark_as_read($announcementId, $staffId = null)
    {
        $announcementId = (int) $announcementId;
        $staffId = (int) ($staffId ?: get_staff_user_id());
        if ($announcementId < 1 || $staffId < 1) {
            return false;
        }

        $sop = $this->get($announcementId);
        if (!$sop || !$this->staff_is_recipient($staffId, $sop)) {
            return false;
        }

        $exists = (int) $this->db->where('announcementid', $announcementId)
            ->where('staff', 1)
            ->where('userid', $staffId)
            ->count_all_results(db_prefix() . 'dismissed_announcements');

        if ($exists > 0) {
            return true;
        }

        $this->db->insert(db_prefix() . 'dismissed_announcements', [
            'announcementid' => $announcementId,
            'staff'          => 1,
            'userid'         => $staffId,
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * @return int[]
     */
    private function get_dismissed_announcement_ids_for_staff($staffId)
    {
        $staffId = (int) $staffId;
        if ($staffId < 1) {
            return [];
        }

        $rows = $this->db->select('announcementid')
            ->from(db_prefix() . 'dismissed_announcements')
            ->where('staff', 1)
            ->where('userid', $staffId)
            ->get()
            ->result_array();

        return array_values(array_map('intval', array_column($rows, 'announcementid')));
    }

    public function staff_is_recipient($staffId, $sop)
    {
        $staffId = (int) $staffId;
        if ($staffId < 1 || !$sop) {
            return false;
        }

        $depts = $this->parse_csv_or_all($sop->email_departments ?? 'all');
        $staffIds = $this->parse_csv_or_all($sop->email_staffids ?? 'all');

        if (in_array('all', $staffIds, true)) {
            if (in_array('all', $depts, true)) {
                return $this->staff_is_active($staffId);
            }

            return $this->staff_in_departments($staffId, $depts);
        }

        return in_array((string) $staffId, array_map('strval', $staffIds), true);
    }

    private function staff_is_active($staffId)
    {
        return (int) $this->db->where('staffid', (int) $staffId)->where('active', 1)
            ->count_all_results(db_prefix() . 'staff') > 0;
    }

    private function staff_in_departments($staffId, array $departmentIds)
    {
        $departmentIds = array_values(array_filter(array_map('intval', $departmentIds)));
        if (empty($departmentIds)) {
            return false;
        }

        return (int) $this->db->where('staffid', (int) $staffId)
            ->where_in('departmentid', $departmentIds)
            ->count_all_results(db_prefix() . 'staff_departments') > 0;
    }

    /**
     * @param array $email_departments
     * @param array $email_staffids
     * @return int[]
     */
    public function resolve_recipient_staff_ids(array $email_departments, array $email_staffids)
    {
        $email_departments = array_values(array_filter(array_map('strval', $email_departments)));
        $email_staffids = array_values(array_filter(array_map('strval', $email_staffids)));
        if (empty($email_departments) || empty($email_staffids)) {
            return [];
        }

        $this->db->distinct();
        $this->db->select(db_prefix() . 'staff.staffid');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where(db_prefix() . 'staff.active', 1);

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

        if (!in_array('all', $email_staffids, true)) {
            $ids = array_values(array_filter(array_map('intval', $email_staffids)));
            if (empty($ids)) {
                return [];
            }
            $this->db->where_in(db_prefix() . 'staff.staffid', $ids);
        }

        $rows = $this->db->get()->result_array();
        $out = [];
        foreach ($rows as $row) {
            $out[] = (int) $row['staffid'];
        }

        return array_values(array_unique($out));
    }

    /**
     * @return array{sent:int,no_recipients:bool,failed:bool}
     */
    public function send_sop_emails($sop, array $email_departments, array $email_staffids)
    {
        $result = ['sent' => 0, 'no_recipients' => false, 'failed' => false];
        if (!$sop) {
            $result['no_recipients'] = true;

            return $result;
        }

        $staffIds = $this->resolve_recipient_staff_ids($email_departments, $email_staffids);
        if (empty($staffIds)) {
            $result['no_recipients'] = true;

            return $result;
        }

        $this->db->select('email');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where_in('staffid', $staffIds);
        $this->db->where('active', 1);
        $rows = $this->db->get()->result_array();
        $emails = [];
        foreach ($rows as $row) {
            $email = trim((string) $row['email']);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        $emails = array_values(array_unique($emails));
        if (empty($emails)) {
            $result['no_recipients'] = true;

            return $result;
        }

        $from_email = get_option('smtp_email');
        if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
            $from_email = 'noreply@t2gworkroom.com';
        }
        $from_name = get_option('companyname') ?: 'Tech2globe Workroom';

        $subject = (string) $sop->name;
        $body = '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5;">';
        $body .= '<p><strong>' . html_escape($subject) . '</strong></p>';
        $body .= nl2br(html_escape((string) $sop->message));
        $body .= '<p style="margin-top:16px;"><a href="' . admin_url('sop/view/' . $sop->announcementid) . '">View SOP in Workroom</a></p>';
        $body .= '</div>';

        $this->load->library('email');
        $chunks = array_chunk($emails, 40);
        $any_ok = false;
        foreach ($chunks as $chunk) {
            try {
                $this->email->clear(true);
                $this->email->from($from_email, $from_name);
                $this->email->to($chunk);
                $this->email->subject($subject);
                $this->email->message($body);
                if ($this->email->send()) {
                    $result['sent'] += count($chunk);
                    $any_ok = true;
                } else {
                    log_activity('SOP email batch failed: ' . $this->email->print_debugger(['headers']));
                    $result['failed'] = true;
                }
            } catch (Exception $e) {
                log_activity('SOP email exception: ' . $e->getMessage());
                $result['failed'] = true;
            }
        }

        if (!$any_ok && $result['sent'] === 0) {
            $result['failed'] = true;
        }

        return $result;
    }

    public function add($data, array $email_departments, array $email_staffids)
    {
        $data['is_sop'] = 1;
        unset($data['showtostaff'], $data['showtousers']);
        $data['showname'] = 1;
        $data['email_departments'] = in_array('all', $email_departments, true)
            ? 'all'
            : implode(',', $email_departments);
        $data['email_staffids'] = in_array('all', $email_staffids, true)
            ? 'all'
            : implode(',', $email_staffids);
        $data['email_roles'] = 'all';

        return $this->announcements_model->add($data);
    }

    /**
     * Staff options for compose modal (optionally filtered by departments).
     *
     * @param array $departmentIds empty or contains 'all'
     */
    public function get_staff_options(array $departmentIds = [])
    {
        $this->db->select('s.staffid, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->where('s.active', 1);
        $this->db->order_by('s.firstname', 'ASC');
        $this->db->order_by('s.lastname', 'ASC');

        $departmentIds = array_values(array_filter(array_map('strval', $departmentIds)));
        if (!empty($departmentIds) && !in_array('all', $departmentIds, true)) {
            $ids = array_values(array_filter(array_map('intval', $departmentIds)));
            if (!empty($ids)) {
                $this->db->join(db_prefix() . 'staff_departments sd', 'sd.staffid = s.staffid', 'inner');
                $this->db->where_in('sd.departmentid', $ids);
                $this->db->group_by('s.staffid, s.firstname, s.lastname, s.email');
            }
        }

        $rows = $this->db->get()->result();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'staffid' => (int) $row->staffid,
                'name'    => trim($row->firstname . ' ' . $row->lastname),
                'email'   => (string) $row->email,
            ];
        }

        return $out;
    }

    private function parse_csv_or_all($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === 'all') {
            return ['all'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
