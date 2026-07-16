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
        <?php $this->render_admin_audiogram( $freq_json ); ?>
        <p>提交时间：<?php echo esc_html( $created_at ); ?></p>
        <?php
    }


    private function get_chart_rows( $freq_json ) {
        $rows = is_string( $freq_json ) ? json_decode( $freq_json, true ) : $freq_json;
        if ( ! is_array( $rows ) ) return array();

        $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );
        $data  = array( 'right' => array(), 'left' => array() );

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) continue;
            $ear  = isset( $row['ear'] ) ? sanitize_key( $row['ear'] ) : '';
            $freq = isset( $row['frequency'] ) ? (int) $row['frequency'] : 0;
            if ( ! isset( $data[ $ear ] ) || ! in_array( $freq, $freqs, true ) ) continue;

            $level = null;
            if ( array_key_exists( 'relative_level', $row ) && $row['relative_level'] !== '' && $row['relative_level'] !== null ) {
                $level = max( 1, min( 6, (int) $row['relative_level'] ) );
            }
            $db = $level === null || ! function_exists( 'bhs_level_to_estimated_dbhl' ) ? null : bhs_level_to_estimated_dbhl( $level );
            $data[ $ear ][ $freq ] = array(
                'level'  => $level,
                'db'     => $db,
                'status' => isset( $row['result_status'] ) ? sanitize_key( $row['result_status'] ) : '',
            );
        }

        return $data;
    }

    private function render_admin_audiogram( $freq_json ) {
        $data  = $this->get_chart_rows( $freq_json );
        $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );
        $has_data = false;
        foreach ( $data as $items ) {
            if ( ! empty( $items ) ) { $has_data = true; break; }
        }

        echo '<div class="bhs-admin-audiogram" style="margin:18px 0;padding:18px;border:1px solid #d8d0ea;border-radius:12px;background:#fbf9ff;max-width:860px;">';
        echo '<h3 style="margin:0 0 6px;font-size:16px;">&#21548;&#21147;&#22270;&#65288;&#21518;&#21488;&#21487;&#35270;&#21270;&#65289;</h3>';
        echo '<p style="margin:0 0 14px;color:#646970;">&#32437;&#36724;&#20026;&#26681;&#25454;&#30456;&#23545;&#38899;&#37327;&#31561;&#32423;&#25442;&#31639;&#30340;&#20272;&#31639; dBHL&#65292;&#20165;&#20379;&#36828;&#31243;&#39564;&#37197;&#26381;&#21153;&#27807;&#36890;&#21442;&#32771;&#12290;</p>';

        if ( ! $has_data ) {
            echo '<p style="padding:14px;background:#fff;border:1px dashed #d8d0ea;border-radius:10px;">&#26242;&#26080;&#21487;&#29992;&#30340;&#27979;&#35797;&#39057;&#28857;&#25968;&#25454;&#12290;</p></div>';
            return;
        }

        $w = 760; $h = 360; $pad_l = 54; $pad_r = 24; $pad_t = 24; $pad_b = 42;
        $iw = $w - $pad_l - $pad_r; $ih = $h - $pad_t - $pad_b;
        $x = static function( $i ) use ( $pad_l, $iw, $freqs ) { return $pad_l + $iw * ( $i + 0.5 ) / count( $freqs ); };
        $y = static function( $db ) use ( $pad_t, $ih ) {
            $v = $db === null ? 95 : max( 0, min( 100, (int) $db ) );
            return $pad_t + $ih * ( $v / 110 );
        };

        echo '<svg viewBox="0 0 ' . esc_attr( $w ) . ' ' . esc_attr( $h ) . '" style="width:100%;height:auto;background:#fff;border-radius:12px;border:1px solid #ebe5f5;" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="&#21548;&#21147;&#22270;">';

        $zones = array(
            array( 0, 25, '#2fa869', '&#21442;&#32771;&#33539;&#22260;' ),
            array( 25, 40, '#e8a13c', '&#36731;&#24230;&#20559;&#24369;' ),
            array( 40, 60, '#e07b3c', '&#20013;&#24230;&#20559;&#24369;' ),
            array( 60, 110, '#e0523f', '&#26126;&#26174;&#20559;&#24369;' ),
        );
        foreach ( $zones as $zone ) {
            $zy = $y( $zone[0] );
            $zh = $y( $zone[1] ) - $zy;
            echo '<rect x="' . esc_attr( $pad_l ) . '" y="' . esc_attr( $zy ) . '" width="' . esc_attr( $iw ) . '" height="' . esc_attr( $zh ) . '" fill="' . esc_attr( $zone[2] ) . '" opacity="0.07" />';
            echo '<text x="' . esc_attr( $pad_l + $iw - 8 ) . '" y="' . esc_attr( $zy + 16 ) . '" font-size="12" fill="' . esc_attr( $zone[2] ) . '" text-anchor="end">' . esc_html( $zone[3] ) . '</text>';
        }

        for ( $db = 0; $db <= 100; $db += 20 ) {
            $gy = $y( $db );
            echo '<line x1="' . esc_attr( $pad_l ) . '" y1="' . esc_attr( $gy ) . '" x2="' . esc_attr( $w - $pad_r ) . '" y2="' . esc_attr( $gy ) . '" stroke="#e9e5f5" stroke-width="1" />';
            echo '<text x="' . esc_attr( $pad_l - 10 ) . '" y="' . esc_attr( $gy + 4 ) . '" font-size="12" fill="#756d86" text-anchor="end">' . esc_html( (string) $db ) . '</text>';
        }

        foreach ( $freqs as $i => $freq ) {
            $label = $freq >= 1000 ? ( $freq / 1000 ) . 'k' : (string) $freq;
            echo '<text x="' . esc_attr( $x( $i ) ) . '" y="' . esc_attr( $h - 16 ) . '" font-size="13" fill="#3f3852" text-anchor="middle" font-weight="600">' . esc_html( $label ) . '</text>';
        }

        $ears = array(
            'right' => array( 'color' => '#e0523f', 'label' => '&#21491;&#32819;' ),
            'left'  => array( 'color' => '#2f6fd6', 'label' => '&#24038;&#32819;' ),
        );
        foreach ( $ears as $ear => $meta ) {
            $points = array();
            foreach ( $freqs as $i => $freq ) {
                $db = isset( $data[ $ear ][ $freq ] ) ? $data[ $ear ][ $freq ]['db'] : null;
                $points[] = array( $x( $i ), $y( $db ), $db );
            }
            $path = '';
            foreach ( $points as $i => $point ) {
                $path .= ( $i ? ' L ' : 'M ' ) . round( $point[0], 2 ) . ' ' . round( $point[1], 2 );
            }
            echo '<path d="' . esc_attr( $path ) . '" fill="none" stroke="' . esc_attr( $meta['color'] ) . '" stroke-width="2.4" opacity="0.65" stroke-linejoin="round" />';
            foreach ( $points as $point ) {
                if ( $ear === 'right' ) {
                    echo '<circle cx="' . esc_attr( $point[0] ) . '" cy="' . esc_attr( $point[1] ) . '" r="6" fill="#fff" stroke="' . esc_attr( $meta['color'] ) . '" stroke-width="2.4" />';
                } else {
                    echo '<path d="M' . esc_attr( $point[0] - 5 ) . ' ' . esc_attr( $point[1] - 5 ) . ' L' . esc_attr( $point[0] + 5 ) . ' ' . esc_attr( $point[1] + 5 ) . ' M' . esc_attr( $point[0] + 5 ) . ' ' . esc_attr( $point[1] - 5 ) . ' L' . esc_attr( $point[0] - 5 ) . ' ' . esc_attr( $point[1] + 5 ) . '" stroke="' . esc_attr( $meta['color'] ) . '" stroke-width="2.4" stroke-linecap="round" />';
                }
            }
        }
        echo '</svg>';

        echo '<div style="display:flex;gap:18px;align-items:center;justify-content:center;margin-top:10px;color:#3f3852;">';
        echo '<span><span style="display:inline-block;width:11px;height:11px;border:2px solid #e0523f;border-radius:50%;vertical-align:-1px;margin-right:6px;"></span>&#21491;&#32819;</span>';
        echo '<span><span style="display:inline-block;color:#2f6fd6;font-weight:700;margin-right:6px;">&#10005;</span>&#24038;&#32819;</span>';
        echo '</div>';
        echo '</div>';
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
