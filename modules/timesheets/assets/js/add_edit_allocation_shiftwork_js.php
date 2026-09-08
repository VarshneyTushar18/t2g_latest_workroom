<script>
(function(){
  "use strict";
  var hot = null;
  var list_type = <?php echo json_encode($shift_type); ?>;
  var setHeader = <?php echo json_encode($head_data); ?>;
  var dataObject = <?php echo json_encode($data_object); ?>;
  var isEdit = <?php echo isset($word_shift) ? 'true' : 'false'; ?>;
  var advancedMode = isEdit;

  function initHotIfNeeded() {
    var hotElement = document.querySelector('#example');
    if (!hotElement || hot) { return; }

    var staffSelect = $('#advanced_shift_panel select#staff');
    var staff = staffSelect.length ? staffSelect.val() : $('select[name="staff[]"]').not('#staff_simple').val();
    var columns = [];

    if (staff != '' && staff != null) {
      columns = [{
        data: 'staffid',
        width: 250,
        type: 'text',
      },
      {
        data: 'staff',
        width: 250,
        type: 'text',
        readOnly: true,
      }];
      $.each(setHeader, function(index, value) {
        if (index >= 2) {
          columns.push({
            data: value,
            renderer: customDropdownRenderer,
            editor: "chosen",
            width: 250,
            chosenOptions: { data: list_type }
          });
        }
      });
    } else {
      $.each(setHeader, function(index, value) {
        columns.push({
          data: value,
          renderer: customDropdownRenderer,
          editor: "chosen",
          width: 250,
          chosenOptions: { data: list_type }
        });
      });
    }

    hot = new Handsontable(hotElement, {
      data: dataObject,
      columns: columns,
      contextMenu: true,
      manualRowMove: true,
      manualColumnMove: true,
      stretchH: 'all',
      autoWrapRow: true,
      rowHeights: 40,
      defaultRowHeight: 100,
      headerTooltips: true,
      maxRows: 200,
      minHeight: '100%',
      maxHeight: '500px',
      width: '100%',
      height: 400,
      rowHeaders: true,
      licenseKey: 'non-commercial-and-evaluation',
      colHeaders: setHeader,
      columnHeaderHeight: 40,
      minRows: 1,
      dropdownMenu: true,
      filters: true,
      allowInsertRow: true,
      allowRemoveRow: true,
      manualRowResize: true,
      manualColumnResize: true
    });

    if (staff != '' && staff != null) {
      hot.updateSettings({
        hiddenColumns: { columns: [0], indicators: true }
      });
    }
  }

  function applyStaffScope() {
    var scope = $('input[name="staff_scope"]:checked').val() || 'search';
    var $sel = $('#staff_simple');
    if (!$sel.length) { return; }

    if (scope === 'all') {
      $('#employee_row').addClass('hide');
      $sel.selectpicker('val', []);
      $sel.prop('disabled', true).selectpicker('refresh');
    } else {
      $('#employee_row').removeClass('hide');
      $sel.prop('disabled', false);
      if (scope === 'search') {
        // Force single selection
        var cur = $sel.val() || [];
        if (cur.length > 1) { $sel.selectpicker('val', [cur[0]]); }
        $sel.attr('data-max-options', '1');
      } else {
        $sel.removeAttr('data-max-options');
      }
      $sel.selectpicker('refresh');
    }
  }

  function enableSimpleMode() {
    advancedMode = false;
    $('#simple_shift_form').removeClass('hide');
    $('#advanced_shift_panel').addClass('hide');
    $('#staff_simple').prop('disabled', false).selectpicker('refresh');
    $('#advanced_shift_panel select#staff').prop('disabled', true);
    $('#advanced_shift_panel input[name="type_shiftwork"]').prop('disabled', true);
    // Ensure simple hidden type stays enabled
    $('#simple_shift_form input[name="type_shiftwork"]').prop('disabled', false);
    applyStaffScope();
  }

  function enableAdvancedMode() {
    advancedMode = true;
    $('#simple_shift_form').addClass('hide');
    $('#advanced_shift_panel').removeClass('hide');
    $('#staff_simple').prop('disabled', true).selectpicker('refresh');
    $('#advanced_shift_panel select#staff').prop('disabled', false).selectpicker('refresh');
    $('#advanced_shift_panel input[name="type_shiftwork"]').prop('disabled', false);
    $('#simple_shift_form input[name="type_shiftwork"]').prop('disabled', true);
    // Remove simple_shift_id so controller uses grid path
    $('#simple_shift_id').prop('disabled', true);
    initHotIfNeeded();
    setTimeout(function(){ if (hot) { hot.render(); } }, 100);
  }

  if (isEdit) {
    initHotIfNeeded();
  }

  $('input[name="staff_scope"]').on('change', applyStaffScope);
  applyStaffScope();

  $('#show_advanced_shift').on('click', function(e) {
    e.preventDefault();
    enableAdvancedMode();
  });

  $('#hide_advanced_shift').on('click', function(e) {
    e.preventDefault();
    $('#simple_shift_id').prop('disabled', false);
    enableSimpleMode();
  });

  // Limit search mode to one employee
  $('#staff_simple').on('changed.bs.select', function() {
    var scope = $('input[name="staff_scope"]:checked').val();
    if (scope === 'search') {
      var vals = $(this).val() || [];
      if (vals.length > 1) {
        $(this).selectpicker('val', [vals[vals.length - 1]]);
      }
    }
  });

  $('#shift_f-form').on('submit', function(e) {
    if (advancedMode) {
      if (hot) {
        $('input[name="shifts_detail"]').val(hot.getData());
      }
      return true;
    }

    var scope = $('input[name="staff_scope"]:checked').val();
    var shiftId = $('#simple_shift_id').val();
    var fromDate = $('#simple_shift_form input[name="from_date"]').val();
    var toDate = $('#simple_shift_form input[name="to_date"]').val();
    var staff = $('#staff_simple').val() || [];

    if (!fromDate || !toDate) {
      alert_float('warning', 'Please select From Date and To Date.');
      e.preventDefault();
      return false;
    }
    if (!shiftId) {
      alert_float('warning', 'Please select a Shift.');
      e.preventDefault();
      return false;
    }
    if (scope !== 'all' && (!staff || staff.length === 0)) {
      alert_float('warning', 'Please select an employee.');
      e.preventDefault();
      return false;
    }
    return true;
  });

  $('.save_detail_shift').on('click', function() {
    if (hot) {
      $('input[name="shifts_detail"]').val(hot.getData());
    }
  });

  $('#advanced_shift_panel').on('change', 'input[name="type_shiftwork"], select[name="department[]"], select[name="role[]"], select#staff, input[name="from_date"], input[name="to_date"]', function() {
    if (!hot) { return; }
    var val = $('#advanced_shift_panel input[name="type_shiftwork"]:checked').val();
    var department = $('select[name="department[]"]').val();
    var role = $('select[name="role[]"]').val();
    var staff = $('#advanced_shift_panel select#staff').val();
    var from_date = $('input[name="from_date"]').val();
    var to_date = $('input[name="to_date"]').val();

    $.post(admin_url + 'timesheets/get_hanson_shiftwork', {
      department: department,
      role: role,
      staff: staff,
      type_shiftwork: val,
      from_date: from_date,
      to_date: to_date
    }).done(function(response) {
      response = JSON.parse(response);
      if (staff != '' && staff != null) {
        var columns = [{
          data: 'staffid', width: 250, type: 'text',
        }, {
          data: 'staff', width: 250, type: 'text', readOnly: true,
        }];
        $.each(response.head_data, function(index, value) {
          if (index >= 2) {
            columns.push({
              data: value,
              renderer: customDropdownRenderer,
              editor: "chosen",
              width: 250,
              chosenOptions: { data: list_type }
            });
          }
        });
        hot.updateSettings({
          data: response.data_object,
          columns: columns,
          colHeaders: response.head_data,
          hiddenColumns: { columns: [0], indicators: true }
        });
      } else {
        var columns = [];
        $.each(response.head_data, function(index, value) {
          columns.push({
            data: value,
            renderer: customDropdownRenderer,
            editor: "chosen",
            width: 250,
            chosenOptions: { data: list_type }
          });
        });
        hot.updateSettings({
          columns: columns,
          data: response.data_object,
          colHeaders: response.head_data,
          hiddenColumns: {}
        });
      }
    });
  });

})(jQuery);

function customDropdownRenderer(instance, td, row, col, prop, value, cellProperties) {
  "use strict";
  var selectedId;
  var optionsList = cellProperties.chosenOptions.data;

  if (typeof optionsList === "undefined" || typeof optionsList.length === "undefined" || !optionsList.length) {
    Handsontable.cellTypes.text.renderer(instance, td, row, col, prop, value, cellProperties);
    return td;
  }

  var values = (value + "").split("|");
  value = [];
  for (var index = 0; index < optionsList.length; index++) {
    if (values.indexOf(optionsList[index].id + "") > -1) {
      selectedId = optionsList[index].id;
      value.push(optionsList[index].label);
    }
  }
  value = value.join(", ");
  Handsontable.cellTypes.text.renderer(instance, td, row, col, prop, value, cellProperties);
  return td;
}
</script>
