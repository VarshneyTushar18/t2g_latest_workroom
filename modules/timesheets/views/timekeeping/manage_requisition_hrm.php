
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();

$valid_cur_date = $this->timesheets_model->get_next_shift_date(get_staff_user_id(), date('Y-m-d'));

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

            <div class="horizontal-scrollable-tabs preview-tabs-top">

              <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>

              <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>

              <div class="horizontal-tabs">

                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">

                  <li role="presentation" class="<?php if(!isset($tab)){ echo 'active';} ?>">

                   <a href="#registration_on_leave" aria-controls="registration_on_leave" role="tab" data-toggle="tab">

                     <span class="glyphicon glyphicon-align-justify"></span>&nbsp;<?php echo _l('registration_on_leave'); ?>

                   </a>

                 </li>

                 <!--<?php if($data_timekeeping_form == 'timekeeping_manually'){ ?>

                  <li role="presentation" class="<?php if(isset($tab)){ echo 'active';} ?>">

                   <a href="#additional_timesheets" aria-controls="additional_timesheets" role="tab" data-toggle="tab">

                    <span class="glyphicon glyphicon-pencil"></span>&nbsp;<?php echo _l('additional_timesheets'); ?>

                  </a>

                </li>

              <?php } ?>-->

            </ul>

          </div>

        </div>

        <input type="hidden" name="userid" value="<?php echo html_entity_decode($userid); ?>">



        <div class="tab-content active">

          <div role="tabpanel" class="tab-pane <?php if(!isset($tab)){ echo 'active';} ?>" id="registration_on_leave">

            <div class="row">

              <div class="col-md-12 mtop15">

                <a href="#" onclick="new_requisition(); return false;" class="btn mright5 btn-info pull-left display-block" data-toggle="sidebar-right" data-target=".requisition_m"  >

                  <?php echo _l('Manage Attendance'); ?>

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

