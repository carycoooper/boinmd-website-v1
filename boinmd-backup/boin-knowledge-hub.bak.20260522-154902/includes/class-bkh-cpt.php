<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BKH_CPT
 * Register knowledge_article + knowledge_topic CPTs and knowledge_category taxonomy.
 * URLs:
 *   /knowledge/                          (post type archive of knowledge_article)
 *   /knowledge/{category}/               (taxonomy term archive)
 *   /knowledge/{category}/{post-slug}/   (single knowledge_article)
 *   /knowledge/{topic-slug}/             (single knowledge_topic)  -- same as category slug if matched
 *
 * Note: WordPress permalinks alone can't deliver `{cat}/{slug}` for a CPT
 * without a custom rewrite + post_link filter. We do that here.
 */
class BKH_CPT {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'init', array( $this, 'register_all' ), 5 );
        add_filter( 'post_type_link', array( $this, 'filter_article_permalink' ), 10, 2 );
    }

    public function register_all() {
        $this->register_taxonomy();
        $this->register_cpts();
    }

    private function register_taxonomy() {
        register_taxonomy(
            'knowledge_category',
            array( 'knowledge_article', 'knowledge_topic' ),
            array(
                'labels' => array(
                    'name'              => '知识分类',
                    'singular_name'     => '知识分类',
                    'search_items'      => '搜索分类',
                    'all_items'         => '全部分类',
                    'edit_item'         => '编辑分类',
                    'update_item'       => '更新分类',
                    'add_new_item'      => '新增分类',
                    'new_item_name'     => '新分类名称',
                    'menu_name'         => '知识分类',
                ),
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => array(
                    'slug'         => 'knowledge',
                    'with_front'   => false,
                    'hierarchical' => false,
                ),
            )
        );
    }

    private function register_cpts() {
        // ===== knowledge_article =====
        register_post_type( 'knowledge_article', array(
            'labels' => array(
                'name'               => '知识文章',
                'singular_name'      => '知识文章',
                'menu_name'          => '知识文章',
                'archives'           => '听力知识中心',
                'add_new'            => '新增',
                'add_new_item'       => '新增知识文章',
                'edit_item'          => '编辑知识文章',
                'new_item'           => '新知识文章',
                'view_item'          => '查看知识文章',
                'search_items'       => '搜索知识文章',
                'not_found'          => '未找到知识文章',
                'not_found_in_trash' => '回收站为空',
            ),
            'public'              => true,
            'show_in_rest'        => true,
            'menu_position'       => 23,
            'menu_icon'           => 'dashicons-book-alt',
            'has_archive'         => 'knowledge',
            'rewrite'             => array(
                // %knowledge_category% is replaced via post_type_link filter
                'slug'       => 'knowledge/%knowledge_category%',
                'with_front' => false,
                'feeds'      => false,
            ),
            'supports' => array(
                'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields',
            ),
            'taxonomies' => array( 'knowledge_category' ),
        ) );

        // ===== knowledge_topic =====
        // Topic permalinks: /knowledge/{topic-slug}/
        // We use the same `knowledge` prefix; topics double as category landing pages.
        register_post_type( 'knowledge_topic', array(
            'labels' => array(
                'name'               => '知识专题',
                'singular_name'      => '知识专题',
                'menu_name'          => '知识专题',
                'add_new'            => '新增',
                'add_new_item'       => '新增知识专题',
                'edit_item'          => '编辑知识专题',
                'new_item'           => '新知识专题',
                'view_item'          => '查看知识专题',
            ),
            'public'              => true,
            'show_in_rest'        => true,
            'menu_position'       => 24,
            'menu_icon'           => 'dashicons-category',
            'has_archive'         => false,
            'rewrite'             => array(
                'slug'       => 'knowledge-topic',
                'with_front' => false,
            ),
            'supports' => array(
                'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields',
            ),
            'taxonomies' => array( 'knowledge_category' ),
        ) );
    }

    /**
     * Replace %knowledge_category% in article permalinks with actual term slug.
     */
    public function filter_article_permalink( $url, $post ) {
        if ( get_post_type( $post ) !== 'knowledge_article' ) return $url;
        if ( strpos( $url, '%knowledge_category%' ) === false ) return $url;

        $terms = wp_get_object_terms( $post->ID, 'knowledge_category' );
        $slug  = 'uncategorized';
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            // Prefer non-default term
            foreach ( $terms as $t ) { $slug = $t->slug; break; }
        }
        return str_replace( '%knowledge_category%', $slug, $url );
    }
}
