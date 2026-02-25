<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4>Upload privacy Policy</h4>
                        <hr class="hr-panel-heading">
                        <?php echo $error; ?>

                        <?php echo form_open_multipart('admin/hr_profile/do_upload'); ?>

                        <input type="file" name="userfile" size="20" />

                        <br /><br />

                        <input type="submit" value="upload" name="userSubmit"/>

                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>