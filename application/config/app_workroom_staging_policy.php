<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MilesWeb / internal staging only — do not deploy this file to AWS live (t2gworkroom.com).
 * Loaded from app-config.php when present.
 */
if (!defined('APP_WORKROOM_LICENSE_BYPASS')) {
    define('APP_WORKROOM_LICENSE_BYPASS', true);
}

// Block real outbound mail on MilesWeb staging (DB name production_crm would otherwise allow SES).
if (!defined('APP_FORCE_DISABLE_EMAIL')) {
    define('APP_FORCE_DISABLE_EMAIL', true);
}

function app_workroom_skip_greentech_module_license()
{
    return defined('APP_WORKROOM_LICENSE_BYPASS') && APP_WORKROOM_LICENSE_BYPASS;
}
