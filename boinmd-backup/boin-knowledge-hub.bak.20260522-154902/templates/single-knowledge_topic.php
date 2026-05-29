<?php
/**
 * single-knowledge_topic.php - 知识专题详情
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
$sub      = get_post_meta( $post_id, '_bkh_hero_subtitle', true );
$hero_img = get_post_meta( $post_id, '_bkh_hero_image', true ) ?: get_the_post_thumbnail_url( null, 'full' );
$scenes   = bkh_get_scenes( $post_id );
$reasons  = bkh_get_reasons( $post_id );
$faqs     = bkh_get_faqs( $post_id );
$terms    = wp_get_object_terms( $post_id, 'knowledge_category' );
$cat_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '';

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

<main class="bkh-page bkh-topic">

  <section class="bkh-hero bkh-hero-topic" <?php if ( $hero_img ) printf( 'style="background-image:linear-gradient(180deg,rgba(255,255,255,.85) 0%%,rgba(255,255,255,.95) 100%%),url(%s);background-size:cover;background-position:center"', esc_url( $hero_img ) ); ?>>
    <div class="bkh-wrap">
      <nav class="bkh-crumbs">
        <a href="<?php echo esc_url( bkh_url( '/' ) ); ?>">首页</a> ·
        <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">知识中心</a> ·
        <span><?php the_title(); ?></span>
      </nav>
      <h1 class="bkh-hero-title"><?php the_title(); ?></h1>
      <?php if ( $sub ) : ?><p class="bkh-hero-sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
      <?php if ( has_excerpt() ) : ?><p class="bkh-hero-summary"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
    </div>
  </section>

  <?php if ( ! empty( $cats ) ) : ?>
  <section class="bkh-tabs">
    <div class="bkh-wrap">
      <nav class="bkh-tab-nav" aria-label="知识分类">
        <a class="bkh-tab" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">全部</a>
        <?php foreach ( $cats as $c ) : ?>
          <a class="bkh-tab <?php echo $c->slug === $cat_slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( bkh_category_url( $c->slug ) ); ?>">
            <?php echo esc_html( $cat_labels[ $c->slug ] ?? $c->name ); ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $scenes ) ) : ?>
  <section class="bkh-block bkh-scenes">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">常见场景</h2>
      <div class="bkh-scene-grid">
        <?php foreach ( $scenes as $s ) :
          $link = $s['scene_link'] ?? '';
        ?>
          <?php if ( $link ) : ?><a class="bkh-scene-card" href="<?php echo esc_url( $link ); ?>"><?php else : ?><div class="bkh-scene-card"><?php endif; ?>
            <?php if ( ! empty( $s['scene_icon'] ) ) : ?>
              <span class="bkh-scene-icon"><?php echo esc_html( $s['scene_icon'] ); ?></span>
            <?php endif; ?>
            <h3 class="bkh-scene-title"><?php echo esc_html( $s['scene_title'] ?? '' ); ?></h3>
            <p class="bkh-scene-desc"><?php echo esc_html( $s['scene_description'] ?? '' ); ?></p>
          <?php if ( $link ) : ?></a><?php else : ?></div><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php $content = apply_filters( 'the_content', get_the_content() ); if ( trim( wp_strip_all_tags( $content ) ) ) : ?>
  <section class="bkh-block bkh-content">
    <div class="bkh-wrap bkh-wrap-text">
      <?php echo $content; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $reasons ) ) : ?>
  <section class="bkh-block bkh-reasons">
    <div class="bkh-wrap bkh-wrap-text">
      <h2 class="bkh-section-title">原因说明</h2>
      <?php foreach ( $reasons as $r ) : ?>
        <article class="bkh-reason">
          <h3 class="bkh-reason-title"><?php echo esc_html( $r['reason_title'] ?? '' ); ?></h3>
          <div class="bkh-reason-desc"><?php echo wp_kses_post( $r['reason_description'] ?? '' ); ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $faqs ) ) : bkh_render_faq_block( $faqs ); endif; ?>

  <?php bkh_render_video_block( $post_id ); ?>

  <?php
  if ( $cat_slug ) {
      $related = new WP_Query( array(
          'post_type'      => 'knowledge_article',
          'posts_per_page' => 6,
          'meta_key'       => '_bkh_featured_priority',
          'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
          'tax_query'      => array( array( 'taxonomy' => 'knowledge_category', 'field' => 'slug', 'terms' => $cat_slug ) ),
      ) );
      if ( $related->have_posts() ) : ?>
        <section class="bkh-block bkh-related">
          <div class="bkh-wrap">
            <h2 class="bkh-section-title">本专题相关文章</h2>
            <div class="bkh-article-grid">
              <?php while ( $related->have_posts() ) : $related->the_post();
                $thumb = get_the_post_thumbnail_url( null, 'medium' );
              ?>
                <article class="bkh-art-card">
                  <?php if ( $thumb ) : ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a><?php endif; ?>
                  <div class="bkh-art-body">
                    <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
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

</main>

<?php get_footer();
