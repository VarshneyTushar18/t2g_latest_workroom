<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //print_r($dept);die; ?>

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
<!-- Custom CSS -->
<style>
    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        background-color: #141e46;
        color: #fff;
        font-size: 0.9rem;
        margin: 0;
        font-weight: 500;
        padding: 10px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .card-body {
        padding: 15px;
        padding-bottom: 0;
    }

    .score {
        font-size: 2rem;
        font-weight: 500;
    }

    .comment {
        font-size: 14px;
        color: #555;
        padding-top: 10px;
    }

    .low {
        color: green;
    }

    .medium {
        color: green;
    }

    .high {
        color: green;
    }

    .info-icon {
        cursor: pointer;
    }

    .popover-header {
        font-size: 14px;
        background-color: unset;
        border-bottom: 0;
    }

    .hr {
        border: 1px dashed #cfcfcf;
        margin-top: 4px;
    }

    .ellipsis {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        -webkit-line-clamp: 3;
    }

    .popover-body {
        padding: 8px;
    }

    #wrapper .row {
        display: flex;
        flex-wrap: wrap;
    }

    .h-100 {
        height: 100%;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .text-end {
        text-align: end;
    }

    .rounded {
        border-radius: 10px;
    }

    .p-0 {
        padding: 0;
    }

    .p-i-4 {
        padding-inline: 4px;
    }

    .popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        max-width: 60%;
        color: #000;

        overflow: auto;
    }

    .close-btn {
        text-align: end;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }
	.leftmargin{
		padding-top: 75px; 
		left: 250px;
	}
</style>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-*.min.js"></script>
<!--<img src="https://lh3.googleusercontent.com/d/1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM=w1000?authuser=0">-->
        <div class="row">
            <div class="col-md-12 leftmargin">
       <div class="col-sm-3 col-md-3 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Today’s Logged-in Candidates
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Timely and accurate reporting of work progress. Demonstrates consistent adherence to deadlines and provides detailed updates on tasks and projects."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="reporting">
													<?php $count = 0;
													foreach($dept as $departmentcount){ ?>
													<?php $total +=$departmentcount->login_count_today; ?>
														<?php } echo $total;?></div>
                                                    <!--<div class="hr"></div>
                                                    <div class="comment ellipsis reporting_comment"><span style="font-weight: bold;"> Feedback :  </span>  -</div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<div class="col-sm-3 col-md-3 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
											Candidates Not Logged In (Last 1 Hour)
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Timely and accurate reporting of work progress. Demonstrates consistent adherence to deadlines and provides detailed updates on tasks and projects."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="reporting">
													<?php //$count = 0;
													 ?>
													<?php echo count($emp_not_loggedin); ?>
														<?php ?></div>
                                                    <!--<div class="hr"></div>
                                                    <div class="comment ellipsis reporting_comment"><span style="font-weight: bold;"> Feedback :  </span>  -</div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<div class="col-sm-3 col-md-3 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Yesterday’s Logged-in Candidates
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Timely and accurate reporting of work progress. Demonstrates consistent adherence to deadlines and provides detailed updates on tasks and projects."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="reporting">
													<?php $count = 0;
													foreach($emp_yesterday_count as $yesterday_count){ ?>
													<?php $total +=$yesterday_count->emp_yesterday_count; ?>
														<?php } echo $total;?></div>
                                                    <!--<div class="hr"></div>
                                                    <div class="comment ellipsis reporting_comment"><span style="font-weight: bold;"> Feedback :  </span>  -</div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								</div>
                                </div>
<div id="wrapper" class="leave_balance">

                 
    <div class="content"style="padding-top:10px !Important;">
