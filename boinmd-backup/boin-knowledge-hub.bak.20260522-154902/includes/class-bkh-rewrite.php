<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BKH_Rewrite
 * - Output-side URL filters that strip `/blog/` prefix from knowledge URLs.
 *   (Nginx handles input-side rewriting from /knowledge/* to /blog/knowledge/*.)
 * - Add a /knowledge/ landing-page route (knowledge hub home).
 */
class BKH_Rewrite {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        // Add rewrite rule for /knowledge/ landing page
        add_action( 'init', array( $this, 'add_landing_rule' ), 20 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_landing' ), 5 );

        // ==== URL output filters: strip /blog/ in front of /knowledge/ ====
        add_filter( 'post_type_link',          array( $this, 'strip_blog' ), 99 );
        add_filter( 'post_link',               array( $this, 'strip_blog' ), 99 );
        add_filter( 'term_link',               array( $this, 'strip_blog' ), 99 );
        add_filter( 'post_type_archive_link',  array( $this, 'strip_blog' ), 99 );
        add_filter( 'home_url',                array( $this, 'home_url_filter' ), 99, 4 );

        // ==== Yoast SEO compatibility (if installed) ====
        add_filter( 'wpseo_canonical',     array( $this, 'strip_blog' ), 99 );
        add_filter( 'wpseo_opengraph_url', array( $this, 'strip_blog' ), 99 );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'bkh_landing';
        return $vars;
    }

    public function add_landing_rule() {
        // /knowledge/?  -> bkh_landing=1  (when nginx forwards /knowledge -> /blog/knowledge)
        add_rewrite_rule(
            '^knowledge/?$',
            'index.php?bkh_landing=1',
            'top'
        );
    }

    public function maybe_render_landing() {
        if ( (int) get_query_var( 'bkh_landing' ) !== 1 ) return;
        status_header( 200 );
        // Locate template from plugin
        $template = BKH_TEMPLATE_DIR . 'archive-knowledge.php';
        if ( file_exists( $template ) ) {
            // Set a global for template
            $GLOBALS['bkh_landing'] = true;
            include $template;
            exit;
        }
    }

    public function strip_blog( $url ) {
        if ( ! is_string( $url ) || $url === '' ) return $url;
        return preg_replace( '#(https?://[^/]+)/blog/knowledge/#', '$1/knowledge/', $url );
    }

    /**
     * Only rewrite home_url calls that target /blog/knowledge/* — leave admin / general home_url alone.
     */
    public function home_url_filter( $url, $path, $orig_scheme, $blog_id ) {
        if ( is_admin() ) return $url;
        if ( strpos( $url, '/blog/knowledge/' ) !== false ) {
            return $this->strip_blog( $url );
        }
        return $url;
    }
}
