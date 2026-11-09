<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Bio Punch Merge
Description: Loads the biometric punch merge fix. The model file itself is not writable from the web.
Version: 1.0.1
Requires at least: 2.3.0
Author: Workroom
*/

function bio_punch_merge_preload()
{
    $target = APPPATH . 'models/Biometric_model.php';
    $live = @file_get_contents($target);
    if (is_string($live) && strpos($live, 'function merge_punch_record_strings') !== false) {
        return;
    }

    if (class_exists('Biometric_model', false)) {
        return;
    }

    if (!class_exists('CI_Model', false)) {
        require_once BASEPATH . 'core/Model.php';
    }

    if (!class_exists('App_Model', false)) {
        require_once APPPATH . 'core/App_Model.php';
    }

    require_once __DIR__ . '/Biometric_model.php';
}

try {
    bio_punch_merge_preload();
} catch (Throwable $e) {
    log_message('error', 'bio_punch_merge preload failed: ' . $e->getMessage());
}
