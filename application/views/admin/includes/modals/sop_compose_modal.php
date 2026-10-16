<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!is_staff_logged_in() || !can_create_sop()) {
    return;
}
$CI = &get_instance();
$CI->load->model('departments_model');
$departments = $CI->departments_model->get();
$department_options = [
    ['departmentid' => 'all', 'name' => _l('announcement_email_all_departments')],
];
foreach ($departments as $department) {
    $department_options[] = [
        'departmentid' => $department['departmentid'],
        'name'         => $department['name'],
    ];
}
$CI->load->model('staff_model');
$staff_rows = $CI->staff_model->get('', ['active' => 1]);
$staff_options = [
    ['staffid' => 'all', 'name' => _l('announcement_email_all_employees')],
];
foreach ($staff_rows as $member) {
    $staff_options[] = [
        'staffid' => $member['staffid'],
        'name'    => trim($member['firstname'] . ' ' . $member['lastname']),
    ];
}
?>
<div class="modal fade sop-compose-modal" id="sopComposeModal" tabindex="-1" role="dialog" aria-labelledby="sopComposeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('sop/send'), ['id' => 'sopComposeForm']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="sopComposeModalLabel"><?php echo _l('sop_new'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                echo render_select(
                    'email_departments[]',
                    $department_options,
                    ['departmentid', 'name'],
                    'announcement_email_departments',
                    ['all'],
                    [
                        'multiple'                => true,
                        'data-actions-box'        => true,
                        'data-live-search'        => true,
                        'data-width'              => '100%',
                        'id'                      => 'sop_email_departments',
                        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                    ],
                    [],
                    '',
                    '',
                    false
                );
                echo render_select(
                    'email_staffids[]',
                    $staff_options,
                    ['staffid', 'name'],
                    'sop_employees',
                    ['all'],
                    [
                        'multiple'                => true,
                        'data-actions-box'        => true,
                        'data-live-search'        => true,
                        'data-width'              => '100%',
                        'id'                      => 'sop_email_staffids',
                        'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                    ],
                    [],
                    '',
                    '',
                    false
                );
                echo render_input('name', 'sop_subject', '', 'text', ['id' => 'sop_subject']);
                ?>
                <div class="sop-message-editor-wrap announcement-message-field">
                    <p class="bold sop-message-label"><?php echo _l('announcement_message'); ?></p>
                    <?php echo render_textarea('message', '', '', ['rows' => 6], [], 'no-mbot', 'tinymce tinymce-sop'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('announcement_send'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
(function () {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var STAFF_URL = <?php echo json_encode(admin_url('sop/staff_options')); ?>;

    function sopSelectAllGuard($el) {
        var vals = $el.val() || [];
        if (vals.indexOf('all') !== -1 && vals.length > 1) {
            $el.selectpicker('val', ['all']);
        }
    }

    function reloadSopStaffOptions() {
        var $dept = $('#sop_email_departments');
        var depts = $dept.val() || ['all'];
        var $staff = $('#sop_email_staffids');
        var keepAll = ($staff.val() || []).indexOf('all') !== -1;
        $.get(STAFF_URL, { departments: depts }, function (resp) {
            if (!resp || !resp.success) {
                return;
            }
            var html = '<option value="all"><?php echo html_escape(_l('announcement_email_all_employees')); ?></option>';
            (resp.staff || []).forEach(function (row) {
                html += '<option value="' + row.staffid + '">' + $('<div>').text(row.name).html() + '</option>';
            });
            $staff.html(html);
            if ($staff.data('selectpicker')) {
                $staff.selectpicker('destroy');
            }
            $staff.selectpicker({
                liveSearch: true,
                actionsBox: true,
                width: '100%'
            });
            $staff.selectpicker('val', keepAll ? ['all'] : ['all']);
        });
    }

    $(function () {
        $('#sop_email_departments').on('changed.bs.select', function () {
            sopSelectAllGuard($(this));
            reloadSopStaffOptions();
        });
        $('#sop_email_staffids').on('changed.bs.select', function () {
            sopSelectAllGuard($(this));
        });
        function destroySopEditor() {
            if (typeof tinymce === 'undefined') {
                return;
            }
            var ed = tinymce.get('message');
            if (ed) {
                tinymce.remove(ed);
            }
        }

        function initSopEditor() {
            if (typeof init_editor !== 'function') {
                return;
            }
            destroySopEditor();
            if ($('#sopComposeModal .tinymce-sop').length === 0) {
                return;
            }
            init_editor('#sopComposeModal .tinymce-sop', {
                height: 400,
                menubar: true,
                plugins: [
                    'advlist autoresize autosave lists link image print hr codesample',
                    'visualblocks code fullscreen',
                    'media save table contextmenu',
                    'paste textcolor colorpicker',
                ],
                toolbar1:
                    'fontselect fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | image link | bullist numlist | restoredraft',
            });
            setTimeout(function () {
                var ed = typeof tinymce !== 'undefined' ? tinymce.get('message') : null;
                if (ed) {
                    ed.fire('ResizeEditor');
                }
            }, 150);
        }

        $('#sopComposeForm').on('submit', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });

        $('#sopComposeModal').on('hidden.bs.modal', function () {
            destroySopEditor();
        });

        $('#sopComposeModal').on('shown.bs.modal', function () {
            if ($('#sop_email_departments').data('selectpicker')) {
                $('#sop_email_departments').selectpicker('val', ['all']);
            }
            reloadSopStaffOptions();
            initSopEditor();
        });
        $(document).on('click', '[data-target="#sopComposeModal"], .js-open-sop-modal', function (e) {
            e.preventDefault();
            $('#sopComposeModal').modal('show');
        });
    });
})();
</script>
