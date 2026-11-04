<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$_where_projects = '';
if (!has_permission('projects', '', 'view')) {
    $_where_projects = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
}
$total_projects             = total_rows(db_prefix() . 'projects', $_where_projects);
$where_in_progress          = ($_where_projects === '' ? '' : $_where_projects . ' AND ') . 'status = 2';
$total_projects_in_progress = total_rows(db_prefix() . 'projects', $where_in_progress);

$_where_tasks = '';
if (!has_permission('tasks', '', 'view')) {
    $_where_tasks = db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ')';
}
$total_tasks              = total_rows(db_prefix() . 'tasks', $_where_tasks);
$where_open_tasks         = ($_where_tasks === '' ? '' : $_where_tasks . ' AND ') . 'status != ' . Tasks_model::STATUS_COMPLETE;
$total_not_finished_tasks = total_rows(db_prefix() . 'tasks', $where_open_tasks);

$where_leads = '';
if (!is_admin()) {
    $where_leads .= '(addedfrom = ' . get_staff_user_id() . ' OR assigned = ' . get_staff_user_id() . ')';
}
$total_leads = total_rows(db_prefix() . 'leads', ($where_leads === '' ? 'junk=0' : $where_leads . ' AND junk =0'));
if ($where_leads === '') {
    $where_leads_open = 'status=1';
} else {
    $where_leads_open = $where_leads . ' AND status =1';
}
$total_leads_open = total_rows(db_prefix() . 'leads', $where_leads_open);

$leave_balance_days = null;
$break_minutes      = null;
$CI                 = &get_instance();
try {
    if ($CI->db->table_exists(db_prefix() . 'timesheets_option') && $CI->app_modules->is_active('timesheets')) {
        $CI->load->model('timesheets/timesheets_model');
        if (method_exists($CI->timesheets_model, 'get_staff_leave_balance_cards')) {
            $cards = $CI->timesheets_model->get_staff_leave_balance_cards((int) get_staff_user_id(), (int) date('Y'), (int) date('m'));
            $sum   = 0.0;
            foreach ($cards as $card) {
                if (!empty($card['slug']) && $card['slug'] === 'loss-of-pay') {
                    continue;
                }
                $sum += (float) ($card['balance'] ?? 0);
            }
            $leave_balance_days = round($sum, 1);
        }
    }
    if (function_exists('timesheets_admin_navbar_punch_context')) {
        $CI->load->helper('timesheets/timesheets');
        $punch_ctx = timesheets_admin_navbar_punch_context(13);
        if (!empty($punch_ctx['biometric_break_summary']['total_minutes'])) {
            $break_minutes = (int) $punch_ctx['biometric_break_summary']['total_minutes'];
        }
    }
} catch (Throwable $e) {
    log_message('error', 'Associate KPI widget: ' . $e->getMessage());
}
?>
<div class="widget apex-kpi-widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('quick_stats'); ?>">
    <div class="widget-dragger"></div>
    <section class="apex-kpi-grid" data-purpose="metric-summary-row">
        <a class="apex-kpi-card apex-kpi-tone-blue" href="<?php echo admin_url('projects'); ?>">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </span>
                <span class="apex-kpi-chevron" aria-hidden="true">›</span>
            </div>
            <div class="apex-kpi-label"><?php echo _l('projects'); ?></div>
            <div class="apex-kpi-value"><?php echo (int) $total_projects; ?></div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta"><?php echo (int) $total_projects_in_progress; ?> in progress</span>
            </div>
        </a>

        <a class="apex-kpi-card apex-kpi-tone-purple" href="<?php echo admin_url('tasks/list_tasks'); ?>">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="apex-kpi-chevron" aria-hidden="true">›</span>
            </div>
            <div class="apex-kpi-label"><?php echo _l('tasks'); ?></div>
            <div class="apex-kpi-value"><?php echo (int) $total_not_finished_tasks; ?></div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta"><?php echo (int) $total_tasks; ?> total assigned</span>
                <?php if ($total_not_finished_tasks > 0) { ?>
                <span class="apex-kpi-pill apex-kpi-pill-purple">Open</span>
                <?php } ?>
            </div>
        </a>

        <a class="apex-kpi-card apex-kpi-tone-teal" href="<?php echo admin_url('timesheets/my_attendance'); ?>">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </span>
                <span class="apex-kpi-chevron" aria-hidden="true">›</span>
            </div>
            <div class="apex-kpi-label">Attendance</div>
            <div class="apex-kpi-value" id="apexKpiAttendancePct">—</div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta" id="apexKpiAttendanceMeta">This month</span>
            </div>
        </a>

        <a class="apex-kpi-card apex-kpi-tone-amber" href="<?php echo admin_url('staff/leave_balance'); ?>">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </span>
                <span class="apex-kpi-chevron" aria-hidden="true">›</span>
            </div>
            <div class="apex-kpi-label">Leave balance</div>
            <div class="apex-kpi-value" id="apexKpiLeaveDays"<?php echo $leave_balance_days === null ? ' data-needs-fetch="1"' : ''; ?>>
                <?php if ($leave_balance_days !== null) {
                    echo html_escape($leave_balance_days) . ' <span class="apex-kpi-unit">Days</span>';
                } else {
                    echo '—';
                } ?>
            </div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta">Combined eligible leave</span>
            </div>
        </a>

        <a class="apex-kpi-card apex-kpi-tone-rose" href="<?php echo admin_url('leads'); ?>">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg>
                </span>
                <span class="apex-kpi-chevron" aria-hidden="true">›</span>
            </div>
            <div class="apex-kpi-label">Tickets</div>
            <div class="apex-kpi-value"><?php echo (int) $total_leads_open; ?></div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta"><?php echo (int) $total_leads; ?> total pipeline</span>
                <?php if ($total_leads_open > 0) { ?>
                <span class="apex-kpi-pill apex-kpi-pill-rose">Open</span>
                <?php } ?>
            </div>
        </a>

        <div class="apex-kpi-card apex-kpi-tone-cyan">
            <div class="apex-kpi-card-top">
                <span class="apex-kpi-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="apex-kpi-label">Break</div>
            <div class="apex-kpi-value" id="apexKpiBreakMin">
                <?php echo $break_minutes !== null ? (int) $break_minutes : '—'; ?>
                <span class="apex-kpi-unit">min</span>
            </div>
            <div class="apex-kpi-foot">
                <span class="apex-kpi-meta">Today</span>
                <span class="apex-kpi-pill apex-kpi-pill-emerald">Live</span>
            </div>
        </div>
    </section>
</div>
