<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_308 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'staff_earned_leave_override';
        if ($this->db->table_exists($table)) {
            return;
        }

        $this->db->query('CREATE TABLE `' . $table . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `staff_id` INT(11) NOT NULL,
            `month` TINYINT(2) NOT NULL,
            `year` SMALLINT(4) NOT NULL,
            `earned_days` DECIMAL(6,2) NOT NULL DEFAULT 0,
            `updated_by` INT(11) NOT NULL DEFAULT 0,
            `date_updated` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `staff_month_year` (`staff_id`, `month`, `year`),
            KEY `idx_year_month` (`year`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }
}
