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
  .bio-tabs { margin: 18px 0 8px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; }
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
  #bio_daily_filters .form-control,
  #bio_daily_filters select { max-width: 100%; }
  .bio-simple-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin: 8px 0 14px;
  }
  .bio-simple-head h4 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
  }
  .bio-simple-sub {
    margin: 0;
    font-size: 13px;
    color: #64748b;
  }
  .bio-mode-toggle {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
  }
  .bio-mode-toggle .bio-range-btn {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
    border-radius: 8px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 650;
    line-height: 1.2;
  }
  .bio-mode-toggle .bio-range-btn.active {
    background: #141e46;
    border-color: #141e46;
    color: #fff;
  }
  .bio-other-panel {
    display: none;
    padding: 14px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
  }
  .bio-other-panel.is-open { display: block; }
  .bio-other-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 10px;
  }
  .bio-other-row .bio-field {
    flex: 1 1 160px;
    min-width: 140px;
    max-width: 240px;
  }
  .bio-other-row .bio-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 4px;
  }
  .bio-other-row .bio-apply .btn {
    height: 38px;
    padding: 8px 18px;
    white-space: nowrap;
  }
  .bio-extra-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
  }
  .bio-extra-filters .bio-field {
    flex: 1 1 160px;
    min-width: 140px;
    max-width: 240px;
  }
  .bio-extra-filters .bio-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 4px;
  }
  .bio-empty-today {
    display: none;
    padding: 16px 18px;
    margin-bottom: 12px;
    border-radius: 10px;
    border: 1px dashed #94a3b8;
    background: #f1f5f9;
    color: #334155;
    font-size: 14px;
  }
  .bio-empty-today strong { color: #0f172a; }
  .bio-viewing-label {
    font-size: 13px;
    color: #475569;
    margin: 0 0 12px;
  }
  .bio-viewing-label strong { color: #0f172a; }
  @media (max-width: 575px) {
    .bio-other-row .bio-field,
    .bio-other-row .bio-apply,
    .bio-extra-filters .bio-field {
      flex: 1 1 100%;
      max-width: 100%;
    }
    .bio-other-row .bio-apply .btn,
    .bio-mode-toggle .bio-range-btn { width: 100%; }
  }
  #pagination .pagination {
    display: flex; flex-wrap: wrap; justify-content: center; gap: 4px;
    overflow-x: auto; max-width: 100%;
  }
  .biometric-table-wrap th, #swipes_table th { white-space: nowrap; }
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
              $today_label = date('d M Y');
              $today_rows = (int) ($sync['today_rows'] ?? 0);
            ?>
            <div class="t2g-sync-banner <?= $is_live ? '' : 'is-stale' ?>" id="t2g_sync_banner">
              <div>
                <h5>Live Biometric sync</h5>
                <div class="t2g-sync-meta">
                  Auto-updates every <strong>2 minutes</strong> from the office Biometric bridge.
                </div>
                <div class="t2g-sync-meta mtop5" id="t2g_sync_detail">
                  Last sync: <strong id="t2g_sync_last"><?= $last_sync ? html_escape($last_sync) : '—' ?></strong>
                  · <span id="t2g_sync_age"><?= html_escape($age_label) ?></span>
                  · Today: <strong id="t2g_sync_today"><?= $today_rows ?></strong> people
                  · <span id="t2g_auto_refresh_label">Table auto-refreshes every 2 min</span>
                </div>
              </div>
              <span class="t2g-sync-pill <?= $is_live ? 'live' : 'waiting' ?>" id="t2g_sync_pill">
                <?= $is_live ? 'Live' : 'Waiting for bridge' ?>
              </span>
            </div>

            <ul class="nav nav-tabs bio-tabs" role="tablist">
              <li role="presentation" class="active"><a href="#bio_tab_daily" aria-controls="bio_tab_daily" role="tab" data-toggle="tab">Daily Attendance</a></li>
              <li role="presentation"><a href="#bio_tab_swipes" aria-controls="bio_tab_swipes" role="tab" data-toggle="tab">Punch Swipes</a></li>
            </ul>

            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="bio_tab_daily">

                <div id="bio_daily_filters">
                  <div class="bio-simple-head">
                    <div>
                      <h4 id="bio_page_title">Today's Biometric attendance</h4>
                      <p class="bio-simple-sub" id="bio_page_sub">Showing only <strong><?= html_escape($today_label) ?></strong> · Biometric punches</p>
                    </div>
                  </div>

                  <div class="bio-mode-toggle" role="group" aria-label="Attendance day">
                    <button type="button" class="bio-range-btn active" data-range="today">Today</button>
                    <button type="button" class="bio-range-btn" data-range="day">Other day</button>
                    <button type="button" class="bio-range-btn" data-range="month">This month</button>
                  </div>

                  <div class="bio-other-panel" id="bio_other_panel">
                    <div class="bio-other-row">
                      <div class="bio-field" id="wrap_filter_day">
                        <label for="filter_day">Choose date</label>
                        <input type="date" id="filter_day" class="form-control" value="<?= html_escape($default_day ?? date('Y-m-d')) ?>">
                      </div>
                      <div class="bio-field" id="wrap_filter_month" style="display:none;">
                        <label for="filter_month">Choose month</label>
                        <input type="month" id="filter_month" class="form-control" value="<?= html_escape($default_month ?? date('Y-m')) ?>">
                      </div>
                      <div class="bio-apply">
                        <label class="control-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary" id="applyFilters">Show</button>
                      </div>
                    </div>
                  </div>

                  <?php if (!empty($can_filter_all) || !empty($can_filter_team)) { ?>
                  <div class="bio-extra-filters">
                      <?php if (!empty($can_filter_all)) { ?>
                      <div class="bio-field">
                        <label for="filter_department">Department</label>
                        <select id="filter_department" class="form-control">
                          <option value="">All Departments</option>
                          <?php foreach ($result as $dept): ?>
                            <option value="<?= html_escape($dept['departmentid']) ?>"><?= html_escape($dept['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="bio-field">
                        <label for="filter_staff">Employee</label>
                        <select id="filter_staff" class="form-control">
                          <option value="">All Staff</option>
                        </select>
                      </div>
                      <?php } elseif (!empty($can_filter_team)) { ?>
                      <div class="bio-field">
                        <label for="filter_staff">Employee</label>
                        <select id="filter_staff" class="form-control">
                          <option value="">My Team</option>
                          <?php foreach (($team_staff ?? []) as $s): ?>
                            <option value="<?= html_escape($s['staffid']) ?>"><?= html_escape($s['full_name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <?php } ?>
                  </div>
                  <?php } ?>

                  <div class="bio-viewing-label" id="bio_viewing_label">Viewing: <strong>Today</strong> (<?= html_escape(date('d-M-Y')) ?>)</div>
                  <div class="bio-empty-today" id="bio_empty_today">
                    <strong>No Biometric punches for today yet.</strong><br>
                    The office bridge may still be catching up, or nobody has punched today.
                    Use <em>Other day</em> to check yesterday, or wait ~2 minutes and refresh.
                  </div>
                </div>

            <div class="table-responsive mtop10 biometric-table-wrap" id="biometric_table_wrap">
              <div class="biometric-table-overlay" id="biometric_table_overlay">
                <div class="dt-loader"></div>
              </div>
              <table class="table table-bordered">
                <thead>
                  <tr>
					<th>S.No</th>
                    <th class="bio-col-date" style="display:none;">Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Emp ID</th>
                    <th>Name</th>
					<th>Status</th>
					<th>Work time</th>
					<th>Break</th>
					<th>Punches</th>	
                  </tr>
                </thead>
                <tbody id="attendance_tbody">
                  <?php if (!empty($initial_rows)) { ?>
                    <?php $sn = 1; foreach ($initial_rows as $row) {
                      $isAbsent = ($row['status'] ?? '') === 'A';
                      $btnClass = $isAbsent ? 'btn-danger' : 'btn-primary';
                      $btnLabel = $isAbsent ? 'Absent' : 'View';
                      $btnDisabled = $isAbsent ? 'disabled' : '';
                      $punch = htmlspecialchars($row['punch_records'] ?? '', ENT_QUOTES, 'UTF-8');
                      $date = htmlspecialchars($row['attendance_date'] ?? '', ENT_QUOTES, 'UTF-8');
                      $name = htmlspecialchars($row['employee_name'] ?? '', ENT_QUOTES, 'UTF-8');
                      // Out = last OUT only (ignore trailing IN)
                      $display_out = $row['a_out_time'] ?? '';
                      $punch_raw = (string) ($row['punch_records'] ?? '');
                      if ($punch_raw !== '' && preg_match_all('/(\d{1,2}:\d{2})\s*\(\s*(in|out)\s*\)/i', $punch_raw, $mm, PREG_SET_ORDER)) {
                          $last_out = '';
                          $last_type = '';
                          foreach ($mm as $p) {
                              $last_type = strtolower($p[2]);
                              if ($last_type === 'out') {
                                  $last_out = $p[1];
                              }
                          }
                          $display_out = ($last_type === 'in') ? $last_out : ($last_out !== '' ? $last_out : $display_out);
                      }
                    ?>
                    <tr>
                      <td><?= $sn++ ?></td>
                      <td class="bio-col-date" style="display:none;"><?= html_escape($row['attendance_date'] ?? '') ?></td>
                      <td><?= html_escape($row['a_in_time'] ?? '') ?></td>
                      <td><?= html_escape($display_out) ?></td>
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
                    <tr><td colspan="10" class="text-center text-muted">No Biometric attendance for today yet.</td></tr>
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
                <p class="bio-simple-sub mtop15">Individual Biometric punch swipes. Defaults to <strong>today</strong>.</p>
                <div class="row mtop10">
                  <div class="col-md-3">
                    <label>From date</label>
                    <input type="date" id="swipe_from" class="form-control" value="<?= html_escape(date('Y-m-d')) ?>">
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
                  <?php } elseif (!empty($can_filter_team)) { ?>
                  <div class="col-md-3">
                    <label>Employee</label>
                    <select id="swipe_staff" class="form-control">
                      <option value="">My Team</option>
                      <?php foreach (($team_staff ?? []) as $s): ?>
                        <option value="<?= html_escape($s['staffid']) ?>"><?= html_escape($s['full_name']) ?></option>
                      <?php endforeach; ?>
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
      <div class="modal-body" style="padding-bottom:16px;">
        <div id="punchRawList" class="mbot15" style="display:none;"></div>
        <div class="table-responsive">
        <table class="table table-bordered" id="punchTable" style="margin-bottom:0;">
          <thead>
            <tr>
              <th>In Time</th>
              <th>Out Time</th>
              <th>Duration</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        </div>
        <p id="punchModalHint" class="text-muted mtop10 mbot0" style="font-size:12px;"></p>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
let currentPage = 1;
const limit = 10;
let totalPages = <?= max(1, (int) ceil(((int) ($initial_total ?? 0)) / 10)) ?>;
let bioRange = '<?= html_escape($default_range ?? 'today') ?>';

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

function formatDisplayDay(ymd) {
  if (!ymd) return '';
  const parts = String(ymd).split('-');
  if (parts.length !== 3) return ymd;
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const m = parseInt(parts[1], 10);
  return parts[2] + '-' + (months[m - 1] || parts[1]) + '-' + parts[0];
}

/** Prefer last real OUT from punch_records (don't show a final IN as Out). */
function deriveOutFromPunches(punchStr, fallbackOut) {
  if (!punchStr) return fallbackOut || '';
  const entries = String(punchStr).split(',').map(function (e) { return e.trim(); }).filter(Boolean);
  let lastOut = '';
  let lastLabel = '';
  entries.forEach(function (entry) {
    const match = entry.match(/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i);
    if (!match) return;
    lastLabel = match[2].toLowerCase();
    if (lastLabel === 'out') {
      lastOut = match[1].substring(0, 5);
    }
  });
  if (lastLabel === 'in') {
    return lastOut || '';
  }
  return lastOut || (fallbackOut || '');
}

function updateBioRangeUI() {
  $('.bio-range-btn').removeClass('active');
  $('.bio-range-btn[data-range="' + bioRange + '"]').addClass('active');

  const needPanel = (bioRange === 'day' || bioRange === 'month');
  $('#bio_other_panel').toggleClass('is-open', needPanel);
  $('#wrap_filter_day').toggle(bioRange === 'day');
  $('#wrap_filter_month').toggle(bioRange === 'month');

  const todayYmd = '<?= date('Y-m-d') ?>';
  const todayNice = '<?= date('d M Y') ?>';
  let title = "Today's Biometric attendance";
  let sub = 'Showing only <strong>' + escapeHtml(todayNice) + '</strong> · Biometric punches';
  let label = 'Today (' + formatDisplayDay(todayYmd) + ')';

  if (bioRange === 'day') {
    const dayVal = $('#filter_day').val() || todayYmd;
    title = 'Biometric attendance';
    sub = 'Showing <strong>' + escapeHtml(formatDisplayDay(dayVal)) + '</strong> · Biometric punches';
    label = 'Day (' + formatDisplayDay(dayVal) + ')';
  } else if (bioRange === 'month') {
    const monthVal = $('#filter_month').val() || '<?= date('Y-m') ?>';
    title = 'This month — Biometric attendance';
    sub = 'Showing month <strong>' + escapeHtml(monthVal) + '</strong>';
    label = 'This month (' + monthVal + ')';
  }

  $('#bio_page_title').text(title);
  $('#bio_page_sub').html(sub);
  $('#bio_viewing_label').html('Viewing: <strong>' + escapeHtml(label) + '</strong>');

  // Hide Date column for single-day views (less clutter).
  const hideDate = (bioRange === 'today' || bioRange === 'day');
  $('.bio-col-date').toggle(!hideDate);
}

function getBioFilterPayload() {
  let department = $('#filter_department').length ? ($('#filter_department').val() || '') : '';
  let staff = $('#filter_staff').length ? ($('#filter_staff').val() || '') : '';
  if (department === '#' || department === 'all') department = '';
  if (staff === '#' || staff === 'all') staff = '';

  const payload = { limit, offset: (currentPage - 1) * limit, department, staff, range: bioRange, month: '', day: '' };
  const todayYmd = '<?= date('Y-m-d') ?>';
  const thisMonth = '<?= date('Y-m') ?>';

  if (bioRange === 'today') {
    payload.day = todayYmd;
    $('#filter_day').val(todayYmd);
  } else if (bioRange === 'day') {
    payload.day = $('#filter_day').val() || todayYmd;
  } else {
    payload.month = (bioRange === 'month')
      ? thisMonth
      : ($('#filter_month').val() || thisMonth);
    if (bioRange === 'month') {
      $('#filter_month').val(thisMonth);
    }
  }
  return payload;
}

function loadAttendanceData(page = 1) {
  currentPage = page;
  updateBioRangeUI();
  const data = getBioFilterPayload();
  data.offset = (page - 1) * limit;

  setBiometricLoading(true);
  $('#bio_empty_today').hide();

  $.ajax({
    url: "<?= admin_url('biometric/fetch_attendance_data') ?>",
    method: "GET",
    dataType: "json",
    data: data,
    timeout: 60000
  }).done(function(res) {
    setBiometricLoading(false);
    let rows = "";
    let i = data.offset + 1;
    const hideDate = (bioRange === 'today' || bioRange === 'day');
    const total = (res && res.total) || 0;

    if (bioRange === 'today' && total === 0) {
      $('#bio_empty_today').show();
    }

    if (res && res.data && res.data.length > 0) {
      res.data.forEach(row => {
        const isAbsent = row.status === 'A';
        const buttonClass = isAbsent ? 'btn-danger' : 'btn-primary';
        const buttonLabel = isAbsent ? 'Absent' : 'View';
        const buttonDisabled = isAbsent ? 'disabled' : '';
        const punchSafe = escapeHtml(row.punch_records || '').replace(/&#39;/g, "\\'");
        const dateSafe = escapeHtml(row.attendance_date || '');
        const nameSafe = escapeHtml(row.employee_name || '').replace(/&#39;/g, "\\'");
        rows += `<tr>
          <td>${i++}</td>
          <td class="bio-col-date"${hideDate ? ' style="display:none"' : ''}>${escapeHtml(row.attendance_date)}</td>
          <td>${escapeHtml(row.a_in_time)}</td>
          <td>${escapeHtml(deriveOutFromPunches(row.punch_records, row.a_out_time))}</td>
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
      const tip = bioRange === 'today'
        ? 'No Biometric attendance for <b>today</b> yet. Try <b>Other day</b> for yesterday.'
        : 'No Biometric records for this selection.';
      rows = `<tr><td colspan="10" class="text-center text-muted">${tip}</td></tr>`;
    }

    $("#attendance_tbody").html(rows);
    totalPages = Math.max(1, Math.ceil(total / limit));
    buildPagination();
    updateBioRangeUI();
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
  let html = '<ul class="pagination justify-content-center">';

  html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${currentPage - 1})">Previous</a>
  </li>`;

  // Windowed pages so long months don't overflow the desktop toolbar
  const windowSize = 2;
  let start = Math.max(1, currentPage - windowSize);
  let end = Math.min(totalPages, currentPage + windowSize);
  if (start > 1) {
    html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(1)">1</a></li>`;
    if (start > 2) {
      html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
  }
  for (let i = start; i <= end; i++) {
    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
      <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${i})">${i}</a>
    </li>`;
  }
  if (end < totalPages) {
    if (end < totalPages - 1) {
      html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
    html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${totalPages})">${totalPages}</a></li>`;
  }

  html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="event.preventDefault(); loadAttendanceData(${currentPage + 1})">Next</a>
  </li>`;

  html += '</ul>';
  $('#pagination').html(html);
}



$(document).ready(function() {
    updateBioRangeUI();
    buildPagination();
    <?php if (empty($initial_rows)) { ?>
    if (bioRange === 'today') {
      $('#bio_empty_today').show();
    }
    <?php } ?>

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
        if (bioRange === 'today' && !s.today_rows) {
          $('#bio_empty_today').show();
        }
      });
    }

    refreshSyncStatus();
    setInterval(refreshSyncStatus, 30000);

    // Auto-reload attendance table every 2 minutes while viewing Today
    // (matches Biometric bridge poll interval).
    setInterval(function () {
      if (document.hidden) return;
      if (bioRange !== 'today') return;
      // Don't yank the table while a punch modal is open.
      if ($('#punchModal').hasClass('in') || $('#punchModal').is(':visible')) return;
      loadAttendanceData(currentPage);
      refreshSyncStatus();
    }, 120000);
});
</script>
<script>
function showPunchModal(punchStr, date, empName) {
  $('#punchModal .modal-title').text(`Punch Details: ${empName} (${date})`);
  $('#punchTable tbody').empty();
  $('#punchRawList').hide().empty();
  $('#punchModalHint').text('');

  if (!punchStr || punchStr.trim() === "") {
    $('#punchTable tbody').append(`<tr><td colspan="3" class="text-center">No punch data found.</td></tr>`);
    $('#punchModal').modal('show');
    return;
  }

  const entries = punchStr.split(',').filter(e => e.trim() !== '');
  const punches = [];

  entries.forEach(entry => {
    entry = entry.trim();
    const match = entry.match(/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i);
    if (match) {
      let timeStr = match[1];
      if (timeStr.length === 4) timeStr = '0' + timeStr; // 9:05 -> 09:05
      if (timeStr.length === 5) timeStr = timeStr + ':00';
      punches.push({
        time: timeStr,
        type: match[2].toLowerCase(),
        minutes: timeToMinutes(timeStr)
      });
    }
  });

  // Full raw punch trail so nothing looks "hidden"
  if (punches.length) {
    const chips = punches.map(function (p) {
      const tone = p.type === 'in' ? '#166534' : '#9a3412';
      const bg = p.type === 'in' ? '#dcfce7' : '#ffedd5';
      return '<span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;background:'
        + bg + ';color:' + tone + ';font-size:12px;font-weight:650;">'
        + escapeHtml(p.time.substring(0, 5)) + ' ' + p.type.toUpperCase() + '</span>';
    }).join('');
    $('#punchRawList').html(
      '<div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px;">All punches from Biometric ('
      + punches.length + ')</div>' + chips
    ).show();
  }

  const workSessions = [];
  let totalMinutes = 0;
  let hasOpenSession = false;

  let i = 0;
  while (i < punches.length) {
    const current = punches[i];
    const next = punches[i + 1];

    if (current.type === 'in' && next && next.type === 'out') {
      const duration = next.minutes - current.minutes;
      totalMinutes += duration;
      workSessions.push({ inTime: current.time, outTime: next.time, duration: duration });
      $('#punchTable tbody').append(`
        <tr>
          <td>${current.time}</td>
          <td>${next.time}</td>
          <td>${formatDuration(duration)}</td>
        </tr>
      `);
      i += 2;
    } else if (current.type === 'in') {
      const isLast = (i === punches.length - 1);
      hasOpenSession = hasOpenSession || isLast;
      workSessions.push({ inTime: current.time, outTime: null });
      $('#punchTable tbody').append(`
        <tr class="${isLast ? '' : 'text-danger'}">
          <td>${current.time}</td>
          <td>${isLast ? '<span class="text-muted">Still inside</span>' : 'Missing'}</td>
          <td>${isLast ? '<span class="text-muted">Waiting for Out punch</span>' : 'Punch Missing'}</td>
        </tr>
      `);
      i += 1;
    } else if (current.type === 'out') {
      workSessions.push({ inTime: null, outTime: current.time });
      $('#punchTable tbody').append(`
        <tr class="text-danger">
          <td>Missing</td>
          <td>${current.time}</td>
          <td>Punch Missing</td>
        </tr>
      `);
      i += 1;
    } else {
      i += 1;
    }
  }

  if (workSessions.length === 0) {
    $('#punchTable tbody').append(`<tr><td colspan="3" class="text-center">No valid punch pairs found.</td></tr>`);
  } else {
    $('#punchTable tbody').append(`
      <tr style="font-weight:bold;">
        <td colspan="2" class="text-right">Total completed work</td>
        <td>${formatDuration(totalMinutes)}</td>
      </tr>
    `);
  }

  if (hasOpenSession) {
    $('#punchModalHint').text('Last punch is IN — Out is not in Biometric yet. After the next Out swipe, sync (every 2 min) will show the full session.');
  }

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




</script>

<script>
$('#applyFilters').on('click', function () {
  currentPage = 1;
  loadAttendanceData(currentPage);
});

$(document).on('click', '.bio-range-btn', function () {
  bioRange = $(this).data('range') || 'today';
  if (bioRange === 'today') {
    $('#filter_day').val('<?= date('Y-m-d') ?>');
  } else if (bioRange === 'month') {
    $('#filter_month').val('<?= date('Y-m') ?>');
  }
  updateBioRangeUI();
  // Today / This month apply immediately; Other day waits for Show
  if (bioRange === 'today' || bioRange === 'month') {
    currentPage = 1;
    loadAttendanceData(1);
  }
});

$('#filter_day, #filter_month').on('change', function () {
  updateBioRangeUI();
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
