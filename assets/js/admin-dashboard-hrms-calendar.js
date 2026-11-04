(function initDashboardAttendanceCalendar() {
  'use strict';

  var grid = document.getElementById('dashboardHrmsCalGrid');
  if (!grid) {
    return;
  }

  var attStaffId =
    typeof window.__apexStaffId !== 'undefined'
      ? window.__apexStaffId
      : typeof admin_url !== 'undefined'
        ? 0
        : 0;
  if (!attStaffId && typeof app !== 'undefined' && app.user_id) {
    attStaffId = parseInt(app.user_id, 10);
  }

  var months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];
  var calView = new Date();
  calView.setDate(1);
  var monthLabel = document.getElementById('dashboardHrmsCalMonthLabel');
  var dayMap = {};
  var calLoading = false;

  function pad(n) {
    return (n < 10 ? '0' : '') + n;
  }

  function ymd(d) {
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }

  function monthKey(d) {
    return d.getFullYear() + '-' + pad(d.getMonth() + 1);
  }

  function parseHrs(val) {
    if (!val || val === '—') {
      return null;
    }
    var n = parseFloat(String(val).replace('h', ''));
    return isNaN(n) ? null : n;
  }

  function dayHours(day) {
    if (!day) {
      return 0;
    }
    if (typeof day.hours === 'number' && day.hours > 0) {
      return day.hours;
    }
    var aw = parseHrs(day.actual_work_hrs);
    if (aw !== null) {
      return aw;
    }
    return parseHrs(day.total_work_hrs) || 0;
  }

  function dayHealthTone(day) {
    if (!day || day.status === 'future') {
      return 'neutral';
    }
    var code = day.code || '';
    if (day.status === 'night_carryover' || code === 'NC') {
      return 'off';
    }
    if (day.status === 'weekend' || code === 'O') {
      return 'off';
    }
    if (day.status === 'holiday' || code === 'HO' || code === 'H') {
      return 'holiday';
    }
    if (day.status === 'leave' || day.status === 'saturday_leave') {
      return 'leave';
    }
    if (
      ['EL', 'PL', 'L', 'SL', 'UL', 'LOP', 'CO', 'MAL', 'PHD', 'UHD', 'PAL', 'SHL'].indexOf(
        code
      ) >= 0
    ) {
      return 'leave';
    }
    if (day.status === 'regularised') {
      return 'ok';
    }
    if (day.status === 'pending') {
      return 'neutral';
    }
    var presentMin = day.present_min_hours || 8.0;
    var halfMin = day.half_day_min_hours || 5;
    var hrs = dayHours(day);
    if (day.status === 'absent' || day.status === 'punch_missing' || code === 'AB' || code === 'A') {
      return 'bad';
    }
    if (day.status === 'rejected') {
      if (hrs + 0.001 >= presentMin) {
        return 'ok';
      }
      if (hrs + 0.001 >= halfMin) {
        return 'half';
      }
      return 'bad';
    }
    if (day.status === 'half_day' || code === 'HD') {
      return 'half';
    }
    if (day.status === 'ok' || code === 'P') {
      return hrs + 0.001 >= presentMin
        ? 'ok'
        : hrs + 0.001 >= halfMin
          ? 'half'
          : hrs > 0
            ? 'bad'
            : 'neutral';
    }
    if (day.status === 'short_hours') {
      return hrs + 0.001 >= halfMin ? 'half' : 'bad';
    }
    if (hrs + 0.001 >= presentMin) {
      return 'ok';
    }
    if (hrs + 0.001 >= halfMin) {
      return 'half';
    }
    if (hrs > 0) {
      return 'bad';
    }
    return 'neutral';
  }

  function dayCodeClass(tone) {
    if (tone === 'ok') {
      return 'code-ok';
    }
    if (tone === 'half') {
      return 'code-half';
    }
    if (tone === 'bad') {
      return 'code-bad';
    }
    if (tone === 'leave') {
      return 'code-leave';
    }
    if (tone === 'holiday') {
      return 'code-holiday';
    }
    if (tone === 'off') {
      return 'code-off';
    }
    return '';
  }

  function dayCodeLabel(code) {
    if (code === 'AB') {
      return 'A';
    }
    if (code === 'NC') {
      return 'NC';
    }
    return code || '';
  }

  function renderAttendanceCalendar() {
    var y = calView.getFullYear();
    var m = calView.getMonth();
    if (monthLabel) {
      monthLabel.textContent = months[m] + ' ' + y;
    }
    var firstDow = new Date(y, m, 1).getDay();
    var daysInMonth = new Date(y, m + 1, 0).getDate();
    var prevDays = new Date(y, m, 0).getDate();
    var todayKey = ymd(new Date());
    var html = '';
    var i;
    for (i = 0; i < firstDow; i++) {
      html +=
        '<div class="hrms-cal-day muted"><span class="hrms-cal-num">' +
        (prevDays - firstDow + i + 1) +
        '</span></div>';
    }
    for (var day = 1; day <= daysInMonth; day++) {
      var key = y + '-' + pad(m + 1) + '-' + pad(day);
      var d = dayMap[key];
      var tone = d ? dayHealthTone(d) : 'neutral';
      var cls = 'hrms-cal-day tone-' + tone;
      if (key === todayKey) {
        cls += ' today';
      }
      html += '<div class="' + cls + '" data-date="' + key + '">';
      html += '<span class="hrms-cal-num">' + day + '</span>';
      if (d && d.code) {
        html +=
          '<span class="hrms-cal-code ' +
          dayCodeClass(tone) +
          '">' +
          dayCodeLabel(d.code) +
          '</span>';
      }
      html += '</div>';
    }
    var cells = firstDow + daysInMonth;
    var trailing = (7 - (cells % 7)) % 7;
    for (var t = 1; t <= trailing; t++) {
      html += '<div class="hrms-cal-day muted"><span class="hrms-cal-num">' + t + '</span></div>';
    }
    grid.innerHTML = html;
  }

  function loadAttendanceCalendar() {
    if (calLoading || !attStaffId) {
      return;
    }
    calLoading = true;
    grid.innerHTML =
      '<div style="grid-column:1/-1;padding:10px;text-align:center;color:#64748b;font-size:11px;"><i class="fa fa-spinner fa-spin"></i></div>';
    if (typeof jQuery === 'undefined') {
      calLoading = false;
      return;
    }
    jQuery
      .getJSON(admin_url + 'timesheets/my_attendance_calendar', {
        month: monthKey(calView),
        staff_id: attStaffId,
      })
      .done(function (res) {
        dayMap = {};
        (res.days || []).forEach(function (d) {
          dayMap[d.date] = d;
        });
        renderAttendanceCalendar();
        updateApexAttendanceKpi();
      })
      .fail(function () {
        grid.innerHTML =
          '<div style="grid-column:1/-1;padding:10px;text-align:center;color:#dc2626;font-size:11px;">Could not load calendar.</div>';
      })
      .always(function () {
        calLoading = false;
      });
  }

  var prevBtn = document.getElementById('dashboardHrmsCalPrev');
  var nextBtn = document.getElementById('dashboardHrmsCalNext');
  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      calView.setMonth(calView.getMonth() - 1);
      loadAttendanceCalendar();
    });
  }
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      calView.setMonth(calView.getMonth() + 1);
      loadAttendanceCalendar();
    });
  }
  grid.addEventListener('click', function (e) {
    var cell = e.target.closest('.hrms-cal-day[data-date]');
    if (!cell || cell.classList.contains('muted')) {
      return;
    }
    var ds = cell.getAttribute('data-date');
    var my = ds ? ds.slice(0, 7) : monthKey(calView);
    window.location.href =
      admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(my);
  });

  function updateApexAttendanceKpi() {
    var pctEl = document.getElementById('apexKpiAttendancePct');
    var metaEl = document.getElementById('apexKpiAttendanceMeta');
    if (!pctEl) {
      return;
    }
    var keys = Object.keys(dayMap || {});
    if (!keys.length) {
      return;
    }
    var present = 0;
    var counted = 0;
    keys.forEach(function (key) {
      var day = dayMap[key];
      if (!day) {
        return;
      }
      if (
        day.code === 'WO' ||
        day.code === 'H' ||
        day.status === 'off' ||
        day.status === 'holiday'
      ) {
        return;
      }
      counted++;
      var tone = dayHealthTone(day);
      if (tone === 'ok' || tone === 'half') {
        present += tone === 'ok' ? 1 : 0.5;
      }
    });
    if (counted > 0) {
      var pct = Math.round((present / counted) * 100);
      pctEl.textContent = pct + '%';
      if (metaEl) {
        metaEl.textContent = Math.round(present) + ' of ' + counted + ' working days';
      }
    }
  }

  var origRenderAttendanceCalendar = renderAttendanceCalendar;
  renderAttendanceCalendar = function () {
    origRenderAttendanceCalendar();
    updateApexAttendanceKpi();
  };

  if (typeof jQuery !== 'undefined') {
    jQuery(loadAttendanceCalendar);
  } else {
    loadAttendanceCalendar();
  }
})();
