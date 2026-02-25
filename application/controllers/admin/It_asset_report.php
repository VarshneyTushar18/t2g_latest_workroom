<?php


defined('BASEPATH') or exit('No direct script access allowed');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

class It_asset_report extends AdminController
{

    public function index()
    {
        if ($this->input->post()) {
            $from = date("Y-m-d", strtotime($this->input->post('from')));
            $to = date("Y-m-d", strtotime($this->input->post('to')));
            $data = $this->get_asset_data($from, $to);
            $this->it_asset_weekly_email($data, $from, $to);
        }
    }

    public function get_asset_data($from, $to)
    {
        $get_asset_query = "SELECT tblstaff.staff_identifi, assets , tblassets_acction_1.acction_to, tblassets_acction_1.reporting_manager FROM tblassets_acction_1 LEFT JOIN tblassets ON tblassets.id = tblassets_acction_1.assets LEFT JOIN tblstaff ON tblstaff.staffid = tblassets_acction_1.acction_to WHERE type = 'allocation' AND (DATE(tblassets_acction_1.time_acction) BETWEEN '$from' AND '$to')";
        $asset_data = $this->db->query($get_asset_query)->result_array();


        $staff_assets = array();

        foreach ($asset_data as $data) {
            $acction_to = $data['acction_to'];
            $assets = $data['assets'];
            $emp_id = $data['staff_identifi'];
            $reporting_manager = $data['reporting_manager'];
            if ($this->check_if_revoke($assets, $acction_to)) {
                continue;
            }
            if (!array_key_exists($acction_to, $staff_assets)) {
                $staff_assets[$acction_to] = [];
            }

            $staff_assets[$acction_to]['assets'][] = $assets;
            $staff_assets[$acction_to]['emp_id'] = $emp_id;
            $staff_assets[$acction_to]['reporting_manager'] = $reporting_manager;
        }

        // print_r($staff_assets);die;

        // print_r(array_column($staff_assets,'assets'));die;
        return $staff_assets;
    }

    public function maximum_number_of_assets($staff_assets)
    {
        $max = 0;
        foreach ($staff_assets as $staff_asset) {
            $asset_count = count($staff_asset);
            if ($asset_count > $max) $max = $asset_count;
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
        $CI           = &get_instance();
        $CI->db->where('id', $id);
        $assets = $CI->db->get(db_prefix() . 'assets')->row();

        if ($assets) {

            return $assets->assets_code;
        } else {
            return '';
        }
    }

    public function it_asset_weekly_email($staff_assets, $from, $to)
    {

        try {
            $this->email->set_mailtype("html");
            $this->email->from('noreply_workroom@tech2globe.com', 'Tech2globe');
            $this->email->to(get_staff_email_id(get_staff_user_id()));
            $this->email->to('navneet.baid@tech2globe.in');
            // $this->email->cc(array('sarabjeet@tech2globe.com','sarabjeet@tech2globe.net'));
            $subject = "IT Asset Summary Report ($from to $to)";

            $message = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Asset Group Summary</title>
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
                <h3>Asset Group Summary</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Employee Name</th>
                            <th>Reporting Manager</th>
                            
                ';

            $assets_array = array_column($staff_assets, 'assets');
            $max_assets = $this->maximum_number_of_assets($assets_array);

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
                    <td>" . $value['emp_id'] . "</td>
                    <td>" . get_staff_full_name($key) . "</td>
                    <td>" . get_staff_full_name($value['reporting_manager']) . "</td>";

                for ($i = 0; $i < $max_assets; $i++) {
                    if (isset($value['assets'][$i])) {
                        $message .= "<td>" . $this->get_asset_code_by_id($value['assets'][$i]) . "</td>";
                    } else {
                        $message .= "<td></td>";
                    }
                }
                // foreach($value as $asset_id){
                //     $message .= "<td>".$this->get_asset_code_by_id($asset_id)."</td>";
                // }
                $message .= "<td>" . count($value['assets']) . "</td></tr>";
            }


            $message .= "</tbody></table></body></html>";

            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo 'success';
                die;
            } else {
                echo 'fail';
                die;
            }
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }
}
