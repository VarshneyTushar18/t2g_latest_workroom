<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
  .t2g-att .panel-body { padding: 18px 20px; }
  .t2g-att-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
  .t2g-att-head h4 { margin: 0; font-weight: 700; color: #1e293b; }
  .t2g-att-stats { display: flex; gap: 0; flex-wrap: wrap; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 14px; }
  .t2g-att-stat-card { flex: 1; min-width: 140px; padding: 14px 18px; border-right: 1px solid #e2e8f0; background: #fff; }
  .t2g-att-stat-card:last-child { border-right: none; }
  .t2g-att-stat-card span { display: block; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
  .t2g-att-stat-card strong { font-size: 20px; font-weight: 700; color: #0f172a; }
  .t2g-att-alert { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; font-size: 12px; font-weight: 600; padding: 8px 14px; border-radius: 6px; margin-bottom: 14px; }
  .t2g-att-layout { display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
  .t2g-att-cal-wrap { flex: 1 1 620px; min-width: 340px; }
  .t2g-att-cal {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px 14px;
  }
  .t2g-att-cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
  .t2g-att-cal-nav button {
    width: 30px; height: 30px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;
    font-size: 18px; line-height: 1; color: #475569;
  }
  .t2g-att-cal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
  .t2g-att-cal-weekdays, .t2g-att-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
  .t2g-att-cal-weekdays span { text-align: center; font-size: 11px; font-weight: 700; color: #64748b; padding: 4px 0 8px; }
  .t2g-att-cal-day {
    position: relative; min-height: 74px; padding: 5px 6px 8px; border-radius: 4px; cursor: pointer;
    border: 1px solid #e2e8f0; background: #fff; transition: box-shadow .15s ease, transform .1s ease;
  }
  .t2g-att-cal-day:not(.other-month):hover { box-shadow: 0 2px 6px rgba(15,23,42,.1); transform: translateY(-1px); }
  .t2g-att-cal-day.other-month { opacity: .3; cursor: default; background: #f8fafc; }
  .t2g-att-cal-day.selected { outline: 2px solid #2563eb; outline-offset: -2px; z-index: 1; }
  .t2g-att-cal-day.today .t2g-att-cal-num { color: #2563eb; font-weight: 800; }
  .t2g-att-cal-num { display: block; font-size: 12px; font-weight: 600; color: #334155; }
  .t2g-att-cal-code {
    display: block; margin-top: 6px; font-size: 12px; font-weight: 800; text-align: center; line-height: 1.2;
  }
  .t2g-att-cal-code.code-ok { color: #1d4ed8; }
  .t2g-att-cal-code.code-bad { color: #dc2626; }
  .t2g-att-cal-code.code-leave { color: #7c3aed; }
  .t2g-att-cal-code.code-off { color: #64748b; }
  .t2g-att-cal-code.code-holiday { color: #0369a1; }
  .t2g-att-cal-emp { display: none; }
  .t2g-att-cal-day.tone-ok { background: #dbeafe; border-color: #93c5fd; }
  .t2g-att-cal-day.tone-bad { background: #ffedd5; border-color: #fdba74; }
  .t2g-att-cal-day.tone-leave { background: #f3e8ff; border-color: #d8b4fe; }
  .t2g-att-cal-day.tone-holiday { background: #e0f2fe; border-color: #7dd3fc; }
  .t2g-att-cal-day.tone-off { background: #f8fafc; border-color: #e2e8f0; }
  .t2g-att-cal-day.tone-neutral { background: #fff; border-color: #e2e8f0; }
  .t2g-att-cal-day.today .t2g-att-cal-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 50%; background: #2563eb; color: #fff !important;
  }
  .t2g-att-cal-legend {
    display: flex; gap: 14px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px;
    border-top: 1px dashed #e2e8f0; font-size: 11px; color: #64748b;
  }
  .t2g-att-cal-legend span { display: inline-flex; align-items: center; gap: 5px; }
  .t2g-att-cal-legend i { display: inline-block; width: 12px; height: 12px; border-radius: 2px; border: 1px solid transparent; }
  .t2g-att-cal-legend .lg-ok { background: #dbeafe; border-color: #93c5fd; }
  .t2g-att-cal-legend .lg-bad { background: #ffedd5; border-color: #fdba74; }
  .t2g-att-cal-legend .lg-leave { background: #f3e8ff; border-color: #d8b4fe; }
  .t2g-att-cal-legend .lg-off { background: #f8fafc; border-color: #e2e8f0; }
  .t2g-att-detail {
    flex: 0 1 400px; min-width: 320px; background: #fff; border: 1px solid #e2e8f0;
    border-radius: 8px; min-height: 420px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.06);
  }
  .t2g-att-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 420px; padding: 32px; text-align: center; color: #64748b;
  }
  .t2g-att-empty i { font-size: 42px; color: #cbd5e1; margin-bottom: 14px; }
  .t2g-att-empty h5 { font-weight: 700; color: #1e293b; margin: 0 0 8px; }
  .t2g-att-empty p { font-size: 13px; margin: 0; max-width: 280px; line-height: 1.5; }
  .t2g-att-day-head { padding: 14px 16px 10px; border-bottom: 1px solid #e2e8f0; }
  .t2g-att-day-head h5 { margin: 0 0 6px; font-weight: 700; font-size: 15px; color: #0f172a; }
  .t2g-att-day-emp { font-size: 13px; font-weight: 600; color: #334155; }
  .t2g-att-day-shift { font-size: 12px; color: #64748b; margin-top: 2px; }
  .t2g-att-day-body { padding: 12px 16px 16px; overflow-x: auto; }
  .t2g-att-metrics { width: 100%; font-size: 10px; border-collapse: collapse; margin-bottom: 12px; min-width: 340px; }
  .t2g-att-metrics th, .t2g-att-metrics td { border: 1px solid #e2e8f0; padding: 6px 4px; text-align: center; }
  .t2g-att-metrics th { background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap; }
  .t2g-att-metrics td { font-weight: 600; color: #0f172a; }
  .t2g-att-section { font-size: 11px; font-weight: 700; margin: 10px 0 6px; color: #475569; text-transform: uppercase; letter-spacing: .03em; }
  .t2g-att-subtable { width: 100%; font-size: 11px; border-collapse: collapse; margin-bottom: 10px; }
  .t2g-att-subtable th, .t2g-att-subtable td { border: 1px solid #e2e8f0; padding: 6px 8px; }
  .t2g-att-subtable th { background: #f8fafc; font-weight: 700; width: 35%; }
  .t2g-att-actions-row { margin-top: 14px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
  .t2g-att-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; align-items: center; }
  .t2g-att-filters .form-control { max-width: 170px; height: 34px; font-size: 12px; }
  .t2g-att-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end; margin-bottom: 12px; }
  .t2g-att-badge-in { background: #16a34a; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 10px; font-weight: 700; }
  .t2g-att-badge-out { background: #dc2626; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 10px; font-weight: 700; }
  .t2g-att-punch-list ul { list-style: none; margin: 0; padding: 0; font-size: 12px; }
  .t2g-att-punch-list li { padding: 5px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; }
  .t2g-att-checkio-btn {
    background: #1e3a5f !important; border-color: #1e3a5f !important; color: #fff !important;
    font-weight: 600; border-radius: 6px; padding: 8px 16px; white-space: nowrap;
  }
  .t2g-att-checkio-btn:hover, .t2g-att-checkio-btn:focus { background: #152a47 !important; border-color: #152a47 !important; color: #fff !important; }
</style>

<div id="wrapper">
  <div class="content t2g-att">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="t2g-att-head">
              <div>
                <h4>Attendance Info</h4>
                <small class="text-muted"><?php echo html_escape($staff_name); ?><?php if (!empty($staff_code) && $staff_code !== '0000') { ?> · <?php echo html_escape($staff_code); ?><?php } ?></small>
              </div>
              <?php
              $data_timekeeping_form = get_timesheets_option('timekeeping_form');
              if ($data_timekeeping_form === 'timekeeping_manually' && (int) $staff_id === (int) get_staff_user_id()) { ?>
                <button type="button" onclick="open_check_in_out();" class="btn btn-sm t2g-att-checkio-btn check_in_out_timesheet" data-toggle="tooltip" title="<?php echo _l('check_in'); ?> / <?php echo _l('check_out'); ?>">
                  <?php echo _l('check_in'); ?> / <?php echo _l('check_out'); ?>
                </button>
              <?php } ?>
            </div>

            <div class="t2g-att-filters">
              <input type="month" id="att_reg_month" class="form-control" value="<?php echo html_escape($month_year); ?>">
              <?php if (!empty($can_pick_staff) && !empty($staff_list)) { ?>
                <select id="att_reg_staff" class="form-control">
                  <?php foreach ($staff_list as $s) {
                    $is_me = ((int) $s['staffid'] === (int) get_staff_user_id());
                  ?>
                    <option value="<?php echo (int) $s['staffid']; ?>" <?php echo ((int) $s['staffid'] === (int) $staff_id) ? 'selected' : ''; ?>>
                      <?php echo html_escape(trim($s['firstname'] . ' ' . $s['lastname'])); ?><?php echo $is_me ? ' (Me)' : ''; ?>
                    </option>
                  <?php } ?>
                </select>
              <?php } else { ?>
                <input type="hidden" id="att_reg_staff" value="<?php echo (int) $staff_id; ?>">
              <?php } ?>
            </div>

            <div class="t2g-att-actions">
              <a href="<?php echo admin_url('timesheets/attendance_regularization?month=' . urlencode($month_year)); ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-edit"></i> Regularization &amp; Permission
              </a>
              <?php if (false && (has_permission('attendance_management', '', 'view_own') || has_permission('attendance_management', '', 'view') || is_admin())) { ?>
                <a href="<?php echo admin_url('timesheets/timekeeping'); ?>" class="btn btn-default btn-sm">Team calendar</a>
              <?php } ?>
              <button type="button" class="btn btn-default btn-sm" id="att_export_excel">
                <i class="fa fa-file-excel-o"></i> <?php echo _l('export_to_excel'); ?>
              </button>
            </div>

            <div class="t2g-att-stats" id="t2g_month_stats">
              <div class="t2g-att-stat-card"><span>Avg. work hrs</span><strong id="stat_avg_work">—</strong></div>
              <div class="t2g-att-stat-card"><span>Avg. actual work hrs</span><strong id="stat_avg_actual">—</strong></div>
              <div class="t2g-att-stat-card"><span>Penalty days</span><strong id="stat_penalty">0</strong></div>
            </div>
            <div class="t2g-att-alert" id="t2g_exception_banner" style="display:none;"></div>

            <div class="t2g-att-layout">
              <div class="t2g-att-cal-wrap">
                <div class="t2g-att-cal">
                  <div class="t2g-att-cal-nav">
                    <button type="button" id="t2g_cal_prev" aria-label="Previous month">&lsaquo;</button>
                    <div class="t2g-att-cal-title" id="t2g_cal_title"></div>
                    <button type="button" id="t2g_cal_next" aria-label="Next month">&rsaquo;</button>
                  </div>
                  <div class="t2g-att-cal-weekdays">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                  </div>
                  <div class="t2g-att-cal-grid" id="t2g_cal_grid"></div>
                  <div class="t2g-att-cal-legend">
                    <span><i class="lg-ok"></i> OK (9+ hrs)</span>
                    <span><i class="lg-bad"></i> Exception / &lt;9 hrs</span>
                    <span><i class="lg-leave"></i> Leave</span>
                    <span><i class="lg-off"></i> Off / Holiday</span>
                  </div>
                </div>
              </div>
              <div class="t2g-att-detail" id="attRegDayPanel">
                <div class="t2g-att-empty" id="t2g_empty_tpl">
                  <i class="fa fa-calendar-o"></i>
                  <h5>Select a date</h5>
                  <p>Click any day on the calendar to view attendance details.</p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="attRegModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Request attendance correction</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="reg_date" value="">
        <p id="reg_day_label" class="text-muted" style="font-size:12px;"></p>
        <div class="form-group">
          <label>Corrected time in <span class="text-danger">*</span></label>
          <input type="time" class="form-control input-sm" id="reg_time_in" required>
        </div>
        <div class="form-group">
          <label>Corrected time out <span class="text-danger">*</span></label>
          <input type="time" class="form-control input-sm" id="reg_time_out" required>
        </div>
        <div class="form-group">
          <label>Reason <span class="text-danger">*</span></label>
          <textarea class="form-control input-sm" id="reg_reason" rows="2" placeholder="Brief reason for correction"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="reg_submit_btn">Send for approval</button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function($) {
  'use strict';
  var monthYear = '<?php echo html_escape($month_year); ?>';
  var staffCode = '<?php echo html_escape($staff_code ?? ''); ?>';
  var calendarData = <?php echo json_encode($calendar['days'] ?? []); ?>;
  var dayMap = {};
  var selectedDate = '';

  calendarData.forEach(function(d) { dayMap[d.date] = d; });

  function esc(s) { return $('<div>').text(s || '').html(); }
  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function parseMonthYear(my) {
    var p = (my || monthYear).split('-');
    return { year: parseInt(p[0], 10), month: parseInt(p[1], 10) - 1 };
  }
  function dateStr(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }
  function monthLabel(y, m) {
    return ['January','February','March','April','May','June','July','August','September','October','November','December'][m] + ' ' + y;
  }
  function parseHrs(val) {
    if (!val || val === '—') return null;
    var n = parseFloat(String(val).replace('h', ''));
    return isNaN(n) ? null : n;
  }
  function fmtClock(decimal) {
    if (decimal === null || decimal <= 0) return '—';
    var h = Math.floor(decimal);
    var m = Math.round((decimal - h) * 60);
    if (m === 60) { h++; m = 0; }
    return pad(h) + ':' + pad(m);
  }

  function gapDays() {
    return calendarData.filter(function(d) {
      return d.can_regularise || d.status === 'absent' || d.status === 'punch_missing' || d.status === 'short_hours';
    });
  }

  function dayCodeLabel(code) {
    if (code === 'AB') return 'A';
    return code || '';
  }

  function dayHours(day) {
    if (!day) return 0;
    if (typeof day.hours === 'number' && day.hours > 0) return day.hours;
    var aw = parseHrs(day.actual_work_hrs);
    if (aw !== null) return aw;
    return parseHrs(day.total_work_hrs) || 0;
  }

  function dayHealthTone(day) {
    if (!day || day.status === 'future') return 'neutral';
    var code = day.code || '';
    if (day.status === 'weekend' || code === 'O') return 'off';
    if (day.status === 'holiday' || code === 'HO' || code === 'H') return 'holiday';
    if (day.status === 'leave' || day.status === 'saturday_leave') return 'leave';
    if (['EL', 'PL', 'SL', 'UL', 'CO', 'MAL', 'PHD', 'UHD', 'PAL', 'SHL'].indexOf(code) >= 0) return 'leave';

    // Approved regularization = Present (green), regardless of original punch gaps.
    if (day.status === 'regularised') {
      return 'ok';
    }
    if (day.status === 'pending') {
      return 'neutral';
    }

    var req = day.required_hours || 9;
    var hrs = dayHours(day);

    if (day.status === 'absent' || day.status === 'punch_missing' || day.status === 'rejected' || code === 'AB' || code === 'A') {
      return 'bad';
    }
    if (day.status === 'short_hours' || code === 'HD') return 'bad';
    if (day.can_regularise && hrs > 0 && hrs < req) return 'bad';
    if (hrs > 0 && hrs < req && (day.status === 'ok' || code === 'P')) return 'bad';
    if (day.status === 'ok' || code === 'P') {
      return hrs >= req ? 'ok' : (hrs > 0 ? 'bad' : 'neutral');
    }
    if (hrs >= req) return 'ok';
    if (hrs > 0 && hrs < req) return 'bad';
    return 'neutral';
  }

  function dayCodeClass(tone) {
    if (tone === 'ok') return 'code-ok';
    if (tone === 'bad') return 'code-bad';
    if (tone === 'leave') return 'code-leave';
    if (tone === 'off' || tone === 'holiday') return tone === 'holiday' ? 'code-holiday' : 'code-off';
    return '';
  }

  function dayCodeHtml(day) {
    if (!day || !day.code) return '';
    var tone = dayHealthTone(day);
    var label = dayCodeLabel(day.code);
    return '<span class="t2g-att-cal-code ' + dayCodeClass(tone) + '">' + esc(label) + '</span>';
  }

  function updateMonthStats() {
    var workHrs = [], actualHrs = [], penalty = 0, exceptions = 0;
    calendarData.forEach(function(d) {
      if (d.status === 'absent' || d.status === 'short_hours' || d.status === 'punch_missing') {
        penalty++;
        exceptions++;
      }
      var tw = parseHrs(d.total_work_hrs);
      var aw = parseHrs(d.actual_work_hrs);
      if (tw !== null) workHrs.push(tw);
      if (aw !== null) actualHrs.push(aw);
    });
    var avgW = workHrs.length ? workHrs.reduce(function(a, b) { return a + b; }, 0) / workHrs.length : 0;
    var avgA = actualHrs.length ? actualHrs.reduce(function(a, b) { return a + b; }, 0) / actualHrs.length : 0;
    $('#stat_avg_work').text(avgW > 0 ? fmtClock(avgW) : '—');
    $('#stat_avg_actual').text(avgA > 0 ? fmtClock(avgA) : '—');
    $('#stat_penalty').text(penalty);
    if (exceptions > 0) {
      $('#t2g_exception_banner').text(exceptions + ' exception day(s) this month — use Regularization to correct.').show();
    } else {
      $('#t2g_exception_banner').hide();
    }
  }

  function renderCalendar() {
    var vm = parseMonthYear(monthYear);
    $('#t2g_cal_title').text(monthLabel(vm.year, vm.month));

    var first = new Date(vm.year, vm.month, 1);
    var sundayStart = first.getDay();
    var daysInMonth = new Date(vm.year, vm.month + 1, 0).getDate();
    var daysInPrev = new Date(vm.year, vm.month, 0).getDate();
    var todayStr = new Date().getFullYear() + '-' + pad(new Date().getMonth() + 1) + '-' + pad(new Date().getDate());

    var html = '';
    var cell = 0;
    for (var i = sundayStart - 1; i >= 0; i--) {
      html += '<div class="t2g-att-cal-day other-month"><span class="t2g-att-cal-num">' + (daysInPrev - i) + '</span></div>';
      cell++;
    }
    for (var d = 1; d <= daysInMonth; d++) {
      var ds = dateStr(vm.year, vm.month, d);
      var day = dayMap[ds];
      var tone = day ? dayHealthTone(day) : 'neutral';
      var cls = 't2g-att-cal-day tone-' + tone;
      if (ds === todayStr) cls += ' today';
      if (ds === selectedDate) cls += ' selected';
      html += '<div class="' + cls + '" data-date="' + ds + '">';
      html += '<span class="t2g-att-cal-num">' + d + '</span>';
      if (day && day.code) html += dayCodeHtml(day);
      html += '</div>';
      cell++;
    }
    var next = 1;
    while (cell % 7 !== 0) {
      html += '<div class="t2g-att-cal-day other-month"><span class="t2g-att-cal-num">' + next + '</span></div>';
      next++; cell++;
    }
    $('#t2g_cal_grid').html(html);
    updateMonthStats();
  }

  function emptyPanelHtml() {
    return $('#t2g_empty_tpl').prop('outerHTML');
  }

  function valOrDash(v) { return (v && v !== '') ? esc(v) : '—'; }

  function renderDayPanel(day) {
    if (!day) {
      $('#attRegDayPanel').html(emptyPanelHtml());
      return;
    }
    var dayNum = day.day || parseInt(day.date.split('-')[2], 10);
    var html = '<div class="t2g-att-day-head">';
    html += '<h5>' + dayNum + ' ' + esc(day.weekday) + '</h5>';
    if (staffCode && staffCode !== '0000') {
      html += '<div class="t2g-att-day-emp">' + esc(staffCode) + '</div>';
    }
    html += '<div class="t2g-att-day-shift">' + esc(day.shift_scheme || 'General') + '</div>';
    if (day.shift_start && day.shift_end) {
      html += '<div class="t2g-att-day-shift">Shift : ' + esc(day.shift_start) + ' to ' + esc(day.shift_end) + '</div>';
    }
    html += '</div><div class="t2g-att-day-body">';

    html += '<table class="t2g-att-metrics"><thead><tr>';
    html += '<th>First In</th><th>Last Out</th><th>Late In</th><th>Early Out</th>';
    html += '<th>Total Work Hrs</th><th>Break Hrs</th><th>Actual Work Hrs</th>';
    html += '</tr></thead><tbody><tr>';
    html += '<td>' + valOrDash(day.first_in || day.check_in) + '</td>';
    html += '<td>' + valOrDash(day.last_out || day.check_out) + '</td>';
    html += '<td>' + valOrDash(day.late_in) + '</td>';
    html += '<td>' + valOrDash(day.early_out) + '</td>';
    html += '<td>' + valOrDash(day.total_work_hrs) + '</td>';
    html += '<td>' + valOrDash(day.break_hrs) + '</td>';
    html += '<td>' + valOrDash(day.actual_work_hrs) + '</td>';
    html += '</tr></tbody></table>';

    var remarks = (day.issues && day.issues.length) ? day.issues.join('; ') : '';
    if (day.status === 'pending') remarks = 'Regularization request awaiting approval';
    html += '<div class="t2g-att-section">Status Details</div>';
    html += '<table class="t2g-att-subtable"><tbody>';
    html += '<tr><th>Status</th><td>' + esc(day.status_label || day.code || '—') + '</td></tr>';
    html += '<tr><th>Remarks</th><td>' + (remarks ? esc(remarks) : '—') + '</td></tr>';
    if (day.regularisation && parseInt(day.regularisation.status, 10) === 2) {
      var rejReason = (day.regularisation.rejection_comment || '').trim();
      html += '<tr><th>Rejection reason</th><td class="text-danger">' + (rejReason ? esc(rejReason) : '—') + '</td></tr>';
    }
    html += '</tbody></table>';

    if (day.sessions && day.sessions.length) {
      html += '<div class="t2g-att-section">Session Details</div>';
      html += '<table class="t2g-att-subtable"><thead><tr><th>Session</th><th>First in</th><th>Last out</th></tr></thead><tbody>';
      day.sessions.forEach(function(s) {
        html += '<tr><td>' + esc(s.label) + '</td><td>' + valOrDash(s.first_in) + '</td><td>' + valOrDash(s.last_out) + '</td></tr>';
      });
      html += '</tbody></table>';
    }

    if (day.swipes && day.swipes.length) {
      html += '<div class="t2g-att-section">Punch Log</div><div class="t2g-att-punch-list"><ul>';
      day.swipes.forEach(function(sw) {
        var badge = sw.type === 'IN' ? '<span class="t2g-att-badge-in">IN</span>' : (sw.type === 'OUT' ? '<span class="t2g-att-badge-out">OUT</span>' : esc(sw.type));
        html += '<li><span>' + esc(sw.time) + '</span>' + badge + '</li>';
      });
      html += '</ul></div>';
    }

    if (day.can_regularise || day.status === 'pending') {
      html += '<div class="t2g-att-actions-row">';
      if (day.can_regularise) {
        html += '<button type="button" class="btn btn-primary btn-sm btn-open-reg" data-date="' + esc(day.date) + '">Regularize</button>';
      } else if (day.status === 'pending') {
        html += '<p class="text-warning mbot0" style="font-size:12px;"><i class="fa fa-clock-o"></i> Awaiting approval</p>';
      }
      html += '</div>';
    }

    html += '</div>';
    $('#attRegDayPanel').html(html);
  }

  function staffId() {
    var $s = $('#att_reg_staff');
    return $s.is('select') ? $s.val() : $s.val();
  }

  function reloadCalendar(newMonth) {
    if (newMonth) monthYear = newMonth;
    $.getJSON(admin_url + 'timesheets/my_attendance_calendar', { month: monthYear, staff_id: staffId() }).done(function(res) {
      calendarData = res.days || [];
      dayMap = {};
      calendarData.forEach(function(d) { dayMap[d.date] = d; });
      selectedDate = '';
      renderCalendar();
      renderDayPanel(null);
    });
  }

  function shiftMonth(delta) {
    var vm = parseMonthYear(monthYear);
    var d = new Date(vm.year, vm.month + delta, 1);
    monthYear = d.getFullYear() + '-' + pad(d.getMonth() + 1);
    $('#att_reg_month').val(monthYear);
    if ($('#att_reg_staff').is('select')) {
      window.location.href = admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(monthYear) + '&staff_id=' + encodeURIComponent(staffId());
      return;
    }
    reloadCalendar(monthYear);
  }

  function openRegModal(day) {
    $('#reg_date').val(day.date);
    $('#reg_day_label').text('Correction for ' + day.date);
    $('#reg_time_in').val(day.check_in && day.check_in !== '—' ? day.check_in.substring(0, 5) : (day.shift_start || '09:30'));
    $('#reg_time_out').val(day.check_out && day.check_out !== '—' ? day.check_out.substring(0, 5) : (day.shift_end || '18:30'));
    $('#reg_reason').val('');
    $('#attRegModal').modal('show');
  }

  renderCalendar();

  $(document).on('click', '.t2g-att-cal-day:not(.other-month)', function() {
    selectedDate = $(this).data('date');
    renderCalendar();
    renderDayPanel(dayMap[selectedDate] || null);
  });

  $('#t2g_cal_prev').on('click', function() { shiftMonth(-1); });
  $('#t2g_cal_next').on('click', function() { shiftMonth(1); });

  $(document).on('click', '.btn-open-reg', function() {
    openRegModal(dayMap[$(this).data('date')] || { date: $(this).data('date') });
  });

  $('#att_reg_month').on('change', function() {
    monthYear = $(this).val();
    if ($('#att_reg_staff').is('select')) {
      window.location.href = admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(monthYear) + '&staff_id=' + encodeURIComponent(staffId());
      return;
    }
    reloadCalendar(monthYear);
  });

  $('#att_reg_staff').on('change', function() {
    window.location.href = admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(monthYear) + '&staff_id=' + encodeURIComponent(staffId());
  });

  $('#att_export_excel').on('click', function() {
    var $btn = $(this);
    if ($btn.prop('disabled')) return;
    $btn.prop('disabled', true);
    var data = { month: monthYear || $('#att_reg_month').val(), department: '', role: '', staff: [String(staffId())] };
    if (typeof csrfData !== 'undefined') data[csrfData.token_name] = csrfData.hash;
    $.post(admin_url + 'timesheets/export_attendance_excel', data).done(function(response) {
      try {
        response = typeof response === 'string' ? JSON.parse(response) : response;
        if (response.site_url && response.filename) window.location.href = response.site_url + response.filename;
        else alert_float('danger', 'Export failed.');
      } catch (e) { alert_float('danger', 'Export failed.'); }
    }).fail(function() { alert_float('danger', 'Could not export attendance.'); })
      .always(function() { $btn.prop('disabled', false); });
  });

  $('#reg_submit_btn').on('click', function() {
    var payload = {
      additional_day: $('#reg_date').val(),
      time_in: $('#reg_time_in').val(),
      time_out: $('#reg_time_out').val(),
      reason: $('#reg_reason').val(),
      timekeeping_value: ''
    };
    if (typeof csrfData !== 'undefined') payload[csrfData.token_name] = csrfData.hash;
    if (!payload.additional_day || !payload.time_in || !payload.time_out || !payload.reason) {
      alert_float('warning', 'Please complete all fields.');
      return;
    }
    var parts = String(payload.additional_day).split('-');
    if (parts.length === 3) {
      var dateYm = parts[0] + '-' + parts[1];
      var now = new Date();
      var currentMy = now.getFullYear() + '-' + (now.getMonth() + 1 < 10 ? '0' : '') + (now.getMonth() + 1);
      var prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      var prevMy = prev.getFullYear() + '-' + (prev.getMonth() + 1 < 10 ? '0' : '') + (prev.getMonth() + 1);
      if (dateYm !== currentMy && dateYm !== prevMy) {
        alert_float('warning', 'Regularization is allowed only for the current and previous month.');
        return;
      }
    }
    $('#reg_submit_btn').prop('disabled', true);
    $.post(admin_url + 'timesheets/submit_attendance_regularisation', payload).done(function(res) {
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { res = {}; }
      if (res.success) {
        alert_float('success', res.message || 'Request submitted');
        $('#attRegModal').modal('hide');
        setTimeout(function() { location.reload(); }, 600);
      } else {
        alert_float('danger', res.message || 'Could not submit');
      }
    }).fail(function(xhr) {
      var msg = 'Could not submit request';
      if (xhr && xhr.responseText) {
        try { var err = JSON.parse(xhr.responseText); if (err.message) msg = err.message; } catch(e) {}
      }
      alert_float('danger', msg);
    }).always(function() { $('#reg_submit_btn').prop('disabled', false); });
  });
})(jQuery);
</script>
</body>
</html>
