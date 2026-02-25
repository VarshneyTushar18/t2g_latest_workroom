<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();
$valid_cur_date = $this->timesheets_model->get_next_shift_date(get_staff_user_id(), date('Y-m-d'));
?>
<div id="wrapper">
	<div class="content">
<?php echo form_open(); ?>
    <?php echo form_hidden('calendar_filters', true); ?>
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="col-md-2">
<?php
$choses = [
	['id' => 'all', 'name' => _l('all')],
	['id' => 'my_approve', 'name' => _l('my_approve')],
];
?>
              <?php echo render_select('chose', $choses, array('id', 'name'), '', $chose, ['data-none-selected-text' => _l('filter_by')], array(), '', '', false); ?>
           </div>
           <div class="col-md-2" id='status_filter'>
<?php
$status_filters = [
	['id' => 0, 'name' => _l('Create')],
	['id' => 1, 'name' => _l('approved')],
	['id' => 2, 'name' => _l('Reject')],
];
?>
            <?php echo render_select('status_filter[]', $status_filters, array('id', 'name'), '', $status_filter, ['data-none-selected-text' => _l('filter_by_status'), 'multiple' => true, 'data-width' => '100%', 'class' => 'selectpicker'], array(), '', '', false); ?>
         </div>
         <div class="col-md-3" id='rel_type_filter'>
<?php
$rel_type_filters = [
	['id' => 1, 'name' => _l('Leave')],
	['id' => 2, 'name' => _l('late')],
	['id' => 6, 'name' => _l('early')],
	['id' => 3, 'name' => _l('Go_out')],
	['id' => 4, 'name' => _l('Go_on_bussiness')],
];
?>
          <?php echo render_select('rel_type_filter[]', $rel_type_filters, array('id', 'name'), '', $rel_type_filter, ['data-none-selected-text' => _l('filter_by_type'), 'multiple' => true, 'data-width' => '100%', 'class' => 'selectpicker'], array(), '', '', false); ?>
       </div>
       <div class="col-md-3" id='department_filter'>
        <?php echo render_select('department_filter[]', $departments, array('departmentid', 'name'), '', $department_filter, ['data-none-selected-text' => _l('filter_by_department'), 'multiple' => true, 'data-width' => '100%', 'class' => 'selectpicker', 'data-live-search' => "true"], array(), '', '', false); ?>
     </div>
     <div class="col-md-2 text-right">
        <button class="btn btn-success" type="submit"><?php echo _l('apply'); ?></button>
        <a class="btn btn-default" href="<?php echo admin_url('timesheets/requisition_manage'); ?>"><?php echo _l('ts_back'); ?></a>
    </div>
   </div>
 </div>
</div>
</div>
<?php echo form_close(); ?>
<div class="row">
 <div class="col-md-12">
  <div class="panel_s">
   <div class="panel-body">
    <div class="dt-loader hide"></div>
    <div id="calendars"></div>
  </div>
</div>
</div>
</div>
</div>
</div>
<input type="hidden" name="userid" value="<?php echo get_staff_user_id() ?>">
<!-- The Modal -->
<!-- start -->
<div class="modal fade" id="requisition_m" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
   <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

     </div>
     <?php //echo form_open_multipart(admin_url('timesheets/add_requisition_ajax'), array('id' => 'requisition-form')); ?>
    <div class="modal-body">
      <div id="additional_contract_type"></div>
      <div class="form">
        <input type="hidden" name="redirect_calendar" value="1">
        <div class="row">
          <div class="col-md-12">
            <div id="additional_contract_type"></div>
            <div class="form" id="new_requisition">
              <div class="row">
                <div class="col-md-12">
                 <div id="type_check_in"></div>
				 <div id="type_check_date_in"></div>
				 <div id="type_check_out"></div>
				 <div id="type_check_date_out"></div>
				 
                </div>
              </div>
 
      <div class="form-group" id="date_form" style="display:none;">
      <div class="row mtop10 date_input ">
        <div class="col-md-6 start_time">
          <?php echo render_date_input('start_time', 'From_Date', _d($valid_cur_date)) ?>
        </div>
        <div class="col-md-6 end_time">
          <?php echo render_date_input('end_time', 'To_Date', _d($valid_cur_date)) ?>
        </div>
      </div>

      <div class="row mtop10 datetime_input hide">
        <div class="col-md-6 start_time">
          <?php echo render_datetime_input('start_time_s', 'From_Date', _d(date('Y-m-d H:i:s'))) ?>
        </div>
        <div class="col-md-6 end_time">
          <?php echo render_datetime_input('end_time_s', 'To_Date', _d(date('Y-m-d H:i:s'))) ?>
        </div>
      </div>
	</div>


     

    
    </div>
  </div>
</div>

</div>
</div>

</div>
</div>
</div>
<!-- end -->

<div class="modal fade" id="add_new_type_of_leave" tabindex="1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open(admin_url('timesheets/add_type_of_leave'), array('id' => 'add_type_of_leave-form')); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4>
          <?php echo _l('ts_input_new_type_of_leave'); ?>
        </h4>
      </div>
      <div class="modal-body">
        <input type="hidden" name="is_calendar_page" value="1">
        <div class="col-md-6">
         <?php echo render_input('type_name', 'type_of_leave') ?>
       </div>
       <div class="col-md-6">
        <?php echo render_input('symbol', _l('ts_character') . ' <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="' . _l('ts_it_will_be_displayed_on_the_timesheet') . '"></i>') ?>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="modal-footer">
      <button type="" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
      <button class="btn btn-info add_type_of_leave"><?php echo _l('ts_add'); ?></button>
    </div>
    <?php echo form_close(); ?>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>


<?php
$date_format = '';
$data_date_format = get_option('dateformat');
if ($data_date_format) {
	$date_format = $data_date_format;
}
?>
<input type="hidden" name="date_format" value="<?php echo html_entity_decode($date_format); ?>">
<?php init_tail();?>
<?php
require 'modules/timesheets/assets/js/leave/calendar_leave_application_js.php';
?>
</body>
</html>
