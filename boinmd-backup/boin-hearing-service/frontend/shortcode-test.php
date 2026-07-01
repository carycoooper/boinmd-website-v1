<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Shortcode_Test {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'boin_test', array( $this, 'render' ) );
    }

    public function render() {
        bhs_enqueue_frontend_assets();
        $freqs = array( '250', '500', '1000', '2000', '4000', '8000' );
        ob_start();
        ?>
        <section class="bhs-card" id="boin-test">
            <h2>6 频听力测试记录</h2>
            <p>请填写测试结果。该记录仅作服务沟通参考，不替代专业诊断。</p>
            <form class="bhs-form" data-bhs-form="test">
                <label>手机号<input type="tel" name="user_phone" required placeholder="请输入手机号"></label>
                <div class="bhs-grid">
                    <?php foreach ( $freqs as $freq ) : ?>
                        <label><?php echo esc_html( $freq ); ?> Hz<input type="number" name="freq_<?php echo esc_attr( $freq ); ?>" min="0" max="120" step="1" placeholder="dB"></label>
                    <?php endforeach; ?>
                </div>
                <label>简要备注<textarea name="summary" rows="3" placeholder="例如：嘈杂环境听不清，右耳更明显"></textarea></label>
                <button class="bhs-btn bhs-btn-primary" type="submit">提交测试结果</button>
                <p class="bhs-form-msg" aria-live="polite"></p>
            </form>
        </section>
        <?php
        return ob_get_clean();
    }
}
