<?php

class Staff_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }


  function login_check_password($email = null, $password = null)
  {
     if ($email === null || $email === '') {
         $email = $this->input->post('email');
     }
     if ($password === null || $password === '') {
         $password = $this->input->post('password');
     }
     $email = trim((string) $email);
     $password = (string) $password;

     $this->db->where('active', 1);
    $this->db->where('email', $email);
    $query = $this->db->get('tblstaff');
    $row = $query->row();
    if (!$row) {
        return false;
    }
    $staff_id = $row->staffid;
    $hash = $row->password;
        if ($this->workroom_password_matches($password, $hash)) {
			//echo "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 200 MINUTE)";die;
             //$this->db->where(date('Y-m-d','date'), $date);
               // $query = "select DATE_FORMAT(`date`,'%Y-%m-%d') AS date ,staff_id,type_check from `tblcheck_in_out` where staff_id = '".$staff_id."' AND DATE_FORMAT(`date`,'%Y-%m-%d') ='".$date."'";
               // $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE)";
				 //$query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE )";
				 $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = ? AND `date` >= DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 20 HOUR) ORDER by date desc LIMIT 1";
                $rows = $this->db->query($query, array($staff_id))->result_array();
                if ($this->has_open_biometric_checkin($staff_id)) {
                    return array(array(
                        'date' => date('Y-m-d H:i:s'),
                        'staff_id' => $staff_id,
                        'type_check' => 1,
                    ));
                }
                if (!empty($rows)) {
                    return $rows;
                }
                return array();
            //return TRUE;
        }
        else {
           return FALSE;
        }
      

    }

    /**
     * Workroom stores phpass hashes ($2a$ or $P$). password_verify alone rejects a correct password.
     */
    function workroom_password_matches($password, $hash)
    {
        $password = (string) $password;
        $hash = (string) $hash;
        if ($password === '' || $hash === '') {
            return false;
        }

        if (!isset($this->passwordhash)) {
            $this->load->library('passwordhash');
        }
        if ($this->passwordhash && $this->passwordhash->CheckPassword($password, $hash)) {
            return true;
        }

        return function_exists('password_verify') && password_verify($password, $hash);
    }

    /**
     * Snapshot only reads tblcheck_in_out. A biometric punch is stored on
     * tblbiometric_report and must count as checked in while the latest swipe is IN.
     */
    function has_open_biometric_checkin($staff_id)
    {
        $codes = $this->db->query(
            'SELECT TRIM(COALESCE(s.staff_identifi, "")) AS identifi,
                    TRIM(COALESCE(i.empid, "")) AS empid
             FROM tblstaff s
             LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
             WHERE s.staffid = ?
             LIMIT 1',
            array((int) $staff_id)
        )->row_array();

        if (!$codes) {
            return false;
        }

        $employee_codes = array();
        foreach (array('identifi', 'empid') as $key) {
            $code = trim((string) ($codes[$key] ?? ''));
            if ($code !== '' && !in_array($code, $employee_codes, true)) {
                $employee_codes[] = $code;
            }
        }
        if (empty($employee_codes)) {
            return false;
        }

        $dates = array(date('d-M-Y'), date('d-M-Y', strtotime('-1 day')));
        $placeholders = implode(',', array_fill(0, count($employee_codes), '?'));
        $params = array_merge($dates, $employee_codes);
        $rows = $this->db->query(
            'SELECT attendance_date, a_in_time, a_out_time, punch_records
             FROM tblbiometric_report
             WHERE attendance_date IN (?, ?)
               AND TRIM(employee_code) IN (' . $placeholders . ')',
            $params
        )->result_array();

        foreach ($rows as $row) {
            if ($this->biometric_row_is_checked_in($row)) {
                return true;
            }
        }

        return false;
    }

    function biometric_row_is_checked_in($row)
    {
        $is_today = strcasecmp(trim((string) ($row['attendance_date'] ?? '')), date('d-M-Y')) === 0;
        $records = trim((string) ($row['punch_records'] ?? ''));
        if ($records !== '' && preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
            $last = end($matches);
            if (strtolower($last[2]) === 'in') {
                return true;
            }
            if ($is_today) {
                foreach ($matches as $match) {
                    if (strtolower($match[2]) === 'in') {
                        return true;
                    }
                }
            }

            return false;
        }

        $in_time = trim((string) ($row['a_in_time'] ?? ''));
        $out_time = trim((string) ($row['a_out_time'] ?? ''));
        $has_in = $in_time !== '' && $in_time !== '00:00' && $in_time !== '00:00:00' && strtolower($in_time) !== 'nil';
        $has_out = $out_time !== '' && $out_time !== '00:00' && $out_time !== '00:00:00' && strtolower($out_time) !== 'nil';
        if ($is_today && $has_in) {
            return true;
        }

        return $has_in && !$has_out;
    }
}
  

 ?>
