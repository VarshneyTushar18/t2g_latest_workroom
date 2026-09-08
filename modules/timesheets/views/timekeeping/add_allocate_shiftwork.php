<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$id = '';
$department = [];
$position = [];
$staff = [];
$from_date = _d(date('Y-m') . '-01');
$to_date = _d(date('Y-m-t'));
$type_shiftwork = 'repeat_periodically';
$shift_reason = '';
$is_edit = isset($word_shift);

if ($is_edit) {
	$id = $word_shift->id;
	$department = explode(',', $word_shift->department);
	$position = explode(',', $word_shift->position);
	$staff = $word_shift->staff !== '' ? explode(',', $word_shift->staff) : [];
	$from_date = _d($word_shift->from_date);
	$to_date = _d($word_shift->to_date);
	$type_shiftwork = $word_shift->type_shiftwork;
	$shift_reason = isset($word_shift->shift_name) ? $word_shift->shift_name : '';
}

$staff_scope = 'search';
if ($is_edit) {
	if (empty($staff) && empty($department) && empty($position)) {
		$staff_scope = 'all';
	} elseif (count($staff) > 1) {
		$staff_scope = 'selected';
	}
}
?>
<style>
  .simple-shift-form { max-width: 720px; }
  .simple-shift-form .form-row-shift {
    display: flex;
    align-items: flex-start;
    margin-bottom: 16px;
  }
  .simple-shift-form .form-row-shift > label {
    width: 140px;
    padding-top: 8px;
    margin: 0 16px 0 0;
    text-align: right;
    color: #4b5563;
    font-weight: 500;
  }
  .simple-shift-form .form-row-shift .field {
    flex: 1;
  }
  .simple-shift-form .scope-radios label {
    font-weight: normal;
    margin-right: 18px;
    color: #374151;
  }
  .simple-shift-form .btn-cancel-shift {
    background: #fff;
    border: 1px solid #3b82f6;
    color: #2563eb;
  }
  #advanced_shift_panel { margin-top: 24px; }
  .advanced-toggle { margin-top: 8px; display: inline-block; }
