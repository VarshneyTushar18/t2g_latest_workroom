<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">Employee Swipes</h4>
            <hr class="hr-panel-heading" />
            <div class="row">
              <div class="col-md-3">
                <label>From date</label>
                <input type="date" id="swipe_from" class="form-control" value="<?= html_escape($from_date) ?>">
              </div>
              <div class="col-md-3">
                <label>To date</label>
                <input type="date" id="swipe_to" class="form-control" value="<?= html_escape($to_date) ?>">
              </div>
              <div class="col-md-3">
                <label>Employee</label>
                <input type="text" class="form-control" value="<?= html_escape($staff_name) ?>" readonly>
              </div>
              <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="button" id="swipe_filter" class="btn btn-info btn-block">Filter</button>
              </div>
            </div>
            <div class="table-responsive mtop15">
              <table class="table table-striped table-hover" id="swipes_table">
                <thead>
                  <tr>
                    <th>Employee Name</th>
                    <th>Swipe Time &amp; Date</th>
                    <th>Shift</th>
                    <th>In/Out</th>
                    <th>Received On</th>
                    <th>Door/Address</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="swipes_body">
                  <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
function loadSwipes() {
  $('#swipes_body').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
  $.getJSON(admin_url + 'biometric/fetch_swipes', {
    from: $('#swipe_from').val(),
    to: $('#swipe_to').val()
  }).done(function (res) {
    var rows = (res && res.data) ? res.data : [];
    if (!rows.length) {
      $('#swipes_body').html('<tr><td colspan="7" class="text-center">No swipes found for this date range.</td></tr>');
      return;
    }
    var html = '';
    rows.forEach(function (r) {
      var badge = r.in_out === 'IN' ? 'success' : 'warning';
      html += '<tr>' +
        '<td>' + $('<div>').text(r.employee_name).html() + '<br><small>#' + $('<div>').text(r.employee_code || '').html() + '</small></td>' +
        '<td>' + $('<div>').text(r.swipe_time + ' ' + r.swipe_date).html() + '</td>' +
        '<td>' + $('<div>').text(r.shift || '-').html() + '</td>' +
        '<td><span class="label label-' + badge + '">' + $('<div>').text(r.in_out).html() + '</span></td>' +
        '<td>' + $('<div>').text(r.received_on).html() + '</td>' +
        '<td>' + $('<div>').text(r.door).html() + '</td>' +
        '<td><span class="label label-success">' + $('<div>').text(r.status).html() + '</span></td>' +
        '</tr>';
    });
    $('#swipes_body').html(html);
  }).fail(function () {
    $('#swipes_body').html('<tr><td colspan="7" class="text-center text-danger">Could not load swipes.</td></tr>');
  });
}
$(function () {
  $('#swipe_filter').on('click', loadSwipes);
  loadSwipes();
});
</script>
</body></html>
