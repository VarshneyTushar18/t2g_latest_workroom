<?php

class Staff_model extends CI_Model{

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }


  function login_check_password()
  {      

     $password = $_POST['password'];
     $email =    $_POST['email'];
     
     $this->db->where('active', 1);
    $this->db->where('email', $email);
    $query = $this->db->get('tblstaff');
    $row = $query->row();
    if (!$row) {
        return false;
    }
    $staff_id = $row->staffid;
   // print_r($row);die;
  // $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $email_match = $row->email;
    $date =  date("Y-m-d");
    $hash = $row->password;
        if (password_verify($password, $hash)) {
			//echo "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 200 MINUTE)";die;
             //$this->db->where(date('Y-m-d','date'), $date);
               // $query = "select DATE_FORMAT(`date`,'%Y-%m-%d') AS date ,staff_id,type_check from `tblcheck_in_out` where staff_id = '".$staff_id."' AND DATE_FORMAT(`date`,'%Y-%m-%d') ='".$date."'";
               // $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE)";
				 //$query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = '".$staff_id."' AND `tblcheck_in_out`.`date` >= DATE_ADD(CURDATE(), INTERVAL 400 MINUTE )";
				 $query = "SELECT `date`,`staff_id`,`type_check` FROM `tblcheck_in_out` WHERE staff_id = ? AND `date` >= DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 20 HOUR) ORDER by date desc LIMIT 1";
                $rows = $this->db->query($query, array($staff_id))->result_array();
                if (!empty($rows)) {
                    return $rows;
                }
                if ($this->has_open_biometric_checkin($staff_id)) {
                    return array(array(
                        'date' => date('Y-m-d H:i:s'),
                        'staff_id' => $staff_id,
                        'type_check' => 1,
                    ));
                }
                return array();
            //return TRUE;
        }
        else {
           return FALSE;
        }
      

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
        $records = trim((string) ($row['punch_records'] ?? ''));
        if ($records !== '' && preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
            $last = end($matches);
            return strtolower($last[2]) === 'in';
        }

        $in_time = trim((string) ($row['a_in_time'] ?? ''));
        $out_time = trim((string) ($row['a_out_time'] ?? ''));
        $has_in = $in_time !== '' && $in_time !== '00:00' && $in_time !== '00:00:00' && strtolower($in_time) !== 'nil';
        $has_out = $out_time !== '' && $out_time !== '00:00' && $out_time !== '00:00:00' && strtolower($out_time) !== 'nil';

        return $has_in && !$has_out;
    }
}
  

 ?>
