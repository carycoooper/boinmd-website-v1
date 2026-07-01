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

        return wp_remote_post( $webhook, array(
            'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
            'timeout' => 8,
            'body'    => wp_json_encode( array(
                'msgtype' => 'text',
                'text'    => array( 'content' => $content ),
            ), JSON_UNESCAPED_UNICODE ),
        ) );
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
