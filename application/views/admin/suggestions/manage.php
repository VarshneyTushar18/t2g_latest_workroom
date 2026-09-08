<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <?php echo _l('staff_suggestions'); ?>
                        </h4>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo _l('staff_member'); ?></th>
                                        <th><?php echo _l('suggestion_subject'); ?></th>
                                        <th><?php echo _l('suggestion_message'); ?></th>
                                        <th><?php echo _l('date_created'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($suggestions)) { ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?php echo _l('no_suggestions_found'); ?></td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php foreach ($suggestions as $row) { ?>
                                            <tr class="<?php echo ((int) $row['status'] === 0) ? 'warning' : ''; ?>">
                                                <td><?php echo (int) $row['id']; ?></td>
                                                <td>
                                                    <?php echo html_escape($row['staff_name']); ?>
                                                    <?php if (!empty($row['staff_identifi'])) { ?>
                                                        <br><small>#<?php echo html_escape($row['staff_identifi']); ?></small>
                                                    <?php } ?>
                                                    <br><small><?php echo html_escape($row['staff_email']); ?></small>
                                                </td>
                                                <td><?php echo html_escape($row['subject']); ?></td>
                                                <td><?php echo nl2br(html_escape($row['message'])); ?></td>
                                                <td><?php echo _dt($row['date_created']); ?></td>
                                                <td>
                                                    <?php
                                                    if ((int) $row['status'] === 0) {
                                                        echo '<span class="label label-warning">' . _l('suggestion_status_new') . '</span>';
                                                    } elseif ((int) $row['status'] === 1) {
                                                        echo '<span class="label label-info">' . _l('suggestion_status_read') . '</span>';
                                                    } else {
                                                        echo '<span class="label label-success">' . _l('suggestion_status_closed') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ((int) $row['status'] === 0) { ?>
                                                        <a href="<?php echo admin_url('suggestions/mark_status/' . $row['id'] . '/1'); ?>" class="btn btn-default btn-xs"><?php echo _l('suggestion_mark_read'); ?></a>
                                                    <?php } ?>
                                                    <?php if ((int) $row['status'] !== 2) { ?>
                                                        <a href="<?php echo admin_url('suggestions/mark_status/' . $row['id'] . '/2'); ?>" class="btn btn-success btn-xs"><?php echo _l('suggestion_mark_closed'); ?></a>
                                                    <?php } ?>
                                                    <?php if (is_admin() || is_admin2() || is_super_admin()) { ?>
                                                        <a href="<?php echo admin_url('suggestions/delete/' . $row['id']); ?>" class="btn btn-danger btn-xs _delete"><?php echo _l('delete'); ?></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
