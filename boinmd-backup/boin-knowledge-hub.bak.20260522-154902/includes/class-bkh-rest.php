<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * REST API:
 *   GET /wp-json/boin/v1/social-videos
 *   GET /wp-json/boin/v1/knowledge-articles
 *   GET /wp-json/boin/v1/knowledge-topics
 *   POST /wp-json/boin/v1/knowledge-articles/import
 *
 * Public endpoints are read-only; import requires an authenticated editor.
 */
class BKH_REST {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register' ) );
    }

    public function register() {
        register_rest_route( BKH_REST_NS, '/social-videos', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_social_videos' ),
            'permission_callback' => '__return_true',
            'args' => array(
                'platform' => array( 'type' => 'string', 'required' => false ),
                'per_page' => array( 'type' => 'integer', 'default' => 20 ),
                'page'     => array( 'type' => 'integer', 'default' => 1 ),
            ),
        ) );

        register_rest_route( BKH_REST_NS, '/knowledge-articles', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_articles' ),
            'permission_callback' => '__return_true',
            'args' => array(
                'category' => array( 'type' => 'string', 'required' => false ),
                'per_page' => array( 'type' => 'integer', 'default' => 20 ),
                'page'     => array( 'type' => 'integer', 'default' => 1 ),
                'orderby'  => array( 'type' => 'string', 'default' => 'featured' ),
            ),
        ) );

        register_rest_route( BKH_REST_NS, '/knowledge-topics', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_topics' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( BKH_REST_NS, '/knowledge-articles/import', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'import_article' ),
            'permission_callback' => array( $this, 'can_import_article' ),
        ) );
    }

    /* ============== handlers ============== */

    public function can_import_article() {
        return current_user_can( 'edit_posts' );
    }

    public function get_social_videos( $req ) {
        $args = array(
            'post_type'      => array( 'knowledge_article', 'knowledge_topic' ),
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, min( 100, (int) $req['per_page'] ) ),
            'paged'          => max( 1, (int) $req['page'] ),
            'meta_query'     => array(
                array( 'key' => '_bkh_videos', 'compare' => 'EXISTS' ),
            ),
        );
        $q = new WP_Query( $args );
        $out = array();
        foreach ( $q->posts as $p ) {
            $videos = bkh_get_videos( $p->ID );
            foreach ( $videos as $v ) {
                if ( ! empty( $req['platform'] ) && ( $v['platform'] ?? '' ) !== $req['platform'] ) continue;
                $out[] = array(
                    'post_id'      => $p->ID,
                    'post_title'   => get_the_title( $p ),
                    'post_url'     => get_permalink( $p ),
                    'platform'     => $v['platform']     ?? '',
                    'video_title'  => $v['video_title']  ?? '',
                    'video_url'    => $v['video_url']    ?? '',
                    'video_id'     => $v['video_id']     ?? '',
                    'cover_image'  => $v['cover_image']  ?? '',
                    'publish_date' => $v['publish_date'] ?? '',
                    'account_name' => $v['account_name'] ?? '',
                );
            }
        }
        return rest_ensure_response( array(
            'total' => count( $out ),
            'page'  => (int) $req['page'],
            'items' => $out,
        ) );
    }

    public function get_articles( $req ) {
        $args = array(
            'post_type'      => 'knowledge_article',
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, min( 100, (int) $req['per_page'] ) ),
            'paged'          => max( 1, (int) $req['page'] ),
        );
        if ( ! empty( $req['category'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'knowledge_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $req['category'] ),
                ),
            );
        }
        if ( $req['orderby'] === 'featured' ) {
            $args['meta_key'] = '_bkh_featured_priority';
            $args['orderby']  = array( 'meta_value_num' => 'DESC', 'date' => 'DESC' );
        }
        $q = new WP_Query( $args );
        $items = array();
        foreach ( $q->posts as $p ) {
            $terms = wp_get_object_terms( $p->ID, 'knowledge_category', array( 'fields' => 'slugs' ) );
            $items[] = array(
                'post_id'    => $p->ID,
                'post_title' => get_the_title( $p ),
                'post_url'   => get_permalink( $p ),
                'excerpt'    => wp_strip_all_tags( get_the_excerpt( $p ) ),
                'category'   => is_wp_error( $terms ) ? array() : $terms,
                'thumbnail'  => get_the_post_thumbnail_url( $p, 'medium' ),
                'date'       => get_the_date( 'c', $p ),
                'featured_priority' => (int) get_post_meta( $p->ID, '_bkh_featured_priority', true ),
            );
        }
        return rest_ensure_response( array(
            'total' => (int) $q->found_posts,
            'pages' => (int) $q->max_num_pages,
            'page'  => max( 1, (int) $req['page'] ),
            'items' => $items,
        ) );
    }

    public function get_topics( $req ) {
        $q = new WP_Query( array(
            'post_type'      => 'knowledge_topic',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
        ) );
        $items = array();
        foreach ( $q->posts as $p ) {
            $terms = wp_get_object_terms( $p->ID, 'knowledge_category', array( 'fields' => 'slugs' ) );
            $items[] = array(
                'post_id'    => $p->ID,
                'post_title' => get_the_title( $p ),
                'post_url'   => get_permalink( $p ),
                'excerpt'    => wp_strip_all_tags( get_the_excerpt( $p ) ),
                'category'   => is_wp_error( $terms ) ? array() : $terms,
                'thumbnail'  => get_the_post_thumbnail_url( $p, 'medium' ),
            );
        }
        return rest_ensure_response( array( 'total' => count( $items ), 'items' => $items ) );
    }

    public function import_article( $req ) {
        $data = $req->get_json_params();
        if ( ! is_array( $data ) ) $data = array();

        $title = sanitize_text_field( $data['title'] ?? '' );
        $slug  = sanitize_title( $data['slug'] ?? '' );
        if ( $title === '' || $slug === '' ) {
            return new WP_Error( 'bkh_missing_required', 'title and slug are required.', array( 'status' => 400 ) );
        }

        $status = sanitize_key( $data['status'] ?? 'draft' );
        if ( ! in_array( $status, array( 'draft', 'pending', 'publish' ), true ) ) {
            $status = 'draft';
        }
        if ( $status === 'publish' && ! current_user_can( 'publish_posts' ) ) {
            $status = 'draft';
        }

        $postarr = array(
            'post_type'    => 'knowledge_article',
            'post_status'  => $status,
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_excerpt' => wp_kses_post( $data['excerpt'] ?? '' ),
            'post_content' => wp_kses_post( $data['content'] ?? '' ),
        );

        $existing = get_page_by_path( $slug, OBJECT, 'knowledge_article' );
        if ( $existing instanceof WP_Post ) {
            $postarr['ID'] = $existing->ID;
            $post_id = wp_update_post( $postarr, true );
        } else {
            $post_id = wp_insert_post( $postarr, true );
        }

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        $category = sanitize_title( $data['category'] ?? '' );
        if ( $category !== '' ) {
            $term = term_exists( $category, 'knowledge_category' );
            if ( ! $term ) {
                $term = wp_insert_term( $category, 'knowledge_category', array( 'slug' => $category ) );
            }
            if ( ! is_wp_error( $term ) ) {
                wp_set_object_terms( $post_id, array( $category ), 'knowledge_category', false );
            }
        }

        if ( array_key_exists( 'summary', $data ) ) {
            update_post_meta( $post_id, 'summary', sanitize_textarea_field( $data['summary'] ) );
        }
        if ( array_key_exists( 'featured_priority', $data ) ) {
            update_post_meta( $post_id, '_bkh_featured_priority', (int) $data['featured_priority'] );
        }
        if ( isset( $data['key_points'] ) && is_array( $data['key_points'] ) ) {
            update_post_meta( $post_id, '_bkh_key_points', $this->sanitize_key_points( $data['key_points'] ) );
        }
        if ( isset( $data['faqs'] ) && is_array( $data['faqs'] ) ) {
            update_post_meta( $post_id, '_bkh_faqs', $this->sanitize_faqs( $data['faqs'] ) );
        }
        if ( isset( $data['videos'] ) && is_array( $data['videos'] ) ) {
            update_post_meta( $post_id, '_bkh_videos', $this->sanitize_videos( $data['videos'] ) );
        }

        clean_post_cache( $post_id );

        return rest_ensure_response( array(
            'post_id'   => $post_id,
            'status'    => get_post_status( $post_id ),
            'slug'      => get_post_field( 'post_name', $post_id ),
            'permalink' => get_permalink( $post_id ),
            'edit_url'  => get_edit_post_link( $post_id, 'raw' ),
            'preview'   => get_preview_post_link( $post_id ),
        ) );
    }

    private function sanitize_faqs( $faqs ) {
        $out = array();
        foreach ( $faqs as $f ) {
            if ( ! is_array( $f ) ) continue;
            $q = sanitize_text_field( $f['question'] ?? $f['q'] ?? '' );
            $a = sanitize_textarea_field( $f['answer'] ?? $f['a'] ?? '' );
            if ( $q === '' || $a === '' ) continue;
            $out[] = array( 'question' => $q, 'answer' => $a );
        }
        return $out;
    }

    private function sanitize_key_points( $points ) {
        $out = array();
        foreach ( $points as $point ) {
            $point = sanitize_text_field( $point );
            if ( $point !== '' ) {
                $out[] = $point;
            }
        }
        return array_slice( $out, 0, 5 );
    }

    private function sanitize_videos( $videos ) {
        $allowed = array( 'bilibili', 'youtube', 'douyin', 'wechat_video', 'kuaishou' );
        $out = array();
        foreach ( $videos as $v ) {
            if ( ! is_array( $v ) ) continue;
            $platform = sanitize_key( $v['platform'] ?? '' );
            $url = esc_url_raw( $v['video_url'] ?? '' );
            if ( ! in_array( $platform, $allowed, true ) || $url === '' ) continue;
            $out[] = array(
                'platform'      => $platform,
                'video_title'   => sanitize_text_field( $v['video_title'] ?? '' ),
                'video_url'     => $url,
                'video_id'      => sanitize_text_field( $v['video_id'] ?? '' ),
                'embed_code'    => wp_kses_post( $v['embed_code'] ?? '' ),
                'cover_image'   => esc_url_raw( $v['cover_image'] ?? '' ),
                'duration'      => sanitize_text_field( $v['duration'] ?? '' ),
                'publish_date'  => sanitize_text_field( $v['publish_date'] ?? '' ),
                'display_order' => (int) ( $v['display_order'] ?? 0 ),
                'is_primary'    => ! empty( $v['is_primary'] ) ? 1 : 0,
                'account_name'  => sanitize_text_field( $v['account_name'] ?? '' ),
                'account_url'   => esc_url_raw( $v['account_url'] ?? '' ),
                'tracking_code' => sanitize_text_field( $v['tracking_code'] ?? '' ),
            );
        }
        return $out;
    }
}
