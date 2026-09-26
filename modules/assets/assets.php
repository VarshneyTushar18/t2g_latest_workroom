<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Asset Management
Module URI: https://codecanyon.net/item/assets-management-module-for-perfex-crm/25615418
Description: Asset management module, allocation, recovery, depreciation, asset status
Version: 1.1.0
Requires at least: 2.3.*
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('ASSETS_MODULE', 'assets');
define('ASSETS_PATH', 'modules/assets/uploads/');
define('ASSETS_UPLOAD_FOLDER', module_dir_path(ASSETS_MODULE, 'uploads'));

// Local installs often lack vendor/ and fail Envato domain checks — don't hard-crash or auto-deactivate.
$assets_vendor = __DIR__ . '/vendor/autoload.php';
$assets_is_local = (
    (defined('APP_BASE_URL') && (stripos(APP_BASE_URL, 'localhost') !== false || stripos(APP_BASE_URL, '127.0.0.1') !== false))
    || (isset($_SERVER['HTTP_HOST']) && (stripos($_SERVER['HTTP_HOST'], 'localhost') !== false || stripos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false))
);
if (file_exists($assets_vendor)) {
    require_once $assets_vendor;
    if (!$assets_is_local) {
        modules\assets\core\Apiinit::the_da_vinci_code(ASSETS_MODULE);
        modules\assets\core\Apiinit::ease_of_mind(ASSETS_MODULE);
    }
}
hooks()->add_action('admin_init', 'assets_permissions');
hooks()->add_action('admin_init', 'assets_module_schema_update', 1);
hooks()->add_action('admin_init', 'assets_module_init_menu_items');
hooks()->add_action('app_admin_head', 'assets_add_head_components');

/**
 * Injects needed CSS.
 */
function assets_add_head_components()
{
    $CI = &get_instance();
    echo '<link href="'.base_url('modules/assets/css/style.css').'?v='.$CI->app_scripts->core_version().'"  rel="stylesheet" type="text/css" />';
}

function assets_module_schema_update()
{
    $CI = &get_instance();
    if ($CI->db->table_exists(db_prefix() . 'assets') && !$CI->db->field_exists('status_override', db_prefix() . 'assets')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . "assets` ADD `status_override` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`");
    }
    if ($CI->db->table_exists(db_prefix() . 'assets') && !$CI->db->table_exists(db_prefix() . 'asset_sales')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'asset_sales` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `asset_id` INT(11) NOT NULL,
          `asset_name` VARCHAR(255) NOT NULL,
          `asset_code` VARCHAR(100) NOT NULL,
          `quantity` INT(11) NOT NULL,
          `sale_date` DATETIME NOT NULL,
          `selling_price` DECIMAL(15,2) NOT NULL,
          `buyer_name` VARCHAR(255) NOT NULL,
          `buyer_company` VARCHAR(255) NULL,
          `buyer_email` VARCHAR(255) NULL,
          `buyer_phone` VARCHAR(50) NULL,
          `buyer_address` TEXT NULL,
          `handler_name` VARCHAR(255) NOT NULL,
          `handler_contact` VARCHAR(100) NULL,
          `handler_department` VARCHAR(255) NULL,
          `payment_method` VARCHAR(100) NOT NULL,
          `payment_reference` VARCHAR(255) NULL,
          `handover_location` VARCHAR(255) NULL,
          `notes` TEXT NULL,
          `created_by` INT(11) NOT NULL,
          `created_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`),
          KEY `asset_id` (`asset_id`)
        )');
    }
}

// Register activation module hook
register_activation_hook(ASSETS_MODULE, 'assets_module_activation_hook');
/**
 * Load the module helper.
 */
$CI = &get_instance();

function assets_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__.'/install.php';
}

// Register language files, must be registered if the module is using languages
register_language_files(ASSETS_MODULE, [ASSETS_MODULE]);

$CI = &get_instance();
$CI->load->helper(ASSETS_MODULE.'/asset');
/**
 * Init goals module menu items in setup in admin_init hook.
 *
 * @return null
 */
