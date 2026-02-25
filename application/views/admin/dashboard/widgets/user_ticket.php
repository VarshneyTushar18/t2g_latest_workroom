<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('user_widget'); ?>">
    <div class="panel_s user-data">
        <div class="panel-body home-activity">
            <div class="widget-dragger"></div>
            <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller scroller-left arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller scroller-right arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                        
                        
                        <?php if ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()) { ?>
                        <li role="presentation">
                            <a href="#home_tab_tickets" onclick="init_table_tickets(true);"
                                aria-controls="home_tab_tickets" role="tab" data-toggle="tab">
                                <i class="fa-regular fa-life-ring menu-icon"></i> <?php echo _l('home_tickets'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php hooks()->do_action('after_user_data_widget_tabs'); ?>
                    </ul>
                </div>
            </div>
            <div class="tab-content tw-mt-5">
                
                <?php if ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_tab_tickets">
                    <a href="<?php echo admin_url('tickets'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="_filters _hidden_inputs hidden tickets_filters">
                        <?php
                           // On home only show on hold, open and in progress
                           echo form_hidden('ticket_status_1', true);
                           echo form_hidden('ticket_status_2', true);
                           echo form_hidden('ticket_status_4', true);
                           ?>
                    </div>
                    <?php echo AdminTicketsTableStructure(); ?>
                </div>
                <?php } ?>
                
               
                <?php hooks()->do_action('after_user_data_widge_tabs_content'); ?>
            </div>
        </div>
    </div>
</div>