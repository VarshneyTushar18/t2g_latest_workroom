	<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$check = $this->input->get('check'); ?>
<?php if(isset($id)) { ?>
  <div id="wrapper">
    <div class="content">
      <div class="row">
        <div class="content p-2 row" style="margin-top: -50px;">
          <div class="panel_s">
           <div class="panel-body w-100">
            <div class="wrap">

              <?php 
              $status['name'] = '';
              $status['color'] = '';

              ?>
			   <?php 
			 //  echo '<pre>';print_r($request_leave);die;
			   $this->db->where('id', $request_leave->id);
			   $manager_comment_status =  $this->db->get(db_prefix() . 'timesheets_requisition_leave')->result_array(); 
			   $manager_comm_status = $manager_comment_status[0]['status'];
				
			  $this->db->where('leave_id', $request_leave->id);
			  $approve_comment =  $this->db->get(db_prefix() . 'leave_comment')->result_array(); 
			  //print_r($approve_comment);die;
					  ?>
              <div class="ribbonc" ><span><?php echo html_entity_decode($status['name']); ?></span></div>
            </div>
            <div class="row">
			 <input type="hidden" name="userid" value="<?php echo html_entity_decode($userid); ?>">
			<?php  if(isset($_SESSION['message'])): ?>
			<div class="alert alert-success" id="mssg">
				<?php echo $_SESSION['message']; ?>
				</div>
			<?php endif; ?>
        <div class="tab-content active">

          
            <div class="row">
			<?php if($manager == true or is_admin()){ ?>
              <div class="col-md-12 ">
			 <?php if($manager_comm_status == 0) { ?>
			  <a href="#" onclick="approve_leave(); return false;" class="btn mright5 btn-danger pull-right display-block" data-toggle="sidebar-right" data-target=".approve_leave_ap">

			  <?php echo _l('Reject'); ?></a>
			  
			 <a href="#" onclick="reject_leave(); return false;" class="btn mright5 btn-success pull-right display-block" data-toggle="sidebar-right" data-target=".reject_leave_rj">

                  <?php echo _l('Approve'); ?>

                </a>
			 <?php } elseif($manager_comm_status == 1) {?>
			<span class="btn mright5 btn-success pull-right display-block disabled">Already Approved</span>
			 <?php  }  elseif($manager_comm_status == 2) {?>
			 <span class="btn mright5 btn-danger pull-right display-block disabled">Already Rejected</span>
			 <?php  }?>
            </div>
			<?php  } ?>
			</div>
			
			</div>
			
            <h4><?php echo _l('Leave Application Information'); ?></h4>
            <hr/>
            <div class="col-md-6">
              <table class="table border table-striped ">
                <tbody>
                  <tr>
                    <td><?php echo _l('subject'); ?></td>
                    <td id="subject_name"><?php echo html_entity_decode($request_leave->subject); ?></td>
					
                  </tr>
                  <tr>
                     <td><?php echo _l('type_of_leave'); ?></td>		
                    <td>
                      <?php   												for($i=0;$i<=count($type_of_leave);$i++){													if($type_of_leave[$i]['slug'] == $request_leave->type_of_leave){								echo $type_of_leave[$i]['type_name'];							}													}
                      $rel_type_text = '';
                      $type_of_leave = '';
                   
                     /*   switch ($request_leave->type_of_leave) {
                          case 8:
                          $type_of_leave = _l('annual_leave');
                          break;
                          case 2:
                          $type_of_leave = _l('maternity_leave');
                          break;
                          case 4:
                          $type_of_leave = _l('private_work_without_pay');
                          break;
                          case 1:
                          $type_of_leave = _l('sick_leave');
                          break;						  						  default:						  echo $request_leave->type_of_leave;
                        }  */
                      ?>                      
                    </td>
                  </tr>
                  <?php 
                  if($type_of_leave != ''){ 				  
                    ?>
                    <tr>
                      <td><?php echo _l('type_of_leave'); ?></td>
                      <td><?php //echo $type_of_leave;; ?></td>
                    </tr>
                  <?php }
                  ?>

                  
                  </tr>
					 <tr>
                      <td><?php echo _l('project_datecreated'); ?></td>
                      <td><?php 
                      $datecreated = $request_leave->datecreated;
                      if($datecreated == ''){
                        $datecreated = $request_leave->start_time;
                      }
                      echo _d($datecreated); ?></td>
                
                  <!-- handover recipients -->
                  <?php if($rel_type == 'Leave'){  ?>
                    <tr>
                      <td><?php echo _l('handover_recipients'); ?></td>
                      <td>

                        <?php 
                        $handover_recipient = $request_leave->handover_recipients;
                        $views_handover_recipient = '';
                        if(($handover_recipient != null ) && $handover_recipient != ''){
                          $views_handover_recipient .= '<a href="' . admin_url('staff/profile/' . $handover_recipient) . '">' . staff_profile_image($handover_recipient,[
                            'staff-profile-image-small mright5',
                          ], 'small', [
                            'data-toggle' => 'tooltip',
                            'data-title'  => get_staff_full_name($handover_recipient),
                          ]) . '</a>';
                        }
                        echo html_entity_decode($views_handover_recipient);
                        ?></td>
                      </tr>
                    <?php } ?>
					<tr>
                        <td><?php echo _l('department'); ?></td>
                        <td><?php echo html_entity_decode($request_leave->name); ?></td>
						
                      </tr>

                  </tbody>
                </table>
              </div>

              <div class="col-md-6">
                <table class="table table-striped">
                  <tbody>
                    <tr>
                    <td><?php echo _l('Manager'); ?></td>
                    <td>
                      <?php 
					  
                      echo $request_leave->type_of_leave_text;					  
					
                      ?>
                    </td>
                    </tr>
                    <tr>
					<?php if(strtotime($request_leave->start_time) == strtotime($request_leave->end_time)){ ?>
                      <td><?php echo _l('From Date'); ?></td>
                        <td><?php echo _d(date('Y-m-d', strtotime($request_leave->end_time)));  ?></td>
					<?php }else{?>
					<td><?php echo _l('From Date'); ?></td>
                        <td><?php echo _d(date('Y-m-d', strtotime($request_leave->start_time)));  ?></td>
                       
					<?php }?>
                      </tr>
					  <tr>
					  <td><?php echo _l('To Date'); ?></td>
					  <td><?php echo _dt(date('Y-m-d', strtotime($request_leave->end_time)));  ?> </td>
					  </tr>
                      
                       <!--<tr>
                        <td><?php //echo _l('Number_of_leaving_day'); ?></td>
                        <td><?php //echo html_entity_decode($request_leave->number_of_leaving_day); ?></td>
                      </tr>-->
                      <tr>
                        <td><?php echo _l('Number Of Days'); ?></td>
                        <td><?php 
                        if($request_leave->number_of_leaving_day != ''){
                          $number_day_off = $request_leave->number_of_leaving_day;
                        }
                        echo html_entity_decode($number_day_off); 
                        ?></td>
                      </tr>
                    
                    
                  </tbody>
                </table>
              </div>
				<?php if($manager == true){ ?>
				 <div class="col-md-6">
            <table class="table table-striped">  
              <tbody>
				<h4><?php echo _l("Employee Information") ?></h4>
                <tr>
                  <td><?php echo _l('requester'); ?></td>
                  <td>
                    <?php 
                    $_data = '<a href="' . admin_url('staff/profile/' . $request_leave->staff_id) . '">' . staff_profile_image($request_leave->staff_id, [
                      'staff-profile-image-small',
                    ]) . '</a>';
                    $_data .= ' <a href="' . admin_url('staff/profile/' . $request_leave->staff_id) . '">' . get_staff_full_name($request_leave->staff_id) . '</a>';
                    echo $_data;
                  ?></td>
                </tr>

                <tr>
                  <td><?php echo _l('email'); ?></td>
                  <td><?php echo $request_leave->email; ?></td>
                </tr>
                <tr>
                  <td><?php echo _l('department'); ?></td>
                  <td><?php echo $request_leave->name; ?></td>
                </tr>
              </tbody>
            </table>
          </div>
		   <div class="col-md-6">
                <table class="table">  
                  <tbody>
					
                  <h4><?php echo _l('Reason') ?></h4>
                      <tr>
					  <td><?php echo html_entity_decode($request_leave->reason); ?></td>
					  </tr>
                    </tbody>
                  </table>
                </div>
					<?php } else{  ?>
              <div class="col-md-6" style="padding-top:40px;">
                <table class="table">  
                  <tbody>
					
                  <h4><?php echo _l('Reason') ?></h4>
                      <tr>
					  <td><?php echo html_entity_decode($request_leave->reason); ?></td>
					  </tr>
                    </tbody>
                  </table>
                </div>
				
					
				 <div class="col-md-6" style="padding-top:40px;">
				 <?php if($approve_comment[0]['approval_comment'] !=null){ ?>
                <table class="table">  
                  <tbody>
			
                  <h4><?php echo _l("Manager's Comment") ?></h4>
						<?php  echo $results  ?>
                      <tr>
					  <td>
					 
					 <?php
                      echo $approve_comment[0]['approval_comment']; ?></td>
					  </tr>
                    </tbody>
                  </table>
				 <?php }else {?>
				 <table class="table">  
                  <tbody>
			
                  <h4><?php echo _l("Manager's Comment") ?></h4>
						<?php  echo $results  ?>
                      <tr>
					  <td>
					 
					 <?php
                      echo $approve_comment[0]['rejection_comment']; ?></td>
					  </tr>
                    </tbody>
                  </table>
				 <?php }?>
                </div>
               <?php  }?>
               <div class="col-md-12" >
			   <table class="table">  
                  <tbody>
			<a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
                <h4><?php echo _l('contract_attachments'); ?> </h4>           
              </a>                 
				
                      <tr>
					  <?php    
               $href_url = '';
               $data = '<div class="row">';
               foreach($request_leave->attachments as $attachment) {
                $href_url = site_url('modules/timesheets/uploads/requisition_leave/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
                $data .= '<div class="display-block contract-attachment-wrapper" style="padding-top:40px;">';
                $data .= '<div class="col-md-10">';
                $data .= '<div class="col-md-1">';
                $data .= '<a class="btn btn-info pull-right display-block" data-file='.$attachment['id'].' data-id='.$attachment['rel_id'].' onclick="preview_asset_btn(this)">';
                $data .= '<i class="fa fa-eye" ></i>'; 
                $data .= '</a>';
                $data .= '</div>';
                $data .= '<div class=col-md-9>';
                $data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
                $data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
                $data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
                $data .= '</div>';
                $data .= '</div>';
                
               $data .= '<div class="clearfix"></div><hr/>';
               $data .= '</div>';
             }
             $data .= '</div>';
             echo html_entity_decode($data);
             ?>
					  
					  </tr>
                    </tbody>
                  </table>
				  </div>
              
              <div id="contract_attachments" class="mtop30">
               

           </div>
         </div>
        

    
  </div>

</div>
</div>
</div>
<div id="asset_file_data">
</div>
</div>
</div>


<!-- The Modal -->

<!-- start -->

<div class="modal fade" id="approve_leave_ap" tabindex="-1" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        
		<h4 class="modal-title">
         <span class="add-title"><?php echo _l('Reject Leave'); ?></span>
       </h4>
     </div>

     <?php echo form_open_multipart(admin_url('timesheets/reject_comment'),array('id'=>'requisition-form'));?>             

     <div class="modal-body">

      <div id="additional_contract_type"></div>                    

      <div class="form">



        <div class="row">

          <div class="col-md-12">

            <div id="additional_contract_type"></div>

            <div class="form" id="approve_leave">
			<?php $start_time = $request_leave->start_time;
				$splitTimeStamp = explode(" ",$start_time);
				$sdate = $splitTimeStamp[0];
				//$time = $splitTimeStamp[1];
				$end_time = $request_leave->end_time;
				$splitTimeStampend = explode(" ",$end_time);
				$edate = $splitTimeStampend[0];
				//$time = $splitTimeStamp[1];
				
				
				
			?>
              <input name="start_time" type="hidden" value="<?php echo $sdate; ?>" />
			  <input name="end_time" type="hidden" value="<?php echo $edate; ?>" />
			 <input name="staff_id" type="hidden" value="<?php  echo get_staff_user_id()?>" />
			   <input type="hidden" name="userid" value="<?php echo $request_leave->staff_id; ?>">
		<input type="hidden" name="leave_id" value="<?php echo html_entity_decode($request_leave->id); ?>">
		<input type="hidden" name="leave_type" value="<?php echo $request_leave->type_of_leave; ?>">
			   
			

	   <div class="row mtop10">
	    <div class="col-md-12 pb-4" id="leave_">
		
           <div class="row mtop10">

        <div class="col-md-12">

          <?php echo render_textarea('rejection_comment', "Manager's Comment") ?>

        </div>

      </div>

        </div>
	</div>
 

    </div>

  </div>

</div>



</div>

</div>

<div class="modal-footer">

  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

  <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>

</div>

<?php echo form_close(); ?>                 

</div><!-- /.modal-content -->

</div><!-- /.modal-dialog -->

</div><!-- /.modal -->

<!-- end -->



<!-- The Modal -->

<!-- start -->

<div class="modal fade" id="reject_leave_rj" tabindex="-1" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title">
         <span class="add-title"><?php echo _l('Approve Leave'); ?></span>
       </h4>

     </div>

     <?php echo form_open_multipart(admin_url('timesheets/approve_comment'),array('id'=>'requisition-form'));?>             

     <div class="modal-body">

      <div id="additional_contract_type"></div>                    

      <div class="form">



        <div class="row">

          <div class="col-md-12">

            <div id="additional_contract_type"></div>

            <div class="form" id="reject_leave">
<?php $start_time = $request_leave->start_time;
				$splitTimeStamp = explode(" ",$start_time);
				$sdate = $splitTimeStamp[0];
				//$time = $splitTimeStamp[1];
				$end_time = $request_leave->end_time;
				$splitTimeStampend = explode(" ",$end_time);
				$edate = $splitTimeStampend[0];
				//$time = $splitTimeStamp[1];
				
			?>
              <input name="start_time" type="hidden" value="<?php echo $sdate; ?>" />
			  <input name="end_time" type="hidden" value="<?php echo $edate; ?>" />
           
			 <input name="staff_id" type="hidden" value="<?php  echo get_staff_user_id()?>" />
		<input type="hidden" name="leave_id" value="<?php echo html_entity_decode($request_leave->id); ?>">
		 <input type="hidden" name="userid" value="<?php echo $request_leave->staff_id; ?>">
		  <input type="hidden" name="leave_type" value="<?php echo $request_leave->type_of_leave; ?>">
			   
		

	   <div class="row mtop10">
	    <div class="col-md-12 pb-4" id="leave_">
		
           <div class="row mtop10">

        <div class="col-md-12">

          <?php echo render_textarea('approval_comment', "Manager's Comment") ?>

        </div>

      </div>

        </div>
	</div>
 

    </div>

  </div>

</div>



</div>

</div>

<div class="modal-footer">

  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

  <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>

</div>

<?php echo form_close(); ?>                 

</div><!-- /.modal-content -->

</div><!-- /.modal-dialog -->

</div><!-- /.modal -->

<!-- end -->

<div class="modal fade" id="convert_expense" tabindex="-1" role="dialog">
 <div class="modal-dialog">
  <div class="modal-content">
   <?php echo form_open(admin_url('timesheets/advance_payment_update'),array('id'=>'advance_payment_update-form','class'=>'dropzone dropzone-manual')); ?>
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php echo _l('add_new', _l('expense_lowercase')); ?></h4>
  </div>
  <div class="modal-body">
    <div id="dropzoneDragArea" class="dz-default dz-message">
     <span><?php echo _l('expense_add_edit_attach_receipt'); ?></span>
   </div>
   <div class="dropzone-previews"></div>
   <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('expense_name_help'); ?>"></i>
   <?php echo render_input('expense_name','expense_name'); ?>
   <?php echo render_textarea('note','expense_add_edit_note','',array('rows'=>4),array()); ?>
   <?php
   $this->load->model('clients_model');
   $customers = $this->clients_model->get();
   echo render_select('clientid',$customers,array('userid','company'),'customer'); ?>
   <?php
   $this->load->model('expenses_model');
   $categories = $this->expenses_model->get_category();

   if(is_admin() || get_option('staff_members_create_inline_expense_categories') == '1'){
    echo render_select_with_input_group('category',$categories,array('id','name'),'expense_category', '','<a href="#" onclick="new_category();return false;"><i class="fa fa-plus"></i></a>');
  } else {
    echo render_select('category',$categories,array('id','name'),'expense_category', '');
  }
  ?>
  <?php echo render_date_input('date','expense_add_edit_date',_d(date('Y-m-d'))); ?>
  <?php echo render_input('amount','expense_add_edit_amount','','number');
  $this->load->model('taxes_model');
  $taxes = $this->taxes_model->get();
  ?>
  <div class="row mbot15">
   <div class="col-md-6">
    <div class="form-group">
     <label class="control-label" for="tax"><?php echo _l('tax_1'); ?></label>
     <select class="selectpicker display-block" data-width="100%" name="tax" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
      <option value=""><?php echo _l('no_tax'); ?></option>
      <?php foreach($taxes as $tax){ ?>
        <option value="<?php echo html_entity_decode($tax['id']); ?>" data-subtext="<?php echo html_entity_decode($tax['name']); ?>"><?php echo html_entity_decode($tax['taxrate']); ?>%</option>
      <?php } ?>
    </select>
  </div>
</div>
<div class="col-md-6">
  <div class="form-group">
   <label class="control-label" for="tax2"><?php echo _l('tax_2'); ?></label>
   <select class="selectpicker display-block" data-width="100%" name="tax2" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" disabled>
    <option value=""><?php echo _l('no_tax'); ?></option>
    <?php foreach($taxes as $tax){ ?>
      <option value="<?php echo html_entity_decode($tax['id']); ?>" data-subtext="<?php echo html_entity_decode($tax['name']); ?>"><?php echo html_entity_decode($tax['taxrate']); ?>%</option>
    <?php } ?>
  </select>
</div>
</div>
</div>
<?php
$this->load->model('currencies_model');
$currencies = $this->currencies_model->get();
$currency_attr = array('disabled'=>true,'data-show-subtext'=>true);

$currency_attr = apply_filters_deprecated('expense_currency_disabled', [$currency_attr], '2.3.0', 'expense_currency_attributes');

foreach($currencies as $currency){
  if($currency['isdefault'] == 1){
    $currency_attr['data-base'] = $currency['id'];
  }
  if(isset($expense)){
    if($currency['id'] == $expense->currency){
      $selected = $currency['id'];
    }
    if($expense->billable == 0){
      if($expense->clientid != 0){
        $c = $this->clients_model->get_customer_default_currency($expense->clientid);
        if($c != 0){
          $customer_currency = $c;
        }
      }
    }
  } else {
    if(isset($customer_id)){
      $c = $this->clients_model->get_customer_default_currency($customer_id);
      if($c != 0){
        $customer_currency = $c;
      }
    }
    if($currency['isdefault'] == 1){
      $selected = $currency['id'];
    }
  }
}
$currency_attr = hooks()->apply_filters('expense_currency_attributes', $currency_attr);
?>
<input type="hidden" name="currency" value="<?php echo html_entity_decode($selected); ?>">
<div id="expense_currency">
 <?php echo render_select('currency', $currencies, array('id','name','symbol'), 'expense_currency', $selected, $currency_attr); ?>
</div>
<div class="checkbox checkbox-primary">
 <input type="checkbox" id="billable" name="billable" checked>
 <label for="billable"><?php echo _l('expense_add_edit_billable'); ?></label>
</div>
<?php echo render_input('reference_no','expense_add_edit_reference_no'); ?>
<?php
// Fix becuase payment modes are used for invoice filtering and there needs to be shown all
// in case there is payment made with payment mode that was active and now is inactive
$this->load->model('payment_modes_model');
$payment_modes = $this->payment_modes_model->get('', [
  'invoices_only !=' => 1,
]);
$expenses_modes = array();
foreach($payment_modes as $m){
 if(isset($m['invoices_only']) && $m['invoices_only'] == 1) {continue;}
 if($m['active'] == 1){
   $expenses_modes[] = $m;
 }
}
?>
<?php

echo render_select('paymentmode',$expenses_modes,array('id','name'),'payment_mode'); ?>
<div class="clearfix mbot15"></div>
<?php echo render_custom_fields('expenses'); ?>
<div id="pur_order_additional"></div>
<div class="clearfix"></div>
</div>


<input type="hidden" name="amount_received" value="">
<input type="hidden" name="received_date" value="">
<input type="hidden" name="id" value="">

<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>

<div class="modal fade" id="expense-category-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open(admin_url('timesheets/add_expense_category'),array('id'=>'expense-category-form')); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="edit-title"><?php echo _l('edit_expense_category'); ?></span>
          <span class="add-title"><?php echo _l('new_expense_category'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <input type="hidden" name="leave_id" value="<?php echo html_entity_decode($request_leave->id); ?>">
            <?php echo render_input('name','expense_add_edit_name'); ?>
            <?php echo render_textarea('description','expense_add_edit_description'); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
    </div><!-- /.modal-content -->
    <?php echo form_close(); ?>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<input type="hidden" name="has_send_mail" value="<?php echo html_entity_decode($has_send_mail); ?>">

<?php } ?>
<?php init_tail(); ?>
<?php require 'modules/timesheets/assets/js/requisition_detail_js.php';?>
 <script> 
        setTimeout(function() {
            $('#mssg').hide('fast');
        }, 10000);
    </script>