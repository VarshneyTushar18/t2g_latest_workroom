
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();

// Use today as default dates — avoid recursive shift lookups on every page open.
$valid_cur_date = date('Y-m-d');

?>

<div id="wrapper">

  <div class="content">

    <div class="row">

      <div class="col-md-12">

        <div class="panel_s">

          <div class="panel-body">

            <div class="row">

              <div class="col-md-6">

                <h4><?php echo '<i class=" fa fa-clipboard"></i> '. _l('manage_requisition') ?></h4>

              </div> 

            </div>

            <div class="clearfix"></div>

            <div class="panel panel-default" style="margin-top:12px;border-color:#e2e8f0;">
              <div class="panel-heading" style="background:#f8fafc;cursor:pointer;" data-toggle="collapse" data-target="#leave_policy_box" aria-expanded="false">
                <strong><i class="fa fa-info-circle"></i> Leave Policy (company guidelines)</strong>
                <span class="pull-right text-muted" style="font-weight:normal;font-size:12px;">Click to expand</span>
              </div>
              <div id="leave_policy_box" class="panel-collapse collapse">
                <div class="panel-body" style="font-size:13px;line-height:1.55;color:#334155;">
                  <ol style="padding-left:18px;margin:0;">
                    <li><strong>Sandwich Leave:</strong> Leave falling between holidays/week-offs may be counted as leave when full-day leave is taken on both bordering working days.</li>
                    <li><strong>Initial months:</strong> First month — no leave earned. Second month — first + second month entitlement credited together.</li>
                    <li><strong>Monthly earning:</strong> Interns 1 · FTE 1.25 · After 2 years 1.75 · WFH 1.</li>
                    <li><strong>FY carry forward:</strong> FTE max 10 · WFH max 5 (excess lapses end of March).</li>
                    <li><strong>Resignation:</strong> Leave balance becomes zero; no further earning during notice period.</li>
                    <li><strong>Advance planning:</strong> Apply leave well in advance; do not wait for balance credit before applying planned leave. Inform RM and HR.</li>
                  </ol>
                </div>
              </div>
            </div>

            <div class="horizontal-scrollable-tabs preview-tabs-top">

              <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>

              <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>

              <div class="horizontal-tabs">

                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">

                  <li role="presentation" class="<?php if(!isset($tab) || $tab !== 'additional_timesheets'){ echo 'active';} ?>">

                   <a href="#registration_on_leave" aria-controls="registration_on_leave" role="tab" data-toggle="tab">

                     <span class="glyphicon glyphicon-align-justify"></span>&nbsp;<?php echo _l('registration_on_leave'); ?>

                   </a>

                 </li>

                 <?php if($data_timekeeping_form == 'timekeeping_manually' || is_admin() || is_HR() || is_super_hr() || is_manager()){ ?>

                  <li role="presentation" class="<?php if(isset($tab) && $tab === 'additional_timesheets'){ echo 'active';} ?>">

                   <a href="#additional_timesheets" aria-controls="additional_timesheets" role="tab" data-toggle="tab">

                    <span class="glyphicon glyphicon-pencil"></span>&nbsp;Regularization Approvals

                  </a>

                </li>

              <?php } ?>

            </ul>

          </div>

        </div>

        <input type="hidden" name="userid" value="<?php echo html_entity_decode($userid); ?>">



        <div class="tab-content active">

          <div role="tabpanel" class="tab-pane <?php if(!isset($tab) || $tab !== 'additional_timesheets'){ echo 'active';} ?>" id="registration_on_leave">

            <?php $this->load->view('timesheets/partials/leave_balance_cards'); ?>

            <div class="row">

              <div class="col-md-12 mtop15">

                <a href="#" onclick="new_requisition(); return false;" class="btn mright5 btn-info pull-left display-block" data-toggle="sidebar-right" data-target=".requisition_m"  >

                  <?php echo 'Apply for Leave'; ?>

                </a>

                <!--<a href="<?php //echo admin_url('timesheets/calendar_leave_application'); ?>" class="btn btn-default">

                  <i class="fa fa-calendar menu-icon"></i>&nbsp;

                  <?php //echo _l('ts_calendar_view'); ?>

                </a>-->

                <a href="<?php echo admin_url('staff/leave_balance'); ?>" class="btn btn-primary">

                  <?php echo "Leave Balance"; ?>

                </a>
				<a href="<?php echo admin_url('holiday/manageHolidayStaff'); ?>" class="btn btn-primary">

                  <?php echo "View Saturday Leaves"; ?>

                </a>

                


                <div class="clearfix"></div>

                <br>

                <br>          

              </div>

            </div>



            <div class="row">

              <!--<div class="col-md-3">

                <select name="chose" class="selectpicker" id="select_type" data-width="100%" id="chose" data-none-selected-text="<?php echo _l('filter_by'); ?>"> 

                 <option value="all"><?php echo _l('all') ?></option>                  

                 <option value="my_approve"><?php echo _l('my_approve') ?></option>                  

               </select>

             </div>-->

             <div class="col-md-3">

              <select name="status_filter[]" class="selectpicker" data-width="100%" id="status_filter" multiple data-none-selected-text="<?php echo _l('filter_by_status'); ?>"> 

               <option value="0"><?php echo _l('Pending') ?></option>                  

               <option value="1"><?php echo _l('approved') ?></option>   

               <option value="2"><?php echo _l('Reject') ?></option>      
				
				<option value="2"><?php echo _l('Absent') ?></option>      

             </select>

           </div>

        <div class="col-md-3">

            <select name="rel_type_filter[]" class="selectpicker" data-width="100%" id="rel_type_filter" multiple data-none-selected-text="<?php echo _l('filter_by_type'); ?>"> 

             <option value="1"><?php echo _l('Jan') ?></option>                  

             <option value="2"><?php echo _l('Feb') ?></option>                  

             <option value="3"><?php echo _l('March') ?></option>                  

             <option value="4"><?php echo _l('April') ?></option>                  

             <option value="05"><?php echo _l('May') ?></option>
			<option value="06"><?php echo _l('June') ?></option>
			<option value="07"><?php echo _l('July') ?></option>
			<option value="08"><?php echo _l('Aug') ?></option>
			<option value="9"><?php echo _l('Sept') ?></option>			 

           </select>

         </div>

         <!--<div class="col-md-3">

          <select name="department_filter[]" class="selectpicker" data-width="100%" id="department_filter" multiple data-live-search="true" data-none-selected-text="<?php echo _l('filter_by_department'); ?>"> 

           <?php foreach($departments as $dpm){ ?>               

             <option value="<?php echo html_entity_decode($dpm['departmentid']); ?>"><?php echo html_entity_decode($dpm['name']); ?></option>                  

           <?php } ?>

         </select>          

       </div>-->

     </div>





     <div class="clearfix"></div>

     <br>

     <div class="modal bulk_actions fade" id="table_registration_leave_bulk_actions" tabindex="-1" role="dialog">

      <div class="modal-dialog" role="document">

       <div class="modal-content">

        <div class="modal-header">

         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

         <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>

       </div>

       <div class="modal-body">

         <?php if(is_admin()){ ?>

           <div class="checkbox checkbox-danger">

            <input type="checkbox" name="mass_delete" id="mass_delete">

            <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>

          </div>

        <?php } ?>

      </div>

      <div class="modal-footer">

       <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>



       <?php if(is_admin()){ ?>

         <a href="#" class="btn btn-info" onclick="staff_delete_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>

       <?php } ?>

     </div>

   </div>

 </div>

