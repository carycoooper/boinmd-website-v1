<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin settings page: 设置 → 博音知识中心设置
 * Manages: hero, hot questions, default CTA, social platform API keys (masked).
 */
class BKH_Admin {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function menu() {
        add_options_page(
            '博音知识中心设置',
            '博音知识中心设置',
            'manage_options',
            'boin-knowledge-hub',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting( 'bkh_settings_group', BKH_OPT_SETTINGS, array(
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
        ) );
        register_setting( 'bkh_settings_group', BKH_OPT_CTA, array(
            'sanitize_callback' => array( $this, 'sanitize_cta' ),
        ) );
    }

    public function sanitize_settings( $input ) {
        $existing = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
        $clean = $existing;

        // Hero text fields
        foreach ( array( 'hero_title', 'hero_subtitle', 'hero_placeholder' ) as $k ) {
            if ( isset( $input[ $k ] ) ) $clean[ $k ] = sanitize_text_field( wp_unslash( $input[ $k ] ) );
        }

        // Hot questions repeater
        if ( isset( $input['hot_questions'] ) && is_array( $input['hot_questions'] ) ) {
            $list = array();
            foreach ( $input['hot_questions'] as $row ) {
                if ( ! is_array( $row ) ) continue;
                $label = sanitize_text_field( wp_unslash( $row['label'] ?? '' ) );
                $url   = sanitize_text_field( wp_unslash( $row['url']   ?? '' ) );
                if ( $label || $url ) {
                    $list[] = array( 'label' => $label, 'url' => $url );
                }
            }
            $clean['hot_questions'] = $list;
        }

        // Social platform keys: preserve existing if input is masked (•••)
        $key_fields = array(
            'bilibili_app_key', 'bilibili_secret',
            'douyin_app_key', 'douyin_secret',
            'wechat_video_app_key', 'wechat_video_secret',
            'kuaishou_app_key', 'kuaishou_secret',
            'youtube_api_key',
        );
        foreach ( $key_fields as $f ) {
            if ( ! isset( $input[ $f ] ) ) continue;
            $val = trim( wp_unslash( $input[ $f ] ) );
            // If submitted value is the masked placeholder, keep existing
            if ( $val === '' || $this->is_masked( $val ) ) {
                continue;
            }
            $clean[ $f ] = sanitize_text_field( $val );
        }

        return $clean;
    }

    public function sanitize_cta( $input ) {
        $clean = array();
        $fields = array(
            'cta_title'           => 'sanitize_text_field',
            'cta_description'     => 'sanitize_textarea_field',
            'cta_button_text'     => 'sanitize_text_field',
            'cta_button_url'      => 'sanitize_text_field',
            'cta_secondary_text'  => 'sanitize_text_field',
            'cta_secondary_url'   => 'sanitize_text_field',
        );
        foreach ( $fields as $k => $cb ) {
            $clean[ $k ] = call_user_func( $cb, wp_unslash( $input[ $k ] ?? '' ) );
        }
        return $clean;
    }

    private function is_masked( $v ) {
        return preg_match( '/^[•·\*]+$/u', $v ) === 1;
    }

