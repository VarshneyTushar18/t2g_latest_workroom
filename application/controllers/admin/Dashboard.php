<?php

use app\services\utilities\Str;

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
    }

    /* This is admin dashboard view */
    public function index()
    {
        close_setup_menu();
        $this->load->model('departments_model');
        $this->load->model('todo_model');
        $data['departments'] = $this->departments_model->get();

        $data['todos'] = $this->todo_model->get_todo_items(0);
        // Only show last 5 finished todo items
        $this->todo_model->setTodosLimit(5);
        $data['todos_finished']            = $this->todo_model->get_todo_items(1);
        $data['upcoming_events_next_week'] = $this->dashboard_model->get_upcoming_events_next_week();
        $data['upcoming_events']           = $this->dashboard_model->get_upcoming_events();
        $data['title']                     = _l('dashboard_string');

        $data['activity_log']  = $this->misc_model->get_activity_log();

        $data['projects_status_stats'] = json_encode($this->dashboard_model->projects_status_stats());
        $data['leads_status_stats']    = json_encode($this->dashboard_model->leads_status_stats());
        $data['google_ids_calendars']  = $this->misc_model->get_google_calendar_ids();
        $bodyclass = 'dashboard invoices-total-manual hrms-ui';
        $this->load->helper('apex_workhub');
        $data['use_apex_admin_team_dashboard']  = t2g_apex_workhub_admin_team_user();
        $data['use_apex_associate_dashboard']   = t2g_apex_workhub_dashboard_user();
        $data['use_apex_manager_dashboard']     = t2g_apex_workhub_manager_user() && !$data['use_apex_admin_team_dashboard'];
        if ($data['use_apex_associate_dashboard']) {
            $bodyclass .= ' associate-dashboard apex-workhub';
            if ($data['use_apex_manager_dashboard']) {
                $bodyclass .= ' apex-workhub-manager';
            }
            if (!empty($data['use_apex_admin_team_dashboard'])) {
                $bodyclass .= ' apex-workhub-admin-team';
            }
        }
        $data['bodyclass'] = $bodyclass;
        $this->load->model('announcements_model');
        $data['staff_announcements']             = $this->announcements_model->get();
        $data['total_undismissed_announcements'] = $this->announcements_model->get_total_undismissed_announcements();

        $this->load->model('projects_model');
        $data['projects_activity'] = $this->projects_model->get_activity('', hooks()->apply_filters('projects_activity_dashboard_limit', 20));
        if (!is_staff_member()) {
            add_calendar_assets();
        }
        $this->load->model('utilities_model');
        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $data['dashboard'] = true;

        $data['user_dashboard_visibility'] = get_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility');

        if (!$data['user_dashboard_visibility']) {
            $data['user_dashboard_visibility'] = [];
        } else {
            $data['user_dashboard_visibility'] = unserialize($data['user_dashboard_visibility']);
        }
        $data['user_dashboard_visibility'] = json_encode($data['user_dashboard_visibility']);

        $data = hooks()->apply_filters('before_dashboard_render', $data);
        $this->load->view('admin/dashboard/dashboard', $data);
    }

    /* Chart weekly payments statistics on home page / ajax */
    public function weekly_payments_statistics($currency)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->dashboard_model->get_weekly_payments_statistics($currency));
            die();
        }
    }

    /* Chart monthly payments statistics on home page / ajax */
    public function monthly_payments_statistics($currency)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->dashboard_model->get_monthly_payments_statistics($currency));
            die();
        }
    }

    public function ticket_widget($type)
    {
        $data['tickets_report'] = (new \app\services\TicketsReportByStaff())->filterBy($type);
        $this->load->view('admin/dashboard/widgets/tickets_report_table', $data);
    }

    /**
     * Manager WorkHub — team tab data (deferred from home page load).
     */
    public function apex_manager_team_data()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }
        $this->load->helper('apex_workhub');
        if (!t2g_apex_workhub_team_dashboard_user()) {
            show_404();
        }
        if (!function_exists('t2g_apex_gather_manager_team_dashboard')) {
            show_404();
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $payload = t2g_apex_gather_manager_team_dashboard();
            $payload['deferred'] = false;
            $html    = t2g_apex_render_manager_team_dashboard($payload);
            $json    = json_encode([
                'data' => $payload,
                'html' => $html,
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            if ($json === false) {
                throw new RuntimeException('Team dashboard JSON encode failed: ' . json_last_error_msg());
            }
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output($json);
        } catch (Throwable $e) {
            log_message('error', 'apex_manager_team_data: ' . $e->getMessage());
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'error' => 'Team dashboard could not be built.',
                    'detail' => ENVIRONMENT !== 'production' ? $e->getMessage() : '',
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}');
        }
    }
}