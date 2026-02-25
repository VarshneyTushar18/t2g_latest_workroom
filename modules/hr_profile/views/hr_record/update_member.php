<div class="modal fade" id="appointmentModal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php
            $title = '';
            $staffid = '';
            if (isset($member)) {
               $title .= _l('hr_update_staff_profile');
               $staffid = $member->staffid;

               echo form_hidden('memberid', $staffid);
               echo form_hidden('isedit');
            } else {
               $title .= _l('add_staff_profile');
            }
            ?>
            <h4 class="modal-title"><?php echo html_entity_decode($title); ?></h4>
         </div>

         <?php echo form_open_multipart(admin_url('hr_profile/add_edit_member/' . $staffid), array('id' => 'add_edit_member')); ?>
         <div class="modal-body">
            <ul class="nav nav-tabs" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
                     <?php echo 'Personal details'; ?>
                  </a>
               </li>
               <!-- <li role="presentation">
									<a href="#tab_staff_contact" aria-controls="tab_staff_contact" role="tab" data-toggle="tab">
										<?php echo _l('hr_staff_profile_related_info'); ?>
									</a>
								</li> -->
               <li role="presentation">
                  <a href="#tab_staff_official" aria-controls="tab_staff_official" role="tab" data-toggle="tab">
                     <?php echo 'Official Details'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_compliances" aria-controls="tab_compliances" role="tab" data-toggle="tab">
                     <?php echo 'Compliances'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_team" aria-controls="tab_team" role="tab" data-toggle="tab">
                     <?php echo 'Team Details'; ?>
                  </a>
               </li>
               

               <li role="presentation">
                  <a href="#tab_fam" aria-controls="tab_fam" role="tab" data-toggle="tab">
                     <?php echo 'Family Member Details'; ?>
                  </a>
               </li>

               <li role="presentation">
                  <a href="#tab_qualification" aria-controls="tab_qualification" role="tab" data-toggle="tab">
                     <?php echo 'Qualification Details'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_graduation" aria-controls="tab_graduation" role="tab" data-toggle="tab">
                     <?php echo 'Graduation Details'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_work" aria-controls="tab_work" role="tab" data-toggle="tab">
                     <?php echo 'Work Experience Details'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_declaration" aria-controls="tab_declaration" role="tab" data-toggle="tab">
                     <?php echo 'Declaration Details'; ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_bgv" aria-controls="tab_bgv" role="tab" data-toggle="tab">
                     <?php echo 'Background Verification Details'; ?>
                  </a>
               </li>
            </ul>

            <div class="tab-content">
               <div class="manage_staff hide">
                  <?php
                  if (isset($manage_staff)) {
                     echo form_hidden('manage_staff');
                  }
                  ?>
               </div>
               <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">

                  <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'two-factor-authentication', 'active' => 0)) == 0) { ?>
                     <div class="checkbox checkbox-primary">
                        <input type="checkbox" value="1" name="two_factor_auth_enabled" id="two_factor_auth_enabled" <?php if (isset($member) && $member->two_factor_auth_enabled == 1) {
                                                                                                                        echo ' checked';
                                                                                                                     } ?>>
                        <label for="two_factor_auth_enabled"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('two_factor_authentication_info'); ?>"></i>
                           <?php echo _l('enable_two_factor_authentication'); ?></label>
                     </div>
                  <?php } ?>



                  <div class="clearfix"></div>
                  <br>
                  <div class="clearfix"></div>



                  

                  <div class="row">
                     <?php $value = (isset($member) ? $member->firstname : ''); ?>
                     <?php $attrs = (isset($member) ? array() : array('autofocus' => true)); ?>
                     <div class="col-md-6">
                        <?php echo render_input('firstname', 'hr_firstname', $value, 'text', $attrs); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->lastname : ''); ?>

                        <?php echo render_input('lastname', 'hr_lastname', $value, 'text', $attrs); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php
                        $birthday = (isset($member) ? $member->birthday : '');
                        echo render_date_input('birthday', 'DOB on document', _d($birthday)); ?>
                     </div>

                     <div class="col-md-6">
                        <?php
                        $dob_original = (isset($member) ? $member->dob_original : '');
                        echo render_date_input('dob_original', 'DOB Original', _d($dob_original)); ?>
                     </div>
                  </div>


                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                        <?php echo render_input('phonenumber', 'Contact 1', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->contact_2 : ''); ?>

                        <?php echo render_input('contact_2', 'Contact 2',  $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->city : ''); ?>
                        <?php echo render_input('city', 'City', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php
                        $value = (isset($member) ? $member->current_address : '');
                        echo render_input('current_address', 'Address', $value, 'text'); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->pin_code : ''); ?>

                        <?php echo render_input('pin_code', 'Pincode', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group" app-field-wrapper="sex">
                           <label for="">Gender</label>
                        </div>
                        <div class="col-md-6">

                           <?php $value = (isset($member) ? $member->sex : ''); ?>
                           <label for="sex" class="control-label">Male</label>

                           <input type="radio" name="sex" value="Male" <?php if ($value == 'Male') {
                                                                           echo 'checked';
                                                                        } ?>>

                           <label for="sex" class="control-label">Female</label>
                           <input type="radio" name="sex" value="Female" <?php if ($value == 'Female') {
                                                                              echo 'checked';
                                                                           } ?>>
                        </div>

                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->personal_email : ''); ?>
                        <?php echo render_input('personal_email', 'Personal Email', $value); ?>
                     </div>
                 
                     <div class="col-md-6">

                        <?php $value = (isset($member) ? $member->permanent_address : ''); ?>
                        <?php echo render_input('permanent_address', 'Permanent address', $value); ?>

                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <label for="">Marital Status</label>
                        
                        <div class="form-group" >
                           <label for="marital_status" class="control-label">Single</label>
                           <?php $value = (isset($member) ? $member->marital_status : ''); ?>

                           <input type="radio" name="marital_status" value="Single" <?php if ($value == 'Single') {
                                                                                       echo 'checked';
                                                                                    } ?>>
                       
                        
                           <label for="marital_status" class="control-label">Married</label>
                           <input type="radio" name="marital_status" value="Married" <?php if ($value == 'Married') {
                                                                                          echo 'checked';
                                                                                       } ?>>
                        
                           <label for="marital_status" class="control-label">Separated</label>
                           <input type="radio" name="marital_status" value="Separated" <?php if ($value == 'Separated') {
                                                                                          echo 'checked';
                                                                                       } ?>>
                        </div>
                     </div>

                     

                  </div>

                  <!-- <div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="sex" class="control-label"><?php echo _l('hr_sex'); ?></label>
												<select name="sex" class="selectpicker" id="sex" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
													<option value=""></option>
													<option value="<?php echo 'male'; ?>" <?php if (isset($member) && $member->sex == 'male') {
                                                                                 echo 'selected';
                                                                              } ?>><?php echo _l('male'); ?></option>
													<option value="<?php echo 'female'; ?>" <?php if (isset($member) && $member->sex == 'female') {
                                                                                    echo 'selected';
                                                                                 } ?>><?php echo _l('female'); ?></option>
												</select>
											</div>
										</div>

										
									</div> -->

                  <!-- <div class="row">
										<div class="col-md-6">
											<?php $value = (isset($member) ? $member->email : ''); ?>
											<div class="form-group" app-field-wrapper="email">
												<label for="email" class="control-label">Email</label>
												<input type="email" id="email" name="email" class="form-control" autocomplete="off" value="<?php echo html_entity_decode($value) ?>" <?php if (!is_admin() && !has_permission('hrm_hr_records', '', 'edit') && !has_permission('hrm_hr_records', '', 'create')) {
                                                                                                                                                                              echo 'disabled';
                                                                                                                                                                           }  ?>>
											</div>
										</div>
									</div> -->

                  <!-- <div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="workplace" class="control-label"><?php echo _l('hr_hr_workplace'); ?></label>
												<select name="workplace" class="selectpicker" id="workplace" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
													<option value=""></option>
													<?php foreach ($workplace as $w) { ?>

														<option value="<?php echo html_entity_decode($w['id']); ?>" <?php if (isset($member) && $member->workplace == $w['id']) {
                                                                                                         echo 'selected';
                                                                                                      } ?>><?php echo html_entity_decode($w['name']); ?></option>

													<?php } ?>
												</select>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="status_work" class="control-label"><?php echo _l('hr_status_work'); ?></label>
												<select name="status_work" class="selectpicker" id="status_work" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
													<option value="<?php echo 'working'; ?>" <?php if (isset($member) && $member->status_work == 'working') {
                                                                                    echo 'selected';
                                                                                 } ?>><?php echo _l('hr_working'); ?></option>
													<option value="<?php echo 'maternity_leave'; ?>" <?php if (isset($member) && $member->status_work == 'maternity_leave') {
                                                                                             echo 'selected';
                                                                                          } ?>><?php echo _l('hr_maternity_leave'); ?></option>
													<option value="<?php echo 'inactivity'; ?>" <?php if (isset($member) && $member->status_work == 'inactivity') {
                                                                                       echo 'selected';
                                                                                    } ?>><?php echo _l('hr_inactivity'); ?></option>
												</select>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="job_position" class="control-label"><?php echo _l('hr_hr_job_position'); ?></label>
												<select name="job_position" class="selectpicker" id="job_position" data-width="100%" data-action-box="true" data-hide-disabled="true" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
													<option value=""></option>
													<?php foreach ($positions as $p) { ?>
														<option value="<?php echo html_entity_decode($p['position_id']); ?>" <?php if (isset($member) && $member->job_position == $p['position_id']) {
                                                                                                                  echo 'selected';
                                                                                                               } ?>><?php echo html_entity_decode($p['position_name']); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>

										<div class="col-md-6">
											
											<?php if (has_permission('hrm_hr_records', '', 'edit') || has_permission('hrm_hr_records', '', 'create')) { ?>
												<?php $value = (isset($member) ? $member->team_manage : ''); ?>
												<?php echo render_select('team_manage', $list_staff, array('staffid', 'full_name'), 'hr_team_manage', $value); ?>
											<?php } ?>
											<
										</div>
									</div>

									<?php if (is_admin() || has_permission('hrm_hr_records', '', 'edit')) { ?>

										<?php
                              hooks()->do_action('staff_render_permissions');
                              $selected = '';
                              foreach ($roles_value as $role_value) {
                                 if (isset($member)) {
                                    if ($member->role == $role_value['roleid']) {
                                       $selected = $role_value['roleid'];
                                    }
                                 } else {
                                    $default_staff_role = get_option('default_staff_role');
                                    if ($default_staff_role == $role_value['roleid']) {
                                       $selected = $role_value['roleid'];
                                    }
                                 }
                              }
                              ?>

										<div class="row">
											<div class="col-md-12">
												<?php
                                    // echo '<pre>';
                                    // print_r($roles_value);
                                    // echo '</pre>';
                                    // die;

                                    ?>
												<?php echo render_select('role_v', $roles_value, array('roleid', 'name'), 'staff_add_edit_role', $selected); ?>
											</div>
										</div>
									<?php } ?>

									<div class="row">
										<div class="col-md-6">
											<?php $literacy = (isset($member) ? $member->literacy : ''); ?>
											<div class="form-group">
												<label for="literacy" class="control-label"><?php echo _l('hr_hr_literacy'); ?></label>
												<select name="literacy" id="literacy" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('hr_not_required'); ?>">
													<option value=""></option>
													<option value="primary_level" <?php if ($literacy == 'primary_level') {
                                                                        echo 'selected';
                                                                     } ?>><?php echo _l('hr_primary_level'); ?></option>
													<option value="intermediate_level" <?php if ($literacy == 'intermediate_level') {
                                                                              echo 'selected';
                                                                           } ?>><?php echo _l('hr_intermediate_level'); ?></option>
													<option value="college_level" <?php if ($literacy == 'college_level') {
                                                                        echo 'selected';
                                                                     } ?>><?php echo _l('hr_college_level'); ?></option>
													<option value="masters" <?php if ($literacy == 'masters') {
                                                                  echo 'selected';
                                                               } ?>><?php echo _l('hr_masters'); ?></option>
													<option value="doctor" <?php if ($literacy == 'doctor') {
                                                                  echo 'selected';
                                                               } ?>><?php echo _l('hr_Doctor'); ?></option>
													<option value="bachelor" <?php if ($literacy == 'bachelor') {
                                                                     echo 'selected';
                                                                  } ?>><?php echo _l('hr_bachelor'); ?></option>
													<option value="engineer" <?php if ($literacy == 'engineer') {
                                                                     echo 'selected';
                                                                  } ?>><?php echo _l('hr_Engineer'); ?></option>
													<option value="university" <?php if ($literacy == 'university') {
                                                                     echo 'selected';
                                                                  } ?>><?php echo _l('hr_university'); ?></option>
													<option value="intermediate_vocational" <?php if ($literacy == 'intermediate_vocational') {
                                                                                    echo 'selected';
                                                                                 } ?>><?php echo _l('hr_intermediate_vocational'); ?></option>
													<option value="college_vocational" <?php if ($literacy == 'college_vocational') {
                                                                              echo 'selected';
                                                                           } ?>><?php echo _l('hr_college_vocational'); ?></option>
													<option value="in-service" <?php if ($literacy == 'in-service') {
                                                                     echo 'selected';
                                                                  } ?>><?php echo _l('hr_in-service'); ?></option>
													<option value="high_school" <?php if ($literacy == 'high_school') {
                                                                        echo 'selected';
                                                                     } ?>><?php echo _l('hr_high_school'); ?></option>
													<option value="intermediate_level_pro" <?php if ($literacy == 'intermediate_level_pro') {
                                                                                 echo 'selected';
                                                                              } ?>><?php echo _l('hr_intermediate_level_pro'); ?></option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="hourly_rate"><?php echo _l('staff_hourly_rate'); ?></label>
												<div class="input-group">
													<input type="number" name="hourly_rate" value="<?php if (isset($member)) {
                                                                                          echo html_entity_decode($member->hourly_rate);
                                                                                       } else {
                                                                                          echo 0;
                                                                                       } ?>" id="hourly_rate" class="form-control">
													<span class="input-group-addon">
														<?php echo html_entity_decode($base_currency->symbol); ?>
													</span>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<?php if (get_option('disable_language') == 0) { ?>
												<div class="form-group">
													<label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?></label>
													<select name="default_language" data-live-search="true" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
														<option value=""><?php echo _l('system_default_string'); ?></option>
														<?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                                             $selected = '';
                                             if (isset($member)) {
                                                if ($member->default_language == $availableLanguage) {
                                                   $selected = 'selected';
                                                }
                                             }
                                          ?>
															<option value="<?php echo html_entity_decode($availableLanguage); ?>" <?php echo html_entity_decode($selected); ?>><?php echo ucfirst($availableLanguage); ?></option>
														<?php } ?>
													</select>
												</div>
											<?php } ?>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="direction"><?php echo _l('document_direction'); ?></label>
												<select class="selectpicker" data-none-selected-text="<?php echo _l('system_default_string'); ?>" data-width="100%" name="direction" id="direction">
													<option value="" <?php if (isset($member) && empty($member->direction)) {
                                                            echo 'selected';
                                                         } ?>></option>
													<option value="ltr" <?php if (isset($member) && $member->direction == 'ltr') {
                                                               echo 'selected';
                                                            } ?>>LTR</option>
													<option value="rtl" <?php if (isset($member) && $member->direction == 'rtl') {
                                                               echo 'selected';
                                                            } ?>>RTL</option>
												</select>
											</div>
										</div>
									</div>





									<?php if (is_admin() || has_permission('hrm_hr_records', '', 'edit')) { ?>
										<div class="form-group">
											<div class="row">
												<div class="col-md-6">
													<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('staff_email_signature_help'); ?>"></i>
													<?php $value = (isset($member) ? $member->email_signature : ''); ?>
													<?php echo render_textarea('email_signature', 'settings_email_signature', $value, ['data-entities-encode' => 'true']); ?>
												</div>
												<div class="col-md-6">
													<?php
                                       $orther_infor = (isset($member) ? $member->orther_infor : '');
                                       echo render_textarea('orther_infor', 'hr_orther_infor', $orther_infor); ?>
												</div>
											</div>

											<br>
											<?php if (count($departments) > 0) { ?>
												<label for="departments"><?php echo _l('staff_add_edit_departments'); ?></label>
											<?php } ?>

											<?php foreach ($departments as $department) { ?>
												<div class="checkbox checkbox-primary">
													<?php
                                       $checked = '';
                                       if (isset($member)) {
                                          foreach ($staff_departments as $staff_department) {
                                             if ($staff_department['departmentid'] == $department['departmentid']) {
                                                $checked = ' checked';
                                             }
                                          }
                                       }
                                       ?>
													<input type="checkbox" id="dep_<?php echo html_entity_decode($department['departmentid']); ?>" name="departments[]" value="<?php echo html_entity_decode($department['departmentid']); ?>" <?php echo html_entity_decode($checked); ?>>
													<label for="dep_<?php echo html_entity_decode($department['departmentid']); ?>"><?php echo html_entity_decode($department['name']); ?></label>
												</div>
											<?php } ?>
										</div>
									<?php } ?> -->

                  <?php $rel_id = (isset($member) ? $member->staffid : false); ?>
                  <?php echo render_custom_fields('staff', $rel_id); ?>

                  <div class="row">
                     <div class="col-md-12">
                        <hr class="hr-10" />


                        <?php if (!isset($member) && total_rows(db_prefix() . 'emailtemplates', array('slug' => 'new-staff-created', 'active' => 0)) === 0) { ?>
                           <div class="checkbox checkbox-primary">
                              <input type="checkbox" name="send_welcome_email" id="send_welcome_email" checked>
                              <label for="send_welcome_email"><?php echo _l('staff_send_welcome_email'); ?></label>
                           </div>
                        <?php } ?>
                     </div>
                  </div>

                  <?php if (!isset($member) || is_admin() || !is_admin() && $member->admin == 0) { ?>
                     <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                     <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                     <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1" />
                     <div class="clearfix form-group"></div>
                     <label for="password" class="control-label"><?php echo _l('staff_add_edit_password'); ?></label>
                     <div class="input-group">
                        <input type="password" class="form-control password" name="password" autocomplete="off">
                        <span class="input-group-addon">
                           <a href="#password" class="show_password" onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
                        </span>
                        <span class="input-group-addon">
                           <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
                        </span>
                     </div>
                     <?php if (isset($member)) { ?>
                        <p class="text-muted"><?php echo _l('staff_add_edit_password_note'); ?></p>
                        <?php if ($member->last_password_change != NULL) { ?>
                           <?php echo _l('staff_add_edit_password_last_changed'); ?>:
                           <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($member->last_password_change); ?>">
                              <?php echo time_ago($member->last_password_change); ?>
                           </span>
                     <?php }
                     } ?>
                  <?php } ?>

               </div>



               <div role="tabpanel" class="tab-pane hide" id="staff_permissions">
                  <div class="table-responsive">

                     <table class="table table-bordered roles no-margin">
                        <thead>
                           <tr>
                              <th>Feature</th>
                              <th>Capabilities</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
                           if (isset($member)) {
                              $is_admin = is_admin($member->staffid);
                           }

                           foreach (get_available_staff_permissions($funcData) as $feature => $permission) {
                           ?>
                              <tr data-name="<?php echo html_entity_decode($feature); ?>">
                                 <td>
                                    <b><?php echo html_entity_decode($permission['name']); ?></b>
                                 </td>
                                 <td>
                                    <?php
                                    if (isset($permission['before'])) {
                                       echo html_entity_decode($permission['before']);
                                    }
                                    ?>
                                    <?php foreach ($permission['capabilities'] as $capability => $name) {
                                       $checked = '';
                                       $disabled = '';
                                       if ((isset($is_admin) && $is_admin) ||
                                          (is_array($name) && isset($name['not_applicable']) && $name['not_applicable']) ||
                                          (
                                             ($capability == 'view_own' || $capability == 'view'
                                                && array_key_exists('view_own', $permission['capabilities']) && array_key_exists('view', $permission['capabilities']))
                                             &&
                                             ((isset($member)
                                                && staff_can(($capability == 'view' ? 'view_own' : 'view'), $feature, $member->staffid))
                                                ||
                                                (isset($role)
                                                   && has_role_permission($role->roleid, ($capability == 'view' ? 'view_own' : 'view'), $feature))
                                             )
                                          )
                                       ) {
                                          $disabled = ' disabled ';
                                       } else if ((isset($member) && staff_can($capability, $feature, $member->staffid))
                                          || isset($role) && has_role_permission($role->roleid, $capability, $feature)
                                       ) {
                                          $checked = ' checked ';
                                       }
                                    ?>
                                       <div class="checkbox">
                                          <input <?php if ($capability == 'view') { ?> data-can-view <?php } ?> <?php if ($capability == 'view_own') { ?> data-can-view-own <?php } ?> <?php if (is_array($name) && isset($name['not_applicable']) && $name['not_applicable']) { ?> data-not-applicable="true" <?php } ?> type="checkbox" <?php echo html_entity_decode($checked); ?> class="capability" id="<?php echo html_entity_decode($feature . '_' . $capability); ?>" name="permissions[<?php echo html_entity_decode($feature); ?>][]" value="<?php echo html_entity_decode($capability); ?>" <?php echo html_entity_decode($disabled); ?>>
                                          <label for="<?php echo html_entity_decode($feature . '_' . $capability); ?>">
                                             <?php echo !is_array($name) ? $name : $name['name']; ?>
                                          </label>
                                          <?php
                                          if (isset($permission['help']) && array_key_exists($capability, $permission['help'])) {
                                             echo '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . $permission['help'][$capability] . '"></i>';
                                          }
                                          ?>
                                       </div>
                                    <?php } ?>
                                    <?php
                                    if (isset($permission['after'])) {
                                       echo html_entity_decode($permission['after']);
                                    }
                                    ?>
                                 </td>
                              </tr>
                           <?php } ?>
                        </tbody>
                     </table>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane " id="tab_staff_official">


                  <div class="col-md-12">
                     <div class="picture-container pull-left">
                        <div class="picture pull-left">
                           <?php echo staff_profile_image($member->staffid, array('img', 'img-responsive', 'staff-profile-image-thumb', 'picture-src'), 'thumb', ['id' => 'wizardPicturePreview']); ?>
                           <?php
                           echo staff_profile_image($member->staffid, array('img', 'img-responsive', 'staff-profile-image-thumb', 'picture-src'), 'thumb', ['id' => 'wizardPicturePreview']);
                           ?>
                           <input type="file" name="profile_image" class="form-control" id="profile_image" accept=".png, .jpg, .jpeg">
                        </div>
                     </div>
                  </div>
                  <div class="row">


                     <div class="col-md-12">
                        <?php $hr_codes = (isset($member) ? $member->staff_identifi : $staff_code); ?>
                        <div class="form-group" app-field-wrapper="staff_identifi">
                           <label for="staff_identifi" class="control-label"><?php echo 'Emp Id'; ?></label>
                           <input type="text" id="staff_identifi" name="staff_identifi" class="form-control" value="<?php echo html_entity_decode($hr_codes) ?>" aria-invalid="false" <?php if (!is_admin() && !has_permission('hrm_hr_records', '', 'edit') && !has_permission('hrm_hr_records', '', 'create')) {
                                                                                                                                                                                          echo 'disabled';
                                                                                                                                                                                       }  ?>>
                        </div>
                     </div>

                  </div>

                  

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->email : ''); ?>
                        <div class="form-group" app-field-wrapper="email">
                           <label for="email" class="control-label">Email</label>
                           <input type="email" id="email" name="email" class="form-control" autocomplete="off" value="<?php echo html_entity_decode($value) ?>" <?php if (!is_admin() && !has_permission('hrm_hr_records', '', 'edit') && !has_permission('hrm_hr_records', '', 'create')) {
                                                                                                                                                                     echo 'disabled';
                                                                                                                                                                  }  ?>>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="skype" class="control-label"><i class="fa fa-skype"></i> <?php echo _l('staff_add_edit_skype'); ?></label>
                           <input type="text" class="form-control" name="skype" value="<?php if (isset($member)) {
                                                                                          echo html_entity_decode($member->skype);
                                                                                       } ?>">
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->doj : ''); ?>
                        <?php echo render_date_input('doj', 'Date of joining', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->doa : ''); ?>
                        <?php echo render_date_input('doa', 'Date of Assessment', $value); ?>
                     </div>


                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->q_bonus : ''); ?>
                        <?php echo render_input('q_bonus', 'Quaterly Bonus Due', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->b_bonus : ''); ?>
                        <?php echo render_input('b_bonus', 'bi annual bonus due', $value); ?>
                     </div>


                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->f_time : ''); ?>

                        <?php echo render_select('f_time', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'Is Flexible Timing?', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->s_time : ''); ?>
                        <?php echo render_input('s_time', 'Shift time', $value); ?>
                     </div>


                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->permanent_address : ''); ?>
                        <?php echo render_input('permanent_address', 'Permanent address', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bank_ac_no : ''); ?>
                        <?php echo render_input('bank_ac_no', 'Bank Account Number', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bank_name : ''); ?>
                        <?php echo render_input('bank_name', 'Bank name', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bank_address : ''); ?>
                        <?php echo render_input('bank_address', 'Bank address', $value); ?>
                     </div>
                  </div>


                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->isfc_code : ''); ?>
                        <?php echo render_input('isfc_code', 'IFSC Code', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->pan : ''); ?>
                        <?php echo render_input('pan', 'PAN Card', $value); ?>
                     </div>

                  </div>



                  <div class="row">

                     <div class="col-md-6">
                        <?php
                        $blood_group = (isset($member) ? $member->blood_group : '');

                        $blood_group_array = array(
                           array('field_val' => 'A+', 'field_label' => 'A+'),
                           array('field_val' => 'B+', 'field_label' => 'B+'),
                           array('field_val' => 'O+', 'field_label' => 'O+'),
                           array('field_val' => 'AB+', 'field_label' => 'AB+'),
                           array('field_val' => 'A-', 'field_label' => 'A-'),
                           array('field_val' => 'B-', 'field_label' => 'B-'),
                           array('field_val' => 'O-', 'field_label' => 'O-'),
                           array('field_val' => 'AB-', 'field_label' => 'AB-'),
                        );
                        ?>
                        <?php echo render_select('blood_group', $blood_group_array, array('field_val', 'field_label'), 'Blood Group?', $blood_group); ?>

                     </div>


                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->e_contact : ''); ?>
                        <?php echo render_input('e_contact', 'Emergency Contact Number', $value); ?>
                     </div>


                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->e_person : ''); ?>

                        <?php echo render_input('e_person', 'Emergency Contact Name', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->relation : ''); ?>
                        <?php echo render_input('relation', 'Emergency Contact Relation', $value); ?>
                     </div>

                  </div>

                  <div class="row">

                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->referr_type : '');

                        $referr_type_array = array(
                           array('field_val' => 'Walk-In', 'field_label' => 'Walk-In'),
                           array('field_val' => 'Referral', 'field_label' => 'Referral'),
                           array('field_val' => 'Newspaper Ad', 'field_label' => 'Newspaper Ad'),
                           array('field_val' => 'Facebook', 'field_label' => 'Facebook'),
                           array('field_val' => 'Twitter', 'field_label' => 'Twitter'),
                           array('field_val' => 'Other', 'field_label' => 'Other'),
                        );
                        ?>
                        <?php echo render_select('referr_type', $referr_type_array, array('field_val', 'field_label'), 'Referrence Type', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->identification : '');
                        ?>
                        <?php echo render_input('identification', 'Adhaar Card', $value ); ?>

                        
                           <!-- <p>Uploaded Adhaar card: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Adhaar card: <?php if ($value) { ?> <a href="<?php echo prep_url($value); ?>" target="_blank"><?php echo  'view document'; ?></a> <?php } ?> </p>

                        
                     </div>
                  </div>

               </div>

               <div role="tabpanel" class="tab-pane " id="tab_compliances">

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->esi_ip : ''); ?>
                        <?php echo render_input('esi_ip', 'ESI/IP Number', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->pf : ''); ?>
                        <?php echo render_input('pf', 'PF/UAN Number', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->medical_insurance : ''); ?>
                        <?php echo render_input('medical_insurance', 'Medical insurance ID', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->appointment_letter : ''); ?>
                        <?php echo render_select('appointment_letter', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'Appointment letter issued?', $value); ?>
                     </div>

                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->coi_letter : ''); ?>
                        <?php echo render_select('coi_letter', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'COI Letter Signed?', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->nda : ''); ?>
                        <?php echo render_select('nda', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'NDA Signed?', $value); ?>
                     </div>

                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->policy_document : ''); ?>

                        <?php echo render_select('policy_document', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'Policy document shared', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bio_enroll : ''); ?>

                        <?php echo render_select('bio_enroll', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'Biometric Enrollment?', $value); ?>
                     </div>

                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->join_kit : ''); ?>

                        <?php echo render_select('join_kit', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'Joining kit provided?', $value); ?>
                     </div>

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->id_card : ''); ?>

                        <?php echo render_select('id_card', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'ID card issued?', $value); ?>
                     </div>

                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bgv : ''); ?>

                        <?php echo render_select('bgv', array(array('field_val' => 'yes', 'field_label' => 'yes'), array('field_val' => 'no', 'field_label' => 'no')), array('field_label', 'field_label'), 'BGV Completed?', $value); ?>
                     </div>



                  </div>

               </div>

               <div role="tabpanel" class="tab-pane " id="tab_team">



                  <div class="row">
                     <div class="col-md-6">

                        <?php if (count($departments) > 0) { ?>
                           <label for="departments"><?php echo 'Department'; ?></label>
                        <?php } ?>

                        <?php foreach ($departments as $department) { ?>
                           <div class="checkbox checkbox-primary">
                              <?php
                              $checked = '';
                              if (isset($member)) {
                                 foreach ($staff_departments as $staff_department) {
                                    if ($staff_department['departmentid'] == $department['departmentid']) {
                                       $checked = ' checked';
                                    }
                                 }
                              }
                              ?>
                              <input type="checkbox" id="dep_<?php echo html_entity_decode($department['departmentid']); ?>" name="departments[]" value="<?php echo html_entity_decode($department['departmentid']); ?>" <?php echo html_entity_decode($checked); ?>>
                              <label for="dep_<?php echo html_entity_decode($department['departmentid']); ?>"><?php echo html_entity_decode($department['name']); ?></label>
                           </div>
                        <?php } ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="job_position" class="control-label"><?php echo 'Designation' ?></label>
                           <select name="job_position" class="selectpicker" id="job_position" data-width="100%" data-action-box="true" data-hide-disabled="true" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                              <option value=""></option>
                              <?php foreach ($positions as $p) { ?>
                                 <option value="<?php echo html_entity_decode($p['position_id']); ?>" <?php if (isset($member) && $member->job_position == $p['position_id']) {
                                                                                                         echo 'selected';
                                                                                                      } ?>><?php echo html_entity_decode($p['position_name']); ?></option>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <!--teamanage -->
                        <?php if (has_permission('hrm_hr_records', '', 'edit') || has_permission('hrm_hr_records', '', 'create')) { ?>
                           <?php $value = (isset($member) ? $member->team_manage : ''); ?>
                           <?php echo render_select('team_manage', $list_staff, array('staffid', 'full_name'), 'Reporting person', $value); ?>
                        <?php } ?>
                        <!--teamanage -->
                     </div>
                     <?php if (is_admin() || has_permission('hrm_hr_records', '', 'edit')) { ?>

                        <?php
                        hooks()->do_action('staff_render_permissions');
                        $selected = '';
                        foreach ($roles_value as $role_value) {
                           if (isset($member)) {
                              if ($member->role == $role_value['roleid']) {
                                 $selected = $role_value['roleid'];
                              }
                           } else {
                              $default_staff_role = get_option('default_staff_role');
                              if ($default_staff_role == $role_value['roleid']) {
                                 $selected = $role_value['roleid'];
                              }
                           }
                        }
                        ?>


                        <div class="col-md-6">
                           <?php
                           // echo '<pre>';
                           // print_r($roles_value);
                           // echo '</pre>';
                           // die;

                           ?>
                           <?php echo render_select('role_v', $roles_value, array('roleid', 'name'), 'User role', $selected); ?>
                        </div>

                     <?php } ?>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->user_type : ''); ?>
                        <?php echo render_input('user_type', 'User Type', $value) ?>
                     </div>

                  </div>

               </div>

            
      
               <div role="tabpanel" class="tab-pane " id="tab_fam">

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->father_name : ''); ?>
                        <?php echo render_input('father_name', 'Father name', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->father_DOB : ''); ?>
                        <?php echo render_input('father_DOB', 'Father DOB', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->father_residing : ''); ?>
                        <?php echo render_input('father_residing', 'Father Residing', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->mother_name : ''); ?>
                        <?php echo render_input('mother_name', 'Mother Name', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->mother_DOB : ''); ?>
                        <?php echo render_input('mother_DOB', 'Mother DOB', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->mother_residing : ''); ?>
                        <?php echo render_input('mother_residing', 'Mother Residing', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->child_name : ''); ?>
                        <?php echo render_input('child_name', 'Child Name', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->child_DOB : ''); ?>
                        <?php echo render_input('child_DOB', 'Child DOB', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->child_residing : ''); ?>
                        <?php echo render_input('child_residing', 'Child Residing', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-12">
                        <?php $value = (isset($member) ? $member->nominee : ''); ?>
                        <?php echo render_input('nominee', 'Nominee', $value); ?>
                     </div>
                  </div>
               </div>

               <div role="tabpanel" class="tab-pane " id="tab_qualification">
                  <h2>10th Class Details</h2>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_board : ''); ?>
                        <?php echo render_input('_10_board', 'Board/degree', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_school_name : ''); ?>
                        <?php echo render_input('_10_school_name', 'School Name', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_passed_year : ''); ?>
                        <?php echo render_input('_10_passed_year', 'Passed Year', $value); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_percentage : ''); ?>
                        <?php echo render_input('_10_percentage', 'Percentage', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_grade : ''); ?>
                        <?php echo render_input('_10_grade', 'Grade', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_10_field : ''); ?>
                        <?php echo render_input('_10_field', 'Field of study', $value); ?>
                     </div>
                  </div>

                  <h2>12th Class Details</h2>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_board : ''); ?>
                        <?php echo render_input('_12_board', 'Board/degree', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_institute : ''); ?>
                        <?php echo render_input('_12_institute', 'School Name', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_passed_year : ''); ?>
                        <?php echo render_input('_12_passed_year', 'Passed Year', $value); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_percentage : ''); ?>
                        <?php echo render_input('_12_percentage', 'Percentage', $value); ?>
                     </div>

                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_grade : ''); ?>
                        <?php echo render_input('_12_grade', 'Grade', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->_12_field : ''); ?>
                        <?php echo render_input('_12_field', 'Field of study', $value); ?>
                     </div>
                  </div>
                  <hr>
                  <div class="row">
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->_10_marksheet : '');
                        ?>
                        <?php echo render_input('_10_marksheet', '10th Marksheet', $value); ?>

                        
                           <!-- <p>Uploaded Marksheet: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Marksheet: <?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a><?php } ?></p>

                        
                     </div>
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->_12_marksheet : '');
                        ?>
                        <?php echo render_input('_12_marksheet', '12th Marksheet', $value); ?>

                        
                           <!-- <p>Uploaded Marksheet: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Marksheet: <?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a><?php } ?> </p>

                        
                     </div>

                  </div>

               </div>

               <div role="tabpanel" class="tab-pane " id="tab_graduation">
                  <h2>Graduation Details</h2>
                  <div class="row">

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->graduation_university_name : ''); ?>
                        <?php echo render_input('graduation_university_name', 'University Name', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->graduation_passed_year : ''); ?>
                        <?php echo render_input('graduation_passed_year', 'Passed Year', $value); ?>
                     </div>

                  </div>
                  <h2>Post Graduation Details</h2>
                  <div class="row">

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->post_graduation_university_name : ''); ?>
                        <?php echo render_input('post_graduation_university_name', 'University Name', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->post_graduation_passed_year : ''); ?>
                        <?php echo render_input('post_graduation_passed_year', 'Passed Year', $value); ?>
                     </div>

                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->graduation_marksheet : '');
                        ?>
                        <?php echo render_input('graduation_marksheet', 'Graduation Marksheet', $value); ?>

                        
                           <!-- <p>Uploaded Marksheet: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Marksheet: <?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a> <?php } ?></p>

                       
                     </div>
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->post_graduation_marksheet : '');
                        ?>
                        <?php echo render_input('post_graduation_marksheet', 'Post Graduation Marksheet', $value); ?>

                        
                           <!-- <p>Uploaded Marksheet: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Marksheet: <?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a><?php } ?></p>

                        
                     </div>

                  </div>
               </div>


               <div role="tabpanel" class="tab-pane " id="tab_work">
                  <h2>Previous Organization Details</h2>
                  <div class="row">
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->previous_designation : ''); ?>
                        <?php echo render_input('previous_designation', 'Designation', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->previous_year : ''); ?>
                        <?php echo render_input('previous_year', 'Year', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->previous_emp_id : ''); ?>
                        <?php echo render_input('previous_emp_id', 'Emp ID', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->previous_pay : ''); ?>
                        <?php echo render_input('previous_pay', 'Net Pay', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->previous_person_name : ''); ?>
                        <?php echo render_input('previous_person_name', 'Reporting Person Name', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->previous_person_designation : ''); ?>
                        <?php echo render_input('previous_person_designation', 'Reporting Person Designation', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->previous_person_contact : ''); ?>
                        <?php echo render_input('previous_person_contact', 'Reporting Person Contact', $value); ?>
                     </div>
                  </div>

                  <h2>Before 1 Year Organization Details</h2>
                  <div class="row">
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->before_designation : ''); ?>
                        <?php echo render_input('before_designation', 'Designation', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->before_year : ''); ?>
                        <?php echo render_input('before_year', 'Year', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->before_emp_id : ''); ?>
                        <?php echo render_input('before_emp_id', 'Emp ID', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->before_pay : ''); ?>
                        <?php echo render_input('before_pay', 'Net Pay', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->before_reporting_person : ''); ?>
                        <?php echo render_input('before_reporting_person', 'Reporting Person Name', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->before_reporting_designation : ''); ?>
                        <?php echo render_input('before_reporting_designation', 'Reporting Person Designation', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->before_reporting_contact : ''); ?>
                        <?php echo render_input('before_reporting_contact', 'Reporting Person Contact', $value); ?>
                     </div>
                  </div>

                  <hr>
                  <div class="row">
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->previous_uploads : '');
                        ?>
                        <?php echo render_input('previous_uploads', 'Previous Organization Upload Offer Letter, Salary Slips', $value); ?>

                        
                           <!-- <p>Uploaded Document: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Document:<?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a><?php } ?></p>

                        
                     </div>
                     <div class="col-md-6">
                        <?php

                        $value = (isset($member) ? $member->before_uploads : '');
                        ?>
                        <?php echo render_input('before_uploads', 'Before 1 Organization Offer Letter, Salary Slips', $value); ?>

                        
                           <!-- <p>Uploaded Document: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Document:<?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo  'view document'; ?></a><?php } ?></p>

                        
                     </div>

                  </div>

               </div>

               <div role="tabpanel" class="tab-pane " id="tab_declaration">

                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->declaration_date : ''); ?>
                        <?php echo render_input('declaration_date', 'Declaration Date', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->declaration_place : ''); ?>
                        <?php echo render_input('declaration_place', 'Declaration Place', $value); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <?php

                        $value = (isset($member) ? $member->declaration_signature : '');
                        ?>
                        <?php echo render_input('declaration_signature', 'Upload Signature', $value); ?>

                        
                           <!-- <p>Uploaded Document: <a href="<?php echo  base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $value; ?>" target="_blank"><?php echo  $value; ?></a></p> -->
                           <p>Uploaded Document:<?php if ($value) { ?> <a href="<?php echo $value; ?>" target="_blank"><?php echo 'view document' ; ?></a> <?php } ?></p>

                       
                     </div>
                  </div>
               </div>

               <div role="tabpanel" class="tab-pane " id="tab_bgv">

                  <div class="row">
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->bgv_emp_name : ''); ?>
                        <?php echo render_input('bgv_emp_name', 'Emp Name', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->bgv_emp_position : ''); ?>
                        <?php echo render_input('bgv_emp_position', 'Position', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->bgv_emp_code : ''); ?>
                        <?php echo render_input('bgv_emp_code', 'Emp Code', $value); ?>
                     </div>
                     <div class="col-md-3">
                        <?php $value = (isset($member) ? $member->bgv_emp_date : ''); ?>
                        <?php echo render_input('bgv_emp_date', 'Date of Employment', $value); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_number : ''); ?>
                        <?php echo render_input('bgv_emp_number', 'Contact Number', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_address : ''); ?>
                        <?php echo render_input('bgv_emp_address', 'Address', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_department : ''); ?>
                        <?php echo render_input('bgv_emp_department', 'Department', $value); ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_year : ''); ?>
                        <?php echo render_input('bgv_emp_year', 'Year', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_hand_salary : ''); ?>
                        <?php echo render_input('bgv_emp_hand_salary', 'In Hand Salary', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_gross_salary : ''); ?>
                        <?php echo render_input('bgv_emp_gross_salary', 'Gross Salary', $value); ?>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_location : ''); ?>
                        <?php echo render_input('bgv_emp_location', 'Location', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_previous_document : ''); ?>
                        <?php echo render_input('bgv_emp_previous_document', 'Previous Company Documents', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->bgv_emp_supervisior : ''); ?>
                        <?php echo render_input('bgv_emp_supervisior', 'Supervisor Name', $value); ?>
                     </div>
                  </div>

                  <h2>HR Contact Details</h2>

                  <div class="row">

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bgv_emp_hr_email : ''); ?>
                        <?php echo render_input('bgv_emp_hr_email', 'Email Address', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">

                           <?php $value = (isset($member) ? $member->bgv_emp_leaving_reason : ''); ?>
                           <label class="control-label">Reason for Leaving</label>
                           <textarea name="bgv_emp_leaving_reason" class="form-control">
                              <?= $value ?>
                           </textarea>
                        </div>

                     </div>
                  </div>

                  <h2>Work History Gap</h2>

                  <div class="row">

                     <div class="col-md-6">
                        <?php $value = (isset($member) ? $member->bgv_emp_gap : ''); ?>
                        <?php echo render_input('bgv_emp_gap', 'discharged or asked to resign from a job', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">

                           <?php $value = (isset($member) ? $member->bgv_emp_gap_reason : ''); ?>
                           <label class="control-label">Reason for Leaving</label>
                           <textarea name="bgv_emp_gap_reason" class="form-control">
                              <?= $value ?>
                           </textarea>
                        </div>

                     </div>
                  </div>

               </div>
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-default close_btn" data-dismiss="modal"><?php echo _l('hr_close'); ?></button>
               <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
         </div><!-- /.modal-content -->

      </div>
   </div>
   <?php
   require 'modules/hr_profile/assets/js/hr_record/add_update_staff_js.php';
   ?>