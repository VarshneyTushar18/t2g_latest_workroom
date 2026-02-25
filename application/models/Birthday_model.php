<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Birthday_model extends CI_Model
{



    // Get upcoming anniversaries within the next X days
    public function get_upcoming_anniversaries($days_ahead = 7)
    {
        $today = date('m-d');
        $future_date = date('m-d', strtotime("+$days_ahead days"));


        // $this->db->select('* , COALESCE(ts.doj, STR_TO_DATE(ti.doj, "%m/%d/%Y")) AS doj');
        // $this->db->from('tblstaff ts');
        // $this->db->join('tblstaff_info ti', 'ts.staffid = ti.staffid', 'left'); // Adjust the foreign key if necessary
        // $this->db->where('ts.active', 1);
        // // Fetch anniversaries between today and $days_ahead
        // $this->db->group_start();  // Open parentheses for grouping OR conditions
        // $this->db->where("DATE_FORMAT(COALESCE(ts.doj, STR_TO_DATE(ti.doj, '%m/%d/%Y')) , '%m-%d') =", $today);

        // $this->db->or_group_start();  // Use OR for birthdays
        // $this->db->where('DATE_FORMAT(birthday, "%m-%d") =', $today);


        // $this->db->group_end();  // Close parentheses for grouping OR conditions
        // $this->db->group_end();  // Close parentheses for grouping OR conditions

        // $query = $this->db->get();



        $this->db->select('*, COALESCE(ts.doj, STR_TO_DATE(ti.doj, "%m/%d/%Y")) AS doj_combined');
        $this->db->from('tblstaff ts');
        $this->db->join('tblstaff_info ti', 'ts.staffid = ti.staffid', 'left');
        $this->db->where('ts.active', 1);
        $this->db->group_start();
        $this->db->where("MONTH(birthday) =", date('m'));
        $this->db->or_group_start();
        $this->db->where("MONTH(COALESCE(ts.doj, STR_TO_DATE(ti.doj, '%m/%d/%Y'))) =", date('m'));
        $this->db->group_end();
        $this->db->group_end();
        $this->db->order_by("DAY(birthday)", "ASC");
        $this->db->order_by("DAY(doj_combined)", "ASC");



        $query = $this->db->get();

        return $query->result();
    }
}
