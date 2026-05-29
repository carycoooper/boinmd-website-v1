<?php
/**
 * single-knowledge_article.php — 知识文章详情
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'bkh_render_video_block' ) ) {
    require_once BKH_DIR . 'templates/partials/video-block.php';
}
if ( ! function_exists( 'bkh_render_faq_block' ) ) {
    require_once BKH_DIR . 'templates/partials/faq-block.php';
}
if ( ! function_exists( 'bkh_render_cta_block' ) ) {
    require_once BKH_DIR . 'templates/partials/cta-block.php';
}

get_header();
wp_enqueue_style( 'bkh-frontend', BKH_URL . 'assets/css/bkh-frontend.css', array(), BKH_VERSION );

if ( ! have_posts() ) { get_footer(); return; }
the_post();
$post_id = get_the_ID();
$terms = wp_get_object_terms( $post_id, 'knowledge_category' );
$cat_name = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
$cat_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '';
$faqs = bkh_get_faqs( $post_id );
?>

<main class="bkh-page bkh-article">

  <article class="bkh-art-detail">

    <header class="bkh-art-header">
      <div class="bkh-wrap">
        <nav class="bkh-crumbs">
          <a href="<?php echo esc_url( bkh_url( '/' ) ); ?>">首页</a> ›
          <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">知识中心</a>
          <?php if ( $cat_slug ) : ?> › <a href="<?php echo esc_url( bkh_category_url( $cat_slug ) ); ?>"><?php echo esc_html( $cat_name ); ?></a><?php endif; ?>
          › <span><?php the_title(); ?></span>
        </nav>
        <?php if ( $cat_name ) : ?><span class="bkh-art-tag"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
        <h1 class="bkh-art-h1"><?php the_title(); ?></h1>
        <?php if ( has_excerpt() ) : ?><p class="bkh-art-lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        <p class="bkh-art-meta">
          <span>作者：<?php the_author(); ?></span>
          <span>·</span>
          <span>发布：<?php echo esc_html( get_the_date() ); ?></span>
          <?php if ( get_the_modified_date() !== get_the_date() ) : ?>
            <span>·</span><span>更新：<?php echo esc_html( get_the_modified_date() ); ?></span>
          <?php endif; ?>
        </p>
      </div>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
      <figure class="bkh-art-cover">
        <?php the_post_thumbnail( 'large' ); ?>
      </figure>
    <?php endif; ?>

    <section class="bkh-art-body-wrap">
      <div class="bkh-wrap bkh-wrap-text">
        <?php the_content(); ?>
      </div>
    </section>

    <?php if ( ! empty( $faqs ) ) : bkh_render_faq_block( $faqs ); endif; ?>

    <?php bkh_render_video_block( $post_id ); ?>

    <?php
    // Related: same category, exclude self
    if ( $cat_slug ) {
        $rel = new WP_Query( array(
            'post_type'      => 'knowledge_article',
            'posts_per_page' => 4,
            'post__not_in'   => array( $post_id ),
            'meta_key'       => '_bkh_featured_priority',
            'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
            'tax_query'      => array( array( 'taxonomy' => 'knowledge_category', 'field' => 'slug', 'terms' => $cat_slug ) ),
        ) );
        if ( $rel->have_posts() ) : ?>
          <section class="bkh-block bkh-related">
            <div class="bkh-wrap">
              <h2 class="bkh-section-title">相关阅读</h2>
              <div class="bkh-article-grid">
                <?php while ( $rel->have_posts() ) : $rel->the_post();
                  $thumb = get_the_post_thumbnail_url( null, 'medium' );
                ?>
                  <article class="bkh-art-card">
                    <?php if ( $thumb ) : ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a><?php endif; ?>
                    <div class="bkh-art-body">
                      <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    </div>
                  </article>
                <?php endwhile; wp_reset_postdata(); ?>
              </div>
            </div>
          </section>
        <?php endif;
    }
    ?>

    <?php bkh_render_cta_block( $post_id ); ?>

  </article>
</main>

<?php get_footer();
