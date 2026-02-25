<?php defined('BASEPATH') or exit('No direct script access allowed');

class Task_esclation_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'task_esclations';
    }

    public function get_paginated($project_id, $limit, $offset)
    {
        $this->db->where('rel_type', 'project');
        $this->db->where('rel_id', $project_id);
        $this->db->limit($limit, $offset);
        return $this->db->get($this->table)->result();
    }

    public function count_all($project_id)
    {
        $this->db->where('rel_type', 'project');
        $this->db->where('rel_id', $project_id);
        return $this->db->count_all_results($this->table);
    }
}
