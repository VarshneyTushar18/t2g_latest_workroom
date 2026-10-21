<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Whether outbound email must be suppressed (local dev, staging hosts, loopback, etc.).
 * Live production: only t2gworkroom.com over non-loopback, non-Windows CLI.
 *
 * Override in app-config-local.php:
 *   define('APP_FORCE_DISABLE_EMAIL', true);
 *   define('APP_FORCE_ENABLE_EMAIL', true);  // use with care
 */
function app_should_disable_outbound_email()
{
    if (defined('APP_FORCE_ENABLE_EMAIL') && APP_FORCE_ENABLE_EMAIL) {
        return false;
    }
    if (defined('APP_FORCE_DISABLE_EMAIL') && APP_FORCE_DISABLE_EMAIL) {
        return true;
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