    private function mask( $value ) {
        if ( $value === '' ) return '';
        $len = mb_strlen( $value );
        if ( $len <= 4 ) return str_repeat( '•', $len );
        return mb_substr( $value, 0, 2 ) . str_repeat( '•', max( 4, $len - 4 ) ) . mb_substr( $value, -2 );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $s   = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
        $cta = get_option( BKH_OPT_CTA, bkh_default_cta_settings() );
        ?>
        <div class="wrap">
            <h1>博音知识中心设置</h1>
            <p class="description">控制 <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>" target="_blank">/knowledge/</a> 知识中心首页内容、默认 CTA、自媒体平台 API 凭证（预留）。</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'bkh_settings_group' ); ?>

                <h2 class="title">一、知识中心首页 Hero</h2>
                <table class="form-table">
                    <tr><th>主标题</th><td><input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[hero_title]" value="<?php echo esc_attr( $s['hero_title'] ); ?>" class="regular-text"></td></tr>
                    <tr><th>副标题</th><td><input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[hero_subtitle]" value="<?php echo esc_attr( $s['hero_subtitle'] ); ?>" class="large-text"></td></tr>
                    <tr><th>搜索框 placeholder</th><td><input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[hero_placeholder]" value="<?php echo esc_attr( $s['hero_placeholder'] ); ?>" class="regular-text"></td></tr>
                </table>

                <h2 class="title">二、热门问题入口（首页卡片）</h2>
                <table class="form-table" id="bkh-hot-questions">
                    <?php $hqs = $s['hot_questions'] ?: array(); if ( empty( $hqs ) ) $hqs = array( array() ); ?>
                    <?php foreach ( $hqs as $i => $q ) : ?>
                    <tr>
                        <th>问题 <?php echo $i + 1; ?></th>
                        <td>
                            <input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[hot_questions][<?php echo $i; ?>][label]" value="<?php echo esc_attr( $q['label'] ?? '' ); ?>" class="regular-text" placeholder="文案"><br>
                            <input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[hot_questions][<?php echo $i; ?>][url]" value="<?php echo esc_attr( $q['url'] ?? '' ); ?>" class="regular-text" placeholder="/knowledge/tinnitus/" style="margin-top:4px">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <p class="description">最多 5 条（首页只取前 3 条作为"你可能还想了解"轻量入口；首页知识中心区按全部显示）。需要更多时手动新增 row 即可。</p>

                <h2 class="title">三、自媒体平台 API 凭证（预留，可留空）</h2>
                <p class="description">支持通过 wp-config.php 常量覆盖：<code>BKH_BILIBILI_SECRET</code> / <code>BKH_DOUYIN_SECRET</code> / 等。</p>
                <table class="form-table">
                    <?php
                    $keys = array(
                        'bilibili_app_key'      => 'B 站 App Key',
                        'bilibili_secret'       => 'B 站 Secret',
                        'douyin_app_key'        => '抖音 App Key',
                        'douyin_secret'         => '抖音 Secret',
                        'wechat_video_app_key'  => '视频号 App Key',
                        'wechat_video_secret'   => '视频号 Secret',
                        'kuaishou_app_key'      => '快手 App Key',
                        'kuaishou_secret'       => '快手 Secret',
                        'youtube_api_key'       => 'YouTube API Key',
                    );
                    foreach ( $keys as $k => $label ) :
                        $raw = $s[ $k ] ?? '';
                        $display = ( strpos( $k, 'secret' ) !== false || strpos( $k, 'api_key' ) !== false ) ? $this->mask( $raw ) : $raw;
                    ?>
                    <tr>
                        <th><?php echo esc_html( $label ); ?></th>
                        <td>
                            <input type="text" name="<?php echo BKH_OPT_SETTINGS; ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $display ); ?>" class="regular-text" autocomplete="off">
                            <p class="description">留空保留原值。Secret 字段显示为掩码 (•)，仅在新输入完整值时才更新。</p>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <h2 class="title">四、默认 CTA（文章/专题底部）</h2>
                <table class="form-table">
                    <?php
                    $cta_fields = array(
                        'cta_title'          => '主标题',
                        'cta_description'    => '描述',
                        'cta_button_text'    => '主按钮文案',
                        'cta_button_url'     => '主按钮 URL',
                        'cta_secondary_text' => '副按钮文案',
                        'cta_secondary_url'  => '副按钮 URL',
                    );
                    foreach ( $cta_fields as $k => $label ) :
                    ?>
                    <tr>
                        <th><?php echo esc_html( $label ); ?></th>
                        <td><input type="text" name="<?php echo BKH_OPT_CTA; ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $cta[ $k ] ?? '' ); ?>" class="large-text"></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button( '保存设置' ); ?>
            </form>

            <h2 class="title">五、REST API 端点</h2>
            <table class="form-table">
                <tr><th>视频聚合</th><td><a href="<?php echo esc_url( rest_url( BKH_REST_NS . '/social-videos' ) ); ?>" target="_blank"><?php echo esc_html( rest_url( BKH_REST_NS . '/social-videos' ) ); ?></a></td></tr>
                <tr><th>知识文章</th><td><a href="<?php echo esc_url( rest_url( BKH_REST_NS . '/knowledge-articles' ) ); ?>" target="_blank"><?php echo esc_html( rest_url( BKH_REST_NS . '/knowledge-articles' ) ); ?></a></td></tr>
                <tr><th>知识专题</th><td><a href="<?php echo esc_url( rest_url( BKH_REST_NS . '/knowledge-topics' ) ); ?>" target="_blank"><?php echo esc_html( rest_url( BKH_REST_NS . '/knowledge-topics' ) ); ?></a></td></tr>
            </table>

            <h2 class="title">六、首页轻量入口短代码</h2>
            <p>在主站任意页面粘贴：<code>[boin_knowledge_entry]</code>（默认 3 条）或 <code>[boin_knowledge_entry count="3"]</code>。</p>
        </div>
        <?php
    }
}
