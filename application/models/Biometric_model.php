<?php



defined('BASEPATH') or exit('No direct script access allowed');

	

class Biometric_model extends App_Model

{

  public function __construct()
    {
        parent::__construct();
    }

    public function insert_attendance_bulk($data)
    {
        $inserted_or_updated = false;

    foreach ($data as $row) {
        // Convert "28-Jun-2025" to Y-m-d
        $dateObj = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
        if (!$dateObj) {
            log_message('error', 'Invalid attendance_date: ' . $row['attendance_date']);
            continue;
        }

        $formatted_date = $dateObj->format('d-M-Y'); // Keep format same for consistency with DB
        $row['attendance_date'] = $formatted_date;
        $row['created_at'] = date('Y-m-d H:i:s');

        // Check for existing record
        $this->db->where('employee_code', $row['employee_code']);
        $this->db->where('attendance_date', $formatted_date);
        $existing = $this->db->get(db_prefix() . 'biometric_report')->row();

        if ($existing) {
            // Update existing record
            $this->db->where('id', $existing->id); // assuming `id` is the primary key
            $this->db->update(db_prefix() . 'biometric_report', $row);
        } else {
            // Insert new
            $this->db->insert(db_prefix() . 'biometric_report', $row);
        }

        $inserted_or_updated = true;
    }

    return $inserted_or_updated;
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
public function get_attendance_filtered($limit, $offset, $filters)
{
    $this->db->select('tblbiometric_report.*, tblstaff.firstname, tblstaff.lastname');
		$this->db->from('tblbiometric_report');
		$this->db->join('tblstaff', 'tblstaff.staff_identifi = tblbiometric_report.employee_code', 'left');
		$this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'left');
		// $this->db->group_by(['tblbiometric_report.employee_code', 'tblbiometric_report.attendance_date']);
		if (!empty($filters['department'])) {
			$this->db->where('tblstaff_departments.departmentid', $filters['department']);
		}

		if (!empty($filters['staff'])) {
			$this->db->where('tblstaff.staffid', $filters['staff']);
		}

		if (!empty($filters['month'])) {
			$month = $filters['month']; // e.g. "2025-06"
			$this->db->where("DATE_FORMAT(STR_TO_DATE(attendance_date, '%d-%b-%Y'), '%Y-%m') = " . $this->db->escape($month), null, false);
		}
		$this->db->order_by('tblbiometric_report.created_at', 'ASC');
		$this->db->limit($limit, $offset);

		$query = $this->db->get();
		return $query->result_array();

}


public function get_attendance_filtered_count($filters)
{
    $this->db->from('tblbiometric_report');
    $this->db->join('tblstaff', 'tblstaff.staff_identifi = tblbiometric_report.employee_code', 'left');
    $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'left');

    if (!empty($filters['department'])) {
        $this->db->where('tblstaff_departments.departmentid', $filters['department']);
    }

    if (!empty($filters['staff'])) {
        $this->db->where('tblstaff.staffid', $filters['staff']);
    }

    if (!empty($filters['month'])) {
        $month = $filters['month']; // e.g. "2025-06"
        $this->db->where("DATE_FORMAT(STR_TO_DATE(attendance_date, '%d-%b-%Y'), '%Y-%m') = " . $this->db->escape($month), null, false);
    }

    return $this->db->count_all_results();
}



private function apply_filters($filters)
{
    if (!empty($filters['month'])) {
        $yearMonth = explode('-', $filters['month']);
        if (count($yearMonth) === 2) {
            $this->db->where('MONTH(attendance_date)', (int)$yearMonth[1]);
            $this->db->where('YEAR(attendance_date)', (int)$yearMonth[0]);
        }
    }

    if (!empty($filters['department'])) {
        $this->db->where('tblstaff.department', $filters['department']);
    }

    if (!empty($filters['staff'])) {
        $this->db->where('tblstaff.staffid', $filters['staff']);
    } else {
        $this->db->where('tblstaff.staffid', get_staff_user_id()); // restrict to self unless admin
    }
}

}
