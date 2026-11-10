<?php

defined('BASEPATH') or exit('No direct script access allowed');

function t2g_apex_render_manager_tab_switch()
{
    return '<div class="apex-manager-view-tabs flex justify-center" role="tablist" aria-label="Dashboard view">'
        . '<div class="inline-flex p-1 rounded-xl bg-slate-200/80 border border-slate-200 shadow-inner">'
        . '<button type="button" role="tab" class="apex-manager-view-tab is-active" data-apex-manager-view="personal" aria-selected="true">Self</button>'
        . '<button type="button" role="tab" class="apex-manager-view-tab" data-apex-manager-view="team" aria-selected="false">Team</button>'
        . '</div></div>';
}

/**
 * Place Self / Team toggle inside the shared greeting header bar.
 */
function t2g_apex_inject_manager_tabs_into_header($header_html)
{
    $header_html = (string) $header_html;
    if ($header_html === '') {
        return $header_html;
    }

    $tabs = '<div class="apex-manager-tabs-slot apex-manager-tabs-in-header flex items-center justify-center flex-1 px-2 sm:px-6 min-w-0">'
        . t2g_apex_render_manager_tab_switch()
        . '</div>';

    if (preg_match('/<div class="apex-workhub-thought/', $header_html)) {
        return preg_replace('/(\s*<div class="apex-workhub-thought)/', $tabs . '$1', $header_html, 1);
    }

    if (preg_match('/<\/header>\s*$/', $header_html)) {
        return preg_replace('/\s*<\/header>\s*$/', $tabs . '</header>', $header_html, 1);
    }

    return $header_html . $tabs;
}

/**
 * Lift greeting header out of personal HTML so it can sit above both manager panes.
 *
 * @return array{header: string, html: string}
 */
function t2g_apex_split_workhub_top_header($html)
{
    if (!preg_match(
        '/<!-- BEGIN: TopHeader -->\s*(<header[^>]*data-purpose="top-navigation-bar"[\s\S]*?<\/header>)\s*<!-- END: TopHeader -->/',
        $html,
        $m
    )) {
        return ['header' => '', 'html' => $html];
    }

    $rest = preg_replace(
        '/\s*<!-- BEGIN: TopHeader -->[\s\S]*?<!-- END: TopHeader -->\s*/',
        "\n",
        $html,
        1
    );

    return ['header' => $m[1], 'html' => $rest !== null ? $rest : $html];
}

