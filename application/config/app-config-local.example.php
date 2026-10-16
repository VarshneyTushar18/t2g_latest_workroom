<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Copy to app-config-local.php (gitignored) for your machine.
 *
 * Typical XAMPP: MariaDB on port 3307, user root, empty password.
 * If tables show "doesn't exist in engine", use MySQL 8 instead (often port 3300)
 * or re-import a .sql dump into XAMPP MariaDB.
 */
$app_db_hostname = '127.0.0.1';
$app_db_port     = 3300;
$app_db_username = 'root';
$app_db_password = 'Testpassword@123';
$app_db_name     = 'new_workroom';
