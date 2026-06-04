<?php
/**
 * Knowledge Hub landing page (/knowledge/)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
$hero_title    = $settings['hero_title'] ?? '听力知识中心';
$hero_subtitle = $settings['hero_subtitle'] ?? '';
$hero_ph       = $settings['hero_placeholder'] ?? '例如：晚上耳鸣特别明显怎么办？';
$hot           = $settings['hot_questions'] ?? array();

$tab_order = array( 'tinnitus', 'hearing-loss', 'hearing-aids', 'ai-hearing', 'care', 'fitting', 'stories' );
$tab_labels = array(
    'tinnitus'     => '耳鸣专题',
    'hearing-loss' => '听力下降',
    'hearing-aids' => '助听器百科',
    'ai-hearing'   => 'AI智能助听',
    'care'         => '使用与保养',
    'fitting'      => '验配指南',
    'stories'      => '用户案例',
);

$core_topics = array(
    array(
        'slug'  => 'tinnitus',
        'title' => '耳鸣专题',
        'desc'  => '了解耳鸣常见场景、可能原因与改善建议',
    ),
    array(
        'slug'  => 'hearing-loss',
        'title' => '听力下降',
        'desc'  => '识别听力下降的早期表现与日常影响',
    ),
    array(
        'slug'  => 'hearing-aids',
        'title' => '助听器百科',
        'desc'  => '了解助听器选购、佩戴、使用误区与常见问题',
    ),
    array(
        'slug'  => 'ai-hearing',
        'title' => 'AI智能助听',
        'desc'  => '了解 AI 降噪、场景识别与智能助听体验',
    ),
);

$hot_fallback = array(
    array( 'label' => '耳鸣越来越严重怎么办？', 'url' => '/knowledge/tinnitus/' ),
    array( 'label' => '晚上耳鸣特别明显怎么办？', 'url' => '/knowledge/tinnitus/' ),
    array( 'label' => '老人听不清别人说话怎么办？', 'url' => '/knowledge/hearing-loss/' ),
    array( 'label' => '助听器会越戴越聋吗？', 'url' => '/knowledge/hearing-aids/' ),
    array( 'label' => 'AI助听器真的有用吗？', 'url' => '/knowledge/ai-hearing/' ),
    array( 'label' => '第一次给父母买助听器怎么选？', 'url' => '/knowledge/hearing-aids/' ),
);
$merged_hot = array();
foreach ( $hot as $row ) {
    if ( empty( $row['label'] ) ) continue;
    $merged_hot[] = $row;
}
if ( count( $merged_hot ) < 6 ) {
    foreach ( $hot_fallback as $fb ) {
        $exists = false;
        foreach ( $merged_hot as $m ) {
            if ( $m['label'] === $fb['label'] ) { $exists = true; break; }
        }
        if ( ! $exists ) $merged_hot[] = $fb;
        if ( count( $merged_hot ) >= 6 ) break;
    }
}
$merged_hot = array_slice( $merged_hot, 0, 6 );

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
    'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
) );

$article_page = isset( $_GET['kpage'] ) ? max( 1, (int) $_GET['kpage'] ) : 1;
$article_per_page = 6;
$articles = new WP_Query( array(
    'post_type'      => 'knowledge_article',
    'posts_per_page' => $article_per_page,
    'paged'          => $article_page,
    'meta_key'       => '_bkh_featured_priority',
    'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
) );
$article_total_pages = max( 1, (int) $articles->max_num_pages );

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
        <input type="hidden" name="post_type" value="knowledge_article">
        <button type="submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          搜索
        </button>
      </form>
    </div>
  </section>

  <section class="bkh-tabs">
    <div class="bkh-wrap">
      <nav class="bkh-tab-nav" aria-label="知识分类">
        <a class="bkh-tab is-active" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">全部</a>
        <?php foreach ( $tab_order as $slug ) : ?>
          <a class="bkh-tab" href="<?php echo esc_url( bkh_url( '/knowledge/' . $slug . '/' ) ); ?>"><?php echo esc_html( $tab_labels[ $slug ] ); ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>

  <section class="bkh-hot">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">热门问题入口</h2>
      <div class="bkh-hot-grid">
        <?php foreach ( $merged_hot as $q ) :
          $url = $q['url'] ?: '/knowledge/';
          if ( strpos( $url, 'http' ) !== 0 && substr( $url, 0, 1 ) !== '/' ) $url = '/' . $url;
        ?>
          <a class="bkh-hot-card" href="<?php echo esc_url( bkh_url( $url ) ); ?>">
            <span class="bkh-hot-q"><?php echo esc_html( $q['label'] ); ?></span>
            <span class="bkh-hot-arrow" aria-hidden="true">→</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="bkh-topics">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">专题入口区</h2>
      <div class="bkh-topic-grid">
        <?php foreach ( $core_topics as $topic ) : ?>
          <a class="bkh-topic-card" href="<?php echo esc_url( bkh_url( '/knowledge/' . $topic['slug'] . '/' ) ); ?>">
            <h3 class="bkh-topic-name"><?php echo esc_html( $topic['title'] ); ?></h3>
            <p class="bkh-topic-desc"><?php echo esc_html( $topic['desc'] ); ?></p>
            <span class="bkh-topic-link">进入专题 →</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

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
  <section class="bkh-recommended" id="knowledge-recommended">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">推荐阅读</h2>
      <div class="bkh-article-grid bkh-home-article-grid">
        <?php while ( $articles->have_posts() ) : $articles->the_post();
          $terms = wp_get_object_terms( get_the_ID(), 'knowledge_category' );
          $cat_label = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
          $thumb = get_the_post_thumbnail_url( null, 'full' );
          $read_minutes = bkh_get_reading_minutes( get_the_ID(), 450 );
        ?>
          <article class="bkh-art-card">
            <?php if ( $thumb ) : ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a><?php endif; ?>
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
      <?php if ( $article_total_pages > 1 ) : ?>
        <nav class="bkh-pagination" aria-label="推荐阅读分页">
          <?php if ( $article_page > 1 ) :
            $prev_url = add_query_arg( 'kpage', $article_page - 1, bkh_url( '/knowledge/' ) ) . '#knowledge-recommended';
          ?>
            <a class="bkh-page-btn" href="<?php echo esc_url( $prev_url ); ?>">上一页</a>
          <?php else : ?>
            <span class="bkh-page-btn bkh-page-btn-disabled">上一页</span>
          <?php endif; ?>
          <span class="bkh-page-count">第 <?php echo esc_html( $article_page ); ?> / <?php echo esc_html( $article_total_pages ); ?> 页</span>
          <?php if ( $article_page < $article_total_pages ) :
            $next_url = add_query_arg( 'kpage', $article_page + 1, bkh_url( '/knowledge/' ) ) . '#knowledge-recommended';
          ?>
            <a class="bkh-page-btn" href="<?php echo esc_url( $next_url ); ?>">下一页</a>
          <?php else : ?>
            <span class="bkh-page-btn bkh-page-btn-disabled">下一页</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="bkh-cta bkh-cta-soft">
    <div class="bkh-wrap">
      <div class="bkh-cta-inner">
        <div class="bkh-cta-text">
          <h2 class="bkh-cta-title">想了解父母的听力情况？</h2>
          <p class="bkh-cta-desc">如果家人经常听不清、电视声音越开越大，建议先了解听力下降的常见表现，再结合听力测试或专业建议判断是否需要助听方案。</p>
        </div>
        <div class="bkh-cta-actions">
          <a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">了解听力下降</a>
          <a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-aids/' ) ); ?>">查看助听器选购指南</a>
        </div>
      </div>
    </div>
  </section>

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

