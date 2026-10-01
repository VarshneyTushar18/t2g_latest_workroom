<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
  .t2g-reg .panel-body { padding: 18px 20px; }
  .t2g-reg-tabs { display: flex; justify-content: center; gap: 0; margin-bottom: 20px; flex-wrap: wrap; }
  .t2g-reg-tabs button {
    border: 1px solid #e2e8f0; background: #fff; padding: 10px 28px; font-size: 13px; font-weight: 600;
    color: #64748b; cursor: pointer;
  }
  .t2g-reg-tabs button:first-child { border-radius: 6px 0 0 6px; }
  .t2g-reg-tabs button:last-child { border-radius: 0 6px 6px 0; }
  .t2g-reg-tabs button.active { background: #2563eb; color: #fff; border-color: #2563eb; }
  .t2g-reg-layout { display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap; }
  .t2g-reg-cal {
    width: 280px; flex: 0 0 280px; background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 14px;
  }
  @media (max-width: 1199px) {
    .t2g-reg .col-md-4.text-right { text-align: left !important; margin-top: 8px; }
    .t2g-reg-tabs button { padding: 10px 16px; font-size: 12px; }
    .t2g-reg-cal { width: 100%; flex: 1 1 260px; max-width: 320px; }
  }
  .t2g-reg-cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
  .t2g-reg-cal-nav button {
    width: 28px; height: 28px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;
    font-size: 16px; line-height: 1; color: #475569;
  }
  .t2g-reg-cal-title { font-size: 14px; font-weight: 700; color: #0f172a; }
  .t2g-reg-cal-weekdays, .t2g-reg-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
  .t2g-reg-cal-weekdays span { text-align: center; font-size: 10px; font-weight: 700; color: #94a3b8; padding: 2px 0 6px; }
  .t2g-reg-cal-day {
    text-align: center; padding: 5px 2px; border-radius: 6px; cursor: pointer; min-height: 32px;
    font-size: 12px; font-weight: 600; color: #334155; position: relative;
  }
  .t2g-reg-cal-day.other-month { opacity: .35; cursor: default; }
  .t2g-reg-cal-day.selected { background: #2563eb; color: #fff; }
  .t2g-reg-cal-day.gap-day::before {
    content: ''; position: absolute; top: 2px; left: 2px; width: 0; height: 0;
    border-style: solid; border-width: 6px 6px 0 0; border-color: #2563eb transparent transparent transparent;
  }
  .t2g-reg-cal-day.gap-day.selected::before { border-color: #fff transparent transparent transparent; }
  .t2g-reg-cal-foot { margin-top: 12px; padding-top: 10px; border-top: 1px dashed #cbd5e1; text-align: center; }
  .t2g-reg-gap-count { font-size: 12px; color: #dc2626; font-weight: 600; margin-bottom: 8px; }
  .t2g-reg-quick-add {
    font-size: 12px; font-weight: 600; padding: 6px 16px; border-radius: 6px;
    border: 1px solid #cbd5e1; background: #fff; color: #334155;
  }
  .t2g-reg-main { flex: 1; min-width: 300px; min-height: 380px; }
  .t2g-reg-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 380px; text-align: center; color: #64748b; padding: 32px;
  }
  .t2g-reg-empty i { font-size: 48px; color: #cbd5e1; margin-bottom: 16px; }
  .t2g-reg-empty h5 { font-weight: 700; color: #1e293b; margin: 0 0 8px; }
  .t2g-reg-empty p { font-size: 13px; margin: 0; }
  .t2g-reg-form { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; max-width: 480px; box-shadow: 0 1px 3px rgba(15,23,42,.06); }
  .t2g-reg-form h5 { margin: 0 0 16px; font-weight: 700; color: #0f172a; }
  .t2g-reg-cal-day:not(.other-month):hover { background: #eff6ff; }
  .t2g-reg-panel { display: none; }
  .t2g-reg-panel.active { display: block; }
</style>

<div id="wrapper">
  <div class="content t2g-reg">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row mbot15">
              <div class="col-md-8">
                <h4 class="no-margin">Regularization &amp; Permission</h4>
                <p class="text-muted"><?php echo html_escape($staff_name); ?> · <?php echo html_escape($staff_code ?? ''); ?></p>
                <p class="text-muted" style="margin:4px 0 0;font-size:12px;">You can regularize only the <strong>current month</strong> and the <strong>previous month</strong>.</p>
              </div>
              <div class="col-md-4 text-right">
                <a href="<?php echo admin_url('timesheets/my_attendance?month=' . urlencode($month_year ?? date('Y-m'))); ?>" class="btn btn-default btn-sm">
                  <i class="fa fa-calendar"></i> Attendance Info
                </a>
              </div>
            </div>

            <?php if (!empty($can_pick_staff) && !empty($staff_list)) { ?>
              <div class="row mbot15">
                <div class="col-md-4">
                  <select id="att_reg_staff_pick" class="form-control" onchange="window.location.href='<?php echo admin_url('timesheets/attendance_regularization'); ?>?staff_id=' + this.value + '&month=' + encodeURIComponent('<?php echo html_escape($month_year ?? date('Y-m')); ?>');">
                    <?php foreach ($staff_list as $s) {
                      $is_me = ((int) $s['staffid'] === (int) get_staff_user_id());
                    ?>
                      <option value="<?php echo (int) $s['staffid']; ?>" <?php echo ((int) $s['staffid'] === (int) $staff_id) ? 'selected' : ''; ?>>
                        <?php echo html_escape(trim($s['firstname'] . ' ' . $s['lastname'])); ?><?php echo $is_me ? ' (Me)' : ''; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <?php } ?>

            <div class="t2g-reg-tabs" role="tablist">
              <button type="button" class="active" data-tab="apply">Apply</button>
              <button type="button" data-tab="pending">Pending</button>
              <button type="button" data-tab="history">History</button>
            </div>

            <div class="t2g-reg-panel active" id="tab_apply">
              <div class="t2g-reg-layout">
                <div class="t2g-reg-cal">
                  <div class="t2g-reg-cal-nav">
                    <button type="button" id="reg_cal_prev" aria-label="Previous">&lsaquo;</button>
                    <div class="t2g-reg-cal-title" id="reg_cal_title"></div>
                    <button type="button" id="reg_cal_next" aria-label="Next">&rsaquo;</button>
                  </div>
                  <div class="t2g-reg-cal-weekdays">
                    <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                  </div>
                  <div class="t2g-reg-cal-grid" id="reg_cal_grid"></div>
                  <div class="t2g-reg-cal-foot">
                    <div class="t2g-reg-gap-count" id="reg_gap_count">0 Gap day(s)</div>
                    <button type="button" class="t2g-reg-quick-add" id="reg_quick_add">Quick Add</button>
                  </div>
                </div>
                <div class="t2g-reg-main" id="reg_apply_panel">
                  <div class="t2g-reg-empty" id="reg_empty_state">
                    <i class="fa fa-calendar-plus-o"></i>
                    <h5>Get Going!</h5>
                    <p>Select date(s) to apply for permission.</p>
                  </div>
                  <div class="t2g-reg-form" id="reg_apply_form" style="display:none;">
                    <h5>Apply for <span id="reg_form_date"></span></h5>
                    <div id="apply_suggest_box" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;margin:0 0 14px;font-size:12px;color:#1e3a8a;">
                      <div id="apply_recorded_summary" style="margin-bottom:6px;"></div>
                      <div id="apply_suggest_text" style="margin-bottom:8px;"></div>
                      <div id="apply_leave_deduct_text" style="margin-bottom:8px;font-weight:600;color:#9a3412;"></div>
                      <button type="button" class="btn btn-default btn-xs" id="apply_suggest_btn">Suggest times for required span</button>
                      <span class="text-muted" style="margin-left:8px;">You can still edit times manually.</span>
                    </div>
                    <div class="form-group">
                      <label>Time in <span class="text-danger">*</span></label>
                      <input type="time" class="form-control" id="apply_time_in" step="60" required>
                    </div>
                    <div class="form-group">
                      <label>Time out <span class="text-danger">*</span></label>
                      <input type="time" class="form-control" id="apply_time_out" step="60" required>
                      <p class="text-muted" style="margin:6px 0 0;font-size:12px;">Minutes are allowed (e.g. 09:15–09:45 = 30 minutes).</p>
                      <p class="text-info" style="margin:4px 0 0;font-size:12px;" id="apply_hours_preview"></p>
                    </div>
                    <div class="form-group">
                      <label>Reason <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="apply_reason" rows="3" placeholder="Reason for regularization"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" id="apply_submit_btn">Submit request</button>
                    <button type="button" class="btn btn-default" id="apply_cancel_btn">Cancel</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="t2g-reg-panel" id="tab_pending">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr><th>Date</th><th>Time in</th><th>Time out</th><th>Reason</th><th>Status</th></tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($pending_requests)) { foreach ($pending_requests as $req) { ?>
                      <tr>
                        <td><?php echo _d($req['additional_day']); ?></td>
                        <td><?php echo html_escape($req['time_in']); ?></td>
                        <td><?php echo html_escape($req['time_out']); ?></td>
                        <td><?php echo html_escape($req['reason']); ?></td>
                        <td><span class="label label-warning">Awaiting approval</span></td>
                      </tr>
                    <?php } } else { ?>
                      <tr><td colspan="5" class="text-muted">No pending requests.</td></tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="t2g-reg-panel" id="tab_history">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr><th>Date</th><th>Time in</th><th>Time out</th><th>Reason</th><th>Status</th><th>Rejection reason</th></tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($history_requests)) { foreach ($history_requests as $req) { ?>
                      <tr>
                        <td><?php echo _d($req['additional_day']); ?></td>
                        <td><?php echo html_escape($req['time_in']); ?></td>
                        <td><?php echo html_escape($req['time_out']); ?></td>
                        <td><?php echo html_escape($req['reason']); ?></td>
                        <td>
                          <?php if ((int) $req['status'] === 1) { ?>
                            <span class="label label-success">Approved</span>
                          <?php } else { ?>
                            <span class="label label-danger">Rejected</span>
                          <?php } ?>
                        </td>
                        <td>
                          <?php if ((int) $req['status'] === 2) {
                            $rej = trim((string) ($req['rejection_comment'] ?? ''));
                            echo $rej !== '' ? '<span class="text-danger">' . html_escape($rej) . '</span>' : '<span class="text-muted">—</span>';
                          } else {
                            echo '<span class="text-muted">—</span>';
                          } ?>
                        </td>
                      </tr>
                    <?php } } else { ?>
                      <tr><td colspan="6" class="text-muted">No history yet.</td></tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function($) {
  'use strict';
  var monthYear = '<?php echo html_escape($month_year ?? date('Y-m')); ?>';
  var calendarData = <?php echo json_encode($calendar['days'] ?? []); ?>;
  var calendarMeta = {
    required_span_minutes: <?php echo (int) ($calendar['required_span_minutes'] ?? 535); ?>,
    half_day_min_span_minutes: <?php echo (int) ($calendar['half_day_min_span_minutes'] ?? 300); ?>
  };
  var dayMap = {};
  var selectedDate = '';
  var gapIndex = 0;
  var calendarLoading = false;
  var staffIdForCal = '<?php echo (int) $staff_id; ?>';

  calendarData.forEach(function(d) { dayMap[d.date] = d; });

  function showRegCalendarLoading() {
    $('#reg_cal_grid').html('<div style="grid-column:1/-1;padding:24px;text-align:center;color:#64748b;font-size:13px;"><i class="fa fa-spinner fa-spin"></i> Loading…</div>');
    $('#reg_gap_count').text('… Gap day(s)');
  }

  function reloadRegCalendar() {
    if (calendarLoading) return;
    calendarLoading = true;
    showRegCalendarLoading();
    $.getJSON(admin_url + 'timesheets/my_attendance_calendar', { month: monthYear, staff_id: staffIdForCal })
      .done(function(res) {
        calendarData = res.days || [];
        if (res.required_span_minutes) calendarMeta.required_span_minutes = parseInt(res.required_span_minutes, 10);
        if (res.half_day_min_span_minutes) calendarMeta.half_day_min_span_minutes = parseInt(res.half_day_min_span_minutes, 10);
        dayMap = {};
        calendarData.forEach(function(d) { dayMap[d.date] = d; });
        selectedDate = '';
        gapIndex = 0;
        renderRegCalendar();
        showApplyForm(null);
      })
      .always(function() { calendarLoading = false; });
  }

  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function parseMonthYear(my) {
    var p = (my || monthYear).split('-');
    return { year: parseInt(p[0], 10), month: parseInt(p[1], 10) - 1 };
  }
  function dateStr(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }
  function monthLabel(y, m) {
    return ['January','February','March','April','May','June','July','August','September','October','November','December'][m] + ' ' + y;
  }

  function gapDays() {
    return calendarData.filter(function(d) {
      return d.can_regularise && d.status !== 'pending' && d.status !== 'future';
    });
  }

  function isGapDay(day) {
    return day && day.can_regularise && day.status !== 'pending' && day.status !== 'future';
  }

  function updateGapCount() {
    var n = gapDays().length;
    $('#reg_gap_count').text(n + ' Gap day(s)');
  }

  function renderRegCalendar() {
    var vm = parseMonthYear(monthYear);
    $('#reg_cal_title').text(monthLabel(vm.year, vm.month));

    var first = new Date(vm.year, vm.month, 1);
    var mondayStart = (first.getDay() + 6) % 7;
    var daysInMonth = new Date(vm.year, vm.month + 1, 0).getDate();
    var daysInPrev = new Date(vm.year, vm.month, 0).getDate();

    var html = '';
    var cell = 0;
    for (var i = mondayStart - 1; i >= 0; i--) {
      html += '<div class="t2g-reg-cal-day other-month">' + (daysInPrev - i) + '</div>';
      cell++;
    }
    for (var d = 1; d <= daysInMonth; d++) {
      var ds = dateStr(vm.year, vm.month, d);
      var day = dayMap[ds];
      var cls = 't2g-reg-cal-day';
      if (isGapDay(day)) cls += ' gap-day';
      if (ds === selectedDate) cls += ' selected';
      html += '<div class="' + cls + '" data-date="' + ds + '">' + d + '</div>';
      cell++;
    }
    var next = 1;
    while (cell % 7 !== 0) {
      html += '<div class="t2g-reg-cal-day other-month">' + next + '</div>';
      next++; cell++;
    }
    $('#reg_cal_grid').html(html);
    updateGapCount();
  }

  function toMins(hhmm) {
    if (!hhmm || hhmm === '—' || hhmm === '-') return null;
    var p = String(hhmm).substring(0, 5).split(':');
    if (p.length < 2) return null;
    var h = parseInt(p[0], 10), m = parseInt(p[1], 10);
    if (isNaN(h) || isNaN(m)) return null;
    return (h * 60) + m;
  }
  function fromMins(mins) {
    mins = Math.max(0, Math.min((23 * 60) + 59, Math.round(mins)));
    return pad(Math.floor(mins / 60)) + ':' + pad(mins % 60);
  }
  function lunchOverlapMins(inM, outM) {
    // Match backend default lunch window used in regularisation hours.
    var ls = 12 * 60, le = (12 * 60) + 30;
    return Math.max(0, Math.min(outM, le) - Math.max(inM, ls));
  }
  function workMinsFromRange(inM, outM) {
    if (inM === null || outM === null || outM <= inM) return 0;
    return Math.max(0, outM - inM - lunchOverlapMins(inM, outM));
  }
  function fmtHm(mins) {
    var h = Math.floor(mins / 60), m = Math.round(mins % 60);
    if (m === 60) { h++; m = 0; }
    return (h > 0 ? h + 'h ' : '') + m + 'm';
  }
  function parseHrsText(val) {
    if (val === null || val === undefined || val === '' || val === '—') return null;
    if (typeof val === 'number') return isNaN(val) ? null : val;
    var n = parseFloat(String(val).replace('h', ''));
    return isNaN(n) ? null : n;
  }
  function spanMinutesLabel(mins) {
    mins = parseInt(mins, 10) || 0;
    var h = Math.floor(mins / 60), m = mins % 60;
    return (h > 0 ? h + 'h ' : '') + m + 'm';
  }
  function suggestTimesForRequiredSpan(day) {
    var requiredMins = parseInt(day.required_span_minutes || calendarMeta.required_span_minutes, 10);
    if (isNaN(requiredMins) || requiredMins <= 0) requiredMins = 535;
    var required = requiredMins / 60;
    var recordedIn = toMins(day.first_in || day.check_in);
    var recordedOut = toMins(day.last_out || day.check_out);
    var shiftIn = toMins(day.shift_start);
    if (shiftIn === null) shiftIn = (9 * 60) + 30;
    var sugIn, sugOut, note;

    function outForIn(inM) {
      // Start with required + typical lunch, then correct using exact overlap.
      var outM = inM + requiredMins + 30;
      outM = inM + requiredMins + lunchOverlapMins(inM, outM);
      if (outM > (23 * 60) + 59) outM = (23 * 60) + 59;
      return outM;
    }
    function inForOut(outM) {
      var inM = outM - requiredMins - 30;
      if (inM < 0) inM = 0;
      inM = outM - requiredMins - lunchOverlapMins(inM, outM);
      if (inM < 0) inM = 0;
      return inM;
    }

    if (recordedIn !== null) {
      sugIn = recordedIn;
      sugOut = outForIn(sugIn);
      note = 'Kept your first punch and calculated time out to complete ' + spanMinutesLabel(requiredMins) + ' span.';
    } else if (recordedOut !== null) {
      sugOut = recordedOut;
      sugIn = inForOut(sugOut);
      note = 'Kept your last punch and calculated time in to complete ' + spanMinutesLabel(requiredMins) + ' span.';
    } else {
      sugIn = shiftIn;
      sugOut = outForIn(sugIn);
      note = 'No punches found — suggested window to complete ' + spanMinutesLabel(requiredMins) + ' span.';
    }

    return {
      required: required,
      required_mins: requiredMins,
      time_in: fromMins(sugIn),
      time_out: fromMins(sugOut),
      note: note,
      recorded_in: recordedIn !== null ? fromMins(recordedIn) : null,
      recorded_out: recordedOut !== null ? fromMins(recordedOut) : null,
      recorded_hours: parseHrsText(day.actual_work_hrs) !== null
        ? parseHrsText(day.actual_work_hrs)
        : (typeof day.hours === 'number' ? day.hours : parseHrsText(day.total_work_hrs))
    };
  }

  function applySuggestion(day, fillTimes) {
    var s = suggestTimesForRequiredSpan(day);
    var recBits = [];
    if (s.recorded_in || s.recorded_out) {
      recBits.push('Recorded: ' + (s.recorded_in || '—') + ' – ' + (s.recorded_out || '—'));
    } else {
      recBits.push('Recorded: no punches');
    }
    if (s.recorded_hours !== null && s.recorded_hours !== undefined) {
      recBits.push(Number(s.recorded_hours).toFixed(2) + 'h');
    }
    $('#apply_recorded_summary').text(recBits.join(' · '));
    $('#apply_suggest_text').text(
      'To complete ' + spanMinutesLabel(s.required_mins || calendarMeta.required_span_minutes) + ' span, suggested: ' + s.time_in + ' – ' + s.time_out + '. ' + s.note
    );
    var leaveDays = parseFloat(day.leave_days_current);
    if (isNaN(leaveDays)) leaveDays = parseFloat(day.leave_days_if_regularised);
    if (isNaN(leaveDays)) leaveDays = 0;
    var leaveNow = leaveDays === 0.5 ? 'Half day (0.5 leave)' : (leaveDays >= 1 ? 'Absent (1 leave)' : 'Present (0 leave)');
    $('#apply_leave_deduct_text').text(
      'Current day: ' + leaveNow + '. On approval, leave balance will be adjusted to match corrected hours (Absent=1, Half day=0.5, Present=0).'
    );
    $('#apply_suggest_btn').data('day', day.date);
    if (fillTimes) {
      $('#apply_time_in').val(s.time_in);
      $('#apply_time_out').val(s.time_out);
      previewApplyHours();
    }
    return s;
  }

  function showApplyForm(day) {
    if (!day) {
      $('#reg_empty_state').show();
      $('#reg_apply_form').hide();
      return;
    }
    $('#reg_empty_state').hide();
    $('#reg_apply_form').show();
    $('#reg_form_date').text(day.date);
    $('#apply_reason').val('');
    applySuggestion(day, true);
  }

  function shiftMonth(delta) {
    var vm = parseMonthYear(monthYear);
    var d = new Date(vm.year, vm.month + delta, 1);
    var nextMy = d.getFullYear() + '-' + pad(d.getMonth() + 1);
    var now = new Date();
    var currentMy = now.getFullYear() + '-' + pad(now.getMonth() + 1);
    var prevDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    var prevMy = prevDate.getFullYear() + '-' + pad(prevDate.getMonth() + 1);
    if (nextMy > currentMy) {
      alert_float('info', 'Cannot open a future month for regularization.');
      return;
    }
    if (nextMy < prevMy) {
      alert_float('info', 'Regularization is allowed only for the current and previous month.');
      return;
    }
    monthYear = nextMy;
    var staffId = '<?php echo (int) $staff_id; ?>';
    window.location.href = admin_url + 'timesheets/attendance_regularization?month=' + encodeURIComponent(monthYear) + '&staff_id=' + staffId;
  }

  // Deferred calendar load — page shell paints first.
  reloadRegCalendar();

  (function loadRegStaffPicker() {
    var $sel = $('#att_reg_staff_pick');
    if (!$sel.length) return;
    var cur = String($sel.val() || '');
    $.getJSON(admin_url + 'timesheets/get_viewable_staff_json').done(function(res) {
      var staff = res.staff || [];
      if (!staff.length) return;
      var meId = '<?php echo (int) get_staff_user_id(); ?>';
      $sel.empty();
      for (var i = 0; i < staff.length; i++) {
        var s = staff[i];
        var id = String(s.staffid);
        var name = $.trim((s.firstname || '') + ' ' + (s.lastname || ''));
        if (id === meId) name += ' (Me)';
        $sel.append($('<option></option>').attr('value', id).text(name));
      }
      if (cur && $sel.find('option[value="' + cur + '"]').length) $sel.val(cur);
    });
  })();

  $('.t2g-reg-tabs button').on('click', function() {
    var tab = $(this).data('tab');
    $('.t2g-reg-tabs button').removeClass('active');
    $(this).addClass('active');
    $('.t2g-reg-panel').removeClass('active');
    $('#tab_' + tab).addClass('active');
  });

  $(document).on('click', '.t2g-reg-cal-day:not(.other-month)', function() {
    selectedDate = $(this).data('date');
    renderRegCalendar();
    var day = dayMap[selectedDate];
    if (isGapDay(day)) {
      showApplyForm(day);
    } else if (day && day.status === 'pending') {
      $('#reg_empty_state').show();
      $('#reg_apply_form').hide();
      alert_float('info', 'A request is already pending for this date.');
    } else if (day && day.within_reg_window === false) {
      showApplyForm(null);
      alert_float('warning', 'Regularization is allowed only for the current and previous month.');
    } else {
      showApplyForm(null);
      alert_float('info', 'This date does not need regularization.');
    }
  });

  $('#reg_quick_add').on('click', function() {
    var gaps = gapDays();
    if (!gaps.length) {
      alert_float('info', 'No gap days this month.');
      return;
    }
    if (gapIndex >= gaps.length) gapIndex = 0;
    var day = gaps[gapIndex];
    gapIndex++;
    selectedDate = day.date;
    renderRegCalendar();
    showApplyForm(day);
  });

  $('#reg_cal_prev').on('click', function() { shiftMonth(-1); });
  $('#reg_cal_next').on('click', function() { shiftMonth(1); });

  $('#apply_cancel_btn').on('click', function() {
    selectedDate = '';
    renderRegCalendar();
    showApplyForm(null);
  });

  function previewApplyHours() {
    var tin = $('#apply_time_in').val();
    var tout = $('#apply_time_out').val();
    var $p = $('#apply_hours_preview');
    if (!tin || !tout) { $p.text(''); return; }
    var inM = toMins(tin), outM = toMins(tout);
    if (inM === null || outM === null || outM <= inM) {
      $p.text('Time out must be after time in.');
      return;
    }
    var lunch = lunchOverlapMins(inM, outM);
    var work = workMinsFromRange(inM, outM);
    var target = calendarMeta.required_span_minutes || 535;
    var day = dayMap[selectedDate];
    if (day && parseInt(day.required_span_minutes, 10) > 0) target = parseInt(day.required_span_minutes, 10);
    var msg = 'Work duration: ' + fmtHm(work) + ' (' + (work / 60).toFixed(2) + 'h)';
    if (lunch > 0) msg += ' after ' + lunch + 'm lunch';
    if (work >= target) msg += ' · meets ' + spanMinutesLabel(target) + ' span target';
    else msg += ' · short by ' + fmtHm(target - work) + ' (need ' + spanMinutesLabel(target) + ' span)';
    $p.text(msg);
  }
  $('#apply_time_in, #apply_time_out').on('change input', previewApplyHours);

  $('#apply_suggest_btn').on('click', function() {
    var day = dayMap[selectedDate];
    if (!day) return;
    applySuggestion(day, true);
  });

  $('#apply_submit_btn').on('click', function() {
    var payload = {
      additional_day: selectedDate,
      time_in: $('#apply_time_in').val(),
      time_out: $('#apply_time_out').val(),
      reason: $('#apply_reason').val(),
      timekeeping_value: ''
    };
    if (typeof csrfData !== 'undefined') payload[csrfData.token_name] = csrfData.hash;
    if (!payload.additional_day || !payload.time_in || !payload.time_out || !payload.reason) {
      alert_float('warning', 'Please complete all fields.');
      return;
    }
    $('#apply_submit_btn').prop('disabled', true);
    $.post(admin_url + 'timesheets/submit_attendance_regularisation', payload).done(function(res) {
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { res = {}; }
      if (res.success) {
        alert_float('success', res.message || 'Request submitted');
        setTimeout(function() { location.reload(); }, 600);
      } else {
        alert_float('danger', res.message || 'Could not submit');
      }
    }).fail(function() {
      alert_float('danger', 'Could not submit request.');
    }).always(function() { $('#apply_submit_btn').prop('disabled', false); });
  });
})(jQuery);
</script>
</body>
</html>
