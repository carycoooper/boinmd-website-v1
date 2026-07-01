<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_REST_API {
    private static $instance = null;
    const NS = 'boin/v1';

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( self::NS, '/devices', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_devices' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( self::NS, '/request', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_request' ),
            'permission_callback' => array( $this, 'check_submit_permission' ),
        ) );

        register_rest_route( self::NS, '/test', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_test' ),
            'permission_callback' => array( $this, 'check_submit_permission' ),
        ) );
    }

    public function check_submit_permission( WP_REST_Request $request ) {
        $nonce = (string) $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'rest_forbidden', '表单校验失败，请刷新页面后重试。', array( 'status' => 403 ) );
        }

        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
        $key = 'bhs_submit_' . md5( $ip );
        $count = (int) get_transient( $key );
        if ( $count >= 10 ) {
            return new WP_Error( 'too_many_requests', '提交过于频繁，请稍后再试。', array( 'status' => 429 ) );
        }
        set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

        return true;
    }

    public function get_devices() {
        $devices = array_map( 'bhs_get_device_payload', bhs_get_active_devices() );
        return rest_ensure_response( array( 'success' => true, 'devices' => $devices ) );
    }

    public function create_request( WP_REST_Request $request ) {
        $phone       = bhs_sanitize_phone( $request->get_param( 'user_phone' ) );
        $device      = sanitize_text_field( (string) $request->get_param( 'device_model' ) );
        $description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
        $test_id     = absint( $request->get_param( 'test_id' ) );

        if ( $phone === '' || $description === '' ) {
            return new WP_Error( 'missing_required_fields', '请填写手机号和调试需求。', array( 'status' => 400 ) );
        }

        $post_id = wp_insert_post( array(
            'post_type'   => 'hearing_request',
            'post_title'  => '远程调试请求 - ' . bhs_mask_phone( $phone ) . ' - ' . bhs_current_time(),
            'post_status' => 'publish',
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return new WP_Error( 'create_request_failed', '需求提交失败，请稍后再试。', array( 'status' => 500 ) );
        }

        update_post_meta( $post_id, 'user_phone', $phone );
        update_post_meta( $post_id, 'device_model', $device );
        update_post_meta( $post_id, 'description', $description );
        update_post_meta( $post_id, 'test_id', $test_id );
        update_post_meta( $post_id, 'status', 'pending' );
        update_post_meta( $post_id, 'created_at', bhs_current_time() );

        BHS_WeCom::send( BHS_WeCom::request_message( $post_id ) );

        return rest_ensure_response( array( 'success' => true, 'id' => $post_id ) );
    }

    public function create_test( WP_REST_Request $request ) {
        $phone = bhs_sanitize_phone( $request->get_param( 'user_phone' ) );
        $freq  = $request->get_param( 'freq_result' );

        if ( $phone === '' || ! is_array( $freq ) ) {
            return new WP_Error( 'missing_required_fields', '请填写手机号和 6 频测试结果。', array( 'status' => 400 ) );
        }

        $clean_freq = array();
        foreach ( $freq as $key => $value ) {
            $clean_freq[ sanitize_key( $key ) ] = floatval( $value );
        }

        $summary = sanitize_textarea_field( (string) $request->get_param( 'summary' ) );
        if ( $summary === '' ) {
            $avg = count( $clean_freq ) ? round( array_sum( $clean_freq ) / count( $clean_freq ), 1 ) : 0;
            $summary = '平均听阈约 ' . $avg . ' dB，仅作初步参考。';
        }

        $post_id = wp_insert_post( array(
            'post_type'   => 'hearing_test',
            'post_title'  => '听力测试 - ' . bhs_mask_phone( $phone ) . ' - ' . bhs_current_time(),
            'post_status' => 'publish',
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return new WP_Error( 'create_test_failed', '测试记录保存失败，请稍后再试。', array( 'status' => 500 ) );
        }

        update_post_meta( $post_id, 'user_phone', $phone );
        update_post_meta( $post_id, 'freq_result', wp_json_encode( $clean_freq, JSON_UNESCAPED_UNICODE ) );
        update_post_meta( $post_id, 'summary', $summary );
        update_post_meta( $post_id, 'created_at', bhs_current_time() );

        BHS_WeCom::send( BHS_WeCom::test_message( $post_id ) );

        return rest_ensure_response( array( 'success' => true, 'id' => $post_id, 'summary' => $summary ) );
    }
}
