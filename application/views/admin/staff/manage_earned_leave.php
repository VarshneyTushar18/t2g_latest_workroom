<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.el-manage-hint { color:#64748b; font-size:13px; margin-bottom:16px; }
.el-manage-panel { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px; }
.el-manage-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-top:12px; }
#el_save_status { min-height:22px; font-size:13px; margin-top:10px; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
          <h4 class="tw-mt-0"><i class="fa fa-sliders"></i> Manage Earned Leave</h4>
          <a href="<?php echo admin_url('staff/leave_balance'); ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Leave Balance Report
          </a>
        </div>

        <div class="panel_s">
          <div class="panel-body">
            <p class="el-manage-hint">
              Set monthly <strong>Leaves Earned</strong> manually for employees — same flow as Manage Saturday:
              pick department → select employee(s) or <strong>Select All</strong> → choose month/year → enter days → Save.
              Default system rates by policy: Intern/WFH = 1; FTE = 1.25 (1.75 after 2 years).
              First employment month earns 0; second month credits month-1 + month-2.
              Overrides here replace the calculated amount for the selected month only.
            </p>

            <div class="el-manage-panel">
              <div class="row">
                <div class="col-md-3">
                  <?php echo render_select('departments', $departments, ['departmentid', 'name'], 'Department'); ?>
                </div>
                <div class="col-md-4">
                  <?php echo render_select('staffid[]', $staffs, ['staffid', ['firstname', 'lastname', 'staff_identifi']], 'Select Employee(s)', '', ['multiple' => true, 'data-live-search' => true, 'data-actions-box' => true], [], '', '', 'selectpicker', false); ?>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="el_month">Month</label>
                    <select id="el_month" class="form-control">
                      <?php
                      $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                      foreach ($months as $i => $name) {
                          $m = $i + 1;
                          $sel = ($m === (int) $current_month) ? ' selected' : '';
                          echo '<option value="' . $m . '"' . $sel . '>' . $name . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="el_year">Year</label>
                    <select id="el_year" class="form-control">
                      <?php for ($y = (int) $current_year; $y >= $current_year - 5; $y--) {
                          $sel = ($y === (int) $current_year) ? ' selected' : '';
                          echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
                      } ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="el_earned_days">Leaves Earned</label>
                    <input type="number" step="0.01" min="0" id="el_earned_days" class="form-control" value="1.25" placeholder="e.g. 1.25">
                  </div>
                </div>
              </div>
              <div class="el-manage-actions">
                <button type="button" class="btn btn-info" id="el_select_all_staff">Select all in department</button>
                <button type="button" class="btn btn-primary" id="el_save_btn">
                  <i class="fa fa-save"></i> Save for selected
                </button>
              </div>
              <div id="el_save_status"></div>
            </div>

            <p class="text-muted" style="font-size:12px;">
              Tip: After saving, open <a href="<?php echo admin_url('staff/leave_balance'); ?>">Leave Balance</a>,
              pick the same month, and verify the <strong>Leaves Earned</strong> column. HR can also edit values directly in that table.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function($) {
  'use strict';
  var $staffSelect = $('select[name="staffid[]"]');
  var csrf = (typeof csrfData !== 'undefined') ? csrfData : { token_name: 'csrf_token_name', hash: '' };

  function setStatus(msg, ok) {
    var cls = ok ? 'text-success' : (ok === false ? 'text-danger' : 'text-muted');
    $('#el_save_status').html(msg ? '<span class="' + cls + '">' + msg + '</span>' : '');
  }

  function loadStaffForDepartment(department) {
    $staffSelect.empty();
    if (!department) {
      $staffSelect.selectpicker('refresh');
      return;
    }
    $.post(admin_url + 'staff/get_staff_department_json', { department: department }, function(response) {
      var list = Array.isArray(response) ? response : [];
      list.forEach(function(s) {
        var label = ((s.firstname || '') + ' ' + (s.lastname || '')).trim();
        if (s.staff_identifi) {
          label += ' ' + s.staff_identifi;
        }
        $staffSelect.append($('<option>', { value: s.staffid, text: label.trim() }));
      });
      $staffSelect.selectpicker('refresh');
    }, 'json').fail(function() {
      setStatus('Could not load employees for this department.', false);
    });
  }

  $('#departments').on('change', function() {
    loadStaffForDepartment($(this).val());
  });

  if ($('#departments').val()) {
    loadStaffForDepartment($('#departments').val());
  }

  $('#el_select_all_staff').on('click', function() {
    var opts = $staffSelect.find('option').map(function() { return $(this).val(); }).get();
    $staffSelect.selectpicker('val', opts);
  });

  $('#el_save_btn').on('click', function() {
    var staffIds = $staffSelect.val();
    if (!staffIds || !staffIds.length) {
      setStatus('Select at least one employee.', false);
      return;
    }
    var payload = {
      staffids: staffIds,
      month: $('#el_month').val(),
      year: $('#el_year').val(),
      earned_days: $('#el_earned_days').val()
    };
    payload[csrf.token_name] = csrf.hash;

    var $btn = $(this).prop('disabled', true);
    setStatus('Saving...', null);
    $.post(admin_url + 'staff/save_earned_leave_bulk', payload).done(function(res) {
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { res = {}; }
      setStatus(res.message || (res.success ? 'Saved.' : 'Failed.'), !!res.success);
      if (res.success) {
        alert_float('success', res.message || 'Saved');
      }
    }).fail(function() {
      setStatus('Could not save. Try again.', false);
    }).always(function() {
      $btn.prop('disabled', false);
    });
  });
})(jQuery);
</script>
</body>
</html>
