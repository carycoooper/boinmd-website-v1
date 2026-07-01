<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Shortcode_Home {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'boin_home', array( $this, 'render' ) );
    }

    public function render() {
        bhs_enqueue_frontend_assets();
        ob_start();
        ?>
        <section class="bhs-card bhs-home">
            <p class="bhs-eyebrow">BOINMD Hearing Service</p>
            <h2>悦听礼赠款远程服务</h2>
            <p>完成听力测试，或提交助听器调试需求，后台验配师会及时查看并处理。</p>
            <div class="bhs-actions">
                <a class="bhs-btn bhs-btn-primary" href="#boin-test">开始听力测试</a>
                <a class="bhs-btn bhs-btn-ghost" href="#boin-request">提交调试需求</a>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
