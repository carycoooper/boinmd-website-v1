<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Page_Manager {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    public static function pages() {
        return array(
            'home'             => array( 'title' => '悦听远程服务', 'slug' => 'hearing-service', 'shortcode' => '[boin_hearing_service_home]' ),
            'test_intro'       => array( 'title' => '测试准备', 'slug' => 'hearing-service/test-intro', 'shortcode' => '[boin_hearing_test_intro]' ),
            'calibration'      => array( 'title' => '设备音量确认', 'slug' => 'hearing-service/test-calibration', 'shortcode' => '[boin_hearing_calibration]' ),
            'test'             => array( 'title' => '听力测试', 'slug' => 'hearing-service/test', 'shortcode' => '[boin_hearing_test]' ),
            'result'           => array( 'title' => '测试结果', 'slug' => 'hearing-service/test-result', 'shortcode' => '[boin_hearing_test_result]' ),
            'stopped'          => array( 'title' => '测试已停止', 'slug' => 'hearing-service/test-stopped', 'shortcode' => '[boin_hearing_test_stopped]' ),
            'request'          => array( 'title' => '提交调试需求', 'slug' => 'hearing-service/request', 'shortcode' => '[boin_hearing_request]' ),
            'request_success'  => array( 'title' => '需求提交成功', 'slug' => 'hearing-service/request-success', 'shortcode' => '[boin_hearing_request_success]' ),
        );
    }

    public static function ensure_pages() {
        $ids = get_option( 'boin_hearing_service_pages', array() );
        $ids = is_array( $ids ) ? $ids : array();
        foreach ( self::pages() as $key => $page ) {
            $existing = get_page_by_path( $page['slug'] );
            if ( $existing ) {
                $ids[ $key ] = (int) $existing->ID;
                continue;
            }
            $parts = explode( '/', $page['slug'] );
            $slug = array_pop( $parts );
            $parent_id = 0;
            if ( ! empty( $parts ) ) {
                $parent_path = implode( '/', $parts );
                $parent = get_page_by_path( $parent_path );
                if ( $parent ) $parent_id = (int) $parent->ID;
            }
            $post_id = wp_insert_post( array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_parent'  => $parent_id,
                'post_content' => $page['shortcode'],
            ) );
            if ( $post_id && ! is_wp_error( $post_id ) ) {
                $ids[ $key ] = (int) $post_id;
            }
        }
        update_option( 'boin_hearing_service_pages', $ids, false );
    }
}
