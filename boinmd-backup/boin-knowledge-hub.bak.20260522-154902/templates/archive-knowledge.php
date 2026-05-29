<?php
/**
 * Knowledge Hub landing page (/knowledge/)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
$hero_title    = $settings['hero_title'] ?? '听力知识中心';
$hero_subtitle = $settings['hero_subtitle'] ?? '';
$hero_ph       = $settings['hero_placeholder'] ?? '';
$hot           = $settings['hot_questions'] ?? array();

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
    foreach ( $cats_raw as $term ) {
        $cats_map[ $term->slug ] = $term;
    }
}
$cats = array();
foreach ( $cat_order as $slug ) {
    if ( isset( $cats_map[ $slug ] ) ) {
        $cats[] = $cats_map[ $slug ];
    }
}

$trending = new WP_Query( array(
    'post_type'      => 'knowledge_article',
    'posts_per_page' => 4,
    'meta_key'       => '_bkh_featured_priority',
    'meta_type'      => 'NUMERIC',
    'meta_query'     => array(
        array(
            'key'     => '_bkh_featured_priority',
            'value'   => 1,
            'type'    => 'NUMERIC',
            'compare' => '>=',
        ),
    ),
    'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
) );

$articles = new WP_Query( array(
    'post_type'      => 'knowledge_article',
    'posts_per_page' => 9,
    'meta_key'       => '_bkh_featured_priority',
    'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
) );

wp_enqueue_style( 'bkh-frontend', BKH_URL . 'assets/css/bkh-frontend.css', array(), BKH_VERSION );
?>

<main class="bkh-page bkh-landing">

  <section class="bkh-hero">
    <div class="bkh-wrap">
      <p class="bkh-eyebrow">Knowledge Hub</p>
      <h1 class="bkh-hero-title"><?php echo esc_html( $hero_title ); ?></h1>
      <?php if ( $hero_subtitle ) : ?>
        <p class="bkh-hero-sub"><?php echo esc_html( $hero_subtitle ); ?></p>
      <?php endif; ?>
      <form class="bkh-search bkh-search-hero" role="search" method="get" action="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">
        <input type="search" name="s" placeholder="<?php echo esc_attr( $hero_ph ); ?>" aria-label="搜索听力知识">
        <input type="hidden" name="post_type[]" value="knowledge_article">
        <input type="hidden" name="post_type[]" value="knowledge_topic">
        <button type="submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          搜索
        </button>
      </form>
    </div>
  </section>

  <?php if ( ! empty( $cats ) ) : ?>
  <section class="bkh-tabs">
    <div class="bkh-wrap">
      <nav class="bkh-tab-nav" aria-label="知识分类">
        <a class="bkh-tab is-active" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">全部</a>
        <?php foreach ( $cats as $c ) : ?>
          <a class="bkh-tab" href="<?php echo esc_url( bkh_category_url( $c->slug ) ); ?>">
            <?php echo esc_html( $cat_labels[ $c->slug ] ?? $c->name ); ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $hot ) ) : ?>
  <section class="bkh-hot">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">热门问题入口</h2>
      <div class="bkh-hot-grid">
        <?php foreach ( $hot as $q ) :
          $url = $q['url'] ?: bkh_url( '/knowledge/' );
          if ( strpos( $url, 'http' ) !== 0 && substr( $url, 0, 1 ) !== '/' ) $url = '/' . $url;
        ?>
          <a class="bkh-hot-card" href="<?php echo esc_url( $url ); ?>">
            <span class="bkh-hot-q"><?php echo esc_html( $q['label'] ); ?></span>
            <span class="bkh-hot-arrow" aria-hidden="true">→</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $cats ) ) : ?>
  <section class="bkh-topics">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">专题入口区</h2>
      <div class="bkh-topic-grid">
        <?php foreach ( $cats as $c ) :
          $url = bkh_category_url( $c->slug );
        ?>
          <a class="bkh-topic-card" href="<?php echo esc_url( $url ); ?>">
            <h3 class="bkh-topic-name"><?php echo esc_html( $cat_labels[ $c->slug ] ?? $c->name ); ?></h3>
            <p class="bkh-topic-desc"><?php echo esc_html( $c->description ?: ( '探索' . ( $cat_labels[ $c->slug ] ?? $c->name ) . '相关知识与建议。' ) ); ?></p>
            <span class="bkh-topic-link">进入专题 →</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $trending->have_posts() ) : ?>
  <section class="bkh-trending">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">热门文章</h2>
      <div class="bkh-trending-list">
        <?php while ( $trending->have_posts() ) : $trending->the_post(); ?>
          <a class="bkh-trending-item" href="<?php the_permalink(); ?>">
            <h3 class="bkh-trending-title"><?php the_title(); ?></h3>
            <span class="bkh-trending-cta">查看解决方案 →</span>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $articles->have_posts() ) : ?>
  <section class="bkh-recommended">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">最新文章</h2>
      <div class="bkh-article-grid">
        <?php while ( $articles->have_posts() ) : $articles->the_post();
          $terms = wp_get_object_terms( get_the_ID(), 'knowledge_category' );
          $cat_label = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
          $thumb = get_the_post_thumbnail_url( null, 'medium' );
          $word_count = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
          $read_minutes = max( 1, (int) ceil( $word_count / 260 ) );
        ?>
          <article class="bkh-art-card">
            <?php if ( $thumb ) : ?>
              <a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a>
            <?php endif; ?>
            <div class="bkh-art-body">
              <?php if ( $cat_label ) : ?><span class="bkh-art-tag"><?php echo esc_html( $cat_label ); ?></span><?php endif; ?>
              <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
              <div class="bkh-art-meta-row">
                <span class="bkh-art-meta"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></span>
                <span class="bkh-art-meta"><?php echo esc_html( $read_minutes ); ?> 分钟阅读</span>
              </div>
              <a class="bkh-art-cta" href="<?php the_permalink(); ?>">阅读全文</a>
            </div>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="bkh-bottom-nav">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title bkh-section-title-sm">知识导航</h2>
      <ul class="bkh-bottom-list">
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/tinnitus/' ) ); ?>">耳鸣专题</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">听力下降</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-aids/' ) ); ?>">助听器百科</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/ai-hearing/' ) ); ?>">AI智能助听</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/care/' ) ); ?>">使用与保养</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/fitting/' ) ); ?>">验配指南</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/stories/' ) ); ?>">用户案例</a></li>
      </ul>
    </div>
  </section>

</main>

<?php get_footer();
