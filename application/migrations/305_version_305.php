<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_305 extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists(db_prefix() . 'staff_suggestions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'staff_suggestions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `staffid` INT(11) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "0=new,1=read,2=closed",
                `date_created` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `staffid` (`staffid`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }
    }
}
