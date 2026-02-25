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
	.modal-header{
		    padding: 0.3rem 1rem !important;
	}
	
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <?php if (has_permission('projects', '', 'create')) { ?>
                        <a href="<?php echo admin_url('projects/project'); ?>" class="btn btn-primary pull-left display-block mright5">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_project'); ?>
                        </a>
                    <?php } ?>

                    <a href="<?php echo admin_url('projects/gantt'); ?>" data-toggle="tooltip" data-title="<?php echo _l('project_gant'); ?>" class="btn btn-default btn-with-tooltip">
                        <i class="fa fa-align-left" aria-hidden="true"></i>
                    </a>

                    <!-- <?php //$this->load->view('admin/projects/dynamic_filter'); 
                            ?> -->
                    <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data">

                        <button id="toggleFilter" class="btn btn-success ">Filter</button>
                    </div>
                    <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">

                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-right width300">
                            <li>
                                <a href="#" data-cview="all" onclick="dt_custom_view('','.table-projects',''); return false;">
                                    <?php echo _l('expenses_list_all'); ?>
                                </a>
                            </li>
                            <?php
                            // Only show this filter if user has permission for projects view otherwise wont need this becuase by default this filter will be applied
                            if (has_permission('projects', '', 'view')) { ?>
                                <li>
                                    <a href="#" data-cview="my_projects" onclick="dt_custom_view('my_projects','.table-projects','my_projects'); return false;">
                                        <?php echo _l('home_my_projects'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="divider"></li>
                            <?php foreach ($statuses as $status) { ?>
                                <li class="<?php if ($status['filter_default'] == true && !$this->input->get('status') || $this->input->get('status') == $status['id']) {
                                                echo 'active';
                                            } ?>">
                                    <a href="#" data-cview="<?php echo 'project_status_' . $status['id']; ?>" onclick="dt_custom_view('project_status_<?php echo $status['id']; ?>','.table-projects','project_status_<?php echo $status['id']; ?>'); return false;">
                                        <?php echo $status['name']; ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div id="dynamicFilterContainer" style="display: inline;">
                    <!-- The dynamic filter UI will be inserted here -->
                    <!-- <button id="applyFiltersBtn">Apply Filters</button> -->
                </div>


                <div class="panel_s tw-mt-2 sm:tw-mt-4">
                    <div class="panel-body">

                        <div class="row mbot15">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>

                                    <span>
                                        <?php echo _l('projects_summary'); ?>
                                    </span>
                                </h4>

                                <?php
                                $_where = '';
                                if (!has_permission('projects', '', 'view')) {
                                    $_where = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
                                }
                                ?>
                            </div>
                            <div class="_filters _hidden_inputs">
                                <?php
                                echo form_hidden('my_projects');
                                foreach ($statuses as $status) {
                                    $value = $status['id'];
                                    if ($status['filter_default'] == false && !$this->input->get('status')) {
                                        $value = '';
                                    } elseif ($this->input->get('status')) {
                                        $value = ($this->input->get('status') == $status['id'] ? $status['id'] : '');
                                    }
                                    echo form_hidden('project_status_' . $status['id'], $value); ?>
                                    <div class="col-md-2 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">
                                        <?php $where = ($_where == '' ? '' : $_where . ' AND ') . 'status = ' . $status['id']; ?>
                                        <a href="#" class="tw-text-neutral-600 hover:tw-opacity-70 tw-inline-flex tw-items-center" onclick="dt_custom_view('project_status_<?php echo $status['id']; ?>','.table-projects','project_status_<?php echo $status['id']; ?>',true); return false;">
                                            <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                                                <?php echo total_rows(db_prefix() . 'projects', $where); ?>
                                            </span>
                                            <span style="color:<?php echo $status['color']; ?>" project-status-<?php echo $status['id']; ?>">
                                                <?php echo $status['name']; ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php
                                } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-separator" />
                        <div class="panel-table-full">
                            <?php echo form_hidden('custom_view'); ?>
                            <?php $this->load->view('admin/projects/table_html'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/projects/copy_settings'); ?>
<?php init_tail(); ?>
<script>
    $(function() {


        var ProjectsServerParams = {};

        $.each($('._hidden_inputs._filters input'), function() {
            ProjectsServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });

        initDataTable('.table-projects', admin_url + 'projects/table', undefined, undefined, ProjectsServerParams,
            <?php echo hooks()->apply_filters('projects_table_default_order', json_encode([5, 'asc'])); ?>);

        init_ajax_search('customer', '#clientid_copy_project.ajax-search');



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


    function inferColumnType(index, heading) {
        // Implement your logic to infer column type based on column content
        // For example, you could check the content of the first few rows in that column
        // and decide whether it's a text, numeric, date, or other type.
        // Return 'select', 'date', or 'text' based on your inference.

        init_ajax_projects_search();

        var startDateConditions = [
            'Start Date',

        ]

        var endDateConditions = [
            'Deadline',
        ]



        // Example inference logic:
        var firstCellValue = $('#DataTables_Table_0 tbody tr:first td:eq(' + index + ')').text();


        if (startDateConditions.includes(heading)) {
            return '<input class = "form-control" type="date" id="filter_' + index + '" name="' + heading + '">';
        } else if (endDateConditions.includes(heading)) {
            return '<input class = "form-control" type="date" id="filter_' + index + '" name="' + heading + '">';
        } else {
            return '<input class = "form-control" type="text" id="filter_' + index + '" name="' + heading + '">';
        }
    }

    function isDate(value) {
        var dateFormat = /^\d{4}-\d{2}-\d{2}$/;
        return dateFormat.test(value);
    }
</script>
<script>
function loadTeamHours(projectId) {
    var month = $('#month-filter-' + projectId).val();
    var from = $('.date-from[data-project-id="' + projectId + '"]').val();
    var to = $('.date-to[data-project-id="' + projectId + '"]').val();
	var staffId = $('.staff-filter[data-project-id="' + projectId + '"]').val(); 

    $.post(admin_url + 'projects/filter_team_hours', {
        project_id: projectId,
        month: month,
        from: from,
        to: to,
		 staff_id: staffId 
    }, function (html) {
        $('#team-hours-table-' + projectId).html(html);
    });
}

$('body').on('change', '.month-filter, .date-from, .date-to,.staff-filter', function () {
    var projectId = $(this).data('project-id');
    loadTeamHours(projectId);
});

</script>
<script>
$(document).ready(function() {
  $('.modal').on('shown.bs.modal', function () {
    var modal = $(this);
    var projectId = modal.find('.staff-filter').data('project-id');
    
    // Load staff into dropdown
    $.post(admin_url + 'projects/get_project_staff_with_time', { project_id: projectId }, function(response) {
        modal.find('.staff-filter').html(response);
    });

    // Filter on change
    modal.find('.staff-filter, .date-from, .date-to').on('change', function () {
      filterTeamHours(modal, projectId);
    });
  });
  </script>
</body>

</html>