<?php

defined('BASEPATH') or exit('No direct script access allowed');

function t2g_apex_manager_ensure_snapshot_helpers()
{
    if (function_exists('snapshot_can_view_all_departments')) {
        return;
    }
    $CI = &get_instance();
    $CI->load->helper('custom');
}

/**
 * Active staff in one or more departments (same idea as Snapshot department filter).
 *
 * @param int[] $department_ids
 * @return int[]
 */
function t2g_apex_manager_active_staff_ids_in_departments(array $department_ids)
{
    $department_ids = array_values(array_unique(array_filter(array_map('intval', $department_ids))));
    if ($department_ids === []) {
        return [];
    }
    $CI = &get_instance();
    $in   = implode(',', $department_ids);
    $rows = $CI->db->query(
        'SELECT DISTINCT s.staffid FROM ' . db_prefix() . 'staff s
         INNER JOIN ' . db_prefix() . 'staff_departments sd ON sd.staffid = s.staffid
         WHERE s.active = 1 AND sd.departmentid IN (' . $in . ')'
    )->result_array();

    return array_values(array_unique(array_map('intval', array_column($rows, 'staffid'))));
}

/**
 * Department IDs that define this manager's team (Snapshot/PEDMA scope + dept head).
 *
 * @return int[]
 */
function t2g_apex_manager_department_ids_for_team($manager_id)
{
    t2g_apex_manager_ensure_snapshot_helpers();
    $manager_id = (int) $manager_id;
    $dept_ids   = [];

    if (snapshot_can_view_all_departments($manager_id)) {
        $CI = &get_instance();
        $CI->load->model('departments_model');
        foreach ($CI->departments_model->get() as $dep) {
            $id = is_array($dep) ? (int) ($dep['departmentid'] ?? 0) : (int) ($dep->departmentid ?? 0);
            if ($id > 0) {
                $dept_ids[] = $id;
            }
        }

        return array_values(array_unique($dept_ids));
    }

    if (function_exists('snapshot_viewer_department_ids')) {
        $scope = snapshot_viewer_department_ids($manager_id);
        if (is_array($scope) && $scope !== []) {
            $dept_ids = array_merge($dept_ids, $scope);
        }
    }

    $CI = &get_instance();
    $CI->db->reset_query();
    $rows = $CI->db->select('departmentid')
        ->from(db_prefix() . 'departments')
        ->where('manager_id', $manager_id)
        ->get()
        ->result_array();
    foreach ($rows as $row) {
        $id = (int) ($row['departmentid'] ?? 0);
        if ($id > 0) {
            $dept_ids[] = $id;
        }
    }

    return array_values(array_unique($dept_ids));
}

/**
 * Department filter options (same list as Snapshot / PEDMA for this manager).
 *
 * @return array<int, array{id:int, name:string}>
 */
function t2g_apex_manager_department_filter_options($manager_id)
{
    if (!function_exists('pedma_departments_for_viewer')) {
        return [];
    }
    $deps = pedma_departments_for_viewer($manager_id);
    $out  = [];
    foreach ($deps as $dep) {
        $id   = is_array($dep) ? (int) ($dep['departmentid'] ?? 0) : (int) ($dep->departmentid ?? 0);
        $name = is_array($dep) ? trim((string) ($dep['name'] ?? '')) : trim((string) ($dep->name ?? ''));
        if ($id > 0 && $name !== '') {
            $out[] = ['id' => $id, 'name' => $name];
        }
    }

    return $out;
}

/**
 * Team member staff IDs for the manager Team tab (same department scope as Snapshot / PEDMA).
 *
 * @return array<int>
 */
function t2g_apex_manager_team_report_ids($manager_id)
{
    t2g_apex_manager_ensure_snapshot_helpers();
    $manager_id = (int) $manager_id;
    if ($manager_id <= 0) {
        return [];
    }

    if (snapshot_can_view_all_departments($manager_id)) {
        $CI = &get_instance();
        $rows = $CI->db->select('staffid')
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->get()
            ->result_array();

        return array_values(array_unique(array_filter(array_map('intval', array_column($rows, 'staffid')), function ($id) {
            return $id > 0;
        })));
    }

    $dept_ids = t2g_apex_manager_department_ids_for_team($manager_id);
    if ($dept_ids === []) {
        return [];
    }

    $ids = t2g_apex_manager_active_staff_ids_in_departments($dept_ids);

    if (function_exists('snapshot_staff_id_in_viewer_scope')) {
        $ids = array_values(array_filter($ids, function ($sid) use ($manager_id) {
            return snapshot_staff_id_in_viewer_scope($sid, $manager_id);
        }));
    }

    return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) use ($manager_id) {
        return $id > 0 && $id !== $manager_id;
    })));
}

