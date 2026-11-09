<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Speed up dashboard lookups on large unindexed tables.
 */
class Migration_Version_313 extends CI_Migration
{
    public function up()
    {
        $this->add_index_if_missing(
            'biometric_report',
            'idx_bio_employee_date',
            '`employee_code`, `attendance_date`'
        );
        $this->add_index_if_missing(
            'notifications',
            'idx_notifications_user_date',
            '`touserid`, `date`'
        );
        $this->add_index_if_missing(
            'notifications',
            'idx_notifications_user_read',
            '`touserid`, `isread`'
        );
        $this->add_index_if_missing(
            'notifications',
            'idx_notifications_user_inline',
            '`touserid`, `isread_inline`'
        );
        $this->add_index_if_missing(
            'task_followers',
            'idx_task_followers_task_staff',
            '`taskid`, `staffid`'
        );
        $this->add_index_if_missing(
            'dismissed_announcements',
            'idx_dismissed_announcements_user',
            '`staff`, `userid`, `announcementid`'
        );
    }

    private function add_index_if_missing($table, $index, $columns)
    {
        $table = db_prefix() . $table;
        if (!$this->db->table_exists($table)) {
            return;
        }

        $exists = $this->db->query(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
             LIMIT 1',
            [$table, $index]
        )->row();
        if ($exists) {
            return;
        }

        $this->db->query(
            'ALTER TABLE `' . $table . '` ADD INDEX `' . $index . '` (' . $columns . '), ALGORITHM=INPLACE, LOCK=NONE'
        );
    }
}
