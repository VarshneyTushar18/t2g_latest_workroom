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
      <div class="col-md-12" id="small-table">
        <div class="panel_s">
          <div class="panel-body">
            <?php echo form_hidden('interview_id', $interview_id); ?>
            <div class="row">
              <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-calendar" aria-hidden="true"></i>
                  <?php echo _l($title); ?></h4>
                <hr />
              </div>
            </div>
            <?php if (has_permission('recruitment', '', 'create') || is_admin()) { ?>
              <a href="#" onclick="new_interview_schedule(); return false;"
                class="btn btn-info pull-left display-block"><?php echo 'Schedule Interview'; ?></a>
            <?php } ?>

            <?php if (has_permission('recruitment', '', 'view') || is_admin()) { ?>
              <a href="<?php echo admin_url('recruitment/calendar_interview_schedule'); ?>"
                class="btn btn-default pull-left display-block mleft5"><?php echo _l('calendar_view'); ?></a>
            <?php } ?>
            <button id="toggleFilter" class="btn btn-success pull-left mleft5">Filter</button>
            <div class="col-md-1 pull-right">
              <a href="#" class="btn btn-default pull-right btn-with-tooltip toggle-small-view hidden-xs"
                onclick="toggle_small_view_interview_schedules('.interview_sm','#interview_sm_view'); return false;"
                data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i
                  class="fa fa-angle-double-left"></i></a>
            </div>

            <div id="dynamicFilterContainer" style="display: inline;">
              <!-- The dynamic filter UI will be inserted here -->
              <!-- <button id="applyFiltersBtn">Apply Filters</button> -->
            </div>
            <br><br><br>
            <?php render_datatable(
              array(
                'Edit',
                _l('candidate'),
                _l('interviewer'),
                'Round Name',
                _l('rec_time'),
                _l('interview_day'),
                'Recruitment',
                _l('date_add'),
                'Recruiter Name',
                'Scheduled By',
              ),
              'table_interview',
              ['interview_sm' => 'interview_sm']
            ); ?>
          </div>
        </div>
      </div>
      <div class="col-md-7 small-table-right-col">
        <div id="interview_sm_view" class="hide">
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="interview_schedules_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open_multipart(admin_url('recruitment/interview_schedules'), array('id' => 'interview_schedule-form')); ?>
    <div class="modal-content width-135">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo 'Schedule Interview'; ?></span>
          <span class="edit-title"><?php echo _l('edit_interview_schedule'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- <div id="additional_interview"></div>
                    <div class="col-md-12">
                      <h5 class="bold"><?php echo _l('general_infor') ?></h5>
                      <hr class="margin-top-10"/>
                    </div> -->
          <div class="col-md-6">
            <div class="form-group">
              <label for="campaign"><?php echo _l('recruitment_campaign'); ?></label>
              <select name="campaign" id="campaign" class="selectpicker" data-live-search="true" data-width="100%"
                data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                <option value=""></option>
                <?php foreach ($rec_campaigns as $s) { ?>
                  <option value="<?php echo html_entity_decode($s['cp_id']); ?>" <?php if (isset($candidate) && $s['cp_id'] == $candidate->rec_campaign) {
                       echo 'selected';
                     } ?>><?php echo html_entity_decode($s['campaign_code'] . ' - ' . $s['campaign_name']); ?></option>
                <?php } ?>
              </select>
            </div>

          </div>
          <!-- <div class="col-md-4">
                      <?php echo render_input('is_name', 'interview_schedules_name') ?>

                    </div> -->
          <div class="col-md-6">

            <div class="form-group">
              <label for="candidate"><?php echo _l('candidate'); ?></label>
              <select name="candidate" onchange="candidate_infor_change(this); return false;" id="candidate"
                class="selectpicker" data-live-search="true" data-width="100%"
                data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" required>
                <option value=""></option>
                <?php foreach ($candidates as $s) { ?>
                  <option value="<?php echo html_entity_decode($s['id']); ?>">
                    <?php echo html_entity_decode($s['candidate_code'] . ' ' . $s['candidate_name'] . ' ' . $s['last_name']); ?>
                  </option>
                <?php } ?>
              </select>

            </div>

          </div>


          <div class="col-md-4"> <label for="position"><?php echo _l('position'); ?></label> </div>
          <div class="col-md-4"> <label for="email"><?php echo _l('email'); ?></label> </div>
          <div class="col-md-3"> <label for="phonenumber"><?php echo _l('phonenumber'); ?></label> </div>

          <div class="list_candidates">

            <div class="row col-md-12" id="candidates-item">
              <div class="col-md-4 form-group">
                <select disabled="true" id="position" class="selectpicker" data-live-search="true" data-width="100%"
                  data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                  <option value=""></option>
                  <?php foreach ($positions as $p) { ?>
                    <option value="<?php echo html_entity_decode($p['position_id']); ?>">
                      <?php echo html_entity_decode($p['position_name']); ?>
                    </option>
                  <?php } ?>

                </select>
                <input type="hidden" id="hiddenSelect" name="position" value="">
              </div>

              <div class="col-md-4">

                <input type="text" disabled="true" name="email" id="email" class="form-control" />
              </div>

              <div class="col-md-4 ">
                <input type="text" disabled="true" name="phonenumber" id="phonenumber" class="form-control" />
              </div>
              <!-- <div class="col-md-1 lightheight-34-nowrap">
                              <span class="input-group-btn pull-bot">
                                  <button name="add" class="btn new_candidates btn-success border-radius-4" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                              </span>
                            </div> -->

            </div>
          </div>




          <div class="col-md-12">
            <h4 class="bold"><?php echo 'Interview Rounds'; ?></h4>
            <hr class="margin-top-10" />
          </div>

          <div class="col-md-12">
            <div id="example"></div>
          </div>




          <div class="list_interviews">

            <div class="row col-md-12" id="interviews-item">
              <div class="col-md-10">
                <h4 id="interview_heading" class='interview_heading'>Round 1</h4>
              </div>
              <div class="col-md-2 lightheight-34-nowrap">
                <span class="input-group-btn pull-bot">
                  <button name="add" class="btn new_interviews btn-success border-radius-4" data-ticket="true"
                    type="button"><i class="fa fa-plus"></i></button>
                </span>
              </div>
              <div class="col-md-4">
                <?php //echo render_date_input('interview_day[0]', 'interview_day'); 
                ?>

                <div class="form-group" app-field-wrapper="interview_day[0]"><label for="interview_day[0]"
                    class="control-label">Interview date</label>
                  <div class="input-group date" style="width: 100%;"><input type="date" id="interview_day[0]"
                      name="interview_day[0]" class="form-control " value="" autocomplete="off"
                      style="border-radius: 5px;">
                    <!-- <div class="input-group-addon">
                      <i class="fa-regular fa-calendar calendar-icon"></i>
                    </div> -->
                  </div>
                </div>

              </div>
              <div class="col-md-4">
                <?php echo render_input('from_time[0]', 'from_time', '', 'time'); ?>

              </div>

              <div class="col-md-4">
                <?php echo render_input('to_time[0]', 'to_time', '', 'time'); ?>

              </div>

              <div class="col-md-12 form-group">
                <label for="interviewer"><span class="text-danger">* </span><?php echo _l('interviewer'); ?></label>
                <select name="interviewer[0][]" id="interviewer[0]" class="selectpicker" multiple="true"
                  data-live-search="true" data-width="100%"
                  data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" required>

                  <?php foreach ($staffs as $s) { ?>
                    <option value="<?php echo html_entity_decode($s['staffid']); ?>">
                      <?php echo html_entity_decode($s['firstname'] . ' ' . $s['lastname']); ?>
                    </option>
                  <?php } ?>
                </select>
                <br><br>
              </div>

              <div class="col-md-12 form-group">
                <!-- <label for="interview_link"><span class="text-danger">* </span><?php echo 'Interview Link'; ?></label> -->
                <?php echo render_textarea('interview_link[0]', 'Interview Link') ?>
                <br><br>
                <input type="hidden" id="round[0]" name="round[0]" value="1">
              </div>

            </div>

          </div>

        </div>
        <div class="modal-footer">
          <input type="hidden" name="edit" value="">
          <input type="hidden" id="id" name="id" value="">
          <button type="
          " class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button id="sm_btn" type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
        </div>
      </div><!-- /.modal-content -->
      <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <?php init_tail(); ?>


  <script>

    $(function () {
      customFilterSearch();
    });

    function hideShowFilter() {
      $('#dynamicFilterContainer').hide(); // Hide filter by default

      $('#toggleFilter').on('click', function () {
        $('#dynamicFilterContainer').toggle();
      });

    }

    function customFilterSearch() {
      hideShowFilter();

      var projectTable = $('#DataTables_Table_0').DataTable();
      // Generate search inputs for each column
      var filters = []; // Store filter values
      function applyFilters() {
        projectTable.columns().every(function (index) {
          if (typeof filters[index] !== 'undefined') {
            this.search(filters[index]);
          } else {
            this.search('');
          }
        });

        projectTable.draw(); // Redraw the table
      }

      function isDateColumn(title) {
        const dateKeywords = [/interview date/, /added date/]; // Add more keywords as needed
        title = title.toLowerCase();

        return dateKeywords.some(keyword => keyword.test(title));
      }



      projectTable.columns().every(function (index) {
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


        input.find('input').on('keyup change', function () {
          filters[index] = $(this).val(); // Store the filter value
          applyFilters(); // Apply filters and redraw the table

        });
      });
      // $('#applyFiltersBtn').on('click', function() {
      //     applyFilters(); // Apply filters and redraw the table
      // });

    }






    function isDate(value) {
      var dateFormat = /^\d{4}-\d{2}-\d{2}$/;
      return dateFormat.test(value);
    }
  </script>
  </body>

  </html>