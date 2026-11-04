<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'helpers/apex_workhub_data_helper.php';
require_once APPPATH . 'helpers/apex_workhub_render_helper.php';

/**
 * Associate / employee home only — not admin, manager, HR, HOD, or other elevated roles.
 */
function t2g_apex_associate_dashboard_user($staff_id = '')
{
    if ($staff_id === '' || $staff_id === null) {
        $staff_id = get_staff_user_id();
    }
    if (!is_staff_member($staff_id)) {
        return false;
    }
    if (is_admin($staff_id)) {
        return false;
    }
    if (function_exists('is_super_admin') && is_super_admin()) {
        return false;
    }
    if (function_exists('is_admin2') && is_admin2($staff_id)) {
        return false;
    }
    if (function_exists('is_HR') && is_HR($staff_id)) {
        return false;
    }
    if (function_exists('is_manager') && is_manager($staff_id)) {
        return false;
    }
    if (function_exists('get_staff_role_slug')) {
        $role = get_staff_role_slug($staff_id);
        if (function_exists('pedma_role_is_manager_family') && pedma_role_is_manager_family($role)) {
            return false;
        }
        if (function_exists('pedma_is_hod_role') && pedma_is_hod_role($staff_id)) {
            return false;
        }
        $employee_roles = ['associate', 'asscociate', 'employee'];
        if (in_array($role, $employee_roles, true)) {
            return true;
        }
    }

    return function_exists('is_associate') && is_associate();
}

/**
 * Apex WorkHub dashboard: code.html layout + live staff data.
 */
function t2g_apex_workhub_html()
{
    try {
        $data = t2g_apex_workhub_gather_data();
    } catch (Throwable $e) {
        log_message('error', 'Apex WorkHub gather: ' . $e->getMessage());

        return '<div class="alert alert-danger mtop20">Dashboard could not load. Please contact support if this persists.<br><small>'
            . html_escape($e->getMessage()) . '</small></div>';
    }

    $paths = [
        FCPATH . 'code.html',
        APPPATH . '../code.html',
    ];
    $code_path = null;
    foreach ($paths as $p) {
        if (is_readable($p)) {
            $code_path = $p;
            break;
        }
    }
    if (!$code_path) {
        return '<div class="alert alert-danger mtop20">Apex WorkHub template (code.html) was not found.</div>';
    }

    $html = file_get_contents($code_path);
    $chunks = [];

    if (preg_match('/<!-- BEGIN: TopHeader -->.*?<!-- END: TopHeader -->/s', $html, $m)) {
        $chunks[] = $m[0];
    }
    if (preg_match('/<main class="p-8[^>]*>.*?<\/main>/s', $html, $m)) {
        $chunks[] = $m[0];
    }
    if (preg_match('/<!-- BEGIN: Footer -->.*?<!-- END: Footer -->/s', $html, $m)) {
        $chunks[] = $m[0];
    }

    if ($chunks === []) {
        return '<div class="alert alert-danger mtop20">Could not parse Apex WorkHub sections from code.html.</div>';
    }

    $out = '<div id="apex-workhub-root" class="apex-workhub-root flex flex-col min-w-0 bg-slate-100 text-slate-800 antialiased">';
    $out .= implode("\n", $chunks);
    $out .= '</div>';

    try {
        $out = t2g_apex_fix_links($out);
        $out = t2g_apex_patch_header($out, $data);
        $out = t2g_apex_apply_dynamic_sections($out, $data);
    } catch (Throwable $e) {
        log_message('error', 'Apex WorkHub render: ' . $e->getMessage());

        return '<div class="alert alert-danger mtop20">Dashboard layout could not be rendered.<br><small>'
            . html_escape($e->getMessage()) . '</small></div>';
    }

    $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    if ($json === false) {
        $json = '{}';
    }
    $out .= '<script type="application/json" id="apex-workhub-data">' . $json . '</script>';

    return $out;
    
}