function t2g_apex_render_manager_team_dashboard($d)
{
    $k   = $d['kpis'] ?? [];
    $u   = $d['urls'] ?? [];
    $total = (int) ($d['team_size'] ?? 0);
    $present = (int) ($k['present_today'] ?? 0);
    $att_rate = (int) ($k['attendance_rate'] ?? 0);
    $on_leave = (int) ($k['on_leave'] ?? 0);
    $leave_pct = $total > 0 ? round(($on_leave / $total) * 100) : 0;
    $perf = $d['charts']['performance'] ?? [];
    $pedma = $d['charts']['pedma_grades'] ?? [];
    $pg    = $pedma['grades'] ?? ['A' => 0, 'B' => 0, 'C' => 0];
    $pedma_month = trim((string) ($pedma['month_label'] ?? 'Previous month'));
    $pedma_evaluated = (int) ($pedma['evaluated'] ?? 0);
    $dept_filters    = $d['department_filters'] ?? [];

    ob_start();
    ?>
<div id="apex-manager-team-root" class="apex-manager-team-root bg-slate-100 min-w-0 text-slate-800">
<div class="p-8 max-w-[1720px] mx-auto w-full space-y-6">
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<h1 class="text-2xl font-black text-slate-900 tracking-tight">Team Dashboard</h1>
<p class="text-sm text-slate-500 mt-1">Overview of your team&apos;s activities and performance.</p>
</div>
<div class="flex items-center gap-2">
<select class="apex-team-period text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-600" disabled aria-label="Period">
<option><?php echo html_escape($d['period_label'] ?? 'This Month'); ?></option>
</select>
<button type="button" class="apex-team-refresh text-xs font-semibold px-3 py-2 rounded-lg border border-slate-200 bg-white text-blue-600 hover:bg-blue-50" onclick="window.location.reload()">Refresh</button>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
<?php echo t2g_apex_manager_kpi_card('Total Employees', (string) $total, (string) $total . ' in your team', 'slate'); ?>
<?php echo t2g_apex_manager_kpi_card('Present Today', $present . '/' . max(1, $total), $att_rate . '% Attendance', 'emerald'); ?>
<?php echo t2g_apex_manager_kpi_card('On Leave', (string) $on_leave, $leave_pct . '% of team', 'amber'); ?>
<?php echo t2g_apex_manager_kpi_card('Active Tasks', (string) ($k['active_tasks'] ?? 0), 'Across team', 'blue'); ?>
<?php echo t2g_apex_manager_kpi_card('Avg. Check-in', html_escape($k['avg_checkin'] ?? '—'), 'Today', 'indigo'); ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
<div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<h3 class="text-sm font-bold text-slate-900 mb-3">Team Attendance Trend</h3>
<div class="relative h-[220px]"><canvas id="apexTeamAttendanceTrend"></canvas></div>
</div>
<div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<h3 class="text-sm font-bold text-slate-900 mb-3">Team Leave Summary</h3>
<div class="relative h-[220px] flex items-center justify-center"><canvas id="apexTeamLeaveDonut"></canvas></div>
</div>
<div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col items-center">
<h3 class="text-sm font-bold text-slate-900 mb-1 self-start w-full">Team Performance (PEDMA)</h3>
<p class="text-[11px] text-slate-500 mb-3 self-start w-full"><?php echo html_escape($pedma_month); ?></p>
<div class="relative w-44 h-44">
<canvas id="apexTeamPedmaGradeDonut" aria-label="PEDMA grade distribution"></canvas>
<div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
<span class="text-2xl font-black text-slate-900"><?php echo $pedma_evaluated > 0 ? (int) $pedma_evaluated : '—'; ?></span>
<span class="text-[10px] text-slate-500 uppercase font-semibold text-center px-2">A / B / C grades</span>
</div>
</div>
<div class="grid grid-cols-3 gap-2 mt-4 w-full text-center text-[11px]">
<div class="rounded-lg bg-emerald-50 p-2 border border-emerald-100"><div class="font-bold text-emerald-800"><?php echo (int) ($pg['A'] ?? 0); ?></div><div class="text-emerald-700">Grade A</div></div>
<div class="rounded-lg bg-blue-50 p-2 border border-blue-100"><div class="font-bold text-blue-800"><?php echo (int) ($pg['B'] ?? 0); ?></div><div class="text-blue-700">Grade B</div></div>
<div class="rounded-lg bg-amber-50 p-2 border border-amber-100"><div class="font-bold text-amber-800"><?php echo (int) ($pg['C'] ?? 0); ?></div><div class="text-amber-700">Grade C</div></div>
</div>
<?php $pedma_url = html_escape($u['manage_pedma'] ?? admin_url('staff/manage_pedma')); ?>
<a href="<?php echo $pedma_url; ?>" class="mt-3 text-[11px] font-semibold text-blue-600 hover:underline self-start">Open Manage PEDMA →</a>
</div>
</div>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
<h3 class="text-sm font-bold text-slate-900">Team Members Live Status</h3>
<div class="flex flex-wrap items-center gap-2">
<select id="apexTeamFilterDept" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50" data-apex-dept-preset="1" aria-label="Department filter"><option value="">All departments</option><?php
foreach ($dept_filters as $dep) {
    $name = html_escape($dep['name'] ?? '');
    if ($name === '') {
        continue;
    }
    echo '<option value="' . $name . '">' . $name . '</option>';
}
?></select>
<select id="apexTeamFilterStatus" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50">
<option value="">All Status</option>
<option value="present">Present</option>
<option value="absent">Absent</option>
<option value="leave">On leave</option>
</select>
<input type="search" id="apexTeamSearch" placeholder="Search employee…" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white min-w-[160px]"/>
</div>
</div>
<div class="table-responsive">
<table class="table table-striped table-bordered table-hover apex-sortable-table" id="apexTeamLiveTable">
<thead><tr>
<?php
echo t2g_apex_sortable_th('Employee', 0);
echo t2g_apex_sortable_th('Department', 1);
echo t2g_apex_sortable_th('First check-in', 2);
echo t2g_apex_sortable_th('Current task', 3);
echo t2g_apex_sortable_th('Task timer', 4);
echo t2g_apex_sortable_th('Break', 5);
echo t2g_apex_sortable_th('Snapshot', 6);
echo t2g_apex_sortable_th('Leave', 7);
?>
</tr></thead>
<tbody>
<?php echo t2g_apex_manager_live_status_rows($d['live_status'] ?? []); ?>
</tbody>
</table>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
<div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<h3 class="text-sm font-bold text-slate-900 mb-3">Complete Leave Data</h3>
<div class="table-responsive max-h-72 overflow-y-auto">
<table class="table table-striped table-bordered table-hover apex-sortable-table" id="apexTeamLeaveBalanceTable">
<thead><tr>
<?php
echo t2g_apex_sortable_th('Employee', 0);
echo t2g_apex_sortable_th('Earned', 1);
echo t2g_apex_sortable_th('Taken', 2);
echo t2g_apex_sortable_th('Balance', 3);
echo t2g_apex_sortable_th('Status', 4);
?>
</tr></thead>
<tbody><?php echo t2g_apex_manager_leave_balance_rows($d['leave_balances'] ?? []); ?></tbody>
</table>
</div>
</div>
<div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<h3 class="text-sm font-bold text-slate-900 mb-3">Upcoming Leaves</h3>
<div class="table-responsive max-h-72 overflow-y-auto">
<table class="table table-striped table-bordered table-hover apex-sortable-table" id="apexTeamUpcomingLeaveTable">
<thead><tr>
<?php
echo t2g_apex_sortable_th('Employee', 0);
echo t2g_apex_sortable_th('Type', 1);
echo t2g_apex_sortable_th('Dates', 2);
echo t2g_apex_sortable_th('Status', 3);
?>
</tr></thead>
<tbody><?php echo t2g_apex_manager_upcoming_leave_rows($d['upcoming_leaves'] ?? []); ?></tbody>
</table>
</div>
</div>
<div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
<h3 class="text-sm font-bold text-slate-900 mb-3">Quick Insights</h3>
<ul class="space-y-3 text-xs m-0 p-0 list-none">
<?php foreach ($d['insights'] ?? [] as $ins) {
    $tone = $ins['tone'] ?? 'neutral';
    $dot = 'bg-slate-400';
    if ($tone === 'danger') {
        $dot = 'bg-rose-500';
    } elseif ($tone === 'info') {
        $dot = 'bg-blue-500';
    } elseif ($tone === 'success') {
        $dot = 'bg-emerald-500';
    } elseif ($tone === 'purple') {
        $dot = 'bg-violet-500';
    } ?>
<li class="flex gap-2 items-start"><span class="w-2 h-2 rounded-full <?php echo $dot; ?> mt-1.5 shrink-0"></span><span class="text-slate-600 leading-relaxed"><?php echo html_escape($ins['text'] ?? ''); ?></span></li>
<?php } ?>
</ul>
<a href="<?php echo html_escape($u['timesheets'] ?? admin_url('staff/timesheets')); ?>" class="inline-block mt-4 text-xs font-semibold text-blue-600 hover:underline">Open team timesheets →</a>
</div>
</div>
</div>
</div>
    <?php
    return ob_get_clean();
}