/**
 * @return null|string present|absent|leave
 */
function t2g_apex_manager_attendance_bucket($code)
{
    $code = strtoupper(trim((string) $code));
    if ($code === 'P' || $code === 'HD') {
        return 'present';
    }
    if (in_array($code, ['AB', 'NC'], true)) {
        return 'absent';
    }
    if ($code !== '' && (strpos($code, 'L') === 0 || in_array($code, ['EL', 'SL', 'PL', 'CL', 'ML'], true))) {
        return 'leave';
    }

    return null;
}

/**
 * @return array{attendance_pct:?int,calendar_days:array<int,array<string,mixed>>}
 */
function t2g_apex_manager_staff_calendar($CI, $staff_id)
{
    $attendance_pct = null;
    $calendar_days  = [];
    try {
        if ($CI->app_modules->is_active('timesheets')) {
            $CI->load->model('timesheets/timesheets_model');
            if (method_exists($CI->timesheets_model, 'get_attendance_regularisation_calendar')) {
                $y   = (int) date('Y');
                $m   = (int) date('m');
                $cal = $CI->timesheets_model->get_attendance_regularisation_calendar((int) $staff_id, $y, $m);
                $calendar_days = $cal['days'] ?? [];
                $present = 0;
                $counted = 0;
                foreach ($calendar_days as $d) {
                    if (empty($d['date'])) {
                        continue;
                    }
                    $code = $d['code'] ?? '';
                    if (in_array($code, ['WO', 'H'], true)) {
                        continue;
                    }
                    $counted++;
                    if ($code === 'P' || ($d['status'] ?? '') === 'ok') {
                        $present++;
                    } elseif ($code === 'HD' || ($d['status'] ?? '') === 'half_day') {
                        $present += 0.5;
                    }
                }
                if ($counted > 0) {
                    $attendance_pct = (int) round(($present / $counted) * 100);
                }
            }
        }
    } catch (Throwable $e) {
        log_message('error', 'Manager team calendar: ' . $e->getMessage());
    }

    return ['attendance_pct' => $attendance_pct, 'calendar_days' => $calendar_days];
}

function t2g_apex_manager_day_code(array $calendar_days, $ymd)
{
    foreach ($calendar_days as $day) {
        if (($day['date'] ?? '') === $ymd) {
            return (string) ($day['code'] ?? '');
        }
    }

    return '';
}

/**
 * @param int[] $staff_ids
 * @return array<int, array<string, int>>
 */
function t2g_apex_manager_batch_checkins_by_date($CI, array $staff_ids, array $ymd_list)
{
    $staff_ids = array_values(array_unique(array_filter(array_map('intval', $staff_ids))));
    $ymd_list  = array_values(array_unique(array_filter($ymd_list)));
    if ($staff_ids === [] || $ymd_list === []) {
        return [];
    }
    $table = db_prefix() . 'check_in_out';
    if (!$CI->db->table_exists($table)) {
        return [];
    }
    $in  = implode(',', $staff_ids);
    $min = min($ymd_list);
    $max = max($ymd_list);
    $rows = $CI->db->query(
        'SELECT staff_id, DATE(date) AS work_day, MIN(date) AS first_in FROM ' . $table
        . ' WHERE staff_id IN (' . $in . ') AND type_check = 1 AND DATE(date) BETWEEN ? AND ?'
        . ' GROUP BY staff_id, DATE(date)',
        [$min, $max]
    )->result_array();
    $map = [];
    foreach ($rows as $row) {
        $sid = (int) ($row['staff_id'] ?? 0);
        $day = (string) ($row['work_day'] ?? '');
        if ($sid < 1 || $day === '') {
            continue;
        }
        $map[$sid][$day] = strtotime($row['first_in']);
    }

    return $map;
}

/**
 * Approved leave mapped to calendar days in range (lightweight; not full HR calendar).
 *
 * @param int[] $staff_ids
 * @return array<int, array<string, string>>
 */
