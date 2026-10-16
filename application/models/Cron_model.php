<?php



use app\services\imap\Imap;

use Ddeboer\Imap\Exception\UnsupportedCharsetException;

use Ddeboer\Imap\SearchExpression;

use Ddeboer\Imap\Search\Flag\Unseen;

use app\services\imap\ConnectionErrorException;

use Ddeboer\Imap\Exception\UnexpectedEncodingException;

use Ddeboer\Imap\Exception\MessageDoesNotExistException;



defined('BASEPATH') or exit('No direct script access allowed');



define('CRON', true);



class Cron_model extends App_Model
{

    public $manually = false;



    private $lock_handle;



    private $currentImapMessage;



    public function __construct()
    {

        if (!defined('APP_DISABLE_CRON_LOCK') || defined('APP_DISABLE_CRON_LOCK') && !APP_DISABLE_CRON_LOCK) {

            register_shutdown_function([$this, '__destruct']);

            $f = fopen(get_temp_dir() . 'pcrm-cron-lock', 'w+');



            if (!$f) {

                $this->lock_handle = fopen(TEMP_FOLDER . 'pcrm-cron-lock', 'w+');

                // Again? Disable the lock

                if (!$this->lock_handle && !defined('APP_DISABLE_CRON_LOCK')) {

                    // Defined this constant manually here so the cron is able to run

                    // Used in method can_cron_run

                    define('APP_DISABLE_CRON_LOCK', true);
                }
            } else {

                $this->lock_handle = $f;
            }
        }



        parent::__construct();

        $this->load->model('emails_model');
        $this->load->model('birthday_model');


        $this->load->model('staff_model');



        register_shutdown_function(function () {

            if ($this->hasTimeoutOccurred() && $this->currentImapMessage) {

                $this->currentImapMessage->markAsSeen();
            }
        });
    }



    public function run($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }



            $this->staff_reminders();

            $this->events();

            $this->tasks_reminders();

            $this->recurring_tasks();



            $this->proposals();

            $this->invoice_overdue();

            $this->invoice_due();



            $this->estimate_expiration();

            $this->contracts_expiration_check();



            $this->autoclose_tickets();

            $this->recurring_invoices();

            $this->recurring_expenses();



            $this->auto_import_imap_tickets();

            $this->check_leads_email_integration();

            $this->delete_activity_log();

            $this->send_scheduled_emails();

            $this->delete_twocheckout_logs();

            $this->stop_task_timers();

            $this->non_billed_tasks_notification();

            $this->send_delay_ticket_mail_run();
            // check in check out auto checkout
            $this->processCheckins();

