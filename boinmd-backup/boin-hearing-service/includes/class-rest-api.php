<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_REST_API {
    private static $instance = null;
    const NS = 'boin-hearing/v1';
    const LEGACY_NS = 'boin/v1';
    private $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( self::NS, '/devices', array( 'methods' => 'GET', 'callback' => array( $this, 'get_devices' ), 'permission_callback' => '__return_true' ) );
        register_rest_route( self::NS, '/sessions', array( 'methods' => 'POST', 'callback' => array( $this, 'create_session' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
        register_rest_route( self::NS, '/sessions/(?P<uuid>[a-zA-Z0-9\-]+)/calibration', array( 'methods' => 'POST', 'callback' => array( $this, 'save_calibration' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
        register_rest_route( self::NS, '/sessions/(?P<uuid>[a-zA-Z0-9\-]+)/answer', array( 'methods' => 'POST', 'callback' => array( $this, 'save_answer' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
        register_rest_route( self::NS, '/sessions/(?P<uuid>[a-zA-Z0-9\-]+)/interrupt', array( 'methods' => 'POST', 'callback' => array( $this, 'interrupt_session' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
        register_rest_route( self::NS, '/sessions/(?P<uuid>[a-zA-Z0-9\-]+)/result', array( 'methods' => 'GET', 'callback' => array( $this, 'get_result' ), 'permission_callback' => '__return_true' ) );
        register_rest_route( self::NS, '/requests', array( 'methods' => 'POST', 'callback' => array( $this, 'create_service_request' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );

        register_rest_route( self::LEGACY_NS, '/devices', array( 'methods' => 'GET', 'callback' => array( $this, 'get_devices' ), 'permission_callback' => '__return_true' ) );
        register_rest_route( self::LEGACY_NS, '/request', array( 'methods' => 'POST', 'callback' => array( $this, 'create_legacy_request' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
        register_rest_route( self::LEGACY_NS, '/test', array( 'methods' => 'POST', 'callback' => array( $this, 'create_legacy_test' ), 'permission_callback' => array( $this, 'check_nonce' ) ) );
    }

    public function check_nonce( WP_REST_Request $request ) {
        $nonce = (string) $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'rest_forbidden', '当前页面会话已过期，请刷新页面后重试。', array( 'status' => 403 ) );
        }
        return true;
    }

    public function get_devices() {
        return rest_ensure_response( array( 'success' => true, 'data' => array( 'devices' => array_map( 'bhs_get_device_payload', bhs_get_active_devices() ) ), 'devices' => array_map( 'bhs_get_device_payload', bhs_get_active_devices() ) ) );
    }

    public function create_session( WP_REST_Request $request ) {
        global $wpdb;
        $phone = '';
        $client = bhs_client_info();
        $uuid = wp_generate_uuid4();
        $token = wp_generate_password( 40, false, false );
        $now = bhs_current_time();
        $wpdb->insert( BHS_DB::sessions_table(), array(
            'session_uuid' => $uuid,
            'session_token' => wp_hash_password( $token ),
            'phone' => $phone,
            'status' => 'created',
            'current_ear' => 'left',
            'current_frequency' => 250,
            'current_level' => 3,
            'completed_steps' => 0,
            'total_steps' => 12,
            'browser' => $client['browser'],
            'operating_system' => $client['operating_system'],
            'user_agent' => $client['user_agent'],
            'ip_hash' => bhs_hash_ip(),
            'started_at' => $now,
            'expires_at' => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
            'created_at' => $now,
            'updated_at' => $now,
        ), array( '%s','%s','%s','%s','%s','%d','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s' ) );
        if ( ! $wpdb->insert_id ) return new WP_Error( 'session_failed', '测试会话创建失败，请稍后重试。', array( 'status' => 500 ) );
        BHS_Logger::log( 'test_session', 'created', array( 'phone' => $phone, 'uuid' => $uuid ) );
        return rest_ensure_response( array( 'success' => true, 'message' => '会话已创建', 'data' => array( 'session_uuid' => $uuid, 'session_token' => $token, 'next_url' => bhs_service_url( 'test-calibration/', array( 'session' => $uuid, 'token' => $token ) ) ) ) );
    }

    private function get_session_row( $uuid, $token ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::sessions_table() . ' WHERE session_uuid = %s', $uuid ) );
        if ( ! $row || ! wp_check_password( (string) $token, $row->session_token ) ) return null;
        if ( ! empty( $row->expires_at ) && strtotime( $row->expires_at ) < time() ) return null;
        return $row;
    }

    public function save_calibration( WP_REST_Request $request ) {
        global $wpdb;
        $session = $this->get_session_row( sanitize_text_field( $request['uuid'] ), (string) $request->get_param( 'token' ) );
        if ( ! $session ) return new WP_Error( 'session_expired', '当前测试会话已过期，请重新开始测试。', array( 'status' => 403 ) );
        $wpdb->update( BHS_DB::sessions_table(), array( 'status' => 'testing', 'headphone_confirmed' => 1, 'volume_confirmed' => 1, 'updated_at' => bhs_current_time() ), array( 'id' => $session->id ), array( '%s','%d','%d','%s' ), array( '%d' ) );
        return rest_ensure_response( array( 'success' => true, 'message' => '设备音量确认已保存', 'data' => array( 'next_url' => bhs_service_url( 'test/', array( 'session' => $session->session_uuid, 'token' => (string) $request->get_param( 'token' ) ) ) ) ) );
    }

    public function save_answer( WP_REST_Request $request ) {
        global $wpdb;
        $uuid = sanitize_text_field( $request['uuid'] );
        $token = (string) $request->get_param( 'token' );
        $session = $this->get_session_row( $uuid, $token );
        if ( ! $session ) return new WP_Error( 'session_expired', '当前测试会话已过期，请重新开始测试。', array( 'status' => 403 ) );
        if ( $session->status !== 'testing' ) return new WP_Error( 'invalid_session_status', '当前测试状态异常，请重新开始测试。', array( 'status' => 400 ) );

        $answer = sanitize_key( $request->get_param( 'answer' ) );
        if ( ! in_array( $answer, array( 'heard', 'not_heard' ), true ) ) return new WP_Error( 'invalid_answer', '请选择是否听到声音。', array( 'status' => 400 ) );
        $ear = $session->current_ear ?: 'left';
        $frequency = (int) $session->current_frequency;
        $level = max( 1, min( 6, (int) $session->current_level ?: 3 ) );
        $now = bhs_current_time();

        $result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::results_table() . ' WHERE session_id = %d AND ear = %s AND frequency = %d', $session->id, $ear, $frequency ) );
        $steps = $result && $result->raw_steps_json ? json_decode( $result->raw_steps_json, true ) : array();
        $steps = is_array( $steps ) ? $steps : array();
        $steps[] = array( 'level' => $level, 'answer' => $answer, 'played_at' => $now );
        $answer_count = count( $steps );

        $complete_freq = false;
        $relative = null;
        $status = 'in_progress';
        $next_level = $answer === 'heard' ? max( 1, $level - 1 ) : min( 6, $level + 1 );
        if ( $answer_count >= 3 || ( $answer === 'heard' && $level <= 2 ) || ( $answer === 'not_heard' && $level >= 6 ) ) {
            $complete_freq = true;
            $relative = $answer === 'heard' ? $level : ( $level >= 6 ? null : min( 6, $level + 1 ) );
            $status = $relative === null ? 'no_response_at_max_level' : 'completed';
        }

        $data = array( 'session_id' => $session->id, 'ear' => $ear, 'frequency' => $frequency, 'relative_level' => $relative, 'result_status' => $status, 'answer_count' => $answer_count, 'raw_steps_json' => wp_json_encode( $steps, JSON_UNESCAPED_UNICODE ), 'updated_at' => $now );
        if ( $result ) {
            $wpdb->update( BHS_DB::results_table(), $data, array( 'id' => $result->id ), array( '%d','%s','%d','%d','%s','%d','%s','%s' ), array( '%d' ) );
        } else {
            $data['created_at'] = $now;
            $wpdb->insert( BHS_DB::results_table(), $data, array( '%d','%s','%d','%d','%s','%d','%s','%s','%s' ) );
        }

        if ( $complete_freq ) {
            $next = $this->next_step( $ear, $frequency );
            $completed = (int) $session->completed_steps + 1;
            if ( $next['done'] ) {
                $wpdb->update( BHS_DB::sessions_table(), array( 'status' => 'completed', 'completed_steps' => $completed, 'completed_at' => $now, 'updated_at' => $now ), array( 'id' => $session->id ), array( '%s','%d','%s','%s' ), array( '%d' ) );
                $this->sync_session_to_cpt( $session->id );
                return rest_ensure_response( array( 'success' => true, 'message' => '测试已完成', 'data' => array( 'next_url' => bhs_service_url( 'test-result/', array( 'session' => $uuid, 'token' => $token ) ) ) ) );
            }
            $wpdb->update( BHS_DB::sessions_table(), array( 'current_ear' => $next['ear'], 'current_frequency' => $next['frequency'], 'current_level' => 3, 'completed_steps' => $completed, 'updated_at' => $now ), array( 'id' => $session->id ), array( '%s','%d','%d','%d','%s' ), array( '%d' ) );
            return rest_ensure_response( array( 'success' => true, 'message' => '已保存，进入下一步', 'data' => array( 'next_url' => bhs_service_url( 'test/', array( 'session' => $uuid, 'token' => $token ) ) ) ) );
        }

        $wpdb->update( BHS_DB::sessions_table(), array( 'current_level' => $next_level, 'updated_at' => $now ), array( 'id' => $session->id ), array( '%d','%s' ), array( '%d' ) );
        return rest_ensure_response( array( 'success' => true, 'message' => '已保存，请继续确认当前频率', 'data' => array( 'next_url' => bhs_service_url( 'test/', array( 'session' => $uuid, 'token' => $token ) ) ) ) );
    }

    private function next_step( $ear, $frequency ) {
        $idx = array_search( (int) $frequency, $this->freqs, true );
        if ( $idx !== false && isset( $this->freqs[ $idx + 1 ] ) ) return array( 'done' => false, 'ear' => $ear, 'frequency' => $this->freqs[ $idx + 1 ] );
        if ( $ear === 'left' ) return array( 'done' => false, 'ear' => 'right', 'frequency' => 250 );
        return array( 'done' => true );
    }

    public function interrupt_session( WP_REST_Request $request ) {
        global $wpdb;
        $session = $this->get_session_row( sanitize_text_field( $request['uuid'] ), (string) $request->get_param( 'token' ) );
        if ( ! $session ) return new WP_Error( 'session_expired', '当前测试会话已过期，请重新开始测试。', array( 'status' => 403 ) );
        $reason = sanitize_text_field( (string) $request->get_param( 'reason' ) );
        $wpdb->update( BHS_DB::sessions_table(), array( 'status' => 'interrupted', 'interrupt_reason' => $reason, 'interrupted_at' => bhs_current_time(), 'updated_at' => bhs_current_time() ), array( 'id' => $session->id ), array( '%s','%s','%s','%s' ), array( '%d' ) );
        return rest_ensure_response( array( 'success' => true, 'message' => '测试已停止', 'data' => array( 'next_url' => bhs_service_url( 'test-stopped/' ) ) ) );
    }

    public function get_result( WP_REST_Request $request ) {
        $session = $this->get_session_row( sanitize_text_field( $request['uuid'] ), (string) $request->get_param( 'token' ) );
        if ( ! $session ) return new WP_Error( 'session_expired', '当前测试会话已过期，请重新开始测试。', array( 'status' => 403 ) );
        return rest_ensure_response( array( 'success' => true, 'data' => $this->result_payload( $session ) ) );
    }

    private function result_payload( $session ) {
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::results_table() . ' WHERE session_id = %d ORDER BY ear ASC, frequency ASC', $session->id ) );
        return array( 'session' => array( 'uuid' => $session->session_uuid, 'status' => $session->status, 'completed_at' => $session->completed_at, 'phone' => bhs_mask_phone( $session->phone ) ), 'results' => $rows );
    }

    public function create_service_request( WP_REST_Request $request ) {
        global $wpdb;
        $phone = bhs_sanitize_phone( $request->get_param( 'phone' ) );
        if ( strlen( preg_replace( '/\D+/', '', $phone ) ) !== 11 ) return new WP_Error( 'invalid_phone', '请填写完整的11位手机号。', array( 'status' => 400 ) );
        $device_id = absint( $request->get_param( 'device_id' ) );
        $device = $device_id ? get_post( $device_id ) : null;
        $main_problem = sanitize_text_field( (string) $request->get_param( 'main_problem' ) );
        $description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
        $privacy = (bool) $request->get_param( 'privacy_confirmed' );
        if ( $phone === '' || ! $device || get_post_type( $device ) !== 'hearing_device' || $main_problem === '' || ! $privacy ) return new WP_Error( 'missing_required_fields', '请填写手机号、设备型号、主要问题，并确认服务说明。', array( 'status' => 400 ) );
        if ( get_post_meta( $device->ID, 'is_active', true ) !== '1' ) return new WP_Error( 'device_disabled', '当前设备型号已停用，请返回重新选择。', array( 'status' => 400 ) );
        $uuid = wp_generate_uuid4();
        $now = bhs_current_time();
        $session = null;
        $session_uuid = sanitize_text_field( (string) $request->get_param( 'session_uuid' ) );
        $session_token = (string) $request->get_param( 'session_token' );
        if ( $session_uuid && $session_token ) $session = $this->get_session_row( $session_uuid, $session_token );
        $wpdb->insert( BHS_DB::requests_table(), array( 'request_uuid' => $uuid, 'phone' => $phone, 'device_id' => $device->ID, 'device_name_snapshot' => get_the_title( $device ), 'hearing_session_id' => $session ? $session->id : null, 'main_problem' => $main_problem, 'usage_scene' => sanitize_text_field( (string) $request->get_param( 'usage_scene' ) ), 'ear_description' => sanitize_text_field( (string) $request->get_param( 'ear_description' ) ), 'feedback_options_json' => wp_json_encode( array_map( 'sanitize_text_field', (array) $request->get_param( 'feedback_options' ) ), JSON_UNESCAPED_UNICODE ), 'description' => $description, 'status' => 'pending', 'wecom_status' => 'pending', 'created_at' => $now, 'updated_at' => $now ), array( '%s','%s','%d','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s' ) );
        $request_id = (int) $wpdb->insert_id;
        if ( ! $request_id ) return new WP_Error( 'request_failed', '需求提交失败，请稍后重试。', array( 'status' => 500 ) );
        if ( $session ) $this->attach_phone_to_session( (int) $session->id, $phone );
        $wecom = BHS_WeCom::send_service_request( $request_id );
        if ( is_wp_error( $wecom ) ) {
            $wpdb->update( BHS_DB::requests_table(), array( 'wecom_status' => 'failed', 'wecom_error' => $wecom->get_error_message(), 'updated_at' => $now ), array( 'id' => $request_id ), array( '%s','%s','%s' ), array( '%d' ) );
        } else {
            $wpdb->update( BHS_DB::requests_table(), array( 'wecom_status' => 'sent', 'wecom_sent_at' => bhs_current_time(), 'updated_at' => bhs_current_time() ), array( 'id' => $request_id ), array( '%s','%s','%s' ), array( '%d' ) );
        }
        $this->sync_request_to_cpt( $request_id );
        return rest_ensure_response( array( 'success' => true, 'message' => '需求已提交', 'data' => array( 'request_uuid' => $uuid, 'next_url' => bhs_service_url( 'request-success/', array( 'request' => $uuid ) ) ) ) );
    }

    public function create_legacy_request( WP_REST_Request $request ) {
        $request->set_param( 'phone', $request->get_param( 'user_phone' ) );
        $request->set_param( 'main_problem', $request->get_param( 'description' ) );
        $devices = bhs_get_active_devices();
        if ( ! empty( $devices ) ) $request->set_param( 'device_id', $devices[0]->ID );
        $request->set_param( 'privacy_confirmed', true );
        return $this->create_service_request( $request );
    }

    public function create_legacy_test( WP_REST_Request $request ) {
        return rest_ensure_response( array( 'success' => false, 'message' => '请使用新版六频在线听力筛查流程。' ) );
    }

    private function attach_phone_to_session( $session_id, $phone ) {
        global $wpdb;
        $session_id = absint( $session_id );
        $phone = bhs_sanitize_phone( $phone );
        if ( ! $session_id || $phone === '' ) return;

        $wpdb->update( BHS_DB::sessions_table(), array( 'phone' => $phone, 'updated_at' => bhs_current_time() ), array( 'id' => $session_id ), array( '%s','%s' ), array( '%d' ) );
        $session = $wpdb->get_row( $wpdb->prepare( 'SELECT session_uuid FROM ' . BHS_DB::sessions_table() . ' WHERE id = %d', $session_id ) );
        if ( ! $session || empty( $session->session_uuid ) ) return;

        $posts = get_posts( array(
            'post_type'      => 'hearing_test',
            'post_status'    => 'any',
            'meta_key'       => 'session_uuid',
            'meta_value'     => $session->session_uuid,
            'fields'         => 'ids',
            'posts_per_page' => 10,
        ) );

        foreach ( $posts as $post_id ) {
            update_post_meta( $post_id, 'user_phone', $phone );
            wp_update_post( array(
                'ID'         => $post_id,
                'post_title' => '六频筛查 - ' . bhs_mask_phone( $phone ) . ' - ' . bhs_current_time(),
            ) );
        }
    }

    private function sync_session_to_cpt( $session_id ) {
        global $wpdb;
        $session = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::sessions_table() . ' WHERE id = %d', $session_id ) );
        if ( ! $session ) return;
        $exists = get_posts( array( 'post_type' => 'hearing_test', 'post_status' => 'any', 'meta_key' => 'session_uuid', 'meta_value' => $session->session_uuid, 'fields' => 'ids', 'posts_per_page' => 1 ) );
        if ( ! empty( $exists ) ) return;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT ear, frequency, relative_level, result_status FROM ' . BHS_DB::results_table() . ' WHERE session_id = %d ORDER BY ear ASC, frequency ASC', $session_id ), ARRAY_A );
        $post_id = wp_insert_post( array( 'post_type' => 'hearing_test', 'post_status' => 'publish', 'post_title' => '六频筛查 - ' . ( $session->phone ? bhs_mask_phone( $session->phone ) : '未留手机号' ) . ' - ' . bhs_current_time() ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, 'session_uuid', $session->session_uuid );
            update_post_meta( $post_id, 'user_phone', $session->phone );
            update_post_meta( $post_id, 'freq_result', wp_json_encode( $rows, JSON_UNESCAPED_UNICODE ) );
            update_post_meta( $post_id, 'summary', bhs_generate_hearing_test_summary( $rows ) );
            update_post_meta( $post_id, 'created_at', $session->created_at );
        }
    }

    private function sync_request_to_cpt( $request_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::requests_table() . ' WHERE id = %d', $request_id ) );
        if ( ! $row ) return;
        $post_id = wp_insert_post( array( 'post_type' => 'hearing_request', 'post_status' => 'publish', 'post_title' => '远程调试需求 - ' . bhs_mask_phone( $row->phone ) . ' - ' . bhs_current_time() ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, 'request_uuid', $row->request_uuid );
            update_post_meta( $post_id, 'user_phone', $row->phone );
            update_post_meta( $post_id, 'device_model', $row->device_name_snapshot );
            update_post_meta( $post_id, 'description', trim( $row->main_problem . "\n" . $row->description ) );
            update_post_meta( $post_id, 'test_id', $row->hearing_session_id );
            update_post_meta( $post_id, 'status', $row->status );
            update_post_meta( $post_id, 'created_at', $row->created_at );
        }
    }
}
