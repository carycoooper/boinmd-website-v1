<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BKH_Schema — JSON-LD output for Breadcrumb / Article / FAQ / VideoObject + OG tags.
 * Hooks into wp_head AND is also callable from templates if theme strips wp_head.
 */
class BKH_Schema {
    private static $instance;
    public static function instance() { return self::$instance ?: self::$instance = new self(); }

    private function __construct() {
        add_action( 'wp_head', array( $this, 'maybe_render' ), 8 );
    }

    public function maybe_render() {
        if ( is_singular( array( 'knowledge_article', 'knowledge_topic' ) ) ) {
            global $post;
            echo $this->breadcrumb_jsonld( $post );
            if ( get_post_type( $post ) === 'knowledge_article' ) {
                echo $this->article_jsonld( $post );
            }
            $faqs = bkh_get_visible_faqs( $post->ID );
            if ( ! empty( $faqs ) ) {
                echo $this->faq_jsonld( $faqs );
            }
            $videos = bkh_get_videos( $post->ID );
            foreach ( $videos as $v ) {
                $vjson = $this->video_jsonld( $v, $post );
                if ( $vjson ) echo $vjson;
            }
            echo $this->og_tags( $post );
        }
        if ( is_tax( 'knowledge_category' ) || (int) get_query_var( 'bkh_landing' ) === 1 || is_post_type_archive( 'knowledge_article' ) ) {
            echo $this->landing_breadcrumb_jsonld();
            echo $this->collection_page_jsonld();
            echo $this->item_list_jsonld();
        }
    }

    /* ============================================ */

    public function breadcrumb_jsonld( $post ) {
        $items = array(
            array( 'name' => '首页', 'url' => bkh_url( '/' ) ),
            array( 'name' => '知识中心', 'url' => bkh_url( '/knowledge/' ) ),
        );
        if ( get_post_type( $post ) === 'knowledge_article' ) {
            $terms = wp_get_object_terms( $post->ID, 'knowledge_category' );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $items[] = array(
                    'name' => $terms[0]->name,
                    'url'  => bkh_category_url( $terms[0]->slug ),
                );
            }
        } elseif ( get_post_type( $post ) === 'knowledge_topic' ) {
            $items[] = array( 'name' => '专题', 'url' => bkh_url( '/knowledge/' ) );
        }
        $items[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );

