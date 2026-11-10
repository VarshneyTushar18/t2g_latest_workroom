<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Navbar_probe extends CI_Controller
{
    public function index()
    {
        header('Content-Type: text/plain; charset=UTF-8');
        $this->load->helper('timesheets/timesheets');
        $ids = [1268, 99, 1295, 1301, 203, 292];
        foreach ($ids as $id) {
            $this->session->set_userdata([
                'staff_logged_in' => true,
                'staff_user_id'   => $id,
            ]);
            try {
                $ctx = timesheets_admin_navbar_punch_context(13);
            } catch (Throwable $e) {
                echo $id, " THROW ", $e->getMessage(), "\n";
                continue;
            }
            echo $id,
                ' bio=', !empty($ctx['biometric_navbar_active']) ? '1' : '0',
                ' label=', ($ctx['biometric_checkin_label'] ?? ''),
                ' type=', var_export($ctx['type_check_in_out'] ?? '', true),
                ' wfh=', !empty($ctx['on_wfh_today']) ? '1' : '0',
                ' open=', !empty($ctx['workroom_open_checkin']) ? '1' : '0',
                "\n";
        }
        exit;
    }
}
