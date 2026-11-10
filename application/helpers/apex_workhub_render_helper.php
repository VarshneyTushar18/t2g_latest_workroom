<?php

defined('BASEPATH') or exit('No direct script access allowed');

function t2g_apex_h($s)
{
    return html_escape((string) $s);
}

function t2g_apex_table_heading($label)
{
    $label = trim((string) $label);
    if ($label === '' || $label === '#') {
        return $label;
    }
    $lower = mb_strtolower($label, 'UTF-8');

    return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 1, null, 'UTF-8');
}

function t2g_apex_sortable_th($label, $col_index)
{
    return '<th data-sort-col="' . (int) $col_index . '" class="sorting">'
        . html_escape(t2g_apex_table_heading($label))
        . ' <i class="fa fa-sort apex-sort-icon text-muted" aria-hidden="true"></i></th>';
}

function t2g_apex_task_progress_from_status($status)
{
    $s = (int) $status;
    if ($s === Tasks_model::STATUS_COMPLETE) {
        return 100;
    }
    if ($s === 4) {
        return 75;
    }
    if ($s === 3) {
        return 50;
    }
    if ($s === 2) {
        return 25;
    }

    return 10;
}

function t2g_apex_task_priority_badge($priority)
{
    $p = (int) $priority;
    if ($p >= 3) {
        return ['High', 'rose'];
    }
    if ($p === 2) {
        return ['Medium', 'amber'];
    }

    return ['Low', 'sky'];
}

function t2g_apex_replace_section_by_purpose($html, $purpose, $innerHtml)
{
    $pattern = '/(<section[^>]*data-purpose="' . preg_quote($purpose, '/') . '"[^>]*>)(.*?)(<\/section>)/s';

    return preg_replace($pattern, '$1' . $innerHtml . '$3', $html, 1);
}

function t2g_apex_render_kpi_section($d)
{
    $k   = $d['kpi'];
    $u   = $d['urls'];
    $att = $k['attendance_pct'] !== null ? $k['attendance_pct'] . '%' : '—';
    $leave = $k['leave_balance'] !== null ? t2g_apex_format_leave_days($k['leave_balance']) : '—';
    $break = $k['break_minutes'] !== null ? (int) $k['break_minutes'] : '—';

    ob_start();
    ?>
<!-- 6 Micro-Trend KPI Summary Cards -->
<style>
    #dashinfo-cards{
        display: contents;
        width: 100%;
    }
</style>
<div class="gap-4" id="dashinfo-cards">
<a href="<?php echo t2g_apex_h($u['projects']); ?>" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow block no-underline text-inherit">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Projects</div>
<div class="text-2xl font-black text-slate-900 mt-0.5"><?php echo (int) $k['projects_total']; ?></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
<span class="text-emerald-600 font-semibold">+<?php echo (int) $k['projects_in_progress']; ?> in progress</span>
<?php if ($k['projects_at_risk'] > 0) { ?><span class="text-amber-600 font-semibold bg-amber-50 px-1.5 py-0.5 rounded"><?php echo (int) $k['projects_at_risk']; ?> at risk</span><?php } ?>
</div>
</a>
<a href="<?php echo t2g_apex_h($u['tasks']); ?>" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow block no-underline text-inherit">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tasks</div>
<div class="text-2xl font-black text-slate-900 mt-0.5"><?php echo (int) $k['tasks_open']; ?></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
<span class="text-slate-500"><?php echo (int) $k['tasks_total']; ?> total</span>
<?php if ($k['tasks_due_today'] > 0) { ?><span class="text-purple-600 font-semibold bg-purple-50 px-1.5 py-0.5 rounded"><?php echo (int) $k['tasks_due_today']; ?> due today</span><?php } ?>
</div>
</a>
<a href="<?php echo t2g_apex_h($u['attendance']); ?>" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow block no-underline text-inherit">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Attendance</div>
<div class="text-2xl font-black text-slate-900 mt-0.5" id="apexKpiAttendancePct"><?php echo t2g_apex_h($att); ?></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 text-[11px] text-slate-500"><span id="apexKpiAttendanceMeta">This month</span></div>
</a>
<a href="<?php echo t2g_apex_h($u['leave']); ?>" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow block no-underline text-inherit">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Leave Balance</div>
<div class="text-2xl font-black text-slate-900 mt-0.5"><?php echo t2g_apex_h($leave); ?> <span class="text-sm font-semibold text-slate-400">Days</span></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
<span class="text-slate-500 font-medium"><?php echo (int) $k['leave_pending']; ?> pending</span>
</div>
</a>
<a href="<?php echo t2g_apex_h($u['tickets']); ?>" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow block no-underline text-inherit">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tickets</div>
<div class="text-2xl font-black text-slate-900 mt-0.5"><?php echo (int) $k['tickets_open']; ?></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 text-[11px] text-slate-500"><?php echo (int) $k['tickets_total']; ?> total</div>
</a>
<div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
<div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Break</div>
<div class="text-2xl font-black text-slate-900 mt-0.5"><?php echo t2g_apex_h($break); ?> <span class="text-sm font-semibold text-slate-400">min</span></div>
<div class="mt-2.5 pt-2 border-t border-slate-100 text-[11px] text-emerald-600 font-semibold">Today</div>
</div>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_tasks_tbody($d)
{
    $CI = &get_instance();
    $rows = $d['tasks'];
    if ($rows === []) {
        return '<tr><td colspan="7" class="py-6 text-center text-slate-500 text-xs">No open tasks assigned to you.</td></tr>';
    }
    $html = '';
    $i    = 1;
    foreach ($rows as $task) {
        [$plabel, $pcolor] = t2g_apex_task_priority_badge($task['priority'] ?? 1);
        $progress = t2g_apex_task_progress_from_status($task['status'] ?? 0);
        $due = !empty($task['duedate']) ? _d($task['duedate']) : '—';
        $assignee = get_staff_full_name($d['staff_id']);
        $link     = admin_url('tasks/view/' . $task['id']);
        $html .= '<tr class="hover:bg-slate-50/70 transition-colors">';
        $html .= '<td class="py-3.5 pl-2 font-mono text-slate-400">' . $i . '</td>';
        $html .= '<td class="py-3.5 pr-4 font-semibold text-slate-900 max-w-xs truncate"><a href="' . t2g_apex_h($link) . '" class="text-slate-900 hover:text-blue-600">' . t2g_apex_h($task['name']) . '</a></td>';
        $html .= '<td class="py-3.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-' . $pcolor . '-50 text-' . $pcolor . '-700 border border-' . $pcolor . '-200/60">' . t2g_apex_h($plabel) . '</span></td>';
        $html .= '<td class="py-3.5 px-3 text-slate-500 whitespace-nowrap">' . t2g_apex_h($due) . '</td>';
        // $html .= '<td class="py-3.5 px-3"><div class="flex items-center gap-2"><div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden"><div class="bg-blue-600 h-1.5 rounded-full" style="width:' . min(100, max(0, $progress)) . '%"></div></div><span class="font-mono text-[11px] font-bold text-slate-600">' . $progress . '%</span></div></td>';
        $html .= '<td class="py-3.5 px-3"><span class="text-xs text-slate-600 font-medium">' . t2g_apex_h($assignee) . '</span></td>';
        // $html .= '<td class="py-3.5 text-right pr-2"><a href="' . t2g_apex_h($link) . '" class="text-blue-600 text-xs font-semibold">Open</a></td>';
        $html .= '</tr>';
        $i++;
    }

    return $html;
}

