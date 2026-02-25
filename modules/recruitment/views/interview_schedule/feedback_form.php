<?php init_head(); ?>


<script src="https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js"></script>
<style>
  .ck-editor__editable_inline {
    min-height: 200px;
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12" id="small-table">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-calendar" aria-hidden="true"></i>
                  <?php echo _l($title); ?></h4>
                <hr />
                <div class="col-md-6 padding-left-right-0">
                  <table class="table border table-striped margin-top-0">
                    <tbody>
                      <tr class="project-overview">
                        <td class="bold" width="30%"><?php echo 'Candidate Name'; ?></td>
                        <td><?php echo $candidate_name ?></td>
                      </tr>
                      <tr class="project-overview">
                        <td class="bold"><?php echo 'Current Round'; ?></td>
                        <td><?php echo $round; ?></td>
                      </tr>
                      <tr class="project-overview">
                        <td class="bold"><?php echo 'Applied Position' ?></td>
                        <td><?php echo $position; ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

              </div>

              <?php
              if ($previous_rounds_data) {
                $html = '';
                foreach ($previous_rounds_data as $previous_round) {
                  $html .= '<div class="col-md-12"><h4> Round ' . $previous_round['round'] . ' Remarks :</h4><hr>';
                  $html .= '<pre>' . $previous_round['remarks'] . '</pre>';

                  $options = array(array('field_val' => 'selected', 'field_label' => 'Selected'), array('field_val' => 'onhold', 'field_label' => 'On Hold'), array('field_val' => 'shortlisted', 'field_label' => 'Shortlisted'), array('field_val' => 'recommended', 'field_label' => 'Recommended'), array('field_val' => 'rejected', 'field_label' => 'Rejected'));

                  $html .= '<div class="col-md-6">' . render_select('recommendation', $options, array('field_val', 'field_label'), 'Recommendation: ', $previous_round['recommendation'], ['disabled' => 'true']) . '</div>';

                  $interviewer_id = $previous_round['added_from'];

                  $html .= '<div class="col-md-6" style="text-align: end;">';
                  $html .= '<a href="' . admin_url('staff/profile/' . $interviewer_id) . '">' . staff_profile_image($interviewer_id, [
                    'staff-profile-image-small mright5',
                  ], 'small', [
                    'data-toggle' => 'tooltip',
                    'data-title' => get_staff_full_name($interviewer_id),
                  ]) . '</a>' . get_staff_full_name($interviewer_id) . '</div>';

                  $html .= '</div>';
                }
                echo $html;
              }
              ?>

              <?php if (check_staff_is_interviewer()): ?>
                <div class="col-md-12">
                  <h4> Round <?php echo $round ?> Remarks :</h4>
                  <hr>
                  <?php echo form_open_multipart(admin_url('recruitment/add_feedback'), array('id' => 'feedback_form')); ?>

                  <div class="form-group">
                    <?php echo render_textarea('remarks', '', '', [], [], '', 'tinymce'); ?>
                    <!-- <textarea name="remarks" id="remarks"></textarea> -->
                  </div>

                  <div class="col-md-6">
                    <?php

                    $options = array(array('field_val' => 'selected', 'field_label' => 'Selected'), array('field_val' => 'onhold', 'field_label' => 'On Hold'), array('field_val' => 'shortlisted', 'field_label' => 'Shortlisted'), array('field_val' => 'rejected', 'field_label' => 'Rejected'));

                    echo render_select('recommendation', $options, array('field_val', 'field_label'), 'Recommendation: ');

                    ?>
                  </div>

                  <div class="col-md-6" style="text-align: end;">
                    <?php

                    $staffid = get_staff_user_id();
                    echo '<a href="' . admin_url('staff/profile/' . $staffid) . '">' . staff_profile_image($staffid, [
                      'staff-profile-image-small mright5',
                    ], 'small', [
                      'data-toggle' => 'tooltip',
                      'data-title' => get_staff_full_name($staffid),
                    ]) . '</a>'; ?>
                    <?php echo get_staff_full_name($staffid); ?>
                  </div>

                  <div class="col-md-12">
                    <input type="hidden" name='candidate' value='<?php echo $candidate_id ?>'>
                    <input type="hidden" name='round' value='<?php echo $round ?>'>

                    <button id="sm_btn" type="submit" class="btn btn-info pull-right"><?php echo 'submit'; ?></button>
                  </div>
                  </form>


                </div>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>
      <div class="col-md-7 small-table-right-col">
        <div id="interview_sm_view" class="hide">
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
  // var editor;
  // CKEDITOR.replace('editor');


  // CKEDITOR.on('instanceReady', function (ev) {
  //   editor = ev.editor;

  // });


  // ClassicEditor
  //   .create(document.querySelector('#remarks'))
  //   .catch(error => {
  //     console.error(error);
  //   });


  // appValidateForm($('#feedback_form'), {
  //   recommendation: 'required',
  //   remarks: 'required'
  // });
</script>
</body>

</html>