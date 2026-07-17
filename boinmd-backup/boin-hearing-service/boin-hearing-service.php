<?php
/**
 * Plugin Name: Boin Hearing Service
 * Plugin URI:  https://www.boinmd.com.cn/
 * Description: 博音悦听礼赠款远程服务系统：在线听力筛查、用户需求、设备型号、企业微信通知与后台管理。
 * Version:     0.6.15
 * Author:      博音 BOINMD
 * Text Domain: boin-hearing-service
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BHS_VERSION', '0.6.15' );
define( 'BHS_FILE', __FILE__ );
define( 'BHS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BHS_URL', plugin_dir_url( __FILE__ ) );
define( 'BHS_OPT_SETTINGS', 'boin_hearing_service_settings' );

require_once BHS_DIR . 'includes/class-db.php';
require_once BHS_DIR . 'includes/functions.php';
require_once BHS_DIR . 'includes/class-logger.php';
require_once BHS_DIR . 'includes/class-page-manager.php';
require_once BHS_DIR . 'includes/class-wecom.php';
require_once BHS_DIR . 'includes/class-sms.php';
require_once BHS_DIR . 'includes/class-device-cpt.php';
require_once BHS_DIR . 'includes/class-request-cpt.php';
require_once BHS_DIR . 'includes/class-test-cpt.php';
require_once BHS_DIR . 'includes/class-rest-api.php';
require_once BHS_DIR . 'includes/class-service-page.php';
require_once BHS_DIR . 'admin/admin-settings.php';
require_once BHS_DIR . 'frontend/shortcode-home.php';
require_once BHS_DIR . 'frontend/shortcode-test.php';
require_once BHS_DIR . 'frontend/shortcode-request.php';

add_action( 'plugins_loaded', function() {
    BHS_Device_CPT::instance();
    BHS_Request_CPT::instance();
    BHS_Test_CPT::instance();
    BHS_REST_API::instance();
    BHS_Service_Page::instance();
    BHS_Admin_Settings::instance();
    BHS_Page_Manager::instance();
    BHS_Shortcode_Home::instance();
    BHS_Shortcode_Test::instance();
    BHS_Shortcode_Request::instance();
} );

register_activation_hook( __FILE__, 'bhs_activate' );
function bhs_activate() {
    BHS_DB::install();
    BHS_Device_CPT::instance()->register();
    BHS_Request_CPT::instance()->register();
    BHS_Test_CPT::instance()->register();
    BHS_Service_Page::instance()->register_rewrite();
    bhs_seed_default_device();
    BHS_Page_Manager::ensure_pages();
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'bhs_deactivate' );
function bhs_deactivate() {
    flush_rewrite_rules();
}
