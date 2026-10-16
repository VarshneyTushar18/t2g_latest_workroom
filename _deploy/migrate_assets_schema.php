<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($mysqli->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

function column_exists($mysqli, $table, $column)
{
    $table = $mysqli->real_escape_string($table);
    $column = $mysqli->real_escape_string($column);
    $res = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return $res && $res->num_rows > 0;
}

function table_exists($mysqli, $table)
{
    $table = $mysqli->real_escape_string($table);
    $res = $mysqli->query("SHOW TABLES LIKE '{$table}'");
    return $res && $res->num_rows > 0;
}

if (!column_exists($mysqli, 'tblassets', 'status_override')) {
    $mysqli->query("ALTER TABLE `tblassets` ADD `status_override` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`");
    echo "added status_override\n";
} else {
    echo "status_override exists\n";
}

if (!table_exists($mysqli, 'tblasset_sales')) {
    $mysqli->query('CREATE TABLE `tblasset_sales` (
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
    echo "created tblasset_sales\n";
} else {
    echo "tblasset_sales exists\n";
}

echo "done\n";
