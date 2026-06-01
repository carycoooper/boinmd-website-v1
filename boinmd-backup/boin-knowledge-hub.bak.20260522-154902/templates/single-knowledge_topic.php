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
$is_tinnitus = ( $cat_slug === 'tinnitus' );

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

$tinnitus_scenes = array(
    array( 'title' => '晚上耳鸣特别明显', 'slug' => 'night-tinnitus' ),
    array( 'title' => '安静时耳鸣更明显', 'slug' => 'quiet-room-tinnitus' ),
    array( 'title' => '一只耳朵耳鸣', 'slug' => 'one-ear-ringing' ),
    array( 'title' => '耳朵像蝉叫', 'slug' => 'cicada-sound' ),
    array( 'title' => '耳朵嗡嗡响', 'slug' => 'buzzing-sound' ),
    array( 'title' => '耳鸣影响睡眠', 'slug' => 'sleep' ),
);

$tinnitus_questions = array(
    array( 'q' => '耳鸣越来越严重怎么办？', 'a' => '耳鸣感受变化可能与作息、压力、噪音暴露和听力状态有关。建议先记录出现时间和场景，并结合听力测试进一步判断。', 'slug' => 'worsening-tinnitus' ),
    array( 'q' => '晚上耳鸣特别明显是怎么回事？', 'a' => '夜晚环境更安静时，外界声音减少，耳鸣更容易被注意到。部分人也会在疲劳和睡眠不足时感受更明显。', 'slug' => 'night-tinnitus' ),
    array( 'q' => '一只耳朵耳鸣正常吗？', 'a' => '单侧耳鸣并不少见，但持续存在或伴随听不清、眩晕等情况时，建议尽早做专业评估。', 'slug' => 'one-ear-ringing' ),
    array( 'q' => '耳鸣和听力下降有关吗？', 'a' => '不少耳鸣人群同时存在不同程度听力下降。耳鸣不等于一定听损，但两者可能同时影响沟通与生活质量。', 'slug' => 'hearing-loss' ),
    array( 'q' => '耳鸣会自己好吗？', 'a' => '部分人群在调整作息、减少噪音刺激后可能会减轻，但变化因人而异，持续耳鸣建议结合听力评估判断。', 'slug' => 'self-relief' ),
    array( 'q' => '戴助听器对耳鸣有帮助吗？', 'a' => '对部分伴随听力下降的人群，合适的声音放大和干预可能有助于减轻耳鸣困扰，需结合个体情况评估。', 'slug' => 'hearing-aid-help' ),
);

$products_url = bkh_url( '/products/' );
?>