<!--<img src="https://lh3.googleusercontent.com/d/1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM=w1000?authuser=0">-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
					<select class="form-select form-select-lg mb-3" class="selectpicker" data-live-search="true" id ="select_file" aria-label=".form-select-lg example">
					  <option selected>Select Menu</option>
					<?php foreach ($result as $res) {  ?>
					  <option id="select_id" value="<?php echo $res->staffid; ?>"><?php echo $res->firstname ." ".$res->lastname ; ?></option>
					  <?php  } ?>
					</select>
					
					<select class="form-select form-select-lg mb-3" class="selectpicker" id ="select_file_child" data-live-search="true" aria-label=".form-select-lg example">
					 <option selected>Select Menu</option>
					
					</select>
					
		 <div align="center">    			 
     <div class="content" >
 
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
							<div class="w3-row-padding" >
								 <ul class="list-inline" id="display_img">
									  
									</ul>
									<div class="pagination" id="pagination">
									
							</div>

							

									</div>
								</div>
							</div>
						</div>
					</div>
        
        <!--modal body-->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" title="Close">
<span class="glyphicon glyphicon-remove"></span></button>
</div>
<div class="modal-body">

 <!-- carousel body-->
<div id="myGallery" class="carousel slide" data-interval="false">
<div class="carousel-inner" id="img_model"> 
	
    </div>

<!-- Previous and Next buttons-->
<a class="left carousel-control" href="#myGallery" role="button" data-slide="prev">
<span class="glyphicon glyphicon-chevron-left"></span></a> 
<a class="right carousel-control" href="#myGallery" role="button" data-slide="next">
<span class="glyphicon glyphicon-chevron-right"></span></a>
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
    
   
<?php init_tail(); ?>
<script>

$( "#select_file" ).each(function(index) {
		//$( "#select_file_child" ).empty();
		//$( "#select_file_child" ).empty();
		 $("#select_file").change(function(event){
		event.preventDefault(event);
       //var boolKey = $(this).val();
	  var boolKey = $(this).children("option:selected").val();
		// var boolKey = $(this).v("name");
		  //alert(boolKey);
		 
        $.ajax({
                url: '<?php echo base_url('admin/Emt/getEmployeedepartment');?>',
                type: 'GET',
				data: {staffid: boolKey},
				 cache: false,
                success: function(res) {
						var emp = JSON.parse(res);
						console.log(emp);
						let options = ''; 
					for(var i = 0; i < emp.length; i++) {
						//console.log(emp[0]['name']);
				//$('.loader').removeClass('hidden');
					let date_convert = emp[i]['date_time'];
					const date = new Date(date_convert)

					// Get year, month, and day components
					const year = date.getFullYear();
					const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Add 1 because getMonth() returns 0-indexed months
					const day = date.getDate().toString().padStart(2, '0');

					// Format the date string
					const formattedDate = `${year}-${month}-${day}`;

					console.log(formattedDate);
					options += '<option class="'+emp[i]['staff_id']+'" attr-data="'+emp[i]['directory_id']+'" value="'+emp[i]['staff_id']+'">' + formattedDate + '</option>'; 
				  }
				  console.log(options);
				  $('#select_file_child').html('<option>Select Menu</option>'+options);
				  $('#select_file_child').selectpicker('destroy');	
					$('#select_file_child').selectpicker();				  
                }	
                
            });
 
});
});