function t2g_apex_manager_batch_leave_days($CI, array $staff_ids, array $ymd_list)
{
    $staff_ids = array_values(array_unique(array_filter(array_map('intval', $staff_ids))));
    $ymd_list  = array_values(array_unique(array_filter($ymd_list)));
    if ($staff_ids === [] || $ymd_list === []) {
        return [];
    }
    $leave_table = db_prefix() . 'timesheets_requisition_leave';
    if (!$CI->db->table_exists($leave_table)) {
        return [];
    }
    $in  = implode(',', $staff_ids);
    $min = min($ymd_list);
    $max = max($ymd_list);
    $ymd_set = array_flip($ymd_list);
    try {
        $rows = $CI->db->query(
            'SELECT staff_id, start_time, end_time FROM ' . $leave_table
            . ' WHERE staff_id IN (' . $in . ') AND status = 1'
            . ' AND start_time <= ? AND end_time >= ?',
            [$max . ' 23:59:59', $min . ' 00:00:00']
        )->result_array();
    } catch (Throwable $e) {
        return [];
    }
    $map = [];
    foreach ($rows as $row) {
        $sid = (int) ($row['staff_id'] ?? 0);
        if ($sid < 1) {
            continue;
        }
        $start = strtotime(date('Y-m-d', strtotime((string) ($row['start_time'] ?? ''))));
        $end   = strtotime(date('Y-m-d', strtotime((string) ($row['end_time'] ?? ''))));
        if ($start <= 0 || $end <= 0) {
            continue;
        }
        for ($t = $start; $t <= $end; $t += 86400) {
            $d = date('Y-m-d', $t);
            if (isset($ymd_set[$d])) {
                $map[$sid][$d] = 'L';
            }
        }
    }

    return $map;
}

function t2g_apex_manager_fast_day_code(array $leave_days, array $checkins, $staff_id, $ymd)
{
    $staff_id = (int) $staff_id;
    if (!empty($leave_days[$staff_id][$ymd])) {
        return (string) $leave_days[$staff_id][$ymd];
    }
    if (!empty($checkins[$staff_id][$ymd])) {
        return 'P';
    }

    return 'AB';
}

/**
 * @param int[]      $staff_ids
 * @param int[]|null $scope_department_ids when set, only names in these departments
 * @return array<int, string>
 */
function t2g_apex_manager_batch_department_labels($CI, array $staff_ids, array $scope_department_ids = null)
{
    $staff_ids = array_values(array_unique(array_filter(array_map('intval', $staff_ids))));
    if ($staff_ids === []) {
        return [];
    }
    $in = implode(',', $staff_ids);
    $dept_sql = '';
    if ($scope_department_ids !== null && $scope_department_ids !== []) {
        $dept_sql = ' AND sd.departmentid IN (' . implode(',', array_map('intval', $scope_department_ids)) . ')';
    }
    $rows = $CI->db->query(
        'SELECT sd.staffid, d.name FROM ' . db_prefix() . 'staff_departments sd'
        . ' INNER JOIN ' . db_prefix() . 'departments d ON d.departmentid = sd.departmentid'
        . ' WHERE sd.staffid IN (' . $in . ')' . $dept_sql . ' ORDER BY d.name ASC'
    )->result_array();
    $map = [];
    foreach ($rows as $row) {
        $sid = (int) ($row['staffid'] ?? 0);
        $name = trim((string) ($row['name'] ?? ''));
        if ($sid < 1 || $name === '') {
            continue;
        }
        if (!isset($map[$sid])) {
            $map[$sid] = $name;
        } else {
            $map[$sid] .= ', ' . $name;
        }
    }

    return $map;
}

/**
 * Minimal team payload so manager home loads without running full team aggregation.
 *
 * @return array<string, mixed>
 */
/**
 * PEDMA grade counts (A includes A+) for team members in one evaluation month.
 *
 * @param int[] $team_ids
 * @return array{month: string, month_label: string, grades: array{A:int,B:int,C:int}, evaluated: int, team_count: int}
 */
