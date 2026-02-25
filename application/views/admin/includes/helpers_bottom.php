<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once(APPPATH . 'views/admin/includes/modals/post_likes.php'); ?>
<?php include_once(APPPATH . 'views/admin/includes/modals/post_comment_likes.php'); ?>
<div id="event"></div>
<div id="newsfeed" class="animated fadeIn hide" <?php if ($this->session->flashdata('newsfeed_auto')) {
                                                  echo 'data-newsfeed-auto';
                                                } ?>>
</div>
<!-- Task modal view -->
<div class="modal fade task-modal-single" id="task-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog <?php echo get_option('task_modal_class'); ?>">
    <div class="modal-content data">

    </div>
  </div>
</div>

<!--Add/edit task modal-->
<div id="_task"></div>

<!-- Lead Data Add/Edit-->
<div class="modal fade lead-modal" id="lead-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog <?php echo get_option('lead_modal_class'); ?>">
    <div class="modal-content data">

    </div>
  </div>
</div>

<div id="timers-logout-template-warning" class="hide">
  <h2 class="bold"><?php echo _l('timers_started_confirm_logout'); ?></h2>
  <hr />
  <a href="<?php echo admin_url('authentication/logout'); ?>" class="btn btn-danger"><?php echo _l('confirm_logout'); ?></a>
</div>

<div id="checkout-template-warning" class="hide">
  <h2 class="bold"><?php echo '9 hrs not completed yet. Do you still want to Check out?'; ?></h2>
  <hr />
  <button class="btn btn-danger btn-lg" onclick="checkOutwarning()"><?php echo "Yes"; ?></button>
  <button class="btn btn-success btn-lg" onclick="closeCheckOutWarning()"><?php echo "No"; ?></button>

</div>

<!--Lead convert to customer modal-->
<div id="lead_convert_to_customer"></div>

<!--Lead reminder modal-->
<div id="lead_reminder_modal"></div>

<script>
  function checkOutwarning() {
    const form = document.getElementById('timesheets-form-check-out');
    form.submit();
  }

  function closeCheckOutWarning() {
    $("body").removeClass("system-popup");
    $('.system-popup').remove();
  }
</script>