<div class="row">

	<!-- <?php if (($staff_p->staffid == get_staff_user_id() || is_admin()) && !$this->input->get('notifications')) { ?>
		<div class="col-md-12">
			<div class="panel-body no-padding-bottom">
				<?php $this->load->view('hr_record/includes/stats'); ?>
			</div>
		</div>
	<?php } ?>

	<br>
	<br> -->
	<?php
	if ($member->active == 0) { ?>
		<div class="alert alert-danger text-center"><?php echo _l('staff_profile_inactive_account'); ?></div>
		<hr />
	<?php } ?>
	<div class="col-md-12 pl-0">
		<div class="col-md-5">
			<div class="row">
				<div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
					<div class="card box-shadow-0 overflow-hidden">
						<?php if ($member->status_work == 'working') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_working"><?php echo _l('hr_working'); ?></span></div>
						<?php } elseif ($member->status_work == 'maternity_leave') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_maternity_leave"><?php echo _l('hr_maternity_leave'); ?></span></div>
						<?php } elseif ($member->status_work == 'inactivity') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_inactivity"><?php echo _l('hr_inactivity'); ?></span></div>
						<?php } ?>
						<div class="card-body">
							<div class="text-center">
								<div class="userprofile">
									<div class="userpic  brround mb-3">
										<?php echo staff_profile_image($member->staffid, array('staff-profile-image-thumb'), 'thumb'); ?>
									</div>
									<h3 class="username mb-2"><?php echo html_entity_decode($member->firstname . ' ' . $member->lastname); ?></h3>
									<div class="socials text-center mt-3">
										<!-- <a href="facebook: <?php echo html_escape($member->facebook); ?>" class="btn btn-circle">
											<i class="fa fa-facebook"></i>
										</a>
										<a href="linkedin: <?php echo html_escape($member->linkedin); ?>" class="btn btn-circle">
											<i class="fa fa-linkedin"></i>
										</a> -->
										<a href="skype: <?php echo html_escape($member->skype); ?>" class="btn btn-circle">
											<i class="fa fa-skype"></i>
										</a>
										<a href="mailto: <?php echo html_escape($member->email); ?>" class="btn btn-circle">
											<i class="fa fa-envelope"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
						<br>
					</div>
				</div>
			</div>
			<div class="card panel-theme">
				<div class="card-body no-padding">
					<ul class="list-group no-margin">
						<li class="list-group-item"><i class="fa fa-envelope mr-4"></i> <?php echo html_entity_decode($member->email) ?></li>
						<li class="list-group-item"><i class="fa fa-phone mr-4"></i> <?php echo html_entity_decode($member->phonenumber) ?></li>
						<!-- <li class="list-group-item"><i class="fa fa-graduation-cap mr-4"></i> <?php echo html_entity_decode($member->literacy) ?></li>
						<li class="list-group-item"><i class="fa fa-intersex mr-4"></i> <?php echo html_entity_decode(_l($member->sex)) ?></li> -->
					</ul>
				</div>

				<div class="card-header">
					<div class="float-left">
						<br>
						<h4 class="card-title text-center"><?php echo _l('staff_profile_departments') ?></h4>
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="card-body no-padding">
					<ul class="list-group no-margin">
						<li class="list-group-item">

							<?php if (count($staff_departments) > 0) {
							?>
								<div class="form-group mtop10">
									<div class="clearfix"></div>
									<?php
									foreach ($departments as $department) {
									?>
										<?php
										foreach ($staff_departments as $staff_department) {
											if ($staff_department['departmentid'] == $department['departmentid']) { ?>
												<div class="chip-circle"><?php echo html_entity_decode($staff_department['name']); ?></div>
										<?php }
										}
										?>
									<?php } ?>
								</div>
							<?php } ?>

						</li>
					</ul>
				</div>

				<div class="card-header">
					<div class="float-left">
						<br>
						<h4 class="card-title text-left"><?php echo _l('hr_team_manage') . ':  ' . staff_profile_image($member->team_manage, ['staff-profile-image-small']) . '  ' . get_staff_full_name($member->team_manage) ?></h4>
					</div>
					<div class="clearfix"></div>
				</div>

			</div>

		</div>
		<div class="col-md-7">

			<div class="col-md-12">
				<h4 class="bold"><?php echo 'Personal Details'; ?></h4>

				<table class="table border table-striped ">
					<tbody>
						<tr class="project-overview">
							<td class="bold"><?php echo 'DOB on document'; ?></td>
							<td><?php echo html_entity_decode($member->birthday); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'DOB Original'; ?></td>
							<td><?php echo html_entity_decode($member->dob_original); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Blood Group'; ?></td>
							<td><?php echo html_entity_decode($member->blood_group); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Contact 1'; ?></td>
							<td><?php echo html_entity_decode($member->phonenumber); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Contact 2'; ?></td>
							<td><?php echo html_entity_decode($member->contact_2); ?></td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Contact 2'; ?></td>
							<td><?php echo html_entity_decode($member->contact_2); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'City'; ?></td>
							<td><?php echo html_entity_decode($member->city); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Address'; ?></td>
							<td><?php echo html_entity_decode($member->current_address); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Pin Code'; ?></td>
							<td><?php echo html_entity_decode($member->pin_code); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Emergency Contact Number'; ?></td>
							<td><?php echo html_entity_decode($member->e_contact); ?></td>
						</tr>

						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Emergency Contact Person'; ?></td>
							<td><?php echo html_entity_decode($member->e_person); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Aadhaar card'; ?></td>
							<td><?php if (isset($identification)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $identification; ?>" target="_blank"><?php echo  $identification; ?></a></p>
								<?php } ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'PAN Card'; ?></td>
							<td><?php echo html_entity_decode($member->pan); ?></td>
						</tr>


						<!-- <tr class="project-overview">
							<td class="bold" width="40%"><?php echo _l('hr_hr_job_position'); ?></td>
							<td>
								<?php
								if ($member->job_position > 0) {
									$job_position_name = html_entity_decode(hr_profile_get_job_position_name($member->job_position))
								?>
									<a href="<?php echo admin_url() . 'hr_profile/job_position_view_edit/' . $member->job_position; ?>"><?php echo $job_position_name; ?></a>
								<?php
								}

								?>
							</td>
						</tr> -->


					</tbody>
				</table>
			</div>


			<div class="col-md-12">
				<h4><?php echo 'Official Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo 'Emp Id'; ?></td>
							<td><?php echo html_entity_decode($member->staff_identifi); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('hr_hr_staff_name'); ?></td>
							<td><?php echo html_entity_decode($member->firstname . ' ' . $member->lastname); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Official Email'; ?></td>
							<td><?php echo _d($member->email); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Skype ID'; ?></td>
							<td><?php echo _l($member->skype); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Date of joining'; ?></td>
							<td><?php echo html_entity_decode($member->doj); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Date of Assessment'; ?></td>
							<td><?php echo html_entity_decode($member->doa); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Quaterly Bonus Due'; ?></td>
							<td><?php echo html_entity_decode($member->q_bonus); ?></td>
						</tr>


						<tr class="project-overview">
							<td class="bold"><?php echo 'bi annual bonus due'; ?></td>
							<td><?php echo html_entity_decode($member->b_bonus); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Is Flexible Timing?'; ?></td>
							<td><?php echo html_entity_decode($member->f_time); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Shift time'; ?></td>
							<td><?php echo html_entity_decode($member->s_time); ?></td>
						</tr>

					</tbody>
				</table>
			</div>
			<div class="col-md-12">
				<br>
				<h4><?php echo  'Compliances'; ?></h4>
				<table class="table border table-striped ">
					<tbody>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo 'ESI/IP Number'; ?></td>
							<td><?php echo html_entity_decode($member->esi_ip); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo 'PF/UAN Number'; ?></td>
							<td><?php echo html_entity_decode($member->pf); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Official Email'; ?></td>
							<td><?php echo _d($member->email); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Medical insurance ID'; ?></td>
							<td><?php echo _l($member->medical_insurance); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Appointment letter issued?'; ?></td>
							<td><?php echo html_entity_decode($member->appointment_letter); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'COI Letter Signed?'; ?></td>
							<td><?php echo html_entity_decode($member->coi_letter); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'NDA Signed?'; ?></td>
							<td><?php echo html_entity_decode($member->nda); ?></td>
						</tr>


						<tr class="project-overview">
							<td class="bold"><?php echo 'Policy document shared?'; ?></td>
							<td><?php echo html_entity_decode($member->policy_document); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Biometric Enrollment?'; ?></td>
							<td><?php echo html_entity_decode($member->bio_enroll); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Joining kit provided?'; ?></td>
							<td><?php echo html_entity_decode($member->join_kit); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'ID card issued?'; ?></td>
							<td><?php echo html_entity_decode($member->id_card); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'BGV Completed?'; ?></td>
							<td><?php echo html_entity_decode($member->bgv); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-12">
				<h4><?php echo 'Team Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Departments'; ?></td>
							<td>
								<?php if (count($staff_departments) > 0) {
								?>
									<div class="form-group mtop10">
										<div class="clearfix"></div>
										<?php
										foreach ($departments as $department) {
										?>
											<?php
											foreach ($staff_departments as $staff_department) {
												if ($staff_department['departmentid'] == $department['departmentid']) { ?>
													<?php echo html_entity_decode($staff_department['name']); ?>
											<?php }
											}
											?>
										<?php } ?>
									</div>
								<?php } ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Designation'; ?></td>
							<td><?php
								if ($member->job_position > 0) {
									$job_position_name = html_entity_decode(hr_profile_get_job_position_name($member->job_position))
								?>
									<a href="<?php echo admin_url() . 'hr_profile/job_position_view_edit/' . $member->job_position; ?>"><?php echo $job_position_name; ?></a>
								<?php
								}

								?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Reporting person'; ?></td>
							<td><?php echo html_entity_decode(get_staff_full_name($member->team_manage)); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'User Role'; ?></td>
							<td><?php
								$this->load->model('roles_model');
								echo html_entity_decode($this->roles_model->get($member->role)->name);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'User Type'; ?></td>
							<td><?php echo html_entity_decode($member->user_type); ?></td>
						</tr>

					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo 'Family Member Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Father Name'; ?></td>
							<td>

								<?php echo $father_name; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Father DOB'; ?></td>
							<td>
								<?php echo $father_DOB; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Father Residing with self?'; ?></td>
							<td>
								<?php echo $father_residing; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Mother Name'; ?></td>
							<td>
								<?php echo $mother_name; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Mother DOB'; ?></td>
							<td>
								<?php echo $mother_DOB; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Mother Residing with self?'; ?></td>
							<td>
								<?php echo $mother_residing; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Son/Daughter Name'; ?></td>
							<td>
								<?php echo $child_name; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Son/Daughter DOB'; ?></td>
							<td>
								<?php echo $child_DOB; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Son/Daughter Residing with self?'; ?></td>
							<td>
								<?php echo $child_residing; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Nominee Name'; ?></td>
							<td>
								<?php echo $nominee; ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo 'Qualification Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo '10th Board/Degree'; ?></td>
							<td>
								<?php echo $_10_board; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'School Name'; ?></td>
							<td>
								<?php echo $_10_school_name; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Passed Year'; ?></td>
							<td>
								<?php echo $_10_passed_year; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo '10th Percentage'; ?></td>
							<td>
								<?php echo $_10_percentage; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Grade'; ?></td>
							<td>
								<?php echo $_10_grade; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Field of Study'; ?></td>
							<td>
								<?php echo $_10_field; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo '12th Board/Degree'; ?></td>
							<td>
								<?php echo $_12_board; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Field of Study'; ?></td>
							<td>
								<?php echo $_12_field; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Passed Year'; ?></td>
							<td>
								<?php echo $_12_passed_year; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo '12th Percentage'; ?></td>
							<td>
								<?php echo $_12_percentage; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Grade'; ?></td>
							<td>
								<?php echo $_12_grade; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Institute/University'; ?></td>
							<td>
								<?php echo $_12_institute; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo '10th Marksheet'; ?></td>
							<td>
								<?php if (isset($_10_marksheet)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $_10_marksheet; ?>" target="_blank"><?php echo  $_10_marksheet; ?></a></p>
								<?php } ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo '12th Marksheet'; ?></td>
							<td>
								<?php if (isset($_12_marksheet)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $_12_marksheet; ?>" target="_blank"><?php echo  $_12_marksheet; ?></a></p>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo 'Graduation Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Graduation Institute/University'; ?></td>
							<td>

								<?php echo $graduation_university_name; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Passed Year'; ?></td>
							<td>
								<?php echo $graduation_passed_year; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Post Graduation Institute/University'; ?></td>
							<td>
								<?php echo $post_graduation_university_name; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Passed Year'; ?></td>
							<td>
								<?php echo $post_graduation_passed_year; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Graduation Marksheet'; ?></td>
							<td>
								<?php if (isset($graduation_marksheet)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $graduation_marksheet; ?>" target="_blank"><?php echo  $graduation_marksheet; ?></a></p>
								<?php } ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Post Graduation Marksheet'; ?></td>
							<td>
								<?php if (isset($post_graduation_marksheet)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $post_graduation_marksheet; ?>" target="_blank"><?php echo  $post_graduation_marksheet; ?></a></p>
								<?php } ?>
							</td>
						</tr>

					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo 'Previous Organization Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Designation'; ?></td>
							<td>

								<?php echo $previous_designation; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Select Year'; ?></td>
							<td>
								<?php echo $previous_year; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Emp ID'; ?></td>
							<td>
								<?php echo $previous_emp_id; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Net Pay'; ?></td>
							<td>
								<?php echo $previous_pay; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Reporting Person Name'; ?></td>
							<td>
								<?php echo $previous_person_name; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Reporting Person Designation'; ?></td>
							<td>
								<?php echo $previous_person_designation; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Reporting Person Contact'; ?></td>
							<td>
								<?php echo $previous_person_contact; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Offer Letter, Salary Slips'; ?></td>
							<td>
								<?php if (isset($previous_uploads)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $previous_uploads; ?>" target="_blank"><?php echo  $previous_uploads; ?></a></p>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>


			<div class="col-md-12">
				<h4><?php echo 'Before 1 Year Organization Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Designation'; ?></td>
							<td>

								<?php echo $before_designation; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Select Year'; ?></td>
							<td>
								<?php echo $before_year; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Emp ID'; ?></td>
							<td>
								<?php echo $before_emp_id; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Net Pay'; ?></td>
							<td>
								<?php echo $before_pay; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Reporting Person Name'; ?></td>
							<td>
								<?php echo $before_reporting_person; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Reporting Person Designation'; ?></td>
							<td>
								<?php echo $before_reporting_designation; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Reporting Person Contact'; ?></td>
							<td>
								<?php echo $before_reporting_contact; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Offer Letter, Salary Slips'; ?></td>
							<td>
								<?php if (isset($before_uploads)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $before_uploads; ?>" target="_blank"><?php echo  $before_uploads; ?></a></p>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo 'Declaration'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Declaration Date'; ?></td>
							<td>

								<?php echo $declaration_date; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Declaration Place'; ?></td>
							<td>
								<?php echo $declaration_place; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Signature'; ?></td>
							<td>
								<?php if (isset($declaration_signature)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $declaration_signature; ?>" target="_blank"><?php echo  $declaration_signature; ?></a></p>
								<?php } ?>
							</td>
						</tr>


					</tbody>
				</table>
			</div>


			<div class="col-md-12">
				<h4><?php echo 'Background Verification Details'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Emp Name'; ?></td>
							<td>
								<?php echo $bgv_emp_name; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Postion'; ?></td>
							<td>
								<?php echo $bgv_emp_position; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Emp Code'; ?></td>
							<td>
								<?php echo $bgv_emp_code; ?>
							</td>
						</tr>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Date of Emp'; ?></td>
							<td>
								<?php echo $bgv_emp_date; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Contact Number'; ?></td>
							<td>
								<?php echo $bgv_emp_number; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Address'; ?></td>
							<td>
								<?php echo $bgv_emp_address; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Department'; ?></td>
							<td>
								<?php echo $bgv_emp_department; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Select Year'; ?></td>
							<td>
								<?php echo $bgv_emp_year; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'In Hand Salary'; ?></td>
							<td>
								<?php echo $bgv_emp_hand_salary; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Gross Salary'; ?></td>
							<td>
								<?php echo $bgv_emp_gross_salary; ?>
							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Location'; ?></td>
							<td>
								<?php echo $bgv_emp_location; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Company Documents Status'; ?></td>
							<td>
								<?php echo $bgv_emp_previous_document; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Supervisor Name'; ?></td>
							<td>
								<?php echo $bgv_emp_supervisior; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'HR Email Address'; ?></td>
							<td>
								<?php echo $bgv_emp_hr_email; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Reason for leaving'; ?></td>
							<td>
								<?php echo $bgv_emp_leaving_reason; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'gap in work history'; ?></td>
							<td>
								<?php echo $bgv_emp_gap; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Gap reason'; ?></td>
							<td>
								<?php echo $bgv_emp_gap_reason; ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-12">
				<h4><?php echo 'Agreement'; ?></h4>
				<table class="table border table-striped ">
					<tbody>

						<tr class="project-overview">
							<td class="bold"><?php echo 'Applicant Name'; ?></td>
							<td>

								<?php echo $applicant_name; ?>

							</td>

						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo 'Date'; ?></td>
							<td>
								<?php echo $agreement_date; ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo 'Signature'; ?></td>
							<td>
								<?php if (isset($agreement_signature)) { ?>
									<p>Uploaded File: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $agreement_signature; ?>" target="_blank"><?php echo  $agreement_signature; ?></a></p>
								<?php } ?>
							</td>
						</tr>


					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>