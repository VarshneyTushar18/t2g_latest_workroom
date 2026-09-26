<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$greeting_label = $greeting_label ?? 'Hello';
$staff_first_name = $staff_first_name ?? 'there';
$hrms_quotes = !empty($hrms_quotes) && is_array($hrms_quotes) ? array_values($hrms_quotes) : [
  'Small daily improvements are the key to staggering long-term results.',
];
// One quote per calendar day (changes every 24h at midnight).
$hrms_quote_of_day = $hrms_quotes[((int) date('z')) % max(1, count($hrms_quotes))];
$upcoming_holidays = $upcoming_holidays ?? [];
$holiday_dates = $holiday_dates ?? [];
$holiday_calendar_url = $holiday_calendar_url ?? admin_url('holiday/calendar');
$server_now = $server_now ?? date('c');
$biometric_checkin_label = $biometric_checkin_label ?? '';
?>
<style>
  .hrms-home {
    --hrms-ink: #0f172a;
    --hrms-muted: #64748b;
    --hrms-line: #e2e8f0;
    --hrms-bg: #f1f5f9;
    --hrms-card: #ffffff;
    --hrms-accent: #141e46;
    --hrms-blue: #2563eb;
    --hrms-amber: #d97706;
    --hrms-teal: #0f766e;
    --hrms-radius: 16px;
  }
  .hrms-home .content { background: var(--hrms-bg); min-height: calc(100vh - 70px); }
  .hrms-shell {
    max-width: 1180px;
    margin: 0 auto;
    padding: 8px 4px 32px;
  }
  .hrms-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin: 8px 0 22px;
    min-height: 52px;
  }
  .hrms-greeting {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--hrms-ink);
    letter-spacing: -0.02em;
    line-height: 1.2;
    flex: 0 1 auto;
    min-width: 0;
  }
  .hrms-greeting-name { color: var(--hrms-accent); }
  .hrms-quote-wrap {
    flex: 1 1 420px;
    max-width: 560px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    min-width: 0;
  }
  .hrms-quote-icon {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #e0e7ff;
    color: #4338ca;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 13px;
  }
  .hrms-quote {
    margin: 0;
    color: var(--hrms-muted);
    font-size: 13.5px;
    font-style: italic;
    line-height: 1.45;
    text-align: right;
    max-width: 100%;
    opacity: 1;
    transition: opacity .35s ease;
  }
  .hrms-quote.is-fading { opacity: 0; }
  @media (max-width: 900px) {
    .hrms-hero {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    .hrms-quote-wrap {
      max-width: 100%;
      width: 100%;
      justify-content: flex-start;
    }
    .hrms-quote { text-align: left; }
  }
  .hrms-panel {
    background: var(--hrms-card);
    border: 1px solid var(--hrms-line);
    border-radius: var(--hrms-radius);
    padding: 18px 18px 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  }
  .hrms-panel-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 14px;
    color: var(--hrms-ink);
    font-size: 15px;
    font-weight: 700;
  }
  .hrms-panel-head i { color: var(--hrms-muted); }
  .hrms-panel-head a {
    margin-left: auto;
    color: var(--hrms-muted);
    text-decoration: none !important;
    font-size: 14px;
  }
  .hrms-panel-head a:hover { color: var(--hrms-blue); }
  .hrms-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }
  @media (max-width: 767px) {
    .hrms-actions { grid-template-columns: 1fr 1fr; }
  }
  .hrms-action {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    min-height: 112px;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid var(--hrms-line);
    background: #fff;
    text-decoration: none !important;
    color: var(--hrms-ink) !important;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
  }
  .hrms-action:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
  }
  .hrms-action-icon {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }
  .hrms-action-icon.blue { background: #dbeafe; color: var(--hrms-blue); }
  .hrms-action-icon.amber { background: #fef3c7; color: var(--hrms-amber); }
  .hrms-action-icon.teal { background: #ccfbf1; color: var(--hrms-teal); }
  .hrms-action-icon.slate { background: #e2e8f0; color: #334155; }
  .hrms-action span {
    font-size: 14px;
    font-weight: 650;
    line-height: 1.3;
  }

  /* Three cards under Quick actions */
  .hrms-cards-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-top: 18px;
    align-items: stretch;
  }
  @media (max-width: 991px) {
    .hrms-cards-row { grid-template-columns: 1fr; }
  }
  .hrms-card {
    background: var(--hrms-card);
    border: 1px solid var(--hrms-line);
    border-radius: var(--hrms-radius);
    padding: 16px 16px 14px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    min-height: 260px;
    display: flex;
    flex-direction: column;
  }
  .hrms-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 14px;
    color: var(--hrms-ink);
    font-size: 15px;
    font-weight: 700;
  }
  .hrms-card-title i { color: var(--hrms-muted); }
  .hrms-card-title a {
    margin-left: auto;
    color: var(--hrms-muted);
    text-decoration: none !important;
  }
  .hrms-card-title a:hover { color: var(--hrms-blue); }

  /* Upcoming holidays */
  .hrms-holiday-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
  }
  .hrms-holiday-list li {
    display: grid;
    grid-template-columns: 72px 1fr;
    gap: 10px;
    align-items: baseline;
  }
  .hrms-holiday-date {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    font-variant-numeric: tabular-nums;
  }
  .hrms-holiday-day {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: var(--hrms-muted);
  }
  .hrms-holiday-name {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--hrms-ink);
    line-height: 1.35;
  }
  .hrms-empty {
    color: var(--hrms-muted);
    font-size: 13px;
    margin: 8px 0 0;
  }

  /* Live clock (no punch button) */
  .hrms-clock-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #ffffff 0%, #f8fafc 55%, #eef2ff 100%);
  }
  .hrms-clock-deco {
    position: absolute;
    right: -18px;
    top: -18px;
    width: 110px;
    height: 110px;
    border-radius: 999px;
    background: rgba(45, 212, 191, 0.22);
    pointer-events: none;
  }
  .hrms-clock-deco::after {
    content: '';
    position: absolute;
    right: 34px;
    top: 10px;
    width: 14px;
    height: 14px;
    border-radius: 999px;
    background: rgba(244, 114, 182, 0.55);
  }
  .hrms-clock-date {
    margin: 4px 0 0;
    font-size: 15px;
    font-weight: 650;
    color: #334155;
  }
  .hrms-clock-meta {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--hrms-muted);
  }
  .hrms-live-clock {
    margin-top: 28px;
    font-size: 34px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--hrms-ink);
    font-variant-numeric: tabular-nums;
    line-height: 1;
  }
  .hrms-live-clock .sep {
    opacity: 0.45;
    margin: 0 2px;
    animation: hrmsBlink 1s steps(1) infinite;
  }
  @keyframes hrmsBlink {
    50% { opacity: 0.15; }
  }
  .hrms-swipes-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: auto;
    padding: 9px 14px;
    border-radius: 10px;
    background: #0f172a;
    color: #fff !important;
    font-size: 13px;
    font-weight: 650;
    text-decoration: none !important;
    border: 0;
    cursor: pointer;
    width: fit-content;
    transition: background .15s ease, transform .15s ease;
  }
  .hrms-swipes-btn:hover {
    background: #1e293b;
    color: #fff !important;
    transform: translateY(-1px);
  }
  .hrms-swipes-btn i {
    opacity: 0.9;
  }
  #hrmsSwipesModal .modal-dialog {
    width: 920px;
    max-width: 96vw;
  }
  #hrmsSwipesModal .table-responsive {
    max-height: 55vh;
    overflow: auto;
  }
  #hrmsSwipesModal .label-in { background: #16a34a; }
  #hrmsSwipesModal .label-out { background: #d97706; }

  /* Attendance calendar (same codes as My Attendance) */
  .hrms-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  .hrms-cal-nav button {
    border: 1px solid var(--hrms-line);
    background: #fff;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    color: #475569;
    cursor: pointer;
  }
  .hrms-cal-nav button:hover { background: #f8fafc; }
  .hrms-cal-month {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--hrms-ink);
  }
  .hrms-cal-weekdays,
  .hrms-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 3px;
  }
  .hrms-cal-dow {
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--hrms-muted);
    padding: 2px 0 6px;
    text-transform: uppercase;
  }
  .hrms-cal-day {
    min-height: 46px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 3px 4px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    position: relative;
  }
  .hrms-cal-day.muted {
    opacity: 0.35;
    cursor: default;
    background: #f8fafc;
  }
  .hrms-cal-num {
    font-size: 11px;
    font-weight: 650;
    color: #334155;
    line-height: 1.2;
  }
  .hrms-cal-code {
    margin-top: 2px;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.1;
  }
  .hrms-cal-code.code-ok { color: #15803d; }
  .hrms-cal-code.code-half { color: #c2410c; }
  .hrms-cal-code.code-bad { color: #dc2626; }
  .hrms-cal-code.code-leave { color: #7c3aed; }
  .hrms-cal-code.code-off { color: #64748b; }
  .hrms-cal-code.code-holiday { color: #0369a1; }
  .hrms-cal-day.tone-ok { background: #dcfce7; border-color: #86efac; }
  .hrms-cal-day.tone-half { background: #ffedd5; border-color: #fdba74; }
  .hrms-cal-day.tone-bad { background: #fee2e2; border-color: #fca5a5; }
  .hrms-cal-day.tone-leave { background: #f3e8ff; border-color: #d8b4fe; }
  .hrms-cal-day.tone-holiday { background: #e0f2fe; border-color: #7dd3fc; }
  .hrms-cal-day.tone-off { background: #f8fafc; border-color: #e2e8f0; }
  .hrms-cal-day.today .hrms-cal-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
  }
  .hrms-cal-legend {
    margin-top: 10px;
    font-size: 11px;
    color: var(--hrms-muted);
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .hrms-cal-legend span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .hrms-cal-legend i {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    border: 1px solid transparent;
  }
  .hrms-cal-legend .lg-ok { background: #dcfce7; border-color: #86efac; }
  .hrms-cal-legend .lg-half { background: #ffedd5; border-color: #fdba74; }
  .hrms-cal-legend .lg-bad { background: #fee2e2; border-color: #fca5a5; }
  .hrms-cal-legend .lg-leave { background: #f3e8ff; border-color: #d8b4fe; }
  .hrms-cal-legend .lg-off { background: #f8fafc; border-color: #e2e8f0; }
  .hrms-cal-legend .lg-holiday { background: #e0f2fe; border-color: #7dd3fc; }
  .hrms-cal-footer {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
  }
  .hrms-cal-footer a {
    font-size: 12px;
    font-weight: 650;
  }
</style>

<div id="wrapper" class="hrms-home">
  <div class="content">
    <div class="hrms-shell">
      <div class="hrms-hero">
        <h1 class="hrms-greeting">
          <?php echo html_escape($greeting_label); ?>,
          <span class="hrms-greeting-name"><?php echo html_escape($staff_first_name); ?></span>
        </h1>
        <div class="hrms-quote-wrap" aria-live="polite">
          <span class="hrms-quote-icon" title="Daily quote"><i class="fa fa-quote-left"></i></span>
          <p id="hrmsAutoQuote" class="hrms-quote"><?php echo html_escape($hrms_quote_of_day); ?></p>
        </div>
      </div>

      <div class="hrms-panel">
        <div class="hrms-panel-head"><i class="fa fa-bolt"></i> Quick actions</div>
        <div class="hrms-actions">
          <?php foreach (($quick_actions ?? []) as $action) { ?>
            <a class="hrms-action" href="<?php echo html_escape($action['href']); ?>">
              <span class="hrms-action-icon <?php echo html_escape($action['tone']); ?>">
                <i class="fa <?php echo html_escape($action['icon']); ?>"></i>
              </span>
              <span><?php echo html_escape($action['label']); ?></span>
            </a>
          <?php } ?>
        </div>
      </div>

      <div class="hrms-cards-row">
        <!-- 1. Upcoming holidays -->
        <div class="hrms-card">
          <div class="hrms-card-title">
            <i class="fa fa-umbrella"></i> Upcoming Holidays
            <a href="<?php echo html_escape($holiday_calendar_url); ?>" title="Open holiday calendar">
              <i class="fa fa-angle-right"></i>
            </a>
          </div>
          <?php if (!empty($upcoming_holidays)) { ?>
            <ul class="hrms-holiday-list">
              <?php foreach ($upcoming_holidays as $h) { ?>
                <li>
                  <div class="hrms-holiday-date">
                    <?php echo html_escape($h['label']); ?>
                    <span class="hrms-holiday-day"><?php echo html_escape($h['day']); ?></span>
                  </div>
                  <div class="hrms-holiday-name"><?php echo html_escape($h['name']); ?></div>
                </li>
              <?php } ?>
            </ul>
          <?php } else { ?>
            <p class="hrms-empty">No upcoming holidays found.</p>
          <?php } ?>
        </div>

        <!-- 2. Live clock -->
        <div class="hrms-card hrms-clock-card">
          <div class="hrms-clock-deco" aria-hidden="true"></div>
          <div class="hrms-card-title"><i class="fa fa-clock-o"></i> Time &amp; Attendance</div>
          <p class="hrms-clock-date" id="hrmsClockDate">—</p>
          <p class="hrms-clock-meta" id="hrmsClockMeta">—</p>
          <div class="hrms-live-clock" id="hrmsLiveClock" aria-live="polite">
            <span data-part="h">00</span><span class="sep">:</span><span data-part="m">00</span><span class="sep">:</span><span data-part="s">00</span>
          </div>
          <button type="button" class="hrms-swipes-btn" id="hrmsViewSwipesBtn">
            <i class="fa fa-id-badge"></i> View all biometric swipes
          </button>
        </div>

        <!-- 3. Attendance calendar (same as My Attendance) -->
        <div class="hrms-card">
          <div class="hrms-card-title"><i class="fa fa-calendar"></i> Attendance calendar</div>
          <div class="hrms-cal-nav">
            <button type="button" id="hrmsCalPrev" aria-label="Previous month"><i class="fa fa-angle-left"></i></button>
            <div class="hrms-cal-month" id="hrmsCalMonthLabel">—</div>
            <button type="button" id="hrmsCalNext" aria-label="Next month"><i class="fa fa-angle-right"></i></button>
          </div>
          <div class="hrms-cal-weekdays">
            <div class="hrms-cal-dow">Su</div>
            <div class="hrms-cal-dow">Mo</div>
            <div class="hrms-cal-dow">Tu</div>
            <div class="hrms-cal-dow">We</div>
            <div class="hrms-cal-dow">Th</div>
            <div class="hrms-cal-dow">Fr</div>
            <div class="hrms-cal-dow">Sa</div>
          </div>
          <div class="hrms-cal-grid" id="hrmsCalGrid">
            <div style="grid-column:1/-1;padding:18px;text-align:center;color:#64748b;font-size:12px;">
              <i class="fa fa-spinner fa-spin"></i> Loading attendance…
            </div>
          </div>
          <div class="hrms-cal-legend">
            <span><i class="lg-ok"></i> Present</span>
            <span><i class="lg-half"></i> Half</span>
            <span><i class="lg-bad"></i> Absent</span>
            <span><i class="lg-leave"></i> Leave</span>
            <span><i class="lg-holiday"></i> Holiday</span>
            <span><i class="lg-off"></i> Off</span>
          </div>
          <div class="hrms-cal-footer">
            <a href="<?php echo admin_url('timesheets/my_attendance'); ?>">Open full attendance →</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="hrmsSwipesModal" tabindex="-1" role="dialog" aria-labelledby="hrmsSwipesModalTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="hrmsSwipesModalTitle">Biometric swipes</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="margin-top:0;margin-bottom:12px;font-size:13px;">Showing today’s biometric swipes only.</p>
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="hrmsSwipesTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>In/Out</th>
                <th>Shift</th>
                <th>Door</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="hrmsSwipesBody">
              <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <a href="<?php echo admin_url('biometric?tab=swipes'); ?>" class="btn btn-default">Open full biometric page</a>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function () {
  // Quote is fixed for the calendar day (server picks one per 24h). No mid-day rotate.

  // Live clock — no punch button
  var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  var clockRoot = document.getElementById('hrmsLiveClock');
  var dateEl = document.getElementById('hrmsClockDate');
  var metaEl = document.getElementById('hrmsClockMeta');
  var serverIso = <?php echo json_encode($server_now); ?>;
  var offsetMs = 0;
  try {
    offsetMs = new Date(serverIso).getTime() - Date.now();
  } catch (e) {
    offsetMs = 0;
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function ymd(d) {
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }
  function tickClock() {
    var now = new Date(Date.now() + offsetMs);
    if (dateEl) {
      dateEl.textContent = pad(now.getDate()) + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    }
    if (metaEl) {
      metaEl.textContent = days[now.getDay()];
    }
    if (clockRoot) {
      clockRoot.querySelector('[data-part="h"]').textContent = pad(now.getHours());
      clockRoot.querySelector('[data-part="m"]').textContent = pad(now.getMinutes());
      clockRoot.querySelector('[data-part="s"]').textContent = pad(now.getSeconds());
    }
  }
  tickClock();
  setInterval(tickClock, 1000);

  // Attendance calendar (same data/codes as My Attendance)
  var attStaffId = <?php echo (int) get_staff_user_id(); ?>;
  var calView = new Date(Date.now() + offsetMs);
  calView.setDate(1);
  var grid = document.getElementById('hrmsCalGrid');
  var monthLabel = document.getElementById('hrmsCalMonthLabel');
  var dayMap = {};
  var calLoading = false;

  function monthKey(d) {
    return d.getFullYear() + '-' + pad(d.getMonth() + 1);
  }

  function parseHrs(val) {
    if (!val || val === '—') return null;
    var n = parseFloat(String(val).replace('h', ''));
    return isNaN(n) ? null : n;
  }

  function dayHours(day) {
    if (!day) return 0;
    if (typeof day.hours === 'number' && day.hours > 0) return day.hours;
    var aw = parseHrs(day.actual_work_hrs);
    if (aw !== null) return aw;
    return parseHrs(day.total_work_hrs) || 0;
  }

  function dayHealthTone(day) {
    if (!day || day.status === 'future') return 'neutral';
    var code = day.code || '';
    if (day.status === 'weekend' || code === 'O') return 'off';
    if (day.status === 'holiday' || code === 'HO' || code === 'H') return 'holiday';
    if (day.status === 'leave' || day.status === 'saturday_leave') return 'leave';
    if (['EL', 'PL', 'L', 'SL', 'UL', 'LOP', 'CO', 'MAL', 'PHD', 'UHD', 'PAL', 'SHL'].indexOf(code) >= 0) return 'leave';
    if (day.status === 'regularised') return 'ok';
    if (day.status === 'pending') return 'neutral';
    var presentMin = day.present_min_hours || 8.0;
    var halfMin = day.half_day_min_hours || 5;
    var hrs = dayHours(day);
    if (day.status === 'absent' || day.status === 'punch_missing' || code === 'AB' || code === 'A') return 'bad';
    if (day.status === 'rejected') {
      if (hrs + 0.001 >= presentMin) return 'ok';
      if (hrs + 0.001 >= halfMin) return 'half';
      return 'bad';
    }
    if (day.status === 'half_day' || code === 'HD') return 'half';
    if (hrs + 0.001 >= presentMin || day.status === 'ok' || code === 'P') {
      return hrs + 0.001 >= presentMin ? 'ok' : (hrs + 0.001 >= halfMin ? 'half' : (hrs > 0 ? 'bad' : 'neutral'));
    }
    if (day.status === 'short_hours') return hrs + 0.001 >= halfMin ? 'half' : 'bad';
    if (hrs + 0.001 >= presentMin) return 'ok';
    if (hrs + 0.001 >= halfMin) return 'half';
    if (hrs > 0) return 'bad';
    return 'neutral';
  }

  function dayCodeClass(tone) {
    if (tone === 'ok') return 'code-ok';
    if (tone === 'half') return 'code-half';
    if (tone === 'bad') return 'code-bad';
    if (tone === 'leave') return 'code-leave';
    if (tone === 'holiday') return 'code-holiday';
    if (tone === 'off') return 'code-off';
    return '';
  }

  function dayCodeLabel(code) {
    if (code === 'AB') return 'A';
    return code || '';
  }

  function renderAttendanceCalendar() {
    if (!grid || !monthLabel) return;
    var y = calView.getFullYear();
    var m = calView.getMonth();
    monthLabel.textContent = months[m] + ' ' + y;
    var firstDow = new Date(y, m, 1).getDay();
    var daysInMonth = new Date(y, m + 1, 0).getDate();
    var prevDays = new Date(y, m, 0).getDate();
    var todayKey = ymd(new Date(Date.now() + offsetMs));
    var html = '';
    for (var i = 0; i < firstDow; i++) {
      html += '<div class="hrms-cal-day muted"><span class="hrms-cal-num">' + (prevDays - firstDow + i + 1) + '</span></div>';
    }
    for (var day = 1; day <= daysInMonth; day++) {
      var key = y + '-' + pad(m + 1) + '-' + pad(day);
      var d = dayMap[key];
      var tone = d ? dayHealthTone(d) : 'neutral';
      var cls = 'hrms-cal-day tone-' + tone;
      if (key === todayKey) cls += ' today';
      html += '<div class="' + cls + '" data-date="' + key + '">';
      html += '<span class="hrms-cal-num">' + day + '</span>';
      if (d && d.code) {
        html += '<span class="hrms-cal-code ' + dayCodeClass(tone) + '">' + dayCodeLabel(d.code) + '</span>';
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
    if (!grid || calLoading) return;
    calLoading = true;
    grid.innerHTML = '<div style="grid-column:1/-1;padding:18px;text-align:center;color:#64748b;font-size:12px;"><i class="fa fa-spinner fa-spin"></i> Loading attendance…</div>';
    $.getJSON(admin_url + 'timesheets/my_attendance_calendar', {
      month: monthKey(calView),
      staff_id: attStaffId
    }).done(function (res) {
      dayMap = {};
      (res.days || []).forEach(function (d) { dayMap[d.date] = d; });
      renderAttendanceCalendar();
    }).fail(function () {
      grid.innerHTML = '<div style="grid-column:1/-1;padding:18px;text-align:center;color:#dc2626;font-size:12px;">Could not load attendance calendar.</div>';
    }).always(function () {
      calLoading = false;
    });
  }

  var prevBtn = document.getElementById('hrmsCalPrev');
  var nextBtn = document.getElementById('hrmsCalNext');
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
  if (grid) {
    grid.addEventListener('click', function (e) {
      var cell = e.target.closest('.hrms-cal-day[data-date]');
      if (!cell || cell.classList.contains('muted')) return;
      var ds = cell.getAttribute('data-date');
      var my = ds ? ds.slice(0, 7) : monthKey(calView);
      window.location.href = admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(my);
    });
  }
  loadAttendanceCalendar();

  // Biometric swipes popup — today only, no date filters
  var swipeStaffId = <?php echo (int) get_staff_user_id(); ?>;
  var swipeBtn = document.getElementById('hrmsViewSwipesBtn');
  var swipeBody = document.getElementById('hrmsSwipesBody');

  function escHtml(v) {
    return $('<div>').text(v == null ? '' : String(v)).html();
  }

  function loadHrmsSwipes() {
    if (!swipeBody) return;
    var today = ymd(new Date(Date.now() + offsetMs));
    swipeBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>';
    $.getJSON(admin_url + 'biometric/fetch_swipes', {
      from: today,
      to: today,
      staff: swipeStaffId
    }).done(function (res) {
      var rows = (res && res.data) ? res.data : [];
      if (!rows.length) {
        swipeBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No swipes found for today.</td></tr>';
        return;
      }
      var html = '';
      rows.forEach(function (r) {
        var io = String(r.in_out || '').toUpperCase();
        var badge = io === 'IN' ? 'label-in' : 'label-out';
        html += '<tr>' +
          '<td>' + escHtml(r.swipe_date || '') + '</td>' +
          '<td>' + escHtml(r.swipe_time || '') + '</td>' +
          '<td><span class="label ' + badge + '">' + escHtml(io || '-') + '</span></td>' +
          '<td>' + escHtml(r.shift || '-') + '</td>' +
          '<td>' + escHtml(r.door || '-') + '</td>' +
          '<td>' + escHtml(r.status || '-') + '</td>' +
          '</tr>';
      });
      swipeBody.innerHTML = html;
    }).fail(function () {
      swipeBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Could not load swipes.</td></tr>';
    });
  }

  if (swipeBtn) {
    swipeBtn.addEventListener('click', function () {
      $('#hrmsSwipesModal').modal('show');
      loadHrmsSwipes();
    });
  }
})();
</script>
</body>
</html>
