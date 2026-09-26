<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .it-dash {
        --it-navy: #141e46;
        --it-muted: #64748b;
        --it-border: #e2e8f0;
        --it-card: #ffffff;
    }
    .it-dash .it-hero {
        margin-bottom: 20px;
    }
    .it-dash .it-hero h2 {
        margin: 0 0 4px;
        font-size: 22px;
        font-weight: 700;
        color: var(--it-navy);
    }
    .it-dash .it-hero p {
        margin: 0;
        color: var(--it-muted);
        font-size: 13px;
    }
    .it-dash .asset-search-wrap {
        position: relative;
        margin: 0 0 24px;
        max-width: 620px;
    }
    .it-dash .asset-search-wrap .search-input {
        width: 100%;
        height: 46px;
        border: 1px solid var(--it-border);
        border-radius: 8px;
        padding: 0 16px 0 44px;
        font-size: 14px;
        color: var(--it-navy);
        background: #fff;
        outline: none;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .it-dash .asset-search-wrap .search-input:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.18);
    }
    .it-dash .asset-search-wrap .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .it-dash .asset-search-results {
        display: none;
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 8px);
        z-index: 50;
        background: #fff;
        border: 1px solid var(--it-border);
        border-radius: 8px;
        max-height: 320px;
        overflow-y: auto;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
    }
    .it-dash .asset-search-results.open {
        display: block;
    }
    .it-dash .asset-search-results a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        min-height: 52px;
        padding: 12px 16px;
        color: var(--it-navy);
        text-decoration: none;
        border-bottom: 1px solid #f1f5f9;
    }
    .it-dash .asset-search-results a:last-child {
        border-bottom: 0;
    }
    .it-dash .asset-search-results a:hover {
        background: #f8fafc;
    }
    .it-dash .asset-search-results .asset-name {
        font-weight: 600;
    }
    .it-dash .asset-search-results .asset-meta {
        font-size: 12px;
        color: var(--it-muted);
        white-space: nowrap;
    }
    .it-dash .asset-search-results .empty,
    .it-dash .asset-search-results .loading {
        padding: 16px;
        color: var(--it-muted);
        font-size: 13px;
    }
    .it-dash .quick-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .it-dash .quick-nav .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 14px;
        border: 1px solid var(--it-border);
        background: #fff;
        color: var(--it-navy);
        box-shadow: none;
    }
    .it-dash .quick-nav .btn:hover,
    .it-dash .quick-nav .btn:focus {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--it-navy);
    }
    .it-dash .quick-nav .btn-primary-nav {
        background: var(--it-navy);
        border-color: var(--it-navy);
        color: #fff;
    }
    .it-dash .quick-nav .btn-primary-nav:hover,
    .it-dash .quick-nav .btn-primary-nav:focus {
        background: #0f1736;
        border-color: #0f1736;
        color: #fff;
    }
    .it-dash .quick-nav .btn i {
        margin-right: 6px;
    }
    .it-dash .stat-card {
        background: var(--it-card);
        border: 1px solid var(--it-border);
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 18px;
        min-height: 110px;
        position: relative;
        overflow: hidden;
    }
    .it-dash .stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }
    .it-dash .stat-card.it-stat-total::before { background: #141e46; }
    .it-dash .stat-card.it-stat-assigned::before { background: #0ea5e9; }
    .it-dash .stat-card.it-stat-available::before { background: #22c55e; }
    .it-dash .stat-card.it-stat-damaged::before { background: #ef4444; }
    .it-dash .stat-card.it-stat-warranty::before { background: #f59e0b; }
    .it-dash .stat-card.it-stat-unavailable::before { background: #64748b; }
    .it-dash .stat-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--it-muted);
        margin-bottom: 8px;
    }
    .it-dash .stat-value {
        font-size: 34px;
        font-weight: 700;
        line-height: 1;
        color: var(--it-navy);
        font-variant-numeric: tabular-nums;
    }
    .it-dash .stat-sub {
        margin-top: 8px;
        font-size: 12px;
        color: var(--it-muted);
    }
    .it-dash .stat-sub a {
        color: #0ea5e9;
        font-weight: 600;
    }
    .it-dash .department-panel .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .it-dash .department-panel .panel-heading::after {
        content: 'Inventory allocation';
        color: var(--it-muted);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .it-dash .asset-department-table {
        border: 0;
    }
    .it-dash .asset-department-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid var(--it-border);
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        padding: 11px 14px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .it-dash .asset-department-table tbody td {
        border-top: 1px solid #eef2f7;
        color: #475569;
        font-size: 13px;
        padding: 12px 14px;
        vertical-align: middle;
    }
    .it-dash .asset-department-table tbody tr:first-child td {
        border-top: 0;
    }
    .it-dash .asset-department-table tbody tr:hover {
        background: #f8fbff;
    }
    .it-dash .department-name {
        color: #334155;
        font-weight: 600;
        line-height: 1.3;
    }
    .it-dash .department-utilization {
        align-items: center;
        color: #94a3b8;
        display: flex;
        font-size: 10px;
        gap: 7px;
        margin-top: 6px;
        white-space: nowrap;
    }
    .it-dash .department-utilization-bar {
        background: #e2e8f0;
        border-radius: 99px;
        height: 4px;
        max-width: 86px;
        overflow: hidden;
        width: 100%;
    }
    .it-dash .department-utilization-bar span {
        background: #0ea5e9;
        border-radius: inherit;
        display: block;
        height: 100%;
    }
    .it-dash .department-number {
        border-radius: 6px;
        display: inline-block;
        font-variant-numeric: tabular-nums;
        min-width: 30px;
        padding: 3px 6px;
        text-align: center;
    }
    .it-dash .department-number-total {
        background: #f1f5f9;
        color: #1e293b;
        font-weight: 700;
    }
    .it-dash .department-number-assigned {
        background: #e0f2fe;
        color: #0369a1;
    }
    .it-dash .department-number-available {
        background: #dcfce7;
        color: #15803d;
    }
    @media (max-width: 600px) {
        .it-dash .department-panel .panel-heading::after {
            display: none;
        }
        .it-dash .asset-department-table thead th,
        .it-dash .asset-department-table tbody td {
            padding-left: 10px;
            padding-right: 10px;
        }
        .it-dash .department-utilization-bar {
            max-width: 54px;
        }
    }
    .it-dash .types-panel .panel-heading {
        background: #fff;
        border-bottom: 1px solid var(--it-border);
        padding: 14px 18px;
    }
    .it-dash .types-panel .panel-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--it-navy);
        margin: 0;
    }
    .it-dash .type-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .it-dash .type-row:last-child {
        border-bottom: 0;
    }
    .it-dash .type-name {
        width: 160px;
        flex-shrink: 0;
        font-weight: 600;
        color: var(--it-navy);
    }
    .it-dash .type-bar-wrap {
        flex: 1;
        background: #f1f5f9;
        border-radius: 999px;
        height: 10px;
        overflow: hidden;
    }
    .it-dash .type-bar {
        height: 100%;
        background: linear-gradient(90deg, #141e46, #3b82f6);
        border-radius: 999px;
        min-width: 2px;
    }
    .it-dash .type-count {
        width: 70px;
        text-align: right;
        font-weight: 700;
        color: var(--it-navy);
        font-variant-numeric: tabular-nums;
    }
    .it-dash .type-meta {
        width: 140px;
        text-align: right;
        font-size: 12px;
        color: var(--it-muted);
        flex-shrink: 0;
    }
    .it-dash .staff-lookup .panel-heading {
        background: #fff;
        border-bottom: 1px solid var(--it-border);
        padding: 14px 18px;
    }
    .it-dash .staff-lookup .panel-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--it-navy);
        margin: 0;
    }
    .it-dash #heading {
        color: var(--it-navy);
        font-size: 18px;
        font-weight: 700;
        margin: 4px 0 16px !important;
    }
    .it-dash #heading .text-muted {
        color: var(--it-muted);
        font-size: 14px;
        font-weight: 500;
    }
    .it-dash .asset-lookup-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 18px;
    }
    .it-dash .asset-lookup-card {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        min-width: 0;
        overflow: hidden;
        position: relative;
    }
    .it-dash .asset-lookup-card::before {
        background: #0ea5e9;
        content: '';
        height: 4px;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
    }
    .it-dash .asset-lookup-card-header {
        align-items: center;
        background: #f8fbff;
        border-bottom: 1px solid var(--it-border);
        display: flex;
        gap: 12px;
        justify-content: space-between;
        min-height: 64px;
        padding: 16px 14px 12px;
    }
    .it-dash .asset-lookup-card-identity {
        align-items: center;
        display: flex;
        gap: 10px;
        min-width: 0;
    }
    .it-dash .asset-lookup-card-number {
        align-items: center;
        background: #e0f2fe;
        border-radius: 6px;
        color: #0369a1;
        display: inline-flex;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 800;
        height: 28px;
        justify-content: center;
        width: 28px;
    }
    .it-dash .asset-lookup-card-title {
        color: var(--it-navy);
        font-size: 14px;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .it-dash .asset-lookup-card-code {
        background: #eef2f7;
        border-radius: 5px;
        color: #475569;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 7px;
    }
    .it-dash .asset-lookup-card-section-label {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        padding: 12px 0 2px;
        text-transform: uppercase;
    }
    .it-dash .asset-lookup-card-body {
        padding: 0 14px 10px;
    }
    .it-dash .asset-lookup-field {
        align-items: baseline;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 8px 0;
    }
    .it-dash .asset-lookup-field:last-child {
        border-bottom: 0;
    }
    .it-dash .asset-lookup-label {
        color: #475569;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
    }
    .it-dash .asset-lookup-value {
        color: #334155;
        font-size: 12px;
        max-width: 68%;
        overflow-wrap: anywhere;
        text-align: right;
    }
    .it-dash .asset-lookup-empty {
        border: 1px dashed var(--it-border);
        border-radius: 8px;
        color: var(--it-muted);
        padding: 18px;
        text-align: center;
    }
    @media (max-width: 767px) {
        .it-dash .asset-lookup-grid {
            grid-template-columns: 1fr;
        }
    }
    .it-dash .loader {
        position: fixed;
        z-index: 99999;
        inset: 0;
        background: rgba(255, 255, 255, 0.65);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .it-dash .loader.hidden {
        display: none;
    }
    .it-dash .loader img {
        width: 72px;
    }
</style>

<div id="wrapper" class="it-dash">
    <div class="content">
        <div class="it-hero">
            <h2>IT Assets Dashboard</h2>
            <p>Overview of inventory: totals, assignments, asset types, and departments</p>
        </div>

        <div class="asset-search-wrap">
            <i class="fa fa-search search-icon"></i>
            <input type="text"
                   id="assetSearchInput"
                   class="search-input"
                   placeholder="Search asset by ID, code, or name..."
                   autocomplete="off">
            <div id="assetSearchResults" class="asset-search-results"></div>
        </div>

        <div class="quick-nav">
            <a href="#" id="btnNewAsset" class="btn btn-primary-nav" onclick="openNewAssetChooser(); return false;">
                <i class="fa fa-plus"></i> New Asset
            </a>
            <a href="<?php echo admin_url('assets/allocation'); ?>" class="btn">
                <i class="fa fa-share"></i> Allocate
            </a>
            <a href="<?php echo admin_url('assets/eviction'); ?>" class="btn">
                <i class="fa fa-undo"></i> Revoke
            </a>
            <a href="<?php echo admin_url('assets/broken'); ?>" class="btn">
                <i class="fa fa-chain-broken"></i> Broken
            </a>
            <a href="<?php echo admin_url('assets/depreciation'); ?>" class="btn">
                <i class="fa fa-line-chart"></i> Depreciation
            </a>
            <a href="<?php echo admin_url('assets/manage_assets'); ?>" class="btn">
                <i class="fa fa-list"></i> All Assets
            </a>
            <a href="<?php echo admin_url('assets/setting'); ?>" class="btn">
                <i class="fa fa-cog"></i> Settings
            </a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card it-stat-total">
                    <span class="stat-label">Total Assets</span>
                    <div class="stat-value"><?php echo (int) $total_assets; ?></div>
                    <div class="stat-sub">
                        <a href="<?php echo admin_url('assets/manage_assets'); ?>">View all assets</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card it-stat-assigned">
                    <span class="stat-label">Assigned Assets</span>
                    <div class="stat-value"><?php echo (int) $assigned_assets; ?></div>
                    <div class="stat-sub">
                        Currently allocated to staff
                        <?php if (!empty($total_assets)) { ?>
                            · <?php echo round(($assigned_assets / $total_assets) * 100); ?>% of total
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card it-stat-available">
                    <span class="stat-label">Available</span>
                    <div class="stat-value"><?php echo (int) $available_assets; ?></div>
                    <div class="stat-sub">Not assigned yet</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card it-stat-damaged">
                    <span class="stat-label">Damaged</span>
                    <div class="stat-value"><?php echo (int) $damaged_assets; ?></div>
                    <div class="stat-sub">
                        <a href="<?php echo admin_url('assets/broken'); ?>">View broken assets</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card it-stat-warranty">
                    <span class="stat-label">Out of Warranty</span>
                    <div class="stat-value"><?php echo (int) $out_of_warranty; ?></div>
                    <div class="stat-sub">Warranty period expired</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card it-stat-unavailable">
                    <span class="stat-label">Not Available</span>
                    <div class="stat-value"><?php echo (int) $not_available_assets; ?></div>
                    <div class="stat-sub">Lost or liquidated</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="panel_s types-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">Asset Types</h4>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($asset_types)) { ?>
                            <p class="text-muted mtop15">No asset types found.</p>
                        <?php } else {
                            $maxType = max(array_column($asset_types, 'total_count') ?: [1]);
                            foreach ($asset_types as $type) {
                                $pct = $maxType > 0 ? round(($type['total_count'] / $maxType) * 100) : 0;
                                ?>
                                <div class="type-row">
                                    <div class="type-name"><?php echo html_escape($type['type_name']); ?></div>
                                    <div class="type-bar-wrap">
                                        <div class="type-bar" style="width: <?php echo (int) $pct; ?>%;"></div>
                                    </div>
                                    <div class="type-count"><?php echo (int) $type['total_count']; ?></div>
                                    <div class="type-meta">
                                        <?php echo (int) $type['assigned_count']; ?> assigned
                                        · <?php echo (int) $type['available_count']; ?> free
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="panel_s department-panel" >

                    <div class="panel-heading" style="background:#fff;border-bottom:1px solid #9fb7d6;padding:14px 18px;">
                        <h4 class="panel-title" style="margin:0;font-size:15px;font-weight:700;color:#141e46;">Assets by department</h4>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table asset-department-table" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Assigned</th>
                                        <th class="text-right">Available</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assets_by_department)) { ?>
                                        <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                                    <?php } else {
                                        foreach ($assets_by_department as $dept) {
                                            $department_total = (int) $dept['total_count'];
                                            $department_assigned = (int) $dept['assigned_count'];
                                            $assigned_percentage = $department_total > 0
                                                ? min(100, round(($department_assigned / $department_total) * 100))
                                                : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="department-name"><?php echo html_escape($dept['department_name']); ?></div>
                                                    <div class="department-utilization">
                                                        <span class="department-utilization-bar" aria-hidden="true"><span style="width: <?php echo $assigned_percentage; ?>%;"></span></span>
                                                        <span><?php echo $assigned_percentage; ?>% assigned</span>
                                                    </div>
                                                </td>
                                                <td class="text-right"><span class="department-number department-number-total"><?php echo $department_total; ?></span></td>
                                                <td class="text-right"><span class="department-number department-number-assigned"><?php echo $department_assigned; ?></span></td>
                                                <td class="text-right"><span class="department-number department-number-available"><?php echo (int) $dept['available_count']; ?></span></td>
                                            </tr>
                                        <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s staff-lookup">
                    <div class="panel-heading">
                        <h4 class="panel-title">Staff asset lookup</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="control-label">Employee name or ID</label>
                                <select id="designationFilter" class="form-control selectpicker" data-live-search="true">
                                    <option value="">Filter by employee</option>
                                    <?php foreach ($asset_lookup_staff as $staff) {
                                        $staff_name = trim($staff['firstname'] . ' ' . $staff['lastname']);
                                        $staff_identifier = trim((string) $staff['staff_identifi']);
                                        ?>
                                        <option value="<?php echo (int) $staff['staffid']; ?>" data-tokens="<?php echo html_escape($staff_name . ' ' . $staff_identifier); ?>">
                                            <?php echo html_escape($staff_name . ($staff_identifier !== '' ? ' (' . $staff_identifier . ')' : '')); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">&nbsp;</label>
                                <button type="button" id="submit" class="btn btn-info btn-block">Filter</button>
                            </div>
                        </div>
                        <hr>
                        <h4 id="heading" style="margin-top:0;"></h4>
                        <div id="display_data" class="asset-lookup-grid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loader hidden">
        <img src="<?php echo base_url('assets/images/loading.gif'); ?>" alt="Loading..."
             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2235%22 fill=%22none%22 stroke=%22%23141e46%22 stroke-width=%228%22 stroke-dasharray=%22160%22><animateTransform attributeName=%22transform%22 type=%22rotate%22 from=%220 50 50%22 to=%22360 50 50%22 dur=%221s%22 repeatCount=%22indefinite%22/></circle></svg>'">
    </div>
</div>

<div class="modal fade" id="newAssetTypeModal" tabindex="-1" role="dialog" aria-labelledby="newAssetTypeLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background:#141e46; color:#fff; border:0;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="newAssetTypeLabel" style="font-weight:700;">New Asset</h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="margin:0 0 14px; color:#64748b;">What type of asset do you want to add?</p>
                <button type="button" class="btn btn-block" style="background:#141e46; color:#fff; margin-bottom:10px; font-weight:600; padding:12px;"
                        onclick="chooseAssetType('hardware');">
                    <i class="fa fa-laptop"></i> Hardware Asset
                </button>
                <button type="button" class="btn btn-block btn-default" style="font-weight:600; padding:12px;"
                        onclick="chooseAssetType('software');">
                    <i class="fa fa-key"></i> Software Asset
                </button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function openNewAssetChooser() {
    $('#newAssetTypeModal').modal('show');
}

function chooseAssetType(type) {
    $('#newAssetTypeModal').modal('hide');
    var base = '<?php echo admin_url('assets/manage_assets'); ?>';
    window.location.href = base + (base.indexOf('?') >= 0 ? '&' : '?') + 'new=' + encodeURIComponent(type);
}

(function ($) {
    var searchTimer = null;
    var $input = $('#assetSearchInput');
    var $results = $('#assetSearchResults');
    var searchUrl = '<?php echo admin_url('assets/search_assets'); ?>';

    function closeResults() {
        $results.removeClass('open').empty();
    }

    function renderResults(items) {
        if (!items.length) {
            $results.html('<div class="empty">No assets found</div>').addClass('open');
            return;
        }
        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var status = item.assigned ? 'Assigned' : 'Available';
            html += '<a href="' + item.url + '">' +
                '<span><span class="asset-name">' + $('<div>').text(item.name || '').html() + '</span>' +
                '<br><span class="asset-meta">ID #' + item.id +
                (item.code ? ' · ' + $('<div>').text(item.code).html() : '') +
                (item.group ? ' · ' + $('<div>').text(item.group).html() : '') +
                '</span></span>' +
                '<span class="asset-meta">' + status + '</span>' +
                '</a>';
        }
        $results.html(html).addClass('open');
    }

    $input.on('input', function () {
        var q = $.trim($input.val());
        clearTimeout(searchTimer);
        if (q.length < 1) {
            closeResults();
            return;
        }
        $results.html('<div class="loading">Searching...</div>').addClass('open');
        searchTimer = setTimeout(function () {
            $.getJSON(searchUrl, { q: q })
                .done(function (data) {
                    renderResults($.isArray(data) ? data : []);
                })
                .fail(function () {
                    $results.html('<div class="empty">Search failed</div>').addClass('open');
                });
        }, 250);
    });

    $input.on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeResults();
            $input.blur();
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.asset-search-wrap').length) {
            closeResults();
        }
    });

    $('#designationFilter').selectpicker();

    function escapeHtml(value) {
        return $('<div>').text(value || 'N/A').html();
    }

    function field(label, value) {
        return '<div class="asset-lookup-field"><span class="asset-lookup-label">' + label +
            '</span><span class="asset-lookup-value">' + escapeHtml(value) + '</span></div>';
    }

    function assetCard(asset, index) {
        return '<article class="asset-lookup-card">' +
            '<div class="asset-lookup-card-header"><div class="asset-lookup-card-identity">' +
            '<span class="asset-lookup-card-number">' + (index + 1) + '</span>' +
            '<span class="asset-lookup-card-title">' + escapeHtml(asset.assets_name) +
            '</span></div><span class="asset-lookup-card-code">' + escapeHtml(asset.assets_code) +
            '</span></div>' +
            '<div class="asset-lookup-card-body">' +
            '<div class="asset-lookup-card-section-label">Specifications</div>' +
            field('Series', asset.series) +
            field('Serial No', asset.serial_no) +
            field('Processor', asset.processor) +
            field('RAM', asset.ram) +
            field('Storage', asset.storage_1) +
            field('OS', asset.operating_system) +
            '</div></article>';
    }

    $('#submit').on('click', function () {
        var emp = $('#designationFilter').val();
        if (!emp) {
            alert('Please select an employee');
            return;
        }
        $('.loader').removeClass('hidden');
        $.ajax({
            url: '<?php echo admin_url('assets/getFilterData'); ?>',
            data: { employee: emp },
            type: 'POST',
            success: function (response) {
                $('.loader').addClass('hidden');
                var data = [];
                try { data = JSON.parse(response); } catch (e) { data = []; }
                if (!data.length) {
                    $('#heading').html('');
                    $('#display_data').html('<div class="asset-lookup-empty">No assets found for this employee.</div>');
                    return;
                }
                var employeeName = $.trim((data[0]['firstname'] || '') + ' ' + (data[0]['lastname'] || ''));
                var employeeId = data[0]['staff_identifi'] || '';
                $('#heading').html(employeeName + (employeeId ? ' <span class="text-muted">(' + $('<div>').text(employeeId).html() + ')</span>' : ''));
                var list = '';
                for (var i = 0; i < data.length; i++) {
                    list += assetCard(data[i], i);
                }
                $('#display_data').html(list);
            },
            error: function () {
                $('.loader').addClass('hidden');
            }
        });
    });
})(jQuery);
</script>
</body>
</html>