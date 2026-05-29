<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Provider_Wechat_Video extends BKH_Provider_Abstract {
    public $platform  = 'wechat_video';
    public $label     = '视频号';
    public $can_embed = false; // 视频号无 iframe，卡片展示

    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    public function normalize_url( $url ) { return trim( $url ); }

    public function extract_video_id( $url ) {
        // 视频号通常用 export ID 或 finder_id，难以稳定提取
        if ( preg_match( '#[?&]id=([^&]+)#', $url, $m ) ) return $m[1];
        return '';
    }

    public function get_embed_url( $record ) { return ''; }
    public function get_embed_html( $record ) { return ''; }

    public function validate_url( $url ) {
        return (bool) preg_match( '#https?://(channels\.weixin\.qq\.com|finder\.video\.qq\.com|mp\.weixin\.qq\.com)#i', $url );
    }
}
