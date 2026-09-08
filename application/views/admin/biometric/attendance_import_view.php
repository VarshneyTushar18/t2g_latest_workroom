<?php init_head(); ?>
<style>
  .biometric-table-wrap {
    position: relative;
    min-height: 180px;
  }
  .biometric-table-wrap.is-loading .biometric-table-overlay {
    display: flex;
  }
  .biometric-table-overlay {
    display: none;
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.75);
    z-index: 5;
    align-items: center;
    justify-content: center;
  }
  .table-loading {
    background: unset;
  }
  .t2g-sync-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #99f6e4;
    background: linear-gradient(90deg, #f0fdfa, #fff);
  }
  .t2g-sync-banner.is-stale {
    border-color: #fcd34d;
    background: linear-gradient(90deg, #fffbeb, #fff);
  }
  .t2g-sync-banner h5 {
    margin: 0 0 4px;
    font-weight: 700;
    color: #0f766e;
  }
  .t2g-sync-banner.is-stale h5 { color: #b45309; }
  .t2g-sync-meta { font-size: 12px; color: #64748b; }
  .t2g-sync-pill {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
  }
  .t2g-sync-pill.live { background: #dcfce7; color: #166534; }
  .t2g-sync-pill.waiting { background: #fef3c7; color: #92400e; }
  .bio-tabs { margin: 18px 0 8px; border-bottom: 1px solid #e2e8f0; }
  .bio-tabs > li > a {
    padding: 10px 16px;
    font-weight: 600;
    color: #64748b;
    border: none !important;
    background: transparent !important;
  }
  .bio-tabs > li.active > a,
  .bio-tabs > li.active > a:hover,
  .bio-tabs > li.active > a:focus {
    color: #0f766e !important;
    border-bottom: 2px solid #14b8a6 !important;
    background: transparent !important;
  }
  /* Ensure merged tab panes actually show */
  #wrapper .tab-content > .tab-pane { display: none; }
  #wrapper .tab-content > .tab-pane.active { display: block !important; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php
              $sync = $sync_status ?? [];
              $is_live = !empty($sync['is_live']);
              $last_sync = $sync['last_sync_at'] ?? '';
              $age = isset($sync['last_sync_age_s']) ? (int) $sync['last_sync_age_s'] : null;
              $age_label = $age === null ? 'No sync yet' : ($age < 60 ? $age . 's ago' : round($age / 60) . ' min ago');
            ?>
            <div class="t2g-sync-banner <?= $is_live ? '' : 'is-stale' ?>" id="t2g_sync_banner">
              <div>
                <h5>Live Biomax sync</h5>
                <div class="t2g-sync-meta">
                  Auto-sync every <strong>2 minutes</strong> (faster than typical 15–30 min HRMS delays).
                  Manual Excel upload is <strong>disabled</strong>.
                </div>
                <div class="t2g-sync-meta mtop5" id="t2g_sync_detail">
                  Last sync: <strong id="t2g_sync_last"><?= $last_sync ? html_escape($last_sync) : '—' ?></strong>
                  · <span id="t2g_sync_age"><?= html_escape($age_label) ?></span>
                  · Today: <strong id="t2g_sync_today"><?= (int) ($sync['today_rows'] ?? 0) ?></strong> rows
                </div>
              </div>
              <span class="t2g-sync-pill <?= $is_live ? 'live' : 'waiting' ?>" id="t2g_sync_pill">
                <?= $is_live ? 'Live' : 'Waiting for bridge' ?>
              </span>
            </div>

            <ul class="nav nav-tabs bio-tabs" role="tablist">
              <li role="presentation" class="active"><a href="#bio_tab_daily" aria-controls="bio_tab_daily" role="tab" data-toggle="tab">Daily Attendance</a></li>
              <li role="presentation"><a href="#bio_tab_swipes" aria-controls="bio_tab_swipes" role="tab" data-toggle="tab">Biometric Swipes</a></li>
            </ul>

            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="bio_tab_daily">
		
				<div class="row mb-2 mtop20">
		  <div class="col-md-3">
			<input type="month" id="filter_month" class="form-control" value="<?= html_escape($default_month ?? date('Y-m')) ?>">
		  </div>
		   <?php if (!empty($can_filter_all)) { ?>
		  <div class="col-md-3">
			<select id="filter_department" class="form-control">
			  <option value="">All Departments</option>
			  <?php foreach ($result as $dept): ?>
				<option value="<?= html_escape($dept['departmentid']) ?>"><?= html_escape($dept['name']) ?></option>
			  <?php endforeach; ?>
			</select>
		  </div>
		  <div class="col-md-3">
			<select id="filter_staff" class="form-control">
			  <option value="">All Staff</option>
			  <!-- This will be populated dynamically -->
			</select>
		  </div>
		  <?php } ?>
		  <div class="col-md-3">
			<button class="btn btn-primary" id="applyFilters">Apply</button>
		  </div>
		</div>
 
            <div class="table-responsive mtop20 biometric-table-wrap" id="biometric_table_wrap">
              <div class="biometric-table-overlay" id="biometric_table_overlay">
                <div class="dt-loader"></div>
              </div>
              <table class="table table-bordered">
                <thead>
                  <tr>
					<th>S.No</th>
                    <th>Attendance Date</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
					<th>Status</th>
					<th>Total Login Time</th>
					<th>Total Break</th>
					<th>Total Punch Record</th>	
                  </tr>
                </thead>
                <tbody id="attendance_tbody">
                  <?php if (!empty($initial_rows)) { ?>
                    <?php $sn = 1; foreach ($initial_rows as $row) {
                      $isAbsent = ($row['status'] ?? '') === 'A';
                      $btnClass = $isAbsent ? 'btn-danger' : 'btn-primary';
                      $btnLabel = $isAbsent ? 'Absent' : 'View Punch';
                      $btnDisabled = $isAbsent ? 'disabled' : '';
                      $punch = htmlspecialchars($row['punch_records'] ?? '', ENT_QUOTES, 'UTF-8');
                      $date = htmlspecialchars($row['attendance_date'] ?? '', ENT_QUOTES, 'UTF-8');
                      $name = htmlspecialchars($row['employee_name'] ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                      <td><?= $sn++ ?></td>
                      <td><?= html_escape($row['attendance_date'] ?? '') ?></td>
                      <td><?= html_escape($row['a_in_time'] ?? '') ?></td>
                      <td><?= html_escape($row['a_out_time'] ?? '') ?></td>
                      <td><?= html_escape($row['employee_code'] ?? '') ?></td>
                      <td><?= html_escape($row['employee_name'] ?? '') ?></td>
                      <td><?= html_escape($row['status'] ?? '') ?></td>
                      <td><?= html_escape($row['t_duration'] ?? '') ?></td>
                      <td><?= html_escape($row['break_time'] ?? '') ?></td>
                      <td>
                        <button class="btn btn-sm <?= $btnClass ?> mt-1"
                          onclick="showPunchModal('<?= $punch ?>', '<?= $date ?>', '<?= $name ?>')"
                          <?= $btnDisabled ?>>
                          <?= $btnLabel ?>
                        </button>
                      </td>
                    </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr><td colspan="10" class="text-center">No records found for this filter.</td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

            <nav>
              <ul class="pagination mb-0" id="pagination">
                <!-- Pagination will be generated here -->
              </ul>
            </nav>
              </div><!-- /#bio_tab_daily -->

              <div role="tabpanel" class="tab-pane" id="bio_tab_swipes">
                <div class="row mtop20">
                  <div class="col-md-3">
                    <label>From date</label>
                    <input type="date" id="swipe_from" class="form-control" value="<?= html_escape(date('Y-m-d', strtotime('-7 days'))) ?>">
                  </div>
                  <div class="col-md-3">
                    <label>To date</label>
                    <input type="date" id="swipe_to" class="form-control" value="<?= html_escape(date('Y-m-d')) ?>">
                  </div>
                  <?php if (!empty($can_filter_all)) { ?>
                  <div class="col-md-3">
                    <label>Employee</label>
                    <select id="swipe_staff" class="form-control">
                      <option value="">All Staff</option>
                    </select>
                  </div>
                  <?php } else { ?>
                  <div class="col-md-3">
                    <label>Employee</label>
                    <input type="text" class="form-control" value="<?= html_escape(get_staff_full_name()) ?>" readonly>
                  </div>
                  <?php } ?>
                  <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="button" id="swipe_filter" class="btn btn-info btn-block">Filter</button>
                  </div>
                </div>
                <div class="table-responsive mtop15">
                  <table class="table table-striped table-hover" id="swipes_table">
                    <thead>
                      <tr>
                        <th>Employee Name</th>
                        <th>Swipe Time &amp; Date</th>
                        <th>Shift</th>
                        <th>In/Out</th>
                        <th>Received On</th>
                        <th>Door/Address</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody id="swipes_body">
                      <tr><td colspan="7" class="text-center text-muted">Open this tab or click Filter to load punch swipes.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div><!-- /#bio_tab_swipes -->
            </div><!-- /.tab-content -->

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="punchModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title" style="font-size: 18px;padding: 8px 2px 0px;">Punch Details</span>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="punchTable">
          <thead>
            <tr>
              <th>In Time</th>
              <th>Out Time</th>
              <th>Duration</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
		<div class="mb-2">
  <span style="display:inline-block; width:15px; height:15px; background:#4CAF50; margin-right:5px;"></span> Valid Session
  <span style="display:inline-block; width:15px; height:15px; background:#FFDD57; margin:0 10px;"></span> Break
  <span style="display:inline-block; width:15px; height:15px; background:#F44336; margin-right:5px;"></span> Punch Missing
</div>

       <div id="punchRangeChart" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
let currentPage = 1;
const limit = 10;
let totalPages = <?= max(1, (int) ceil(((int) ($initial_total ?? 0)) / 10)) ?>;

function setBiometricLoading(isLoading) {
  $('#biometric_table_wrap').toggleClass('is-loading', !!isLoading);
}

function escapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function loadAttendanceData(page = 1) {
  currentPage = page;
  const offset = (page - 1) * limit;
  const month = $('#filter_month').val() || '';
  let department = $('#filter_department').length ? ($('#filter_department').val() || '') : '';
  let staff = $('#filter_staff').length ? ($('#filter_staff').val() || '') : '';

  if (department === '#' || department === 'all') {
    department = '';
  }
  if (staff === '#' || staff === 'all') {
    staff = '';
  }

  setBiometricLoading(true);

  $.ajax({
    url: "<?= admin_url('biometric/fetch_attendance_data') ?>",
    method: "GET",
    dataType: "json",
    data: { limit, offset, month, department, staff },
    timeout: 60000
  }).done(function(res) {
    setBiometricLoading(false);
    let rows = "";
    let i = offset + 1;
    if (res && res.data && res.data.length > 0) {
      res.data.forEach(row => {
        const isAbsent = row.status === 'A';
        const buttonClass = isAbsent ? 'btn-danger' : 'btn-primary';
        const buttonLabel = isAbsent ? 'Absent' : 'View Punch';
        const buttonDisabled = isAbsent ? 'disabled' : '';
        const punchSafe = escapeHtml(row.punch_records || '').replace(/&#39;/g, "\\'");
        const dateSafe = escapeHtml(row.attendance_date || '');
        const nameSafe = escapeHtml(row.employee_name || '').replace(/&#39;/g, "\\'");
        rows += `<tr>
          <td>${i++}</td>
          <td>${escapeHtml(row.attendance_date)}</td>
          <td>${escapeHtml(row.a_in_time)}</td>
          <td>${escapeHtml(row.a_out_time)}</td>
          <td>${escapeHtml(row.employee_code)}</td>
          <td>${escapeHtml(row.employee_name)}</td>
          <td>${escapeHtml(row.status)}</td>
          <td>${escapeHtml(row.t_duration)}</td>
          <td>${escapeHtml(row.break_time)}</td>
          <td>
            <button class="btn btn-sm ${buttonClass} mt-1"
              onclick="showPunchModal('${punchSafe}', '${dateSafe}', '${nameSafe}')"
              ${buttonDisabled}>
              ${buttonLabel}
            </button>
          </td>
        </tr>`;
      });
    } else {
      rows = `<tr><td colspan="10" class="text-center">No records found for this filter. Try month <b>2025-08</b> or <b>2025-11</b>.</td></tr>`;
    }

    $("#attendance_tbody").html(rows);
    totalPages = Math.max(1, Math.ceil(((res && res.total) || 0) / limit));
    buildPagination();
  }).fail(function(xhr, textStatus) {
    setBiometricLoading(false);
    let detail = textStatus || 'Request failed';
    if (xhr && xhr.status) {
      detail += ' (HTTP ' + xhr.status + ')';
    }
    if (xhr && xhr.responseText) {
      detail += ': ' + xhr.responseText.substring(0, 160);
    }
    $("#attendance_tbody").html(`<tr><td colspan="10" class="text-center text-danger">Failed to load attendance. ${escapeHtml(detail)}</td></tr>`);
  });
}

function buildPagination() {
  let html = '';
  html += `<nav><ul class="pagination justify-content-center">`;

  // Previous button
  html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${currentPage - 1})">Previous</a>
  </li>`;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
      <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${i})">${i}</a>
    </li>`;
  }

  // Next button
  html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${currentPage + 1})">Next</a>
  </li>`;

  html += `</ul></nav>`;
  $('#pagination').html(html);
}



$(document).ready(function() {
    buildPagination();

    function refreshSyncStatus() {
      $.getJSON("<?= admin_url('biometric/sync_status') ?>", function (s) {
        if (!s) return;
        var live = !!s.is_live;
        $('#t2g_sync_banner').toggleClass('is-stale', !live);
        $('#t2g_sync_pill').toggleClass('live', live).toggleClass('waiting', !live)
          .text(live ? 'Live' : 'Waiting for bridge');
        $('#t2g_sync_last').text(s.last_sync_at || '—');
        if (s.last_sync_age_s == null) {
          $('#t2g_sync_age').text('No sync yet');
        } else if (s.last_sync_age_s < 60) {
          $('#t2g_sync_age').text(s.last_sync_age_s + 's ago');
        } else {
          $('#t2g_sync_age').text(Math.round(s.last_sync_age_s / 60) + ' min ago');
        }
        $('#t2g_sync_today').text(s.today_rows || 0);
      });
    }

    refreshSyncStatus();
    setInterval(refreshSyncStatus, 30000);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function showPunchModal(punchStr, date, empName) {
  $('#punchModal .modal-title').text(`Punch Details: ${empName} (${date})`);
  $('#punchTable tbody').empty();
 
  if (window.punchChartObj) {
    window.punchChartObj.destroy();
    window.punchChartObj = null;
  }
 
  if (!punchStr || punchStr.trim() === "") {
    $('#punchTable tbody').append(`<tr><td colspan="3" class="text-center">No punch data found.</td></tr>`);
    $('#punchModal').modal('show');
    return;
  }
 
  const entries = punchStr.split(',').filter(e => e.trim() !== '');
  const punches = [];
 
  entries.forEach(entry => {
    entry = entry.trim();
    const match = entry.match(/(\d{2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i);
    if (match) {
      const timeStr = match[1].length === 5 ? match[1] + ":00" : match[1];
      punches.push({
        time: timeStr,
        type: match[2].toLowerCase(),
        minutes: timeToMinutes(timeStr)
      });
    }
  });
 
  const workSessions = [];
  let totalMinutes = 0;
 
  let i = 0;
  while (i < punches.length) {
    const current = punches[i];
    const next = punches[i + 1];
 
    if (current.type === 'in' && next && next.type === 'out') {
      // ✅ Valid session
      const duration = next.minutes - current.minutes;
      totalMinutes += duration;
 
      workSessions.push({
        inTime: current.time,
        outTime: next.time,
        duration: duration
      });
 
      $('#punchTable tbody').append(`
        <tr>
          <td>${current.time}</td>
          <td>${next.time}</td>
          <td>${formatDuration(duration)}</td> 
        </tr>
      `);
      i += 2;
    } else {
      // ❌ Anomalous entry (standalone in or out)
      if (current.type === 'in') {
        workSessions.push({ inTime: current.time, outTime: null });
        $('#punchTable tbody').append(`
          <tr class="text-danger">
            <td>${current.time}</td>
            <td>Missing</td>
            <td>Punch Missing</td>
          </tr>
        `);
      } else if (current.type === 'out') {
        workSessions.push({ inTime: null, outTime: current.time });
        $('#punchTable tbody').append(`
          <tr class="text-danger">
            <td>Missing</td>
            <td>${current.time}</td>
            <td>Punch Missing</td>
          </tr>
        `);
      }
      i += 1;
    }
  }
 
  if (workSessions.length === 0) {
    $('#punchTable tbody').append(`<tr><td colspan="3" class="text-center">No valid punch pairs found.</td></tr>`);
  } else {
    $('#punchTable tbody').append(`
      <tr style="font-weight:bold;">
        <td colspan="2" class="text-right">Total Worked Duration</td>
        <td>${formatDuration(totalMinutes)}</td>
      </tr>
    `);
  }
 
  drawRangeBarChart(workSessions);
  $('#punchModal').modal('show');
}



function formatDuration(mins) {
  const h = Math.floor(mins / 60);
  const m = Math.floor(mins % 60);
  const s = Math.round((mins - Math.floor(mins)) * 60);
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}


function timeToMinutes(timeStr) {
  if (!timeStr || typeof timeStr !== 'string') return 0;
  const [hours, minutes] = timeStr.split(":").map(Number);
  return hours * 60 + minutes;
}
 
function minutesToTime(minutes) {
  const hrs = String(Math.floor(minutes / 60)).padStart(2, '0');
  const mins = String(minutes % 60).padStart(2, '0');
  return `${hrs}:${mins}`;
}



function drawRangeBarChart(sessions) {
  if (window.punchChartObj) {
    window.punchChartObj.destroy();
  }

  const chartData = [];
  let sessionCount = 1;
  let lastKnownTime = 0;

  for (let i = 0; i < sessions.length; i++) {
    const session = sessions[i];
    const hasIn = !!session.inTime;
    const hasOut = !!session.outTime;

    const inMin = hasIn ? timeToMinutes(session.inTime) : null;
    const outMin = hasOut ? timeToMinutes(session.outTime) : null;

    let fromMin = inMin;
    let toMin = outMin;
    let label = '';
    let durationText = '';
    let isAnomaly = false;

    if (!hasIn && hasOut) {
      isAnomaly = true;
      fromMin = lastKnownTime;
      toMin = outMin;
      label = `Punch In Missing`;
      durationText = `${minutesToTime(fromMin)} - ${minutesToTime(toMin)}`;
    } else if (hasIn && !hasOut) {
      isAnomaly = true;
      fromMin = inMin;
      toMin = inMin + 30;
      label = `Punch Out Missing`;
      durationText = `${minutesToTime(fromMin)} - ${minutesToTime(toMin)}`;
    } else if (!hasIn && !hasOut) {
      isAnomaly = true;
      fromMin = lastKnownTime;
      toMin = fromMin + 30;
      label = `Punch In & Out Missing`;
      durationText = `${minutesToTime(fromMin)} - ${minutesToTime(toMin)}`;
    } else {
      label = `${session.inTime} - ${session.outTime}`;
      durationText = minutesToTime(toMin - fromMin);
    }

    chartData.push({
      x: isAnomaly ? `A${sessionCount++}` : `S${sessionCount++}`,
      y: [fromMin, toMin],
      fillColor: isAnomaly ? '#F44336' : '#4CAF50',
      meta: {
        inTime: session.inTime || minutesToTime(fromMin),
        outTime: session.outTime || minutesToTime(toMin),
        duration: durationText,
        label: isAnomaly ? `${label}: ${durationText}` : label
      }
    });

    if (!isAnomaly && toMin > lastKnownTime) {
      lastKnownTime = toMin;
    }

    // Add break if there's a next session
    if (!isAnomaly && i < sessions.length - 1 && sessions[i + 1].inTime) {
      const nextInMin = timeToMinutes(sessions[i + 1].inTime);
      if (nextInMin > toMin) {
        chartData.push({
          x: `B${sessionCount - 1}`,
          y: [toMin, nextInMin],
          fillColor: '#FFDD57',
          meta: {
            inTime: minutesToTime(toMin),
            outTime: minutesToTime(nextInMin),
            duration: minutesToTime(nextInMin - toMin),
            label: `Break: ${minutesToTime(toMin)} - ${minutesToTime(nextInMin)}`
          }
        });
      }
    }
  }

  const options = {
    chart: {
      type: 'rangeBar',
      height: Math.max(500, chartData.length * 35),
      toolbar: { show: false },
      zoom: { enabled: false }
    },
    plotOptions: {
      bar: {
        horizontal: true,
        barHeight: '30px'
      }
    },
    tooltip: {
      custom: function({ w, seriesIndex, dataPointIndex }) {
        const meta = w.config.series[0].data[dataPointIndex].meta;
        return `
          <div style="padding:6px 10px;">
            <strong>${meta.label}</strong><br>
            Duration: ${meta.duration}
          </div>`;
      }
    },
    dataLabels: {
      enabled: true,
      formatter: function(val, opts) {
        return opts.w.config.series[0].data[opts.dataPointIndex].meta.duration;
      },
      style: {
        fontSize: '11px',
        colors: ['#000']
      }
    },
    xaxis: {
      type: 'numeric',
      title: { text: 'Time of Day' },
      labels: {
        formatter: function (val) {
          return minutesToTime(val);
        }
      }
    },
    yaxis: {
      labels: {
        style: { fontSize: '12px' }
      }
    },
    series: [{ data: chartData }]
  };

  window.punchChartObj = new ApexCharts(document.querySelector("#punchRangeChart"), options);
  window.punchChartObj.render();
}




</script>

<script>
$('#applyFilters').on('click', function () {
  currentPage = 1;
  loadAttendanceData(currentPage);
});

$('#filter_department').on('change', function () {
  let deptId = $(this).val() || '';
  if (deptId === '#' || deptId === 'all') {
    deptId = '';
  }
  $.get("<?= admin_url('biometric/get_staff_by_department') ?>", { dept_id: deptId }, function (res) {
    const staffList = (typeof res === 'string') ? JSON.parse(res) : res;
    let options = '<option value="">All Staff</option>';
    (staffList || []).forEach(staff => {
      options += `<option value="${escapeHtml(staff.staffid)}">${escapeHtml(staff.full_name)}</option>`;
    });
    $('#filter_staff').html(options);
    if ($('#swipe_staff').length) {
      $('#swipe_staff').html(options);
    }
  });
});

function loadSwipes() {
  if (!$('#swipes_body').length) {
    return;
  }
  $('#swipes_body').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
  var params = {
    from: $('#swipe_from').val(),
    to: $('#swipe_to').val()
  };
  if ($('#swipe_staff').length) {
    params.staff = $('#swipe_staff').val() || '';
  }
  $.getJSON(admin_url + 'biometric/fetch_swipes', params).done(function (res) {
    var rows = (res && res.data) ? res.data : [];
    if (!rows.length) {
      $('#swipes_body').html('<tr><td colspan="7" class="text-center">No swipes found for this date range. Try a wider From/To date.</td></tr>');
      return;
    }
    var html = '';
    rows.forEach(function (r) {
      var badge = (String(r.in_out || '').toUpperCase() === 'IN') ? 'success' : 'warning';
      html += '<tr>' +
        '<td>' + escapeHtml(r.employee_name) + '<br><small>#' + escapeHtml(r.employee_code || '') + '</small></td>' +
        '<td>' + escapeHtml((r.swipe_time || '') + ' ' + (r.swipe_date || '')) + '</td>' +
        '<td>' + escapeHtml(r.shift || '-') + '</td>' +
        '<td><span class="label label-' + badge + '">' + escapeHtml(r.in_out) + '</span></td>' +
        '<td>' + escapeHtml(r.received_on || '') + '</td>' +
        '<td>' + escapeHtml(r.door || '') + '</td>' +
        '<td><span class="label label-success">' + escapeHtml(r.status || '') + '</span></td>' +
        '</tr>';
    });
    $('#swipes_body').html(html);
  }).fail(function (xhr) {
    var msg = 'Could not load swipes.';
    if (xhr && xhr.status) {
      msg += ' (HTTP ' + xhr.status + ')';
    }
    $('#swipes_body').html('<tr><td colspan="7" class="text-center text-danger">' + msg + '</td></tr>');
  });
}

$(function () {
  $('#swipe_filter').on('click', function (e) {
    e.preventDefault();
    loadSwipes();
  });

  // Load whenever Biometric Swipes tab is shown
  $(document).on('shown.bs.tab', 'a[href="#bio_tab_swipes"]', function () {
    loadSwipes();
  });
  $(document).on('click', 'a[href="#bio_tab_swipes"]', function () {
    setTimeout(loadSwipes, 100);
  });

  // Deep-link: /admin/biometric?tab=swipes
  if ((window.location.search || '').indexOf('tab=swipes') !== -1) {
    $('a[href="#bio_tab_swipes"]').tab('show');
    setTimeout(loadSwipes, 150);
  }

  // Prefill swipe staff dropdown from daily filter list if already loaded
  if ($('#filter_staff').length && $('#swipe_staff').length && $('#filter_staff option').length > 1) {
    $('#swipe_staff').html($('#filter_staff').html());
  }
});

</script>

</body>
</html>
