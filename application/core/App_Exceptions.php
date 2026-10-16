<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Production-friendly errors: show maintenance page instead of stack traces.
 */
class App_Exceptions extends CI_Exceptions
{
    protected function shouldShowMaintenance($template)
    {
        if (is_cli()) {
            return false;
        }
        if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'production') {
            return false;
        }

        return $template !== 'error_404';
    }

    protected function renderMaintenancePage($statusCode = 503)
    {
        $path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'maintenance.php';
        if (!is_file($path)) {
            return parent::show_error('Error', 'Service temporarily unavailable.', 'error_general', $statusCode);
        }

        if (!headers_sent()) {
            set_status_header($statusCode);
        }

        $http_code = $statusCode;
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }

    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        if ($this->shouldShowMaintenance($template)) {
            $code = (int) $status_code;
            if ($code < 500) {
                $code = 503;
            }
            echo $this->renderMaintenancePage($code);
            exit(1);
        }

        return parent::show_error($heading, $message, $template, $status_code);
    }

    public function show_exception($exception)
    {
        if ($this->shouldShowMaintenance('error_general')) {
            log_message('error', 'Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
            echo $this->renderMaintenancePage(503);
            exit(1);
        }

        parent::show_exception($exception);
    }

    public function show_php_error($severity, $message, $filepath, $line)
    {
        $fatal = in_array($severity, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true);
        if ($fatal && $this->shouldShowMaintenance('error_general')) {
            log_message('error', 'PHP Error: ' . $message . ' in ' . $filepath . ':' . $line);
            echo $this->renderMaintenancePage(503);
            exit(1);
        }

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production' && !is_cli()) {
            $this->log_exception($severity, $message, $filepath, $line);

            return;
        }

        parent::show_php_error($severity, $message, $filepath, $line);
    }
}
