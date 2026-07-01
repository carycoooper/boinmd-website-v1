<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bhs_default_settings() {
    return array(
        'wecom_enabled' => '0',
        'wecom_webhook' => '',
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
        'id'         => (int) $post->ID,
        'name'       => get_the_title( $post ),
        'model_code' => (string) get_post_meta( $post->ID, 'model_code', true ),
        'is_active'  => (string) get_post_meta( $post->ID, 'is_active', true ) === '1',
        'sort'       => (int) get_post_meta( $post->ID, 'sort', true ),
    );
}

function bhs_sanitize_phone( $phone ) {
    return preg_replace( '/[^\d+\-\s]/', '', (string) $phone );
}

function bhs_current_time() {
    return current_time( 'mysql' );
}

function bhs_enqueue_frontend_assets() {
    wp_enqueue_style( 'boin-hearing-service', BHS_URL . 'assets/css/boin-hearing-service.css', array(), BHS_VERSION );
    wp_enqueue_script( 'boin-hearing-service', BHS_URL . 'assets/js/boin-hearing-service.js', array(), BHS_VERSION, true );
    wp_localize_script( 'boin-hearing-service', 'BHS_DATA', array(
        'restUrl' => esc_url_raw( rest_url( 'boin/v1/' ) ),
        'nonce'   => wp_create_nonce( 'wp_rest' ),
    ) );
}
