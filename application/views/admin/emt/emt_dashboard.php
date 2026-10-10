<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //print_r($res_child); ?>
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-*.min.js"></script>
<div id="wrapper" class="leave_balance">
    <div class="content">
<!--<img src="https://lh3.googleusercontent.com/d/1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM=w1000?authuser=0">-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
					<select class="form-select form-select-lg mb-3" class="selectpicker" data-live-search="true" id ="select_file" aria-label=".form-select-lg example">
					  <option selected>Select Menu</option>
					<?php foreach ($result->files as $file) {  ?>
					  <option id="select_id" value="<?php echo $file->id; ?>"><?php echo $file->name; ?></option>
					  <?php  } ?>
					</select>
					
					<select class="form-select form-select-lg mb-3" class="selectpicker" id ="select_file_child" data-live-search="true" aria-label=".form-select-lg example">
					 <option selected>Select Menu</option>
					
					</select>
					
		 <div align="center">    			 
     <div class="content">

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
		$( "#select_file_child" ).empty();
		 $("#select_file").change(function(event){
		event.preventDefault(event);
       //var boolKey = $(this).val();
	  var boolKey = $(this).children("option:selected").val();
		// var boolKey = $(this).v("name");
		  // alert(boolKey);
		 
        $.ajax({
                url: '<?php echo base_url('admin/Emt/getEmployeeDate');?>',
                type: 'GET',
				data: {date: boolKey},
				 cache: false,
                success: function(res) {
						var emp = JSON.parse(res);
						
						let options = ''; 
					for(var i = 0; i < emp.length; i++) {
						//console.log(emp[0]['name']);
				//$('.loader').removeClass('hidden');
					options += '<option class="'+emp[i]['id']+'" value="'+emp[i]['id']+'">' + emp[i]['name'] + '</option>'; 
				  }
				  console.log(options);
				  $('#select_file_child').html('<option>Select Menu</option>'+options);
				  $('#select_file_child').selectpicker('destroy');	
					$('#select_file_child').selectpicker();				  
                }	
                
            });
 
});
});

function snapshotImgUrl(fileId) {
	return '<?php echo base_url('admin/Emt/viewImg/'); ?>' + encodeURIComponent(fileId);
}

function snapshotTimeLabel(name) {
	try {
		var parts = String(name || '').split('_');
		if (parts.length < 3) {
			return name || '';
		}
		var timedata = parts[parts.length - 1].split('.')[0];
		var chunks = timedata.match(/.{1,2}/g);
		return chunks ? chunks.join(':') : timedata;
	} catch (e) {
		return name || '';
	}
}

$( "#select_file_child" ).each(function(index) {	

$("#select_file_child").change(function(event){
	event.preventDefault();
	$( "#display_img" ).empty();
        var boolKey = $(this).children("option:selected").val();
		if (!boolKey || boolKey === 'Select Menu') {
			return;
		}
		$( "#img_model" ).empty();
        $.ajax({
                url: '<?php echo base_url('admin/Emt/getImg');?>',
                type: 'GET',
				async: true,
				data: {date: boolKey},
				 cache: false,
                success: function(res) {
					var emp = [];
					try {
						emp = (typeof res === 'string') ? JSON.parse(res) : res;
					} catch (e) {
						$('#display_img').html('<li class="text-danger">Could not load images.</li>');
						return;
					}
					if (!emp || !emp.length) {
						$('#display_img').html('<li class="text-muted">No screenshots found for this date.</li>');
						return;
					}

					for (var i = 0; i < emp.length; i++) {
						var fileId = emp[i]['id'];
						var string_value = snapshotTimeLabel(emp[i]['name']);
						var src = snapshotImgUrl(fileId);
						var activeClass = (i === 0) ? ' active' : '';

						$('#display_img').append(
							'<li><a href="#myGallery" data-slide-to="'+i+'">' +
							'<img class="img-thumbnail" src="'+src+'" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a>' +
							'<span class="date_class">'+string_value+'</span></li>'
						);
						$('#img_model').append(
							'<div class="item'+activeClass+'">' +
							'<button class="download" onClick="ImageidDataVal(\''+fileId+'\',\''+string_value+'\')"><i class="fa fa-download" aria-hidden="true"></i></button>' +
							'<h4>'+string_value+'</h4>' +
							'<img src="'+src+'" alt="item'+i+'" style="height:400px;width:600px;">' +
							'<div class="carousel-caption"></div></div>'
						);
					}
                },
				error: function() {
					$('#display_img').html('<li class="text-danger">Failed to load images from Drive.</li>');
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