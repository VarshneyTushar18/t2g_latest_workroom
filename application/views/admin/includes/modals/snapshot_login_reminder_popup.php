<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!is_staff_logged_in()) {
    return;
}
?>
<style>
#snapshotLoginReminderModal {
    z-index: 10060 !important;
}
#snapshotLoginReminderModal + .modal-backdrop,
body > .modal-backdrop.snapshot-login-backdrop {
    z-index: 10050 !important;
}
#snapshotLoginReminderModal .modal-dialog {
    width: 92%;
    max-width: 640px;
    margin: 10vh auto;
}
#snapshotLoginReminderModal .modal-header {
    background: #1e40af;
    color: #fff;
}
#snapshotLoginReminderModal .modal-title {
    margin: 0;
    color: #fff;
}
</style>
<div class="modal fade" id="snapshotLoginReminderModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-desktop"></i> Snapshot login required
                </h4>
            </div>
            <div class="modal-body">
                <p id="snapshotLoginReminderMessage" class="tw-mb-2"></p>
                <p class="text-muted tw-mb-0" style="font-size:13px;">
                    This reminder appears every 5 minutes on Workroom until the Snapshot application logs in and starts uploading.
                </p>
            </div>
            <div class="modal-footer" style="text-align:center;">
                <button type="button" class="btn btn-primary" id="snapshotLoginReminderOkBtn">OK, I will log in</button>
            </div>
        </div>
    </div>
</div>
<?php
if (!function_exists('snapshot_login_reminder_popup_script')) {
    function snapshot_login_reminder_popup_script()
    {
        ?>
<script>
(function () {
    if (typeof jQuery === 'undefined') {
        return;
    }

    var STAFF_ID = <?php echo (int) get_staff_user_id(); ?>;
    var SNOOZE_KEY = 'snapshot_login_reminder_snooze_' + STAFF_ID;
    var ENDPOINT = <?php echo json_encode(admin_url('staff/snapshot_login_reminder')); ?>;
    var POLL_MS = 5 * 60 * 1000;

    function isSnoozed() {
        try {
            var until = parseInt(localStorage.getItem(SNOOZE_KEY) || '0', 10);
            return until > Date.now();
        } catch (e) {
            return false;
        }
    }

    function snoozeFiveMinutes() {
        try {
            localStorage.setItem(SNOOZE_KEY, String(Date.now() + POLL_MS));
        } catch (e) {}
    }

    function clearSnooze() {
        try {
            localStorage.removeItem(SNOOZE_KEY);
        } catch (e) {}
    }

    function isModalVisible() {
        var $m = $('#snapshotLoginReminderModal');
        return $m.length && ($m.hasClass('in') || $m.hasClass('show') || $m.is(':visible'));
    }

    function dismissSystemPopup() {
        $('body').removeClass('system-popup');
        $('#system-popup').remove();
    }

    function showSystemPopupFallback(message) {
        if (typeof system_popup !== 'function') {
            window.alert(message);
            return;
        }
        if ($('#system-popup').length) {
            return;
        }
        var safeMsg = $('<div>').text(message).html();
        var content = '<p style="color:#333;text-align:left;margin-bottom:16px;">' + safeMsg + '</p>';
        content += '<button type="button" class="btn btn-primary" id="snapshotLoginReminderOkBtnFallback">OK, I will log in</button>';
        system_popup({ message: 'Snapshot login required', content: content });
        $('#snapshotLoginReminderOkBtnFallback').on('click', function () {
            snoozeFiveMinutes();
            dismissSystemPopup();
        });
    }

    function showReminderModal(data) {
        var msg = data.message || 'Please log in to the Snapshot application on your computer.';
        var $modal = $('#snapshotLoginReminderModal');
        if (!$modal.length) {
            showSystemPopupFallback(msg);
            return;
        }

        $('#snapshotLoginReminderMessage').text(msg);
        $modal.appendTo('body');
        $('.modal-backdrop').not('.snapshot-login-backdrop').removeClass('snapshot-login-backdrop');

        try {
            $modal.modal({ backdrop: 'static', keyboard: false, show: true });
        } catch (e) {
            showSystemPopupFallback(msg);
            return;
        }

        setTimeout(function () {
            if (!isModalVisible()) {
                $modal.modal('hide');
                showSystemPopupFallback(msg);
            } else {
                $('.modal-backdrop').last().addClass('snapshot-login-backdrop');
            }
        }, 350);
    }

    function hideReminderModal() {
        var $modal = $('#snapshotLoginReminderModal');
        if ($modal.length) {
            $modal.modal('hide');
        }
        dismissSystemPopup();
    }

    function checkReminder() {
        $.ajax({
            url: ENDPOINT,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function (resp) {
                if (!resp || !resp.success) {
                    return;
                }
                if (!resp.show) {
                    clearSnooze();
                    hideReminderModal();
                    return;
                }
                if (isSnoozed()) {
                    return;
                }
                if (isModalVisible() || $('#system-popup').length) {
                    return;
                }
                showReminderModal(resp);
            },
            error: function (xhr) {
                if (window.console && console.warn) {
                    console.warn('Snapshot reminder check failed', xhr.status);
                }
            }
        });
    }

    $(function () {
        $('#snapshotLoginReminderOkBtn').on('click', function () {
            snoozeFiveMinutes();
            hideReminderModal();
        });
        checkReminder();
        setInterval(checkReminder, POLL_MS);
    });
})();
</script>
        <?php
    }
}
?>
