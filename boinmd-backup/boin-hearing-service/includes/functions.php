<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bhs_default_settings() {
    return array(
        'wecom_enabled'      => '0',
        'wecom_webhook'      => '',
        'wecom_mask_phone'   => '1',
        'log_retention_days' => '30',
    );
}

function bhs_get_settings() {
    $settings = get_option( BHS_OPT_SETTINGS, array() );
    return wp_parse_args( is_array( $settings ) ? $settings : array(), bhs_default_settings() );
}

function bhs_mask_phone( $phone ) {
    $phone = preg_replace( '/\D+/', '', (string) $phone );
    if ( strlen( $phone ) < 7 ) return $phone;
    return substr( $phone, 0, 3 ) . '****' . substr( $phone, -4 );
}

function bhs_seed_default_device() {
    $existing = get_posts( array(
        'post_type'      => 'hearing_device',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'meta_key'       => 'model_code',
        'meta_value'     => 'q10-p',
        'fields'         => 'ids',
    ) );

    if ( ! empty( $existing ) ) return;

    $post_id = wp_insert_post( array(
        'post_type'   => 'hearing_device',
        'post_title'  => '悦听礼赠款助听器',
        'post_status' => 'publish',
    ) );

    if ( $post_id && ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, 'model_code', 'q10-p' );
        update_post_meta( $post_id, 'product_series', '悦听礼赠款' );
        update_post_meta( $post_id, 'device_note', '默认远程服务设备' );
        update_post_meta( $post_id, 'is_active', '1' );
        update_post_meta( $post_id, 'sort', '10' );
    }
}

function bhs_get_active_devices() {
    return get_posts( array(
        'post_type'      => 'hearing_device',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'meta_query'     => array(
            array(
                'key'   => 'is_active',
                'value' => '1',
            ),
        ),
        'meta_key'       => 'sort',
        'orderby'        => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
    ) );
}

function bhs_get_device_payload( $post ) {
    return array(
        'id'             => (int) $post->ID,
        'name'           => get_the_title( $post ),
        'model_code'     => (string) get_post_meta( $post->ID, 'model_code', true ),
        'product_series' => (string) get_post_meta( $post->ID, 'product_series', true ),
        'is_active'      => (string) get_post_meta( $post->ID, 'is_active', true ) === '1',
        'sort'           => (int) get_post_meta( $post->ID, 'sort', true ),
    );
}

function bhs_sanitize_phone( $phone ) {
    return preg_replace( '/[^\d+\-\s]/', '', (string) $phone );
}

function bhs_current_time() {
    return current_time( 'mysql' );
}

function bhs_public_url( $path = '/' ) {
    $base = home_url( '/' );
    $base = preg_replace( '#/blog/?$#', '/', $base );
    return trailingslashit( $base ) . ltrim( $path, '/' );
}

function bhs_service_url( $path = '/', $args = array() ) {
    $url = bhs_public_url( 'hearing-service/' . ltrim( $path, '/' ) );
    return ! empty( $args ) ? add_query_arg( $args, $url ) : $url;
}


function bhs_get_audio_map() {
    $freqs = array( 250, 500, 1000, 2000, 4000, 8000 );
    $levels = array( 1, 2, 3, 4, 5, 6 );
    $map = array(
        'reference' => array(
            'left' => array(
                '3' => BHS_URL . 'assets/audio/reference-left-level-3.wav',
            ),
        ),
        'left'  => array(),
        'right' => array(),
    );

    foreach ( array( 'left', 'right' ) as $ear ) {
        foreach ( $freqs as $freq ) {
            $map[ $ear ][ (string) $freq ] = array();
            foreach ( $levels as $level ) {
                $map[ $ear ][ (string) $freq ][ (string) $level ] = BHS_URL . 'assets/audio/' . $ear . '-' . $freq . '-level-' . $level . '.wav';
            }
        }
    }

    return $map;
}