function assets_module_init_menu_items()
{
    $CI = &get_instance();
    // Show for admins, staff with assets permission, or IT role
    if (has_permission('assets', '', 'view') || is_admin() || (function_exists('is_IT') && is_IT()) || (function_exists('is_super_admin') && is_super_admin()) || (function_exists('is_admin2') && is_admin2())) {
        $CI->app_menu->add_sidebar_menu_item('assets', [
            'collapse' => true,
            'name'     => 'IT Assets',
            'icon'     => 'fa fa-bank',
            'position' => 7,
        ]);
		 $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'assets_dashboard',
            'name'     => _l('dashboard'),
            // 'icon'     => 'fa fa-cogs',
            'href'     => admin_url('assets/assets_dashboard'),
            'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'assets_menu',
            'name'     => _l('assets'),
            // 'icon'     => 'fa fa-bank',
            'href'     => admin_url('assets/manage_assets'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'allocations',
            'name'     => _l('allocation'),
            // 'icon'     => 'fa fa-pencil',
            'href'     => admin_url('assets/allocation'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'evictions',
            'name'     => _l('eviction'),
            // 'icon'     => 'fa fa-pencil-square',
            'href'     => admin_url('assets/eviction'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'depreciations',
            'name'     => _l('depreciation'),
            // 'icon'     => 'fa fa-legal',
            'href'     => admin_url('assets/depreciation'),
            'position' => 5,
        ]);
        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'broken',
            'name'     => 'broken',
            // 'icon'     => 'fa fa-chain-broken',
            'href'     => admin_url('assets/broken'),
            'position' => 6,
        ]);
        $CI->app_menu->add_sidebar_children_item('assets', [
            'slug'     => 'settings',
            'name'     => _l('setting'),
            // 'icon'     => 'fa fa-cogs',
            'href'     => admin_url('assets/setting'),
            'position' => 7,
        ]);
		
		//  $CI->app_menu->add_sidebar_menu_item('emt-review', [
        //     'collapse' => true,
        //     'name'     => 'Snapshot',
        //     'icon'     => 'fa fa-desktop',
        //     'position' => 8,
        //     'badge'    => [],
        // ]);

        // $CI->app_menu->add_sidebar_children_item('emt-review', [
        //     'name'     => _l('Snapshot dashboard'),
        //     'href'     => admin_url('Emt/getEmployeeId'),
        //     'position' => 1,
        //     'badge'    => [],
        // ]);

        // $CI->app_menu->add_sidebar_children_item('emt-review', [
        //     'name'     => _l('Snapshot Interval'),
        //     'href'     => admin_url('Emt/EmtInterval'),
        //     'position' => 2,
        //     'badge'    => [],
        // ]);
		
    }
}
function assets_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view').'('._l('permission_global').')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('assets', $capabilities, _l('assets'));
}

// Inject upload folder location for assets module
hooks()->add_filter('get_upload_path_by_type', 'asset_upload_folder', 10, 2);
function asset_upload_folder($path, $type)
{
    if ('assets' == $type) {
        return ASSETS_UPLOAD_FOLDER.'/';
    }

    return $path;
}

// Add Menu In Customer Side
hooks()->add_action('customers_navigation_start', 'add_asset_menu');
function add_asset_menu()
{
    $CI = &get_instance();
    if (is_client_logged_in()) {
        $CI->load->model('assets/assets_model');
        $client_user_id                                              = $CI->session->userdata('client_user_id');
        $where["find_in_set('".$client_user_id."',`belongs_to`) <>"] = 0;
        $allocated_asset                                             = $CI->assets_model->get_clients_assign_assets('assets', $where);
        if (!empty($allocated_asset) && has_contact_permission('asset')) {
            echo '<li class="customers-nav-item-contracts">
                <a href="'.site_url('assets/client').'">'._l('assets').'</a>
            </li>';
        }
    }
}

hooks()->add_filter('get_contact_permissions', 'add_asset_permission');
function add_asset_permission($permissions)
{
    $permissions[] = [
            'id'         => 7,
            'name'       => _l('assets'),
            'short_name' => 'asset',
        ];

    return $permissions;
}


hooks()->add_action('app_init', ASSETS_MODULE.'_actLib');
function assets_actLib()
{
    // Skip Envato license phone-home on local — it deactivates the module for non-production domains.
    $is_local = (
        (defined('APP_BASE_URL') && (stripos(APP_BASE_URL, 'localhost') !== false || stripos(APP_BASE_URL, '127.0.0.1') !== false))
        || (isset($_SERVER['HTTP_HOST']) && (stripos($_SERVER['HTTP_HOST'], 'localhost') !== false || stripos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false))
    );
    if ($is_local || !file_exists(__DIR__ . '/vendor/autoload.php')) {
        return;
    }

    $CI = &get_instance();
    $CI->load->library(ASSETS_MODULE.'/Assets_aeiou');
    $envato_res = $CI->assets_aeiou->validatePurchase(ASSETS_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', ASSETS_MODULE.'_sidecheck');
function assets_sidecheck($module_name)
{
    if (ASSETS_MODULE == $module_name['system_name']) {
        modules\assets\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', ASSETS_MODULE.'_deregister');
function assets_deregister($module_name)
{
    if (ASSETS_MODULE == $module_name['system_name']) {
        delete_option(ASSETS_MODULE.'_verification_id');
        delete_option(ASSETS_MODULE.'_last_verification');
        delete_option(ASSETS_MODULE.'_product_token');
        delete_option(ASSETS_MODULE.'_heartbeat');
    }
}
