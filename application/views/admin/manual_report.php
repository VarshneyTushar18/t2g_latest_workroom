<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();  ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
	.selected-report {
		background: #141e461a;
		border-radius: 20px;
		width: inherit;
		padding: 5px;
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<!-- Table report -->
							<div class="col-md-6 border-right" id="report-container">
								<h4 class="no-margin font-medium"><i class="fa fa-balance-scale" aria-hidden="true"></i> <?php echo _l('reports'); ?></h4>
								<hr />

								<?php if (is_admin() || in_the_department('HR')) { ?>
									<p><a href="<?= '#' //admin_url('hr_recruit_report')
												?>" class="font-medium" onclick="init_report(this,'hr_recruit_report'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo 'HR Recruit report'; ?></a></p>
								<hr class="hr-10" />
								<?php } ?>
								<?php if (is_admin()) { ?>

									<p><a href="<?= '#' //admin_url('inactive_staff_report')
												?>" class="font-medium" onclick="init_report(this,'inactive_staff_report'); gen_reports(); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo 'Inactive Staffs'; ?></a></p>
								<hr class="hr-10" />
								<?php } ?>
								<?php if (is_admin() || in_the_department('IT')) { ?>

								<p><a href="<?= '#' //admin_url('it_asset_report')
											?>" class="font-medium" onclick="init_report(this,'it_assets_report'); return false;"><i class="fa fa-caret-down" aria-hidden="true"></i> <?php echo 'IT Assets'; ?></a></p>
								<?php } ?>

							</div>
							<!-- End table report -->



							<div class="col-md-6">
								<div class="bg-light-gray border-radius-4">
									<div class="p8">

										<div id="currency" class="form-group hide">
											<label for="currency"><i class="fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('report_sales_base_currency_select_explanation'); ?>"></i> <?php echo _l('currency'); ?></label><br />
											<select class="selectpicker" name="currency" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">

											</select>
										</div>


										<div class="form-group" id="report-time">
											<label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
											<select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
												<!-- <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
												<option value="this_month"><?php echo _l('this_month'); ?></option>
												<option value="1"><?php echo _l('last_month'); ?></option>
												<option value="this_year"><?php echo _l('ts_this_year'); ?></option>
												<option value="last_year"><?php echo _l('ts_last_year'); ?></option>
												<option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
												<option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
												<option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option> -->
												<option value="custom"><?php echo _l('period_datepicker'); ?></option>
											</select>
										</div>

										<div class="form-group hide" id="report-month">
											<label for="months-report"><?php echo _l('month'); ?></label><br />
											<select class="selectpicker" name="months_2_report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
												<?php
												for ($i = 1; $i <= 12; $i++) {
													$dateObj   = DateTime::createFromFormat('!m', $i);
													$monthName = $dateObj->format('F');
													$month = $i;
													if (strlen($i) == 1) {
														$month = '0' . $i;
													}
													$selected = '';
													if (date('m') == $month) {
														$selected = 'selected';
													}
												?>
													<option value="<?php echo html_entity_decode($month); ?>" <?php echo html_entity_decode($selected); ?>><?php echo html_entity_decode($monthName); ?></option>
												<?php } ?>
											</select>
										</div>

										<?php $current_year = date('Y');
										$y0 = (int)$current_year;
										$y1 = (int)$current_year - 1;
										$y2 = (int)$current_year - 2;
										$y3 = (int)$current_year - 3;
										?>

										<div class="form-group hide" id="year_requisition">
											<label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
											<select name="year_requisition" id="year_requisition" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('filter_by') . ' ' . _l('year'); ?>">
												<option value="<?php echo html_entity_decode($y0); ?>" <?php echo 'selected' ?>><?php echo _l('year') . ' ' . $y0; ?></option>
												<option value="<?php echo html_entity_decode($y1); ?>"><?php echo _l('year') . ' ' . $y1; ?></option>
												<option value="<?php echo html_entity_decode($y2); ?>"><?php echo _l('year') . ' ' . $y2; ?></option>
												<option value="<?php echo html_entity_decode($y3); ?>"><?php echo _l('year') . ' ' . $y3; ?></option>

											</select>
										</div>


										<div id="date-range" class="hide mbot15">
											<div class="row">
												<div class="col-md-6">
													<?php echo render_date_input('report-from', 'report_sales_from_date'); ?>
												</div>
												<div class="col-md-6">
													<?php echo render_date_input('report-to', 'report_sales_to_date'); ?>
												</div>
											</div>

											<input type="submit" value="Send email" class="btn btn-primary" onclick="gen_reports()">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="report" class="hide">
							<hr class="hr-panel-heading" />
							<div class="row">
								<center>
									<h4 class="title_table"></h4>
								</center>
							</div>

							<?php if (has_permission('report_management', '', 'view') || is_admin()) { ?>
								<div class="row sorting_table hide">
									<div class="table-fillter col-md-4">
										<div class="form-group">
											<label for="annual_leave"><?php echo _l('role'); ?></label>
											<select name="role[]" class="selectpicker" data-live-search="true" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
												<?php foreach ($roles as $role) { ?>
													<option value="<?php echo html_entity_decode($role['roleid']); ?>"><?php echo html_entity_decode($role['name']) ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="table-fillter col-md-4">
										<div class="form-group">
											<label for="annual_leave"><?php echo _l('department'); ?></label>
											<select name="department[]" class="selectpicker" data-live-search="true" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
												<?php foreach ($department as $value) { ?>
													<option value="<?php echo html_entity_decode($value['departmentid']); ?>"><?php echo html_entity_decode($value['name']); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="table-fillter col-md-4">
										<div class="form-group">
											<label for="annual_leave"><?php echo _l('staff'); ?></label>
											<select name="staff[]" class="selectpicker" data-live-search="true" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
												<?php foreach ($staff as $item) { ?>
													<option value="<?php echo html_entity_decode($item['staffid']); ?>"><?php echo html_entity_decode($item['firstname']) . ' ' . $item['lastname']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="table-fillter hide col-md-4 rel-type-fillter">
										<div class="form-group">
											<label for="rel_type" class="control-label"><?php echo _l('type'); ?></label>
											<select name="rel_type[]" class="selectpicker" data-width="100%" multiple="true" data-none-selected-text="<?php echo _l('none_type'); ?>">
												<option value="1"><?php echo _l('Leave') ?></option>
												<option value="2"><?php echo _l('late') ?></option>
												<option value="6"><?php echo _l('early') ?></option>
												<option value="3"><?php echo _l('Go_out') ?></option>
												<option value="4"><?php echo _l('Go_on_bussiness') ?></option>
											</select>
										</div>
									</div>
								</div>

								<!-- workplace - root -->
								<div class="row sorting_2_table hide">
									<div class="filter_fr_2 col-md-3 staff_2_fr">
										<div class="form-group">
											<label for="annual_leave"><?php echo _l('staff'); ?></label>
											<select name="staff_2_fillter[]" class="selectpicker" data-live-search="true" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
												<?php foreach ($staff as $item) { ?>
													<option value="<?php echo html_entity_decode($item['staffid']); ?>"><?php echo html_entity_decode($item['firstname']) . ' ' . $item['lastname']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="filter_fr_2 col-md-3 department_2_fr">
										<?php echo render_select('department_2_fillter[]', $department, array('departmentid', 'name'), 'department', '', array('multiple' => true, 'data-live-search' => true), [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3 roles_2_fr">
										<?php echo render_select('roles_2_fillter[]', $roles, array('roleid', 'name'), 'role', '', array('multiple' => true, 'data-live-search' => true), [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3 workplace_2_fr">
										<?php echo render_select('workplace_2_fillter[]', $workplace, array('id', 'name'), 'workplace', '', array('multiple' => true, 'data-live-search' => true), [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3 route_point_2_fr">
										<?php echo render_select('route_point_2_fillter[]', $route_point, array('id', 'name'), 'route_point', '', array('multiple' => true, 'data-live-search' => true), [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3 word_shift_2_fr">
										<?php echo render_select('word_shift_2_fillter[]', $word_shift, array('id', 'shift_type_name'), 'shift', '', array('multiple' => true, 'data-live-search' => true), [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3 type_2_fr">
										<?php echo render_select('type_2_fillter', [['id' => 3, 'name' => _l('all')], ['id' => 1, 'name' => _l('check_in')], ['id' => 2, 'name' => _l('check_out')]], array('id', 'name'), 'type', 3, [], [], '', '', false); ?>
									</div>

									<div class="filter_fr_2 col-md-3 type_22_fr">
										<?php echo render_select('type_22_fillter', [
											['id' => 3, 'name' => _l('all')],
											['id' => 1, 'name' => _l('check_in')],
											['id' => 2, 'name' => _l('check_out')],
											['id' => 4, 'name' => _l('not_check_in')],
											['id' => 5, 'name' => _l('not_check_out')],
											['id' => 6, 'name' => _l('check_in_check_out')]

										], array('id', 'name'), 'type', 3, [], [], '', '', false); ?>
									</div>
									<div class="filter_fr_2 col-md-3">
									</div>
									<div class="filter_fr_2 col-md-3">
									</div>
									<div class="filter_fr_2 col-md-3">
									</div>

								</div>
								<!-- workplace - root -->
							<?php } ?>


						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
</body>


<script>
	var salesChart;
	var groupsChart;
	var paymentMethodsChart;
	var customersTable;
	var report_from = $('input[name="report-from"]');
	var report_to = $('input[name="report-to"]');

	var report_leave_statistics = $('#leave-statistics');

	var date_range = $('#date-range');
	var report_from_choose = $('#report-time');
	(function() {
		"use strict";
		init_datepicker();
		report_from.on('change', function() {
			var val = $(this).val();
			var report_to_val = report_to.val();
			if (val != '') {
				report_to.attr('disabled', false);

			} else {
				report_to.attr('disabled', true);
			}
		});

		// report_to.on('change', function() {
		// 	var val = $(this).val();
		// 	if (val != '') {
		// 		// gen_reports();
		// 	}
		// });

		$('select[name="months-report"]').on('change', function() {
			var val = $(this).val();
			report_to.attr('disabled', true);
			report_to.val('');
			report_from.val('');
			if (val == 'custom') {
				date_range.addClass('fadeIn').removeClass('hide');
				return;
			} else {
				if (!date_range.hasClass('hide')) {
					date_range.removeClass('fadeIn').addClass('hide');
				}
			}
			console.log('this is sparta');
		});
		$('select[name="staff_2_fillter[]"],select[name="department_2_fillter[]"],select[name="workplace_2_fillter[]"],select[name="route_point_2_fillter[]"],select[name="word_shift_2_fillter[]"],select[name="type_2_fillter"],select[name="type_22_fillter"],select[name="roles_2_fillter[]"],select[name="role[]"],select[name="department[]"],select[name="staff[]"],select[name="rel_type[]"],select[name="year_requisition"],select[name="role[]"], select[name="months_2_report"]').on('change', function() {
			gen_reports();
		});
	})(jQuery);
	var current_type = '';
	var list_fillter = {};

	function init_report(e, type) {
		"use strict";
		current_type = type;
		var report_wrapper = $('#report');

		if (report_wrapper.hasClass('hide')) {
			report_wrapper.removeClass('hide');
		}

		$('head title').html($(e).text());
		$('.leave-statistics-gen').addClass('hide');

		report_leave_statistics.addClass('hide');

		report_from_choose.addClass('hide');
		$('#bs-select-2-8').removeClass('hide');
		$('select[name="months-report"]').selectpicker('val', 'this_month');
		report_to.val('');
		report_from.val('');
		$('.reports_fr').addClass('hide');
		$('#report-time').removeClass('hide');
		$('.title_table').text('');
		$('.sorting_table').addClass('hide');
		$('select[name="role[]"]').closest('.col-md-4').removeClass('hide');
		$('select[name="staff[]"]').closest('.col-md-4').removeClass('hide');
		date_range.addClass('hide');
		$('.working-hours-gen').addClass('hide');
		$('.report_of_leave').addClass('hide');
		$('#leave-reports').addClass('hide');
		$('#year_requisition').addClass('hide');
		$('#history_check_in_out').addClass('hide');
		$('#check_in_out_progress_according_to_the_route').addClass('hide');
		$('#general_public_report').addClass('hide');
		$('.leave_by_department').addClass('hide');
		$('.ratio_check_in_out_by_workplace').addClass('hide');

		$('#report-time').addClass('hide');
		$('#requisition_report').addClass('hide');

		$('.rel-type-fillter').addClass('hide');
		$('.working-hours-gen').addClass('hide');
		$('#leave-reports').addClass('hide');
		$('#report_the_employee_quitting').addClass('hide');
		$('#list_of_employees_with_salary_change').addClass('hide');
		$('.table-fillter').addClass('col-md-4').removeClass('col-md-3');
		$('.sorting_2_table').addClass('hide');
		$('.filter_fr_2').addClass('hide').removeClass('col-md-4').addClass('col-md-3');
		$('#report-month').addClass('hide');


		// highlighted section custom added
		$('#report-container p').removeClass('selected-report');
		$(e).parent().addClass('selected-report');

		if (type == 'hr_recruit_report') {

			$('#report-time').removeClass('hide');
		} else if (type == 'inactive_staff_report') {
			$('#report-time').addClass('hide');
		} else if (type == 'it_assets_report') {
			$('#report-time').removeClass('hide');
		}
	}


	function send_hr_email() {

		console.log('this is hr mail');
		var report_from = $('input[name="report-from"]').val();
		var report_to = $('input[name="report-to"]').val();

		console.log(report_from + "\n" + report_to);
		$.ajax({
			url: '<?= admin_url('hr_recruit_report') ?>',
			method: 'POST',
			data: {
				from: report_from,
				to: report_to
			},
			success: function(result) {

				console.log(result);
				if (result == 'success') {
					Swal.fire({
						icon: "success",
						title: "Email Sent successfully!",
						showConfirmButton: false,
						timer: 2000
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Error while sending Email",
						showConfirmButton: false,
						timer: 2000
					});
				}
			}
		});

	}

	function send_inactive_staff_email() {

		console.log('this inactive staff mail');
		// var report_from = $('input[name="report-from"]').val();
		// var report_to = $('input[name="report-to"]').val();
		$.ajax({
			url: '<?= admin_url('inactive_staff_report') ?>',
			method: 'POST',
			// data: {
			// 	from: report_from,
			// 	to: report_to
			// },
			success: function(result) {

				// console.log(result);
				if (result == 'success') {
					Swal.fire({
						icon: "success",
						title: "Email Sent successfully!",
						showConfirmButton: false,
						timer: 2000
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Error while sending Email",
						showConfirmButton: false,
						timer: 2000
					});
				}
			}
		});

	}

	function send_it_assets_email() {

		console.log('this is it asset mail');
		var report_from = $('input[name="report-from"]').val();
		var report_to = $('input[name="report-to"]').val();

		$.ajax({
			url: '<?= admin_url('it_asset_report') ?>',
			method: 'POST',
			data: {
				from: report_from,
				to: report_to
			},
			success: function(result) {
				console.log(result);
				if (result == 'success') {
					Swal.fire({
						icon: "success",
						title: "Email Sent successfully!",
						showConfirmButton: false,
						timer: 2000
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Error while sending Email",
						showConfirmButton: false,
						timer: 2000
					});
				}
			}
		});

	}

	function gen_reports() {
		"use strict";
		if (current_type != '') {
			switch (current_type) {
				case 'annual_leave_report':
					leave_report();
					break;
				case 'general_public_report':
					general_public_report();
					break;
				case 'requisition_report':
					requisition_report();
					break;
				case 'history_check_in_out':
					history_check_in_out_report();
					break;
				case 'check_in_out_progress_according_to_the_route':
					check_in_out_progress_according_to_the_route_report();
					break;
				case 'check_in_out_progress':
					check_in_out_progress_report();
					break;
				case 'working_hours':
					report_by_working_hours();
					break;
				case 'report_of_leave':
					report_of_leave();
					break;
				case 'leave_by_department':
					leave_by_department();
					break;
				case 'ratio_check_in_out_by_workplace':
					ratio_check_in_out_by_workplace();
					break;
				case 'hr_recruit_report':
					send_hr_email();
					break;
				case 'it_assets_report':
					send_it_assets_email();
					break;
				case 'inactive_staff_report':
					send_inactive_staff_email();
					break;
			}
		}
	}
</script>

</html>