function t2g_apex_manager_kpi_card($label, $value, $sub, $tone)
{
    $value_cls = 'text-slate-900';
    if ($tone === 'emerald') {
        $value_cls = 'text-emerald-700';
    } elseif ($tone === 'amber') {
        $value_cls = 'text-amber-700';
    } elseif ($tone === 'blue') {
        $value_cls = 'text-blue-700';
    } elseif ($tone === 'indigo') {
        $value_cls = 'text-indigo-700';
    }

    return '<div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">'
        . '<div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">' . html_escape($label) . '</div>'
        . '<div class="text-2xl font-black ' . $value_cls . ' mt-0.5">' . $value . '</div>'
        . '<div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500">' . html_escape($sub) . '</div></div>';
}

function t2g_apex_manager_live_status_rows(array $rows)
{
    if ($rows === []) {
        return '<tr><td colspan="8" class="text-center text-muted">No team members in your department scope yet.</td></tr>';
    }
    $html = '';
    foreach ($rows as $r) {
        $task = $r['task'] ?? null;
        $task_name = $task ? ($task['name'] ?? '—') : '—';
        $progress = $task ? (int) ($task['progress'] ?? 0) : 0;
        $dept = html_escape($r['department'] ?? '—');
        $html .= '<tr class="apex-team-live-row" data-status="' . html_escape($r['status_key'] ?? '') . '" data-dept="' . $dept . '" data-search="' . html_escape(strtolower(($r['name'] ?? '') . ' ' . ($r['emp_id'] ?? ''))) . '">';
        $html .= '<td><div class="flex items-center gap-2.5">'
            . '<img src="' . html_escape($r['avatar'] ?? '') . '" alt="" class="w-8 h-8 rounded-full object-cover bg-slate-100">'
            . '<div><div class="font-semibold text-slate-900">' . html_escape($r['name'] ?? '') . '</div>'
            . '<div class="text-[10px] text-slate-400 font-mono">' . html_escape($r['emp_id'] ?? '') . '</div></div></div></td>';
        $html .= '<td>' . $dept . '</td>';
        $ago = trim((string) ($r['check_in_ago'] ?? ''));
        $html .= '<td><strong>' . html_escape($r['check_in'] ?? '—') . '</strong>';
        if ($ago !== '') {
            $html .= '<br><small class="text-muted">' . html_escape($ago) . '</small>';
        }
        $html .= '</td>';
        $html .= '<td>' . html_escape($task_name)
            . '<div class="progress no-margin progress-bar-mini mtop5"><div class="progress-bar progress-bar-primary" style="width:' . min(100, $progress) . '%"></div></div>'
            . '<small class="text-muted">' . $progress . '%</small></td>';
        $timer = $task ? ($task['started'] ?? '—') : '—';
        $html .= '<td>' . ($task ? '<i class="fa fa-play text-success"></i> ' : '') . html_escape($timer) . '</td>';
        $break_cls = ($r['break_tone'] ?? '') === 'working' ? 'label-success' : 'label-default';
        $html .= '<td><span class="label ' . $break_cls . '">' . html_escape($r['break_label'] ?? '—') . '</span></td>';
        $snap = !empty($r['snapshot']);
        $html .= '<td>' . ($snap ? '<span class="text-success"><i class="fa fa-check-circle"></i> Yes</span>' : '<span class="text-muted">No</span>') . '</td>';
        $leave_lbl = $r['leave_label'] ?? '—';
        if ($leave_lbl !== '—' && $leave_lbl !== '') {
            $html .= '<td><span class="label label-warning">' . html_escape($leave_lbl) . '</span></td>';
        } else {
            $html .= '<td class="text-muted">—</td>';
        }
        $html .= '</tr>';
    }

    return $html;
}

