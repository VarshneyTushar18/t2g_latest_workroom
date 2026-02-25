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
        <div class="row">
            <div class="panel-table-full">
                <?php $this->load->view('admin/proposals/list_template'); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
var hidden_columns = [4, 5, 6, 7, 8];
</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
var proposal_id;
$(function() {
    var Proposals_ServerParams = {};
    $.each($('._hidden_inputs._filters input'), function() {
        Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });
    initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'],
        Proposals_ServerParams, [7, 'desc']);
    init_proposal();
    
    
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
    
    
</script>
</body>

</html>