function t2g_apex_manager_team_pedma_grades($CI, array $team_ids, $month_ym = null)
{
    $team_ids = array_values(array_unique(array_filter(array_map('intval', $team_ids))));
    $empty    = [
        'month'       => '',
        'month_label' => '',
        'grades'      => ['A' => 0, 'B' => 0, 'C' => 0],
        'evaluated'   => 0,
        'team_count'  => count($team_ids),
    ];
    if ($team_ids === [] || !$CI->db->table_exists(db_prefix() . 'staff_performance')) {
        return $empty;
    }

    $CI->load->model('staff_model');
    if ($month_ym === null || !preg_match('/^\d{4}-\d{2}$/', (string) $month_ym)) {
        $month_ym = $CI->staff_model->get_pedma_eval_target_month();
    }

    $from = $month_ym . '-01';
    $to   = date('Y-m-t', strtotime($from));
    $in   = implode(',', $team_ids);

    try {
        $rows = $CI->db->query(
            'SELECT staffid, avg_score, fatal_error_score, add_on_score, date_created FROM ' . db_prefix() . 'staff_performance'
            . ' WHERE staffid IN (' . $in . ') AND status != 0'
            . ' AND date_created >= ? AND date_created <= ?'
            . ' ORDER BY date_created DESC',
            [$from, $to . ' 23:59:59']
        )->result_array();
    } catch (Throwable $e) {
        log_message('error', 'Manager PEDMA grades: ' . $e->getMessage());

        return $empty;
    }

    $latest_by_staff = [];
    foreach ($rows as $row) {
        $sid = (int) ($row['staffid'] ?? 0);
        if ($sid < 1 || isset($latest_by_staff[$sid])) {
            continue;
        }
        $latest_by_staff[$sid] = $row;
    }

    $grades = ['A' => 0, 'B' => 0, 'C' => 0];
    foreach ($latest_by_staff as $row) {
        $score = $CI->staff_model->compute_pedma_final_score_row($row);
        $band  = $CI->staff_model->pedma_score_to_grade($score);
        if ($band === 'A+' || $band === 'A') {
            $grades['A']++;
        } elseif ($band === 'B') {
            $grades['B']++;
        } elseif ($band === 'C') {
            $grades['C']++;
        }
    }

    return [
        'month'       => $month_ym,
        'month_label' => date('F Y', strtotime($from)),
        'grades'      => $grades,
        'evaluated'   => $grades['A'] + $grades['B'] + $grades['C'],
        'team_count'  => count($team_ids),
    ];
}

function t2g_apex_manager_team_dashboard_placeholder($manager_id = 0)
{
    $manager_id = $manager_id > 0 ? (int) $manager_id : (int) get_staff_user_id();
    $n          = count(t2g_apex_manager_team_report_ids($manager_id));
    $trend      = ['labels' => [], 'present' => [], 'absent' => [], 'leave' => []];
    for ($i = 5; $i >= 0; $i--) {
        $trend['labels'][] = date('D', strtotime('-' . $i . ' days'));
        $trend['present'][] = 0;
        $trend['absent'][] = 0;
        $trend['leave'][] = 0;
    }

    return [
        'deferred'      => true,
        'manager_id'    => $manager_id,
        'team_size'     => $n,
        'period_label'  => date('F Y'),
        'kpis'          => [
            'total_employees' => $n,
            'present_today'   => 0,
            'on_leave'        => 0,
            'active_tasks'    => 0,
            'avg_checkin'     => '—',
            'attendance_rate' => 0,
        ],
        'charts'        => [
            'attendance_trend' => $trend,
            'leave_summary'    => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'total' => 0],
            'performance'      => [
                'productivity'    => null,
                'on_track'        => 0,
                'at_risk'         => 0,
                'needs_attention' => 0,
            ],
            'pedma_grades'     => [
                'month'       => '',
                'month_label' => 'Previous month',
                'grades'      => ['A' => 0, 'B' => 0, 'C' => 0],
                'evaluated'   => 0,
                'team_count'  => $n,
            ],
        ],
        'live_status'    => [],
        'leave_balances' => [],
        'upcoming_leaves'=> [],
        'insights'            => [['tone' => 'neutral', 'text' => 'Open this tab to load live team metrics.']],
        'department_filters'  => t2g_apex_manager_department_filter_options($manager_id),
        'scope_department_ids'=> t2g_apex_manager_department_ids_for_team($manager_id),
        'urls'                => function_exists('t2g_apex_workhub_routes') ? t2g_apex_workhub_routes() : [],
    ];
}

function t2g_apex_manager_staff_department_label($CI, $staff_id)
{
    try {
        if (!isset($CI->departments_model)) {
            $CI->load->model('departments_model');
        }
        if (method_exists($CI->departments_model, 'get_staff_departments')) {
            $deps = $CI->departments_model->get_staff_departments((int) $staff_id, true);
            if (is_array($deps) && $deps !== []) {
                return implode(', ', array_column($deps, 'name'));
            }
        }
    } catch (Throwable $e) {
    }

    return '—';
}

function t2g_apex_manager_first_checkin($CI, $staff_id, $ymd)
{
    $table = db_prefix() . 'check_in_out';
    if (!$CI->db->table_exists($table)) {
        return null;
    }
    try {
        $CI->db->reset_query();
        $row = $CI->db->query(
            'SELECT MIN(date) AS first_in FROM ' . $table
            . ' WHERE staff_id = ? AND DATE(date) = ? AND type_check = 1',
            [(int) $staff_id, $ymd]
        )->row();
        if ($row && !empty($row->first_in)) {
            return strtotime($row->first_in);
        }
    } catch (Throwable $e) {
    }

    return null;
}

