<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Default settings (hero, hot questions, social API keys).
 */
function bkh_default_settings() {
    return array(
        'hero_title'        => '听力知识中心',
        'hero_subtitle'     => '关于耳鸣、听力下降、助听器与 AI 智能助听的专业知识内容',
        'hero_placeholder'  => '例如：晚上耳鸣特别明显怎么办？',
        'hot_questions'     => array(
            array( 'label' => '耳鸣越来越严重怎么办？', 'url' => '/knowledge/tinnitus/' ),
            array( 'label' => '晚上耳鸣特别明显怎么办？', 'url' => '/knowledge/tinnitus/' ),
            array( 'label' => '老人听不清别人说话怎么办？', 'url' => '/knowledge/hearing-loss/' ),
            array( 'label' => '助听器会越戴越聋吗？', 'url' => '/knowledge/hearing-aids/' ),
            array( 'label' => 'AI助听器真的有用吗？', 'url' => '/knowledge/ai-hearing/' ),
        ),
        // API keys — never echo to frontend
        'bilibili_app_key'      => '',
        'bilibili_secret'       => '',
        'douyin_app_key'        => '',
        'douyin_secret'         => '',
        'wechat_video_app_key'  => '',
        'wechat_video_secret'   => '',
        'kuaishou_app_key'      => '',
        'kuaishou_secret'       => '',
        'youtube_api_key'       => '',
    );
}

function bkh_default_cta_settings() {
    return array(
        'cta_title'          => '想了解适合自己的助听器方案？',
        'cta_description'    => '博音 BOINMD 悦听礼赠款 Q10-P 耳背式数字助听器，单耳 688 元 / 双耳 888 元 / Pro 版 1688 元含门店听力测试与个性化调机。',
        'cta_button_text'    => '了解悦听礼赠款',
        'cta_button_url'     => '/q10-p/',
        'cta_secondary_text' => '咨询听力顾问',
        'cta_secondary_url'  => 'tel:18601383858',
    );
}

/**
 * Read setting safely. Supports wp-config.php constant override for API keys:
 *   define( 'BKH_BILIBILI_SECRET', 'xxx' );
 */
function bkh_get_setting( $key, $default = '' ) {
    $constant = 'BKH_' . strtoupper( $key );
    if ( defined( $constant ) ) {
        return constant( $constant );
    }
    $settings = get_option( BKH_OPT_SETTINGS, array() );
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

function bkh_get_cta( $post_id = null ) {
    $defaults = get_option( BKH_OPT_CTA, bkh_default_cta_settings() );
    if ( ! $post_id ) {
        return $defaults;
    }
    // Per-post override fields (stored via meta box)
    $fields = array( 'cta_title', 'cta_description', 'cta_button_text', 'cta_button_url', 'cta_secondary_text', 'cta_secondary_url' );
    $out = array();
    foreach ( $fields as $f ) {
        $val = get_post_meta( $post_id, "_bkh_$f", true );
        $out[ $f ] = ( $val !== '' ) ? $val : ( $defaults[ $f ] ?? '' );
    }
    return $out;
}

/**
 * Get videos for a post (array of video records).
 */
function bkh_get_videos( $post_id ) {
    $videos = get_post_meta( $post_id, '_bkh_videos', true );
    if ( ! is_array( $videos ) ) return array();
    // Sort by display_order asc, primary first
    usort( $videos, function( $a, $b ) {
        $pa = ! empty( $a['is_primary'] ) ? 1 : 0;
        $pb = ! empty( $b['is_primary'] ) ? 1 : 0;
        if ( $pa !== $pb ) return $pb - $pa;
        return ( (int)( $a['display_order'] ?? 99 ) ) - ( (int)( $b['display_order'] ?? 99 ) );
    } );
    return $videos;
}

/**
 * Pick the front-facing video for cn audience.
 * Priority: is_primary → bilibili (iframe) → other CN platforms (card) → none.
 */
function bkh_get_primary_video( $post_id ) {
    $videos = bkh_get_videos( $post_id );
    if ( empty( $videos ) ) return null;
    foreach ( $videos as $v ) {
        if ( ! empty( $v['is_primary'] ) ) return $v;
    }
    foreach ( $videos as $v ) {
        if ( ( $v['platform'] ?? '' ) === 'bilibili' ) return $v;
    }
    foreach ( $videos as $v ) {
        if ( in_array( ( $v['platform'] ?? '' ), array( 'douyin', 'wechat_video', 'kuaishou' ), true ) ) return $v;
    }
    foreach ( $videos as $v ) {
        if ( ( $v['platform'] ?? '' ) === 'youtube' ) return $v;
    }
    return $videos[0] ?? null;
}

/**
 * Get FAQ items for a post.
 */
function bkh_get_faqs( $post_id ) {
    $faqs = get_post_meta( $post_id, '_bkh_faqs', true );
    return is_array( $faqs ) ? $faqs : array();
}

/**
 * Get topic "scene cards" for a knowledge_topic.
 */
function bkh_get_scenes( $post_id ) {
    $scenes = get_post_meta( $post_id, '_bkh_scenes', true );
    return is_array( $scenes ) ? $scenes : array();
}

/**
 * Get topic "reason" blocks.
 */
function bkh_get_reasons( $post_id ) {
    $reasons = get_post_meta( $post_id, '_bkh_reasons', true );
    return is_array( $reasons ) ? $reasons : array();
}

/**
 * Get a provider instance by platform key.
 */
function bkh_get_provider( $platform ) {
    static $map = null;
    if ( $map === null ) {
        $map = array(
            'bilibili'     => 'BKH_Provider_Bilibili',
            'douyin'       => 'BKH_Provider_Douyin',
            'wechat_video' => 'BKH_Provider_Wechat_Video',
            'kuaishou'     => 'BKH_Provider_Kuaishou',
            'youtube'      => 'BKH_Provider_Youtube',
        );
    }
    $cls = $map[ $platform ] ?? null;
    if ( $cls && class_exists( $cls ) ) {
        return $cls::instance();
    }
    return null;
}

/**
 * Convert /blog/knowledge/... to /knowledge/... in URLs.
 */
function bkh_strip_blog_prefix( $url ) {
    if ( ! is_string( $url ) || $url === '' ) return $url;
    $home = home_url( '/' );
    // Match against blog-rooted URL
    $url = str_replace( $home . 'knowledge/', site_url( '/' ) . 'knowledge/', $url );
    // If WP's home was set to https://www.boinmd.com.cn/blog/, this turns
    // /blog/knowledge/x → /knowledge/x
    $url = preg_replace( '#(https?://[^/]+)/blog/knowledge/#', '$1/knowledge/', $url );
    return $url;
}

/**
 * Build the public URL for a knowledge category term.
 */
function bkh_category_url( $slug ) {
    return home_url( '/knowledge/' . ltrim( $slug, '/' ) . '/' );
}

/**
 * Resolve a publicly-correct URL (after blog-prefix stripping).
 */
function bkh_url( $path = '/' ) {
    $base = preg_replace( '#/blog/?$#', '', untrailingslashit( home_url() ) );
    if ( $base === '' ) $base = untrailingslashit( site_url() );
    return $base . '/' . ltrim( $path, '/' );
}
