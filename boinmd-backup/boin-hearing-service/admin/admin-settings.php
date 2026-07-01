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
        add_menu_page(
            '悦听礼赠款系统',
            '悦听礼赠款系统',
            'manage_options',
            'boin-hearing-service',
            array( $this, 'render_dashboard' ),
            'dashicons-controls-volumeon',
            26
        );

        add_submenu_page(
            'boin-hearing-service',
            '企业微信配置',
            '企业微信配置',
            'manage_options',
            'boin-hearing-service-settings',
            array( $this, 'render_settings' )
        );
    }

    public function register_settings() {
        register_setting( 'bhs_settings_group', BHS_OPT_SETTINGS, array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
            'default'           => bhs_default_settings(),
        ) );
    }

    public function sanitize_settings( $input ) {
        $input = is_array( $input ) ? $input : array();
        return array(
            'wecom_enabled' => ! empty( $input['wecom_enabled'] ) ? '1' : '0',
            'wecom_webhook' => esc_url_raw( $input['wecom_webhook'] ?? '' ),
        );
    }

    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>悦听礼赠款系统</h1>
            <p>这里是远程服务系统入口。你可以管理用户需求、听力测试记录、设备型号，并配置企业微信通知。</p>
            <ul class="ul-disc">
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_request' ) ); ?>">用户需求管理</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_test' ) ); ?>">听力测试记录</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hearing_device' ) ); ?>">设备型号管理</a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=boin-hearing-service-settings' ) ); ?>">企业微信配置</a></li>
            </ul>
            <h2>前端短代码</h2>
            <p><code>[boin_home]</code> 首页入口</p>
            <p><code>[boin_test]</code> 听力测试</p>
            <p><code>[boin_request]</code> 需求提交</p>
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
                    <tr>
                        <th scope="row">开启通知</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[wecom_enabled]" value="1" <?php checked( $settings['wecom_enabled'], '1' ); ?>>
                                用户提交需求或听力测试后，自动推送企业微信
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="url" name="<?php echo esc_attr( BHS_OPT_SETTINGS ); ?>[wecom_webhook]" value="<?php echo esc_attr( $settings['wecom_webhook'] ); ?>" class="large-text" placeholder="https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=...">
                            <p class="description">请在企业微信群机器人中复制 Webhook 地址。</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