</div>

<a href="#"  onclick="staff_bulk_actions(); return false;" data-toggle="modal" data-table=".table-table_registration_leave" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>



<?php

$table_data = array(

 _l('id'),

 '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="table_registration_leave"><label></label></div>',
_l('ID'),
_l('EmpID'),

_l('Name'),

 _l('start_time'),

 _l('end_time'),
 
 _l('Subject'),

 _l('Manager'),

 _l('Type'),

 _l('status'),

 _l('Leave Applied'),

 _l('options'),

);

render_datatable($table_data,'table_registration_leave',

 array('customizable-table'),

 array(

   'id'=>'table-table_registration_leave',

   'data-last-order-identifier'=>'table_registration_leave',

   'data-default-order'=>get_table_last_order('table_registration_leave'),

 )); ?>

</div>

<div role="tabpanel" class="tab-pane <?php if(isset($tab) && $tab === 'additional_timesheets'){ echo 'active';} ?>" id="additional_timesheets">



  <div class="row mtop15">

    <div class="col-md-12">

      <?php
      // Hidden: legacy "Additional Work Hours" manual entry — use Attendance Info → Regularization & Permission instead.
      if (false && (has_permission('additional_timesheets_management', '', 'view') || has_permission('additional_timesheets_management', '', 'view_own') || is_admin())) {
       ?>

       <a href="#" onclick="btn_additional_timesheets(); return false;" class="btn mright5 btn-default pull-left display-block" title="For HR manual entry only">

        HR manual entry

      </a>

    <?php } ?>

  </div>

  <div class="clearfix"></div>

  <br>

  <br>

