<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!is_staff_logged_in() || !function_exists('can_view_own_pedma') || !can_view_own_pedma()) {
    return;
} ?>
<style>
#pedmaFullscreenAckModal .modal-dialog {
    width: 96%;
    max-width: 960px;
    margin: 2vh auto;
}
#pedmaFullscreenAckModal .modal-content {
    min-height: 92vh;
    display: flex;
    flex-direction: column;
}
#pedmaFullscreenAckModal .modal-body {
    flex: 1;
    overflow-y: auto;
}
#pedmaFullscreenAckModal .pedma-ack-feedback {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    min-height: 180px;
    max-height: 45vh;
    overflow: auto;
}
#pedmaFullscreenAckModal .modal-backdrop {
    opacity: 0.85;
}
</style>
<div class="modal fade" id="pedmaFullscreenAckModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="pedmaFullscreenAckLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#141e46;color:#fff;">
                <h3 class="modal-title" id="pedmaFullscreenAckLabel" style="margin:0;color:#fff;">Monthly Evaluation - PEDMA</h3>
            </div>
            <div class="modal-body">
                <p class="text-muted">Please review your performance feedback and choose an action below.</p>
                <div class="row">
                    <div class="col-md-4">
                        <label>Month</label>
                        <input type="text" class="form-control" id="pedmaAckMonthLabel" readonly>
                        <input type="hidden" id="pedmaAckMonthKey">
                    </div>
                    <div class="col-md-4">
                        <label>Year</label>
                        <input type="text" class="form-control" id="pedmaAckYearLabel" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Score</label>
                        <input type="text" class="form-control" id="pedmaAckScoreLabel" readonly>
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <label>Overall Feedback</label>
                    <div class="pedma-ack-feedback" id="pedmaAckFeedbackPreview"></div>
                </div>
            </div>
            <div class="modal-footer" style="text-align:center;">
                <button type="button" class="btn btn-danger btn-lg" id="pedmaAckNeedMeetingBtn" style="min-width:160px;margin-right:10px;">
                    Need Meeting
                </button>
                <button type="button" class="btn btn-success btn-lg" id="pedmaAckSubmitBtn" style="min-width:180px;">
                    I Acknowledge
                </button>
            </div>
        </div>
    </div>