function t2g_apex_render_projects_tbody($d)
{
    $rows = $d['projects'];
    if ($rows === []) {
        return '<tr><td colspan="5" class="py-6 text-center text-slate-500 text-xs">No active projects assigned to you.</td></tr>';
    }
    $html = '';
    $i    = 1;
    foreach ($rows as $p) {
        $status     = get_project_status_by_id($p['status']);
        $statusName = $status['name'] ?? '—';
        $color      = $p['at_risk'] ? 'amber' : 'emerald';
        $due  = !empty($p['deadline']) ? _d($p['deadline']) : '—';
        $link = admin_url('projects/view/' . $p['id']);
        $html .= '<tr class="hover:bg-slate-50/70 transition-colors">';
        $html .= '<td class="py-3.5 pl-2 font-mono text-slate-400">' . $i . '</td>';
        $html .= '<td class="py-3.5 pr-4 font-semibold text-slate-900 max-w-xs truncate"><a href="' . t2g_apex_h($link) . '" class="text-slate-900 hover:text-blue-600">' . t2g_apex_h($p['name']) . '</a></td>';
        $html .= '<td class="py-3.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-' . $color . '-50 text-' . $color . '-700 border border-' . $color . '-200/60">' . ($p['at_risk'] ? 'At Risk' : t2g_apex_h($statusName)) . '</span></td>';
        $html .= '<td class="py-3.5 px-3"><div class="flex items-center gap-2 min-w-[5rem]"><div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden"><div class="bg-blue-600 h-1.5 rounded-full" style="width:' . min(100, max(0, (int) $p['progress'])) . '%"></div></div><span class="font-mono text-[11px] font-bold text-slate-600">' . (int) $p['progress'] . '%</span></div></td>';
        $html .= '<td class="py-3.5 px-3 text-slate-500 whitespace-nowrap">' . t2g_apex_h($due) . '</td>';
        $html .= '</tr>';
        $i++;
    }

    return $html;
}

function t2g_apex_render_announcements_tbody($d)
{
    $rows = $d['announcements_my_work'] ?? $d['announcements'];
    if (!is_array($rows) || $rows === []) {
        return '<tr><td colspan="4" class="py-6 text-center text-slate-500 text-xs">No announcements right now.</td></tr>';
    }
    $html = '';
    $i    = 1;
    foreach (array_slice($rows, 0, 5) as $ann) {
        $id    = (int) ($ann['announcementid'] ?? 0);
        $title = $ann['name'] ?? '—';
        $date  = !empty($ann['dateadded']) ? _dt($ann['dateadded']) : '—';
        $excerpt = strip_tags($ann['message'] ?? '');
        if (strlen($excerpt) > 80) {
            $excerpt = mb_substr($excerpt, 0, 77) . '…';
        }
        $link = admin_url('announcements/view/' . $id);
        $html .= '<tr class="hover:bg-slate-50/70 transition-colors">';
        $html .= '<td class="py-3.5 pl-2 font-mono text-slate-400">' . $i . '</td>';
        $html .= '<td class="py-3.5 pr-4 font-semibold text-slate-900 max-w-sm"><a href="' . t2g_apex_h($link) . '" class="text-slate-900 hover:text-blue-600">' . t2g_apex_h($title) . '</a></td>';
        $html .= '<td class="py-3.5 px-3 text-slate-500 text-xs max-w-md truncate">' . t2g_apex_h($excerpt ?: '—') . '</td>';
        $html .= '<td class="py-3.5 px-3 text-slate-500 whitespace-nowrap text-xs">' . t2g_apex_h($date) . '</td>';
        $html .= '</tr>';
        $i++;
    }

    return $html;
}

