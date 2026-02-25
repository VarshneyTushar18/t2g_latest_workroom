<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Custom CSS -->
<style>
    
    .loader {
        position: fixed;
        z-index: 99;
        top: 0;
        left: 0;
        width: 100%;
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
	.custom-control-label{
		padding-left: 10px;
	}
	.alert {
		background:silver;
		margin-top: 15px;
	}
	.alert-success{
		background:green;
		color:white;
	}
	.hide{
		display:none;
	}
    
</style>

<!-- <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script> -->
<script src="https://cdn.ckeditor.com/4.8.0/full-all/ckeditor.js"></script>


<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body"> 
			<div class="success hide">
										<div class="alert alert-success">
										  <strong>Success!</strong> Indicates a successful or positive action.
										</div>
								</div>		
                <?php echo form_open('admin/Holiday', array('id' => 'holiday-form')); ?>
				  
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
						<label>Start Date</label>
						<input type="date" class="holiday_selectpicker form-control" id="holiday_start_date" name="start_date" style="width: 100%; padding:6px 12px;" required />
					
						
						</div>
						<div class="col-md-3 form-group">	
						<label>	End Date</label>
						<input type="date" class="holiday_selectpickers form-control" id="holiday_end_date" name="end_date" style="width: 100%; padding:6px 12px;" required />
						</div>
						<div class="col-md-3 form-group">	
						
						<input type="hidden" class="status form-control" id="status" name="status" value="1" />
						</div>
						
                </div>
				
                <div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
                </div>
				
				<div id="holiday_master">
				</div>
				
					<div class="col-md-12 form-group">
					<button id="submit" class="btn btn-info" type="button" >submit</button>
					</div>
				
				
                <!-- Default KRA Table -->
               

             


                <!-- <input id="submitBtn" type="submit" class="btn btn-primary" value="Submit"> -->
                <!--<button class="btn btn-primary submitBtn nonEditButton" name="status" type="submit" value="1">Publish</button>
                <button class="btn btn-warning submitBtn nonEditButton" name="status" type="submit" value="0">Draft</button>-->


                </form>

            </div>
    </div>
</div>

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
	$('#holiday_end_date').on('change', function() {
        $('.loader').removeClass('hidden'); // Show loader
        let department = $('#departments').val();
        var staffid = $('#staffid').val();
        var from_month = $('#holiday_start_date').val();
		 var to_month = $('#holiday_end_date').val();
	
        // Fetch staff details
        $.ajax({
            url: "<?php echo base_url('admin/holiday/get_staff_to_month'); ?>",
            type: 'POST',
            data: {
                staffid,
				from_month,
				to_month
            },
            dataType: 'json',
            success: function(res) {
				//console.log(res.length);
				//var len = res.length;
				
               //var response = JSON.parse(res);
			   $( "#holiday_master" ).empty();
			
			    //console.log(String(res[0].month));
			// console.log(res[0]!==null);
			 if(String(res[0].month)==='undefined'){
				 var array = [];
				for(var i = 0; i < res.length; i++) {
				
					array.push(res[i]);
				 
				}
				console.log(array);
				var data = array;
				alert("Data already added in "+data);
			 }else{
				
				   for(var i = 0; i < res.length; i++) {
					//console.log(res[i]['1-sat']!=null && res[i]['2-sat']!=null);
					if(res[i]['1-sat']!=null && res[i]['2-sat']==null && res[i]['3-sat']==null && res[i]['4-sat']==null){
					   $('#holiday_master').append('<div class="col-md-6 form-group" style="height:250px"><div class="alert alert-primary" role="alert">'+res[i].month+'</div><div class="input-group"><span class="input-group-addon"><input type="checkbox" class="custom-control-input" value="'+res[i]['1-sat']+'" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> First Saturday</label></div></div>'); 
					}
					else if(res[i]['1-sat']!=null && res[i]['2-sat']!=null && res[i]['3-sat']==null && res[i]['4-sat']==null){
						 $('#holiday_master').append('<div class="col-md-6 form-group" style="height:250px"><div class="alert alert-primary" role="alert">'+res[i].month+'</div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['1-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> First Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" class="custom-control-input" value="'+res[i]['2-sat']+'" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Second Saturday</label></div></div>'); 
					}
					else if(res[i]['1-sat']!=null && res[i]['2-sat']!=null && res[i]['3-sat']!=null && res[i]['4-sat']==null){
						 $('#holiday_master').append('<div class="col-md-6 form-group" style="height:250px"><div class="alert alert-primary" role="alert">'+res[i].month+'</div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['1-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> First Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" class="custom-control-input" value="'+res[i]['2-sat']+'" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Second Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['3-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Third Saturday</label></div></div>'); 
					}
					else if(res[i]['1-sat']!=null && res[i]['2-sat']!=null && res[i]['3-sat']!=null && res[i]['4-sat']!=null && res[i]['5-sat']==null){
						 $('#holiday_master').append('<div class="col-md-6 form-group" style="height:250px"><div class="alert alert-primary" role="alert">'+res[i].month+'</div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['1-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> First Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" class="custom-control-input" value="'+res[i]['2-sat']+'" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Second Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['3-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Third Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['4-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Fourth Saturday</label></div></div>'); 
					}
					else{
						
						 $('#holiday_master').append('<div class="col-md-6 form-group" style="height:250px"><div class="alert alert-primary" role="alert">'+res[i].month+'</div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['1-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> First Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['2-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Second Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" class="custom-control-input" value="'+res[i]['3-sat']+'" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Third Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['4-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Fourth Saturday</label></div><div class="input-group"><span class="input-group-addon"><input type="checkbox" value="'+res[i]['5-sat']+'" class="custom-control-input" id="customCheck" name="example1"></span><label class="custom-control-label form-control" for="customCheck"> Fifth Saturday</label></div></div>'); 
						
					}
					
				   }
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

	  // On change of staffid dropdown
    $('#submit').on('click', function() {
		var sat = [];  
		 let department_id = $('#departments').val();
			var staffid = $('#staffid').val();
			var from_month = $('#holiday_start_date').val();
			var to_month = $('#holiday_end_date').val();
			var status = $('#status').val();
        $(':checkbox:checked').each(function(i){
		if($(this).is(":checked")){  
			 sat.push($(this).val());  
			}  
		 });
		  $.ajax({
				url: "<?php echo base_url('admin/holiday/get_staff_calculate_saturday'); ?>",
				type: 'POST',
				data: {
					department_id,
					staffid,
					from_month,
					to_month,
					status,
					sat:sat

					
				},
				dataType: 'json',
				success: function(response) {
					//console.log(response.success);
					$('.success').removeClass('hide'); 
					 $('#submit').attr('disabled','disabled');
					setTimeout(function() 
					  {
						// Show loader
						top.location.href = '<?php base_url('admin/Holiday');?>';
					  }, 1000);
				},
				error: function() {
					alert('Failed to fetch staff. Please try again.');
				},
				complete: function() {
					$('#submit').attr('disabled','disabled');
					$('.loader').addClass('hidden'); // Hide loader after request completes
				}
			});
      
     
       
    });
	  
 

    
 
</script>





</body>

</html>