<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>



<style>
    .error {

        color: red;



    }



    .custom-loader {

        display: none;

        text-align: center;

        position: absolute;

        top: 50%;

        left: 50%;

        transform: translate(-50%, -50%);

        background-color: rgba(0, 0, 0, 0.8);

        color: white;

        padding: 10px;

        border-radius: 5px;

    }



    /* Style for the loader */

    .loader {

        text-align: center;

        border: 4px solid #f3f3f3;

        border-top: 4px solid #3498db;

        border-radius: 50%;

        width: 30px;

        height: 30px;

        animation: spin 2s linear infinite;

        margin-bottom: 10px;

        margin: 0 auto;

    }



    @keyframes spin {

        0% {

            transform: rotate(0deg);

        }



        100% {

            transform: rotate(360deg);

        }

    }



    /* change the font size for deselect all overflow issue */

    .bootstrap-select .btn {

        font-size: 11.5px;



    }
</style>

<div id="wrapper">

    <div class="content">

        <?php if (!isset($view_all)) { ?>

            <?php $this->load->view('admin/staff/stats'); ?>

        <?php } ?>

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                        <?php if (staff_can('view-timesheets', 'reports')||is_in_managers_list()) { ?>

                            <!-- added is manager function to display button only if staff is a manager -->
                            

                                <a href="<?php echo site_url($this->uri->uri_string() . (!isset($view_all) ? '?view=all' : '')); ?>" class="btn btn-primary"><i class="fa-regular fa-clock"></i>

                                    <?php

                                    echo (isset($view_all) ? _l('my_timesheets') :  _l('view_members_timesheets'));

                                    ?>
                                <?php }  ?>

                                </a>

                                <hr />

                           

                            <canvas id="timesheetsChart" style="max-height:400px;" width="350" height="350"></canvas>

                            <hr />

                            <div class="clearfix"></div>

                            <div class="row">

                                <div class="col-md-5ths">

                                    <div class="select-placeholder">

                                        <select name="range" id="range" class="selectpicker" data-width="100%">

                                            <option value="today" selected><?php echo _l('today'); ?></option>

                                            <option value="this_month">

                                                <?php echo _l('staff_stats_this_month_total_logged_time'); ?></option>

                                            <option value="last_month">

                                                <?php echo _l('staff_stats_last_month_total_logged_time'); ?></option>

                                            <option value="this_week">

                                                <?php echo _l('staff_stats_this_week_total_logged_time'); ?></option>

                                            <option value="last_week">

                                                <?php echo _l('staff_stats_last_week_total_logged_time'); ?></option>

                                            <option value="period"><?php echo _l('period_datepicker'); ?></option>

                                        </select>

                                    </div>

                                    <div class="row mtop15">

                                        <div class="col-md-12 period hide">

                                            <?php //echo render_date_input('period-from'); 

                                            echo render_datetime_input('period-from');

                                            ?>

                                        </div>

                                        <div class="col-md-12 period hide">

                                            <?php //echo render_date_input('period-to');

                                            echo render_datetime_input('period-to');

                                            ?>

                                        </div>

                                    </div>

                                </div>

                                <?php if (isset($view_all)) { ?>

                                    <div class="col-md-5ths">

                                        <div class="select-placeholder">

                                            <?php echo render_select_without_label('staff_id[]', $staffs, array('staffid', array('firstname', 'lastname')), 'staff', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>

                                            <!-- <select multiple="true" name="staff_id[]" id="staff_id" class="selectpicker" data-width="100%">

                                            <option value=""><?php echo _l('all_staff_members'); ?></option>

                                            <option value="<?php echo get_staff_user_id(); ?>">

                                                <?php echo get_staff_full_name(get_staff_user_id()); ?></option>

                                            <?php foreach ($staff_members_with_timesheets as $staff) { ?>

                                                <option value="<?php echo $staff['staff_id']; ?>">

                                                    <?php echo get_staff_full_name($staff['staff_id']); ?></option>

                                            <?php } ?>

                                        </select> -->

                                        </div>

                                    </div>

                                <?php } ?>

                                <div class="col-md-5ths">

                                    <div class="select-placeholder">

                                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search" data-empty-title="<?php echo _l('client'); ?>" data-none-selected-text="<?php echo _l('client'); ?>">

                                        </select>

                                    </div>

                                </div>

                                <div class="col-md-5ths">

                                    <div class="select-placeholder projects-wrapper">

                                        <div id="project_ajax_search_wrapper">

                                            <select data-empty-title="<?php echo _l('project'); ?>" multiple="true" name="project_id[]" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%">

                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-5ths">

                                    <a href="#" id="apply_filters_timesheets" class="btn btn-primary pull-left"><?php echo _l('apply'); ?></a>

                                </div>

                                <div class="mtop10 hide relative pull-right" id="group_by_tasks_wrapper">

                                    <span><?php echo _l('group_by_task'); ?></span>

                                    <div class="onoffswitch">

                                        <input type="checkbox" name="group_by_task" class="onoffswitch-checkbox" id="group_by_task">

                                        <label class="onoffswitch-label" for="group_by_task"></label>

                                    </div>

                                </div>

                                <div class="col-md-12">

                                    <hr class="no-mtop" />

                                </div>

                            </div>

                            <div class="clearfix"></div>



                            <!-- Modal for the send mail-->

                            <div class="modal" id="myModal" tabindex="-1" role="dialog">

                                <div class="modal-dialog" role="document">

                                    <div class="modal-content">

                                        <div class="modal-header">

                                            <h5 class="modal-title">Send Report</h5>

                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                                                <span aria-hidden="true">&times;</span>

                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <!-- Modal body content goes here -->



                                            <?php

                                            echo validation_errors();



                                            echo form_open('', array('id' => 'myform'));



                                            echo form_label('To', 'to', array('class' => 'control-label'));





                                            $to_data = array(

                                                'name' => 'to',

                                                'id' => 'to',

                                                'class' => 'form-control',

                                            );



                                            $cc_data = array(

                                                'name' => 'cc',

                                                'id' => 'cc',

                                                'class' => 'form-control'

                                            );

                                            echo form_input($to_data);

                                            echo '<br>';

                                            echo form_label('CC', 'cc', array('class' => 'control-label'));



                                            echo form_input($cc_data);



                                            ?>





                                        </div>

                                        <div id="customLoader" class="custom-loader">

                                            <div class="loader"></div>



                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                                            <button type="submit" class="btn btn-primary">Send Email</button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <table class="table table-timesheets-report">

                                <thead>

                                    <tr>

                                        <?php if (isset($view_all)) { ?>

                                            <th><?php echo _l('staff_member'); ?></th>

                                        <?php } ?>

                                        <th><?php echo _l('project_timesheet_task'); ?></th>

                                        <th><?php echo _l('timesheet_tags'); ?></th>

                                        <?php if (get_option('round_off_task_timer_option') == 0) { ?>

                                            <th class="t-start-time"><?php echo _l('project_timesheet_start_time'); ?></th>

                                            <th class="t-end-time"><?php echo _l('project_timesheet_end_time'); ?></th>

                                        <?php } ?>

                                        <th width="150px;"><?php echo _l('note'); ?></th>

                                        <th><?php echo _l('task_relation'); ?></th>

                                        <th><?php echo _l('time_h'); ?></th>

                                        <th><?php echo _l('time_decimal'); ?></th>

                                    </tr>

                                </thead>

                                <tbody></tbody>

                                <tfoot>

                                    <tr>

                                        <?php if (isset($view_all)) { ?>

                                            <td></td>

                                        <?php } ?>

                                        <td></td>

                                        <td></td>

                                        <?php if (get_option('round_off_task_timer_option') == 0) { ?>

                                            <td></td>

                                            <td></td>

                                        <?php } ?>

                                        <td></td>

                                        <td></td>

                                        <td class="total_logged_time_timesheets_staff_h"></td>

                                        <td class="total_logged_time_timesheets_staff_d"></td>

                                    </tr>

                                </tfoot>

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

    $(function() {



        init_ajax_projects_search();

        var ctx = document.getElementById("timesheetsChart");

        var chartOptions = {

            type: 'bar',

            data: {

                labels: [],

                datasets: [{

                    label: '',

                    data: [],

                    backgroundColor: [],

                    borderColor: [],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                tooltips: {

                    enabled: true,

                    mode: 'single',

                    callbacks: {

                        label: function(tooltipItems, data) {

                            return decimalToHM(tooltipItems.yLabel);

                        }

                    }

                },

                scales: {

                    yAxes: [{

                        ticks: {

                            beginAtZero: true,

                            min: 0,

                            userCallback: function(label, index, labels) {

                                return decimalToHM(label);

                            },

                        }

                    }]

                },

            }

        };



        var timesheetsTable = $('.table-timesheets-report');

        $('#apply_filters_timesheets').on('click', function(e) {

            e.preventDefault();

            timesheetsTable.DataTable().ajax.reload();

        });



        $('body').on('change', '#group_by_task', function() {

            <?php if (get_option('round_off_task_timer_option') == 0) { ?>

                var tApi = timesheetsTable.DataTable();

                var visible = $(this).prop('checked') == false;

                var tEndTimeIndex = $('.t-end-time').index();

                var tStartTimeIndex = $('.t-start-time').index();

                if (tEndTimeIndex == -1 && tStartTimeIndex == -1) {

                    tStartTimeIndex = $(this).attr('data-start-time-index');

                    tEndTimeIndex = $(this).attr('data-end-time-index');

                } else {

                    $(this).attr('data-start-time-index', tStartTimeIndex);

                    $(this).attr('data-end-time-index', tEndTimeIndex);

                }

                tApi.column(tEndTimeIndex).visible(visible, false).columns.adjust();

                tApi.column(tStartTimeIndex).visible(visible, false).columns.adjust();

                tApi.ajax.reload();

            <?php } else { ?>

                timesheetsTable.DataTable().ajax.reload();

            <?php } ?>

        });



        var timesheetsChart;

        var Timesheets_ServerParams = {};

        Timesheets_ServerParams['range'] = '[name="range"]';

        Timesheets_ServerParams['period-from'] = '[name="period-from"]';

        Timesheets_ServerParams['period-to'] = '[name="period-to"]';

        // Timesheets_ServerParams['staff_id'] = '[name="staff_id"]';

        /* changed the staff id selector to accept multiple staff ids */

        // Timesheets_ServerParams['staff_id'] = 'select#staff_id';

        Timesheets_ServerParams['staff_id'] = '[name="staff_id[]"]';



        Timesheets_ServerParams['project_id'] = 'select#project_id';

        Timesheets_ServerParams['clientid'] = 'select#clientid';

        Timesheets_ServerParams['group_by_task'] = '[name="group_by_task"]:checked';

        initDataTable('.table-timesheets-report', window.location.href, undefined, undefined,

            Timesheets_ServerParams, [<?php if (isset($view_all)) {

                                            echo 3;
                                        } else {

                                            echo 2;
                                        } ?>, 'desc']);



        init_ajax_project_search_by_customer_id();



        $('#clientid').on('change', function() {

            var projectAjax = $('select#project_id');

            var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();

            var projectsWrapper = $('.projects-wrapper');

            projectAjax.selectpicker('destroy').remove();

            projectAjax = clonedProjectsAjaxSearchSelect;

            $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);

            init_ajax_project_search_by_customer_id();

        });



        timesheetsTable.on('init.dt', function() {

            var $dtFilter = $('body').find('.dataTables_filter');

            var $gr = $('#group_by_tasks_wrapper').clone()

            $('#group_by_tasks_wrapper').remove();

            $gr.removeClass('hide');

            $gr.find('span').css('position', 'absolute');

            $gr.find('span').css('top', '1px');

            $gr.find('span').css((isRTL == 'true' ? 'right' : 'left'), '-110px');

            $dtFilter.before($gr, '<div class="clearfix"></div>');

            $dtFilter.addClass('mtop15');

        });



        timesheetsTable.on('draw.dt', function() {

            var TimesheetsTable = $(this).DataTable();

            var logged_time = TimesheetsTable.ajax.json().logged_time;

            var chartResponse = TimesheetsTable.ajax.json().chart;

            var chartType = TimesheetsTable.ajax.json().chart_type;

            $(this).find('tfoot').addClass('bold');

            $(this).find('tfoot td.total_logged_time_timesheets_staff_h').html(

                "<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_h);

            $(this).find('tfoot td.total_logged_time_timesheets_staff_d').html(

                "<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_d);

            if (typeof(timesheetsChart) !== 'undefined') {

                timesheetsChart.destroy();

            }

            if (chartType != 'month') {

                chartOptions.data.labels = chartResponse.labels;

            } else {

                chartOptions.data.labels = [];

                for (var i in chartResponse.labels) {

                    chartOptions.data.labels.push(moment(chartResponse.labels[i]).format("MMM Do YY"));

                }

            }

            chartOptions.data.datasets[0].data = [];

            chartOptions.data.datasets[0].backgroundColor = [];

            chartOptions.data.datasets[0].borderColor = [];

            for (var i in chartResponse.data) {

                chartOptions.data.datasets[0].data.push(chartResponse.data[i]);

                if (chartResponse.data[i] == 0) {

                    chartOptions.data.datasets[0].backgroundColor.push('rgba(167, 167, 167, 0.6)');

                    chartOptions.data.datasets[0].borderColor.push('rgba(167, 167, 167, 1)');

                } else {

                    chartOptions.data.datasets[0].backgroundColor.push('rgba(132, 197, 41, 0.6)');

                    chartOptions.data.datasets[0].borderColor.push('rgba(132, 197, 41, 1)');

                }

            }



            var selected_staff_member = staff_member_select.val();

            var selected_staff_member_name = staff_member_select.find('option:selected').text();

            chartOptions.data.datasets[0].label = $('select[name="range"] option:selected').text() + (

                selected_staff_member != '' && selected_staff_member != undefined ? ' - ' +

                selected_staff_member_name : '');

            setTimeout(function() {

                timesheetsChart = new Chart(ctx, chartOptions);

            }, 30);

            do_timesheets_title();

        });

    });



    function do_timesheets_title() {

        var _temp;

        var range = $('select[name="range"]');

        var _range_heading = range.find('option:selected').text();

        if (range.val() != 'period') {

            _temp = _range_heading;

        } else {

            _temp = _range_heading + ' (' + $('input[name="period-from"]').val() + ' - ' + $('input[name="period-to"]')

                .val() + ') ';

        }

        $('head title').html(_temp + (staff_member_select.find('option:selected').text() != '' ? ' - ' + staff_member_select

            .find('option:selected').text() : ''));

    }





    $(document).ready(function() {





        /* Added condition to include send mail button only on all timesheet */



        <?php if (isset($view_all)) : ?>





            var dataTable = $(".table-timesheets-report").DataTable();



            // Add your custom button

            var button = '<button id="customButton" class ="btn btn-default buttons-collection btn-sm btn-default-dt-options">Send Report</button>';

            dataTable.buttons().container().prepend(button);



        <?php endif ?>



        // Handle button click event

        $("#customButton").on("click", function() {

            // Your button click logic goes here

            $('#myModal').modal('show');

        });





        jQuery.validator.addMethod("ccValidation", function(value, element) {



            return /^([\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}(\s*,\s*[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,})*)?$/.test(value);

        }, "Enter valid Email address seperated by comma");



        $('#myform').validate({

            rules: {

                to: {

                    required: true,

                    email: true

                },

                cc: {

                    ccValidation: true

                }

            },

            messages: {

                to: {

                    required: "Please enter recipient email address.",

                    email: "Please enter a valid recipient email address."

                },

                cc: {

                    email: "Please enter a valid email address for CC."

                }

            },

            submitHandler: function(e) {

                // Form is valid, proceed with AJAX form submission



                // e.preventDefault();

                $('#myform').hide();

                $('#customLoader').show();



                var dt = $('.table-timesheets-report').DataTable();





                var dataTableData = dt.rows().data().toArray();









                // console.log(' json stringify data: ', dataTableData); return false;





                if (dataTableData.length == 0) {

                    alert('No Entries found in Table.');

                    $('#myform').show();

                    $('#customLoader').hide();

                    return false;

                }





                var periodText = $('.filter-option-inner-inner:eq(0)').text();



                var periodfrom = $('#period-from').val();

                var periodto = $('#period-to').val();





                var form = $('#myform');



                serializedData = form.serializeArray();





                // serializedData = form.serialize();

                // console.log(serializedData); return false;



                // console.log(serializedData); return false;





                serializedData.push({

                    periodText

                }, {

                    periodfrom

                }, {

                    periodto

                }, {

                    dataTableData

                })



                // console.log(serializedData.push({name : "dataTableData", value : data}));

                // return false;

                // var serializedData = form.serialize() + '&' + csrfName + '=' + csrfHash + '&' + "dataTableData" + '=' + data;

                // console.log(serializedData); return false;



                $.ajax({

                    url: "sendmail",

                    method: "POST",

                    data: {

                        data: serializedData

                    },

                    success: function(result) {




                        $('#customLoader').hide();



                        $('#myform').show();



                        $('#myModal').modal('hide');


                        // console.log(result);
                        // return false;

                        if (result == 'success') {

                            alert('Email sent successfully!');

                        } else {

                            alert(result);

                        }

                    },

                    error: function(result) {

                        console.log('this is the error ', result);

                    }

                });





            }





        });













    });
</script>

</body>



</html>