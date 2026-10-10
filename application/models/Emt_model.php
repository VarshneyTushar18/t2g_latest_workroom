<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Emt_model extends App_Model
{

public function get()
  {      
  //echo "Select tbltimeinterval.staffid,tbltimeinterval.time_interval,tblstaff.email,tblstaff.firstname,tblstaff.lastname,tblstaff.team_manage from tbltimeinterval left join tblstaff ON tbltimeinterval.staffid = tblstaff.staffid where tblstaff.active='1'";die;
   $sql="Select tbltimeinterval.id,tbltimeinterval.staffid,tbltimeinterval.time_interval,tblstaff.email,tblstaff.firstname,tblstaff.lastname,tblstaff.team_manage,tblstaff.staff_identifi,tblstaff.active from tbltimeinterval left join tblstaff ON tbltimeinterval.staffid = tblstaff.staffid where tblstaff.active='1'";    
    $query = $this->db->query($sql);
    return $query->result();  

    }
/*public function addIntervalData()
{
	$data = array(  
		'staffid'     => $_POST['staffid'],  
		'time'  => $_POST['time']
        );  
     $this->db->insert('tbltimeinterval',$data); 
	 return true;	 
}*/
	
public function update()
  {      
  //print_r($_POST);die;
  $id = $_POST['date'];
  $time = $_POST['time'];
  //echo $id;echo "<pre>";echo $time;die;
  //echo "UPDATE `tbltimeinterval` SET `time_interval` = ".$time." WHERE `id` = ".$id."";die;
	$this->db->set('time_interval', $time);
	$this->db->where('id', $id);
	$this->db->update('tbltimeinterval'); // gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2
   //$sql="UPDATE `tbltimeinterval` SET `time_interval` = ".$time." WHERE `id` = ".$id."";    
  //  $query = $this->db->query($sql);
    return $this->db->affected_rows();

    }
	
public function getDepartment()
	{
		$staff_id = get_staff_user_id();
		//echo "SELECT staffid,email,firstname,lastname,team_manage,hod_team_manage,file_id,directory_id,parent_directory_id,status,date_time from tblstaff left join tblstaff_drive_data ON tblstaff.staffid= tblstaff_drive_data.staff_id where team_manage=$staff_id AND active=1 GROUP BY staffid";die;
		$sql="SELECT 
    s.staffid,
    s.email,
    s.firstname,
    s.lastname,
    s.team_manage,
    s.hod_team_manage,
    d.name AS department_name,
    sdd.file_id,
    sdd.directory_id,
    sdd.parent_directory_id,
    sdd.status,
    sdd.date_time AS last_login
FROM tblstaff s
JOIN tblstaff_departments sd ON s.staffid = sd.staffid
JOIN tbldepartments d ON sd.departmentid = d.departmentid
LEFT JOIN (
    SELECT sdd1.staff_id,
           sdd1.date_time,
           sdd1.file_id,
           sdd1.directory_id,
           sdd1.parent_directory_id,
           sdd1.status
    FROM tblstaff_drive_data sdd1
    INNER JOIN (
        SELECT staff_id, MAX(date_time) AS max_dt
        FROM tblstaff_drive_data
        GROUP BY staff_id
    ) latest ON latest.staff_id = sdd1.staff_id
           AND latest.max_dt = sdd1.date_time
) sdd ON s.staffid = sdd.staff_id
WHERE 
    s.active = 1
    AND s.staffid NOT IN (1, 292, 178)
    AND sd.departmentid IN (
        SELECT departmentid
        FROM tblstaff_departments
        WHERE staffid = $staff_id
    )
ORDER BY d.name, s.firstname, s.lastname;";    
		$query = $this->db->query($sql);
		if (!$query) {
			log_message('error', 'Emt_model::getDepartment query failed: ' . $this->db->error()['message']);
			return [];
		}
		return $query->result(); 
	}
	
public function getEmtDepartmentCount()
	{
		$staff_id = get_staff_user_id();
		$sql="SELECT d.departmentid, d.name AS department_name, COUNT(DISTINCT CASE WHEN DATE(sdd.date_time) = CURDATE() THEN sdd.staff_id ELSE NULL END) AS login_count_today FROM tbldepartments d JOIN tblstaff_departments sd ON d.departmentid = sd.departmentid JOIN tblstaff s ON sd.staffid = s.staffid AND s.active = 1 LEFT JOIN tblstaff_drive_data sdd ON s.staffid = sdd.staff_id WHERE d.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) GROUP BY d.departmentid, d.name ORDER BY d.name;";    
		$query = $this->db->query($sql);
		return $query->result(); 
	}
	
public function getEmpStaffNotLoggedin(){
	$staff_id = get_staff_user_id();
	$sql = "SELECT s.staffid, CONCAT(s.firstname, ' ', s.lastname) AS name, d.name AS department_name FROM tblstaff s JOIN tblstaff_departments sd ON s.staffid = sd.staffid JOIN tbldepartments d ON sd.departmentid = d.departmentid WHERE s.active = 1 AND s.staffid NOT IN (1, 292, 178,403,662) AND sd.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) AND s.staffid NOT IN ( SELECT DISTINCT sdd.staff_id FROM tblstaff_drive_data sdd WHERE sdd.date_time >= NOW() - INTERVAL 1 HOUR ) ORDER BY d.name, name;";
	$query  = $this->db->query($sql);
	return $query->result();
}
public function getEmpStaffYesterdayLoggedin(){
	$staff_id = get_staff_user_id();
	$sql = "SELECT d.departmentid, d.name AS department_name, COUNT(DISTINCT CASE WHEN DATE(sdd.date_time) = CURDATE() - INTERVAL 1 DAY THEN sdd.staff_id ELSE NULL END) AS login_count_yesterday FROM tbldepartments d JOIN tblstaff_departments sd ON d.departmentid = sd.departmentid JOIN tblstaff s ON sd.staffid = s.staffid AND s.active = 1 LEFT JOIN tblstaff_drive_data sdd ON s.staffid = sdd.staff_id WHERE d.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) GROUP BY d.departmentid, d.name ORDER BY d.name;";
	$query  = $this->db->query($sql);
	return $query->result();
}	

}
  
