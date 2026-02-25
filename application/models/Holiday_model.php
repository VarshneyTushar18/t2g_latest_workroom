<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Holiday_model extends App_Model

{



    // custom function to get staff with their department id
    public function get_staff_based_on_department($id = '')
    {
        $this->load->model('departments_model');
        if ($id) {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1 and tblstaff_departments.departmentid = ' . $id;
        } else {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;';
        }
        $all_staff_data = $this->db->query($query)->result();
        $departments = $this->departments_model->get_staff_departments();

        $staff_with_same_department = [];
        $start = false;

        /* foreach will loop through all active staff data then first for loop will check whether 
        that staff is present in the array. if it is present then variable will be true 
        in second loop we loop through all the departments that staff is part of . if staff is part of 
        department that logged in staff is part of then it will be inserted in the array . Also it will be
        inserted only if it is false . This is to prevent inserting of the duplicate staff */

        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array)$staff_data;
                }
            }
            $start = false;
        }

        return $staff_with_same_department;
    }

    // this will change column name of staffid to staff_id
    public function _get_staff_based_on_department()
    {
        $this->load->model('departments_model');
        $all_staff_data = $this->db->query('select *,tblstaff.staffid as staff_id from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;')->result();
        $departments = $this->departments_model->get_staff_departments();
        $staff_with_same_department = [];
        $start = false;
        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array)$staff_data;
                }
            }
        }

        return $staff_with_same_department;
    }
    /**

     * Get staff permissions

     * @param  mixed $id staff id

     * @return array

     */

    public function get_staff_permissions($id)

    {

        // Fix for version 2.3.1 tables upgrade

        if (defined('DOING_DATABASE_UPGRADE')) {

            return [];
        }



        $permissions = $this->app_object_cache->get('staff-' . $id . '-permissions');



        if (!$permissions && !is_array($permissions)) {

            $this->db->where('staff_id', $id);

            $permissions = $this->db->get('staff_permissions')->result_array();



            $this->app_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }



        return $permissions;
    }



    /**

     * Add new staff member

     * @param array $data staff $_POST data

     */

	


  
	
    
}
