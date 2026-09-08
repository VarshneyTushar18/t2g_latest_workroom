<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates_model extends App_Model
{
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $template = $this->db->get(db_prefix() . 'task_templates')->row();
            if ($template) {
                $template->items = $this->get_items($id);
            }
            return $template;
        }

        $this->db->order_by('name', 'ASC');
        $templates = $this->db->get(db_prefix() . 'task_templates')->result_array();
        foreach ($templates as $key => $template) {
            $templates[$key]['task_count'] = total_rows(db_prefix() . 'task_template_items', ['template_id' => $template['id']]);
        }

        return $templates;
    }

    public function get_items($template_id)
    {
        $this->db->where('template_id', $template_id);
        $this->db->order_by('item_order', 'ASC');
        return $this->db->get(db_prefix() . 'task_template_items')->result_array();
    }

    public function add($data)
    {
        $items = isset($data['items']) ? $data['items'] : [];
        $insert = [
            'name'      => trim($data['name']),
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => get_staff_user_id(),
        ];
        $this->db->insert(db_prefix() . 'task_templates', $insert);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->save_items($insert_id, $items);
            log_activity('New Task Template Added [ID: ' . $insert_id . ', ' . $insert['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    public function edit($data)
    {
        $templateid = $data['templateid'];
        $items      = isset($data['items']) ? $data['items'] : [];

        $this->db->where('id', $templateid);
        $this->db->update(db_prefix() . 'task_templates', ['name' => trim($data['name'])]);
        $this->save_items($templateid, $items);
        log_activity('Task Template Updated [ID: ' . $templateid . ']');
        return true;
    }

    public function delete($id)
    {
        $this->db->where('template_id', $id);
        $this->db->delete(db_prefix() . 'task_template_items');
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'task_templates');
        return $this->db->affected_rows() > 0;
    }

    public function create_tasks_from_templates($template_ids, $options = [])
    {
        if (!is_array($template_ids)) {
            $template_ids = [$template_ids];
        }

        $created = 0;
        foreach (array_filter(array_map('intval', $template_ids)) as $template_id) {
            $created += $this->create_tasks_from_template($template_id, $options);
        }

        return $created;
    }

    public function create_tasks_from_template($template_id, $options = [])
    {
        $items = $this->get_items($template_id);
        if (count($items) === 0) {
            return 0;
        }

        $this->load->model('tasks_model');
        $created = 0;
        foreach ($items as $item) {
            $task_data = [
                'name'        => $item['name'],
                'qty'         => $item['qty'] !== null ? $item['qty'] : 0,
                'hourly_rate' => $item['hourly_rate'] !== null ? $item['hourly_rate'] : 0,
                'priority'    => $item['priority'],
                'task_deptid' => $item['task_deptid'],
                'description' => $item['description'],
                'rel_type'    => isset($options['rel_type']) ? $options['rel_type'] : '',
                'rel_id'      => isset($options['rel_id']) ? $options['rel_id'] : '',
                'startdate'   => isset($options['startdate']) ? $options['startdate'] : _d(date('Y-m-d')),
                'assignees'   => $this->resolve_template_item_assignees($item, $options),
            ];

            if ($this->tasks_model->add($task_data)) {
                $created++;
            }
        }

        return $created;
    }

    private function resolve_template_item_assignees($item, $options = [])
    {
        if (!empty($item['task_deptid'])) {
            $department_assignees = $this->get_department_staff_ids($item['task_deptid']);
            if (count($department_assignees) > 0) {
                return $department_assignees;
            }
        }

        if (!empty($options['rel_type']) && $options['rel_type'] === 'project' && !empty($options['rel_id'])) {
            $project_members = $this->get_project_member_ids($options['rel_id']);
            if (count($project_members) > 0) {
                return $project_members;
            }
        }

        if (isset($options['assignees']) && is_array($options['assignees']) && count($options['assignees']) > 0) {
            return $options['assignees'];
        }

        return [get_staff_user_id()];
    }

    private function get_project_member_ids($project_id)
    {
        $this->db->select('staff_id');
        $this->db->from(db_prefix() . 'project_members');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'project_members.staff_id');
        $this->db->where(db_prefix() . 'project_members.project_id', $project_id);
        $this->db->where(db_prefix() . 'staff.active', 1);
        $rows = $this->db->get()->result_array();
        return array_values(array_unique(array_column($rows, 'staff_id')));
    }

    private function get_department_staff_ids($department_id)
    {
        $this->db->select(db_prefix() . 'staff_departments.staffid');
        $this->db->from(db_prefix() . 'staff_departments');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'staff_departments.staffid');
        $this->db->where(db_prefix() . 'staff_departments.departmentid', $department_id);
        $this->db->where(db_prefix() . 'staff.active', 1);
        $rows = $this->db->get()->result_array();
        return array_values(array_unique(array_column($rows, 'staffid')));
    }

    public function validate_items($items)
    {
        if (!is_array($items) || count($items) === 0) {
            return _l('task_template_add_at_least_one_task');
        }

        $valid = 0;
        foreach ($items as $item) {
            if (empty($item['name'])) {
                continue;
            }
            if (!isset($item['hourly_rate']) || $item['hourly_rate'] === '') {
                return _l('task_hourly_rate_required');
            }
            if (empty($item['task_deptid'])) {
                return _l('task_template_department_help');
            }
            $valid++;
        }
        if ($valid === 0) {
            return _l('task_template_add_at_least_one_task');
        }

        return true;
    }

    public function add_item($template_id, $item)
    {
        if (empty($item['name'])) {
            return false;
        }

        $this->db->select_max('item_order');
        $this->db->where('template_id', $template_id);
        $max = $this->db->get(db_prefix() . 'task_template_items')->row();
        $order = ($max && $max->item_order) ? ((int) $max->item_order + 1) : 1;

        $this->db->insert(db_prefix() . 'task_template_items', [
            'template_id' => $template_id,
            'name' => trim($item['name']),
            'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : null,
            'hourly_rate' => isset($item['hourly_rate']) && $item['hourly_rate'] !== '' ? $item['hourly_rate'] : null,
            'priority' => isset($item['priority']) && $item['priority'] !== '' ? $item['priority'] : null,
            'task_deptid' => isset($item['task_deptid']) && $item['task_deptid'] !== '' ? $item['task_deptid'] : null,
            'description' => isset($item['description']) ? $item['description'] : null,
            'item_order' => $order,
        ]);

        return $this->db->insert_id() ? true : false;
    }

    private function save_items($template_id, $items)
    {
        $this->db->where('template_id', $template_id);
        $this->db->delete(db_prefix() . 'task_template_items');

        if (!is_array($items)) {
            return;
        }

        $order = 1;
        foreach ($items as $item) {
            if (empty($item['name'])) {
                continue;
            }

            $this->db->insert(db_prefix() . 'task_template_items', [
                'template_id' => $template_id,
                'name' => trim($item['name']),
                'qty' => isset($item['qty']) && $item['qty'] !== '' ? $item['qty'] : null,
                'hourly_rate' => isset($item['hourly_rate']) && $item['hourly_rate'] !== '' ? $item['hourly_rate'] : null,
                'priority' => isset($item['priority']) && $item['priority'] !== '' ? $item['priority'] : null,
                'task_deptid' => isset($item['task_deptid']) && $item['task_deptid'] !== '' ? $item['task_deptid'] : null,
                'description' => isset($item['description']) ? $item['description'] : null,
                'item_order' => $order,
            ]);
            $order++;
        }
    }
}