<main class="bkh-page bkh-topic">
  <section class="bkh-hero bkh-hero-topic" <?php if ( $hero_img ) printf( 'style="background-image:linear-gradient(180deg,rgba(255,255,255,.85) 0%%,rgba(255,255,255,.95) 100%%),url(%s);background-size:cover;background-position:center"', esc_url( $hero_img ) ); ?>>
    <div class="bkh-wrap">
      <nav class="bkh-crumbs">
        <a href="<?php echo esc_url( bkh_url( '/' ) ); ?>">首页</a> ·
        <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">听力知识中心</a> ·
        <span><?php echo esc_html( $is_tinnitus ? '耳鸣专题' : get_the_title() ); ?></span>
      </nav>
      <?php if ( $is_tinnitus ) : ?>
        <h1 class="bkh-hero-title">耳鸣专题</h1>
        <p class="bkh-hero-sub">了解耳鸣常见场景、可能原因、改善建议，以及它与听力下降之间的关系。</p>
        <p class="bkh-hero-summary">耳鸣常被描述为嗡嗡声、蝉鸣声、电流声或滋滋声。它可能和疲劳、噪音暴露、听力下降等多种因素有关。具体情况因人而异，建议结合听力测试或专业建议判断。</p>
        <div class="bkh-hero-actions">
          <a class="bkh-btn bkh-btn-primary" href="#tinnitus-questions">查看常见耳鸣问题</a>
          <a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">了解听力评估</a>
        </div>
      <?php else : ?>
        <h1 class="bkh-hero-title"><?php the_title(); ?></h1>
        <?php if ( $sub ) : ?><p class="bkh-hero-sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
        <?php if ( has_excerpt() ) : ?><p class="bkh-hero-summary"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
      <?php endif; ?>
    </div>
  </section>

  <?php if ( ! empty( $cats ) ) : ?>
  <section class="bkh-tabs"><div class="bkh-wrap"><nav class="bkh-tab-nav" aria-label="知识分类"><a class="bkh-tab" href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">全部</a><?php foreach ( $cats as $c ) : ?><a class="bkh-tab <?php echo $c->slug === $cat_slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( bkh_category_url( $c->slug ) ); ?>"><?php echo esc_html( $cat_labels[ $c->slug ] ?? $c->name ); ?></a><?php endforeach; ?></nav></div></section>
  <?php endif; ?>

  <?php if ( $is_tinnitus ) : ?>
  <section class="bkh-block bkh-scenes"><div class="bkh-wrap"><h2 class="bkh-section-title">你是哪一种耳鸣情况？</h2><div class="bkh-scene-grid bkh-scene-grid-2col"><?php foreach ( $tinnitus_scenes as $s ) : ?><a class="bkh-scene-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback( $s['slug'], '/knowledge/tinnitus/' ) ); ?>"><h3 class="bkh-scene-title"><?php echo esc_html( $s['title'] ); ?></h3></a><?php endforeach; ?></div></div></section>

  <section id="tinnitus-questions" class="bkh-block bkh-tinnitus-questions"><div class="bkh-wrap"><h2 class="bkh-section-title">耳鸣常见问题</h2><div class="bkh-hot-grid"><?php foreach ( $tinnitus_questions as $q ) : ?><a class="bkh-hot-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback( $q['slug'], '/knowledge/tinnitus/' ) ); ?>"><span class="bkh-hot-q"><?php echo esc_html( $q['q'] ); ?></span><span class="bkh-hot-a"><?php echo esc_html( $q['a'] ); ?></span><span class="bkh-hot-arrow" aria-hidden="true">→</span></a><?php endforeach; ?></div></div></section>

  <section class="bkh-block bkh-tinnitus-factors"><div class="bkh-wrap"><h2 class="bkh-section-title">耳鸣可能与这些因素有关</h2><div class="bkh-topic-grid"><article class="bkh-topic-card"><h3 class="bkh-topic-name">听力下降相关</h3><p class="bkh-topic-desc">当外界声音输入减少时，大脑可能会对声音信号变得更敏感。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">长期噪音暴露</h3><p class="bkh-topic-desc">长时间处于高噪音环境，可能增加听觉系统负担。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">疲劳与睡眠不足</h3><p class="bkh-topic-desc">熬夜、压力大、睡眠差时，部分人会感觉耳鸣更明显。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">安静环境放大感受</h3><p class="bkh-topic-desc">夜晚或安静房间里，外界声音减少，耳鸣更容易被注意到。</p></article></div></div></section>

  <section class="bkh-block bkh-tinnitus-relation"><div class="bkh-wrap bkh-wrap-text"><h2 class="bkh-section-title">耳鸣和听力下降有什么关系？</h2><p class="bkh-hero-sub">不少耳鸣人群同时存在不同程度的听力下降。耳鸣不等于一定听损，但如果伴随听不清说话、电视声音越开越大、嘈杂环境交流困难，建议尽早做一次听力评估。</p><div class="bkh-compare-card"><p><strong>耳鸣：</strong>听到不存在的声音感受</p><p><strong>听力下降：</strong>对外界声音接收变弱</p><p><strong>共同点：</strong>都可能影响沟通、睡眠和情绪</p><p><strong>建议：</strong>结合听力测试判断具体情况</p></div><p><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">了解听力下降</a></p></div></section>

  <section class="bkh-block bkh-tinnitus-tips"><div class="bkh-wrap"><h2 class="bkh-section-title">耳鸣明显时，可以先尝试这些方式</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">避免长时间完全安静</div><div class="bkh-tip-card">减少强噪音刺激</div><div class="bkh-tip-card">保持规律作息</div><div class="bkh-tip-card">记录耳鸣出现时间和场景</div><div class="bkh-tip-card">关注是否伴随听力下降</div><div class="bkh-tip-card">必要时进行听力测试</div></div><p class="bkh-disclaimer">以上内容仅作科普参考，不替代专业诊断或治疗建议。若耳鸣持续存在或明显影响生活，建议咨询专业人员。</p></div></section>
  <?php endif; ?>

  <?php if ( ! empty( $faqs ) ) : bkh_render_faq_block( $faqs ); endif; ?>

  <?php if ( $is_tinnitus ) : $faq_entities = array(); foreach ( $tinnitus_questions as $f ) { $faq_entities[] = array('@type' => 'Question','name' => wp_strip_all_tags( $f['q'] ),'acceptedAnswer' => array('@type' => 'Answer','text' => wp_strip_all_tags( $f['a'] ),),); } if ( ! empty( $faq_entities ) ) : ?><script type="application/ld+json"><?php echo wp_json_encode( array('@context' => 'https://schema.org','@type' => 'FAQPage','mainEntity' => $faq_entities,), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script><?php endif; endif; ?>

  <?php bkh_render_video_block( $post_id ); ?>

  <?php if ( $cat_slug ) { $related = new WP_Query( array('post_type' => 'knowledge_article','posts_per_page' => 6,'meta_key' => '_bkh_featured_priority','orderby' => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),'tax_query' => array( array( 'taxonomy' => 'knowledge_category', 'field' => 'slug', 'terms' => $cat_slug ) ),) ); ?><section class="bkh-block bkh-related"><div class="bkh-wrap"><h2 class="bkh-section-title"><?php echo esc_html( $is_tinnitus ? '更多耳鸣相关内容' : '本专题相关文章' ); ?></h2><?php if ( $related->have_posts() ) : ?><div class="bkh-article-grid"><?php while ( $related->have_posts() ) : $related->the_post(); $thumb = get_the_post_thumbnail_url( null, 'medium' ); ?><article class="bkh-art-card"><?php if ( $thumb ) : ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a><?php endif; ?><div class="bkh-art-body"><h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p></div></article><?php endwhile; wp_reset_postdata(); ?></div><?php else : ?><p class="bkh-empty">更多耳鸣相关内容正在整理中。</p><?php endif; ?></div></section><?php } ?>

  <?php if ( $is_tinnitus ) : ?><section class="bkh-bottom-nav"><div class="bkh-wrap"><h2 class="bkh-section-title bkh-section-title-sm">继续了解相关听力问题</h2><ul class="bkh-bottom-list"><li><a href="<?php echo esc_url( bkh_url( '/knowledge/tinnitus/' ) ); ?>">耳鸣专题</a></li><li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">听力下降</a></li><li><a href="<?php echo esc_url( bkh_url( '/knowledge/hearing-aids/' ) ); ?>">助听器百科</a></li><li><a href="<?php echo esc_url( bkh_url( '/knowledge/ai-hearing/' ) ); ?>">AI智能助听</a></li></ul></div></section><section class="bkh-cta bkh-cta-soft"><div class="bkh-wrap"><div class="bkh-cta-inner"><div class="bkh-cta-text"><h2 class="bkh-cta-title">如果耳鸣伴随听不清，建议先了解听力情况</h2><p class="bkh-cta-desc">耳鸣和听力下降有时会同时出现。通过听力评估，可以更清楚地了解自己的听力状态，再判断是否需要进一步干预或助听方案。</p></div><div class="bkh-cta-actions"><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url( '/knowledge/hearing-loss/' ) ); ?>">了解听力评估</a><a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( $products_url ); ?>">查看助听器方案</a></div></div></div></section><?php else : ?><?php bkh_render_cta_block( $post_id ); ?><?php endif; ?>
</main>

<?php get_footer();
