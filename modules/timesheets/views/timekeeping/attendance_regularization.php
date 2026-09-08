<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
  .t2g-reg .panel-body { padding: 18px 20px; }
  .t2g-reg-tabs { display: flex; justify-content: center; gap: 0; margin-bottom: 20px; }
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
                    <div class="form-group">
                      <label>Time in <span class="text-danger">*</span></label>
                      <input type="time" class="form-control" id="apply_time_in" required>
                    </div>
                    <div class="form-group">
                      <label>Time out <span class="text-danger">*</span></label>
                      <input type="time" class="form-control" id="apply_time_out" required>
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
  var dayMap = {};
  var selectedDate = '';
  var gapIndex = 0;

  calendarData.forEach(function(d) { dayMap[d.date] = d; });

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

  function showApplyForm(day) {
    if (!day) {
      $('#reg_empty_state').show();
      $('#reg_apply_form').hide();
      return;
    }
    $('#reg_empty_state').hide();
    $('#reg_apply_form').show();
    $('#reg_form_date').text(day.date);
    $('#apply_time_in').val(day.shift_start || '09:30');
    $('#apply_time_out').val(day.shift_end || '18:30');
    $('#apply_reason').val('');
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

  renderRegCalendar();

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