</div>



<div class="row">

  <div class="col-md-3">

    <select name="chose_ats" class="selectpicker" id="chose_ats" data-width="100%" data-none-selected-text="<?php echo _l('filter_by'); ?>"> 

     <option value="all"><?php echo _l('all') ?></option>                  

     <option value="my_approve"><?php echo _l('my_approve') ?></option>                  

   </select>

 </div>

 <div class="col-md-3">

  <select name="status_filter_ats[]" class="selectpicker" id="status_filter_ats" multiple data-width="100%" data-none-selected-text="Filter by status"> 

   <option value="0">Pending</option>                  

   <option value="1">Approved</option>   
     
   <option value="2">Rejected</option>  

 </select>

</div>

<div class="col-md-3 leads-filter-column pull-left">

  <select name="department_ats[]" class="selectpicker" id="department_ats" data-width="100%" multiple data-live-search="true" data-none-selected-text="<?php echo _l('filter_by_department'); ?>"> 

   <?php foreach($departments as $dpm){ ?>               

     <option value="<?php echo html_entity_decode($dpm['departmentid']); ?>"><?php echo html_entity_decode($dpm['name']); ?></option>                  

   <?php } ?>

 </select>



</div>

</div>

<div class="clearfix"></div>

<br>

<?php $this->load->view('additional_timesheets'); ?>

</div>



<!-- The Modal -->

<!-- start -->

<style>
  #requisition_m .modal-dialog { width: 720px; max-width: 95%; }
  #requisition_m .leave-apply-label { color: #6b7280; font-weight: 500; margin-bottom: 6px; }
  #requisition_m .leave-apply-label .req { color: #e11d48; }
  #requisition_m .leave-summary-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 14px 16px;
    min-height: 110px;
    margin-top: 28px;
  }
  #requisition_m .leave-summary-box .sum-row { margin-bottom: 10px; color: #374151; }
  #requisition_m .leave-summary-box .sum-row:last-child { margin-bottom: 0; }
  #requisition_m .leave-summary-box .sum-val { font-weight: 600; color: #111827; }
  #requisition_m .leave-summary-box .sum-val.is-negative,
  #requisition_m .leave-summary-box .lop-notice { color: #dc2626; font-weight: 600; }
  #requisition_m .leave-summary-box .lop-notice { font-size: 12px; margin-top: 6px; }
  #requisition_m .leave-cc-add { color: #2563eb; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
  #requisition_m .attach-hint { color: #9ca3af; font-size: 12px; margin-top: 4px; }
  #requisition_m .modal-footer { text-align: center; }
  #requisition_m .modal-footer .btn { min-width: 110px; margin: 0 6px; }
  #requisition_m .date_session_row { display: flex; gap: 10px; align-items: flex-end; }
  #requisition_m .date_session_row .date-col { flex: 1.4; }
  #requisition_m .date_session_row .session-col { flex: 1; }
  #requisition_m .date_session_row .form-group { margin-bottom: 0; }
</style>

<div class="modal fade" id="requisition_m" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
         <span class="edit-title"><?php echo _l('edit_requisition_m'); ?></span>
         <span class="add-title">Leave Apply</span>
       </h4>
     </div>

<?php
	$last_row = $this->db->select('*')->order_by('id', 'desc')->where('staff_id', get_staff_user_id())->limit(1)->get('tbltimesheets_requisition_leave')->row();
	$carry_forward = ($last_row && isset($last_row->carry_forward)) ? $last_row->carry_forward : 0;
	// Don't show stale leave_balance from last application (often the earn rate 1.25).
	// get_remain_day_off() fills the real card balance when the modal opens.
	$leave_balance = 0;
	$manager_name = isset($results) ? $results : '';
	$cc_staff = !empty($pro) ? $pro : (isset($cc_staff) ? $cc_staff : []);
	$allowed_apply_leave_slugs = [
		'loss-of-pay',
		'earned-leave',
	];
	$hidden_apply_leave_slugs = [
		'comp-off',
		'work-from-home',
	];
