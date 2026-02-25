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
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data">

                        <button id="toggleFilter" class="btn btn-success ">Filter</button>
                     </div>
                     <div class="col-md-4 border-right">
                        <h4 class="no-margin font-bold"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo htmlspecialchars(_l($title)); ?></h4>
                        <hr />
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

</div>
<?php init_tail(); ?>
</body>

</html>
<script>
   initDataTable('.table-table_action', admin_url + 'assets/table_action_allocate/allocation');
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