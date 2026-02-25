<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
		  <?php if(is_admin() || get_staff_user_id()==267){ ?>
            <a href="#" id="import_data_btn" class="btn btn-info mleft5">Import Attendance Data</a>
		  <?php }?>
		
				<div class="row mb-2 mtop20">
		  <div class="col-md-3">
			<input type="month" id="filter_month" class="form-control" value="<?= date('Y-m') ?>">
		  </div>
		   <?php if(is_admin() || is_manager() ){ ?>
		  <div class="col-md-3">
			<select id="filter_department" class="form-control">
			  <option value="#">All Departments</option>
			  <?php foreach ($result as $dept): ?>
				<option value="<?= $dept['departmentid'] ?>"><?= $dept['name'] ?></option>
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
 
            <div class="table-responsive mtop20">
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
                  <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
              </table>
            </div>

            <nav>
              <ul class="pagination mb-0" id="pagination">
                <!-- Pagination will be generated here -->
              </ul>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="import_data" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Attendance Data</h5>
      </div>
      <?php echo form_open_multipart(admin_url("biometric/save_attendance_bulk_data"), ['id' => 'attendance-bulk-upload-form']); ?>
      <div class="modal-body">
        <input type="file" class="form-control" name="excelFile" accept=".xls,.xlsx" required style="margin-bottom: 15px;">
        <a href="/uploads/attendance_template.xlsx" download>Download Sample Format</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Upload</button>
      </div>
      </form>
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
let totalPages = 1;

function loadAttendanceData(page = 1) {
  currentPage = page;
  const offset = (page - 1) * limit;
  const month = $('#filter_month').val();
  const department = $('#filter_department').val();
  const staff = $('#filter_staff').val();

  $.get("<?= admin_url('biometric/fetch_attendance_data') ?>", {
    limit,
    offset,
    month,
    department,
    staff
  }, function(response) {
    const res = JSON.parse(response);
    console.log(res);
    let rows = "";
    let i = offset + 1; // So page 2 starts from 11
    if (res.data.length > 0) {
            res.data.forEach(row => {
					const isAbsent = row.status === 'A';
				    const buttonClass = isAbsent ? 'btn-danger' : 'btn-primary';
					const buttonLabel = isAbsent ? 'Absent' : 'View Punch';
					const buttonDisabled = isAbsent ? 'disabled' : '';
                rows += `<tr>
					<td>${i++}</td>
					<td>${row.attendance_date}</td>
					<td>${row.a_in_time}</td>
					<td>${row.a_out_time}</td>
					<td>${row.employee_code}</td>
					<td>${row.employee_name}</td>
					<td>${row.status}</td>
					<td>${row.t_duration}</td>
					<td>${row.break_time}</td>
					<td>
						<button class="btn btn-sm ${buttonClass} mt-1"
                onclick="showPunchModal('${row.punch_records}', '${row.attendance_date}', '${row.employee_name}')"
                ${buttonDisabled}>
                ${buttonLabel}
            </button>
					</td>
				</tr>`;

            });
        } else {
            rows = `<tr><td colspan="7" class="text-center">No records found.</td></tr>`;
        }

    $("#attendance_tbody").html(rows);

    totalPages = Math.max(1, Math.ceil(res.total / limit));
    buildPagination();
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
    loadAttendanceData(1);

    $('#import_data_btn').click(function(e){
        e.preventDefault();
        $('#import_data').modal('show');
    });
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
  let deptId = $(this).val();
  $.get("<?= admin_url('biometric/get_staff_by_department') ?>", { dept_id: deptId }, function (res) {
    const staffList = JSON.parse(res);
    let options = '<option value="">All Staff</option>';
    staffList.forEach(staff => {
      options += `<option value="${staff.staffid}">${staff.full_name}</option>`;
    });
    $('#filter_staff').html(options);
  });
});

</script>

</body>
</html>
