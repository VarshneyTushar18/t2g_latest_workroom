<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!function_exists('t2g_apex_workhub_html')) {
    $CI = &get_instance();
    $CI->load->helper('apex_workhub');
}
if (!function_exists('t2g_apex_workhub_dashboard_user') || !t2g_apex_workhub_dashboard_user()) {
    return;
}
$html = t2g_apex_workhub_html();
if ($html === '' || $html === null) {
    echo '<div class="alert alert-danger mtop20">WorkHub dashboard did not render. Check application logs or contact support.</div>';
} else {
    echo $html;
}
?>
