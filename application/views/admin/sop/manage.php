<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="hrms-compose-actions">
                    <?php if (can_create_sop()) { ?>
                    <button type="button" class="btn btn-default hrms-btn-sop" data-toggle="modal" data-target="#sopComposeModal">
                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        <?php echo _l('sop_new'); ?>
                    </button>
                    <?php } ?>
                    <a href="<?php echo admin_url('announcements'); ?>" class="btn btn-primary hrms-btn-announcement">
                        <i class="fa-regular fa-plus" aria-hidden="true"></i>
                        <?php echo _l('announcements'); ?>
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table table-sop-list">
                            <thead>
                                <tr>
                                    <th><?php echo _l('sop_subject'); ?></th>
                                    <th><?php echo _l('announcement_date_list'); ?></th>
                                    <th><?php echo _l('sop_from'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sops as $sop) { ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('sop/view/' . $sop->announcementid); ?>"><?php echo html_escape($sop->name); ?></a></td>
                                    <td><?php echo _dt($sop->dateadded); ?></td>
                                    <td><?php echo html_escape($sop->userid); ?></td>
                                </tr>
                                <?php }
                                if (empty($sops)) { ?>
                                <tr><td colspan="3" class="text-muted text-center"><?php echo _l('sop_none'); ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