<div role="tabpanel" class="tab-pane <?php if(isset($tab)){ echo 'active';} ?>" id="additional_timesheets">



  <div class="row mtop15">

    <div class="col-md-12">

      <?php 

      if(has_permission('additional_timesheets_management', '', 'view') || has_permission('additional_timesheets_management', '', 'view_own') || is_admin()) {

       ?>

       <a href="#" onclick="btn_additional_timesheets(); return false;" class="btn mright5 btn-info pull-left display-block" >

        <?php echo _l('add'); ?>

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

  <select name="status_filter_ats[]" class="selectpicker" id="status_filter_ats" multiple data-width="100%" data-none-selected-text="<?php echo _l('filter_by_status'); ?>"> 

   <option value="0"><?php echo _l('Pending') ?></option>                  

   <option value="1"><?php echo _l('Approve') ?></option>   
     
   <option value="2"><?php echo _l('Reject') ?></option>  
	<option value="3"><?php echo _l('Absent') ?></option>  
 
 </select>

</div>

<div class="col-md-3">

  <select name="rel_type_filter_ats[]" class="selectpicker" id="rel_type_filter_ats" data-width="100%" multiple data-none-selected-text="<?php echo _l('filter_by_type'); ?>"> 

   <option value="W"><?php echo _l('W') ?></option>                  

   <option value="OT"><?php echo _l('OT') ?></option>                  



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

<div class="modal fade" id="requisition_m" tabindex="-1" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title">

         <span class="edit-title"><?php echo _l('edit_requisition_m'); ?></span>

         <span class="add-title"><?php echo _l('Manage Attendance Leave'); ?></span>

       </h4>

     </div>
    
<?php   $last_row=$this->db->select('*')->order_by('id',"desc")->where('staff_id',get_staff_user_id())->limit(1)->get('tbltimesheets_requisition_leave')->row();
	$carry_forward = $last_row->carry_forward;
	$leave_balance = $last_row->leave_balance;
	
	//echo $carry_forward;
	//echo $leave_balance;
	
	
	
	
	?>
     <?php echo form_open_multipart(admin_url('timesheets/add_requisition_ajax'),array('id'=>'requisition-form'));?>             

     <div class="modal-body">

      <div id="additional_contract_type"></div>                    

      <div class="form">


        <div class="row">

          <div class="col-md-12">

            <div id="additional_contract_type"></div>

            <div class="form" id="new_requisition">

              <div class="row">

                <div class="col-md-12">

                  <label for="subject" class="control-label"><?php echo _l('Subject'); ?></label>
					<input type="text" id="subject" name="subject" class="form-control" maxlength="80" value="">
                  <?php // echo render_input('subject') ?>
					
                </div>

              </div>

              <?php 

              if(is_admin() || has_permission('leave_management', '', 'view')){ ?>

                <div class="row">

                  <div class="col-md-12">

                    <?php echo render_select('staff_id', $pro, array('staffid', array('firstname', 'lastname')), 'staff', get_staff_user_id(),[],[],'','',false); ?>

                  </div>

                </div>

              <?php }else { 
			  ?>
			 <input name="staff_id" type="hidden" id="staff_id" value="<?php  echo get_staff_user_id()?>" />
			 <input name="handover_recipients" type="hidden" value="<?php  echo $manager_id;?>" />
			
			   
			 <?php  } ?>
			<?php  

					
					/*foreach($res as $result){
						$date = $result['datecreated'];
						$splitTimeStamp = explode(" ",$date);
						$sdate = $splitTimeStamp[0];
						$mdata= strtotime($sdate);
						$today =  date('mm', $mdata);
						
						$currentDate = date('mm');

					if($today == $currentDate){
						echo 'date is correect';
					}else{
						echo 'date not crrect';
					}*/
					
					?>
              <div class="row mtop10">

               <div class="col-md-12" id="type_of_leave">

                 <div class="form-group">

                   <label for="type_of_leave" class="control-label"><?php echo _l('type_of_leave'); ?></label>           

                   <div class="<?php if(is_admin()){ echo 'input-group'; } ?>">

                    <select name="type_of_leave" class="selectpicker" id="rel_type" data-width="100%" data-none-selected-text="<?php echo _l('none_type'); ?>">

                    <option>Select Leave</option>
                     <?php 
					
                     foreach ($type_of_leave as $value) { ?>
						
                      <option value="<?php echo html_entity_decode($value['slug']); ?>"><?php echo html_entity_decode($value['type_name']); ?></option>    

                    <?php } ?>              

                  </select>

                  <?php 

                  if(is_admin()){ ?>

                    <span class="input-group-addon btn add_new_type_of_leave">

                     <i class="fa fa-plus"></i>

                   </span> 

                 <?php } ?>

               </div>

             </div>

           </div>

        </div>

      <br>

      <div class="row mtop10 date_input">

        <div class="col-md-6 start_time">

          <?php echo render_date_input('start_time','From_Date',_d($valid_cur_date)) ?>

        </div>

        <div class="col-md-6 end_time">

          <?php echo render_date_input('end_time','To_Date',_d($valid_cur_date))  ?>

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
	   <div class="row mtop10">
	    <div class="col-md-12 pb-4" id="leave_">
		<input type='hidden' name='followers_id' id="followers_id" value='<?php echo $result; ?>'/>
		<input type='hidden' name='type_of_leave_text' id="type_of_leave_text" value='<?php echo $results; ?>'/>
		<input type='hidden' name='carry_forward' id="carry_forward" value='<?php echo $carry_forward; ?>'/>
		<input type='hidden' name='leave_balance' id="leave_balance" value='<?php echo $leave_balance; ?>'/>
	
           <div class="row mtop10">

        <div class="col-md-12">

          <?php echo render_textarea('reason', 'reason_') ?>

        </div>

      </div>

        </div>
	</div>
    

      <div class="mtop10">

        <label for="file" class="control-label"><?php echo _l('requisition_files'); ?></label>

        <input type="file" id="file" name="file" class="form-control" value="" >

      </div>

    </div>

  </div>

</div>



</div>

</div>

<div class="modal-footer">

  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

  <button type="submit" id="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>

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