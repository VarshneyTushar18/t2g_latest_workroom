<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Standardize snapshot screenshot interval to 10 minutes for every staff member.
 */
class Migration_Version_311 extends CI_Migration
{
    public function up()
    {
        $this->load->helper('custom');
        $this->load->model('emt_model');
        $this->emt_model->syncAllSnapshotIntervals();
    }
}
