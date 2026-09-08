<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" id="fullcalendar-css" href="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.css?v=3.0.4">
<script type="text/javascript" id="fullcalendar-js" src="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.js?v=3.0.4"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php init_head(); ?>

<style>
  .right-elements { display: block; }
  .pagination button {
    margin: 0 5px; text-decoration: none; padding: 5px 10px;
    border: 1px solid #ccc; border-radius: 5px;
  }
  .pagination button.active {
    background-color: #141e46; color: white; border-color: #007bff;
  }
  .pagination-container { clear: both; text-align: center; margin-top: 10px; }
  .calendar-header { font-size: 18px; margin-bottom: 10px; }

  .emp-att-card {
    cursor: pointer;
    transition: box-shadow .15s ease, transform .15s ease;
    position: relative;
  }
  .emp-att-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, .14);
    transform: translateY(-2px);
  }
  .emp-att-card .card-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e5e7eb;
  }
  .emp-att-card .btn-view-card {
    background: #2563eb;
    color: #fff !important;
    border: 0;
    border-radius: 6px;
    padding: 6px 14px;
    font-weight: 600;
    font-size: 13px;
  }
  .emp-att-card .btn-view-card:hover {
    background: #1d4ed8;
    color: #fff !important;
  }
  .emp-att-card .open-hint {
    font-size: 12px; color: #64748b; margin: 0;
  }

  /* Fullscreen popup */
  #empAttFs {
    display: none;
    position: fixed; inset: 0; z-index: 10500;
    background: #0f172a;
  }
  #empAttFs.open { display: flex; flex-direction: column; }
  #empAttFs .fs-top {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap;
    padding: 14px 18px; background: #1e293b; color: #fff;
  }
  #empAttFs .fs-top h3 { margin: 0; font-size: 20px; font-weight: 600; }
  #empAttFs .fs-top .meta { color: #94a3b8; font-size: 13px; margin-top: 2px; }
  #empAttFs .fs-body {
    flex: 1; overflow: auto; background: #f1f5f9; padding: 16px;
  }
  #empAttFs .fs-grid {
    display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px;
  }
  @media (max-width: 992px) {
    #empAttFs .fs-grid { grid-template-columns: 1fr; }
  }
  #empAttFs .fs-panel {
    background: #fff; border-radius: 12px; padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
  }
  #empAttFs .fs-panel h4 {
    margin: 0 0 12px; font-size: 16px; font-weight: 700; color: #0f172a;
  }
  #empAttFs .fs-note {
    font-size: 12px; color: #64748b; margin: 0 0 10px;
  }
  #empAttFs #empAttFsCalendar { min-height: 520px; }
  #empAttFs table { width: 100%; margin: 0; }
  #empAttFs table th, #empAttFs table td {
    padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px;
  }
  #empAttFs .badge-in { background:#dcfce7; color:#166534; padding:2px 8px; border-radius:999px; font-weight:700; font-size:11px; }
  #empAttFs .badge-out { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:999px; font-weight:700; font-size:11px; }
  #empAttFs .fs-close {
    background:#ef4444; color:#fff; border:0; border-radius:8px;
    padding:8px 14px; font-weight:600; cursor:pointer;
  }
  #empAttFs .fs-loading { padding: 40px; text-align:center; color:#64748b; }

  /* Attendance status chips — readable Present / Absent codes */
  .emp-att-card .fc,
  #empAttFsCalendar .fc {
    font-size: 12px;
  }
  .emp-att-card .fc-daygrid-day-frame,
  #empAttFsCalendar .fc-daygrid-day-frame {
    min-height: 52px;
  }
  .emp-att-card .fc-daygrid-event,
  #empAttFsCalendar .fc-daygrid-event {
    margin: 2px 2px 0 !important;
    border: 0 !important;
    border-radius: 5px !important;
    box-shadow: none !important;
    background: transparent !important;
  }
  .emp-att-card .fc-daygrid-event-harness,
  #empAttFsCalendar .fc-daygrid-event-harness {
    margin-top: 2px !important;
  }
  .emp-att-card .fc-event-main,
  #empAttFsCalendar .fc-event-main {
    padding: 0 !important;
  }
  .att-chip {
    display: block;
    width: 100%;
    text-align: center;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.02em;
    line-height: 1.35;
    padding: 3px 2px;
    border-radius: 5px;
    color: #fff !important;
    white-space: nowrap;
    overflow: visible;
  }
  .line-suggestion .btn {
    min-width: 42px;
    margin: 0 4px 6px 0;
    font-weight: 700;
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4>Employee Attendance <small class="text-muted">— use View to open full screen + swipes</small><hr></h4>

            <?php if (has_permission('attendance_management', '', 'view') || is_admin() || (get_staff_user_id() == 23) || attendance_permission() || is_HR()) { ?>
              <?php echo form_open(); ?>
              <div class="row filter_by">
                <div class="col-md-2 leads-filter-column">
                  <?php echo render_input('month_year', '', isset($selectedMonth) ? $selectedMonth: date('Y-m'), 'month'); ?>
                </div>
                <?php if (is_admin() || is_HR() || is_super_hr() || (get_staff_user_id() == 23)): ?>
                  <div class="col-md-2 leads-filter-column">
                    <?php echo render_select('reporting_person', $staffs_in_select_option, array('staffid', 'full_name'), '', $staffs_under_manager) ?>
                  </div>
                <?php endif ?>
                <div class="col-md-1">
                  <button type="submit" class="btn btn-info timesheets_filter">Go</button>
                </div>
              </div>
              <?php echo form_close(); ?>
            <?php } ?>

            <hr class="hr-panel-heading no-margin" />

            <div class="row mtop15">
              <div class="col-md-8 line-suggestion">
                <button type="button" data-toggle="tooltip" title="Present" class="btn" style="background:#2ecc71; color:white">P</button>
                <button type="button" data-toggle="tooltip" title="Absent" class="btn" style="background:#e74c3c; color:white">AB</button>
                <button type="button" data-toggle="tooltip" title="Unplanned Half Leave" class="btn" style="background:#9900FE; color:white">UHL</button>
                <button type="button" data-toggle="tooltip" title="Planned Half Leave" class="btn" style="background:#ff9900; color:white">PHL</button>
                <button type="button" data-toggle="tooltip" title="Sick Leave" class="btn" style="background:#bf9000; color:white">SL</button>
                <button type="button" data-toggle="tooltip" title="Planned Leave" class="btn" style="background:#a64d79; color:white">PL</button>
                <button type="button" data-toggle="tooltip" title="Holiday" class="btn" style="background:#3d85c6; color:white">HO</button>
                <button type="button" data-toggle="tooltip" title="Unplanned Leave" class="btn" style="background:#666666; color:white">UL</button>
                <div class="clearfix"></div>
              </div>

              <div class="col-md-12">
                <hr />
                <?php foreach ($staffToDisplay as $staff): ?>
                  <div class="col-md-4">
                    <div class="panel_s emp-att-card"
                         data-staffid="<?= (int) $staff['staffid'] ?>"
                         data-name="<?= htmlspecialchars($staff['full_name'], ENT_QUOTES) ?>"
                         data-code="<?= htmlspecialchars($staff['staff_identifi'] ?? '', ENT_QUOTES) ?>">
                      <div class="panel-body">
                        <div class="dt-loader hide"></div>
                        <div class="calendar-header"><?= html_escape($staff['full_name']) ?> - <?= html_escape($staff['staff_identifi'] ?? '') ?></div>
                        <div id="calendar-<?= $staff['staffid'] ?>"></div>
                        <div class="card-actions">
                          <span class="open-hint"><i class="fa fa-expand"></i> Full screen + swipes</span>
                          <button type="button" class="btn btn-view-card btn-view-emp-att"
                                  data-staffid="<?= (int) $staff['staffid'] ?>"
                                  data-name="<?= htmlspecialchars($staff['full_name'], ENT_QUOTES) ?>"
                                  data-code="<?= htmlspecialchars($staff['staff_identifi'] ?? '', ENT_QUOTES) ?>">
                            <i class="fa fa-eye"></i> View
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <script>
                    document.addEventListener('DOMContentLoaded', function() {
                      function attLabel(type) {
                        var t = String(type || '').toUpperCase();
                        if (t === 'A' || t === 'AB') return 'AB';
                        if (t === 'H' || t === 'HO' || t === 'M') return 'HO';
                        if (t === 'HD') return 'PHL';
                        return t || '—';
                      }
                      function attColor(type) {
                        var t = attLabel(type);
                        if (t === 'P') return '#2ecc71';
                        if (t === 'AB') return '#e74c3c';
                        if (t === 'UHL') return '#9900FE';
                        if (t === 'PHL' || t === 'HD') return '#ff9900';
                        if (t === 'SL') return '#bf9000';
                        if (t === 'PL') return '#a64d79';
                        if (t === 'HO') return '#3d85c6';
                        if (t === 'UL') return '#666666';
                        return '#94a3b8';
                      }
                      var calendarEl = document.getElementById('calendar-<?= $staff['staffid'] ?>');
                      var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        initialDate: '<?= $month_year ?>-01',
                        height: 360,
                        headerToolbar: { left: '', center: '', right: '' },
                        dayMaxEvents: false,
                        fixedWeekCount: false,
                        events: [
                          <?php foreach ($staff['attendance'] as $attendance):
                            $rawType = strtoupper((string) ($attendance['type'] ?? ''));
                            if ($rawType === 'A') { $rawType = 'AB'; }
                            if ($rawType === 'H' || $rawType === 'M') { $rawType = 'HO'; }
                          ?>
                          {
                            title: '<?= $rawType ?>',
                            start: '<?= $attendance['date_work'] ?>',
                            display: 'block',
                            backgroundColor: '<?php
                              $t = $rawType;
                              echo ($t == 'P') ? '#2ecc71' : (($t == 'AB') ? '#e74c3c' : (($t == 'UHL') ? '#9900FE' : (($t == 'PHL' || $t == 'HD') ? '#ff9900' : (($t == 'SL') ? '#bf9000' : (($t == 'PL') ? '#a64d79' : (($t == 'HO') ? '#3d85c6' : (($t == 'UL') ? '#666666' : '#94a3b8')))))));
                            ?>',
                            borderColor: 'transparent',
                            textColor: '#fff',
                            extendedProps: {
                              total_time: '<?= $attendance['value'] ?>',
                              check_in_time: '<?= $attendance['check_in_time'] ?>',
                              check_out_time: '<?= $attendance['check_out_time'] ?>'
                            }
                          },
                          <?php endforeach; ?>
                        ],
                        eventContent: function(arg) {
                          var label = attLabel(arg.event.title);
                          var color = arg.event.backgroundColor || attColor(label);
                          return {
                            html: '<div class="att-chip" style="background:' + color + '">' + label + '</div>'
                          };
                        },
                        eventDidMount: function(info) {
                          var tooltipContent = 'Status: ' + attLabel(info.event.title) +
                            '<br>Total Time: ' + (info.event.extendedProps.total_time || '—') +
                            '<br>Check in: ' + (info.event.extendedProps.check_in_time || '—') +
                            '<br>Check out: ' + (info.event.extendedProps.check_out_time || '—');
                          info.el.setAttribute('data-bs-toggle', 'tooltip');
                          info.el.setAttribute('title', tooltipContent);
                          if (window.bootstrap && bootstrap.Tooltip) {
                            new bootstrap.Tooltip(info.el, { html: true });
                          }
                        }
                      });
                      calendar.render();
                    });
                  </script>
                <?php endforeach; ?>

                <div class="pagination-container">
                  <?php echo form_open(); ?>
                  <input type="hidden" name="month_year" value="<?php echo $selectedMonth; ?>">
                  <input type="hidden" name="reporting_person" value="<?php echo $staffs_under_manager; ?>">
                  <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                      <button type="submit" name="page" value="<?php echo $currentPage - 1; ?>"><i class="fa fa-arrow-left"></i> Prev</button>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <button type="submit" name="page" value="<?php echo $i; ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>><?php echo $i; ?></button>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                      <button type="submit" name="page" value="<?php echo $currentPage + 1; ?>">Next <i class="fa fa-arrow-right"></i></button>
                    <?php endif; ?>
                  </div>
                  </form>
                </div>
                <hr class="hr-panel-heading" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</div>

