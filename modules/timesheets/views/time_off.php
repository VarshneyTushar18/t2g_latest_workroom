<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.css?v=3.0.4">
<script type="text/javascript" src="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.js?v=3.0.4"></script>
<?php init_head(); ?>
<style>
  .time-off-page {
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --bg: #f1f5f9;
    --card: #fff;
    --radius: 16px;
  }
  .time-off-page .content { background: var(--bg); min-height: calc(100vh - 70px); }
  .to-shell { max-width: 1180px; margin: 0 auto; padding: 8px 4px 28px; }
  .to-title {
    font-size: 28px; font-weight: 700; color: var(--ink);
    margin: 8px 0 20px; letter-spacing: -0.02em;
  }
  .to-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 16px;
    align-items: start;
  }
  @media (max-width: 991px) {
    .to-layout { grid-template-columns: 1fr; }
  }
  .to-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 16px 16px 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
    margin-bottom: 14px;
  }
  .to-card h3 {
    margin: 0 0 14px;
    font-size: 15px;
    font-weight: 700;
    color: var(--ink);
  }
  .to-balance-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    padding: 8px 0;
  }
  .to-balance-row + .to-balance-row { border-top: 1px solid #f1f5f9; }
  .to-balance-row .name { font-size: 14px; color: var(--ink); font-weight: 600; }
  .to-balance-row .avail { font-size: 14px; color: var(--ink); font-weight: 700; white-space: nowrap; }
  .to-balance-row .avail span { color: var(--muted); font-weight: 500; font-size: 12px; }
  .to-actions { display: flex; gap: 8px; margin-top: 14px; }
  .to-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: #f8fafc;
    color: var(--ink) !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 650;
    padding: 9px 12px;
  }
  .to-btn.primary {
    background: #141e46;
    border-color: #141e46;
    color: #fff !important;
  }
  .to-req {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    text-decoration: none !important;
    color: inherit !important;
  }
  .to-req + .to-req { border-top: 1px solid #f1f5f9; }
  .to-req .ico {
    width: 34px; height: 34px; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    background: #dbeafe; color: #2563eb; flex: 0 0 auto;
  }
  .to-req .ico.warn { background: #fce7f3; color: #be185d; }
  .to-req .meta { flex: 1; min-width: 0; }
  .to-req .date { font-size: 14px; font-weight: 700; color: var(--ink); }
  .to-req .sub { font-size: 12px; color: var(--muted); margin-top: 2px; }
  .to-pill {
    font-size: 11px; font-weight: 700; color: #475569;
    background: #f1f5f9; border-radius: 999px; padding: 4px 10px;
    white-space: nowrap;
  }
  .to-pill.pending { background: #fef3c7; color: #92400e; }
  .to-pill.rejected { background: #fee2e2; color: #991b1b; }
  .to-empty { color: var(--muted); font-size: 13px; margin: 0; }
  .to-cal-wrap { min-height: 560px; }
  .to-cal-toolbar {
    display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
  }
  .to-cal-toolbar .nav-btn {
    border: 1px solid var(--line); background: #f8fafc; border-radius: 999px;
    width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
  }
  .to-cal-toolbar .today-btn {
    border: 1px solid var(--line); background: #f8fafc; border-radius: 999px;
    padding: 6px 12px; font-size: 12px; font-weight: 650; cursor: pointer;
  }
  .to-cal-toolbar .month-label {
    margin-left: 6px; font-size: 16px; font-weight: 700; color: var(--ink);
  }
  #timeOffCalendar .fc { font-size: 13px; }
  #timeOffCalendar .fc-toolbar { display: none; }
  #timeOffCalendar .fc-daygrid-day-frame { min-height: 64px; }
</style>

<div id="wrapper" class="time-off-page">
  <div class="content">
    <div class="to-shell">
      <h1 class="to-title">Time off</h1>

      <div class="to-layout">
        <div class="to-left">
          <div class="to-card">
            <h3>Time off balances</h3>
            <?php if (empty($balance_rows)) { ?>
              <p class="to-empty">No leave balances found.</p>
            <?php } else { ?>
              <?php foreach ($balance_rows as $row) { ?>
                <div class="to-balance-row">
                  <div class="name"><?php echo html_escape($row['label']); ?></div>
                  <div class="avail"><?php echo html_escape($row['available']); ?> <span>days available</span></div>
                </div>
              <?php } ?>
            <?php } ?>
            <div class="to-actions">
              <a class="to-btn" href="<?php echo html_escape($balance_url); ?>">View all</a>
              <a class="to-btn primary" href="<?php echo html_escape($apply_url); ?>">Request time off</a>
            </div>
          </div>

          <div class="to-card">
            <h3>Time off requests</h3>
            <?php if (empty($request_rows)) { ?>
              <p class="to-empty">No leave requests yet.</p>
            <?php } else { ?>
              <?php foreach ($request_rows as $req) {
                $icoClass = ($req['status_code'] === '2' || stripos($req['subtitle'], 'sick') !== false) ? 'ico warn' : 'ico';
                $pillClass = 'to-pill';
                if ($req['status_code'] === '0') $pillClass .= ' pending';
                if ($req['status_code'] === '2') $pillClass .= ' rejected';
              ?>
                <a class="to-req" href="<?php echo html_escape($req['href']); ?>">
                  <span class="<?php echo $icoClass; ?>"><i class="fa fa-calendar"></i></span>
                  <div class="meta">
                    <div class="date"><?php echo html_escape($req['date_label']); ?></div>
                    <div class="sub"><?php echo html_escape($req['subtitle']); ?></div>
                  </div>
                  <span class="<?php echo $pillClass; ?>"><?php echo html_escape($req['status']); ?></span>
                </a>
              <?php } ?>
            <?php } ?>
          </div>
        </div>

        <div class="to-card" style="margin-bottom:0;">
          <div class="to-cal-toolbar">
            <button type="button" class="today-btn" id="toCalToday">Today</button>
            <button type="button" class="nav-btn" id="toCalPrev"><i class="fa fa-chevron-left"></i></button>
            <button type="button" class="nav-btn" id="toCalNext"><i class="fa fa-chevron-right"></i></button>
            <div class="month-label" id="toCalLabel"></div>
          </div>
          <div class="to-cal-wrap">
            <div id="timeOffCalendar"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function () {
  var events = <?= json_encode($calendar_events ?? []) ?>;
  var monthYear = <?= json_encode($month_year ?? date('Y-m')) ?>;
  var calEl = document.getElementById('timeOffCalendar');
  if (!calEl || typeof FullCalendar === 'undefined') return;

  var cal = new FullCalendar.Calendar(calEl, {
    initialView: 'dayGridMonth',
    initialDate: monthYear + '-01',
    height: 'auto',
    headerToolbar: false,
    fixedWeekCount: false,
    events: events,
    datesSet: function (info) {
      var d = info.view.currentStart;
      var label = d.toLocaleString(undefined, { month: 'long', year: 'numeric' });
      var el = document.getElementById('toCalLabel');
      if (el) el.textContent = label;
    }
  });
  cal.render();

  document.getElementById('toCalToday').addEventListener('click', function () { cal.today(); });
  document.getElementById('toCalPrev').addEventListener('click', function () { cal.prev(); });
  document.getElementById('toCalNext').addEventListener('click', function () { cal.next(); });
})();
</script>
</body>
</html>
