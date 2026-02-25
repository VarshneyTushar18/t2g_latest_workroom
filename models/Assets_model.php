<?php

defined('BASEPATH') or exit('No direct script access allowed');

class assets_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if ('' == $id) {
            return  $this->db->get(db_prefix() . 'assets')->result_array();
        }
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'assets')->row();
    }

    public function get_clients_assign_assets($table, $where = [])
    {
        $this->db->select(db_prefix() . 'assets.*,' . db_prefix() . 'asset_unit.*,' . db_prefix() . 'assets_group.*,' . db_prefix() . 'departments.*, ' . db_prefix() . 'departments.name as dpm_name');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->where('visible_to_client', 1);

        $this->db->join(db_prefix() . 'asset_unit', db_prefix() . 'asset_unit.unit_id = ' . db_prefix() . 'assets.unit', 'LEFT');
        $this->db->join(db_prefix() . 'assets_group', db_prefix() . 'assets_group.group_id = ' . db_prefix() . 'assets.asset_group', 'LEFT');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'assets.department', 'LEFT');

        return $this->db->get(db_prefix() . $table)->result_array();
    }

    public function get_asset_group($id = '')
    {
        if ('' == $id) {
            return  $this->db->get(db_prefix() . 'assets_group')->result_array();
        }
        $this->db->where('group_id', $id);

        return $this->db->get(db_prefix() . 'assets_group')->row();
    }

    public function get_asset_unit($id = '')
    {
        if ('' == $id) {
            return  $this->db->get(db_prefix() . 'asset_unit')->result_array();
        }
        $this->db->where('unit_id', $id);

        return $this->db->get(db_prefix() . 'asset_unit')->row();
    }

    public function get_asset_location($id = '')
    {
        if ('' == $id) {
            return  $this->db->get(db_prefix() . 'asset_location')->result_array();
        }
        $this->db->where('location_id', $id);

        return $this->db->get(db_prefix() . 'asset_location')->row();
    }

    public function add_asset_group($data)
    {
        $this->db->insert(db_prefix() . 'assets_group', $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    public function update_asset_group($data, $id)
    {
        $this->db->where('group_id', $id);
        $this->db->update(db_prefix() . 'assets_group', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete_asset_group($id)
    {
        $this->db->where('group_id', $id);
        $this->db->delete(db_prefix() . 'assets_group');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function add_asset_unit($data)
    {
        $this->db->insert(db_prefix() . 'asset_unit', $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    public function update_asset_unit($data, $id)
    {
        $this->db->where('unit_id', $id);
        $this->db->update(db_prefix() . 'asset_unit', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete_asset_unit($id)
    {
        $this->db->where('unit_id', $id);
        $this->db->delete(db_prefix() . 'asset_unit');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function add_asset_location($data)
    {
        $this->db->insert(db_prefix() . 'asset_location', $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    public function update_asset_location($data, $id)
    {
        $this->db->where('location_id', $id);
        $this->db->update(db_prefix() . 'asset_location', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete_asset_location($id)
    {
        $this->db->where('location_id', $id);
        $this->db->delete(db_prefix() . 'asset_location');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function add_asset($data)
    {
        $data['unit_price'] = reformat_currency_asset($data['unit_price']);
        $data['date_buy']   = to_sql_date($data['date_buy']);
        if (isset($data['file_asset'])) {
            unset($data['file_asset']);
        }
        if (!empty($data['clientid'])) {
            $data['belongs_to'] = implode(',', $data['clientid']);
            unset($data['clientid']);
        }
        if (isset($data['visible_to_client'])) {
            $data['visible_to_client'] = 1;
        }

        $this->db->insert(db_prefix() . 'assets', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $insert_id,
                'date_time'       => $data['date_buy'],
                'acction'         => 'add_new',
                'inventory_begin' => 0,
                'inventory_end'   => $data['amount'],
                'cost'            => $data['unit_price'] * $data['amount'],
            ]);

            // this function will email when asset is added
            $this->add_asset_mail($data);
            return $insert_id;
        }
    }

    public function update_asset($data, $id)
    {
        if (!empty($data['clientid'])) {
            $data['belongs_to'] = implode(',', $data['clientid']);
            unset($data['clientid']);
        }
        if (isset($data['visible_to_client'])) {
            $data['visible_to_client'] = 1;
        } else {
            $data['visible_to_client'] = 0;
        }
        if (!empty($data['unit_price'])) {
            $data['unit_price'] = reformat_currency_asset($data['unit_price']);
        }
        if (!empty($data['date_buy'])) {
            $data['date_buy']   = to_sql_date($data['date_buy']);
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assets', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete_assets($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'assets');
        $attachments = $this->db->get('tblfiles')->result_array();
        foreach ($attachments as $attachment) {
            $this->delete_assets_attachment($attachment['id']);
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assets');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function get_assets_attachments($assets, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $assets);
        }
        $this->db->where('rel_type', 'assets');
        $result = $this->db->get('tblfiles');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    public function delete_assets_attachment($id)
    {
        $attachment = $this->get_assets_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(ASSETS_UPLOAD_FOLDER . '/' . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(ASSETS_UPLOAD_FOLDER . '/' . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(ASSETS_UPLOAD_FOLDER . '/' . $attachment->rel_id);
                if (0 == count($other_attachments)) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(ASSETS_UPLOAD_FOLDER . '/' . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function get_asset_file($asset)
    {
        $this->db->where('rel_id', $asset);
        $this->db->where('rel_type', 'assets');

        return $this->db->get('tblfiles')->result_array();
    }

    public function get_file($id, $rel_id = false)
    {
        $this->db->where('id', $id);
        $file = $this->db->get('tblfiles')->row();

        if ($file && $rel_id) {
            if ($file->rel_id != $rel_id) {
                return false;
            }
        }

        return $file;
    }

    public function allocation_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_1', $data);
        if ($insert_id) {
            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation - $data['amount'],
            ]);

            $this->db->where('id', $data['assets']);
            $this->db->update(db_prefix() . 'assets', ['total_allocation' => $assets->total_allocation + $data['amount']]);

            // custom function to send mail on allocation
            $this->allocation_asset_mail($data);

            return $insert_id;
        }
    }

    public function get_asset_allocation_by_staff($staff, $asset)
    {
        $this->db->where('acction_to', $staff);
        $this->db->where('assets', $asset);
        $this->db->where('type', 'allocation');

        return $this->db->get(db_prefix() . 'assets_acction_1')->result_array();
    }

    public function get_asset_revoke_by_staff($staff, $asset)
    {
        $this->db->where('acction_to', $staff);
        $this->db->where('assets', $asset);
        $this->db->where('type', 'revoke');

        return $this->db->get(db_prefix() . 'assets_acction_1')->result_array();
    }

    public function get_amount_asset_broken($asset)
    {
        $this->db->where('assets', $asset);
        $this->db->where('type', 'broken');

        return $this->db->get(db_prefix() . 'assets_acction_2')->result_array();
    }

    public function get_amount_asset_warranty($asset)
    {
        $this->db->where('assets', $asset);
        $this->db->where('type', 'warranty');

        return $this->db->get(db_prefix() . 'assets_acction_2')->result_array();
    }

    public function revoke_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_1', $data);
        if ($insert_id) {
            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation + $data['amount'],
            ]);
            $this->revoke_asset_mail($data);
            $this->db->where('id', $data['assets']);
            $this->db->update(db_prefix() . 'assets', [
                'total_allocation' => $assets->total_allocation - $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function additional_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['acction_from'] = get_staff_user_id();
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $data['cost']         = $assets->unit_price * $data['amount'];
        $insert_id            = $this->db->insert('tblassets_acction_2', $data);
        if ($insert_id) {
            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation + $data['amount'],
                'cost'            => $data['cost'],
            ]);

            $this->db->where('id', $data['assets']);
            $this->db->update(db_prefix() . 'assets', [
                'amount' => $assets->amount + $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function lost_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['acction_from'] = get_staff_user_id();
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_2', $data);
        $asset_id             = $data['assets'];
        if ($insert_id) {

            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation - $data['amount'],
            ]);

            $this->lost_asset_mail($data);
            $this->db->where('id', $asset_id);
            $this->db->update(db_prefix() . 'assets', [
                'amount'     => $assets->amount - $data['amount'],
                'total_lost' => $assets->total_lost + $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function broken_asset($data)
    {

        $assets               = $this->get($data['assets']);
        $data['acction_from'] = get_staff_user_id();
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_2', $data);
        $asset_id             = $data['assets'];

        if ($insert_id) {

            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation,
            ]);

            $this->broken_asset_mail($data);
            $this->db->where('id', $asset_id);
            $this->db->update(db_prefix() . 'assets', [
                'total_damages' => $assets->total_damages + $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function liquidation_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['cost']         = reformat_currency_asset($data['cost']);
        $data['acction_from'] = get_staff_user_id();
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_2', $data);
        $asset_id             = $data['assets'];

        if ($insert_id) {

            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation - $data['amount'],
                'cost'            => $data['cost'],
            ]);

            $this->liquidation_asset_mail($data);
            $this->db->where('id', $asset_id);
            $this->db->update(db_prefix() . 'assets', [
                'amount'            => $assets->amount - $data['amount'],
                'total_liquidation' => $assets->total_liquidation + $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function warranty_asset($data)
    {
        $assets               = $this->get($data['assets']);
        $data['cost']         = reformat_currency_asset($data['cost']);
        $data['acction_from'] = get_staff_user_id();
        $data['time_acction'] = to_sql_date($data['time_acction'], true);
        $insert_id            = $this->db->insert('tblassets_acction_2', $data);
        $asset_id             = $data['assets'];

        if ($insert_id) {

            $this->db->insert(db_prefix() . 'inventory_history', [
                'assets'          => $data['assets'],
                'date_time'       => $data['time_acction'],
                'acction'         => $data['type'],
                'inventory_begin' => $assets->amount - $assets->total_allocation,
                'inventory_end'   => $assets->amount - $assets->total_allocation,
                'cost'            => $data['cost'],
            ]);
            $this->warranty_asset_mail($data);

            $this->db->where('id', $asset_id);
            $this->db->update(db_prefix() . 'assets', [
                'total_warranty' => $assets->total_liquidation + $data['amount'],
                'total_damages'  => $assets->total_damages - $data['amount'],
            ]);

            return $insert_id;
        }
    }

    public function get_assets($id = '')
    {
        if ('' != $id) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'assets')->row();
        }

        return $this->db->get(db_prefix() . 'assets')->result_array();
    }


    private function sendEmail($subject, $message)
    {
        $this->load->library('email');
        $this->email->set_mailtype("html");
        $this->email->from('noreply_workroom@tech2globe.com', 'IT Assests ');
        $this->email->to('it.support@tech2globe.in');
        $list = array('sarabjeet@tech2globe.com','harpreet@tech2globe.net');
        $this->email->cc($list);

        $this->email->subject($subject);
        $this->email->message($message);
      
        if ($this->email->send()) {
            echo 'Email sent successfully.';
            // die;
        } else {
            echo 'Email sending failed.';
            $error = $this->email->print_debugger(['headers']);
            // die;
        }
    }

    // creating function to sending mail when new asset is added

    public function add_asset_mail($data)
    {

        $subject = 'New Asset Added ' . $data['assets_code'];

        $asset_location = $this->assets_model->get_asset_location($data['asset_location'])->location;
        $asset_group = $this->assets_model->get_asset_group($data['asset_group'])->group_name;


        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Asset Added</title>
        </head>
        <body>
            <p><strong>A New Asset has been Added Below:</strong></p>
            
            <ul>
                <li><strong>Asset Code:</strong> ' . $data['assets_code'] . '</li>
                <li><strong>Asset Name:</strong> ' . $data['assets_name'] . '</li>
                <li><strong>Quantity:</strong> ' . $data['amount'] . '</li>
                <li><strong>Unit:</strong> ' . $data['unit'] . '</li>
                <li><strong>Series/Model:</strong> ' . $data['series'] . '</li>
                <li><strong>Asset Group:</strong> ' . $data['asset_group'] . '</li>
                <li><strong>Asset Location:</strong> ' . $asset_location . '</li>
                <li><strong>Processor:</strong> ' .  $data['processor'] . '</li>
                <li><strong>RAM:</strong> ' .  $data['ram'] . '</li>
                <li><strong>Serial Number:</strong> ' .  $data['serial_no'] . '</li>
                <li><strong>Storage Type:</strong> ' . $data['storage_type'] . '</li>
                <li><strong>Storage 1:</strong> ' .  $data['storage_1'] . '</li>
                <li><strong>Storage 2:</strong> ' .  $data['storage_2'] . '</li>

            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }

    public function allocation_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        $allocated_to_emp = $this->db->query('SELECT empid FROM tblstaff_info WHERE staffid = ' . $data['acction_to'])->row();
        $allocated_from_emp =  $this->db->get_where('tblstaff_info', array('staffid' => get_staff_user_id()))->row();
        $subject = 'Asset Allocated ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Allocated </title>
        </head>
        <body>
            <p><strong>An Asset has been allocated to below details: </strong></p>
            
            <ul>
                <li><strong>Asset Code:</strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name:</strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Allocated to:</strong> ' . 'Name: ' . get_staff_full_name($data['acction_to']) . ', EMP ID: ' . $allocated_to_emp->empid . '</li>
                <li><strong>Allocated by:</strong> ' . 'Name: ' . get_staff_full_name() . ', EMP ID: ' . $allocated_from_emp->empid . '</li>
                <li><strong>Location:</strong> ' . $data['acction_location'] . '</li>
                <li><strong>Allocation Date & time: </strong> ' .  $data['time_acction'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }

    public function revoke_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        $allocated_to_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $data['acction_to']))->row();

        $allocated_from_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $data['acction_from']))->row();

        $subject = 'Asset revoked - ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Revoked </title>
        </head>
        <body>
            <p><strong>Asset has been Revoked Below Details:</strong></p>
            
            <ul>
                <li><strong>Asset Code:</strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name:</strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Revoked From:</strong> ' . 'Name: ' . get_staff_full_name($data['acction_to']) . ', EMP ID: ' . $allocated_to_emp->empid . '</li>
                <li><strong>Revoked by:</strong> ' . 'Name: ' . get_staff_full_name($data['acction_from']) . ', EMP ID: ' . $allocated_from_emp->empid . '</li>
                <li><strong>Location:</strong> ' . $data['acction_location'] . '</li>
                <li><strong>Reason: </strong> ' .  $data['acction_reason'] . '</li>
                <li><strong>Date & time: </strong> ' .  $data['time_acction'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }
    public function lost_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        // $allocated_to =  $asset_data->acction_to;
        // $allocated_to_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $allocated_to))->row();


        $subject = 'Asset Lost - ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Lost </title>
        </head>
        <body>
            <p><strong>Reported Below Asset has been Lost: </strong></p>
            
            <ul>
                <li><strong>Asset Code: </strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name: </strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Allocated To: </strong> ' . 'Name: ' .  ', EMP ID: ' .  '</li>
                <li><strong>Date & time: </strong> ' .  $data['time_acction'] . '</li>
                <li><strong>Description: </strong> ' .  $data['description'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }
    public function broken_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        // $allocated_to =  $asset_data->acction_to;
        // $allocated_to_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $allocated_to))->row();


        $subject = 'Asset is broken - ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Broken </title>
        </head>
        <body>
            <p><strong>Reported Below Asset has been Broken/Faulty: </strong></p>
            
            <ul>
                <li><strong>Asset Code: </strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name: </strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Allocated To: </strong> ' . 'Name: ' .  ', EMP ID: ' .  '</li>
                <li><strong>Date & time: </strong> ' .  $data['time_acction'] . '</li>
                <li><strong>Description: </strong> ' .  $data['description'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }

    public function warranty_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        // $allocated_to =  $asset_data->acction_to;
        // $allocated_to_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $allocated_to))->row();


        $subject = 'Asset Repair - ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Repair </title>
        </head>
        <body>
            <p><strong>Asset Needs to be Repair / Warranty below: </strong></p>
            <ul>
                <li><strong>Asset Code: </strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name: </strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Allocated To: </strong> ' . 'Name: ' .  ', EMP ID: ' .  '</li>
                <li><strong>Repair/Warranty Cost: </strong> ' .  $data['cost'] . '</li>
                <li><strong>Repair / Warranty Time: </strong> ' .  $data['time_acction'] . '</li>
                <li><strong>Description: </strong> ' .  $data['description'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }
    public function liquidation_asset_mail($data)
    {
        $asset_data = $this->db->query('SELECT * FROM tblassets WHERE id = ' . $data['assets'])->row();

        // $allocated_to =  $asset_data->acction_to;
        // $allocated_to_emp =  $this->db->get_where('tblstaff_info', array('staffid' => $allocated_to))->row();


        $subject = 'Asset is liquidated - ' . $data['acction_code'];
        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title> Asset Liquidate </title>
        </head>
        <body>
            <p><strong>Below Asset has been Liquidated </strong></p>
            <ul>
                <li><strong>Asset Code: </strong> ' . $asset_data->assets_code . '</li>
                <li><strong>Asset Name: </strong> ' . $asset_data->assets_name . '</li>
                <li><strong>Allocated To: </strong> ' . 'Name: ' .  ', EMP ID: ' .  '</li>
                <li><strong>Date & time: </strong> ' .  $data['time_acction'] . '</li>
                <li><strong>Liquidation Amount: </strong> ' .  $data['cost'] . '</li>
                <li><strong>Description: </strong> ' .  $data['description'] . '</li>
            </ul>
        
            <p><em>Kind Regards,<br>
            IT Department | Tech2globe</em></p>
        </body>
        </html>
        ';
        $this->sendEmail($subject, $message);
    }
	public function get_staff_departments()
	{
		return $this->db->get(db_prefix() . 'departments')->result_array();
	}
	public function getDepartmentemp($deptid)
	{
		/*print_r($deptid);
		print_r($_GET);die;
		$valuesArray = array_values($deptid); 

		$dept_id = implode(", ", $valuesArray);*/
		//echo $dept_id;
		$result = $this->db->query('select tblstaff_departments.staffid,tblstaff_departments.departmentid,tbldepartments.name,tblstaff.staffid,tblstaff.firstname,
						tblstaff.lastname,tblstaff.staff_identifi
						from tblstaff_departments
						left join tblstaff on tblstaff_departments.staffid = tblstaff.staffid
						left join tbldepartments on tblstaff_departments.departmentid= tbldepartments.departmentid
						where tblstaff_departments.departmentid IN('.$deptid.') AND active=1 GROUP by tblstaff.staffid;');
		return	$result->result_array();
		//$query = $this->db->get('mytable');
	}
	
	public function getDataFilter($dept,$empid)
	{
		$result = $this->db->query("SELECT tblassets.*,tblstaff.firstname,tblstaff.lastname,tblstaff.email from `tblassets` 
		LEFT JOIN `tblassets_acction_1` ON tblassets_acction_1.assets = tblassets.id 
		LEFT JOIN `tblstaff` ON tblassets_acction_1.acction_to = tblstaff.staffid where tblassets_acction_1.acction_to=".$empid);
		return	$result->result_array();
	}
}