            // PEDMA evaluation reminder emails to managers (1st / 5th / 10th only; popup handles after 10th)
            $this->send_pedma_evaluation_reminders();

            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }

    public function processCheckins()
    {
        $CI = &get_instance();
        $CI->load->model('timesheets_model');
        // Calculate the timestamp 13 hours ago (13 hours cooldown)
        $thirteenHoursAgo = time() - (13 * 60 * 60);
        $thirteenHoursAgoFormatted = date('Y-m-d H:i:s', $thirteenHoursAgo);

        // Fetch the latest check-ins and check-outs for each staff member
        $sql = "SELECT staff_id, MAX(date) AS latest_checkin
                FROM tblcheck_in_out
                WHERE type_check = 1
                GROUP BY staff_id";
        $query = $this->db->query($sql);
        $latestCheckins = $query->result();

        $fixed = 0;
        foreach ($latestCheckins as $checkin) {
            $staffId = (int) $checkin->staff_id;
            $latestCheckinTimestamp = $checkin->latest_checkin;
            if ($staffId <= 0 || empty($latestCheckinTimestamp)) {
                continue;
            }

            // Auto-checkout only when THIS person's check-in is older than 13 hours
            // (not at calendar midnight for everyone).
            if ($latestCheckinTimestamp < $thirteenHoursAgoFormatted) {
                // Check if the staff member is not already checked out
                if (!$this->isUserCheckedOut($staffId, $latestCheckinTimestamp)) {
                    // Out time = check-in + 13 hours (per person), not a shared 19:00.
                    $auto_out = date('Y-m-d H:i:s', strtotime($latestCheckinTimestamp) + (13 * 3600));
                    if (strtotime($auto_out) <= strtotime($latestCheckinTimestamp)) {
                        $auto_out = date('Y-m-d H:i:s', strtotime($latestCheckinTimestamp) + (9 * 3600));
                    }

                    $checkoutData = [
                        'staff_id'   => $staffId,
                        'date'       => $auto_out,
                        'type_check' => 2,
                    ];
                    $this->db->insert(db_prefix() . 'check_in_out', $checkoutData);

                    $hours = (float) $CI->timesheets_model->get_hour($latestCheckinTimestamp, $auto_out);
                    $CI->load->helper('timesheets/timesheets');
                    $type = timesheets_attendance_code_from_hours($hours);

                    $existing = $this->db->where('staff_id', $staffId)
                        ->where('date_work', $work_date)
                        ->get(db_prefix() . 'timesheets_timesheet')
                        ->row();
                    $payload = [
                        'staff_id'  => $staffId,
                        'date_work' => $work_date,
                        'type'      => $type,
                        'value'     => $hours,
                        'add_from'  => $staffId,
                    ];
                    if ($existing) {
                        // Do not overwrite approved leave / holiday codes.
                        $keep = ['AL', 'PL', 'SL', 'HO', 'UL', 'LOP', 'CO', 'ML'];
                        if (!in_array(strtoupper((string) $existing->type), $keep, true)) {
                            $this->db->where('id', $existing->id)->update(db_prefix() . 'timesheets_timesheet', [
                                'type'  => $type,
                                'value' => $hours,
                            ]);
                        }
                    } else {
                        $this->db->insert(db_prefix() . 'timesheets_timesheet', $payload);
                    }

                    // Never invent leave applications for missed checkout.
                    $fixed++;
                }
            }
        }

        log_message('info', count($latestCheckins) . ' check-in(s) scanned; ' . $fixed . ' auto-checkout(s) without marking Absent.');
    }

    /**
     * Process auto-checkout for a specific staff member if their check-in is older than 13 hours.
     *
     * @param int $staffId
     * @return bool
     */
    public function processCheckinForStaff($staffId)
    {
        $staffId = (int) $staffId;
        if ($staffId <= 0) {
            return false;
        }

        $latest = $this->db->query(
            "SELECT MAX(date) AS latest_checkin FROM " . db_prefix() . "check_in_out WHERE type_check = 1 AND staff_id = ?",
            [$staffId]
        )->row();

        if ($latest && !empty($latest->latest_checkin)) {
            $this->load->helper('timesheets/timesheets');
            $work_date = date('Y-m-d', strtotime($latest->latest_checkin));
            $grace_hours = function_exists('timesheets_workroom_auto_checkout_hours')
                ? (int) timesheets_workroom_auto_checkout_hours($staffId, $work_date)
                : 13;
            $grace_hours = max(13, min(24, $grace_hours));
            $cutoffFormatted = date('Y-m-d H:i:s', time() - ($grace_hours * 3600));
        }

        if ($latest && !empty($latest->latest_checkin) && !empty($cutoffFormatted) && $latest->latest_checkin < $cutoffFormatted) {
            if (!$this->isUserCheckedOut($staffId, $latest->latest_checkin)) {
                $work_date = date('Y-m-d', strtotime($latest->latest_checkin));
                // Per-person: auto-out at check-in + grace window (24h WFH/remote, 13h office).
                $auto_out = date('Y-m-d H:i:s', strtotime($latest->latest_checkin) + ($grace_hours * 3600));
                if (strtotime($auto_out) <= strtotime($latest->latest_checkin)) {
                    $auto_out = date('Y-m-d H:i:s', strtotime($latest->latest_checkin) + (9 * 3600));
                }

                $this->db->insert(db_prefix() . 'check_in_out', [
                    'staff_id'   => $staffId,
                    'date'       => $auto_out,
                    'type_check' => 2,
                ]);

                $CI = &get_instance();
                $CI->load->model('timesheets_model');
                $hours = (float) $CI->timesheets_model->get_hour($latest->latest_checkin, $auto_out);
                $CI->load->helper('timesheets/timesheets');
                $type = timesheets_attendance_code_from_hours($hours);

                $existing = $this->db->where('staff_id', $staffId)
                    ->where('date_work', $work_date)
                    ->get(db_prefix() . 'timesheets_timesheet')
                    ->row();
                $payload = [
                    'staff_id'  => $staffId,
                    'date_work' => $work_date,
                    'type'      => $type,
                    'value'     => $hours,
                    'add_from'  => $staffId,
                ];
                if ($existing) {
                    $keep = ['AL', 'PL', 'SL', 'HO', 'UL', 'LOP', 'CO', 'ML'];
                    if (!in_array(strtoupper((string) $existing->type), $keep, true)) {
                        $this->db->where('id', $existing->id)->update(db_prefix() . 'timesheets_timesheet', [
                            'type'  => $type,
                            'value' => $hours,
                        ]);
                    }
                } else {
                    $this->db->insert(db_prefix() . 'timesheets_timesheet', $payload);
                }
                return true;
            }
        }

        return false;
    }

    // Function to check if a user is already checked out
    public function isUserCheckedOut($staffId, $latestCheckinTimestamp)
    {
        $this->db->where('staff_id', $staffId);
        $this->db->where('type_check', 2); // Check-out type
        $this->db->where('date >', $latestCheckinTimestamp); // Check-out after the latest check-in

        $query = $this->db->get(db_prefix() . 'check_in_out'); // Replace with your table name

        return $query->num_rows() > 0; // User is checked out if there are check-out records
    }


    public function non_billed_tasks_notification()
    {

        if (get_option('reminder_for_completed_but_not_billed_tasks') === '0') {

            return;
        }



        $tasks_reminder_notification_hour = get_option('tasks_reminder_notification_hour');

        if (!$this->shouldRunAutomations($tasks_reminder_notification_hour)) {

            return;
        }



        $daysToNotify = get_option('reminder_for_completed_but_not_billed_tasks_days');

        if (empty($daysToNotify) || $daysToNotify === '[]') {

            return;
        }



        $daysToNotify = json_decode($daysToNotify, true);

        $lastNotifiedDay = get_option('tasks_reminder_notification_last_notified_day');

        $today = date('l');



        if (!in_array($today, $daysToNotify) || $lastNotifiedDay === $today) {

            return;
        }



        $countNonBilledTasks = total_rows(db_prefix() . 'tasks', ['billable' => 1, 'billed' => 0, 'status' => Tasks_model::STATUS_COMPLETE]);



        if ($countNonBilledTasks > 0) {

            $staffToNotify = json_decode(get_option('staff_notify_completed_but_not_billed_tasks'));



            $this->db->select('email, staffid');

            $this->db->where('active', 1);

            $this->db->where_in('staffid', $staffToNotify);

            $staffToNotify = $this->db->get(db_prefix() . 'staff')->result_array();



            foreach ($staffToNotify as $staff) {

                send_mail_template('non_billed_tasks_reminder_to_staff', $staff['email'], $staff['staffid']);
            }

            update_option('tasks_reminder_notification_last_notified_day', $today);
        }
    }



    public function stop_task_timers()
    {

        $older_than_hours = get_option('automatically_stop_task_timer_after_hours');

        if ($older_than_hours == '0' || empty($older_than_hours)) {

            return;
        }



        $older_than_hours = intval($older_than_hours);

        $time_ago = strtotime(" - {$older_than_hours} hours");

        $this->db->where('end_time IS NULL');

        $this->db->where('task_id !=', '0');

        $this->db->where('start_time <=', $time_ago);

        $this->db->update(db_prefix() . 'taskstimers', [

            'end_time' => time(),

        ]);
    }



    private function delete_twocheckout_logs()
    {

        $older_than_days = hooks()->apply_filters('delete_two_checkout_log_older_than_days', 40);



        if ($older_than_days == 0 || empty($older_than_days)) {

            return;
        }



        $this->db->query('DELETE FROM ' . db_prefix() . 'twocheckout_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ' . $this->db->escape_str($older_than_days) . ' DAY);');
    }



    private function events()
    {

        // User events

        $this->db->where('isstartnotified', 0);

        $events = $this->db->get(db_prefix() . 'events')->result_array();



        $notified_users = [];

        $notificationNotifiedUsers = [];

        $all_notified_events = [];

        foreach ($events as $event) {

            $date_compare = date('Y-m-d H:i:s', strtotime('+' . $event['reminder_before'] . ' ' . strtoupper($event['reminder_before_type'])));



            if ($event['start'] <= $date_compare) {

                array_push($all_notified_events, $event['eventid']);

                array_push($notified_users, $event['userid']);



                $eventNotifications = hooks()->apply_filters('event_notifications', true);



                if ($eventNotifications) {

                    $notified = add_notification([

                        'description' => 'not_event',

                        'touserid' => $event['userid'],

                        'fromcompany' => true,

                        'link' => 'utilities/calendar?eventid=' . $event['eventid'],

                        'additional_data' => serialize([

                            $event['title'],

                        ]),

                    ]);



                    $staff = $this->staff_model->get($event['userid']);



                    send_mail_template('staff_event_notification', array_to_object($event), $staff);

                    array_push($notificationNotifiedUsers, $event['userid']);
                }
            }
        }



        // Public events

        $notified_users = array_unique($notified_users);



        $this->db->where('public', 1);



        $this->db->where('isstartnotified', 0);

        $events = $this->db->get(db_prefix() . 'events')->result_array();



        $whereStaff = 'active=1 AND is_not_staff=0';

        if (count($notified_users) > 0) {

            $whereStaff .= ' AND staffid NOT IN (' . implode(',', $notified_users) . ')';
        }



        $staff = $this->staff_model->get('', $whereStaff);



        foreach ($staff as $member) {

            foreach ($events as $event) {

                $date_compare = date('Y-m-d H:i:s', strtotime('+' . $event['reminder_before'] . ' ' . strtoupper($event['reminder_before_type'])));

                if ($event['start'] <= $date_compare) {

                    array_push($all_notified_events, $event['eventid']);



                    $eventNotifications = hooks()->apply_filters('event_notifications', true);



                    if ($eventNotifications) {

                        $notified = add_notification([

                            'description' => 'not_event_public',

                            'touserid' => $member['staffid'],

                            'fromcompany' => true,

                            'link' => 'utilities/calendar?eventid=' . $event['eventid'],

                            'additional_data' => serialize([

                                $event['title'],

                            ]),

                        ]);

                        send_mail_template('staff_event_notification', array_to_object($event), array_to_object($member));



                        array_push($notificationNotifiedUsers, $member['staffid']);
                    }
                }
            }
        }



        foreach ($all_notified_events as $id) {

            $this->db->where('eventid', $id);

            $this->db->update(db_prefix() . 'events', [

                'isstartnotified' => 1,

            ]);
        }



        pusher_trigger_notification($notificationNotifiedUsers);
    }



    private function autoclose_tickets()
    {

        $auto_close_after = get_option('autoclose_tickets_after');



        if ($auto_close_after == 0) {

            return;
        }



        $this->db->select('ticketid,lastreply,date,userid,contactid,email');

        $this->db->where('status !=', 5); // Closed

        $this->db->where('status !=', 4); // On Hold

        $this->db->where('status !=', 2); // In Progress

        $tickets = $this->db->get(db_prefix() . 'tickets')->result_array();



        $this->load->model('tickets_model');



        foreach ($tickets as $ticket) {

            $close_ticket = false;

            if (!is_null($ticket['lastreply'])) {

                $last_reply = strtotime($ticket['lastreply']);

                if ($last_reply <= strtotime('-' . $auto_close_after . ' hours')) {

                    $close_ticket = true;
                }
            } else {

                $created = strtotime($ticket['date']);

                if ($created <= strtotime('-' . $auto_close_after . ' hours')) {

                    $close_ticket = true;
                }
            }



            if ($close_ticket == true) {

                $this->db->where('ticketid', $ticket['ticketid']);

                $this->db->update(db_prefix() . 'tickets', [

                    'status' => 5,

                ]);

                if ($this->db->affected_rows() > 0) {

                    hooks()->do_action('after_ticket_status_changed', [

                        'id' => $ticket['ticketid'],

                        'status' => 5,

                    ]);



                    $isContact = false;

                    if ($ticket['userid'] != 0 && $ticket['contactid'] != 0) {

                        $email = $this->clients_model->get_contact($ticket['contactid'])->email;

                        $isContact = true;
                    } else {

                        $email = $ticket['email'];
                    }

                    $sendEmail = true;

                    if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $ticket['contactid']]) == 0) {

                        $sendEmail = false;
                    }

                    if ($sendEmail) {

                        $ticket = $this->tickets_model->get($ticket['ticketid']);

                        send_mail_template('ticket_auto_close_to_customer', $ticket, $email);
                    }
                }
            }
        }
    }



    public function contracts_expiration_check()
    {

        $contracts_auto_operations_hour = get_option('contracts_auto_operations_hour');



        if (!$this->shouldRunAutomations($contracts_auto_operations_hour)) {

            return;
        }



        $this->db->select('id,client,dateend,subject,addedfrom,not_visible_to_client,dateadded');

        $this->db->where('isexpirynotified', 0);

        $this->db->where('dateend is NOT NULL');

        $this->db->where('trash', 0);

        $contracts = $this->db->get(db_prefix() . 'contracts')->result_array();

        $now = new DateTime(date('Y-m-d'));



        $notifiedUsers = [];

        if (count($contracts) > 0) {

            $staff = $this->staff_model->get('', ['active' => 1]);
        }



        foreach ($contracts as $contract) {

            if ($contract['dateend'] > date('Y-m-d')) {

                $dateend = new DateTime($contract['dateend']);

                $diff = $dateend->diff($now)->format('%a');

                if ($diff <= get_option('contract_expiration_before')) {

                    $this->db->where('id', $contract['id']);

                    $this->db->update(db_prefix() . 'contracts', [

                        'isexpirynotified' => 1,

                    ]);



                    foreach ($staff as $member) {

                        if ($member['staffid'] == $contract['addedfrom'] || is_admin($member['staffid'])) {

                            $notified = add_notification([

                                'description' => 'not_contract_expiry_reminder',

                                'touserid' => $member['staffid'],

                                'fromcompany' => 1,

                                'fromuserid' => 0,

                                'link' => 'contracts/contract/' . $contract['id'],

                                'additional_data' => serialize([

                                    $contract['subject'],

                                ]),

                            ]);



                            if ($notified) {

                                array_push($notifiedUsers, $member['staffid']);
                            }



                            send_mail_template('contract_expiration_reminder_to_staff', $contract, $member);
                        }
                    }



                    if ($contract['not_visible_to_client'] == 0) {

                        $contacts = $this->clients_model->get_contacts($contract['client'], ['active' => 1, 'contract_emails' => 1]);

                        foreach ($contacts as $contact) {

                            $template = mail_template('contract_expiration_reminder_to_customer', $contract, $contact);



                            $merge_fields = $template->get_merge_fields();



                            $template->send();



                            if (can_send_sms_based_on_creation_date($contract['dateadded'])) {

                                $this->app_sms->trigger(SMS_TRIGGER_CONTRACT_EXP_REMINDER, $contact['phonenumber'], $merge_fields);
                            }
                        }
                    }
                }
            }
        }



        pusher_trigger_notification($notifiedUsers);
    }



    public function recurring_tasks()
    {

        $tasks_reminder_notification_hour = get_option('tasks_reminder_notification_hour');



        if (!$this->shouldRunAutomations($tasks_reminder_notification_hour)) {

            return;
        }



        hooks()->do_action('before_check_recurring_tasks');



        $this->db->select('id,addedfrom,recurring_type,repeat_every,last_recurring_date,startdate,duedate');

        $this->db->where('recurring', 1);

        $this->db->where('(cycles != total_cycles OR cycles=0)');

        $recurring_tasks = $this->db->get(db_prefix() . 'tasks')->result_array();



        foreach ($recurring_tasks as $task) {

            $type = $task['recurring_type'];

            $repeat_every = $task['repeat_every'];

            $last_recurring_date = $task['last_recurring_date'];

            $task_date = $task['startdate'];



            // Current date

            $date = new DateTime(date('Y-m-d'));

            // Check if is first recurring

            if (!$last_recurring_date) {

                $last_recurring_date = date('Y-m-d', strtotime($task_date));
            } else {

                $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
            }



            $re_create_at = date('Y-m-d', strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date)));



            if (date('Y-m-d') >= $re_create_at) {

                $copy_task_data['copy_task_followers'] = 'true';

                $copy_task_data['copy_task_checklist_items'] = 'true';

                $copy_task_data['copy_from'] = $task['id'];



                $overwrite_params = [

                    'startdate' => $re_create_at,

                    'status' => hooks()->apply_filters('recurring_task_status', 1),

                    'recurring_type' => null,

                    'repeat_every' => 0,

                    'cycles' => 0,

                    'recurring' => 0,

                    'custom_recurring' => 0,

                    'last_recurring_date' => null,

                    'is_recurring_from' => $task['id'],

                ];



                if (!empty($task['duedate'])) {

                    $dStart = new DateTime($task['startdate']);

                    $dEnd = new DateTime($task['duedate']);

                    $dDiff = $dStart->diff($dEnd);

                    $overwrite_params['duedate'] = date('Y-m-d', strtotime('+' . $dDiff->days . ' days', strtotime($re_create_at)));
                }



                $newTaskID = $this->tasks_model->copy($copy_task_data, $overwrite_params);



                if ($newTaskID) {

                    $this->db->where('id', $task['id']);

                    $this->db->update(db_prefix() . 'tasks', [

                        'last_recurring_date' => $re_create_at,

                    ]);



                    $this->db->where('id', $task['id']);

                    $this->db->set('total_cycles', 'total_cycles+1', false);

                    $this->db->update(db_prefix() . 'tasks');



                    $this->db->where('taskid', $task['id']);

                    $assigned = $this->db->get(db_prefix() . 'task_assigned')->result_array();

                    foreach ($assigned as $assignee) {

                        $assigneeId = $this->tasks_model->add_task_assignees([

                            'taskid' => $newTaskID,

                            'assignee' => $assignee['staffid'],

                        ], true);



                        if ($assigneeId) {

                            $this->db->where('id', $assigneeId);

                            $this->db->update(db_prefix() . 'task_assigned', ['assigned_from' => $task['addedfrom']]);
                        }
                    }
                }
            }
        }



        hooks()->do_action('after_check_recurring_tasks');
    }



    private function recurring_expenses()
    {

        $expenses_hour_auto_operations = get_option('expenses_auto_operations_hour');



        if (!$this->shouldRunAutomations($expenses_hour_auto_operations)) {

            return;
        }



        $this->db->where('recurring', 1);

        $this->db->where('(cycles != total_cycles OR cycles=0)');

        $recurring_expenses = $this->db->get(db_prefix() . 'expenses')->result_array();

        // Load the necessary models

        $this->load->model('invoices_model');

        $this->load->model('expenses_model');



        $_renewals_ids_data = [];

        $total_renewed = 0;



        foreach ($recurring_expenses as $expense) {

            $type = $expense['recurring_type'];

            $repeat_every = $expense['repeat_every'];

            $last_recurring_date = $expense['last_recurring_date'];

            $create_invoice_billable = $expense['create_invoice_billable'];

            $send_invoice_to_customer = $expense['send_invoice_to_customer'];

            $expense_date = $expense['date'];

            // Current date

            $date = new DateTime(date('Y-m-d'));

            // Check if is first recurring

            if (!$last_recurring_date) {

                $last_recurring_date = date('Y-m-d', strtotime($expense_date));
            } else {

                $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
            }

            $re_create_at = date('Y-m-d', strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date)));



            if (date('Y-m-d') >= $re_create_at) {

                // Ok we can repeat the expense now

                $new_expense_data = [];

                $expense_fields = $this->db->list_fields(db_prefix() . 'expenses');

                foreach ($expense_fields as $field) {

                    if (isset($expense[$field])) {

                        // We dont need the invoiceid field

                        if ($field != 'invoiceid' && $field != 'id' && $field != 'recurring_from') {

                            $new_expense_data[$field] = $expense[$field];
                        }
                    }
                }



                $new_expense_data['dateadded'] = date('Y-m-d H:i:s');

                $new_expense_data['date'] = $re_create_at;

                $new_expense_data['recurring_from'] = $expense['id'];

                $new_expense_data['addedfrom'] = $expense['addedfrom'];



                $new_expense_data['recurring_type'] = null;

                $new_expense_data['repeat_every'] = 0;

                $new_expense_data['recurring'] = 0;

                $new_expense_data['cycles'] = 0;

                $new_expense_data['total_cycles'] = 0;

                $new_expense_data['custom_recurring'] = 0;

                $new_expense_data['last_recurring_date'] = null;



                $this->db->insert(db_prefix() . 'expenses', $new_expense_data);

                $insert_id = $this->db->insert_id();

                if ($insert_id) {

                    // Get the old expense custom field and add to the new

                    $custom_fields = get_custom_fields('expenses');

                    foreach ($custom_fields as $field) {

                        $value = get_custom_field_value($expense['id'], $field['id'], 'expenses', false);

                        if ($value != '') {

                            $this->db->insert(db_prefix() . 'customfieldsvalues', [

                                'relid' => $insert_id,

                                'fieldid' => $field['id'],

                                'fieldto' => 'expenses',

                                'value' => $value,

                            ]);
                        }
                    }

                    $total_renewed++;

                    $this->db->where('id', $expense['id']);

                    $this->db->update(db_prefix() . 'expenses', [

                        'last_recurring_date' => $re_create_at,

                        // In case cron job is late use the date actually when the recurring supposed to happen

                    ]);



                    $this->db->where('id', $expense['id']);

                    $this->db->set('total_cycles', 'total_cycles+1', false);

                    $this->db->update(db_prefix() . 'expenses');



                    $sent = false;

                    $created_invoice_id = '';

                    if ($expense['create_invoice_billable'] == 1 && $expense['billable'] == 1) {

                        $invoiceid = $this->expenses_model->convert_to_invoice($insert_id, false, ['invoice_date' => $re_create_at]);

                        if ($invoiceid) {

                            $created_invoice_id = $invoiceid;

                            if ($expense['send_invoice_to_customer'] == 1) {

                                $sent = $this->invoices_model->send_invoice_to_client($invoiceid, 'invoice_send_to_customer', true);
                            }
                        }
                    }

                    $_renewals_ids_data[] = [

                        'from' => $expense['id'],

                        'renewed' => $insert_id,

                        'send_invoice_to_customer' => $expense['send_invoice_to_customer'],

                        'create_invoice_billable' => $expense['create_invoice_billable'],

                        'is_sent' => $sent,

                        'addedfrom' => $expense['addedfrom'],

                        'created_invoice_id' => $created_invoice_id,

                    ];
                }
            }
        }



        $send_recurring_expenses_email = hooks()->apply_filters('send_recurring_system_expenses_email', 'true');

        if ($total_renewed > 0 && $send_recurring_expenses_email == 'true') {

            $this->load->model('currencies_model');

            $email_send_to_by_staff_and_expense = [];

            $date = _dt(date('Y-m-d H:i:s'));

            // Get all active staff members

            $staff = $this->staff_model->get('', ['active' => 1]);

            foreach ($staff as $member) {

                $sent = false;

                load_admin_language($member['staffid']);

                $recurring_expenses_email_data = _l('not_recurring_expense_cron_activity_heading') . ' - ' . $date . '<br /><br />';

                foreach ($_renewals_ids_data as $data) {

                    if ($data['addedfrom'] == $member['staffid'] || is_admin($member['staffid'])) {

                        $unique_send = '[' . $member['staffid'] . '-' . $data['from'] . ']';

                        $sent = true;

                        // Prevent sending the email twice if the same staff is added is sale agent and is creator for this invoice.

                        if (in_array($unique_send, $email_send_to_by_staff_and_expense)) {

                            $sent = false;
                        }



                        $expense = $this->expenses_model->get($data['from']);



                        $recurring_expenses_email_data .= _l('not_recurring_expenses_action_taken_from') . ': <a href="' . admin_url('expenses/list_expenses/' . $data['from']) . '">' . $expense->category_name . (!empty($expense->expense_name) ? ' (' . $expense->expense_name . ')' : '') . '</a> - ' . _l('expense_amount') . ' ' . app_format_money($expense->amount, get_currency($expense->currency)) . '<br />';



                        $recurring_expenses_email_data .= _l('not_expense_renewed') . ' <a href="' . admin_url('expenses/list_expenses/' . $data['renewed']) . '">' . _l('id') . ' ' . $data['renewed'] . '</a>';



                        if ($data['create_invoice_billable'] == 1) {

                            $recurring_expenses_email_data .= '<br />' . _l('not_invoice_created') . ' ';

                            if (is_numeric($data['created_invoice_id'])) {

                                $recurring_expenses_email_data .= _l('not_invoice_sent_yes');

                                if ($data['send_invoice_to_customer'] == 1) {

                                    if ($data['is_sent']) {

                                        $invoice_sent = 'not_invoice_sent_yes';
                                    } else {

                                        $invoice_sent = 'not_invoice_sent_no';
                                    }

                                    $recurring_expenses_email_data .= '<br />' . _l('not_invoice_sent_to_customer', _l($invoice_sent));
                                }
                            } else {

                                $recurring_expenses_email_data .= _l('not_invoice_sent_no');
                            }
                        }

                        $recurring_expenses_email_data .= '<br /><br />';
                    }
                }

                if ($sent == true) {

                    array_push($email_send_to_by_staff_and_expense, $unique_send);

                    $this->emails_model->send_simple_email($member['email'], _l('not_recurring_expense_cron_activity_heading'), $recurring_expenses_email_data);
                }

                load_admin_language();
            }
        }
    }



    private function recurring_invoices()
    {

        $invoice_hour_auto_operations = get_option('invoice_auto_operations_hour');



        if (!$this->shouldRunAutomations($invoice_hour_auto_operations)) {

            return;
        }



        $new_recurring_invoice_action = get_option('new_recurring_invoice_action');



        $invoices_create_invoice_from_recurring_only_on_paid_invoices = get_option('invoices_create_invoice_from_recurring_only_on_paid_invoices');

        $this->load->model('invoices_model');

        $this->db->select('id,recurring,date,last_recurring_date,number,duedate,recurring_type,custom_recurring,addedfrom,sale_agent,clientid');

        $this->db->from(db_prefix() . 'invoices');

        $this->db->where('recurring !=', 0);

        $this->db->where('(cycles != total_cycles OR cycles=0)');



        if ($invoices_create_invoice_from_recurring_only_on_paid_invoices == 1) {

            // Includes all recurring invoices with paid status if this option set to Yes

            $this->db->where('status', 2);
        }

        $this->db->where('status !=', 6);

        $invoices = $this->db->get()->result_array();



        $_renewals_ids_data = [];

        $total_renewed = 0;

        foreach ($invoices as $invoice) {



            // Current date

            $date = new DateTime(date('Y-m-d'));

            // Check if is first recurring

            if (!$invoice['last_recurring_date']) {

                $last_recurring_date = date('Y-m-d', strtotime($invoice['date']));
            } else {

                $last_recurring_date = date('Y-m-d', strtotime($invoice['last_recurring_date']));
            }

            if ($invoice['custom_recurring'] == 0) {

                $invoice['recurring_type'] = 'MONTH';
            }



            $re_create_at = date('Y-m-d', strtotime('+' . $invoice['recurring'] . ' ' . strtoupper($invoice['recurring_type']), strtotime($last_recurring_date)));



            if (date('Y-m-d') >= $re_create_at) {



                // Recurring invoice date is okey lets convert it to new invoice

                $_invoice = $this->invoices_model->get($invoice['id']);

                $new_invoice_data = [];

                $new_invoice_data['clientid'] = $_invoice->clientid;

                $new_invoice_data['number'] = get_option('next_invoice_number');

                $new_invoice_data['date'] = _d($re_create_at);

                $new_invoice_data['duedate'] = null;



                if ($_invoice->duedate) {

                    // Now we need to get duedate from the old invoice and calculate the time difference and set new duedate

                    // Ex. if the first invoice had duedate 20 days from now we will add the same duedate date but starting from now

                    $dStart = new DateTime($invoice['date']);

                    $dEnd = new DateTime($invoice['duedate']);

                    $dDiff = $dStart->diff($dEnd);

                    $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . $dDiff->days . ' DAY', strtotime($re_create_at))));
                } else {

                    if (get_option('invoice_due_after') != 0) {

                        $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime($re_create_at))));
                    }
                }



                $new_invoice_data['project_id'] = $_invoice->project_id;

                $new_invoice_data['show_quantity_as'] = $_invoice->show_quantity_as;

                $new_invoice_data['currency'] = $_invoice->currency;

                $new_invoice_data['subtotal'] = $_invoice->subtotal;

                $new_invoice_data['total'] = $_invoice->total;

                $new_invoice_data['adjustment'] = $_invoice->adjustment;

                $new_invoice_data['discount_percent'] = $_invoice->discount_percent;

                $new_invoice_data['discount_total'] = $_invoice->discount_total;

                $new_invoice_data['discount_type'] = $_invoice->discount_type;

                $new_invoice_data['terms'] = clear_textarea_breaks($_invoice->terms);

                $new_invoice_data['sale_agent'] = $_invoice->sale_agent;

                // Since version 1.0.6

                $new_invoice_data['billing_street'] = clear_textarea_breaks($_invoice->billing_street);

                $new_invoice_data['billing_city'] = $_invoice->billing_city;

                $new_invoice_data['billing_state'] = $_invoice->billing_state;

                $new_invoice_data['billing_zip'] = $_invoice->billing_zip;

                $new_invoice_data['billing_country'] = $_invoice->billing_country;

                $new_invoice_data['shipping_street'] = clear_textarea_breaks($_invoice->shipping_street);

                $new_invoice_data['shipping_city'] = $_invoice->shipping_city;

                $new_invoice_data['shipping_state'] = $_invoice->shipping_state;

                $new_invoice_data['shipping_zip'] = $_invoice->shipping_zip;

                $new_invoice_data['shipping_country'] = $_invoice->shipping_country;

                if ($_invoice->include_shipping == 1) {

                    $new_invoice_data['include_shipping'] = $_invoice->include_shipping;
                }

                $new_invoice_data['include_shipping'] = $_invoice->include_shipping;

                $new_invoice_data['show_shipping_on_invoice'] = $_invoice->show_shipping_on_invoice;

                // Determine status based on settings

                if ($new_recurring_invoice_action == 'generate_and_send' || $new_recurring_invoice_action == 'generate_unpaid') {

                    $new_invoice_data['status'] = 1;
                } elseif ($new_recurring_invoice_action == 'generate_draft') {

                    $new_invoice_data['save_as_draft'] = true;
                }

                $new_invoice_data['clientnote'] = clear_textarea_breaks($_invoice->clientnote);

                $new_invoice_data['adminnote'] = '';

                $new_invoice_data['allowed_payment_modes'] = unserialize($_invoice->allowed_payment_modes);

                $new_invoice_data['is_recurring_from'] = $_invoice->id;

                $new_invoice_data['newitems'] = [];

                $key = 1;

                $custom_fields_items = get_custom_fields('items');

                foreach ($_invoice->items as $item) {

                    $new_invoice_data['newitems'][$key]['description'] = $item['description'];

                    $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);

                    $new_invoice_data['newitems'][$key]['qty'] = $item['qty'];

                    $new_invoice_data['newitems'][$key]['unit'] = $item['unit'];

                    $new_invoice_data['newitems'][$key]['taxname'] = [];

                    $taxes = get_invoice_item_taxes($item['id']);

                    foreach ($taxes as $tax) {

                        // tax name is in format TAX1|10.00

                        array_push($new_invoice_data['newitems'][$key]['taxname'], $tax['taxname']);
                    }

                    $new_invoice_data['newitems'][$key]['rate'] = $item['rate'];

                    $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];



                    foreach ($custom_fields_items as $cf) {

                        $new_invoice_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);



                        if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {

                            define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                        }
                    }

                    $key++;
                }

                $id = $this->invoices_model->add($new_invoice_data);

                if ($id) {

                    $this->db->where('id', $id);

                    $this->db->update(db_prefix() . 'invoices', [

                        'addedfrom' => $_invoice->addedfrom,

                        'sale_agent' => $_invoice->sale_agent,

                        'cancel_overdue_reminders' => $_invoice->cancel_overdue_reminders,

                    ]);





                    $tags = get_tags_in($_invoice->id, 'invoice');

                    handle_tags_save($tags, $id, 'invoice');



                    // Get the old expense custom field and add to the new

                    $custom_fields = get_custom_fields('invoice');

                    foreach ($custom_fields as $field) {

                        $value = get_custom_field_value($invoice['id'], $field['id'], 'invoice', false);

                        if ($value != '') {

                            $this->db->insert(db_prefix() . 'customfieldsvalues', [

                                'relid' => $id,

                                'fieldid' => $field['id'],

                                'fieldto' => 'invoice',

                                'value' => $value,

                            ]);
                        }
                    }

                    // Increment total renewed invoices

                    $total_renewed++;

                    // Update last recurring date to this invoice

                    $this->db->where('id', $invoice['id']);

                    $this->db->update(db_prefix() . 'invoices', [

                        'last_recurring_date' => $re_create_at,

                    ]);



                    $this->db->where('id', $invoice['id']);

                    $this->db->set('total_cycles', 'total_cycles+1', false);

                    $this->db->update(db_prefix() . 'invoices');



                    if ($new_recurring_invoice_action == 'generate_and_send') {

                        $this->invoices_model->send_invoice_to_client($id, 'invoice_send_to_customer', true);
                    }



                    $_renewals_ids_data[] = [

                        'from' => $invoice['id'],

                        'clientid' => $invoice['clientid'],

                        'renewed' => $id,

                        'addedfrom' => $invoice['addedfrom'],

                        'sale_agent' => $invoice['sale_agent'],

                    ];
                }
            }
        }



        $send_recurring_invoices_email = hooks()->apply_filters('send_recurring_invoices_system_email', 'true');

        if ($total_renewed > 0 && $send_recurring_invoices_email == 'true') {

            $date = _dt(date('Y-m-d H:i:s'));

            $email_send_to_by_staff_and_invoice = [];

            // Get all active staff members

            $staff = $this->staff_model->get('', ['active' => 1]);

            foreach ($staff as $member) {

                $sent = false;

                load_admin_language($member['staffid']);

                $recurring_invoices_email_data = _l('not_recurring_invoices_cron_activity_heading') . ' - ' . $date . '<br /><br />';

                foreach ($_renewals_ids_data as $renewed_invoice_data) {

                    if ($renewed_invoice_data['addedfrom'] == $member['staffid'] || $renewed_invoice_data['sale_agent'] == $member['staffid'] || is_admin($member['staffid'])) {

                        $unique_send = '[' . $member['staffid'] . '-' . $renewed_invoice_data['from'] . ']';

                        $sent = true;

                        // Prevent sending the email twice if the same staff is added is sale agent and is creator for this invoice.

                        if (in_array($unique_send, $email_send_to_by_staff_and_invoice)) {

                            $sent = false;
                        }

                        $recurring_invoices_email_data .= _l('not_action_taken_from_recurring_invoice') . ' <a href="' . admin_url('invoices/list_invoices/' . $renewed_invoice_data['from']) . '">' . format_invoice_number($renewed_invoice_data['from']) . '</a><br />';

                        $recurring_invoices_email_data .= _l('not_invoice_renewed') . ' <a href="' . admin_url('invoices/list_invoices/' . $renewed_invoice_data['renewed']) . '">' . format_invoice_number($renewed_invoice_data['renewed']) . '</a> - <a href="' . admin_url('clients/client/' . $renewed_invoice_data['clientid']) . '">' . get_company_name($renewed_invoice_data['clientid']) . '</a><br /><br />';
                    }
                }

                if ($sent == true) {

                    array_push($email_send_to_by_staff_and_invoice, $unique_send);

                    $this->emails_model->send_simple_email($member['email'], _l('not_recurring_invoices_cron_activity_heading'), $recurring_invoices_email_data);
                }
            }

            load_admin_language();
        }
    }



    private function send_scheduled_emails()
    {

        $this->db->where('scheduled_at <=', date('Y-m-d H:i:s'));

        $emails = $this->db->get('scheduled_emails')->result_array();



        $this->load->model('invoices_model');

        $this->load->model('estimates_model');



        foreach ($emails as $email) {

            $type = $email['rel_type'];



            $GLOBALS['scheduled_email_contacts'] = explode(',', $email['contacts']);



            switch ($type) {

                case 'invoice':

                    $this->invoices_model->send_invoice_to_client(

                        $email['rel_id'],

                        $email['template'],

                        $email['attach_pdf'],

                        $email['cc']

                    );



                    break;

                case 'estimate':

                    $this->estimates_model->send_estimate_to_client(

                        $email['rel_id'],

                        $email['template'],

                        $email['attach_pdf'],

                        $email['cc']

                    );



                    break;
            }



            $this->db->where('id', $email['id']);

            $this->db->delete('scheduled_emails');
        }



        if (isset($GLOBALS['scheduled_email_contacts'])) {

            unset($GLOBALS['scheduled_email_contacts']);
        }
    }



    private function tasks_reminders()
    {

        $tasks_reminder_notification_hour = get_option('tasks_reminder_notification_hour');



        if (!$this->shouldRunAutomations($tasks_reminder_notification_hour)) {

            return;
        }



        $reminder_before = get_option('tasks_reminder_notification_before');

        $this->db->where('status !=', 5);

        $this->db->where('duedate IS NOT NULL');

        $this->db->where('deadline_notified', 0);



        $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();

        $now = new DateTime(date('Y-m-d'));



        $notifiedUsers = [];



        foreach ($tasks as $task) {

            if (date('Y-m-d', strtotime($task['duedate'])) >= date('Y-m-d')) {

                $duedate = new DateTime($task['duedate']);

                $diff = $duedate->diff($now)->format('%a');

                // Check if difference between start date and duedate is the same like the reminder before

                // In this case reminder wont be sent becuase the task it too short

                $start_date = strtotime($task['startdate']);

                $duedate = strtotime($task['duedate']);

                $start_and_due_date_diff = $duedate - $start_date;

                $start_and_due_date_diff = floor($start_and_due_date_diff / (60 * 60 * 24));



                if ($diff <= $reminder_before && $start_and_due_date_diff > $reminder_before) {

                    $assignees = $this->tasks_model->get_task_assignees($task['id']);



                    foreach ($assignees as $member) {

                        $this->db->select('email');

                        $this->db->where('staffid', $member['assigneeid']);

                        $row = $this->db->get(db_prefix() . 'staff')->row();

                        if ($row) {

                            $notified = add_notification([

                                'description' => 'not_task_deadline_reminder',

                                'touserid' => $member['assigneeid'],

                                'fromcompany' => 1,

                                'fromuserid' => 0,

                                'link' => '#taskid=' . $task['id'],

                                'additional_data' => serialize([

                                    $task['name'],

                                ]),

                            ]);



                            if ($notified) {

                                array_push($notifiedUsers, $member['assigneeid']);
                            }



                            send_mail_template('task_deadline_reminder_to_staff', $row->email, $member['assigneeid'], $task['id']);



                            $this->db->where('id', $task['id']);

                            $this->db->update(db_prefix() . 'tasks', [

                                'deadline_notified' => 1,

                            ]);
                        }
                    }
                }
            }
        }



        pusher_trigger_notification($notifiedUsers);
    }



    private function staff_reminders()
    {

        $this->db->select('' . db_prefix() . 'reminders.*, email, phonenumber');

        $this->db->join(db_prefix() . 'staff', '' . db_prefix() . 'staff.staffid=' . db_prefix() . 'reminders.staff');

        $this->db->where('isnotified', 0);

        $reminders = $this->db->get(db_prefix() . 'reminders')->result_array();

        $notifiedUsers = [];



        foreach ($reminders as $reminder) {

            if (date('Y-m-d H:i:s') >= $reminder['date']) {

                $this->db->where('id', $reminder['id']);

                $this->db->update(db_prefix() . 'reminders', [

                    'isnotified' => 1,

                ]);



                $rel_data = get_relation_data($reminder['rel_type'], $reminder['rel_id']);

                $rel_values = get_relation_values($rel_data, $reminder['rel_type']);



                $notificationLink = str_replace(admin_url(), '', $rel_values['link']);

                $notificationLink = ltrim($notificationLink, '/');



                $notified = add_notification([

                    'fromcompany' => true,

                    'touserid' => $reminder['staff'],

                    'description' => 'not_new_reminder_for',

                    'link' => $notificationLink,

                    'additional_data' => serialize([

                        $rel_values['name'] . ' - ' . strip_tags(mb_substr($reminder['description'], 0, 50)) . '...',

                    ]),

                ]);



                if ($notified) {

                    array_push($notifiedUsers, $reminder['staff']);
                }



                $template = mail_template('staff_reminder', $reminder['email'], $reminder['staff'], $reminder);



                if ($reminder['notify_by_email'] == 1) {

                    $template->send();
                }



                $this->app_sms->trigger(SMS_TRIGGER_STAFF_REMINDER, $reminder['phonenumber'], $template->get_merge_fields());
            }
        }



        pusher_trigger_notification($notifiedUsers);
    }



    private function invoice_overdue()
    {

        $invoice_auto_operations_hour = get_option('invoice_auto_operations_hour');



        if (!$this->shouldRunAutomations($invoice_auto_operations_hour)) {

            return;
        }



        $this->load->model('invoices_model');

        $this->db->select('id,date,status,last_overdue_reminder,duedate,cancel_overdue_reminders');

        $this->db->from(db_prefix() . 'invoices');

        $this->db->where('duedate IS NOT NULL'); // We dont need invoices with no duedate

        $this->db->where('status !=', Invoices_model::STATUS_PAID); // We dont need paid status

        $this->db->where('status !=', Invoices_model::STATUS_CANCELLED); // We dont need cancelled status

        $this->db->where('status !=', Invoices_model::STATUS_DRAFT); // We dont need draft status

        $invoices = $this->db->get()->result_array();



        $now = time();

        foreach ($invoices as $invoice) {

            if (empty($invoice['duedate'])) {

                continue;
            }



            $statusid = update_invoice_status($invoice['id']);



            if ($invoice['cancel_overdue_reminders'] == 0 && is_invoices_overdue_reminders_enabled()) {

                if (

                    $invoice['status'] == Invoices_model::STATUS_OVERDUE

                    || $statusid == Invoices_model::STATUS_OVERDUE

                    || $invoice['status'] == Invoices_model::STATUS_PARTIALLY

                ) {

                    if ($invoice['status'] == Invoices_model::STATUS_PARTIALLY) {

                        // Invoice is with status partialy paid and its not due

                        if (date('Y-m-d') <= date('Y-m-d', strtotime($invoice['duedate']))) {

                            continue;
                        }
                    }

                    // Check if already sent invoice reminder

                    if ($invoice['last_overdue_reminder']) {

                        // We already have sent reminder, check for resending

                        $resend_days = get_option('automatically_resend_invoice_overdue_reminder_after');

                        // If resend_days from options is 0 means that the admin dont want to resend the mails.

                        if ($resend_days != 0) {

                            $datediff = $now - strtotime($invoice['last_overdue_reminder']);

                            $days_diff = floor($datediff / (60 * 60 * 24));

                            if ($days_diff >= $resend_days) {

                                $this->invoices_model->send_invoice_overdue_notice($invoice['id']);
                            }
                        }
                    } else {

                        $datediff = $now - strtotime($invoice['duedate']);

                        $days_diff = floor($datediff / (60 * 60 * 24));

                        if ($days_diff >= get_option('automatically_send_invoice_overdue_reminder_after')) {

                            $this->invoices_model->send_invoice_overdue_notice($invoice['id']);
                        }
                    }
                }
            }
        }
    }



    private function invoice_due()
    {

        if (!$this->shouldRunAutomations(get_option('invoice_auto_operations_hour'))) {

            return;
        }



        $reminder_before = get_option('invoice_due_notice_before');

        $resend_days = get_option('invoice_due_notice_resend_after');



        $this->load->model('invoices_model');



        $this->db->select('id,date,status,last_due_reminder,duedate');

        $this->db->from(db_prefix() . 'invoices');

        // We dont need invoices with no duedate and where the duedate is less the current date

        // e.q. is already overdue and partially paid invoice

        $this->db->where('(duedate IS NOT NULL and duedate > "' . date('Y-m-d') . '")')

            ->where_in('status', [Invoices_model::STATUS_UNPAID, Invoices_model::STATUS_PARTIALLY])

            ->where('cancel_overdue_reminders', 0);



        $invoices = $this->db->get()->result_array();



        foreach ($invoices as $invoice) {

            if (empty($invoice['duedate'])) {

                continue;
            }



            if (!$invoice['last_due_reminder']) {

                $due_date = new DateTime($invoice['duedate']);

                $diff = $due_date->diff(new DateTime(date('Y-m-d')))->format('%a');

                $date_and_due_date_diff = floor((strtotime($invoice['duedate']) - strtotime($invoice['date'])) / (60 * 60 * 24));



                if ($diff <= $reminder_before && $date_and_due_date_diff > $reminder_before) {

                    $this->invoices_model->send_invoice_due_notice($invoice['id']);
                }
            } else {

                if ($resend_days != 0) { // If resend_days from options is 0 means that the admin dont want to resend the mails.

                    $datediff = time() - strtotime($invoice['last_due_reminder']);

                    $days_diff = floor($datediff / (60 * 60 * 24));

                    if ($days_diff >= $resend_days) {

                        $this->invoices_model->send_invoice_due_notice($invoice['id']);
                    }
                }
            }
        }
    }



    public function proposals()
    {

        $proposals_auto_operations_hour = get_option('proposals_auto_operations_hour');



        if (!$this->shouldRunAutomations($proposals_auto_operations_hour)) {

            return;
        }



        $this->load->model('proposals_model');



        $this->db->select('open_till,date,id');

        // Only 1 = open, 4 = sent

        $this->db->where('status IN (1,4)');

        $this->db->where('is_expiry_notified', 0);

        $proposals = $this->db->get(db_prefix() . 'proposals')->result_array();

        $now = new DateTime(date('Y-m-d'));



        foreach ($proposals as $proposal) {

            if (

                $proposal['open_till'] != null

                && date('Y-m-d') < $proposal['open_till']

                && is_proposals_expiry_reminders_enabled()

            ) {

                $reminder_before = get_option('send_proposal_expiry_reminder_before');

                $open_till = new DateTime($proposal['open_till']);

                $diff = $open_till->diff($now)->format('%a');

                $date = strtotime($proposal['date']);

                $open_till = strtotime($proposal['open_till']);

                $date_and_due_date_diff = $open_till - $date;

                $date_and_due_date_diff = floor($date_and_due_date_diff / (60 * 60 * 24));



                if ($diff <= $reminder_before && $date_and_due_date_diff > $reminder_before) {

                    $this->proposals_model->send_expiry_reminder($proposal['id']);
                }
            }
        }
    }



    private function estimate_expiration()
    {

        $estimates_auto_operations_hour = get_option('estimates_auto_operations_hour');



        if (!$this->shouldRunAutomations($estimates_auto_operations_hour)) {

            return;
        }



        $this->db->select('id,expirydate,status,is_expiry_notified,date');

        $this->db->from(db_prefix() . 'estimates');

        // Only get sent estimates

        $this->db->where('status', 2);

        $estimates = $this->db->get()->result_array();

        $this->load->model('estimates_model');

        $now = new DateTime(date('Y-m-d'));

        foreach ($estimates as $estimate) {

            if ($estimate['expirydate'] != null) {

                if (date('Y-m-d') > $estimate['expirydate']) {

                    $this->db->where('id', $estimate['id']);

                    $this->db->update(db_prefix() . 'estimates', [

                        'status' => 5,

                    ]);

                    if ($this->db->affected_rows() > 0) {

                        $additional_activity = serialize([

                            '<original_status>' . $estimate['status'] . '</original_status>',

                            '<new_status>5</new_status>',

                        ]);

                        $this->estimates_model->log_estimate_activity($estimate['id'], 'not_estimate_status_updated', false, $additional_activity);
                    }
                } else {

                    if ($estimate['is_expiry_notified'] == 0 && is_estimates_expiry_reminders_enabled()) {

                        $reminder_before = get_option('send_estimate_expiry_reminder_before');

                        $expirydate = new DateTime($estimate['expirydate']);

                        $diff = $expirydate->diff($now)->format('%a');

                        $date = strtotime($estimate['date']);

                        $expirydate = strtotime($estimate['expirydate']);

                        $date_and_due_date_diff = $expirydate - $date;

                        $date_and_due_date_diff = floor($date_and_due_date_diff / (60 * 60 * 24));

                        if ($diff <= $reminder_before && $date_and_due_date_diff > $reminder_before) {

                            $this->estimates_model->send_expiry_reminder($estimate['id']);
                        }
                    }
                }
            }
        }
    }



    public function check_leads_email_integration()
    {

        $this->load->model('leads_model');

        $mail = $this->leads_model->get_email_integration();



        if ($mail->active == 0) {

            return false;
        }



        if (empty($mail->last_run) || (time() > $mail->last_run + ($mail->check_every * 60))) {

            $this->load->model('spam_filters_model');



            $this->db->where('id', 1);

            $this->db->update(db_prefix() . 'leads_email_integration', [

                'last_run' => time(),

            ]);



            $password = $this->encryption->decrypt($mail->password);



            if (!$password) {

                log_activity('Failed to decrypt email integration password, navigateo to Setup->Leads->Email Integration and re-add the password.');



                return false;
            }



            $imap = new Imap(

                $mail->email,

                $password,

                $mail->imap_server,

                $mail->encryption

            );



            try {

                $connection = $imap->testConnection();
            } catch (ConnectionErrorException $e) {

                return false;
            }



            if (empty($mail->folder)) {

                $mail->folder = stripos($mail->imap_server, 'outlook') !== false

                    || stripos($mail->imap_server, 'microsoft')

                    || stripos($mail->imap_server, 'office365') !== false ? 'Inbox' : 'INBOX';
            }



            $mailbox = $connection->getMailbox($mail->folder);



            if ($mail->only_loop_on_unseen_emails == 1) {

                $search = new SearchExpression();

                $search->addCondition(new Unseen);



                $messages = $mailbox->getMessages($search);
            } else {

                $messages = $mailbox->getMessages();
            }



            include_once(APPPATH . 'third_party/simple_html_dom.php');



            foreach ($messages as $message) {

                try {

                    $this->currentImapMessage = $message;

                    $body = $message->getBodyHtml() ?? $message->getBodyText();

                    $html = str_get_html($body);



                    $formFields = [];

                    $lead_form_custom_fields = [];

                    if ($html) {

                        foreach ($html->find('[id^="field_"],[id^="custom_field_"]') as $data) {

                            if (isset($data->plaintext)) {

                                $value = strip_tags(trim($data->plaintext));

                                if ($value && isset($data->attr['id']) && !empty($data->attr['id'])) {

                                    $formFields[$data->attr['id']] = $this->security->xss_clean($value);
                                }
                            }
                        }
                    }



                    foreach ($formFields as $key => $val) {

                        $field = (strpos($key, 'custom_field_') !== false ? strafter($key, 'custom_field_') : strafter($key, 'field_'));



                        if (strpos($key, 'custom_field_') !== false) {

                            $lead_form_custom_fields[$field] = $val;
                        } elseif ($this->db->field_exists($field, db_prefix() . 'leads')) {

                            $formFields[$field] = $val;
                        }



                        unset($formFields[$key]);
                    }



                    $fromAddress = null;

                    $fromName = null;



                    if ($message->getFrom()) {

                        $fromAddress = $message->getFrom()->getAddress();

                        $fromName = $message->getFrom()->getName();
                    }



                    $replyTo = $message->getReplyTo();



                    if (count($replyTo) === 1) {

                        $fromAddress = $replyTo[0]->getAddress();

                        $fromName = $replyTo[0]->getName() ?? $fromName;
                    }



                    $fromAddress = $formFields['email'] ?? $fromAddress;

                    $fromName = $formFields['name'] ?? $fromName;

                    $fromName = $fromName ?: 'Unknown';



                    /**

                     * Check the the fromAddress is null, perhaps invalid address?

                     * @see https://github.com/ddeboer/imap/issues/370

                     */

                    if (is_null($fromAddress)) {

                        $message->markAsSeen();



                        continue;
                    }



                    $mailstatus = $this->spam_filters_model->check($fromAddress, $message->getSubject(), $body, 'leads');



                    if ($mailstatus) {

                        $message->markAsSeen();

                        log_activity('Lead Email Integration Blocked Email by Spam Filters [' . $mailstatus . ']');



                        continue;
                    }



                    $body = hooks()->apply_filters(

                        'leads_email_integration_email_body_for_database',

                        $this->prepare_imap_email_body_html($body)

                    );



                    // Okey everything good now let make some statements

                    // Check if this email exists in customers table first

                    $this->db->select('id,userid');

                    $this->db->where('email', $fromAddress);

                    $contact = $this->db->get(db_prefix() . 'contacts')->row();

                    if ($contact) {

                        if ($mail->create_task_if_customer == '1') {

                            load_admin_language($mail->responsible);



                            $body = '<b>' . _l('leads_email_integration') . ' (' . _l('existing_customer') . ')</b> - <a href="' . admin_url('clients/client/' . $contact->userid . '?contactid=' . $contact->id) . '" target="_blank"><b>' . get_company_name($contact->userid) . '</b></a><br /><br />' . $body;



                            load_admin_language();



                            $task_data = [

                                'name' => $fromName . ' - ' . $fromAddress,

                                'priority' => get_option('default_task_priority'),

                                'dateadded' => date('Y-m-d H:i:s'),

                                'startdate' => date('Y-m-d'),

                                'addedfrom' => $mail->responsible,

                                'status' => 1,

                                'description' => $body,

                            ];



                            $task_data = hooks()->apply_filters('before_add_task', $task_data);

                            $this->db->insert(db_prefix() . 'tasks', $task_data);



                            $task_id = $this->db->insert_id();

                            if ($task_id) {

                                $assignee_data = [

                                    'taskid' => $task_id,

                                    'assignee' => $mail->responsible,

                                ];



                                $this->tasks_model->add_task_assignees($assignee_data, true);

                                $this->handleLeadsEmailIntegrationAttachments($message, false, $task_id);

                                hooks()->do_action('after_add_task', $task_id);
                            }



                            if ($mail->delete_after_import == 1) {

                                $message->delete();

                                $connection->expunge();
                            } else {

                                $message->markAsSeen();
                            }
                        } else {

                            $message->markAsSeen();
                        }

                        // Exists no need to do anything

                        continue;
                    }

                    // Not exists its okey.

                    // Now we need to check the leads table

                    $this->db->where('email', $fromAddress);

                    $lead = $this->db->get(db_prefix() . 'leads')->row();



                    $lead = hooks()->apply_filters('leads_email_integration_lead_check', $lead, $message);



                    if ($lead) {

                        // Check if the lead uid is the same with the email uid

                        if ($lead->email_integration_uid == $message->getNumber()) {

                            $message->markAsSeen();

                            // Set message to seen to in the next time we dont need to loop over this message



                            continue;
                        }

                        // Check if this uid exists in the emails data log table

                        $this->db->where('emailid', $message->getNumber());

                        $exists_in_emails = $this->db->count_all_results(db_prefix() . 'lead_integration_emails');

                        if ($exists_in_emails > 0) {

                            // Set message to seen to in the next time we dont need to loop over this message

                            $message->markAsSeen();



                            continue;
                        }

                        // We dont need the junk leads

                        if ($lead->junk == 1) {

                            // Set message to seen to in the next time we dont need to loop over this message

                            $message->markAsSeen();



                            continue;
                        }

                        // More the one time email from this lead, insert into the lead emails log table

                        $this->db->insert(db_prefix() . 'lead_integration_emails', [

                            'leadid' => $lead->id,

                            'subject' => $message->getSubject(),

                            'body' => $body,

                            'dateadded' => date('Y-m-d H:i:s'),

                            'emailid' => $message->getNumber(),

                        ]);



                        $inserted_email_id = $this->db->insert_id();

                        if ($mail->delete_after_import == 1) {

                            $message->delete();

                            $connection->expunge();
                        } else {

                            $message->markAsSeen();
                        }

                        $this->_notification_lead_email_integration('not_received_one_or_more_messages_lead', $mail, $lead->id);

                        $this->handleLeadsEmailIntegrationAttachments($message, $lead->id);

                        hooks()->do_action('existing_lead_email_inserted_from_email_integration', [

                            'email' => $message,

                            'lead' => $lead,

                            'email_id' => $inserted_email_id,

                        ]);

                        // Exists not need to do anything except to add the email

                        continue;
                    }



                    // Lets insert into the leads table

                    $lead_data = [

                        'name' => $fromName,

                        'assigned' => $mail->responsible,

                        'dateadded' => date('Y-m-d H:i:s'),

                        'status' => $mail->lead_status,

                        'source' => $mail->lead_source,

                        'addedfrom' => 0,

                        'email' => $fromAddress,

                        'is_imported_from_email_integration' => 1,

                        'email_integration_uid' => $message->getNumber(),

                        'lastcontact' => null,

                        'is_public' => $mail->mark_public,

                    ];



                    $lead_data = hooks()->apply_filters('before_insert_lead_from_email_integration', $lead_data);



                    $this->db->insert(db_prefix() . 'leads', $lead_data);

                    $insert_id = $this->db->insert_id();

                    if ($insert_id) {

                        foreach ($formFields as $field => $value) {

                            if ($field == 'country') {

                                if ($value == '') {

                                    $value = 0;
                                } else {

                                    $this->db->where('iso2', $value);

                                    $this->db->or_where('short_name', $value);

                                    $this->db->or_where('long_name', $value);

                                    $country = $this->db->get(db_prefix() . 'countries')->row();

                                    if ($country) {

                                        $value = $country->country_id;
                                    } else {

                                        $value = 0;
                                    }
                                }
                            }



                            if ($field == 'address' || $field == 'description') {

                                $value = nl2br($value);
                            }



                            $this->db->where('id', $insert_id);

                            $this->db->update(db_prefix() . 'leads', [

                                $field => $value,

                            ]);
                        }



                        foreach ($lead_form_custom_fields as $cf_id => $value) {

                            $this->db->insert(db_prefix() . 'customfieldsvalues', [

                                'relid' => $insert_id,

                                'fieldto' => 'leads',

                                'fieldid' => $cf_id,

                                'value' => $value,

                            ]);
                        }



                        $this->db->insert(db_prefix() . 'lead_integration_emails', [

                            'leadid' => $insert_id,

                            'subject' => $message->getSubject(),

                            'body' => $body,

                            'dateadded' => date('Y-m-d H:i:s'),

                            'emailid' => $message->getNumber(),

                        ]);



                        if ($mail->delete_after_import == 1) {

                            $message->delete();

                            $connection->expunge();
                        } else {

                            $message->markAsSeen();
                        }



                        // Set message to seen to in the next time we dont need to loop over this message

                        $this->_notification_lead_email_integration('not_received_lead_imported_email_integration', $mail, $insert_id);

                        $this->leads_model->log_lead_activity($insert_id, 'not_received_lead_imported_email_integration', true);

                        $this->handleLeadsEmailIntegrationAttachments($message, $insert_id);

                        $this->leads_model->lead_assigned_member_notification($insert_id, $mail->responsible, true);



                        hooks()->do_action('lead_created', $insert_id);



                        hooks()->do_action('lead_created_from_email_integration', $insert_id);
                    }
                } catch (MessageDoesNotExistException $e) {

                    continue;
                } catch (UnexpectedEncodingException | UnsupportedCharsetException $e) {

                    $message->markAsSeen();



                    continue;
                }
            }



            $this->currentImapMessage = null;
        }
    }



    public function auto_import_imap_tickets()
    {

        $this->db->select('host,encryption,password,email,delete_after_import,imap_username,folder')

            ->from(db_prefix() . 'departments')

            ->where('host !=', '')

            ->where('password !=', '')

            ->where('email !=', '');



        $departments = $this->db->get()->result_array();



        foreach ($departments as $dept) {

            if (empty($dept['password'])) {

                continue;
            }



            $password = $this->encryption->decrypt($dept['password']);



            if (!$password) {

                log_activity('Failed to decrypt department password, navigate to Setup->Support->Departments and re-add the password for ' . $dept['email'] . ' department');



                continue;
            }



            $imap = new Imap(

                !empty($dept['imap_username']) ? $dept['imap_username'] : $dept['email'],

                $password,

                $dept['host'],

                $dept['encryption']

            );



            try {

                $connection = $imap->testConnection();
            } catch (ConnectionErrorException $e) {

                log_activity('Failed to connect to IMAP auto importing tickets for department ' . $dept['email'] . '.');



                continue;
            }



            $mailbox = $connection->getMailbox(

                empty($dept['folder']) ? 'INBOX' : $dept['folder']

            );



            $search = new SearchExpression();

            $search->addCondition(new Unseen);



            $messages = $mailbox->getMessages($search);



            $this->load->model('tickets_model');



            foreach ($messages as $message) {

                $this->currentImapMessage = $message;



                try {

                    $body = $message->getBodyHtml() ?? $message->getBodyText();

                    // Some mail clients for the text/plain part add only Not set

                    // this is bad practice instead of leaving the text/pain part empty

                    // In this case, if it's Not set, we will use the HTML of the message

                    if ($body == 'Not set') {

                        $body = $message->getBodyHtml();
                    }



                    if (empty($body)) {

                        $body = 'No message found';
                    }



                    if (

                        class_exists('EmailReplyParser\EmailReplyParser')

                        && get_option('ticket_import_reply_only') === '1'

                        && (mb_substr_count($message->getSubject(), 'FWD:') == 0 && mb_substr_count($message->getSubject(), 'FW:') == 0)

                    ) {

                        $parsedBody = \EmailReplyParser\EmailReplyParser::parseReply(

                            $this->prepare_imap_email_body_html($body)

                        );



                        $parsedBody = trim($parsedBody);



                        // For some emails this is causing an issue and not returning the email, instead is returning empty string

                        // In this case, only use parsed email reply if not empty

                        if (!empty($parsedBody)) {

                            $body = $parsedBody;
                        }
                    }



                    $body = $this->prepare_imap_email_body_html($body);

                    $data['attachments'] = [];



                    foreach ($message->getAttachments() as $attachment) {

                        $data['attachments'][] = [

                            'filename' => $attachment->getFilename(),

                            'data' => $attachment->getDecodedContent(),

                        ];
                    }



                    $data['subject'] = $message->getSubject();

                    $data['body'] = $body;



                    $data['to'] = [];

                    $data['cc'] = [];

                    // To is the department name

                    $data['to'][] = $dept['email'];



                    // Check for CC

                    if (count($message->getCc()) > 0) {

                        foreach ($message->getCc() as $recipient) {

                            $data['to'][] = $recipient->getAddress();

                            $data['cc'][] = $recipient->getAddress();
                        }
                    }



                    $data['to'] = implode(',', $data['to']);

                    $fromAddress = null;

                    $fromName = null;



                    if ($message->getFrom()) {

                        $fromAddress = $message->getFrom()->getAddress();

                        $fromName = $message->getFrom()->getName();
                    }



                    if (hooks()->apply_filters('imap_fetch_from_email_by_reply_to_header', true)) {

                        $replyTo = $message->getReplyTo();



                        if (count($replyTo) === 1) {

                            $fromAddress = $replyTo[0]->getAddress();

                            $fromName = $replyTo[0]->getName() ?? $fromName;
                        }
                    }



                    /**

                     * Check the the fromAddress is null, perhaps invalid address?

                     * @see https://github.com/ddeboer/imap/issues/370

                     */

                    if (is_null($fromAddress)) {

                        $message->markAsSeen();



                        continue;
                    }



                    $data['email'] = $fromAddress;

                    $data['fromname'] = $fromName;



                    $data = hooks()->apply_filters('imap_auto_import_ticket_data', $data, $message);



                    try {

                        $status = $this->tickets_model->insert_piped_ticket($data);



                        if ($status == 'Ticket Imported Successfully' || $status == 'Ticket Reply Imported Successfully') {

                            if ($dept['delete_after_import'] == 0) {

                                $message->markAsSeen();
                            } else {

                                $message->delete();

                                $connection->expunge();
                            }
                        } else {

                            // Set unseen message in all cases to prevent looping throught the message again

                            $message->markAsSeen();
                        }
                    } catch (\Exception $e) {

                        // Set unseen message in all cases to prevent looping throught the message again

                        $message->markAsSeen();
                    }
                } catch (MessageDoesNotExistException $e) {

                    continue;
                } catch (UnexpectedEncodingException | UnsupportedCharsetException $e) {

                    log_activity('Failed to auto importing tickets for department ' . $dept['email'] . '. Error:' . $e->getMessage());

                    $message->markAsSeen();



                    continue;
                }
            }

            $this->currentImapMessage = null;
        }
    }



    public function delete_activity_log()
    {

        $older_then_months = get_option('delete_activity_log_older_then');



        if ($older_then_months == 0 || empty($older_then_months)) {

            return;
        }



        $this->db->query('DELETE FROM ' . db_prefix() . 'activity_log WHERE date < DATE_SUB(NOW(), INTERVAL ' . $this->db->escape_str($older_then_months) . ' MONTH);');

        $this->db->query('DELETE FROM ' . db_prefix() . 'tickets_pipe_log WHERE date < DATE_SUB(NOW(), INTERVAL ' . $this->db->escape_str($older_then_months) . ' MONTH);');
    }



    private function _maybe_fix_duplicate_tasks_assignees_and_followers()
    {

        $query = $this->db->query('SELECT `staffid`, `taskid`, COUNT(*) AS c FROM ' . db_prefix() . 'task_assigned GROUP BY `staffid`, `taskid` HAVING c > 1')->result_array();

        foreach ($query as $res) {

            $this->db->where('staffid', $res['staffid']);

            $this->db->where('taskid', $res['taskid']);

            $this->db->limit($res['c'] - 1);

            $this->db->delete(db_prefix() . 'task_assigned');
        }

        $query = $this->db->query('SELECT `staffid`, `taskid`, COUNT(*) AS c FROM ' . db_prefix() . 'task_followers GROUP BY `staffid`, `taskid` HAVING c > 1')->result_array();

        foreach ($query as $res) {

            $this->db->where('staffid', $res['staffid']);

            $this->db->where('taskid', $res['taskid']);

            $this->db->limit($res['c'] - 1);

            $this->db->delete(db_prefix() . 'task_followers');
        }
    }



    private function _notification_lead_email_integration($description, $mail, $leadid)
    {

        if (!empty($mail->notify_type)) {

            if ($mail->notify_type == 'assigned') {

                $ids = [$mail->responsible];

                $field = 'staffid';
            } else {

                $ids = unserialize($mail->notify_ids);

                if (!is_array($ids) || count($ids) == 0) {

                    return;
                }

                if ($mail->notify_type == 'specific_staff') {

                    $field = 'staffid';
                } elseif ($mail->notify_type == 'roles') {

                    $field = 'role';
                } else {

                    return;
                }
            }



            $this->db->where('active', 1);

            $this->db->where_in($field, $ids);

            $staff = $this->db->get(db_prefix() . 'staff')->result_array();



            $notifiedUsers = [];



            foreach ($staff as $member) {

                $notified = add_notification([

                    'description' => $description,

                    'touserid' => $member['staffid'],

                    'fromcompany' => 1,

                    'fromuserid' => 0,

                    'link' => '#leadid=' . $leadid,

                ]);

                if ($notified) {

                    array_push($notifiedUsers, $member['staffid']);
                }
            }

            pusher_trigger_notification($notifiedUsers);
        }
    }



    private function handleLeadsEmailIntegrationAttachments($message, $leadid, $task_id = false)
    {

        foreach ($message->getAttachments() as $attachment) {

            $path = $task_id ?

                get_upload_path_by_type('task') . $task_id . '/' :

                get_upload_path_by_type('lead') . $leadid . '/';



            if (!file_exists($path)) {

                mkdir($path, 0755);

                file_put_contents($path . 'index.html', '');
            }



            $file_name = unique_filename($path, $attachment->getFilename());

            $path = $path . $file_name;



            if (
                file_put_contents(

                    $path,

                    $attachment->getDecodedContent()

                )
            ) {

                $attachment_id = $this->misc_model->add_attachment_to_database(

                    ($task_id ? $task_id : $leadid),

                    ($task_id ? 'task' : 'lead'),

                    [
                        [

                            'file_name' => $file_name,

                            'filetype' => get_mime_by_extension($attachment->getFilename()),

                            'staffid' => 0,

                        ]
                    ]

                );



                if ($attachment_id && $task_id === false) {

                    $this->leads_model->log_lead_activity($leadid, 'not_lead_imported_attachment', true);
                }
            }
        }
    }



    public function __destruct()
    {

        $this->lockHandle();
    }



    private function lockHandle()
    {

        if ($this->lock_handle) {

            flock($this->lock_handle, LOCK_UN);

            fclose($this->lock_handle);

            $this->lock_handle = null;
        }
    }



    private function can_cron_run()
    {
        if ($this->app->is_db_upgrade_required()) {
            return false;
        }



        return ($this->lock_handle && flock($this->lock_handle, LOCK_EX | LOCK_NB))

            || (defined('APP_DISABLE_CRON_LOCK') && APP_DISABLE_CRON_LOCK);
    }



    private function prepare_imap_email_body_html($body)
    {

        // Trim message

        $body = trim($body);

        $body = str_replace('&nbsp;', ' ', $body);

        // Remove html tags - strips inline styles also

        $body = trim(strip_html_tags($body, '<br/>, <br>, <a>'));

        // Once again do security

        $body = $this->security->xss_clean($body);

        // Remove duplicate new lines

        $body = preg_replace("/[\r\n]+/", "\n", $body);

        // new lines with <br />

        $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);

        $body = preg_replace('/\n/', '<br>', $body);



        return $body;
    }



    private function shouldRunAutomations($auto_operation_hour)
    {

        if ($auto_operation_hour == '') {

            $auto_operation_hour = 9;
        }



        $auto_operation_hour = intval($auto_operation_hour);

        $hour_now = date('G');

        if ($hour_now != $auto_operation_hour && $this->manually === false) {

            return false;
        }



        return true;
    }



    private function hasTimeoutOccurred()
    {

        $lastError = error_get_last();



        if (!$lastError) {

            return false;
        }



        return startsWith($lastError['message'], 'Maximum execution time');
    }





    // workroom report 

    public function managers_list()
    {
        $query = "SELECT team_manage from tblstaff where active = 1";
        $data = $this->db->query($query)->result_array();
        $manager_ids = array_filter(array_unique(array_column($data, 'team_manage')));

        $managers = [];

        foreach ($manager_ids as $manager_id) {

            if (check_staff_active($manager_id)) {
                $managers[$manager_id] = get_staff_full_name($manager_id);
            }
        }

        return $managers;
    }

    public function working_hours_summary()
    {

        // $query = "SELECT * FROM tblstaff LEFT JOIN tblstaff_info ON tblstaff.staffid = tblstaff_info.staffid where manageLeave = 1";

        // $data = $this->db->query($query)->result_array();

        $data = $this->managers_list();

        echo '<pre>';

        $working_hours_summary = array();

        foreach ($data as $manager_id => $manager_name) {


            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];
            $nine_plus = 0;
            $five_to_nine = 0;
            $zero_to_five = 0;
            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid]['attendance'] = ($team_staffid == 1 || $team_staffid == 144) ? 1 : $this->is_staff_present($team_staffid);
                $working_hrs = $this->get_staff_working_hrs($team_staffid);
                $staff_data[$team_staffid]['working_hrs'] = $working_hrs;
                $this->check_working_hrs($working_hrs, $nine_plus, $five_to_nine, $zero_to_five);
            }
            $working_hours_summary[$manager_id]['team_data'] = $staff_data;

            $working_hours_summary[$manager_id]['date'] = date('Y-m-d', strtotime("-1 days"));
            $working_hours_summary[$manager_id]['team_count'] = count($staff_data);
            $present = array_sum(array_column($staff_data, 'attendance'));
            $working_hours_summary[$manager_id]['present'] = $present;
            $working_hours_summary[$manager_id]['absent'] = $working_hours_summary[$manager_id]['team_count'] - $present;
            $occupancy = $working_hours_summary[$manager_id]['team_count'] ? ($working_hours_summary[$manager_id]['present'] / $working_hours_summary[$manager_id]['team_count']) * 100 : 0;
            $working_hours_summary[$manager_id]['occupancy'] = round($occupancy, 2);
            $working_hours_summary[$manager_id]['nine_plus'] = $nine_plus;
            $working_hours_summary[$manager_id]['five_to_nine'] = $five_to_nine;
            $working_hours_summary[$manager_id]['zero_to_five'] = $zero_to_five;
        }

        echo '<pre>';
        print_r($working_hours_summary);

        return $working_hours_summary;
    }

    public function is_staff_present($staffid)
    {
        // getting previous date for testing purposes
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT * FROM `tblcheck_in_out` WHERE DATE(date) = '$date' and staff_id = $staffid and type_check = 1";

        // echo $query;

        $today_present_data = $this->db->query($query)->result_array();

        // return  $today_present_data;

        if ($today_present_data) {

            return 1;
        }

        return 0;
    }

    public function get_staff_working_hrs($staffid)
    {
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT * FROM tbltimesheets_timesheet WHERE staff_id = $staffid AND date_work = '$date'";

        $today_present_data = $this->db->query($query)->result_array();

        if ($today_present_data) {
            // return $today_present_data[0]['value'] ?? 0;
            if ($today_present_data[0]['value']) {
                return $today_present_data[0]['value'];
            } else {
                return null;
            }
        } else {
            return 0;
        }
    }

    public function check_working_hrs($hrs, &$nine_plus, &$five_to_nine, &$zero_to_five)
    {
        $this->load->helper('timesheets/timesheets');
        $present_min = timesheets_present_min_hours();
        $half_min = timesheets_half_day_min_hours();

        switch (true) {
            case $hrs + 0.001 < $half_min && $hrs > 0:
                $zero_to_five++;
                break;
            case $hrs + 0.001 >= $half_min && $hrs + 0.001 < $present_min:
                $five_to_nine++;
                break;
            case $hrs + 0.001 >= $present_min:
                $nine_plus++;
                break;
        }
    }

    public function less_than_9_hrs_monthly_summary()
    {


        // $less_than_9_hrs_monthly_summary = array();

        $date = date('Y-m-d', strtotime("-1 days"));

        $month = date('m', strtotime($date));

        // $query = "SELECT staff_id,COUNT(staff_id) AS monthly_count ,departmentid,team_manage,staff_identifi  FROM tbltimesheets_timesheet LEFT JOIN tblstaff_departments ON tbltimesheets_timesheet.staff_id = tblstaff_departments.staffid LEFT JOIN tblstaff ON tbltimesheets_timesheet.staff_id = tblstaff.staffid WHERE month(date_work) = $month AND value < 9 AND value > 0 GROUP BY staff_id, departmentid, team_manage, staff_identifi; ;";

        $query = "SELECT
            staffid,
            firstname,
            lastname,
            staff_identifi,
            team_manage,
            (
            SELECT
                GROUP_CONCAT(
                    tblstaff_departments.departmentid
                )
            FROM
                tblstaff_departments
            WHERE
                staffid = tblstaff.staffid
        ) AS departmentids
        FROM
            `tblstaff`
        LEFT JOIN tblcheck_in_out ON tblstaff.staffid = tblcheck_in_out.staff_id AND DATE(DATE) = '$date' AND type_check = 1
        WHERE
            tblcheck_in_out.staff_id IS NULL AND active = 1 AND(
            SELECT
                COUNT(sd.departmentid)
            FROM
                tblstaff_departments sd
            WHERE
                sd.staffid = tblstaff.staffid
        ) > 0 AND tblstaff.staffid != 1
        ";

        $less_than_9_hrs_data = $this->db->query($query)->result_array();


        foreach ($less_than_9_hrs_data as &$data) {
            $data['employee'] = get_staff_full_name($data['staffid']);

            $data['department_name'] = $this->db->query("select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $data['departmentids'] . ')')->row()->department_names;



            $data['manager_name'] = get_staff_full_name($data['team_manage']);
            $data['emp_id'] = $data['staff_identifi'];
            // $data['manager_name'] = 
            // print_r(array_column($arr,'staffid')); 

        }

        return $less_than_9_hrs_data;
    }

    public function get_staff_emp_id($staffid)
    {

        $query = "SELECT empid from tblstaff_info WHERE staffid = $staffid";

        $empid_obj = $this->db->query($query)->row();

        if ($empid_obj) {

            return $empid_obj->empid;
        }
        return '';
    }




    public function daily_report_email($working_hrs_summary, $less_than_nine_summary)
    {
        $date = date('d-m-Y', strtotime("-1 days"));

        try {

            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
            // $this->email->to('yogesh.gupta@tech2globe.in');
            // $this->email->cc('naved.ahamad1@tech2globe.in');
            /*$this->email->to('hr@tech2globe.com');
            $this->email->cc(array('sarabjeet@tech2globe.net'));
            */
            $this->email->to('niraj.lal.rahi@outlook.com');
            $this->email->cc(array('ishita.rathi@tech2globe.in'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Daily Attendance Report : $date ";

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #2600bd;
                        color : white;
                    }

                    td {
                        color : black;
                    }
                </style>
            </head>
            <body>
         
            ';


            // $count = 1;
            // foreach ($working_hrs_summary as $manager_id => $manager_data) {
            //     $message .= "<tr>
            //     <td>" . $count++ . "</td>
            //     <td>" . get_staff_full_name($manager_id) . "</td>
            //     <td>" . $manager_data['team_count'] . "</td>
            //     <td>" . $manager_data['occupancy'] . "%</td>
            //     </tr>";
            // }



            $message .= '
            <h2>Today\'s Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Cluster</th>
                        <th>Team Count</th>
                        <th>Total Present</th>
                        <th>Total Absent</th>
                        <th>Occupancy</th>

                    </tr>
                </thead>
            <tbody>
            ';

            $count = 1;
            foreach ($working_hrs_summary as $manager_id => $manager_data) {
                $message .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . get_staff_full_name($manager_id) . "</td>
                <td>" . $manager_data['team_count'] . "</td>
                <td>" . $manager_data['present'] . "</td>
                <td>" . $manager_data['absent'] . "</td>
                <td>" . $manager_data['occupancy'] . "%</td>

                </tr>";
            }


            // $message .= ' </tbody> </table>
            // <h2>Working Hours Summary</h2>
            // <table>
            //         <thead>
            //             <tr>
            //                 <th>S.No.</th>
            //                 <th>Cluster</th>
            //                 <th>Team Count</th>
            //                 <th>9+ hours</th>
            //                 <th>5 to 9 hours</th> 
            //                 <th>0 to 5 hours</th> 
            //             </tr>
            //         </thead>
            //     <tbody>';

            // $count = 1;
            // foreach ($working_hrs_summary as $manager_id => $manager_data) {
            //     $message .= "<tr>
            //     <td>" . $count++ . "</td>
            //     <td>" . get_staff_full_name($manager_id) . "</td>
            //     <td>" . $manager_data['team_count'] . "</td>
            //     <td>" . $manager_data['nine_plus'] . "</td>
            //     <td>" . $manager_data['five_to_nine'] . "</td>
            //     <td>" . $manager_data['zero_to_five'] . "</td>
            //     </tr>";
            // }

            $message .= "</tbody></table>
            <h2>Absent Employees Summary</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Reporting Person</th>
                            <th>Department</th> 
                        </tr>
                    </thead>
                <tbody>";


            foreach ($less_than_nine_summary as $key => $less_than_nine_data) {
                $sno = $key + 1;
                $message .= "<tr>
                <td>" . $sno . "</td>
                <td>" . $less_than_nine_data['emp_id'] . "</td>
                <td>" . $less_than_nine_data['employee'] . "</td>
                <td>" . $less_than_nine_data['manager_name'] . "</td>
                <td>" . $less_than_nine_data['department_name'] . "</td>
                </tr>";
            }



            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            // $this->email->bcc(['yogesh.gupta@tech2globe.in', 'naved.ahamad1@tech2globe.net']);
            $this->email->send();
            log_message('error', 'Workroom report email sent!');
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }

    public function consecutive_three_days()
    {
        $date = date('Y-m-d', strtotime("-1 days"));

        $query = "SELECT staffid, firstname,  lastname, email, role , last_login FROM tblstaff WHERE last_login < NOW() - INTERVAL 3 DAY and active = 1;";

        $staff = $this->db->query($query)->result_array();

        foreach ($staff as $key => $value) {

            $staff[$key]["full_name"] = $staff[$key]["firstname"] . ' ' . $staff[$key]["lastname"];
            $staff[$key]["role"] = get_role_name($staff[$key]["role"]);
            unset($staff[$key]['firstname']);
            unset($staff[$key]['lastname']);
        }

        return $staff;
    }

    public function consecutive_three_days_email($consecutive_three_days)
    {

        $date = date('m/d/Y', strtotime("-1 days"));

        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
            $this->email->to('sarabjeet@tech2globe.net');
            $this->email->cc(array('ishan.negi@tech2globe.in', 'naved.ahamad@tech2globe.in', 'sarabjeet@tech2globe.com'));

            $subject = "Inactive Staffs - T2GWorkroom - $date ";

            $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Performance review</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid black;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #2600bd;
                            color : white;
                        }

                        td {
                            color : black;
                        }
                    </style>
                </head>
                <body>
                <h3>Employees that did not Login for 3 consecutive days :</h3>
                <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Last Login</th> 
                        </tr>
                    </thead>
                    <tbody>
                ';


            foreach ($consecutive_three_days as $key => $value) {
                $sno = $key + 1;
                $message .= "<tr>
                    <td>" . $sno . "</td>
                    <td>" . $value['full_name'] . "</td>
                    <td>" . $value['email'] . "</td>
                    <td>" . $value['role'] . "</td>
                    <td>" . $value['last_login'] . "</td>
                    </tr>";
            }


            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
            log_message('error', "consecutive three days email sent!");
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }




    // it asset code begins

    public function get_asset_data()
    {
        $get_asset_query = "SELECT assets , acction_to FROM tblassets_acction_1 LEFT JOIN tblassets ON tblassets.id = tblassets_acction_1.assets WHERE type = 'allocation'";
        $asset_data = $this->db->query($get_asset_query)->result_array();
        echo '<pre>';


        $staff_assets = array();

        foreach ($asset_data as $data) {
            $acction_to = $data['acction_to'];
            $assets = $data['assets'];

            if ($this->check_if_revoke($assets, $acction_to)) {
                continue;
            }
            if (!array_key_exists($acction_to, $staff_assets)) {
                $staff_assets[$acction_to] = [];
            }

            $staff_assets[$acction_to][] = $assets;
        }

        print_r($staff_assets);
        return $staff_assets;
    }

    public function maximum_number_of_assets($staff_assets)
    {
        $max = 0;
        foreach ($staff_assets as $staff_asset) {
            $asset_count = count($staff_asset);
            if ($asset_count > $max)
                $max = $asset_count;
        }
        return $max;
    }

    public function check_if_revoke($asset, $staffid)
    {
        $query = "SELECT * FROM tblassets_acction_1 WHERE assets = $asset AND acction_to = $staffid AND type = 'revoke'";
        $revoke_data = $this->db->query($query)->result_array();

        // print_r($revoke_data);
        if (empty($revoke_data)) {
            return false;
        } else {
            return true;
        }
    }

    function get_asset_code_by_id($id)
    {
        $CI = &get_instance();
        $CI->db->where('id', $id);
        $assets = $CI->db->get(db_prefix() . 'assets')->row();

        if ($assets) {

            return $assets->assets_code;
        } else {
            return '';
        }
    }

    public function it_asset_weekly_email($staff_assets)
    {

        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
            // $this->email->to('ishan.negi@tech2globe.in');
            $this->email->to('sarabjeet@tech2globe.net');
            $this->email->cc(array('ishan.negi@tech2globe.in', 'naved.ahamad@tech2globe.in', 'sarabjeet@tech2globe.com'));

            $subject = "Weekly Support Tickets Report";

            $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Weekly IT Asset</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid black;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #2600bd;
                            color : white;
                        }

                        td {
                            color : black;
                        }
                    </style>
                </head>
                <body>
                <h3>Weekly IT Asset Allocation</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            
                ';

            $max_assets = $this->maximum_number_of_assets($staff_assets);

            for ($i = 1; $i <= $max_assets; $i++) {
                $message .= '<th>Asset ' . $i . '</th>';
            }

            $message .= '
                <th>Total Assets</th>
                </tr>
                    </thead>
                    <tbody>
                ';


            foreach ($staff_assets as $key => $value) {
                $message .= "<tr>
                    <td>" . get_staff_full_name($key) . "</td>";

                for ($i = 0; $i < $max_assets; $i++) {
                    if (isset($value[$i])) {
                        $message .= "<td>" . $this->get_asset_code_by_id($value[$i]) . "</td>";
                    } else {
                        $message .= "<td></td>";
                    }
                }
                // foreach($value as $asset_id){
                //     $message .= "<td>".$this->get_asset_code_by_id($asset_id)."</td>";
                // }
                $message .= "<td>" . count($value) . "</td></tr>";
            }


            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
            log_message('error', "IT asset email sent!");
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }


    public function get_hr_data()
    {
        $get_hr_department = "SELECT
            GROUP_CONCAT(departmentid SEPARATOR ', ') AS staffids
        FROM
            `tbldepartments`
        WHERE NAME LIKE
            '%hr%'";
        $hr_department_ids = $this->db->query($get_hr_department)->row()->staffids;


        $get_hr_name_query = "SELECT
            DISTINCT(firstname) as name
        FROM
            `tblstaff`
        LEFT JOIN `tblstaff_departments` ON tblstaff.staffid = tblstaff_departments.staffid
        WHERE
            departmentid IN($hr_department_ids) AND active = 1 AND admin = 0;";


        $hr_names = $this->db->query($get_hr_name_query)->result_array();
        return $hr_names;
    }

    public function get_candidates_data()
    {


        $hr_names = $this->get_hr_data();

        $date = date('Y-m-d', strtotime("-1 days"));

        $candidates_data = array();

        foreach ($hr_names as $hr_name) {
            $search_candidate_query = "SELECT
                tblrec_job_position.position_name,
                COUNT(tblrec_candidate.candidate_code) AS candidates
            FROM
                tblrec_job_position
            LEFT JOIN 
                tblrec_campaign 
                ON tblrec_job_position.position_id = tblrec_campaign.cp_position
            LEFT JOIN 
                tblrec_candidate 
                ON tblrec_candidate.rec_campaign = tblrec_campaign.cp_id
                AND tblrec_candidate.candidate_code LIKE '%" . $hr_name['name'] . "%'
                AND DATE(tblrec_candidate.DATE_ADD) = '" . $date . "'
            GROUP BY
                tblrec_job_position.position_name
            ORDER BY 
                tblrec_job_position.position_id   
                ";

            $candidates_data[$hr_name['name']] = $this->db->query($search_candidate_query)->result_array();
        }
        echo '<pre>';

        // print_r(array_column($candidates_data,'position_name'));
        print_r($candidates_data);

        return $candidates_data;
    }

    public function hr_recruit_email($candidates_data)
    {

        $get_job_position_query = "select position_name from tblrec_job_position;";

        $job_positions = $this->db->query($get_job_position_query)->result_array();


        // print_r($job_positions); die;
        $date = date('m/d/Y', strtotime("-1 days"));
        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
            $this->email->to('sarabjeet@tech2globe.net');
            $this->email->cc(array('ishan.negi@tech2globe.in', 'naved.ahamad@tech2globe.in', 'sarabjeet@tech2globe.com'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Daily HR recruit report - $date";

            $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Weekly IT Asset</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid black;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #2600bd;
                            color : white;
                        }

                        td {
                            color : black;
                        }
                    </style>
                </head>
                <body>
                <h3>HR Recruit report</h3>
                <table>
                    <thead>
                        <tr>
                            <th>HR Name</th>
                            
                ';


            foreach ($job_positions as $job_position) {
                $message .= '<th>' . $job_position['position_name'] . '</th>';
            }

            $message .= '
                <th>Total</th>
                </tr>
                    </thead>
                    <tbody>
                ';

            foreach ($candidates_data as $hr => $data_per_hr) {
                $message .= '<tr><td>' . $hr . '</td>';
                $count = 0;
                foreach ($data_per_hr as $data) {
                    $message .= '<td>' . $data['candidates'] . '</td>';
                    $count += $data['candidates'];
                }
                $message .= '<td>' . $count . '</td></tr>';
            }




            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();

            log_message('error', "hr recruit email sent!");
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }




    public function hr_recruit_report_run($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom

            if (date('D') != 'Mon') {
                $candidates_data = $this->get_candidates_data();
                $this->hr_recruit_email($candidates_data);
            }
            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }

    // custom report model function

    public function workroom_report_run($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom

            if (date('D') != 'Mon') {
                $working_hours_summary = $this->working_hours_summary();
                $less_than_9_hrs_data = $this->less_than_9_hrs_monthly_summary();
                // $consecutive_three_days = $this->consecutive_three_days();
                $this->daily_report_email($working_hours_summary, $less_than_9_hrs_data);
                // $this->consecutive_three_days_email($consecutive_three_days);
            }

            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }


    public function it_asset_report_run($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom


            $data = $this->get_asset_data();
            $this->it_asset_weekly_email($data);


            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }

    public function update_pending_leaves_monthly()
    {
        $prev_month = date('n', strtotime('first day of previous month'));

        $staff_query = "SELECT staffid FROM tblstaff WHERE active = 1";
        $staffs = $this->db->query($staff_query)->result_array();

        foreach ($staffs as $staff) {

            $staffid = $staff['staffid'];

            // get total leaves
            $query = "SELECT SUM(number_of_days) AS leaves 
                    FROM tbltimesheets_requisition_leave 
                    WHERE staff_id = $staffid 
                    AND status = 0 
                    AND MONTH(start_time) = $prev_month";

            $total_leaves = $this->db->query($query)->row()->leaves;

            if (!$total_leaves) {
                $total_leaves = 0;
            }

            echo "Total leaves are : $total_leaves \n";

            // get latest record id
            $latest = $this->db->query("
                SELECT id 
                FROM tbltimesheets_requisition_leave
                WHERE staff_id = $staffid
                AND MONTH(start_time) = $prev_month
                ORDER BY id DESC
                LIMIT 1
            ")->row();

            // update leave balance for latest record
            if ($latest) {
                $update_query = "UPDATE tbltimesheets_requisition_leave 
                                SET leave_balance = leave_balance - $total_leaves 
                                WHERE id = {$latest->id}";

                $this->db->query($update_query);
            }
        }

        return true;
    }
    public function update_carry_and_leave_balance_monthly()
    {
        $prev_month = date('n', strtotime('first day of previous month'));

        $staff_query = "SELECT staffid FROM tblstaff WHERE active = 1";
        $staffs = $this->db->query($staff_query)->result_array();

        foreach ($staffs as $staff) {

            $staffid = $staff['staffid'];

            // Get latest leave record for previous month
            $query = "SELECT * 
                    FROM tbltimesheets_requisition_leave 
                    WHERE staff_id = $staffid 
                    AND MONTH(start_time) = $prev_month 
                    ORDER BY id DESC 
                    LIMIT 1";

            $result = $this->db->query($query)->row();

            // Prevent error if no record exists
            $leave_balance = 0;
            if ($result) {
                $leave_balance = $result->leave_balance;
            }

            // Calculate carry forward
            if ($leave_balance < 0) {
                $new_carry_forward = 0;
            } else {
                $new_carry_forward = $leave_balance;
            }

            // FY carry cap when entering April (prev month = March).
            $curr_month = (int) date('n');
            if ($curr_month === 4) {
                if (!isset($this->staff_model)) {
                    $this->load->model('staff_model');
                }
                $cap = (float) $this->staff_model->get_leave_carry_forward_cap($staffid);
                if ($new_carry_forward > $cap) {
                    $new_carry_forward = $cap;
                }
            }

            // Get earned leaves (0 after resignation / first employment month, etc.)
            $earned_leaves = get_earned_leaves($staffid);

            // Calculate new balance
            $new_leave_balance = $new_carry_forward + $earned_leaves;

            echo "carry forward : " . $new_carry_forward . "\n leave balance : " . $new_leave_balance . "\n";

            $curr_date_time = date('Y-m-d H:i:s');

            // Cast numbers to avoid SQL issues
            $new_carry_forward = (float)$new_carry_forward;
            $new_leave_balance = (float)$new_leave_balance;

            $query = "INSERT INTO tbltimesheets_requisition_leave (
                staff_id,
                subject,
                start_time,
                end_time,
                datecreated,
                carry_forward,
                leave_balance
            ) VALUES (
                $staffid,
                'Monthly carry forward and leave balance',
                '$curr_date_time',
                '$curr_date_time',
                '$curr_date_time',
                $new_carry_forward,
                $new_leave_balance
            )";

            $this->db->query($query);
        }
    }

    public function update_leave_balance($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);

            update_option('last_cron_run', time());

            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom


            $this->update_pending_leaves_monthly();
            $this->update_carry_and_leave_balance_monthly();


            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }

    public function biweekly_document_report($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom


            $staffs = $this->staff_list_compliances_and_documents();

            $this->hr_docs_biweekly_report($staffs);


            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }


    function check_document_uploaded($staffs)
    {


        $data = [];

        foreach ($staffs as $staff) {
            if (!$staff['identification'] || !$staff['_10_marksheet'] || !$staff['_12_marksheet'] || !$staff['declaration_signature'] || !$staff['appointment_letter'] || $staff['appointment_letter'] == 'no' || !$staff['coi_letter'] || $staff['coi_letter'] == 'no' || !$staff['nda'] || $staff['nda'] == 'no' || !$staff['policy_document'] || $staff['policy_document'] == 'no' || !$staff['appointment_letter'] || $staff['appointment_letter'] == 'no' || !$staff['bio_enroll'] || $staff['bio_enroll'] == 'no' || !$staff['join_kit'] || $staff['join_kit'] == 'no' || !$staff['id_card'] || $staff['id_card'] == 'no' || !$staff['bgv'] || $staff['bgv'] == 'no') {
                $data[] = $staff;
            }
        }

        return $data;
    }


    function staff_list_compliances_and_documents()
    {
        $staffs = $this->db->query('SELECT
            *,
            (
            SELECT
                GROUP_CONCAT(
                    tblstaff_departments.departmentid
                )
            FROM
                tblstaff_departments
            WHERE
                staffid = tblstaff.staffid
        ) AS departmentids
        FROM
            `tblstaff`
        
        WHERE active = 1 AND staffid != 1')->result_array();

        foreach ($staffs as &$data) {

            if ($data['departmentids']) {

                $data['department_name'] = $this->db->query("select GROUP_CONCAT(tbldepartments.name SEPARATOR ', ') AS department_names from tbldepartments where departmentid IN (" . $data['departmentids'] . ')')->row()->department_names;
            } else {
                $data['department_name'] = '';
            }
        }

        $data = $this->check_document_uploaded($staffs);

        return $data;
    }

    public function hr_docs_biweekly_report($staffs)
    {

        $date = date('m/d/Y');


        try {

            $this->email->set_mailtype("html");
            $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
             $this->email->to('hr@tech2globe.com');
            //$this->email->to('naved.ahamad@tech2globe.in');

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Staff list for documents not uploaded- T2GWorkroom - $date ";

            $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Performance review</title>
                <style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #2600bd;
                        color : white;
                    }

                    td {
                        color : black;
                    }
                </style>
            </head>
            <body>
            
            ';







            $message .= "
            <h2>Documents Not Uploaded</h2>
            <table>
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Reporting Person</th>
                            <th>Department</th> 
                             
                        </tr>
                    </thead>
                <tbody>";


            foreach ($staffs as $key => $staff) {
                $sno = $key + 1;
                $message .= "<tr>
                <td>" . $sno . "</td>
                <td>" . $staff['staff_identifi'] . "</td>
                <td>" . $staff['firstname'] . ' ' . $staff['lastname'] . "</td>
                <td>" . get_staff_full_name($staff['team_manage']) . "</td>
                <td>" . $staff['department_name'] . "</td>
               
                </tr>";
            }



            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent';
            }

            log_message('error', 'Workroom report email sent!');
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }

    public function get_birthday_and_anniversary()
    {

        $date = date('m-d');

        $data = $this->birthday_model->get_upcoming_anniversaries();
        foreach ($data as $staff) {

            echo '<pre>';
            print_r($staff);

            $formatted_birthday = '';
            $formatted_doj = '';
            if ($staff->birthday) {
                $birth_datetime = new DateTime($staff->birthday);
                $formatted_birthday = $birth_datetime->format('m-d');
            }

            if ($staff->doj) {
                $doj_datetime = new DateTime($staff->doj);
                $formatted_doj = $doj_datetime->format('m-d');
            }



            $currentDateTime = new DateTime();

            $staff_email = $staff->email;
            $reporting_person_email = get_staff_email_id($staff->team_manage);


            $imagePath = staff_profile_image_url($staff->staffid); // Local path to your image
            $imageData = base64_encode(file_get_contents($imagePath));
            $src = 'data:image/jpeg;base64,' . $imageData;

            if ($date == $formatted_birthday) {

                try {

                    $this->email->set_mailtype("html");
                    $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
                    $this->email->to($staff_email);

                    // $this->email->to('sarabjeet@tech2globe.net');
                    $this->email->cc(array($reporting_person_email, 'hr@tech2globe.com'));

                    $subject = "Wishing you a very Happy Birthday! " . get_staff_full_name($staff->staffid);

                    $message = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Performance review</title>
                        <style>
                            h5 {
                                margin: 5px;
                            }

                            .card-header {
                                background-color: #1E293B;
                                color: white;
                                text-align: center;
                            }



                            .icon {
                                font-size: 30px;
                                color: #1E293B;
                            }

                            .event-title {

                                font-weight: bold;
                            }

                            .event-department {
                                color: #6c757d;

                            }

                            .event-message {
                                color: #0F172B;

                            }

                            .event-icon {
                                color: #1E293B;
                                margin-right: 10px;
                            }

                            .birthday .event-icon {
                                color: #ff4081;
                            }

                            .anniversary .event-icon {
                                color: #1E90FF;
                            }

                            .full-view {
                                background-color: #f9fafb;
                                padding: 30px;
                                border-radius: 10px;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                           
                            .anniversary-view {
                                background-color: #E1F5FE;
                                background-image: url("https://www.transparenttextures.com/patterns/my-little-plaid.png");
                            }

                            .full-view img {
                                width: 120px;
                                height: 120px;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 4px solid #1E293B;
                            }

                            .full-view.birthday-view img {
                                border: 3px solid #ff4081;
                            }

                            .full-view.anniversary-view img {
                                border: 3px solid #1E90FF;
                            }

                            .full-view .employee-name {

                                font-weight: bold;
                                color: #1E293B;
                                margin-top: 10px;
                            }



                            .d-flex {
                                display: flex;
                            }

                            .full-view .employee-dept {
                                color: #6c757d;

                            }

                            .full-view.birthday-view .employee-dept,
                            .full-view.birthday-view .employee-date,
                            .full-view.birthday-view .greeting-message,
                            .full-view.birthday-view .employee-name {
                                color: #ff4081;
                            }

                            .full-view.anniversary-view .employee-dept,
                            .full-view.anniversary-view .employee-date,
                            .full-view.anniversary-view .greeting-message,
                            .full-view.anniversary-view .employee-name {
                                color: #1E90FF;
                            }

                            .full-view .greeting-message {

                                color: #0F172B;
                                margin-top: 20px;
                                font-style: italic;
                            }


                            .full-view .emoji {
                                font-size: 2rem;
                            }

                            .list-group-item:hover {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .list-group-item:active {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .event-title {
                                display: flex;
                                justify-content: space-between;
                                align-items: baseline;
                            }

                            .event-date {

                                color: #6c757d;
                            }

                            .list-group-item.current {
                                background-color: #f1f5f9
                            }
                    </style>
                    </head>
                    <body>
                    
                    ';

                    $message .= '
                        <div style="width:100%; text-align:center; padding:20px; background-color:#f4f4f4; background-image: url(\'https://www.transparenttextures.com/patterns/arches.png\');" class=" anniversary-view">
                            <div style="max-width:600px; margin:auto; background-color:#fff; padding:20px; border-radius:8px;">
                                <div style="text-align:center;">
                                    <img src="' . $src . '" alt="Employee Image" style="border-radius:50%; width:120px; height:120px; margin-bottom:15px;">
                                </div>
                                <div style="font-family:Arial, sans-serif; text-align:center; color:#333;">
                                    <h3 style="margin:0; padding:0; font-size:20px; font-weight:bold;">' . get_staff_full_name($staff->staffid) . ' (' . get_staff_emp_id($staff->staffid) . ')' . '</h3>
                                    <p style="margin:5px 0; font-size:16px; color:#555;">' . get_job_position_by_staffid($staff->staffid) . '</p>
                                    <p style="margin:5px 0; font-size:16px; color:#888;">' . date('j-F') . '</p>
                                    <p style="margin:10px 0; font-size:18px; color:#333;">
                                        🎉 Wishing you a very Happy Birthday, ' . get_staff_full_name($staff->staffid) . '! 🎂</br> Enjoy your special day and have a wonderful year ahead! 🥳🎈
                                    </p>
                                    <p style="font-size:24px;">🎂🎁🎈</p>
                                </div>
                            </div>
                        </div>
                    ';
                    $this->email->subject($subject);
                    $this->email->message($message);
                    if ($this->email->send()) {
                        echo 'mail sent';
                    } else {
                        echo 'mail not sent';
                    }

                    log_message('error', 'Workroom report email sent!');

                    $this->email->clear();

                    // Optional: Add a delay between each email to avoid server throttling or spam flags
                    sleep(1); // Wait 1 second before sending the next email

                } catch (phpmailerException $e) {
                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
                } catch (Exception $e) {
                    echo $e->getMessage(); //Boring error messages from anything else!
                }
            }


            if ($date == $formatted_doj) {
                $interval = $doj_datetime->diff($currentDateTime);
                try {

                    $this->email->set_mailtype("html");
                    $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
  
                    $this->email->to($staff_email);

                    // $this->email->to('sarabjeet@tech2globe.net');
                    $this->email->cc(array($reporting_person_email, 'hr@tech2globe.com'));

                    // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
                    $subject = "Congratulations on completing $interval->y year(s) with us! " . get_staff_full_name($staff->staffid);

                    $message = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Performance review</title>
                        <style>
                            h5 {
                                margin: 5px;
                            }

                            .card-header {
                                background-color: #1E293B;
                                color: white;
                                text-align: center;
                            }



                            .icon {
                                font-size: 30px;
                                color: #1E293B;
                            }

                            .event-title {

                                font-weight: bold;
                            }

                            .event-department {
                                color: #6c757d;

                            }

                            .event-message {
                                color: #0F172B;

                            }

                            .event-icon {
                                color: #1E293B;
                                margin-right: 10px;
                            }

                            .birthday .event-icon {
                                color: #ff4081;
                            }

                            .anniversary .event-icon {
                                color: #1E90FF;
                            }

                            .full-view {
                                background-color: #f9fafb;
                                padding: 30px;
                                border-radius: 10px;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                            .birthday-view {
                                background-color: #FFE4E1;
                                background-image: url("https://www.transparenttextures.com/patterns/arches.png");
                            }

                         

                            .full-view img {
                                width: 120px;
                                height: 120px;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 4px solid #1E293B;
                            }

                            .full-view.birthday-view img {
                                border: 3px solid #ff4081;
                            }

                            .full-view.anniversary-view img {
                                border: 3px solid #1E90FF;
                            }

                            .full-view .employee-name {

                                font-weight: bold;
                                color: #1E293B;
                                margin-top: 10px;
                            }



                            .d-flex {
                                display: flex;
                            }

                            .full-view .employee-dept {
                                color: #6c757d;

                            }

                            .full-view.birthday-view .employee-dept,
                            .full-view.birthday-view .employee-date,
                            .full-view.birthday-view .greeting-message,
                            .full-view.birthday-view .employee-name {
                                color: #ff4081;
                            }

                            .full-view.anniversary-view .employee-dept,
                            .full-view.anniversary-view .employee-date,
                            .full-view.anniversary-view .greeting-message,
                            .full-view.anniversary-view .employee-name {
                                color: #1E90FF;
                            }

                            .full-view .greeting-message {

                                color: #0F172B;
                                margin-top: 20px;
                                font-style: italic;
                            }


                            .full-view .emoji {
                                font-size: 2rem;
                            }

                            .list-group-item:hover {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .list-group-item:active {
                                background-color: #f1f5f9;
                                cursor: pointer;
                            }

                            .event-title {
                                display: flex;
                                justify-content: space-between;
                                align-items: baseline;
                            }

                            .event-date {

                                color: #6c757d;
                            }

                            .list-group-item.current {
                                background-color: #f1f5f9
                            }
                    </style>
                    </head>
                    <body>
                    
                    ';

                    $message .= '
                        <div style="width:100%; text-align:center; padding:20px; background-color:#E1F5FE; background-image: url(\'https://www.transparenttextures.com/patterns/my-little-plaid.png\');" class="anniversary-view">
                            <div style="max-width:600px; margin:auto; background-color:#fff; padding:20px; border-radius:8px;">
                                <div style="text-align:center;">
                                    <img src="' . $src  . '" alt="Employee Image" style="border-radius:50%; width:120px; height:120px; margin-bottom:15px;">
                                </div>
                                <div style="font-family:Arial, sans-serif; text-align:center; color:#333;">
                                    <h3 style="margin:0; padding:0; font-size:20px; font-weight:bold;">' . get_staff_full_name($staff->staffid) . ' (' . get_staff_emp_id($staff->staffid) . ')' . '</h3>
                                    <p style="margin:5px 0; font-size:16px; color:#555;">' . get_job_position_by_staffid($staff->staffid) . '</p>
                                    <p style="margin:5px 0; font-size:16px; color:#888;">' . date('j-F') . '</p>
                                    <p style="margin:10px 0; font-size:18px; color:#333;">
                                        🎊 Congratulations on your ' . $interval->y . '-year work anniversary, ' . get_staff_full_name($staff->staffid) . '! 🎉<br>
                                        Thank you for being such a valuable part of our team. 🎖️
                                    </p>
                                    <p style="font-size:24px;">🎂🎁🎈</p>
                                </div>
                            </div>
                        </div>
                    ';

                    $this->email->subject($subject);
                    $this->email->message($message);
                    if ($this->email->send()) {
                        echo 'mail sent';
                    } else {
                        echo 'mail not sent';
                    }

                    $this->email->clear();

                    // Optional: Add a delay between each email to avoid server throttling or spam flags
                    sleep(1); // Wait 1 second before sending the next email

                    log_message('error', 'Workroom report email sent!');
                } catch (phpmailerException $e) {
                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
                } catch (Exception $e) {
                    echo $e->getMessage(); //Boring error messages from anything else!
                }
            }
        }
    }


    public function birthday_anniversary_email_run($manually = false)

    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);



            update_option('last_cron_run', time());



            if ($manually == true) {

                $this->manually = true;



                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }



                log_activity('Cron Invoked Manually');
            }


            // running cron job for workroom


            $this->get_birthday_and_anniversary();


            /**

             * Finally send any emails in the email queue - if enabled and any

             */




            $this->email->send_queue();



            $last_email_queue_retry = get_option('last_email_queue_retry');



            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {

                $this->email->retry_queue();

                update_option('last_email_queue_retry', time());
            }



            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();



            app_maybe_delete_old_temporary_files();



            hooks()->do_action('after_cron_run', $manually);



            // For all cases try to release the lock after everything is finished

            $this->lockHandle();
        }
    }


    public function get_attendance_status($staff_id, $date)
    {
        $status = $this->db->query("SELECT type FROM tbltimesheets_timesheet WHERE staff_id = $staff_id and date_work = '$date'")->row_array();
        return $status['type'];
    }

    public function previous_week_dates()
    {

        // Get today's date
        $today = new DateTime();

        // Calculate the start date (Monday) of the previous week
        $startDate = clone $today;
        $startDate->modify('monday last week');

        // Calculate the end date (Sunday) of the previous week
        $endDate = clone $today;
        $endDate->modify('sunday last week');

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->modify('+1 day')); // Increment by 1 day

        $lastWeekDates = [];
        foreach ($period as $date) {
            $lastWeekDates[] = $date->format('Y-m-d'); // Format date as needed
        }

        return $lastWeekDates;
    }

   public function check_in_out_details()
{
    $dates = $this->previous_week_dates();
    $managers = get_all_managers();
    $data = array();
    $format = 'Y-m-d H:i:s';

    foreach ($managers as $manager) {
        $id = $manager['staffid'];
        $query = "SELECT staffid FROM tblstaff WHERE team_manage = $id AND active = 1";
        $team_members = $this->db->query($query)->result_array();

        $staff_data = array();

        foreach ($team_members as $member) {
            $team_staffid = $member['staffid'];

            // ✅ Fetch staff's departments
            $dept_query = "
                SELECT sd.departmentid 
                FROM tblstaff_departments sd
                INNER JOIN tbldepartments d ON d.departmentid = sd.departmentid
                WHERE sd.staffid = $team_staffid
            ";
            $dept_result = $this->db->query($dept_query)->result_array();
            $dept_ids = array_column($dept_result, 'departmentid');

            foreach ($dates as $date) {
                $is_saturday = (date('N', strtotime($date)) == 6); // Saturday = 6
                $is_saturday_leave = false;

                if ($is_saturday) {
                    $month = date('m', strtotime($date));

                    // Build condition for departments
                    $dept_condition = '';
                    if (!empty($dept_ids)) {
                        $dept_condition = ' OR department_id IN (' . implode(',', $dept_ids) . ')';
                    }

                    $query_holiday = "
                        SELECT 1 FROM tblholiday
                        WHERE saturday_date = '$date'
                        AND (
                            staffid = $team_staffid
                            $dept_condition
                        )
                        AND from_month <= $month AND to_month >= $month
                        LIMIT 1
                    ";

                    $is_saturday_leave = $this->db->query($query_holiday)->num_rows() > 0;
                }

                if ($is_saturday_leave) {
                    $staff_data[$team_staffid][$date] = [
                        'check_in' => 'Saturday Leave',
                        'check_out' => 'Saturday Leave',
                        'working_hrs' => '00:00'
                    ];
                    continue;
                }

                // Normal check-in
                $query1 = "SELECT * FROM tblcheck_in_out WHERE DATE(date) = '$date' AND staff_id = $team_staffid AND type_check = 1";
                $check_in_data = $this->db->query($query1)->row_array();

                $is_night_shift = is_night_shift($team_staffid);

                if ($is_night_shift) {
                    $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
                    $query2 = "SELECT * FROM tblcheck_in_out WHERE DATE(date) = '$next_date' AND staff_id = $team_staffid AND type_check = 2";
                } else {
                    $query2 = "SELECT * FROM tblcheck_in_out WHERE DATE(date) = '$date' AND staff_id = $team_staffid AND type_check = 2";
                }

                $check_out_data = $this->db->query($query2)->row_array();

                if (
                    (!empty($check_in_data) && isset($check_in_data['date']) && $check_in_data['date']) ||
                    (!empty($check_out_data) && isset($check_out_data['date']) && $check_out_data['date'])
                ) {
                    $formatted_date1 = !empty($check_in_data['date']) ? DateTime::createFromFormat($format, $check_in_data['date']) : null;
                    $formatted_date2 = !empty($check_out_data['date']) ? DateTime::createFromFormat($format, $check_out_data['date']) : null;

                    if ($formatted_date1 && $formatted_date2) {
                        $interval = $formatted_date1->diff($formatted_date2);
                        $time_diff = sprintf('%02d:%02d', $interval->h, $interval->i);
                        $check_in_time = $formatted_date1->format('H:i:s');
                        $check_out_time = $formatted_date2->format('H:i:s');
                    } else {
                        $check_in_time = $formatted_date1 ? $formatted_date1->format('H:i:s') : '';
                        $check_out_time = $formatted_date2 ? $formatted_date2->format('H:i:s') : '';
                        $time_diff = '';
                    }

                    $staff_data[$team_staffid][$date] = [
                        'check_in' => $check_in_time,
                        'check_out' => $check_out_time,
                        'working_hrs' => $time_diff
                    ];
                }
            }
        }

        $data[$id] = $staff_data;
    }

    print_r($data);
    return $data;

}

  
   
	
	public function send_utilization_report_day(){
		
		$managers = get_all_managers();
        $data = array();
        $format = 'Y-m-d H:i:s';
        foreach ($managers as $manager) {
            $id = $manager['staffid'];
            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');



            $staff_data = array();
            foreach ($team_staffids as $team_staffid) {	
				/*echo "SELECT
						tblprojects.name AS project_name,
						tblprojects.description AS project_description,
						tbltasks.name AS task_name,
						tbltasks.description AS task_description,
						FROM_UNIXTIME(start_time, '%Y-%m-%d %H:%i:%s') AS start_time,
						FROM_UNIXTIME(end_time, '%Y-%m-%d %H:%i:%s') AS end_time,
						FROM_UNIXTIME(start_time, '%Y-%m-%d') AS currdate,
						FROM_UNIXTIME(start_time, '%H:%i:%s') AS start_time,
						FROM_UNIXTIME(end_time, '%H:%i:%s') AS end_time,
						tblstaff.firstname,
						tblstaff.lastname,
						tblstaff.staff_identifi,
						tblcheck_in_out.type_check,
						DATE_FORMAT(tblcheck_in_out.date, '%Y-%m-%d') AS datee,
						tblcheck_in_out.date AS DATE
					FROM
						`tblprojects`
					LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
					LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
					LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
					LEFT JOIN tblcheck_in_out ON tblstaff.staffid = tblcheck_in_out.staff_id
					WHERE  tbltaskstimers.staff_id = $team_staffid  AND
					   tblcheck_in_out.date  >= DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND
					   FROM_UNIXTIME(start_time, '%Y-%m-%d')>= DATE_ADD(CURDATE(), INTERVAL -1 DAY);";*/
			/*	$query = "SELECT
					tblprojects.name AS project_name,
					tblprojects.description AS project_description,
					tbltasks.name AS task_name,
					tbltasks.description AS task_description,
					FROM_UNIXTIME(start_time, '%Y-%m-%d %H:%i:%s') AS start_time,
					FROM_UNIXTIME(end_time, '%Y-%m-%d %H:%i:%s') AS end_time,
					FROM_UNIXTIME(start_time, '%Y-%m-%d') AS currdate,
					FROM_UNIXTIME(start_time, '%H:%i:%s') AS start_time,
					FROM_UNIXTIME(end_time, '%H:%i:%s') AS end_time,
					tblstaff.firstname,
					tblstaff.lastname,
					tblstaff.staff_identifi,
					tblcheck_in_out.type_check,
					DATE_FORMAT(tblcheck_in_out.date, '%Y-%m-%d') AS datee,
					tblcheck_in_out.date AS DATE
				FROM
					`tblprojects`
				LEFT JOIN tbltasks ON tblprojects.id = tbltasks.rel_id
				LEFT JOIN tbltaskstimers ON tbltasks.id = tbltaskstimers.task_id
				LEFT JOIN tblstaff ON tbltaskstimers.staff_id = tblstaff.staffid
				LEFT JOIN tblcheck_in_out ON tblstaff.staffid = tblcheck_in_out.staff_id
				WHERE  tbltaskstimers.staff_id = $team_staffid  AND
				   tblcheck_in_out.date  >= DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND
				   FROM_UNIXTIME(start_time, '%Y-%m-%d')>= DATE_ADD(CURDATE(), INTERVAL -1 DAY);";
				   
                    $check_in_data = $this->db->query($query)->row_array();*/
						//echo "<pre>";print_r($check_in_data);
			}
			
		}die;
	}

    function getPreviousDayTimestamp() {
        // Get the timestamp for the current date
        $currentDate = date('Y-m-d');
        
        // Subtract one day
        $previousDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
        
        // Combine previous date with 3:30 AM
        $previousDayTime = $previousDate . ' 09:00:00';
        
        // Convert to Unix timestamp
        $unixTimestamp = strtotime($previousDayTime);
        
        return $unixTimestamp;
    }

    function getPreviousDayMidnightTimestamp() {
        // Get the timestamp for the current date
        $currentDate = date('Y-m-d');
        
        // Combine previous date with 12:30 AM
        $previousDayTime = $currentDate . '06:00:00';
        
        // Convert to Unix timestamp
        $unixTimestamp = strtotime($previousDayTime);
        
        return $unixTimestamp;
    }    

    function formatTimeDifference($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
    
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    public function get_timesheet_data($id) {
       
        $result['timesheets'] = [];

        $start_time = $this->getPreviousDayTimestamp();
        $end_time = $this->getPreviousDayMidnightTimestamp();

        $this->db->select('
            task_id,
            start_time,
            end_time,
            staff_id,
            ' . db_prefix() . 'taskstimers.hourly_rate,
            ' . db_prefix() . 'tasks.name as taskName,
            ' . db_prefix() . 'taskstimers.id,
			' . db_prefix() . 'tasks.hourly_rate as hourly_rates,
            rel_id,
			qty,
            rel_type,
            billed,
            ' . db_prefix() . 'projects.name as projectName
        ');
        $this->db->from(db_prefix() . 'taskstimers');
        $this->db->where('staff_id', $id);
        $this->db->where('start_time >=', $start_time);
        $this->db->where('end_time <=', $end_time);
        
        // Join tasks table
        $this->db->join(
            db_prefix() . 'tasks',
            db_prefix() . 'tasks.id = ' . db_prefix() . 'taskstimers.task_id',
            'left'
        );
        
        // Join projects table
        $this->db->join(
            db_prefix() . 'projects',
            db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id',
            'left'
        );
        
        $timers = $this->db->get()->result_array();
    
        $result['timesheets'] = $timers;

        return $result;
    }    

    public function timesheet_summary(){

        $data = $this->managers_list();

        $timesheet_summary = array();

        foreach ($data as $manager_id => $manager_name) {


            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];

            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid] = $this->get_timesheet_data($team_staffid);
            }
            $timesheet_summary[$manager_id]['team_data'] = $staff_data;
        }

        return $timesheet_summary;
    }
	
		public function timesheet_summary_manger() {
		$query = "SELECT s.staffid, s.team_manage, sd.departmentid, d.name as department_name 
				  FROM tblstaff s 
				  JOIN tblstaff_departments sd ON s.staffid = sd.staffid 
				  JOIN tbldepartments d ON sd.departmentid = d.departmentid 
				  WHERE s.active = 1";

		$results = $this->db->query($query)->result_array();

		$summary = [];

		foreach ($results as $row) {
			$dept_id = $row['departmentid'];
			$dept_name = $row['department_name'];
			$manager_id = $row['team_manage'];
			$staff_id = $row['staffid'];

			if (!isset($summary[$dept_id])) {
				$summary[$dept_id] = [
					'department_name' => $dept_name,
					'managers' => [],
					'team_data' => []
				];
			}

			if ($manager_id && !in_array($manager_id, $summary[$dept_id]['managers'])) {
				$summary[$dept_id]['managers'][] = $manager_id;
			}

			$summary[$dept_id]['team_data'][$staff_id] = $this->get_timesheet_data($staff_id);
		}

		return $summary;
	}

   public function send_daily_timesheet_mail($data) {
	   error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $this->email->set_mailtype("html");
    $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
    //echo "<prE>";print_r($data);die;
	   foreach ($data as $dept_id => $dept_data) {
		if (empty($dept_data['team_data']) || !is_array($dept_data['team_data'])) {
			continue;
		}

		$hasEntries = false;
		foreach ($dept_data['team_data'] as $staff) {
			if (!empty($staff['timesheets'])) {
				$hasEntries = true;
				break;
			}
		}

		if (!$hasEntries) {
			continue;
		}

		$to_emails = [];
		foreach ($dept_data['managers'] as $manager_id) {
			$to_emails[] = get_staff_email_id($manager_id);
		}

		if (empty($to_emails)) {
			continue;
		}

        $this->email->to($to_emails);
	 //  $this->email->to('naved.ahamad1@tech2globe.net');
        $this->email->cc(['sarabjeet@tech2globe.net', 'harpreet.singh@tech2globe.com']);

        $subject = "Daily Utilization Report: " . $dept_data['department_name'] . " - " 
                 . date('d-m-Y', strtotime('-1 day'));

        $message = $this->build_department_report($dept_data);
		print_r($message);
        $this->email->subject($subject);
        $this->email->message($message);

        if ($this->email->send()) {
            echo "Mail sent for department: " . $dept_data['department_name'] . "<br>";
        } else {
            echo "Mail NOT sent for department: " . $dept_data['department_name'] . "<br>";
        }

        $this->email->clear();
    }
}


