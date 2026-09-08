<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
#staffSuggestionModal {
    z-index: 2000 !important;
}
#staffSuggestionModal + .modal-backdrop,
.modal-backdrop.suggestion-backdrop {
    z-index: 1990 !important;
}
</style>
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
<script>
(function() {
    function openStaffSuggestionModal(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var modalEl = document.getElementById('staffSuggestionModal');
        if (!modalEl) {
            alert('Suggestion popup is missing. Please refresh.');
            return false;
        }
        // Move modal to body so nested header/nav CSS cannot hide it
        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            jQuery(modalEl).modal('show');
        } else {
            modalEl.style.display = 'block';
            modalEl.className += ' in show';
            modalEl.setAttribute('aria-hidden', 'false');
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade in show suggestion-backdrop';
            document.body.appendChild(backdrop);
        }
        return false;
    }

    function bindOpenButton() {
        document.addEventListener('click', function(e) {
            var t = e.target;
            var btn = null;
            while (t && t !== document) {
                if (t.id === 'openStaffSuggestionModal') {
                    btn = t;
                    break;
                }
                t = t.parentNode;
            }
            if (btn) {
                openStaffSuggestionModal(e);
            }
        }, true);
    }

    function bindSubmit() {
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
                        if (typeof alert_float === 'function') {
                            alert_float('success', response.message);
                        } else {
                            alert(response.message);
                        }
                        $('#staffSuggestionModal').modal('hide');
                        $form[0].reset();
                    } else {
                        var fail = (response && response.message) ? response.message : <?php echo json_encode(_l('suggestion_submit_failed')); ?>;
                        if (typeof alert_float === 'function') {
                            alert_float('danger', fail);
                        } else {
                            alert(fail);
                        }
                    }
                },
                error: function(xhr) {
                    var msg = <?php echo json_encode(_l('suggestion_submit_failed')); ?>;
                    if (xhr && (xhr.status === 403 || xhr.status === 419)) {
                        msg = 'Session expired. Please refresh the page and try again.';
                    }
                    if (typeof alert_float === 'function') {
                        alert_float('danger', msg);
                    } else {
                        alert(msg);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text(<?php echo json_encode(_l('suggestion_submit')); ?>);
                }
            });
        });
    }

    bindOpenButton();
    if (typeof jQuery !== 'undefined') {
        jQuery(bindSubmit);
    } else {
        document.addEventListener('DOMContentLoaded', bindSubmit);
        window.addEventListener('load', bindSubmit);
    }
    window.openStaffSuggestionModal = openStaffSuggestionModal;
})();
</script>
<?php
hooks()->add_action('app_admin_footer', 'staff_suggestion_box_script');
if (!function_exists('staff_suggestion_box_script')) {
    function staff_suggestion_box_script()
    {
        // Main logic is inline above for reliability.
    }
}
?>
