<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //echo "<pre>"; print_r($result);die; ?>
<?php init_head(); ?>

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
	#display_img img{
		     width: 250px !important;
			margin: 10px 15px;
			border: 1px double;
	}
	#select_file_child{
		padding: 6px 46px;
    border-radius: 5px;
    border-color: #e1e6ee;
	}
	.img_gallery {
    width: 33%;
    display: inline-grid;
	}
	.date_class{
		text-align: center;
		background: gray;
		margin: 0px 20px;
		color: white;
		border-radius: 5px;
	}
	#display_img li{
		display: inline-grid;
	}
	
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!-- Latest compiled and minified CSS -->

<div id="wrapper" class="leave_balance">
    <div class="content">
<!--<img src="https://lh3.googleusercontent.com/d/1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM=w1000?authuser=0">-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
					<select class="form-select form-select-lg mb-3" class="selectpicker" data-live-search="true" id ="select_file" aria-label=".form-select-lg example">
					  <option selected>Select Menu</option>
					<?php foreach ($result as $res) {  ?>
					  <option id="select_id" value="<?php echo $res['departmentid']; ?>"><?php echo $res['name']; ?></option>
					  <?php  } ?>
					</select>
					
					<div class="col-md-3">
                    <!-- Designation Dropdown -->
                    <select id="designationFilter" name="empoyee[]" class="form-control" aria-label="Select Staff">
                        
                    </select>
					
                </div>
					<div class="col-md-2">
                    <button type="button" id="submit" class="form-control btn btn-info" >Filter</button>
                </div>
		 <div align="center">    			 
     <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
							<div class="w3-row-padding" >
								<h3 id="heading"></h3>
								<table id="display_data" class="table table-striped table-dark">
								  
								 
								</table>						
									</div>
								</div>
							</div>
						</div>
					</div>
					
    
	
            
        
 
    </div></div>    
        
        
       </div>
                    </div>
                </div>
            </div>
        </div>
     <div class="loader hidden">

                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">

                </div>
   
<?php init_tail(); ?>
<script>
  

