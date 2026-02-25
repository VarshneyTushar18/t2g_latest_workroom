<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .table-loading {
        background: unset;
    }

    #DataTables_Table_0_wrapper {
        overflow: scroll;
    }

    .dt-table-loading.table,
    .table-loading .dataTables_filter,
    .table-loading .dataTables_length,
    .table-loading .dt-buttons,
    .table-loading table tbody tr,
    .table-loading table thead th {
        opacity: 1 !important;
    }
    
    .leave_balance #applybtn{
     float: left !important;
    padding: 6px 20px;
    }
	.leave_balance .col-md-5ths{
		width:85% !important;
	}
	.leave_balance .bootstrap-select.bs3{
		float:left;
	}
	.leave_balance .bootstrap-select.bs3{
		float:left;
		padding: 0px 5px;
	}
</style>

<div id="wrapper" class="leave_balance">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-5ths">
                                <div class="select-placeholder col-md-8">

                                    <?= form_open('admin/staff/leave_balance') ?>


                                    <select name="range" id="range" class="selectpicker">
                                        <?php
                                        $monthData = [
                                            "All Months", "January", "February", "March", "April", "May", "June", "July",
                                            "August", "September", "October", "November", "December"
                                        ];

                                        foreach ($monthData as $key => $value) {
                                            $isSelected = ""; //added this line
                                            if ($currentMonth == $key) {
                                                $isSelected = "selected";
                                            }
                                            echo '<option value="' . $key . '"' . $isSelected . '>' . $value . '</option>';
                                        }
                                        ?>
                                        <!-- Add options for all 12 months -->
                                    </select>
                                    <select name="year" id="year" class="selectpicker">
                                        <?php
                                        // Generate year options from the current year to a specific range
                                        for ($year = $currentYear; $year >= ($currentYear - 10); $year--) {

                                            $isSelected = ($year == $selectedYear) ? "selected" : '';

                                            echo '<option value="' . $year . '"' . $isSelected . '>' . $year . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <button type="submit" id = 'applybtn' class="btn btn-primary pull-left">Apply</button>


                                    </form>

                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-12 period hide">
                                        <?php //echo render_date_input('period-from');
                                        echo render_date_input('period-from');
                                        ?>
                                    </div>
                                    <div class="col-md-12 period hide">
                                        <?php //echo render_date_input('period-to');
                                        echo render_date_input('period-to');
                                        ?>
                                    </div>
                                </div>
                            </div>


                            <!-- <div class="col-md-5ths">
                                <a href="#" id="apply_filters_timesheets" class="btn btn-primary pull-left"><?php echo _l('apply'); ?></a>
                            </div> -->
                            <div class="mtop10 hide relative pull-right" id="group_by_tasks_wrapper">
                                <span><?php echo _l('group_by_task'); ?></span>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="group_by_task" class="onoffswitch-checkbox"
                                           id="group_by_task">
                                    <label class="onoffswitch-label" for="group_by_task"></label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr class="no-mtop"/>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <?php
                  //   echo '<pre>';
                   // print_r($table_data);
                   //     echo '</pre>';
				   
                        ?>

                        <table class="table table-timesheets-report">
                            <thead>
                            <tr>

                                <th><?php echo "Emp ID"; ?></th>
                                <th><?php echo "Employee Name"; ?></th>
                                <th><?php echo "Month"; ?></th>
                                <th><?php echo "Days In Month"; ?></th>
                                <th><?php echo "Leaves Carry Forward"; ?></th>
                                <th><?php echo "Leaves Taken"; ?></th>
                                <th><?php echo "Leaves Earned"; ?></th>
                                <th><?php echo "Leave Balance"; ?></th>
								<th><?php echo "Absent"; ?></th>


                            </tr>
                            </thead>
                            <tbody>
							
                            <?php foreach ($table_data as $month_number => $value) :
							//print_R($table_data);
                                for ($i = 0; $i < count($value); $i++) {
									
								//	$res  = $this->db->query('SELECT count(number_of_leaving_day) as count_leave FROM tbltimesheets_requisition_leave left join tblleave_comment ON tbltimesheets_requisition_leave.id = tblleave_comment.leave_id where tbltimesheets_requisition_leave.staff_id = "'.$value[$i]['staffid'].'" AND tbltimesheets_requisition_leave.status=1 AND Month(start_time) ='.$month_number)->result_array();;
									
										//echo '<pre>';print_r($res);die;	
								//		$leave_count =  (int)$res[0]['count_leave'];
								//	echo $leave_count;
                                    ?>
                                    <tr>
                                        <td><?php echo($value[$i]['empid'] ?? $value[$i]['staffid']) ?></td>
                                        <td><?php echo get_staff_full_name($value[$i]['staffid']); ?></td>
                                        <td><?php echo $month_number; ?></td>
                                        <td><?php echo cal_days_in_month(CAL_GREGORIAN, $month_number, $currentYear) ?></td>
                                        <td><?php echo $value[$i]['carry_forward'] ?></td>
								
									
									<td><?php echo $value[$i]['leave_taken']; ?></td>
								
                                        <td> <?php echo $value[$i]['earned_leave']; ?> </td>
										<?php // ?>
                                        <td> <?php echo $value[$i]['monthly_leaves']-($value[$i]['leave_taken']-$value[$i]['earned_leave']); ?></td>
										
										 <td> <?php echo $value[$i]['status']; ?></td>
		
                                    </tr>

                                    <?php
								
                                }
                            endforeach; ?>
                            </tbody>


                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var staff_member_select = $('select[name="staff_id"]');
    $(function () {

        // init_ajax_projects_search();
        // var ctx = document.getElementById("timesheetsChart");
        // var chartOptions = {
        //     type: 'bar',
        //     data: {
        //         labels: [],
        //         datasets: [{
        //             label: '',
        //             data: [],
        //             backgroundColor: [],
        //             borderColor: [],
        //             borderWidth: 1
        //         }]
        //     },
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         tooltips: {
        //             enabled: true,
        //             mode: 'single',
        //             callbacks: {
        //                 label: function(tooltipItems, data) {
        //                     return decimalToHM(tooltipItems.yLabel);
        //                 }
        //             }
        //         },
        //         scales: {
        //             yAxes: [{
        //                 ticks: {
        //                     beginAtZero: true,
        //                     min: 0,
        //                     userCallback: function(label, index, labels) {
        //                         return decimalToHM(label);
        //                     },
        //                 }
        //             }]
        //         },
        //     }
        // };

        var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'csv',
                {
                    extend: 'excel',
                    title: ''
                },
                'pdf',
                'print'
            ]
        });
        // $.ajax({
        //     url: "get_leave_data",
        //     type: "POST",
        //     dataType: "json",
        //     success: function(data) {
        //         console.log(data); // Log the received data to the browser's console
        //     }
        // });
        // timesheetsTable.DataTable({
        //     // "processing": true,
        //     // "serverSide": true,
        //     "ajax": {
        //         "url": "get_leave_data", // Replace with your CodeIgniter controller/method
        //         "type": "GET", // Use GET or POST request as needed
        //         "dataType": "json" // Specify the data type you are expecting
        //     },
        //     "columns": [{
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",
        //             "data": "empid",

        //         }
        //         // Add more columns as needed
        //     ],
        // })

        // $('#apply_filters_timesheets').on('click', function(e) {
        //     e.preventDefault();
        //     timesheetsTable.DataTable().ajax.reload();
        // });

        // $('body').on('change', '#group_by_task', function() {
        //     <?php if (get_option('round_off_task_timer_option') == 0) { ?>
        //         var tApi = timesheetsTable.DataTable();
        //         var visible = $(this).prop('checked') == false;
        //         var tEndTimeIndex = $('.t-end-time').index();
        //         var tStartTimeIndex = $('.t-start-time').index();
        //         if (tEndTimeIndex == -1 && tStartTimeIndex == -1) {
        //             tStartTimeIndex = $(this).attr('data-start-time-index');
        //             tEndTimeIndex = $(this).attr('data-end-time-index');
        //         } else {
        //             $(this).attr('data-start-time-index', tStartTimeIndex);
        //             $(this).attr('data-end-time-index', tEndTimeIndex);
        //         }
        //         tApi.column(tEndTimeIndex).visible(visible, false).columns.adjust();
        //         tApi.column(tStartTimeIndex).visible(visible, false).columns.adjust();
        //         tApi.ajax.reload();
        //     <?php } else { ?>
        //         timesheetsTable.DataTable().ajax.reload();
        //     <?php } ?>
        // });


        // init_ajax_project_search_by_customer_id();

        // $('#clientid').on('change', function() {
        //     var projectAjax = $('select#project_id');
        //     var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
        //     var projectsWrapper = $('.projects-wrapper');
        //     projectAjax.selectpicker('destroy').remove();
        //     projectAjax = clonedProjectsAjaxSearchSelect;
        //     $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
        //     init_ajax_project_search_by_customer_id();
        // });

        // timesheetsTable.on('draw.dt', function() {
        //     var TimesheetsTable = $(this).DataTable();
        //     var logged_time = TimesheetsTable.ajax.json().logged_time;
        //     var chartResponse = TimesheetsTable.ajax.json().chart;
        //     var chartType = TimesheetsTable.ajax.json().chart_type;
        //     $(this).find('tfoot').addClass('bold');
        //     $(this).find('tfoot td.total_logged_time_timesheets_staff_h').html(
        //         "<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_h);
        //     $(this).find('tfoot td.total_logged_time_timesheets_staff_d').html(
        //         "<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_d);
        //     if (typeof(timesheetsChart) !== 'undefined') {
        //         timesheetsChart.destroy();
        //     }
        //     if (chartType != 'month') {
        //         chartOptions.data.labels = chartResponse.labels;
        //     } else {
        //         chartOptions.data.labels = [];
        //         for (var i in chartResponse.labels) {
        //             chartOptions.data.labels.push(moment(chartResponse.labels[i]).format("MMM Do YY"));
        //         }
        //     }
        //     chartOptions.data.datasets[0].data = [];
        //     chartOptions.data.datasets[0].backgroundColor = [];
        //     chartOptions.data.datasets[0].borderColor = [];
        //     for (var i in chartResponse.data) {
        //         chartOptions.data.datasets[0].data.push(chartResponse.data[i]);
        //         if (chartResponse.data[i] == 0) {
        //             chartOptions.data.datasets[0].backgroundColor.push('rgba(167, 167, 167, 0.6)');
        //             chartOptions.data.datasets[0].borderColor.push('rgba(167, 167, 167, 1)');
        //         } else {
        //             chartOptions.data.datasets[0].backgroundColor.push('rgba(132, 197, 41, 0.6)');
        //             chartOptions.data.datasets[0].borderColor.push('rgba(132, 197, 41, 1)');
        //         }
        //     }

        //     var selected_staff_member = staff_member_select.val();
        //     var selected_staff_member_name = staff_member_select.find('option:selected').text();
        //     chartOptions.data.datasets[0].label = $('select[name="range"] option:selected').text() + (
        //         selected_staff_member != '' && selected_staff_member != undefined ? ' - ' +
        //         selected_staff_member_name : '');
        //     setTimeout(function() {
        //         timesheetsChart = new Chart(ctx, chartOptions);
        //     }, 30);
        //     do_timesheets_title();
        // });
    });

    // function do_timesheets_title() {
    //     var _temp;
    //     var range = $('select[name="range"]');
    //     var _range_heading = range.find('option:selected').text();
    //     if (range.val() != 'period') {
    //         _temp = _range_heading;
    //     } else {
    //         _temp = _range_heading + ' (' + $('input[name="period-from"]').val() + ' - ' + $('input[name="period-to"]')
    //             .val() + ') ';
    //     }
    //     $('head title').html(_temp + (staff_member_select.find('option:selected').text() != '' ? ' - ' + staff_member_select
    //         .find('option:selected').text() : ''));
    // }
</script>
</body>

</html>