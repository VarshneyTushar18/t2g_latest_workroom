<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <a href="<?php echo admin_url('sop'); ?>" class="btn btn-default tw-mb-3">&larr; <?php echo _l('sop'); ?></a>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold"><?php echo html_escape($sop->name); ?></h4>
                        <p class="text-muted"><?php echo _l('announcement_date', _dt($sop->dateadded)); ?>
                            <?php if (!empty($sop->userid)) { ?>
                            &middot; <?php echo _l('sop_from'); ?> <?php echo html_escape($sop->userid); ?>
                            <?php } ?>
                        </p>
                        <hr />
                        <div class="tc-content"><?php echo $sop->message; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
