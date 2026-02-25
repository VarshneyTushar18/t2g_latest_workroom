<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Escalation_model extends CI_Model
{
    public function save_escalation($data)
    {
        //$this->load->library('email');
        $this->load->model('staff_model');
		
        // Insert escalation
        $insertData = [
            'subject'     => $data['subject'],
            'description' => nl2br($data['description']),
			
            'project_id'  => $data['project_id'],
            'created_by'  => get_staff_user_id(),
            'created_at'  => date('Y-m-d H:i:s')
        ];
		$created_at = $insertData['created_at'];
        $success = $this->db->insert('tblescalation', $insertData);

        if (!$success) {
            log_message('error', 'Insert failed: ' . print_r($this->db->error(), true));
            return [
                'success' => false,
                'message' => 'Escalation insert failed: ' . $this->db->error()['message']
            ];
        }

        $escalation_id = $this->db->insert_id();
		// ✅ Get project name
		$this->db->select('name');
		$this->db->from('tblprojects');
		$this->db->where('id', $data['project_id']);
		$project = $this->db->get()->row();
		$project_name = $project ? $project->name : 'Unknown Project';
        // Insert assignees
        if (!empty($data['assigned_to'])) {
            foreach ($data['assigned_to'] as $staff_id) {
                $this->db->insert('tblescalation_assignees', [
                    'escalation_id' => $escalation_id,
                    'staff_id'      => $staff_id,
                    'is_follower'   => 0
                ]);
            }
        }

        // Insert followers
        if (!empty($data['followers'])) {
            foreach ($data['followers'] as $staff_id) {
                $this->db->insert('tblescalation_assignees', [
                    'escalation_id' => $escalation_id,
                    'staff_id'      => $staff_id,
                    'is_follower'   => 1
                ]);
            }
        }
	$project_url = admin_url('projects/view/' . $data['project_id'] . '?group=project_custom');

        // Email to Assignees
        if (!empty($data['assigned_to'])) {
            foreach ($data['assigned_to'] as $staff_id) {
                $staff = $this->staff_model->get($staff_id);
                if ($staff && $staff->email) {
                    $this->email->set_mailtype("html");
                    $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
                    $this->email->to($staff->email);
                    $this->email->subject("Escalation Raised on Project: {$project_name} - {$data['subject']}");
                    $this->email->message("
                        <p>Hello {$staff->firstname},</p>
                        <p>An escalation has been raised in Workroom for the project: {$project_name}</p>
						<p><strong>Escalation Details:</strong></p>
                        <p><strong>Subject:</strong> {$data['subject']}</p>
                        <p><strong>Issue:</strong><br>" . nl2br($data['description']) . "</p>
						 <p><strong>Date Raised: </strong> {$created_at} </p>
						 <p><a href='{$project_url}' target='_blank'>View Escalation</a></p>
                        <p>Regards,<br>Tech2globe</p>
                    ");
                  if (!$this->email->send()) {
							log_message('error', 'Failed to send escalation email to ' . $staff->email);
						}else{
						$this->email->send();
						}
					$this->email->clear();
                }
            }
        }

        // Email to Followers
		
        if (!empty($data['followers'])) {
            foreach ($data['followers'] as $staff_id) {
                $staff = $this->staff_model->get($staff_id);
                if ($staff && $staff->email) {
					$this->email->set_mailtype("html");
                    
                    $this->email->from('noreply_workroom@tech2globe.net', 'Tech2globe');
                    $this->email->to($staff->email);
                     $this->email->subject("You’ve Been Added as a Follower on an Escalation: {$project_name} - {$data['subject']}");
                    $this->email->message("
                        <p>Hello {$staff->firstname},</p>
                        <p>You have been added as a <strong>follower</strong> to a new escalation:</p>
						<p><strong>Escalation Summary:</strong></p>
                        <p><strong>Subject:</strong> {$data['subject']}</p>
                        <p><strong>Issue:</strong><br>" . nl2br($data['description']) . "</p>
						<p><strong>Date Raised: </strong> {$created_at} </p>
						<br>
						<p><a href='{$project_url}' target='_blank'>View Escalation</a></p>
                        <p>Regards,<br>Tech2globe</p>
                    ");
                   
                   if (!$this->email->send()) {
							log_message('error', 'Failed to send escalation email to ' . $staff->email);
						}else{
						$this->email->send();
						}
						$this->email->clear();
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Escalation created successfully.',
            'redirect_url' => admin_url('projects/view/' . $data['project_id'] . '?group=project_custom')
        ];
    }
	public function get_by_project($project_id)
		{
			// error_reporting(E_ALL);
   // ini_set('display_errors', 1);
			$this->db->select('e.*, s.firstname, s.lastname');
			$this->db->from('tblescalation e');
			$this->db->join('tblstaff s', 's.staffid = e.created_by', 'left');
			$this->db->where('e.project_id', $project_id);
			$this->db->order_by('e.created_at', 'DESC');
			$escalations = $this->db->get()->result_array();

			foreach ($escalations as &$esc) {
				// Get Assignees
				$this->db->select('staff_id');
				$this->db->from('tblescalation_assignees');
				$this->db->where(['escalation_id' => $esc['id'], 'is_follower' => 0]);
				$assignee_ids = array_column($this->db->get()->result_array(), 'staff_id');

				// Get Followers
				$this->db->select('staff_id');
				$this->db->from('tblescalation_assignees');
				$this->db->where(['escalation_id' => $esc['id'], 'is_follower' => 1]);
				$follower_ids = array_column($this->db->get()->result_array(), 'staff_id');

				// Convert staff IDs to names
				$esc['assignees'] = $this->get_staff_names($assignee_ids);
				$esc['followers'] = $this->get_staff_names($follower_ids);
			}

			return $escalations;
		}

		private function get_staff_names($ids)
		{
			if (empty($ids)) return [];
			$this->db->select("CONCAT(firstname, ' ', lastname) as name");
			$this->db->from('tblstaff');
			$this->db->where_in('staffid', $ids);
			return array_column($this->db->get()->result_array(), 'name');
		}
		public function get_by_project_paginated($project_id, $limit, $offset)
		{
			$this->db->select('e.*, s.firstname, s.lastname');
			$this->db->from('tblescalation e');
			$this->db->join('tblstaff s', 's.staffid = e.created_by', 'left');
			$this->db->where('e.project_id', $project_id);
			$this->db->order_by('e.created_at', 'DESC');
			$this->db->limit($limit, $offset);
			$escalations = $this->db->get()->result_array();

			foreach ($escalations as &$esc) {
				$esc['assignees'] = $this->get_staff_names_by_escalation($esc['id'], 0);
				$esc['followers'] = $this->get_staff_names_by_escalation($esc['id'], 1);
			}

			return $escalations;
		}

		private function get_staff_names_by_escalation($escalation_id, $is_follower = 0)
		{
			$this->db->select("CONCAT(s.firstname, ' ', s.lastname) as name");
			$this->db->from('tblescalation_assignees ea');
			$this->db->join('tblstaff s', 's.staffid = ea.staff_id');
			$this->db->where(['ea.escalation_id' => $escalation_id, 'ea.is_follower' => $is_follower]);
			return array_column($this->db->get()->result_array(), 'name');
		}

		public function count_by_project($project_id)
		{
			return $this->db->where('project_id', $project_id)->count_all_results('tblescalation');
		}
		

      

}
