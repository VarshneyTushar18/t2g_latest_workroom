<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.snapshot-list-toolbar { margin-bottom: 16px; }
.snapshot-list-toolbar .bootstrap-select { width: 100% !important; }
.snapshot-list-toolbar .bootstrap-select .dropdown-menu {
    z-index: 1060 !important;
}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo html_escape($dashboard_url); ?>" class="btn btn-default tw-mb-3">&larr; Dashboard</a>
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo html_escape($title); ?></h4>

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row snapshot-list-toolbar">
                            <div class="col-md-4">
                                <label for="snapshot_list_department">Department</label>
                                <select class="selectpicker" data-width="100%" id="snapshot_list_department" data-live-search="true">
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
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>First check-in</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($staff)) { ?>
                                    <tr><td colspan="3" class="text-muted text-center">No employees found.</td></tr>
                                    <?php } else {
                                        foreach ($staff as $row) {
                                            $firstCheckin = isset($row['first_checkin']) ? (string) $row['first_checkin'] : '';
                                            if ($firstCheckin === '' && !empty($row['login_time'])) {
                                                $firstCheckin = (string) $row['login_time'];
                                            }
                                        ?>
                                    <tr>
                                        <td><?php echo html_escape($row['name']); ?></td>
                                        <td><?php echo html_escape($row['department_name'] ?: '—'); ?></td>
                                        <td><?php echo $firstCheckin !== '' ? html_escape($firstCheckin) : '—'; ?></td>
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

    var listBase = <?php echo json_encode($list_base_url ?? admin_url('Emt/getSnapshotStaffList')); ?>;
    var listType = <?php echo json_encode($list_type); ?>;

    var $dept = $('#snapshot_list_department');
    if ($dept.length) {
        if ($dept.data('selectpicker')) {
            $dept.selectpicker('destroy');
        }
        $dept.selectpicker({ liveSearch: true, width: '100%', container: 'body' });
    }

    $dept.on('changed.bs.select', function () {
        var dept = ($(this).val() || '').toString();
        var url = listBase + '?type=' + encodeURIComponent(listType);
        if (dept) {
            url += '&department_id=' + encodeURIComponent(dept);
        }
        snapshotUiLoader.navigate(url);
    });

    snapshotUiLoader.bindNavigation('a.btn-default[href*="getEmployeeId"]');
})(jQuery);
</script>
</body>
</html>
