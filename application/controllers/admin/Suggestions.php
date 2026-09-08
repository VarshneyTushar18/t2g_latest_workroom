<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suggestions extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('suggestions_model');
    }

    public function index()
    {
        if (!is_admin() && !is_admin2() && !is_super_admin() && !is_HR() && !in_the_department('HR')) {
            access_denied('suggestions');
        }

        // Universal list: every staff suggestion, with submitter name/email
        $data['title']       = _l('staff_suggestions');
        $data['suggestions'] = $this->suggestions_model->get();
        $this->load->view('admin/suggestions/manage', $data);
    }

    public function submit()
    {
        header('Content-Type: application/json');

        if (strtoupper($this->input->method()) !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => _l('suggestion_submit_failed'),
            ]);
            return;
        }

        $subject = trim((string) $this->input->post('subject'));
        $message = trim((string) $this->input->post('message'));

        if ($subject === '' || $message === '') {
            echo json_encode([
                'success' => false,
                'message' => _l('suggestion_required_fields'),
            ]);
            return;
        }

        $id = $this->suggestions_model->add([
            'staffid' => get_staff_user_id(),
            'subject' => $subject,
            'message' => $message,
        ]);

        echo json_encode([
            'success' => (bool) $id,
            'message' => $id ? _l('suggestion_submitted_successfully') : _l('suggestion_submit_failed'),
        ]);
    }

    public function mark_status($id, $status)
    {
        if (!is_admin() && !is_admin2() && !is_super_admin() && !is_HR() && !in_the_department('HR')) {
            access_denied('suggestions');
        }

        $this->suggestions_model->update_status($id, $status);
        set_alert('success', _l('updated_successfully', _l('staff_suggestion')));
        redirect(admin_url('suggestions'));
    }

    public function delete($id)
    {
        if (!is_admin() && !is_admin2() && !is_super_admin()) {
            access_denied('suggestions');
        }

        if ($this->suggestions_model->delete($id)) {
            set_alert('success', _l('deleted', _l('staff_suggestion')));
        }

        redirect(admin_url('suggestions'));
    }
}
