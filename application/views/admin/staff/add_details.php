<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
?>

<div id="wrapper">


    <div class="content">

        <?php if (isset($member)) { ?>

            <?php $this->load->view('admin/staff/stats'); ?>

            <div class="member">

                <?php echo form_hidden('isedit'); ?>

                <?php echo form_hidden('memberid', $member->staffid); ?>

            </div>

        <?php } ?>

        <div class="row">

            <?php if (isset($member)) { ?>

                <div class="col-md-12">

                    <?php if (total_rows(db_prefix() . 'departments', ['email' => $member->email]) > 0) { ?>

                        <div class="alert alert-danger">

                            The staff member email exists also as support department email, according to the docs, the
                            support

                            department email must be unique email in the system, you must change the staff email or the
                            support

                            department email in order all the features to work properly.

                        </div>

                    <?php } ?>

                    <div class="tw-flex tw-justify-between">

                        <h4 class="tw-mb-0 tw-font-semibold tw-text-lg tw-text-neutral-700">

                            <?php echo $member->firstname . ' ' . $member->lastname; ?>

                            <?php if ($member->last_activity && $member->staffid != get_staff_user_id()) { ?>

                                <small> -
                                    <?php echo _l('last_active'); ?>:

                                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($member->last_activity); ?>">

                                        <?php echo time_ago($member->last_activity); ?>

                                    </span>

                                </small>

                            <?php } ?>

                        </h4>

                        <a href="#" onclick="small_table_full_view(); return false;" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="toggle_view tw-mt-3 tw-shrink-0 tw-inline-flex tw-items-center tw-justify-center hover:tw-text-neutral-800 active:tw-text-neutral-800 hover:tw-bg-neutral-300 tw-h-10 tw-w-10 tw-rounded-full tw-bg-neutral-200 tw-text-neutral-500">

                            <i class="fa fa-expand"></i></a>

                    </div>

                </div>

            <?php } ?>

            <?php echo form_open_multipart($this->uri->uri_string(), ['class' => 'staff-form', 'autocomplete' => 'off']); ?>

            <div class="col-md-<?php if (!isset($member)) {

                                    echo '8 col-md-offset-2';
                                } else {

                                    echo '5';
                                } ?>" id="small-table">

                <div class="panel_s">

                    <div class="panel-body ">

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">

                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>

                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>

                            <div class="horizontal-tabs">

                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">

                                    <li role="presentation" class="active">

                                        <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">

                                            <?php echo "Add Details"; ?>

                                        </a>

                                    </li>

                                    <!-- <li role="presentation">

                                        <a href="#staff_permissions" aria-controls="staff_permissions" role="tab" data-toggle="tab">

                                            <?php echo _l('staff_add_edit_permissions'); ?>

                                        </a>

                                    </li> -->

                                </ul>

                            </div>

                        </div>

                        <div class="tab-content tw-mt-5">

                            <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">

                                <div class="is-not-staff<?php if (isset($member) && $member->admin == 1) {

                                                            echo ' hide';
                                                        } ?>">

                                </div>


                                <?php $attrs = (isset($member) ? [] : ['autofocus' => true]); ?>

                                <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname')), 'Employee', $attrs); ?>

                                <?php $value = (isset($member) ? $member->lastname : ''); ?>

                                <?php echo render_select('departmentid', $roles, ['roleid', 'name'], 'Department'); ?>

                                <?php echo render_input('empid', 'Employee Id', ''); ?>

                                <?php echo render_date_input('doj', 'Date of Joining', ''); ?>

                                <?php echo render_input('shiftstart', 'Shift Start Timing', '', 'time'); ?>

                                <?php //echo render_select('manageleave', array(array('input_value'=>'1' , 'input_label'=>'Yes'),array('input_value'=>'0', 'input_label'=>'No')), ['input_value','input_label'], 'Leave Management'); 
                                ?>

                                <div class="form-group">
                                    <label for="manageleave" class="control-label">Leave Management</label>
                                    <select name="manageleave" id="manageleave" class="selectpicker" data-width="100%">
                                        <option value=""></option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="attendance_view_edit" class="control-label">Read Only Mode</label>
                                    <select name="attendance_view_edit" id="attendance_view_edit" class="selectpicker" data-width="100%">
                                        <option value=""></option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>

                                <?php //echo render_select('attendance_view_edit', array(array('input_value'=>'1' , 'input_label'=>'Yes'),array('input_value'=>'0', 'input_label'=>'No')), ['input_value','input_label'], 'Attendance View Only?'); 
                                ?>

                                <?php attendance_permission(); ?>

                                <!-- render_input($name, $label = '', $value = '', $type = 'text', $input_attrs = [], $form_group_attr = [], $form_group_class = '', $input_class = '') -->


                            </div>

                            <div role="tabpanel" class="tab-pane" id="staff_permissions">

                                <?php

                                hooks()->do_action('staff_render_permissions');

                                $selected = '';

                                foreach ($roles as $role) {

                                    if (isset($member)) {

                                        if ($member->role == $role['roleid']) {

                                            $selected = $role['roleid'];
                                        }
                                    } else {

                                        $default_staff_role = get_option('default_staff_role');

                                        if ($default_staff_role == $role['roleid']) {

                                            $selected = $role['roleid'];
                                        }
                                    }
                                }

                                ?>


                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="btn-bottom-toolbar text-right">

                <button type="submit" class="btn btn-primary">
                    <?php echo _l('submit'); ?>
                </button>

            </div>

            <?php echo form_close(); ?>

            <?php if (isset($member)) { ?>

                <div class="col-md-7 small-table-right-col">

                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">

                        <?php echo _l('staff_add_edit_notes'); ?>

                    </h4>

                    <div class="panel_s">

                        <div class="panel-body">


                            <a href="#" class="btn btn-success" onclick="slideToggle('.usernote'); return false;">
                                <?php echo _l('new_note'); ?>
                            </a>

                            <div class="clearfix"></div>

                            <hr class="hr-panel-separator" />

                            <div class="mbot15 usernote hide inline-block full-width">

                                <?php echo form_open(admin_url('misc/add_note/' . $member->staffid . '/staff')); ?>

                                <?php echo render_textarea('description', 'staff_add_edit_note_description', '', ['rows' => 5]); ?>

                                <button class="btn btn-primary pull-right mbot15">
                                    <?php echo _l('submit'); ?>
                                </button>

                                <?php echo form_close(); ?>

                            </div>

                            <div class="clearfix"></div>

                            <div class="mtop15">

                                <table class="table dt-table" data-order-col="2" data-order-type="desc">

                                    <thead>

                                        <tr>

                                            <th width="50%">
                                                <?php echo _l('staff_notes_table_description_heading'); ?>
                                            </th>

                                            <th>
                                                <?php echo _l('staff_notes_table_addedfrom_heading'); ?>
                                            </th>

                                            <th>
                                                <?php echo _l('staff_notes_table_dateadded_heading'); ?>
                                            </th>

                                            <th>
                                                <?php echo _l('options'); ?>
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php foreach ($user_notes as $note) { ?>

                                            <tr>

                                                <td width="50%">

                                                    <div data-note-description="<?php echo $note['id']; ?>">

                                                        <?php echo check_for_links($note['description']); ?>

                                                    </div>

                                                    <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide inline-block full-width">

                                                        <textarea name="description" class="form-control" rows="4"><?php echo clear_textarea_breaks($note['description']); ?></textarea>

                                                        <div class="text-right mtop15">

                                                            <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;">
                                                                <?php echo _l('cancel'); ?>
                                                            </button>

                                                            <button type="button" class="btn btn-primary" onclick="edit_note(<?php echo $note['id']; ?>);">
                                                                <?php echo _l('update_note'); ?>
                                                            </button>

                                                        </div>

                                                    </div>

                                                </td>

                                                <td>
                                                    <?php echo $note['firstname'] . ' ' . $note['lastname']; ?>
                                                </td>

                                                <td data-order="<?php echo $note['dateadded']; ?>">

                                                    <?php echo _dt($note['dateadded']); ?>
                                                </td>

                                                <td>

                                                    <div class="tw-flex tw-items-center tw-space-x-3">

                                                        <?php if ($note['addedfrom'] == get_staff_user_id() || has_permission('staff', '', 'delete')) { ?>

                                                            <a href="#" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">

                                                                <i class="fa-regular fa-pen-to-square fa-lg"></i>

                                                            </a>

                                                            <a href="<?php echo admin_url('misc/delete_note/' . $note['id']); ?>" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">

                                                                <i class="fa-regular fa-trash-can fa-lg"></i>

                                                            </a>

                                                        <?php } ?>

                                                    </div>

                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">

                        <?php echo _l('task_timesheets'); ?> &
                        <?php echo _l('als_reports'); ?>

                    </h4>

                    <div class="panel_s">

                        <div class="panel-body">

                            <?php echo form_open($this->uri->uri_string(), ['method' => 'GET', 'id' => 'add_details']); ?>

                            <?php echo form_hidden('filter', 'true'); ?>

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="select-placeholder">

                                        <select name="range" id="range" class="selectpicker" data-width="100%">

                                            <option value="this_month" <?php if (!$this->input->get('range') || $this->input->get('range') == 'this_month') {

                                                                            echo 'selected';
                                                                        } ?>><?php echo _l('staff_stats_this_month_total_logged_time'); ?>
                                            </option>

                                            <option value="last_month" <?php if ($this->input->get('range') == 'last_month') {

                                                                            echo 'selected';
                                                                        } ?>><?php echo _l('staff_stats_last_month_total_logged_time'); ?>
                                            </option>

                                            <option value="this_week" <?php if ($this->input->get('range') == 'this_week') {

                                                                            echo 'selected';
                                                                        } ?>><?php echo _l('staff_stats_this_week_total_logged_time'); ?>
                                            </option>

                                            <option value="last_week" <?php if ($this->input->get('range') == 'last_week') {

                                                                            echo 'selected';
                                                                        } ?>><?php echo _l('staff_stats_last_week_total_logged_time'); ?>
                                            </option>

                                            <option value="period" <?php if ($this->input->get('range') == 'period') {

                                                                        echo 'selected';
                                                                    } ?>><?php echo _l('period_datepicker'); ?>
                                            </option>

                                        </select>

                                    </div>

                                    <div class="row mtop15">

                                        <div class="col-md-12 period <?php if ($this->input->get('range') != 'period') {

                                                                            echo 'hide';
                                                                        } ?>">

                                            <?php echo render_date_input('period-from', '', $this->input->get('period-from')); ?>

                                        </div>

                                        <div class="col-md-12 period <?php if ($this->input->get('range') != 'period') {

                                                                            echo 'hide';
                                                                        } ?>">

                                            <?php echo render_date_input('period-to', '', $this->input->get('period-to')); ?>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-2 text-right">

                                    <button type="submit" class="btn btn-success apply-timesheets-filters">
                                        <?php echo _l('apply'); ?>
                                    </button>

                                </div>

                            </div>

                            <?php echo form_close(); ?>

                            <hr class="hr-panel-separator" />

                            <table class="table dt-table">

                                <thead>

                                    <th>
                                        <?php echo _l('task'); ?>
                                    </th>

                                    <th>
                                        <?php echo _l('timesheet_start_time'); ?>
                                    </th>

                                    <th>
                                        <?php echo _l('timesheet_end_time'); ?>
                                    </th>

                                    <th>
                                        <?php echo _l('task_relation'); ?>
                                    </th>

                                    <th>
                                        <?php echo _l('staff_hourly_rate'); ?> (
                                        <?php echo _l('als_staff'); ?>)
                                    </th>

                                    <th>
                                        <?php echo _l('time_h'); ?>
                                    </th>

                                    <th>
                                        <?php echo _l('time_decimal'); ?>
                                    </th>

                                    <th data-sortable="false"></th>

                                </thead>

                                <tbody>

                                    <?php

                                    $total_logged_time = [];

                                    foreach ($timesheets as $t) { ?>

                                        <tr>

                                            <td><a href="#" onclick="init_task_modal(<?php echo $t['task_id']; ?>); return false;">
                                                    <?php echo $t['name']; ?>
                                                </a>

                                            </td>

                                            <td data-order="<?php echo $t['start_time']; ?>">

                                                <?php echo _dt($t['start_time'], true); ?>
                                            </td>

                                            <td data-order="<?php echo $t['end_time']; ?>">

                                                <?php

                                                // Allow admins or timer user to stop forgotten timers by staff member

                                                if ($t['not_finished'] && (is_admin() || $t['staff_id'] === get_staff_user_id())) {

                                                ?>

                                                    <a href="#" <?php

                                                                // Do not show the note popover when there is no associated task

                                                                // The user will be able to add note and select task in the popup window that will open

                                                                if ($t['task_id'] != 0) { ?> data-toggle="popover" data-placement="bottom" data-html="true" data-trigger="manual" data-title="<?php echo _l('note'); ?>" data-content='<?php echo render_textarea('timesheet_note'); ?><button type="button"

                                          onclick="timer_action(this, <?php echo $t['task_id']; ?>, <?php echo $t['id']; ?>, 1);" class="btn btn-primary btn-sm"><?php echo _l('save'); ?></button>' onclick="return false;" <?php } else { ?> onclick="timer_action(this, <?php echo $t['task_id']; ?>, <?php echo $t['id']; ?>, 1); return false;" <?php } ?> class="text-danger">

                                                        <i class="fa-regular fa-clock"></i>

                                                        <?php echo _l('task_stop_timer'); ?>

                                                    </a>

                                                <?php

                                                } elseif ($t['not_finished']) {

                                                    echo '<b>' . _l('timer_not_stopped_yet') . '</b>';
                                                } else {

                                                    echo _dt($t['end_time'], true);
                                                }

                                                ?>

                                            </td>

                                            <td>

                                                <?php

                                                $rel_data = get_relation_data($t['rel_type'], $t['rel_id']);

                                                $rel_values = get_relation_values($rel_data, $t['rel_type']);

                                                echo '<a href="' . $rel_values['link'] . '">' . $rel_values['name'] . '</a>';

                                                ?>

                                            </td>

                                            <td>
                                                <?php echo app_format_money($t['hourly_rate'], $base_currency); ?>
                                            </td>

                                            <td>

                                                <?php echo '<b>' . seconds_to_time_format($t['end_time'] - $t['start_time']) . '</b>'; ?>

                                            </td>

                                            <td data-order="<?php echo sec2qty($t['total']); ?>">

                                                <?php

                                                $total_logged_time[] = ['total' => $t['total'], 'hourly_rate' => $t['hourly_rate']];

                                                echo '<b>' . sec2qty($t['total']) . '</b>';

                                                ?>

                                            </td>

                                            <td>

                                                <?php

                                                if (!$t['billed']) {

                                                    if (

                                                        has_permission('tasks', '', 'delete')

                                                        || (has_permission('projects', '', 'delete') && $t['rel_type'] == 'project')

                                                        || $t['staff_id'] == get_staff_user_id()

                                                    ) {

                                                        echo '<a href="' . admin_url('tasks/delete_timesheet/' . $t['id']) . '" class="pull-right text-danger mtop5"><i class="fa fa-remove"></i></a>';
                                                    }
                                                }

                                                ?>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                                <tfoot>

                                    <tr>

                                        <td></td>

                                        <td></td>

                                        <td></td>

                                        <td></td>

                                        <td align="right">
                                            <?php echo '<b>' . _l('total_by_hourly_rate') . ':</b> ' . app_format_money(

                                                collect($total_logged_time)->reduce(function ($carry, $item) {

                                                    return $carry + (sec2qty($item['total']) * (float)$item['hourly_rate']);
                                                }, 0),

                                                $base_currency

                                            ); ?>

                                        </td>

                                        <td align="right">

                                            <?php echo '<b>' . _l('total_logged_hours_by_staff') . ':</b> ' . seconds_to_time_format(

                                                collect($total_logged_time)->pluck('total')->sum()

                                            ); ?>

                                        </td>

                                        <td align="right">

                                            <?php echo '<b>' . _l('total_logged_hours_by_staff') . ':</b> ' . sec2qty(

                                                collect($total_logged_time)->pluck('total')->sum()

                                            ); ?>

                                        </td>

                                        <td></td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">

                        <?php echo _l('projects'); ?>

                    </h4>

                    <div class="panel_s">

                        <div class="panel-body">

                            <div class="_filters _hidden_inputs hidden staff_projects_filter">

                                <?php echo form_hidden('staff_id', $member->staffid); ?>

                            </div>

                            <?php render_datatable([

                                _l('project_name'),

                                _l('project_start_date'),

                                _l('project_deadline'),

                                _l('project_status'),

                            ], 'staff-projects'); ?>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

        <div class="btn-bottom-pusher"></div>

    </div>

    <?php init_tail(); ?>

    <script>
        $(function() {


            $('#staffid').on('change', function() {
                var staffid = $(this).val();

                $.ajax({
                        url: "<?php echo base_url('admin/staff/get_custom_staff_details/'); ?>",
                        type: 'POST',
                        data: {
                            staffid
                        },
                        dataType: 'json',
                        success:

                            function(data) {
                                console.log(data);
                                if (data.length == 0) {
                                    $('#empid').val('');
                                    $('#doj').val('');
                                    $('#shiftstart').val('');
                                    $('#shiftend').val('');
                                    $('#departmentid').selectpicker('val', '');
                                    $('#manageleave').selectpicker('val', '');
                                    $('#attendance_view_edit').selectpicker('val', '');


                                } else {
                                    $('#empid').val(data[0].empid);
                                    $('#doj').val(data[0].doj);
                                    $('#shiftstart').val(data[0].shiftstart);
                                    $('#shiftend').val(data[0].shiftend);
                                    $('#departmentid').selectpicker('val', data[0].departmentid);
                                    $('#manageleave').selectpicker('val', data[0].manageleave);
                                    $('#attendance_view_edit').selectpicker('val', data[0].attendance_view_edit);

                                }




                            }
                    }


                )
            })


        });
    </script>

    </body>


    </html>