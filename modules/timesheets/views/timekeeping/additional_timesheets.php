<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
  .t2g-reg-hint {
    font-size: 13px;
    color: #64748b;
    margin: 0 0 14px;
    padding: 10px 14px;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 8px;
  }
</style>

<p class="t2g-reg-hint">
  Employees submit corrections from <strong>HRMS → My Attendance</strong>. Managers and HR review here — approve or reject pending requests.
</p>

<?php
  $table_data = [
      'Employee',
      'Department',
      'Date',
      'Time in',
      'Time out',
      'Work hrs',
      'Reason',
      'Approver',
      'Status',
      _l('options'),
  ];
  render_datatable($table_data, 'table_additional_timesheets');
?>

<div class="modal fade additional-timesheets-sidebar" id="additional_timesheets_modal"></div>

<div class="modal fade" id="regularization_reject_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Reject Regularization</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="reg_reject_id" value="">
        <input type="hidden" id="reg_reject_rel_type" value="additional_timesheets">
        <div class="form-group">
          <label for="reg_reject_reason">Reason for rejecting <span class="text-danger">*</span></label>
          <textarea id="reg_reject_reason" class="form-control" rows="4" placeholder="Enter rejection reason"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="reg_reject_submit_btn">Reject</button>
      </div>
    </div>
  </div>
</div>
