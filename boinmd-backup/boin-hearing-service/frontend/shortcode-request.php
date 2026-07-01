<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Shortcode_Request {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'boin_request', array( $this, 'render' ) );
    }

    public function render() {
        bhs_enqueue_frontend_assets();
        $devices = bhs_get_active_devices();
        ob_start();
        ?>
        <section class="bhs-card" id="boin-request">
            <h2>提交远程调试需求</h2>
            <p>请选择设备型号，并描述目前遇到的问题，后台验配师会查看处理。</p>
            <form class="bhs-form" data-bhs-form="request">
                <label>手机号<input type="tel" name="user_phone" required placeholder="请输入手机号"></label>
                <label>设备型号
                    <select name="device_model" required>
                        <?php foreach ( $devices as $device ) : ?>
                            <option value="<?php echo esc_attr( get_the_title( $device ) ); ?>"><?php echo esc_html( get_the_title( $device ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>调试需求<textarea name="description" rows="5" required placeholder="例如：人声听不清，电视声偏小，嘈杂环境交流费劲"></textarea></label>
                <label>关联测听 ID（可选）<input type="number" name="test_id" placeholder="如有测试记录可填写"></label>
                <button class="bhs-btn bhs-btn-primary" type="submit">提交需求</button>
                <p class="bhs-form-msg" aria-live="polite"></p>
            </form>
        </section>
        <?php
        return ob_get_clean();
    }
}
