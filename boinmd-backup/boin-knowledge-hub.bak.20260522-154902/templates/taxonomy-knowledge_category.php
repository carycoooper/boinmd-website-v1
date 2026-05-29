<?php
/**
 * Taxonomy term archive — knowledge_category
 * If a knowledge_topic post with matching slug exists, render the topic template.
 * Otherwise render a generic category list.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$term = get_queried_object();

// Look for a matching knowledge_topic by slug
$topic_q = new WP_Query( array(
    'post_type'      => 'knowledge_topic',
    'name'           => $term->slug,
    'posts_per_page' => 1,
) );
if ( $topic_q->have_posts() ) {
    $topic_q->the_post();
    // Render the topic template inline
    include BKH_TEMPLATE_DIR . 'single-knowledge_topic.php';
    wp_reset_postdata();
    return;
}

get_header();
wp_enqueue_style( 'bkh-frontend', BKH_URL . 'assets/css/bkh-frontend.css', array(), BKH_VERSION );

$cat_order = array( 'tinnitus', 'hearing-loss', 'hearing-aids', 'ai-hearing', 'care', 'fitting', 'stories' );
$cat_labels = array(
    'tinnitus'     => '耳鸣专题',
    'hearing-loss' => '听力下降',
    'hearing-aids' => '助听器百科',
    'ai-hearing'   => 'AI智能助听',
    'care'         => '使用与保养',
    'fitting'      => '验配指南',
    'stories'      => '用户案例',
);
$cats_raw = get_terms( array( 'taxonomy' => 'knowledge_category', 'hide_empty' => false ) );
$cats_map = array();
if ( ! is_wp_error( $cats_raw ) ) {
    foreach ( $cats_raw as $c ) {
        $cats_map[ $c->slug ] = $c;
    }
}
$cats = array();
foreach ( $cat_order as $slug ) {
    if ( isset( $cats_map[ $slug ] ) ) $cats[] = $cats_map[ $slug ];
}
?>

<main class="bkh-page bkh-tax">
  <section class="bkh-hero bkh-hero-sub-page">
    <div class="bkh-wrap">
      <nav class="bkh-crumbs">
        <a href="<?php echo esc_url( bkh_url( '/' ) ); ?>">首页</a> ›
        <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">知识中心</a> ›
        <span><?php echo esc_html( $term->name ); ?></span>
      </nav>
      <h1 class="bkh-hero-title"><?php echo esc_html( $term->name ); ?></h1>
      <?php if ( $term->description ) : ?>
        <p class="bkh-hero-sub"><?php echo esc_html( $term->description ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ( ! empty( $cats ) ) : ?>
  <section class="bkh-tabs">
    <div class="bkh-wrap">
      <nav class="bkh-tab-nav" aria-label="知识分类">
        <a class="bkh-tab" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">全部</a>
        <?php foreach ( $cats as $c ) : ?>
          <a class="bkh-tab <?php echo $c->slug === $term->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( bkh_category_url( $c->slug ) ); ?>">
            <?php echo esc_html( $cat_labels[ $c->slug ] ?? $c->name ); ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>
  <?php endif; ?>

  <section class="bkh-recommended">
    <div class="bkh-wrap">
      <?php
      $args = array(
          'post_type'      => 'knowledge_article',
          'posts_per_page' => 12,
          'meta_key'       => '_bkh_featured_priority',
          'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
          'tax_query'      => array(
              array( 'taxonomy' => 'knowledge_category', 'field' => 'slug', 'terms' => $term->slug ),
          ),
      );
      $list = new WP_Query( $args );
      if ( $list->have_posts() ) : ?>
        <div class="bkh-article-grid">
          <?php while ( $list->have_posts() ) : $list->the_post();
            $thumb = get_the_post_thumbnail_url( null, 'medium' );
          ?>
            <article class="bkh-art-card">
              <?php if ( $thumb ) : ?>
                <a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a>
              <?php endif; ?>
              <div class="bkh-art-body">
                <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
              </div>
            </article>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?>
        <p class="bkh-empty">本专题暂未发布内容，敬请期待。</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer();
