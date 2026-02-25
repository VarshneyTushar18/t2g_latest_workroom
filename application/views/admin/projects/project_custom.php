<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
#dynamicFilterContainer .filter-row {
    display: inline-block;
    margin: 10px;
}
#dynamicFilterContainer label {
    display: inline-block;
    margin-right: 10px;
}
#dynamicFilterContainer input {
    display: inline-flex;
    width: 150px;
}
</style>
<style>
.loader-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

</style>
<div class="col-md-5 text-left tw-my-2">
    <?php if (has_permission('tasks', '', 'create')) { ?>
        <a href="#"
           onclick="new_task_from_esclation(undefined,'project',<?php echo $project->id; ?>); return false;"
           class="btn btn-primary">
           <i class="fa-regular fa-plus tw-mr-1"></i> <?php echo _l('Create Esclation'); ?>
        </a>
    <?php } ?>
</div>

<!-- Escalation Modal -->
<div class="modal fade" id="escalationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php echo form_open('projects/save_escalation', ['id' => 'escalationForm']); ?>
      <input type="hidden" name="project_id" id="project_id">
      <div class="modal-content">
    <div id="loader" class="loader-overlay dt-loader hide" >
		  <div class="spinner-border text-primary" role="status">
			<span class="sr-only">Loading...</span>
		  </div>
		</div>
		<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">
                   Create Escalation</h4>
            </div>
        <div class="modal-body row">
          <div class="form-group col-md-12">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required>
          </div>
          <div class="form-group col-md-12">
			<label for="description" class="control-label"><?php echo _l('description'); ?></label>
			<textarea name="description" id="description" class="form-control tinymce" rows="6" placeholder="<?php echo _l('description'); ?>"></textarea>
		  </div>
          <div class="form-group col-md-6">
            <label>Assign To</label>
          <select name="assigned_to[]" class="form-control selectpicker"
        data-width="100%" 
        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
        multiple data-live-search="true">
    <?php foreach ($staff as $member) { 
        $first = isset($member['firstname']) ? $member['firstname'] : '';
        $last  = isset($member['lastname']) ? $member['lastname'] : '';
        $staff_id = isset($member['staffid']) ? $member['staffid'] : '';
    ?>
        <option value="<?php echo $staff_id; ?>" 
            <?php if ((get_option('new_task_auto_assign_current_member') == '1') && get_staff_user_id() == $staff_id) {
                echo 'selected';
            } ?>>
            <?php echo $first . ' ' . $last; ?>
        </option>
    <?php } ?>
</select>


          </div>
          <div class="form-group col-md-6">
            <label>Followers</label>
            <select name="followers[]" class="form-control selectpicker"
        data-width="100%" 
        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
        multiple data-live-search="true">
             <?php foreach ($staff as $member) { 
        $first = isset($member['firstname']) ? $member['firstname'] : '';
        $last  = isset($member['lastname']) ? $member['lastname'] : '';
        $staff_id = isset($member['staffid']) ? $member['staffid'] : '';
    ?>
        <option value="<?php echo $staff_id; ?>" 
            <?php if ((get_option('new_task_auto_assign_current_member') == '1') && get_staff_user_id() == $staff_id) {
                echo 'selected';
            } ?>>
            <?php echo $first . ' ' . $last; ?>
        </option>
    <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Create</button>
         <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        </div>
      </div>
 <?php echo form_close(); ?>
  </div>
</div>


<?php
$start = $this->input->get('escalation_page') ?? 0;
$sno = $start + 1;
?>
<div class="col-md-12">
<table class="table table-bordered">
  <thead>
    <tr>
       <th>S.No.</th>
      <th>Subject</th>
      
      <th>Created By</th>
      <th>Created At</th>
      <th>Assignees</th>
      <th>Followers</th>
	  <th>description</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($escalations) > 0) {
      foreach ($escalations as $i => $e) { ?>
        <tr>
          <td><?= $sno++ ?></td>
          <td><?= htmlspecialchars($e['subject']) ?></td>
          
          <td><?= $e['firstname'] . ' ' . $e['lastname'] ?></td>
          <td><?= _dt($e['created_at']) ?></td>
          <td><?= implode(', ', $e['assignees']) ?></td>
          <td><?= implode(', ', $e['followers']) ?></td>
		  <td><button 
    class="btn btn-link view-description" 
    data-description="<?= htmlspecialchars($e['description']) ?>"
  >
    View
  </button></td>
        </tr>
      <?php }
    } else { ?>
      <tr><td colspan="7" class="text-center">No escalation entries found.</td></tr>
    <?php } ?>
  </tbody>
</table>

<ul class="pagination pagination-wrapper text-center">
  <?= $pagination_links ?>
</ul>


</div>
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">
                   Escalation Description </h4>
            </div>
      <div class="modal-body">
        <p id="modalDescriptionContent"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('close'); ?></button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

</body>
<script>
  var admin_url = '<?= admin_url() ?>';
 
function new_task_from_esclation(id, type, project_id) {
  $('#project_id').val(project_id);
  $('#escalationForm')[0].reset();
  $('.selectpicker').selectpicker('refresh');
  $('#escalationModal').modal('show');
}
$(document).ready(function() {
  $('#escalationForm').on('submit', function(e) {
  e.preventDefault();
  //$('#loader').removeClass('show');
  //$('#loader').removeClass('hide');
  
  var formData = $(this).serialize();
console.log(formData);
  $.post(admin_url + 'projects/save_escalation', formData, function(response) {
    //$('#loader').hide();
	
	$('#loader').removeClass('hide');
	//console.log(response);
    if (response.success) {
      alert_float('success', response.message);
      setTimeout(function() {
        window.location.href = response.redirect_url;
      }, 1500);
    } else {
      alert_float('danger', response.message || 'Something went wrong.');
    }
  }, 'json').fail(function() {
    
	 $('#loader').addClass('show');
    alert_float('danger', 'Failed to submit escalation. Please try again.');
  });
});

});
$(document).on('click', '.view-description', function () {
  var description = $(this).data('description');
  $('#modalDescriptionContent').html(nl2br(description));
  $('#descriptionModal').modal('show');
});

function nl2br(str) {
  return str.replace(/\n/g, '<br>');
}

</script>

</html>