</div>
<?php
hooks()->add_action('app_admin_footer', 'pedma_fullscreen_ack_script');
if (!function_exists('pedma_fullscreen_ack_script')) {
    function pedma_fullscreen_ack_script()
    {
        ?>
<script>
(function() {
    var pedmaAckQueue = [];
    var pedmaAckBusy = false;
    var PEDMA_ACK_INTERVAL_MS = 3 * 60 * 60 * 1000; // 3 hours
    var PEDMA_ACK_STAFF_ID = <?php echo (int) get_staff_user_id(); ?>;
    var PEDMA_ACK_STORAGE_KEY = 'pedma_ack_last_shown_' + PEDMA_ACK_STAFF_ID;

    function pedmaAckCsrfData(base) {
        base = base || {};
        if (typeof csrfData !== 'undefined') {
            base[csrfData.token_name] = csrfData.hash;
        }
        return base;
    }

    function readAckShownMap() {
        try {
            var raw = localStorage.getItem(PEDMA_ACK_STORAGE_KEY);
            var parsed = raw ? JSON.parse(raw) : {};
            return (parsed && typeof parsed === 'object') ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function writeAckShownMap(map) {
        try {
            localStorage.setItem(PEDMA_ACK_STORAGE_KEY, JSON.stringify(map || {}));
        } catch (e) {}
    }

    function shouldShowPendingItem(item) {
        var monthKey = item && item.month_key ? String(item.month_key) : '';
        if (!monthKey) {
            return true;
        }
        var map = readAckShownMap();
        var lastShown = parseInt(map[monthKey], 10) || 0;
        if (!lastShown) {
            return true;
        }
        return (Date.now() - lastShown) >= PEDMA_ACK_INTERVAL_MS;
    }

    function markPendingItemShown(monthKey) {
        if (!monthKey) {
            return;
        }
        var map = readAckShownMap();
        map[String(monthKey)] = Date.now();
        writeAckShownMap(map);
    }

    function clearPendingItemShown(monthKey) {
        if (!monthKey) {
            return;
        }
        var map = readAckShownMap();
        delete map[String(monthKey)];
        writeAckShownMap(map);
    }

    function showNextPedmaAck() {
        if (pedmaAckBusy || !pedmaAckQueue.length) {
            return;
        }
        pedmaAckBusy = true;
        var item = pedmaAckQueue[0];
        $('#pedmaAckMonthKey').val(item.month_key || '');
        $('#pedmaAckMonthLabel').val(item.month_label || '');
        $('#pedmaAckYearLabel').val(item.year_label || '');
        $('#pedmaAckScoreLabel').val((item.score || '-') + ((String(item.score || '').indexOf('%') >= 0) ? '' : '%'));
        $('#pedmaAckFeedbackPreview').html(item.overall_feedback || '<em>No overall feedback</em>');
        $('#pedmaAckNeedMeetingBtn, #pedmaAckSubmitBtn').prop('disabled', false);
        markPendingItemShown(item.month_key);
        $('#pedmaFullscreenAckModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    }

    function finishPedmaAck(removeCurrent) {
        if (removeCurrent && pedmaAckQueue.length) {
            var done = pedmaAckQueue.shift();
            if (done && done.month_key) {
                clearPendingItemShown(done.month_key);
            }
        }
        pedmaAckBusy = false;
        $('#pedmaFullscreenAckModal').modal('hide');
        setTimeout(showNextPedmaAck, 400);
    }

    function postPedmaAck(url, successMessage) {
        var month = $('#pedmaAckMonthKey').val();
        if (!month) {
            if (typeof alert_float === 'function') {
                alert_float('warning', 'Invalid report month.');
            }
            return;
        }
        $('#pedmaAckNeedMeetingBtn, #pedmaAckSubmitBtn').prop('disabled', true);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: pedmaAckCsrfData({ month: month }),
            success: function(response) {
                if (response && response.success) {
                    if (typeof alert_float === 'function') {
                        alert_float('success', response.message || successMessage);
                    }
                    finishPedmaAck(true);
                } else {
                    if (typeof alert_float === 'function') {
                        alert_float('danger', (response && response.message) ? response.message : 'Action failed.');
                    }
                    $('#pedmaAckNeedMeetingBtn, #pedmaAckSubmitBtn').prop('disabled', false);
                }
            },
            error: function() {
                if (typeof alert_float === 'function') {
                    alert_float('danger', 'Action failed. Please try again.');
                }
                $('#pedmaAckNeedMeetingBtn, #pedmaAckSubmitBtn').prop('disabled', false);
            }
        });
    }

    function loadPendingPedmaAck() {
        $.ajax({
            url: <?php echo json_encode(admin_url('staff/pedma_pending_ack')); ?>,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && Array.isArray(response.pending) && response.pending.length) {
                    // Only show again if never shown, or last shown >= 3 hours ago
                    pedmaAckQueue = response.pending.filter(shouldShowPendingItem);
                    showNextPedmaAck();
                }
            }
        });
    }

    function bindPedmaAckButtons() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        var $ = jQuery;
        if ($('#pedmaFullscreenAckModal').data('bound')) {
            return;
        }
        $('#pedmaFullscreenAckModal').data('bound', true);

        $('#pedmaAckSubmitBtn').on('click', function() {
            postPedmaAck(<?php echo json_encode(admin_url('staff/pedma_acknowledge_feedback')); ?>, 'Feedback accepted.');
        });
        $('#pedmaAckNeedMeetingBtn').on('click', function() {
            postPedmaAck(<?php echo json_encode(admin_url('staff/pedma_need_meeting')); ?>, 'Meeting request sent.');
        });

        loadPendingPedmaAck();
    }

    if (window.deferAfterjQueryLoaded) {
        window.deferAfterjQueryLoaded.push(bindPedmaAckButtons);
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(bindPedmaAckButtons);
    } else {
        document.addEventListener('DOMContentLoaded', bindPedmaAckButtons);
        window.addEventListener('load', bindPedmaAckButtons);
    }
})();
</script>
        <?php
    }
}
?>
