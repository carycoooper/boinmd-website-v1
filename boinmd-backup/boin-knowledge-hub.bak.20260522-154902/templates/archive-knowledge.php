<?php
/**
 * Knowledge Hub landing page (/knowledge/)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$settings = get_option( BKH_OPT_SETTINGS, bkh_default_settings() );
$hero_title    = $settings['hero_title'] ?? '鍚姏鐭ヨ瘑涓績';
$hero_subtitle = $settings['hero_subtitle'] ?? '';
$hero_ph       = $settings['hero_placeholder'] ?? '渚嬪锛氭櫄涓婅€抽福鐗瑰埆鏄庢樉鎬庝箞鍔烇紵';
$hot           = $settings['hot_questions'] ?? array();

$tab_order = array( 'tinnitus', 'hearing-loss', 'hearing-aids', 'ai-hearing', 'care', 'fitting', 'stories' );
$tab_labels = array(
    'tinnitus'     => '鑰抽福涓撻',
    'hearing-loss' => '鍚姏涓嬮檷',
    'hearing-aids' => '鍔╁惉鍣ㄧ櫨绉?,
    'ai-hearing'   => 'AI鏅鸿兘鍔╁惉',
    'care'         => '浣跨敤涓庝繚鍏?,
    'fitting'      => '楠岄厤鎸囧崡',
    'stories'      => '鐢ㄦ埛妗堜緥',
);

$core_topics = array(
    array(
        'slug' => 'tinnitus',
        'title' => '鑰抽福涓撻',
        'desc' => '浜嗚В鑰抽福甯歌鍦烘櫙銆佸彲鑳藉師鍥犱笌鏀瑰杽寤鸿',
    ),
    array(
        'slug' => 'hearing-loss',
        'title' => '鍚姏涓嬮檷',
        'desc' => '璇嗗埆鍚姏涓嬮檷鐨勬棭鏈熻〃鐜颁笌鏃ュ父褰卞搷',
    ),
    array(
        'slug' => 'hearing-aids',
        'title' => '鍔╁惉鍣ㄧ櫨绉?,
        'desc' => '浜嗚В鍔╁惉鍣ㄩ€夎喘銆佷僵鎴淬€佷娇鐢ㄨ鍖轰笌甯歌闂',
    ),
    array(
        'slug' => 'ai-hearing',
        'title' => 'AI鏅鸿兘鍔╁惉',
        'desc' => '浜嗚В AI 闄嶅櫔銆佸満鏅瘑鍒笌鏅鸿兘鍔╁惉浣撻獙',
    ),
);

$hot_fallback = array(
    array( 'label' => '鑰抽福瓒婃潵瓒婁弗閲嶆€庝箞鍔烇紵', 'url' => '/knowledge/tinnitus/' ),
    array( 'label' => '鏅氫笂鑰抽福鐗瑰埆鏄庢樉鎬庝箞鍔烇紵', 'url' => '/knowledge/tinnitus/' ),
    array( 'label' => '鑰佷汉鍚笉娓呭埆浜鸿璇濇€庝箞鍔烇紵', 'url' => '/knowledge/hearing-loss/' ),
    array( 'label' => '鍔╁惉鍣ㄤ細瓒婃埓瓒婅亱鍚楋紵', 'url' => '/knowledge/hearing-aids/' ),
    array( 'label' => 'AI鍔╁惉鍣ㄧ湡鐨勬湁鐢ㄥ悧锛?, 'url' => '/knowledge/ai-hearing/' ),
    array( 'label' => '绗竴娆＄粰鐖舵瘝涔板姪鍚櫒鎬庝箞閫夛紵', 'url' => '/knowledge/hearing-aids/' ),
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

$articles = new WP_Query( array(
    'post_type'      => 'knowledge_article',
    'posts_per_page' => 16,
    'meta_key'       => '_bkh_featured_priority',
    'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
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
        <input type="search" name="s" placeholder="<?php echo esc_attr( $hero_ph ); ?>" aria-label="鎼滅储鍚姏鐭ヨ瘑">
        <input type="hidden" name="post_type" value="knowledge_article">
        <button type="submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          鎼滅储
        </button>
      </form>
    </div>
  </section>

  <section class="bkh-tabs">
    <div class="bkh-wrap">
      <nav class="bkh-tab-nav" aria-label="鐭ヨ瘑鍒嗙被">
        <a class="bkh-tab is-active" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">鍏ㄩ儴</a>
        <?php foreach ( $tab_order as $slug ) : ?>
          <a class="bkh-tab" href="<?php echo esc_url( bkh_url( '/knowledge/' . $slug . '/' ) ); ?>"><?php echo esc_html( $tab_labels[ $slug ] ); ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>

  <section class="bkh-hot">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">鐑棬闂鍏ュ彛</h2>
      <div class="bkh-hot-grid">
        <?php foreach ( $merged_hot as $q ) :
          $url = $q['url'] ?: '/knowledge/';
          if ( strpos( $url, 'http' ) !== 0 && substr( $url, 0, 1 ) !== '/' ) $url = '/' . $url;
        ?>
          <a class="bkh-hot-card" href="<?php echo esc_url( bkh_url( $url ) ); ?>">
            <span class="bkh-hot-q"><?php echo esc_html( $q['label'] ); ?></span>
            <span class="bkh-hot-arrow" aria-hidden="true">鈫?/span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="bkh-topics">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">涓撻鍏ュ彛鍖?/h2>
      <div class="bkh-topic-grid">
        <?php foreach ( $core_topics as $topic ) : ?>
          <a class="bkh-topic-card" href="<?php echo esc_url( bkh_url( '/knowledge/' . $topic['slug'] . '/' ) ); ?>">
            <h3 class="bkh-topic-name"><?php echo esc_html( $topic['title'] ); ?></h3>
            <p class="bkh-topic-desc"><?php echo esc_html( $topic['desc'] ); ?></p>
            <span class="bkh-topic-link">杩涘叆涓撻 鈫?/span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ( $trending->have_posts() ) : ?>
  <section class="bkh-trending">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">鐑棬鏂囩珷</h2>
      <div class="bkh-trending-list">
        <?php while ( $trending->have_posts() ) : $trending->the_post(); ?>
          <a class="bkh-trending-item" href="<?php the_permalink(); ?>">
            <h3 class="bkh-trending-title"><?php the_title(); ?></h3>
            <span class="bkh-trending-cta">鏌ョ湅瑙ｅ喅鏂规 鈫?/span>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $articles->have_posts() ) : ?>
  <section class="bkh-recommended">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title">鎺ㄨ崘闃呰</h2>
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
                <span class="bkh-art-meta"><?php echo esc_html( $read_minutes ); ?> 鍒嗛挓闃呰</span>
              </div>
              <a class="bkh-art-cta" href="<?php the_permalink(); ?>">闃呰鍏ㄦ枃</a>
            </div>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="bkh-cta bkh-cta-soft">
    <div class="bkh-wrap">
      <div class="bkh-cta-inner">
        <div class="bkh-cta-text">
          <h2 class="bkh-cta-title">鎯充簡瑙ｇ埗姣嶇殑鍚姏鎯呭喌锛?/h2>
          <p class="bkh-cta-desc">濡傛灉瀹朵汉缁忓父鍚笉娓呫€佺數瑙嗗０闊宠秺寮€瓒婂ぇ锛屽缓璁厛浜嗚В鍚姏涓嬮檷鐨勫父瑙佽〃鐜帮紝鍐嶇粨鍚堝惉鍔涙祴璇曟垨涓撲笟寤鸿鍒ゆ柇鏄惁闇€瑕佸姪鍚柟妗堛€?/p>
        </div>
        <div class="bkh-cta-actions">
          <a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">浜嗚В鍚姏涓嬮檷</a>
          <a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-aids/' ) ); ?>">鏌ョ湅鍔╁惉鍣ㄩ€夎喘鎸囧崡</a>
        </div>
      </div>
    </div>
  </section>

  <section class="bkh-bottom-nav">
    <div class="bkh-wrap">
      <h2 class="bkh-section-title bkh-section-title-sm">鐭ヨ瘑瀵艰埅</h2>
      <ul class="bkh-bottom-list">
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/tinnitus/' ) ); ?>">鑰抽福涓撻</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">鍚姏涓嬮檷</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-aids/' ) ); ?>">鍔╁惉鍣ㄧ櫨绉?/a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/ai-hearing/' ) ); ?>">AI鏅鸿兘鍔╁惉</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/care/' ) ); ?>">浣跨敤涓庝繚鍏?/a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/fitting/' ) ); ?>">楠岄厤鎸囧崡</a></li>
        <li><a href="<?php echo esc_url( bkh_url( '/knowledge/stories/' ) ); ?>">鐢ㄦ埛妗堜緥</a></li>
      </ul>
    </div>
  </section>

</main>

<?php get_footer();

