<?php init_head(); ?>
<style>
  .assets-manage-page {
    --am-ink: #0f172a;
    --am-muted: #64748b;
    --am-line: #e2e8f0;
    --am-navy: #141e46;
    --am-blue: #2563eb;
    --am-amber: #d97706;
    --am-teal: #0f766e;
    --am-red: #dc2626;
    --am-violet: #7c3aed;
  }

  .assets-manage-page .content {
    background: #f1f5f9;
    padding-top: 16px;
  }

  #dynamicFilterContainer .filter-row {
    display: inline-block;
    margin: 10px;
  }

  #dynamicFilterContainer label {
    display: inline-block;
    margin-right: 10px;
  }

  #dynamicFilterContainer input {
    display: inline-flex;
    width: 150px;
  }

  .asset-name-cell {
    text-align: center;
  }

  .assets-manage-panel table.dataTable thead th,
  .assets-manage-panel table.dataTable thead td,
  .assets-manage-panel table.dataTable tbody td,
  .assets-manage-panel table.dataTable tbody th {
    font-weight: 700 !important;
    color: var(--am-ink);
    vertical-align: middle;
  }

  .assets-manage-panel table.dataTable thead th:first-child,
  .assets-manage-panel table.dataTable tbody td:first-child {
    width: 90px !important;
    min-width: 90px !important;
    max-width: 90px !important;
    padding-left: 8px;
    padding-right: 8px;
    text-align: center;
  }

  .assets-manage-panel table.dataTable tbody td:first-child img,
  .assets-manage-panel table.dataTable tbody td:first-child .img-thumbnail {
    width: 58px !important;
    height: 58px !important;
    object-fit: cover;
    display: block;
    margin: 0 auto;
    border-radius: 10px;
  }

  .assets-manage-panel .asset-status-select {
    min-width: 120px;
    font-weight: 700;
  }

  .assets-manage-panel {
    border: 1px solid var(--am-line);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    margin-bottom: 0;
  }

  .assets-manage-panel > .panel-body {
    padding: 18px 20px 12px;
  }

  .am-page-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 16px;
  }

  .am-page-icon {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: var(--am-blue);
    font-size: 18px;
    flex-shrink: 0;
  }

  .am-page-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--am-ink);
    letter-spacing: -0.02em;
    line-height: 1.2;
  }

  .am-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 14px;
  }

  .am-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 10px;
    font-weight: 700;
    padding: 7px 12px 7px 8px;
    border: 1px solid var(--am-line);
    background: #fff;
    color: var(--am-ink);
    box-shadow: none;
  }

  .am-btn:hover,
  .am-btn:focus {
    color: var(--am-ink);
    border-color: #cbd5e1;
    background: #f8fafc;
  }

  .am-btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
  }

  .am-btn-icon.blue { background: #dbeafe; color: var(--am-blue); }
  .am-btn-icon.amber { background: #fef3c7; color: var(--am-amber); }
  .am-btn-icon.teal { background: #ccfbf1; color: var(--am-teal); }
  .am-btn-icon.green { background: #dcfce7; color: #15803d; }

  .assets-manage-panel .preview-tabs-top {
    margin-top: 0;
    margin-bottom: 12px;
  }

  .assets-manage-panel .nav-tabs-horizontal {
    margin-bottom: 0;
    border-bottom: 1px solid var(--am-line);
  }

  .assets-manage-panel .nav-tabs-horizontal > li > a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: var(--am-muted);
    border: 0 !important;
    padding: 10px 12px;
    margin-right: 4px;
  }

  .assets-manage-panel .nav-tabs-horizontal > li.active > a,
  .assets-manage-panel .nav-tabs-horizontal > li.active > a:hover,
  .assets-manage-panel .nav-tabs-horizontal > li.active > a:focus {
    color: var(--am-navy);
    background: transparent;
    border-bottom: 2px solid var(--am-navy) !important;
  }

  .am-tab-icon {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
  }

  .am-tab-icon.blue { background: #dbeafe; color: var(--am-blue); }
  .am-tab-icon.teal { background: #ccfbf1; color: var(--am-teal); }
  .am-tab-icon.amber { background: #fef3c7; color: var(--am-amber); }
  .am-tab-icon.slate { background: #e2e8f0; color: #334155; }
  .am-tab-icon.orange { background: #ffedd5; color: #c2410c; }
  .am-tab-icon.red { background: #fee2e2; color: var(--am-red); }
  .am-tab-icon.rose { background: #ffe4e6; color: #e11d48; }
  .am-tab-icon.violet { background: #ede9fe; color: var(--am-violet); }

  .assets-manage-panel table.dataTable thead th,
  .assets-manage-panel table.dataTable thead td {
    font-weight: 700 !important;
    color: var(--am-ink);
    background: #f8fafc;
    border-bottom: 1px solid var(--am-line) !important;
    white-space: nowrap;
    padding-right: 18px;
  }

  .assets-manage-panel table.dataTable tbody td,
  .assets-manage-panel table.dataTable tbody th {
    white-space: nowrap;
  }

  .assets-manage-panel .dataTables_wrapper {
    margin-top: 0;
  }

  .am-btn-icon.red { background: #fee2e2; color: var(--am-red); }
  .am-btn-icon.slate { background: #e2e8f0; color: #334155; }
  .am-btn-icon.orange { background: #ffedd5; color: #c2410c; }
  .am-btn-icon.violet { background: #ede9fe; color: var(--am-violet); }
  .am-btn-icon.rose { background: #ffe4e6; color: #e11d48; }

  .am-preview-dialog {
    width: 92%;
    max-width: 1100px;
    margin: 30px auto;
  }

  .am-preview-content {
    border-radius: 16px;
    border: 1px solid var(--am-line);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    overflow: hidden;
  }

  .am-preview-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--am-line);
    background: #fff;
  }

  .am-preview-header .modal-title {
    display: flex;
    align-items: center;
    margin: 0;
    flex: 1 1 auto;
  }

  .am-preview-close-btn {
    margin-left: auto;
  }

  .am-preview-close {
    margin: 0 0 0 4px;
    font-size: 28px;
    font-weight: 400;
    opacity: 0.55;
  }

  .am-preview-close:hover {
    opacity: 1;
  }

  #asset_preview_modal .modal-body {
    padding: 18px 20px 20px;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
    background: #f8fafc;
  }

  .am-preview .am-preview-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 16px;
  }

  .am-preview .am-preview-title h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--am-ink);
  }

  .am-preview .am-toolbar {
    margin-bottom: 16px;
  }

  .am-preview .nav-tabs-horizontal {
    margin-bottom: 0;
    border-bottom: 1px solid var(--am-line);
  }

  .am-preview .nav-tabs-horizontal > li > a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: var(--am-muted);
    border: 0 !important;
    padding: 10px 12px;
  }

  .am-preview .nav-tabs-horizontal > li.active > a,
  .am-preview .nav-tabs-horizontal > li.active > a:hover,
  .am-preview .nav-tabs-horizontal > li.active > a:focus {
    color: var(--am-navy);
    background: transparent;
    border-bottom: 2px solid var(--am-navy) !important;
  }

  .am-preview .panel-info {
    border: 1px solid var(--am-line);
    border-radius: 12px;
    box-shadow: none;
    background: #fff;
  }

  .am-preview .panel-info > .panel-body {
    padding: 16px;
  }

  .am-preview h4.tw-mt-0,
  .am-preview .panel-info h4 {
    font-weight: 700;
    color: var(--am-ink);
  }

  .am-preview table.table td.bold {
    font-weight: 700;
    color: var(--am-ink);
    width: 42%;
  }

  #asset_preview_modal ~ .modal,
  body > #allocation_modal,
  body > #recalled_modal,
  body > #additional_modal,
  body > #noti_lost_modal,
  body > #liquidation_modal,
  body > #warranty_modal,
  body > #broken_modal {
    z-index: 1065;
  }
</style>
<div id="wrapper" class="assets-manage-page">
  <div class="content">
    <div class="row">
      <div id="dynamicFilterContainer" style="display: inline;">
        <!-- The dynamic filter UI will be inserted here -->
      </div>

      <div class="col-md-12" id="small-table">
        <div class="panel_s assets-manage-panel">
          <div class="panel-body">
            <div class="am-page-head">
              <span class="am-page-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
              <h4 class="am-page-title"><?php echo _l($title); ?></h4>
            </div>

            <div class="am-toolbar _buttons">
              <?php if (has_permission('assets', '', 'create') || is_admin()) { ?>
                <a href="#" onclick="new_asset(); return false;" class="btn am-btn">
                  <span class="am-btn-icon blue"><i class="fa fa-laptop"></i></span>
                  <?php echo _l('new_asset') . ' hardware'; ?>
                </a>
                <a href="#" onclick="new_asset2(); return false;" class="btn am-btn">
                  <span class="am-btn-icon amber"><i class="fa fa-key"></i></span>
                  <?php echo _l('new_asset') . ' software'; ?>
                </a>
              <?php } ?>
              <a href="#" onclick="openAssetHistoryModal(); return false;" class="btn am-btn">
                <span class="am-btn-icon teal"><i class="fa fa-history"></i></span>
                Asset History
              </a>
              <div class="btn-group btn-with-tooltip-group _filter_data">
                <button id="toggleFilter" class="btn am-btn">
                  <span class="am-btn-icon green"><i class="fa fa-filter"></i></span>
                  Filter
                </button>
              </div>
            </div>

            <div class="horizontal-scrollable-tabs preview-tabs-top">
              <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
              <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
              <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                  <li role="presentation" class="active">
                    <a href="#all_asset" aria-controls="all_asset" role="tab" data-toggle="tab">
                      <span class="am-tab-icon blue"><i class="fa fa-th-list"></i></span>
                      <?php echo _l('all_asset'); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#not_pending_yet" aria-controls="not_pending_yet" role="tab" data-toggle="tab">
                      <span class="am-tab-icon teal"><i class="fa fa-inbox"></i></span>
                      <?php echo htmlspecialchars(_l('not_pending_yet')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#using" aria-controls="using" role="tab" data-toggle="tab">
                      <span class="am-tab-icon amber"><i class="fa fa-user"></i></span>
                      <?php echo htmlspecialchars(_l('using')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#liquidation" aria-controls="liquidation" role="tab" data-toggle="tab">
                      <span class="am-tab-icon slate"><i class="fa fa-recycle"></i></span>
                      <?php echo htmlspecialchars(_l('liquidation')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#warranty_repair" aria-controls="warranty_repair" role="tab" data-toggle="tab">
                      <span class="am-tab-icon orange"><i class="fa fa-wrench"></i></span>
                      <?php echo htmlspecialchars(_l('warranty_repair')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#lost" aria-controls="lost" role="tab" data-toggle="tab">
                      <span class="am-tab-icon red"><i class="fa fa-exclamation-triangle"></i></span>
                      <?php echo htmlspecialchars(_l('lost')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#broken" aria-controls="broken" role="tab" data-toggle="tab">
                      <span class="am-tab-icon rose"><i class="fa fa-chain-broken"></i></span>
                      <?php echo htmlspecialchars(_l('broken')); ?>
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#sold" aria-controls="sold" role="tab" data-toggle="tab">
                      <span class="am-tab-icon violet"><i class="fa fa-tag"></i></span>
                      Sold
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <?php echo form_hidden('asset_id', $asset_id); ?>
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="all_asset">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets1', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="not_pending_yet">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets2', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="using">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets3', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="liquidation">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets4', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="warranty_repair">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets5', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="lost">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets6', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="broken">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets7', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="sold">
                <?php
                $table_data = [];
                array_push($table_data, [
                  'name'    => _l('asset_image'),
                  'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                ]);
                $table_data = array_merge($table_data, [
                  _l('asset_image'),
                  _l('asset_code'),
                  _l('asset_name'),
                  _l('asset_group'),
                  'Asset Status',
                  _l('date_buy'),
                  _l('amount_allocate'),
                  _l('amount_rest'),
                  _l('original_price'),
                  _l('unit'),
                  _l('department'),
                  _l('assigned_to_customer'),
                ]);
                render_datatable($table_data, 'table_assets8', ['asset_sm' => 'asset_sm']);
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="asset_preview_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg am-preview-dialog">
          <div class="modal-content am-preview-content">
            <div class="modal-header am-preview-header">
              <h4 class="modal-title am-page-title" style="font-size:18px;">
                <span class="am-page-icon" style="width:34px;height:34px;font-size:15px;margin-right:8px;vertical-align:middle;"><i class="fa fa-cubes"></i></span>
                Asset details
              </h4>
              <button type="button" class="btn am-btn am-preview-close-btn" data-dismiss="modal">
                <span class="am-btn-icon red"><i class="fa fa-times"></i></span>
                Close
              </button>
            </div>
            <div class="modal-body" id="asset_sm_view"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="assets" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open_multipart(admin_url('assets/asset'), ['id' => 'assets-form']); ?>
    <div class="modal-content modalwidth">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="edit-title"><?php echo htmlspecialchars(_l('edit_asset')); ?></span>
          <span class="add-title"><?php echo htmlspecialchars(_l('new_asset')); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active">
            <a href="#asset_information" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
              <?php echo 'Asset information'; ?>
            </a>
          </li>

        </ul>
        <div class="tab-content">

          <div role="tabpanel" class="tab-pane active" id="asset_information">

            <div class="row">
              <div class="col-md-12">
                <div id="additional"></div>
                <div class="panel panel-info">
                  <div class="panel-heading"><?php echo htmlspecialchars(_l('asset_information')) . " : for Laptop / Desktop / Mouse / Keyboard / Monitor / Accessories / Other Accessories"; ?></div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('assets_code', 'asset_code', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('assets_name', 'asset_name', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php $arrAtt        = [];
                        $arrAtt['data-type'] = 'currency';
                        echo render_input('amount', 'amounts', '', 'number'); ?>
                      </div>
                      <div class="col-md-6">
                        <label for="unit"><?php echo htmlspecialchars(_l('unit')); ?></label>
                        <select name="unit" id="unit" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($unit as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['unit_id']); ?>"><?php echo htmlspecialchars($s['unit_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('series', 'series', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <label for="asset_group"><?php echo htmlspecialchars(_l('asset_group')); ?></label>
                        <select name="asset_group" id="asset_group" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($group as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['group_id']); ?>"><?php echo htmlspecialchars($s['group_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <label for="department"><?php echo htmlspecialchars(_l('room_management')); ?></label>
                        <select name="department" id="department" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($departments as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['departmentid']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label for="asset_location"><?php echo htmlspecialchars(_l('asset_location')); ?></label>
                        <select name="asset_location" id="asset_location" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($location as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['location_id']); ?>"><?php echo htmlspecialchars($s['location']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_date_input('date_buy', 'date_buy', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('warranty_period', 'warranty_period', '', 'number'); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('unit_price', 'unit_price', '', 'text', $arrAtt); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('depreciation', 'depreciation_month', '', 'number'); ?>
                        <p id="depreciation-error" class="text-danger"></p>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group select-placeholder">
                          <label for="clientid" class="control-label"><?php echo _l('client_belongs_to'); ?></label>
                          <select id="clientid" name="clientid[]" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple></select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <label for="visible_to_client"><?php echo _l('visible_to_client'); ?></label>
                        <div class="checkbox checkbox-danger">
                          <input type="checkbox" name="visible_to_client" id="visible_to_client" value="<?php echo isset($product) ? $product->visible_to_client : ''; ?>" <?php echo isset($product) ? ('1' == $product->visible_to_client) ? 'checked' : '' : ''; ?>>
                          <label></label>
                        </div>
                      </div>
                    </div>
                    <!-- adding custom fields -->
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('make', 'Make', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('serial_no', 'Serial No.', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('screen_size', 'Screen Size', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <label for="processor"><?php echo 'Processor'; ?></label>
                        <select name="processor" id="processor" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <option value="i3">i3</option>
                          <option value="i5">i5</option>
                          <option value="i7">i7</option>
                          <option value="i9">i9</option>
                          <option value="AMD">AMD</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('processor_gen', 'Processor Gen.', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <label for="ram_gen"><?php echo 'Ram Gen'; ?></label>
                        <select name="ram_gen" id="ram_gen" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <option value="DDR3">DDR3</option>
                          <option value="DDR4">DDR4</option>
                          <option value="DDR5">DDR5</option>
                          <option value="Onboard">Onboard</option>
                        </select>
                      </div>

                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('ram_slot_no', 'RAM Slot no.', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('ram', 'RAM', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <label for="storage_type"><?php echo 'Storage Type'; ?></label>
                        <select name="storage_type" id="storage_type" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <option value="HDD">HDD</option>
                          <option value="SSD">SSD</option>
                          <option value="NVME">NVME</option>
                          <option value="PEN Drive">PEN Drive</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('storage_1', 'Storage 1', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('storage_2', 'Storage 2', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('graphic_card', 'Graphic card', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('mac_address', 'MAC Address', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('mac_address2', 'MAC Address 2', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('imei_number', 'IMEI Number', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('imei_number2', 'IMEI Number 2', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('operating_system', 'Operating System', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('office', 'Office', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('adopter', 'Adopter', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('vendor_name', 'Vendor Name', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('bill_no', 'Bill Number', ''); ?>
                      </div>

                    </div>
                    <!-- custom fields end -->
                    <div class="row">
                      <div class="col-md-12">
                        <div class="attachment">
                          <div class="form-group">
                            <!-- <label for="attachment" class="control-label"><small class="req text-danger">* </small><?php echo _l('asset_image'); ?></label> -->
                            <label for="attachment" class="control-label"><?php echo _l('asset_image'); ?></label>

                            <input type="file" extension="png,jpg,jpeg,gif" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="asset_image" id="asset_image">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div id="asset_existing_image"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="panel panel-info">
                  <div class="panel-heading"><?php echo htmlspecialchars(_l('supplier_information')); ?></div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('supplier_name', 'supplier_name', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('supplier_phone', 'supplier_phone', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <?php echo render_input('supplier_address', 'supplier_address', ''); ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php echo render_textarea('description', 'description', ''); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
      <div class="modal-footer">
        <button type="
                    " class="btn btn-default" data-dismiss="modal"><?php echo htmlspecialchars(_l('close')); ?></button>
        <button id="sm_btn" type="submit" class="btn btn-info"><?php echo htmlspecialchars(_l('submit')); ?></button>
      </div>
    </div><!-- /.modal-content -->
    <?php echo form_close(); ?>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- custom modal created for softwate -->
<div class="modal fade" id="assets2" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open_multipart(admin_url('assets/asset'), ['id' => 'assets-form-software']); ?>
    <div class="modal-content modalwidth">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="edit-title"><?php echo htmlspecialchars(_l('edit_asset')); ?></span>
          <span class="add-title"><?php echo htmlspecialchars(_l('new_asset')); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active">
            <a href="#asset_information" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
              <?php echo 'Asset information'; ?>
            </a>
          </li>

        </ul>
        <div class="tab-content">

          <div role="tabpanel" class="tab-pane active" id="asset_information">

            <div class="row">
              <div class="col-md-12">
                <div id="additional"></div>
                <div class="panel panel-info">
                  <div class="panel-heading"><?php echo htmlspecialchars(_l('asset_information')) . " : Licences"; ?></div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('assets_code', 'asset_code', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('assets_name', 'asset_name', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php $arrAtt        = [];
                        $arrAtt['data-type'] = 'currency';
                        echo render_input('amount', 'amounts', '', 'number'); ?>
                      </div>
                      <div class="col-md-6">
                        <label for="unit"><?php echo htmlspecialchars(_l('unit')); ?></label>
                        <select name="unit" id="unit" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($unit as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['unit_id']); ?>"><?php echo htmlspecialchars($s['unit_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="row">

                      <div class="col-md-6">
                        <label for="asset_group"><?php echo htmlspecialchars(_l('asset_group')); ?></label>
                        <select name="asset_group" id="asset_group" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($group as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['group_id']); ?>"><?php echo htmlspecialchars($s['group_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('serial_no', 'Serial No.', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <label for="department"><?php echo htmlspecialchars(_l('room_management')); ?></label>
                        <select name="department" id="department" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($departments as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['departmentid']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label for="asset_location"><?php echo htmlspecialchars(_l('asset_location')); ?></label>
                        <select name="asset_location" id="asset_location" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                          <option value=""></option>
                          <?php foreach ($location as $s) { ?>
                            <option value="<?php echo htmlspecialchars($s['location_id']); ?>"><?php echo htmlspecialchars($s['location']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_date_input('date_buy', 'date_buy', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('warranty_period', 'warranty_period', '', 'number'); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('unit_price', 'unit_price', '', 'text', $arrAtt); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('depreciation', 'depreciation_month', '', 'number'); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group select-placeholder">
                          <label for="clientid" class="control-label"><?php echo _l('client_belongs_to'); ?></label>
                          <select id="clientid" name="clientid[]" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple></select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <label for="visible_to_client"><?php echo _l('visible_to_client'); ?></label>
                        <div class="checkbox checkbox-danger">
                          <input type="checkbox" name="visible_to_client" id="visible_to_client" value="<?php echo isset($product) ? $product->visible_to_client : ''; ?>" <?php echo isset($product) ? ('1' == $product->visible_to_client) ? 'checked' : '' : ''; ?>>
                          <label></label>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('make', 'Make', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('no_of_users', 'For no. of users', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('vendor_name', 'Vendor Name', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('bill_no', 'Bill no.', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_date_input('subscription_expiry', 'Subscription Expiry', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="attachment">
                          <div class="form-group">
                            <!-- <label for="attachment" class="control-label"><small class="req text-danger">* </small><?php echo 'Upload Bill Copy'; ?></label> -->
                            <label for="attachment" class="control-label"><?php echo 'Upload Bill Copy'; ?></label>

                            <input type="file" extension="png,jpg,jpeg,gif" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="asset_image" id="asset_image">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div id="asset_existing_image"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="panel panel-info">
                  <div class="panel-heading"><?php echo htmlspecialchars(_l('supplier_information')); ?></div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-6">
                        <?php echo render_input('supplier_name', 'supplier_name', ''); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo render_input('supplier_phone', 'supplier_phone', ''); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <?php echo render_input('supplier_address', 'supplier_address', ''); ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php echo render_textarea('description', 'description', ''); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
      <div class="modal-footer">
        <button type="
                    " class="btn btn-default" data-dismiss="modal"><?php echo htmlspecialchars(_l('close')); ?></button>
        <button id="sm_btn" type="submit" class="btn btn-info"><?php echo htmlspecialchars(_l('submit')); ?></button>
      </div>
    </div><!-- /.modal-content -->
    <?php echo form_close(); ?>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="asset_history_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1100px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-history"></i> Asset History</h4>
      </div>
      <div class="modal-body">
        <div class="form-group asset-history-search-wrap" style="position: relative; margin-bottom: 18px;">
          <label for="assetHistorySearchInput">Search asset</label>
          <input type="text" id="assetHistorySearchInput" class="form-control" placeholder="Search by name, code, or ID..." autocomplete="off">
          <div id="assetHistorySearchResults" style="display:none; position:absolute; left:0; right:0; z-index:1060; background:#fff; border:1px solid #ddd; border-top:0; max-height:260px; overflow:auto; box-shadow:0 4px 12px rgba(0,0,0,.12);"></div>
        </div>

        <div id="assetHistorySelected" class="hide" style="margin-bottom: 12px; padding: 10px 12px; background:#f7f9fc; border:1px solid #e5e9f0; border-radius:4px;">
          <strong id="assetHistorySelectedName"></strong>
          <span class="text-muted" id="assetHistorySelectedMeta"></span>
        </div>

        <div id="assetHistoryEmpty" class="text-muted" style="padding: 20px 0;">
          Search and select an asset to view its allocation history.
        </div>

        <div id="assetHistoryLoading" class="hide text-muted" style="padding: 20px 0;">Loading history...</div>

        <div id="assetHistoryTableWrap" class="table-responsive hide">
          <table class="table table-striped table-bordered" id="assetHistoryTable">
            <thead>
              <tr>
                <th>Date / Time</th>
                <th>Action</th>
                <th>Employee</th>
                <th>Emp ID</th>
                <th>Qty</th>
                <th>Code</th>
                <th>Action By</th>
                <th>Location</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <p id="assetHistoryNoRows" class="hide text-muted">No allocation or revoke history found for this asset.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo htmlspecialchars(_l('close')); ?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="asset_sale_modal" tabindex="-1" role="dialog" aria-labelledby="assetSaleTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="asset-sale-form" enctype="multipart/form-data">
        <div class="modal-header" style="background:#141e46;color:#fff;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="assetSaleTitle"><i class="fa fa-shopping-cart"></i> Record Asset Sale</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning"><strong>Important:</strong> saving this sale permanently removes the asset from allocation.</div>
          <input type="hidden" name="asset_id" id="sale_asset_id">
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Asset name</label><input type="text" id="sale_asset_name" class="form-control" readonly></div></div>
            <div class="col-md-6"><div class="form-group"><label>Asset code</label><input type="text" id="sale_asset_code" class="form-control" readonly></div></div>
            <div class="col-md-4"><div class="form-group"><label>Quantity sold *</label><input type="number" name="quantity" id="sale_quantity" class="form-control" min="1" required></div></div>
            <div class="col-md-4"><div class="form-group"><label>Sale date *</label><input type="datetime-local" name="sale_date" class="form-control" required></div></div>
            <div class="col-md-4"><div class="form-group"><label>Selling price *</label><input type="number" name="selling_price" class="form-control" min="0" step="0.01" required></div></div>
          </div>
          <h4 class="bold">Buyer details</h4>
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Buyer name *</label><input type="text" name="buyer_name" class="form-control" required></div></div>
            <div class="col-md-6"><div class="form-group"><label>Company / organization</label><input type="text" name="buyer_company" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Email</label><input type="email" name="buyer_email" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Phone</label><input type="text" name="buyer_phone" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Address</label><input type="text" name="buyer_address" class="form-control"></div></div>
          </div>
          <h4 class="bold">Handler and payment details</h4>
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>Handled by *</label><input type="text" name="handler_name" class="form-control" value="<?php echo htmlspecialchars(get_staff_full_name(get_staff_user_id())); ?>" required></div></div>
            <div class="col-md-4"><div class="form-group"><label>Handler contact</label><input type="text" name="handler_contact" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Handler department</label><input type="text" name="handler_department" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Payment method *</label><select name="payment_method" class="form-control" required><option value="">Select method</option><option>Cash</option><option>Bank transfer</option><option>Card</option><option>Cheque</option><option>UPI</option><option>Other</option></select></div></div>
            <div class="col-md-4"><div class="form-group"><label>Payment reference</label><input type="text" name="payment_reference" class="form-control"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Handover location</label><input type="text" name="handover_location" class="form-control"></div></div>
            <div class="col-md-12"><div class="form-group"><label>Attachments</label><div><label for="sale_attachments" class="btn btn-default"><i class="fa fa-paperclip"></i> Attach files</label><input type="file" name="sale_attachments[]" id="sale_attachments" style="display:none;" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt"><span id="sale_attachment_count" class="text-muted mleft10">No files selected</span></div><small class="text-muted">Attach invoices, payment proof, handover documents, or other sale records.</small></div></div>
            <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save sale</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>

</html>
<script>
  var hidden_columns = [2, 3, 6, 7, 8];
</script>
<script>
  appValidateForm($('#assets-form'), {
    assets_name: 'required',
    amount: 'required',
    unit: 'required',
    date_buy: 'required',
    warranty_period: 'required',
    unit_price: 'required',
    assets_code: {
      required: true,
      remote: {
        url: site_url + "admin/assets/assets_code_exists",
        type: 'post',
        data: {
          assets_code: function() {
            return $('input[name="assets_code"]').val();
          },
          id: function() {
            return $('input[name="id"]').val();
          }
        }
      }
    }
  });
  var table = initDataTable('.table-table_assets1', admin_url + 'assets/table_assets/' + 'all_asset');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets2', admin_url + 'assets/table_assets/' + 'not_pending_yet');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets3', admin_url + 'assets/table_assets/' + 'using');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets4', admin_url + 'assets/table_assets/' + 'liquidation');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets5', admin_url + 'assets/table_assets/' + 'warranty_repair');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets6', admin_url + 'assets/table_assets/' + 'lost');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets7', admin_url + 'assets/table_assets/' + 'broken');
  table.column(1).visible(false);
  var table = initDataTable('.table-table_assets8', admin_url + 'assets/table_assets/' + 'sold');
  table.column(1).visible(false);

  $(document).on('change', '.asset-status-select', function() {
    var select = $(this);
    var previousValue = select.data('previous-value');
    var assetId = select.data('asset-id');

    if (typeof previousValue === 'undefined') {
      previousValue = select.val();
    }

    if (select.val() === '2') {
      select.val(previousValue);
      openAssetAllocation(assetId);
      return;
    }

    if (select.val() === '3') {
      select.val(previousValue);
      $.getJSON(admin_url + 'assets/get_asset_sale_context/' + assetId)
        .done(function(response) {
          if (!response.success || !response.asset) {
            alert_float('danger', 'Unable to load sale details.');
            return;
          }
          $('#asset-sale-form')[0].reset();
          $('#sale_asset_id').val(response.asset.id);
          $('#sale_asset_name').val(response.asset.assets_name);
          $('#sale_asset_code').val(response.asset.assets_code);
          $('#sale_quantity').val(response.asset.available_quantity).attr('max', response.asset.available_quantity);
          $('#asset_sale_modal').data('status-select', select).modal('show');
        })
        .fail(function() {
          alert_float('danger', 'Unable to load sale details.');
        });
      return;
    }

    $.post(admin_url + 'assets/update_asset_status', {
      asset_id: assetId,
      status: select.val()
    }).done(function(response) {
      if (!response.success) {
        select.val(previousValue);
        alert_float('danger', response.message || 'Unable to update asset status.');
        return;
      }

      select.removeClass('asset-status-1 asset-status-2 asset-status-3 asset-status-4 asset-status-5 asset-status-6 asset-status-7');
      select.addClass('asset-status-' + select.val());
      var statusColors = {
        1: ['#dcfce7', '#22c55e', '#166534'],
        2: ['#dbeafe', '#3b82f6', '#1d4ed8'],
        3: ['#f3e8ff', '#a855f7', '#7e22ce'],
        4: ['#fef3c7', '#f59e0b', '#92400e'],
        5: ['#fee2e2', '#ef4444', '#b91c1c'],
        6: ['#ffedd5', '#f97316', '#c2410c'],
        7: ['#cffafe', '#06b6d4', '#0e7490']
      };
      var colors = statusColors[select.val()];
      select.css({
        'background-color': colors[0],
        'border-color': colors[1],
        'color': colors[2]
      });
      select.data('previous-value', select.val());
      alert_float('success', response.message);
    }).fail(function() {
      select.val(previousValue);
      alert_float('danger', 'Unable to update asset status.');
    });
  });

  function openAssetAllocation(assetId) {
    $('#asset_sm_view').empty();
    init_asset(assetId, function() {
      $('#allocation_modal').modal('show');
      $('#allocation_modal .selectpicker').selectpicker('refresh');
      init_datepicker();
    });
  }

  $('#asset-sale-form').on('submit', function(event) {
    event.preventDefault();
    var form = $(this);
    var submit = form.find('button[type="submit"]');
    submit.prop('disabled', true);
    var formData = new FormData(form[0]);
    $.ajax({
      url: admin_url + 'assets/sell_asset',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false
    })
      .done(function(response) {
        if (!response.success) {
          alert_float('danger', response.message || 'Unable to save sale.');
          return;
        }
        $('#asset_sale_modal').modal('hide');
        alert_float('success', response.message);
        setTimeout(function() { window.location.reload(); }, 500);
      })
      .fail(function(xhr) {
        var response = xhr.responseJSON || {};
        alert_float('danger', response.message || 'Unable to save sale.');
      })
      .always(function() {
        submit.prop('disabled', false);
      });
  });

  $('#sale_attachments').on('change', function() {
    var count = this.files ? this.files.length : 0;
    $('#sale_attachment_count').text(count ? count + ' file(s) selected' : 'No files selected');
  });

  function new_asset() {
    $('#assets').modal('show');
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    $('#additional').html('');

    $('#assets #asset_existing_image').html('');
    $('#assets select#clientid').html('').change();
    // $("#assets #asset_image").prop('required', 'required');
    $("#assets .attachment .req").show();
    $('#assets input#visible_to_client').prop('checked', false);


  }

  // custom created funtion for new software asset
  function new_asset2() {
    $('#assets2').modal('show');
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    $('#additional2').html('');

    $('#assets2 #asset_existing_image2').html('');
    $('#assets2 select#clientid2').html('').change();
    // $("#assets2 #asset_image2").prop('required', 'required');
    $("#assets2 .attachment .req").show();
    $('#assets2 input#visible_to_client2').prop('checked', false);


  }


  // validation for software asset modal

  appValidateForm($('#assets-form-software'), {
    assets_name: 'required',
    amount: 'required',
    unit: 'required',
    date_buy: 'required',
    warranty_period: 'required',
    unit_price: 'required',
    assets_code: {
      required: true,
      remote: {
        url: site_url + "admin/assets/assets_code_exists",
        type: 'post',
        data: {
          assets_code: function() {
            return $('input[name="assets_code"]').val();
          },
          id: function() {
            return $('input[name="id"]').val();
          }
        }
      }
    }
  });

  function edit_asset(invoker, id, fileid) {

    $('#additional').html('');
    $('#additional').append(hidden_input('id', id));
    $('#assets input[name="assets_code"]').val($(invoker).data('assets_code'));
    $('#assets input[name="assets_name"]').val($(invoker).data('assets_name'));
    $('#assets input[name="date_buy"]').val($(invoker).data('date_buy'));
    $('#assets input[name="amount"]').val($(invoker).data('amount'));
    $('#assets input[name="unit_price"]').val($(invoker).data('unit_price'));
    $('#assets input[name="supplier_phone"]').val($(invoker).data('supplier_phone'));
    $('#assets input[name="supplier_name"]').val($(invoker).data('supplier_name'));
    $('#assets input[name="supplier_address"]').val($(invoker).data('supplier_address'));
    $('#assets input[name="series"]').val($(invoker).data('series'));
    $('#assets input[name="warranty_period"]').val($(invoker).data('warranty_period'));
    $('#assets input[name="depreciation"]').val($(invoker).data('depreciation'));
    $('#assets select[name="unit"]').val($(invoker).data('unit'));
    $('#assets select[name="unit"]').change();
    $('#assets select[name="asset_group"]').val($(invoker).data('asset_group'));
    $('#assets select[name="asset_group"]').change();
    $('#assets select[name="department"]').val($(invoker).data('department'));
    $('#assets select[name="department"]').change();
    $('#assets select[name="asset_location"]').val($(invoker).data('asset_location'));
    $('#assets select[name="asset_location"]').change();
    $('#assets textarea[name="description"]').val($(invoker).data('description'));

    console.log($(invoker).data('file_id'));
    console.log($(invoker).data());

    $('#assets #asset_existing_image').html(`<img src="https://drive.google.com/thumbnail?id=${fileid}" class='img-thumbnail img-responsive' style="width: 150px; height: 150px;" >`);

    $('#assets select#clientid').html($(invoker).data('belongs_to_option'));
    $('#assets select#clientid').change();

    $('#assets input#visible_to_client').prop('checked', false);
    if ($(invoker).data('visible_to_client') == "1") {
      $('#assets input#visible_to_client').prop('checked', true);
    }

    $("#assets #asset_image").removeAttr('required');
    $("#assets .attachment .req").hide();

    $('#assets').modal('show');
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');
  }
  init_asset();

  function init_asset(id, onLoaded) {
    load_small_table_item_asset(id, '#asset_sm_view', 'asset_id', 'assets/get_asset_data_ajax', '.asset_sm', onLoaded);
  }

  function load_small_table_item_asset(pr_id, selector, input_name, url, table, onLoaded) {
    var _tmpID = $('input[name="' + input_name + '"]').val();
    if (_tmpID !== '' && !window.location.hash) {
      pr_id = _tmpID;
      $('input[name="' + input_name + '"]').val('');
    } else {
      if (window.location.hash && !pr_id) {
        pr_id = window.location.hash.substring(1);
      }
    }
    if (typeof(pr_id) == 'undefined' || pr_id === '') {
      return;
    }
    $('input[name="' + input_name + '"]').val(pr_id);
    do_hash_helper(pr_id);
    var $modal = $('#asset_preview_modal');
    $(selector).html('<div class="text-center" style="padding:48px 20px;color:#64748b;font-weight:600;">Loading asset details...</div>');
    $modal.modal('show');
    $(selector).load(admin_url + url + '/' + pr_id, function() {
      var assetName = $('#asset_sm_view .am-preview-title h4').text();
      if (assetName) {
        $modal.find('.am-preview-header .modal-title').html(
          '<span class="am-page-icon" style="width:34px;height:34px;font-size:15px;margin-right:8px;vertical-align:middle;"><i class="fa fa-cubes"></i></span>' +
          $('<div>').text(assetName).html()
        );
      }
      if (typeof onLoaded === 'function') {
        onLoaded();
      }
    });
  }

  $('#asset_preview_modal').on('hidden.bs.modal', function() {
    if ($('.modal:visible').length) {
      return;
    }
    $('#asset_sm_view').empty();
    if (history.replaceState) {
      history.replaceState(null, null, window.location.pathname + window.location.search);
    }
  });

  function preview_asset_btn(invoker) {
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_asset_file(id, rel_id);
  }

  function view_asset_file(id, rel_id) {
    $('#asset_file_data').empty();
    $("#asset_file_data").load(admin_url + 'assets/file/' + id + '/' + rel_id, function(response, status, xhr) {
      if (status == "error") {
        alert_float('danger', xhr.statusText);
      }
    });
  }

  function close_modal_preview() {
    $('._project_file').modal('hide');
  }



  // custom filter code added

  function hideShowFilter() {
    $('#dynamicFilterContainer').hide(); // Hide filter by default

    $('#toggleFilter').on('click', function() {
      $('#dynamicFilterContainer').toggle();
    });

  }

  function customFilterSearch() {
    hideShowFilter();

    var projectTable = $('#DataTables_Table_0').DataTable();
    // Generate search inputs for each column
    var filters = []; // Store filter values
    function applyFilters() {
      projectTable.columns().every(function(index) {
        if (typeof filters[index] !== 'undefined') {
          this.search(filters[index]);
        } else {
          this.search('');
        }
      });

      projectTable.draw(); // Redraw the table
    }

    function isDateColumn(title) {
      const dateKeywords = ['date', 'deadline']; // Add more keywords as needed
      title = title.toLowerCase();

      return dateKeywords.some(keyword => title.includes(keyword));
    }

    projectTable.columns().every(function(index) {
      var column = this;
      var title = $(column.header()).text();
      var input;
      var excludedFilterTitles = ['Asset Image', 'Asset Status', 'Quantity allocated', 'Inventory', 'Unit'];
      if (excludedFilterTitles.indexOf($.trim(title)) !== -1) {
        return;
      }
      if (title == '#' || title == ' - ') {
        return false;
      }

      if (isDateColumn(title)) {
        input = $('<div class="filter-row"><label for="filter_' + index + '">' + title + ': </label><input type="date" class="form-control" placeholder="' + title + '"></div>')
          .appendTo($('#dynamicFilterContainer'));
      } else {
        input = $('<div class="filter-row"><label for="filter_' + index + '">' + title + ': </label><input type="text" class="form-control" placeholder="' + title + '"></div>')
          .appendTo($('#dynamicFilterContainer'));
      }


      input.find('input').on('keyup change', function() {
        filters[index] = $(this).val(); // Store the filter value
        applyFilters(); // Apply filters and redraw the table

      });
    });
    // $('#applyFiltersBtn').on('click', function() {
    //     applyFilters(); // Apply filters and redraw the table
    // });

  }




  function generateFilter() {
    // Generate dynamic filter form fields
    hideShowFilter();
    var filterHTML = '<form id="dynamicFilterForm">';
    $('#DataTables_Table_0 thead tr th').each(function(index) {
      var heading = $(this).text();
      var fieldType = inferColumnType(index, heading); // Call a function to infer column type
      filterHTML += '<div class="filter-input">';
      filterHTML += '<label for="filter_' + index + '">' + heading + ': </label>';

      filterHTML += fieldType;

      filterHTML += '</div>';
    });
    filterHTML += '<button type="submit" class = "btn btn-primary">Apply Filter</button></form>';

    // Insert the filter form into the dynamic filter container
    // $('#dynamicFilterContainer').html(filterHTML);
  }

  $(function() {
    customFilterSearch();

    // Open create modal when arriving from dashboard New Asset chooser
    var params = new URLSearchParams(window.location.search);
    var openNew = (params.get('new') || '').toLowerCase();
    if (openNew === 'hardware' && typeof new_asset === 'function') {
      setTimeout(function() { new_asset(); }, 300);
    } else if (openNew === 'software' && typeof new_asset2 === 'function') {
      setTimeout(function() { new_asset2(); }, 300);
    }
  })
</script>

<script>
  $(document).ready(function() {
    $('#assets-form').on('submit', function() {
      $('#depreciation-error').empty();
      var value = $('#depreciation').val();
      if (value.trim() === '0') {
        $('#depreciation-error').show();
        $('#depreciation-error').append('Value cannot be zero');
        event.preventDefault(); // Prevent form submission
        $('#depreciation').val('').focus();
      }
    });
  });
</script>

<script>
  function openAssetHistoryModal() {
    $('#asset_history_modal').modal('show');
    setTimeout(function() {
      $('#assetHistorySearchInput').focus();
    }, 300);
  }

  (function($) {
    var searchTimer = null;
    var searchUrl = '<?php echo admin_url('assets/search_assets'); ?>';
    var historyUrl = '<?php echo admin_url('assets/get_asset_history'); ?>';
    var $input = $('#assetHistorySearchInput');
    var $results = $('#assetHistorySearchResults');

    function esc(text) {
      return $('<div>').text(text == null ? '' : text).html();
    }

    function closeResults() {
      $results.hide().empty();
    }

    function resetHistoryPanel() {
      $('#assetHistorySelected').addClass('hide');
      $('#assetHistoryTableWrap').addClass('hide');
      $('#assetHistoryLoading').addClass('hide');
      $('#assetHistoryEmpty').removeClass('hide');
      $('#assetHistoryNoRows').addClass('hide');
      $('#assetHistoryTable tbody').empty();
    }

    function renderSearchResults(items) {
      if (!items.length) {
        $results.html('<div style="padding:10px 12px; color:#888;">No assets found</div>').show();
        return;
      }
      var html = '';
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        html += '<a href="#" class="asset-history-pick" data-id="' + item.id + '" data-name="' + esc(item.name) + '" data-code="' + esc(item.code || '') + '" style="display:flex; justify-content:space-between; padding:10px 12px; border-bottom:1px solid #f0f0f0; color:#333; text-decoration:none;">' +
          '<span><strong>' + esc(item.name) + '</strong><br><span style="color:#888; font-size:12px;">ID #' + item.id +
          (item.code ? ' · ' + esc(item.code) : '') +
          (item.group ? ' · ' + esc(item.group) : '') +
          '</span></span>' +
          '<span style="color:#888; font-size:12px;">' + (item.assigned ? 'Assigned' : 'Available') + '</span>' +
          '</a>';
      }
      $results.html(html).show();
    }

    function loadAssetHistory(assetId, name, code) {
      closeResults();
      $('#assetHistoryEmpty').addClass('hide');
      $('#assetHistoryTableWrap').addClass('hide');
      $('#assetHistoryLoading').removeClass('hide');
      $('#assetHistorySelected').removeClass('hide');
      $('#assetHistorySelectedName').text(name || ('Asset #' + assetId));
      $('#assetHistorySelectedMeta').text(code ? (' · ' + code + ' · ID #' + assetId) : (' · ID #' + assetId));
      $input.val(name || '');

      $.getJSON(historyUrl, { asset_id: assetId })
        .done(function(resp) {
          $('#assetHistoryLoading').addClass('hide');
          if (!resp || !resp.success) {
            $('#assetHistoryEmpty').removeClass('hide').text((resp && resp.message) ? resp.message : 'Could not load history');
            return;
          }
          var rows = resp.history || [];
          var $tbody = $('#assetHistoryTable tbody').empty();
          if (!rows.length) {
            $('#assetHistoryTableWrap').removeClass('hide');
            $('#assetHistoryNoRows').removeClass('hide');
            return;
          }
          $('#assetHistoryNoRows').addClass('hide');
          for (var i = 0; i < rows.length; i++) {
            var r = rows[i];
            var typeBadge = r.type === 'allocation'
              ? '<span class="label label-success">' + esc(r.type_label || 'Allocation') + '</span>'
              : '<span class="label label-warning">' + esc(r.type_label || 'Revoke') + '</span>';
            $tbody.append(
              '<tr>' +
                '<td>' + esc(r.time) + '</td>' +
                '<td>' + typeBadge + '</td>' +
                '<td>' + esc(r.employee) + '</td>' +
                '<td>' + esc(r.employee_id) + '</td>' +
                '<td>' + esc(r.amount) + '</td>' +
                '<td>' + esc(r.code) + '</td>' +
                '<td>' + esc(r.action_by) + '</td>' +
                '<td>' + esc(r.location) + '</td>' +
              '</tr>'
            );
          }
          $('#assetHistoryTableWrap').removeClass('hide');
        })
        .fail(function() {
          $('#assetHistoryLoading').addClass('hide');
          $('#assetHistoryEmpty').removeClass('hide').text('Could not load history');
        });
    }

    $input.on('input', function() {
      var q = $.trim($input.val());
      clearTimeout(searchTimer);
      if (q.length < 1) {
        closeResults();
        return;
      }
      $results.html('<div style="padding:10px 12px; color:#888;">Searching...</div>').show();
      searchTimer = setTimeout(function() {
        $.getJSON(searchUrl, { q: q })
          .done(function(data) {
            renderSearchResults($.isArray(data) ? data : []);
          })
          .fail(function() {
            $results.html('<div style="padding:10px 12px; color:#888;">Search failed</div>').show();
          });
      }, 250);
    });

    $results.on('click', '.asset-history-pick', function(e) {
      e.preventDefault();
      var $el = $(this);
      loadAssetHistory($el.data('id'), $el.data('name'), $el.data('code'));
    });

    $input.on('keydown', function(e) {
      if (e.key === 'Escape') {
        closeResults();
      }
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('.asset-history-search-wrap').length) {
        closeResults();
      }
    });

    $('#asset_history_modal').on('hidden.bs.modal', function() {
      $input.val('');
      closeResults();
      resetHistoryPanel();
    });
  })(jQuery);
</script>
