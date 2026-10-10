<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.sat-cal-wrap { max-width: 960px; }
.sat-cal-toolbar {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:14px; gap:10px; flex-wrap:wrap;
}
.sat-cal-actions { display:flex; gap:8px; flex-wrap:wrap; margin:12px 0 4px; }
.sat-cal-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:8px; }
.sat-cal-dow { text-align:center; font-size:12px; font-weight:700; color:#64748b; padding:8px 0; text-transform:uppercase; }
.sat-cal-day {
  min-height:84px; border:1px solid #e2e8f0; border-radius:10px; padding:8px;
  background:#f8fafc; color:#94a3b8;
}
.sat-cal-day.is-other { opacity:.3; }
.sat-cal-day.is-sat {
  background:#fff; color:#0f172a; border-color:#60a5fa; cursor:pointer;
}
.sat-cal-day.is-sat:hover { box-shadow:0 3px 10px rgba(37,99,235,.14); }
.sat-cal-day.is-selected {
  background:#dbeafe; border-color:#2563eb; color:#1e3a8a;
}
.sat-cal-day .num { font-size:15px; font-weight:700; }
.sat-cal-day .sat-leave-row {
  display:none; margin-top:10px; align-items:center; gap:8px;
  font-size:13px; font-weight:700; color:#1d4ed8;
}
.sat-cal-day.is-sat .sat-leave-row { display:flex; }
.sat-cal-day .sat-leave-cb {
  width:18px; height:18px; margin:0; cursor:pointer; accent-color:#2563eb;
}
.sat-cal-hint { color:#64748b; font-size:13px; margin:0 0 14px; }
.sat-cal-legend {
  display:flex; gap:16px; margin-top:12px; font-size:12px; color:#475569;
  pointer-events:none; user-select:none; flex-wrap:wrap;
}
.sat-cal-legend span { display:inline-flex; align-items:center; gap:6px; }
.sat-cal-swatch {
  display:inline-block; width:14px; height:14px; border-radius:3px;
  border:1px solid #93c5fd; background:#fff;
}
.sat-cal-swatch.selected { background:#dbeafe; border-color:#2563eb; }
.sat-cal-disabled { pointer-events:none; opacity:.55; }
#sat_cal_status { min-height:22px; margin-top:10px; font-size:13px; }
#sat_save_btn[disabled] { opacity:.65; cursor:not-allowed; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
          <h4 class="tw-mt-0"><i class="fa fa-calendar"></i> Assign Saturday Leaves</h4>
          <a href="<?php echo admin_url('holiday/manageHoliday'); ?>" class="btn btn-default">Manage Saturday Leaves</a>
        </div>

        <div class="panel_s">
          <div class="panel-body">
            <p class="sat-cal-hint">
              Select department and employee, pick month/year, tick Saturdays (or <strong>Select all</strong>), then click <strong>Save</strong>. Not auto-saved.
            </p>

            <div class="row">
              <div class="col-md-4">
                <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
              </div>
              <div class="col-md-4">
                <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname', 'staff_identifi')), 'Select Employee'); ?>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="sat_month">Month</label>
                  <select id="sat_month" class="form-control"></select>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="sat_year">Year</label>
                  <select id="sat_year" class="form-control"></select>
                </div>
              </div>
            </div>

            <div id="sat_calendar" class="sat-cal-wrap sat-cal-disabled">
              <div class="sat-cal-toolbar">
                <button type="button" class="btn btn-default" id="sat_prev_month">&larr; Prev</button>
                <strong id="sat_month_label">—</strong>
                <button type="button" class="btn btn-default" id="sat_next_month">Next &rarr;</button>
              </div>

              <div class="sat-cal-actions">
                <button type="button" class="btn btn-info" id="sat_select_all">Select all Saturdays</button>
                <button type="button" class="btn btn-default" id="sat_clear_all">Clear all</button>
                <button type="button" class="btn btn-primary" id="sat_save_btn" disabled>Save Saturday Leaves</button>
              </div>

              <div class="sat-cal-grid" id="sat_dow"></div>
              <div class="sat-cal-grid" id="sat_days"></div>
              <div class="sat-cal-legend">
                <span><span class="sat-cal-swatch" aria-hidden="true"></span> Saturday available</span>
                <span><span class="sat-cal-swatch selected" aria-hidden="true"></span> Selected for leave</span>
              </div>
              <div id="sat_cal_status"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function ($) {
  'use strict';

  var current = new Date();
  current.setDate(1);
  var saved = {};
  var selected = {};
  var dirty = false;
  var csrf = (typeof csrfData !== 'undefined') ? csrfData : { token_name: 'csrf_token_name', hash: '' };
  var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

  function ymd(d) {
    var m = d.getMonth() + 1;
    var day = d.getDate();
    return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
  }
  function monthKey(d) {
    var m = d.getMonth() + 1;
    return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m;
  }
  function monthLabel(d) {
    return monthNames[d.getMonth()] + ' ' + d.getFullYear();
  }
  function setStatus(msg, ok) {
    $('#sat_cal_status').html(msg ? ('<span class="text-' + (ok ? 'success' : (ok === false ? 'danger' : 'muted')) + '">' + msg + '</span>') : '');
  }
  function canUseCalendar() {
    return $('#departments').val() && $('#staffid').val();
  }
  function syncMonthYearSelects() {
    $('#sat_month').val(String(current.getMonth() + 1));
    $('#sat_year').val(String(current.getFullYear()));
  }
  function initMonthYearSelects() {
    var mHtml = '';
    for (var i = 0; i < 12; i++) {
      mHtml += '<option value="' + (i + 1) + '">' + monthNames[i] + '</option>';
    }
    $('#sat_month').html(mHtml);
    var yNow = new Date().getFullYear();
    var yHtml = '';
    for (var y = yNow - 2; y <= yNow + 3; y++) {
      yHtml += '<option value="' + y + '">' + y + '</option>';
    }
    $('#sat_year').html(yHtml);
    syncMonthYearSelects();
  }
  function updateCalendarEnabled() {
    $('#sat_calendar').toggleClass('sat-cal-disabled', !canUseCalendar());
    updateSaveButton();
  }
  function updateSaveButton() {
    $('#sat_save_btn').prop('disabled', !(canUseCalendar() && dirty));
  }
  function markDirty(isDirty) {
    dirty = !!isDirty;
    updateSaveButton();
    if (dirty) setStatus('Changes not saved yet — click Save Saturday Leaves', null);
  }
  function refreshStaff(department) {
    $.post(admin_url + 'holiday/get_staff_department_json', { department: department }, function (response) {
      var staff = '<option value=""></option>';
      (response || []).forEach(function (el) {
        staff += '<option value="' + el.staffid + '">' + el.firstname + ' ' + el.lastname + ' ' + (el.staff_identifi || '') + '</option>';
      });
      $('#staffid').html(staff);
      $('.selectpicker').selectpicker('refresh');
      updateCalendarEnabled();
      loadAssignedAndRender();
    }, 'json');
  }
  function loadAssignedAndRender() {
    renderCalendarSkeleton();
    syncMonthYearSelects();
    if (!canUseCalendar()) {
      saved = {}; selected = {}; markDirty(false); paintSelected();
      setStatus('Select department and employee first', false);
      return;
    }
    var payload = { staffid: $('#staffid').val(), month: monthKey(current) };
    payload[csrf.token_name] = csrf.hash;
    $.post(admin_url + 'holiday/get_calendar_saturdays', payload, function (res) {
      saved = {}; selected = {};
      if (res && res.ok && res.dates) {
        res.dates.forEach(function (d) { saved[d] = true; selected[d] = true; });
      }
      markDirty(false); paintSelected(); setStatus('', true);
    }, 'json').fail(function () { setStatus('Failed to load assigned Saturdays', false); });
  }
  function renderCalendarSkeleton() {
    $('#sat_month_label').text(monthLabel(current));
    var dow = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var dowHtml = '';
    dow.forEach(function (d) { dowHtml += '<div class="sat-cal-dow">' + d + '</div>'; });
    $('#sat_dow').html(dowHtml);
    var year = current.getFullYear(), month = current.getMonth();
    var first = new Date(year, month, 1);
    var start = new Date(first);
    start.setDate(first.getDate() - first.getDay());
    var html = '';
    for (var i = 0; i < 42; i++) {
      var d = new Date(start);
      d.setDate(start.getDate() + i);
      var isOther = d.getMonth() !== month;
      var isSat = d.getDay() === 6;
      var dateStr = ymd(d);
      var cls = 'sat-cal-day' + (isOther ? ' is-other' : '') + ((isSat && !isOther) ? ' is-sat' : '');
      html += '<div class="' + cls + '" data-date="' + dateStr + '"><div class="num">' + d.getDate() + '</div>';
      if (isSat && !isOther) {
        html += '<label class="sat-leave-row"><input type="checkbox" class="sat-leave-cb" data-date="' + dateStr + '"><span>Leave</span></label>';
      }
      html += '</div>';
    }
    $('#sat_days').html(html);
    paintSelected();
  }
  function paintSelected() {
    $('#sat_days .sat-cal-day.is-sat').each(function () {
      var date = String($(this).data('date'));
      var on = !!selected[date];
      $(this).toggleClass('is-selected', on);
      $(this).find('.sat-leave-cb').prop('checked', on);
    });
  }
  function setSelected(dateStr, on) {
    if (on) selected[dateStr] = true; else delete selected[dateStr];
    var changed = false;
    var allDates = {};
    Object.keys(saved).forEach(function (d) { allDates[d] = true; });
    Object.keys(selected).forEach(function (d) { allDates[d] = true; });
    Object.keys(allDates).forEach(function (d) { if (!!saved[d] !== !!selected[d]) changed = true; });
    markDirty(changed);
    paintSelected();
  }
  function getMonthSaturdays() {
    var dates = [];
    $('#sat_days .sat-cal-day.is-sat').each(function () { dates.push(String($(this).data('date'))); });
    return dates;
  }
  function saveLeaves() {
    if (!canUseCalendar() || !dirty) return;
    var payload = {
      staffid: $('#staffid').val(),
      department_id: $('#departments').val(),
      month: monthKey(current),
      dates: Object.keys(selected)
    };
    payload[csrf.token_name] = csrf.hash;
    $('#sat_save_btn').prop('disabled', true);
    setStatus('Saving…', true);
    $.post(admin_url + 'holiday/save_saturday_leaves', payload, function (res) {
      if (!res || !res.ok) {
        updateSaveButton();
        setStatus((res && res.error) ? res.error : 'Save failed', false);
        return;
      }
      saved = {}; selected = {};
      (res.dates || []).forEach(function (d) { saved[d] = true; selected[d] = true; });
      markDirty(false); paintSelected();
      setStatus(res.message || 'Saved', true);
    }, 'json').fail(function (xhr) {
      updateSaveButton();
      if (xhr && xhr.status === 419) {
        setStatus('Page expired — refreshing…', false);
        setTimeout(function () { window.location.reload(); }, 800);
        return;
      }
      setStatus('Save failed', false);
    });
  }
  function applyMonthYearFromSelects() {
    var m = parseInt($('#sat_month').val(), 10) - 1;
    var y = parseInt($('#sat_year').val(), 10);
    if (isNaN(m) || isNaN(y)) return;
    if (dirty && !confirm('You have unsaved changes. Change month/year anyway?')) {
      syncMonthYearSelects();
      return;
    }
    current = new Date(y, m, 1);
    loadAssignedAndRender();
  }

  $('#departments').on('change', function () { refreshStaff($(this).val()); });
  $('#staffid').on('change', function () { updateCalendarEnabled(); loadAssignedAndRender(); });
  $('#sat_month, #sat_year').on('change', applyMonthYearFromSelects);
  $('#sat_prev_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) return;
    current.setMonth(current.getMonth() - 1); syncMonthYearSelects(); loadAssignedAndRender();
  });
  $('#sat_next_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) return;
    current.setMonth(current.getMonth() + 1); syncMonthYearSelects(); loadAssignedAndRender();
  });
  $('#sat_select_all').on('click', function () {
    getMonthSaturdays().forEach(function (d) { selected[d] = true; });
    markDirty(true); paintSelected();
    setStatus('All Saturdays selected — click Save to apply', null);
  });
  $('#sat_clear_all').on('click', function () {
    selected = {};
    markDirty(Object.keys(saved).length > 0); paintSelected();
    setStatus('All Saturdays cleared — click Save to apply', null);
  });
  $('#sat_save_btn').on('click', saveLeaves);
  $(document).on('click', '#sat_days .sat-cal-day.is-sat', function (e) {
    if ($(e.target).is('input.sat-leave-cb, label, span')) return;
    var date = String($(this).data('date'));
    setSelected(date, !selected[date]);
  });
  $(document).on('change', '#sat_days .sat-leave-cb', function (e) {
    e.stopPropagation();
    setSelected(String($(this).data('date')), $(this).is(':checked'));
  });

  initMonthYearSelects();
  renderCalendarSkeleton();
  updateCalendarEnabled();
})(jQuery);
</script>
</body>
</html>