function t2g_apex_manager_active_task($CI, $staff_id)
{
    $table = db_prefix() . 'taskstimers';
    if (!$CI->db->table_exists($table)) {
        return null;
    }
    try {
        $CI->db->reset_query();
        $row = $CI->db->query(
            'SELECT t.name, t.status, tt.start_time, t.id AS task_id
             FROM ' . $table . ' tt
             INNER JOIN ' . db_prefix() . 'tasks t ON t.id = tt.task_id
             WHERE tt.staff_id = ? AND (tt.end_time IS NULL OR tt.end_time = "0000-00-00 00:00:00")
             ORDER BY tt.start_time DESC LIMIT 1',
            [(int) $staff_id]
        )->row_array();
        if ($row) {
            $progress = 25;
            $s = (int) ($row['status'] ?? 0);
            if ($s === 4) {
                $progress = 100;
            } elseif ($s === 3) {
                $progress = 50;
            } elseif ($s === 2) {
                $progress = 25;
            }

            return [
                'name'     => $row['name'] ?? '—',
                'progress' => $progress,
                'task_id'  => (int) ($row['task_id'] ?? 0),
                'started'  => !empty($row['start_time']) ? date('g:i A', strtotime($row['start_time'])) : '—',
                'since_ts' => !empty($row['start_time']) ? strtotime($row['start_time']) : null,
            ];
        }
    } catch (Throwable $e) {
    }

    return null;
}

