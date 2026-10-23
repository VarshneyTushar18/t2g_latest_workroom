<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
	public function __construct()
    {
        parent::__construct();
    }
 
    public function test()
    {
        // allow only CLI or cron access (security)
        if (!$this->input->is_cli_request()) {
            echo "Direct access not allowed";
            return;
        }
 
        $time = date("Y-m-d H:i:s");
 
        // write to log file
        $message = "Cron executed at: ".$time."\n";
 
        $file = APPPATH.'logs/cron_log.txt';
        file_put_contents($file, $message, FILE_APPEND);
 
        echo "Cron Ran Successfully at ".$time.PHP_EOL;
    }

	/** One-off resend leave decision email (CLI only). */
	public function resend_leave_decision_mail($leave_id = 0, $approved = 0)
	{
		if (!$this->input->is_cli_request()) {
			echo "Direct access not allowed\n";
			return;
		}
		$leave_id = (int) $leave_id;
		if ($leave_id <= 0) {
			echo "Usage: php index.php cron resend_leave_decision_mail {leave_id} {0|1}\n";
			return;
		}
		$this->load->model('timesheets/timesheets_model');
		$comment = '';
		$row = $this->db->where('leave_id', $leave_id)->order_by('id', 'DESC')->limit(1)->get(db_prefix() . 'leave_comment')->row();
		if ($row) {
			$comment = (string) ($approved ? ($row->approval_comment ?? '') : ($row->rejection_comment ?? ''));
		}
		$ok = $this->timesheets_model->notify_leave_decision_to_employee($leave_id, ((int) $approved) === 1, $comment, 1);
		echo ($ok ? 'SENT' : 'FAILED') . " leave #{$leave_id}\n";
	}
	
    public function index($key = '')
    {
        update_option('cron_has_run_from_cli', 1);

        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $last_cron_run = get_option('last_cron_run');
        $seconds = hooks()->apply_filters('cron_functions_execute_seconds', 300);

        if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
            $this->load->model('cron_model');
            $this->cron_model->run();
        }
    }

    public function workroom_report($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->workroom_report_run();
    }

    public function it_asset_report($key = '')
    {
        $this->load->model('cron_model');
        $this->cron_model->it_asset_report_run();
    }

    public function hr_recruit_report_run($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->hr_recruit_report_run();

    }

    public function update_leave_balance($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->update_leave_balance();

    }
    public function biweekly_document_report($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->biweekly_document_report();

    }

    public function birthday_anniversary_email_run($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->birthday_anniversary_email_run();
    }

    /*public function send_weekly_attendance_mail_run($key = '')
    {

        $this->load->model('cron_model');
        $this->cron_model->send_weekly_attendance_mail_run();
    }*/
    public function send_utilization_report_day()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_utilization_report_day();
	}

    public function send_daily_timesheet_report_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_timesheet_report_run();
	}
	  public function send_daily_timesheet_report_run_less_then_9hour()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_timesheet_report_run_less_then_9hour();
	}
	  public function send_daily_timesheet_report_run_less_then_10hour()
	{
		
		$this->load->model('cron_model');
		$this->cron_model->send_daily_timesheet_report_run_less_then_10hour();
	}
	  public function send_daily_timesheet_report_run_optimise_report()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_timesheet_report_run_optimise_report();
	}
	 public function send_daily_recrutment_report_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_run();
	}
	 public function send_daily_recrutment_report_to_hr_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_to_hr_run();
	}
	public function send_daily_recrutment_report_to_hr_manager_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_to_hr_manager_run();
	}
	public function send_daily_recrutment_report_to_ITSupport_manager_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_to_ITSupport_manager_run();
	}
	public function send_daily_recrutment_report_to_Sarabjeet_manager_run()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_to_Sarabjeet_manager_run();
	}
	public function send_daily_recrutment_report_to_PEDMA()
	{
		$this->load->model('cron_model');
		$this->cron_model->send_daily_recrutment_report_to_PEDMA();
	}
	public function staff_login_status_last_week()
	{
		$this->load->model('cron_model');
		$this->cron_model->staff_login_status_last_week();
	}
	public function send_daily_biometric_report(){
		$this->load->model('cron_model');
		$this->cron_model->get_attendance_summary_table();
	}
	public function send_daily_late_arrival_report(){
		$this->load->model('cron_model');
		$this->cron_model->send_daily_late_arrival_report();
	}

	/**
	 * Daily Snapshot not logged-in report (default: yesterday). Manual/scheduled run any time.
	 * Example: /cron/send_daily_snapshot_not_logged_in_report/YOUR_CRON_KEY
	 * Optional date segment: .../2026-10-20
	 */
	public function send_daily_snapshot_not_logged_in_report($key = '', $reportDate = '', $recipient = '')
	{
		if (defined('APP_CRON_KEY') && APP_CRON_KEY != '' && APP_CRON_KEY != $key) {
			header('HTTP/1.0 401 Unauthorized');
			die('Passed cron job key is not correct.');
		}

		$this->load->model('cron_model');
		$date = ($reportDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDate)) ? $reportDate : null;
		$opts = ['force' => true];
		if ($recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
			$opts['recipient'] = $recipient;
		}
		$result = $this->cron_model->send_daily_snapshot_not_logged_in_report($date, $opts);
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** Scheduled: morning daily report emails (break sheets + e-commerce). Crontab 02:30 UTC ≈ 8:00 IST */
	public function send_scheduled_daily_morning_reports()
	{
		$this->load->model('cron_model');
		$result = $this->cron_model->run_scheduled_morning_daily_reports();
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** Scheduled: Snapshot not-logged-in report. Crontab 07:35 UTC ≈ 1:05 PM IST */
	public function send_scheduled_daily_snapshot_report()
	{
		$this->load->model('cron_model');
		$result = $this->cron_model->run_scheduled_snapshot_daily_report();
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** Scheduled: biometric + late arrival + break (Harpreet/Sarab). Crontab 04:15 UTC ≈ 9:45 AM IST */
	public function send_scheduled_daily_afternoon_reports()
	{
		$this->load->model('cron_model');
		$result = $this->cron_model->run_scheduled_afternoon_daily_reports();
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** JSON list of daily reports and configured To/CC (for ops verification). */
	public function daily_report_recipients_catalog()
	{
		$this->load->model('cron_model');
		header('Content-Type: application/json');
		echo json_encode($this->cron_model->daily_report_recipients_catalog());
	}

	/** JSON cron health (last /cron/index run, daily send flags). */
	public function cron_health()
	{
		$this->load->model('cron_model');
		header('Content-Type: application/json');
		echo json_encode($this->cron_model->cron_health_status(), JSON_PRETTY_PRINT);
	}
	public function send_monthly_late_arrival_summary($year = null, $month = null){
		$this->load->model('cron_model');
		$this->cron_model->send_monthly_late_arrival_summary(
			$year !== null && $year !== '' ? (int) $year : null,
			$month !== null && $month !== '' ? (int) $month : null
		);
	}
	public function check_deadline_missed_projects(){
		$this->load->model('cron_model');
		$this->cron_model->weekly_deadline_missed_projects_report();
	}
	public function send_biometric_break_alerts(){
		$this->load->model('cron_model');
		$this->cron_model->send_biometric_break_alerts();
	}
	public function send_break_report_to_staff(){
		$this->load->model('cron_model');
		$this->cron_model->send_break_report_to_staff();
	}

	/** E-commerce biometric CSV to Pankaj Lohani (CC Sarabjeet). Optional ?months=3 */
	public function send_ecommerce_biometric_report_pankaj($months = 3)
	{
		$this->load->model('cron_model');
		$result = $this->cron_model->send_ecommerce_biometric_report_to_pankaj($months);
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** Daily break timing sheet to each reporting manager (default: yesterday). Optional date: d-M-Y */
	public function send_daily_team_break_timing_report($date = '')
	{
		$this->load->model('cron_model');
		$att_date = $date !== '' ? $date : null;
		$result = $this->cron_model->send_daily_team_break_timing_to_managers($att_date);
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/** E-commerce break rollup to Pankaj (default: yesterday). Optional date d-M-Y; add /force to resend same day */
	public function send_daily_ecommerce_break_timing_report($date = '', $force = '')
	{
		$this->load->model('cron_model');
		$force_resend = ($force === 'force' || $force === '1');
		$att_date = null;
		if ($date !== '' && $date !== 'force' && $date !== '1') {
			$att_date = $date;
		}
		if (($date === 'force' || $date === '1') && $force === '') {
			$force_resend = true;
		}
		$result = $this->cron_model->send_daily_ecommerce_break_timing_to_pankaj($att_date, $force_resend);
		header('Content-Type: application/json');
		echo json_encode($result);
	}

    /**
     * PEDMA evaluation email reminders for managers (days 1, 5, and 10 only).
     * Example: /cron/send_pedma_evaluation_reminders/YOUR_CRON_KEY
     */
    public function send_pedma_evaluation_reminders($key = '')
    {
        if (defined('APP_CRON_KEY') && APP_CRON_KEY != '' && APP_CRON_KEY != $key) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct.');
        }

        $this->load->model('cron_model');
        $result = $this->cron_model->send_pedma_evaluation_reminders();
        header('Content-Type: application/json');
        echo json_encode($result);
    }

	public function testing()
	{
		$fromEmail='noreply_workroom@tech2globe.com'; 
		$fromName='Tech2Globe'; 
		$messageText='Hello';
		$subject = 'Help Request';

		$message = 'Hello';

		//echo "<pre>";
		//print_r($this->email);
		//echo "</pre>";

		$this->email->from('noreply_workroom@tech2globe.com', 'Help Desk'); // MUST match SMTP domain
		$this->email->to('tech2globe@yopmail.com');               // change if needed
		//$this->email->cc(array('krniraj007@gmail.com')); 
		$this->email->reply_to($fromEmail, $fromName);
		$this->email->subject($subject);
		$this->email->message($message);

		if ($this->email->send()) {
			die('Email sent successfully.');
		} else {
			log_message('error', $this->email->print_debugger());
			print_r($this->email->print_debugger());
			die('Failed to send email.');
		}
	}

	/** Run Perfex/Workroom DB migrations to migration_version (CLI only). */
	public function upgrade_db()
	{
		if (!$this->input->is_cli_request()) {
			echo "Direct access not allowed\n";
			return;
		}

		$this->load->config('migration');
		if ($this->config->item('migration_enabled') !== true) {
			echo "Enable migration_enabled in application/config/migration.php\n";
			return;
		}

		$target = (int) $this->config->item('migration_version');
		$before = (int) $this->app->get_current_db_version();
		echo "Upgrading database from {$before} to {$target}...\n";

		$this->load->library('migration', [
			'migration_enabled'     => true,
			'migration_type'        => $this->config->item('migration_type'),
			'migration_table'       => $this->config->item('migration_table'),
			'migration_auto_latest' => $this->config->item('migration_auto_latest'),
			'migration_version'     => $target,
			'migration_path'        => $this->config->item('migration_path'),
		]);

		if (!defined('DOING_DATABASE_UPGRADE')) {
			define('DOING_DATABASE_UPGRADE', true);
		}

		if ($this->migration->current() === false) {
			echo 'Migration failed: ' . $this->migration->error_string() . "\n";
			return;
		}

		$after = (int) $this->app->get_current_db_version();
		echo "Done. Database version is now {$after}.\n";
	}

}
