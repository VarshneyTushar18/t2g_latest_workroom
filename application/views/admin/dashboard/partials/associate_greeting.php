<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good morning';
    $emoji    = '☀️';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
    $emoji    = '🌤️';
} else {
    $greeting = 'Good evening';
    $emoji    = '🌙';
}
$staff_name = get_staff_full_name();
$job_title  = '';
if (function_exists('get_job_position_by_staffid')) {
    try {
        $position = get_job_position_by_staffid(get_staff_user_id());
        if (!empty($position)) {
            $job_title = $position;
        }
    } catch (Throwable $e) {
        log_message('error', 'Dashboard greeting job position: ' . $e->getMessage());
    }
}
?>
<section class="apex-dashboard-greeting" aria-label="Dashboard greeting">
    <div class="apex-dashboard-greeting-icon" aria-hidden="true"><?php echo $emoji; ?></div>
    <div class="apex-dashboard-greeting-text">
        <div class="apex-dashboard-greeting-title">
            <h1><?php echo html_escape($greeting . ', ' . $staff_name); ?></h1>
            <span class="apex-badge apex-badge-online">Online</span>
        </div>
        <p class="apex-dashboard-greeting-date"><?php echo html_escape(_d(date('Y-m-d'))); ?></p>
        <?php if ($job_title !== '') { ?>
        <p class="apex-dashboard-greeting-role"><?php echo html_escape($job_title); ?></p>
        <?php } ?>
    </div>
</section>