?>
     <?php echo form_open_multipart(admin_url('timesheets/add_requisition_ajax'),array('id'=>'requisition-form'));?>

     <div class="modal-body">
      <div class="form" id="new_requisition">

        <input type="hidden" name="subject" id="subject" value="Leave Application">
        <input type="hidden" name="number_of_leaving_day" id="number_of_leaving_day" value="1">
        <input type="hidden" name="number_day_off" id="number_day_off" value="0">
        <input type="hidden" name="type_of_leave_text" id="type_of_leave_text" value="<?php echo html_escape($manager_name); ?>">
        <input type="hidden" name="carry_forward" id="carry_forward" value="<?php echo html_escape($carry_forward); ?>">
        <input type="hidden" name="leave_balance" id="leave_balance" value="<?php echo html_escape($leave_balance); ?>">
        <input type="hidden" name="handover_recipients" id="handover_recipients" value="<?php echo html_escape($manager_id); ?>">

        <?php if(is_admin() || is_HR() || is_super_hr() || has_permission('leave_management', '', 'view')){ ?>
        <div class="form-group">
          <label class="leave-apply-label">Staff <span class="req">*</span></label>
          <?php echo render_select('staff_id', $pro, array('staffid', array('firstname', 'lastname')), '', get_staff_user_id(),[],[],'','',false); ?>
        </div>
        <?php } else { ?>
        <input name="staff_id" type="hidden" id="staff_id" value="<?php echo get_staff_user_id(); ?>" />
        <?php } ?>

        <div class="form-group" id="type_of_leave">
          <label for="rel_type" class="leave-apply-label">Leave type <span class="req">*</span></label>
          <select name="type_of_leave" class="selectpicker" id="rel_type" data-width="100%" data-none-selected-text="Select type">
            <option value="">Select type</option>
            <?php foreach ($type_of_leave as $value) {
              if (in_array($value['slug'], $hidden_apply_leave_slugs, true)) { continue; }
              if (!in_array($value['slug'], $allowed_apply_leave_slugs, true)) { continue; }
            ?>
              <option value="<?php echo html_entity_decode($value['slug']); ?>"><?php echo html_entity_decode($value['type_name']); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="row date_input">
          <div class="col-md-8">
            <div class="form-group">
              <label class="leave-apply-label">From date <span class="req">*</span></label>
              <div class="date_session_row">
                <div class="date-col start_time">
                  <?php echo render_date_input('start_time','',_d($valid_cur_date)); ?>
                </div>
                <div class="session-col">
                  <select name="start_session" id="start_session" class="selectpicker" data-width="100%">
                    <option value="1" selected>Session 1</option>
                    <option value="2">Session 2</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="leave-apply-label">To date <span class="req">*</span></label>
              <div class="date_session_row">
                <div class="date-col end_time">
                  <?php echo render_date_input('end_time','',_d($valid_cur_date)); ?>
                </div>
                <div class="session-col">
                  <select name="end_session" id="end_session" class="selectpicker" data-width="100%">
                    <option value="1">Session 1</option>
                    <option value="2" selected>Session 2</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="leave-summary-box" id="apply_leave_balance_box">
              <div class="sum-row">Leave Balance: <span class="sum-val" id="leave_balance_value"><?php echo html_escape($leave_balance); ?></span></div>
              <div class="sum-row">Applying For: <span class="sum-val" id="applying_for_value">1 Day</span></div>
              <div class="sum-row">Balance after apply: <span class="sum-val" id="remaining_balance_value">-</span></div>
              <div id="loss_of_pay_notice" class="lop-notice" style="display:none;"></div>
              <div id="sandwich_leave_notice" class="text-warning" style="font-size:12px;margin-top:6px;display:none;"></div>
              <div id="number_days_off_2" class="hide"></div>
            </div>
          </div>
        </div>

        <div class="row mtop10 datetime_input hide">
          <div class="col-md-6 start_time">
            <?php echo render_datetime_input('start_time_s','From_Date',_d(date('Y-m-d H:i:s'))) ?>
          </div>
          <div class="col-md-6 end_time">
            <?php echo render_datetime_input('end_time_s','To_Date',_d(date('Y-m-d H:i:s'))) ?>
          </div>
        </div>

        <div class="form-group">
          <label class="leave-apply-label">Applying to</label>
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-user-circle"></i></span>
            <input type="text" class="form-control" value="<?php echo html_escape($manager_name); ?>" readonly>
          </div>
        </div>

        <div class="form-group" id="leave_">
          <label class="leave-apply-label">CC to</label>
          <div id="cc_picker_wrap" class="hide">
            <select name="followers_id" id="followers_id" data-live-search="true" class="selectpicker" data-width="100%" data-none-selected-text="Select staff">
              <option value=""></option>
              <?php foreach($cc_staff as $s) { ?>
                <option value="<?php echo html_entity_decode($s['staffid']); ?>"><?php echo html_entity_decode($s['firstname'].' '.$s['lastname']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div id="cc_add_link" class="leave-cc-add">
            <i class="fa fa-plus-circle"></i> <span>Add</span>
          </div>
        </div>

        <div class="form-group">
          <label class="leave-apply-label">Contact details</label>
          <input type="text" name="contact_details" id="contact_details" class="form-control" placeholder="Phone / email">
        </div>

        <div class="form-group">
          <label class="leave-apply-label">Reason</label>
          <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Enter a reason"></textarea>
        </div>

        <div class="form-group">
          <label class="leave-apply-label"><i class="fa fa-paperclip"></i> Attach File</label>
          <input type="file" id="file" name="file" class="form-control">
          <div class="attach-hint">Supported File Types: pdf , xls , xlsx , doc , docx , txt , ppt , pptx , gif , jpg , jpeg , png</div>
        </div>

      </div>
     </div>

<div class="modal-footer">
  <button type="submit" id="submit" class="btn btn-info btn-submit">Submit</button>
  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
</div>

<?php echo form_close(); ?>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- end -->

</div>

</div>

</div>

</div>

</div>

</div>

</div>



<div class="modal fade" id="add_new_type_of_leave" tabindex="1" role="dialog">

  <div class="modal-dialog">

    <?php echo form_open(admin_url('timesheets/add_type_of_leave'),array('id'=>'add_type_of_leave-form')); ?>

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4>

          <?php echo _l('ts_input_new_type_of_leave'); ?>

        </h4>

      </div>

      <div class="modal-body">

       <div class="col-md-6">

         <?php echo render_input('type_name', 'type_of_leave') ?>

       </div>

       <div class="col-md-6">

        <?php echo render_input('symbol', _l('ts_character').' <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="'._l('ts_it_will_be_displayed_on_the_timesheet').'"></i>') ?>         

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



<?php if (false) { /* Hidden: legacy Additional Work Hours modal — replaced by Regularization & Permission */ ?>
<div class="modal fade" id="additional_timesheets_modalss" tabindex="-1" role="dialog">

  <div class="modal-dialog">

    <?php echo form_open(admin_url('timesheets/send_additional_timesheets'),array('id'=>'edit_timesheets-form')); ?>

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4>

          <?php echo _l('additional_timesheets'); ?>

        </h4>

      </div>

      <div class="modal-body">

        <div class="col-md-12">

          <?php echo render_date_input('additional_day','additional_day'); ?>

          <?php echo render_input('time_in','time_in','', 'time'); ?>

          <?php echo render_input('time_out','time_out','', 'time'); ?>

          <?php echo render_input('timekeeping_value','timekeeping_value',''); ?>

          <?php echo render_textarea('reason','reason_'); ?>

        </div>

        <div class="clearfix"></div>

      </div>

      <div class="modal-footer">

        <button type="" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

        <button class="btn btn-info btn-additional-timesheets"><?php echo _l('submit'); ?></button>

      </div>

      <?php echo form_close(); ?>

    </div><!-- /.modal-content -->

  </div><!-- /.modal-dialog -->

</div>
<?php } ?>

<input type="hidden" name="current_date" value="<?php echo _d(date('Y-m-d')); ?>">

<?php 

$date_format = '';

$data_date_format = get_option('dateformat');

if($data_date_format)

{

  $date_format = $data_date_format;

}

?>

<input type="hidden" name="date_format" value="<?php echo html_entity_decode($date_format); ?>">

<?php init_tail(); ?>

<?php require 'modules/timesheets/assets/js/requisition_manage_js.php'; ?>

</body>

</html>
<script>

$('#end_time').change(function() {
	var staff = $('#staff_id').val();
	var start_time = $('#start_time').val();
	var end_time = $('#end_time').val();
	var type  = $('#rel_type').find(":selected").val();
	//alert(type);
			$.ajax({
                type:'GET',
                url:admin_url+'timesheets/data_check',
                 data: {staff : staff,start_time:start_time,end_time:end_time,type:type},
                success:function(response){
					console.log(response);
					if(response != '0'){
						//alert();
						$("#submit").css('display','none');
					}else{
						$("#submit").css('display','inline-block');
					}
                },
                failure:function(){
              console.log("nooo"); 
                }             
            });

});




var type  = $('#rel_type').find(":selected").val();
if(type=='planned_leaves'){
jQuery('#start_time').datetimepicker({
		 timepicker:false,
		 formatDate:'Y/m/d',
		 minDate:'-1970/01/02'//yesterday is minimum date(for today use 0 or -1970/01/01)
		});

}
$('#rel_type').change(function() {
	   //Use $option (with the "$") to see that the variable is a jQuery object
    var $option = $(this).find('option:selected');
    //Added with the EDIT
	var staff = $('#staff_id').val();
    var value = $option.val();//to get content of "value" attrib
	//var date = $("#reservation").val();
			$.ajax({
                type:'GET',
                url:admin_url+'timesheets/leave_check',
                 data: {value : value,staff : staff},
                success:function(response){
					//console.log(response);
					if(response == 'hide'){
						//alert();
						$("#submit").css('display','none');
					}else if(response == 'show' || response == 0){
		
						$("#submit").css('display','inline-block');
					}else{
						$("#submit").css('display','inline-block');
					}
					
                },
                failure:function(){
              console.log("nooo"); 
                }             
            });
 
	
    switch (value) { 
	case 'planned_leaves':
		jQuery('#start_time').datetimepicker({
		formatDate:'Y/m/d',
		 minDate:'-1970/01/02',
		 timepicker:false

		});
		jQuery('#end_time').datetimepicker({
		  datepicker:true,
		  timepicker:false,
		   disabledWeekDays:[-1],
		  minDate:'2013/12/03'
		});
		break;
	case 'unplanned_leaves': 
	jQuery('.start_time').show();	
	jQuery('#start_time').datetimepicker({
		  datepicker:true,
		  timepicker:false,
		  minDate:'2013/12/03'
		});
	jQuery('#end_time').datetimepicker({
		  datepicker:true,
		  timepicker:false,
		   disabledWeekDays:[-1],
		  minDate:'2013/12/03'
		});
		break;	
	case 'saturday-leaves': 
	 jQuery('.start_time').hide();	
		jQuery('#end_time').datetimepicker({
		  datepicker:true,
		  timepicker:false,
		  disabledWeekDays:[0,1,2,3,4,5],
		   useCurrent: false, // disable focusable
		  minDate:'2013/12/03'
		});
		break;
	case 'half-days': 
	  jQuery('.start_time').hide();
		jQuery('#end_time').datetimepicker({
		  datepicker:true,
		  disabledWeekDays:[0,1,2,3,4,5],
		  minDate:'2013/12/03'
		});
		break;
	case 'unpaid-half-days': 
	 jQuery('.start_time').hide();	
		jQuery('#end_time').datetimepicker({
		  timepicker:false,
		  disabledWeekDays:[-1]
		});
		break;
	case 'short-leaves':
		jQuery('#start_time').datetimepicker({
		formatDate:'Y/m/d',
		 minDate:'-1970/01/02',
		 timepicker:false

		});
		jQuery('#end_time').datetimepicker({
		  datepicker:true,
		  timepicker:false,
		   disabledWeekDays:[-1],
		  minDate:'2013/12/03'
		});
		break;
		jQuery('.start_time').show();	
		case 'present':
		jQuery('#start_time').datetimepicker({
		datepicker:true,
		  timepicker:false,
		  minDate:'2013/12/03'

		});
		jQuery('#end_time').datetimepicker({
		 datepicker:true,
		  timepicker:false,
		   disabledWeekDays:[-1],
		  minDate:'2013/12/03'
		});
		break;
		case 'holiday-leaves':
		jQuery('#start_time').datetimepicker({
		datepicker:true,
		  timepicker:false,
		  minDate:'2013/12/03'

		});
		jQuery('#end_time').datetimepicker({
		 datepicker:true,
		  timepicker:false,
		   disabledWeekDays:[-1],
		  minDate:'2013/12/03'
		});
		break;
	default:
		console.log('not select any option');
}
});


</script>