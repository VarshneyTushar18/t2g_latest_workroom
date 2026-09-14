<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$leave_balance_cards = $leave_balance_cards ?? [];
$leave_balance_year = $leave_balance_year ?? (int) date('Y');
$leave_balance_month = isset($leave_balance_month) ? (int) $leave_balance_month : 0;
$leave_balance_staff_id = isset($userid) ? (int) $userid : (int) get_staff_user_id();

if (!function_exists('format_leave_balance_number')) {
    function format_leave_balance_number($value)
    {
        $value = (float) $value;
        if (abs($value - round($value)) < 0.001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
?>
<style>
  .leave-balance-cards-wrap { margin-bottom: 20px; }
  .leave-balance-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 14px;
  }
  .leave-balance-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px 16px 12px;
    min-height: 150px;
    display: flex;
    flex-direction: column;
  }
  .leave-balance-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
  }
  .leave-balance-card-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    line-height: 1.3;
  }
  .leave-balance-card-granted {
    font-size: 11px;
    color: #9ca3af;
    white-space: nowrap;
  }
  .leave-balance-card-body {
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4px 0;
  }
  .leave-balance-card-value {
    font-size: 34px;
    line-height: 1;
    font-weight: 400;
    color: #111827;
  }
  .leave-balance-card-value.negative { color: #dc2626; }
  .leave-balance-card-label {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
  }
  .leave-balance-card-link {
    font-size: 12px;
    color: #2563eb;
    margin-top: 6px;
    cursor: pointer;
    display: inline-block;
  }
  .leave-balance-card-foot {
    margin-top: 10px;
  }
  .leave-balance-card-consumed {
    font-size: 11px;
    color: #9ca3af;
    margin-bottom: 6px;
  }
  .leave-balance-card-progress {
    height: 4px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
  }
  .leave-balance-card-progress > span {
    display: block;
    height: 100%;
    background: #3b82f6;
    border-radius: 999px;
  }
  @media (max-width: 767px) {
    .leave-balance-cards-grid {
      grid-template-columns: 1fr;
    }
    .leave-balance-card-head {
      flex-wrap: wrap;
    }
  }
  @media (min-width: 768px) and (max-width: 1100px) {
    .leave-balance-cards-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>

<div class="leave-balance-cards-wrap" id="leave_balance_cards_wrap" data-staff-id="<?php echo $leave_balance_staff_id; ?>" data-year="<?php echo $leave_balance_year; ?>" data-month="<?php echo $leave_balance_month; ?>">
  <?php if (!empty($leave_balance_section_title)) { ?>
  <h5 class="mtop0 mbot12" style="font-weight:700;"><?php echo html_escape($leave_balance_section_title); ?></h5>
  <?php } ?>
  <div class="leave-balance-cards-grid" id="leave_balance_cards_grid">
    <?php if (empty($leave_balance_cards)) { ?>
      <div class="text-muted" style="grid-column:1/-1;padding:12px 4px;">Loading leave balances…</div>
    <?php } ?>
    <?php foreach ($leave_balance_cards as $card) :
      $balance_class = ((float) $card['balance'] < 0) ? ' negative' : '';
      $granted_label = format_leave_balance_number($card['granted']);
      $balance_label = format_leave_balance_number($card['balance']);
      $consumed_label = format_leave_balance_number($card['consumed']);
      $show_foot = ((float) $card['granted'] > 0) || ((float) $card['consumed'] > 0);
      $is_lop = ($card['slug'] === 'loss-of-pay');
    ?>
      <div class="leave-balance-card" data-slug="<?php echo html_escape($card['slug']); ?>">
        <div class="leave-balance-card-head">
          <div class="leave-balance-card-title"><?php echo html_escape($card['label']); ?></div>
          <div class="leave-balance-card-granted">Had: <?php echo $granted_label; ?><?php
            if ($card['slug'] === 'earned-leave' && isset($card['monthly_earn'])) {
              echo ' <span style="font-weight:400;color:#9ca3af;">(+' . format_leave_balance_number($card['monthly_earn']) . ' earn)</span>';
            }
          ?></div>
        </div>
        <div class="leave-balance-card-body">
          <div class="leave-balance-card-value<?php echo $balance_class; ?>"><?php echo $balance_label; ?></div>
          <div class="leave-balance-card-label">Balance</div>
          <a class="leave-balance-card-link leave-card-view-details" data-slug="<?php echo html_escape($card['slug']); ?>" data-label="<?php echo html_escape($card['label']); ?>">View Details</a>
        </div>
        <?php if ($show_foot) { ?>
        <div class="leave-balance-card-foot">
          <?php if ($is_lop && (float) $card['granted'] <= 0 && (float) $card['consumed'] > 0) { ?>
          <div class="leave-balance-card-consumed"><?php echo $consumed_label; ?> day(s) taken (no grant — balance goes negative)</div>
          <?php } else { ?>
          <div class="leave-balance-card-consumed"><?php echo $consumed_label; ?> of <?php echo $granted_label; ?> taken → remain <?php echo $balance_label; ?></div>
          <?php } ?>
          <div class="leave-balance-card-progress"><span style="width: <?php echo (int) $card['progress']; ?>%;"></span></div>
        </div>
        <?php } ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal fade" id="leave_type_details_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document" style="max-width:960px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="leave_type_details_title">Leave Details</h4>
      </div>
      <div class="modal-body" id="leave_type_details_body">
        <p class="text-muted">Loading...</p>
      </div>
    </div>
  </div>
</div>