function t2g_apex_manager_leave_balance_rows(array $rows)
{
    if ($rows === []) {
        return '<tr><td colspan="5" class="py-6 text-center text-slate-500">No data</td></tr>';
    }
    $html = '';
    foreach ($rows as $r) {
        $ok = ($r['balance'] ?? 0) > 0;
        $html .= '<tr>';
        $html .= '<td>' . html_escape($r['name'] ?? '') . '</td>';
        $html .= '<td>' . html_escape($r['earned'] ?? 0) . '</td>';
        $html .= '<td>' . html_escape($r['taken'] ?? 0) . '</td>';
        $html .= '<td><strong>' . html_escape($r['balance'] ?? 0) . '</strong></td>';
        $html .= '<td><span class="label ' . ($ok ? 'label-success' : 'label-danger') . '">' . html_escape($r['available'] ?? '') . '</span></td>';
        $html .= '</tr>';
    }

    return $html;
}

function t2g_apex_manager_upcoming_leave_rows(array $rows)
{
    if ($rows === []) {
        return '<tr><td colspan="4" class="py-6 text-center text-slate-500">No upcoming leave</td></tr>';
    }
    $html = '';
    foreach ($rows as $r) {
        $st = $r['status'] ?? 'Pending';
        $st_cls = $st === 'Approved' ? 'label-success' : ($st === 'Pending' ? 'label-warning' : 'label-danger');
        $html .= '<tr>';
        $html .= '<td>' . html_escape($r['name'] ?? '') . '</td>';
        $html .= '<td>' . html_escape($r['type'] ?? 'Leave') . '</td>';
        $html .= '<td class="tw-whitespace-nowrap">' . html_escape($r['range'] ?? '—') . '</td>';
        $html .= '<td><span class="label ' . $st_cls . '">' . html_escape($st) . '</span></td>';
        $html .= '</tr>';
    }

    return $html;
}
