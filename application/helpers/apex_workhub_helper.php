<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'helpers/apex_workhub_data_helper.php';
require_once APPPATH . 'helpers/apex_workhub_render_helper.php';
require_once APPPATH . 'helpers/apex_workhub_manager_data_helper.php';
require_once APPPATH . 'helpers/apex_workhub_manager_render_helper.php';

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
 * Manager roles with Personal + Team WorkHub dashboard.
 */
function t2g_apex_workhub_manager_user($staff_id = '')
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
    if (function_exists('pedma_is_hod_role') && pedma_is_hod_role($staff_id)) {
        return false;
    }
    if (function_exists('is_manager') && is_manager($staff_id)) {
        return true;
    }
    if (function_exists('get_staff_role_slug')) {
        $role = get_staff_role_slug($staff_id);
        if (function_exists('pedma_role_is_manager_family') && pedma_role_is_manager_family($role)) {
            return true;
        }
    }

    return false;
}

/**
 * Associates and managers (not admin / HR / HOD).
 */
function t2g_apex_workhub_dashboard_user($staff_id = '')
{
    return t2g_apex_associate_dashboard_user($staff_id) || t2g_apex_workhub_manager_user($staff_id);
}

/**
 * @param array<string, mixed> $data
 */
function t2g_apex_workhub_build_personal_html($data, $for_manager = false)
{
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

    $out = t2g_apex_fix_links($out);
    $out = t2g_apex_patch_header($out, $data);
    $out = t2g_apex_apply_dynamic_sections($out, $data);

    return $out;
}

/**
 * Apex WorkHub dashboard: code.html layout + live staff data.
 */
function t2g_apex_workhub_html()
{
    $is_manager = t2g_apex_workhub_manager_user();

    try {
        $personal_data = t2g_apex_workhub_gather_data();
        if ($is_manager) {
            // Full team aggregation is loaded via AJAX (avoids 60s timeout on home).
            $team_data = t2g_apex_manager_team_dashboard_placeholder();
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex WorkHub gather: ' . $e->getMessage());

        return '<div class="alert alert-danger mtop20">Dashboard could not load. Please contact support if this persists.<br><small>'
            . html_escape($e->getMessage()) . '</small></div>';
    }

    try {
        if ($is_manager) {
            $personal_html = t2g_apex_workhub_build_personal_html($personal_data, true);
            $split          = t2g_apex_split_workhub_top_header($personal_html);
            $team_html      = t2g_apex_render_manager_team_dashboard($team_data);

            $out = '<div class="apex-manager-dashboard-shell min-w-0 bg-slate-100 flex flex-col">';
            if ($split['header'] !== '') {
                $out .= t2g_apex_inject_manager_tabs_into_header($split['header']);
            }
            $out .= '<div id="apex-manager-personal-pane" class="apex-manager-pane" data-apex-manager-pane="personal">' . $split['html'] . '</div>';
            $out .= '<div id="apex-manager-team-pane" class="apex-manager-pane hidden" data-apex-manager-pane="team" hidden aria-hidden="true">' . $team_html . '</div>';
            $out .= '</div>';

            $json_personal = json_encode($personal_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}';
            $json_team = json_encode($team_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}';
            $out .= '<script type="application/json" id="apex-workhub-data">' . $json_personal . '</script>';
            $out .= '<script type="application/json" id="apex-manager-team-data">' . $json_team . '</script>';
        } else {
            $out = t2g_apex_workhub_build_personal_html($personal_data);
            $json = json_encode($personal_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}';
            $out .= '<script type="application/json" id="apex-workhub-data">' . $json . '</script>';
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex WorkHub render: ' . $e->getMessage());

        return '<div class="alert alert-danger mtop20">Dashboard layout could not be rendered.<br><small>'
            . html_escape($e->getMessage()) . '</small></div>';
    }

    return $out;
}
