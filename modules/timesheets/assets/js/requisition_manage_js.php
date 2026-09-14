<script>
  var addnewkpi;
  var table_registration_leave = $('table.table-table_registration_leave');
  var table_additional_timesheets  = $('table.table-table_additional_timesheets');
  var rest_time = 0;
  var time = 0;
  var hour_working;
  var number_day_off =$('input[name="number_day_off"]').val();
 

  (function(){
    "use strict";

    $('.fc-datetimepicker').datetimepicker();

    var requisitionServerParams = {
      "status_filter": "[name='status_filter[]']",
      "rel_type_filter": "[name='rel_type_filter[]']",
      "chose": "[name='chose']",
      "department_filter": "[name='department_filter[]']",
    };

    var table_contract = $('.table-table_contract');
    initDataTable(table_registration_leave, admin_url+'timesheets/table_registration_leave', [1], [1], requisitionServerParams, [2, 'desc']);

     //hide first column
     var hidden_columns = [0];
     $(table_registration_leave).DataTable().columns(hidden_columns).visible(false, false);
     $.each(requisitionServerParams, function() {
      $('#status_filter').on('change', function() {
        table_registration_leave.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#rel_type_filter').on('change', function() {
        table_registration_leave.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#chose').on('change', function() {
        table_registration_leave.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#department_filter').on('change', function() {
        table_registration_leave.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });
    });

     var addtimesheetServerParams = {
      "status_filter_ats": "[name='status_filter_ats[]']",
      "rel_type_filter_ats": "[name='rel_type_filter_ats[]']",
      "chose_ats": "[name='chose_ats']",
      "department_ats": "[name='department_ats[]']",
    };

    initDataTable(table_additional_timesheets,admin_url + 'timesheets/table_additional_timesheets', [0], [0],addtimesheetServerParams, [3, 'desc']); 
    $.each(addtimesheetServerParams, function() {
      $('#status_filter_ats').on('change', function() {
        table_additional_timesheets.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#rel_type_filter_ats').on('change', function() {
        table_additional_timesheets.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#chose_ats').on('change', function() {
        table_additional_timesheets.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });

      $('#department_ats').on('change', function() {
        table_additional_timesheets.DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
      });
    });


    $('select[name="type_of_leave"]').on('change', function() {
      get_remain_day_off();
      enforce_earned_vs_lop();
      update_leave_subject();
      // Immediate recalculation from current Applying For days (don't wait for AJAX).
      var days = parseFloat($('#number_of_leaving_day').val() || 0);
      update_remaining_balance_display(days);
    });

    $('select[name="staff_id"]').on('change', function() {
      get_remain_day_off();
      loadLeaveBalanceCards();
      loadLeaveCcStaff();
    });

    loadLeaveBalanceCards();
    loadLeaveCcStaff();
    loadLeaveFormStaffPicker();

    $(document).on('click', '.leave-card-view-details', function(e) {
      e.preventDefault();
      var slug = $(this).data('slug');
      var label = $(this).data('label') || slug;
      var staffId = $('#leave_balance_cards_wrap').data('staff-id') || $('select[name="staff_id"]').val() || $('input[name="staff_id"]').val();
      var year = $('#leave_balance_cards_wrap').data('year') || new Date().getFullYear();
      var month = parseInt($('#leave_balance_cards_wrap').data('month'), 10) || 0;
      var title = label + ' — ' + year;
      if (month > 1) {
        var monthNamesShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        title = label + ' — Jan – ' + monthNamesShort[month - 1] + ' ' + year;
      } else if (month === 1) {
        title = label + ' — January ' + year;
      }
      $('#leave_type_details_title').text(title);
      $('#leave_type_details_body').html('<p class="text-muted">Loading...</p>');
      $('#leave_type_details_modal').modal('show');
      var params = { year: year };
      if (month > 0) { params.month = month; }
      $.get(admin_url + 'timesheets/leave_type_details/' + staffId + '/' + encodeURIComponent(slug), params).done(function(html) {
        $('#leave_type_details_body').html(html);
      }).fail(function() {
        $('#leave_type_details_body').html('<p class="text-danger">Could not load leave details.</p>');
      });
    });

    $('input[name="start_time"], input[name="end_time"]').on('change blur', function() {
      get_remain_day_off();
      update_applying_for();
    });

    $('select[name="start_session"], select[name="end_session"]').on('change', function() {
      update_applying_for();
    });

    $('#cc_add_link').on('click', function() {
      $('#cc_picker_wrap').removeClass('hide');
      $(this).addClass('hide');
      if ($('#followers_id').length) {
        $('#followers_id').selectpicker('refresh');
      }
    });

   

    appValidateForm($('#requisition-form'), {
      type_of_leave: 'required',
      start_time:  'required',
      number_of_leaving_day:  'required',
      end_time: 'required'
    });

    $("body").on('change', '#rel_type', function() {
      var rel_type = $('select[name="rel_type"]').val();
      if(rel_type == '1'){
        $('#leave_handover_recipients').removeClass('hide');
      }
      else{
        $('#leave_handover_recipients').addClass('hide');
      }
    });

    <?php if(isset($additional_timesheets_id)){ ?>
      view_additional_timesheets(<?php echo html_entity_decode($additional_timesheets_id); ?>);
    <?php } ?>

    addnewkpi = $('.new-kpi-al').children().length;

    $("body").on('click', '.new_kpi', function() {
    //get position row
    var idrow = $(this).parents('.new-kpi-al').find('.get_id_row').attr("value");
    if ($(this).hasClass('disabled')) { return false; }

    var newkpi = $(this).parents('.new-kpi-al').find('#new_kpi').eq(0).clone().appendTo($(this).parents('.new-kpi-al'));

    newkpi.find('button[data-toggle="dropdown"]').remove();


    newkpi.find('label[for="used_to[0]"]').remove();
    newkpi.find('input[id="used_to[0]"]').attr('name', 'used_to[' + addnewkpi + ']').val('');
    newkpi.find('input[id="used_to[0]"]').attr('id', 'used_to[' + addnewkpi + ']').val('');


    newkpi.find('input[id="amoun_of_money[0]"]').attr('name', 'amoun_of_money[' + addnewkpi + ']').val('');
    newkpi.find('input[id="amoun_of_money[0]"]').attr('id', 'amoun_of_money[' + addnewkpi + ']').val('');
    newkpi.find('label[for="amoun_of_money[0]"]').remove();

    newkpi.find('div[name="button_add_kpi"]').removeAttr("style");

    newkpi.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
    newkpi.find('button[name="add"]').removeClass('new_kpi').addClass('remove_kpi').removeClass('btn-success').addClass('btn-danger');

    newkpi.find('select').selectpicker('val', '');
    addnewkpi++;

    $("input[data-type='currency']").on({
      keyup: function() {        
        formatCurrency($(this));
      },
      blur: function() { 
        formatCurrency($(this), "blur");
      }
    });
  });

    $("body").on('click', '.remove_kpi', function() {
      $(this).parents('#new_kpi').remove();
    });

    $("input[data-type='currency']").on({
      keyup: function() {        
        formatCurrency($(this));
      },
      blur: function() { 
        formatCurrency($(this), "blur");
      }
    });


    $("#additional_day").change(function(){
      $.post(admin_url + 'timesheets/get_time_working',{date:$("#additional_day").val()}).done(function(response){
       response = JSON.parse(response);
       rest_time = parseInt(response.rest_time);
       hour_working = parseFloat(response.hour_working);
     });
    });


    $("#time_in").change(function(){
      var time_in = $("#time_in").val();
      var time_out = $("#time_out").val();
      if(time_out != '' && time_in != ''){

        if($('#timekeeping_type').val() == 'W'){
          if(timeToSeconds(time_in+':00') >= timeToSeconds('12:00:00') && timeToSeconds(time_in+':00') <= timeToSeconds('13:00:00')){
            time = (timeToSeconds(time_out+':00') - timeToSeconds('13:00:00')) / 3600;
          }else if(timeToSeconds(time_in+':00') >= timeToSeconds('13:00:00')){
            time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00')) / 3600;
          }else{
            time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00') - rest_time) / 3600;
          }
        }else{
          time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00')) / 3600;
        }
        if(time > hour_working){
          $('#timekeeping_value').val(hour_working);
        }else{
          if(time < 0){
            $('#timekeeping_value').val(0);
          }else{
            $('#timekeeping_value').val(time.toFixed(2));
          }
        }
      }
    });



    $("#time_out").change(function(){
      var time_in = $("#time_in").val();
      var time_out = $("#time_out").val();
      if(time_out != '' && time_in != ''){
        if($('#timekeeping_type').val() == 'W'){      
          if(timeToSeconds(time_in+':00') >= timeToSeconds('12:00:00') && timeToSeconds(time_in+':00') <= timeToSeconds('13:00:00')){
           time = (timeToSeconds(time_out+':00') - timeToSeconds('13:00:00')) / 3600;
         }else if(timeToSeconds(time_in+':00') >= timeToSeconds('13:00:00') || timeToSeconds(time_out+':00') <= timeToSeconds('12:00:00')){
           time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00')) / 3600;
         }else{
           time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00') - rest_time) / 3600;
         }
       }else{
         time = (timeToSeconds(time_out+':00') - timeToSeconds(time_in+':00')) / 3600;
       }
       if(time > hour_working){
        $('#timekeeping_value').val(hour_working);
      }else{
        if(time < 0){
          $('#timekeeping_value').val(0);
        }else{
          $('#timekeeping_value').val(time.toFixed(2));
        }
      }
    }
  });


    function get_day_from_date(){
      var data = {};
      data.start_time = $("#start_time").val();
      data.end_time = $("#end_time").val();
      data.number_of_leaving_day = $("#number_of_leaving_day").val();
      data.staffid = $('input[name="staff_id"]').val() || $('select[name="staffid"]').val();
      data.start_session = $('#start_session').val() || 1;
      data.end_session = $('#end_session').val() || 2;

      $.post(admin_url + 'timesheets/calculate_number_days_off',data).done(function(response){
       try { response = typeof response === 'string' ? JSON.parse(response) : response; } catch(e) {}
       var days = (response && typeof response === 'object' && response.days !== undefined) ? response.days : response;
       $('input[name="number_of_leaving_day"]').val(days);
       $('#number_days_off label').text('<?php echo _l('Number_of_leaving_day'); ?>: '+days);
       if(response && response.message){
         $('#sandwich_leave_notice').text(response.message).show();
       } else {
         $('#sandwich_leave_notice').hide().text('');
       }
       update_remaining_balance_display(days);
       var number_day_off = $('input[name="number_day_off"]').val();
       var value = $('select[name="type_of_leave"]').val();
       if(value == 8){
        if(parseFloat(days) > parseFloat(number_day_off)){
          $('button[type="submit"]').attr('disabled', 'true');
        }else{
          $('button[type="submit"]').removeAttr('disabled');
        }
      }
    });
    }

    $("#timekeeping_type").change(function(){
      var value = $("#timekeeping_type").val();
      if(value == 'OT'){
        $("#overtime_setting").removeClass('hide');
      }else{
        $("#overtime_setting").addClass('hide');
      }
    });

    var data_send_mail = {};
    <?php if(isset($send_mail_approve)){ ?>
      data_send_mail = <?php echo json_encode($send_mail_approve); ?>;
      $.post(admin_url+'timesheets/send_mail', data_send_mail).done(function(response){
      });
    <?php } ?>

    $('select[name="staff_id"]').change(function(){
      get_remain_day_off();
    });

    $('.add_new_type_of_leave').click(function(){
      $('#add_new_type_of_leave').modal('show');
      $('#requisition_m').modal('hide');

    });

    $('.add_type_of_leave').on('click', function(){
      var val = $('input[name="type_name"]').val();
      var symbol = $('input[name="symbol"]').val();
      if(val.trim() && symbol.trim()){
        var list_exist_symbol = new Array("AL", "W", "U", "HO", "E", "L", "B", "SI", "M", "ME", "NS", "P");
        let i, duplicate = 0;
        for(i = 0; i<list_exist_symbol.length; i++){
          if(list_exist_symbol[i] == symbol){
            duplicate = 1;
          }
        }
        if(duplicate != 0){
          alert_float('warning', '<?php echo _l('ts_this_character_already_exists'); ?>');
          return false; 
        }

        $('#add_new_type_of_leave').modal('hide');
      }
    });
    appValidateForm($('#add_type_of_leave-form'), {
      type_name: 'required',
      symbol:  'required'
    });
    $("#requisition-form").submit(function(e) {
      "use strict";
      enforce_earned_vs_lop();
      var leaveType = $('select[name="type_of_leave"]').val();
      var number_of_leaving_day = parseFloat($('#number_of_leaving_day').val() || 0);
      var elBal = parseFloat(window._earnedLeaveBalance);
      if (isNaN(elBal)) {
        elBal = parseFloat(($('#leave_balance').val() || '0').toString().replace(/,/g, ''));
      }
      if (isNaN(elBal)) { elBal = 0; }

      if (!leaveType) {
        alert_float('warning', 'Please select a leave type.');
        return false;
      }
      if (leaveType === 'earned-leave' && (elBal <= 0 || number_of_leaving_day > (elBal + 0.001))) {
        alert_float('warning', 'Not enough earned leave for this request. Choose Loss of Pay or reduce days.');
        return false;
      }
      if(parseFloat(number_of_leaving_day) <= 0){
        alert_float('warning', '<?php echo _l('the_minimum_number_of_days_off_must_be_0.5'); ?>');
        return false;
      }
    if($("#requisition-form").valid()){
      $('.btn-submit').text('Processing ...');
      $('.btn-submit').attr('disabled', true);
    }
  });

    $("#edit_timesheets-form").submit(function(e) {
      "use strict";
      if($("#edit_timesheets-form").valid()){
        $('.btn-additional-timesheets').text('Processing ...');   
        $('.btn-additional-timesheets').attr('disabled', true); 
      }
    });
    appValidateForm($('#edit_timesheets-form'), {
      additional_day: 'required'
    });

    $('input[name="start_time_s"]').change(function(){
      start_time_check(this,'input[name="end_time_s"]');
    });

    $('input[name="end_time_s"]').change(function(){
      end_time_check('input[name="start_time_s"]', this);
    });

    $('input[name="start_time"]').change(function(){
      var res = start_time_check(this,'input[name="end_time"]');
      if(res){ get_day_from_date(); }
    });
    
    $('input[name="end_time"]').change(function(){
      var res = end_time_check('input[name="start_time"]', this);
      if(res){ get_day_from_date(); }
    });
  })(jQuery);

  function parse_leave_date(val){
    if(!val){ return null; }
    // Use Perfex global unformat_date (respects Setup → Settings → Date Format, e.g. m/d/Y).
    if(typeof unformat_date === 'function'){
      var iso = unformat_date(val);
      if(iso){
        var parts = iso.split('-');
        if(parts.length === 3){
          var y = parseInt(parts[0], 10), mo = parseInt(parts[1], 10) - 1, d = parseInt(parts[2], 10);
          if(!isNaN(y) && !isNaN(mo) && !isNaN(d)){
            return new Date(y, mo, d);
          }
        }
      }
    }
    // Fallback: ISO Y-m-d
    var m = val.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
    if(m){
      return new Date(parseInt(m[1],10), parseInt(m[2],10)-1, parseInt(m[3],10));
    }
    var d = new Date(val);
    return isNaN(d.getTime()) ? null : d;
  }

  function format_leave_number(value) {
    var num = parseFloat(value);
    if (isNaN(num)) { return '0'; }
    if (Math.abs(num - Math.round(num)) < 0.001) {
      return String(Math.round(num));
    }
    return String(Math.round(num * 100) / 100);
  }

  // Latest earned-leave balance from server (used for EL vs LOP rules).
  window._earnedLeaveBalance = null;

  function enforce_earned_vs_lop() {
    "use strict";
    var $select = $('select[name="type_of_leave"]');
    if (!$select.length) { return; }

    var elBal = parseFloat(window._earnedLeaveBalance);
    if (isNaN(elBal)) {
      elBal = parseFloat(($('#leave_balance').val() || '0').toString().replace(/,/g, ''));
    }
    if (isNaN(elBal)) { elBal = 0; }

    var days = parseFloat($('#number_of_leaving_day').val() || 0);
    if (isNaN(days) || days < 0) { days = 0; }

    var $elOpt = $select.find('option[value="earned-leave"]');
    var current = $select.val() || '';
    var notice = '';

    // Never auto-prefill leave type. User must choose; we only advise / enable options.
    if (elBal <= 0) {
      if ($elOpt.length) {
        $elOpt.prop('disabled', true);
      }
      if (current === 'earned-leave') {
        // User picked EL with no balance — clear so they choose LOP deliberately.
        $select.val('');
        current = '';
        notice = 'No earned leave balance. Choose Loss of Pay' +
          (days > 0 ? (' for ' + format_leave_number(days) + (days === 1 ? ' Day' : ' Days')) : '') + '.';
      } else if (current === '') {
        notice = 'Select leave type. Earned Leave unavailable (balance 0) — use Loss of Pay.';
      }
    } else {
      if ($elOpt.length) {
        $elOpt.prop('disabled', false);
      }
      if (current === 'earned-leave' && days > (elBal + 0.001)) {
        notice = 'Earned leave available: ' + format_leave_number(elBal) +
          ' day(s), but request is ' + format_leave_number(days) +
          ' day(s). Reduce days or choose Loss of Pay.';
      } else if (current === '') {
        notice = 'Select leave type and dates to see balance after apply.';
      }
    }

    if ($select.hasClass('selectpicker')) {
      $select.selectpicker('refresh');
    }

    update_remaining_balance_display(days, notice);
    update_leave_subject();
  }

  function update_remaining_balance_display(applyingDays, forcedNotice) {
    "use strict";
    var balanceRaw = (window._earnedLeaveBalance !== null && window._earnedLeaveBalance !== undefined)
      ? String(window._earnedLeaveBalance)
      : (($('#leave_balance').val() || $('#leave_balance_value').text() || '0').toString());
    balanceRaw = balanceRaw.replace(/,/g, '');
    var balance = parseFloat(balanceRaw);
    var days = parseFloat(applyingDays);
    var leaveType = $('select[name="type_of_leave"]').val() || '';

    if (isNaN(days)) {
      days = parseFloat($('#number_of_leaving_day').val() || 0);
    }
    if (isNaN(balance)) {
      $('#remaining_balance_value').text('-').removeClass('is-negative');
      $('#loss_of_pay_notice').hide().text('');
      return;
    }
    if (isNaN(days) || days < 0) {
      days = 0;
    }

    var remaining = balance;
    if (leaveType === 'earned-leave') {
      remaining = Math.round((balance - days) * 100) / 100;
    } else if (!leaveType) {
      // Don't show a fake "unchanged" balance — wait until type is chosen.
      $('#remaining_balance_value').text('—').removeClass('is-negative');
      if (forcedNotice) {
        $('#loss_of_pay_notice').text(forcedNotice).show();
      } else {
        $('#loss_of_pay_notice').text('Select leave type to calculate balance after apply.').show();
      }
      return;
    } else if (leaveType === 'loss-of-pay') {
      remaining = balance; // LOP does not deduct earned leave
    }

    // No dates yet → don't pretend days were applied
    if (!days || days <= 0) {
      $('#remaining_balance_value').text(leaveType === 'earned-leave' || leaveType === 'loss-of-pay'
        ? format_leave_number(balance)
        : '—').removeClass('is-negative');
      if (forcedNotice) {
        $('#loss_of_pay_notice').text(forcedNotice).show();
      } else if (leaveType === 'earned-leave' || leaveType === 'loss-of-pay') {
        $('#loss_of_pay_notice').text('Select From/To dates to calculate days and balance.').show();
      }
      return;
    }

    var label = format_leave_number(remaining);
    $('#remaining_balance_value').text(label);

    if (forcedNotice) {
      $('#remaining_balance_value').toggleClass('is-negative', remaining < 0 || leaveType === 'loss-of-pay');
      $('#loss_of_pay_notice').text(forcedNotice).show();
      return;
    }

    if (leaveType === 'loss-of-pay') {
      $('#remaining_balance_value').removeClass('is-negative');
      $('#loss_of_pay_notice')
        .text('Loss of Pay: ' + format_leave_number(days) + (days === 1 ? ' Day' : ' Days') + ' (earned leave balance unchanged)')
        .show();
      return;
    }

    if (remaining < 0) {
      $('#remaining_balance_value').addClass('is-negative');
      $('#loss_of_pay_notice')
        .text('Insufficient earned leave for ' + format_leave_number(days) + (days === 1 ? ' Day' : ' Days') + '. Reduce days or choose Loss of Pay.')
        .show();
    } else {
      $('#remaining_balance_value').removeClass('is-negative');
      $('#loss_of_pay_notice').hide().text('');
    }
  }

  function update_applying_for(){
    "use strict";
    var startVal = $('input[name="start_time"]').val();
    var endVal = $('input[name="end_time"]').val();
    var start_session = parseInt($('#start_session').val() || 1, 10);
    var end_session = parseInt($('#end_session').val() || 2, 10);
    var staffid = $('input[name="staff_id"]').val() || $('select[name="staffid"]').val() || '';
    var csrf = (typeof csrfData !== 'undefined') ? csrfData : { token_name: 'csrf_token_name', hash: '' };

    if(!startVal || !endVal){
      $('#number_of_leaving_day').val(0);
      $('#applying_for_value').text('—');
      $('#sandwich_leave_notice').hide().text('');
      enforce_earned_vs_lop();
      update_leave_subject();
      return;
    }

    var payload = {
      start_time: startVal,
      end_time: endVal,
      start_session: start_session,
      end_session: end_session,
      staffid: staffid
    };
    if (csrf && csrf.token_name) { payload[csrf.token_name] = csrf.hash; }

    // Authoritative sandwich leave calculation from server.
    $.post(admin_url + 'timesheets/calculate_number_days_off', payload).done(function(response){
      try { response = typeof response === 'string' ? JSON.parse(response) : response; } catch(e) {}
      var days = 0;
      if(response && typeof response === 'object' && response.days !== undefined){
        days = parseFloat(response.days) || 0;
        if(response.message){
          $('#sandwich_leave_notice').text(response.message).show();
        } else {
          $('#sandwich_leave_notice').hide().text('');
        }
      } else {
        days = parseFloat(response) || 0;
        $('#sandwich_leave_notice').hide().text('');
      }
      $('#number_of_leaving_day').val(days);
      var label = days + (Number(days) === 1 ? ' Day' : ' Days');
      if(Number(days) === 0.5){ label = '0.5 Day'; }
      $('#applying_for_value').text(label);
      enforce_earned_vs_lop();
      update_leave_subject();
    });
  }

  function update_leave_subject(){
    var type_text = $('#rel_type option:selected').text() || 'Leave';
    if(!$('#rel_type').val()){ type_text = 'Leave'; }
    var start = $('input[name="start_time"]').val() || '';
    var end = $('input[name="end_time"]').val() || '';
    var subject = type_text;
    if(start){
      subject += ' (' + start + (end && end !== start ? ' - ' + end : '') + ')';
    }
    $('#subject').val(subject);
  }

  function formatLeaveBalanceNumber(value) {
    var num = parseFloat(value);
    if (isNaN(num)) { return '0'; }
    if (Math.abs(num - Math.round(num)) < 0.001) {
      return String(Math.round(num));
    }
    return String(Math.round(num * 100) / 100);
  }

  function renderLeaveBalanceCards(cards) {
    var html = '';
    if (!cards || !cards.length) {
      html = '<div class="col-md-12"><p class="text-muted">No leave balance data available.</p></div>';
      $('#leave_balance_cards_grid').html(html);
      return;
    }
    cards.forEach(function(card) {
      var balanceClass = parseFloat(card.balance) < 0 ? ' negative' : '';
      var granted = formatLeaveBalanceNumber(card.granted);
      var balance = formatLeaveBalanceNumber(card.balance);
      var consumed = formatLeaveBalanceNumber(card.consumed);
      var showFoot = parseFloat(card.granted) > 0 || parseFloat(card.consumed) > 0;
      html += '<div class="leave-balance-card" data-slug="' + card.slug + '">';
      html += '<div class="leave-balance-card-head">';
      html += '<div class="leave-balance-card-title">' + card.label + '</div>';
      html += '<div class="leave-balance-card-granted">Had: ' + granted;
      if (card.slug === 'earned-leave' && card.monthly_earn !== undefined) {
        html += ' <span style="font-weight:400;color:#9ca3af;">(+' + formatLeaveBalanceNumber(card.monthly_earn) + ' earn)</span>';
      }
      html += '</div>';
      html += '</div>';
      html += '<div class="leave-balance-card-body">';
      html += '<div class="leave-balance-card-value' + balanceClass + '">' + balance + '</div>';
      html += '<div class="leave-balance-card-label">Balance</div>';
      html += '<a class="leave-balance-card-link leave-card-view-details" data-slug="' + card.slug + '" data-label="' + card.label + '">View Details</a>';
      html += '</div>';
      if (showFoot) {
        html += '<div class="leave-balance-card-foot">';
        html += '<div class="leave-balance-card-consumed">' + consumed + ' of ' + granted + ' taken → remain ' + balance + '</div>';
        html += '<div class="leave-balance-card-progress"><span style="width:' + (card.progress || 0) + '%;"></span></div>';
        html += '</div>';
      }
      html += '</div>';
    });
    $('#leave_balance_cards_grid').html(html);
  }

  function loadLeaveBalanceCards() {
    var staffId = $('select[name="staff_id"]').val() || $('input[name="staff_id"]').val() || $('#leave_balance_cards_wrap').data('staff-id');
    if (!staffId || !$('#leave_balance_cards_wrap').length) {
      return;
    }
    var year = $('#leave_balance_cards_wrap').data('year') || new Date().getFullYear();
    var month = parseInt($('#leave_balance_cards_wrap').data('month'), 10) || (new Date().getMonth() + 1);
    $('#leave_balance_cards_wrap').data('staff-id', staffId);
    $('#leave_balance_cards_wrap').data('month', month);
    $.get(admin_url + 'timesheets/get_leave_balance_cards/' + staffId, { year: year, month: month }).done(function(response) {
      try { response = typeof response === 'string' ? JSON.parse(response) : response; } catch (e) { response = {}; }
      renderLeaveBalanceCards(response.cards || []);
    });
  }

  function loadLeaveCcStaff() {
    var $sel = $('#followers_id');
    if (!$sel.length) {
      return;
    }
    var staffId = $('select[name="staff_id"]').val() || $('input[name="staff_id"]').val();
    if (!staffId) {
      return;
    }
    $.get(admin_url + 'timesheets/get_leave_cc_staff/' + staffId).done(function(response) {
      try { response = typeof response === 'string' ? JSON.parse(response) : response; } catch (e) { response = {}; }
      var staff = response.staff || [];
      var prev = $sel.val();
      $sel.empty().append('<option value=""></option>');
      for (var i = 0; i < staff.length; i++) {
        var s = staff[i];
        var id = String(s.staffid);
        var name = (s.firstname || '') + ' ' + (s.lastname || '');
        $sel.append($('<option></option>').attr('value', id).text($.trim(name)));
      }
      if (prev && $sel.find('option[value="' + prev + '"]').length) {
        $sel.val(prev);
      } else {
        $sel.val('');
      }
      if ($sel.hasClass('selectpicker') || $sel.data('selectpicker')) {
        $sel.selectpicker('refresh');
      }
    });
  }

  function loadLeaveFormStaffPicker() {
    var $sel = $('select[name="staff_id"]');
    if (!$sel.length) {
      return;
    }
    var cur = String($sel.val() || '');
    $.getJSON(admin_url + 'timesheets/get_viewable_staff_json').done(function(res) {
      var staff = res.staff || [];
      if (!staff.length) {
        return;
      }
      $sel.empty();
      for (var i = 0; i < staff.length; i++) {
        var s = staff[i];
        var id = String(s.staffid);
        var name = $.trim((s.firstname || '') + ' ' + (s.lastname || ''));
        $sel.append($('<option></option>').attr('value', id).text(name));
      }
      if (cur && $sel.find('option[value="' + cur + '"]').length) {
        $sel.val(cur);
      }
      if ($sel.hasClass('selectpicker') || $sel.data('selectpicker')) {
        $sel.selectpicker('refresh');
      }
    });
  }

  function get_remain_day_off(){
    "use strict";
    var staff_id = $('select[name="staff_id"]').val() || $('input[name="staff_id"]').val();
    var type_of_leave = $('select[name="type_of_leave"]').val() || '';
    var start_time = $('input[name="start_time"]').val() || '';
    update_applying_for();
    if(!staff_id){
      $('#leave_balance_value').text('-');
      return;
    }
    $('#requisition-form .btn-submit').attr('disabled', true);
    $('input[name="userid"]').val(staff_id);
    var payload = { start_time: start_time };
    if (typeof csrfData !== 'undefined') {
      payload[csrfData.token_name] = csrfData.hash;
    }
    $.post(admin_url+'timesheets/get_remain_day_of/'+staff_id+'/'+encodeURIComponent(type_of_leave || 'earned-leave'), payload).done(function(response){
      try { response = typeof response === 'string' ? JSON.parse(response) : response; } catch(e) { response = {}; }
      if(typeof response.earned_balance !== 'undefined'){
        window._earnedLeaveBalance = parseFloat(response.earned_balance);
      } else if(typeof response.balance !== 'undefined'){
        window._earnedLeaveBalance = parseFloat(response.balance);
      }
      if(typeof response.balance !== 'undefined'){
        var bal = format_leave_number(window._earnedLeaveBalance);
        $('#leave_balance_value').text(bal);
        $('#leave_balance').val(window._earnedLeaveBalance);
      }
      if(typeof response.carry_forward !== 'undefined'){
        $('#carry_forward').val(response.carry_forward);
      }
      if(response.html){
        $('#number_days_off_2').html(response.html);
      }
      // Do not auto-fill dates — days must come from the user's date selection.
      enforce_earned_vs_lop();
      update_applying_for();
      if(typeof response.number_day_off !== 'undefined'){
        $('input[name="number_day_off"]').val(response.number_day_off);
      }
      // Keep dashboard cards in sync with apply modal balance.
      if (typeof loadLeaveBalanceCards === 'function') {
        loadLeaveBalanceCards();
      }
      $('#requisition-form .btn-submit').removeAttr('disabled');
      $('button[type="submit"]').removeAttr('disabled');
    }).fail(function(){
      $('#leave_balance_value').text('-');
      update_remaining_balance_display(0);
      $('#requisition-form .btn-submit').removeAttr('disabled');
    });
  }

  function btn_additional_timesheets(){
    "use strict";
    $('#additional_timesheets_modalss').modal();   
  }

  function new_requisition(){
    "use strict";
    $('#requisition_m').modal('show');
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    // name is type_of_leave (id=rel_type) — do not auto-pick LOP/EL
    var $leaveType = $('#requisition_m select[name="type_of_leave"], #requisition_m #rel_type');
    $leaveType.val('');
    if ($leaveType.hasClass('selectpicker')) {
      $leaveType.selectpicker('refresh');
    }
    $('#requisition_m textarea[name="reason"]').val('');
    $('#cc_picker_wrap').addClass('hide');
    $('#cc_add_link').removeClass('hide');
    $('#loss_of_pay_notice').hide().text('');
    // Clear dates so Applying For / balance wait for user selection
    clear_leave_apply_dates();
    setTimeout(function(){
      clear_leave_apply_dates();
      get_remain_day_off();
      update_applying_for();
    }, 200);
  }

  function clear_leave_apply_dates(){
    "use strict";
    var $start = $('input[name="start_time"]');
    var $end = $('input[name="end_time"]');
    $start.val('');
    $end.val('');
    // Some Perfex datepickers refill "today" on init — clear again after that.
    try {
      if ($start.data('DateTimePicker')) { $start.data('DateTimePicker').clear(); }
      if ($end.data('DateTimePicker')) { $end.data('DateTimePicker').clear(); }
    } catch (e) {}
    $('#number_of_leaving_day').val(0);
    $('#applying_for_value').text('—');
    $('#remaining_balance_value').text('—').removeClass('is-negative');
    $('#sandwich_leave_notice').hide().text('');
  }

  function add_requisition(){
    "use strict";
    $subject  = $('#subject').val();
    $approver_id = $('select[name="staff[]"]').val();
    $rel_type = $('select[name="rel_type"]').val();
    $type_of_leave = $('select[name="type_of_leave"]').val();
    $start_date = $("#start_date").val();
    $end_date = $("#end_date").val();
    $file = $("#file").val();
    if(typeof $end_date === 'undefined')
    {
      $end_date = $start_date;
      
    }else{
      $end_date = $("#end_date").val();
    }
    $reason = $("#reason").val();
    $followers_id = $('select[name="follower[]"]').val();

    if($approver_id != '' && $followers_id != ''){

      var formData = new FormData();
      formData.append("subject", $subject);
      formData.append("approver_id", $approver_id);
      formData.append("followers_id", $followers_id);
      formData.append("rel_type", $rel_type);
      formData.append("type_of_leave", $type_of_leave);
      formData.append("start_time", $start_date);
      formData.append("end_time", $end_date);
      formData.append("reason", $reason);
      formData.append("file", $file);
      formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());

      $.ajax({ 
        url: admin_url + 'timesheets/add_requisition_ajax', 
        method: 'post', 
        data: formData, 
        contentType: false, 
        processData: false

      }).done(function(response) {
        response = JSON.parse(response);

        if(response.success == true){
         alert_float('success', "<?php echo _l('Add_requisition_success') ; ?>");
         $('#requisition_m').removeClass('sidebar-open');
         table_registration_leave.DataTable().ajax.reload().columns.adjust().responsive.recalc();

       }else if(response.success == false){
        alert_float('danger', response.message || "<?php echo _l('please_check_again') ; ?>");
      }else{
        alert_float('warning', "<?php echo _l('Requisition_information_already_exists') ; ?>");
      }
    });
      return false;
    }else{
      alert_float('danger', "<?php echo _l('please_check_again') ; ?>");
    }
  }           
  function staff_bulk_actions(){
    "use strict";
    $('#table_registration_leave_bulk_actions').modal('show');
  }

  // Leads bulk action
  function staff_delete_bulk_action(event) {
    "use strict";
    if (confirm_delete()) {
      var mass_delete = $('#mass_delete').prop('checked');

      if(mass_delete == true){
        var ids = [];
        var data = {};

        data.mass_delete = true;
        data.rel_type = 'timesheets_requisition';
		
        var rows = $('#table-table_registration_leave').find('tbody tr');
        $.each(rows, function() {
		
          var checkbox = $($(this).find('td').eq(0)).find('input');
          if (checkbox.prop('checked') === true) {
            ids.push(checkbox.val());
          }
        });
		

        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
          $.post(admin_url + 'timesheets/timesheets_delete_bulk_action', data).done(function() {
            window.location.reload();
          }).fail(function(data) {
            $('#table_registration_leave_bulk_actions').modal('hide');
            alert_float('danger', data.responseText);
          });
        }, 200);
      }else{
        window.location.reload();
      }
    }
  }

  function view_additional_timesheets(id){
    "use strict";
    $.post(admin_url+'timesheets/get_data_additional_timesheets/'+id).done(function(response){
      response = JSON.parse(response);
      $('#additional_timesheets_modal').html('');

      $('#additional_timesheets_modal').append(response.html);

      $('#additional_timesheets_modal').modal('show');
      $('select[name="approver_c"]').selectpicker('refresh');
    });
  }


  function formatNumber(n) {
    "use strict";
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }
  function formatCurrency(input, blur) {
    "use strict";

    var input_val = input.val();

    if (input_val === "") { return; }

    var original_len = input_val.length;

    var caret_pos = input.prop("selectionStart");

    if (input_val.indexOf(".") >= 0) {

      var decimal_pos = input_val.indexOf(".");
      var left_side = input_val.substring(0, decimal_pos);
      var right_side = input_val.substring(decimal_pos);

      left_side = formatNumber(left_side);

      right_side = formatNumber(right_side);

      right_side = right_side.substring(0, 2);
      input_val = left_side + "." + right_side;

    } 
    else {
      input_val = formatNumber(input_val);
      input_val = input_val;
    }
    input.val(input_val);
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
  }

  function timeToSeconds(time) {
    "use strict";
    time = time.split(/:/);
    var Seconds = parseInt(time[0] * 3600) + parseInt(time[1] * 60) + parseInt(time[2]);
    return Seconds;
  }
  function input_method(){
    "use strict";
    $('#input_method_modal').modal('show');
  }



  function approve_request(id, rel_type){
    "use strict";
    change_request_approval_status(id,1,rel_type);
  }

  function deny_request(id, rel_type){
    "use strict";
    if (rel_type === 'additional_timesheets') {
      $('#reg_reject_id').val(id);
      $('#reg_reject_rel_type').val(rel_type);
      $('#reg_reject_reason').val('');
      $('#regularization_reject_modal').modal('show');
      return;
    }
    change_request_approval_status(id,2,rel_type);
  }

  $(document).on('click', '#reg_reject_submit_btn', function(){
    "use strict";
    var id = $('#reg_reject_id').val();
    var rel_type = $('#reg_reject_rel_type').val() || 'additional_timesheets';
    var reason = $.trim($('#reg_reject_reason').val() || '');
    if (!reason) {
      alert_float('warning', 'Please enter a reason for rejecting.');
      $('#reg_reject_reason').focus();
      return;
    }
    $('#regularization_reject_modal').modal('hide');
    change_request_approval_status(id, 2, rel_type, reason);
  });

  function change_request_approval_status(id, status, rel_type, note){
    "use strict";
    var data = {};
    data.rel_id = id;
    data.approve = status;
    data.rel_type = rel_type;
    data.note = (typeof note !== 'undefined' && note !== null) ? note : '';
    if (!data.note && $('#additional_timesheets_modal textarea[name="reason"]').length) {
      data.note = $.trim($('#additional_timesheets_modal textarea[name="reason"]').val() || '');
    }
    if (status === 2 && rel_type === 'additional_timesheets' && !data.note) {
      deny_request(id, rel_type);
      return;
    }
    $("body").append('<div class="dt-loader"></div>');
    $.post(admin_url + 'timesheets/approve_request/' + id, data).done(function(response){
      $("body").find('.dt-loader').remove();
      response = JSON.parse(response);
      if (response.success === true || response.success == 'true') {
        alert_float('success', response.message);
        if (rel_type === 'additional_timesheets' && typeof table_additional_timesheets !== 'undefined' && table_additional_timesheets.length && $.fn.DataTable.isDataTable(table_additional_timesheets)) {
          table_additional_timesheets.DataTable().ajax.reload(null, false);
        } else {
          window.location.reload();
        }
        return;
      }
      alert_float('warning', response.message || 'Could not update request');
    }).fail(function(){
      $("body").find('.dt-loader').remove();
      alert_float('danger', 'Request failed. Please try again.');
    });
  }

  function change_approx(el, next){
    "use strict";
    var date1 = $(el).val();
    var date2 = $('input[name="'+next+'"]').val();
    var arr_date1 = date1.split(" ");
    var arr_date2 = date2.split(" ");
    var dates1 = arr_date1[0].split("-");
    var dates2 = arr_date2[0].split("-");
    var check = 0;
  }

  function get_date(el){
    "use strict";
    var val = $(el).val();
    if(val < 0.5){
      $(el).closest('.form-group').addClass('has-error');
      alert_float('warning', '<?php echo _l('please_enter_a_value_greater_than_or_equal_to_0.5') ?>');
      $('.btn-submit').attr('disabled', true);    
      return ;
    }
    else{
      $(el).closest('.form-group').removeClass('has-error');
      $('.btn-submit').removeAttr('disabled');    
    }
    var data = {};
    data.staffid = $('input[name="userid"]').val();
    data.startdate = $('input[name="start_time"]').val();
    data.enddate = $('input[name="end_time"]').val();
    data.number_of_days = $('input[name="number_of_leaving_day"]').val();
    $.post(admin_url+'timesheets/get_date_leave',data).done(function(response){
      response = JSON.parse(response);
      $('input[name="end_time"]').val(response.end_date);
    });
  }

  function send_request_approve(id, addedfrom){
    "use strict";
    var data = {};
    data.rel_id = id;
    data.rel_type = 'additional_timesheets';
    data.addedfrom = addedfrom;
    $("body").append('<div class="dt-loader"></div>');
    $.post(admin_url + 'timesheets/send_request_approve', data).done(function(response){
      response = JSON.parse(response);
      if(response.type == 'choose'){
        $("body").find('.dt-loader').remove();
        if (response.success === true || response.success == 'true') {
          alert_float('success', response.message);
          window.location.reload();
        }else{
          alert_float('warning', response.message);
          window.location.reload();
        }
      }else if(response.type == 'not_choose'){
        $("body").find('.dt-loader').remove();
        $('#choose_approver').html('');
        alert_float('success', response.message);
        $('#choose_approver').append(response.html);
        $('.selectpicker').selectpicker({});
      }
    });
  }
  
  function choose_approver(id, addedfrom){
    "use strict";
    var data = {};
    data.rel_id = id;
    data.rel_type = 'additional_timesheets';
    data.addedfrom = addedfrom;
    data.staffid = $('#approver_c').val();
    if(data.staffid != ''){
      $("body").append('<div class="dt-loader"></div>');
      $.post(admin_url + 'timesheets/choose_approver', data).done(function(response){
        response = JSON.parse(response);
        $("body").find('.dt-loader').remove();
        if (response.success === true || response.success == 'true') {
          alert_float('success', response.message);
          window.location.reload();
        }else{
          alert_float('warning', response.message);
          window.location.reload();
        }

      });
    }else if(data.staffid == ''){
      alert_float('warning', '<?php echo _l('please_choose_approver'); ?>');
    }
  }

  function start_time_check(start_input, end_input){
    "use strict";
    var rel_type = $('select[name="rel_type"]').val();
    if(rel_type == 3 || rel_type == 2 || rel_type == 6){
      return false;
    }
    var fit_start_time  = $(start_input).val(); 
    var fit_end_time    = $(end_input).val(); 
    if(new Date(datetimeToDate(fit_start_time)).getTime() > new Date(datetimeToDate(fit_end_time)).getTime())
    {
      alert_float('warning', '<?php echo _l('ts_from_date_must_be_less_than_or_equal_to_to_date'); ?>');
      $(start_input).val(fit_end_time);
      return false;
    }
    return true;
  }

  function end_time_check(start_input, end_input){
    "use strict";
    var rel_type = $('select[name="type_of_leave"]').val();
    if(rel_type == 'saturday-leaves' || rel_type == 'half-days' || rel_type == 'unpaid-half-days' || rel_type == 'short-leaves'){
      return false;
    }
    var fit_start_time    = $(start_input).val(); 
    var fit_end_time  = $(end_input).val();
    if(new Date(datetimeToDate(fit_start_time)).getTime() > new Date(datetimeToDate(fit_end_time)).getTime())
    {
      alert_float('warning', '<?php echo _l('ts_to_date_must_be_greater_than_or_equal_to_from_date'); ?>');
      $(end_input).val(fit_start_time);
      return false;
    }
    return true;
  }

  function datetimeToDate(datetime){
    "use strict";
    var format_date = $('input[name="date_format"]').val();
    var parts = '';
    var result = datetime;
    switch(format_date)
    {
      case 'd-m-Y|%d-%m-%Y':
      parts = datetime.split('-');
      result = parts[2] + '/' + parts[1] + '/' + parts[0];//
      break;
      case 'd/m/Y|%d/%m/%Y':
      parts = datetime.split('/');
      result = parts[2] + '/' + parts[1] + '/' + parts[0];//
      break;
      case 'm-d-Y|%m-%d-%Y':
      parts = datetime.split('-');
      result = parts[2] + '/' + parts[0] + '/' + parts[1];//
      break;
      case 'm.d.Y|%m.%d.%Y':
      parts = datetime.split('.');
      result =  parts[2] + '/' + parts[0] + '/' + parts[1];//
      break;
      case 'm/d/Y|%m/%d/%Y':
      parts = datetime.split('/');
      result = parts[2] + '/' + parts[0] + '/' + parts[1];//
      break;
      case 'Y-m-d|%Y-%m-%d':
      parts = datetime.split('-');
      result = parts[0] + '/' + parts[1] + '/' + parts[2];//
      break;
      case 'd.m.Y|%d.%m.%Y':
      parts = datetime.split('.');
      result = parts[2] + '/' + parts[1] + '/' + parts[0];//
      break;
    }  
    return result;
  }
</script>