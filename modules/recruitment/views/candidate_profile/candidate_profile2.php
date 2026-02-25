<?php init_head(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12" id="small-table">
        <div class="panel_s">
          <div class="panel-body">
            <!-- <div class="row">
              <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-user-o" aria-hidden="true"></i>
                  <?php echo _l($title); ?></h4>
                <hr />
              </div>
            </div> -->
            <div class="row">
              <div class="row mbot15">
                <div class="col-md-12">
                  <h4><span>Candidate Summary</span></h4>
                </div>

                <div
                  class="col-md-2 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">

                  <a href="#" class="tw-text-neutral-600 hover:tw-opacity-70 tw-inline-flex tw-items-center">
                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                      <?= $total_candidates ?>
                    </span>
                    <span>
                      Total Added
                    </span>
                  </a>
                </div>
                <div
                  class="col-md-2 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">

                  <a href="#" class="tw-text-neutral-600 hover:tw-opacity-70 tw-inline-flex tw-items-center">
                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                      <?= $yesterday_candidates ?>
                    </span>
                    <span style="color:#764abc">
                      Yesterday Added
                    </span>
                  </a>
                </div>
                <div
                  class="col-md-2 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">

                  <a href="#" class="tw-text-neutral-600 hover:tw-opacity-70 tw-inline-flex tw-items-center">
                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                      <?= $todays_candidates ?>
                    </span>
                    <span style="color:#ffb822">Today Added</span>
                  </a>
                </div>
                <div
                  class="col-md-2 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">

                  <a href="#" class="tw-text-neutral-600 hover:tw-opacity-70 tw-inline-flex tw-items-center">
                    <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                      <?= $weekly_candidates ?>
                    </span>
                    <span style="color:rgb(250, 30, 30)">
                      Weekly Added
                    </span>
                  </a>
                </div>
              </div>
              <hr class="hr-panel-separator" />

            </div>
            <div class="row">

              <div class="col-md-12">
                <!-- <a href="<?php echo admin_url('recruitment/candidates'); ?>"
                  class="btn btn-info pull-left display-block"><?php echo _l('new_candidate'); ?></a> -->
                  <a href="<?php echo admin_url('recruitment/candidatesaddmore'); ?>"
                  class="btn btn-info pull-left display-block"><?php echo _l('new_candidate'); ?></a>
                <a href="#" onclick="send_mail_candidate(); return false;"
                  class="btn btn-success pull-left display-block mleft5"><i
                    class="fa fa-envelope"></i><?php echo ' ' . _l('send_mail'); ?></a>

                <a href="<?php if (!$this->input->get('project_id')) {
                            echo admin_url('recruitment/switch_kanban/' . $switch_kanban);
                          } else {
                            echo admin_url('projects/view/' . $this->input->get('project_id') . '?group=project_tasks');
                          }; ?>" class="btn btn-default mleft10 pull-left hidden-xs">
                  <?php if ($switch_kanban == 1) {
                    echo _l('switch_to_list_view');
                  } else {
                    echo _l('leads_switch_to_kanban');
                  }; ?>
                </a>
                <a href="#" id="import_data_btn" class="btn btn-info pull-left display-block mleft5"><?php echo _l('Import Data'); ?></a>
                <!-- <a href="<?php echo admin_url('recruitment/candidatesaddmore'); ?>" class="btn btn-info pull-left display-block mleft5"><?php echo _l('Add Multiple Candidates'); ?></a> -->
              </div>
            </div>
            <br>


            <?php
            if ($this->session->has_userdata('candidate_profile_kanban_view') && $this->session->userdata('candidate_profile_kanban_view') == 'true') { ?>

              <hr class="hr-panel-heading hr-10" />
              <div class="clearfix"></div>
              <div class="kan-ban-tab kan-ban-overflow" id="kan-ban-tab">
                <div class="row">
                  <div id="kanban-params">
                    <?php echo form_hidden('project_id', $this->input->get('project_id')); ?>
                  </div>
                  <div class="container-fluid">
                    <div id="kan-ban"></div>
                  </div>
                </div>
              </div>
            <?php } else { ?>

              <div class="row">

                <div class="col-lg-3">
                  <input type="text" class="form-control" placeholder="Search" id="searchValue">
                </div>

                <div class="col-lg-3 pull-right">
                  <select name="rec_campaign2[]" id="rec_campaign" class="selectpicker" data-live-search="true"
                    multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('recruitment_campaign'); ?>">

                    <?php foreach ($rec_campaigns as $s) { ?>
                      <option value="<?php echo html_entity_decode($s['cp_id']); ?>" <?php if (isset($candidate) && $s['cp_id'] == $candidate->rec_campaign) {
                                                                                        echo 'selected';
                                                                                      } ?>>
                        <?php echo html_entity_decode($s['campaign_code'] . ' - ' . $s['campaign_name']); ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-lg-3 pull-right">
                  <select name="change_status2[]" id="change_status" class="selectpicker" data-live-search="true"
                    multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('change_status_to'); ?>">
                    <option value="1"><?php echo _l('application'); ?></option>
                    <option value="2"><?php echo _l('potential'); ?></option>
                    <option value="3"><?php echo _l('interview'); ?></option>
                    <option value="4"><?php echo _l('won_interview'); ?></option>
                    <option value="5"><?php echo _l('send_offer'); ?></option>
                    <option value="6"><?php echo _l('elect'); ?></option>
                    <option value="7"><?php echo _l('non_elect'); ?></option>
                    <option value="8"><?php echo _l('unanswer'); ?></option>
                    <option value="9"><?php echo _l('transferred'); ?></option>
                    <option value="10"><?php echo _l('freedom'); ?></option>
                  </select>
                </div>

              </div>

              <br>
              <!-- print barcode -->
              <?php echo form_open_multipart(admin_url('recruitment/item_print_candidate'), array('id' => 'item_print_candidate')); ?>
              <div class="modal bulk_actions" id="table_commodity_list_print_candidate" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                          aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title"><?php echo _l('print_candidate'); ?></h4>
                    </div>
                    <div class="modal-body">
                      <?php if (has_permission('recruitment', '', 'create') || is_admin()) { ?>

                        <div class="row">
                          <div class=" col-md-12">
                            <div class="form-group">
                              <select name="item_select_print_candidate[]" id="item_select_print_candidate"
                                class="selectpicker" data-live-search="true" multiple="true" data-actions-box="true"
                                data-width="100%" data-none-selected-text="<?php echo _l('select_candidate'); ?>">

                                <?php foreach ($candidates as $candidate) { ?>
                                  <option value="<?php echo html_entity_decode($candidate['id']); ?>">
                                    <?php echo html_entity_decode($candidate['candidate_code'] . '-' . $candidate['candidate_name'] . ' ' . $candidate['last_name']); ?>
                                  </option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                        </div>

                      <?php } ?>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>

                      <?php if (has_permission('recruitment', '', 'create') || is_admin()) { ?>

                        <button type="submit" class="btn btn-info"><?php echo _l('confirm'); ?></button>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php echo form_close(); ?>

              <a href="#" onclick="print_candidate_bulk_actions(); return false;" data-toggle="modal"
                data-table=".table-table_rec_candidate" data-target="#print_candidate_item"
                class=" hide print_candidate-bulk-actions-btn table-btn"><?php echo _l('print_candidate'); ?></a>
                
                <div class="table-responsive">
                  <table id="candidates-table" class="table table-striped table-bordered">
                      <thead>
                          <tr>
                              <th>Candidate Code</th>
                              <th>Candidate Name</th>
                              <th>Job Position</th>
                              <th>Status</th>
                              <th>Email</th>
                              <th>Phone Number</th>
                              <th>Joining Date</th>
                              <th>Onboarding Link</th>
                              <th>Copy Link</th>
							  <th>End Date</th>
                          </tr>
                      </thead>
                      <tbody>

                      </tbody>
                  </table>
                </div>
                <ul id="pagination" class="pagination"></ul>

            <?php } ?>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="mail_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open_multipart(admin_url('recruitment/send_mail_list_candidate'), array('id' => 'mail_candidate-form')); ?>
    <div class="modal-content width-100">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span><?php echo _l('send_mail'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <label for="candidate"><?php echo _l('send_to'); ?></label>
            <select name="candidate[]" id="candidate" class="selectpicker" multiple="true" data-live-search="true"
              data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">

              <?php foreach ($candidates as $s) { ?>
                <option value="<?php echo html_entity_decode($s['id']); ?>">
                  <?php echo html_entity_decode($s['candidate_code'] . ' ' . $s['candidate_name'] . ' ' . $s['last_name']); ?>
                </option>
              <?php } ?>
            </select>
            <br><br>
          </div>
          <div class="col-md-12">

          </div>

          <div class="col-md-12">
            <?php echo render_input('subject', 'subject'); ?>
          </div>

          <div class="col-md-12">
            <?php echo render_textarea('content', 'content', '', array(), array(), '', 'tinymce') ?>
          </div>
          <div id="type_care">

          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button id="sm_btn" type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="join_date" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">Select a Date</h5>
      </div>
      <?php echo form_open('admin/recruitment/save_joining_date'); ?>
      <div class="modal-body">
        <!-- Date Selector -->

        <input type="date" class="form-control" name='doj' id="datePicker">
        <input type="hidden" name='candidate-id' id='candidate-id' value=''>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Submit Date</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Structure DOE -->
<!-- Modal for Exit Date and Reason -->
<div class="modal fade" id="end_date" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php echo form_open('admin/recruitment/save_end_date'); ?>
      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">Exit Date and Reason</h5>
      </div>
      <div class="modal-body">
        <div class="mbot15">
          <input type="date" class="form-control" name="doe" id="exit-doe">
        </div>
        <div class="mbot15">
          <label for="exit-reason" class="form-label">Reason of Exit</label>
          <textarea class="form-control" name="reason_to_exit" id="exit-reason" rows="3"></textarea>
        </div>
        <input type="hidden" name="candidate-id" id="exit-candidate-id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>



<!-- Modal Structure view end date -->
<div class="modal fade" id="end_view" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">End Date and Reason</h5>
      </div>
      <form id="exit-form">
        <div class="modal-body">
          <input type="hidden" id="exit-candidate-id" name="candidate-id">
          
          <div class="form-group">
            <label for="exit-doe">End Date</label>
            <input type="date" class="form-control" id="exit-doe" name="doe">
          </div>

          <div class="form-group">
            <label for="exit-reason">Reason for Exit</label>
            <textarea class="form-control" id="exit-reason" name="reason_to_exit" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal Structure -->
<div class="modal fade" id="import_data" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">Import candidate</h5>
      </div>
      <?php echo form_open_multipart(admin_url("recruitment/save_candidate_bulk_data"), ["id" => "recruitment-candidate-bulk-upload-form", "onsubmit" => "disableButton()"]); ?>
      <div class="modal-body">
        <!-- Date Selector -->

        <input type="file" class="form-control" name="excelFile" accept=".xls, .xlsx" required style="margin-bottom: 15px;">

        <a href="/uploads/candidate_form.xlsx" download="">Download File Format</a>
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="submitButton">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>



<!-- Modal Structure -->
<div class="modal fade" id="send_link" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">Send Link</h5>
      </div>
      <?php echo form_open('', ['id' => 'submit_onboarding_form']); ?>
      <div class="modal-body">
        <!-- Date Selector -->
        <p>Are you sure you want to send onboarding link to this candidate?</p>
        <input type="hidden" name='Fname' id='Fname' value=''>
        <input type="hidden" name='Lname' id='Lname' value=''>
        <input type="hidden" name='DOB' id='DOB' value=''>
        <input type="hidden" name='DOJ' id='DOJ' value=''>
		<input type="hidden" name='DOE' id='DOE' value=''>
		<input type="hidden" name='reason_to_exit' id='reason_to_exit' value=''>
        <input type="hidden" name='mnumber' id='mnumber' value=''>
        <input type="hidden" name='tnumber' id='tnumber' value=''>
        <input type="hidden" name='address' id='address' value=''>
        <input type="hidden" name='city' id='city' value=''>
        <input type="hidden" name='pin' id='pin' value=''>
        <input type="hidden" name='sex' id='sex' value=''>
        <input type="hidden" name='martial_status' id='martial_status' value=''>
        <input type="hidden" name='email' id='email' value=''>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        <button type="submit" class="btn btn-primary">Yes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>


$(document).ready(function() {
    const table = $('#candidates-table');
    const skeletonLoader = $('.skeleton-loader');
    const itemsPerPage = 10; // Number of items per page
    let currentPage = 1; // Default starting page
    let searchVal = $('#searchValue');
    let change_status = $('#change_status');
    let rec_campaign = $("#rec_campaign");

    $("#searchValue").keyup(function (e) { 
      fetchCandidates(1);
    });

    $("#change_status").change(function () { 
      fetchCandidates(1)
    });

    $("#rec_campaign").change(function () { 
      fetchCandidates(1)
    });

    // Fetch candidates data from the server
    function fetchCandidates(page = 1) {
  
      skeletonLoader.show();
        $.ajax({
            url: '<?= admin_url('recruitment/fetch_candidates'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                page: page,  // Send the current page number in the request
                per_page: itemsPerPage,
                search: searchVal.val(),
                status_filter: change_status.val(),
                campaign_filter: rec_campaign.val()
            },
            success: function(data) {
                if (data && data.candidates && Array.isArray(data.candidates)) {
                    populateTable(data.candidates);
                    setupPagination(data.total, page);
                    console.log(data);
                }
                skeletonLoader.hide(); // Hide the skeleton loader
                table.show(); // Show the table
            },
            error: function(error) {
                console.error('Error fetching candidates:', error);
                skeletonLoader.hide(); // Hide the skeleton loader on error
            }
        });
    }

    // Function to populate table with data
    function populateTable(candidates) {
    const tbody = table.find('tbody');
    tbody.empty(); // Clear existing rows

    candidates.forEach(function(candidate) {
        const row = $('<tr>');

        // Basic details
        row.append('<td>' + candidate.candidate_code + '</td>');
        row.append('<td>' + generateCandidateNameLink(candidate) + '</td>');
        row.append('<td>' + candidate.position_name + '</td>');
        row.append('<td>' + candidate.status + '</td>');
        row.append('<td>' + candidate.email + '</td>');
        row.append('<td>' + candidate.phonenumber + '</td>');

        // DOJ: Joining date or button
        if (candidate.doj) {
            row.append('<td>' + candidate.doj + '</td>');
        } else {
            row.append(`
                <td>
                    <a class="btn btn-success btn-sm" candidate-id="${candidate.id}" 
                        onclick="open_joining(this); return false;">
                        Select Joining
                    </a>
                </td>`);
        }

        // Send Link: enabled only if DOJ exists
        if (candidate.doj) {
            row.append(`
                <td>
                    <a class="btn btn-warning btn-sm" data-id="${candidate.id}" 
                        onclick="send_link(this); return false;">
                        Send Link
                    </a>
                </td>`);
        } else {
            row.append('<td><a class="btn btn-warning btn-sm" disabled>Send Link</a></td>');
        }

        // Token button
        if (candidate.token) {
            row.append(`
                <td>
                    <a class="btn btn-success btn-sm" onclick="copyButtonValue(this)">
                        ${candidate.token}
                    </a>
                </td>`);
        } else {
            row.append('<td>NA</td>');
        }

        // DOE and Exit Reason
       if (candidate.doe || candidate.reason_to_exit) {
			row.append(`
				<td>
					<a class="btn btn-info btn-sm" candidate-id="${candidate.id}" 
					   onclick="end_view(this); return false;">
					   End Reason
					</a>
				</td>
			`);
		} else {
			row.append(`
				<td>
					<a class="btn btn-success btn-sm" candidate-id="${candidate.id}" 
					   onclick="end_joining(this); return false;">
					   Select End Date
					</a>
				</td>
			`);
		}


        tbody.append(row); // Add the new row
    });
}



    // Function to generate the candidate name with links
    function generateCandidateNameLink(candidate) {
        let nameLink = '<a href="' + '<?php echo admin_url("recruitment/candidate/"); ?>' + candidate.id + '" >' + candidate.candidate_name  + ' ' + candidate.last_name + '</a>';
        nameLink += '<div class="row-options">';

        nameLink += '<a href="' + '<?php echo admin_url("recruitment/candidate/"); ?>' + candidate.id + '" ><?php echo _l("view"); ?></a>';

        <?php if (has_permission('recruitment', '', 'edit') || is_admin()): ?>
            nameLink += ' | <a href="' + '<?php echo admin_url("recruitment/candidates/"); ?>' + candidate.id + '" ><?php echo _l("edit"); ?></a>';
        <?php endif; ?>

        <?php if (has_permission('recruitment', '', 'delete') || is_admin()): ?>
            nameLink += ' | <a href="' + '<?php echo admin_url("recruitment/delete_candidate/"); ?>' + candidate.id + '" class="text-danger _delete"><?php echo _l("delete"); ?></a>';
        <?php endif; ?>

        nameLink += '</div>';
        return nameLink;
    }

    // Function to setup pagination controls
    function setupPagination(totalItems, currentPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const paginationContainer = $('#pagination');
        paginationContainer.empty(); // Clear existing pagination

        const maxVisiblePages = 5;  // Number of pages to show in the pagination (e.g., 1, 2, 3, ..., 5)
        const startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        // Add "Previous" button
        if (currentPage > 1) {
            paginationContainer.append('<li class="page-item"><a class="page-link" href="#" data-page="' + (currentPage - 1) + '">Previous</a></li>');
        }

        // Add "First" button for large pages
        if (startPage > 1) {
            paginationContainer.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
            if (startPage > 2) {
                paginationContainer.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = (i === currentPage) ? ' active' : '';
            paginationContainer.append('<li class="page-item' + activeClass + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
        }

        // Add "Last" button for large pages
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationContainer.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
            paginationContainer.append('<li class="page-item"><a class="page-link" href="#" data-page="' + totalPages + '">' + totalPages + '</a></li>');
        }

        // Add "Next" button
        if (currentPage < totalPages) {
            paginationContainer.append('<li class="page-item"><a class="page-link" href="#" data-page="' + (currentPage + 1) + '">Next</a></li>');
        }

        // Attach click event on page numbers and "Previous"/"Next" buttons
        paginationContainer.find('a').click(function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            currentPage = page;
            fetchCandidates(page);
        });
    }

    // Initial fetch call
    fetchCandidates(currentPage);
});



  function open_joining(event) {

    console.log(event);
    $('#join_date').modal('show');



    // Extract info from data-* attributes
    var staffId = event.getAttribute('candidate-id');

    console.log(staffId);

    // Update the modal's content
    var candidate_inp = document.getElementById('candidate-id');
    candidate_inp.value = staffId;

  }
   function end_joining(event) {
  $('#end_date').modal('show');

  var staffId = event.getAttribute('candidate-id');

  // Reset the form fields
  $('#exit-doe').val('');
  $('#exit-reason').val('');
  $('#exit-candidate-id').val(staffId);
}

 function end_view(event) {
  $('#end_date').modal('show');

  var candidateId = event.getAttribute('candidate-id');
  $('#exit-candidate-id').val(candidateId);

  // Hide the save button in view-only mode
  $('#exit-save-btn').hide();

  $.ajax({
    url: '<?= base_url("recruitment/get_candidate_exit_data") ?>',
    type: 'POST',
    data: { id: candidateId },
    success: function(response) {
      var data = JSON.parse(response);
      $('#exit-doe').val(data.doe);
      $('#exit-reason').val(data.reason_to_exit);
    },
    error: function(xhr, status, error) {
      console.error("Error fetching candidate exit data:", error);
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Could not fetch candidate data.'
      });
    }
  });
}


  

  $("#import_data_btn").click(function (e) { 
    e.preventDefault();

    $('#import_data').modal('show');
    
  });

  function send_link(event) {

    console.log(event);
    $('#send_link').modal('show');



    // Extract info from data-* attributes
    var staffId = event.getAttribute('data-id');

    console.log(staffId);


    // Make an AJAX request to fetch the personal details
    $.ajax({
      url: '<?= base_url("interview_form/get_candidate_data") ?>', // Adjust this route as per your controller/method
      type: 'POST',
      data: {
        id: staffId
      },
      success: function(response) {
        // Parse the JSON response
        var data = JSON.parse(response);
        console.log(data);

        // Populate the form fields with the fetched data
        $('#Fname').val(data.candidate_name);
        $('#Lname').val(data.last_name);
        $('#DOB').val(data.birthday);
        $('#DOJ').val(data.doj);
		$('#DOE').val(data.doe);
		$('#reason_to_exit').val(data.reason_to_exit);
        $('#mnumber').val(data.phonenumber);
        $('#tnumber').val(data.mobile_no_2);
        $('#address').val(data.address);
        $('#city').val(data.city);
        $('#pin').val(data.pin);
        $('#sex').val(data.gender);
        $('#martial_status').val(data.marital_status);
        $('#email').val(data.email);

        // Populate other fields similarly
      },
      error: function(xhr, status, error) {
        console.error("Error: " + error);
      }
    });

  }

  function copyButtonValue(button) {
      // Get the button's text
      var buttonText = button.innerText;

      // Append the base URL to the button text
      var baseUrl = "<?= base_url('onboarding/staff/') ?>"; // PHP outputs the base URL as a string
      buttonText = baseUrl + buttonText;

      // Use the Clipboard API to copy the text
      navigator.clipboard.writeText(buttonText).then(() => {
          alert(`Copied: "${buttonText}"`);
      }).catch(err => {
          console.error('Failed to copy: ', err);
          alert('Failed to copy text!');
      });
  }


  $(document).ready(function() {
    // When the button is clicked, open the modal and fetch the details
    $('#submit_onboarding_form').submit(function(e) {
      // Get the candidate ID from the data-id attribute of the button
      e.preventDefault(); // avoid to execute the actual submit of the form.

      $('#send_link').modal('hide');

      var form = $(this);
      var actionUrl = form.attr('action');

      // Make an AJAX request to fetch the personal details
      $.ajax({
        url: '<?= base_url("/onboarding/personal_details") ?>', // Adjust this route as per your controller/method
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
          console.log(response);
          if (response == 'used') {
            Swal.fire({
              title: "Email has already been sent",
              
              icon: "warning"
            });
          } else {
            Swal.fire({
              title: "Success!",
              text: "Email is sent successfully!",
              icon: "success"
            });
          }
        },
        error: function(xhr, status, error) {
          console.error("Error: " + error);
        }
      });
    });
  });
</script>

<script>
    function disableButton(event) {
        // Get the form element
        var form = document.getElementById("recruitment-candidate-bulk-upload-form");

        // Disable the submit button
        document.getElementById("submitButton").disabled = true;

        // Optionally, change the button text to indicate it's being submitted
        document.getElementById("submitButton").innerText = "Submitting...";

        // Allow the form to be submitted
        return true;
        
    }
</script>
</body>

</html>