<?php

class Interval_model extends CI_Model
{
    /** Snapshot screenshot interval for all staff (H:i:s duration). */
    private const CAPTURE_INTERVAL_TIME = '00:10:00';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_staff($email)
    {
        $this->db->where('tblstaff.email', $email);
        $this->db->select('tblstaff.staffid,tblstaff.email,tbltimeinterval.time_interval');
        $this->db->from('tblstaff');
        $this->db->join('tbltimeinterval', 'tbltimeinterval.staffid = tblstaff.staffid', 'left');
        $query = $this->db->get();

        return $this->normalize_interval_rows($query->result());
    }

    public function get_all_staff()
    {
        $this->db->select('tblstaff.staffid,tblstaff.email,tbltimeinterval.time_interval');
        $this->db->from('tblstaff');
        $this->db->join('tbltimeinterval', 'tbltimeinterval.staffid = tblstaff.staffid', 'left');
        $this->db->where('tblstaff.active', 1);
        $query = $this->db->get();

        return $this->normalize_interval_rows($query->result());
    }

    /**
     * Desktop client reads time_interval as seconds-between-captures (via H:i:s).
     */
    private function normalize_interval_rows($rows)
    {
        foreach ($rows as $row) {
            $stored = isset($row->time_interval) ? trim((string) $row->time_interval) : '';
            if ($stored === '' || $stored === '00:00:00') {
                $row->time_interval = self::CAPTURE_INTERVAL_TIME;
            }
        }

        return $rows;
    }
}
