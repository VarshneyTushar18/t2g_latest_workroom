<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $isRTL = (is_rtl() ? 'true' : 'false'); ?>

<!DOCTYPE html>
<html lang="<?php echo $locale; ?>" dir="<?php echo ($isRTL == 'true') ? 'rtl' : 'ltr' ?>">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?php echo isset($title) ? $title : get_option('companyname'); ?></title>

    <?php echo app_compile_css(); ?>
    <?php render_admin_js_variables(); ?>

    <!-- code for stopping website access on mobile -->
    <?php
    // Check if the "mobile" word exists in User-Agent 
    // Platform check  
    // $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"));
    // $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"));
    // $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"));
    // $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"));
    // $isIOS = $isIPhone || $isIPad;
    // $isMob = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile"));

    // if (!$isWin) {
    //     echo '<p style="color:white;">Tech2Globe Workroom is not accessible on mobile device. Please use desktop or laptop for a seamless use.</p>';
    //     die;
    // }

    ?>

    <?php
    

    if (is_mobile()) {
        echo '<p style="color:white;">Tech2Globe Workroom is not accessible on mobile device. Please use desktop or laptop for a seamless use.</p>';
        die;
    }
    ?>
    <script>
        var totalUnreadNotifications = <?php echo $current_user->total_unread_notifications; ?>,
            proposalsTemplates = <?php echo json_encode(get_proposal_templates()); ?>,
            contractsTemplates = <?php echo json_encode(get_contract_templates()); ?>,
            billingAndShippingFields = ['billing_street', 'billing_city', 'billing_state', 'billing_zip', 'billing_country',
                'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'
            ],
            isRTL = '<?php echo $isRTL; ?>',
            taskid, taskTrackingStatsData, taskAttachmentDropzone, taskCommentAttachmentDropzone, newsFeedDropzone,
            expensePreviewDropzone, taskTrackingChart, cfh_popover_templates = {},
            _table_api;
    </script>
    <?php app_admin_head(); ?>



</head>
<style>
/* Central Celebration Modal */
#birthday-celebration {
  position: fixed;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(to right, #ffecd2 0%, #fcb69f 100%);
  padding: 30px 50px;
  border-radius: 20px;
  box-shadow: 0 0 20px #ff69b4;
  text-align: center;
  z-index: 99999;
  animation: popupFade 0.5s ease-out;
}

@keyframes popupFade {
  from { transform: translateX(-50%) scale(0.5); opacity: 0; }
  to { transform: translateX(-50%) scale(1); opacity: 1; }
}

#birthday-celebration h1 {
  font-size: 36px;
  margin: 0;
  color: #e91e63;
  font-family: 'Segoe UI', sans-serif;
  text-shadow: 1px 1px 5px white;
}

/* Confetti style */
.confetti {
  width: 10px;
  height: 10px;
  background-color: #ff0;
  position: fixed;
  z-index: 9999;
  animation: fall linear infinite;
}

@keyframes fall {
  0% { transform: translateY(0); opacity: 1; }
  100% { transform: translateY(100vh); opacity: 0; }
}
</style>

<body <?php echo admin_body_class(isset($bodyclass) ? $bodyclass : ''); ?>>
    <?php hooks()->do_action('after_body_start'); ?>