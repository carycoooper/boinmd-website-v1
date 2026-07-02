<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_WeCom {
    public static function send( $content ) {
        $settings = bhs_get_settings();
        if ( empty( $settings['wecom_enabled'] ) || $settings['wecom_enabled'] !== '1' ) {
            return new WP_Error( 'wecom_disabled', '企业微信通知未开启。' );
        }

        $webhook = esc_url_raw( $settings['wecom_webhook'] ?? '' );
        if ( ! $webhook ) {
            return new WP_Error( 'wecom_missing_webhook', '企业微信 Webhook 未配置。' );
        }

        $response = wp_remote_post( $webhook, array(
            'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
            'timeout' => 8,
            'body'    => wp_json_encode( array(
                'msgtype' => 'text',
                'text'    => array( 'content' => $content ),
            ), JSON_UNESCAPED_UNICODE ),
        ) );

        if ( is_wp_error( $response ) ) {
            BHS_Logger::log( 'wecom', 'send failed', array( 'error' => $response->get_error_message() ), 'error' );
        }
        return $response;
    }

    public static function send_service_request( $request_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::requests_table() . ' WHERE id = %d', $request_id ) );
        if ( ! $row ) return new WP_Error( 'request_not_found', '需求记录不存在。' );
        $content = "【新的远程调试需求】\n\n"
            . "需求编号：RQ" . str_pad( (string) $row->id, 8, '0', STR_PAD_LEFT ) . "\n"
            . "设备：" . $row->device_name_snapshot . "\n"
            . "手机号：" . bhs_mask_phone( $row->phone ) . "\n"
            . "主要问题：" . $row->main_problem . "\n"
            . "使用场景：" . ( $row->usage_scene ?: '未填写' ) . "\n"
            . "关联听力筛查：" . ( $row->hearing_session_id ? '已关联' : '未关联' ) . "\n"
            . "提交时间：" . $row->created_at . "\n\n"
            . "请登录 WordPress 后台及时查看和处理。";
        return self::send( $content );
    }

    public static function request_message( $post_id ) {
        $phone       = get_post_meta( $post_id, 'user_phone', true );
        $device      = get_post_meta( $post_id, 'device_model', true );
        $description = get_post_meta( $post_id, 'description', true );
        $created_at  = get_post_meta( $post_id, 'created_at', true );

        return "新的远程调试请求\n\n"
            . "手机号：" . bhs_mask_phone( $phone ) . "\n"
            . "设备：" . $device . "\n"
            . "问题描述：" . $description . "\n\n"
            . "提交时间：" . $created_at . "\n\n"
            . "请及时处理";
    }

    public static function test_message( $post_id ) {
        $phone      = get_post_meta( $post_id, 'user_phone', true );
        $summary    = get_post_meta( $post_id, 'summary', true );
        $created_at = get_post_meta( $post_id, 'created_at', true );

        return "新的听力测试记录\n\n"
            . "手机号：" . bhs_mask_phone( $phone ) . "\n"
            . "简要分析：" . $summary . "\n\n"
            . "提交时间：" . $created_at . "\n\n"
            . "请在后台查看完整 6 频结果";
    }
}
