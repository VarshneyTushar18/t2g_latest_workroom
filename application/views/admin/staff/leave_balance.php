<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .leave_balance .table-loading {
        background: unset !important;
    }
    .leave_balance .table-loading::before {
        display: none !important;
        content: none !important;
        animation: none !important;
    }

    #DataTables_Table_0_wrapper,
    .leave_balance .dataTables_wrapper {
        overflow: auto;
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
		padding: 0px 5px;
	}
  .leave-summary-section { margin-bottom: 18px; }
  .leave-summary-section h5 { margin: 0 0 12px; font-weight: 700; color: #111; }
  .leave-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 8px;
  }
  .leave-summary-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px 12px;
    text-align: center;
    min-height: 88px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .leave-summary-card .val {
    font-size: 28px;
    line-height: 1.1;
    font-weight: 600;
    color: #111827;
  }
  .leave-summary-card .val.danger { color: #dc2626; }
  .leave-summary-card .val.success { color: #16a34a; }
  .leave-summary-card .lbl {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  .leave-balance-table-section h5 { margin: 0 0 10px; font-weight: 700; color: #111; }
  .el-earned-input { width: 72px; display: inline-block; height: 28px; padding: 2px 6px; font-size: 12px; }
  .el-earned-save { padding: 2px 8px; font-size: 11px; margin-left: 4px; }
  .el-earned-override { color: #2563eb; font-size: 10px; display: block; }
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
                                    <?php if (!empty($userid)) { ?>
                                    <input type="hidden" name="staff_id" value="<?php echo (int) $userid; ?>">
                                    <?php } ?>


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

                        <?php if (!empty($can_pick_staff) && !empty($staff_list)) { ?>
                        <div class="row mbot15">
                            <div class="col-md-5">
                                <label class="control-label">View leave balance for</label>
                                <select id="leave_balance_staff_pick" class="form-control">
                                    <?php foreach ($staff_list as $s) {
                                        $is_me = ((int) $s['staffid'] === (int) get_staff_user_id());
                                        $is_sel = ((int) $s['staffid'] === (int) $userid);
                                    ?>
                                        <option value="<?php echo (int) $s['staffid']; ?>" <?php echo $is_sel ? 'selected' : ''; ?>>
                                            <?php echo html_escape(trim($s['firstname'] . ' ' . $s['lastname'])); ?><?php echo $is_me ? ' (Me)' : ''; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <?php if (!empty($is_team_manager) && empty($is_hr_viewer)) { ?>
                                    <p class="text-muted" style="font-size:12px;margin-top:6px;">Managers see their own balance and direct team only.</p>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if (!empty($summary)) { ?>
                        <div class="leave-summary-section">
                            <h5>My leave summary — <?php echo html_escape($summary['month_name']); ?> <?php echo (int) $selectedYear; ?></h5>
                            <div class="leave-summary-cards">
                                <div class="leave-summary-card">
                                    <div class="val"><?php echo html_escape($summary['carry_forward']); ?></div>
                                    <div class="lbl">Carry Forward</div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val"><?php echo html_escape($summary['leave_taken']); ?></div>
                                    <div class="lbl">Leaves Taken</div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val success"><?php echo html_escape($summary['earned_leave']); ?></div>
                                    <div class="lbl">Leaves Earned</div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val success"><?php echo html_escape($summary['leave_balance']); ?></div>
                                    <div class="lbl">Leave Balance</div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val danger"><?php echo html_escape($summary['absent']); ?></div>
                                    <div class="lbl">Absent</div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if (!empty($leave_balance_cards) && is_dir(module_dir_path('timesheets'))) {
                            $viewing_self = ((int) $userid === (int) get_staff_user_id());
                            $leave_balance_section_title = $viewing_self
                                ? 'My leave balance'
                                : ('Team leave balance — ' . get_staff_full_name($userid));
                            include module_dir_path('timesheets', 'views/partials/leave_balance_cards.php');
                        } ?>

                        <div class="leave-balance-table-section">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                              <h5 class="mbot0"><?php echo (!empty($is_team_manager) || !empty($is_hr_viewer)) ? 'Team leave balance report' : 'Leave balance report'; ?></h5>
                              <?php if (!empty($can_edit_earned_leave)) { ?>
                                <a href="<?php echo admin_url('staff/manage_earned_leave'); ?>" class="btn btn-default btn-sm">
                                  <i class="fa fa-sliders"></i> Manage Earned Leave (bulk)
                                </a>
                              <?php } ?>
                            </div>
                            <?php if (!empty($can_edit_earned_leave)) { ?>
                              <p class="text-muted" style="font-size:12px;margin-bottom:10px;">
                                HR can edit <strong>Leaves Earned</strong> per row below, or use bulk manage for department-wise updates.
                                Select a single month (not All Months) for easier editing.
                              </p>
                            <?php } ?>
                        </div>

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
                                        <td><?php echo cal_days_in_month(CAL_GREGORIAN, $month_number, $selectedYear) ?></td>
                                        <td><?php echo $value[$i]['carry_forward'] ?></td>
								
									
									<td><?php echo $value[$i]['leave_taken']; ?></td>
								
                                        <td>
                                          <?php if (!empty($can_edit_earned_leave)) { ?>
                                            <input type="number" step="0.01" min="0" class="form-control el-earned-input"
                                              value="<?php echo html_escape($value[$i]['earned_leave']); ?>"
                                              data-staff-id="<?php echo (int) $value[$i]['staffid']; ?>"
                                              data-month="<?php echo (int) $month_number; ?>"
                                              data-year="<?php echo (int) $selectedYear; ?>">
                                            <button type="button" class="btn btn-default btn-xs el-earned-save" title="Save">Save</button>
                                            <?php if (!empty($value[$i]['earned_leave_is_override'])) { ?>
                                              <span class="el-earned-override">Manual</span>
                                            <?php } ?>
                                          <?php } else { ?>
                                            <?php echo $value[$i]['earned_leave']; ?>
                                          <?php } ?>
                                        </td>
										<?php // ?>
                                        <td class="el-balance-cell"
                                          data-carry="<?php echo (float) $value[$i]['carry_forward']; ?>"
                                          data-taken="<?php echo (float) $value[$i]['leave_taken']; ?>">
                                          <?php echo $value[$i]['leave_balance']; ?>
                                        </td>
										
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
    $('#leave_balance_staff_pick').on('change', function() {
      var staffId = $(this).val();
      var params = new URLSearchParams(window.location.search);
      params.set('staff_id', staffId);
      var range = $('#range').val();
      var year = $('#year').val();
      if (range) { params.set('range', range); }
      if (year) { params.set('year', year); }
      window.location.href = admin_url + 'staff/leave_balance?' + params.toString();
    });

    $(document).on('click', '.el-earned-save', function() {
      var $btn = $(this);
      var $input = $btn.siblings('.el-earned-input');
      var $cell = $btn.closest('tr').find('.el-balance-cell');
      var payload = {
        staffids: [$input.data('staff-id')],
        month: $input.data('month'),
        year: $input.data('year'),
        earned_days: $input.val()
      };
      if (typeof csrfData !== 'undefined') {
        payload[csrfData.token_name] = csrfData.hash;
      }
      $btn.prop('disabled', true);
      $.post(admin_url + 'staff/save_earned_leave_bulk', payload).done(function(res) {
        try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { res = {}; }
        if (res.success) {
          alert_float('success', res.message || 'Saved');
          var cf = parseFloat($cell.data('carry')) || 0;
          var taken = parseFloat($cell.data('taken')) || 0;
          var earned = parseFloat($input.val()) || 0;
          var bal = Math.round((cf + earned - taken) * 100) / 100;
          $cell.text(bal);
          $btn.siblings('.el-earned-override').remove();
          $input.after('<span class="el-earned-override">Manual</span>');
          setTimeout(function() { location.reload(); }, 800);
        } else {
          alert_float('danger', res.message || 'Could not save');
        }
      }).fail(function() {
        alert_float('danger', 'Could not save earned leave');
      }).always(function() {
        $btn.prop('disabled', false);
      });
    });

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

        function clearLeaveBalanceLoader() {
            timesheetsTable.removeClass('dt-table-loading table-loading');
            timesheetsTable.parents('.table-loading').removeClass('table-loading');
            $('.leave_balance .table-loading').removeClass('table-loading');
            $('.leave_balance .dataTables_processing').hide();
        }

        if ($.fn.DataTable.isDataTable(timesheetsTable)) {
            timesheetsTable.DataTable().destroy();
        }

        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            deferRender: true,
            pageLength: 25,
            buttons: [
                'copy',
                'csv',
                {
                    extend: 'excel',
                    title: 'Leave Balance'
                },
                'pdf',
                'print'
            ],
            initComplete: function () {
                clearLeaveBalanceLoader();
            },
            drawCallback: function () {
                clearLeaveBalanceLoader();
            }
        });

        // Perfex may re-add table-loading after init — clear it.
        setTimeout(clearLeaveBalanceLoader, 50);
        setTimeout(clearLeaveBalanceLoader, 500);

        $(document).on('click', '.leave-card-view-details', function(e) {
            e.preventDefault();
            var slug = $(this).data('slug');
            var label = $(this).data('label') || slug;
            var staffId = $('#leave_balance_cards_wrap').data('staff-id');
            var year = $('#leave_balance_cards_wrap').data('year') || new Date().getFullYear();
            var month = parseInt($('#leave_balance_cards_wrap').data('month'), 10) || 0;
            var title = label + ' — ' + year;
            if (month > 1) {
                var monthNamesShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                title = label + ' — Jan – ' + monthNamesShort[month - 1] + ' ' + year;
            } else if (month === 1) {
                title = label + ' — January ' + year;
            }
            $('#leave_type_details_title').text(title);
            $('#leave_type_details_body').html('<p class="text-muted">Loading...</p>');
            $('#leave_type_details_modal').modal('show');
            var params = { year: year };
            if (month > 0) { params.month = month; }
            $.get(admin_url + 'timesheets/leave_type_details/' + staffId + '/' + encodeURIComponent(slug), params).done(function(html) {
                $('#leave_type_details_body').html(html);
            }).fail(function() {
                $('#leave_type_details_body').html('<p class="text-danger">Could not load leave details.</p>');
            });
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