<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_SMS {
    public static function send( $phone, $content ) {
        return new WP_Error( 'sms_not_configured', '短信能力暂未启用。' );
    }
}
