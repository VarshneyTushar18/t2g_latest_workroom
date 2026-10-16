<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sop extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sop_model');
        $this->load->model('departments_model');
    }

    public function index()
    {
        if (!is_staff_logged_in()) {
            redirect(admin_url('authentication'));
        }
        $data['title'] = _l('sop');
        $data['sops'] = $this->sop_model->get_for_user();
        $this->load->view('admin/sop/manage', $data);
    }

    public function view($id = '')
    {
        $id = (int) $id;
        $sop = $this->sop_model->get($id);
        if (!$sop) {
            blank_page(_l('sop_not_found'));
        }
        if (!is_admin() && !$this->sop_model->staff_is_recipient(get_staff_user_id(), $sop)) {
            access_denied('sop');
        }
        $this->sop_model->mark_as_read($id);
        $data['sop'] = $sop;
        $data['title'] = $sop->name;
        $this->load->view('admin/sop/view', $data);
    }

    public function send()
    {
        if (!can_create_sop()) {
            access_denied('sop');
        }
        if (!$this->input->post()) {
            redirect(admin_url('sop'));
        }

        $name = trim((string) $this->input->post('name', true));
        $message = trim((string) html_purify($this->input->post('message', false)));
        $email_departments = $this->input->post('email_departments');
        $email_staffids = $this->input->post('email_staffids');

        $email_departments = is_array($email_departments) ? $email_departments : ($email_departments ? [$email_departments] : []);
        $email_staffids = is_array($email_staffids) ? $email_staffids : ($email_staffids ? [$email_staffids] : []);

        $email_departments = array_values(array_filter(array_map('strval', $email_departments)));
        $email_staffids = array_values(array_filter(array_map('strval', $email_staffids)));

        if ($name === '') {
            set_alert('warning', _l('sop_subject_required'));
            redirect($_SERVER['HTTP_REFERER'] ?: admin_url('sop'));
        }
        if ($message === '') {
            set_alert('warning', _l('sop_body_required'));
            redirect($_SERVER['HTTP_REFERER'] ?: admin_url('sop'));
        }
        if (empty($email_departments)) {
            set_alert('warning', _l('announcement_email_departments_required'));
            redirect($_SERVER['HTTP_REFERER'] ?: admin_url('sop'));
        }
        if (empty($email_staffids)) {
            set_alert('warning', _l('sop_staff_required'));
            redirect($_SERVER['HTTP_REFERER'] ?: admin_url('sop'));
        }

        $id = $this->sop_model->add([
            'name'    => $name,
            'message' => $message,
        ], $email_departments, $email_staffids);

        if (!$id) {
            set_alert('danger', _l('sop_send_failed'));
            redirect(admin_url('sop'));
        }

        $sop = $this->sop_model->get($id);
        $send = $this->sop_model->send_sop_emails($sop, $email_departments, $email_staffids);
        if (!empty($send['sent'])) {
            set_alert('success', sprintf(_l('sop_email_sent'), (int) $send['sent']));
        } elseif (!empty($send['no_recipients'])) {
            set_alert('warning', _l('sop_email_none'));
        } else {
            set_alert('warning', _l('announcement_email_failed'));
        }

        redirect(admin_url('sop/view/' . $id));
    }

    public function staff_options()
    {
        if (!can_create_sop()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => _l('sop_create_denied')]);

            return;
        }
        header('Content-Type: application/json');
        $departments = $this->input->get('departments');
        if (!is_array($departments)) {
            $departments = $departments !== null && $departments !== '' ? [$departments] : ['all'];
        }
        echo json_encode([
            'success' => true,
            'staff'   => $this->sop_model->get_staff_options($departments),
        ]);
    }
}
