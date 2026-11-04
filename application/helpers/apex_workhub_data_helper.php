<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Same visibility rules as get_tasks_where_string(), but unqualified `id` (required for MySQL IN subqueries).
 */
function t2g_apex_tasks_visibility_sql($staff_id)
{
    $p   = db_prefix();
    $sid = (int) $staff_id;
    $w   = '(id IN (SELECT taskid FROM ' . $p . 'task_assigned WHERE staffid=' . $sid . ')'
        . ' OR id IN (SELECT taskid FROM ' . $p . 'task_followers WHERE staffid=' . $sid . ')'
        . ' OR (addedfrom=' . $sid . ' AND is_added_from_contact=0)';
    if (get_option('show_all_tasks_for_project_member') == 1) {
        $w .= ' OR (rel_type="project" AND rel_id IN (SELECT project_id FROM ' . $p . 'project_members WHERE staff_id=' . $sid . '))';
    }

    return $w . ' OR is_public=1)';
}

/**
 * COUNT(*) with a clean query builder (avoids stale WHERE from prior queries).
 *
 * @param string       $table
 * @param string|array $where
 */
/**
 * Task timer totals (same source as Staff → Timesheets stats).
 *
 * @return array{total:int,last_month:int,this_month:int,last_week:int,this_week:int}
 */
function t2g_apex_load_timesheet_stats($staff_id)
{
    $CI = &get_instance();
    $empty = [
        'total'      => 0,
        'last_month' => 0,
        'this_month' => 0,
        'last_week'  => 0,
        'this_week'  => 0,
    ];
    try {
        $CI->db->reset_query();
        $CI->load->model('staff_model');
        $logged = $CI->staff_model->get_logged_time_data((int) $staff_id);
        $CI->db->reset_query();
        if (!is_array($logged)) {
            return $empty;
        }

        return [
            'total'      => (int) ($logged['total'] ?? 0),
            'last_month' => (int) ($logged['last_month'] ?? 0),
            'this_month' => (int) ($logged['this_month'] ?? 0),
            'last_week'  => (int) ($logged['last_week'] ?? 0),
            'this_week'  => (int) ($logged['this_week'] ?? 0),
        ];
    } catch (Throwable $e) {
        log_message('error', 'Apex timesheet stats: ' . $e->getMessage());
        $CI->db->reset_query();

        return $empty;
    }
}

function t2g_apex_db_count($table, $where = '')
{
    $CI = &get_instance();
    $CI->db->reset_query();
    if (is_string($where) && $where !== '') {
        $CI->db->where($where, null, false);
    } elseif (is_array($where) && $where !== []) {
        $CI->db->where($where);
    }
    $count = (int) $CI->db->count_all_results($table);
    $CI->db->reset_query();

    return $count;
}

/**
 * Live dashboard payload for Apex WorkHub (per logged-in staff).
 *
 * @return array<string, mixed>
 */
