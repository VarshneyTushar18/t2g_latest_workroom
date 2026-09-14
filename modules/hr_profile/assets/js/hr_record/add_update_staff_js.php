<script>
	$(function(){
		'use strict';
		appValidateForm($('#add_edit_member'), {
			firstname: 'required',
			lastname: 'required',
			staff_identifi: 'required',
			status_work: 'required',
			job_position: 'required',
			phonenumber: 'required',

			//making reporting person required
			team_manage:'required',
			password: {
				required: {
					depends: function(element) {
						return ($('input[name="isedit"]').length == 0) ? true : false
					}
				}
			},
			email: {
				required: true,
				email: true,
				remote: {
					url: site_url + "admin/misc/staff_email_exists",
					type: 'post',
					data: {
						email: function() {
							return $('input[name="email"]').val();
						},
						memberid: function() {
							return $('input[name="memberid"]').val();
						}
					}
				}
			},
			staff_identifi: {
				required: true,
				remote: {
					url: site_url + "admin/hr_profile/hr_code_exists",
					type: 'post',
					data: {
						staff_identifi: function() {
							return $('input[name="staff_identifi"]').val();
						},
						memberid: function() {
							return $('input[name="memberid"]').val();
						}
					}
				}
			}
		});

		init_datepicker();
		init_selectpicker();
		$(".selectpicker").selectpicker('refresh');

		$('select[name="role_v"]').on('change', function() {
			var roleid = $(this).val();
			init_roles_permissions_v2(roleid, true);
		});


		$("input[name='profile_image']").on('change', function() {
			readURL(this);
		});

	});

	function init_roles_permissions_v2(roleid, user_changed) {
		"use strict";

		roleid = typeof(roleid) == 'undefined' ? $('select[name="role_v"]').val() : roleid;
		var isedit = $('.member > input[name="isedit"]');

    // Check if user is edit view and user has changed the dropdown permission if not only return
    if (isedit.length > 0 && typeof(roleid) !== 'undefined' && typeof(user_changed) == 'undefined') {
    	return;
    }

    // Administrators does not have permissions
    if ($('input[name="administrator"]').prop('checked') === true) {
    	return;
    }

    // Last if the roleid is blank return
    if (roleid === '') {
    	return;
    }

    // Get all permissions
    var permissions = $('table.roles').find('tr');
    requestGetJSON('hr_profile/hr_role_changed/' + roleid).done(function(response) {

    	permissions.find('.capability').not('[data-not-applicable="true"]').prop('checked', false).trigger('change');

    	$.each(permissions, function() {
    		var row = $(this);
    		$.each(response, function(feature, obj) {
    			if (row.data('name') == feature) {
    				$.each(obj, function(i, capability) {
    					row.find('input[id="' + feature + '_' + capability + '"]').prop('checked', true);
    					if (capability == 'view') {
    						row.find('[data-can-view]').change();
    					} else if (capability == 'view_own') {
    						row.find('[data-can-view-own]').change();
    					}
    				});
    			}
    		});
    	});
    });
}

	function readURL(input) {
		"use strict";
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				$("img[id='wizardPicturePreview']").attr('src', e.target.result).fadeIn('slow');
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	function hr_profile_update_staff(staff_id) {
		"use strict";

		$("#modal_wrapper").load("<?php echo admin_url('hr_profile/hr_profile/member_modal'); ?>", {
			slug: 'update',
			staff_id: staff_id
		}, function() {
			if ($('.modal-backdrop.fade').hasClass('in')) {
				$('.modal-backdrop.fade').remove();
			}
			if ($('#appointmentModal').is(':hidden')) {
				$('#appointmentModal').modal({
					show: true
				});
			}
			$('#appointmentModal div[app-field-wrapper="role_v"]').hide();
			$('#appointmentModal #div_hourly_rate').hide();
			init_employment_leave_sync();

		});

		init_selectpicker();
		$(".selectpicker").selectpicker('refresh');
	}

	function init_employment_leave_sync() {
		"use strict";

		var $form = $('#add_edit_member');
		if (!$form.length || !$form.find('[name="employment_category"]').length) {
			return;
		}

		var $rate = $form.find('[name="earned_leave_rate"]');
		$rate.prop('readonly', false).prop('disabled', false);

		function parseDoj(raw) {
			raw = $.trim(raw || '');
			if (!raw) {
				return null;
			}
			// Y-m-d
			var m = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
			if (m) {
				return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
			}
			// d/m/Y or d-m-Y
			m = raw.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/);
			if (m) {
				return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10));
			}
			var d = new Date(raw);
			return isNaN(d.getTime()) ? null : d;
		}

		function monthlyRate(category, doj) {
			category = (category || 'fte').toLowerCase();
			if (category === 'intern' || category === 'contractual' || category === 'wfh') {
				return 1;
			}
			// Full time: 1.25, or 1.75 after 2 years
			if (doj) {
				var now = new Date();
				var years = (now - doj) / (365 * 24 * 60 * 60 * 1000);
				if (years >= 2) {
					return 1.75;
				}
			}
			return 1.25;
		}

		function isFirstEmploymentMonth(doj) {
			if (!doj) {
				return false;
			}
			var now = new Date();
			return doj.getFullYear() === now.getFullYear() && doj.getMonth() === now.getMonth();
		}

		function syncEarnedLeave(forceFill) {
			var category = $form.find('[name="employment_category"]').val() || 'fte';
			var doj = parseDoj($form.find('[name="doj"]').val());
			var $msg = $('#earned_leave_first_month_msg');
			var current = $.trim($rate.val() || '');

			if (isFirstEmploymentMonth(doj)) {
				$msg.removeClass('hide');
				if (forceFill || current === '') {
					$rate.val('0');
				}
				return;
			}

			$msg.addClass('hide');
			if (forceFill || current === '') {
				$rate.val(String(monthlyRate(category, doj)));
			}
		}

		$form.off('changed.bs.select.employmentLeave change.employmentLeave', '[name="employment_category"]')
			.on('changed.bs.select.employmentLeave change.employmentLeave', '[name="employment_category"]', function() {
				syncEarnedLeave(true);
			});
		$form.off('change.employmentLeave blur.employmentLeave', '[name="doj"]')
			.on('change.employmentLeave blur.employmentLeave', '[name="doj"]', function() {
				syncEarnedLeave(false);
			});
		$form.off('dp.change.employmentLeave', '[name="doj"]')
			.on('dp.change.employmentLeave', '[name="doj"]', function() {
				syncEarnedLeave(false);
			});

		// Keep saved/manual value if present; only auto-fill when empty.
		syncEarnedLeave(false);
	}

	init_employment_leave_sync();

</script>