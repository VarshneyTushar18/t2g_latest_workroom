<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suggestions_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_table();
    }

    public function ensure_table()
    {
        if ($this->db->table_exists(db_prefix() . 'staff_suggestions')) {
            return;
        }

        $this->db->query('CREATE TABLE `' . db_prefix() . 'staff_suggestions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `staffid` INT(11) NOT NULL,
            `subject` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "0=new,1=read,2=closed",
            `date_created` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `staffid` (`staffid`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
    }

    public function add($data)
    {
        $insert = [
            'staffid'      => (int) $data['staffid'],
            'subject'      => trim($data['subject']),
            'message'      => trim($data['message']),
            'status'       => 0,
            'date_created' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'staff_suggestions', $insert);
        $id = $this->db->insert_id();

        if ($id) {
            log_activity('New Staff Suggestion [ID: ' . $id . ', StaffID: ' . $insert['staffid'] . ']');
        }

        return $id;
    }

    /**
     * Get all suggestions (or one by id) with staff name/email for admin list.
     * No staff filter — universal inbox for every submitter.
     */
    public function get($id = '')
    {
        $this->db->select(
            db_prefix() . 'staff_suggestions.*, ' .
            'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as staff_name, ' .
            db_prefix() . 'staff.email as staff_email, ' .
            db_prefix() . 'staff.staff_identifi'
        );
        $this->db->from(db_prefix() . 'staff_suggestions');
        $this->db->join(
            db_prefix() . 'staff',
            db_prefix() . 'staff.staffid = ' . db_prefix() . 'staff_suggestions.staffid',
            'left'
        );

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'staff_suggestions.id', $id);

            return $this->db->get()->row();
        }

        $this->db->order_by(db_prefix() . 'staff_suggestions.date_created', 'DESC');

        return $this->db->get()->result_array();
    }

    public function count_new()
    {
        $this->db->where('status', 0);

        return (int) $this->db->count_all_results(db_prefix() . 'staff_suggestions');
    }

    public function update_status($id, $status)
    {
        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'staff_suggestions', ['status' => (int) $status]);

        return $this->db->affected_rows() > 0;
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->delete(db_prefix() . 'staff_suggestions');

        return $this->db->affected_rows() > 0;
    }

    private function notify_admins($id, $suggestion)
    {
        $staff_name = get_staff_full_name($suggestion['staffid']);
        $subject    = 'New Suggestion from ' . $staff_name;
        $link       = admin_url('suggestions');

        $message = '<p><b>New suggestion submitted</b></p>';
        $message .= '<p><b>From:</b> ' . html_escape($staff_name) . '</p>';
        $message .= '<p><b>Subject:</b> ' . html_escape($suggestion['subject']) . '</p>';
        $message .= '<p><b>Message:</b><br>' . nl2br(html_escape($suggestion['message'])) . '</p>';
        $message .= '<p><a href="' . $link . '">View in Workroom</a></p>';

        $this->db->select('email');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('admin', 1);
        $this->db->where('active', 1);
        $admins = $this->db->get()->result_array();

        if (empty($admins)) {
            return;
        }

        $this->load->library('email');
        $this->email->initialize();
        $this->email->set_mailtype('html');
        $this->email->from(get_option('smtp_email') ?: 'noreply@tech2globe.com', get_option('companyname') ?: 'Workroom');

        foreach ($admins as $admin) {
            if (empty($admin['email'])) {
                continue;
            }
            $this->email->clear(true);
            $this->email->set_mailtype('html');
            $this->email->from(get_option('smtp_email') ?: 'noreply@tech2globe.com', get_option('companyname') ?: 'Workroom');
            $this->email->to($admin['email']);
            $this->email->subject($subject);
            $this->email->message($message);
            @$this->email->send();
        }
    }
}
