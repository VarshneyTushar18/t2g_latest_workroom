<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$report = $report ?? null;
if (!$report) {
    echo '<p class="text-muted mbot0">No leave details available.</p>';
    return;
}

if (!function_exists('leave_detail_fmt_num')) {
    function leave_detail_fmt_num($value)
    {
        $value = (float) $value;
        if (abs($value - round($value)) < 0.001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}

$s = $report['summary'];
$max_chart = 0.1;
foreach ($report['monthly'] as $m) {
  if (empty($m['is_elapsed'])) {
    continue;
  }
  $max_chart = max($max_chart, (float) ($m['granted'] ?? 0), (float) $m['consumed'], (float) ($m['balance'] ?? 0));
}
$has_chart_data = false;
foreach ($report['monthly'] as $m) {
  if (!empty($m['is_elapsed']) && ((float) $m['consumed'] > 0 || (float) ($m['balance'] ?? 0) > 0)) {
    $has_chart_data = true;
    break;
  }
}
?>
<style>
  .ld-panel { font-size: 13px; color: #1e293b; }
  .ld-summary { display: flex; flex-wrap: wrap; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 18px; }
  .ld-summary-item { flex: 1; min-width: 110px; padding: 12px 14px; border-right: 1px solid #e2e8f0; background: #fff; }
  .ld-summary-item:last-child { border-right: none; }
  .ld-summary-item span { display: block; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
  .ld-summary-item strong { font-size: 20px; font-weight: 700; color: #0f172a; }
  .ld-chart-wrap { border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 12px 8px; margin-bottom: 18px; background: #fff; }
  .ld-chart-title { font-weight: 700; font-size: 14px; margin: 0 0 12px; color: #0f172a; }
  .ld-chart-legend { display: flex; gap: 16px; font-size: 11px; color: #64748b; margin-bottom: 10px; }
  .ld-chart-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }
  .ld-chart-legend .lg-bal { background: #93c5fd; }
  .ld-chart-legend .lg-con { background: #f9a8d4; }
  .ld-chart-bars { display: flex; align-items: flex-end; gap: 6px; height: 120px; padding-top: 8px; overflow-x: auto; }
  .ld-chart-col { flex: 1; min-width: 36px; text-align: center; }
  .ld-chart-col .bars { display: flex; align-items: flex-end; justify-content: center; gap: 2px; height: 90px; }
  .ld-chart-col .bar { width: 10px; border-radius: 2px 2px 0 0; min-height: 2px; }
  .ld-chart-col .bar.bal { background: #93c5fd; }
  .ld-chart-col .bar.con { background: #f9a8d4; }
  .ld-chart-col label { display: block; font-size: 9px; color: #64748b; margin-top: 6px; white-space: nowrap; }
  .ld-tx-table { font-size: 12px; }
  .ld-tx-table th { background: #f8fafc; font-size: 11px; white-space: nowrap; }
  .ld-tx-type { font-weight: 600; }
  .ld-tx-type.availed { color: #2563eb; }
  .ld-tx-type.pending { color: #d97706; }
  .ld-tx-type.rejected, .ld-tx-type.absent { color: #dc2626; }
  .ld-tx-type.granted { color: #059669; }
  .ld-tx-type.carry-forward { color: #7c3aed; }
  .ld-chart-col.future label { color: #cbd5e1; }
  .ld-chart-col.future .bars { opacity: .35; }
  .ld-section-title { font-weight: 700; font-size: 14px; margin: 0 0 10px; color: #0f172a; }
  .ld-month-table { font-size: 12px; margin-bottom: 18px; }
  .ld-month-table th { background: #f8fafc; font-size: 11px; white-space: nowrap; }
  .ld-month-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
</style>

<div class="ld-panel">
  <div class="ld-summary">
    <div class="ld-summary-item"><span>Available Balance</span><strong><?php echo leave_detail_fmt_num($s['available_balance']); ?></strong></div>
    <div class="ld-summary-item"><span>Opening Balance</span><strong><?php echo leave_detail_fmt_num($s['opening_balance']); ?></strong></div>
    <div class="ld-summary-item"><span>Granted</span><strong><?php echo leave_detail_fmt_num($s['granted']); ?></strong></div>
    <div class="ld-summary-item"><span>Availed</span><strong><?php echo leave_detail_fmt_num($s['availed']); ?></strong></div>
    <div class="ld-summary-item"><span>Lapsed</span><strong><?php echo leave_detail_fmt_num($s['lapsed']); ?></strong></div>
  </div>

  <?php if ($has_chart_data) { ?>
  <div class="ld-chart-wrap">
    <div class="ld-chart-title"><?php echo html_escape($report['label']); ?>: <?php echo html_escape($report['period_label'] ?? $report['year']); ?></div>
    <div class="ld-chart-legend">
      <?php if ($report['slug'] === 'earned-leave') { ?><span><i class="lg-bal"></i> Balance (end of month)</span><?php } ?>
      <span><i class="lg-con"></i> Consumed</span>
    </div>
    <div class="ld-chart-bars">
      <?php foreach ($report['monthly'] as $m) {
        if (empty($m['is_elapsed'])) {
          $bal_h = 0;
          $con_h = 0;
        } else {
          $bal_h = $report['slug'] === 'earned-leave' && $m['balance'] !== null
            ? round(((float) $m['balance'] / $max_chart) * 85) : 0;
          $con_h = round(((float) $m['consumed'] / $max_chart) * 85);
        }
        $col_class = empty($m['is_elapsed']) ? 'ld-chart-col future' : 'ld-chart-col';
      ?>
        <div class="<?php echo $col_class; ?>">
          <div class="bars">
            <?php if (!empty($m['is_elapsed']) && $report['slug'] === 'earned-leave' && $bal_h > 0) { ?><div class="bar bal" style="height:<?php echo (int) $bal_h; ?>px;" title="Balance <?php echo leave_detail_fmt_num($m['balance']); ?>"></div><?php } ?>
            <?php if (!empty($m['is_elapsed']) && $con_h > 0) { ?><div class="bar con" style="height:<?php echo (int) $con_h; ?>px;" title="Consumed <?php echo leave_detail_fmt_num($m['consumed']); ?>"></div><?php } ?>
          </div>
          <label><?php echo html_escape($m['label']); ?></label>
        </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>

  <?php
  $elapsed_months = array_values(array_filter($report['monthly'], function ($m) {
      return !empty($m['is_elapsed']);
  }));
  if (!empty($elapsed_months)) { ?>
  <div class="ld-section-title">Month-to-month breakdown</div>
  <div class="table-responsive">
    <table class="table table-striped table-condensed ld-month-table">
      <thead>
        <tr>
          <th>Month</th>
          <th class="text-right">Granted</th>
          <th class="text-right">Consumed</th>
          <th class="text-right">Closing Balance</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($elapsed_months as $m) { ?>
          <tr>
            <td><?php echo html_escape($m['full_label'] ?? $m['label']); ?></td>
            <td class="num"><?php echo leave_detail_fmt_num($m['granted']); ?></td>
            <td class="num"><?php echo leave_detail_fmt_num($m['consumed']); ?></td>
            <td class="num"><?php echo $m['balance'] !== null ? leave_detail_fmt_num($m['balance']) : '—'; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <?php } ?>

  <div class="ld-section-title">All transactions (<?php echo html_escape($report['period_label'] ?? $report['year']); ?>)</div>
  <div class="table-responsive">
    <table class="table table-striped table-condensed ld-tx-table mbot0">
      <thead>
        <tr>
          <th>Transaction type</th>
          <th>Posted on</th>
          <th>From</th>
          <th>To</th>
          <th>Days</th>
          <th>Reason</th>
          <th>Remarks</th>
          <th>Expiry Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($report['transactions'])) { ?>
          <tr><td colspan="8" class="text-muted">No leave transactions for <?php echo html_escape($report['period_label'] ?? (string) $report['year']); ?>.</td></tr>
        <?php } else {
          foreach ($report['transactions'] as $tx) {
            $type_class = strtolower(str_replace(' ', '-', $tx['transaction_type']));
            $from_date = _d(date('Y-m-d', strtotime($tx['start_time'])));
            $to_date = _d(date('Y-m-d', strtotime($tx['end_time'])));
            $days = (float) $tx['number_of_leaving_day'];
            $is_balance_tx = in_array($type_class, ['granted', 'carry-forward'], true);
            if (!$is_balance_tx) {
              if ($days > 0 && $days < 1) {
                $from_date .= ' Session 2';
                $to_date .= ' Session 2';
              } elseif ($from_date !== $to_date) {
                $from_date .= ' Session 1';
                $to_date .= ' Session 2';
              } else {
                $from_date .= ' Session 1';
                $to_date .= ' Session 2';
              }
            }
        ?>
          <tr>
            <td><span class="ld-tx-type <?php echo html_escape($type_class); ?>"><?php echo html_escape($tx['transaction_type']); ?></span></td>
            <td><?php echo _d(date('Y-m-d', strtotime($tx['posted_on']))); ?></td>
            <td><?php echo html_escape($from_date); ?></td>
            <td><?php echo html_escape($to_date); ?></td>
            <td><?php echo leave_detail_fmt_num($days); ?></td>
            <td><?php echo html_escape($tx['reason'] ?? '—'); ?></td>
            <td><?php echo html_escape($tx['remarks'] !== '' ? $tx['remarks'] : '—'); ?></td>
            <td>—</td>
          </tr>
        <?php }
        } ?>
      </tbody>
    </table>
  </div>
</div>
