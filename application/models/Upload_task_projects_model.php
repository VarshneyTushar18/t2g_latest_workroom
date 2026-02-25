<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Upload_task_projects_model extends App_Model

{


    public function __construct()
    {
        parent::__construct();
    }

    public function get_project_data()
    {
        $get_project_data = "SELECT
						tblprojects.id,
						tblprojects.name,
						tblprojects.description,
						tblprojects.status,
						tblprojects.billing_type,
						tblprojects.start_date,
						tblprojects.deadline,
						tblprojects.project_created,
						tblprojects.date_finished,
						tblprojects.progress,
						tblprojects.progress_from_tasks,
						tblprojects.project_cost,
						tblprojects.project_rate_per_hour,
						tblprojects.estimated_hours,
						tblproject_members.staff_id,
						tblcustomfields.name as custom_field,
						tblcustomfieldsvalues.value as custom_value,
						tblcustomfieldsvalues.id AS custom_id
					FROM
						`tblprojects`
					LEFT JOIN tblproject_members ON tblprojects.id = tblproject_members.project_id
					LEFT JOIN tblcustomfieldsvalues ON tblprojects.id = tblcustomfieldsvalues.relid
					LEFT JOIN tblcustomfields ON tblcustomfields.id = tblcustomfieldsvalues.fieldid
					GROUP BY
						tblprojects.id,
						tblprojects.name,
						tblprojects.description,
						tblproject_members.staff_id,
						tblcustomfields.name,
						tblcustomfieldsvalues.value;";

        $data = $this->db->query($get_project_data)->result_array();

        return $data;
    }
}
