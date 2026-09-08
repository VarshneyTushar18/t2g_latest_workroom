<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Utilization_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    
    public function getDepartment()
    {
       return $this->db->get(db_prefix() . 'departments')->result_array();
		//return  $this->db->get()->result_array();
    }
	public function getDepartmentemp($deptid, $active = 1)
	{
		if (!is_array($deptid)) {
			$deptid = [$deptid];
		}
		$valuesArray = array_values(array_filter($deptid, function ($v) {
			return $v !== '' && $v !== null;
		}));
		if (empty($valuesArray)) {
			return [];
		}

		$dept_id = implode(',', array_map('intval', $valuesArray));
		$activeClause = '';
		if ($active !== 'all' && $active !== null && $active !== '') {
			$active = ((int) $active === 0) ? 0 : 1;
			$activeClause = ' AND tblstaff.active=' . (int) $active;
		}
		$result = $this->db->query('select tblstaff_departments.staffid,tblstaff_departments.departmentid,tbldepartments.name,tblstaff.staffid,tblstaff.firstname,
						tblstaff.lastname,tblstaff.staff_identifi,tblstaff.active
						from tblstaff_departments
						left join tblstaff on tblstaff_departments.staffid = tblstaff.staffid
						left join tbldepartments on tblstaff_departments.departmentid= tbldepartments.departmentid
						where tblstaff_departments.departmentid IN('.$dept_id.')' . $activeClause . ' GROUP by tblstaff.staffid ORDER BY tblstaff.active DESC, tblstaff.firstname ASC, tblstaff.lastname ASC;');
		return	$result->result_array();
	}
	public function getFilterData($deptid,$empid)
	{
		$valuesArray = array_values($deptid); 

		$dept_id = implode(", ", $valuesArray);
		$empid = $_POST['employee'];
		$sdate = $_POST['start_date'];
		$edate = $_POST['end_date'];
		
		if($empid == 'Filter By Employee' && $sdate == NULL && $edate == NULL){
			//echo "if condition";
			$result = $this->db->query('SELECT
									tbldepartments.name AS "Department",
									tblprojects.name AS "Project",
									tblprojects.estimated_hours AS "Assigned Hours",
									sum(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time ,"%H:%i:%s"), FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s")))/3600) as "Consumed Hours",

								(tblprojects.estimated_hours -(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time,"%H:%i:%s"),
								FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s"))) / 3600)) AS "Hours Variance"
								FROM
									tblclients
								LEFT JOIN tblprojects ON tblclients.userid = tblprojects.clientid
								LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
								LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
								LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
								LEFT JOIN tbldepartments ON tbltaskstimers.deptid = tbldepartments.departmentid
								WHERE
									tbldepartments.departmentid IN('.$dept_id.') AND tblstaff.active = 1
								GROUP BY
									tblprojects.id;');
				return	$result->result_array();
			
		}else if($empid != 'Filter By Employee' && $sdate == NULL && $edate == NULL){
			//PRINT_R("else condition".$empid);
		
		$result = $this->db->query('SELECT
									tbldepartments.name AS "Department Name",
									tblprojects.name AS "project name",
									tblprojects.estimated_hours,
									sum(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time ,"%H:%i:%s"), FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s")))/3600) as "Consumed Hour",
								(tblprojects.estimated_hours -(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time,"%H:%i:%s"),
								FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s"))) / 3600)) AS "Hours Variance"
								FROM
									tblclients
								LEFT JOIN tblprojects ON tblclients.userid = tblprojects.clientid
								LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
								LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
								LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
								LEFT JOIN tbldepartments ON tbltaskstimers.deptid = tbldepartments.departmentid
								WHERE
									tbltaskstimers.staff_id IN('.$empid.') AND tblstaff.active = 1
								GROUP BY
									tblprojects.id;');
				return	$result->result_array();
		}else if($empid != 'Filter By Employee' && $sdate != NULL && $edate != NULL){
			$result = $this->db->query('SELECT
									tbldepartments.name AS "Department Name",
									tblprojects.name AS "project name",
									tblprojects.estimated_hours,
									sum(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time ,"%H:%i:%s"), FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s")))/3600) as "Consumed Hour",
								(tblprojects.estimated_hours -(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time,"%H:%i:%s"),
								FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s"))) / 3600)) AS "Hours Variance"
								FROM
									tblclients
								LEFT JOIN tblprojects ON tblclients.userid = tblprojects.clientid
								LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
								LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
								LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
								LEFT JOIN tbldepartments ON tbltaskstimers.deptid = tbldepartments.departmentid
								WHERE
									tbltaskstimers.staff_id IN('.$empid.') AND tblstaff.active = 1 AND FROM_UNIXTIME(tbltaskstimers.start_time,"%Y-%m-%d") >= "'.$sdate.'" AND FROM_UNIXTIME(tbltaskstimers.end_time ,"%Y-%m-%d") <= "'.$edate.'"
								GROUP BY
									tblprojects.id;');
				return	$result->result_array();
		}else if($empid == 'Filter By Employee' && $dept_id !=NULL && $sdate != NULL && $edate != NULL){
			$result = $this->db->query('SELECT
									tbldepartments.name AS "Department Name",
									tblprojects.name AS "project name",
									tblprojects.estimated_hours,
									sum(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time ,"%H:%i:%s"), FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s")))/3600) as "Consumed Hour",
								(tblprojects.estimated_hours -(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time,"%H:%i:%s"),
								FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s"))) / 3600)) AS "Hours Variance"
								FROM
									tblclients
								LEFT JOIN tblprojects ON tblclients.userid = tblprojects.clientid
								LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
								LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
								LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
								LEFT JOIN tbldepartments ON tbltaskstimers.deptid = tbldepartments.departmentid
								WHERE
									tbldepartments.departmentid IN('.$dept_id.') AND tblstaff.active = 1 AND FROM_UNIXTIME(tbltaskstimers.start_time,"%Y-%m-%d") >= "'.$sdate.'" AND FROM_UNIXTIME(tbltaskstimers.end_time ,"%Y-%m-%d") <= "'.$edate.'"
								GROUP BY
									tblprojects.id;');
				return	$result->result_array();
			
		}
		else {
			$result = $this->db->query('SELECT
									tbldepartments.name AS "Department Name",
									tblprojects.name AS "project name",
									tblprojects.estimated_hours,
									sum(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time ,"%H:%i:%s"), FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s")))/3600) as "Consumed Hour",
								(tblprojects.estimated_hours -(TIME_TO_SEC(TIMEDIFF(FROM_UNIXTIME(tbltaskstimers.end_time,"%H:%i:%s"),
								FROM_UNIXTIME(tbltaskstimers.start_time,"%H:%i:%s"))) / 3600)) AS "Hours Variance"
								FROM
									tblclients
								LEFT JOIN tblprojects ON tblclients.userid = tblprojects.clientid
								LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
								LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
								LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
								LEFT JOIN tbldepartments ON tbltaskstimers.deptid = tbldepartments.departmentid
								WHERE
									tbltaskstimers.staff_id IN('.$empid.') AND tblstaff.active = 1
								GROUP BY
									tblprojects.id;');
				return	$result->result_array();
		}
	}
   
}
