<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.sat-cal-wrap { max-width: 960px; }
.sat-cal-toolbar {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:14px; gap:10px; flex-wrap:wrap;
}
.sat-cal-pickers { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.sat-cal-pickers select {
  min-width:120px; height:34px; border:1px solid #cbd5e1; border-radius:6px; padding:4px 8px;
}
.sat-cal-actions { display:flex; gap:8px; flex-wrap:wrap; margin:12px 0 4px; align-items:center; }
.sat-cal-presets {
  display:flex; gap:16px; flex-wrap:wrap; align-items:center;
  padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;
  margin:0 0 10px;
}
.sat-cal-presets label {
  display:inline-flex; align-items:center; gap:8px; margin:0;
  font-size:13px; font-weight:600; color:#334155; cursor:pointer; user-select:none;
}
.sat-cal-presets input[type="checkbox"] {
  width:17px; height:17px; margin:0; cursor:pointer; accent-color:#2563eb;
}
.sat-cal-presets-hint { font-size:12px; color:#64748b; margin-left:4px; }
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
.sat-cal-day.is-dirty:not(.is-selected) { border-style:dashed; }
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
          <h4 class="tw-mt-0"><i class="fa fa-calendar"></i> Manage Saturday Leaves</h4>
        </div>
			
                <div class="panel_s">
          <div class="panel-body">
            <p class="sat-cal-hint">
              <strong>One place to assign and view Saturday leaves.</strong>
              Choose department + one or more employees → pick month/year → tick Saturdays (or use quick options below) → click <strong>Save</strong>.
              When multiple employees are selected, the same Saturdays are applied to all of them.
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
                <div class="sat-cal-pickers">
                  <strong id="sat_month_label">—</strong>
                </div>
                <button type="button" class="btn btn-default" id="sat_next_month">Next &rarr;</button>
                </div>
				
              <div class="sat-cal-presets">
                <span class="sat-cal-presets-hint">Quick select:</span>
                <label>
                  <input type="checkbox" id="sat_preset_13" aria-label="1st and 3rd Saturday">
                  1st &amp; 3rd Saturday
                </label>
                <label>
                  <input type="checkbox" id="sat_preset_24" aria-label="2nd and 4th Saturday">
                  2nd &amp; 4th Saturday
                </label>
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
                        
            <hr>
            <h5>Assigned leaves — <span id="sat_table_month_label">this month</span></h5>
            <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                  <th>S.No.</th>
                  <th>Month</th>
                  <th>Department</th>
                  <th>Staff</th>
                  <th>Saturday Leave</th>
                            </tr>
                            </thead>
                            <tbody id="sat_data">
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
  var saved = {};      // dates saved in DB
  var selected = {};   // draft checkbox state
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

  var $staffSelect = $('select[name="staffid[]"]');

  function getSelectedStaffIds() {
    var v = $staffSelect.val();
    if (!v) {
      return [];
    }
    return Array.isArray(v) ? v : [v];
  }

  function canUseCalendar() {
    return $('#departments').val() && getSelectedStaffIds().length > 0;
  }

  function staffSelectionLabel() {
    var n = getSelectedStaffIds().length;
    if (n === 0) {
      return '';
    }
    if (n === 1) {
      return '1 employee selected';
    }
    return n + ' employees selected — same Saturdays will apply to all';
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
    if (canUseCalendar()) {
      $('#sat_calendar').removeClass('sat-cal-disabled');
    } else {
      $('#sat_calendar').addClass('sat-cal-disabled');
    }
    updateSaveButton();
  }

  function updateSaveButton() {
    $('#sat_save_btn').prop('disabled', !(canUseCalendar() && dirty));
  }

  function markDirty(isDirty) {
    dirty = !!isDirty;
    updateSaveButton();
    if (dirty) {
      setStatus('Changes not saved yet — click Save Saturday Leaves', null);
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
    $('#sat_table_month_label').text(monthLabel(current));
    if (!canUseCalendar()) {
      $('#sat_data').html('<tr><td colspan="5" class="text-center text-muted">Select employee(s) to view</td></tr>');
      return;
    }
    var payload = {
      department: $('#departments').val(),
      staffids: getSelectedStaffIds(),
      month: current.getMonth() + 1,
      year: current.getFullYear()
    };
    payload[csrf.token_name] = csrf.hash;
    $.post(admin_url + 'holiday/getHolidayStaffData', payload, function (res) {
      $('#sat_data').empty();
      if (!res || !res.length) {
        $('#sat_data').html('<tr><td colspan="5" class="text-center text-muted">No Saturday leaves in ' + monthLabel(current) + '</td></tr>');
        return;
      }
      var j = 1;
      for (var i = 0; i < res.length; i++) {
        $('#sat_data').append(
          '<tr><td>' + (j++) + '</td><td>' + monthLabel(current) +
          '</td><td>' + (res[i].department_name || '') +
          '</td><td>' + (res[i].staff_firstname || '') + ' ' + (res[i].staff_lastname || '') +
          '</td><td>' + (res[i].saturday_date || '') + '</td></tr>'
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
    $.post(admin_url + 'holiday/get_calendar_saturdays', payload, function (res) {
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
      setStatus('Failed to load assigned Saturdays', false);
    });
  }

  function renderCalendarSkeleton() {
    $('#sat_month_label').text(monthLabel(current));

    var dow = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var dowHtml = '';
    dow.forEach(function (d) { dowHtml += '<div class="sat-cal-dow">' + d + '</div>'; });
    $('#sat_dow').html(dowHtml);

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
      var isSat = d.getDay() === 6;
      var dateStr = ymd(d);
      var cls = 'sat-cal-day';
      if (isOther) cls += ' is-other';
      if (isSat && !isOther) cls += ' is-sat';
      html += '<div class="' + cls + '" data-date="' + dateStr + '">';
      html += '<div class="num">' + d.getDate() + '</div>';
      if (isSat && !isOther) {
        html += '<label class="sat-leave-row">';
        html += '<input type="checkbox" class="sat-leave-cb" data-date="' + dateStr + '">';
        html += '<span>Leave</span></label>';
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
      var was = !!saved[date];
      $(this).toggleClass('is-selected', on);
      $(this).toggleClass('is-dirty', on !== was);
      $(this).find('.sat-leave-cb').prop('checked', on);
    });
    syncPresetCheckboxes();
  }

  function setSelected(dateStr, on) {
    if (on) {
      selected[dateStr] = true;
    } else {
      delete selected[dateStr];
    }
    var changed = false;
    var allDates = {};
    Object.keys(saved).forEach(function (d) { allDates[d] = true; });
    Object.keys(selected).forEach(function (d) { allDates[d] = true; });
    Object.keys(allDates).forEach(function (d) {
      if (!!saved[d] !== !!selected[d]) {
        changed = true;
      }
    });
    markDirty(changed);
    paintSelected();
  }

  function getMonthSaturdays() {
    var dates = [];
    $('#sat_days .sat-cal-day.is-sat').each(function () {
      dates.push(String($(this).data('date')));
    });
    return dates;
  }

  function getSaturdaysByOrdinal(ordinals) {
    var all = getMonthSaturdays();
    var out = [];
    ordinals.forEach(function (n) {
      if (all[n - 1]) {
        out.push(all[n - 1]);
      }
    });
    return out;
  }

  function recalcDirty() {
    var changed = false;
    var allDates = {};
    Object.keys(saved).forEach(function (d) { allDates[d] = true; });
    Object.keys(selected).forEach(function (d) { allDates[d] = true; });
    Object.keys(allDates).forEach(function (d) {
      if (!!saved[d] !== !!selected[d]) {
        changed = true;
      }
    });
    markDirty(changed);
  }

  function syncPresetCheckboxes() {
    var sat13 = getSaturdaysByOrdinal([1, 3]);
    var sat24 = getSaturdaysByOrdinal([2, 4]);
    $('#sat_preset_13').prop('checked', sat13.length > 0 && sat13.every(function (d) { return !!selected[d]; }));
    $('#sat_preset_24').prop('checked', sat24.length > 0 && sat24.every(function (d) { return !!selected[d]; }));
  }

  function applyOrdinalPreset(ordinals, on) {
    getSaturdaysByOrdinal(ordinals).forEach(function (d) {
      if (on) {
        selected[d] = true;
      } else {
        delete selected[d];
      }
    });
    recalcDirty();
    paintSelected();
    syncPresetCheckboxes();
  }

  function ordinalPresetLabel(ordinals) {
    var dates = getSaturdaysByOrdinal(ordinals);
    if (!dates.length) {
      return '';
    }
    return dates.map(function (d) {
      return d.split('-')[2].replace(/^0/, '');
    }).join(', ');
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
    var dates = Object.keys(selected);
    var payload = {
      staffids: getSelectedStaffIds(),
      department_id: $('#departments').val(),
      month: monthKey(current),
      dates: dates
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
    var m = parseInt($('#sat_month').val(), 10) - 1;
    var y = parseInt($('#sat_year').val(), 10);
    if (isNaN(m) || isNaN(y)) {
      return;
    }
    if (dirty && !confirm('You have unsaved changes. Change month/year anyway?')) {
      syncMonthYearSelects();
      return;
    }
    current = new Date(y, m, 1);
    loadAssignedAndRender();
  }

  $('#departments').on('change', function () {
    refreshStaff($(this).val());
  });
  $staffSelect.on('changed.bs.select', function () {
    updateCalendarEnabled();
    loadAssignedAndRender();
  });
  $('#sat_month, #sat_year').on('change', applyMonthYearFromSelects);
  $('#sat_prev_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) {
      return;
    }
    current.setMonth(current.getMonth() - 1);
    syncMonthYearSelects();
    loadAssignedAndRender();
  });
  $('#sat_next_month').on('click', function () {
    if (dirty && !confirm('You have unsaved changes. Change month anyway?')) {
      return;
    }
    current.setMonth(current.getMonth() + 1);
    syncMonthYearSelects();
    loadAssignedAndRender();
  });
  $('#sat_select_all').on('click', function () {
    getMonthSaturdays().forEach(function (d) { selected[d] = true; });
    markDirty(true);
    paintSelected();
    setStatus('All Saturdays selected — click Save to apply', null);
  });
  $('#sat_clear_all').on('click', function () {
    selected = {};
    markDirty(Object.keys(saved).length > 0);
    paintSelected();
    $('#sat_preset_13, #sat_preset_24').prop('checked', false);
    setStatus('All Saturdays cleared — click Save to apply', null);
  });
  $('#sat_preset_13').on('change', function () {
    applyOrdinalPreset([1, 3], $(this).is(':checked'));
    var dates = ordinalPresetLabel([1, 3]);
    setStatus($(this).is(':checked')
      ? ('1st & 3rd Saturday selected' + (dates ? ' (' + dates + ')' : '') + ' — click Save to apply')
      : ('1st & 3rd Saturday cleared — click Save to apply'), null);
  });
  $('#sat_preset_24').on('change', function () {
    applyOrdinalPreset([2, 4], $(this).is(':checked'));
    var dates = ordinalPresetLabel([2, 4]);
    setStatus($(this).is(':checked')
      ? ('2nd & 4th Saturday selected' + (dates ? ' (' + dates + ')' : '') + ' — click Save to apply')
      : ('2nd & 4th Saturday cleared — click Save to apply'), null);
  });
  $('#sat_save_btn').on('click', saveLeaves);

  $(document).on('click', '#sat_days .sat-cal-day.is-sat', function (e) {
    if ($(e.target).is('input.sat-leave-cb, label, span')) {
      return;
    }
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