function t2g_apex_manager_snapshot_today($CI, $staff_id)
{
    $table = db_prefix() . 'staff_drive_data';
    if (!$CI->db->table_exists($table)) {
        return false;
    }
    try {
        $CI->db->reset_query();
        $row = $CI->db->query(
            'SELECT 1 FROM ' . $table . ' WHERE staff_id = ? AND date_time >= ? LIMIT 1',
            [(int) $staff_id, date('Y-m-d 00:00:00')]
        )->row();

        return (bool) $row;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * @param int[] $staff_ids
 * @return array<int, array<string, mixed>>
 */
function t2g_apex_manager_batch_active_tasks($CI, array $staff_ids)
{
    $staff_ids = array_values(array_unique(array_filter(array_map('intval', $staff_ids))));
    if ($staff_ids === []) {
        return [];
    }
    $table = db_prefix() . 'taskstimers';
    if (!$CI->db->table_exists($table)) {
        return [];
    }
    $map = [];
    try {
        foreach (array_chunk($staff_ids, 400) as $chunk) {
            $in = implode(',', $chunk);
            $CI->db->reset_query();
            $rows = $CI->db->query(
                'SELECT tt.staff_id, t.name, t.status, tt.start_time, t.id AS task_id
                 FROM ' . $table . ' tt
                 INNER JOIN ' . db_prefix() . 'tasks t ON t.id = tt.task_id
                 WHERE tt.staff_id IN (' . $in . ') AND (tt.end_time IS NULL OR tt.end_time = "0000-00-00 00:00:00")
                 ORDER BY tt.staff_id ASC, tt.start_time DESC'
            )->result_array();
            foreach ($rows as $row) {
        $sid = (int) ($row['staff_id'] ?? 0);
        if ($sid < 1 || isset($map[$sid])) {
            continue;
        }
        $progress = 25;
        $s = (int) ($row['status'] ?? 0);
        if ($s === 4) {
            $progress = 100;
        } elseif ($s === 3) {
            $progress = 50;
        } elseif ($s === 2) {
            $progress = 25;
        }
                $map[$sid] = [
                    'name'     => $row['name'] ?? '—',
                    'progress' => $progress,
                    'task_id'  => (int) ($row['task_id'] ?? 0),
                    'started'  => !empty($row['start_time']) ? date('g:i A', strtotime($row['start_time'])) : '—',
                    'since_ts' => !empty($row['start_time']) ? strtotime($row['start_time']) : null,
                ];
            }
        }
    } catch (Throwable $e) {
        return $map;
    }

    return $map;
}

/**
 * @param int[] $staff_ids
 * @return array<int, bool>
 */
function t2g_apex_manager_batch_snapshot_today($CI, array $staff_ids)
{
    $staff_ids = array_values(array_unique(array_filter(array_map('intval', $staff_ids))));
    if ($staff_ids === []) {
        return [];
    }
    $table = db_prefix() . 'staff_drive_data';
    if (!$CI->db->table_exists($table)) {
        return [];
    }
    $map = [];
    try {
        foreach (array_chunk($staff_ids, 400) as $chunk) {
            $in = implode(',', $chunk);
            $CI->db->reset_query();
            $rows = $CI->db->query(
                'SELECT DISTINCT staff_id FROM ' . $table . ' WHERE staff_id IN (' . $in . ') AND date_time >= ?',
                [date('Y-m-d 00:00:00')]
            )->result_array();
            foreach ($rows as $row) {
                $sid = (int) ($row['staff_id'] ?? 0);
                if ($sid > 0) {
                    $map[$sid] = true;
                }
            }
        }
    } catch (Throwable $e) {
        return $map;
    }

    return $map;
}

/**
 * Manager Team dashboard payload (matches team tab mockup).
 *
 * @return array<string, mixed>
 */
function t2g_apex_gather_manager_team_dashboard($manager_id = 0)
{
    $CI = &get_instance();
    t2g_apex_manager_ensure_snapshot_helpers();
    $manager_id = $manager_id > 0 ? (int) $manager_id : (int) get_staff_user_id();
    $company_wide = snapshot_can_view_all_departments($manager_id);
    $team_ids         = t2g_apex_manager_team_report_ids($manager_id);
    $scope_dept_ids   = t2g_apex_manager_department_ids_for_team($manager_id);
    $department_filters = t2g_apex_manager_department_filter_options($manager_id);
    $today            = date('Y-m-d');
    $total            = count($team_ids);
    $active_task_map  = t2g_apex_manager_batch_active_tasks($CI, $team_ids);
    $snapshot_map     = t2g_apex_manager_batch_snapshot_today($CI, $team_ids);

    $present_count = 0;
    $leave_count   = 0;
    $checkin_ts    = [];
    $live_rows     = [];
    $leave_balance_rows = [];
    $on_track = 0;
    $at_risk = 0;
    $needs_attention = 0;
    $active_tasks_total = 0;

    $trend_days = [];
    for ($i = 5; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
        $trend_days[$d] = ['label' => date('D', strtotime($d)), 'present' => 0, 'absent' => 0, 'leave' => 0];
    }

    $trend_ymd    = array_keys($trend_days);
    $checkin_map  = t2g_apex_manager_batch_checkins_by_date($CI, $team_ids, $trend_ymd);
    $leave_map    = t2g_apex_manager_batch_leave_days($CI, $team_ids, $trend_ymd);
    $dept_labels  = t2g_apex_manager_batch_department_labels($CI, $team_ids, $scope_dept_ids);
    $staff_rows   = [];
    if ($team_ids !== []) {
        foreach (array_chunk($team_ids, 400) as $chunk) {
            $in = implode(',', array_map('intval', $chunk));
            try {
                foreach ($CI->db->query(
                    'SELECT staffid, firstname, lastname, staff_identifi FROM ' . db_prefix() . 'staff WHERE staffid IN (' . $in . ')'
                )->result_array() as $row) {
                    $staff_rows[(int) $row['staffid']] = $row;
                }
            } catch (Throwable $e) {
                log_message('error', 'Manager team staff rows: ' . $e->getMessage());
            }
        }
    }

    foreach ($team_ids as $sid) {
        $code_today = t2g_apex_manager_fast_day_code($leave_map, $checkin_map, $sid, $today);
        $bucket = t2g_apex_manager_attendance_bucket($code_today);

        $status_key = 'absent';
        $status_label = 'Absent';
        if ($bucket === 'present') {
            $present_count++;
            $status_key = 'present';
            $status_label = 'Present';
        } elseif ($bucket === 'leave') {
            $leave_count++;
            $status_key = 'leave';
            $status_label = 'On leave';
        }

        foreach ($trend_days as $ymd => $_t) {
            $c = t2g_apex_manager_fast_day_code($leave_map, $checkin_map, $sid, $ymd);
            $b = t2g_apex_manager_attendance_bucket($c);
            if ($b === 'present') {
                $trend_days[$ymd]['present']++;
            } elseif ($b === 'leave') {
                $trend_days[$ymd]['leave']++;
            } elseif ($b === 'absent') {
                $trend_days[$ymd]['absent']++;
            }
        }

        if ($bucket === 'present') {
            $on_track++;
        } elseif ($bucket === 'leave') {
            $at_risk++;
        } else {
            $needs_attention++;
        }

        $checkin = $checkin_map[$sid][$today] ?? null;
        if ($checkin) {
            $checkin_ts[] = $checkin;
        }

        $task = $active_task_map[$sid] ?? null;
        if ($task) {
            $active_tasks_total++;
        }

        $staff_row = $staff_rows[$sid] ?? null;
        $emp_id = ($staff_row && !empty($staff_row['staff_identifi'])) ? $staff_row['staff_identifi'] : 'EMP' . $sid;

        $live_rows[] = [
            'staff_id'    => (int) $sid,
            'name'        => get_staff_full_name($sid),
            'emp_id'      => $emp_id,
            'department'  => $dept_labels[$sid] ?? '—',
            'avatar'      => staff_profile_image_url($sid, 'small'),
            'check_in'    => $checkin ? date('g:i A', $checkin) : '—',
            'check_in_ago'=> $checkin ? t2g_apex_manager_time_ago($checkin) : '—',
            'status_key'  => $status_key,
            'status_label'=> $status_label,
            'leave_label' => $bucket === 'leave' ? ($code_today ?: 'Leave') : '—',
            'task'        => $task,
            'break_label' => $status_key === 'present' ? 'Working' : ($status_key === 'leave' ? 'On leave' : '—'),
            'break_tone'  => $status_key === 'present' ? 'working' : 'neutral',
            'snapshot'    => !empty($snapshot_map[$sid]),
            'profile_url' => admin_url('staff/member/' . $sid),
            'timesheet_url' => admin_url('staff/timesheets/' . $sid),
        ];

        if (!$company_wide) {
            $earned = 0.0;
            $taken = 0.0;
            $balance = 0.0;
            try {
                if ($CI->app_modules->is_active('timesheets')) {
                    $CI->load->model('timesheets/timesheets_model');
                    if (method_exists($CI->timesheets_model, 'get_staff_leave_balance_cards')) {
                        $cards = $CI->timesheets_model->get_staff_leave_balance_cards((int) $sid, (int) date('Y'), (int) date('m'));
                        foreach ($cards as $card) {
                            if ((string) ($card['slug'] ?? '') === 'earned-leave') {
                                $earned = (float) ($card['granted'] ?? 0);
                                $taken = (float) ($card['consumed'] ?? 0);
                                $balance = (float) ($card['available'] ?? $card['balance'] ?? 0);
                                break;
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
            }

            $leave_balance_rows[] = [
                'staff_id' => (int) $sid,
                'name'     => get_staff_full_name($sid),
                'earned'   => round($earned, 1),
                'taken'    => round($taken, 1),
                'balance'  => round($balance, 1),
                'available'=> $balance > 0 ? 'Available' : 'Low balance',
            ];
        }
    }

    $avg_checkin = '—';
    if ($checkin_ts !== []) {
        $avg = (int) round(array_sum($checkin_ts) / count($checkin_ts));
        $avg_checkin = date('g:i A', $avg);
    }

    $leave_summary = ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'total' => 0];
    $upcoming_leaves = [];
    $leave_table = db_prefix() . 'timesheets_requisition_leave';
    if ($team_ids !== [] && $CI->db->table_exists($leave_table)) {
        $month_start = date('Y-m-01 00:00:00');
        try {
            if ($company_wide) {
                $rows = $CI->db->query(
                    'SELECT status, COUNT(*) AS c FROM ' . $leave_table
                    . ' WHERE datecreated >= ? GROUP BY status',
                    [$month_start]
                )->result_array();
                $upcoming = $CI->db->query(
                    'SELECT staff_id, start_time, end_time, status, subject FROM ' . $leave_table
                    . ' WHERE start_time >= ? ORDER BY start_time ASC LIMIT 8',
                    [date('Y-m-d 00:00:00')]
                )->result_array();
            } else {
                $ids_sql = implode(',', array_map('intval', $team_ids));
                $rows = $CI->db->query(
                    'SELECT status, COUNT(*) AS c FROM ' . $leave_table
                    . ' WHERE staff_id IN (' . $ids_sql . ') AND datecreated >= ? GROUP BY status',
                    [$month_start]
                )->result_array();
                $upcoming = $CI->db->query(
                    'SELECT staff_id, start_time, end_time, status, subject FROM ' . $leave_table
                    . ' WHERE staff_id IN (' . $ids_sql . ') AND start_time >= ? ORDER BY start_time ASC LIMIT 8',
                    [date('Y-m-d 00:00:00')]
                )->result_array();
            }
            foreach ($rows as $r) {
                $st = (int) ($r['status'] ?? -1);
                $c = (int) ($r['c'] ?? 0);
                $leave_summary['total'] += $c;
                if ($st === 1) {
                    $leave_summary['approved'] += $c;
                } elseif ($st === 0) {
                    $leave_summary['pending'] += $c;
                } elseif ($st === 2) {
                    $leave_summary['rejected'] += $c;
                }
            }
            foreach ($upcoming as $u) {
                $upcoming_leaves[] = [
                    'name'  => get_staff_full_name((int) $u['staff_id']),
                    'type'  => !empty($u['subject']) ? $u['subject'] : 'Leave',
                    'range' => t2g_apex_manager_date_range_label($u['start_time'] ?? '', $u['end_time'] ?? ''),
                    'status'=> (int) ($u['status'] ?? 0) === 1 ? 'Approved' : (((int) ($u['status'] ?? 0) === 0) ? 'Pending' : 'Rejected'),
                ];
            }
        } catch (Throwable $e) {
            log_message('error', 'Manager leave summary: ' . $e->getMessage());
        }
    }

    $productivity = $total > 0 ? (int) round(($present_count / $total) * 100) : null;

    $insights = [];
    if ($company_wide) {
        $insights[] = [
            'tone' => 'info',
            'text' => 'Company-wide view: earned-leave balance table is omitted for speed. KPIs and live status include all active staff.',
        ];
    }
    $not_checked = max(0, $total - count($checkin_ts));
    if ($not_checked > 0) {
        $insights[] = ['tone' => 'danger', 'text' => $not_checked . ' team member' . ($not_checked === 1 ? ' has' : 's have') . ' not checked in yet today.'];
    }
    if ($leave_count > 0) {
        $insights[] = ['tone' => 'info', 'text' => $leave_count . ' on approved leave today.'];
    }
    if ($active_tasks_total > 0) {
        $insights[] = ['tone' => 'success', 'text' => $active_tasks_total . ' active task timer' . ($active_tasks_total === 1 ? '' : 's') . ' running across the team.'];
    }
    if ($productivity !== null) {
        $insights[] = ['tone' => 'purple', 'text' => 'Team attendance productivity this month is at ' . $productivity . '%.'];
    }
    if ($insights === []) {
        $insights[] = ['tone' => 'neutral', 'text' => 'No alerts right now. Team metrics look steady.'];
    }

    $trend_labels = [];
    $trend_present = [];
    $trend_absent = [];
    $trend_leave = [];
    foreach ($trend_days as $row) {
        $trend_labels[] = $row['label'];
        $trend_present[] = (int) $row['present'];
        $trend_absent[] = (int) $row['absent'];
        $trend_leave[] = (int) $row['leave'];
    }

    $pedma_grades = t2g_apex_manager_team_pedma_grades($CI, $team_ids);
    if ($pedma_grades['evaluated'] > 0) {
        $insights[] = [
            'tone' => 'purple',
            'text' => 'PEDMA (' . $pedma_grades['month_label'] . '): '
                . $pedma_grades['grades']['A'] . ' A, '
                . $pedma_grades['grades']['B'] . ' B, '
                . $pedma_grades['grades']['C'] . ' C.',
        ];
    }

    return [
        'manager_id'    => $manager_id,
        'team_size'     => $total,
        'period_label'  => date('F Y'),
        'kpis'          => [
            'total_employees' => $total,
            'present_today'     => $present_count,
            'on_leave'          => $leave_count,
            'active_tasks'      => $active_tasks_total,
            'avg_checkin'       => $avg_checkin,
            'attendance_rate'   => $total > 0 ? (int) round(($present_count / $total) * 100) : 0,
        ],
        'charts'        => [
            'attendance_trend' => [
                'labels'  => $trend_labels,
                'present' => $trend_present,
                'absent'  => $trend_absent,
                'leave'   => $trend_leave,
            ],
            'leave_summary' => $leave_summary,
            'performance'   => [
                'productivity'     => $productivity,
                'on_track'         => $on_track,
                'at_risk'          => $at_risk,
                'needs_attention'  => $needs_attention,
            ],
            'pedma_grades'  => $pedma_grades,
        ],
        'live_status'   => $live_rows,
        'leave_balances'=> $leave_balance_rows,
        'upcoming_leaves'=> $upcoming_leaves,
        'insights'            => $insights,
        'department_filters'  => $department_filters,
        'scope_department_ids'=> $scope_dept_ids,
        'urls'                => t2g_apex_workhub_routes(),
    ];
}

function t2g_apex_manager_time_ago($timestamp)
{
    $diff = time() - (int) $timestamp;
    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return (int) floor($diff / 60) . 'm ago';
    }
    if ($diff < 86400) {
        return (int) floor($diff / 3600) . 'h ago';
    }

    return date('M j', $timestamp);
}

function t2g_apex_manager_date_range_label($start, $end)
{
    if (empty($start)) {
        return '—';
    }
    $s = date('d M', strtotime($start));
    if (empty($end) || date('Y-m-d', strtotime($start)) === date('Y-m-d', strtotime($end))) {
        return $s;
    }

    return $s . ' – ' . date('d M', strtotime($end));
}
