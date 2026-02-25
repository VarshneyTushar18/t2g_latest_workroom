<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminer extends CI_Controller {
    public function index() {

        $adminerPath = APPPATH . '../adminer.php';

        // Check if the file exists
        if (file_exists($adminerPath)) {
            // Include or require the file
            require_once($adminerPath);
        } else {
            // Handle the case where the file doesn't exist
            echo('Adminer file not found. ' . $adminerPath);
        }
    }
}
