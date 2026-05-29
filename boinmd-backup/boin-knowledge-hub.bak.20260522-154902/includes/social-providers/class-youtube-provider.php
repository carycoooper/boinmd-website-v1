<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Provider_Youtube extends BKH_Provider_Abstract {
    public $platform  = 'youtube';
    public $label     = 'YouTube';
    public $can_embed = true; // 海外受众；国内官网默认不优先

    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    public function normalize_url( $url ) { return trim( $url ); }

    public function extract_video_id( $url ) {
        if ( preg_match( '#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})#', $url, $m ) ) return $m[1];
        return '';
    }

    public function get_embed_url( $record ) {
        $vid = $record['video_id'] ?: $this->extract_video_id( $record['video_url'] ?? '' );
        if ( ! $vid ) return '';
        return 'https://www.youtube.com/embed/' . $vid;
    }

    public function get_embed_html( $record ) {
        if ( ! empty( $record['embed_code'] ) ) return $record['embed_code'];
        $url = $this->get_embed_url( $record );
        if ( ! $url ) return '';
        return sprintf(
            '<div class="bkh-video-embed bkh-video-youtube"><iframe src="%s" loading="lazy" allowfullscreen allow="autoplay; encrypted-media" frameborder="0" referrerpolicy="no-referrer" title="%s"></iframe></div>',
            esc_url( $url ),
            esc_attr( $record['video_title'] ?? 'YouTube video' )
        );
    }

    public function validate_url( $url ) {
        return (bool) preg_match( '#https?://(www\.)?(youtube\.com|youtu\.be)#i', $url );
    }
}
