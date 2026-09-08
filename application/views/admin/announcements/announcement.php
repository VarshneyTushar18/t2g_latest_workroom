<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
#message{
	visibility: visible !important; 
}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">


                        <?php $value = (isset($announcement) ? $announcement->name : ''); ?>
                        <?php echo render_input('name', 'announcement_name', $value); ?>

                        <?php
                        $selected_departments = [];
                        if (isset($announcement) && !empty($announcement->email_departments)) {
                            $selected_departments = array_filter(explode(',', (string) $announcement->email_departments));
                        }
                        $department_options = [
                            ['departmentid' => 'all', 'name' => _l('announcement_email_all_departments')],
                        ];
                        if (!empty($departments)) {
                            foreach ($departments as $department) {
                                $department_options[] = [
                                    'departmentid' => $department['departmentid'],
                                    'name'         => $department['name'],
                                ];
                            }
                        }
                        echo render_select(
                            'email_departments[]',
                            $department_options,
                            ['departmentid', 'name'],
                            'announcement_email_departments',
                            $selected_departments,
                            [
                                'multiple'                => true,
                                'data-actions-box'        => true,
                                'data-live-search'        => true,
                                'data-width'              => '100%',
                                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                            ],
                            [],
                            '',
                            '',
                            false
                        );
                        ?>
                        <p class="text-muted tw-mb-4"><?php echo _l('announcement_email_departments_help'); ?></p>

                        <?php
                        $selected_roles = [];
                        if (isset($announcement) && !empty($announcement->email_roles)) {
                            $selected_roles = array_filter(explode(',', (string) $announcement->email_roles));
                        }
                        $role_options = [
                            ['roleid' => 'all', 'name' => _l('announcement_email_all_employees')],
                        ];
                        if (!empty($roles)) {
                            foreach ($roles as $role) {
                                $role_options[] = [
                                    'roleid' => $role['roleid'],
                                    'name'   => $role['name'],
                                ];
                            }
                        }
                        echo render_select(
                            'email_roles[]',
                            $role_options,
                            ['roleid', 'name'],
                            'announcement_email_roles',
                            $selected_roles,
                            [
                                'multiple'                => true,
                                'data-actions-box'        => true,
                                'data-live-search'        => true,
                                'data-width'              => '100%',
                                'data-none-selected-text' => _l('dropdown_non_selected_tex'),
                            ],
                            [],
                            '',
                            '',
                            false
                        );
                        ?>
                        <p class="text-muted tw-mb-4"><?php echo _l('announcement_email_roles_help'); ?></p>

                        <p class="bold"><?php echo _l('announcement_message'); ?></p>
                        <?php $contents = ''; if (isset($announcement)) {
    $contents                           = $announcement->message;
} ?>
                        <?php echo render_textarea('message', '', $contents, [], [], '', 'tinymce'); ?>

                    </div>
                    <div class="panel-footer">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <div>

                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="showtostaff" id="showtostaff"
                                        <?php echo (!isset($announcement) || (isset($announcement) && $announcement->showtostaff == 1)) ? 'checked' : ''; ?>>
                                    <label for="showtostaff"><?php echo _l('announcement_show_to_staff'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="showtousers" id="showtousers"
                                        <?php echo isset($announcement) && $announcement->showtousers == 1 ? 'checked' : ''; ?>>
                                    <label for="showtousers"><?php echo _l('announcement_show_to_clients'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary checkbox-inline">
                                    <input type="checkbox" name="showname" id="showname"
                                        <?php echo isset($announcement) && $announcement->showname == 1 ? 'checked' : ''; ?>>
                                    <label for="showname"><?php echo _l('announcement_show_my_name'); ?></label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo _l('announcement_send'); ?></button>
                        </div>


                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    appValidateForm($('form'), {
        name: 'required',
        'email_departments[]': 'required',
        'email_roles[]': 'required',
        message: 'required'
    });

    $('select[name="email_departments[]"]').on('changed.bs.select', function () {
        var $el = $(this);
        var vals = $el.val() || [];
        if (vals.indexOf('all') !== -1 && vals.length > 1) {
            $el.selectpicker('val', ['all']);
        }
    });

    $('select[name="email_roles[]"]').on('changed.bs.select', function () {
        var $el = $(this);
        var vals = $el.val() || [];
        if (vals.indexOf('all') !== -1 && vals.length > 1) {
            $el.selectpicker('val', ['all']);
        }
    });
});
</script>
</body>

</html>
