<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Dynamic sitemap for the knowledge hub.
 *
 * Endpoint:
 *   /blog/knowledge-sitemap.xml
 *
 * The root site is static, while knowledge content lives in WordPress. This
 * sitemap reads published knowledge articles directly from WordPress so new
 * articles are discoverable without manually editing /sitemap.xml.
 */
class BKH_Sitemap {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'init', array( $this, 'add_rewrite_rule' ), 20 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_sitemap' ), 1 );
    }

    public function add_rewrite_rule() {
        add_rewrite_rule(
            '^knowledge-sitemap\.xml$',
            'index.php?bkh_knowledge_sitemap=1',
            'top'
        );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'bkh_knowledge_sitemap';
        return $vars;
    }

    public function maybe_render_sitemap() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

        $is_sitemap = (int) get_query_var( 'bkh_knowledge_sitemap' ) === 1;
        if ( ! $is_sitemap && is_string( $request_path ) ) {
            $is_sitemap = (bool) preg_match( '#/(blog/)?knowledge-sitemap\.xml$#', $request_path );
        }

        if ( ! $is_sitemap ) return;

        status_header( 200 );
        nocache_headers();
        header( 'Content-Type: application/xml; charset=UTF-8' );
        echo $this->build_xml();
        exit;
    }

    private function build_xml() {
        $urls = array();

        $urls[] = array(
            'loc'        => $this->root_url( '/knowledge/' ),
            'lastmod'    => $this->latest_knowledge_modified(),
            'changefreq' => 'daily',
            'priority'   => '0.9',
        );

        $terms = get_terms( array(
            'taxonomy'   => 'knowledge_category',
            'hide_empty' => false,
        ) );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $urls[] = array(
                    'loc'        => $this->root_url( '/knowledge/' . $term->slug . '/' ),
                    'lastmod'    => $this->latest_term_modified( $term->slug ),
                    'changefreq' => 'weekly',
                    'priority'   => $this->topic_priority( $term->slug ),
                );
            }
        }

        $articles = get_posts( array(
            'post_type'      => 'knowledge_article',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ) );
        foreach ( $articles as $post ) {
            $urls[] = array(
                'loc'        => $this->strip_blog( get_permalink( $post ) ),
                'lastmod'    => $this->post_lastmod( $post ),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            );
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ( $urls as $url ) {
            if ( empty( $url['loc'] ) ) continue;
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . esc_xml( $url['loc'] ) . "</loc>\n";
            if ( ! empty( $url['lastmod'] ) ) {
                $xml .= '    <lastmod>' . esc_xml( $url['lastmod'] ) . "</lastmod>\n";
            }
            if ( ! empty( $url['changefreq'] ) ) {
                $xml .= '    <changefreq>' . esc_xml( $url['changefreq'] ) . "</changefreq>\n";
            }
            if ( ! empty( $url['priority'] ) ) {
                $xml .= '    <priority>' . esc_xml( $url['priority'] ) . "</priority>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";
        return $xml;
    }

    private function root_url( $path ) {
        $root = preg_replace( '#/blog/?$#', '', home_url( '/' ) );
        return trailingslashit( untrailingslashit( $root ) ) . ltrim( $path, '/' );
    }

    private function strip_blog( $url ) {
        return preg_replace( '#(https?://[^/]+)/blog/knowledge/#', '$1/knowledge/', $url );
    }

    private function post_lastmod( $post ) {
        $time = $post->post_modified_gmt ?: $post->post_date_gmt;
        return $time ? gmdate( 'c', strtotime( $time ) ) : gmdate( 'c' );
    }

    private function latest_knowledge_modified() {
        $latest = get_posts( array(
            'post_type'      => 'knowledge_article',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );
        if ( empty( $latest ) ) return gmdate( 'c' );
        return $this->post_lastmod( get_post( $latest[0] ) );
    }

    private function latest_term_modified( $slug ) {
        $posts = get_posts( array(
            'post_type'      => 'knowledge_article',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'knowledge_category',
                    'field'    => 'slug',
                    'terms'    => $slug,
                ),
            ),
        ) );
        if ( empty( $posts ) ) return $this->latest_knowledge_modified();
        return $this->post_lastmod( get_post( $posts[0] ) );
    }

    private function topic_priority( $slug ) {
        $core = array( 'tinnitus', 'hearing-loss', 'hearing-aids', 'ai-hearing' );
        return in_array( $slug, $core, true ) ? '0.8' : '0.6';
    }
}
