<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Provider_Bilibili extends BKH_Provider_Abstract {
    public $platform  = 'bilibili';
    public $label     = 'B 站';
    public $can_embed = true;

    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    public function normalize_url( $url ) {
        return trim( $url );
    }

    /**
     * Bilibili URLs:
     *   https://www.bilibili.com/video/BV1xx411c7mD
     *   https://www.bilibili.com/video/av170001
     *   https://b23.tv/xxxxxx (shortcode, redirects)
     */
    public function extract_video_id( $url ) {
        if ( preg_match( '#/video/(BV[0-9A-Za-z]+)#', $url, $m ) ) return $m[1];
        if ( preg_match( '#/video/av(\d+)#i', $url, $m ) )         return 'av' . $m[1];
        return '';
    }

    public function get_embed_url( $record ) {
        $vid = $record['video_id'] ?: $this->extract_video_id( $record['video_url'] ?? '' );
        if ( ! $vid ) return '';
        if ( strpos( $vid, 'BV' ) === 0 ) {
            return 'https://player.bilibili.com/player.html?bvid=' . $vid . '&autoplay=0';
        }
        if ( strpos( $vid, 'av' ) === 0 ) {
            return 'https://player.bilibili.com/player.html?aid=' . substr( $vid, 2 ) . '&autoplay=0';
        }
        return '';
    }

    public function get_embed_html( $record ) {
        // If admin pasted custom embed code, prefer that
        if ( ! empty( $record['embed_code'] ) ) return $record['embed_code'];

        $embed = $this->get_embed_url( $record );
        if ( ! $embed ) return '';
        return sprintf(
            '<div class="bkh-video-embed bkh-video-bilibili"><iframe src="%s" loading="lazy" allowfullscreen allow="autoplay; encrypted-media" frameborder="0" referrerpolicy="no-referrer" title="%s"></iframe></div>',
            esc_url( $embed ),
            esc_attr( $record['video_title'] ?? 'B站视频' )
        );
    }

    public function validate_url( $url ) {
        return (bool) preg_match( '#https?://(www\.bilibili\.com/video/|b23\.tv/)#i', $url );
    }
}
