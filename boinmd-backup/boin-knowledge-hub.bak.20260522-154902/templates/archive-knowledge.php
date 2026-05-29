<?php
/**
 * Knowledge Hub landing page  (/knowledge/)
 * Sections:
 *  1. Hero + search
 *  2. Hot question cards
 *  3. Topic cards (all knowledge_category terms)
 *  4. Recommended articles (by featured_priority, fallback date)
 *  5. Bottom knowledge nav
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
$hero_title    = $settings['hero_title']    ?? '听力知识中心';
$hero_subtitle = $settings['hero_subtitle'] ?? '';
$hero_ph       = $settings['hero_placeholder'] ?? '';
$hot           = $settings['hot_questions'] ?? array();

$cats = get_terms( array( 'taxonomy' => 'knowledge_category', 'hide_empty' => false ) );

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
      <p class="bkh-eyebrow">Knowledge Hub · 博音听力知识中心</p>
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

  <?php if ( ! empty( $hot ) ) : ?>
  <section class="bkh-hot">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">热门问题</h2>
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

  <?php if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) : ?>
  <section class="bkh-topics">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">专题入口</h2>
      <div class="bkh-topic-grid">
        <?php foreach ( $cats as $c ) :
          $url = bkh_category_url( $c->slug );
        ?>
          <a class="bkh-topic-card" href="<?php echo esc_url( $url ); ?>">
            <h3 class="bkh-topic-name"><?php echo esc_html( $c->name ); ?></h3>
            <p class="bkh-topic-desc"><?php echo esc_html( $c->description ?: '探索 ' . $c->name . ' 相关知识与建议。' ); ?></p>
            <span class="bkh-topic-link">进入专题 →</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $articles->have_posts() ) : ?>
  <section class="bkh-recommended">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">推荐内容</h2>
      <div class="bkh-article-grid">
        <?php while ( $articles->have_posts() ) : $articles->the_post();
          $terms = wp_get_object_terms( get_the_ID(), 'knowledge_category' );
          $cat_label = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
          $thumb = get_the_post_thumbnail_url( null, 'medium' );
        ?>
          <article class="bkh-art-card">
            <?php if ( $thumb ) : ?>
              <a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a>
            <?php endif; ?>
            <div class="bkh-art-body">
              <?php if ( $cat_label ) : ?><span class="bkh-art-tag"><?php echo esc_html( $cat_label ); ?></span><?php endif; ?>
              <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
            </div>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="bkh-bottom-nav">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title bkh-section-title-sm">浏览全部专题</h2>
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
