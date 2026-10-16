<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //print_r($res_child); ?>
<?php init_head(); ?>
<style>
    .table-loading {
        background: unset;
    }

    #DataTables_Table_0_wrapper {
        overflow: scroll;
    }
 
    .dt-table-loading.table,
    .table-loading .dataTables_filter,
    .table-loading .dataTables_length,
    .table-loading .dt-buttons,
    .table-loading table tbody tr,
    .table-loading table thead th {
        opacity: 1 !important;
    }
    
    .snapshot-dashboard-filter {
        margin-bottom: 16px;
    }
    .snapshot-dashboard-filter label {
        display: block;
        margin-bottom: 6px;
    }
    .snapshot-dashboard-filter .bootstrap-select {
        width: 100% !important;
    }
    .snapshot-dashboard-filter .bootstrap-select .dropdown-menu {
        z-index: 1060 !important;
    }
    .snapshot-stat-row {
        margin-bottom: 18px;
    }
    a.snapshot-stat-card {
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 14px 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        transition: border-color 0.15s, box-shadow 0.15s;
        text-decoration: none !important;
        color: inherit;
        position: relative;
    }
    a.snapshot-stat-card:hover,
    a.snapshot-stat-card:focus {
        border-color: #141e46;
        box-shadow: 0 2px 8px rgba(20, 30, 70, 0.12);
        outline: none;
    }
    a.snapshot-stat-card .snapshot-stat-label {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }
    a.snapshot-stat-card .snapshot-stat-value {
        display: block;
        font-size: 28px;
        font-weight: 600;
        color: #141e46;
        line-height: 1.1;
    }
    .snapshot-dept-table {
        margin-bottom: 20px;
    }
    .snapshot-dept-table th {
        background: #141e46 !important;
        color: #fff !important;
        font-weight: 600;
        font-size: 13px;
    }
    .snapshot-dept-table td,
    .snapshot-dept-table th {
        vertical-align: middle !important;
    }
    .snapshot-dept-table a.snapshot-dept-count {
        font-weight: 600;
        color: #141e46;
    }
    .snapshot-dept-table a.snapshot-dept-count:hover {
        text-decoration: underline;
    }
	.table-loading table thead tr{
		height:auto !important;
	}

	table.dataTable thead th.sorting:after, table.dataTable thead th.sorting_desc:after{
  filter: invert(1) !important;
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div id="wrapper" class="leave_balance">
    <div class="content">
<!--<img src="https://lh3.googleusercontent.com/d/1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM=w1000?authuser=0">-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
					<?php if (!empty($drive_error)) { ?>
					<div class="alert alert-warning"><?php echo htmlspecialchars($drive_error); ?>
						<?php if (strpos($drive_error, 'not configured') === false) { ?>
						<br><small>Share each employee snapshot folder with <strong>t2gworkroom@t2g-monitoring.iam.gserviceaccount.com</strong> (Viewer or higher).</small>
						<?php } ?>
					</div>
					<?php } elseif (!empty($drive_hint)) { ?>
					<div class="alert alert-info"><?php echo htmlspecialchars($drive_hint); ?></div>
					<?php } ?>
					<?php
					$folderCount = isset($employee_folder_count) ? (int) $employee_folder_count : 0;
					$snapshotSummary = isset($snapshot_summary) && is_array($snapshot_summary) ? $snapshot_summary : [
						'total_active' => 0,
						'logged_in_today' => 0,
						'not_logged_in' => 0,
					];
					$departments = isset($departments) && is_array($departments) ? $departments : [];
					$snapshotByDepartment = isset($snapshot_by_department) && is_array($snapshot_by_department) ? $snapshot_by_department : [];
					$snapshotListBase = admin_url('Emt/getSnapshotStaffList');
					$monitoringUrl = admin_url('Emt/monitoring');
					?>
					<p class="text-muted tw-mb-2" style="font-size:13px;">
						<a href="<?php echo html_escape($monitoringUrl); ?>">Open Monitoring</a> to browse screenshots.
					</p>
					<div class="row snapshot-dashboard-filter">
						<div class="col-md-4">
							<label for="snapshot_dashboard_department">Filter counts by department</label>
							<select class="selectpicker" data-live-search="true" data-width="100%" id="snapshot_dashboard_department" aria-label="Department filter">
								<option value="">All departments</option>
								<?php foreach ($departments as $department) {
									$deptId = is_array($department) ? $department['departmentid'] : $department->departmentid;
									$deptName = is_array($department) ? $department['name'] : $department->name;
								?>
								<option value="<?php echo (int) $deptId; ?>"><?php echo html_escape($deptName); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="snapshot-ui-loading-target" id="snapshotDashboardMetrics">
					<div class="row snapshot-stat-row">
						<div class="col-md-4">
							<a href="<?php echo html_escape($snapshotListBase . '?type=active'); ?>" class="snapshot-stat-card" data-snapshot-list="active" aria-label="Total active employees — view list">
								<span class="snapshot-stat-card-head">
									<span class="snapshot-stat-label">Total active employees</span>
									<span class="snapshot-stat-card-eye" title="View list"><i class="fa fa-eye" aria-hidden="true"></i></span>
								</span>
								<span class="snapshot-stat-value" id="snapshotStatTotal"><?php echo (int) $snapshotSummary['total_active']; ?></span>
							</a>
						</div>
						<div class="col-md-4">
							<a href="<?php echo html_escape($snapshotListBase . '?type=logged_in'); ?>" class="snapshot-stat-card" data-snapshot-list="logged_in" aria-label="Today's snapshot logged-in — view list">
								<span class="snapshot-stat-card-head">
									<span class="snapshot-stat-label">Today's snapshot logged-in</span>
									<span class="snapshot-stat-card-eye" title="View list"><i class="fa fa-eye" aria-hidden="true"></i></span>
								</span>
								<span class="snapshot-stat-value" id="snapshotStatLoggedIn"><?php echo (int) $snapshotSummary['logged_in_today']; ?></span>
							</a>
						</div>
						<div class="col-md-4">
							<a href="<?php echo html_escape($snapshotListBase . '?type=not_logged_in'); ?>" class="snapshot-stat-card" data-snapshot-list="not_logged_in" aria-label="Not logged-in — view list">
								<span class="snapshot-stat-card-head">
									<span class="snapshot-stat-label">Not logged-in</span>
									<span class="snapshot-stat-card-eye" title="View list"><i class="fa fa-eye" aria-hidden="true"></i></span>
								</span>
								<span class="snapshot-stat-value" id="snapshotStatNotLoggedIn"><?php echo (int) $snapshotSummary['not_logged_in']; ?></span>
							</a>
						</div>
					</div>
					<h5 class="tw-font-semibold tw-mb-2">Department snapshot login</h5>
					<div class="table-responsive snapshot-dept-table">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Department</th>
									<th class="text-center">Active employees</th>
									<th class="text-center">Snapshot logged-in today</th>
									<th class="text-center">Not logged-in</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($snapshotByDepartment)) { ?>
								<tr><td colspan="4" class="text-muted text-center">No department data.</td></tr>
								<?php } else {
									foreach ($snapshotByDepartment as $deptRow) {
										$deptId = (int) ($deptRow['departmentid'] ?? 0);
										$deptQ = $deptId > 0 ? ('&department_id=' . $deptId) : '';
								?>
								<tr>
									<td><?php echo html_escape($deptRow['name'] ?? ''); ?></td>
									<td class="text-center">
										<a class="snapshot-dept-count" href="<?php echo html_escape($snapshotListBase . '?type=active' . $deptQ); ?>"><?php echo (int) ($deptRow['total_active'] ?? 0); ?></a>
									</td>
									<td class="text-center">
										<a class="snapshot-dept-count" href="<?php echo html_escape($snapshotListBase . '?type=logged_in' . $deptQ); ?>"><?php echo (int) ($deptRow['logged_in_today'] ?? 0); ?></a>
									</td>
									<td class="text-center">
										<a class="snapshot-dept-count" href="<?php echo html_escape($snapshotListBase . '?type=not_logged_in' . $deptQ); ?>"><?php echo (int) ($deptRow['not_logged_in'] ?? 0); ?></a>
									</td>
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
        </div>
    </div>
</div>

<?php $this->load->view('admin/emt/_snapshot_ui_loader'); ?>

<?php init_tail(); ?>
<?php $this->load->view('admin/emt/_snapshot_ui_loader_script'); ?>
<script>
var SNAPSHOT_SUMMARY_URL = <?php echo json_encode(admin_url('Emt/snapshot_dashboard_summary')); ?>;
var SNAPSHOT_LIST_PAGE = <?php echo json_encode(admin_url('Emt/getSnapshotStaffList')); ?>;

function updateSnapshotStatCardLinks() {
	var dept = ($('#snapshot_dashboard_department').val() || '').toString();
	$('.snapshot-stat-card').each(function () {
		var listType = ($(this).data('snapshot-list') || '').toString();
		if (!listType) {
			return;
		}
		var url = SNAPSHOT_LIST_PAGE + '?type=' + encodeURIComponent(listType);
		if (dept) {
			url += '&department_id=' + encodeURIComponent(dept);
		}
		$(this).attr('href', url);
	});
}

function refreshSnapshotSummaryCounts() {
	var dept = ($('#snapshot_dashboard_department').val() || '').toString();
	var $metrics = $('#snapshotDashboardMetrics');
	updateSnapshotStatCardLinks();
	snapshotUiLoader.show();
	snapshotUiLoader.setLoadingTarget($metrics, true);
	$.ajax({
		url: SNAPSHOT_SUMMARY_URL,
		data: { department_id: dept },
		dataType: 'json',
	}).done(function (resp) {
		if (!resp || !resp.success || !resp.summary) {
			return;
		}
		$('#snapshotStatTotal').text(resp.summary.total_active);
		$('#snapshotStatLoggedIn').text(resp.summary.logged_in_today);
		$('#snapshotStatNotLoggedIn').text(resp.summary.not_logged_in);
	}).always(function () {
		snapshotUiLoader.setLoadingTarget($metrics, false);
		snapshotUiLoader.hide();
	});
}

$(function () {
	var $dept = $('#snapshot_dashboard_department');
	if ($dept.length) {
		if ($dept.data('selectpicker')) {
			$dept.selectpicker('destroy');
		}
		$dept.selectpicker({ liveSearch: true, width: '100%', container: 'body' });
		$dept.on('changed.bs.select', function () {
			refreshSnapshotSummaryCounts();
		});
	}
	updateSnapshotStatCardLinks();
	snapshotUiLoader.bindNavigation('a.snapshot-stat-card, a.snapshot-dept-count, a[href*="Emt/monitoring"]');
});
</script>
</body>
</html>
