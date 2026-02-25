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
                                <label for="attachment" class="control-label"><small class="req text-danger">* </small><?php echo 'Upload Documents' ?></label>
                                <input type="file" extension="gif,png,jpg,jpeg,pdf" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="images[]" id="images" multiple required>
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


    // function will put the manager ud
    function set_manager_id() {
        var id = document.querySelector("[name='reporting_manager']").value;
        // alert(id);
        document.querySelector("[name='reporting_manager_empid']").value = id;

    }
</script>