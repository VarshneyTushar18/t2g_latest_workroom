<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$year = (int) ($year ?? date('Y'));
$can_manage = !empty($can_manage);
$holidays = $holidays ?? [];
$by_month = [];
for ($m = 1; $m <= 12; $m++) {
    $by_month[$m] = [];
}
foreach ($holidays as $h) {
    $d = $h['display_date'] ?? $h['break_date'];
    $m = (int) date('n', strtotime($d));
    if ($m >= 1 && $m <= 12) {
        $by_month[$m][] = $h;
    }
}
$year_options = range((int) date('Y') - 2, (int) date('Y') + 3);
?>
<style>
.hc-page { max-width: 1180px; margin: 0 auto; padding-bottom: 24px; }
.hc-top {
  display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
  margin-bottom: 18px;
}
.hc-title {
  display:flex; align-items:center; gap:10px; margin:0;
  font-size:22px; font-weight:600; color:#334155; letter-spacing:-.01em;
}
.hc-title .ico {
  width:28px; height:28px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;
  background:#e0f2fe; color:#0284c7; font-size:14px;
}
.hc-year-wrap select {
  min-width: 110px; height: 36px; border:1px solid #e2e8f0; border-radius:8px;
  padding: 0 12px; background:#fff; color:#334155; font-weight:600; font-size:14px;
  box-shadow: 0 1px 2px rgba(15,23,42,.04);
}
.hc-actions { display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 14px; }
.hc-grid {
  display:grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}
