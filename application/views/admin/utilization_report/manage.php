<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php //print_r($result); ?>
<?php // foreach($result as $res){ echo $res['name']; }?>
						
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .mb-3{
        margin-bottom: 15px;
    }

    .swal2-container{
        z-index: 100000 !important;
    }
	.loader {

        position: fixed;

        z-index: 99999;

        top: 100px;
		left: 280px;
		width: 75%;

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
<div class="loader hidden">

                    <img src="https://www.stage.tech2globe.co.in/loader.gif" alt="Loading...">

                </div>
<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 20px;">
                    <p class="h4"><i class="fa fa-tachometer" aria-hidden="true"></i> Utilization Report</p>
                </div>
			
                <div class="col-md-3">
                    <!-- Department Dropdown -->
				 <form id="multiSelectForm">
                    <select id="departmentFilter" name="department[]" class="form-control selectpicker" multiple aria-label="Select Department" data-live-search="true">
                        <option value="">Filter by department</option> 
						<?php foreach($result as $res){ ?>
							<option value="<?php echo $res['departmentid']; ?>"><?php echo $res['name']; ?></option> 
						<?php  }?>
						
                    </select>
					   </form>
                </div>
                <div class="col-md-3">
                    <!-- Designation Dropdown -->
                    <select id="designationFilter" name="empoyee[]" class="form-control" aria-label="Select Staff">
                        
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="startDateFilter" class="form-control" value="">
                </div>
                <div class="col-md-2">
                    <input type="date" id="endDateFilter" class="form-control" value="">
                </div>
				<div class="col-md-2">
                    <button type="button" id="submit" class="form-control btn btn-info" >Filter</button>
                </div>
             <div class="col-md-12" style="margin-top: 15px;">
                    <div class="table-responsive">
                        <table class="table" id="staffTable">
                            <thead>
                                <tr id="addTable"></tr>
                            </thead>
                            <tbody id="staffTableBody">
                                 
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="loader hidden">
   <!-- <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">-->
	 <img src="https://www.stage.tech2globe.co.in/loader.gif" alt="Loading...">
	
</div>

<?php init_tail(); ?>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
		$('#departmentFilter').change(function() {
		 $('.loader').removeClass('hidden');
    var value = $("#departmentFilter").val();
	
   // var value = $option.val();
	//alert(value);
		 $.ajax({
			url: "<?php echo base_url('admin/UtilizationReport/getDeptemp'); ?>",
			data: { department:value},
			type: "post",
			success: function(response){
				$('.loader').addClass('hidden');
			 console.log(JSON.parse(response));
			 let dept = JSON.parse(response);
						let options = ''; 
					for(var i = 0; i < dept.length; i++) {
						//console.log(emp[0]['name']);
				//$('.loader').removeClass('hidden');
					options += '<option class="empid'+dept[i]['staffid']+'" value="'+dept[i]['staffid']+'">' + dept[i]['firstname'] + " " + dept[i]['lastname'] +'</option>'; 
				  }
				  console.log(options);
				  $('#designationFilter').html('<option>Filter By Employee</option>'+options);
				  $('#designationFilter').selectpicker('destroy');	
					$('#designationFilter').selectpicker();				  
                }
                       
			});
		});
});


 $(document).ready(function () {
		$('#submit').click(function() {
			$('.loader').removeClass('hidden');
		$( "#addTable" ).empty();	
		$( "#staffTableBody" ).empty();
		var value = $("#departmentFilter").val();
		var emp = $('#designationFilter').val();
		var sdate = $("#startDateFilter").val();
		var edate = $('#endDateFilter').val();

		//alert(emp);
    $.ajax({
			url: "<?php echo base_url('admin/UtilizationReport/getFilterData'); ?>",
			data: { department:value,employee:emp,start_date:sdate,end_date:edate},
			type: "post",
			success: function(response){
				$('.loader').addClass('hidden');
			 console.log(JSON.parse(response));
			 var data = JSON.parse(response);
			  if (data.length > 0) {
				 const firstRecord =  data[0]; // Get first record keys
				  const firstRecordKeys = Object.keys(data[0]);
				// Generate table headers
				const headerRow = document.getElementById("addTable");
				const tableBody  = document.getElementById("staffTableBody");
				Object.keys(firstRecord).forEach(key => {
					const th = document.createElement("th");
					th.textContent = key;
					headerRow.appendChild(th);
				});
				// Generate table row data
				 // Loop through all records and populate the table
            data.forEach(record => {
                const tr = document.createElement("tr");

                firstRecordKeys.forEach(key => {
                    const td = document.createElement("td");
                    td.textContent = record[key] || ""; // Ensure key exists in all records
                    tr.appendChild(td);
                });

                tableBody.appendChild(tr);
            });
			  }
						}
                       
			});
		});
});
   var timesheetsTable = $('#staffTable');


        timesheetsTable.DataTable({
			searching: false,
            dom: 'Bfrtip',
			 buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
          
        });
</script>

