<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Logger {
    public static function log( $type, $message, $context = array(), $level = 'info' ) {
        global $wpdb;
        if ( ! class_exists( 'BHS_DB' ) ) return;
        $safe_context = self::sanitize_context( is_array( $context ) ? $context : array() );
        $wpdb->insert( BHS_DB::logs_table(), array(
            'log_type'     => sanitize_key( $type ),
            'level'        => sanitize_key( $level ),
            'message'      => sanitize_textarea_field( $message ),
            'context_json' => wp_json_encode( $safe_context, JSON_UNESCAPED_UNICODE ),
            'created_at'   => current_time( 'mysql' ),
        ), array( '%s', '%s', '%s', '%s', '%s' ) );
    }

    private static function sanitize_context( $context ) {
        unset( $context['webhook'], $context['token'], $context['session_token'] );
        if ( isset( $context['phone'] ) ) {
            $context['phone'] = bhs_mask_phone( $context['phone'] );
        }
        if ( isset( $context['ip'] ) ) {
            $context['ip'] = substr( hash( 'sha256', (string) $context['ip'] ), 0, 16 );
        }
        return $context;
    }
}
