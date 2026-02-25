<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 	init_head(); ?>
<style>
	  .loader {
        position: fixed;
        z-index: 99;
        top: 0;
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
	#idimage.img{
		 width: 210px !important;
	}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

<div id="wrapper" class="leave_balance">
    <div class="content">
<img src="https://drive.usercontent.google.com/download?id=1fTxuTK_r_orF_d0-qvSyNn2ojdXd6ZiM&authuser=0">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
						<div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
					</div>
                        
                        <div class="clearfix"></div>
<div class="w3-row-padding" >

 <?php $i = 1; 
							  foreach ($res->files as $file) {  ?>
							 
  <div class="w3-container w3-third">
   <img id="idimage" src="https://lh3.googleusercontent.com/d/<?php echo $file->id; ?>" style="width:100%;cursor:pointer;opacity: 1;" 
    onclick="onClick(this)" class="w3-hover-opacity" allow="autoplay">

  </div>
  <?php  } ?>
</div>

<div id="modal01" class="w3-modal" onclick="this.style.display='none'">
  <span class="w3-button w3-hover-red w3-xlarge w3-display-topright">&times;</span>
  <div class="w3-modal-content w3-animate-zoom">
    <img id="img01" style="width:100%">
  </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
}
</script>
 
                           
                
<?php init_tail(); ?>
<script>


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
</body>

</html>