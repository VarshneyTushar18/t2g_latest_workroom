<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php //echo "<pre>"; print_r($result); ?>
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
	.table-loading table thead tr{
		height:auto !important;
	}
		  .loader {
         position: fixed;
		z-index: 99;
		top: 0;
		left: 250px;
		width: 80%;
		height: 100%;
		background: white;
		display: flex;
		justify-content: center;
		align-items: center;
    }

    .loader>img {
        width: 100px;
    }

    .loader .hidden {
        animation: fadeOut 1s;
        animation-fill-mode: forwards;
    }
</style>

<div id="wrapper" class="leave_balance">
    <div class="content">
	<div class="col-md-12 mtop15">
	<div class="col-md-6">

                <h4><i class=" fa fa-exchange"></i> Manage Saturday Leaves</h4>

              </div>
		<a href="<?php echo admin_url('holiday/'); ?>" class="btn btn-primary pull-right">

                  <?php echo "Assign Saturday Leaves"; ?>

                </a>
				</div>
				
        <div class="row">
            <div class="col-md-12">
			
                <div class="panel_s">
				
                    <div class="panel-body">
						<?php echo form_open('admin/Holiday/manageHoliday', array('id' => 'holiday-form')); ?>
                        <div class="clearfix"></div>
                        <div class="row">
                           <div class="col-md-12 form-group">
                        <!-- <form action=""> -->
						<div class="col-md-6">	
                        <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
						  </div>
						  <div class="col-md-6 form-group">
                        <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname', 'staff_identifi')), 'Select Employee');
                        ?>
						 </div>
						  <div class="col-md-3 form-group">
						 
                                      <select name="month" class="selectpicker" id="month" required>
										<?php
											// Generate month options
											for ($m = 1; $m <= 12; $m++) {
												$monthName = date('F', mktime(0, 0, 0, $m, 10));
												// Retain selected month after submission
												$selected = (isset($_POST['month']) && $_POST['month'] == $m) ? 'selected' : '';
												echo "<option value='$m' $selected>$monthName</option>";
											}
										?>
									</select>
                                  
							</div>
							 <div class="col-md-3 form-group">
                                  <select name="year" class="selectpicker" id="year" required>
									<?php
										$currentYear = date('Y');
										// Allow selection from current year -5 to current year +5
										for ($y = $currentYear; $y <= $currentYear + 5; $y++) {
											// Retain selected year after submission
											$selected = (isset($_POST['year']) && $_POST['year'] == $y) ? 'selected' : '';
											echo "<option value='$y' $selected>$y</option>";
										}
									?>
								</select>
							</div>
							 <div class="col-md-6 form-group">
                                    <button type="button" id = 'applybtn' class="btn btn-primary pull-left">View</button>
							</div>
						<div class="col-md-3 form-group">	
						
						<input type="hidden" class="status form-control" id="status" name="status" value="1" />
						</div>
						
                </div>
				
                <div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
                </div>

                       
                        </div>
                        
                        <table class="table table-timesheets-report">
                            <thead>
                            <tr>
							
                                <th><?php echo "S.No."; ?></th>
								<th><?php echo "Department"; ?></th>
                                <th><?php echo "Staff"; ?></th>
								 <th><?php echo "Saturday Leave"; ?></th>


                            </tr>
                            </thead>
                            <tbody id="sat_data">
								
							 
							  
                            </tbody>


                        </table>

					</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<!-- Button trigger modal -->


<!-- Modal -->

<?php init_tail(); ?>
<script>

// Set required prop for staffid
    $("#departments").prop('required', true);
    $("#staffid").prop('required', true);

    // On change of departments dropdown
    $('#departments').on('change', function() {
        $('.loader').removeClass('hidden'); // Show loader

        let department = $(this).val(); // Get selected department
		//alert(department);
        // AJAX call to fetch staff based on department
			$.ajax({
				url: "<?php echo base_url('admin/holiday/get_staff_department_json'); ?>",
				type: 'POST',
				data: {
					department
				},
				dataType: 'json',
				success: function(response) {
					let staff = '<option value=""></option>'; // Default empty option

					// Populate staff dropdown
					response.forEach(element => {
						staff += `<option value="${element.staffid}">${element.firstname} ${element.lastname} ${element.staff_identifi}</option>`;
					});
					
					$('#staffid').html(staff); // Insert the staff options
					$('.selectpicker').selectpicker('refresh'); // Refresh selectpicker UI
				},
				error: function() {
					alert('Failed to fetch staff. Please try again.');
				},
				complete: function() {
					$('.loader').addClass('hidden'); // Hide loader after request completes
				}
			});
    });

    // On change of staffid dropdown
    $('#staffid').on('change', function() {
       $('.loader').removeClass('hidden'); // Show loader
        let department = $('#departments').val();
        var staffid = $(this).val();
     


        // Fetch staff details
        $.ajax({
            url: "<?php echo base_url('admin/holiday/get_staff_json'); ?>",
            type: 'POST',
            data: {
                staffid
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
            },
            error: function() {
                alert('Failed to fetch staff details. Please try again.');
            },
            complete: function() {
                $('.loader').addClass('hidden'); // Hide loader after request completes
            }
        });

       
    });
$('#applybtn').on('click', function() {
	
        $('.loader').removeClass('hidden'); // Show loader
        let department = $('#departments').val();
        var staffid = $('#staffid').val();
        var month = $('#month').val();
		 var year = $('#year').val();
		//alert(range);
		// var to_month = $('#holiday_end_date').val();
	
        // Fetch staff details
        $.ajax({
            url: "<?php echo base_url('admin/holiday/getHolidayStaffData'); ?>",
            type: 'POST',
            data: {
				department,
                staffid,
				month,
				year
            },
            dataType: 'json',
            success: function(res) {
				$( "#sat_data" ).empty();
				var j=1;
			   for(var i = 0; i < res.length; i++) {
				   console.log(res[i].department_id);
				   
				    $('#sat_data').append('<tr><td>'+j+++'</td><td>'+res[i].department_name+'</td><td>'+res[i].staff_firstname +" "+res[i].staff_lastname+'</td><td>'+res[i].saturday_date+'</td></tr>');
				
				
			   }
			   
			   
            },
            error: function() {
                alert('Failed to fetch staff details. Please try again.');
            },
            complete: function() {
                $('.loader').addClass('hidden'); // Hide loader after request completes
            }
        });

       
    });

 
/* var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
            ]
        });
		*/
//document.getElementById("settime").value = "00:05:00";
</script>
</body>

</html>