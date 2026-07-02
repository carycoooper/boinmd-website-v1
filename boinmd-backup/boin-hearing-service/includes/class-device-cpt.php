<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_Device_CPT {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_hearing_device', array( $this, 'save_meta' ) );
    }

    public function register() {
        register_post_type( 'hearing_device', array(
            'labels' => array(
                'name'          => '设备型号',
                'singular_name' => '设备型号',
                'add_new_item'  => '新增设备型号',
                'edit_item'     => '编辑设备型号',
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'boin-hearing-service',
            'supports'     => array( 'title' ),
            'menu_icon'    => 'dashicons-headphones',
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box( 'bhs_device_meta', '设备信息', array( $this, 'render_meta_box' ), 'hearing_device', 'normal', 'high' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'bhs_save_device', 'bhs_device_nonce' );
        $model_code = get_post_meta( $post->ID, 'model_code', true );
        $series     = get_post_meta( $post->ID, 'product_series', true );
        $note       = get_post_meta( $post->ID, 'device_note', true );
        $is_active  = get_post_meta( $post->ID, 'is_active', true );
        $sort       = get_post_meta( $post->ID, 'sort', true );
        ?>
        <p><label>型号编码<br><input type="text" name="model_code" value="<?php echo esc_attr( $model_code ); ?>" class="widefat" placeholder="q10-p"></label></p>
        <p><label>产品系列<br><input type="text" name="product_series" value="<?php echo esc_attr( $series ); ?>" class="widefat" placeholder="悦听礼赠款"></label></p>
        <p><label>设备备注<br><textarea name="device_note" rows="3" class="widefat"><?php echo esc_textarea( $note ); ?></textarea></label></p>
        <p><label><input type="checkbox" name="is_active" value="1" <?php checked( $is_active, '1' ); ?>> 前端启用</label></p>
        <p><label>排序<br><input type="number" name="sort" value="<?php echo esc_attr( $sort !== '' ? $sort : 10 ); ?>" class="small-text"></label></p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['bhs_device_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bhs_device_nonce'] ) ), 'bhs_save_device' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, 'model_code', sanitize_text_field( wp_unslash( $_POST['model_code'] ?? '' ) ) );
        update_post_meta( $post_id, 'product_series', sanitize_text_field( wp_unslash( $_POST['product_series'] ?? '' ) ) );
        update_post_meta( $post_id, 'device_note', sanitize_textarea_field( wp_unslash( $_POST['device_note'] ?? '' ) ) );
        update_post_meta( $post_id, 'is_active', isset( $_POST['is_active'] ) ? '1' : '0' );
        update_post_meta( $post_id, 'sort', (string) intval( $_POST['sort'] ?? 10 ) );
    }
}