$( "#select_file" ).each(function(index) {
		//$( "#select_file_child" ).empty();
		$( "#select_file_child" ).empty();
		
		 $("#select_file").change(function(){
	 $('.loader').removeClass('hidden');
       //var boolKey = $(this).val();
	  var boolKey = $(this).children("option:selected").val();
		// var boolKey = $(this).v("name");
		   //alert(boolKey);
		 
        $.ajax({
                url: '<?php echo base_url('admin/assets/getDepartmentStaff');?>',
                type: 'GET',
				data: {date: boolKey},
                success: function(response) {
					$('.loader').addClass('hidden');
			 console.log(JSON.parse(response));
			 let dept = JSON.parse(response);
						let options = ''; 
					for(var i = 0; i < dept.length; i++) {
						//console.log(emp[0]['name']);
			
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
		//$( "#addTable" ).empty();	
	//	$( "#staffTableBody" ).empty();
		var value = $("#select_file").val();
		var emp = $('#designationFilter').val();

		//alert(emp);
    $.ajax({
			url: "<?php echo base_url('admin/assets/getFilterData'); ?>",
			data: { department:value,employee:emp},
			type: "post",
			success: function(response){
				$('.loader').addClass('hidden');
			 console.log(JSON.parse(response));
			let list = ''; 
			 var data = JSON.parse(response);
			
			 
			  if (data.length > 0) {
				  for(var i = 0; i < data.length; i++) {
					 if(i==0){
					//console.log("dataaaa"+data);
					$('#heading').html(data[i]['firstname']+ " "+data[i]['lastname']); 
					//list += '<tr><td>Name:</td><td style="text-align: right;">'+data[i]['firstname']+ " "+data[i]['lastname']+'</td></tr>';  
					//list += '<tr><td>Email:</td><td style="text-align: right;">'+data[i]['email']+'</td></tr>';  
					list += '<tr><td style="font-weight:800;">Assets Name:</td><td style="text-align: right;">' + (data[i]['assets_name'] || "N/A") + '</td></tr>';
					list += '<tr><td style="font-weight:800;">Screen Size:</td><td style="text-align: right;">'+(data[i]['screen_size']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Serial No:</td><td style="text-align: right;">'+(data[i]['serial_no']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Series:</td><td style="text-align: right;">'+(data[i]['series']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Unit Price:</td><td style="text-align: right;">'+(data[i]['unit_price']|| "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Warranty Period:</td><td style="text-align: right;">'+(data[i]['warranty_period']|| "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Action Code:</td><td style="text-align: right;">'+(data[i]['acction_code']|| "N/A")+'</td></tr>';  
					list += '<tr><td style="font-weight:800;">Assets Code:</td><td style="text-align: right;">'+(data[i]['assets_code'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ms Office:</td><td style="text-align: right;">'+(data[i]['office'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">OS:</td><td style="text-align: right;">'+(data[i]['operating_system'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Processor:</td><td style="text-align: right;">'+(data[i]['processor'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ram:</td><td style="text-align: right;">'+(data[i]['ram'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ram Gen:</td><td style="text-align: right;">'+(data[i]['ram_gen'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage - 1:</td><td style="text-align: right;">'+(data[i]['storage_1'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage - 2:</td><td style="text-align: right;">'+(data[i]['storage_2'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage Type:</td><td style="text-align: right;">'+(data[i]['storage_type'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Allocation:</td><td style="text-align: right;">'+(data[i]['total_allocation'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Damage:</td><td style="text-align: right;">'+(data[i]['total_damages'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Liquidation:</td><td style="text-align: right;">'+(data[i]['total_liquidation'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Lost:</td><td style="text-align: right;">'+(data[i]['total_lost'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Warranty:</td><td style="text-align: right;">'+(data[i]['total_warranty'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Time Allocation:</td><td style="text-align: right;">'+(data[i]['time_acction'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Adopter:</td><td style="text-align: right;">'+(data[i]['adopter']|| "N/A")+'</td></tr>';  
					list += '<tr><td style="font-weight:800;">Graphic Card:</td><td style="text-align: right;">'+(data[i]['graphic_card']|| "N/A")+'</td></tr>'; 
					list += '<br/><tr style="border-bottom:1px solid #000; height:20px;"></tr><br/>';
					  
				//  console.log(list);
				 $('#display_data').html(list);
					 }else{
					//	 $('#device').html('Device');
					list += '<br/><tr style="height:20px;"></tr><br/>';
					list += '<tr><td style="font-weight:800;">Assets Name:</td><td style="text-align: right;">' + (data[i]['assets_name'] || "N/A") + '</td></tr>';
					list += '<tr><td style="font-weight:800;">Screen Size:</td><td style="text-align: right;">'+(data[i]['screen_size']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Serial No:</td><td style="text-align: right;">'+(data[i]['serial_no']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Series:</td><td style="text-align: right;">'+(data[i]['series']|| "N/A")+'</td></tr>'; 
					list += '<tr><td style="font-weight:800;">Unit Price:</td><td style="text-align: right;">'+(data[i]['unit_price']|| "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Warranty Period:</td><td style="text-align: right;">'+(data[i]['warranty_period']|| "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Action Code:</td><td style="text-align: right;">'+(data[i]['acction_code']|| "N/A")+'</td></tr>';  
					list += '<tr><td style="font-weight:800;">Assets Code:</td><td style="text-align: right;">'+(data[i]['assets_code'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ms Office:</td><td style="text-align: right;">'+(data[i]['office'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">OS:</td><td style="text-align: right;">'+(data[i]['operating_system'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Processor:</td><td style="text-align: right;">'+(data[i]['processor'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ram:</td><td style="text-align: right;">'+(data[i]['ram'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Ram Gen:</td><td style="text-align: right;">'+(data[i]['ram_gen'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage - 1:</td><td style="text-align: right;">'+(data[i]['storage_1'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage - 2:</td><td style="text-align: right;">'+(data[i]['storage_2'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Storage Type:</td><td style="text-align: right;">'+(data[i]['storage_type'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Allocation:</td><td style="text-align: right;">'+(data[i]['total_allocation'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Damage:</td><td style="text-align: right;">'+(data[i]['total_damages'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Liquidation:</td><td style="text-align: right;">'+(data[i]['total_liquidation'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Lost:</td><td style="text-align: right;">'+(data[i]['total_lost'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Total Warranty:</td><td style="text-align: right;">'+(data[i]['total_warranty'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Time Allocation:</td><td style="text-align: right;">'+(data[i]['time_acction'] || "N/A")+'</td></tr>';
					list += '<tr><td style="font-weight:800;">Adopter:</td><td style="text-align: right;">'+(data[i]['adopter']|| "N/A")+'</td></tr>';  
					list += '<tr><td style="font-weight:800;">Graphic Card:</td><td style="text-align: right;">'+(data[i]['graphic_card']|| "N/A")+'</td></tr>'; 
					$('#display_data').html(list);
					 }
				  }
				  }else{
					 $('#display_data').html("<h3>Data Not Available</h3>"); 
				  }
				//list += '<li class="empid'+dept[i]['staffid']+'" value="'+dept[i]['staffid']+'">' + dept[i]['firstname'] + " " + dept[i]['lastname'] +'</li>'; 
				//  }
				//  console.log(list);
				//  $('#display_data').html(list);
			  }
						})
                       
			});
		});
 

   var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
            ]
        });
		
		
		
	
$('#select_file').selectpicker();

</script>
<script>
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
}
</script>
 
         
</body>

</html>