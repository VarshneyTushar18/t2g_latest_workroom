<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Grant Sales role the same standard HRMS permissions as Associate (view_own attendance + leave).
 */
class Migration_Version_310 extends CI_Migration
{
    public function up()
    {
        $rolesTable = db_prefix() . 'roles';

        $associate = $this->db->where('name', 'Associate')->get($rolesTable)->row();
        $sales = $this->db->where('name', 'Sales')->get($rolesTable)->row();
        if (!$sales) {
            return;
        }

        $associatePerms = [];
        if ($associate && !empty($associate->permissions)) {
            $associatePerms = @unserialize($associate->permissions);
        }
        if (!is_array($associatePerms)) {
            $associatePerms = [];
        }

        $salesPerms = @unserialize($sales->permissions);
        if (!is_array($salesPerms)) {
            $salesPerms = [];
        }

        $hrmsFeatures = [
            'attendance_management',
            'leave_management',
        ];

        foreach ($hrmsFeatures as $feature) {
            if (!empty($associatePerms[$feature]) && is_array($associatePerms[$feature])) {
                $salesPerms[$feature] = array_values(array_unique($associatePerms[$feature]));
            } else {
                $salesPerms[$feature] = ['view_own'];
            }
        }

        $this->db->where('roleid', (int) $sales->roleid)->update($rolesTable, [
            'permissions' => serialize($salesPerms),
        ]);

        $this->load->model('staff_model');
        $staff = $this->staff_model->get('', ['role' => (int) $sales->roleid]);
        if (!is_array($staff)) {
            return;
        }

        foreach ($staff as $member) {
            if (empty($member['staffid'])) {
                continue;
            }
            $this->staff_model->update_permissions($salesPerms, (int) $member['staffid']);
        }
    }

    public function down()
    {
        // Intentionally no rollback — removing HRMS from Sales would lock them out again.
    }
}