private function build_department_report($dept_data) {
    $message = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Performance review</title>
    </head>
    <body style="font-family: Arial, sans-serif; font-size:14px; color:#333;">
    <h2 style="color:#004085;">Daily Utilization Report: ' . $dept_data['department_name'] . '</h2>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#004085; color:#fff;">
                <th style="padding:10px; border:1px solid #ccc;">Name</th>
                <th style="padding:10px; border:1px solid #ccc;">Project</th>
                <th style="padding:10px; border:1px solid #ccc;">Task</th>
                <th style="padding:10px; border:1px solid #ccc;">Start Time</th>
                <th style="padding:10px; border:1px solid #ccc;">End Time</th>
                <th style="padding:10px; border:1px solid #ccc;">Time (h)</th>
                <th style="padding:10px; border:1px solid #ccc;">AHT</th>
				<th style="padding:10px; border:1px solid #ccc;">QTY</th>
            </tr>
        </thead>
        <tbody>';
        
    foreach ($dept_data['team_data'] as $staff_id => $staff_data) {
        $breakTimeSeconds = 0;
        $totalWorkTimeSeconds = 0;
		
        foreach ($staff_data['timesheets'] as $idx => $task) {
            $timeDifference = $task['end_time'] - $task['start_time'];

            if (in_array(strtolower($task['taskName']), ['lunch break', 'tea break', 'break time', 'dinner break'])) {
                $breakTimeSeconds += $timeDifference;
                $rowStyle = 'background-color:#fff3cd; color:#856404;';
            } else {
                $totalWorkTimeSeconds += $timeDifference;
                $rowStyle = 'background-color:#f9f9f9;';
            }

            $formattedDifference = $this->formatTimeDifference($timeDifference);

            $message .= '<tr style="' . $rowStyle . '">';
            if ($idx == 0) {
                $message .= '<td rowspan="' . count($staff_data['timesheets']) . '" style="border:1px solid #ccc; padding:8px;">'
                         . get_staff_full_name($staff_id) . ' - ' . get_staff_emp_id($staff_id) . '</td>';
            }

            $message .= '<td style="border:1px solid #ccc; padding:8px;">' . $task['projectName'] . '</td>
                         <td style="border:1px solid #ccc; padding:8px;">' . $task['taskName'] . '</td>
                         <td style="border:1px solid #ccc; padding:8px;">' . date('Y-m-d H:i:s', $task['start_time']) . '</td>
                         <td style="border:1px solid #ccc; padding:8px;">' . date('Y-m-d H:i:s', $task['end_time']) . '</td>
                         <td style="border:1px solid #ccc; padding:8px;">' . $formattedDifference . '</td>
                         <td style="border:1px solid #ccc; padding:8px;">' . $task['hourly_rates'] . '</td>
						 <td style="border:1px solid #ccc; padding:8px;">' . $task['qty'] . '</td>
                     </tr>';
        }

        // Add break and work total rows
        if ($totalWorkTimeSeconds > 0 || $breakTimeSeconds > 0) {
            $message .= '<tr style="background:#fff8e1; font-weight:bold;">
                <td colspan="7" style="text-align:right; padding:8px; border:1px solid #ccc;">Break Time:</td>
                <td style="padding:8px; border:1px solid #ccc;">' . $this->formatTimeDifference($breakTimeSeconds) . '</td>
            </tr>
            <tr style="background:#d4edda; font-weight:bold;">
                <td colspan="7" style="text-align:right; padding:8px; border:1px solid #ccc;">Total Work Time:</td>
                <td style="padding:8px; border:1px solid #ccc;">' . $this->formatTimeDifference($totalWorkTimeSeconds) . '</td>
            </tr>';
        }
    }

    $message .= '</tbody></table></body></html>';
	print_r($message);
    return $message;
}


    public function send_daily_timesheet_report_run($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);

            update_option('last_cron_run', time());

            if ($manually == true) {

                $this->manually = true;

                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }

                log_activity('Cron Invoked Manually');
            }

            if (date('D') != 'Mon') {
                // running cron job for workroom
                $data = $this->timesheet_summary_manger();
                $this->send_daily_timesheet_mail($data);
            }

            /**

             * Finally send any emails in the email queue - if enabled and any

             */

            $this->email->send_queue();

            $last_email_queue_retry = get_option('last_email_queue_retry');

            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {
                $this->email->retry_queue();
                update_option('last_email_queue_retry', time());
            }

            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();

            app_maybe_delete_old_temporary_files();

            hooks()->do_action('after_cron_run', $manually);

            // For all cases try to release the lock after everything is finished
            $this->lockHandle();
        }
    }

 

	/* Less then 9 hour*/
	
	
	public function get_timesheet_data_9hour($id) {
			$result['timesheets'] = [];
		 
			// Define previous day range
			$start_time = $this->getPreviousDayTimestamp(); // Midnight of previous day
			$end_time = $this->getPreviousDayMidnightTimestamp();       // Midnight of today
		 
			// Get total worked seconds for the staff
			$this->db->select_sum('(end_time - start_time)', 'total_seconds');
			$this->db->where('staff_id', $id);
			$this->db->where('start_time >=', $start_time);
			$this->db->where('start_time <', $end_time);
			$query = $this->db->get(db_prefix() . 'taskstimers');
			$total = $query->row();
		 
			// Check if total time is less than 9 hours (32400 seconds)
			if ($total->total_seconds > 0 && $total->total_seconds < 32400) {
				$this->db->select('
					task_id,
					start_time,
					end_time,
					staff_id,
					' . db_prefix() . 'taskstimers.hourly_rate,
					' . db_prefix() . 'tasks.name as taskName,
					' . db_prefix() . 'taskstimers.id,
					rel_id,
					rel_type,
					billed,
					' . db_prefix() . 'projects.name as projectName
				');
				$this->db->from(db_prefix() . 'taskstimers');
				$this->db->where('staff_id', $id);
				$this->db->where('start_time >=', $start_time);
				$this->db->where('start_time <', $end_time);
		 
				// Join tasks and projects
				$this->db->join(
					db_prefix() . 'tasks',
					db_prefix() . 'tasks.id = ' . db_prefix() . 'taskstimers.task_id',
					'left'
				);
				$this->db->join(
					db_prefix() . 'projects',
					db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id',
					'left'
				);
				
				$timers = $this->db->get()->result_array();
				$result['timesheets'] = $timers;
				$result['total_seconds'] = $total->total_seconds;
			}
		 
    return $result;
}
	
	public function timesheet_summary_9hour(){

        $data = $this->managers_list();

        $timesheet_summary_9hour = array();

        foreach ($data as $manager_id => $manager_name) {


            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];

            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid] = $this->get_timesheet_data_9hour($team_staffid);
            }
            $timesheet_summary_9hour[$manager_id]['team_data'] = $staff_data;
        }

        return $timesheet_summary_9hour;
    }
	
	public function send_daily_timesheet_mail_less_then_9hour($data)
		{
			$this->email->set_mailtype("html");
			$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');

			$subject = "Daily Less Than 9 Hours Report: " . date('d-m-Y', strtotime('-1 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Daily Utilization Report</h2>';

			$hasData = false; // To check if at least one manager has data

			foreach ($data as $manager_id => $manager_data) {
				$userData = $manager_data['team_data'];
				if (empty($userData)) {
					continue;
				}

				$managerName = get_staff_full_name($manager_id);
				$message .= '<h3>Manager: ' . $managerName . '</h3>';

				$managerHasEmployee = false;

				foreach ($userData as $staff_id => $staff_data) {
					if (empty($staff_data['timesheets'])) {
						continue;
					}

					$hasData = true;
					$managerHasEmployee = true;

					$breakTimeSeconds = 0;
					$totalWorkTimeSeconds = 0;
					$rowCount = count($staff_data['timesheets']);
					$isFirstTask = true;

					$message .= '<p><strong>Employee: ' . get_staff_full_name($staff_id) . ' - ' . get_staff_emp_id($staff_id) . '</strong></p>';
					$message .= '<table>
									<thead>
										<tr>
											<th>Project</th>
											<th>Task</th>
											<th>Start Time</th>
											<th>End Time</th>
											<th>Time (h)</th>
										</tr>
									</thead>
									<tbody>';

					foreach ($staff_data['timesheets'] as $task) {
						$timeDifference = $task['end_time'] - $task['start_time'];
						$formattedDifference = $this->formatTimeDifference($timeDifference);

						if (in_array(strtolower($task['taskName']), ['lunch break', 'tea break', 'break time', 'dinner break'])) {
							$breakTimeSeconds += $timeDifference;
						} else {
							$totalWorkTimeSeconds += $timeDifference;
						}

						$message .= '<tr>
										<td>' . $task['projectName'] . '</td>
										<td>' . $task['taskName'] . '</td>
										<td>' . date('Y-m-d H:i:s', $task['start_time']) . '</td>
										<td>' . date('Y-m-d H:i:s', $task['end_time']) . '</td>
										<td>' . $formattedDifference . '</td>
									</tr>';
					}

					$formattedBreakTime = $this->formatTimeDifference($breakTimeSeconds);
					$formattedTotalTime = $this->formatTimeDifference($totalWorkTimeSeconds + $formattedBreakTime);

					$message .= '<tr>
									<td colspan="4" style="text-align:right; font-weight:bold;">Break Time:</td>
									<td>' . $formattedBreakTime . '</td>
								 </tr>';
					$message .= '<tr>
									<td colspan="4" style="text-align:right; font-weight:bold;">Total Work Time:</td>
									<td>' . $formattedTotalTime . '</td>
								 </tr>';
					$message .= '</tbody></table>';
				}

				if (!$managerHasEmployee) {
					$message .= '<p>No employees worked less then 9 hour under this manager</p>';
				}
			}

			$message .= '</body></html>';

			if ($hasData) {
				//$this->email->to('naved.ahamad1@tech2globe.net'); // Replace with recipient
				$this->email->to('harpreet.singh@tech2globe.com');
				$this->email->cc(array('sarabjeet@tech2globe.net'));
				$this->email->subject($subject);
				$this->email->message($message);

				if ($this->email->send()) {
					echo 'Mail sent';
				} else {
					echo 'Mail not sent';
				}
				$this->email->clear();
			} else {
				echo "No employee available for any manager.";
			}
}



    public function send_daily_timesheet_report_run_less_then_9hour($manually = false)
    {
log_message('error', '=== Cron function entered ===');

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);

            update_option('last_cron_run', time());

            if ($manually == true) {

                $this->manually = true;

                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }

                log_activity('Cron Invoked Manually');
            }
                       log_message('error', '=== Cron timesheet controller hit. Day: ' . date('D') . ' ===');

           // if (date('D') != 'Mon') {
	   if (date('D') != 'Mon') {
                // running cron job for workroom
                $data = $this->timesheet_summary_9hour();
                $this->send_daily_timesheet_mail_less_then_9hour($data);
            }

            /**

             * Finally send any emails in the email queue - if enabled and any

             */

            $this->email->send_queue();

            $last_email_queue_retry = get_option('last_email_queue_retry');

            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {
                $this->email->retry_queue();
                update_option('last_email_queue_retry', time());
            }

            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();

            app_maybe_delete_old_temporary_files();

            hooks()->do_action('after_cron_run', $manually);

            // For all cases try to release the lock after everything is finished
            $this->lockHandle();
        }
    }
	
	/* Less then 9 hour end*/
	
	
	/* Greater then 10 hour */
	/* Less then 9 hour*/
	
	
	public function get_timesheet_data_10hour($id) {
			$result['timesheets'] = [];
		 
			// Define previous day range
			$start_time = $this->getPreviousDayTimestamp(); // Midnight of previous day
			$end_time = $this->getPreviousDayMidnightTimestamp();       // Midnight of today
		 
			// Get total worked seconds for the staff
			$this->db->select_sum('(end_time - start_time)', 'total_seconds');
			$this->db->where('staff_id', $id);
			$this->db->where('start_time >=', $start_time);
			$this->db->where('start_time <', $end_time);
			$query = $this->db->get(db_prefix() . 'taskstimers');
			$total = $query->row();
		 
			// Check if total time is less than 9 hours (32400 seconds)
			if ($total->total_seconds > 36000) {
				$this->db->select('
					task_id,
					start_time,
					end_time,
					staff_id,
					' . db_prefix() . 'taskstimers.hourly_rate,
					' . db_prefix() . 'tasks.name as taskName,
					' . db_prefix() . 'taskstimers.id,
					rel_id,
					rel_type,
					billed,
					' . db_prefix() . 'projects.name as projectName
				');
				$this->db->from(db_prefix() . 'taskstimers');
				$this->db->where('staff_id', $id);
				$this->db->where('start_time >=', $start_time);
				$this->db->where('start_time <', $end_time);
		 
				// Join tasks and projects
				$this->db->join(
					db_prefix() . 'tasks',
					db_prefix() . 'tasks.id = ' . db_prefix() . 'taskstimers.task_id',
					'left'
				);
				$this->db->join(
					db_prefix() . 'projects',
					db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id',
					'left'
				);
				
				$timers = $this->db->get()->result_array();
				$result['timesheets'] = $timers;
				$result['total_seconds'] = $total->total_seconds;
			}
		 
    return $result;
}
	
	public function timesheet_summary_10hour(){

        $data = $this->managers_list();

        $timesheet_summary_9hour = array();

        foreach ($data as $manager_id => $manager_name) {


            $query = "SELECT staffid FROM tblstaff WHERE team_manage = $manager_id AND active = 1";


            $team_staffids = array_column($this->db->query($query)->result_array(), 'staffid');
            $staff_data = [];

            foreach ($team_staffids as $team_staffid) {
                $staff_data[$team_staffid] = $this->get_timesheet_data_10hour($team_staffid);
            }
            $timesheet_summary_10hour[$manager_id]['team_data'] = $staff_data;
        }

        return $timesheet_summary_10hour;
    }
	
	 public function send_daily_timesheet_mail_less_then_10hour($data)
    {
			$this->email->set_mailtype("html");
			$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');

			$subject = "Daily Greater Than 10 Hours Report: " . date('d-m-Y', strtotime('-1 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Daily Utilization Report</h2>';

			$hasData = false; // To check if at least one manager has data

			foreach ($data as $manager_id => $manager_data) {
				$userData = $manager_data['team_data'];
				if (empty($userData)) {
					continue;
				}

				$managerName = get_staff_full_name($manager_id);
				$message .= '<h3>Manager: ' . $managerName . '</h3>';

				$managerHasEmployee = false;

				foreach ($userData as $staff_id => $staff_data) {
					if (empty($staff_data['timesheets'])) {
						continue;
					}

					$hasData = true;
					$managerHasEmployee = true;

					$breakTimeSeconds = 0;
					$totalWorkTimeSeconds = 0;
					$rowCount = count($staff_data['timesheets']);
					$isFirstTask = true;

					$message .= '<p><strong>Employee: ' . get_staff_full_name($staff_id) . ' - ' . get_staff_emp_id($staff_id) . '</strong></p>';
					$message .= '<table>
									<thead>
										<tr>
											<th>Project</th>
											<th>Task</th>
											<th>Start Time</th>
											<th>End Time</th>
											<th>Time (h)</th>
										</tr>
									</thead>
									<tbody>';

					foreach ($staff_data['timesheets'] as $task) {
						$timeDifference = $task['end_time'] - $task['start_time'];
						$formattedDifference = $this->formatTimeDifference($timeDifference);

						if (in_array(strtolower($task['taskName']), ['lunch break', 'tea break', 'break time', 'dinner break'])) {
							$breakTimeSeconds += $timeDifference;
						} else {
							$totalWorkTimeSeconds += $timeDifference;
						}

						$message .= '<tr>
										<td>' . $task['projectName'] . '</td>
										<td>' . $task['taskName'] . '</td>
										<td>' . date('Y-m-d H:i:s', $task['start_time']) . '</td>
										<td>' . date('Y-m-d H:i:s', $task['end_time']) . '</td>
										<td>' . $formattedDifference . '</td>
									</tr>';
					}

					$formattedBreakTime = $this->formatTimeDifference($breakTimeSeconds);
					$formattedTotalTime = $this->formatTimeDifference($totalWorkTimeSeconds + $formattedBreakTime);

					$message .= '<tr>
									<td colspan="4" style="text-align:right; font-weight:bold;">Break Time:</td>
									<td>' . $formattedBreakTime . '</td>
								 </tr>';
					$message .= '<tr>
									<td colspan="4" style="text-align:right; font-weight:bold;">Total Work Time:</td>
									<td>' . $formattedTotalTime . '</td>
								 </tr>';
					$message .= '</tbody></table>';
				}

				if (!$managerHasEmployee) {
					$message .= '<p>No employees worked more then 10 hour under this manager.</p>';
				}
			}

			$message .= '</body></html>';

			if ($hasData) {
				//$this->email->to('naved.ahamad1@tech2globe.net'); // Replace with recipient
				$this->email->to('harpreet.singh@tech2globe.com');
				$this->email->cc(array('sarabjeet@tech2globe.net'));
				$this->email->subject($subject);
				$this->email->message($message);

				if ($this->email->send()) {
					echo 'Mail sent';
				} else {
					echo 'Mail not sent';
				}
				$this->email->clear();
			} else {
				echo "No employee available for any manager.";
			}
			
			
    }

    public function send_daily_timesheet_report_run_less_then_10hour($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);

            update_option('last_cron_run', time());

            if ($manually == true) {

                $this->manually = true;

                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }

                log_activity('Cron Invoked Manually');
            }

           // if (date('D') != 'Mon') {
			if (date('D') != 'Mon') {
                // running cron job for workroom
                $data = $this->timesheet_summary_10hour();
				
                $this->send_daily_timesheet_mail_less_then_10hour($data);
            }

            /**

             * Finally send any emails in the email queue - if enabled and any

             */

            $this->email->send_queue();

            $last_email_queue_retry = get_option('last_email_queue_retry');

            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {
                $this->email->retry_queue();
                update_option('last_email_queue_retry', time());
            }

            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();

            app_maybe_delete_old_temporary_files();

            hooks()->do_action('after_cron_run', $manually);

            // For all cases try to release the lock after everything is finished
            $this->lockHandle();
        }
    }
	
	/* Greater then 10 hour end */
	
	
	/* optimise report*/
