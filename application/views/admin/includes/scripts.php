<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once(APPPATH . 'views/admin/includes/helpers_bottom.php'); ?>

<?php hooks()->do_action('before_js_scripts_render'); ?>

<?php echo app_compile_scripts();

/**
 * Global function for custom field of type hyperlink
 */
echo get_custom_fields_hyperlink_js_function(); ?>
<?php
/**
 * Check for any alerts stored in session
 */
app_js_alerts();
?>
<?php
/**
 * Check pusher real time notifications
 */
if (get_option('pusher_realtime_notifications') == 1) { ?>
    <script type="text/javascript">
        $(function() {
            // Enable pusher logging - don't include this in production
            // Pusher.logToConsole = true;
            <?php $pusher_options = hooks()->apply_filters('pusher_options', [['disableStats' => true]]);
            if (!isset($pusher_options['cluster']) && get_option('pusher_cluster') != '') {
                $pusher_options['cluster'] = get_option('pusher_cluster');
            }
            ?>
            var pusher_options = <?php echo json_encode($pusher_options); ?>;
            var pusher = new Pusher("<?php echo get_option('pusher_app_key'); ?>", pusher_options);
            var channel = pusher.subscribe('notifications-channel-<?php echo get_staff_user_id(); ?>');
            channel.bind('notification', function(data) {
                fetch_notifications();
            });
        });
    </script>
<?php } ?>
<?php app_admin_footer(); ?>

<script>
    function extractTime(element) {
        const timestring = element.textContent.match(/(\d{2}:\d{2}:\d{2} (AM|PM))/)[0];
        return timestring;
    }

    function convertTo24HourFormat(timestring) {
        let [time, modifier] = timestring.split(' ');
        let [hrs, mins, secs] = time.split(':');

        hrs = parseInt(hrs, 10);
        if (modifier === 'PM' && hrs !== 12) {
            hrs += 12;
        } else if (modifier === 'AM' && hrs === 12) {
            hrs = 0;
        }
        return `${hrs.toString().padStart(2,'0')}:${mins}:${secs}`;
    }

    function parseTimeString(timestring) {
        const [hrs, mins, secs] = timestring.split(':').map(Number);
        return {
            hrs,
            mins,
            secs
        };
    }

    function convertToSeconds({
        hrs,
        mins,
        secs
    }) {
        return hrs * 60 * 60 + mins * 60 + secs;
    }

    function formatDifference(totalseconds) {
        const hrs = Math.floor(totalseconds / 3600);
        totalseconds %= 3600;
        const mins = Math.floor(totalseconds / 60);
        const secs = totalseconds % 60;

        console.log(`${hrs}:${mins}:${secs}`);
        return hrs;
    }

    function calculateTimeDifference(startTime, endTime) {
        const start = parseTimeString(startTime);
        const end = parseTimeString(endTime);

        const startInSeconds = convertToSeconds(start);
        const endInSeconds = convertToSeconds(end);

        const diffInSeconds = Math.abs(endInSeconds - startInSeconds);

        return formatDifference(diffInSeconds);
    }

    function getCurrentTimeString() {
        const now = new Date();
        let hrs = now.getHours();
        const mins = now.getMinutes().toString().padStart(2, '0');
        const secs = now.getSeconds().toString().padStart(2, '0');

        const modifier = hrs >= 12 ? 'PM' : 'AM';

        hrs = hrs % 12 || hrs;

        // console.log(`${hrs}:${mins}:${secs} ${modifier}`);
        return `${hrs}:${mins}:${secs} ${modifier}`;
    }

    // Attach the event listener when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('timesheets-form-check-out');
        form.addEventListener('submit', handleCheckout);
    });

    function handleCheckout(event) {
        const checkInTimeElement = document.querySelector('.alert-success');
        const checkInTime = extractTime(checkInTimeElement);
        const checkOutTime = getCurrentTimeString();

        const checkInTimeIn24hrs = convertTo24HourFormat(checkInTime);
        const checkOutTimeIn24hrs = convertTo24HourFormat(checkOutTime);
        const diff = calculateTimeDifference(checkInTimeIn24hrs, checkOutTimeIn24hrs);

        if (diff < 9) {
            var warning = $("#checkout-template-warning").html()
            event.preventDefault();
            system_popup({
                message: "",
                content: warning,
            });
            return false;
        }
    }
</script>