<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$canEditInterval = !empty($can_edit_interval);
$intervalPresets = isset($interval_presets) && is_array($interval_presets) ? $interval_presets : [
    '00:10:00' => '10 minutes (default)',
];
$defaultInterval = html_escape($snapshot_interval_label ?? '00:10:00');
?>
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

    .table-loading table thead tr {
        height: auto !important;
    }

    .interval-filters label {
        display: block;
        margin-bottom: 6px;
    }

    .interval-bulk-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px 16px;
        margin-bottom: 15px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .interval-bulk-bar .form-group {
        margin-bottom: 0;
    }

    .interval-input {
        min-width: 140px;
    }

    .table-timesheets-report .interval-save-row.btn-xs {
        margin-top: 4px;
    }
</style>
<div id="wrapper">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="tw-font-semibold tw-text-lg">
                                <span><i class="fa fa-desktop"></i> Staff Interval Data </span>
                            </div>
                            <p class="text-muted mtop10">
                                Set screenshot capture interval per employee (<code>H:i:s</code> duration).
                                Default is <strong>10 minutes</strong> (<code><?php echo $defaultInterval; ?></code>).
                                The Snapshot desktop app loads each staff member&apos;s value from the API.
                            </p>

                            <div class="col-md-12">
                                <hr class="mtop10"/>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <?php if ($canEditInterval) { ?>
                        <div class="interval-bulk-bar">
                            <div class="form-group">
                                <label for="bulk_interval_time">Interval for selected</label>
                                <select id="bulk_interval_time" class="form-control interval-input">
                                    <?php foreach ($intervalPresets as $value => $label) { ?>
                                    <option value="<?php echo html_escape($value); ?>"<?php echo $value === '00:10:00' ? ' selected' : ''; ?>>
                                        <?php echo html_escape($label); ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" id="btn_apply_bulk_interval">Apply to selected</button>
                            <button type="button" class="btn btn-default" id="btn_select_filtered_dept">Select all in department</button>
                            <button type="button" class="btn btn-default" id="btn_clear_interval_selection">Clear selection</button>
                        </div>
                        <?php } ?>

                        <div class="row interval-filters mbot15">
                            <div class="col-md-4">
                                <label for="filter_department">Department</label>
                                <select id="filter_department" class="selectpicker" data-live-search="true" data-width="100%">
                                    <option value="">All departments</option>
                                    <?php
                                    $departments = isset($departments) && is_array($departments) ? $departments : [];
                                    foreach ($departments as $department) {
                                        $deptId = is_array($department) ? $department['departmentid'] : $department->departmentid;
                                        $deptName = is_array($department) ? $department['name'] : $department->name;
                                    ?>
                                    <option value="<?php echo (int) $deptId; ?>"><?php echo html_escape($deptName); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <table class="table table-timesheets-report">
                            <thead>
                            <tr>
                                <?php if ($canEditInterval) { ?>
                                <th style="width:36px;">
                                    <input type="checkbox" id="interval_select_all_page" title="Select all on this page">
                                </th>
                                <?php } ?>
                                <th><?php echo 'S.No.'; ?></th>
                                <th><?php echo 'EMP ID'; ?></th>
                                <th><?php echo 'Name'; ?></th>
                                <th><?php echo 'Department'; ?></th>
                                <th><?php echo 'Interval'; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 1;
                            foreach ($result as $res) {
                                $intervalVal = trim((string) ($res->time_interval ?? ''));
                                if ($intervalVal === '' || $intervalVal === '00:00:00') {
                                    $intervalVal = $snapshot_interval_label ?? '00:10:00';
                                }
                                $deptIds = trim((string) ($res->department_ids ?? ''), ',');
                                $rowPresets = $intervalPresets;
                                if (!array_key_exists($intervalVal, $rowPresets)) {
                                    $rowPresets[$intervalVal] = $intervalVal;
                                }
                            ?>
                            <tr data-interval-id="<?php echo (int) $res->id; ?>" data-dept-ids="<?php echo html_escape($deptIds); ?>">
                                <?php if ($canEditInterval) { ?>
                                <td>
                                    <input type="checkbox" class="interval-row-checkbox" value="<?php echo (int) $res->id; ?>">
                                </td>
                                <?php } ?>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo html_escape($res->staff_identifi); ?></td>
                                <td><?php echo html_escape(trim($res->firstname . ' ' . $res->lastname)); ?></td>
                                <td><?php echo html_escape($res->department_names ?: '—'); ?></td>
                                <td>
                                    <?php if ($canEditInterval) { ?>
                                    <select class="form-control input-sm interval-row-select" data-interval-id="<?php echo (int) $res->id; ?>">
                                        <?php foreach ($rowPresets as $value => $label) { ?>
                                        <option value="<?php echo html_escape($value); ?>"<?php echo $intervalVal === $value ? ' selected' : ''; ?>>
                                            <?php echo html_escape($value); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                    <input type="text" class="form-control input-sm interval-row-custom mtop5" placeholder="Custom H:i:s" value="<?php echo html_escape($intervalVal); ?>" data-interval-id="<?php echo (int) $res->id; ?>">
                                    <button type="button" class="btn btn-info btn-xs interval-save-row" data-interval-id="<?php echo (int) $res->id; ?>">Save</button>
                                    <?php } else { ?>
                                    <span class="interval-value"><?php echo html_escape($intervalVal); ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(function () {
    var canEdit = <?php echo $canEditInterval ? 'true' : 'false'; ?>;
    var checkboxOffset = canEdit ? 1 : 0;
    var deptColIndex = 3 + checkboxOffset;
    var nameColIndex = 2 + checkboxOffset;

    var timesheetsTable = $('.table-timesheets-report').DataTable({
        pageLength: 25,
        order: [[nameColIndex, 'asc']],
        columnDefs: [
            { targets: canEdit ? [0, 1] : [0], orderable: false }
        ]
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable !== timesheetsTable.table().node()) {
            return true;
        }
        var deptId = ($('#filter_department').val() || '').toString();
        if (!deptId) {
            return true;
        }
        var row = timesheetsTable.row(dataIndex).node();

        return rowMatchesDepartment($(row), deptId);
    });

    if ($('#filter_department').length && !$('#filter_department').data('selectpicker')) {
        $('#filter_department').selectpicker({ liveSearch: true, width: '100%' });
    }

    function rowMatchesDepartment($row, deptId) {
        if (!deptId) {
            return false;
        }
        var raw = ($row.attr('data-dept-ids') || '').toString();
        if (!raw) {
            return false;
        }
        var parts = raw.split(',');
        for (var i = 0; i < parts.length; i++) {
            if (parts[i].trim() === String(deptId)) {
                return true;
            }
        }
        return false;
    }

    $('#filter_department').on('change', function () {
        timesheetsTable.draw();
    });

    if (!canEdit) {
        return;
    }

    function selectedIntervalIds() {
        var ids = [];
        $('.interval-row-checkbox:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function rowIntervalValue($row) {
        var custom = $.trim($row.find('.interval-row-custom').val());
        if (custom) {
            return custom;
        }
        return $row.find('.interval-row-select').val();
    }

    function postIntervalUpdate(payload, onSuccess) {
        $.post(admin_url + 'Emt/EmtUpdateInterval', payload)
            .done(function (res) {
                if (typeof res === 'string') {
                    try { res = JSON.parse(res); } catch (e) { res = {}; }
                }
                if (res.success) {
                    if (typeof onSuccess === 'function') {
                        onSuccess(res);
                    }
                    alert_float('success', 'Interval updated.');
                } else {
                    alert_float('danger', res.message || 'Could not update interval.');
                }
            })
            .fail(function () {
                alert_float('danger', 'Could not update interval.');
            });
    }

    $('#interval_select_all_page').on('change', function () {
        var checked = $(this).is(':checked');
        timesheetsTable.rows({ page: 'current', search: 'applied' }).every(function () {
            $(this.node()).find('.interval-row-checkbox').prop('checked', checked);
        });
    });

    $('#btn_select_filtered_dept').on('click', function () {
        var deptId = ($('#filter_department').val() || '').toString();
        if (!deptId) {
            alert_float('warning', 'Choose a department first.');
            return;
        }
        $('.interval-row-checkbox').prop('checked', false);
        timesheetsTable.rows({ search: 'applied' }).every(function () {
            var $row = $(this.node());
            if (rowMatchesDepartment($row, deptId)) {
                $row.find('.interval-row-checkbox').prop('checked', true);
            }
        });
    });

    $('#btn_clear_interval_selection').on('click', function () {
        $('.interval-row-checkbox').prop('checked', false);
        $('#interval_select_all_page').prop('checked', false);
    });

    $('#btn_apply_bulk_interval').on('click', function () {
        var ids = selectedIntervalIds();
        var time = $('#bulk_interval_time').val();
        if (!ids.length) {
            alert_float('warning', 'Select at least one employee.');
            return;
        }
        postIntervalUpdate({ ids: ids, time: time }, function () {
            ids.forEach(function (id) {
                var $row = $('tr[data-interval-id="' + id + '"]');
                $row.find('.interval-row-select').val(time);
                $row.find('.interval-row-custom').val(time);
            });
        });
    });

    $(document).on('click', '.interval-save-row', function () {
        var id = $(this).data('interval-id');
        var $row = $(this).closest('tr');
        var time = rowIntervalValue($row);
        postIntervalUpdate({ date: id, time: time }, function (res) {
            $row.find('.interval-row-select').val(res.time);
            $row.find('.interval-row-custom').val(res.time);
        });
    });
});
</script>
</body>

</html>