<!-- Fullscreen employee attendance + swipes -->
<div id="empAttFs" aria-hidden="true">
  <div class="fs-top">
    <div>
      <h3 id="empAttFsTitle">Employee</h3>
      <div class="meta" id="empAttFsMeta"></div>
    </div>
    <button type="button" class="fs-close" id="empAttFsClose">&times; Close</button>
  </div>
  <div class="fs-body">
    <div class="fs-loading" id="empAttFsLoading">Loading attendance &amp; swipes…</div>
    <div class="fs-grid" id="empAttFsContent" style="display:none;">
      <div class="fs-panel">
        <h4>Monthly attendance</h4>
        <div id="empAttFsCalendar"></div>
      </div>
      <div class="fs-panel">
        <h4>Biometric / punch swipes</h4>
        <p class="fs-note" id="empAttFsBioNote"></p>
        <div style="max-height:280px; overflow:auto; margin-bottom:16px;">
          <table>
            <thead>
              <tr><th>Date</th><th>Time</th><th>IN/OUT</th><th>Source</th></tr>
            </thead>
            <tbody id="empAttFsBioBody">
              <tr><td colspan="4" class="text-muted">No biometric swipes</td></tr>
            </tbody>
          </table>
        </div>
        <h4>Workroom check-in / check-out</h4>
        <div style="max-height:280px; overflow:auto;">
          <table>
            <thead>
              <tr><th>Date</th><th>Time</th><th>IN/OUT</th><th>Source</th></tr>
            </thead>
            <tbody id="empAttFsCioBody">
              <tr><td colspan="4" class="text-muted">No punches</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function ($) {
  'use strict';
  var fsCal = null;
  var monthYear = <?= json_encode($month_year) ?>;
  var csrf = (typeof csrfData !== 'undefined') ? csrfData : { token_name: 'csrf_token_name', hash: '' };

  function typeBadge(type) {
    var t = String(type || '').toUpperCase();
    if (t === 'IN') return '<span class="badge-in">IN</span>';
    if (t === 'OUT') return '<span class="badge-out">OUT</span>';
    return t || '—';
  }

  function fillSwipeTable(tbodySel, rows) {
    var $tb = $(tbodySel);
    $tb.empty();
    if (!rows || !rows.length) {
      $tb.html('<tr><td colspan="4" class="text-muted">No records for this month</td></tr>');
      return;
    }
    rows.forEach(function (r) {
      $tb.append(
        '<tr><td>' + (r.date || '') + '</td><td>' + (r.time || '') +
        '</td><td>' + typeBadge(r.type) + '</td><td>' + (r.source || '') + '</td></tr>'
      );
    });
  }

  function closeFs() {
    $('#empAttFs').removeClass('open').attr('aria-hidden', 'true');
    $('body').css('overflow', '');
    if (fsCal) {
      fsCal.destroy();
      fsCal = null;
    }
  }

  function openFs(staffId, name, code) {
    $('#empAttFs').addClass('open').attr('aria-hidden', 'false');
    $('body').css('overflow', 'hidden');
    $('#empAttFsTitle').text(name + (code ? (' — ' + code) : ''));
    $('#empAttFsMeta').text('Month: ' + monthYear);
    $('#empAttFsLoading').show();
    $('#empAttFsContent').hide();

    var payload = { staffid: staffId, month_year: monthYear };
    payload[csrf.token_name] = csrf.hash;

    $.post(admin_url + 'timesheets/employee_attendance_popup', payload, function (res) {
      $('#empAttFsLoading').hide();
      if (!res || !res.ok) {
        $('#empAttFsLoading').text((res && res.error) ? res.error : 'Failed to load').show();
        return;
      }
      $('#empAttFsContent').show();
      $('#empAttFsBioNote').text(res.bio_note || 'Biometric API integration coming soon.');
      fillSwipeTable('#empAttFsBioBody', res.biometric_swipes || []);
      fillSwipeTable('#empAttFsCioBody', res.check_in_out || []);

      if (fsCal) {
        fsCal.destroy();
        fsCal = null;
      }
      var calEl = document.getElementById('empAttFsCalendar');
      fsCal = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        initialDate: (res.month_year || monthYear) + '-01',
        height: 'auto',
        headerToolbar: { left: 'title', center: '', right: '' },
        dayMaxEvents: false,
        events: (res.events || []).map(function (e) {
          var title = String(e.title || '').toUpperCase();
          if (title === 'A') title = 'AB';
          if (title === 'H' || title === 'M') title = 'HO';
          return {
            title: title,
            start: e.start,
            display: 'block',
            backgroundColor: e.color,
            borderColor: 'transparent',
            textColor: '#fff',
            extendedProps: {
              total_time: e.total_time,
              check_in_time: e.check_in_time,
              check_out_time: e.check_out_time
            }
          };
        }),
        eventContent: function (arg) {
          var label = String(arg.event.title || '').toUpperCase();
          if (label === 'A') label = 'AB';
          if (label === 'H' || label === 'M') label = 'HO';
          var color = arg.event.backgroundColor || '#94a3b8';
          return { html: '<div class="att-chip" style="background:' + color + '">' + label + '</div>' };
        },
        eventDidMount: function (info) {
          var tip = 'Status: ' + info.event.title +
            ' | Total: ' + (info.event.extendedProps.total_time || '') +
            ' | In: ' + (info.event.extendedProps.check_in_time || '') +
            ' | Out: ' + (info.event.extendedProps.check_out_time || '');
          info.el.setAttribute('title', tip);
        }
      });
      fsCal.render();
    }, 'json').fail(function (xhr) {
      $('#empAttFsLoading').text(xhr && xhr.status === 419 ? 'Session expired — refresh page' : 'Failed to load').show();
    });
  }

  $(document).on('click', '.btn-view-emp-att', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var $btn = $(this);
    openFs($btn.data('staffid'), $btn.data('name'), $btn.data('code'));
  });

  $(document).on('click', '.emp-att-card', function (e) {
    // Card click also opens view (unless a nested control handled it)
    if ($(e.target).closest('a, button, input, select, textarea').length) {
      return;
    }
    e.preventDefault();
    openFs($(this).data('staffid'), $(this).data('name'), $(this).data('code'));
  });
  $('#empAttFsClose').on('click', closeFs);
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && $('#empAttFs').hasClass('open')) {
      closeFs();
    }
  });
})(jQuery);
</script>
</body>
</html>
