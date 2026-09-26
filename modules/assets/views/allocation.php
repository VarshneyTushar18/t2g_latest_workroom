<?php init_head(); ?>
<style>
   #dynamicFilterContainer .filter-row {
      display: inline-block;
      margin: 10px;
   }

   #dynamicFilterContainer label {
      display: inline-block;
      margin-right: 10px;
      /* Adjust as needed */
   }

   #dynamicFilterContainer input {
      display: inline-flex;
      width: 150px;
      /* Adjust as needed */
   }
</style>

<div id="wrapper">
   <div class="content">
      <div class="row">

         <div id="dynamicFilterContainer" style="display: inline;">
            <!-- The dynamic filter UI will be inserted here -->
            <!-- <button id="applyFiltersBtn">Apply Filters</button> -->
         </div>
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-6 border-right">
                        <h4 class="no-margin font-bold"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo htmlspecialchars(_l($title)); ?></h4>
                        <hr />
                     </div>
                     <div class="col-md-6 text-right">
                        <?php if (has_permission('assets', '', 'create') || has_permission('assets', '', 'edit') || is_admin() || (function_exists('is_IT') && is_IT())) { ?>
                           <a href="#" onclick="openAllocateAssetModal(); return false;" class="btn btn-info">
                              <i class="fa fa-share"></i> Allocate Asset
                           </a>
                        <?php } ?>
                        <div class="btn-group mleft4 btn-with-tooltip-group _filter_data">
                           <button id="toggleFilter" class="btn btn-success ">Filter</button>
                        </div>
                     </div>
                  </div>

                  <?php
                  $table_data = [
                     'Emp ID',
                     'Employee Name',
                     'Manager ID',
                     'Reporting Manager',
                     _l('time'),
                     _l('asset_name'),
                     _l('acction_code'),
                     _l('action'),
                     _l('quantity_as_qty'),
                     _l('acction_from'),
                     'Images'
                  ];
                  render_datatable($table_data, 'table_action');
                  ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="allocate_asset_modal" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open_multipart(admin_url('assets/allocation_asset'), ['id' => 'page-allocation-form']); ?>
      <div class="modal-content modalwidth">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo htmlspecialchars(_l('allocation_asset')); ?></h4>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-12">
                  <?php echo render_input('acction_code', 'allocation_code', ''); ?>
               </div>
               <div class="col-md-12">
                  <label for="assets"><?php echo htmlspecialchars(_l('asset_name')); ?></label>
                  <select name="assets" id="allocate_asset_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('dropdown_non_selected_tex')); ?>" required>
                     <option value=""></option>
                     <?php foreach ($allocatable_assets as $asset) {
                        $rest = (int) $asset['amount'] - (int) $asset['total_allocation'];
                        ?>
                        <option value="<?php echo (int) $asset['id']; ?>"
                           data-rest="<?php echo (int) $rest; ?>"
                           data-amount="<?php echo (int) $asset['amount']; ?>"
                           data-location="<?php echo htmlspecialchars(get_asset_location($asset['asset_location'])); ?>">
                           <?php echo htmlspecialchars($asset['assets_name'] . ' (' . $asset['assets_code'] . ') — ' . $rest . ' available'); ?>
                        </option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-6 mtop15">
                  <label for="acction_to"><?php echo htmlspecialchars(_l('receiver')); ?></label>
                  <select name="acction_to" id="acction_to" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>">
                     <option value=""></option>
                     <?php foreach ($staffs as $s) { ?>
                        <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-6 mtop15">
                  <?php echo render_input('amount', 'amounts', '1', 'number', ['min' => 1, 'step' => 1, 'id' => 'allocate_amount']); ?>
                  <small class="text-muted" id="allocate_rest_label">Select an asset to see remaining quantity</small>
               </div>
               <div class="col-md-6">
                  <?php echo render_datetime_input('time_acction', 'allocation_time'); ?>
               </div>
               <div class="col-md-6">
                  <?php echo render_input('asset_location', 'asset_location', '', 'text', ['id' => 'allocate_asset_location']); ?>
               </div>
               <div class="col-md-12">
                  <label for="reporting_manager"><?php echo 'Reporting manager'; ?></label>
                  <select name="reporting_manager" id="reporting_manager" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo htmlspecialchars(_l('ticket_settings_none_assigned')); ?>" onchange="document.getElementById('reporting_manager_empid').value=this.value;">
                     <option value=""></option>
                     <?php foreach ($staffs as $s) { ?>
                        <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                     <?php } ?>
                  </select>
               </div>
               <div class="col-md-12 mtop15">
                  <?php echo render_input('acction_location', 'handover_location', ''); ?>
               </div>
               <div class="col-md-12">
                  <div class="form-group">
                     <label for="images" class="control-label"><small class="req text-danger">* </small>Upload Documents</label>
                     <input type="file" extension="gif,png,jpg,jpeg,pdf" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="images[]" id="images" multiple required>
                  </div>
               </div>
               <div class="col-md-12">
                  <?php echo render_textarea('acction_reason', 'acction_reason', ''); ?>
                  <input type="hidden" name="type" value="allocation">
                  <input type="hidden" name="acction_from" value="<?php echo (int) get_staff_user_id(); ?>">
                  <input type="hidden" name="reporting_manager_empid" id="reporting_manager_empid" value="">
                  <input type="hidden" name="redirect_to" value="allocation">
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
   initDataTable('.table-table_action', admin_url + 'assets/table_action_allocate/allocation');

   function openAllocateAssetModal() {
      $('#allocate_asset_modal').modal('show');
      setTimeout(function() {
         $('#allocate_asset_modal .selectpicker').selectpicker('refresh');
         if (typeof init_datepicker === 'function') {
            init_datepicker();
         }
      }, 200);
   }

   $('#allocate_asset_id').on('changed.bs.select change', function() {
      var $opt = $(this).find('option:selected');
      var rest = parseInt($opt.data('rest') || 0, 10);
      var amount = parseInt($opt.data('amount') || 0, 10);
      var location = $opt.data('location') || '';
      $('#allocate_amount').attr('max', rest > 0 ? rest : 1);
      if (parseInt($('#allocate_amount').val() || 0, 10) > rest) {
         $('#allocate_amount').val(rest > 0 ? rest : 1);
      }
      $('#allocate_rest_label').text(rest + ' / ' + amount + ' remaining');
      $('#allocate_asset_location').val(location);
   });

   appValidateForm($('#page-allocation-form'), {
      assets: 'required',
      acction_to: 'required',
      amount: 'required',
      time_acction: 'required',
      acction_location: 'required',
      reporting_manager: 'required',
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


<script>
   // custom filter code added

   function hideShowFilter() {
      $('#dynamicFilterContainer').hide(); // Hide filter by default

      $('#toggleFilter').on('click', function() {
         $('#dynamicFilterContainer').toggle();
      });

   }

   function customFilterSearch() {
      hideShowFilter();

      var projectTable = $('#DataTables_Table_0').DataTable();
      // Generate search inputs for each column
      var filters = []; // Store filter values
      function applyFilters() {
         projectTable.columns().every(function(index) {
            if (typeof filters[index] !== 'undefined') {
               this.search(filters[index]);
            } else {
               this.search('');
            }
         });

         projectTable.draw(); // Redraw the table
      }

      function isDateColumn(title) {
         const dateKeywords = ['date', 'deadline']; // Add more keywords as needed
         title = title.toLowerCase();

         return dateKeywords.some(keyword => title.includes(keyword));
      }

      projectTable.columns().every(function(index) {
         var column = this;
         var title = $(column.header()).text();
         var input;
         if (title == '#' || title == ' - ') {
            return false;
         }

         if (isDateColumn(title)) {
            input = $('<div class="filter-row"><label for="filter_' + index + '">' + title + ': </label><input type="date" class="form-control" placeholder="' + title + '"></div>')
               .appendTo($('#dynamicFilterContainer'));
         } else {
            input = $('<div class="filter-row"><label for="filter_' + index + '">' + title + ': </label><input type="text" class="form-control" placeholder="' + title + '"></div>')
               .appendTo($('#dynamicFilterContainer'));
         }


         input.find('input').on('keyup change', function() {
            filters[index] = $(this).val(); // Store the filter value
            applyFilters(); // Apply filters and redraw the table

         });
      });
      // $('#applyFiltersBtn').on('click', function() {
      //     applyFilters(); // Apply filters and redraw the table
      // });

   }




   function generateFilter() {
      // Generate dynamic filter form fields
      hideShowFilter();
      var filterHTML = '<form id="dynamicFilterForm">';
      $('#DataTables_Table_0 thead tr th').each(function(index) {
         var heading = $(this).text();
         var fieldType = inferColumnType(index, heading); // Call a function to infer column type
         filterHTML += '<div class="filter-input">';
         filterHTML += '<label for="filter_' + index + '">' + heading + ': </label>';

         filterHTML += fieldType;

         filterHTML += '</div>';
      });
      filterHTML += '<button type="submit" class = "btn btn-primary">Apply Filter</button></form>';

      // Insert the filter form into the dynamic filter container
      // $('#dynamicFilterContainer').html(filterHTML);
   }

   $(function() {
      customFilterSearch();

   })
</script>