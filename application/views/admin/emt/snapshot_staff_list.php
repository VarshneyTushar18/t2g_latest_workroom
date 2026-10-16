<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.snapshot-list-toolbar { margin-bottom: 16px; }
.snapshot-list-toolbar .bootstrap-select { width: 100% !important; }
.snapshot-list-toolbar .bootstrap-select .dropdown-menu {
    z-index: 1060 !important;
}
#snapshotPreviewModal img { max-width: 100%; height: auto; }
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
                                        <th>Snapshot login time</th>
                                        <th>Screenshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($staff)) { ?>
                                    <tr><td colspan="4" class="text-muted text-center">No employees found.</td></tr>
                                    <?php } else {
                                        foreach ($staff as $row) { ?>
                                    <tr>
                                        <td><?php echo html_escape($row['name']); ?></td>
                                        <td><?php echo html_escape($row['department_name'] ?: '—'); ?></td>
                                        <td><?php echo $row['login_time'] !== '' ? html_escape($row['login_time']) : '—'; ?></td>
                                        <td>
                                            <?php if (!empty($row['screenshot_file_id'])) { ?>
                                            <button type="button" class="btn btn-default btn-xs snapshot-view-screenshot"
                                                data-file-id="<?php echo html_escape($row['screenshot_file_id']); ?>"
                                                data-name="<?php echo html_escape($row['name']); ?>">View screenshot</button>
                                            <?php } ?>
                                            <?php if (!empty($staff_folder_map[$row['staffid']])) { ?>
                                            <a class="btn btn-primary btn-xs" href="<?php echo html_escape(($monitoring_url ?? admin_url('Emt/monitoring')) . '?folder=' . urlencode($staff_folder_map[$row['staffid']])); ?>">Open gallery</a>
                                            <?php } elseif (empty($row['screenshot_file_id'])) { ?>
                                            —
                                            <?php } ?>
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

<div class="modal fade" id="snapshotPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="snapshotPreviewModalTitle">Snapshot</h4>
            </div>
            <div class="modal-body text-center">
                <img id="snapshotPreviewImage" src="" alt="Snapshot preview">
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php $this->load->view('admin/emt/_snapshot_ui_loader_script'); ?>
<script>
(function ($) {
    'use strict';

    var listBase = <?php echo json_encode($list_base_url ?? admin_url('Emt/getSnapshotStaffList')); ?>;
    var listType = <?php echo json_encode($list_type); ?>;
    var imageBase = <?php echo json_encode(admin_url('Emt/snapshotImage?id=')); ?>;

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

    snapshotUiLoader.bindNavigation('.panel-body a.btn[href*="monitoring"], a.btn-default[href*="getEmployeeId"]');

    $(document).on('click', '.snapshot-view-screenshot', function () {
        var fileId = ($(this).data('file-id') || '').toString();
        var name = ($(this).data('name') || '').toString();
        if (!fileId) {
            return;
        }
        snapshotUiLoader.show();
        $('#snapshotPreviewModalTitle').text(name ? name + ' — snapshot' : 'Snapshot');
        var $img = $('#snapshotPreviewImage');
        $img.off('load error').one('load error', function () {
            snapshotUiLoader.hide();
        });
        $img.attr('src', imageBase + encodeURIComponent(fileId));
        $('#snapshotPreviewModal').modal('show');
    });
})(jQuery);
</script>
</body>
</html>
