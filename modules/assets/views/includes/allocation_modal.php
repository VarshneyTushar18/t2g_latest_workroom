<div class="modal fade" id="allocation_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('assets/allocation_asset'), ['id' => 'allocation-form']); ?>
        <div class="modal-content modalwidth">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo htmlspecialchars(_l('allocation_asset')); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('acction_code', 'allocation_code', ''); ?>

                        <table class="table border table-striped nomargintop">
                            <tbody>
                                <tr class="project-overview">
                                    <td class="bold"><?php echo htmlspecialchars(_l('asset_code')); ?></td>
                                    <td><?php echo htmlspecialchars($assets->assets_code); ?></td>
                                    <td class="bold"><?php echo htmlspecialchars(_l('asset_name')); ?></td>
                                    <td><?php echo htmlspecialchars($assets->assets_name); ?></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="col-md-6">
                        <label for="acction_to"><?php echo htmlspecialchars(_l('receiver')); ?></label>
                        <select name="acction_to" id="acction_to" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                            <option value=""></option>
                            <?php foreach ($staffs as $s) { ?>
                                <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <?php $attr = [];
                        $attr       = ['min' => 1, 'max' => $assets->amount - $assets->total_allocation, 'step' => 1];
                        echo render_input('amount', 'amounts', '', 'number', $attr); ?>
                    </div>
                    <div class="col-md-6">
                        <table class="table border table-striped margintop25">
                            <tbody>
                                <tr class="project-overview">
                                    <td class="bold"><?php echo htmlspecialchars(_l('rest')) . ':'; ?></td>
                                    <td><?php echo htmlspecialchars($assets->amount - $assets->total_allocation . '/' . $assets->amount); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_datetime_input('time_acction', 'allocation_time'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('asset_location', 'asset_location', get_asset_location($assets->asset_location)); ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                        $managers = $this->db->query('select * from tblstaff_info left join tblstaff on tblstaff_info.staffid = tblstaff.staffid where tblstaff_info.manageleave = 1;')->result_array();
                        // $managers = $this->db->get_where(db_prefix().'staff_info',array('manageleave'=>1))->result_array();
                        ?>
                        <label for="reporting_manager"><?php echo 'Reporting manager'; ?></label>
                        <select name="reporting_manager" onchange="set_manager_id()" id="reporting_manager" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                            <option value=""></option>
                            <?php foreach ($staffs as $s) { ?>
                                <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('acction_location', 'handover_location', ''); ?>
                    </div>


                    <!-- custom field for uploading allocation image -->
                    <div class="col-md-12">
                        <div class="attachment">
                            <div class="form-group">
                                <label class="control-label"><small class="req text-danger">* </small><?php echo 'Upload Documents' ?></label>
                                <div>
                                    <label for="allocation_images" class="btn btn-default">
                                        <i class="fa fa-paperclip"></i> Attach files
                                    </label>
                                    <input type="file" extension="gif,jpg,jpeg,pdf" filesize="<?php echo file_upload_max_size(); ?>" name="images[]" id="allocation_images" multiple required style="display: none;" accept=".gif,.jpg,.jpeg,.pdf">
                                    <span id="allocation_attachment_count" class="text-muted mleft10">No files selected</span>
                                </div>
                                <div id="allocation_selected_files" class="mtop10"></div>
                            </div>
                        </div>
                    </div>
                    <!-- end -->


                    <div class="col-md-12">
                        <?php echo render_textarea('acction_reason', 'acction_reason', ''); ?>
                        <?php echo form_hidden('assets', $assets->id); ?>
                        <?php echo form_hidden('type', 'allocation'); ?>
                        <?php

                        echo form_hidden('reporting_manager_empid',);

                        ?>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="" class="btn btn-default" data-dismiss="modal"><?php echo htmlspecialchars(_l('close')); ?></button>
                <button id="sm_btn" type="submit" class="btn btn-info"><?php echo htmlspecialchars(_l('submit')); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    init_datepicker();
    appValidateForm($('#allocation-form'), {
        acction_to: 'required',
        acction_from: 'required',
        amount: 'required',
        time_acction: 'required',
        acction_location: 'required',
        reporting_manager: 'required',
        acction_code: {
            required: true,
            remote: {
                url: site_url + "admin/assets/acction_code_exists",
                type: 'post',
                data: {
                    assets_code: function() {
                        return $('input[name="acction_code"]').val();
                    }
                }
            }
        }
    });
    $('.selectpicker').selectpicker({});

    var allocationSelectedFiles = [];

    function syncAllocationInputFiles() {
        var input = document.getElementById('allocation_images');
        if (!input) return;

        var dataTransfer = new DataTransfer();
        allocationSelectedFiles.forEach(function(file) {
            dataTransfer.items.add(file);
        });
        input.files = dataTransfer.files;
        updateAllocationSelectedFiles(input.files);
    }

    function updateAllocationSelectedFiles(fileList) {
        var $container = $('#allocation_selected_files');
        var files = Array.from(fileList || []);

        $container.empty();

        if (!files.length) {
            $('#allocation_attachment_count').text('No files selected');
            return;
        }

        $('#allocation_attachment_count').text(files.length + ' file' + (files.length === 1 ? '' : 's') + ' selected');

        files.forEach(function(file, index) {
            var $row = $('<div class="selected-file-row" style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px; padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#fafafa;"></div>');
            var $name = $('<span style="display:inline-block; max-width:80%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle;">').text(file.name);
            var $remove = $('<button type="button" class="btn btn-xs btn-danger remove-allocation-file" style="padding:2px 8px;">Remove</button>');
            $remove.attr('data-index', index);
            $row.append($name).append($remove);
            $container.append($row);
        });
    }

    $('#allocation_images').on('change', function(event) {
        var newFiles = Array.from(event.target.files || []);
        var existingFiles = allocationSelectedFiles || [];

        allocationSelectedFiles = existingFiles.concat(newFiles.filter(function(file) {
            return !existingFiles.some(function(existingFile) {
                return existingFile.name === file.name && existingFile.size === file.size && existingFile.lastModified === file.lastModified;
            });
        }));

        syncAllocationInputFiles();
    });

    $('#allocation_selected_files').on('click', '.remove-allocation-file', function() {
        var index = parseInt($(this).data('index'), 10);
        var input = document.getElementById('allocation_images');
        var files = Array.from(input.files || []);

        if (index >= 0 && index < files.length) {
            files.splice(index, 1);
            allocationSelectedFiles = files;
            syncAllocationInputFiles();
        }
    });

    // function will put the manager ud
    function set_manager_id() {
        var id = document.querySelector("[name='reporting_manager']").value;
        // alert(id);
        document.querySelector("[name='reporting_manager_empid']").value = id;

    }
</script>