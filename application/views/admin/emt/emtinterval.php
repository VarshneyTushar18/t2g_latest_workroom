<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php //echo "<pre>"; print_r($result); ?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-*.min.js"></script>
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
	.bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn) {
    width: 520px !important;
}
</style>
<div class="loader hidden">
							<div class="alert alert-success">
							  <strong>Success!</strong> Indicates a successful or positive action.
							</div>
					</div>
<div id="wrapper" class="leave_balance">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="clearfix"></div>
                        <div class="row">
                           


                          <div class="tw-font-semibold tw-text-lg">
                                    <span><i class="fa fa-desktop"></i> Staff Interval Data </span>
                                </div>
							
                            <div class="mtop10 hide relative pull-right" id="group_by_tasks_wrapper">
                                <span><?php //echo _l('group_by_task'); ?></span>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="group_by_task" class="onoffswitch-checkbox"
                                           id="group_by_task">
                                    <label class="onoffswitch-label" for="group_by_task"></label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr class="mtop10"/>
                            </div>
                        </div>
                        <div class="clearfix"></div>
						
                        
                        <table class="table table-timesheets-report">
                            <thead>
                            <tr>
							
                                <th><?php echo "S.No."; ?></th>
								<th><?php echo "EMP ID"; ?></th>
                                <th><?php echo "Email"; ?></th>
								 <th><?php echo "Name"; ?></th>
                                <th><?php echo "Interval"; ?></th>
								<th><?php echo "Action"; ?></th>


                            </tr>
                            </thead>
                            <tbody>
								
							  <?php $i = 1; 
							 // echo count($result);
							
							  foreach ($result as $res) { ?>
							  
							  <tr>
							   <td><?php echo $i++; ?></td>
							    <td><?php echo $res->staff_identifi; ?></td>
							   <td><?php echo $res->email; ?></td>
							   	<td><?php echo $res->firstname." ".$res->lastname; ?></td>
							    <td><?php echo $res->time_interval; ?></td>
								
								<td><a href="#" class="popup btn btn-primary" data-toggle="modal" data-val="<?php echo $res->id; ?>" data-target="#exampleModal<?php echo $res->id; ?>">Change Interval</a></td>
							  </tr>
							  <?php  } ?>
                            </tbody>


                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->

<!-- End Modal Add Interval -->
<!-- Button trigger modal -->


<!-- Modal -->
<?php  foreach ($result as $res) { ?>
<div class="modal fade" id="exampleModal<?php echo $res->id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
	   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="exampleModalLabel">Interval Time</h4>
       
      </div>
      <div class="modal-body">
	  <div class="form-group">
		<label for="timeFormControlInput1">Interval Time</label>
        <input class="form-control" name="time" value="<?php echo $res->time_interval;  ?>"  id="time" type="time" step="1" />
		
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <input type="submit"  class="submit btn btn-primary" value="Save changes">
      </div>
    </div>
  </div>
</div>
<?php }?>
<?php init_tail(); ?>
<script>


   /* $('#intervalbtn').change(function(){
      //  var boolKey = $(this).val();
	   var boolKey = $('#interval').val();
	   alert(boolKey);
        $.ajax({
                url: '<?php echo base_url('admin/Emt/EmtUpdateInterval');?>',
                type: 'GET',
				data: {date: boolKey},
                success: function(res) {
					$('.loader').removeClass('hidden'); // Show loader
					 top.location.href="/admin/Emt/getImg?date="+boolKey;//redirection
                    // window.location.href = '<?php base_url('admin/Emt/getChildId');?>';
                }
            });
    });	*/

 
 var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
            ]
        });
	$('#select_file').selectpicker();	
//document.getElementById("settime").value = "00:05:00";


 $('.popup').on("click", function(){

	 var boolKey = $(this).attr("data-val");
	 $('input').on("change keyup input", function(){
		     boolKey1 = $(this).val();
			 //	 alert(boolKey1);
	//  var boolKey1 =   $(".settime").val(response.name); 
	  console.log(boolKey1);
	  $('.submit').on("click", function(){
		$.ajax({
                url: '<?php echo base_url('admin/Emt/EmtUpdateInterval');?>',
                type: 'POST',
				data: {date: boolKey,time:boolKey1},
                success: function(res) {
					console.log(res);
					$('#exampleModal'+boolKey).modal('hide');
					setTimeout(function() 
							  {
								$('.loader').removeClass('hidden'); // Show loader
								window.location.href = '<?php base_url('admin/Emt/EmtInterval');?>';
							  }, 1000);
					//$('.loader').removeClass('hidden'); // Show loader
					// top.location.href="/admin/Emt/getImg?date="+boolKey;//redirection
                    // window.location.href = '<?php base_url('admin/Emt/EmtInterval');?>';
                }
            });
	  });
	 });
 });
</script>
</body>

</html>