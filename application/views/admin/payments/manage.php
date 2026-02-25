<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
        
        <div class="panel_s">
            
            
            <div class="panel-body">
                
                <div class="panel-table-full">
                    <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data">

                        <button id="toggleFilter" class="btn btn-success ">Filter</button>
                    </div>
                 <div id="dynamicFilterContainer">
                <!-- The dynamic filter UI will be inserted here -->
            </div>
            
                    <?php $this->load->view('admin/payments/table_html'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-payments', admin_url + 'payments/table', undefined, undefined, 'undefined',
        <?php echo hooks()->apply_filters('payments_table_default_order', json_encode([0, 'desc'])); ?>);
        customFilterSearch();
});

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
</script>
</body>

</html>