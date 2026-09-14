<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
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
  .hrms-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--hrms-ink);
    margin: 8px 0 22px;
    letter-spacing: -0.02em;
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
</style>

<div id="wrapper" class="hrms-home">
  <div class="content">
    <div class="hrms-shell">
      <h1 class="hrms-title">HRMS</h1>

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
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
