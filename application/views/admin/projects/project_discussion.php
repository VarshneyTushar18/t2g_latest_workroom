<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Project Discussion Modal -->
<div class="modal fade" id="discussion" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open(admin_url('projects/discussion'), ['id' => 'discussion_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
          <span class="edit-title"><?php echo _l('edit_discussion'); ?></span>
          <span class="add-title"><?php echo _l('new_project_discussion'); ?></span>
        </h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <?php echo form_hidden('project_id', $project->id); ?>
            <?php echo render_input('subject', 'project_discussion_subject'); ?>

            <div class="form-group" app-field-wrapper="description">

			<div class="form-group">
			<label for="description" class="control-label"><?php echo _l('description'); ?></label>
			<textarea name="description" id="description" class="form-control tinymce" rows="6" placeholder="<?php echo _l('description'); ?>"></textarea>
		  </div>

            </div>

            <div class="form-group" app-field-wrapper="datecreated">
              <label for="datecreated" class="control-label">Date and Time</label>
              <input type="datetime-local" id="datecreated" name="datecreated" class="form-control">
            </div>

            <div class="checkbox checkbox-primary">
              <input type="checkbox" name="show_to_customer" checked id="show_to_customer">
              <label for="show_to_customer"><?php echo _l('project_discussion_show_to_customer'); ?></label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#discussion_form">
          <?php echo _l('submit'); ?>
        </button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>


