<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Admin_Settings {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_menu() {
        add_menu_page( '悦听远程服务', '悦听远程服务', 'manage_options', 'boin-hearing-service', array( $this, 'render_dashboard' ), 'dashicons-controls-volumeon', 26 );
        add_submenu_page( 'boin-hearing-service', '企业微信配置', '企业微信配置', 'manage_options', 'boin-hearing-service-settings', array( $this, 'render_settings' ) );
    }

    public function register_settings() {
        register_setting( 'bhs_settings_group', BHS_OPT_SETTINGS, array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_settings' ), 'default' => bhs_default_settings() ) );
    }

    public function sanitize_settings( $input ) {
        $input = is_array( $input ) ? $input : array();
        return array(
            'wecom_enabled'      => ! empty( $input['wecom_enabled'] ) ? '1' : '0',
            'wecom_webhook'      => esc_url_raw( $input['wecom_webhook'] ?? '' ),
            'wecom_mask_phone'   => ! empty( $input['wecom_mask_phone'] ) ? '1' : '0',
            'log_retention_days' => max( 7, intval( $input['log_retention_days'] ?? 30 ) ),
        );
    }

    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>悦听远程服务</h1>
            <p>远程服务系统包含六频在线听力筛查与独立调试需求提交。调试需求不属于测试流程，用户可直接提交。</p>
            <ul class="ul-disc">
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_request' ) ); ?>">用户需求管理</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_test' ) ); ?>">听力测试记录</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_device' ) ); ?>">设备型号管理</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=boin-hearing-service-settings' ) ); ?>">企业微信配置</a></li>
            </ul>
            <h2>自动创建页面</h2>
            <p><code>/hearing-service/</code> 远程服务首页</p>
            <p><code>/hearing-service/test-intro/</code> 测试准备</p>
            <p><code>/hearing-service/test-calibration/</code> 设备音量确认</p>
            <p><code>/hearing-service/test/</code> 听力测试</p>
            <p><code>/hearing-service/test-result/</code> 测试结果</p>
            <p><code>/hearing-service/test-stopped/</code> 测试已停止</p>
            <p><code>/hearing-service/request/</code> 提交调试需求</p>
            <p><code>/hearing-service/request-success/</code> 需求提交成功</p>
        </div>
        <?php
    }

    public function render_settings() {
        $settings = bhs_get_settings();
        ?>
        <div class="wrap">
            <h1>企业微信通知配置</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'bhs_settings_group' ); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row">开启通知</th><td><label><input type="checkbox" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[wecom_enabled]" value="1" <?php checked( $settings['wecom_enabled'], '1' ); ?>> 用户提交需求后自动推送企业微信</label></td></tr>
                    <tr><th scope="row">Webhook URL</th><td><input type="url" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[wecom_webhook]" value="<?php echo esc_attr( $settings['wecom_webhook'] ); ?>" class="large-text" placeholder="https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=..."><p class="description">Webhook 仅保存在后台，不会输出到前端。</p></td></tr>
                    <tr><th scope="row">通知中脱敏手机号</th><td><label><input type="checkbox" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[wecom_mask_phone]" value="1" <?php checked( $settings['wecom_mask_phone'], '1' ); ?>> 开启</label></td></tr>
                    <tr><th scope="row">日志保留天数</th><td><input type="number" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[log_retention_days]" value="<?php echo esc_attr( $settings['log_retention_days'] ); ?>" min="7" class="small-text"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
