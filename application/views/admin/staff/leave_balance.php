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
    
    /* Filter toolbar: month + year + Apply aligned */
    .leave_balance .lb-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .leave_balance .lb-filters form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin: 0;
        width: 100%;
    }
    .leave_balance .lb-filters .bootstrap-select {
        float: none !important;
        padding: 0 !important;
        width: auto !important;
        max-width: 100%;
    }
    .leave_balance .lb-filters .bootstrap-select > .dropdown-toggle {
        min-width: 140px;
        border-radius: 10px !important;
        height: 38px;
        padding-right: 36px !important;
        position: relative;
    }
    /* Hide broken double caret on leave balance filters */
    .leave_balance .lb-filters .bootstrap-select .caret,
    .leave_balance .lb-filters .bootstrap-select .bs-caret {
        display: none !important;
        border: 0 !important;
    }
    .leave_balance .lb-filters .bootstrap-select > .dropdown-toggle::after {
        content: "";
        display: block;
        position: absolute;
        top: 50%;
        right: 14px;
        width: 7px;
        height: 7px;
        margin-top: -5px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: rotate(45deg);
        pointer-events: none;
    }
    .leave_balance .lb-filters .bootstrap-select.open > .dropdown-toggle::after {
        margin-top: -1px;
        transform: rotate(225deg);
    }
    .leave_balance .lb-filters select#year + .bootstrap-select > .dropdown-toggle,
    .leave_balance .lb-filters .bootstrap-select:nth-of-type(2) > .dropdown-toggle {
        min-width: 110px;
    }
    .leave_balance #applybtn {
        float: none !important;
        margin: 0 !important;
        padding: 8px 22px;
        height: 38px;
        line-height: 1.2;
        white-space: nowrap;
        flex: 0 0 auto;
    }
    .leave_balance .lb-staff-pick {
        max-width: 420px;
        width: 100%;
    }
    .leave_balance .lb-report-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .leave_balance .lb-report-head h5 {
        margin: 0;
        font-weight: 700;
        color: #111;
        flex: 1 1 auto;
        min-width: 180px;
    }
    .leave_balance .lb-report-head .btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .leave-summary-section { margin-bottom: 18px; }
    .leave-summary-section h5 { margin: 0 0 12px; font-weight: 700; color: #111; }
    .leave-summary-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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
    .leave-balance-table-section h5 { margin: 0; font-weight: 700; color: #111; }
    .leave_balance .lb-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .leave_balance .lb-table-wrap .table {
        min-width: 780px;
        margin-bottom: 0;
    }
    .el-earned-cell {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }
    .el-earned-input {
        width: 72px;
        display: inline-block;
        height: 28px;
        padding: 2px 6px;
        font-size: 12px;
    }
    .el-earned-save {
        padding: 2px 8px;
        font-size: 11px;
        margin-left: 0;
    }
    .el-earned-override { color: #2563eb; font-size: 10px; display: block; width: 100%; }

    @media (max-width: 991px) {
        .leave-summary-cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .leave_balance .lb-filters form > .bootstrap-select {
            flex: 1 1 140px;
        }
        .leave_balance .lb-filters .bootstrap-select,
        .leave_balance .lb-filters .bootstrap-select > .dropdown-toggle {
            width: 100% !important;
            min-width: 0 !important;
        }
    }
    @media (max-width: 575px) {
        .leave-summary-cards {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .leave-summary-card { min-height: 76px; padding: 10px 8px; }
        .leave-summary-card .val { font-size: 22px; }
        .leave_balance .lb-filters form {
            flex-direction: column;
            align-items: stretch;
        }
        .leave_balance .lb-filters .bootstrap-select,
        .leave_balance .lb-filters .bootstrap-select > .dropdown-toggle {
            width: 100% !important;
        }
        .leave_balance #applybtn {
            width: 100%;
            height: 40px;
        }
        .leave_balance .lb-report-head {
            flex-direction: column;
            align-items: stretch;
        }
        .leave_balance .lb-report-head .btn {
            width: 100%;
            text-align: center;
        }
        .leave_balance .lb-staff-pick {
            max-width: 100%;
        }
    }
</style>

<div id="wrapper" class="leave_balance">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="lb-filters">
                            <?= form_open('admin/staff/leave_balance') ?>
                            <input type="hidden" name="staff_id" id="leave_balance_staff_id_hidden" value="<?php echo !empty($filter_one_staff) ? (int) $userid : ''; ?>">
                            <input type="hidden" name="all" id="leave_balance_all_hidden" value="<?php echo !empty($want_all_report) ? '1' : ''; ?>">
                            <select name="range" id="range" class="selectpicker" data-width="fit">
                                <?php
                                $monthData = [
                                    "All Months", "January", "February", "March", "April", "May", "June", "July",
                                    "August", "September", "October", "November", "December"
                                ];
                                foreach ($monthData as $key => $value) {
                                    $isSelected = ((int) $currentMonth === (int) $key) ? ' selected' : '';
                                    echo '<option value="' . $key . '"' . $isSelected . '>' . $value . '</option>';
                                }
                                ?>
                            </select>
                            <select name="year" id="year" class="selectpicker" data-width="fit">
                                <?php
                                for ($year = $currentYear; $year >= ($currentYear - 10); $year--) {
                                    $isSelected = ($year == $selectedYear) ? ' selected' : '';
                                    echo '<option value="' . $year . '"' . $isSelected . '>' . $year . '</option>';
                                }
                                ?>
                            </select>
                            <button type="submit" id="applybtn" class="btn btn-primary">Apply</button>
                            </form>
                        </div>
                        <div class="row mtop15 hide">
                            <div class="col-md-12 period hide">
                                <?php echo render_date_input('period-from'); ?>
                            </div>
                            <div class="col-md-12 period hide">
                                <?php echo render_date_input('period-to'); ?>
                            </div>
                        </div>
                        <hr class="no-mtop"/>

                        <?php if (!empty($can_pick_staff)) { ?>
                        <div class="mbot15 lb-staff-pick">
                            <label class="control-label">View leave balance for</label>
                            <select id="leave_balance_staff_pick" class="form-control">
                                <?php if (!empty($is_hr_viewer)) { ?>
                                <option value="" <?php echo !empty($want_all_report) ? 'selected' : ''; ?>>All employees (full report — slower)</option>
                                <?php } ?>
                                <?php
                                $pick_list = !empty($staff_list) ? $staff_list : [];
                                $have_me = false;
                                foreach ($pick_list as $s) {
                                    if ((int) $s['staffid'] === (int) get_staff_user_id()) {
                                        $have_me = true;
                                        break;
                                    }
                                }
                                if (!$have_me) {
                                    array_unshift($pick_list, [
                                        'staffid' => (int) get_staff_user_id(),
                                        'firstname' => get_staff_full_name(get_staff_user_id()),
                                        'lastname' => '',
                                    ]);
                                }
                                foreach ($pick_list as $s) {
                                    $sid = (int) $s['staffid'];
                                    $is_me = ($sid === (int) get_staff_user_id());
                                    $is_sel = empty($want_all_report) && ((int) $userid === $sid);
                                    $label = trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''));
                                    if ($label === '') {
                                        $label = get_staff_full_name($sid);
                                    }
                                ?>
                                    <option value="<?php echo $sid; ?>" <?php echo $is_sel ? 'selected' : ''; ?>>
                                        <?php echo html_escape($label); ?><?php echo $is_me ? ' (Me)' : ''; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php if (!empty($is_team_manager) && empty($is_hr_viewer)) { ?>
                                <p class="text-muted" style="font-size:12px;margin-top:6px;">Managers see their own balance and direct team only.</p>
                            <?php } ?>
                            <?php if (!empty($want_all_report)) { ?>
                                <p class="text-warning" style="font-size:12px;margin-top:6px;">Full company report recalculates leave for every employee — this can take a few seconds.</p>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <?php if (!empty($summary)) { ?>
                        <div class="leave-summary-section">
                            <h5>My leave summary — <?php echo html_escape($summary['month_name']); ?> <?php echo (int) $selectedYear; ?></h5>
                            <?php if ((int) ($summary['month'] ?? -1) === 0) { ?>
                            <p class="text-muted" style="font-size:12px;margin:-4px 0 12px;">
                              Carry Forward and Leave Balance below are your <strong>current</strong> figures.
                              Leave applied for any month (including a previous month) adjusts this same running balance.
                            </p>
                            <?php } ?>
                            <div class="leave-summary-cards">
                                <div class="leave-summary-card">
                                    <div class="val"><?php echo html_escape($summary['carry_forward']); ?></div>
                                    <div class="lbl">Carry Forward</div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val"><?php echo html_escape($summary['leave_taken']); ?></div>
                                    <div class="lbl"><?php echo ((int) ($summary['month'] ?? -1) === 0) ? 'Leaves Taken (YTD)' : 'Leaves Taken'; ?></div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val success"><?php echo html_escape($summary['earned_leave']); ?></div>
                                    <div class="lbl"><?php echo ((int) ($summary['month'] ?? -1) === 0) ? 'Leaves Earned (YTD)' : 'Leaves Earned'; ?></div>
                                </div>
                                <div class="leave-summary-card">
                                    <div class="val success"><?php echo html_escape($summary['leave_balance']); ?></div>
                                    <div class="lbl">Leave Balance</div>
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
                            <div class="lb-report-head">
                              <h5><?php
                                if (!empty($filter_one_staff)) {
                                    echo 'Leave balance report — ' . html_escape(get_staff_full_name($userid));
                                } elseif (!empty($is_team_manager) || !empty($is_hr_viewer)) {
                                    echo 'Team leave balance report';
                                } else {
                                    echo 'Leave balance report';
                                }
                              ?></h5>
                              <?php if (!empty($can_edit_earned_leave)) { ?>
                                <a href="<?php echo admin_url('staff/manage_earned_leave'); ?>" class="btn btn-default btn-sm">
                                  <i class="fa fa-sliders"></i> Manage Earned Leave (bulk)
                                </a>
                              <?php } ?>
                            </div>
                            <p class="text-muted" style="font-size:12px;margin-bottom:10px;">
                              <?php if (!empty($filter_one_staff)) { ?>
                                Showing <strong>1 employee</strong> for the selected month/year. Choose <strong>All employees (full report)</strong> above to see the full team table.
                              <?php } else { ?>
                                Showing <strong><?php echo (int) ($report_row_count ?? 0); ?></strong> row(s) for the selected month/year.
                              <?php } ?>
                              <?php if (!empty($can_edit_earned_leave)) { ?>
                                HR can edit <strong>Leaves Earned</strong> per row below, or use bulk manage for department-wise updates.
                              <?php } ?>
                            </p>
                        </div>

                        <div class="lb-table-wrap">
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


                            </tr>
                            </thead>
                            <tbody>
							
                            <?php
                            $lb_rows_rendered = 0;
                            foreach ($table_data as $month_number => $value) :
                                if (!is_array($value)) {
                                    continue;
                                }
                                for ($i = 0; $i < count($value); $i++) {
                                    $lb_rows_rendered++;
                                    ?>
                                    <tr>
                                        <td><?php echo($value[$i]['empid'] ?? $value[$i]['staffid']) ?></td>
                                        <td><?php
                                            $nm = trim(($value[$i]['firstname'] ?? '') . ' ' . ($value[$i]['lastname'] ?? ''));
                                            echo $nm !== '' ? html_escape($nm) : get_staff_full_name($value[$i]['staffid']);
                                        ?></td>
                                        <td><?php echo (int) $month_number; ?></td>
                                        <td><?php echo cal_days_in_month(CAL_GREGORIAN, (int) $month_number, (int) $selectedYear) ?></td>
                                        <td><?php echo $value[$i]['carry_forward'] ?></td>
                                        <td><?php echo $value[$i]['leave_taken']; ?></td>
                                        <td>
                                          <?php if (!empty($can_edit_earned_leave)) { ?>
                                            <div class="el-earned-cell">
                                            <input type="number" step="0.01" min="0" class="form-control el-earned-input"
                                              value="<?php echo html_escape($value[$i]['earned_leave']); ?>"
                                              data-staff-id="<?php echo (int) $value[$i]['staffid']; ?>"
                                              data-month="<?php echo (int) $month_number; ?>"
                                              data-year="<?php echo (int) $selectedYear; ?>">
                                            <button type="button" class="btn btn-default btn-xs el-earned-save" title="Save">Save</button>
                                            <?php if (!empty($value[$i]['earned_leave_is_override'])) { ?>
                                              <span class="el-earned-override">Manual</span>
                                            <?php } ?>
                                            </div>
                                          <?php } else { ?>
                                            <?php echo $value[$i]['earned_leave']; ?>
                                          <?php } ?>
                                        </td>
                                        <td class="el-balance-cell"
                                          data-carry="<?php echo (float) $value[$i]['carry_forward']; ?>"
                                          data-taken="<?php echo (float) $value[$i]['leave_taken']; ?>">
                                          <?php echo $value[$i]['leave_balance']; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            endforeach;
                            if ($lb_rows_rendered === 0) { ?>
                              <tr>
                                <td colspan="8" class="text-center text-muted">
                                  No leave balance rows for this selection. Pick a month and employee, then click Apply.
                                </td>
                              </tr>
                            <?php } ?>
                            </tbody>


                        </table>
                        </div>


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
      var staffId = $(this).val() || '';
      $('#leave_balance_staff_id_hidden').val(staffId);
      var params = new URLSearchParams();
      if (staffId) {
        params.set('staff_id', staffId);
      } else {
        params.set('all', '1');
      }
      var range = $('#range').val();
      var year = $('#year').val();
      if (range !== null && range !== undefined && range !== '') { params.set('range', range); }
      if (year) { params.set('year', year); }
      window.location.href = admin_url + 'staff/leave_balance?' + params.toString();
    });

    // Fill staff picker after paint (HR/managers).
    (function loadLeaveBalanceStaffPicker() {
      var $sel = $('#leave_balance_staff_pick');
      if (!$sel.length) return;
      var cur = String($sel.val() || '');
      var wantAll = <?php echo !empty($want_all_report) ? 'true' : 'false'; ?>;
      $.getJSON(admin_url + 'timesheets/get_viewable_staff_json').done(function(res) {
        var staff = res.staff || [];
        if (!staff.length) return;
        var meId = '<?php echo (int) get_staff_user_id(); ?>';
        var html = '';
        <?php if (!empty($is_hr_viewer)) { ?>
        html += '<option value="">All employees (full report — slower)</option>';
        <?php } ?>
        for (var i = 0; i < staff.length; i++) {
          var s = staff[i];
          var id = String(s.staffid);
          var name = $.trim((s.firstname || '') + ' ' + (s.lastname || ''));
          if (id === meId) name += ' (Me)';
          html += '<option value="' + id + '">' + $('<div>').text(name).html() + '</option>';
        }
        $sel.html(html);
        if (wantAll) {
          $sel.val('');
        } else if (cur && $sel.find('option[value="' + cur + '"]').length) {
          $sel.val(cur);
        } else {
          $sel.val(meId);
        }
      });
    })();

    $('form').has('#applybtn').on('submit', function() {
      if ($('#leave_balance_staff_pick').length) {
        var v = $('#leave_balance_staff_pick').val() || '';
        $('#leave_balance_staff_id_hidden').val(v);
        $('#leave_balance_all_hidden').val(v ? '' : '1');
      }
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