<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_306 extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists(db_prefix() . 'staff_performance')) {
            if (!$this->db->field_exists('feedback_accepted', db_prefix() . 'staff_performance')) {
                $this->db->query('ALTER TABLE `' . db_prefix() . 'staff_performance`
                    ADD `feedback_accepted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "0=pending,1=accepted,2=need_meeting" AFTER `staff_comment`,
                    ADD `feedback_accepted_at` DATETIME NULL DEFAULT NULL AFTER `feedback_accepted`');
            }
        }
    }

    public function down()
    {
        if ($this->db->table_exists(db_prefix() . 'staff_performance')) {
            if ($this->db->field_exists('feedback_accepted_at', db_prefix() . 'staff_performance')) {
                $this->db->query('ALTER TABLE `' . db_prefix() . 'staff_performance` DROP `feedback_accepted_at`');
            }
            if ($this->db->field_exists('feedback_accepted', db_prefix() . 'staff_performance')) {
                $this->db->query('ALTER TABLE `' . db_prefix() . 'staff_performance` DROP `feedback_accepted`');
            }
        }
    }
}
