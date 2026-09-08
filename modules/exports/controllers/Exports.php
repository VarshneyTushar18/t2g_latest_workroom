<?php

/**
 * CSV Export Controller
 * @property CSVExport $CSVExport
 * @property Exports_module $exports_module
 */
class Exports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!(function_exists('can_export_table_data') ? can_export_table_data() : is_admin())) {
            access_denied('Export');
        }
        $this->load->library(EXPORTS_MODULE_NAME . '/exports_module');
    }

    public function index()
    {
        if ($this->input->post()) {
            $this->exports_module->export($this->input->post());
        }

        $data['features'] = $this->exports_module->getFeaturesWithNames();
        $data['title'] = _l('csv_exports');
        $this->load->view(EXPORTS_MODULE_NAME . '/manage', $data);
    }
}
