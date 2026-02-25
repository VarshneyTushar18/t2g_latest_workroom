<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Ultimate Orange Theme
Description: Ultimate Orange Theme for Perfex CRM
Version: 2.4.0
Author: Dweb Digital Solutions
Author URI: https://dweb.digital
Requires at least: 2.2.2
*/
define('ULTIMATE_ORANGE_THEME', 'ultimate_orange_theme');
define('ULTIMATE_ORANGE_THEME_CSS', module_dir_path(ULTIMATE_ORANGE_THEME, 'assets/css/theme_styles.css'));
$CI = &get_instance();
register_activation_hook(ULTIMATE_ORANGE_THEME, 'ultimate_orange_theme_activation_hook');
function ultimate_orange_theme_activation_hook(){
	require(__DIR__ . '/install.php');
}
$CI->load->helper(ULTIMATE_ORANGE_THEME . '/ultimate_orange_theme');