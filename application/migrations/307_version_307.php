<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_307 extends CI_Migration
{
    public function up()
    {
        $roles = db_prefix() . 'roles';

        $exists = $this->db->where('name', 'Super HR')->count_all_results($roles);
        if ($exists) {
            return;
        }

        // Full HRMS permissions (view/create/edit/delete where applicable).
        // Non-HRMS modules intentionally omitted.
        $permissions = [
            'attendance_management' => ['view_own', 'view'],
            'leave_management' => ['view_own', 'view'],
            'route_management' => ['view_own', 'view'],
            'additional_timesheets_management' => ['view_own', 'view'],
            'table_shiftwork_management' => ['view_own', 'view'],
            'report_management' => ['view_own', 'view'],
            'table_workplace_management' => ['view_own', 'view'],
            'hrm_dashboard' => ['view'],
            'staffmanage_orgchart' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hrm_reception_staff' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hrm_hr_records' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'staffmanage_job_position' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'staffmanage_training' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hr_manage_q_a' => ['view', 'create', 'edit', 'delete'],
            'hrm_contract' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hrm_dependent_person' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hrm_procedures_for_quitting_work' => ['view_own', 'view', 'create', 'edit', 'delete'],
            'hrm_report' => ['view'],
            'hrm_setting' => ['view', 'create', 'edit', 'delete'],
            'recruitment' => ['view', 'create', 'edit', 'delete'],
            'staff' => ['view', 'create', 'edit', 'delete'],
            'pedma' => ['view', 'create', 'edit'],
            'reports' => ['view', 'view-timesheets'],
        ];

        $this->db->insert($roles, [
            'name' => 'Super HR',
            'permissions' => serialize($permissions),
        ]);
    }

    public function down()
    {
        $this->db->where('name', 'Super HR')->delete(db_prefix() . 'roles');
    }
}
