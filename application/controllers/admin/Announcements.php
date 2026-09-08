<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Announcements extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('announcements_model');
        $this->load->model('roles_model');
        $this->load->model('departments_model');
    }

    /* List all announcements */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('announcements');
        }
        $data['title'] = _l('announcements');
        $this->load->view('admin/announcements/manage', $data);
    }

    /* Edit announcement or add new if passed id */
    public function announcement($id = '')
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['message'] = html_purify($this->input->post('message', false));

            $email_roles = [];
            if (isset($data['email_roles'])) {
                $email_roles = is_array($data['email_roles']) ? $data['email_roles'] : [$data['email_roles']];
                unset($data['email_roles']);
            }
            $email_roles = array_values(array_filter(array_map('strval', $email_roles)));

            $email_departments = [];
            if (isset($data['email_departments'])) {
                $email_departments = is_array($data['email_departments']) ? $data['email_departments'] : [$data['email_departments']];
                unset($data['email_departments']);
            }
            $email_departments = array_values(array_filter(array_map('strval', $email_departments)));

            if (empty($email_departments)) {
                set_alert('warning', _l('announcement_email_departments_required'));
                redirect($this->uri->uri_string());
            }

            if (empty($email_roles)) {
                set_alert('warning', _l('announcement_email_roles_required'));
                redirect($this->uri->uri_string());
            }

            if (trim(strip_tags((string) $data['message'])) === '') {
                set_alert('warning', _l('announcement_message') . ' is required.');
                redirect($this->uri->uri_string());
            }

            $data['email_roles'] = in_array('all', $email_roles, true) ? 'all' : implode(',', $email_roles);
            $data['email_departments'] = in_array('all', $email_departments, true) ? 'all' : implode(',', $email_departments);

            if ($id == '') {
                $id = $this->announcements_model->add($data);
                if ($id) {
                    $announcement = $this->announcements_model->get($id);
                    $send_result  = $this->announcements_model->send_announcement_emails($announcement, $email_roles, $email_departments);
                    $this->_set_send_alert($send_result, true);
                    redirect(admin_url('announcements/view/' . $id));
                }
            } else {
                $success = $this->announcements_model->update($data, $id);
                $announcement = $this->announcements_model->get($id);
                $send_result  = $this->announcements_model->send_announcement_emails($announcement, $email_roles, $email_departments);
                if ($success) {
                    $this->_set_send_alert($send_result, false);
                } else {
                    $this->_set_send_alert($send_result, false);
                }
                redirect(admin_url('announcements/view/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('announcement_lowercase'));
        } else {
            $data['announcement'] = $this->announcements_model->get($id);
            $title                = _l('edit', _l('announcement_lowercase'));
        }
        $data['roles']       = $this->roles_model->get();
        $data['departments'] = $this->departments_model->get();
        $data['title']       = $title;
        $this->load->view('admin/announcements/announcement', $data);
    }

    private function _set_send_alert($send_result, $is_new)
    {
        if (!empty($send_result['sent']) && (int) $send_result['sent'] > 0) {
            set_alert('success', sprintf(_l('announcement_email_sent'), (int) $send_result['sent']));
            return;
        }
        if (!empty($send_result['no_recipients'])) {
            set_alert('warning', _l('announcement_email_none'));
            return;
        }
        set_alert('warning', _l('announcement_email_failed'));
    }

    public function view($id)
    {
        if (is_staff_member()) {
            $announcement = $this->announcements_model->get($id);
            if (!$announcement) {
                blank_page(_l('announcement_not_found'));
            }
            $data['announcement']         = $announcement;
            $data['recent_announcements'] = $this->announcements_model->get('', [
                'announcementid !=' => $id,
            ], 4);
            $data['title'] = $announcement->name;
            $this->load->view('admin/announcements/view', $data);
        }
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('announcements'));
        }
        if (!is_admin()) {
            access_denied('Announcement');
        }
        $response = $this->announcements_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('announcement')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('announcement_lowercase')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}