function t2g_apex_render_attendance_card($d)
{
    $punch = $d['punch'];
    $u     = $d['urls'];
    $checked_in = !empty($punch['workroom_open_checkin']) || !empty($punch['biometric_navbar_active']);
    $check_label = 'Not checked in';
    $check_time  = '—';
    if (!empty($punch['html_list'])) {
        if (preg_match('/Check in\s*:\s*([0-9:]+)/i', $punch['html_list'], $m)) {
            $check_time = $m[1];
            $check_label = 'Checked In';
        }
    } elseif (!empty($punch['biometric_checkin_label'])) {
        $check_time  = $punch['biometric_checkin_label'];
        $check_label = 'Checked In';
    }
    $out_status = !empty($punch['biometric_is_checked_out']) || (!empty($punch['type_check_in_out']) && (int) $punch['type_check_in_out'] === 2 && (float) ($punch['time_from_checkin'] ?? 999) < 13) ? 'Done' : 'Pending';
    $break_txt  = $d['kpi']['break_minutes'] !== null ? (int) $d['kpi']['break_minutes'] . ' min taken' : '—';

    ob_start();
    ?>
<!-- <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
<div>
<div class="flex items-center justify-between pb-4 border-b border-slate-100">
<div class="flex items-center gap-3">
<div class="p-2 rounded-xl bg-indigo-50 text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
<div><h2 class="text-base font-bold text-slate-900">Today's Attendance</h2><p class="text-xs text-slate-400">Shift logging &amp; break tracker</p></div>
</div>
<a class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700" href="<?php echo t2g_apex_h($u['attendance-details']); ?>">View Details</a>
</div>
<div class="grid grid-cols-1 gap-4 mt-6">
<div class="flex items-center gap-4 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100">
<div class="w-14 h-14 rounded-full <?php echo $checked_in ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-500'; ?> flex items-center justify-center flex-shrink-0 ring-4 ring-emerald-50"><svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4.5 12.75l6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
<div><div class="text-[11px] font-bold uppercase tracking-wider text-slate-400"><?php echo t2g_apex_h($check_label); ?></div><div class="text-xl font-black text-slate-900 leading-tight"><?php echo t2g_apex_h($check_time); ?></div><div class="text-[11px] text-slate-500 font-medium"><?php echo t2g_apex_h($d['date_long']); ?></div></div>
</div>
<div class="space-y-2 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100">
<div class="flex items-center justify-between text-xs"><span class="text-slate-500 font-medium">Check Out:</span><span class="font-bold <?php echo $out_status === 'Pending' ? 'text-amber-600 bg-amber-100/70' : 'text-emerald-700 bg-emerald-50'; ?> px-2 py-0.5 rounded text-[11px]"><?php echo t2g_apex_h($out_status); ?></span></div>
<div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60"><span class="text-slate-500">☕ Break Total:</span><span class="font-black text-cyan-700"><?php echo t2g_apex_h($break_txt); ?></span></div>
</div>
</div>
</div>
<div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
<?php if ($checked_in && $out_status === 'Pending') { ?>
<button type="button" id="apex-punch-out" class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-md shadow-rose-600/20 transition-all flex items-center justify-center gap-2">Punch Out</button>
<?php } else { ?>
<button type="button" id="apex-punch-in" class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md transition-all flex items-center justify-center gap-2">Punch In</button>
<?php } ?>
<a href="<?php echo t2g_apex_h($u['attendance']); ?>" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 whitespace-nowrap">Full attendance</a>
</div>
</div> -->
    <?php
    return ob_get_clean();
}

