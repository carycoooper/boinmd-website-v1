<?php
/**
 * Plugin Name: Boin Knowledge Hub
 * Plugin URI:  https://www.boinmd.com.cn/
 * Description: 鍗氶煶鍚姏鐭ヨ瘑涓績 鈥?鑷畾涔夊唴瀹圭被鍨?(knowledge_article / knowledge_topic)銆佷笓棰樸€佽棰戞帴鍙ｃ€丷EST銆丼chema銆乁RL rewrite (/knowledge/...).
 * Version:     1.0.10
 * Author:      鍗氶煶 BOINMD
 * Text Domain: boin-knowledge-hub
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BKH_VERSION',  '1.0.10' );
define( 'BKH_FILE',     __FILE__ );
define( 'BKH_DIR',      plugin_dir_path( __FILE__ ) );
define( 'BKH_URL',      plugin_dir_url( __FILE__ ) );
define( 'BKH_TEMPLATE_DIR', BKH_DIR . 'templates/' );
define( 'BKH_REST_NS',  'boin/v1' );

/* ===== Constants for option keys ===== */
define( 'BKH_OPT_SETTINGS', 'bkh_settings' );        // hero, hot questions, social API keys
define( 'BKH_OPT_CTA',      'bkh_default_cta' );     // default CTA fallback

/* ===== Autoload includes ===== */
require_once BKH_DIR . 'includes/functions.php';
require_once BKH_DIR . 'includes/class-bkh-cpt.php';
require_once BKH_DIR . 'includes/class-bkh-rewrite.php';
require_once BKH_DIR . 'includes/class-bkh-meta.php';
require_once BKH_DIR . 'includes/class-bkh-template-loader.php';
require_once BKH_DIR . 'includes/class-bkh-schema.php';
require_once BKH_DIR . 'includes/class-bkh-rest.php';
require_once BKH_DIR . 'includes/class-bkh-admin.php';
require_once BKH_DIR . 'includes/class-bkh-shortcodes.php';
require_once BKH_DIR . 'includes/class-bkh-sitemap.php';

require_once BKH_DIR . 'includes/social-providers/abstract-bkh-provider.php';
require_once BKH_DIR . 'includes/social-providers/class-bilibili-provider.php';
require_once BKH_DIR . 'includes/social-providers/class-douyin-provider.php';
require_once BKH_DIR . 'includes/social-providers/class-wechat-video-provider.php';
require_once BKH_DIR . 'includes/social-providers/class-kuaishou-provider.php';
require_once BKH_DIR . 'includes/social-providers/class-youtube-provider.php';

/* ===== Init all modules ===== */
add_action( 'plugins_loaded', function () {
    BKH_CPT::instance();
    BKH_Rewrite::instance();
    BKH_Meta::instance();
    BKH_Template_Loader::instance();
    BKH_Schema::instance();
    BKH_REST::instance();
    BKH_Admin::instance();
    BKH_Shortcodes::instance();
    BKH_Sitemap::instance();
} );

/* ===== Activation / Deactivation ===== */
register_activation_hook( __FILE__, 'bkh_activate' );
function bkh_activate() {
    // Register CPT + Taxonomy so rewrite rules exist
    BKH_CPT::instance()->register_all();

    // Seed default taxonomy terms
    $defaults = array(
        'tinnitus'      => array( '鑰抽福涓撻',    '鑰抽福鐩稿叧鐭ヨ瘑銆佸師鍥犮€佺紦瑙ｄ笌灏卞尰寤鸿' ),
        'hearing-loss'  => array( '鍚姏涓嬮檷',    '鍚姏涓嬮檷鏃╂湡淇″彿銆佹娴嬪缓璁笌鏃ュ父搴斿' ),
        'hearing-aids'  => array( '鍔╁惉鍣ㄧ櫨绉?,  '鍔╁惉鍣ㄥ師鐞嗐€侀€夊瀷銆佽鍖轰笌鏃ュ父缁存姢' ),
        'ai-hearing'    => array( 'AI鏅鸿兘鍔╁惉',  'AI 绠楁硶鍦ㄥ姪鍚櫒涓殑搴旂敤涓庤竟鐣岃鏄? ),
        'care'          => array( '浣跨敤涓庝繚鍏?,  '鍔╁惉鍣ㄤ僵鎴淬€佹竻娲併€佺數姹犱笌缁存姢' ),
        'fitting'       => array( '楠岄厤鎸囧崡',    '鍚姏妫€娴嬨€佽皟鏈恒€侀€傚簲鏈熶笌灏卞尰寤鸿' ),
        'stories'       => array( '鐢ㄦ埛妗堜緥',    '鐪熷疄浣╂埓鑰呯殑鏁呬簨涓庨€傚簲杩囩▼' ),
    );
    foreach ( $defaults as $slug => $meta ) {
        if ( ! term_exists( $slug, 'knowledge_category' ) ) {
            wp_insert_term(
                $meta[0],
                'knowledge_category',
                array( 'slug' => $slug, 'description' => $meta[1] )
            );
        }
    }

    // Default plugin settings
    if ( get_option( BKH_OPT_SETTINGS ) === false ) {
        add_option( BKH_OPT_SETTINGS, bkh_default_settings() );
    }
    if ( get_option( BKH_OPT_CTA ) === false ) {
        add_option( BKH_OPT_CTA, bkh_default_cta_settings() );
    }

    // Create static cache file dir (for shop-links style JSON if needed later)
    // (knowledge hub itself reads from DB; placeholder for future)

    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'bkh_deactivate' );
function bkh_deactivate() {
    flush_rewrite_rules();
}

