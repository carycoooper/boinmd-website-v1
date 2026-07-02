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

    public function title_parts( $title ) {
        if ( $this->is_service_page() ) $title['title'] = '测听服务';
        return $title;
    }

    public function document_title( $title ) {
        if ( $this->is_service_page() ) return '测听服务 - 博音悦听礼赠';
        return $title;
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
        bhs_enqueue_frontend_assets();
        status_header( 200 );
        get_header();
        echo '<main class="bhs-service-page" aria-label="悦听礼赠款助听器远程服务">';
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
        echo '<label>手机号<input class="bhs-input" type="tel" data-bhs-phone placeholder="请输入手机号，便于保存本次筛查记录"></label>';
        echo '<div class="bhs-actions"><button class="bhs-btn bhs-btn-primary" data-bhs-play-reference type="button">播放参考声音</button></div>';
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

    public function render_test_result() {
        $this->back_home();
        $session = $this->current_session();
        if ( ! $session ) { $this->expired(); return; }
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . BHS_DB::results_table() . ' WHERE session_id = %d ORDER BY ear ASC, frequency ASC', $session->id ) );
        echo '<section class="bhs-card bhs-flow-card"><h2>测试结果</h2><p>该结果仅用于远程服务沟通和助听器调试参考，不替代专业纯音测听及医疗诊断。</p>';
        echo '<div class="bhs-result-grid"><div><strong>测试记录编号</strong><span>' . esc_html( $session->session_uuid ) . '</span></div><div><strong>完成时间</strong><span>' . esc_html( $session->completed_at ?: bhs_current_time() ) . '</span></div></div>';
        echo '<div class="bhs-table-wrap"><table class="bhs-result-table"><thead><tr><th>耳侧</th><th>频率</th><th>相对听见等级</th><th>状态</th></tr></thead><tbody>';
        foreach ( $rows as $row ) echo '<tr><td>' . esc_html( $row->ear === 'right' ? '右耳' : '左耳' ) . '</td><td>' . esc_html( $row->frequency ) . ' Hz</td><td>' . esc_html( $row->relative_level ?: '最高等级未响应' ) . '</td><td>' . esc_html( $row->result_status ) . '</td></tr>';
        echo '</tbody></table></div>';
        echo '<p class="bhs-note">本次测试中，部分频率可能需要更高的相对播放等级。本次结果可供远程服务沟通参考。</p>';
        echo '<div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( bhs_service_url() ) . '">返回远程服务首页</a><a class="bhs-btn bhs-btn-ghost" href="' . esc_url( bhs_service_url( 'test-intro/' ) ) . '">重新测试</a></div>';
        echo '<p><a href="' . esc_url( bhs_service_url( 'request/', array( 'session' => $session->session_uuid, 'token' => sanitize_text_field( $_GET['token'] ?? '' ) ) ) ) . '">另有助听器使用问题？提交调试需求</a></p>';
        echo '</section>';
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
        echo '<label>手机号<input type="tel" name="phone" required placeholder="请输入手机号"></label><label>设备型号<select name="device_id" required>';
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