</style>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <div id="shift_setting">
            <?php echo form_open(admin_url('timesheets/shifts'), array('id' => 'shift_f-form')); ?>
            <h4 class="modal-title"><?php echo html_entity_decode($title); ?></h4>
            <hr>

            <?php if (!$is_edit) { ?>
            <div class="simple-shift-form" id="simple_shift_form">
              <div class="form-row-shift">
                <label></label>
                <div class="field scope-radios">
                  <label><input type="radio" name="staff_scope" value="search" <?php echo $staff_scope === 'search' ? 'checked' : ''; ?>> Search employees</label>
                  <label><input type="radio" name="staff_scope" value="selected" <?php echo $staff_scope === 'selected' ? 'checked' : ''; ?>> Selected employees</label>
                  <label><input type="radio" name="staff_scope" value="all" <?php echo $staff_scope === 'all' ? 'checked' : ''; ?>> All employees</label>
                </div>
              </div>

              <div class="form-row-shift" id="employee_row">
                <label for="staff_simple">Employee</label>
                <div class="field">
                  <select name="staff[]" id="staff_simple" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="select an employee..." multiple>
                    <?php foreach ($staffs as $dpm) { ?>
                      <option value="<?php echo html_entity_decode($dpm['staffid']); ?>">
                        <?php echo html_entity_decode($dpm['firstname'] . ' ' . $dpm['lastname']); ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-row-shift">
                <label for="from_date">From Date</label>
                <div class="field">
                  <?php echo render_date_input('from_date', '', $from_date); ?>
                </div>
              </div>

              <div class="form-row-shift">
                <label for="to_date">To Date</label>
                <div class="field">
                  <?php echo render_date_input('to_date', '', $to_date); ?>
                </div>
              </div>

              <div class="form-row-shift">
                <label for="simple_shift_id">Shift</label>
                <div class="field">
                  <select name="simple_shift_id" id="simple_shift_id" class="selectpicker" data-width="100%" data-none-selected-text="Select shift">
                    <option value=""></option>
                    <?php foreach ($shift_type as $st) { ?>
                      <option value="<?php echo html_entity_decode($st['id']); ?>"><?php echo html_entity_decode($st['label']); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-row-shift">
                <label for="reason">Reason</label>
                <div class="field">
                  <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Optional reason"><?php echo html_escape($shift_reason); ?></textarea>
                </div>
              </div>

              <input type="hidden" name="type_shiftwork" value="repeat_periodically">
              <input type="hidden" name="shifts_detail" id="shifts_detail" value="">

              <div class="form-row-shift">
                <label></label>
                <div class="field">
                  <button type="submit" class="btn btn-info save_simple_shift"><?php echo _l('save'); ?></button>
                  <a href="<?php echo admin_url('timesheets/shift_management'); ?>" class="btn btn-cancel-shift"><?php echo _l('cancel'); ?></a>
                  <a href="#" class="advanced-toggle text-muted" id="show_advanced_shift">Advanced day-by-day grid</a>
                </div>
              </div>
            </div>
            <?php } ?>

            <div id="advanced_shift_panel" class="<?php echo $is_edit ? '' : 'hide'; ?>">
              <?php if (!$is_edit) { ?>
                <hr>
                <h5>Advanced allocation</h5>
                <p class="text-muted">Use this only if different days need different shifts.</p>
              <?php } ?>

              <div class="row mbot15">
                <div class="col-md-6">
                  <label for="department"><?php echo _l('department'); ?></label>
                  <select name="department[]" id="department" onchange="dpm_change(this); return false;" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('all'); ?>" data-hide-disabled="true" data-live-search="true" multiple="true">
                    <?php foreach ($departments as $dpm) {
                      $selected = in_array($dpm['departmentid'], $department) ? 'selected' : '';
                    ?>
                      <option <?php echo html_entity_decode($selected); ?> value="<?php echo html_entity_decode($dpm['departmentid']); ?>"><?php echo html_entity_decode($dpm['name']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="role"><?php echo _l('role'); ?></label>
                  <select name="role[]" id="role" onchange="role_change(this); return false;" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('all'); ?>" data-hide-disabled="true" data-live-search="true" multiple="true">
                    <?php foreach ($roles as $dpm) {
                      $selected = in_array($dpm['roleid'], $position) ? 'selected' : '';
                    ?>
                      <option <?php echo html_entity_decode($selected); ?> value="<?php echo html_entity_decode($dpm['roleid']); ?>"><?php echo html_entity_decode($dpm['name']); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <label for="staff"><?php echo _l('staff'); ?></label>
                  <select name="staff[]" id="staff" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('all'); ?>" data-hide-disabled="true" data-live-search="true" multiple="true" <?php echo !$is_edit ? 'disabled' : ''; ?>>
                    <?php foreach ($staffs as $dpm) {
                      $selected = in_array($dpm['staffid'], $staff) ? 'selected' : '';
                    ?>
                      <option <?php echo html_entity_decode($selected); ?> value="<?php echo html_entity_decode($dpm['staffid']); ?>"><?php echo html_entity_decode($dpm['firstname'] . ' ' . $dpm['lastname']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="clearfix"></div>
                <br>
                <?php if ($is_edit) { ?>
                <div class="col-md-6">
                  <?php echo render_date_input('from_date', 'from_date', $from_date); ?>
                </div>
                <div class="col-md-6">
                  <?php echo render_date_input('to_date', 'to_date', $to_date); ?>
                </div>
                <?php } ?>
              </div>

              <div class="col-md-12">
                <input type="radio" id="repeat_periodically" class="type_shift" <?php if ($type_shiftwork == 'repeat_periodically' || !$is_edit) { echo 'checked'; } ?> name="type_shiftwork" value="repeat_periodically" <?php echo !$is_edit ? 'disabled' : ''; ?>>
                <label for="repeat_periodically"><?php echo _l('repeat_weekly'); ?></label><br>
                <input type="radio" id="by_absolute_time" class="type_shift" <?php if ($type_shiftwork == 'by_absolute_time') { echo 'checked'; } ?> name="type_shiftwork" value="by_absolute_time" <?php echo !$is_edit ? 'disabled' : ''; ?>>
                <label for="by_absolute_time"><?php echo _l('specific_time_period'); ?></label><br>
              </div>
              <div class="col-md-12">
                <h4><?php echo _l('shifts_detail'); ?></h4>
                <hr />
                <small>Shift + Mouse scroll to scroll horizontally</small>
              </div>
              <div class="col-md-12" id="example"></div>
              <?php if ($is_edit) { echo form_hidden('shifts_detail'); } ?>

              <?php if ($is_edit) { ?>
              <hr>
              <div class="row">
                <div class="col-md-12">
                  <button class="btn btn-info pull-right save_detail_shift"><?php echo _l('submit'); ?></button>
                  <a href="<?php echo admin_url('timesheets/shift_management'); ?>" class="btn btn-default pull-right mright10"><?php echo _l('cancel'); ?></a>
                </div>
              </div>
              <?php } else { ?>
              <div class="row mtop15">
                <div class="col-md-12">
                  <button type="button" class="btn btn-default" id="hide_advanced_shift">Back to simple form</button>
                </div>
              </div>
              <?php } ?>
            </div>

            <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php require 'modules/timesheets/assets/js/add_edit_allocation_shiftwork_js.php'; ?>
</body>
</html>
