<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Provider_Douyin extends BKH_Provider_Abstract {
    public $platform  = 'douyin';
    public $label     = '抖音';
    public $can_embed = false; // 抖音不开放 iframe，统一卡片跳转

    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    public function normalize_url( $url ) { return trim( $url ); }

    /**
     * Douyin URLs:
     *   https://www.douyin.com/video/7300000000000000000
     *   https://v.douyin.com/xxxxx/ (短链)
     */
    public function extract_video_id( $url ) {
        if ( preg_match( '#/video/(\d+)#', $url, $m ) ) return $m[1];
        return '';
    }

    public function get_embed_url( $record ) {
        // 抖音无稳定公开 iframe，VideoObject 用 contentUrl 即可
        return '';
    }

    public function get_embed_html( $record ) {
        return ''; // 由前端 card 渲染
    }

    public function validate_url( $url ) {
        return (bool) preg_match( '#https?://(www\.douyin\.com|v\.douyin\.com|haohuo\.jinritemai\.com)#i', $url );
    }
}