$( "#select_file_child" ).each(function(index) {	

$("#select_file_child").change(function(event){
	event.preventDefault();
	$( "#display_img" ).empty();
		//var bookKey = $(this).attr("attr-data");
		//$(this).find(':selected').attr('data-id')
		var boolKey = $(this).find("option:selected").attr('attr-data');
	  // alert(boolKey);
		// var boolKey = $(this).v("name");
		  // alert(boolKey);
		console.log("empty");
		$( "#img_model" ).empty();
        $.ajax({
                url: '<?php echo base_url('admin/Emt/getImgDepartment');?>',
                type: 'GET',
				async: true,
				data: {date: boolKey},
				 cache: false,
                success: function(res) {
				//	$("#display_img").remove();
				
						var emp = JSON.parse(res);
						console.log(emp);
						var i = 0;
					if(i == 0){
					
						let str=emp[i]['name'];
						let string_data = str.split("_");
						let timedata = string_data[2];
						let timedata_con = timedata.split(".");
						let string_match = timedata_con[0].match(/.{1,2}/g);
						let string_value = string_match.join(":");
						let empid_local = emp[0]['id'];
							//$('#download').append('<a href="https://drive.usercontent.google.com/uc?id='+emp[0]['id']+'&authuser=0&export=download">Download</a>');
							$('#img_model').append(`<div class="item active"><i class="fa fa-download" aria-hidden="true"></i><button class="download" onClick=ImageidData('${emp[0]['id']}','${string_value}')>Download</button><h4>${string_value}</h4><img src="https://lh3.googleusercontent.com/d/${emp[0]['id']}=w1000?authuser=0" alt="item0" style="height:400px;width:600px;"><div class="carousel-caption"></div></div>`);
						}
						
					for(var i = 1; i < emp.length; i++) {
						//console.log(emp[i]['name']);
						let str=emp[i]['name'];
						let string_data = str.split("_");
						let timedata = string_data[2];
						let timedata_con = timedata.split(".");
						let string_match = timedata_con[0].match(/.{1,2}/g);
						let string_value = string_match.join(":");
						//console.log("loop");
						//var empid_loop = 'fgfgfgf';
						let empids = emp[i]['id'];

					//if(emp[i]['nextPageToken']==''){
						//$('#download').append('<a href="https://drive.usercontent.google.com/uc?id='+emp[0]['id']+'&authuser=0&export=download">Download</a>');
					$('#display_img').append('<li><a href="#myGallery"data-slide-to="'+i+'"><img class="img-thumbnail"  src="https://lh3.googleusercontent.com/d/'+emp[i]['id']+'=w1000?authuser=0" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a><span class="date_class">'+string_value+'</span></li>');
					$('#img_model').append(`<div class="item"><button class="download" onClick=ImageidDataVal('${emp[i]['id']}','${string_value}')><i class="fa fa-download" aria-hidden="true"></i></button><h4>${string_value}</h4><img src="https://lh3.googleusercontent.com/d/${emp[i]['id']}=w1000?authuser=0" alt="item${i}"><div class="carousel-caption"></div></div>`);
					
					//}else{
					
					//$('#display_img').append('<li><a href="#myGallery"data-slide-to="'+i+'"><img class="img-thumbnail"  src="https://lh3.googleusercontent.com/d/'+emp[i]['id']+'=w1000?authuser=0" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a><span class="date_class">'+string_value+'</span></li>');
					//}
					
					//$( "select_file_child" ).selectmenu( "refresh" );
                }
				//$('#pagination').append('<input type="text" value="~!!~AI9FV7SeDD7_KlYO6MiXIrAOlG93m4rLaC4Ywrc7kRrrD2zUqgHFVQmpByXIv8GJtt-OpYrc3Izj9HqviO_sVfxW6j4lHMsdEyEcy1qHLJCF6CpalxovTAJFG1N3dOoPAuzfsxXEYIS0R9s4hGIw2yyY6v9ms8BnT1C8aeadpaonrDTS2DPzxDiCWBWem65taMO-z-IFGTulwHA7ir1c03xZT3TPUU2tNf0xbiSN499d50TWvqJg3XpINXvNDByrhvtzxn0iX3kYMOZNi-8BtHYCEB32Yo62A7W_sxcItiojzhoj0OkOMScWYDTC08cCUJm7h1vmkMJvggF3MN07ZuOZCbD3z9DHnQO7Kyf-rOvBw2hKgON2Dxk1D8wcfulRv8sTFVZr7iVMEoJh0x29LNy4Dihn-sHARQ=="><button id="next">Next</button>');	
				//	$('.loader').removeClass('hidden'); // Show loader
					// top.location.href="/admin/Emt/getEmployeeDate?date="+boolKey;//redirection
                    // window.location.href = '<?php base_url('admin/Emt/getChildId');?>';
					//$('#emdata').append('<tr><td>' + res[0][i]['name'] + '</td></tr>');
                }
            });
 
});
});
function ImageidData(id,time){
	 $(document).ready(function () {
         //  let field = $(this).val();
			//alert(time);
           $.ajax({
              url: '<?php echo base_url('admin/Emt/getDownload');?>',
              type: 'POST',
			  data: {date: id,time:time},
			  xhrFields: {
                responseType: 'blob'  // Important to handle binary data (image)
            },
              success: function(response) {
               const blobUrl = URL.createObjectURL(response);

                // Create an anchor element to trigger the download
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = 'modified-image.png';  // Set the filename
                document.body.appendChild(a);  // Append anchor to the body
                a.click();  // Simulate click to trigger download

                // Clean up
                a.remove();
                URL.revokeObjectURL(blobUrl);  // Revoke the object URL
               }
            });
	 });
}

  function ImageidDataVal(id,time){
           $(document).ready(function () {
         //  let field = $(this).val();
			//alert(time);
           $.ajax({
              url: '<?php echo base_url('admin/Emt/getDownload');?>',
              type: 'POST',
			  data: {date: id,time:time},
			  xhrFields: {
                responseType: 'blob'  // Important to handle binary data (image)
            },
              success: function(response) {
				  const blobUrl = URL.createObjectURL(response);

                // Create an anchor element to trigger the download
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = 'modified-image.png';  // Set the filename
                document.body.appendChild(a);  // Append anchor to the body
                a.click();  // Simulate click to trigger download

                // Clean up
                a.remove();
                URL.revokeObjectURL(blobUrl);  // Revoke the object URL
               }
            });
	 });
}

