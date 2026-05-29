<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Provider_Kuaishou extends BKH_Provider_Abstract {
    public $platform  = 'kuaishou';
    public $label     = '快手';
    public $can_embed = false;

    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    public function normalize_url( $url ) { return trim( $url ); }

    public function extract_video_id( $url ) {
        if ( preg_match( '#/short-video/([A-Za-z0-9_-]+)#', $url, $m ) ) return $m[1];
        if ( preg_match( '#/f/([A-Za-z0-9_-]+)#', $url, $m ) ) return $m[1];
        return '';
    }

    public function get_embed_url( $record ) { return ''; }
    public function get_embed_html( $record ) { return ''; }

    public function validate_url( $url ) {
        return (bool) preg_match( '#https?://(www\.kuaishou\.com|v\.kuaishou\.com)#i', $url );
    }
}
