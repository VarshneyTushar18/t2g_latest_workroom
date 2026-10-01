<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!is_staff_logged_in() || !function_exists('can_evaluate_pedma') || !can_evaluate_pedma()) {
    return;
}
?>
<style>
#pedmaEvalReminderModal .modal-dialog {
    width: 92%;
    max-width: 720px;
    margin: 8vh auto;
}
#pedmaEvalReminderModal .modal-content {
    border-radius: 8px;
    overflow: hidden;
}
#pedmaEvalReminderModal .pedma-eval-list {
    max-height: 280px;
    overflow: auto;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
}
#pedmaEvalReminderModal .pedma-eval-list li {
    margin-bottom: 4px;
}
</style>
<div class="modal fade" id="pedmaEvalReminderModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#b45309;color:#fff;">
                <h4 class="modal-title" style="margin:0;color:#fff;">
                    <i class="fa fa-bell"></i> PEDMA Evaluation Reminder
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="pedmaEvalReminderIntro">
                    Please complete pending PEDMA evaluations for your team.
                </p>
                <p><strong>Month:</strong> <span id="pedmaEvalReminderMonth">-</span></p>
                <p><strong>Pending (not filled):</strong> <span id="pedmaEvalReminderCount">0</span> team member(s)</p>
                <div class="pedma-eval-list">
                    <ul id="pedmaEvalReminderList"></ul>
                </div>
                <p class="text-muted" style="margin-top:12px;margin-bottom:0;">
                    This reminder appears once per day on Workroom until evaluations are published (emails stop after the 10th).
                </p>
            </div>
            <div class="modal-footer" style="text-align:center;">
                <button type="button" class="btn btn-default" id="pedmaEvalReminderLaterBtn">Remind me later</button>
                <a href="<?php echo admin_url('staff/pedma_admin'); ?>" class="btn btn-warning" id="pedmaEvalReminderOpenBtn">
                    Open PEDMA Evaluation
                </a>
            </div>
        </div>
    </div>
</div>
<?php
hooks()->add_action('app_admin_footer', 'pedma_eval_reminder_popup_script');
if (!function_exists('pedma_eval_reminder_popup_script')) {
    function pedma_eval_reminder_popup_script()
    {
        ?>
<script>
(function () {
    var STAFF_ID = <?php echo (int) get_staff_user_id(); ?>;
    var STORAGE_KEY = 'pedma_eval_reminder_last_shown_' + STAFF_ID;
    var ENDPOINT = <?php echo json_encode(admin_url('staff/pedma_eval_pending_reminder')); ?>;

    function todayKey() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function shouldShowNow() {
        try {
            return localStorage.getItem(STORAGE_KEY) !== todayKey();
        } catch (e) {
            return true;
        }
    }

    function markShown() {
        try {
            localStorage.setItem(STORAGE_KEY, todayKey());
        } catch (e) {}
    }

    function openModal(data) {
        var monthLabel = data.month_label || data.month || '-';
        var pending = data.pending || [];
        $('#pedmaEvalReminderMonth').text(monthLabel);
        $('#pedmaEvalReminderCount').text(pending.length);
        var $list = $('#pedmaEvalReminderList').empty();
        pending.forEach(function (item) {
            var label = item.name || ('Staff #' + item.staffid);
            if (item.staff_identifi) {
                label += ' (#' + item.staff_identifi + ')';
            }
            $list.append($('<li/>').text(label));
        });
        $('#pedmaEvalReminderIntro').text(
            'These team members still do not have a published PEDMA evaluation for ' + monthLabel + '. Please complete them.'
        );
        markShown();
        $('#pedmaEvalReminderModal').modal('show');
    }

    function checkPending() {
        if (!shouldShowNow()) {
            return;
        }
        $.ajax({
            url: ENDPOINT,
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (!resp || !resp.success || !resp.show || !resp.pending || !resp.pending.length) {
                    return;
                }
                openModal(resp);
            }
        });
    }

    $(function () {
        $('#pedmaEvalReminderLaterBtn').on('click', function () {
            markShown();
            $('#pedmaEvalReminderModal').modal('hide');
        });
        setTimeout(checkPending, 2500);
    });
})();
</script>
        <?php
    }
}
?>
