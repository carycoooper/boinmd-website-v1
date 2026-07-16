<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Service_Page {
    private static $instance = null;
    private $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_rewrite' ) );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );
        add_action( 'template_redirect', array( $this, 'render_page' ) );
        add_filter( 'document_title_parts', array( $this, 'title_parts' ), 30 );
        add_filter( 'pre_get_document_title', array( $this, 'document_title' ), 30 );
        add_filter( 'wpseo_title', array( $this, 'seo_title' ), 99 );
        add_filter( 'wpseo_metadesc', array( $this, 'seo_description' ), 99 );
        add_filter( 'wpseo_opengraph_title', array( $this, 'seo_title' ), 99 );
        add_filter( 'wpseo_opengraph_desc', array( $this, 'seo_description' ), 99 );
        add_filter( 'wpseo_canonical', array( $this, 'seo_canonical' ), 99 );
        add_filter( 'wpseo_opengraph_url', array( $this, 'seo_canonical' ), 99 );
        add_filter( 'wpseo_robots', array( $this, 'seo_robots' ), 99 );
        add_filter( 'wp_robots', array( $this, 'wp_robots' ), 99 );
        add_filter( 'the_generator', '__return_empty_string', 99 );
        remove_action( 'wp_head', 'wp_generator' );
        add_action( 'wp_head', array( $this, 'output_json_ld' ), 30 );
        add_action( 'init', array( $this, 'maybe_flush_rewrite' ), 30 );
        add_shortcode( 'boin_hearing_service_home', array( $this, 'shortcode_home' ) );
        add_shortcode( 'boin_hearing_test_intro', array( $this, 'shortcode_test_intro' ) );
        add_shortcode( 'boin_hearing_calibration', array( $this, 'shortcode_test_calibration' ) );
        add_shortcode( 'boin_hearing_test', array( $this, 'shortcode_test' ) );
        add_shortcode( 'boin_hearing_test_result', array( $this, 'shortcode_test_result' ) );
        add_shortcode( 'boin_hearing_test_stopped', array( $this, 'shortcode_test_stopped' ) );
        add_shortcode( 'boin_hearing_request', array( $this, 'shortcode_request' ) );
        add_shortcode( 'boin_hearing_request_success', array( $this, 'shortcode_request_success' ) );
    }

    public function register_rewrite() {
        add_rewrite_rule( '^hearing-service(?:/.*)?$', 'index.php?bhs_service_page=1', 'top' );
    }

    public function query_vars( $vars ) {
        $vars[] = 'bhs_service_page';
        return $vars;
    }

    public function is_service_page() {
        $path = $this->path();
        return (int) get_query_var( 'bhs_service_page' ) === 1 || strpos( $path, '/hearing-service' ) === 0;
    }

    private function path() {
        $path = parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
        $path = '/' . trim( (string) $path, '/' );
        return $path === '/' ? '/' : $path;
    }

    private function route_key() {
        $path = untrailingslashit( $this->path() );
        $map = array(
            '/hearing-service' => 'home',
            '/hearing-service/test-intro' => 'test_intro',
            '/hearing-service/test-calibration' => 'test_calibration',
            '/hearing-service/test' => 'test',
            '/hearing-service/test-result' => 'test_result',
            '/hearing-service/test-stopped' => 'test_stopped',
            '/hearing-service/request' => 'request',
            '/hearing-service/request-success' => 'request_success',
        );
        return $map[ $path ] ?? 'home';
    }

    private function route_meta() {
        $brand = '博音BOINMD';
        $meta = array(
            'home' => array(
                'path' => '',
                'title' => '在线听力筛查｜六频左右耳自测 - 博音BOINMD',
                'description' => '约3分钟完成免费的六频在线听力筛查，左右耳分别测试，结果仅供远程验配服务沟通参考，不替代专业听力检查或诊断。',
                'index' => true,
            ),
            'test_intro' => array(
                'path' => 'test-intro/',
                'title' => '听力筛查准备 - 博音BOINMD',
                'description' => '开始六频在线听力筛查前，请确认安静环境、耳机佩戴和测试注意事项。',
                'index' => false,
            ),
            'test_calibration' => array(
                'path' => 'test-calibration/',
                'title' => '设备音量确认 - 博音BOINMD',
                'description' => '播放参考声音并确认设备音量，帮助后续左右耳六频筛查保持相对一致。',
                'index' => false,
            ),
            'test' => array(
                'path' => 'test/',
                'title' => '六频听力筛查进行中 - 博音BOINMD',
                'description' => '按步骤完成左右耳六个频率的在线听力筛查。',
                'index' => false,
            ),
            'test_result' => array(
                'path' => 'test-result/',
                'title' => '听力筛查结果 - 博音BOINMD',
                'description' => '查看本次六频听力筛查参考结果，并可选择提交助听器调试需求。',
                'index' => false,
            ),
            'test_stopped' => array(
                'path' => 'test-stopped/',
                'title' => '听力筛查已停止 - 博音BOINMD',
                'description' => '本次听力筛查已停止，可返回首页后重新开始。',
                'index' => false,
            ),
            'request' => array(
                'path' => 'request/',
                'title' => '提交助听器调试需求 - 博音BOINMD',
                'description' => '提交悦听礼赠款助听器使用问题，后台验配师会结合信息进行远程服务沟通。',
                'index' => false,
            ),
            'request_success' => array(
                'path' => 'request-success/',
                'title' => '调试需求已提交 - 博音BOINMD',
                'description' => '助听器调试需求已提交成功，验配师会及时查看并处理。',
                'index' => false,
            ),
        );
        $route = $this->route_key();
        return $meta[ $route ] ?? $meta['home'];
    }

    private function route_canonical_url() {
        $meta = $this->route_meta();
        return bhs_service_url( $meta['path'] );
    }

    public function dedupe_title_tags( $html ) {
        if ( ! is_string( $html ) || stripos( $html, '<title' ) === false ) return $html;
        if ( ! preg_match_all( '#<title\b[^>]*>.*?</title>\s*#is', $html, $matches, PREG_OFFSET_CAPTURE ) ) return $html;
        if ( count( $matches[0] ) < 2 ) return $html;

        for ( $i = count( $matches[0] ) - 2; $i >= 0; $i-- ) {
            $match = $matches[0][ $i ];
            $html = substr_replace( $html, '', $match[1], strlen( $match[0] ) );
        }
        return $html;
    }

    public function title_parts( $title ) {
        if ( $this->is_service_page() ) {
            $title['title'] = $this->route_meta()['title'];
            unset( $title['tagline'] );
        }
        return $title;
    }

    public function document_title( $title ) {
        if ( $this->is_service_page() ) return $this->route_meta()['title'];
        return $title;
    }

    public function seo_title( $title ) {
        if ( $this->is_service_page() ) return $this->route_meta()['title'];
        return $title;
    }

    public function seo_description( $description ) {
        if ( $this->is_service_page() ) return $this->route_meta()['description'];
        return $description;
    }

    public function seo_canonical( $url ) {
        if ( $this->is_service_page() ) return $this->route_canonical_url();
        return $url;
    }

    public function seo_robots( $robots ) {
        if ( ! $this->is_service_page() ) return $robots;
        return $this->route_meta()['index'] ? 'index, follow' : 'noindex, follow';
    }

    public function wp_robots( $robots ) {
        if ( ! $this->is_service_page() ) return $robots;
        if ( $this->route_meta()['index'] ) {
            unset( $robots['noindex'] );
            $robots['index'] = true;
        } else {
            unset( $robots['index'] );
            $robots['noindex'] = true;
        }
        $robots['follow'] = true;
        return $robots;
    }

    public function output_json_ld() {
        if ( ! $this->is_service_page() || $this->route_key() !== 'home' ) return;

        $url = $this->route_canonical_url();
        $data = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'MedicalWebPage',
                    '@id' => $url . '#medical-webpage',
                    'name' => '在线听力筛查',
                    'url' => $url,
                    'description' => $this->route_meta()['description'],
                    'inLanguage' => 'zh-CN',
                    'about' => array(
                        '@type' => 'MedicalCondition',
                        'name' => '听力变化',
                    ),
                    'isPartOf' => array(
                        '@type' => 'WebSite',
                        'name' => '博音BOINMD',
                        'url' => home_url( '/' ),
                    ),
                ),
                array(
                    '@type' => 'WebApplication',
                    '@id' => $url . '#web-application',
                    'name' => '六频在线听力筛查',
                    'url' => $url,
                    'applicationCategory' => 'HealthApplication',
                    'operatingSystem' => 'Web',
                    'inLanguage' => 'zh-CN',
                    'description' => '通过左右耳六个频率的在线筛查，为远程验配服务沟通提供参考。',
                    'offers' => array(
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'CNY',
                    ),
                ),
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

    public function maybe_flush_rewrite() {
        $key = 'bhs_rewrite_version';
        if ( get_option( $key ) !== BHS_VERSION ) {
            $this->register_rewrite();
            flush_rewrite_rules( false );
            update_option( $key, BHS_VERSION, false );
        }
    }

    public function render_page() {
        if ( ! $this->is_service_page() ) return;

        $prototype = BHS_DIR . 'templates/hearing-test-mobile.html';
        if ( is_readable( $prototype ) ) {
            status_header( 200 );
            header( 'Content-Type: text/html; charset=UTF-8' );
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            header( 'Pragma: no-cache' );
            $html = file_get_contents( $prototype );
            $data = '<script>window.BHS_DATA=' . wp_json_encode( array(
                'restUrl' => esc_url_raw( rest_url( 'boin-hearing/v1/' ) ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ';</script>';
            echo str_replace( '<body>', '<body>' . $data, $html );
            exit;
        }

        bhs_enqueue_frontend_assets();
        status_header( 200 );
        remove_action( 'wp_head', '_wp_render_title_tag', 1 );
        ob_start( array( $this, 'dedupe_title_tags' ) );
        get_header();
        echo '<main class="bhs-service-page" aria-label="Boin hearing service">';
        $method = 'render_' . $this->route_key();
        if ( method_exists( $this, $method ) ) $this->$method();
        else $this->render_home();
        echo '</main>';
        get_footer();
        exit;
    }

    private function back_home() {
        echo '<div class="bhs-top-back"><a href="' . esc_url( bhs_service_url() ) . '">← 返回远程服务首页</a></div>';
    }

    public function render_home() {
        echo '<section class="bhs-card bhs-home-grid" data-bhs-page="home">';
        echo '<article class="bhs-entry-card"><span>01</span><h2>六频在线听力筛查</h2><p>通过六个频率，了解左右耳对不同声音的响应情况，结果仅供远程服务沟通参考。</p><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url( 'test-intro/' ) ) . '">开始听力筛查</a></article>';
        echo '<article class="bhs-entry-card"><span>02</span><h2>提交调试需求</h2><p>反馈人声不清、声音刺耳、环境声过大、啸叫、音量不合适等助听器使用问题。</p><a class="bhs-btn bhs-btn-ghost" href="' . esc_url( bhs_service_url( 'request/' ) ) . '">提交调试需求</a></article>';
        echo '</section>';
    }

    public function render_test_intro() {
        $this->back_home();
        echo '<section class="bhs-card bhs-flow-card" data-bhs-page="test-intro">';
        echo '<div class="bhs-progress"><span class="is-current">测试准备</span><span>设备确认</span><span>左耳测试</span><span>右耳测试</span><span>测试完成</span></div>';
        echo '<h2>开始测试前，请做好以下准备</h2>';
        echo '<ul class="bhs-check-list"><li>请尽量选择安静的环境</li><li>建议佩戴状态正常的耳机</li><li>测试时请取下助听器</li><li>请将设备媒体音量调至舒适范围</li><li>左耳和右耳需要分别完成测试</li><li>测试过程中如感觉刺耳或不适，请立即停止</li><li>本测试仅用于远程服务沟通参考，不替代专业听力检查</li></ul>';
        echo '<label class="bhs-consent"><input type="checkbox" data-bhs-intro-consent> 我已阅读并了解以上测试说明</label>';
        echo '<div class="bhs-actions"><a class="bhs-btn bhs-btn-primary is-disabled" data-bhs-intro-next href="' . esc_url( bhs_service_url( 'test-calibration/' ) ) . '">下一步：设备音量确认</a></div>';
        echo '</section>';
    }

    public function render_test_calibration() {
        $this->back_home();
        echo '<section class="bhs-card bhs-flow-card" data-bhs-page="calibration">';
        echo '<div class="bhs-progress"><span>测试准备</span><span class="is-current">设备确认</span><span>左耳测试</span><span>右耳测试</span><span>测试完成</span></div>';
        echo '<h2>设备音量确认</h2><p>接下来会播放一段参考声音，请将手机或电脑音量调整到清晰、舒适且不刺耳的位置。该步骤不是医学声学校准。</p>';
        echo '<div class="bhs-actions"><button class="bhs-btn bhs-btn-primary is-disabled" data-bhs-play-reference type="button" disabled>播放参考声音</button></div>';
        echo '<div class="bhs-calibration-options" hidden><button data-bhs-volume="small" type="button">声音太小</button><button data-bhs-volume="ok" type="button">音量合适</button><button data-bhs-volume="large" type="button">声音太大</button></div>';
        echo '<p class="bhs-form-msg" aria-live="polite"></p>';
        echo '<div class="bhs-actions"><button class="bhs-btn bhs-btn-primary is-disabled" data-bhs-start-test type="button" disabled>开始左耳测试</button><a class="bhs-btn bhs-btn-ghost" href="' . esc_url( bhs_service_url( 'test-intro/' ) ) . '">返回测试准备页</a></div>';
        echo '</section>';
    }

    public function render_test() {
        $this->back_home();
        $session = $this->current_session();
        if ( ! $session ) { $this->expired(); return; }
        $ear_label = $session->current_ear === 'right' ? '右耳' : '左耳';
        $freq_index = array_search( (int) $session->current_frequency, $this->freqs, true );
        $freq_index = $freq_index === false ? 0 : $freq_index;
        $overall = (int) $session->completed_steps + 1;
        echo '<section class="bhs-card bhs-flow-card bhs-test-step" data-bhs-page="test" data-session="' . esc_attr( $session->session_uuid ) . '" data-token="' . esc_attr( $_GET['token'] ?? '' ) . '" data-ear="' . esc_attr( $session->current_ear ) . '" data-frequency="' . esc_attr( $session->current_frequency ) . '" data-level="' . esc_attr( $session->current_level ) . '">';
        echo '<div class="bhs-progress"><span>测试准备</span><span>设备确认</span><span class="' . ( $session->current_ear === 'left' ? 'is-current' : '' ) . '">左耳测试</span><span class="' . ( $session->current_ear === 'right' ? 'is-current' : '' ) . '">右耳测试</span><span>测试完成</span></div>';
        echo '<p class="bhs-step-count">' . esc_html( $ear_label ) . '测试 ' . esc_html( $freq_index + 1 ) . '/6　整体进度 ' . esc_html( $overall ) . '/12</p>';
        echo '<h2>' . esc_html( $ear_label ) . '测试</h2><p class="bhs-current-frequency">' . esc_html( $session->current_frequency ) . ' Hz · Level ' . esc_html( $session->current_level ) . '</p>';
        echo '<p>点击播放后，请判断是否听到了声音。播放结束后才能作答。</p>';
        echo '<div class="bhs-tone-box"><span>' . esc_html( $session->current_frequency ) . ' Hz</span><small>相对音量等级 ' . esc_html( $session->current_level ) . '</small></div>';
        echo '<p class="bhs-form-msg" aria-live="polite"></p>';
        echo '<div class="bhs-actions"><button class="bhs-btn bhs-btn-primary" data-bhs-play-tone type="button">播放声音</button><button class="bhs-btn bhs-btn-ghost" data-bhs-replay-tone type="button" disabled>重新播放</button></div>';
        echo '<div class="bhs-actions"><button class="bhs-btn bhs-btn-primary" data-bhs-answer="heard" type="button" disabled>听到了</button><button class="bhs-btn bhs-btn-ghost" data-bhs-answer="not_heard" type="button" disabled>没听到</button></div>';
        echo '<details class="bhs-stop"><summary>停止测试</summary><div class="bhs-stop-options"><select data-bhs-stop-reason><option>声音刺耳</option><option>耳朵不舒服</option><option>出现头晕</option><option>耳鸣突然加重</option><option>设备播放异常</option><option>环境太吵</option><option>暂时不想继续</option><option>其他原因</option></select><button class="bhs-btn bhs-btn-ghost" data-bhs-stop-test type="button">确认停止</button></div></details>';
        echo '</section>';
    }



    private function result_overview( $rows ) {
        $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );
        $ears = array( 'left' => '左耳', 'right' => '右耳' );
        $by_ear = array( 'left' => array(), 'right' => array() );

        foreach ( $rows as $row ) {
            $ear = isset( $row->ear ) ? sanitize_key( $row->ear ) : '';
            $freq = isset( $row->frequency ) ? (int) $row->frequency : 0;
            if ( ! isset( $by_ear[ $ear ] ) || ! in_array( $freq, $freqs, true ) ) continue;
            $level = isset( $row->relative_level ) && $row->relative_level !== null && $row->relative_level !== '' ? (int) $row->relative_level : null;
            $by_ear[ $ear ][ $freq ] = $level === null ? null : bhs_level_to_estimated_dbhl( $level );
        }

        $cards = array();
        $focus = array();
        foreach ( $ears as $ear => $label ) {
            $values = array_values( array_filter( $by_ear[ $ear ], static function( $value ) { return is_numeric( $value ); } ) );
            $avg = bhs_average_number( $values );
            $cards[ $ear ] = array( 'label' => $label, 'value' => bhs_db_label( $avg ) );

            $speech = bhs_average_number( array_filter( array( $by_ear[ $ear ][1000] ?? null, $by_ear[ $ear ][2000] ?? null ), 'is_numeric' ) );
            $high = bhs_average_number( array_filter( array( $by_ear[ $ear ][4000] ?? null, $by_ear[ $ear ][8000] ?? null ), 'is_numeric' ) );
            if ( $high !== null && $high >= 55 ) $focus[] = $label . '：高频响应偏弱，验配师可重点关注人声清晰度、尖锐感和嘈杂环境沟通。';
            elseif ( $speech !== null && $speech >= 55 ) $focus[] = $label . '：语音频段响应偏弱，验配师可重点关注家人对话、看电视和电话沟通。';
        }

        return array(
            'cards' => $cards,
            'focus' => empty( $focus ) ? '筛查已完成，建议结合日常听声场景继续观察。' : implode( ' ', array_unique( $focus ) ),
        );
    }

    public function render_test_result() {
        $this->back_home();
        $session = $this->current_session();
        if ( ! $session ) { $this->expired(); return; }
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::results_table() . ' WHERE session_id = %d ORDER BY ear ASC, frequency ASC', $session->id ) );
        $overview = $this->result_overview( $rows );
        $devices = bhs_get_active_devices();
        $device_id = ! empty( $devices ) ? (int) $devices[0]->ID : 0;
        $token = sanitize_text_field( $_GET['token'] ?? '' );

        echo '<section class="bhs-card bhs-flow-card"><h2>测试结果</h2><p>本次结果仅作远程服务沟通参考，不替代专业听力检查或诊断。</p>';
        echo '<div class="bhs-result-grid"><div><strong>测试记录编号</strong><span>' . esc_html( $session->session_uuid ) . '</span></div><div><strong>完成时间</strong><span>' . esc_html( $session->completed_at ?: bhs_current_time() ) . '</span></div></div>';
        echo '<div class="bhs-result-overview">';
        echo '<div class="bhs-result-pill"><strong>左耳平均估算</strong><span>' . esc_html( $overview['cards']['left']['value'] ?? '暂无足够数据' ) . '</span></div>';
        echo '<div class="bhs-result-pill"><strong>右耳平均估算</strong><span>' . esc_html( $overview['cards']['right']['value'] ?? '暂无足够数据' ) . '</span></div>';
        echo '<div class="bhs-result-pill bhs-result-focus"><strong>重点关注</strong><span>' . esc_html( $overview['focus'] ) . '</span></div>';
        echo '</div>';
        if ( $device_id ) {
            echo '<section class="bhs-result-lead"><h3>获取完整报告与验配师免费解读</h3><p>留下手机号后，验配师会结合本次筛查记录和使用场景，给出更具体的助听器调试建议。</p><form class="bhs-form" data-bhs-request-form>';
            echo '<input type="hidden" name="session_uuid" value="' . esc_attr( $session->session_uuid ) . '"><input type="hidden" name="session_token" value="' . esc_attr( $token ) . '">';
            echo '<input type="hidden" name="device_id" value="' . esc_attr( $device_id ) . '"><input type="hidden" name="main_problem" value="申请完整报告与验配师免费解读">';
            echo '<input type="hidden" name="usage_scene" value="六频在线听力筛查结果页"><input type="hidden" name="ear_description" value=""><input type="hidden" name="description" value="用户已完成六频在线听力筛查，希望获取完整报告和验配师免费解读。">';
            echo '<label>手机号<input type="tel" name="phone" required inputmode="numeric" maxlength="11" pattern="[0-9]{11}" placeholder="请输入11位手机号，便于验配师联系"></label>';
            echo '<label class="bhs-consent"><input type="checkbox" name="privacy_confirmed" value="1" required> 我同意将手机号和本次筛查记录用于远程服务沟通</label><button class="bhs-btn bhs-btn-primary" type="submit">发送完整报告并免费解读</button><p class="bhs-form-msg" aria-live="polite"></p></form></section>';
        }
        echo '<details class="bhs-result-details"><summary>查看12项测试明细</summary><div class="bhs-table-wrap"><table class="bhs-result-table"><thead><tr><th>耳侧</th><th>频率</th><th>相对听见等级</th><th>状态</th></tr></thead><tbody>';
        foreach ( $rows as $row ) echo '<tr><td>' . esc_html( $row->ear === 'right' ? '右耳' : '左耳' ) . '</td><td>' . esc_html( $row->frequency ) . ' Hz</td><td>' . esc_html( $row->relative_level ?: '最高等级未响应' ) . '</td><td>' . esc_html( $row->result_status ) . '</td></tr>';
        echo '</tbody></table></div></details><p class="bhs-note">提示：相对等级越高，通常表示该频率需要更高播放强度才有反应；完整解读需结合用户主诉、佩戴反馈和真实生活场景。</p>';
        echo '<div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url() ) . '">返回远程服务首页</a><a class="bhs-btn bhs-btn-ghost" href="' . esc_url( bhs_service_url( 'test-intro/' ) ) . '">替父母或家人再测一次</a></div></section>';
    }

    public function render_test_stopped() {
        $this->back_home();
        echo '<section class="bhs-card bhs-flow-card"><h2>测试已停止</h2><p>测试已停止。如出现耳痛、眩晕、突然听力下降等情况，请停止使用并及时咨询专业机构。</p><div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url() ) . '">返回远程服务首页</a></div></section>';
    }

    public function render_request() {
        $this->back_home();
        $devices = bhs_get_active_devices();
        $session = $this->current_session();
        echo '<section class="bhs-card bhs-flow-card" data-bhs-page="request"><h2>提交远程调试需求</h2><p>请选择设备型号，并描述当前遇到的问题。后台验配师收到后会及时查看和处理。</p>';
        if ( $session ) echo '<p class="bhs-linked-session">已关联本次六频听力筛查记录</p>';
        echo '<form class="bhs-form" data-bhs-request-form><input type="hidden" name="session_uuid" value="' . esc_attr( $session ? $session->session_uuid : '' ) . '"><input type="hidden" name="session_token" value="' . esc_attr( sanitize_text_field( $_GET['token'] ?? '' ) ) . '">';
        echo '<label>手机号<input type="tel" name="phone" required inputmode="numeric" maxlength="11" pattern="[0-9]{11}" placeholder="请输入11位手机号"></label><label>设备型号<select name="device_id" required>';
        foreach ( $devices as $device ) echo '<option value="' . esc_attr( $device->ID ) . '">' . esc_html( get_the_title( $device ) ) . '</option>';
        echo '</select></label><label>主要问题<input type="text" name="main_problem" required placeholder="例如：人声不清楚、环境声偏大"></label><label>使用场景<input type="text" name="usage_scene" placeholder="例如：家庭交流、看电视、户外"></label><label>左右耳情况<input type="text" name="ear_description" placeholder="例如：左耳更明显、双耳都有"></label>';
        echo '<fieldset class="bhs-options"><legend>常见反馈</legend><label><input type="checkbox" name="feedback_options[]" value="啸叫"> 出现啸叫</label><label><input type="checkbox" name="feedback_options[]" value="刺耳"> 声音刺耳</label><label><input type="checkbox" name="feedback_options[]" value="人声不清楚"> 人声不清楚</label><label><input type="checkbox" name="feedback_options[]" value="环境声过大"> 环境声过大</label><label><input type="checkbox" name="feedback_options[]" value="电视声音偏小"> 电视声音偏小</label><label><input type="checkbox" name="feedback_options[]" value="声音闷"> 声音闷</label><label><input type="checkbox" name="feedback_options[]" value="断音不稳定"> 断音或声音不稳定</label></fieldset>';
        echo '<label>补充说明<textarea name="description" rows="5" placeholder="请补充描述问题出现的时间、场景和感受"></textarea></label><label class="bhs-consent"><input type="checkbox" name="privacy_confirmed" value="1" required> 我已阅读并同意信息用于本次远程服务处理</label><button class="bhs-btn bhs-btn-primary" type="submit">提交需求</button><p class="bhs-form-msg" aria-live="polite"></p></form></section>';
    }

    public function render_request_success() {
        $this->back_home();
        echo '<section class="bhs-card bhs-flow-card"><h2>您的调试需求已提交成功</h2><p>后台验配师会及时查看并处理。后续如需进一步沟通，将通过预留手机号联系您。</p><div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url() ) . '">返回远程服务首页</a></div></section>';
    }

    private function current_session() {
        global $wpdb;
        $uuid = sanitize_text_field( wp_unslash( $_GET['session'] ?? '' ) );
        $token = (string) ( $_GET['token'] ?? '' );
        if ( ! $uuid || ! $token ) return null;
        $row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::sessions_table() . ' WHERE session_uuid = %s', $uuid ) );
        if ( ! $row || ! wp_check_password( $token, $row->session_token ) ) return null;
        return $row;
    }

    private function expired() {
        echo '<section class="bhs-card bhs-flow-card"><h2>当前测试会话已过期</h2><p>请重新开始测试，避免生成误导性结果。</p><div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url( 'test-intro/' ) ) . '">重新开始测试</a><a class="bhs-btn bhs-btn-ghost" href="' . esc_url( bhs_service_url() ) . '">返回首页</a></div></section>';
    }
    private function shortcode_render( $method ) {
        bhs_enqueue_frontend_assets();
        ob_start();
        echo '<div class="bhs-service-page bhs-shortcode-page">';
        if ( method_exists( $this, $method ) ) $this->$method();
        echo '</div>';
        return ob_get_clean();
    }

    public function shortcode_home() { return $this->shortcode_render( 'render_home' ); }
    public function shortcode_test_intro() { return $this->shortcode_render( 'render_test_intro' ); }
    public function shortcode_test_calibration() { return $this->shortcode_render( 'render_test_calibration' ); }
    public function shortcode_test() { return $this->shortcode_render( 'render_test' ); }
    public function shortcode_test_result() { return $this->shortcode_render( 'render_test_result' ); }
    public function shortcode_test_stopped() { return $this->shortcode_render( 'render_test_stopped' ); }
    public function shortcode_request() { return $this->shortcode_render( 'render_request' ); }
    public function shortcode_request_success() { return $this->shortcode_render( 'render_request_success' ); }
}
