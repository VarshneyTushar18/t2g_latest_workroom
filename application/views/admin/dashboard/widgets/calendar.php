<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget dashboard-calendar-widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('calendar'); ?>">
  <div class="clearfix"></div>
  <div class="panel_s">
    <div class="panel-body">
      <div class="widget-dragger"></div>
      <div class="dt-loader hide"></div>
      <?php if (is_staff_member()) { ?>
        <div class="dashboard-cal-birthday-row">
          <div class="dashboard-cal-col">
        <div class="dashboard-hrms-panel">
          <div class="dashboard-hrms-panel-head">
            <span><i class="fa fa-calendar"></i> Attendance calendar</span>
            <a href="<?php echo admin_url('utilities/calendar'); ?>"><?php echo _l('calendar'); ?> (events)</a>
          </div>
          <div class="dashboard-hrms-cal-wrap dashboard-hrms-cal">
            <div class="hrms-cal-nav">
              <button type="button" id="dashboardHrmsCalPrev" aria-label="Previous month"><i class="fa fa-angle-left"></i></button>
              <div class="hrms-cal-month" id="dashboardHrmsCalMonthLabel">—</div>
              <button type="button" id="dashboardHrmsCalNext" aria-label="Next month"><i class="fa fa-angle-right"></i></button>
            </div>
            <div class="hrms-cal-weekdays">
              <div class="hrms-cal-dow">Su</div>
              <div class="hrms-cal-dow">Mo</div>
              <div class="hrms-cal-dow">Tu</div>
              <div class="hrms-cal-dow">We</div>
              <div class="hrms-cal-dow">Th</div>
              <div class="hrms-cal-dow">Fr</div>
              <div class="hrms-cal-dow">Sa</div>
            </div>
            <div class="hrms-cal-grid" id="dashboardHrmsCalGrid">
              <div style="grid-column:1/-1;padding:14px;text-align:center;color:#64748b;font-size:12px;">
                <i class="fa fa-spinner fa-spin"></i> Loading…
              </div>
            </div>
            <div class="hrms-cal-legend">
              <span><i class="lg-ok"></i> Present</span>
              <span><i class="lg-half"></i> Half</span>
              <span><i class="lg-bad"></i> Absent</span>
              <span><i class="lg-leave"></i> Leave</span>
              <span><i class="lg-holiday"></i> Holiday</span>
              <span><i class="lg-off"></i> Off</span>
            </div>
            <div class="dashboard-hrms-cal-footer">
              <a href="<?php echo admin_url('timesheets/my_attendance'); ?>">Open full attendance →</a>
            </div>
          </div>
        </div>
          </div>
          <div class="dashboard-birthday-col" id="celebrantWidget">
            <?php $this->load->view('admin/dashboard/widgets/birthday_spotlight'); ?>
          </div>
        </div>
      <?php } else { ?>
        <div class="dashboard-fc-compact">
          <?php $this->load->view('admin/utilities/calendar_filters'); ?>
          <div id="calendar"></div>
        </div>
      <?php } ?>
    </div>
  </div>
  <div class="clearfix"></div>
</div>
