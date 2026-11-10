#!/usr/bin/env python3
"""Insert approve_pending_leave_request on live Timesheets_model if missing."""
import sys

path = sys.argv[1] if len(sys.argv) > 1 else '/var/www/html/t2gworkroom/modules/timesheets/models/Timesheets_model.php'
text = open(path, encoding='utf-8', errors='replace').read()
if 'function approve_pending_leave_request' in text:
    print('already_present')
    sys.exit(0)

snippet = r'''
	/**
	 * Final-approve one pending leave application (HR / Super HR / Admin).
	 *
	 * @param int $request_id
	 * @param int $approver_id
	 * @return array{success:bool,message:string}
	 */
	public function approve_pending_leave_request($request_id, $approver_id = 0)
	{
		$request_id = (int) $request_id;
		$approver_id = (int) ($approver_id ?: get_staff_user_id());
		if ($request_id <= 0) {
			return ['success' => false, 'message' => 'Invalid request.'];
		}

		$req = $this->db->where('id', $request_id)->get(db_prefix() . 'timesheets_requisition_leave')->row();
		if (!$req) {
			return ['success' => false, 'message' => 'Request not found.'];
		}
		if ((int) $req->status !== 0) {
			return ['success' => false, 'message' => 'Not pending.'];
		}

		$applicant_id = (int) ($req->staff_id ?? 0);
		if (!timesheets_can_final_approve_leave($approver_id, $applicant_id)) {
			return ['success' => false, 'message' => 'Not allowed to approve this leave.'];
		}

		$rel_type = timesheets_requisition_leave_rel_type($req->type_of_leave ?? 0, $req->type_of_leave_text ?? '');
		$this->update_approve_request($request_id, $rel_type, 1);
		try {
			$this->notify_leave_decision_to_employee($request_id, true, '', $approver_id);
		} catch (Throwable $e) {
			log_activity('Leave bulk approve notify failed #' . $request_id . ': ' . $e->getMessage());
		}

		return ['success' => true, 'message' => 'Approved'];
	}

'''

marker = '\t/**\n\t * Email employee + HOD when regularization is finally approved'
if marker not in text:
    marker = 'public function notify_regularisation_decision'
    if marker not in text:
        print('marker_not_found', file=sys.stderr)
        sys.exit(1)
    text = text.replace(marker, snippet + '\t' + marker, 1)
else:
    text = text.replace(marker, snippet + marker, 1)

open(path, 'w', encoding='utf-8').write(text)
print('patched')
