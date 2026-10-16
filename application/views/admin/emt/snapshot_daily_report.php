<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.snapshot-daily-report-toolbar { margin-bottom: 18px; }
.snapshot-daily-report-toolbar .bootstrap-select { width: 100% !important; }
.snapshot-daily-report-toolbar .bootstrap-select .dropdown-menu {
    z-index: 1060 !important;
}
.snapshot-daily-report-note {
    margin-bottom: 16px;
    font-size: 13px;
    color: #64748b;
}
.snapshot-daily-report-table th {
    background: #141e46 !important;
    color: #fff !important;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.snapshot-daily-report-table td {
    vertical-align: middle !important;
    font-size: 13px;
}

.snapshot-daily-report-table.dataTable thead > tr > th.sorting,
.snapshot-daily-report-table.dataTable thead > tr > th.sorting_asc,
.snapshot-daily-report-table.dataTable thead > tr > th.sorting_desc {
    padding-right: 30px !important;
    cursor: pointer;
}

.snapshot-daily-report-table.dataTable thead .sorting:after,
.snapshot-daily-report-table.dataTable thead .sorting_asc:after,
.snapshot-daily-report-table.dataTable thead .sorting_desc:after,
.snapshot-daily-report-table.dataTable thead .sorting:before,
.snapshot-daily-report-table.dataTable thead .sorting_asc:before,
.snapshot-daily-report-table.dataTable thead .sorting_desc:before {
    color: #fff !important;
    opacity: 0.45;
}

.snapshot-daily-report-table.dataTable thead .sorting_asc:after,
.snapshot-daily-report-table.dataTable thead .sorting_desc:after {
    opacity: 0.95;
}

