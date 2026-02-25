<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Test extends App_Controller
{
    public function index($key = '')
    {
        $date = date('m/d/Y', strtotime("-1 days"));

        try {

            $this->email->set_mailtype("html");
            $this->email->from('no-reply@t2gworkroom.com', 'Tech2globe');
            $this->email->to('prateekjaintglobe@gmail.com');
            //$this->email->to('bhavyakhanna.tech2globe@gmail.com');
            // $this->email->to('hr@tech2globe.com');
            // $this->email->cc(array('sarabjeet@tech2globe.com'));
            $this->email->cc(array('bhavya.khanna@tech2globe.in','naved.ahamad@tech2globe.in','bhavyakhanna.tech2globe@gmail.com'));

            // $this->email->cc(array('ishan.negi@tech2globe.in','sarabjeet@tech2globe.net','sarabjeet@tech2globe.com'));
            $subject = "Daily Report - T2GWorkroom - $date ";

            $message = 'test';

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
            log_message('error', 'Workroom report email sent!');
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }



}