        $list = array();
        foreach ( $items as $i => $it ) {
            $list[] = array(
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $it['name'],
                'item'     => $it['url'],
            );
        }
        return $this->wrap( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list ) );
    }

    public function landing_breadcrumb_jsonld() {
        $items = array(
            array( 'name' => '首页', 'url' => bkh_url( '/' ) ),
            array( 'name' => '知识中心', 'url' => bkh_url( '/knowledge/' ) ),
        );
        if ( is_tax( 'knowledge_category' ) ) {
            $term = get_queried_object();
            if ( $term ) {
                $items[] = array( 'name' => $term->name, 'url' => bkh_category_url( $term->slug ) );
            }
        }
        $list = array();
        foreach ( $items as $i => $it ) {
            $list[] = array(
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $it['name'],
                'item'     => $it['url'],
            );
        }
        return $this->wrap( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list ) );
    }

    public function article_jsonld( $post ) {
        $author = get_the_author_meta( 'display_name', $post->post_author );
        $img = get_the_post_thumbnail_url( $post, 'full' );
        $data = array(
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => get_the_title( $post ),
            'description'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
            'datePublished'    => get_the_date( 'c', $post ),
            'dateModified'     => get_the_modified_date( 'c', $post ),
            'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink( $post ) ),
            'author'           => array( '@type' => 'Person', 'name' => $author ?: '博音 BOINMD' ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => '博音 BOINMD',
                'logo'  => array( '@type' => 'ImageObject', 'url' => bkh_url( '/assets/images/boinmd-logo.svg' ) ),
            ),
        );
        if ( $img ) $data['image'] = $img;
        return $this->wrap( $data );
    }

    public function faq_jsonld( $faqs ) {
        $entities = array();
        foreach ( $faqs as $f ) {
            if ( empty( $f['question'] ) || empty( $f['answer'] ) ) continue;
            $entities[] = array(
                '@type'          => 'Question',
                'name'           => wp_strip_all_tags( $f['question'] ),
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags( $f['answer'] ),
                ),
            );
        }
        if ( empty( $entities ) ) return '';
        return $this->wrap( array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ) );
    }

    public function video_jsonld( $v, $post ) {
        if ( empty( $v['video_url'] ) || empty( $v['video_title'] ) ) return '';
        $data = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'VideoObject',
            'name'        => $v['video_title'],
            'description' => wp_strip_all_tags( get_the_excerpt( $post ) ) ?: $v['video_title'],
        );
        if ( ! empty( $v['cover_image'] ) ) $data['thumbnailUrl'] = $v['cover_image'];
        if ( ! empty( $v['publish_date'] ) ) $data['uploadDate'] = $v['publish_date'];
        if ( ! empty( $v['duration'] ) )    $data['duration'] = $v['duration'];

        // Need at least one of contentUrl/embedUrl for valid Schema
        $provider = bkh_get_provider( $v['platform'] ?? '' );
        $embed = $provider ? $provider->get_embed_url( $v ) : '';
        if ( $embed ) {
            $data['embedUrl'] = $embed;
        } else {
            $data['contentUrl'] = $v['video_url'];
        }

        // Drop if missing required fields
        if ( empty( $data['thumbnailUrl'] ) || empty( $data['uploadDate'] ) ) {
            // VideoObject requires thumbnailUrl + uploadDate per Google;
            // skip outputting incomplete schema to avoid validation errors.
            return '';
        }
        return $this->wrap( $data );
    }

    public function og_tags( $post ) {
        // If Yoast or RankMath active, let them own OG to avoid dup tags
        if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) return '';

        $title = wp_strip_all_tags( get_the_title( $post ) ) . ' — 博音 BOINMD 听力知识中心';
        $desc  = wp_strip_all_tags( get_the_excerpt( $post ) ) ?: get_bloginfo( 'description' );
        $img   = get_the_post_thumbnail_url( $post, 'full' );
        $url   = get_permalink( $post );

        $out  = '';
        $out .= sprintf( "<meta property=\"og:type\" content=\"article\">\n" );
        $out .= sprintf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
        $out .= sprintf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $desc ) );
        $out .= sprintf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
        if ( $img ) {
            $out .= sprintf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $img ) );
        }
        $out .= "<meta property=\"og:site_name\" content=\"博音 BOINMD 听力知识中心\">\n";
        $out .= "<meta property=\"og:locale\" content=\"zh_CN\">\n";
        return $out;
    }

    public function collection_page_jsonld() {
        $name = '听力知识中心';
        $url  = bkh_url( '/knowledge/' );
        $description = '关于耳鸣、听力下降、助听器与 AI 智能助听的知识内容。';

        if ( is_tax( 'knowledge_category' ) ) {
            $term = get_queried_object();
            if ( $term && ! is_wp_error( $term ) ) {
                $name = $term->name . ' - 听力知识中心';
                $url  = bkh_category_url( $term->slug );
                if ( ! empty( $term->description ) ) $description = $term->description;
            }
        }

        return $this->wrap( array(
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $name,
            'description' => $description,
            'url'         => $url,
            'inLanguage'  => 'zh-CN',
            'isPartOf'    => array(
                '@type' => 'WebSite',
                'name'  => get_bloginfo( 'name' ),
                'url'   => home_url( '/' ),
            ),
        ) );
    }

    public function item_list_jsonld() {
        $page = 1;
        $query_args = array(
            'post_type'      => 'knowledge_article',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'orderby'        => array( 'date' => 'DESC' ),
        );

        if ( function_exists( 'bkh_is_knowledge_landing' ) && bkh_is_knowledge_landing() ) {
            $page = isset( $_GET['kpage'] ) ? max( 1, (int) $_GET['kpage'] ) : 1;
            $query_args['posts_per_page'] = 6;
            $query_args['paged'] = $page;
            $query_args['meta_key'] = '_bkh_featured_priority';
            $query_args['orderby'] = array( 'meta_value_num' => 'ASC', 'date' => 'DESC' );
        }

        if ( is_tax( 'knowledge_category' ) ) {
            $term = get_queried_object();
            if ( $term && ! is_wp_error( $term ) ) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'knowledge_category',
                        'field'    => 'slug',
                        'terms'    => $term->slug,
                    ),
                );
            }
        }

        $q = new WP_Query( $query_args );
        if ( ! $q->have_posts() ) return '';

        $items = array();
        $pos = ( ( $page - 1 ) * (int) $query_args['posts_per_page'] ) + 1;
        while ( $q->have_posts() ) {
            $q->the_post();
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => get_the_title(),
                'url'      => get_permalink(),
            );
        }
        wp_reset_postdata();

        return $this->wrap( array(
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'itemListElement' => $items,
        ) );
    }

    private function wrap( $data ) {
        ob_start();
        bkh_output_json_ld_once( $data );
        return ob_get_clean();
    }
}
