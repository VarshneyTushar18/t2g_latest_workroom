<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" id="fullcalendar-css" href="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.css?v=3.0.4">
<script type="text/javascript" id="fullcalendar-js" src="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.js?v=3.0.4"></script>

<?php init_head(); ?>

<style>
  .right-elements { display: block; }
  .pagination button {
    margin: 0; text-decoration: none; padding: 5px 10px;
    border: 1px solid #ccc; border-radius: 5px; background: #fff; min-width: 36px;
  }
  .pagination button.active {
    background-color: #141e46; color: white; border-color: #141e46;
  }
  .pagination button:disabled { opacity: .45; cursor: default; }
  .pagination .pg-ellipsis {
    display: inline-flex; align-items: center; padding: 0 6px; color: #64748b; user-select: none;
  }
  .pagination-container { clear: both; text-align: center; margin-top: 14px; }
  .pagination { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 4px; }
  .line-suggestion { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }
  .line-suggestion .btn { margin: 0; padding: 4px 8px; font-size: 11px; }
  .emp-att-meta { font-size: 12px; color: #64748b; margin: 6px 0 0; }
  .calendar-header {
    font-size: 12px; font-weight: 700; margin: 0 0 14px; padding-bottom: 8px;
    color: #0f172a; border-bottom: 1px solid #eef2f7;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .emp-att-card .fc-header-toolbar { display: none !important; margin: 0 !important; padding: 0 !important; }
  .emp-att-card [id^="calendar-"] { margin-top: 2px; }

  .emp-att-card {
    cursor: pointer;
    transition: box-shadow .15s ease, transform .15s ease;
    position: relative;
    margin-bottom: 12px;
  }
  .emp-att-card .panel-body { padding: 8px 10px 10px; }
  .emp-att-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, .14);
    transform: translateY(-2px);
  }
  .emp-att-card .card-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px solid #e5e7eb;
  }
  .emp-att-card .btn-view-card {
    background: #2563eb;
    color: #fff !important;
    border: 0;
    border-radius: 5px;
    padding: 3px 10px;
    font-weight: 600;
    font-size: 11px;
  }
  .emp-att-card .btn-view-card:hover {
    background: #1d4ed8;
    color: #fff !important;
  }
  .emp-att-card .open-hint {
    font-size: 10px; color: #64748b; margin: 0;
  }

  /* Compact chips for 3×3 grid (9 calendars) */
  .emp-att-grid-col { padding-left: 8px; padding-right: 8px; }
  .emp-att-card .fc { font-size: 10px; }
  .emp-att-card .fc-col-header-cell-cushion { padding: 2px 0; font-size: 9px; }
  .emp-att-card .fc-daygrid-day-number { font-size: 9px; padding: 1px 2px; }
  .emp-att-card .fc-daygrid-day-frame { min-height: 28px !important; }

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
  #empAttFs .fs-punch-bar {
    display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
    margin: 0 0 12px; padding: 8px 10px; background: #f8fafc;
    border: 1px solid #e2e8f0; border-radius: 8px;
  }
  #empAttFs .fs-punch-bar .fs-punch-label {
    flex: 1 1 auto; font-size: 13px; color: #334155; margin: 0; font-weight: 600;
  }
  #empAttFs .fs-punch-bar .fs-punch-label span { color: #0f172a; }
  #empAttFs .fs-punch-bar .btn-all-month {
    background: #141e46; color: #fff; border: 0; border-radius: 6px;
    padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer;
  }
  #empAttFs .fs-punch-bar .btn-all-month[disabled] {
    opacity: .45; cursor: default;
  }
  #empAttFsCalendar .fc-daygrid-day.emp-att-day-selected {
    background: #fef9c3 !important;
  }
  #empAttFsCalendar .fc-daygrid-day {
    cursor: pointer;
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

  /* Attendance status chips — compact on cards, larger in fullscreen */
  .emp-att-card .fc,
  #empAttFsCalendar .fc {
    font-size: 12px;
  }
  .emp-att-card .fc-daygrid-day-frame {
    min-height: 28px !important;
  }
  #empAttFsCalendar .fc-daygrid-day-frame {
    min-height: 52px;
  }
  .emp-att-card .fc-daygrid-event,
  #empAttFsCalendar .fc-daygrid-event {
    margin: 0 1px !important;
    border: 0 !important;
    border-radius: 3px !important;
    box-shadow: none !important;
    background: transparent !important;
  }
  .emp-att-card .fc-daygrid-event-harness,
  #empAttFsCalendar .fc-daygrid-event-harness {
    margin-top: 1px !important;
  }
  .emp-att-card .fc-event-main,
  #empAttFsCalendar .fc-event-main {
    padding: 0 !important;
  }
  .att-chip {
    display: block;
    width: 100%;
    text-align: center;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.02em;
    line-height: 1.2;
    padding: 1px 0;
    border-radius: 3px;
    color: #fff !important;
    white-space: nowrap;
    overflow: visible;
  }
  #empAttFsCalendar .att-chip {
    font-size: 12px;
    padding: 3px 2px;
    border-radius: 5px;
  }
  .line-suggestion .btn {
    min-width: 36px;
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
              <div class="row filter_by emp-att-filters">
                <div class="col-md-2 leads-filter-column">
                  <label class="control-label">Month</label>
                  <?php echo render_input('month_year', '', isset($selectedMonth) ? $selectedMonth: date('Y-m'), 'month'); ?>
                </div>
                <div class="col-md-2 leads-filter-column">
                  <label class="control-label">Department</label>
                  <?php echo render_select('department_id', $departments ?? [], array('departmentid', 'name'), '', $department_id ?? '') ?>
                </div>
                <?php if (is_admin() || is_HR() || is_super_hr() || (get_staff_user_id() == 23)): ?>
                  <div class="col-md-2 leads-filter-column">
                    <label class="control-label">Reporting manager</label>
                    <?php echo render_select('reporting_person', $staffs_in_select_option, array('staffid', 'full_name'), '', $reporting_person ?? '', ['data-live-search' => 'true', 'data-none-selected-text' => 'Select manager']) ?>
                  </div>
                <?php endif ?>
                <div class="col-md-3 leads-filter-column">
                  <label class="control-label">Employee name</label>
                  <?php echo render_select('staff_id', $staff_options ?? [], array('staffid', 'full_name'), '', $staff_id ?? '', ['data-live-search' => 'true', 'data-none-selected-text' => 'Select employee']) ?>
                </div>
                <div class="col-md-2">
                  <label class="control-label">&nbsp;</label>
                  <div>
                    <button type="submit" class="btn btn-info timesheets_filter">Go</button>
                    <?php if (!empty($staff_id) || !empty($department_id) || !empty($reporting_person)) { ?>
                      <a href="<?php echo admin_url('timesheets/check_employee_attendance'); ?>" class="btn btn-default">Clear</a>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <p class="emp-att-meta">
                Showing <?php echo count($staffToDisplay); ?> of <?php echo (int) ($totalStaff ?? 0); ?> staff
                · 9 calendars per page · page <?php echo (int) $currentPage; ?>/<?php echo (int) $totalPages; ?>
              </p>
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
                  <div class="col-lg-4 col-md-6 emp-att-grid-col">
                    <div class="panel_s emp-att-card"
                         data-staffid="<?= (int) $staff['staffid'] ?>"
                         data-name="<?= htmlspecialchars($staff['full_name'], ENT_QUOTES) ?>"
                         data-code="<?= htmlspecialchars($staff['staff_identifi'] ?? '', ENT_QUOTES) ?>">
                      <div class="panel-body">
                        <div class="dt-loader hide"></div>
                        <div class="calendar-header" title="<?= html_escape($staff['full_name']) ?>"><?= html_escape($staff['full_name']) ?> · <?= html_escape($staff['staff_identifi'] ?? '') ?></div>
                        <div id="calendar-<?= $staff['staffid'] ?>"></div>
                        <div class="card-actions">
                          <span class="open-hint"><i class="fa fa-expand"></i> Full screen</span>
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
                        height: 220,
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
                          info.el.setAttribute('data-toggle', 'tooltip');
                          info.el.setAttribute('data-html', 'true');
                          info.el.setAttribute('title', tooltipContent);
                          if (window.jQuery && jQuery.fn.tooltip) {
                            jQuery(info.el).tooltip({ html: true, container: 'body' });
                          }
                        }
                      });
                      calendar.render();
                    });
                  </script>
                <?php endforeach; ?>

                <?php if ((int) $totalPages > 1): ?>
                <div class="pagination-container">
                  <?php echo form_open(); ?>
                  <input type="hidden" name="month_year" value="<?php echo html_escape($selectedMonth); ?>">
                  <?php if (!empty($reporting_person)) { ?>
                    <input type="hidden" name="reporting_person" value="<?php echo (int) $reporting_person; ?>">
                  <?php } ?>
                  <?php if (!empty($department_id)) { ?>
                    <input type="hidden" name="department_id" value="<?php echo (int) $department_id; ?>">
                  <?php } ?>
                  <?php if (!empty($staff_id)) { ?>
                    <input type="hidden" name="staff_id" value="<?php echo (int) $staff_id; ?>">
                  <?php } ?>
                  <div class="pagination">
                    <?php
                      $cur = (int) $currentPage;
                      $tot = (int) $totalPages;
                      $window = 2;
                      $pages = [];
                      $pages[] = 1;
                      for ($i = max(2, $cur - $window); $i <= min($tot - 1, $cur + $window); $i++) {
                          $pages[] = $i;
                      }
                      if ($tot > 1) {
                          $pages[] = $tot;
                      }
                      $pages = array_values(array_unique($pages));
                      sort($pages);
                    ?>
                    <button type="submit" name="page" value="<?php echo max(1, $cur - 1); ?>" <?php echo $cur <= 1 ? 'disabled' : ''; ?>>
                      <i class="fa fa-arrow-left"></i> Prev
                    </button>
                    <?php
                      $prevShown = 0;
                      foreach ($pages as $p):
                        if ($prevShown && $p > $prevShown + 1):
                    ?>
                      <span class="pg-ellipsis">…</span>
                    <?php endif; ?>
                      <button type="submit" name="page" value="<?php echo $p; ?>" <?php echo $p === $cur ? 'class="active"' : ''; ?>><?php echo $p; ?></button>
                    <?php
                        $prevShown = $p;
                      endforeach;
                    ?>
                    <button type="submit" name="page" value="<?php echo min($tot, $cur + 1); ?>" <?php echo $cur >= $tot ? 'disabled' : ''; ?>>
                      Next <i class="fa fa-arrow-right"></i>
                    </button>
                  </div>
                  </form>
                </div>
                <?php endif; ?>
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
        <h4>Punches</h4>
        <div class="fs-punch-bar">
          <p class="fs-punch-label" id="empAttFsPunchLabel">Showing: <span>all month</span></p>
          <button type="button" class="btn-all-month" id="empAttFsAllMonth" disabled>View all month</button>
        </div>
        <h4 style="font-size:14px; margin-top:4px;">Biometric / punch swipes</h4>
        <p class="fs-note" id="empAttFsBioNote"></p>
        <div style="max-height:240px; overflow:auto; margin-bottom:16px;">
          <table>
            <thead>
              <tr><th>Date</th><th>Time</th><th>IN/OUT</th><th>Source</th></tr>
            </thead>
            <tbody id="empAttFsBioBody">
              <tr><td colspan="4" class="text-muted">No biometric swipes</td></tr>
            </tbody>
          </table>
        </div>
        <h4 style="font-size:14px;">Workroom check-in / check-out</h4>
        <div style="max-height:240px; overflow:auto;">
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
  var allBioSwipes = [];
  var allCioSwipes = [];
  var selectedPunchDate = null;

  function typeBadge(type) {
    var t = String(type || '').toUpperCase();
    if (t === 'IN') return '<span class="badge-in">IN</span>';
    if (t === 'OUT') return '<span class="badge-out">OUT</span>';
    return t || '—';
  }

  function fillSwipeTable(tbodySel, rows, emptyMsg) {
    var $tb = $(tbodySel);
    $tb.empty();
    if (!rows || !rows.length) {
      $tb.html('<tr><td colspan="4" class="text-muted">' + (emptyMsg || 'No records') + '</td></tr>');
      return;
    }
    rows.forEach(function (r) {
      $tb.append(
        '<tr><td>' + (r.date || '') + '</td><td>' + (r.time || '') +
        '</td><td>' + typeBadge(r.type) + '</td><td>' + (r.source || '') + '</td></tr>'
      );
    });
  }

  function filterRowsByDate(rows, dateStr) {
    if (!dateStr) {
      return rows || [];
    }
    return (rows || []).filter(function (r) {
      return String(r.date || '') === String(dateStr);
    });
  }

  function updatePunchFilterUi() {
    if (selectedPunchDate) {
      $('#empAttFsPunchLabel').html('Showing punches for <span>' + selectedPunchDate + '</span>');
      $('#empAttFsAllMonth').prop('disabled', false);
    } else {
      $('#empAttFsPunchLabel').html('Showing: <span>all month</span>');
      $('#empAttFsAllMonth').prop('disabled', true);
    }
  }

  function renderPunchTables() {
    var emptyDay = selectedPunchDate
      ? ('No punches on ' + selectedPunchDate)
      : 'No records for this month';
    fillSwipeTable('#empAttFsBioBody', filterRowsByDate(allBioSwipes, selectedPunchDate), emptyDay);
    fillSwipeTable('#empAttFsCioBody', filterRowsByDate(allCioSwipes, selectedPunchDate), emptyDay);
    updatePunchFilterUi();
  }

  function highlightSelectedDay(dateStr) {
    $('#empAttFsCalendar .fc-daygrid-day').removeClass('emp-att-day-selected');
    if (!dateStr || !fsCal) {
      return;
    }
    var cell = fsCal.el.querySelector('.fc-daygrid-day[data-date="' + dateStr + '"]');
    if (cell) {
      cell.classList.add('emp-att-day-selected');
    }
  }

  function selectPunchDay(dateStr) {
    selectedPunchDate = dateStr || null;
    renderPunchTables();
    highlightSelectedDay(selectedPunchDate);
  }

  function clearPunchDayFilter() {
    selectedPunchDate = null;
    renderPunchTables();
    highlightSelectedDay(null);
  }

  function closeFs() {
    $('#empAttFs').removeClass('open').attr('aria-hidden', 'true');
    $('body').css('overflow', '');
    if (fsCal) {
      fsCal.destroy();
      fsCal = null;
    }
    allBioSwipes = [];
    allCioSwipes = [];
    selectedPunchDate = null;
  }

  function openFs(staffId, name, code) {
    $('#empAttFs').addClass('open').attr('aria-hidden', 'false');
    $('body').css('overflow', 'hidden');
    $('#empAttFsTitle').text(name + (code ? (' — ' + code) : ''));
    $('#empAttFsMeta').text('Month: ' + monthYear + ' · click a day to see that day punches');
    $('#empAttFsLoading').text('Loading attendance & swipes…').show();
    $('#empAttFsContent').hide();
    selectedPunchDate = null;
    allBioSwipes = [];
    allCioSwipes = [];

    var payload = { staffid: staffId, month_year: monthYear };
    payload[csrf.token_name] = csrf.hash;

    $.post(admin_url + 'timesheets/employee_attendance_popup', payload, function (res) {
      $('#empAttFsLoading').hide();
      if (!res || !res.ok) {
        $('#empAttFsLoading').text((res && res.error) ? res.error : 'Failed to load').show();
        return;
      }
      $('#empAttFsContent').show();
      $('#empAttFsBioNote').text(res.bio_note || '');
      allBioSwipes = res.biometric_swipes || [];
      allCioSwipes = res.check_in_out || [];
      clearPunchDayFilter();

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
        dateClick: function (info) {
          selectPunchDay(info.dateStr);
        },
        eventClick: function (info) {
          info.jsEvent.preventDefault();
          var d = info.event.startStr || (info.event.start ? info.event.start.toISOString().slice(0, 10) : '');
          if (d) {
            selectPunchDay(d.slice(0, 10));
          }
        },
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
            ' | Out: ' + (info.event.extendedProps.check_out_time || '') +
            ' | Click to see punches';
          info.el.setAttribute('title', tip);
          info.el.style.cursor = 'pointer';
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
  $('#empAttFsAllMonth').on('click', function () {
    clearPunchDayFilter();
  });
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && $('#empAttFs').hasClass('open')) {
      closeFs();
    }
  });

  function fillSelect($sel, rows, keepVal, blankText) {
    var $el = $($sel);
    if (!$el.length) {
      return '';
    }
    // Prefer the real <select> behind bootstrap-select.
    if ($el.hasClass('dropdown-toggle') || $el.closest('.bootstrap-select').length) {
      var $linked = $el.closest('.bootstrap-select').find('select').first();
      if ($linked.length) {
        $el = $linked;
      }
    }
    $el.empty().append($('<option></option>').attr('value', '').text(''));
    (rows || []).forEach(function (r) {
      if (!r || r.staffid === undefined || r.staffid === null || r.staffid === '') {
        return;
      }
      $el.append(
        $('<option></option>').attr('value', String(r.staffid)).text(r.full_name || ('#' + r.staffid))
      );
    });
    var next = '';
    if (keepVal && $el.find('option[value="' + String(keepVal) + '"]').length) {
      next = String(keepVal);
      $el.val(next);
    } else {
      $el.val('');
    }
    if ($.fn.selectpicker) {
      if (blankText) {
        $el.attr('data-none-selected-text', blankText);
      }
      try {
        $el.selectpicker('refresh');
      } catch (e) {
        $el.selectpicker({ liveSearch: true, noneSelectedText: blankText || 'Nothing selected' });
      }
    }
    return next;
  }

  function reloadFilterOptions(opts) {
    opts = opts || {};
    var $dept = $('select[name="department_id"]');
    var dept = $dept.val() || '';
    if (Array.isArray(dept)) {
      dept = dept[0] || '';
    }
    var mgrSel = $('select[name="reporting_person"]');
    var empSel = $('select[name="staff_id"]');
    var mgrKeep = opts.resetManager ? '' : (mgrSel.val() || '');
    var empKeep = opts.resetEmployee ? '' : (empSel.val() || '');
    if (Array.isArray(mgrKeep)) mgrKeep = mgrKeep[0] || '';
    if (Array.isArray(empKeep)) empKeep = empKeep[0] || '';

    $.getJSON(admin_url + 'timesheets/check_employee_attendance_filter_options', {
      department_id: dept,
      reporting_person: mgrKeep
    }).done(function (data) {
      if (!data || typeof data !== 'object') {
        data = { managers: [], employees: [] };
      }
      if (mgrSel.length) {
        mgrKeep = fillSelect(mgrSel, data.managers || [], data.reporting_person || mgrKeep, 'Select manager');
      }
      fillSelect(empSel, data.employees || [], empKeep, 'Select employee');
    }).fail(function () {
      // Keep existing options if AJAX fails.
    });
  }

  var filterReloadTimer = null;
  function scheduleFilterReload(opts) {
    clearTimeout(filterReloadTimer);
    filterReloadTimer = setTimeout(function () {
      reloadFilterOptions(opts);
    }, 80);
  }

  $(document).on('changed.bs.select', 'select[name="department_id"]', function () {
    scheduleFilterReload({ resetManager: true, resetEmployee: true });
  });
  $(document).on('changed.bs.select', 'select[name="reporting_person"]', function () {
    scheduleFilterReload({ resetManager: false, resetEmployee: true });
  });
})(jQuery);
</script>
</body>
</html>