function t2g_apex_workhub_gather_data()
{
    $CI       = &get_instance();
    $staff_id = (int) get_staff_user_id();

    $timesheet_seconds = t2g_apex_load_timesheet_stats($staff_id);
    $timesheet_stats   = [
        'total'      => seconds_to_time_format($timesheet_seconds['total']),
        'last_month' => seconds_to_time_format($timesheet_seconds['last_month']),
        'this_month' => seconds_to_time_format($timesheet_seconds['this_month']),
        'last_week'  => seconds_to_time_format($timesheet_seconds['last_week']),
        'this_week'  => seconds_to_time_format($timesheet_seconds['this_week']),
    ];

    $hour = (int) date('G');
    if ($hour < 12) {
        $greeting = 'Good morning';
    } elseif ($hour < 17) {
        $greeting = 'Good afternoon';
    } else {
        $greeting = 'Good evening';
    }

    $staff_name = get_staff_full_name();
    $first_name = trim(explode(' ', $staff_name)[0] ?? $staff_name);

    $role_label = 'Associate';
    if (function_exists('get_job_position_by_staffid')) {
        try {
            $pos = get_job_position_by_staffid($staff_id);
            if (!empty($pos)) {
                $role_label = $pos;
            }
        } catch (Throwable $e) {
        }
    }

    $initials = '';
    foreach (preg_split('/\s+/', $staff_name) as $part) {
        if ($part !== '') {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }
    if ($initials === '') {
        $initials = 'U';
    }

    $_where_projects = '';
    if (!has_permission('projects', '', 'view')) {
        $_where_projects = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . $staff_id . ')';
    }
    $projects_table = db_prefix() . 'projects';
    $total_projects             = t2g_apex_db_count($projects_table, $_where_projects);
    $where_in_progress          = ($_where_projects === '' ? '' : $_where_projects . ' AND ') . 'status = 2';
    $total_projects_in_progress = t2g_apex_db_count($projects_table, $where_in_progress);
    $where_at_risk              = ($_where_projects === '' ? '' : $_where_projects . ' AND ') . 'status = 3';
    $total_projects_at_risk     = t2g_apex_db_count($projects_table, $where_at_risk);

    $CI->load->model('tasks_model');
    $tasks_table = db_prefix() . 'tasks';

    $_where_tasks = '';
    if (!has_permission('tasks', '', 'view')) {
        $_where_tasks = t2g_apex_tasks_visibility_sql($staff_id);
    }
    $total_tasks = 0;
    $open_tasks  = 0;
    try {
        $total_tasks      = t2g_apex_db_count($tasks_table, $_where_tasks);
        $where_open_tasks = ($_where_tasks === '' ? '' : $_where_tasks . ' AND ') . 'status != ' . (int) Tasks_model::STATUS_COMPLETE;
        $open_tasks       = t2g_apex_db_count($tasks_table, $where_open_tasks);
    } catch (Throwable $e) {
        log_message('error', 'Apex task counts: ' . $e->getMessage());
    }

    $task_rows = [];
    try {
        $CI->db->reset_query();
        $status_complete = (int) Tasks_model::STATUS_COMPLETE;
        $priority_col    = $CI->db->field_exists('priority', $tasks_table) ? ', priority' : '';
        $sql             = 'SELECT id, name, duedate, status' . $priority_col
            . ' FROM `' . $tasks_table . '` WHERE status != ' . $status_complete;
        if (!has_permission('tasks', '', 'view')) {
            $sql .= ' AND ' . t2g_apex_tasks_visibility_sql($staff_id);
        }
        $sql .= ' ORDER BY (duedate IS NULL) ASC, duedate ASC LIMIT 5';
        $task_rows = $CI->db->query($sql)->result_array();
    } catch (Throwable $e) {
        log_message('error', 'Apex tasks query: ' . $e->getMessage());
        $task_rows = [];
    } finally {
        $CI->db->reset_query();
    }

    $tasks_due_today = 0;
    $today_ymd       = date('Y-m-d');
    foreach ($task_rows as $tr) {
        if (!empty($tr['duedate']) && date('Y-m-d', strtotime($tr['duedate'])) === $today_ymd) {
            $tasks_due_today++;
        }
    }

    $ticket_open = 0;
    $ticket_total = 0;
    if ($CI->db->table_exists(db_prefix() . 'tickets')) {
        $tw = 'assigned=' . $staff_id;
        if (is_admin()) {
            $tw = '1=1';
        }
        $ticket_total = t2g_apex_db_count(db_prefix() . 'tickets', $tw);
        $ticket_open  = t2g_apex_db_count(db_prefix() . 'tickets', $tw . ' AND status != 5');
    } else {
        $where_leads = '';
        if (!is_admin()) {
            $where_leads = '(addedfrom = ' . $staff_id . ' OR assigned = ' . $staff_id . ')';
        }
        $ticket_total = t2g_apex_db_count(db_prefix() . 'leads', ($where_leads === '' ? 'junk=0' : $where_leads . ' AND junk=0'));
        $wl           = ($where_leads === '' ? 'status=1' : $where_leads . ' AND status=1');
        $ticket_open  = t2g_apex_db_count(db_prefix() . 'leads', $wl);
    }

    $leave_granted = 0.0;
    $leave_balance = 0.0;
    $leave_pending = 0;
    $leave_taken_month = 0.0;
    $leave_balance_primary = null;
    try {
        if ($CI->db->table_exists(db_prefix() . 'timesheets_option') && $CI->app_modules->is_active('timesheets')) {
            $CI->load->model('timesheets/timesheets_model');
            if (method_exists($CI->timesheets_model, 'get_staff_leave_balance_cards')) {
                $cards = $CI->timesheets_model->get_staff_leave_balance_cards($staff_id, (int) date('Y'), (int) date('m'));
                foreach ($cards as $card) {
                    $slug = (string) ($card['slug'] ?? '');
                    if ($slug === 'loss-of-pay') {
                        continue;
                    }
                    if ($slug === 'earned-leave') {
                        $leave_taken_month = (float) ($card['consumed'] ?? 0);
                        $leave_balance_primary = isset($card['available'])
                            ? (float) $card['available']
                            : (float) ($card['balance'] ?? 0);
                    }
                    $leave_granted += (float) ($card['granted'] ?? 0);
                    $leave_balance += (float) ($card['balance'] ?? 0);
                }
            }
            if ($CI->db->table_exists(db_prefix() . 'timesheets_requisition_leave')) {
                $leave_pending = t2g_apex_db_count(db_prefix() . 'timesheets_requisition_leave', 'staff_id=' . $staff_id . ' AND status=0');
            }
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex leave data: ' . $e->getMessage());
    }
    if ($leave_balance_primary === null) {
        $leave_balance_primary = $leave_balance;
    }
    $leave_used = max(0, $leave_granted - $leave_balance);

    $attendance_pct     = null;
    $attendance_present = 0;
    $attendance_counted = 0;
    $calendar_days      = [];
    $calendar_month     = date('Y-m');
    try {
        if ($CI->app_modules->is_active('timesheets')) {
            $CI->load->model('timesheets/timesheets_model');
            if (method_exists($CI->timesheets_model, 'get_attendance_regularisation_calendar')) {
                $y = (int) date('Y');
                $m = (int) date('m');
                $cal = $CI->timesheets_model->get_attendance_regularisation_calendar($staff_id, $y, $m);
                $calendar_days = $cal['days'] ?? [];
                foreach ($calendar_days as $d) {
                    if (empty($d['date'])) {
                        continue;
                    }
                    $code = $d['code'] ?? '';
                    if (in_array($code, ['WO', 'H'], true)) {
                        continue;
                    }
                    $attendance_counted++;
                    if ($code === 'P' || ($d['status'] ?? '') === 'ok') {
                        $attendance_present++;
                    } elseif ($code === 'HD' || ($d['status'] ?? '') === 'half_day') {
                        $attendance_present += 0.5;
                    }
                }
                if ($attendance_counted > 0) {
                    $attendance_pct = (int) round(($attendance_present / $attendance_counted) * 100);
                }
            }
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex attendance data: ' . $e->getMessage());
    }

    $break_minutes = null;
    $punch         = [];
    try {
        if (function_exists('timesheets_admin_navbar_punch_context')) {
            $CI->load->helper('timesheets/timesheets');
            $punch = timesheets_admin_navbar_punch_context(13);
            if (!empty($punch['biometric_break_summary']['total_minutes'])) {
                $break_minutes = (int) $punch['biometric_break_summary']['total_minutes'];
            }
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex punch context: ' . $e->getMessage());
        $punch = [];
    } finally {
        $CI->db->reset_query();
    }

    $projects     = [];
    $project_rows = [];
    try {
        $CI->load->model('projects_model');
        $CI->db->reset_query();
        $CI->db->select('id, name, status, progress, progress_from_tasks, deadline');
        $CI->db->from(db_prefix() . 'projects');
        if (!has_permission('projects', '', 'view')) {
            $CI->db->where('id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . $staff_id . ')', null, false);
        }
        $CI->db->where('status !=', 4);
        $CI->db->order_by('deadline IS NULL', 'ASC', false);
        $CI->db->order_by('deadline', 'ASC');
        $CI->db->limit(5);
        $project_rows = $CI->db->get()->result_array();
        foreach ($project_rows as $p) {
            $prog = (float) $CI->projects_model->calc_progress($p['id']);
            $projects[] = [
                'id'       => (int) $p['id'],
                'name'     => $p['name'],
                'progress' => round($prog),
                'status'   => (int) $p['status'],
                'at_risk'  => (int) $p['status'] === 3,
                'deadline' => $p['deadline'] ?? '',
            ];
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex projects query: ' . $e->getMessage());
        $projects = [];
    }

    $announcements     = [];
    $announcements_all = [];
    $undismissed       = 0;
    try {
        $CI->load->model('announcements_model');
        $announcements_all = t2g_apex_fetch_staff_announcements($CI, 10);
        $announcements     = array_slice($announcements_all, 0, 2);
        $undismissed       = (int) $CI->announcements_model->get_total_undismissed_announcements();
    } catch (Throwable $e) {
        log_message('error', 'Apex announcements: ' . $e->getMessage());
    } finally {
        $CI->db->reset_query();
    }

    $pedma = t2g_apex_workhub_default_pedma();
    try {
        $pedma = t2g_apex_workhub_load_pedma($CI, $staff_id);
    } catch (Throwable $e) {
        log_message('error', 'Apex PEDMA: ' . $e->getMessage());
    } finally {
        $CI->db->reset_query();
    }

    $profile_image = staff_profile_image_url($staff_id, 'small');

    $bulletin = [];
    try {
        $bulletin = t2g_apex_load_bulletin_data($CI, $staff_id, $calendar_days, $punch, $pedma);
    } catch (Throwable $e) {
        log_message('error', 'Apex bulletin: ' . $e->getMessage());
    } finally {
        $CI->db->reset_query();
    }

    return [
        'staff_id'                  => $staff_id,
        'greeting'                  => $greeting,
        'first_name'                => $first_name,
        'staff_name'                => $staff_name,
        'role_label'                => $role_label,
        'initials'                  => $initials,
        'profile_image'             => $profile_image,
        'date_long'                 => date('l, d F Y'),
        'kpi'                       => [
            'projects_total'       => $total_projects,
            'projects_in_progress' => $total_projects_in_progress,
            'projects_at_risk'     => $total_projects_at_risk,
            'tasks_open'           => $open_tasks,
            'tasks_total'          => $total_tasks,
            'tasks_due_today'      => $tasks_due_today,
            'attendance_pct'       => $attendance_pct,
            'leave_balance'        => round($leave_balance_primary, 1),
            'leave_pending'        => $leave_pending,
            'tickets_open'         => $ticket_open,
            'tickets_total'        => $ticket_total,
            'break_minutes'        => $break_minutes,
        ],
        'tasks'                     => $task_rows,
        'projects'                  => $projects,
        'leave'                     => [
            'granted'          => round($leave_granted, 1),
            'balance'          => round($leave_balance_primary, 1),
            'used'             => round($leave_used, 1),
            'taken_month'      => round($leave_taken_month, 1),
            'pending'          => $leave_pending,
            'pct_left'         => $leave_granted > 0 ? round(($leave_balance_primary / $leave_granted) * 100, 1) : 0,
        ],
        'thought'                   => t2g_apex_thought_of_day(),
        'announcements'             => $announcements,
        'announcements_my_work'     => $announcements_all,
        'announcements_unread'      => $undismissed,
        'pedma'                     => $pedma,
        'punch'                     => $punch,
        'calendar_days'             => $calendar_days,
        'calendar_month'            => $calendar_month,
        'calendar_month_label'      => date('F Y'),
        'timesheet_stats'           => $timesheet_stats,
        'bulletin'                  => $bulletin,
        'urls'                      => t2g_apex_workhub_routes(),
    ];
}

/**
 * @param mixed $val
 */
function t2g_apex_parse_hours_value($val)
{
    if ($val === null || $val === '' || $val === '—') {
        return 0.0;
    }
    if (is_numeric($val)) {
        return (float) $val;
    }
    if (preg_match('/([\d.]+)/', (string) $val, $m)) {
        return (float) $m[1];
    }

    return 0.0;
}

/**
 * @param float $hours
 */
function t2g_apex_format_hours_label($hours)
{
    $hours = max(0, (float) $hours);
    if ($hours < 0.05) {
        return '0h';
    }
    $whole = (int) floor($hours);
    $mins  = (int) round(($hours - $whole) * 60);
    if ($mins >= 60) {
        $whole += 1;
        $mins = 0;
    }
    if ($mins === 0) {
        return $whole . 'h';
    }

    return $whole . 'h ' . $mins . 'm';
}

/**
 * @param array<int, array<string, mixed>> $calendar_days
 * @return array<string, array<string, mixed>>
 */
function t2g_apex_calendar_days_index($calendar_days)
{
    $map = [];
    foreach ($calendar_days as $day) {
        if (!empty($day['date'])) {
            $map[(string) $day['date']] = $day;
        }
    }

    return $map;
}

/**
 * @param array<string, array<string, mixed>> $map
 * @return array<string, mixed>|null
 */
function t2g_apex_calendar_day_for($CI, $staff_id, $ymd, array &$map)
{
    if (isset($map[$ymd])) {
        return $map[$ymd];
    }
    $ts = strtotime($ymd);
    if ($ts === false) {
        return null;
    }
    try {
        if ($CI->app_modules->is_active('timesheets')) {
            $CI->load->model('timesheets/timesheets_model');
            if (method_exists($CI->timesheets_model, 'get_attendance_regularisation_calendar')) {
                $cal = $CI->timesheets_model->get_attendance_regularisation_calendar(
                    $staff_id,
                    (int) date('Y', $ts),
                    (int) date('m', $ts)
                );
                foreach ($cal['days'] ?? [] as $day) {
                    if (!empty($day['date'])) {
                        $map[(string) $day['date']] = $day;
                    }
                }
            }
        }
    } catch (Throwable $e) {
        log_message('error', 'Apex calendar day load: ' . $e->getMessage());
    }

    return $map[$ymd] ?? null;
}

/**
 * @param array<string, mixed> $punch
 * @param array<string, mixed> $pedma
 * @return array<string, mixed>
 */
function t2g_apex_load_bulletin_data($CI, $staff_id, $calendar_days, $punch, $pedma)
{
    $map   = t2g_apex_calendar_days_index(is_array($calendar_days) ? $calendar_days : []);
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $yDay = t2g_apex_calendar_day_for($CI, $staff_id, $yesterday, $map);
    $yWork = $yDay ? t2g_apex_parse_hours_value($yDay['actual_work_hrs_num'] ?? $yDay['hours'] ?? 0) : 0.0;
    $yBreak = $yDay ? t2g_apex_parse_hours_value($yDay['break_hrs'] ?? 0) : 0.0;

    $avgSamples = [];
    foreach ($map as $date => $day) {
        if ($date >= $today || substr($date, 0, 7) !== date('Y-m')) {
            continue;
        }
        $code = (string) ($day['code'] ?? '');
        if (in_array($code, ['WO', 'H', ''], true)) {
            continue;
        }
        $hrs = t2g_apex_parse_hours_value($day['actual_work_hrs_num'] ?? $day['hours'] ?? 0);
        if ($hrs > 0 || in_array($code, ['P', 'HD'], true)) {
            $avgSamples[] = $hrs;
        }
    }
    $avgWork = $avgSamples !== [] ? array_sum($avgSamples) / count($avgSamples) : 0.0;

    $compareWork = 'No recent average yet';
    if ($avgWork > 0 && $yDay) {
        $diffPct = round((($yWork - $avgWork) / $avgWork) * 100);
        if ($diffPct > 0) {
            $compareWork = $diffPct . '% above your month average';
        } elseif ($diffPct < 0) {
            $compareWork = abs($diffPct) . '% below your month average';
        } else {
            $compareWork = 'Matches your month average';
        }
    }

    $workdays_mtd = 0;
    $logged_mtd   = 0;
    foreach ($map as $date => $day) {
        if ($date > $today || substr($date, 0, 7) !== date('Y-m')) {
            continue;
        }
        $code = (string) ($day['code'] ?? '');
        if (in_array($code, ['WO', 'H'], true)) {
            continue;
        }
        $workdays_mtd++;
        $hrs = t2g_apex_parse_hours_value($day['actual_work_hrs_num'] ?? $day['hours'] ?? 0);
        if (in_array($code, ['P', 'HD'], true) || $hrs > 0) {
            $logged_mtd++;
        }
    }

    $snapshot_logged = false;
    $snapshot_time   = '';
    $driveTable      = db_prefix() . 'staff_drive_data';
    if ($CI->db->table_exists($driveTable)) {
        $CI->db->reset_query();
        $row = $CI->db->query(
            'SELECT MIN(date_time) AS first_at FROM ' . $driveTable . ' WHERE staff_id = ? AND date_time >= ?',
            [(int) $staff_id, date('Y-m-d 00:00:00')]
        )->row();
        if ($row && !empty($row->first_at)) {
            $snapshot_logged = true;
            $snapshot_time   = date('g:i A', strtotime($row->first_at));
        }
    }

    $onDuty = !empty($punch['biometric_navbar_active'])
        || ((int) ($punch['type_check_in_out'] ?? 0) === 1 && empty($punch['biometric_is_checked_out']));
    if ($snapshot_logged) {
        $snapshot_status = 'Snapshot logged today at ' . $snapshot_time;
        $snapshot_tone   = 'ok';
    } elseif ($onDuty) {
        $snapshot_status = 'Checked in — snapshot not detected yet today';
        $snapshot_tone   = 'warn';
    } else {
        $snapshot_status = 'No snapshot activity logged today yet';
        $snapshot_tone   = 'neutral';
    }

    $prev = is_array($pedma['previous_month'] ?? null) ? $pedma['previous_month'] : [];
    $pedma_score = $prev['overall_score'] ?? $pedma['score'] ?? null;
    $pedma_label = $prev['label'] ?? 'Previous month';
    $pedma_grade = $pedma['grade'] ?? '—';

    return [
        'interval_ms' => 6000,
        'slides'      => [
            [
                'key'     => 'yesterday',
                'title'   => 'Yesterday at work',
                'metric'  => t2g_apex_format_hours_label($yWork),
                'metric_sub' => 'Working hours',
                'lines'   => [
                    'Break: ' . t2g_apex_format_hours_label($yBreak),
                    $compareWork,
                ],
            ],
            [
                'key'    => 'snapshot',
                'title'  => 'Snapshot monitor',
                'metric' => $snapshot_logged ? 'Logged in' : 'Not logged',
                'metric_sub' => 'Today',
                'lines'  => [$snapshot_status],
                'tone'   => $snapshot_tone,
            ],
            [
                'key'    => 'attendance',
                'title'  => 'This month',
                'metric' => (string) (int) $logged_mtd,
                'metric_sub' => 'Days logged in',
                'lines'  => [
                    'Out of ' . (int) $workdays_mtd . ' working days so far in ' . date('F'),
                    ($workdays_mtd > 0
                        ? round(($logged_mtd / $workdays_mtd) * 100) . '% attendance days marked'
                        : 'Calendar data will appear as the month progresses'),
                ],
            ],
            [
                'key'    => 'pedma',
                'title'  => 'PEDMA performance',
                'metric' => $pedma_score !== null ? rtrim(rtrim(number_format((float) $pedma_score, 1, '.', ''), '0'), '.') : '—',
                'metric_sub' => $pedma_label . ' score',
                'lines'  => [
                    'Tier: ' . (string) $pedma_grade,
                    'Open PEDMA for KRA details and feedback',
                ],
            ],
        ],
    ];
}

/**
 * Daily motivational line (same rotation as HRMS home).
 */
function t2g_apex_thought_of_day()
{
    $quotes = [
        'Small daily improvements are the key to staggering long-term results.',
        'Your attitude determines your direction.',
        'Progress, not perfection.',
        'Consistency beats intensity when intensity is inconsistent.',
        'Do the best you can until you know better. Then when you know better, do better.',
        'Great things are done by a series of small things brought together.',
        'Focus on being productive instead of busy.',
        'Every accomplishment starts with the decision to try.',
        'Stay positive, work hard, make it happen.',
        'Success is the sum of small efforts repeated day in and day out.',
        'Be so good they can’t ignore you.',
        'The secret of getting ahead is getting started.',
        'Opportunities don’t happen. You create them.',
        'Quality is not an act, it is a habit.',
        'Make each day your masterpiece.',
    ];

    return $quotes[((int) date('z')) % count($quotes)];
}

/**
 * @param float|int|string|null $value
 */
function t2g_apex_format_leave_days($value)
{
    if ($value === null || $value === '') {
        return '—';
    }
    $value = (float) $value;
    if (abs($value - round($value)) < 0.001) {
        return (string) (int) round($value);
    }

    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}

/**
 * Staff-visible announcements (same visibility as the announcements module; not dismissed-only).
 *
 * @return array<int, array<string, mixed>>
 */
function t2g_apex_fetch_staff_announcements($CI, $limit = 10)
{
    $table = db_prefix() . 'announcements';
    if (!$CI->db->table_exists($table)) {
        return [];
    }

    $CI->db->reset_query();
    $CI->db->from($table);
    $CI->db->where('showtostaff', 1);
    if ($CI->db->field_exists('is_sop', $table)) {
        $CI->db->group_start();
        $CI->db->where('is_sop', 0);
        $CI->db->or_where('is_sop IS NULL', null, false);
        $CI->db->group_end();
    }
    $CI->db->order_by('dateadded', 'DESC');
    if ($limit > 0) {
        $CI->db->limit((int) $limit);
    }
    $rows = $CI->db->get()->result_array();

    return is_array($rows) ? $rows : [];
}

/**
 * @return array<string, mixed>
 */
function t2g_apex_workhub_default_pedma()
{
    $prev = new DateTime('first day of last month');

    return [
        'score'          => null,
        'grade'          => null,
        'kra_rating_max' => function_exists('pedma_kra_rating_max') ? pedma_kra_rating_max() : 5,
        'previous_month' => [
            'month_key'          => $prev->format('Y-m'),
            'label'              => $prev->format('F Y'),
            'overall_score'      => null,
            'manager_feedback'   => '',
            'hod_feedback'       => '',
            'hr_feedback'        => '',
            'kra_labels'         => [],
            'kra_achieved'       => [],
            'kra_max'            => [],
        ],
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function t2g_apex_parse_kra_data($raw)
{
    if (is_array($raw)) {
        return function_exists('pedma_normalize_kra_data_rows')
            ? pedma_normalize_kra_data_rows($raw)
            : $raw;
    }
    if ($raw === null || $raw === '') {
        return [];
    }
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    return function_exists('pedma_normalize_kra_data_rows')
        ? pedma_normalize_kra_data_rows($decoded)
        : $decoded;
}

/**
 * @param array<string, mixed> $item
 */
function t2g_apex_kra_achieved_score($item)
{
    if (!is_array($item)) {
        return 0.0;
    }
    if (isset($item['get_score']) && $item['get_score'] !== '' && $item['get_score'] !== null) {
        return (float) $item['get_score'];
    }
    $sum = 0.0;
    if (!empty($item['kpiData']) && is_array($item['kpiData'])) {
        foreach ($item['kpiData'] as $kpi) {
            if (is_array($kpi)) {
                $sum += (float) ($kpi['get_score'] ?? 0);
            }
        }
    }

    return $sum;
}

/**
 * @return array<string, mixed>
 */
function t2g_apex_workhub_load_pedma_month($CI, $staff_id, $month_key)
{
    $rating_max = function_exists('pedma_kra_rating_max') ? pedma_kra_rating_max() : 5;
    $ts         = strtotime($month_key . '-01');
    $payload    = [
        'month_key'        => $month_key,
        'label'            => $ts !== false ? date('F Y', $ts) : $month_key,
        'overall_score'    => null,
        'manager_feedback' => '',
        'hod_feedback'     => '',
        'hr_feedback'      => '',
        'kra_labels'       => [],
        'kra_achieved'     => [],
        'kra_max'          => [],
    ];

    if (!$CI->db->table_exists('tblstaff_performance')) {
        return $payload;
    }

    $CI->db->reset_query();
    $CI->db->from('tblstaff_performance');
    $CI->db->where('staffid', (int) $staff_id);
    $CI->db->where('status !=', 0);
    $CI->db->like('date_created', $month_key, 'after');
    $CI->db->order_by('date_created', 'DESC');
    $rows = $CI->db->get()->result_array();
    if ($rows === []) {
        return $payload;
    }

    $row = $rows[0];
    if (isset($row['avg_score']) && $row['avg_score'] !== '' && $row['avg_score'] !== null) {
        $ascore = (float) $row['avg_score'];
        $fatal  = !empty($row['fatal_error_score']) ? ((float) $row['fatal_error_score'] / 100) * $ascore : 0.0;
        $add    = !empty($row['add_on_score']) ? ((float) $row['add_on_score'] / 100) * $ascore : 0.0;
        $payload['overall_score'] = round($ascore - $fatal + $add, 2);
    }

    $payload['manager_feedback'] = trim((string) ($row['overall_manager_feedback'] ?? ''));
    $payload['hod_feedback']     = trim((string) ($row['overall_hod_feedback'] ?? ''));
    $payload['hr_feedback']      = trim((string) ($row['overall_hr_feedback'] ?? ''));

    if ($payload['manager_feedback'] === '' && $payload['hod_feedback'] === '' && $payload['hr_feedback'] === '') {
        $legacy = trim(strip_tags((string) ($row['overall_feedback'] ?? '')));
        if ($legacy !== '') {
            $payload['manager_feedback'] = $legacy;
        }
    }

    foreach (t2g_apex_parse_kra_data($row['kra_data'] ?? '') as $item) {
        $payload['kra_labels'][]   = (string) ($item['name'] ?? 'KRA');
        $payload['kra_achieved'][] = t2g_apex_kra_achieved_score($item);
        $payload['kra_max'][]      = $rating_max;
    }

    return $payload;
}

/**
 * @return array<string, mixed>
 */
function t2g_apex_workhub_load_pedma($CI, $staff_id)
{
    $pedma      = t2g_apex_workhub_default_pedma();
    $month_key  = $pedma['previous_month']['month_key'];
    $previous   = t2g_apex_workhub_load_pedma_month($CI, $staff_id, $month_key);
    $pedma['previous_month'] = $previous;

    if ($previous['overall_score'] !== null) {
        $pedma['score'] = (int) round((float) $previous['overall_score']);
        if ($CI->db->table_exists('tblstaff_performance')) {
            $CI->load->model('staff_model');
            if (method_exists($CI->staff_model, 'pedma_score_to_grade')) {
                $pedma['grade'] = $CI->staff_model->pedma_score_to_grade($pedma['score']);
            }
        }
    }

    return $pedma;
}

/**
 * @return array<string, string>
 */
function t2g_apex_workhub_routes()
{
    $CI          = &get_instance();
    $tickets_url = admin_url('tickets');
    if (!$CI->db->table_exists(db_prefix() . 'tickets')) {
        $tickets_url = admin_url('leads');
    }

    return [
        ''                    => admin_url(),
        'dashboard'           => admin_url(),
        'projects'            => admin_url('projects'),
        'tasks'               => admin_url('tasks/list_tasks'),
        'timesheets'          => admin_url('staff/timesheets'),
        'attendance'          => admin_url('timesheets/my_attendance'),
        'leave'               => admin_url('staff/leave_balance'),
        'tickets'             => $tickets_url,
        'announcements'       => admin_url('announcements'),
        'performance'         => admin_url('staff/pedma'),
        'pedma'               => admin_url('staff/pedma'),
        'reports'             => admin_url('reports'),
        'settings'            => admin_url('settings'),
        'view-all-tasks'      => admin_url('tasks/list_tasks'),
        'attendance-details'  => admin_url('timesheets/my_attendance'),
        'view-projects'       => admin_url('projects'),
        'view-leave'          => admin_url('staff/leave_balance'),
        'view-announcements'  => admin_url('announcements'),
        'break-history'       => admin_url('timesheets/my_attendance'),
        'appraisal'           => admin_url('staff/pedma'),
        'repo'                => admin_url('projects'),
        'profile'             => admin_url('profile'),
        'notifications'       => admin_url('profile?notifications=1'),
        'snapshot'            => admin_url('emt'),
        'company-feed'        => admin_url('announcements'),
    ];
}
