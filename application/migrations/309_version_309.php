<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_309 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'staff_info';
        if (!$this->db->table_exists($table)) {
            return;
        }

        if (!$this->db->field_exists('employment_category', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '`
                 ADD COLUMN `employment_category` VARCHAR(20) NOT NULL DEFAULT \'fte\'
                 COMMENT \'fte|intern|wfh\' AFTER `doj`'
            );
        }
    }
}
