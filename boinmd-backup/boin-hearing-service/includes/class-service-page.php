<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Service_Page {
    private static $instance = null;
    private $freqs = array( '250', '500', '1000', '2000', '4000', '8000' );

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
    }

    public function register_rewrite() {
        add_rewrite_rule( '^hearing-service/?$', 'index.php?bhs_service_page=1', 'top' );
    }

    public function query_vars( $vars ) {
        $vars[] = 'bhs_service_page';
        $vars[] = 'bhs_step';
        return $vars;
    }

    public function is_service_page() {
        return (int) get_query_var( 'bhs_service_page' ) === 1;
    }

    private function step() {
        $step = sanitize_key( get_query_var( 'bhs_step' ) ?: ( $_GET['bhs_step'] ?? 'home' ) );
        $allowed = array( 'home', 'test-start', 'test-run', 'test-result', 'request', 'success' );
        return in_array( $step, $allowed, true ) ? $step : 'home';
    }

    private function url( $step = 'home', $args = array() ) {
        $args = array_merge( array( 'bhs_step' => $step ), $args );
        return add_query_arg( $args, bhs_public_url( '/hearing-service/' ) );
    }

    public function title_parts( $title ) {
        if ( $this->is_service_page() ) {
            $title['title'] = '测听服务';
        }
        return $title;
    }

    public function document_title( $title ) {
        if ( $this->is_service_page() ) {
            return '测听服务 - 博音悦听礼赠';
        }
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
        echo '<main class="bhs-service-page" aria-label="测听服务">';
        $this->render_header();
        $method = 'render_' . str_replace( '-', '_', $this->step() );
        if ( method_exists( $this, $method ) ) {
            $this->$method();
        } else {
            $this->render_home();
        }
        echo '</main>';
        get_footer();
        exit;
    }

    private function render_header() {
        echo '<section class="bhs-service-hero"><div class="bhs-service-wrap">';
        echo '<p class="bhs-eyebrow">BOINMD Hearing Service</p>';
        echo '<h1>测听服务</h1>';
        echo '<p>按步骤完成听力测试，或单独提交悦听礼赠款助听器调试需求。每一步都会更清楚，也更适合手机端使用。</p>';
        echo '</div></section>';
    }

    private function render_home() {
        echo '<section class="bhs-card bhs-flow-card">';
        echo '<h2>你想先做什么？</h2>';
        echo '<p>建议先做 6 频听力测试，再根据结果提交调试需求。如果已经明确问题，也可以直接提交需求。</p>';
        echo '<div class="bhs-actions">';
        echo '<a class="bhs-btn bhs-btn-primary" href="' . esc_url( $this->url( 'test-start' ) ) . '">开始听力测试</a>';
        echo '<a class="bhs-btn bhs-btn-ghost" href="' . esc_url( $this->url( 'request' ) ) . '">提交调试需求</a>';
        echo '</div>';
        echo '</section>';
    }

    private function render_test_start() {
        echo '<section class="bhs-card bhs-flow-card">';
        echo '<h2>开始前请确认</h2>';
        echo '<ul class="bhs-check-list"><li>请在安静环境中测试</li><li>建议佩戴耳机或使用稳定音量</li><li>测试结果仅作服务沟通参考，不替代专业诊断</li></ul>';
        echo '<form class="bhs-form bhs-start-form" method="get" action="' . esc_url( bhs_public_url( '/hearing-service/' ) ) . '">';
        echo '<input type="hidden" name="bhs_step" value="test-run">';
        echo '<input type="hidden" name="freq" value="250">';
        echo '<label>手机号<input type="tel" name="user_phone" required placeholder="请输入手机号，便于验配师联系"></label>';
        echo '<div class="bhs-actions">';
        echo '<button class="bhs-btn bhs-btn-primary" type="submit">开始测试</button>';
        echo '<a class="bhs-btn bhs-btn-ghost" href="' . esc_url( $this->url( 'home' ) ) . '">返回</a>';
        echo '</div>';
        echo '</form>';
        echo '</section>';
    }

    private function render_test_run() {
        $freq = sanitize_text_field( wp_unslash( $_GET['freq'] ?? '250' ) );
        if ( ! in_array( $freq, $this->freqs, true ) ) $freq = '250';
        $index = array_search( $freq, $this->freqs, true );
        $next = $this->freqs[ $index + 1 ] ?? '';
        $phone = bhs_sanitize_phone( wp_unslash( $_GET['user_phone'] ?? '' ) );

        $base_args = array( 'user_phone' => $phone );
        foreach ( $this->freqs as $past_freq ) {
            $key = 'freq_' . $past_freq;
            if ( isset( $_GET[ $key ] ) ) {
                $base_args[ $key ] = sanitize_key( wp_unslash( $_GET[ $key ] ) );
            }
        }

        $heard_args = array_merge( $base_args, array( 'freq_' . $freq => 'heard' ) );
        $not_args = array_merge( $base_args, array( 'freq_' . $freq => 'not_heard' ) );
        $heard_url = $next ? $this->url( 'test-run', array_merge( $heard_args, array( 'freq' => $next ) ) ) : $this->url( 'test-result', $heard_args );
        $not_url = $next ? $this->url( 'test-run', array_merge( $not_args, array( 'freq' => $next ) ) ) : $this->url( 'test-result', $not_args );

        echo '<section class="bhs-card bhs-flow-card bhs-test-step">';
        echo '<p class="bhs-step-count">第 ' . esc_html( $index + 1 ) . ' / 6 步</p>';
        echo '<h2>' . esc_html( $freq ) . ' Hz 是否能听到？</h2>';
        echo '<p>请播放当前频率声音。如果能听到，点击“听到了”；如果听不到，点击“听不到”。系统会自动进入下一频率。</p>';
        echo '<div class="bhs-tone-box"><span>' . esc_html( $freq ) . ' Hz</span><small>频率提示</small></div>';
        echo '<div class="bhs-actions">';
        echo '<a class="bhs-btn bhs-btn-primary" href="' . esc_url( $heard_url ) . '">听到了，下一步</a>';
        echo '<a class="bhs-btn bhs-btn-ghost" href="' . esc_url( $not_url ) . '">听不到，下一步</a>';
        echo '</div>';
        echo '</section>';
    }

    private function render_test_result() {
        $phone = bhs_sanitize_phone( wp_unslash( $_GET['user_phone'] ?? '' ) );
        $missing = array();
        foreach ( $this->freqs as $freq ) {
            $value = sanitize_key( wp_unslash( $_GET[ 'freq_' . $freq ] ?? '' ) );
            if ( ! in_array( $value, array( 'heard', 'not_heard' ), true ) ) {
                $missing[] = $freq;
            }
        }

        echo '<section class="bhs-card bhs-flow-card">';
        echo '<h2>测试完成</h2>';
        echo '<p>你已经完成听力测试，验配师会结合测试记录查看，尽快与您取得联系。</p>';
        echo '<div class="bhs-result-grid"><div><strong>测试频率</strong><span>250 / 500 / 1000 / 2000 / 4000 / 8000 Hz</span></div><div><strong>建议</strong><span>如有听不清、耳鸣或佩戴不适，建议提交调试需求。</span></div></div>';
        if ( $phone !== '' && empty( $missing ) ) {
            echo '<form class="bhs-form bhs-auto-test-form" data-bhs-form="test" data-bhs-auto-submit="1">';
            echo '<input type="hidden" name="user_phone" value="' . esc_attr( $phone ) . '">';
            echo '<input type="hidden" name="summary" value="6 频听力测试已完成，结果仅作服务沟通参考。">';
            foreach ( $this->freqs as $freq ) {
                echo '<input type="hidden" name="freq_' . esc_attr( $freq ) . '" value="' . esc_attr( sanitize_key( wp_unslash( $_GET[ 'freq_' . $freq ] ?? '' ) ) ) . '">';
            }
            echo '<p class="bhs-form-msg" aria-live="polite">正在保存测试记录...</p>';
            echo '</form>';
        } else {
            echo '<p class="bhs-form-msg is-error">测试信息不完整，请返回重新开始测试。</p>';
        }
        echo '<div class="bhs-actions">';
        echo '<a class="bhs-btn bhs-btn-primary" href="' . esc_url( $this->url( 'request', array( 'from' => 'test' ) ) ) . '">提交调试需求</a>';
        echo '<a class="bhs-btn bhs-btn-ghost" href="' . esc_url( $this->url( 'home' ) ) . '">返回首页</a>';
        echo '</div>';
        echo '</section>';
    }

    private function render_request() {
        echo do_shortcode( '[boin_request]' );
    }

    private function render_success() {
        echo '<section class="bhs-card bhs-flow-card">';
        echo '<h2>提交成功</h2>';
        echo '<p>我们已经收到你的信息。后台验配师会查看需求，并通过企业微信通知及时处理。</p>';
        echo '<div class="bhs-actions"><a class="bhs-btn bhs-btn-primary" href="' . esc_url( $this->url( 'home' ) ) . '">返回测听服务首页</a></div>';
        echo '</section>';
    }
}
