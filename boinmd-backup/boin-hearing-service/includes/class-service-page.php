<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Service_Page {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_rewrite' ) );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );
        add_action( 'template_redirect', array( $this, 'render_page' ) );
        add_filter( 'document_title_parts', array( $this, 'title_parts' ), 30 );
        add_filter( 'pre_get_document_title', array( $this, 'document_title' ), 30 );
        add_action( 'init', array( $this, 'maybe_flush_rewrite' ), 30 );
    }

    public function register_rewrite() {
        add_rewrite_rule( '^hearing-service/?$', 'index.php?bhs_service_page=1', 'top' );
    }

    public function query_vars( $vars ) {
        $vars[] = 'bhs_service_page';
        return $vars;
    }

    public function is_service_page() {
        return (int) get_query_var( 'bhs_service_page' ) === 1;
    }

    public function title_parts( $title ) {
        if ( $this->is_service_page() ) {
            $title['title'] = '测听服务';
        }
        return $title;
    }

    public function document_title( $title ) {
        if ( $this->is_service_page() ) {
            return '测听服务 - 博音悦听礼赠';
        }
        return $title;
    }

    public function maybe_flush_rewrite() {
        $key = 'bhs_rewrite_version';
        if ( get_option( $key ) !== BHS_VERSION ) {
            $this->register_rewrite();
            flush_rewrite_rules( false );
            update_option( $key, BHS_VERSION, false );
        }
    }

    public function render_page() {
        if ( ! $this->is_service_page() ) return;

        status_header( 200 );
        get_header();
        echo '<main class="bhs-service-page" aria-label="测听服务">';
        echo '<section class="bhs-service-hero"><div class="bhs-service-wrap">';
        echo '<p class="bhs-eyebrow">BOINMD Hearing Service</p>';
        echo '<h1>测听服务</h1>';
        echo '<p>在线记录 6 频听力测试结果，或提交悦听礼赠款助听器调试需求。后台验配师可查看记录并接收企业微信通知。</p>';
        echo '</div></section>';
        echo do_shortcode( '[boin_home]' );
        echo do_shortcode( '[boin_test]' );
        echo do_shortcode( '[boin_request]' );
        echo '</main>';
        get_footer();
        exit;
    }
}
