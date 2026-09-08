<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('task_templates_model');

        if (!can_manage_task_templates()) {
            access_denied('Task Templates');
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('task_templates');
        }

        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $data['title']       = _l('task_templates');
        $this->load->view('admin/task_templates/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data  = $this->input->post();
            $items = isset($data['items']) ? $data['items'] : [];
            unset($data['items']);

            $payload = [
                'name'       => isset($data['name']) ? $data['name'] : '',
                'templateid' => isset($data['templateid']) ? $data['templateid'] : '',
                'items'      => $items,
            ];

            $validation = $this->task_templates_model->validate_items($items);
            if ($validation !== true) {
                echo json_encode([
                    'success' => false,
                    'message' => $validation,
                ]);
                die;
            }

            if ($payload['templateid'] == '') {
                $insert_id = $this->task_templates_model->add($payload);
                $success   = $insert_id ? true : false;
                $message   = $success ? _l('added_successfully', _l('task_template')) : '';
                $id        = $insert_id;
            } else {
                $success = $this->task_templates_model->edit($payload);
                $message = $success ? _l('updated_successfully', _l('task_template')) : '';
                $id      = $payload['templateid'];
            }

            echo json_encode([
                'success' => $success,
                'message' => $message,
                'id'      => $id,
            ]);
        }
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('task_templates'));
        }

        $response = $this->task_templates_model->delete($id);
        if ($response) {
            set_alert('success', _l('deleted', _l('task_template')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('task_template_lowercase')));
        }

        redirect(admin_url('task_templates'));
    }

    public function get($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $template = $this->task_templates_model->get($id);
        if (!$template) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['success' => false]);
            die;
        }

        echo json_encode([
            'success'  => true,
            'template' => $template,
            'items'    => $template->items,
        ]);
    }

    public function list_all()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        echo json_encode([
            'success'   => true,
            'templates' => array_map(function ($template) {
                $template['task_count'] = total_rows(db_prefix() . 'task_template_items', ['template_id' => $template['id']]);
                return $template;
            }, $this->task_templates_model->get()),
        ]);
    }
}

