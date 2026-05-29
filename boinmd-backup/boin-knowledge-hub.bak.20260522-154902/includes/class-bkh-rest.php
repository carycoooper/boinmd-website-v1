<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * REST API:
 *   GET /wp-json/boin/v1/social-videos
 *   GET /wp-json/boin/v1/knowledge-articles
 *   GET /wp-json/boin/v1/knowledge-topics
 *
 * All read-only public endpoints for now.
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
    }

    /* ============== handlers ============== */

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
}