/* Perfex adds .table-loading on DataTables wrappers; clear it so rows are visible */
#snapshot-daily-report-table_wrapper.table-loading table thead th,
#snapshot-daily-report-table_wrapper.table-loading table tbody tr {
    opacity: 1 !important;
}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo html_escape($dashboard_url); ?>" class="btn btn-default tw-mb-3">&larr; Snapshot dashboard</a>
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo html_escape($title); ?></h4>

                <div class="panel_s">
                    <div class="panel-body">
                        <p class="snapshot-daily-report-note">
                            This report is for a <strong>completed</strong> calendar day. By default it shows
                            <strong>yesterday</strong> (<?php echo html_escape(date('F j, Y', strtotime($default_report_date . ' 12:00:00'))); ?>).
                            Today's data is not included until the day has ended.
                        </p>

                        <div class="row snapshot-daily-report-toolbar">
                            <div class="col-md-3">
                                <label for="snapshot_report_date">Report date</label>
                                <input type="date" class="form-control" id="snapshot_report_date"
                                    max="<?php echo html_escape($default_report_date); ?>"
                                    value="<?php echo html_escape($report_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="snapshot_report_department">Department</label>
                                <select class="selectpicker" data-live-search="true" data-width="100%" id="snapshot_report_department">
                                    <option value="">All departments</option>
                                    <?php foreach ($departments as $department) {
                                        $deptId = is_array($department) ? $department['departmentid'] : $department->departmentid;
                                        $deptName = is_array($department) ? $department['name'] : $department->name;
                                        $sel = ((int) $department_id === (int) $deptId) ? ' selected' : '';
                                    ?>
                                    <option value="<?php echo (int) $deptId; ?>"<?php echo $sel; ?>><?php echo html_escape($deptName); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="snapshot_report_login">Snapshot login</label>
                                <select class="selectpicker" data-width="100%" id="snapshot_report_login">
                                    <?php
                                    $loginFilter = isset($snapshot_login_filter) ? (string) $snapshot_login_filter : 'all';
                                    $loginOptions = [
                                        'all'           => 'All employees',
                                        'logged_in'     => 'Snapshot logged-in',
                                        'not_logged_in' => 'Not snapshot logged-in',
                                    ];
                                    foreach ($loginOptions as $val => $label) {
                                        $sel = ($loginFilter === $val) ? ' selected' : '';
                                        echo '<option value="' . html_escape($val) . '"' . $sel . '>' . html_escape($label) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <h5 class="tw-font-semibold tw-mb-2"><?php echo html_escape($report_date_label); ?></h5>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered snapshot-daily-report-table" id="snapshot-daily-report-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>First check-in</th>
                                        <th>Snapshot log-in</th>
                                        <th>Snapshot log-out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)) { ?>
                                    <tr><td colspan="5" class="text-muted text-center">No employees found for this filter.</td></tr>
                                    <?php } else {
                                        foreach ($rows as $row) { ?>
                                    <tr>
                                        <td><?php echo html_escape($row['name']); ?></td>
                                        <td><?php echo html_escape($row['department_name']); ?></td>
                                        <td data-order="<?php echo html_escape($row['first_checkin_raw'] ?? ''); ?>">
                                            <?php echo $row['first_checkin'] !== '' ? html_escape($row['first_checkin']) : '—'; ?>
                                        </td>
                                        <td data-order="<?php echo html_escape($row['snapshot_login_raw'] ?? ''); ?>">
                                            <?php echo $row['snapshot_login'] !== '' ? html_escape($row['snapshot_login']) : '—'; ?>
                                        </td>
                                        <td data-order="<?php echo html_escape($row['snapshot_logout_raw'] ?? ''); ?>">
                                            <?php echo $row['snapshot_logout'] !== '' ? html_escape($row['snapshot_logout']) : '—'; ?>
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
<?php $this->load->view('admin/emt/_snapshot_ui_loader'); ?>
<?php init_tail(); ?>
<?php $this->load->view('admin/emt/_snapshot_ui_loader_script'); ?>
<script>
(function ($) {
    'use strict';

    function initSnapshotReportSelectpickers() {
        $('#snapshot_report_department, #snapshot_report_login').each(function () {
            var $el = $(this);
            if (!$el.length) {
                return;
            }
            if ($el.data('selectpicker')) {
                $el.selectpicker('destroy');
            }
            $el.selectpicker({
                liveSearch: $el.is('#snapshot_report_department'),
                width: '100%',
                container: 'body',
            });
        });
    }

    var baseUrl = <?php echo json_encode($report_base_url); ?>;

    function reloadReport() {
        var date = ($('#snapshot_report_date').val() || '').toString();
        var dept = ($('#snapshot_report_department').val() || '').toString();
        var login = ($('#snapshot_report_login').val() || 'all').toString();
        var url = baseUrl;
        var q = [];
        if (date) {
            q.push('date=' + encodeURIComponent(date));
        }
        if (dept) {
            q.push('department_id=' + encodeURIComponent(dept));
        }
        if (login && login !== 'all') {
            q.push('snapshot_login=' + encodeURIComponent(login));
        }
        if (q.length) {
            url += '?' + q.join('&');
        }
        snapshotUiLoader.navigate(url);
    }

    initSnapshotReportSelectpickers();

    $('#snapshot_report_date').on('change', reloadReport);
    $('#snapshot_report_department').on('changed.bs.select', reloadReport);
    $('#snapshot_report_login').on('changed.bs.select', reloadReport);

    var $reportTable = $('#snapshot-daily-report-table');
    if ($reportTable.length && $reportTable.find('tbody tr td[colspan]').length === 0 && $.fn.DataTable) {
        $reportTable.DataTable({
            paging: true,
            pageLength: 25,
            searching: true,
            order: [[0, 'asc']],
            columnDefs: [
                { targets: [2, 3, 4], type: 'string' }
            ],
            language: {
                emptyTable: 'No employees found for this filter.'
            },
            initComplete: function () {
                var $t = $(this.api().table().node());
                $t.removeClass('dt-table-loading');
                $t.parents('.table-loading').removeClass('table-loading');
                snapshotUiLoader.hide();
            }
        });
    }

    snapshotUiLoader.bindNavigation('a.btn-default[href*="getEmployeeId"]');
})(jQuery);
</script>
</body>
</html>