@media (max-width: 1100px) { .hc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 800px) { .hc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 520px) { .hc-grid { grid-template-columns: 1fr; } }
.hc-card {
  background:#fff;
  border:1px solid #e8edf3;
  border-radius: 10px;
  min-height: 168px;
  padding: 16px 16px 12px;
  box-shadow: 0 1px 2px rgba(15,23,42,.03);
  animation: hcIn .4s ease both;
  transition: box-shadow .2s ease, transform .2s ease;
}
.hc-card:hover { box-shadow: 0 6px 16px rgba(15,23,42,.07); transform: translateY(-1px); }
.hc-card-head {
  font-size: 12px; font-weight: 600; color:#94a3b8;
  letter-spacing: .04em; text-transform: uppercase; margin-bottom: 14px;
}
.hc-empty {
  display:flex; align-items:center; justify-content:center;
  min-height: 100px; color:#cbd5e1; font-size:14px; font-weight:500;
}
.hc-item {
  display:flex; align-items:flex-start; gap:14px;
  padding: 8px 0;
}
.hc-item + .hc-item { border-top: 1px solid #f1f5f9; }
.hc-daycol { width: 36px; flex-shrink:0; text-align:left; }
.hc-daynum {
  display:block; font-size: 22px; font-weight: 700; color:#475569; line-height:1.05;
  font-variant-numeric: tabular-nums;
}
.hc-weekday {
  display:block; font-size: 12px; color:#94a3b8; margin-top:2px; font-weight:500;
}
.hc-name {
  flex:1; min-width:0; padding-top: 4px;
  font-size: 14px; font-weight: 500; color:#475569; line-height:1.35;
}
.hc-item-actions {
  display:flex; gap:4px; flex-shrink:0; opacity:0; transition: opacity .15s ease;
  padding-top: 4px;
}
.hc-card:hover .hc-item-actions { opacity:1; }
.hc-item-actions .btn { padding: 2px 7px; font-size:11px; }
@keyframes hcIn {
  from { opacity:0; transform: translateY(6px); }
  to { opacity:1; transform: none; }
}
</style>

<div id="wrapper">
  <div class="content">
    <div class="hc-page">
      <div class="hc-top">
        <h1 class="hc-title">
          <span class="ico"><i class="fa fa-calendar"></i></span>
          Holiday Calendar
        </h1>
        <div class="hc-year-wrap">
          <select id="hc_year_select" aria-label="Year">
            <?php foreach ($year_options as $y) { ?>
              <option value="<?php echo (int) $y; ?>" <?php echo ((int) $y === $year) ? 'selected' : ''; ?>><?php echo (int) $y; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <?php if ($can_manage) { ?>
      <div class="hc-actions">
        <button type="button" class="btn btn-info btn-sm" id="hc_btn_add"><i class="fa fa-plus"></i> Add Holiday</button>
        <button type="button" class="btn btn-default btn-sm" id="hc_btn_upload"><i class="fa fa-upload"></i> Upload</button>
        <a class="btn btn-default btn-sm" href="<?php echo admin_url('holiday/download_holiday_template'); ?>"><i class="fa fa-download"></i> Template</a>
      </div>
      <?php } ?>

      <div class="hc-grid">
        <?php for ($m = 1; $m <= 12; $m++) {
            $items = $by_month[$m];
            $label = strtoupper(date('M', mktime(0, 0, 0, $m, 1, $year))) . ' ' . $year;
            $delay = number_format(($m - 1) * 0.03, 2);
            ?>
          <div class="hc-card" style="animation-delay:<?php echo $delay; ?>s">
            <div class="hc-card-head"><?php echo html_escape($label); ?></div>
            <?php if (!$items) { ?>
              <div class="hc-empty">No Holidays</div>
            <?php } else {
                foreach ($items as $h) {
                    $d = $h['display_date'] ?? $h['break_date'];
                    $ts = strtotime($d);
                    ?>
              <div class="hc-item">
                <div class="hc-daycol">
                  <span class="hc-daynum"><?php echo date('d', $ts); ?></span>
                  <span class="hc-weekday"><?php echo date('D', $ts); ?></span>
                </div>
                <div class="hc-name"><?php echo html_escape($h['off_reason']); ?></div>
                <?php if ($can_manage) { ?>
                <div class="hc-item-actions">
                  <button type="button" class="btn btn-default btn-xs hc-edit"
                    data-id="<?php echo (int) $h['id']; ?>"
                    data-date="<?php echo html_escape($h['break_date']); ?>"
                    data-name="<?php echo html_escape($h['off_reason']); ?>"
                    data-type="<?php echo html_escape($h['off_type'] ?? 'holiday'); ?>"
                    data-repeat="<?php echo (int) ($h['repeat_by_year'] ?? 0); ?>">Edit</button>
                  <button type="button" class="btn btn-danger btn-xs hc-del" data-id="<?php echo (int) $h['id']; ?>">Del</button>
                </div>
                <?php } ?>
              </div>
                <?php }
            } ?>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php if ($can_manage) { ?>
<div class="modal fade" id="hc_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title" id="hc_modal_title">Add Holiday</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="hc_id" value="">
        <div class="form-group">
          <label>Date</label>
          <input type="date" class="form-control" id="hc_date" required>
        </div>
        <div class="form-group">
          <label>Holiday name</label>
          <input type="text" class="form-control" id="hc_name" placeholder="e.g. Diwali" required>
        </div>
        <div class="form-group">
          <label>Type</label>
          <select class="form-control" id="hc_type">
            <option value="holiday">Holiday</option>
            <option value="event_break">Event</option>
            <option value="unexpected_break">Unexpected</option>
          </select>
        </div>
        <div class="checkbox">
          <label><input type="checkbox" id="hc_repeat" value="1"> Repeat every year</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="hc_save">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="hc_upload_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Upload holidays</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">Columns: <code>date</code>, <code>name</code>, optional <code>type</code>, optional <code>repeat_by_year</code>.</p>
        <div class="form-group">
          <input type="file" id="hc_file" accept=".csv,.xlsx,.xls" class="form-control">
        </div>
        <div id="hc_upload_result" class="text-muted"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="hc_upload_go">Upload</button>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<?php init_tail(); ?>
<script>
(function($){
  var csrf = (typeof csrfData !== 'undefined') ? csrfData : { token_name: 'csrf_token_name', hash: '' };

  function withCsrf(payload) {
    payload = payload || {};
    if (csrf && csrf.token_name) {
      payload[csrf.token_name] = csrf.hash;
    }
    return payload;
  }

  function appendCsrf(fd) {
    if (csrf && csrf.token_name) {
      fd.append(csrf.token_name, csrf.hash);
    }
    return fd;
  }

  $('#hc_year_select').on('change', function(){
    window.location.href = admin_url + 'holiday/calendar?year=' + encodeURIComponent($(this).val());
  });

  var canManage = <?php echo $can_manage ? 'true' : 'false'; ?>;
  if (!canManage) return;

  function openForm(data) {
    data = data || {};
    $('#hc_id').val(data.id || '');
    $('#hc_date').val(data.date || '');
    $('#hc_name').val(data.name || '');
    $('#hc_type').val(data.type || 'holiday');
    $('#hc_repeat').prop('checked', String(data.repeat) === '1');
    $('#hc_modal_title').text(data.id ? 'Edit Holiday' : 'Add Holiday');
    $('#hc_modal').modal('show');
  }

  $('#hc_btn_add').on('click', function(){ openForm({}); });
  $(document).on('click', '.hc-edit', function(e){
    e.stopPropagation();
    var $el = $(this);
    openForm({
      id: $el.data('id'),
      date: $el.data('date'),
      name: $el.data('name'),
      type: $el.data('type'),
      repeat: $el.data('repeat')
    });
  });

  $('#hc_save').on('click', function(){
    var payload = withCsrf({
      id: $('#hc_id').val(),
      break_date: $('#hc_date').val(),
      leave_reason: $('#hc_name').val(),
      leave_type: $('#hc_type').val(),
      repeat_by_year: $('#hc_repeat').is(':checked') ? 1 : 0
    });
    if (!payload.break_date || !payload.leave_reason) {
      alert_float('warning', 'Date and holiday name are required.');
      return;
    }
    $.post(admin_url + 'holiday/save_holiday', payload).done(function(res){
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
      if (res && res.success) {
        alert_float('success', res.message || 'Saved');
        window.location.reload();
      } else {
        alert_float('danger', (res && res.message) || 'Save failed');
      }
    }).fail(function(xhr){
      if (xhr && xhr.status === 419) {
        alert_float('warning', 'Page expired — refreshing...');
        setTimeout(function(){ window.location.reload(); }, 600);
        return;
      }
      alert_float('danger', 'Save failed');
    });
  });

  $(document).on('click', '.hc-del', function(e){
    e.stopPropagation();
    var id = $(this).data('id');
    if (!id || !confirm('Delete this holiday?')) return;
    $.post(admin_url + 'holiday/delete_holiday/' + id, withCsrf({})).done(function(res){
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
      if (res && res.success) {
        alert_float('success', res.message || 'Deleted');
        window.location.reload();
      } else {
        alert_float('danger', (res && res.message) || 'Delete failed');
      }
    }).fail(function(xhr){
      if (xhr && xhr.status === 419) {
        alert_float('warning', 'Page expired — refreshing...');
        setTimeout(function(){ window.location.reload(); }, 600);
      }
    });
  });

  $('#hc_btn_upload').on('click', function(){
    $('#hc_file').val('');
    $('#hc_upload_result').text('');
    $('#hc_upload_modal').modal('show');
  });

  $('#hc_upload_go').on('click', function(){
    var file = $('#hc_file')[0].files[0];
    if (!file) {
      alert_float('warning', 'Choose a file first.');
      return;
    }
    var fd = new FormData();
    fd.append('holiday_file', file);
    appendCsrf(fd);
    $('#hc_upload_result').text('Uploading...');
    $.ajax({
      url: admin_url + 'holiday/upload_holidays',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false
    }).done(function(res){
      try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
      if (res && res.success) {
        var msg = res.message || 'Done';
        if (res.errors && res.errors.length) {
          msg += ' — ' + res.errors.slice(0, 3).join(' ');
        }
        $('#hc_upload_result').text(msg);
        alert_float('success', res.message || 'Upload complete');
        setTimeout(function(){ window.location.reload(); }, 800);
      } else {
        $('#hc_upload_result').text((res && res.message) || 'Upload failed');
        alert_float('danger', (res && res.message) || 'Upload failed');
      }
    }).fail(function(xhr){
      if (xhr && xhr.status === 419) {
        $('#hc_upload_result').text('Page expired — please refresh and try again.');
        alert_float('warning', 'Page expired — refreshing...');
        setTimeout(function(){ window.location.reload(); }, 700);
        return;
      }
      var msg = 'Upload failed';
      try {
        var body = typeof xhr.responseJSON !== 'undefined' ? xhr.responseJSON : JSON.parse(xhr.responseText);
        if (body && body.message) msg = body.message;
      } catch (e) {}
      $('#hc_upload_result').text(msg);
      alert_float('danger', msg);
    });
  });
})(jQuery);
</script>
</body>
</html>
