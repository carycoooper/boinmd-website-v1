<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BKH_Meta — native WordPress meta boxes for knowledge_article + knowledge_topic.
 * Stores all repeatable fields as serialized post_meta:
 *   _bkh_videos    array of { platform, video_title, video_url, video_id, embed_code, cover_image, duration, publish_date, account_name, account_url, is_primary, display_order, tracking_code }
 *   _bkh_faqs      array of { question, answer }
 *   _bkh_scenes    array of { scene_title, scene_description, scene_link, scene_icon }   (topic only)
 *   _bkh_reasons   array of { reason_title, reason_description }                          (topic only)
 *   _bkh_featured_priority   int (article)
 *   _bkh_cta_*     CTA overrides (per-post)
 *   _bkh_hero_subtitle, _bkh_hero_image  (topic)
 */
class BKH_Meta {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post',      array( $this, 'save_post' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }

    public function admin_assets( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || ! in_array( $screen->post_type, array( 'knowledge_article', 'knowledge_topic' ), true ) ) return;

        wp_enqueue_style( 'bkh-admin', BKH_URL . 'assets/css/bkh-admin.css', array(), BKH_VERSION );
        wp_enqueue_script( 'bkh-admin', BKH_URL . 'assets/js/bkh-admin.js', array( 'jquery' ), BKH_VERSION, true );
    }

    public function add_meta_boxes() {
        // ===== knowledge_article =====
        add_meta_box( 'bkh_article_videos',  '视频 (可绑定多平台)',     array( $this, 'render_videos' ),    'knowledge_article', 'normal', 'high' );
        add_meta_box( 'bkh_article_faqs',    'FAQ',                     array( $this, 'render_faqs' ),      'knowledge_article', 'normal', 'default' );
        add_meta_box( 'bkh_article_featured','推荐优先级',              array( $this, 'render_featured' ),  'knowledge_article', 'side',   'default' );
        add_meta_box( 'bkh_article_cta',     'CTA 转化模块 (可覆盖默认)', array( $this, 'render_cta' ),       'knowledge_article', 'normal', 'default' );

        // ===== knowledge_topic =====
        add_meta_box( 'bkh_topic_hero',     '专题 Hero',          array( $this, 'render_topic_hero' ),  'knowledge_topic', 'normal', 'high' );
        add_meta_box( 'bkh_topic_scenes',   '场景问题 (卡片)',    array( $this, 'render_scenes' ),      'knowledge_topic', 'normal', 'high' );
        add_meta_box( 'bkh_topic_reasons',  '原因说明',           array( $this, 'render_reasons' ),     'knowledge_topic', 'normal', 'default' );
        add_meta_box( 'bkh_topic_faqs',     'FAQ',                array( $this, 'render_faqs' ),        'knowledge_topic', 'normal', 'default' );
        add_meta_box( 'bkh_topic_videos',   '视频',               array( $this, 'render_videos' ),      'knowledge_topic', 'normal', 'default' );
        add_meta_box( 'bkh_topic_cta',      'CTA 转化模块',       array( $this, 'render_cta' ),         'knowledge_topic', 'normal', 'default' );
    }

    /* ======================================================
     * RENDERERS
     * ====================================================== */

    public function render_videos( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $videos = bkh_get_videos( $post->ID );
        $platforms = array(
            'bilibili'     => 'B 站 (优先)',
            'douyin'       => '抖音',
            'wechat_video' => '视频号',
            'kuaishou'     => '快手',
            'youtube'      => 'YouTube (国际备用)',
        );
        ?>
        <div class="bkh-repeater" data-name="_bkh_videos">
            <div class="bkh-rep-items">
            <?php if ( empty( $videos ) ) : $videos = array( array() ); endif; ?>
            <?php foreach ( $videos as $i => $v ) : ?>
                <div class="bkh-rep-item">
                    <div class="bkh-rep-grid">
                        <p>
                            <label>平台</label>
                            <select name="_bkh_videos[<?php echo $i; ?>][platform]">
                                <?php foreach ( $platforms as $k => $label ) : ?>
                                    <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $v['platform'] ?? '', $k ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <label>视频标题</label>
                            <input type="text" name="_bkh_videos[<?php echo $i; ?>][video_title]" value="<?php echo esc_attr( $v['video_title'] ?? '' ); ?>" class="widefat">
                        </p>
                        <p>
                            <label>视频链接 (URL)</label>
                            <input type="url" name="_bkh_videos[<?php echo $i; ?>][video_url]" value="<?php echo esc_attr( $v['video_url'] ?? '' ); ?>" class="widefat" placeholder="https://www.bilibili.com/video/BVxxxx">
                        </p>
                        <p>
                            <label>视频 ID (可选，留空则自动从 URL 提取)</label>
                            <input type="text" name="_bkh_videos[<?php echo $i; ?>][video_id]" value="<?php echo esc_attr( $v['video_id'] ?? '' ); ?>" class="widefat">
                        </p>
                        <p>
                            <label>嵌入代码 (可选，原 embed iframe)</label>
                            <textarea name="_bkh_videos[<?php echo $i; ?>][embed_code]" rows="2" class="widefat"><?php echo esc_textarea( $v['embed_code'] ?? '' ); ?></textarea>
                        </p>
                        <p>
                            <label>封面图 URL</label>
                            <input type="url" name="_bkh_videos[<?php echo $i; ?>][cover_image]" value="<?php echo esc_attr( $v['cover_image'] ?? '' ); ?>" class="widefat">
                        </p>
                        <p class="bkh-row">
                            <span><label>时长 (ISO8601, 如 PT3M21S)</label><input type="text" name="_bkh_videos[<?php echo $i; ?>][duration]" value="<?php echo esc_attr( $v['duration'] ?? '' ); ?>"></span>
                            <span><label>发布时间</label><input type="date" name="_bkh_videos[<?php echo $i; ?>][publish_date]" value="<?php echo esc_attr( $v['publish_date'] ?? '' ); ?>"></span>
                            <span><label>排序</label><input type="number" name="_bkh_videos[<?php echo $i; ?>][display_order]" value="<?php echo esc_attr( $v['display_order'] ?? 0 ); ?>" style="width:80px"></span>
                            <span><label><input type="checkbox" name="_bkh_videos[<?php echo $i; ?>][is_primary]" value="1" <?php checked( ! empty( $v['is_primary'] ) ); ?>> 主视频</label></span>
                        </p>
                        <p class="bkh-row">
                            <span><label>账号名称</label><input type="text" name="_bkh_videos[<?php echo $i; ?>][account_name]" value="<?php echo esc_attr( $v['account_name'] ?? '' ); ?>"></span>
                            <span><label>账号主页 URL</label><input type="url" name="_bkh_videos[<?php echo $i; ?>][account_url]" value="<?php echo esc_attr( $v['account_url'] ?? '' ); ?>"></span>
                            <span><label>追踪参数</label><input type="text" name="_bkh_videos[<?php echo $i; ?>][tracking_code]" value="<?php echo esc_attr( $v['tracking_code'] ?? '' ); ?>"></span>
                        </p>
                    </div>
                    <button type="button" class="button bkh-remove">删除</button>
                </div>
            <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-secondary bkh-add">+ 添加一个视频</button></p>
        </div>
        <?php
    }

    public function render_faqs( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $faqs = bkh_get_faqs( $post->ID );
        if ( empty( $faqs ) ) $faqs = array( array() );
        ?>
        <div class="bkh-repeater" data-name="_bkh_faqs">
            <div class="bkh-rep-items">
                <?php foreach ( $faqs as $i => $f ) : ?>
                <div class="bkh-rep-item">
                    <p><label>问题</label>
                        <input type="text" name="_bkh_faqs[<?php echo $i; ?>][question]" value="<?php echo esc_attr( $f['question'] ?? '' ); ?>" class="widefat">
                    </p>
                    <p><label>答案 (支持简单 HTML)</label>
                        <textarea name="_bkh_faqs[<?php echo $i; ?>][answer]" rows="3" class="widefat"><?php echo esc_textarea( $f['answer'] ?? '' ); ?></textarea>
                    </p>
                    <button type="button" class="button bkh-remove">删除此 FAQ</button>
                </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-secondary bkh-add">+ 添加 FAQ</button></p>
        </div>
        <?php
    }

    public function render_featured( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $val = (int) get_post_meta( $post->ID, '_bkh_featured_priority', true );
        ?>
        <p><input type="number" name="_bkh_featured_priority" value="<?php echo esc_attr( $val ); ?>" min="0" max="999" style="width:80px"></p>
        <p class="description">数值越大越优先在知识中心首页/专题页"推荐内容"区出现。留空或 0 = 按时间排序。</p>
        <?php
    }

    public function render_topic_hero( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $sub = get_post_meta( $post->ID, '_bkh_hero_subtitle', true );
        $img = get_post_meta( $post->ID, '_bkh_hero_image', true );
        ?>
        <p><label>专题副标题</label>
            <input type="text" name="_bkh_hero_subtitle" value="<?php echo esc_attr( $sub ); ?>" class="widefat" placeholder="例：耳鸣不是病，但需要被认真对待">
        </p>
        <p><label>专题封面图 URL (可选，留空则用文章特色图)</label>
            <input type="url" name="_bkh_hero_image" value="<?php echo esc_attr( $img ); ?>" class="widefat">
        </p>
        <p class="description">专题标题取自文章标题；简介取自摘要 (Excerpt)；正文区取自编辑器内容。</p>
        <?php
    }

    public function render_scenes( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $scenes = bkh_get_scenes( $post->ID );
        if ( empty( $scenes ) ) $scenes = array( array() );
        ?>
        <div class="bkh-repeater" data-name="_bkh_scenes">
            <div class="bkh-rep-items">
                <?php foreach ( $scenes as $i => $s ) : ?>
                <div class="bkh-rep-item">
                    <p class="bkh-row">
                        <span><label>场景标题</label>
                            <input type="text" name="_bkh_scenes[<?php echo $i; ?>][scene_title]" value="<?php echo esc_attr( $s['scene_title'] ?? '' ); ?>" class="widefat">
                        </span>
                        <span><label>图标 emoji 或 CSS class</label>
                            <input type="text" name="_bkh_scenes[<?php echo $i; ?>][scene_icon]" value="<?php echo esc_attr( $s['scene_icon'] ?? '' ); ?>" placeholder="🌙">
                        </span>
                    </p>
                    <p><label>说明</label>
                        <textarea name="_bkh_scenes[<?php echo $i; ?>][scene_description]" rows="2" class="widefat"><?php echo esc_textarea( $s['scene_description'] ?? '' ); ?></textarea>
                    </p>
                    <p><label>跳转链接</label>
                        <input type="text" name="_bkh_scenes[<?php echo $i; ?>][scene_link]" value="<?php echo esc_attr( $s['scene_link'] ?? '' ); ?>" class="widefat" placeholder="/knowledge/tinnitus/night-tinnitus/">
                    </p>
                    <button type="button" class="button bkh-remove">删除场景</button>
                </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-secondary bkh-add">+ 添加场景卡片</button></p>
        </div>
        <?php
    }

    public function render_reasons( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $reasons = bkh_get_reasons( $post->ID );
        if ( empty( $reasons ) ) $reasons = array( array() );
        ?>
        <div class="bkh-repeater" data-name="_bkh_reasons">
            <div class="bkh-rep-items">
                <?php foreach ( $reasons as $i => $r ) : ?>
                <div class="bkh-rep-item">
                    <p><label>原因标题</label>
                        <input type="text" name="_bkh_reasons[<?php echo $i; ?>][reason_title]" value="<?php echo esc_attr( $r['reason_title'] ?? '' ); ?>" class="widefat">
                    </p>
                    <p><label>说明</label>
                        <textarea name="_bkh_reasons[<?php echo $i; ?>][reason_description]" rows="3" class="widefat"><?php echo esc_textarea( $r['reason_description'] ?? '' ); ?></textarea>
                    </p>
                    <button type="button" class="button bkh-remove">删除原因</button>
                </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-secondary bkh-add">+ 添加原因</button></p>
        </div>
        <?php
    }

    public function render_cta( $post ) {
        wp_nonce_field( 'bkh_save_meta', 'bkh_meta_nonce' );
        $fields = array( 'cta_title', 'cta_description', 'cta_button_text', 'cta_button_url', 'cta_secondary_text', 'cta_secondary_url' );
        $defaults = get_option( BKH_OPT_CTA, bkh_default_cta_settings() );
        ?>
        <p class="description">留空 = 使用插件设置中的默认 CTA。</p>
        <?php foreach ( $fields as $f ) :
            $val = get_post_meta( $post->ID, "_bkh_$f", true ); ?>
            <p><label><?php echo esc_html( $f ); ?> (默认: <code><?php echo esc_html( $defaults[$f] ?? '' ); ?></code>)</label>
                <input type="text" name="_bkh_<?php echo esc_attr( $f ); ?>" value="<?php echo esc_attr( $val ); ?>" class="widefat">
            </p>
        <?php endforeach;
    }

    /* ======================================================
     * SAVE
     * ====================================================== */
    public function save_post( $post_id, $post ) {
        if ( ! isset( $_POST['bkh_meta_nonce'] ) || ! wp_verify_nonce( $_POST['bkh_meta_nonce'], 'bkh_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! in_array( $post->post_type, array( 'knowledge_article', 'knowledge_topic' ), true ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Repeaters
        $this->save_repeater( $post_id, '_bkh_videos',  array( 'platform', 'video_title', 'video_url', 'video_id', 'embed_code', 'cover_image', 'duration', 'publish_date', 'display_order', 'is_primary', 'account_name', 'account_url', 'tracking_code' ) );
        $this->save_repeater( $post_id, '_bkh_faqs',    array( 'question', 'answer' ) );
        $this->save_repeater( $post_id, '_bkh_scenes',  array( 'scene_title', 'scene_description', 'scene_link', 'scene_icon' ) );
        $this->save_repeater( $post_id, '_bkh_reasons', array( 'reason_title', 'reason_description' ) );

        // Single fields
        $singles = array(
            '_bkh_featured_priority' => 'intval',
            '_bkh_hero_subtitle'     => 'sanitize_text_field',
            '_bkh_hero_image'        => 'esc_url_raw',
            '_bkh_cta_title'         => 'sanitize_text_field',
            '_bkh_cta_description'   => 'sanitize_textarea_field',
            '_bkh_cta_button_text'   => 'sanitize_text_field',
            '_bkh_cta_button_url'    => 'sanitize_text_field',
            '_bkh_cta_secondary_text'=> 'sanitize_text_field',
            '_bkh_cta_secondary_url' => 'sanitize_text_field',
        );
        foreach ( $singles as $key => $cb ) {
            if ( isset( $_POST[ $key ] ) ) {
                $val = call_user_func( $cb, wp_unslash( $_POST[ $key ] ) );
                if ( $val === '' || $val === 0 ) {
                    delete_post_meta( $post_id, $key );
                } else {
                    update_post_meta( $post_id, $key, $val );
                }
            }
        }
    }

    private function save_repeater( $post_id, $key, $sub_fields ) {
        if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
            delete_post_meta( $post_id, $key );
            return;
        }
        $clean = array();
        foreach ( $_POST[ $key ] as $row ) {
            if ( ! is_array( $row ) ) continue;
            $item = array();
            $has_content = false;
            foreach ( $sub_fields as $sf ) {
                $raw = isset( $row[ $sf ] ) ? wp_unslash( $row[ $sf ] ) : '';
                if ( in_array( $sf, array( 'answer', 'embed_code', 'scene_description', 'reason_description' ), true ) ) {
                    $item[ $sf ] = wp_kses_post( $raw );
                } elseif ( in_array( $sf, array( 'video_url', 'cover_image', 'account_url', 'scene_link' ), true ) ) {
                    $item[ $sf ] = esc_url_raw( $raw );
                } elseif ( in_array( $sf, array( 'is_primary', 'display_order' ), true ) ) {
                    $item[ $sf ] = (int) $raw;
                } else {
                    $item[ $sf ] = sanitize_text_field( $raw );
                }
                if ( ! empty( $item[ $sf ] ) ) $has_content = true;
            }
            if ( $has_content ) $clean[] = $item;
        }
        if ( empty( $clean ) ) {
            delete_post_meta( $post_id, $key );
        } else {
            update_post_meta( $post_id, $key, $clean );
        }
    }
}
