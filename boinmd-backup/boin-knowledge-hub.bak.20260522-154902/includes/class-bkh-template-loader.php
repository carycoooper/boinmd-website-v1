<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BKH_Template_Loader
 * Override theme templates for our CPT/Taxonomy with bundled plugin templates,
 * unless the theme provides its own.
 */
class BKH_Template_Loader {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_filter( 'template_include', array( $this, 'route' ), 99 );
    }

    public function route( $template ) {
        // Knowledge hub landing page
        if ( (int) get_query_var( 'bkh_landing' ) === 1 ) {
            $t = $this->locate( 'archive-knowledge.php' );
            if ( $t ) return $t;
        }
        // Archive page for knowledge_article (when registered with has_archive = 'knowledge')
        if ( is_post_type_archive( 'knowledge_article' ) ) {
            $t = $this->locate( 'archive-knowledge.php' );
            if ( $t ) return $t;
        }
        // Taxonomy category landing -> show topic if a knowledge_topic exists with matching slug,
        // otherwise list articles in that category
        if ( is_tax( 'knowledge_category' ) ) {
            $t = $this->locate( 'taxonomy-knowledge_category.php' );
            if ( $t ) return $t;
        }
        if ( is_singular( 'knowledge_article' ) ) {
            $t = $this->locate( 'single-knowledge_article.php' );
            if ( $t ) return $t;
        }
        if ( is_singular( 'knowledge_topic' ) ) {
            $t = $this->locate( 'single-knowledge_topic.php' );
            if ( $t ) return $t;
        }
        return $template;
    }

    private function locate( $file ) {
        // 1) theme override: search /theme/boin-knowledge-hub/$file
        $theme_path = locate_template( array( 'boin-knowledge-hub/' . $file ) );
        if ( $theme_path ) return $theme_path;
        // 2) plugin default
        $plugin_path = BKH_TEMPLATE_DIR . $file;
        return file_exists( $plugin_path ) ? $plugin_path : '';
    }
}