/*	
 $('#next').on("click", function(){
	var token =  $('#token').val();

	$( "#display_img" ).empty();

        $.ajax({
                url: '<?php echo base_url('admin/Emt/nextToken');?>',
                type: 'POST',
				async: true,
				data: {date: token},
                success: function(res) {
						var emp = JSON.parse(res);
						
						var i = 0;
					if(i == 0){
							$('#img_model').append('<div class="item active"><img src="https://lh3.googleusercontent.com/d/'+emp[0]['id']+'=w1000?authuser=0" alt="item0"  style="height:400px;width:600px;"><div class="carousel-caption"></div></div>');
						}
					for(var i = 1; i < emp.length; i++) {
						console.log(emp[i]['name']);
						let str=emp[i]['name'];
						let string_data = str.split("_");
						let timedata = string_data[2];
						let timedata_con = timedata.split(".");
						let string_match = timedata_con[0].match(/.{1,2}/g);
						let string_value = string_match.join(":");
						
					if(emp[i]['nextPageToken']==''){
					$('#display_img').append('<li><a href="#myGallery"data-slide-to="'+i+'"><img class="img-thumbnail"  src="https://lh3.googleusercontent.com/d/'+emp[i]['id']+'=w1000?authuser=0" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a><span class="date_class">'+string_value+'</span></li>');
					$('#img_model').append('<div class="item"><img src="https://lh3.googleusercontent.com/d/'+emp[i]['id']+'=w1000?authuser=0" alt="item'+i+'"  style="height:400px;width:600px;"><div class="carousel-caption"></div></div>');
					
					}else{
					
					$('#display_img').append('<li><a href="#myGallery"data-slide-to="'+i+'"><img class="img-thumbnail"  src="https://lh3.googleusercontent.com/d/'+emp[i]['id']+'=w1000?authuser=0" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a><span class="date_class">'+string_value+'</span></li>');
					}
					
					//$( "select_file_child" ).selectmenu( "refresh" );
                }
				$('#pagination').append('<button class="next">Next</a>');	
				//	$('.loader').removeClass('hidden'); // Show loader
					// top.location.href="/admin/Emt/getEmployeeDate?date="+boolKey;//redirection
                    // window.location.href = '<?php base_url('admin/Emt/getChildId');?>';
					//$('#emdata').append('<tr><td>' + res[0][i]['name'] + '</td></tr>');
                }
            });
 

});*/
   var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
            ]
        });
		
		
		
	
$('#select_file').selectpicker();


 /* $(document).ready(function() {

        $('#link_status').change(function() {
            let field = $('.inputid').val();
			console.log(field);
            $.ajax({
                url: '<?php echo base_url('admin/emt/getChildId');?>',
                type: 'POST',
                success: function(data) {
                   alert(data);
                }
            });
        });
    });
    */

</script>
<script>
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
}
</script>
 
         
</body>

</html>