function bhs_generate_hearing_test_summary( $rows ) {
    if ( is_string( $rows ) ) {
        $decoded = json_decode( $rows, true );
        $rows = is_array( $decoded ) ? $decoded : array();
    }
    if ( ! is_array( $rows ) || empty( $rows ) ) {
        return '六频在线听力筛查已完成，暂无可分析的频率数据。结果仅作远程服务沟通参考，不替代专业听力检查。';
    }

    $freqs       = array( 250, 500, 1000, 2000, 4000, 8000 );
    $ear_labels  = array( 'left' => '左耳', 'right' => '右耳' );
    $group_names = array( 'low' => '低频 250/500Hz', 'speech' => '语音频段 1000/2000Hz', 'high' => '高频 4000/8000Hz' );
    $groups      = array(
        'low'    => array( 250, 500 ),
        'speech' => array( 1000, 2000 ),
        'high'   => array( 4000, 8000 ),
    );

    $by_ear = array( 'left' => array(), 'right' => array() );
    foreach ( $rows as $row ) {
        $ear  = isset( $row['ear'] ) ? sanitize_key( $row['ear'] ) : '';
        $freq = isset( $row['frequency'] ) ? (int) $row['frequency'] : 0;
        if ( ! isset( $by_ear[ $ear ] ) || ! in_array( $freq, $freqs, true ) ) {
            continue;
        }
        $level = isset( $row['relative_level'] ) && $row['relative_level'] !== '' && $row['relative_level'] !== null ? (int) $row['relative_level'] : null;
        $by_ear[ $ear ][ $freq ] = array(
            'level'  => $level,
            'status' => isset( $row['result_status'] ) ? sanitize_key( $row['result_status'] ) : '',
        );
    }

    $lines = array();
    $lines[] = '六频在线听力筛查已完成，以下内容仅作远程验配服务沟通参考，不替代专业听力检查或诊断。';
    $lines[] = '读数说明：相对音量等级越高，通常表示该频率需要更高的播放强度才有反应；若显示未响应，建议验配师重点复核该频率及对应生活场景。';

    $ear_avgs = array();
    foreach ( $by_ear as $ear => $items ) {
        $parts = array();
        foreach ( $freqs as $freq ) {
            if ( ! isset( $items[ $freq ] ) ) {
                $parts[] = $freq . 'Hz：未记录';
                continue;
            }
            $level = $items[ $freq ]['level'];
            $parts[] = $freq . 'Hz：' . ( $level === null ? '未响应' : 'Level ' . $level );
        }
        $lines[] = $ear_labels[ $ear ] . '结果：' . implode( '；', $parts ) . '。';

        $group_parts = array();
        foreach ( $groups as $group_key => $group_freqs ) {
            $vals = array();
            foreach ( $group_freqs as $freq ) {
                if ( isset( $items[ $freq ] ) && $items[ $freq ]['level'] !== null ) {
                    $vals[] = (int) $items[ $freq ]['level'];
                }
            }
            if ( $vals ) {
                $avg = array_sum( $vals ) / count( $vals );
                $ear_avgs[ $ear ][ $group_key ] = $avg;
                $group_parts[] = $group_names[ $group_key ] . '平均约 Level ' . round( $avg, 1 );
            }
        }
        if ( $group_parts ) {
            $lines[] = $ear_labels[ $ear ] . '频段观察：' . implode( '；', $group_parts ) . '。';
        }
    }

    foreach ( array( 'left', 'right' ) as $ear ) {
        if ( empty( $ear_avgs[ $ear ] ) ) continue;
        $high  = $ear_avgs[ $ear ]['high'] ?? null;
        $mid   = $ear_avgs[ $ear ]['speech'] ?? null;
        $low   = $ear_avgs[ $ear ]['low'] ?? null;
        $label = $ear_labels[ $ear ];
        if ( $high !== null && $mid !== null && $high - $mid >= 1 ) {
            $lines[] = $label . '高频相对等级高于语音频段，调试时可重点关注高频清晰度、齿音/尖锐感和嘈杂环境中人声边缘，不建议一次性大幅提高，应结合佩戴反馈逐步微调。';
        }
        if ( $low !== null && $mid !== null && $low - $mid >= 1 ) {
            $lines[] = $label . '低频相对等级偏高，建议关注低频增益、堵耳感、闷胀感和环境低频噪声，必要时结合通气/佩戴舒适度一起判断。';
        }
    }

    foreach ( $groups as $group_key => $_ ) {
        if ( isset( $ear_avgs['left'][ $group_key ], $ear_avgs['right'][ $group_key ] ) ) {
            $diff = abs( $ear_avgs['left'][ $group_key ] - $ear_avgs['right'][ $group_key ] );
            if ( $diff >= 1 ) {
                $side = $ear_avgs['left'][ $group_key ] > $ear_avgs['right'][ $group_key ] ? '左耳' : '右耳';
                $lines[] = $group_names[ $group_key ] . '左右差异较明显，' . $side . '需要更高相对等级。验配师可分别询问两侧听感，并按左右耳独立微调。';
            }
        }
    }

    $lines[] = '建议验配师回访时重点询问：家人说话是否清楚、电视音量是否偏大、嘈杂环境是否费力、声音是否刺耳或发闷、是否有啸叫、佩戴是否舒适。';
    $lines[] = '调试方向建议：先结合用户主诉确定优先场景，再按左右耳和频段逐步调整；每次调整后让用户在真实沟通、电视和户外场景中复核，具体适配效果因人而异。';

    return implode( "\n", $lines );
}

function bhs_enqueue_frontend_assets() {
    wp_enqueue_style( 'boin-hearing-service', BHS_URL . 'assets/css/boin-hearing-service.css', array(), BHS_VERSION );
    wp_enqueue_script( 'boin-hearing-service', BHS_URL . 'assets/js/boin-hearing-service.js', array(), BHS_VERSION, true );
    wp_localize_script( 'boin-hearing-service', 'BHS_DATA', array(
        'restUrl'   => esc_url_raw( rest_url( 'boin-hearing/v1/' ) ),
        'legacyUrl' => esc_url_raw( rest_url( 'boin/v1/' ) ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'homeUrl'   => esc_url_raw( bhs_service_url() ),
        'audioMap'  => bhs_get_audio_map(),
    ) );
}

function bhs_client_info() {
    $ua = sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
    $browser = 'unknown';
    if ( stripos( $ua, 'Edg/' ) !== false ) $browser = 'Edge';
    elseif ( stripos( $ua, 'Chrome/' ) !== false ) $browser = 'Chrome';
    elseif ( stripos( $ua, 'Safari/' ) !== false ) $browser = 'Safari';
    elseif ( stripos( $ua, 'Firefox/' ) !== false ) $browser = 'Firefox';
    $os = 'unknown';
    if ( stripos( $ua, 'Windows' ) !== false ) $os = 'Windows';
    elseif ( stripos( $ua, 'iPhone' ) !== false || stripos( $ua, 'iPad' ) !== false ) $os = 'iOS';
    elseif ( stripos( $ua, 'Android' ) !== false ) $os = 'Android';
    elseif ( stripos( $ua, 'Mac OS' ) !== false ) $os = 'macOS';
    return array( 'user_agent' => $ua, 'browser' => $browser, 'operating_system' => $os );
}

function bhs_hash_ip() {
    $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
    return hash( 'sha256', wp_salt( 'nonce' ) . '|' . $ip );
}
