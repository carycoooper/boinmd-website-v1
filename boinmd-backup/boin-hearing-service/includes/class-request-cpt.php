<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Request_CPT {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_hearing_request', array( $this, 'save_meta' ) );
    }

    public function register() {
        register_post_type( 'hearing_request', array(
            'labels' => array(
                'name'          => '用户需求管理',
                'singular_name' => '用户需求',
                'edit_item'     => '处理用户需求',
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'boin-hearing-service',
            'supports'     => array( 'title' ),
            'menu_icon'    => 'dashicons-format-status',
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box( 'bhs_request_meta', '需求信息', array( $this, 'render_meta_box' ), 'hearing_request', 'normal', 'high' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'bhs_save_request', 'bhs_request_nonce' );
        $phone       = get_post_meta( $post->ID, 'user_phone', true );
        $device      = get_post_meta( $post->ID, 'device_model', true );
        $description = get_post_meta( $post->ID, 'description', true );
        $test_id     = get_post_meta( $post->ID, 'test_id', true );
        $status      = get_post_meta( $post->ID, 'status', true ) ?: 'pending';
        $created_at  = get_post_meta( $post->ID, 'created_at', true );
        ?>
        <p><label>手机号<br><input type="text" name="user_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></label></p>
        <p><label>设备型号<br><input type="text" name="device_model" value="<?php echo esc_attr( $device ); ?>" class="regular-text"></label></p>
        <p><label>调试需求<br><textarea name="description" rows="5" class="widefat"><?php echo esc_textarea( $description ); ?></textarea></label></p>
        <p><label>测听 ID<br><input type="number" name="test_id" value="<?php echo esc_attr( $test_id ); ?>" class="small-text"></label></p>
        <p><label>状态<br>
            <select name="status">
                <option value="pending" <?php selected( $status, 'pending' ); ?>>待处理</option>
                <option value="processing" <?php selected( $status, 'processing' ); ?>>处理中</option>
                <option value="done" <?php selected( $status, 'done' ); ?>>已完成</option>
            </select>
        </label></p>
        <p>提交时间：<?php echo esc_html( $created_at ); ?></p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['bhs_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bhs_request_nonce'] ) ), 'bhs_save_request' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, 'user_phone', bhs_sanitize_phone( wp_unslash( $_POST['user_phone'] ?? '' ) ) );
        update_post_meta( $post_id, 'device_model', sanitize_text_field( wp_unslash( $_POST['device_model'] ?? '' ) ) );
        update_post_meta( $post_id, 'description', sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ) );
        update_post_meta( $post_id, 'test_id', intval( $_POST['test_id'] ?? 0 ) );
        update_post_meta( $post_id, 'status', sanitize_key( $_POST['status'] ?? 'pending' ) );
    }
}
