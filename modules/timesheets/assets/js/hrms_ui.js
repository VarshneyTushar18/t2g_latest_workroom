/**
 * HRMS shared UI helpers (local).
 * Adds body.hrms-ui + quick topnav on HRMS routes.
 */
(function () {
  'use strict';
  var path = (window.location.pathname || '') + (window.location.search || '');
  var isHrms =
    path.indexOf('/admin/timesheets') !== -1 ||
    path.indexOf('/admin/biometric') !== -1 ||
    path.indexOf('/admin/staff/leave_balance') !== -1 ||
    path.indexOf('/admin/staff/manage_earned_leave') !== -1 ||
    path.indexOf('/admin/holiday') !== -1;

  if (!isHrms) {
    return;
  }

  document.documentElement.classList.add('hrms-ui');
  if (document.body) {
    document.body.classList.add('hrms-ui');
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      document.body.classList.add('hrms-ui');
    });
  }

  function injectTopnav() {
    if (document.querySelector('.hrms-topnav')) {
      return;
    }
    var content = document.querySelector('#wrapper > .content');
    if (!content) {
      return;
    }
    var admin = (typeof admin_url !== 'undefined') ? admin_url : '/admin/';
    var items = [
      { href: admin + 'timesheets/hrms_home', label: 'Home', icon: 'fa-home', match: '/timesheets/hrms_home' },
      { href: admin + 'timesheets/my_attendance', label: 'Attendance', icon: 'fa-clock', match: '/timesheets/my_attendance' },
      { href: admin + 'timesheets/attendance_regularization', label: 'Regularize', icon: 'fa-pen-to-square', match: '/timesheets/attendance_regularization' },
      { href: admin + 'timesheets/requisition_manage', label: 'Leave apply', icon: 'fa-file-lines', match: '/timesheets/requisition_manage' },
      { href: admin + 'staff/leave_balance', label: 'Balances', icon: 'fa-chart-pie', match: '/staff/leave_balance' },
      { href: admin + 'biometric', label: 'Biometric', icon: 'fa-id-badge', match: '/admin/biometric' },
      { href: admin + 'timesheets/check_employee_attendance', label: 'Team attendance', icon: 'fa-users', match: '/check_employee_attendance' },
      { href: admin + 'holiday/calendar', label: 'Holidays', icon: 'fa-sun', match: '/holiday' }
    ];

    var nav = document.createElement('div');
    nav.className = 'hrms-topnav';
    items.forEach(function (it) {
      var a = document.createElement('a');
      a.href = it.href;
      if (path.indexOf(it.match) !== -1) {
        a.className = 'active';
      }
      a.innerHTML = '<i class="fa-solid ' + it.icon + '"></i><span>' + it.label + '</span>';
      nav.appendChild(a);
    });

    // Insert as first child of content
    if (content.firstChild) {
      content.insertBefore(nav, content.firstChild);
    } else {
      content.appendChild(nav);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectTopnav);
  } else {
    injectTopnav();
  }
})();
