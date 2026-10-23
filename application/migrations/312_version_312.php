<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PEDMA: every KRA uses a fixed max rating of 5 (replaces legacy point weights).
 */
class Migration_Version_312 extends CI_Migration
{
    public function up()
    {
        $max = 5;
        if ($this->db->table_exists('tblstaff_performance_kra')) {
            $this->db->query('UPDATE `tblstaff_performance_kra` SET `max_score` = ' . (int) $max);
        }
        if ($this->db->table_exists('tblstaff_performance_kpi')) {
            $this->db->query('UPDATE `tblstaff_performance_kpi` SET `max_score` = ' . (int) $max);
        }
    }
}
