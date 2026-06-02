<?php
/**
 * Plugin Name: Boin Knowledge Hub
 * Plugin URI:  https://www.boinmd.com.cn/
 * Description: 博音听力知识中心 — 自定义内容类型 (knowledge_article / knowledge_topic)、专题、视频接口、REST、Schema、URL rewrite (/knowledge/...).
 * Version:     1.0.5
 * Author:      博音 BOINMD
 * Text Domain: boin-knowledge-hub
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BKH_VERSION',  '1.0.5' );
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
} );

/* ===== Activation / Deactivation ===== */
register_activation_hook( __FILE__, 'bkh_activate' );
function bkh_activate() {
    // Register CPT + Taxonomy so rewrite rules exist
    BKH_CPT::instance()->register_all();

    // Seed default taxonomy terms
    $defaults = array(
        'tinnitus'      => array( '耳鸣专题',    '耳鸣相关知识、原因、缓解与就医建议' ),
        'hearing-loss'  => array( '听力下降',    '听力下降早期信号、检测建议与日常应对' ),
        'hearing-aids'  => array( '助听器百科',  '助听器原理、选型、误区与日常维护' ),
        'ai-hearing'    => array( 'AI智能助听',  'AI 算法在助听器中的应用与边界说明' ),
        'care'          => array( '使用与保养',  '助听器佩戴、清洁、电池与维护' ),
        'fitting'       => array( '验配指南',    '听力检测、调机、适应期与就医建议' ),
        'stories'       => array( '用户案例',    '真实佩戴者的故事与适应过程' ),
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
