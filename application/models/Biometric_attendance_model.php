<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Biometric_attendance_model extends CI_Model
{

  public function __construct()
    {
        parent::__construct();
    }
	public function index(){
		
	}

    public function insert_attendance_bulk($data)
    {
        $inserted = false;
        foreach ($data as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'biometric_report', $row);
            if ($this->db->affected_rows() > 0) {
                $inserted = true;
            }
        }
        return $inserted;
    }
	public function get_all_attendance()
{
    $this->db->select('biometric_report.*, tblstaff.firstname, tblstaff.lastname');
    $this->db->from(db_prefix() . 'biometric_report');
    $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staff_identifi = ' . db_prefix() . 'biometric_report.employee_code', 'left');
    $this->db->where(db_prefix() . 'staff.staffid', get_staff_user_id());
	
    return $this->db->get()->result_array();
}

public function get_attendance_paginated($limit, $offset)
{
    $this->db->select('biometric_report.*, tblstaff.firstname, tblstaff.lastname');
    $this->db->from(db_prefix() . 'biometric_report');
    $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staff_identifi = ' . db_prefix() . 'biometric_report.employee_code', 'left');
    $this->db->where(db_prefix() . 'staff.staffid', get_staff_user_id());
    $this->db->limit($limit);
    $this->db->offset($offset);

    return $this->db->get()->result_array();
}

public function get_attendance_count()
{
    $this->db->from(db_prefix() . 'biometric_report');
    $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staff_identifi = ' . db_prefix() . 'biometric_report.employee_code', 'left');
    $this->db->where(db_prefix() . 'staff.staffid', get_staff_user_id());

    return $this->db->count_all_results();
}

}
