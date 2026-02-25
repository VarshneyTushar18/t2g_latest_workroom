<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Emails extends AdminController

{

    public function __construct()

    {

        parent::__construct();

        $this->load->model('emails_model');
    }



    /* List all email templates */

    public function index()

    {

        if (!has_permission('email_templates', '', 'view')) {

            access_denied('email_templates');
        }

        $langCheckings = get_option('email_templates_language_checks');

        if ($langCheckings == '') {

            $langCheckings = [];
        } else {

            $langCheckings = unserialize($langCheckings);
        }





        $this->db->where('language', 'english');

        $email_templates_english = $this->db->get(db_prefix() . 'emailtemplates')->result_array();

        foreach ($this->app->get_available_languages() as $avLanguage) {

            if ($avLanguage != 'english') {

                foreach ($email_templates_english as $template) {



                    // Result is cached and stored in database

                    // This page may perform 1000 queries per request

                    if (isset($langCheckings[$template['slug'] . '-' . $avLanguage])) {

                        continue;
                    }



                    $notExists = total_rows(db_prefix() . 'emailtemplates', [

                        'slug'     => $template['slug'],

                        'language' => $avLanguage,

                    ]) == 0;



                    $langCheckings[$template['slug'] . '-' . $avLanguage] = 1;



                    if ($notExists) {

                        $data              = [];

                        $data['slug']      = $template['slug'];

                        $data['type']      = $template['type'];

                        $data['language']  = $avLanguage;

                        $data['name']      = $template['name'] . ' [' . $avLanguage . ']';

                        $data['subject']   = $template['subject'];

                        $data['message']   = '';

                        $data['fromname']  = $template['fromname'];

                        $data['plaintext'] = $template['plaintext'];

                        $data['active']    = $template['active'];

                        $data['order']     = $template['order'];

                        $this->db->insert(db_prefix() . 'emailtemplates', $data);
                    }
                }
            }
        }



        update_option('email_templates_language_checks', serialize($langCheckings));



        $data['staff'] = $this->emails_model->get([

            'type'     => 'staff',

            'language' => 'english',

        ]);



        $data['credit_notes'] = $this->emails_model->get([

            'type'     => 'credit_note',

            'language' => 'english',

        ]);



        $data['tasks'] = $this->emails_model->get([

            'type'     => 'tasks',

            'language' => 'english',

        ]);

        $data['client'] = $this->emails_model->get([

            'type'     => 'client',

            'language' => 'english',

        ]);

        $data['tickets'] = $this->emails_model->get([

            'type'     => 'ticket',

            'language' => 'english',

        ]);

        $data['invoice'] = $this->emails_model->get([

            'type'     => 'invoice',

            'language' => 'english',

        ]);

        $data['estimate'] = $this->emails_model->get([

            'type'     => 'estimate',

            'language' => 'english',

        ]);

        $data['contracts'] = $this->emails_model->get([

            'type'     => 'contract',

            'language' => 'english',

        ]);

        $data['proposals'] = $this->emails_model->get([

            'type'     => 'proposals',

            'language' => 'english',

        ]);

        $data['projects'] = $this->emails_model->get([

            'type'     => 'project',

            'language' => 'english',

        ]);

        $data['leads'] = $this->emails_model->get([

            'type'     => 'leads',

            'language' => 'english',

        ]);



        $data['gdpr'] = $this->emails_model->get([

            'type'     => 'gdpr',

            'language' => 'english',

        ]);



        $data['subscriptions'] = $this->emails_model->get([

            'type'     => 'subscriptions',

            'language' => 'english',

        ]);



        $data['estimate_request'] = $this->emails_model->get([

            'type'     => 'estimate_request',

            'language' => 'english',

        ]);



        $data['notifications'] = $this->emails_model->get([

            'type'     => 'notifications',

            'language' => 'english',

        ]);



        $data['title'] = _l('email_templates');



        $data['hasPermissionEdit'] = has_permission('email_templates', '', 'edit');



        $this->load->view('admin/emails/email_templates', $data);
    }



    /* Edit email template */

    public function email_template($id)

    {

        if (!has_permission('email_templates', '', 'view')) {

            access_denied('email_templates');
        }

        if (!$id) {

            redirect(admin_url('emails'));
        }



        if ($this->input->post()) {

            if (!has_permission('email_templates', '', 'edit')) {

                access_denied('email_templates');
            }



            $data = $this->input->post();

            $tmp  = $this->input->post(null, false);



            foreach ($data['message'] as $key => $contents) {

                $data['message'][$key] = $tmp['message'][$key];
            }



            foreach ($data['subject'] as $key => $contents) {

                $data['subject'][$key] = $tmp['subject'][$key];
            }



            $data['fromname'] = $tmp['fromname'];



            $success = $this->emails_model->update($data, $id);



            if ($success) {

                set_alert('success', _l('updated_successfully', _l('email_template')));
            }



            redirect(admin_url('emails/email_template/' . $id));
        }



        // English is not included here

        $data['available_languages'] = $this->app->get_available_languages();



        if (($key = array_search('english', $data['available_languages'])) !== false) {

            unset($data['available_languages'][$key]);
        }



        $data['available_merge_fields'] = $this->app_merge_fields->all();



        $data['template'] = $this->emails_model->get_email_template_by_id($id);

        $title            = $data['template']->name;

        $data['title']    = $title;

        $this->load->view('admin/emails/template', $data);
    }



    public function enable_by_type($type)

    {

        if (has_permission('email_templates', '', 'edit')) {

            $this->emails_model->mark_as_by_type($type, 1);
        }

        redirect(admin_url('emails'));
    }



    public function disable_by_type($type)

    {

        if (has_permission('email_templates', '', 'edit')) {

            $this->emails_model->mark_as_by_type($type, 0);
        }

        redirect(admin_url('emails'));
    }



    public function enable($id)

    {

        if (has_permission('email_templates', '', 'edit')) {

            $template = $this->emails_model->get_email_template_by_id($id);

            $this->emails_model->mark_as($template->slug, 1);
        }

        redirect(admin_url('emails'));
    }



    public function disable($id)

    {

        if (has_permission('email_templates', '', 'edit')) {

            $template = $this->emails_model->get_email_template_by_id($id);

            $this->emails_model->mark_as($template->slug, 0);
        }



        redirect(admin_url('emails'));
    }



    /* Since version 1.0.1 - test your smtp settings */

    public function sent_smtp_test_email()

    {

        if ($this->input->post()) {

            $this->load->config('email');

            // Simulate fake template to be parsed

            $template           = new StdClass();

            $template->message  = get_option('email_header') . 'This is test SMTP email. <br />If you received this message that means that your SMTP settings is set correctly.' . get_option('email_footer');

            $template->fromname = get_option('companyname') != '' ? get_option('companyname') : 'TEST';

            $template->subject  = 'SMTP Setup Testing';



            $template = parse_email_template($template);



            hooks()->do_action('before_send_test_smtp_email');

            $this->email->initialize();

            if (get_option('mail_engine') == 'phpmailer') {

                $this->email->set_debug_output(function ($err) {

                    if (!isset($GLOBALS['debug'])) {

                        $GLOBALS['debug'] = '';
                    }

                    $GLOBALS['debug'] .= $err . '<br />';



                    return $err;
                });



                $this->email->set_smtp_debug(3);
            }



            $this->email->set_newline(config_item('newline'));

            $this->email->set_crlf(config_item('crlf'));



            $this->email->from(get_option('smtp_email'), $template->fromname);

            $this->email->to($this->input->post('test_email'));



            $systemBCC = get_option('bcc_emails');



            if ($systemBCC != '') {

                $this->email->bcc($systemBCC);
            }



            $this->email->subject($template->subject);

            $this->email->message($template->message);



            if ($this->email->send(true)) {

                set_alert('success', 'Seems like your SMTP settings is set correctly. Check your email now.');

                hooks()->do_action('smtp_test_email_success');
            } else {

                set_debug_alert('<h1>Your SMTP settings are not set correctly here is the debug log.</h1><br />' . $this->email->print_debugger() . (isset($GLOBALS['debug']) ? $GLOBALS['debug'] : ''));



                hooks()->do_action('smtp_test_email_failed');
            }
        }
    }



    public function delete_queued_email($id)

    {

        if (staff_can('edit', 'settings')) {

            $this->email->delete_queued_email($id);

            set_alert('success', _l('deleted', _l('email_queue')));
        }



        redirect(admin_url('settings?group=email&tab=email_queue'));
    }





    // custom controller to send email from timesheet.



    public function email_datatable_data()

    {

        if ($this->input->post()) {



            $data = $this->input->post('data');



            // echo '<pre>';
            // print_r($data);
            // die;



            // echo '<script>console.log('.print_r($_SESSION).')</script>';  die;





            // for($i=0; $i<count($data[2]['value']);$i++){

            //     $val = implode($data[2]['value'][$i]);

            // }


            try {

                $to = $data[1]['value'];

                $cc = $data[2]['value'];







                // print_r($report); die;



                // $periodText = $data[14]['periodText'];

                // $periodfrom = $data[15]['periodfrom'];

                // $periodto = $data[16]['periodto'];

                // $report = $data[17]['data'];

                if (is_admin()) {

                    $periodText = $data[14]['periodText'];

                    $periodfrom = $data[15]['periodfrom'];

                    $periodto = $data[16]['periodto'];

                    $report = $data[17]['dataTableData'];
                } else {
                    $periodText = $data[13]['periodText'];

                    $periodfrom = $data[14]['periodfrom'];

                    $periodto = $data[15]['periodto'];

                    $report = $data[16]['dataTableData'];
                }


                $period = '';



                $period = changeSubjectAccordingtoPeriod($periodText, $periodto, $periodfrom);





                // die($period);





                $staffid = get_staff_user_id();

                $current_user = $this->staff_model->get($staffid);

                $current_user_name = get_staff_full_name($staffid);







                $timesheetData = array(array());







                $summary = generateSummary($report);





                $this->load->config('email');

                // Simulate fake template to be parsed

                $template           = new StdClass();

                $template->message  = get_option('email_header');



                $template->message .= '<h1>Employee Productivity</h1>

            <table border="1">

                <thead>

                    <tr>

                        <th>Employee Name</th>

                        <th>Break Time</th>

                        <th>Total Hours</th>

                    </tr>

                </thead>

                <tbody>';





                foreach ($summary as $employeeName => $data) {

                    $template->message .= '<tr><td>';

                    $template->message .= $employeeName . '</td><td>';

                    $template->message .= decimalToTime($data['break_hours']) . '</td><td>';

                    $template->message .= decimalToTime($data['total_hours']) . '</td></tr>';
                }



                $template->message .= '</tbody></table><h1>Employee Tasks</h1><table><thead ><tr><th>Staff Member</th><th>Task</th><th>Start Time</th><th>End Time</th><th>Project Name</th><th>Time(h)</th></tr><thead><tbody>';

                foreach ($report as $reportData) {

                    $template->message .= '<tr>';

                    for ($i = 0; $i < count($reportData) - 1; $i++) {

                        if ($i === 2 || $i === 5) continue;

                        $template->message .= '<td>' . $reportData[$i] . '</td>';
                    }

                    $template->message .= '</tr>';
                }

                $template->message .= '</tbody></table>' . get_option('email_footer');

                // echo $template->message ;  die;

                $template->fromname = get_option('companyname') != '' ? get_option('companyname') : 'TEST';

                $template->subject  = 'PMT Report : ' . $period;



                // echo $template->subject ; die;



                $template = parse_email_template($template);



                hooks()->do_action('before_send_test_smtp_email');

                $this->email->initialize();

                if (get_option('mail_engine') == 'phpmailer') {

                    $this->email->set_debug_output(function ($err) {

                        if (!isset($GLOBALS['debug'])) {

                            $GLOBALS['debug'] = '';
                        }

                        $GLOBALS['debug'] .= $err . '<br />';



                        return $err;
                    });



                    $this->email->set_smtp_debug(3);
                }



                $this->email->set_newline(config_item('newline'));

                $this->email->set_crlf(config_item('crlf'));



                $this->email->from($current_user->email, $current_user_name);

                // $this->email->to($this->input->post('test_email'));

                $this->email->to($to);



                $this->email->cc($cc);



                $this->email->bcc($current_user->email);





                $this->email->subject($template->subject);

                $this->email->message($template->message);



                if ($this->email->send(true)) {

                    set_alert('success', 'Seems like your SMTP settings is set correctly. Check your email now.');

                    hooks()->do_action('smtp_test_email_success');

                    echo 'success';
                } else {

                    set_debug_alert('<h1>Your SMTP settings are not set correctly here is the debug log.</h1><br />' . $this->email->print_debugger() . (isset($GLOBALS['debug']) ? $GLOBALS['debug'] : ''));



                    hooks()->do_action('smtp_test_email_failed');

                    echo 'fail';
                }
            } catch (Exception $e) {

                echo 'Error: ' . $e->getMessage();
            }
        }
    }
}
