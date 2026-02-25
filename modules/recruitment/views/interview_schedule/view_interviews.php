<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12" id="small-table">
        <div class="panel_s">
          <div class="panel-body">
            <?php echo form_hidden('interview_id', $interview_id); ?>
            <div class="row">
              <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-calendar" aria-hidden="true"></i> <?php echo _l($title); ?></h4>
                <hr />
              </div>
            </div>

            <?php
            
            render_datatable(array(
              _l('candidate'),
              _l('interviewer'),
              'Round',
              _l('rec_time'),
              _l('interview_day'),
              'Recruitment',
              _l('date_add'),
              'Recruiter Name',
              'Feedback',
            ), 'table_interview', ['interview_sm' => 'interview_sm']); ?>
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

</body>

</html>