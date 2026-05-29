<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BKH_Shortcodes {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_shortcode( 'boin_knowledge_entry', array( $this, 'knowledge_entry' ) );
        add_shortcode( 'boin_knowledge_search', array( $this, 'knowledge_search' ) );
    }

    /**
     * [boin_knowledge_entry count="3"]
     * 首页/任意 WP 页面用 — 输出"你可能还想了解这些听力问题"轻量入口区
     */
    public function knowledge_entry( $atts ) {
        $atts = shortcode_atts( array( 'count' => 3, 'title' => '你可能还想了解这些听力问题' ), $atts );
        $count = max( 1, min( 6, (int) $atts['count'] ) );

        $settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
        $qs = $settings['hot_questions'] ?? array();
        // For 主页轻量入口，按用户指令默认展示这 3 条
        $default_three = array(
            array( 'label' => '晚上耳鸣特别明显怎么办？', 'url' => bkh_url( '/knowledge/tinnitus/' ) ),
            array( 'label' => '父母听不清别人说话怎么办？', 'url' => bkh_url( '/knowledge/hearing-loss/' ) ),
            array( 'label' => '助听器会越戴越聋吗？', 'url' => bkh_url( '/knowledge/hearing-aids/' ) ),
        );
        $items = ! empty( $qs ) ? array_slice( $qs, 0, $count ) : array_slice( $default_three, 0, $count );

        ob_start(); ?>
        <section class="bkh-entry-section" aria-labelledby="bkh-entry-title">
            <p class="bkh-entry-eyebrow">听力知识中心</p>
            <h2 id="bkh-entry-title" class="bkh-entry-title"><?php echo esc_html( $atts['title'] ); ?></h2>
            <div class="bkh-entry-grid">
                <?php foreach ( $items as $q ) :
                    $url = $q['url'] ?: bkh_url( '/knowledge/' );
                    // Make sure relative URLs become absolute
                    if ( strpos( $url, 'http' ) !== 0 && $url[0] !== '/' ) $url = '/' . $url;
                ?>
                    <a class="bkh-entry-card" href="<?php echo esc_url( $url ); ?>">
                        <span class="bkh-entry-q"><?php echo esc_html( $q['label'] ); ?></span>
                        <span class="bkh-entry-arrow" aria-hidden="true">→</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="bkh-entry-more"><a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">查看全部听力知识 →</a></p>
        </section>
        <?php
        return ob_get_clean();
    }

    public function knowledge_search( $atts ) {
        $settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
        $placeholder = $settings['hero_placeholder'] ?? '搜索听力问题…';
        ob_start(); ?>
        <form class="bkh-search" role="search" method="get" action="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">
            <input type="search" name="s" placeholder="<?php echo esc_attr( $placeholder ); ?>" aria-label="搜索听力知识">
            <input type="hidden" name="post_type[]" value="knowledge_article">
            <input type="hidden" name="post_type[]" value="knowledge_topic">
            <button type="submit">搜索</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
