<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Suppress outbound email on local dev / staging hosts only.
 * Live production (production_crm): allow email even when cron uses http://127.0.0.1.
 *
 * Override in app-config-local.php:
 *   define('APP_FORCE_DISABLE_EMAIL', true);
 *   define('APP_FORCE_ENABLE_EMAIL', true);
 */
function app_is_live_production_workroom()
{
    return defined('APP_DB_NAME') && APP_DB_NAME === 'production_crm';
}

/**
 * Would outbound email be blocked if cron called http://127.0.0.1/... (same as server crontab)?
 */
function app_cron_loopback_would_block_email()
{
    $prevHost = $_SERVER['HTTP_HOST'] ?? null;
    $prevAddr = $_SERVER['SERVER_ADDR'] ?? null;
    $_SERVER['HTTP_HOST']   = '127.0.0.1';
    $_SERVER['SERVER_ADDR'] = '127.0.0.1';
    $blocked = app_should_disable_outbound_email();
    if ($prevHost !== null) {
        $_SERVER['HTTP_HOST'] = $prevHost;
    } else {
        unset($_SERVER['HTTP_HOST']);
    }
    if ($prevAddr !== null) {
        $_SERVER['SERVER_ADDR'] = $prevAddr;
    } else {
        unset($_SERVER['SERVER_ADDR']);
    }

    return $blocked;
}

function app_should_disable_outbound_email()
{
    if (defined('APP_FORCE_ENABLE_EMAIL') && APP_FORCE_ENABLE_EMAIL) {
        return false;
    }
    if (defined('APP_FORCE_DISABLE_EMAIL') && APP_FORCE_DISABLE_EMAIL) {
        return true;
    }

    if (app_is_live_production_workroom()) {
        return false;
    }

    $host = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']));
    }

    $production_hosts = ['t2gworkroom.com', 'www.t2gworkroom.com'];

    if ($host !== '') {
        if (strpos($host, 'localhost') !== false || $host === '127.0.0.1' || $host === '[::1]') {
            return true;
        }
        if (preg_match('/\.(local|test|localhost|invalid)$/i', $host)) {
            return true;
        }
        if (!in_array($host, $production_hosts, true)) {
            return true;
        }
    }

    $serverAddr = isset($_SERVER['SERVER_ADDR']) ? (string) $_SERVER['SERVER_ADDR'] : '';
    if ($serverAddr === '127.0.0.1' || $serverAddr === '::1') {
        return true;
    }

    if (PHP_SAPI === 'cli' && stripos(PHP_OS, 'WIN') === 0) {
        return true;
    }

    if ($host === '' && defined('APP_DB_NAME') && APP_DB_NAME === 'new_workroom') {
        return true;
    }

    return false;
}

if (!defined('APP_DISABLE_EMAIL')) {
    define('APP_DISABLE_EMAIL', app_should_disable_outbound_email());
}
