<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-6 border-right">
                        <h4 class="no-margin font-bold"><i class="fa fa-pencil-square" aria-hidden="true"></i> <?php echo htmlspecialchars(_l($title)); ?></h4>
                        <hr />
                     </div>
                     <div class="col-md-6 text-right">
                        <?php if (has_permission('assets', '', 'create') || has_permission('assets', '', 'edit') || is_admin() || (function_exists('is_IT') && is_IT())) { ?>
                           <a href="#" onclick="openRevokeAssetModal(); return false;" class="btn btn-info">
                              <i class="fa fa-reply"></i> Revoke Asset
                           </a>
                        <?php } ?>
                     </div>
                  </div>

                  <?php
                  $table_data = [
                     _l('time'),
                     _l('asset_name'),
                     _l('acction_code'),
                     _l('action'),
                     _l('quantity_as_qty'),
                     _l('acction_from'),
                     _l('acction_to'),
                     'images'
                  ];
                  render_datatable($table_data, 'table_action');
                  ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="revoke_asset_modal" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('assets/revoke_asset'), ['id' => 'page-revoke-form']); ?>
      <div class="modal-content modalwidth">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo htmlspecialchars(_l('recalled')); ?></h4>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-12">
                  <?php echo render_input('acction_code', 'recalled_code', ''); ?>
               </div>
               <div class="col-md-12">
                  <label for="assets"><?php echo htmlspecialchars(_l('asset_name')); ?></label>
                  <select name="assets" id="revoke_asset_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('dropdown_non_selected_tex')); ?>" required>
                     <option value=""></option>
                     <?php foreach ($revokable_assets as $asset) { ?>
                        <option value="<?php echo (int) $asset['id']; ?>"
                           data-allocated="<?php echo (int) $asset['total_allocation']; ?>"
                           data-location="<?php echo htmlspecialchars(get_asset_location($asset['asset_location'])); ?>">
                           <?php echo htmlspecialchars($asset['assets_name'] . ' (' . $asset['assets_code'] . ') — ' . (int) $asset['total_allocation'] . ' allocated'); ?>
                        </option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-6 mtop15">
                  <label for="acction_to"><?php echo htmlspecialchars(_l('recalled_from')); ?></label>
                  <select name="acction_to" id="revoke_acction_to" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                     <option value=""></option>
                     <?php foreach ($staffs as $s) { ?>
                        <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-6 mtop15">
                  <label for="acction_from"><?php echo 'Revoke Who'; ?></label>
                  <select name="acction_from" id="revoke_acction_from" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                     <option value=""></option>
                     <?php foreach ($staffs as $s) { ?>
                        <option value="<?php echo htmlspecialchars($s['staffid']); ?>" <?php echo ((int) $s['staffid'] === (int) get_staff_user_id()) ? 'selected' : ''; ?>>
                           <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                        </option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-6 form-group mtop15">
                  <label for="amount_revoke" class="control-label"><?php echo htmlspecialchars(_l('amounts')); ?></label>
                  <input type="number" id="amount_revoke" name="amount" class="form-control" min="1" step="1" value="1">
                  <small class="text-muted" id="revoke_allocated_label">Select asset and staff to see allocated quantity</small>
               </div>
               <div class="col-md-6 mtop15">
                  <?php echo render_datetime_input('time_acction', 'recalled_time', _dt(date('Y-m-d H:i:s'))); ?>
               </div>
               <div class="col-md-6">
                  <?php echo render_input('asset_location', 'asset_location', '', 'text', ['id' => 'revoke_asset_location']); ?>
               </div>
               <div class="col-md-6">
                  <?php echo render_input('acction_location', 'handover_location', ''); ?>
               </div>
               <div class="col-md-12">
                  <?php echo render_textarea('acction_reason', 'Revoke Reason', ''); ?>
                  <input type="hidden" name="type" value="revoke">
                  <input type="hidden" name="redirect_to" value="eviction">
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo htmlspecialchars(_l('close')); ?></button>
            <button type="submit" class="btn btn-info"><?php echo htmlspecialchars(_l('submit')); ?></button>
         </div>
      </div>
      <?php echo form_close(); ?>
   </div>
</div>

<?php init_tail(); ?>
</body>
</html>
<script>
   initDataTable('.table-table_action', admin_url + 'assets/table_action_allocate/revoke');

   function openRevokeAssetModal() {
      $('#revoke_asset_modal').modal('show');
      setTimeout(function() {
         $('#revoke_asset_modal .selectpicker').selectpicker('refresh');
         if (typeof init_datepicker === 'function') {
            init_datepicker();
         }
      }, 200);
   }

   function refreshRevokeAllocatedQty() {
      var staff = $('#revoke_acction_to').val();
      var assets = $('#revoke_asset_id').val();
      var $opt = $('#revoke_asset_id').find('option:selected');
      var location = $opt.data('location') || '';
      $('#revoke_asset_location').val(location);

      if (!staff || !assets) {
         $('#revoke_allocated_label').text('Select asset and staff to see allocated quantity');
         return;
      }

      $.post(admin_url + 'assets/get_asset_allocation_by_staff/' + staff + '/' + assets).done(function(response) {
         response = JSON.parse(response);
         var total = parseInt(response.total || 0, 10);
         $('#revoke_allocated_label').text(total + ' currently allocated to this staff');
         $('#amount_revoke').attr('max', total > 0 ? total : 1);
         if (parseInt($('#amount_revoke').val() || 0, 10) > total) {
            $('#amount_revoke').val(total > 0 ? total : 1);
         }
      });
   }

   $('#revoke_asset_id, #revoke_acction_to').on('changed.bs.select change', function() {
      refreshRevokeAllocatedQty();
   });

   appValidateForm($('#page-revoke-form'), {
      assets: 'required',
      acction_to: 'required',
      acction_from: 'required',
      amount: 'required',
      acction_code: {
         required: true,
         remote: {
            url: site_url + 'admin/assets/acction_code_exists',
            type: 'post',
            data: {
               assets_code: function() {
                  return $('input[name="acction_code"]').val();
               }
            }
         }
      }
   });
</script>
