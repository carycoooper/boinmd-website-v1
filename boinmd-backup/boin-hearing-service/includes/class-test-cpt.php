<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Test_CPT {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_hearing_test', array( $this, 'save_meta' ) );
    }

    public function register() {
        register_post_type( 'hearing_test', array(
            'labels' => array(
                'name'          => '听力测试记录',
                'singular_name' => '听力测试',
                'edit_item'     => '查看听力测试',
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'boin-hearing-service',
            'supports'     => array( 'title' ),
            'menu_icon'    => 'dashicons-chart-line',
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box( 'bhs_test_meta', '测试信息', array( $this, 'render_meta_box' ), 'hearing_test', 'normal', 'high' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'bhs_save_test', 'bhs_test_nonce' );
        $phone      = get_post_meta( $post->ID, 'user_phone', true );
        $freq_json  = get_post_meta( $post->ID, 'freq_result', true );
        $summary    = get_post_meta( $post->ID, 'summary', true );
        if ( ( ! $summary || trim( $summary ) === '六频在线听力筛查已完成，仅作远程服务沟通参考。' ) && function_exists( 'bhs_generate_hearing_test_summary' ) ) {
            $summary = bhs_generate_hearing_test_summary( $freq_json );
        }
        $created_at = get_post_meta( $post->ID, 'created_at', true );
        ?>
        <p><label>手机号<br><input type="text" name="user_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></label></p>
        <p><label>6频结果 JSON<br><textarea name="freq_result" rows="8" class="widefat code"><?php echo esc_textarea( $freq_json ); ?></textarea></label></p>
        <p><label>简要分析<br><textarea name="summary" rows="4" class="widefat"><?php echo esc_textarea( $summary ); ?></textarea></label></p>
        <p>提交时间：<?php echo esc_html( $created_at ); ?></p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['bhs_test_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bhs_test_nonce'] ) ), 'bhs_save_test' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, 'user_phone', bhs_sanitize_phone( wp_unslash( $_POST['user_phone'] ?? '' ) ) );
        update_post_meta( $post_id, 'freq_result', wp_kses_post( wp_unslash( $_POST['freq_result'] ?? '' ) ) );
        update_post_meta( $post_id, 'summary', sanitize_textarea_field( wp_unslash( $_POST['summary'] ?? '' ) ) );
    }
}
