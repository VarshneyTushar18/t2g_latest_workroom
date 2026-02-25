<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
</style>

<div id="wrapper" class="leave_balance">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
					<a href="admin/Emt/EmtDeleteId">Back</a>
                         <table class="table table-timesheets-report">
                            <thead>
                            <tr>
                                <th><?php echo "S.No."; ?></th>
                                <th><?php echo "Employee Name"; ?></th>
                                <th><?php echo "Link"; ?></th>
								<th><?php echo "Action"; ?></th>
                            </tr>
                            </thead>
                            <tbody>
							  <?php $i = 1; 
							  foreach ($res_child->files as $file) {  ?>
							  <tr>
							   <td><?php echo $i++; ?></td>
							   <td><?php echo $file->name; ?></td>
							   
							   <td><button id="input" data-attribute="<?php echo $file->id; ?>">Go To Next</button></td>
							   <td><i class="fa fa-trash text-danger"><input class="inputids" type="hidden" value="<?php echo $file->id; ?>"></i></td>
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
<?php init_tail(); ?>
<script>
  
/*
$( "button" ).each(function(index) {
    $(this).on("click", function(){
        var boolKey = $(this).attr('data-attribute');
		//alert(boolKey);
        $.ajax({
                url: '<?php echo base_url('admin/Emt/EmtDeleteDate?date=');?>'+boolKey,
                type: 'GET',
				data: {date: boolKey},
                success: function(res) {
					console.log(res);
					// top.location.href="/admin/Emt/getChildId?date="+boolKey;//redirection
                     window.location.href = '<?php base_url('admin/Emt/getChildId');?>';
                }
            });
    });
});*/
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
	  var timesheetsTable = $('.table-timesheets-report');


        timesheetsTable.DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
            ]
        });
		
		
</script>
</body>

</html>