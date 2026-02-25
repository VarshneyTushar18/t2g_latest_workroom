<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" id="fullcalendar-css" href="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.css?v=3.0.4">
<script type="text/javascript" id="fullcalendar-js" src="<?= base_url(); ?>/assets/plugins/fullcalendar/lib/main.min.js?v=3.0.4"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
  .calendar-header {
    font-size: 18px;
    margin-bottom: 10px;
  }
</style>


<?php init_head(); ?>

<style>
  .right-elements {
    display: block;
  }

  .pagination button {
    margin: 0 5px;
    text-decoration: none;
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .pagination button.active {
    background-color: #141e46;
    color: white;
    border-color: #007bff;
  }

  .pagination-container {
    clear: both;
    /* Ensure it starts on a new line */
    text-align: center;
    /* Center the pagination if needed */
    margin-top: 10px;
  }
</style>

<div id="wrapper">

  <div class="content">

    <div class="row">

      <div class="col-md-12">

        <div class="panel_s">

          <div class="panel-body">

            <h4><?php echo 'Employee Attendance'; ?>

              <hr>

            </h4>


            <?php

            if (has_permission('attendance_management', '', 'view') || is_admin() || (get_staff_user_id() == 23) || attendance_permission() || is_HR()) {

            ?>

              <?php echo form_open(); ?>
              <div class="row filter_by">

                <div class="col-md-2 leads-filter-column">

                  <?php echo render_input('month_year', '', isset($selectedMonth) ? $selectedMonth: date('Y-m'), 'month'); ?>


                </div>


                <?php if (is_admin()||(get_staff_user_id() == 23)): ?>
                  <div class="col-md-2 leads-filter-column">
                    <?php echo render_select('reporting_person', $staffs_in_select_option, array('staffid', 'full_name'), '', $staffs_under_manager) ?>
                  </div>
                <?php endif ?>



                <!-- <div class="col-md-3 leads-filter-column">

                  <?php // echo render_select('job_position_timesheets', $roles, array('roleid', 'name'), 'role'); 
                  ?>

                </div> -->


                <div class="col-md-1 ">

                  <button type="submit" class="btn btn-info timesheets_filter"><?php echo 'Go'; ?></button>

                </div>

              </div>

              <?php echo form_close(); ?>

            <?php } ?>


            <hr class="hr-panel-heading no-margin" />

            <div class="row mtop15">

              <div class="col-md-8 line-suggestion">

                <!-- <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('p_x_timekeeping'); 
                                                                                                            ?>" class="btn" >AL</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('W_x_timekeeping'); 
                                                                                              ?>" class="btn" >W</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('A_x_timekeeping'); 
                                                                                              ?>" class="btn" >U</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('Le_x_timekeeping'); 
                                                                                              ?>" class="btn" >HO</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('E_x_timekeeping'); 
                                                                                              ?>" class="btn" >E</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('L_x_timekeeping'); 
                                                                                              ?>" class="btn" >L</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('CT_x_timekeeping'); 
                                                                                              ?>" class="btn" >B</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('OM_x_timekeeping'); 
                                                                                              ?>" class="btn" >SI</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('TS_x_timekeeping'); 
                                                                                              ?>" class="btn" >M</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('H_x_timekeeping'); 
                                                                                              ?>" class="btn" >ME</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('NS_x_timekeeping'); 
                                                                                              ?>" class="btn" >NS</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('EB_x_timekeeping'); 
                                                                                              ?>" class="btn" >EB</button>

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('UB_x_timekeeping'); 
                                                                                              ?>" class="btn" >UB</button> 

        <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<?php //echo _l('P_timekeeping'); 
                                                                                              ?>" class="btn" >P</button> -->

                <button type="button" data-toggle="tooltip" data-placement="top" title="Present" class="btn" style="background:#2ecc71; color:white">P</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Absent" class="btn" style="background:#e74c3c; color:white">AB</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Unplanned Half Leave" class="btn" style="background:#9900FE; color:white">UHL</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Planned Half Leave" class="btn" style="background:#ff9900; color:white">PHL</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Sick Leave" class="btn" style="background:#bf9000; color:white">SL</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Planned Leave" class="btn" style="background:#a64d79; color:white">PL</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Holiday" class="btn" style="background:#3d85c6; color:white">HO</button>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Unplanned Leave" class="btn" style="background:#666666; color:white">UL</button>

                <!-- <button type="button" data-toggle="tooltip" data-placement="top" data-original-title="<? //php echo _l('NS_x_timekeeping'); 
                                                                                                            ?>" class="btn" >NS:No Shift</button> -->


                <div class="clearfix"></div>

              </div>




              <div class="col-md-12">
                <!-- calendar code html -->


                <hr />

                <?php
                // $calendarsPerPage = 6;
                // $totalStaff = count($staffs); // Assume $staffList is an array of staff data
                // $totalPages = ceil($totalStaff / $calendarsPerPage);
                // $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                // $startIndex = ($currentPage - 1) * $calendarsPerPage;

                // $staffToDisplay = array_slice($staffs, $startIndex, $calendarsPerPage);
                ?>

                <?php foreach ($staffToDisplay as $staff): ?>

                  <div class="col-md-4">
                    <div class="panel_s">
                      <div class="panel-body">
                        <div class="dt-loader hide"></div>

                        <div class="calendar-header"><?= $staff['full_name'] ?> - <?= $staff['staff_identifi'] ?></div>
                        <div id="calendar-<?= $staff['staffid'] ?>"></div>


                      </div>
                    </div>
                  </div>

                  <script>
                    document.addEventListener('DOMContentLoaded', function() {
                      var calendarEl = document.getElementById('calendar-<?= $staff['staffid'] ?>');

                      var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        initialDate: '<?= $month_year ?>-01',
                        headerToolbar: {
                          left: '', // No buttons on the left
                          center: '', // No title in the center
                          right: '' // No buttons on the right
                        },
                        events: [
                          <?php foreach ($staff['attendance'] as $attendance): ?> {
                              title: '<?= ucfirst($attendance['type']) ?>',
                              start: '<?= $attendance['date_work'] ?>',
                              color: '<?php echo ($attendance['type'] == 'P') ? '#2ecc71' : (($attendance['type'] == 'AB') ? '#e74c3c' : (($attendance['type'] == 'HD') ? '#ff9900' : (($attendance['type'] == 'HO') ? '#3d85c6' : 'grey'))); ?>',
                              extendedProps: {
                                total_time: '<?= $attendance['value'] ?>',
                                check_in_time: '<?= $attendance['check_in_time'] ?>',
                                check_out_time: '<?= $attendance['check_out_time'] ?>'

                              }
                            },
                          <?php endforeach; ?>
                        ],
                        eventDidMount: function(info) {
                          var tooltipContent = 'Total Time: ' + info.event.extendedProps.total_time + '<br> Check in: ' + info.event.extendedProps.check_in_time + '<br> Check out: ' + info.event.extendedProps.check_out_time;
                          var eventElement = info.el;

                          eventElement.setAttribute('data-bs-toggle', 'tooltip');
                          eventElement.setAttribute('title', tooltipContent);

                          new bootstrap.Tooltip(eventElement, {
                            html: true
                          });
                        }
                      });

                      calendar.render();
                    });
                  </script>
                <?php endforeach; ?>


                <div class="pagination-container">

                  <?php echo form_open(); ?>
                  <input type="hidden" name="month_year" value="<?php echo $selectedMonth; ?>">
                  <input type="hidden" name="reporting_person" value="<?php echo $staffs_under_manager; ?>">

                  <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                      <button type="submit" name="page" value="<?php echo $currentPage - 1; ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Prev</button>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <button type="submit" name="page" value="<?php echo $i; ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>>
                        <?php echo $i; ?>
                      </button>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                      <button type="submit" name="page" value="<?php echo $currentPage + 1; ?>">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                    <?php endif; ?>
                  </div>
                  </form>


                  <input type="hidden" name="userid" value="<?php echo get_staff_user_id() ?>">

                </div>
                <!-- calendar code end  -->




                <hr class="hr-panel-heading" />


              </div>

            </div>



          </div>

        </div>

      </div>

      <div class="clearfix"></div>

    </div>

  </div>

</div>




<?php init_tail(); ?>

</body>

</html>