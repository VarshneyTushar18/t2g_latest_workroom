<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="staffSuggestionModal" tabindex="-1" role="dialog" aria-labelledby="staffSuggestionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="staffSuggestionModalLabel"><?php echo _l('suggestion_box'); ?></h4>
            </div>
            <form id="staff-suggestion-form" method="post" action="<?php echo admin_url('suggestions/submit'); ?>">
                <div class="modal-body">
                    <p class="text-muted"><?php echo _l('suggestion_box_help'); ?></p>
                    <div class="form-group">
                        <label for="suggestion_subject"><?php echo _l('suggestion_subject'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="suggestion_subject" name="subject" maxlength="255" required placeholder="<?php echo _l('suggestion_subject_placeholder'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="suggestion_message"><?php echo _l('suggestion_message'); ?> <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="suggestion_message" name="message" rows="5" required placeholder="<?php echo _l('suggestion_message_placeholder'); ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="suggestion-submit-btn"><?php echo _l('suggestion_submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
hooks()->add_action('app_admin_footer', 'staff_suggestion_box_script');
if (!function_exists('staff_suggestion_box_script')) {
    function staff_suggestion_box_script()
    {
        ?>
<script>
(function() {
    function bindStaffSuggestionForm() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        var $ = jQuery;
        var $form = $('#staff-suggestion-form');
        if (!$form.length || $form.data('suggestion-bound')) {
            return;
        }
        $form.data('suggestion-bound', true);

        $form.on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#suggestion-submit-btn');
            $btn.prop('disabled', true).text(<?php echo json_encode(_l('wait_text')); ?>);

            var data = {
                subject: $('#suggestion_subject').val(),
                message: $('#suggestion_message').val()
            };
            if (typeof csrfData !== 'undefined') {
                data[csrfData.token_name] = csrfData.hash;
            }

            $.ajax({
                url: <?php echo json_encode(admin_url('suggestions/submit')); ?>,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response && response.success) {
                        alert_float('success', response.message);
                        $('#staffSuggestionModal').modal('hide');
                        $form[0].reset();
                    } else {
                        alert_float('danger', (response && response.message) ? response.message : <?php echo json_encode(_l('suggestion_submit_failed')); ?>);
                    }
                },
                error: function(xhr) {
                    var msg = <?php echo json_encode(_l('suggestion_submit_failed')); ?>;
                    if (xhr && (xhr.status === 403 || xhr.status === 419)) {
                        msg = 'Session expired. Please refresh the page and try again.';
                    }
                    alert_float('danger', msg);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(<?php echo json_encode(_l('suggestion_submit')); ?>);
                }
            });
        });
    }

    if (window.deferAfterjQueryLoaded) {
        window.deferAfterjQueryLoaded.push(bindStaffSuggestionForm);
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(bindStaffSuggestionForm);
    } else {
        document.addEventListener('DOMContentLoaded', bindStaffSuggestionForm);
        window.addEventListener('load', bindStaffSuggestionForm);
    }
})();
</script>
        <?php
    }
}
?>