public function send_daily_timesheet_optimise_report($data)
{
    $this->email->set_mailtype("html");
    $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');

    $subject = "Daily Working Hours Report: " . date('d-m-Y', strtotime('-1 day'));

    $message = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Performance review</title>
        <style>
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid black; padding: 8px; text-align: left; }
            th { background-color: #2600bd; color: white; }
            td { color: black; }
            h2 { color: #2600bd; }
            h3 { background-color: #f2f2f2; padding: 5px; }
        </style>
    </head>
    <body>';

    $message .= '<h2>Daily Working Hours Report</h2>';

    $hasData = false;

    foreach ($data as $manager_id => $manager_data) {
        $userData = $manager_data['team_data'] ?? [];

        $managerName = get_staff_full_name($manager_id);
        $message .= '<h3>Manager: ' . $managerName . '</h3>';

        $message .= '<table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Total Break Time</th>
                                <th>Total Working Time</th>
                            </tr>
                        </thead>
                        <tbody>';

        if (!empty($userData)) {
            foreach ($userData as $staff_id => $staff_data) {
                $empId = get_staff_emp_id($staff_id);
                $empName = get_staff_full_name($staff_id);

                if (empty($staff_data['timesheets'])) {
                    $message .= '<tr>
                                    <td>' . $empId . '</td>
                                    <td>' . $empName . '</td>
                                    <td colspan="4">No timesheet entries</td>
                                 </tr>';
                    continue;
                }

                $hasData = true;
                $breakTimeSeconds = 0;
                $workTimeSeconds = 0;
                $startTime = null;
                $endTime = null;

                foreach ($staff_data['timesheets'] as $task) {
                    $start = $task['start_time'];
                    $end = $task['end_time'];

                    // Skip if invalid or empty
                    if (!$start || !$end || $end <= $start) {
                        continue;
                    }

                    $duration = $end - $start;

                    if (in_array(strtolower($task['taskName']), ['lunch break', 'tea break', 'break time', 'dinner break'])) {
                        $breakTimeSeconds += $duration;
                    } else {
                        $workTimeSeconds += $duration+$breakTimeSeconds;
                    }

                    if ($startTime === null || $start < $startTime) {
                        $startTime = $start;
                    }
                    if ($endTime === null || $end > $endTime) {
                        $endTime = $end;
                    }
                }

                $formattedBreak = $this->formatTimeDifference($breakTimeSeconds);
                $formattedWork = $this->formatTimeDifference($workTimeSeconds);
                $startFormatted = $startTime ? date('Y-m-d H:i:s', $startTime) : 'N/A';
                $endFormatted = $endTime ? date('Y-m-d H:i:s', $endTime) : 'N/A';

                $message .= '<tr>
                                <td>' . $empId . '</td>
                                <td>' . $empName . '</td>
                                <td>' . $startFormatted . '</td>
                                <td>' . $endFormatted . '</td>
                                <td>' . $formattedBreak . '</td>
                                <td>' . $formattedWork . '</td>
                             </tr>';
            }
        } else {
            $message .= '<tr><td colspan="6">No team members assigned to this manager.</td></tr>';
        }

        $message .= '</tbody></table>';
    }

    $message .= '</body></html>';
	print_R($message);
    // Debug: preview HTML output
    // file_put_contents(FCPATH . 'application/logs/timesheet_report_preview.html', $message);

    if ($hasData) {
        //$this->email->to('naved.ahamad1@tech2globe.net');
		 $this->email->to('harpreet.singh@tech2globe.com');
         $this->email->cc(['hr@tech2globe.com', 'sarabjeet@tech2globe.net']);
			$this->email->subject($subject);
			$this->email->message($message);

        if ($this->email->send()) {
            echo 'Mail sent';
        } else {
            echo 'Mail not sent';
            echo $this->email->print_debugger(); // Show error info
        }
        $this->email->clear();
    } else {
        echo "No employee available for any manager.";
    }
}



    public function send_daily_timesheet_report_run_optimise_report($manually = false)
    {

        if ($this->can_cron_run()) {

            hooks()->do_action('before_cron_run', $manually);

            update_option('last_cron_run', time());

            if ($manually == true) {

                $this->manually = true;

                if (!extension_loaded('suhosin')) {

                    @ini_set('memory_limit', '-1');
                }

                log_activity('Cron Invoked Manually');
            }

           // if (date('D') != 'Mon') {
		if (date('D') != 'Mon') {
                // running cron job for workroom
                $data = $this->timesheet_summary();
				
                $this->send_daily_timesheet_optimise_report($data);
           }

            /**

             * Finally send any emails in the email queue - if enabled and any

             */

            $this->email->send_queue();

            $last_email_queue_retry = get_option('last_email_queue_retry');

            $retryQueue = hooks()->apply_filters('cron_retry_email_queue_seconds', 600);

            // Retry queue failed emails every 10 minutes

            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + $retryQueue))) {
                $this->email->retry_queue();
                update_option('last_email_queue_retry', time());
            }

            $this->_maybe_fix_duplicate_tasks_assignees_and_followers();

            app_maybe_delete_old_temporary_files();

            hooks()->do_action('after_cron_run', $manually);

            // For all cases try to release the lock after everything is finished
            $this->lockHandle();
        }
    }
	
	/* optimise report end*/
    public function send_delay_ticket_mail_run(){

        $this->db->where('status !=', 5);
        $this->db->where('reminder_status =', 0);
        $this->db->where('date IS NOT NULL');

        $tickets = $this->db->get(db_prefix() . 'tickets')->result_array();

        foreach ($tickets as $ticket) {
            $date = strtotime($ticket['date']);
            $lastreplydate = strtotime($ticket['lastreply']);
            $currentTime = strtotime(date('Y-m-d H:i:s')); // Current time

            if($ticket['priority'] == 3){
                $hoursInSeconds = 24 * 3600; // 24 hours in seconds
            }else if($ticket['priority'] == 2){
                $hoursInSeconds = 48 * 3600; // 48 hours in seconds
            }else{
                $hoursInSeconds = 72 * 3600; // 72 hours in seconds
            }

            $high_priority_hoursInSeconds = 24 * 3600;
        
            // Condition
            if (($currentTime - $date) >= $hoursInSeconds && $ticket['level'] == 1 && empty($ticket['lastreply'])) {
                $this->send_delay_ticket_mail_to_department_manager($ticket);
                $this->send_delay_ticket_mail_to_user($ticket);

                $this->db->where('ticketid', $ticket['ticketid']);
                $this->db->update(db_prefix() . 'tickets', [
                    'reminder_status' => 1,
                ]);
            } elseif(($currentTime - $lastreplydate) >= $high_priority_hoursInSeconds && $ticket['level'] != 1 && !empty($ticket['lastreply'])) {
                $this->send_delay_ticket_mail_to_department_manager($ticket);

                $this->db->where('ticketid', $ticket['ticketid']);
                $this->db->update(db_prefix() . 'tickets', [
                    'reminder_status' => 1,
                ]);
            }
        }
    }

    public function send_delay_ticket_mail_to_department_manager($data){

        if($data['priority'] == 3){
            $hours = 24;
            $priority = "High";
        }else if($data['priority'] == 2){
            $hours = 48;
            $priority = "Medium";
        }else{
            $hours = 72;
            $priority = "Low";
        }

        $this->db->select('name, email');
        $this->db->from('tbldepartments');
        $this->db->where('departmentid', $data['department']);
        $dep = $this->db->get()->result_array();

        $dep_name = $dep[0]['name'];
        $dep_email = $dep[0]['email'];
        // $dep_email = 'yogesh.gupta@tech2globe.in';

        if($data['level'] == 2){
            $dep_email = 'ishan.negi@tech2globe.in';
        }else if($data['level'] == 3){
            $dep_email = 'naved.ahamad@tech2globe.in';
        }

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
        $this->email->to($dep_email);

        $subject = "Raise Ticket Delay Reminder";

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Raise Ticket delay reminder</title>
            
        </head>
        <body>
            <p>Dear Team,</p>
            <p>
            The following '.$priority.' ticket has not been worked on within '.$hours.' hours based on its priority level.<br><br>

            <b>Ticket Details:</b><br>
            <b>Ticket ID:</b> #'.$data['ticketid'].'<br>
            <b>Ticket Level:</b> '.$data['level'].'<br>
            <b>Priority:</b> '.$priority.'<br>
            <b>Raised By:</b> '.$data['name'].'<br>
            <b>Date Raised:</b> '.$data['date'].'<br>
            <b>Ticket Issue:</b> '.$data['message'].'<br>
            <b>Assigned To:</b> '.$dep_name.'<br>
            We kindly request you to review this ticket and take the necessary steps to ensure prompt resolution.
            </p>
            <br><br>
            <p>Best Regards,<br>
            Tech2globe</p>
        </body>
        </html>
        ';

        $this->email->subject($subject);
        $this->email->message($message);

        if ($this->email->send()) {
            echo 'mail sent';
        } else {
            echo 'mail not sent';
        }

        $this->email->clear();
    
    }

    public function send_delay_ticket_mail_to_user($data){

        if($data['priority'] == 3){
            $hours = 24;
            $priority = "High";
        }else if($data['priority'] == 2){
            $hours = 48;
            $priority = "Medium";
        }else{
            $hours = 72;
            $priority = "Low";
        }

        $this->db->select('name');
        $this->db->from('tbldepartments');
        $this->db->where('departmentid', $data['department']);
        $dep_name = $this->db->get()->result_array();

        $dep_name = $dep_name[0]['name'];

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
        $this->email->to($data['email']);

        $subject = "Raise Ticket Delay Reminder";

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Raise Ticket delay reminder</title>
            
        </head>
        <body>
            <p>Dear '. $data['name'] .',</p>
            <p>
            We regret to inform you the following ticket raised by you have not been worked upon within the time limit.<br><br>

            <b>Ticket Details:</b><br>
            <b>Ticket ID:</b> #'.$data['ticketid'].'<br>
            <b>Ticket Level:</b> '.$data['level'].'<br>
            <b>Priority:</b> '.$priority.'<br>
            <b>Raised By:</b> '.$data['name'].'<br>
            <b>Date Raised:</b> '.$data['date'].'<br>
            <b>Ticket Issue:</b> '.$data['message'].'<br>
            <b>Assigned To:</b> '.$dep_name.'<br>
            </p>
            <br><br>
            <p>Best Regards,<br>
            Tech2globe</p>
        </body>
        </html>
        ';

        $this->email->subject($subject);
        $this->email->message($message);

        if ($this->email->send()) {
            echo 'mail sent';
        } else {
            echo 'mail not sent';
        }

        $this->email->clear();
    
    }
	
	public function send_daily_recrutment_report_run(){
		$currentDate = date('d-m-Y'); 
		$prev_date =  date('d-m-Y',strtotime("yesterday"));
		$get_hr_department = "SELECT
            GROUP_CONCAT(departmentid SEPARATOR ', ') AS staffids
        FROM
            `tbldepartments`
        WHERE NAME LIKE
            '%hr%'";
        $hr_department_ids = $this->db->query($get_hr_department)->row()->staffids;


        $get_hr_name_query = "SELECT
            DISTINCT(firstname) as name, email,staff_identifi
        FROM
            `tblstaff`
        LEFT JOIN `tblstaff_departments` ON tblstaff.staffid = tblstaff_departments.staffid
        WHERE
            departmentid IN($hr_department_ids) AND active = 1 AND admin = 0;";


        $hr_names = $this->db->query($get_hr_name_query)->result_array();
		 $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
        $subject = "Daily Recruitment Report : $prev_date";
		//$this->email->to('naved.ahamad1@tech2globe.net');	
		$this->email->to('sarabjeet@tech2globe.net');
       $this->email->cc(array('harpreet.singh@tech2globe.com'));
		$message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Daily HR Recruitment Report</title>
                <style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #2600bd;
                        color : white;
                    }

                    td {
                        color : black;
                    }
                </style>
            </head>
            <body>
            ';
			 $message .= '
            <h2>Daily Recruitment Report '.$prev_date.'</h2> 
               <table>
                    <thead>
						<th>EMP ID</th>
                        <th>Name</th>
                        <th>Candidate Data</th>
                    </thead>
                    <tbody>';

		foreach($hr_names as $hrname){
			$name = $hrname['name'];
			
			 $search_candidate_query = "SELECT
                COUNT(candidate_code) AS candidates,
                MAX(added_from) AS staff_id
            FROM
                tblrec_candidate
            WHERE
                candidate_code LIKE '%" . trim($name) . "%'  AND date_add BETWEEN '".$prev_date."' AND '".$currentDate."'";

            $candidates_data[$hrname['name']]  =  $this->db->query($search_candidate_query)->result_array();
			
			$message .=' 
					<tr>
					<td>'.$hrname['staff_identifi'].'</td>
					<td>'.$hrname['name'].'</td>
					<td>'.$candidates_data[$hrname['name']][0]['candidates'].'</td>
					</tr>';
			
		}
		$message .= '</tbody>
                </table>
            </body>
            </html>';
		 $this->email->subject($subject);
            $this->email->message($message);
            // $this->email->bcc(['yogesh.gupta@tech2globe.in', 'naved.ahamad1@tech2globe.net']);

            if ($this->email->send()) {
                echo 'mail sent';
            } else {
                echo 'mail not sent'; 
            }

            $this->email->clear();
		print_r($message);
	}
	
	public function countOpen($text) {
    return (strcasecmp($text, 'Open') === 0) ? 1 : 0;
		}
	public function countClose($text) {
    return (strcasecmp($text, 'Closed') === 0) ? 1 : 0;
		}

	public function send_daily_recrutment_report_to_hr_run(){
		 $this->email->set_mailtype("html");
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		 $query = "SELECT
					ticketid,
					tbltickets.name as name,
					department,
					priority,
					status,
					subject,
					message,
					DATE,
					assigned,
					level,
					tbltickets_priorities.name as priority_name,
					tbldepartments.name as department_name,
					tbltickets_status.name as ticketstatus_name
				FROM
					tbltickets
					LEFT JOIN tbltickets_status ON tbltickets_status.ticketstatusid  = tbltickets.status
					LEFT JOIN tbltickets_priorities ON tbltickets_priorities.priorityid = tbltickets.priority
					LEFT JOIN tbldepartments ON tbldepartments.departmentid = tbltickets.department
				WHERE
					status = 1;";
		 $ticket_report = $this->db->query($query)->result_array();
		 
		 $subject = "Weekly Support Tickets Report: " . date('d-m-Y', strtotime('-8 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Weekly Support Tickets Report</h2>';
		$openCount = 0;
		$closeCount = 0;
		$expiredCount = 0;
		foreach ($ticket_report as $ticket) {
			$openCount += $this->countOpen($ticket['ticketstatus_name']);
			$closeCount += $this->countClose($ticket['ticketstatus_name']);
			    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
						$expiredCount++;
					}
				}
		}
		
		//echo "Total Open tickets: " . $openCount;
		//echo "Total Closed tickets: " . $closeCount;
		$message .='<table>
						<thead>
							<tr>
								<th>Open Ticket</th>
								<th>Closed Ticket</th>
								<th>TAT Missing</th>
							</tr>
						</thead>
                    <tbody>';
		 $message .= '<tr>
                                <td>' . $openCount . '</td>
                                <td>' . $closeCount . '</td>
                                <td>' . $expiredCount. '</td>
                             </tr>';
		$message .= '</tbody></table>';		 
				$hasData = false;
				 $message .= '<table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Employee Name</th>
                                <th>Department Name</th>
                                <th>Ticket Status</th>
                                <th>Priority </th>
								<th>Ticket Created Date </th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>';
				
						 $statuses = [];
				 foreach($ticket_report as $ticket){
					 //echo "<pre>";print_r($ticket['DATE']);
					 $formattedDate = date("d-m-Y", strtotime($ticket['DATE']));
					 $statuses[] = $ticket['ticketstatus_name'];
					 
					    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
					 $message .= '<tr style="background-color:#ff000073;">
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
				}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
					 
					
					
				
				 }
				  $message .= '</tbody></table>';
				$message .= '</body></html>';
		print_R($message);
		 //$this->email->to('naved.ahamad1@tech2globe.net');
		  $this->email->to('harpreet.singh@tech2globe.com');
        // $this->email->cc(['hr@tech2globe.com', 'sarabjeet@tech2globe.net']);
		$this->email->subject($subject);
		$this->email->message($message);

			if ($this->email->send()) {
				echo 'Mail sent';
			} else {
				echo 'Mail not sent';
				echo $this->email->print_debugger(); // Show error info
			}
			$this->email->clear();
						
			}
			
	public function send_daily_recrutment_report_to_hr_manager_run(){
		 $this->email->set_mailtype("html");
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		 $query = "SELECT
					ticketid,
					tbltickets.name AS name,
					department,
					priority,
				STATUS
					,
					subject,
					message,
					DATE,
					assigned,
					level,
					tbltickets_priorities.name AS priority_name,
					tbldepartments.name AS department_name,
					tbltickets_status.name AS ticketstatus_name
				FROM
					tbltickets
				LEFT JOIN tbltickets_status ON tbltickets_status.ticketstatusid = tbltickets.status
				LEFT JOIN tbltickets_priorities ON tbltickets_priorities.priorityid = tbltickets.priority
				LEFT JOIN tbldepartments ON tbldepartments.departmentid = tbltickets.department
				WHERE
					(
						(
            tbltickets.status = 5 AND tbltickets.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()) OR
            (tbltickets.status = 1)) AND tbltickets.department IN('27', '25');";
		 $ticket_report = $this->db->query($query)->result_array();
		 
		 $subject = "Weekly Support Tickets Report: " . date('d-m-Y', strtotime('-8 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Weekly Support Tickets Report</h2>';
		$openCount = 0;
		$closeCount = 0;
		$expiredCount = 0;
		foreach ($ticket_report as $ticket) {
			$openCount += $this->countOpen($ticket['ticketstatus_name']);
			$closeCount += $this->countClose($ticket['ticketstatus_name']);
			    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
						$expiredCount++;
					}
				}
		}
		
		//echo "Total Open tickets: " . $openCount;
		//echo "Total Closed tickets: " . $closeCount;
		$message .='<table>
						<thead>
							<tr>
								<th>Open Ticket</th>
								<th>Closed Ticket</th>
								<th>TAT Missing</th>
							</tr>
						</thead>
                    <tbody>';
		 $message .= '<tr>
                                <td>' . $openCount . '</td>
                                <td>' . $closeCount . '</td>
                                <td>' . $expiredCount. '</td>
                             </tr>';
		$message .= '</tbody></table>';		 
				$hasData = false;
				 $message .= '<table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Employee Name</th>
                                <th>Department Name</th>
                                <th>Ticket Status</th>
                                <th>Priority </th>
								<th>Ticket Created Date </th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>';
				
						 $statuses = [];
				 foreach($ticket_report as $ticket){
					 //echo "<pre>";print_r($ticket['DATE']);
					 $formattedDate = date("d-m-Y", strtotime($ticket['DATE']));
					 $statuses[] = $ticket['ticketstatus_name'];
					 
					    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
					 $message .= '<tr style="background-color:#ff000073;">
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
				}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
					 
					
					
				
				 }
				  $message .= '</tbody></table>';
				$message .= '</body></html>';
		print_R($message);
		// $this->email->to('naved.ahamad1@tech2globe.net');
		// $this->email->to('navneet.baid@tech2globe.in');
		  $this->email->to('hr@tech2globe.com');
		$this->email->subject($subject);
		$this->email->message($message);

			if ($this->email->send()) {
				echo 'Mail sent';
			} else {
				echo 'Mail not sent';
				echo $this->email->print_debugger(); // Show error info
			}
			$this->email->clear();
						
			}
			
	public function send_daily_recrutment_report_to_Sarabjeet_manager_run(){
		 $this->email->set_mailtype("html");
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		 $query = "SELECT
					ticketid,
					tbltickets.name AS name,
					department,
					priority,
					status
						,
						subject,
						message,
						DATE,
						assigned,
						level,
						tbltickets_priorities.name AS priority_name,
						tbldepartments.name AS department_name,
						tbltickets_status.name AS ticketstatus_name
					FROM
						tbltickets
					LEFT JOIN tbltickets_status ON tbltickets_status.ticketstatusid = tbltickets.status
					LEFT JOIN tbltickets_priorities ON tbltickets_priorities.priorityid = tbltickets.priority
					LEFT JOIN tbldepartments ON tbldepartments.departmentid = tbltickets.department
					WHERE
						(
							(
								tbltickets.status = 5 AND tbltickets.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()) OR(tbltickets.status = 1)) AND tbltickets.department IN('28', '15');";
		 $ticket_report = $this->db->query($query)->result_array();
		 
		 $subject = "Weekly Support Tickets Report: " . date('d-m-Y', strtotime('-8 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Weekly Support Tickets Report</h2>';
		$openCount = 0;
		$closeCount = 0;
		$expiredCount = 0;
		foreach ($ticket_report as $ticket) {
			$openCount += $this->countOpen($ticket['ticketstatus_name']);
			$closeCount += $this->countClose($ticket['ticketstatus_name']);
			    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
						$expiredCount++;
					}
				}
		}
		
		//echo "Total Open tickets: " . $openCount;
		//echo "Total Closed tickets: " . $closeCount;
		$message .='<table>
						<thead>
							<tr>
								<th>Open Ticket</th>
								<th>Closed Ticket</th>
								<th>TAT Missing</th>
							</tr>
						</thead>
                    <tbody>';
		 $message .= '<tr>
                                <td>' . $openCount . '</td>
                                <td>' . $closeCount . '</td>
                                <td>' . $expiredCount. '</td>
                             </tr>';
		$message .= '</tbody></table>';		 
				$hasData = false;
				 $message .= '<table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Employee Name</th>
                                <th>Department Name</th>
                                <th>Ticket Status</th>
                                <th>Priority </th>
								<th>Ticket Created Date </th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>';
				
						 $statuses = [];
				 foreach($ticket_report as $ticket){
					 //echo "<pre>";print_r($ticket['DATE']);
					 $formattedDate = date("d-m-Y", strtotime($ticket['DATE']));
					 $statuses[] = $ticket['ticketstatus_name'];
					 
					    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
					 $message .= '<tr style="background-color:#ff000073;">
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
				}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
					 
					
					
				
				 }
				  $message .= '</tbody></table>';
				$message .= '</body></html>';
		print_R($message);
		// $this->email->to('naved.ahamad1@tech2globe.net');
		 $this->email->to('sarabjeet@tech2globe.net');
		  //$this->email->to('hr@tech2globe.com');
        // $this->email->cc(['hr@tech2globe.com', 'sarabjeet@tech2globe.net']);
		$this->email->subject($subject);
		$this->email->message($message);

			if ($this->email->send()) {
				echo 'Mail sent';
			} else {
				echo 'Mail not sent';
				echo $this->email->print_debugger(); // Show error info
			}
			$this->email->clear();
						
			}
			
	public function send_daily_recrutment_report_to_ITSupport_manager_run(){
		 $this->email->set_mailtype("html");
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		 $query = "SELECT
					ticketid,
					tbltickets.name AS name,
					department,
					priority,
					status
						,
						subject,
						message,
						DATE,
						assigned,
						level,
						tbltickets_priorities.name AS priority_name,
						tbldepartments.name AS department_name,
						tbltickets_status.name AS ticketstatus_name
					FROM
						tbltickets
					LEFT JOIN tbltickets_status ON tbltickets_status.ticketstatusid = tbltickets.status
					LEFT JOIN tbltickets_priorities ON tbltickets_priorities.priorityid = tbltickets.priority
					LEFT JOIN tbldepartments ON tbldepartments.departmentid = tbltickets.department
					WHERE
						(
							(
								tbltickets.status = 5 AND tbltickets.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()) OR(tbltickets.status = 1)) AND tbltickets.department IN('15');";
		 $ticket_report = $this->db->query($query)->result_array();
		 
		 $subject = "Weekly Support Tickets Report: " . date('d-m-Y', strtotime('-8 day'));

			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Performance review</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
					h3 { background-color: #f2f2f2; padding: 5px; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Weekly Support Tickets Report</h2>';
		$openCount = 0;
		$closeCount = 0;
		$expiredCount = 0;
		foreach ($ticket_report as $ticket) {
			$openCount += $this->countOpen($ticket['ticketstatus_name']);
			$closeCount += $this->countClose($ticket['ticketstatus_name']);
			    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
						$expiredCount++;
					}
				}
		}
		
		//echo "Total Open tickets: " . $openCount;
		//echo "Total Closed tickets: " . $closeCount;
		$message .='<table>
						<thead>
							<tr>
								<th>Open Ticket</th>
								<th>Closed Ticket</th>
								<th>TAT Missing</th>
							</tr>
						</thead>
                    <tbody>';
		 $message .= '<tr>
                                <td>' . $openCount . '</td>
                                <td>' . $closeCount . '</td>
                                <td>' . $expiredCount. '</td>
                             </tr>';
		$message .= '</tbody></table>';		 
				$hasData = false;
				 $message .= '<table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Employee Name</th>
                                <th>Department Name</th>
                                <th>Ticket Status</th>
                                <th>Priority </th>
								<th>Ticket Created Date </th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>';
				
						 $statuses = [];
				 foreach($ticket_report as $ticket){
					 //echo "<pre>";print_r($ticket['DATE']);
					 $formattedDate = date("d-m-Y", strtotime($ticket['DATE']));
					 $statuses[] = $ticket['ticketstatus_name'];
					 
					    if (strcasecmp($ticket['ticketstatus_name'], 'Open') === 0) {
					$createdAt = new DateTime($ticket['DATE']);
					$now = new DateTime();

					$diffInHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

					if ($diffInHours > 24) {
					 $message .= '<tr style="background-color:#ff000073;">
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
				}else{
						
						 $message .= '<tr>
                                <td>' . $ticket['ticketid'] . '</td>
                                <td>' . $ticket['name'] . '</td>
                                <td>' . $ticket['department_name']. '</td>
                                <td>' . $ticket['ticketstatus_name'] . '</td>
                                <td>' . $ticket['priority_name'] . '</td>
								<td>' . $formattedDate . '</td>
                                <td>' . $ticket['subject'] . '</td>
                             </tr>';
					}
					 
					
					
				
				 }
				  $message .= '</tbody></table>';
				$message .= '</body></html>';
		print_R($message);
		// $this->email->to('naved.ahamad1@tech2globe.net');
		 $this->email->to('it.support@tech2globe.in');
		  //$this->email->to('hr@tech2globe.com');
         $this->email->cc(['sarabjeet@tech2globe.net']);
		$this->email->subject($subject);
		$this->email->message($message);

			if ($this->email->send()) {
				echo 'Mail sent';
			} else {
				echo 'Mail not sent';
				echo $this->email->print_debugger(); // Show error info
			}
			$this->email->clear();
						
			}		
	public function send_daily_recrutment_report_to_PEDMA()
		{
			$this->email->set_mailtype("html");
			$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');

			// ✅ Refined Query
			$query = "
				SELECT 
			
			d.name AS department_name,
			CONCAT(m.firstname, ' ', m.lastname) AS manager_name,
			s.staff_identifi,
			s.firstname,
			s.lastname
		FROM tblstaff s
		JOIN tblstaff_departments sd ON s.staffid = sd.staffid
		JOIN tbldepartments d ON sd.departmentid = d.departmentid
		LEFT JOIN tblstaff m ON s.team_manage = m.staffid
		WHERE s.active = 1
		  AND s.staffid NOT IN (1, 292, 178)
		  AND (s.team_manage IS NULL OR s.team_manage NOT IN (1, 292, 178))
		  AND s.staffid NOT IN (
			  SELECT DISTINCT team_manage FROM tblstaff 
			  WHERE team_manage IS NOT NULL
		  )
		  AND NOT EXISTS (
			  SELECT 1 
			  FROM tblstaff_performance p 
			  WHERE p.staffid = s.staffid 
				AND p.date_created >= DATE_SUB(CURDATE(), INTERVAL 45 DAY)
		  )
		ORDER BY d.name, s.firstname;

			";

			$pedma_report = $this->db->query($query)->result_array();

			$subject = "Monthly PEDMA Missed : " . date('F')."-".date('Y');

			
			$message = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<title>PEDMA Record Missed</title>
				<style>
					table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					th, td { border: 1px solid black; padding: 8px; text-align: left; }
					th { background-color: #2600bd; color: white; }
					td { color: black; }
					h2 { color: #2600bd; }
				</style>
			</head>
			<body>';

			$message .= '<h2>Monthly PEDMA Missed</h2>';

			if (empty($pedma_report)) {
				$message .= '<p>No staff found missing performance reports in the last 65 days.</p>';
			} else {
				$groupedByManager = [];

		foreach ($pedma_report as $row) {
			$manager = $row['manager_name'] ?: 'No Manager Assigned';
			$groupedByManager[$manager][] = $row;
		}

		foreach ($groupedByManager as $manager => $staffList) {
			$message .= '<h3>Manager: ' . htmlspecialchars($manager) . '</h3>';
			$message .= '<table>
							<thead>
								<tr>
								
									<th>Department Name</th>
									<th>Employee Code</th>
									<th>First Name</th>
									<th>Last Name</th>
								</tr>
							</thead>
							<tbody>';

			foreach ($staffList as $staff) {
				$message .= '<tr>
							
								<td>' . htmlspecialchars($staff['department_name']) . '</td>
								<td>' . htmlspecialchars($staff['staff_identifi']) . '</td>
								<td>' . htmlspecialchars($staff['firstname']) . '</td>
								<td>' . htmlspecialchars($staff['lastname']) . '</td>
							</tr>';
			}

			$message .= '</tbody></table>';
		}
			}

			$message .= '</body></html>';
			print_r($message);
			// ✅ Send the email
		    $this->email->to('sarabjeet@tech2globe.net');
			 $this->email->cc(['hr@tech2globe.com', 'harpreet.singh@tech2globe.com']); // Optional 
			$this->email->subject($subject);
			$this->email->message($message);

			if ($this->email->send()) {
				echo 'Mail sent';
			} else {
				echo 'Mail not sent';
				echo $this->email->print_debugger();
			}

			$this->email->clear();
		}
		
		
		
	//snapshot cron	
	public function staff_login_status_last_week()
			{
				$this->email->set_mailtype("html");
			$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
			$subject = 'Weekly Snapshot Login Report(Mon-Sat)';
				// Get last Mon-Sat dates
				$dates = [];
				$today = strtotime('today');
				$start = strtotime('last monday', $today);
				for ($i = 0; $i < 6; $i++) {
					$dates[] = date('Y-m-d', strtotime("+$i day", $start));
				}

				// Get all active staff with departments
				$staff_query = "
					SELECT s.staffid, s.firstname, s.lastname, d.name AS department
					FROM tblstaff s
					LEFT JOIN tblstaff_departments sd ON s.staffid = sd.staffid
					LEFT JOIN tbldepartments d ON sd.departmentid = d.departmentid
					WHERE s.active = 1 AND s.staffid NOT IN (178,1,292)
				";
				$staff_list = $this->db->query($staff_query)->result_array();

				// Get login data
				$date_list_str = "'" . implode("','", $dates) . "'";
				$login_query = "
					SELECT DISTINCT staff_id, DATE(date_time) AS login_date
					FROM tblstaff_drive_data
					WHERE DATE(date_time) IN ($date_list_str)
				";
				$login_data = $this->db->query($login_query)->result_array();

				// Map login data
				$login_map = [];
				foreach ($login_data as $row) {
					$login_map[$row['staff_id']][$row['login_date']] = true;
				}

				// Group staff by department
				$dept_map = [];
				foreach ($staff_list as $staff) {
					$dept = $staff['department'] ?: 'No Department';
					$dept_map[$dept][] = $staff;
				}

				// Build table
				$message .= "<table border='1' cellpadding='5' cellspacing='0'>";
				$message .= "<thead><tr>
					<th>Department</th>
					<th>Staff ID</th>
					<th>Name</th>";
				foreach ($dates as $d) {
					$message .= "<th>" . date('D', strtotime($d)) . "<br>" . $d . "</th>";
				}
				$message .= "</tr></thead><tbody>";

				foreach ($dept_map as $dept_name => $staffs) {
					$first = true;
					foreach ($staffs as $staff) {
						$message .= "<tr>";
						if ($first) {
							$message .= "<td rowspan='" . count($staffs) . "' style='font-weight:bold;'>$dept_name</td>";
							$first = false;
						}
						$message .= "<td>{$staff['staffid']}</td>";
						$message .= "<td>{$staff['firstname']} {$staff['lastname']}</td>";
						foreach ($dates as $d) {
							$status = isset($login_map[$staff['staffid']][$d]) ? 'TRUE' : 'FALSE';
							$color = ($status === 'TRUE') ? '#d4edda' : '#f8d7da';
							$message .= "<td style='background-color:$color;text-align:center;'>$status</td>";
						}
						$message .= "</tr>";
					}
				}

				$message .= "</tbody></table>";
				print_r($message);
				 $this->email->to('harpreet.singh@tech2globe.com');
			 $this->email->cc(['sarabjeet@tech2globe.net']); // Optional
				$this->email->subject($subject);
				$this->email->message($message);

				if ($this->email->send()) {
					echo 'Mail sent';
				} else {
					echo 'Mail not sent';
					echo $this->email->print_debugger();
				}

				$this->email->clear();
			}
	public function weekly_deadline_missed_projects_report()
		{
			$today = date('Y-m-d');
			// Only include projects that missed a deadline recently (skip years-old overdue rows).
			$recent_cutoff = date('Y-m-d', strtotime('-30 days'));
			$this->load->library('email');

			$this->db->select('name, start_date, deadline');
			$this->db->from(db_prefix() . 'projects');
			$this->db->where('deadline >=', $recent_cutoff);
			$this->db->where('deadline <', $today);
			$this->db->where('status !=', 4); // Exclude finished/completed
			$this->db->order_by('deadline', 'DESC');
			$projects = $this->db->get()->result();

			if (empty($projects)) {
				echo "✅ No projects with missed deadlines in the last 30 days.";
				return;
			}

			// Compose HTML table
			$table = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">';
			$table .= '<thead><tr style="background-color:#f2f2f2;">
						<th>Project Name</th>
						<th>Start Date</th>
						<th>Deadline</th>
					   </tr></thead><tbody>';

			foreach ($projects as $project) {
				$table .= "<tr>
							<td>{$project->name}</td>
							<td>{$project->start_date}</td>
							<td>{$project->deadline}</td>
						   </tr>";
			}
			$table .= '</tbody></table>';
			print_r($table);
			// Email setup
		   $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
			$this->email->to('sarabjeet@tech2globe.net');
			$this->email->cc(['harpreet.singh@tech2globe.com','tech2globe@yopmail.com']); // Optional
			//$this->email->to('naved.ahamad1@tech2globe.net'); // You can dynamically add recipients
			$this->email->subject('Weekly Report: Projects Missed Deadline');
			$this->email->message(
				"<p>The following active projects missed their deadline within the last 30 days (as of {$today}). "
				. "Older overdue projects are excluded from this report.</p>" . $table
			);
			$this->email->set_mailtype("html");

			if ($this->email->send()) {
				echo "✅ Missed deadline report sent.";
			} else {
				echo "❌ Failed to send email. " . $this->email->print_debugger();
			}
		}

		/*Biometric attendace send to staff member and mangaer*/

public function send_biometric_break_alerts($date = null)
{
    // TEMP OFF (2026-09-10): break exceeded emails paused until HR asks to re-enable.
    // Set to false to turn alerts back on.
    $break_alert_emails_disabled = true;
    if ($break_alert_emails_disabled) {
        echo "Biometric break alert emails are temporarily disabled.\n";
        return;
    }

    $this->load->library('email');
    $this->load->model('staff_model');
    $this->email->set_mailtype("html");
    $this->email->from('noreply@t2gworkroom.com', 'Tech2globe');

    if (!$date) {
        $date = date('d-M-Y', strtotime('-1 day'));
    }

    // Get biometric records for the day
    $this->db->where('employee_code !=', 1001);
    $this->db->where('attendance_date', $date);
    $records = $this->db->get('tblbiometric_report')->result();

    if (!$records) {
        echo "No biometric records found.";
        return;
    }

    $violations_by_manager = []; // manager_id => array of staff

    foreach ($records as $row) {
        $punches = $this->parse_punch_records($row->punch_records);
        $ins = array_filter($punches, fn($p) => $p['type'] === 'in');
        $outs = array_filter($punches, fn($p) => $p['type'] === 'out');

        if (count($ins) === 0 && count($outs) === 0) continue;

        $first_in = $ins ? $ins[array_key_first($ins)]['time'] : '-';
        $last_out = $outs ? $outs[array_key_last($outs)]['time'] : '-';
        $login_mins = $this->calc_minutes_diff($first_in, $last_out);
        $worked_mins = $this->calc_worked_minutes($punches);
        $break_mins = max(0, $login_mins - $worked_mins);

        if ($break_mins <= 65) continue;

        // Fetch staff info
        $staff = $this->staff_model->get('', ['staff_identifi' => $row->employee_code]);
		

        if (!is_array($staff) || count($staff) === 0 || !isset($staff[0]['staffid'])) {
            echo "❌ No valid staff found for employee code {$row->employee_code}<br>";
            continue;
        }

        $staff_row = $staff[0];
        $staff_id = $staff_row['staffid'];
        $manager_id = $staff_row['team_manage'];

        if (empty($manager_id) || !is_numeric($manager_id)) {
            echo "❌ Invalid or missing manager for staff ID {$staff_id}<br>";
            continue;
        }

        // Append staff entry under their manager
        if (!isset($violations_by_manager[$manager_id])) {
            $violations_by_manager[$manager_id] = ['entries' => []];
        }

        $violations_by_manager[$manager_id]['entries'][] = [
            'code' => $row->employee_code,
            'name' => $row->employee_name,
            'break_time' => $this->format_hms($break_mins),
            'first_in' => $first_in,
            'last_out' => $last_out,
        ];
    }

    // Now send to each manager
    foreach ($violations_by_manager as $manager_id => $data) {
        if (empty($data['entries'])) continue;

        // Fetch manager info
        $manager = $this->db->get_where('tblstaff', ['staffid' => $manager_id])->row();

        if (!$manager || !filter_var($manager->email, FILTER_VALIDATE_EMAIL)) {
            echo "❌ Invalid or missing email for manager ID: {$manager_id}<br>";
            continue;
        }

        $html = "<p>Dear {$manager->firstname} {$manager->lastname},</p>";
        $html .= "<p>The following team members exceeded the 65-minute break limit on <strong>{$date}</strong>:</p>";
        $html .= "<table border='1' cellpadding='5' cellspacing='0'>
            <thead><tr>
                <th>Code</th><th>Name</th><th>Break Time</th><th>First IN</th><th>Last OUT</th>
            </tr></thead><tbody>";

        foreach ($data['entries'] as $entry) {
            $html .= "<tr>
                <td>{$entry['code']}</td>
                <td>{$entry['name']}</td>
                <td>{$entry['break_time']}</td>
                <td>{$entry['first_in']}</td>
                <td>{$entry['last_out']}</td>
            </tr>";
        }

        $html .= "</tbody></table>";

        // Send mail
		print_r($html);
		
        $this->email->to($manager->email);
		$this->email->cc('sarabjeet@tech2globe.net');
        $this->email->subject("Daily Biometric Report - {$date}");
        $this->email->message($html);

        if ($this->email->send()) {
            echo "✅ Summary sent to manager {$manager->firstname} ({$manager->email})<br>";
        } else {
            echo "❌ Failed to send to manager: {$manager->email}<br>";
            echo $this->email->print_debugger();
        }

        $this->email->clear();
    }
}


public function send_break_report_to_staff()
{
    // TEMP OFF (2026-09-10): staff break exceeded emails paused until HR asks to re-enable.
    // Set to false to turn alerts back on.
    $break_alert_emails_disabled = true;
    if ($break_alert_emails_disabled) {
        echo "Staff break report emails are temporarily disabled.\n";
        return;
    }

    $this->load->model('staff_model');
    $this->load->library('email');

    $date = date('d-M-Y', strtotime('-1 day'));

    // Get biometric data for yesterday
	
   // $query = $this->db->get_where('tblbiometric_report', ['attendance_date' => $date]);
  //  $results = $query->result();
	$this->db->where('attendance_date', $date);
	$this->db->where('employee_code !=', '1001');
	$query = $this->db->get('tblbiometric_report');
	$results = $query->result();

    if (!$results) {
        echo "No biometric records found for {$date}.";
        return;
    }

    $this->email->set_mailtype("html");
    $this->email->from('noreply@t2gworkroom.com', 'Tech2Globe');
    
    $summary = "";
    $exceeded_count = 0;

    foreach ($results as $row) {
        $staff_info = $this->staff_model->get('', ['staff_identifi' => $row->employee_code]);
        if (!$staff_info) continue;

        $email = $staff_info[0]['email'];
        $punches = $this->parse_punch_records($row->punch_records);

        if (!$punches || count($punches) < 2) continue;

        $login_minutes = $this->calc_minutes_diff($punches[0]['time'], end($punches)['time']);
        $worked_minutes = $this->calc_worked_minutes($punches);
        $break_minutes = max(0, $login_minutes - $worked_minutes);

        if ($break_minutes > 65) {
            $exceeded_count++;

            // Send mail to staff
            $this->email->to($email);
			$this->email->cc(['sarabjeet@tech2globe.net']);
            $this->email->subject("Biometric Report Break Time Exceeded on {$date}");
            $this->email->message("
                <p>Dear {$row->employee_name},</p>
                <p>You have exceeded the 65-minute break limit on {$date}.</p>
                <p>Break Time: <strong>{$this->format_hms($break_minutes)}</strong></p>
                <p>Please avoid long breaks during working hours.</p>
                <p>Regards,<br>HR</p>
            ");
            $this->email->send();
            $this->email->clear();
    }

}
}









		
		/* end code biometric */
	public function get_attendance_summary_table($date = null)
		{
			$this->email->set_mailtype("html");
			$this->email->from('noreply@t2gworkroom.com', 'Tech2globe'); 
			$subject = 'Daily Biometric Report: '.date('d-M-Y', strtotime('-1 day'));
			if (!$date) {
				// Generate yesterday's date in d-M-Y format
				$date = date('d-M-Y', strtotime('-1 day')); 
			}

			$table = db_prefix() . 'biometric_report';
			$staff = db_prefix() . 'staff';
			$this->db->select($table . '.*, ' . $staff . '.firstname AS staff_firstname, ' . $staff . '.lastname AS staff_lastname', false);
			$this->db->from($table);
			$this->db->join(
				$staff,
				'TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code) AND ' . $staff . '.active = 1',
				'inner',
				false
			);
			if ($date) {
				$this->db->where($table . '.attendance_date', $date);
			}
			// Lowest / oldest employee code first (e.g. 1007 Sarabjeet before 1806 Manisha).
			$this->db->order_by('CAST(TRIM(' . $table . '.employee_code) AS UNSIGNED)', 'ASC', false);
			$this->db->order_by('TRIM(' . $table . '.employee_code)', 'ASC', false);
			$query = $this->db->get();
			
			$result = $query->result();
			
			if (!$result) {
				return "<p>No data found for this date.</p>";
			}
				$html = '
				<table border="1" cellpadding="5" cellspacing="0" style="
					border-collapse: collapse;
					width: 100%;
					font-family: Arial, sans-serif;
					font-size: 13px;
				">
				<thead style="background-color: #f2f2f2; color: #333;">
					<tr>
						<th style="border: 1px solid #ccc;">S.No</th>
						<th style="border: 1px solid #ccc;">Employee Code</th>
						<th style="border: 1px solid #ccc;">Employee Name</th>
						<th style="border: 1px solid #ccc;">Total IN</th>
						<th style="border: 1px solid #ccc;">Total OUT</th>
						<th style="border: 1px solid #ccc;">First IN</th>
						<th style="border: 1px solid #ccc;">Last IN</th>
						<th style="border: 1px solid #ccc;">Last OUT</th>
						<th style="border: 1px solid #ccc;">Total Login Time</th>
						<th style="border: 1px solid #ccc;">Total Break Time</th>
					</tr>
				</thead>
				<tbody>
				';

			$count = 1;
			foreach ($result as $row) {
				$punches = $this->parse_punch_records($row->punch_records);
				$ins = array_filter($punches, fn($p) => $p['type'] === 'in');
				$outs = array_filter($punches, fn($p) => $p['type'] === 'out');
				   // Skip if both IN and OUT counts are 0
				if (count($ins) === 0 && count($outs) === 0) {
					continue;
				}
				$first_in = $ins ? $ins[array_key_first($ins)]['time'] : '-';
				$last_in = $ins ? $ins[array_key_last($ins)]['time'] : '-';
				$last_out = $outs ? $outs[array_key_last($outs)]['time'] : '-';

				$login_mins = $this->calc_minutes_diff($first_in, $last_out);
				$worked_mins = $this->calc_worked_minutes($punches);
				$break_mins = max(0, $login_mins - $worked_mins);
				$break_hms = $this->format_hms($break_mins);
				$row_style = ($break_mins > 65) 
        ? 'background-color: #f8d7da; color: #721c24;' // light red bg, dark red text
        : 'background-color: #fff; color: #333;';

				$employee_name = trim((string) $row->employee_name);
				if ($employee_name === '') {
					$employee_name = trim(trim((string) ($row->staff_firstname ?? '')) . ' ' . trim((string) ($row->staff_lastname ?? '')));
				}

				$html .= '
    <tr style="' . $row_style . '">
        <td style="border: 1px solid #ddd; text-align: center;">' . $count . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($row->employee_code, ENT_QUOTES, 'UTF-8') . '</td>
        <td style="border: 1px solid #ddd;">' . htmlspecialchars($employee_name, ENT_QUOTES, 'UTF-8') . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . count($ins) . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . count($outs) . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . $first_in . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . $last_in . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . $last_out . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . $this->format_hms($login_mins) . '</td>
        <td style="border: 1px solid #ddd; text-align: center;">' . $break_hms . '</td>
    </tr>
    ';

				$count++;
			}
			if ($count === 1) {
			return "<p>No valid data found for {$date}.</p>";
		}

			$html .= '</tbody></table>';
			//$this->email->to('naved.ahamad1@tech2globe.net');
			 $this->email->to('harpreet.singh@tech2globe.com');
			 $this->email->cc(['hr@tech2globe.com', 'sarabjeet@tech2globe.net', 'monika.sharma@tech2globe.com']);
				$this->email->subject('Break Time Report: ' . $date);
				$this->email->message($html);
			print_r($html);
				if ($this->email->send()) {
					echo 'Mail sent';
				} else {
					echo 'Mail not sent';
					echo $this->email->print_debugger();
				}

				$this->email->clear();
			//return $html;
		}

	/**
	 * Daily late-arrival report from biometric first IN vs assigned shift (15 min grace).
	 * Grace is used only to decide late vs on-time; "Late By" shows actual time after shift start.
	 * Active staff only, sorted by employee code. Includes monthly late-day counter.
	 */
	public function send_daily_late_arrival_report($date = null)
	{
		$grace_mins = 15;

		if (!$date) {
			$date = date('d-M-Y', strtotime('-1 day'));
		}

		$date_obj = DateTime::createFromFormat('d-M-Y', $date);
		if (!$date_obj) {
			echo 'Invalid date: ' . $date;
			return;
		}
		$date_ymd = $date_obj->format('Y-m-d');
		$month_suffix = $date_obj->format('M-Y');

		$table = db_prefix() . 'biometric_report';
		$staff = db_prefix() . 'staff';
		$this->db->select($table . '.*, ' . $staff . '.staffid, ' . $staff . '.firstname AS staff_firstname, ' . $staff . '.lastname AS staff_lastname', false);
		$this->db->from($table);
		$this->db->join(
			$staff,
			'TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code) AND ' . $staff . '.active = 1',
			'inner',
			false
		);
		$this->db->where($table . '.attendance_date', $date);
		$this->db->order_by('CAST(TRIM(' . $table . '.employee_code) AS UNSIGNED)', 'ASC', false);
		$this->db->order_by('TRIM(' . $table . '.employee_code)', 'ASC', false);
		$result = $this->db->get()->result();

		if (!$result) {
			echo "<p>No attendance data for {$date}.</p>";
			return;
		}

		$this->load->model('timesheets/timesheets_model');
		$monthly_late_counts = $this->build_monthly_late_arrival_counts($month_suffix, $date_ymd, $grace_mins);
		$late_rows = [];

		foreach ($result as $row) {
			$late_info = $this->evaluate_late_arrival_for_row($row, $date_ymd, $grace_mins);
			if ($late_info === null) {
				continue;
			}

			$code = trim((string) $row->employee_code);
			$late_rows[] = [
				'employee_code' => $code,
				'employee_name' => $late_info['employee_name'],
				'shift_start'   => $late_info['shift_start'],
				'first_in'      => $late_info['first_in'],
				'late_mins'     => $late_info['late_mins'],
				'month_late_days' => (int) ($monthly_late_counts[$code] ?? 0),
			];
		}

		usort($late_rows, function ($a, $b) {
			$code_a = (int) $a['employee_code'];
			$code_b = (int) $b['employee_code'];
			if ($code_a !== $code_b) {
				return $code_a <=> $code_b;
			}
			return strcmp($a['employee_code'], $b['employee_code']);
		});

		$html = '
		<p style="font-family: Arial, sans-serif; font-size: 13px;">
			Late arrivals for <strong>' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</strong>
			(based on first biometric IN vs assigned shift; <strong>' . (int) $grace_mins . ' minute grace</strong> before flagging as late).
			<strong>Late By</strong> shows actual time after shift start (grace is not deducted). Early arrival is not flagged.
		</p>
		<table border="1" cellpadding="5" cellspacing="0" style="
			border-collapse: collapse;
			width: 100%;
			font-family: Arial, sans-serif;
			font-size: 13px;
		">
		<thead style="background-color: #f2f2f2; color: #333;">
			<tr>
				<th style="border: 1px solid #ccc;">S.No</th>
				<th style="border: 1px solid #ccc;">Employee Code</th>
				<th style="border: 1px solid #ccc;">Employee Name</th>
				<th style="border: 1px solid #ccc;">Shift Start</th>
				<th style="border: 1px solid #ccc;">First IN</th>
				<th style="border: 1px solid #ccc;">Late By</th>
				<th style="border: 1px solid #ccc;">Late Days (' . htmlspecialchars($date_obj->format('M Y'), ENT_QUOTES, 'UTF-8') . ')</th>
			</tr>
		</thead>
		<tbody>';

		if (empty($late_rows)) {
			$html .= '<tr><td colspan="7" style="border: 1px solid #ddd; text-align: center; padding: 12px;">No late arrivals for this date.</td></tr>';
		} else {
			$count = 1;
			foreach ($late_rows as $late) {
				$html .= '
			<tr style="background-color: #fff3cd; color: #856404;">
				<td style="border: 1px solid #ddd; text-align: center;">' . $count . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($late['employee_code'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd;">' . htmlspecialchars($late['employee_name'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($late['shift_start'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($late['first_in'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . $this->format_hms($late['late_mins']) . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . (int) $late['month_late_days'] . '</td>
			</tr>';
				$count++;
			}
		}

		$html .= '</tbody></table>';

		$this->email->set_mailtype('html');
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		$this->email->to('sarabjeet@tech2globe.net');
		$this->email->cc(['hr@tech2globe.com']);
		$this->email->subject('Late Arrival Report: ' . $date);
		$this->email->message($html);
		print_r($html);

		if ($this->email->send()) {
			echo 'Mail sent';
		} else {
			echo 'Mail not sent';
			echo $this->email->print_debugger();
		}

		$this->email->clear();
	}

	/**
	 * Monthly late-arrival summary for the previous calendar month.
	 * Intended to run on the 1st of each month (e.g. 1-Oct sends September data).
	 *
	 * @param int|null $year  e.g. 2026
	 * @param int|null $month e.g. 9 for September
	 */
	public function send_monthly_late_arrival_summary($year = null, $month = null)
	{
		$grace_mins = 15;

		if ($year === null || $month === null) {
			$period = new DateTime('first day of last month');
		} else {
			$period = DateTime::createFromFormat('Y-n-j', (int) $year . '-' . (int) $month . '-1');
		}

		if (!$period) {
			echo 'Invalid month/year.';
			return;
		}

		$month_suffix = $period->format('M-Y');
		$month_label = $period->format('F');
		$month_year_label = $period->format('F Y');
		$last_day_ymd = $period->format('Y-m-t');

		$this->load->model('timesheets/timesheets_model');
		$summary_rows = $this->build_monthly_late_arrival_summary_rows($month_suffix, $last_day_ymd, $grace_mins, $month_label);

		usort($summary_rows, function ($a, $b) {
			$code_a = (int) $a['employee_code'];
			$code_b = (int) $b['employee_code'];
			if ($code_a !== $code_b) {
				return $code_a <=> $code_b;
			}
			return strcmp($a['employee_code'], $b['employee_code']);
		});

		$html = '
		<p style="font-family: Arial, sans-serif; font-size: 13px;">
			Monthly late arrival summary for <strong>' . htmlspecialchars($month_year_label, ENT_QUOTES, 'UTF-8') . '</strong>.
			An employee is marked late when first biometric IN is more than <strong>' . (int) $grace_mins . ' minutes</strong> after assigned shift start.
			<strong>Total Red Marks</strong> = number of late days in the month.
		</p>
		<table border="1" cellpadding="5" cellspacing="0" style="
			border-collapse: collapse;
			width: 100%;
			font-family: Arial, sans-serif;
			font-size: 13px;
		">
		<thead style="background-color: #f2f2f2; color: #333;">
			<tr>
				<th style="border: 1px solid #ccc;">S.No</th>
				<th style="border: 1px solid #ccc;">EmpId</th>
				<th style="border: 1px solid #ccc;">Name</th>
				<th style="border: 1px solid #ccc;">Month</th>
				<th style="border: 1px solid #ccc;">Late Status</th>
				<th style="border: 1px solid #ccc;">Total Red Marks</th>
			</tr>
		</thead>
		<tbody>';

		if (empty($summary_rows)) {
			$html .= '<tr><td colspan="6" style="border: 1px solid #ddd; text-align: center; padding: 12px;">No late arrivals recorded for ' . htmlspecialchars($month_year_label, ENT_QUOTES, 'UTF-8') . '.</td></tr>';
		} else {
			$count = 1;
			foreach ($summary_rows as $row) {
				$html .= '
			<tr style="background-color: #f8d7da; color: #721c24;">
				<td style="border: 1px solid #ddd; text-align: center;">' . $count . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($row['employee_code'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd;">' . htmlspecialchars($row['employee_name'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($row['month'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="border: 1px solid #ddd; text-align: center;">Yes</td>
				<td style="border: 1px solid #ddd; text-align: center; font-weight: bold;">' . (int) $row['red_marks'] . '</td>
			</tr>';
				$count++;
			}
		}

		$html .= '</tbody></table>';

		$this->email->set_mailtype('html');
		$this->email->from('noreply@t2gworkroom.com', 'Tech2globe');
		$this->email->to('sarabjeet@tech2globe.net');
		$this->email->cc(['hr@tech2globe.com']);
		$this->email->subject('Monthly Late Arrival Report: ' . $month_year_label);
		$this->email->message($html);
		print_r($html);

		if ($this->email->send()) {
			echo 'Mail sent';
		} else {
			echo 'Mail not sent';
			echo $this->email->print_debugger();
		}

		$this->email->clear();
	}

	private function normalize_shift_time_for_report($time, $fallback = '')
	{
		$time = trim((string) $time);
		if ($time === '' || $time === '00:00:00' || $time === '00:00') {
			return $fallback;
		}
		if (preg_match('/(\d{1,2}:\d{2})/', $time, $m)) {
			$parts = explode(':', $m[1]);
			return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
		}

		return $fallback;
	}

	private function actual_late_minutes($first_in, $shift_start)
	{
		$first_in = trim((string) $first_in);
		$shift_start = $this->normalize_shift_time_for_report($shift_start, '');
		if ($first_in === '' || $shift_start === '') {
			return 0;
		}

		$in_mins = $this->time_to_minutes(strlen($first_in) === 5 ? $first_in . ':00' : $first_in);
		$shift_mins = $this->time_to_minutes($shift_start . ':00');
		$diff = $in_mins - $shift_mins;

		return $diff > 0 ? (int) round($diff) : 0;
	}

	private function is_late_beyond_grace($first_in, $shift_start, $grace_mins)
	{
		return $this->actual_late_minutes($first_in, $shift_start) > (int) $grace_mins;
	}

	/**
	 * @return array{employee_name:string,shift_start:string,first_in:string,late_mins:int}|null
	 */
	private function evaluate_late_arrival_for_row($row, $date_ymd, $grace_mins)
	{
		$punches = $this->parse_punch_records($row->punch_records ?? '');
		$ins = array_filter($punches, fn($p) => $p['type'] === 'in');
		if (count($ins) === 0) {
			return null;
		}

		$first_in = $ins[array_key_first($ins)]['time'];
		$shift_info = $this->timesheets_model->get_info_hour_shift_staff((int) $row->staffid, $date_ymd);
		$shift_start = $this->normalize_shift_time_for_report($shift_info->start_working ?? '', '');
		if ($shift_start === '') {
			return null;
		}

		$late_mins = $this->actual_late_minutes($first_in, $shift_start);
		if (!$this->is_late_beyond_grace($first_in, $shift_start, $grace_mins)) {
			return null;
		}

		$employee_name = trim((string) ($row->employee_name ?? ''));
		if ($employee_name === '') {
			$employee_name = trim(trim((string) ($row->staff_firstname ?? '')) . ' ' . trim((string) ($row->staff_lastname ?? '')));
		}

		return [
			'employee_name' => $employee_name,
			'shift_start'   => $shift_start,
			'first_in'      => substr($first_in, 0, 8),
			'late_mins'     => $late_mins,
		];
	}

	/**
	 * Count late days in calendar month up to report date (inclusive), keyed by employee code.
	 */
	private function build_monthly_late_arrival_counts($month_suffix, $through_date_ymd, $grace_mins)
	{
		$table = db_prefix() . 'biometric_report';
		$staff = db_prefix() . 'staff';
		$this->db->select($table . '.employee_code, ' . $table . '.attendance_date, ' . $table . '.punch_records, ' . $staff . '.staffid', false);
		$this->db->from($table);
		$this->db->join(
			$staff,
			'TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code) AND ' . $staff . '.active = 1',
			'inner',
			false
		);
		$this->db->like($table . '.attendance_date', '-' . $month_suffix, 'before');
		$rows = $this->db->get()->result();

		$counts = [];
		foreach ($rows as $row) {
			$day_obj = DateTime::createFromFormat('d-M-Y', (string) $row->attendance_date);
			if (!$day_obj || $day_obj->format('Y-m-d') > $through_date_ymd) {
				continue;
			}

			$late_info = $this->evaluate_late_arrival_for_row($row, $day_obj->format('Y-m-d'), $grace_mins);
			if ($late_info === null) {
				continue;
			}

			$code = trim((string) $row->employee_code);
			if (!isset($counts[$code])) {
				$counts[$code] = 0;
			}
			$counts[$code]++;
		}

		return $counts;
	}

	/**
	 * Monthly summary rows for employees with at least one late day.
	 *
	 * @return array<int, array{employee_code:string,employee_name:string,month:string,red_marks:int}>
	 */
	private function build_monthly_late_arrival_summary_rows($month_suffix, $through_date_ymd, $grace_mins, $month_label)
	{
		$table = db_prefix() . 'biometric_report';
		$staff = db_prefix() . 'staff';
		$this->db->select(
			$table . '.employee_code, ' . $table . '.employee_name, ' . $table . '.attendance_date, ' . $table . '.punch_records, '
			. $staff . '.staffid, ' . $staff . '.firstname AS staff_firstname, ' . $staff . '.lastname AS staff_lastname',
			false
		);
		$this->db->from($table);
		$this->db->join(
			$staff,
			'TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code) AND ' . $staff . '.active = 1',
			'inner',
			false
		);
		$this->db->like($table . '.attendance_date', '-' . $month_suffix, 'before');
		$rows = $this->db->get()->result();

		$summary = [];
		foreach ($rows as $row) {
			$day_obj = DateTime::createFromFormat('d-M-Y', (string) $row->attendance_date);
			if (!$day_obj || $day_obj->format('Y-m-d') > $through_date_ymd) {
				continue;
			}

			$late_info = $this->evaluate_late_arrival_for_row($row, $day_obj->format('Y-m-d'), $grace_mins);
			if ($late_info === null) {
				continue;
			}

			$code = trim((string) $row->employee_code);
			if (!isset($summary[$code])) {
				$name = trim((string) ($row->employee_name ?? ''));
				if ($name === '') {
					$name = trim(trim((string) ($row->staff_firstname ?? '')) . ' ' . trim((string) ($row->staff_lastname ?? '')));
				}
				if ($name === '' && !empty($late_info['employee_name'])) {
					$name = $late_info['employee_name'];
				}

				$summary[$code] = [
					'employee_code' => $code,
					'employee_name' => $name,
					'month'         => $month_label,
					'red_marks'     => 0,
				];
			}
			$summary[$code]['red_marks']++;
		}

		return array_values($summary);
	}

		private function parse_punch_records($punchStr)
				{
					$entries = explode(',', $punchStr);
					$punches = [];
					foreach ($entries as $entry) {
						if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i', trim($entry), $m)) {
							$time = (strlen($m[1]) === 5) ? $m[1] . ':00' : $m[1];  // Ensure HH:MM:SS
							$punches[] = [
								'time' => $time,
								'type' => strtolower($m[2]),
								'minutes' => $this->time_to_minutes($time)
							];
						}
					}
					return $punches;
				}
		private function time_to_minutes($time)
			{
				[$h, $m, $s] = explode(':', $time);
				return ((int)$h * 60) + (int)$m + ((int)$s / 60);
			}

		private function calc_minutes_diff($t1, $t2)
			{
				if ($t1 === '-' || $t2 === '-') return 0;
				$m1 = $this->time_to_minutes($t1);
				$m2 = $this->time_to_minutes($t2);
				if ($m2 < $m1) $m2 += 1440;  // next day
				return $m2 - $m1;
			}


		private function calc_worked_minutes($punches)
			{
				$total = 0;
				$temp_in = null;
				foreach ($punches as $p) {
					if ($p['type'] === 'in') {
						$temp_in = $p['time'];
					} elseif ($p['type'] === 'out' && $temp_in) {
						$total += $this->calc_minutes_diff($temp_in, $p['time']);
						$temp_in = null;
					}
				}
				return $total;
			}


		private function format_hms($mins)
			{
				$h = floor($mins / 60);
				$m = floor($mins % 60);
				$s = round(($mins - floor($mins)) * 60);
				return sprintf('%02d:%02d:%02d', $h, $m, $s);
			}

    /**
     * Email PEDMA evaluation reminders to managers.
     * Emails only on days 1, 5, and 10. After the 10th, managers are reminded via Workroom popup only.
     */
    public function send_pedma_evaluation_reminders($force = false)
    {
        if (!$force && function_exists('pedma_eval_reminders_are_active') && !pedma_eval_reminders_are_active()) {
            return [
                'skipped' => true,
                'reason'  => 'starts_' . (function_exists('pedma_eval_reminders_start_date') ? pedma_eval_reminders_start_date() : '2026-09-01'),
                'sent'    => 0,
            ];
        }

        $day = (int) date('j');
        $today = date('Y-m-d');
        $shouldSend = in_array($day, [1, 5, 10], true);

        if (!$shouldSend && !$force) {
            return ['skipped' => true, 'reason' => 'not_reminder_day', 'sent' => 0];
        }

        $optionKey = 'pedma_eval_reminder_sent_' . $today;
        if (!$force && get_option($optionKey) === '1') {
            return ['skipped' => true, 'reason' => 'already_sent_today', 'sent' => 0];
        }

        $this->load->model('staff_model');
        $monthYm = $this->staff_model->get_pedma_eval_target_month();
        $managers = $this->staff_model->get_managers_with_pending_pedma_eval($monthYm);

        $sent = 0;
        foreach ($managers as $manager) {
            if ($this->staff_model->send_pedma_evaluation_reminder_email($manager, $day)) {
                $sent++;
            }
        }

        update_option($optionKey, '1');
        log_activity('PEDMA evaluation reminders sent to ' . $sent . ' manager(s) for month ' . $monthYm . ' (day ' . $day . ')');

        return [
            'skipped' => false,
            'month'   => $monthYm,
            'day'     => $day,
            'managers'=> count($managers),
            'sent'    => $sent,
        ];
    }

}