function t2g_apex_render_primary_section($d)
{
    $u = $d['urls'];
    $open_count      = count($d['tasks']);
    $projects_shown  = count($d['projects']);
    $projects_total  = (int) $d['kpi']['projects_total'];
    $ann_list        = $d['announcements_my_work'] ?? $d['announcements'];
    $ann_total       = is_array($ann_list) ? count($ann_list) : 0;
    $ann_shown       = is_array($ann_list) ? min(5, count($ann_list)) : 0;
    $tasks_tbody     = t2g_apex_render_tasks_tbody($d);
    $projects_tbody  = t2g_apex_render_projects_tbody($d);
    $ann_tbody       = t2g_apex_render_announcements_tbody($d);
    $right_column    = t2g_apex_render_workhub_right_column($d);
    $tab_active      = 'px-3.5 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-xs';
    $tab_idle        = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors';

    ob_start();
    ?>
<section class="grid grid-cols-1 lg:grid-cols-12 gap-7" data-purpose="primary-work-and-attendance">
<div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between" id="apex-my-work">
<div>
<div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
<div class="flex items-center gap-3"><div class="p-2 rounded-xl bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" stroke-linecap="round" stroke-linejoin="round"></path></svg></div><div><h2 class="text-base font-bold text-slate-900">My Work</h2><p class="text-xs text-slate-400">Active tasks and assigned deliverable tracker</p></div></div>
<a id="apex-mywork-view-all" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700" href="<?php echo t2g_apex_h($u['view-all-tasks']); ?>">View All</a>
</div>
<div class="flex items-center gap-2 mt-4 mb-4 flex-wrap" role="tablist" aria-label="My Work views">
<button type="button" role="tab" aria-selected="true" data-apex-mywork-tab="tasks" class="<?php echo $tab_active; ?>">My Tasks (<?php echo (int) $d['kpi']['tasks_open']; ?>)</button>
<button type="button" role="tab" aria-selected="false" data-apex-mywork-tab="projects" class="<?php echo $tab_idle; ?>">My Projects (<?php echo (int) $projects_total; ?>)</button>
<button type="button" role="tab" aria-selected="false" data-apex-mywork-tab="announcements" class="<?php echo $tab_idle; ?>">Announcements (<?php echo (int) $ann_total; ?>)</button>
</div>
<div class="table-responsive" data-apex-mywork-panel="tasks" id="apex-mywork-panel-tasks">
<table class="table table-striped table-bordered table-hover apex-sortable-table">
<thead><tr>
<?php
echo t2g_apex_sortable_th('#', 0);
echo t2g_apex_sortable_th('Task', 1);
echo t2g_apex_sortable_th('Priority', 2);
echo t2g_apex_sortable_th('Due date', 3);
echo t2g_apex_sortable_th('Assignee', 4);
?>
</tr></thead>
<tbody><?php echo $tasks_tbody; ?></tbody>
</table>
</div>
<div class="table-responsive hidden" data-apex-mywork-panel="projects" id="apex-mywork-panel-projects" hidden>
<table class="table table-striped table-bordered table-hover apex-sortable-table">
<thead><tr>
<?php
echo t2g_apex_sortable_th('#', 0);
echo t2g_apex_sortable_th('Project', 1);
echo t2g_apex_sortable_th('Status', 2);
echo t2g_apex_sortable_th('Progress', 3);
echo t2g_apex_sortable_th('Deadline', 4);
?>
</tr></thead>
<tbody><?php echo $projects_tbody; ?></tbody>
</table>
</div>
<div class="table-responsive hidden" data-apex-mywork-panel="announcements" id="apex-mywork-panel-announcements" hidden>
<table class="table table-striped table-bordered table-hover apex-sortable-table">
<thead><tr>
<?php
echo t2g_apex_sortable_th('#', 0);
echo t2g_apex_sortable_th('Title', 1);
echo t2g_apex_sortable_th('Summary', 2);
echo t2g_apex_sortable_th('Posted', 3);
?>
</tr></thead>
<tbody><?php echo $ann_tbody; ?></tbody>
</table>
</div>
</div>
<div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 mt-2">
<span id="apex-mywork-footer" data-tasks-shown="<?php echo (int) min(5, $open_count); ?>" data-tasks-total="<?php echo (int) $d['kpi']['tasks_open']; ?>" data-projects-shown="<?php echo (int) $projects_shown; ?>" data-projects-total="<?php echo (int) $projects_total; ?>" data-announcements-shown="<?php echo (int) $ann_shown; ?>" data-announcements-total="<?php echo (int) $ann_total; ?>">Showing <?php echo (int) min(5, $open_count); ?> of <?php echo (int) $d['kpi']['tasks_open']; ?> active tasks</span>
</div>
</div>
<?php echo $right_column; ?>
</section>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_timesheet_cards_stack($d)
{
    $ts   = $d['timesheet_stats'] ?? [];
    $href = t2g_apex_h($d['urls']['timesheets'] ?? admin_url('staff/timesheets'));
    $cards = [
        ['key' => 'total', 'label' => _l('staff_stats_total_logged_time'), 'tone' => 'emerald'],
        ['key' => 'last_month', 'label' => _l('staff_stats_last_month_total_logged_time'), 'tone' => 'sky'],
        ['key' => 'this_month', 'label' => _l('staff_stats_this_month_total_logged_time'), 'tone' => 'emerald'],
        ['key' => 'last_week', 'label' => _l('staff_stats_last_week_total_logged_time'), 'tone' => 'sky'],
        ['key' => 'this_week', 'label' => _l('staff_stats_this_week_total_logged_time'), 'tone' => 'emerald'],
    ];

    ob_start();
    ?>
<!-- <div data-purpose="timesheet-logged-time-stack">
<div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
<div class="flex items-center gap-2">
<div class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600"><i class="fa fa-clock-o"></i></div>
<h2 class="text-sm font-bold text-slate-900"><?php echo t2g_apex_h(_l('task_timesheets')); ?></h2>
</div>
<a class="text-[11px] font-semibold text-blue-600 hover:text-blue-700" href="<?php echo $href; ?>"><?php echo _l('view'); ?> →</a>
</div>
<div class="flex flex-col gap-2">
<?php foreach ($cards as $c) {
    $val = $ts[$c['key']] ?? seconds_to_time_format(0);
    $labelTone = $c['tone'] === 'emerald' ? 'text-emerald-600' : 'text-sky-600'; ?>
<a href="<?php echo $href; ?>" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-3.5 py-2.5 hover:bg-slate-100/90 transition-colors no-underline">
<span class="text-[11px] font-semibold <?php echo $labelTone; ?> leading-snug"><?php echo t2g_apex_h($c['label']); ?></span>
<span class="text-base font-bold text-blue-600 tabular-nums shrink-0"><?php echo t2g_apex_h($val); ?></span>
</a>
<?php } ?>
</div>
</div> -->
    <?php
    return ob_get_clean();
}

function t2g_apex_render_bulletin_board($d)
{
    $board = $d['bulletin'] ?? [];
    $slides = $board['slides'] ?? [];
    if (!is_array($slides) || $slides === []) {
        return '';
    }
    $interval = (int) ($board['interval_ms'] ?? 8000);
    $u = $d['urls'];

    ob_start();
    ?>
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col min-h-[320px]" id="apex-bulletin-board" data-interval="<?php echo max(2000, min(8000, $interval)); ?>">
<div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
<div class="flex items-center gap-2.5">
<div class="p-2 rounded-xl bg-indigo-50 text-indigo-600"><i class="fa fa-bullhorn"></i></div>
<div>
<h2 class="text-sm font-bold text-slate-900">Bulletin board</h2>
<p class="text-[11px] text-slate-400">Live updates from your workday</p>
</div>
</div>
<div class="flex items-center gap-1.5" id="apex-bulletin-dots" aria-hidden="true">
<?php foreach ($slides as $i => $_s) { ?>
<span class="w-1.5 h-1.5 rounded-full bg-slate-200 <?php echo $i === 0 ? 'is-active bg-indigo-500' : ''; ?>" data-apex-bulletin-dot="<?php echo (int) $i; ?>"></span>
<?php } ?>
</div>
</div>
<style>
    .bg-slate-950 {
    background-color: #1e293b;    
    /* background-image: linear-gradient(to right, blue, skyblue); */
    }
</style>
<div class="relative flex-1 min-h-[220px] overflow-hidden rounded-xl bg-slate-950 text-white">
<div class="apex-bulletin-blackout absolute inset-0 bg-black pointer-events-none" aria-hidden="true"></div>
<?php foreach ($slides as $i => $slide) {
    $tone = (string) ($slide['tone'] ?? 'neutral');
    $metricCls = 'text-4xl font-black tracking-tight';
    if ($tone === 'ok') {
        $metricCls .= ' text-emerald-300';
    } elseif ($tone === 'warn') {
        $metricCls .= ' text-amber-300';
    } else {
        $metricCls .= ' text-white';
    }
    ?>
<div class="apex-bulletin-slide absolute inset-0 p-6 flex flex-col justify-center <?php echo $i === 0 ? 'is-active' : ''; ?>" data-apex-bulletin-slide="<?php echo (int) $i; ?>">
<p class="text-[11px] uppercase tracking-widest text-slate-400 font-bold mb-2"><?php echo t2g_apex_h($slide['title'] ?? ''); ?></p>
<p class="<?php echo $metricCls; ?>"><?php echo t2g_apex_h($slide['metric'] ?? '—'); ?></p>
<?php if (!empty($slide['metric_sub'])) { ?>
<p class="text-sm text-slate-300 mt-1"><?php echo t2g_apex_h($slide['metric_sub']); ?></p>
<?php } ?>
<div class="mt-4 space-y-1.5 text-xs text-slate-300">
<?php foreach ($slide['lines'] ?? [] as $line) { ?>
<p class="m-0"><?php echo t2g_apex_h($line); ?></p>
<?php } ?>
</div>
</div>
<?php } ?>
</div>
<div class="pt-3 flex items-center justify-between text-[11px] text-slate-500">
<!-- <span>Rotates every few seconds</span> -->
<!-- <a class="text-blue-600 font-semibold hover:underline" href="<?php echo t2g_apex_h($u['snapshot']); ?>">Snapshot</a> -->
</div>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_workhub_right_column($d)
{
    $bulletin = t2g_apex_render_bulletin_board($d);

    ob_start();
    ?>
<div class="lg:col-span-5 flex flex-col gap-4 min-h-0">
<?php echo $bulletin; ?>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_hrms_calendar_full($d)
{
    $u = $d['urls'];
    ob_start();
    ?>
<div class="dashboard-hrms-panel apex-hrms-cal-dashboard">
<div class="dashboard-hrms-panel-head">
<span><i class="fa fa-calendar"></i> Attendance calendar</span>
<a href="<?php echo admin_url('utilities/calendar'); ?>"><?php echo _l('calendar'); ?> (events)</a>
</div>
<div class="dashboard-hrms-cal-wrap dashboard-hrms-cal">
<div class="hrms-cal-nav">
<button type="button" id="dashboardHrmsCalPrev" aria-label="Previous month"><i class="fa fa-angle-left"></i></button>
<div class="hrms-cal-month" id="dashboardHrmsCalMonthLabel">—</div>
<button type="button" id="dashboardHrmsCalNext" aria-label="Next month"><i class="fa fa-angle-right"></i></button>
</div>
<div class="hrms-cal-weekdays">
<div class="hrms-cal-dow">Su</div>
<div class="hrms-cal-dow">Mo</div>
<div class="hrms-cal-dow">Tu</div>
<div class="hrms-cal-dow">We</div>
<div class="hrms-cal-dow">Th</div>
<div class="hrms-cal-dow">Fr</div>
<div class="hrms-cal-dow">Sa</div>
</div>
<div class="hrms-cal-grid" id="dashboardHrmsCalGrid">
<div style="grid-column:1/-1;padding:14px;text-align:center;color:#64748b;font-size:12px;">
<i class="fa fa-spinner fa-spin"></i> Loading…
</div>
</div>
<div class="hrms-cal-legend">
<span><i class="lg-ok"></i> Present</span>
<span><i class="lg-half"></i> Half</span>
<span><i class="lg-bad"></i> Absent</span>
<span><i class="lg-leave"></i> Leave</span>
<span><i class="lg-holiday"></i> Holiday</span>
<span><i class="lg-off"></i> Off</span>
</div>
<div class="dashboard-hrms-cal-footer">
<a href="<?php echo t2g_apex_h($u['attendance-details']); ?>">Open full attendance →</a>
</div>
</div>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_hrms_calendar_panel($d)
{
    ob_start();
    ?>
<div class="dashboard-hrms-cal-wrap dashboard-hrms-cal apex-hrms-cal-compact w-full max-w-none apex-hrms-cal-panel-no-ids">
<p class="text-[11px] text-slate-500 text-center py-2">See Attendance calendar in the row above.</p>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_hrms_records_row($d)
{
    $u = $d['urls'];
    $l = $d['leave'];
    $portfolio_projects = array_slice($d['projects'], 0, 3);
    $score = $d['pedma']['score'];
    $grade = $d['pedma']['grade'] ?: '—';
    $cal = t2g_apex_render_hrms_calendar_panel($d);

    ob_start();
    ?>
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-4 items-stretch" data-purpose="hrms-records-row">
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col min-h-0">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2"><h3 class="text-xs font-bold text-slate-900">Projects</h3><a class="text-[10px] font-semibold text-blue-600" href="<?php echo t2g_apex_h($u['view-projects']); ?>">All</a></div>
<div class="space-y-2 flex-1 text-[11px]">
<?php if ($portfolio_projects === []) { ?><p class="text-slate-500">No active projects.</p><?php } ?>
<?php foreach ($portfolio_projects as $p) { ?>
<div><a href="<?php echo admin_url('projects/view/' . $p['id']); ?>" class="font-semibold text-slate-800 hover:text-blue-600 truncate block"><?php echo t2g_apex_h($p['name']); ?></a><div class="flex items-center gap-2 mt-1"><div class="flex-1 bg-slate-100 rounded-full h-1.5"><div class="bg-blue-600 h-1.5 rounded-full" style="width:<?php echo (int) $p['progress']; ?>%"></div></div><span class="font-mono text-[10px] text-slate-600"><?php echo (int) $p['progress']; ?>%</span></div></div>
<?php } ?>
</div>
</div>
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2"><h3 class="text-xs font-bold text-slate-900">Leave</h3><a class="text-[10px] font-semibold text-blue-600" href="<?php echo t2g_apex_h($u['view-leave']); ?>">All</a></div>
<div class="grid grid-cols-2 gap-2 text-[11px] flex-1">
<div class="bg-slate-50 rounded-lg p-2 border border-slate-100"><div class="text-[10px] text-slate-400 uppercase font-bold">Quota</div><div class="text-lg font-black text-slate-900"><?php echo t2g_apex_h($l['granted']); ?></div></div>
<div class="bg-blue-50/60 rounded-lg p-2 border border-blue-100"><div class="text-[10px] text-blue-600 uppercase font-bold">Left</div><div class="text-lg font-black text-blue-700"><?php echo t2g_apex_h($l['balance']); ?></div></div>
</div>
<p class="text-[10px] text-slate-500 mt-2"><?php echo (int) $l['pending']; ?> pending</p>
</div>
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col min-h-0">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2"><h3 class="text-xs font-bold text-slate-900">Notices</h3><a class="text-[10px] font-semibold text-blue-600" href="<?php echo t2g_apex_h($u['view-announcements']); ?>">All</a></div>
<div class="space-y-2 flex-1 overflow-hidden text-[11px]">
<?php if ($d['announcements'] === []) { ?><p class="text-slate-500">No announcements.</p><?php } ?>
<?php foreach ($d['announcements'] as $ann) {
    $title = $ann['name'] ?? '';
    $link  = admin_url('announcements/view/' . (int) ($ann['announcementid'] ?? 0)); ?>
<a href="<?php echo t2g_apex_h($link); ?>" class="block font-semibold text-slate-800 hover:text-blue-600 truncate"><?php echo t2g_apex_h($title); ?></a>
<?php } ?>
</div>
</div>
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2"><h3 class="text-xs font-bold text-slate-900">PEDMA</h3><a class="text-[10px] font-semibold text-blue-600" href="<?php echo t2g_apex_h($u['pedma']); ?>">Open</a></div>
<div class="flex items-center gap-3 flex-1">
<div class="text-3xl font-black text-slate-900"><?php echo $score !== null ? (int) $score : '—'; ?></div>
<div class="text-[11px] text-slate-600">Tier <span class="font-bold text-emerald-700"><?php echo t2g_apex_h($grade); ?></span></div>
</div>
</div>
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2"><h3 class="text-xs font-bold text-slate-900">Break</h3><a class="text-[10px] font-semibold text-blue-600" href="<?php echo t2g_apex_h($u['break-history']); ?>">Details</a></div>
<div class="text-2xl font-black text-slate-900"><?php echo $d['kpi']['break_minutes'] !== null ? (int) $d['kpi']['break_minutes'] . ' min' : '—'; ?></div>
<p class="text-[10px] text-slate-500 mt-1">Today</p>
</div>
<div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-col min-w-0"><?php echo $cal; ?></div>
</section>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_spotlight_panel()
{
    $CI = &get_instance();
    ob_start();
    ?>
<div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col min-h-0 apex-spotlight-card">
<div id="celebrantWidget" class="dashboard-birthday-col flex-1 min-h-0 min-w-0">
<?php $CI->load->view('admin/dashboard/widgets/birthday_spotlight'); ?>
</div>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_render_middle_overview_inner($d)
{
    ob_start();
    ?>
<div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col min-h-0">
<?php echo t2g_apex_render_hrms_calendar_full($d); ?>
</div>
<?php echo t2g_apex_render_spotlight_panel(); ?>
    <?php
    return ob_get_clean();
}

function t2g_apex_pedma_feedback_html($text)
{
    $t = trim(strip_tags((string) $text));
    if ($t === '') {
        return '<span class="text-slate-400">—</span>';
    }

    return nl2br(t2g_apex_h($t));
}

function t2g_apex_render_performance_section($d)
{
    $u     = $d['urls'];
    $prev  = $d['pedma']['previous_month'] ?? [];
    $score = $prev['overall_score'] ?? $d['pedma']['score'];
    $grade = $d['pedma']['grade'] ?: '—';
    $month_label = $prev['label'] ?? 'Previous month';
    $stroke      = $score !== null ? max(0, min(88, round((float) $score * 0.88))) : 0;
    $score_display = $score !== null ? rtrim(rtrim(number_format((float) $score, 2, '.', ''), '0'), '.') : '—';
    $has_kra     = !empty($prev['kra_labels']);

    ob_start();
    ?>
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-7" data-purpose="performance-and-insights">
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col lg:col-span-5 min-h-0">
<div class="flex items-center justify-between pb-3 border-b border-slate-100">
<div class="flex items-center gap-2.5">
<div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</div>
<div>
<h3 class="text-sm font-bold text-slate-900">PEDMA Performance</h3>
<p class="text-[11px] text-slate-400"><?php echo t2g_apex_h($month_label); ?> · overall score</p>
</div>
</div>
<a class="text-xs font-semibold text-blue-600 hover:text-blue-700 shrink-0" href="<?php echo t2g_apex_h($u['pedma']); ?>">View Details</a>
</div>
<div class="flex items-center gap-4 mt-4">
<div class="relative w-24 h-24 flex items-center justify-center flex-shrink-0">
<svg class="w-24 h-24 donut-ring" viewBox="0 0 36 36"><circle cx="18" cy="18" fill="transparent" r="14" stroke="#f1f5f9" stroke-width="3.5"></circle><circle cx="18" cy="18" fill="transparent" r="14" stroke="#10b981" stroke-dasharray="<?php echo (int) $stroke; ?> 88" stroke-dashoffset="0" stroke-linecap="round" stroke-width="3.5"></circle></svg>
<div class="absolute inset-0 flex flex-col items-center justify-center"><span class="text-xl font-black text-slate-900 leading-none"><?php echo t2g_apex_h($score_display); ?></span><span class="text-[9px] font-bold text-slate-400">/ 100</span></div>
</div>
<div class="flex-1 text-xs text-slate-600">
<?php if ($score !== null) { ?>
Previous month overall score after add-on and fatal adjustments. Tier <span class="font-bold text-emerald-700"><?php echo t2g_apex_h($grade); ?></span>.
<?php } else { ?>
No published PEDMA evaluation for <?php echo t2g_apex_h($month_label); ?> yet. Open PEDMA for full history.
<?php } ?>
</div>
</div>
<div class="mt-4 pt-3 border-t border-slate-100 space-y-3 max-h-64 overflow-y-auto text-[11px]">
<div>
<div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Manager overall feedback</div>
<div class="text-slate-700 mt-1 leading-relaxed"><?php echo t2g_apex_pedma_feedback_html($prev['manager_feedback'] ?? ''); ?></div>
</div>
<div>
<div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">HOD overall feedback</div>
<div class="text-slate-700 mt-1 leading-relaxed"><?php echo t2g_apex_pedma_feedback_html($prev['hod_feedback'] ?? ''); ?></div>
</div>
<div>
<div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">HR overall feedback</div>
<div class="text-slate-700 mt-1 leading-relaxed"><?php echo t2g_apex_pedma_feedback_html($prev['hr_feedback'] ?? ''); ?></div>
</div>
</div>
<!-- <div class="pt-3 mt-auto border-t border-slate-100 flex items-center justify-between text-xs">
<span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded text-[11px]">Tier: <?php echo t2g_apex_h($grade); ?></span>
<a class="text-blue-600 font-semibold hover:underline" href="<?php echo t2g_apex_h($u['appraisal']); ?>">Self Review</a>
</div> -->
</div>
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col lg:col-span-7 min-h-[280px]">
<div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
<h3 class="text-sm font-bold text-slate-900">KRA Performance</h3>
<span class="text-[11px] text-slate-400"><?php echo t2g_apex_h($month_label); ?></span>
</div>
<div class="relative flex-1 min-h-[220px]">
<canvas id="apexKraChart" class="w-full" height="240"></canvas>
<p id="apexKraChartEmpty" class="<?php echo $has_kra ? 'hidden' : ''; ?> absolute inset-0 flex items-center justify-center text-xs text-slate-500 text-center px-4">No KRA breakdown for <?php echo t2g_apex_h($month_label); ?>.</p>
</div>
</div>
</section>
    <?php
    return ob_get_clean();
}

function t2g_apex_day_cell_class($code, $dateYmd)
{
    $today = date('Y-m-d');
    if ($dateYmd === $today) {
        return 'today';
    }
    if (in_array($code, ['WO', 'H'], true)) {
        return 'off';
    }
    if ($code === 'P' || $code === 'HD') {
        return 'present';
    }
    if ($code === 'AB' || $code === 'NC') {
        return 'absent';
    }
    if (strpos($code, 'L') === 0 || $code === 'EL' || $code === 'SL') {
        return 'leave';
    }

    return 'neutral';
}

function t2g_apex_render_calendar_section($d)
{
    $u = $d['urls'];
    $days = $d['calendar_days'];
    $logged = 0;
    $workdays = 0;
    $cells = '';
    foreach ($days as $day) {
        if (empty($day['date'])) {
            continue;
        }
        $ymd   = $day['date'];
        $ts    = strtotime($ymd);
        $dow   = date('D', $ts);
        $num   = date('j', $ts);
        $code  = $day['code'] ?? '';
        $tone  = t2g_apex_day_cell_class($code, $ymd);
        if (!in_array($code, ['WO', 'H'], true)) {
            $workdays++;
            if ($code === 'P' || $code === 'HD') {
                $logged++;
            }
        }
        $is_today = ($ymd === date('Y-m-d'));
        $cls = 'flex flex-col items-center justify-between p-3 rounded-xl border transition-all text-center ';
        if ($is_today) {
            $cls .= 'bg-blue-50/70 border-2 border-blue-500 shadow-sm relative';
        } elseif ($tone === 'off') {
            $cls .= 'bg-slate-100/60 border-slate-200/50';
        } elseif ($tone === 'absent') {
            $cls .= 'bg-rose-50/40 border-rose-100';
        } elseif ($tone === 'leave') {
            $cls .= 'bg-amber-50/50 border-amber-100';
        } else {
            $cls .= 'bg-slate-50 hover:bg-slate-100/80 border-slate-100';
        }
        $icon = '✓';
        $icon_cls = 'w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold';
        if ($tone === 'off') {
            $icon = '';
            $icon_cls = 'w-2.5 h-2.5 rounded-full bg-slate-300';
        } elseif ($tone === 'absent') {
            $icon = '✕';
            $icon_cls = 'w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-[10px] font-bold';
        } elseif ($tone === 'leave') {
            $icon = '●';
            $icon_cls = 'w-5 h-5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-[10px] font-bold';
        } elseif ($is_today) {
            $icon_cls = 'w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shadow';
        }
        $cells .= '<a href="' . t2g_apex_h($u['attendance'] . '?month=' . substr($ymd, 0, 7)) . '" class="' . $cls . ' no-underline text-inherit">';
        $cells .= '<span class="text-[10px] font-semibold text-slate-400 uppercase">' . t2g_apex_h($dow) . '</span>';
        $cells .= '<span class="text-sm font-bold text-slate-800 my-1">' . (int) $num . '</span>';
        $cells .= '<div class="' . $icon_cls . '">' . $icon . '</div>';
        if ($is_today) {
            $cells .= '<span class="absolute -top-2.5 bg-blue-600 text-white text-[9px] font-extrabold px-1.5 py-0.2 rounded-full uppercase tracking-tighter">Today</span>';
        }
        $cells .= '</a>';
    }

    ob_start();
    ?>
<section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6" data-purpose="attendance-monthly-calendar">
<div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
<div class="flex items-center gap-3"><div class="p-2 rounded-xl bg-teal-50 text-teal-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" stroke-linecap="round" stroke-linejoin="round"></path></svg></div><div><h3 class="text-base font-bold text-slate-900">Attendance Calendar</h3><p class="text-xs text-slate-400"><?php echo t2g_apex_h($d['calendar_month_label']); ?></p></div></div>
<a class="text-xs font-semibold text-blue-600 hover:text-blue-700" href="<?php echo t2g_apex_h($u['attendance']); ?>">Open full calendar</a>
</div>
<div id="apex-calendar-grid" class="grid grid-cols-7 sm:grid-cols-14 gap-2.5 mt-5"><?php echo $cells ?: '<p class="col-span-full text-xs text-slate-500">No attendance data for this month.</p>'; ?></div>
<div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500"><span>Logged Workdays: <b class="text-slate-800"><?php echo (int) $logged; ?> / <?php echo max(1, (int) $workdays); ?> days</b></span></div>
</section>
    <?php
    return ob_get_clean();
}

function t2g_apex_apply_dynamic_sections($html, $d)
{
    $html = t2g_apex_replace_section_by_purpose($html, 'metric-summary-row', t2g_apex_render_kpi_section($d));
    $html = preg_replace('/<section class="grid grid-cols-1 lg:grid-cols-12 gap-7" data-purpose="primary-work-and-attendance">.*?<\/section>\s*<!-- END: PrimaryWorkspaceGrid -->/s', t2g_apex_render_primary_section($d) . "\n<!-- END: PrimaryWorkspaceGrid -->", $html, 1);
    $html = t2g_apex_replace_section_by_purpose($html, 'middle-overview-cards', t2g_apex_render_middle_overview_inner($d));
    $html = preg_replace(
        '/<section[^>]*data-purpose="performance-and-insights"[^>]*>.*?<\/section>/s',
        t2g_apex_render_performance_section($d),
        $html,
        1
    );
    $html = preg_replace(
        '/<!-- BEGIN: MonthlyAttendanceCalendarStrip -->[\s\S]*?<section[^>]*data-purpose="attendance-monthly-calendar"[^>]*>[\s\S]*?<\/section>/s',
        '',
        $html,
        1
    );
    $html = preg_replace(
        '/<section[^>]*data-purpose="attendance-monthly-calendar"[^>]*>[\s\S]*?<\/section>/s',
        '',
        $html,
        1
    );

    return $html;
}

function t2g_apex_fix_links($html)
{
    $routes = t2g_apex_workhub_routes();

    return preg_replace_callback('/\bhref="#([^"]*)"/', function ($m) use ($routes) {
        $key = $m[1];
        if (isset($routes[$key])) {
            return 'href="' . html_escape($routes[$key]) . '"';
        }
        if ($key === '') {
            return 'href="' . html_escape(admin_url()) . '"';
        }

        return 'href="' . html_escape(admin_url($key)) . '"';
    }, $html);
}

function t2g_apex_patch_header($html, $d)
{
    $html = str_replace('Good morning, Alex', html_escape($d['greeting'] . ', ' . $d['first_name']), $html);
    $html = str_replace('Monday, 03 November 2026', html_escape($d['date_long']), $html);

    $thought = trim((string) ($d['thought'] ?? ''));
    $thought_block = '';
    if ($thought !== '') {
        $thought_block = '<div class="apex-workhub-thought flex items-center gap-2.5 ml-auto pl-6 max-w-xl min-w-0">'
            . '<span class="apex-workhub-thought-icon flex-shrink-0" aria-hidden="true"><i class="fa fa-quote-left"></i></span>'
            . '<p class="text-xs sm:text-sm text-slate-500 italic leading-snug m-0">' . html_escape($thought) . '</p>'
            . '</div>';
    }

    $html = preg_replace(
        '/(<header[^>]*data-purpose="top-navigation-bar"[^>]*>[\s\S]*?<p class="text-xs text-slate-500 font-medium">[^<]*<\/p>\s*<\/div>\s*<\/div>)\s*[\s\S]*?(<\/header>)/',
        '$1' . $thought_block . '$2',
        $html,
        1
    );

    $html = preg_replace(
        '/(<header[^>]*data-purpose="top-navigation-bar"[^>]*\sclass=")[^"]*(")/',
        '$1h-auto min-h-[4.5rem] bg-white border-b border-slate-200/80 px-8 flex items-center justify-between gap-6 sticky top-0 z-20 shadow-sm backdrop-blur-md bg-white/95$2',
        $html,
        1
    );

    return $html;
}
