<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.wfh-cal-wrap { max-width: 960px; }
.wfh-cal-toolbar {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:14px; gap:10px; flex-wrap:wrap;
}
.wfh-cal-pickers { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.wfh-cal-actions { display:flex; gap:8px; flex-wrap:wrap; margin:12px 0 4px; align-items:center; }
.wfh-cal-presets {
  display:flex; gap:16px; flex-wrap:wrap; align-items:center;
  padding:8px 12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;
  margin:0 0 10px;
}
.wfh-cal-presets label {
  display:inline-flex; align-items:center; gap:8px; margin:0;
  font-size:13px; font-weight:600; color:#166534; cursor:pointer; user-select:none;
}
.wfh-cal-presets input[type="checkbox"] {
  width:17px; height:17px; margin:0; cursor:pointer; accent-color:#16a34a;
}
.wfh-cal-presets-hint { font-size:12px; color:#64748b; margin-left:4px; }
.wfh-cal-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:8px; }
.wfh-cal-dow { text-align:center; font-size:12px; font-weight:700; color:#64748b; padding:8px 0; text-transform:uppercase; }
.wfh-cal-day {
  min-height:84px; border:1px solid #e2e8f0; border-radius:10px; padding:8px;
  background:#f8fafc; color:#94a3b8;
}
.wfh-cal-day.is-other { opacity:.3; }
.wfh-cal-day.is-sunday { background:#fef2f2; color:#cbd5e1; cursor:not-allowed; }
.wfh-cal-day.is-workday {
  background:#fff; color:#0f172a; border-color:#86efac; cursor:pointer;
}
.wfh-cal-day.is-workday:hover { box-shadow:0 3px 10px rgba(22,163,74,.14); }
.wfh-cal-day.is-selected {
  background:#dcfce7; border-color:#16a34a; color:#14532d;
}
.wfh-cal-day.is-dirty:not(.is-selected) { border-style:dashed; }
.wfh-cal-day .num { font-size:15px; font-weight:700; }
.wfh-cal-day .wfh-row {
  display:none; margin-top:10px; align-items:center; gap:8px;
  font-size:13px; font-weight:700; color:#15803d;
}
.wfh-cal-day.is-workday .wfh-row { display:flex; }
.wfh-cal-day .wfh-cb {
  width:18px; height:18px; margin:0; cursor:pointer; accent-color:#16a34a;
}
.wfh-cal-hint { color:#64748b; font-size:13px; margin:0 0 14px; }
.wfh-cal-legend {
  display:flex; gap:16px; margin-top:12px; font-size:12px; color:#475569;
  pointer-events:none; user-select:none; flex-wrap:wrap;
}
.wfh-cal-legend span { display:inline-flex; align-items:center; gap:6px; }
.wfh-cal-swatch {
  display:inline-block; width:14px; height:14px; border-radius:3px;
  border:1px solid #86efac; background:#fff;
}
.wfh-cal-swatch.selected { background:#dcfce7; border-color:#16a34a; }
.wfh-cal-disabled { pointer-events:none; opacity:.55; }
#wfh_cal_status { min-height:22px; margin-top:10px; font-size:13px; }
#wfh_save_btn[disabled] { opacity:.65; cursor:not-allowed; }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
          <h4 class="tw-mt-0"><i class="fa fa-home"></i> Manage WFH Allotment</h4>
        </div>

                <div class="panel_s">
          <div class="panel-body">
            <p class="wfh-cal-hint">
              <strong>Assign Work From Home days for your team.</strong>
              Choose department + one or more employees → pick month/year → tick working days (Mon–Sat; Sunday is weekly off) → click <strong>Save</strong>.
              On allotted WFH days, staff use <strong>Workroom check-in/out</strong>; 9 hours (8h 55m) counts as Present on their calendar.
              When multiple employees are selected, the same WFH days apply to all of them.
            </p>

                        <div class="row">
              <div class="col-md-4">
                        <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
              </div>
              <div class="col-md-4">
                <?php echo render_select('staffid[]', $staffs, array('staffid', array('firstname', 'lastname', 'staff_identifi')), 'Select Employee(s)', '', array('multiple' => true, 'data-live-search' => true, 'data-actions-box' => true), [], '', '', 'selectpicker', false); ?>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="wfh_month">Month</label>
                  <select id="wfh_month" class="form-control"></select>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="wfh_year">Year</label>
                  <select id="wfh_year" class="form-control"></select>
                </div>
              </div>
            </div>

            <div id="wfh_calendar" class="wfh-cal-wrap wfh-cal-disabled">
              <div class="wfh-cal-toolbar">
                <button type="button" class="btn btn-default" id="wfh_prev_month">&larr; Prev</button>
                <div class="wfh-cal-pickers">
                  <strong id="wfh_month_label">—</strong>
                </div>
                <button type="button" class="btn btn-default" id="wfh_next_month">Next &rarr;</button>
              </div>

              <div class="wfh-cal-presets">
                <span class="wfh-cal-presets-hint">Quick select:</span>
                <label>
                  <input type="checkbox" id="wfh_preset_weekdays" aria-label="All Mon-Fri">
                  All Mon–Fri this month
                </label>
              </div>

              <div class="wfh-cal-actions">
                <button type="button" class="btn btn-success" id="wfh_select_all">Select all workdays</button>
                <button type="button" class="btn btn-default" id="wfh_clear_all">Clear all</button>
                <button type="button" class="btn btn-primary" id="wfh_save_btn" disabled>Save WFH Days</button>
              </div>

              <div class="wfh-cal-grid" id="wfh_dow"></div>
              <div class="wfh-cal-grid" id="wfh_days"></div>
              <div class="wfh-cal-legend">
                <span><span class="wfh-cal-swatch" aria-hidden="true"></span> Workday available</span>
                <span><span class="wfh-cal-swatch selected" aria-hidden="true"></span> WFH assigned</span>
              </div>
              <div id="wfh_cal_status"></div>
            </div>

            <hr>
            <h5>Assigned WFH — <span id="wfh_table_month_label">this month</span></h5>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>S.No.</th>
                  <th>Month</th>
                  <th>Department</th>
                  <th>Staff</th>
                  <th>WFH Date</th>
                </tr>
              </thead>
              <tbody id="wfh_data">
                <tr><td colspan="5" class="text-center text-muted">Select employee(s) to view</td></tr>
              </tbody>
            </table>
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

  function isWorkday(d) {
    return d.getDay() !== 0;
  }

  function setStatus(msg, ok) {
    $('#wfh_cal_status').html(msg ? ('<span class="text-' + (ok ? 'success' : (ok === false ? 'danger' : 'muted')) + '">' + msg + '</span>') : '');
  }

  var $staffSelect = $('select[name="staffid[]"]');

  function getSelectedStaffIds() {
    var v = $staffSelect.val();
    if (!v) return [];
    return Array.isArray(v) ? v : [v];
  }

  function canUseCalendar() {
    return $('#departments').val() && getSelectedStaffIds().length > 0;
  }

  function staffSelectionLabel() {
    var n = getSelectedStaffIds().length;
    if (n === 0) return '';
    if (n === 1) return '1 employee selected';
    return n + ' employees selected — same WFH days will apply to all';
  }

  function syncMonthYearSelects() {
    $('#wfh_month').val(String(current.getMonth() + 1));
    $('#wfh_year').val(String(current.getFullYear()));
  }

  function initMonthYearSelects() {
    var mHtml = '';
    for (var i = 0; i < 12; i++) {
      mHtml += '<option value="' + (i + 1) + '">' + monthNames[i] + '</option>';
    }
    $('#wfh_month').html(mHtml);
    var yNow = new Date().getFullYear();
    var yHtml = '';
    for (var y = yNow - 2; y <= yNow + 3; y++) {
      yHtml += '<option value="' + y + '">' + y + '</option>';
    }
    $('#wfh_year').html(yHtml);
    syncMonthYearSelects();
  }

  function updateCalendarEnabled() {
    if (canUseCalendar()) {
      $('#wfh_calendar').removeClass('wfh-cal-disabled');
    } else {
      $('#wfh_calendar').addClass('wfh-cal-disabled');
    }
    updateSaveButton();
  }

  function updateSaveButton() {
    $('#wfh_save_btn').prop('disabled', !(canUseCalendar() && dirty));
  }

  function markDirty(isDirty) {
    dirty = !!isDirty;
    updateSaveButton();
    if (dirty) {
      setStatus('Changes not saved yet — click Save WFH Days', null);
    }
  }

  function refreshStaff(department) {
    $.post(admin_url + 'holiday/get_staff_department_json', { department: department }, function (response) {
      var staff = '';
      (response || []).forEach(function (el) {
        staff += '<option value="' + el.staffid + '">' + el.firstname + ' ' + el.lastname + ' ' + (el.staff_identifi || '') + '</option>';
      });
      $staffSelect.html(staff);
      $('.selectpicker').selectpicker('refresh');
      updateCalendarEnabled();
      loadAssignedAndRender();
    }, 'json');
  }

  function refreshTable() {
    $('#wfh_table_month_label').text(monthLabel(current));
    if (!canUseCalendar()) {
      $('#wfh_data').html('<tr><td colspan="5" class="text-center text-muted">Select employee(s) to view</td></tr>');
      return;
    }
    var payload = {
      department: $('#departments').val(),
      staffids: getSelectedStaffIds(),
      month: current.getMonth() + 1,
      year: current.getFullYear()
    };
    payload[csrf.token_name] = csrf.hash;
    $.post(admin_url + 'holiday/getWfhStaffData', payload, function (res) {
      $('#wfh_data').empty();
      if (!res || !res.length) {
        $('#wfh_data').html('<tr><td colspan="5" class="text-center text-muted">No WFH days in ' + monthLabel(current) + '</td></tr>');
        return;
      }
      var j = 1;
      for (var i = 0; i < res.length; i++) {
        $('#wfh_data').append(
          '<tr><td>' + (j++) + '</td><td>' + monthLabel(current) +
          '</td><td>' + (res[i].department_name || '') +
          '</td><td>' + (res[i].staff_firstname || '') + ' ' + (res[i].staff_lastname || '') +
          '</td><td>' + (res[i].wfh_date || '') + '</td></tr>'
        );
      }
    }, 'json');
  }

  function loadAssignedAndRender() {
    renderCalendarSkeleton();
    syncMonthYearSelects();
    if (!canUseCalendar()) {
      saved = {};
      selected = {};
      markDirty(false);
      paintSelected();
      refreshTable();
      setStatus('Select department and at least one employee', false);
      return;
    }
    var payload = {
      staffids: getSelectedStaffIds(),
      month: monthKey(current)
    };
    payload[csrf.token_name] = csrf.hash;
    $.post(admin_url + 'holiday/get_calendar_wfh_days', payload, function (res) {
      saved = {};
      selected = {};
      if (res && res.ok && res.dates) {
        res.dates.forEach(function (d) {
          saved[d] = true;
          selected[d] = true;
        });
      }
      markDirty(false);
      paintSelected();
      refreshTable();
      var hint = staffSelectionLabel();
      setStatus(hint, hint ? true : '');
    }, 'json').fail(function () {
      setStatus('Failed to load assigned WFH days', false);
    });
  }

  function getMonthWorkdays() {
    var dates = [];
    $('#wfh_days .wfh-cal-day.is-workday').each(function () {
      dates.push(String($(this).data('date')));
    });
    return dates;
  }

  function getMonthWeekdays() {
    var dates = [];
    $('#wfh_days .wfh-cal-day.is-workday').each(function () {
      var dateStr = String($(this).data('date'));
      var parts = dateStr.split('-');
      var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
      if (d.getDay() >= 1 && d.getDay() <= 5) {
        dates.push(dateStr);
      }
    });
    return dates;
  }

  function renderCalendarSkeleton() {
    $('#wfh_month_label').text(monthLabel(current));
    var dow = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var dowHtml = '';
    dow.forEach(function (d) { dowHtml += '<div class="wfh-cal-dow">' + d + '</div>'; });
    $('#wfh_dow').html(dowHtml);

    var year = current.getFullYear();
    var month = current.getMonth();
    var first = new Date(year, month, 1);
    var start = new Date(first);
    start.setDate(first.getDate() - first.getDay());

    var html = '';
    for (var i = 0; i < 42; i++) {
      var d = new Date(start);
      d.setDate(start.getDate() + i);
      var isOther = d.getMonth() !== month;
      var isSunday = d.getDay() === 0;
      var isWork = !isOther && !isSunday;
      var dateStr = ymd(d);
      var cls = 'wfh-cal-day';
      if (isOther) cls += ' is-other';
      if (isSunday && !isOther) cls += ' is-sunday';
      if (isWork) cls += ' is-workday';
      html += '<div class="' + cls + '" data-date="' + dateStr + '">';
      html += '<div class="num">' + d.getDate() + '</div>';
      if (isWork) {
        html += '<label class="wfh-row">';
        html += '<input type="checkbox" class="wfh-cb" data-date="' + dateStr + '">';
        html += '<span>WFH</span></label>';
      }
      html += '</div>';
    }
    $('#wfh_days').html(html);
    paintSelected();
  }

  function paintSelected() {
    $('#wfh_days .wfh-cal-day.is-workday').each(function () {
      var date = String($(this).data('date'));
      var on = !!selected[date];
      var was = !!saved[date];
      $(this).toggleClass('is-selected', on);
      $(this).toggleClass('is-dirty', on !== was);
      $(this).find('.wfh-cb').prop('checked', on);
    });
    syncPresetCheckboxes();
  }

  function recalcDirty() {
    var changed = false;
    var allDates = {};
    Object.keys(saved).forEach(function (d) { allDates[d] = true; });
    Object.keys(selected).forEach(function (d) { allDates[d] = true; });
    Object.keys(allDates).forEach(function (d) {
      if (!!saved[d] !== !!selected[d]) changed = true;
    });
    markDirty(changed);
  }

  function setSelected(dateStr, on) {
    if (on) selected[dateStr] = true;
    else delete selected[dateStr];
    recalcDirty();
    paintSelected();
  }

  function syncPresetCheckboxes() {
    var wd = getMonthWeekdays();
    $('#wfh_preset_weekdays').prop('checked', wd.length > 0 && wd.every(function (d) { return !!selected[d]; }));
  }

  function saveLeaves() {
    if (!canUseCalendar()) {
      setStatus('Select department and at least one employee', false);
      return;
    }
    if (!dirty) {
      setStatus('No changes to save', true);
      return;
    }
    var payload = {
      staffids: getSelectedStaffIds(),
      department_id: $('#departments').val(),
      month: monthKey(current),
      dates: Object.keys(selected)
    };
    payload[csrf.token_name] = csrf.hash;
    $('#wfh_save_btn').prop('disabled', true);
    setStatus('Saving…', true);
    $.post(admin_url + 'holiday/save_wfh_days', payload, function (res) {
      if (!res || !res.ok) {
        updateSaveButton();
        setStatus((res && res.error) ? res.error : 'Save failed', false);
        return;
      }
      saved = {};
      selected = {};
      (res.dates || []).forEach(function (d) {
        saved[d] = true;
        selected[d] = true;
      });
      markDirty(false);
      paintSelected();
      refreshTable();
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
    var m = parseInt($('#wfh_month').val(), 10) - 1;
    var y = parseInt($('#wfh_year').val(), 10);
    if (isNaN(m) || isNaN(y)) return;
    if (dirty && !confirm('You have unsaved changes. Change month/year anyway?')) {
      syncMonthYearSelects();
      return;
    }
    current = new Date(y, m, 1);
    loadAssignedAndRender();
  }

  $('#departments').on('change', function () { refreshStaff($(this).val()); });
  $staffSelect.on('changed.bs.select', function () {
    updateCalendarEnabled();
    loadAssignedAndRender();
  });
  $('#wfh_month, #wfh_year').on('change', applyMonthYearFromSelects);
  $('#wfh_prev_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) return;
    current.setMonth(current.getMonth() - 1);
    syncMonthYearSelects();
    loadAssignedAndRender();
  });
  $('#wfh_next_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) return;
    current.setMonth(current.getMonth() + 1);
    syncMonthYearSelects();
    loadAssignedAndRender();
  });
  $('#wfh_select_all').on('click', function () {
    getMonthWorkdays().forEach(function (d) { selected[d] = true; });
    markDirty(true);
    paintSelected();
    setStatus('All workdays selected — click Save to apply', null);
  });
  $('#wfh_clear_all').on('click', function () {
    selected = {};
    markDirty(Object.keys(saved).length > 0);
    paintSelected();
    $('#wfh_preset_weekdays').prop('checked', false);
    setStatus('All cleared — click Save to apply', null);
  });
  $('#wfh_preset_weekdays').on('change', function () {
    var on = $(this).is(':checked');
    getMonthWeekdays().forEach(function (d) {
      if (on) selected[d] = true;
      else delete selected[d];
    });
    recalcDirty();
    paintSelected();
    setStatus(on ? 'Mon–Fri selected — click Save to apply' : 'Mon–Fri cleared — click Save to apply', null);
  });
  $('#wfh_save_btn').on('click', saveLeaves);

  $(document).on('click', '#wfh_days .wfh-cal-day.is-workday', function (e) {
    if ($(e.target).is('input.wfh-cb, label, span')) return;
    var date = String($(this).data('date'));
    setSelected(date, !selected[date]);
  });
  $(document).on('change', '#wfh_days .wfh-cb', function (e) {
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
