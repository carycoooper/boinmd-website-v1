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
$faqs     = bkh_get_faqs( $post_id );
$terms    = wp_get_object_terms( $post_id, 'knowledge_category' );
$cat_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '';
$is_tinnitus = ( $cat_slug === 'tinnitus' );
$is_hearing_loss = ( $cat_slug === 'hearing-loss' );

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
    foreach ( $cats_raw as $c ) $cats_map[ $c->slug ] = $c;
}
$cats = array();
foreach ( $cat_order as $slug ) if ( isset( $cats_map[ $slug ] ) ) $cats[] = $cats_map[ $slug ];

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
    array( 'q' => '耳鸣和听力下降有关吗？', 'a' => '部分人可能同时出现耳鸣和听力下降，但二者并不完全等同。若同时影响生活，建议进行听力评估。', 'slug' => 'hearing-loss' ),
    array( 'q' => '耳鸣会自己好吗？', 'a' => '部分人群在调整作息、减少噪音刺激后可能会减轻，但变化因人而异，持续耳鸣建议结合听力评估判断。', 'slug' => 'self-relief' ),
    array( 'q' => '戴助听器对耳鸣有帮助吗？', 'a' => '对部分伴随听力下降的人群，合适的声音放大和干预可能有助于减轻耳鸣困扰，需结合个体情况评估。', 'slug' => 'hearing-aid-help' ),
);

$hearing_signs = array(
    array('title'=>'经常听不清别人说话','slug'=>'cannot-hear-speech'),
    array('title'=>'电视声音越开越大','slug'=>'tv-volume-louder'),
    array('title'=>'人多时听不清','slug'=>'noisy-environment'),
    array('title'=>'总觉得别人说话含糊','slug'=>'muffled-speech'),
    array('title'=>'经常让别人重复一遍','slug'=>'ask-repeat'),
    array('title'=>'电话里听不清声音','slug'=>'phone-call'),
);
$hearing_questions = array(
    array('q'=>'老人听不清别人说话怎么办？','a'=>'老人听不清可能与年龄相关听力变化、环境噪音或沟通习惯有关。建议先观察具体场景，必要时做听力评估。','slug'=>'elder-cannot-hear-speech'),
    array('q'=>'听力下降可以恢复吗？','a'=>'听力下降的原因不同，结果也不同。不要自行判断，建议结合听力测试或专业建议了解具体情况。','slug'=>'can-hearing-loss-recover'),
    array('q'=>'为什么在人多的地方更听不清？','a'=>'嘈杂环境中背景声较多，大脑需要分辨更多声音信息，听力下降人群往往会感觉交流更困难。','slug'=>'why-noisy-place-hard-to-hear'),
    array('q'=>'电视声音越开越大是听力下降吗？','a'=>'电视声音变大可能是听力变化的表现之一，也可能和环境、音源有关。如果频繁出现，建议关注听力情况。','slug'=>'tv-volume-sign'),
    array('q'=>'听力下降一定要戴助听器吗？','a'=>'是否需要助听器要结合听力测试、生活影响和专业建议判断，不同人适配方案可能不同。','slug'=>'must-wear-hearing-aid'),
    array('q'=>'听力下降和耳鸣有关吗？','a'=>'部分人可能同时出现耳鸣和听力下降，但二者并不完全等同。若同时影响生活，建议进行听力评估。','slug'=>'hearing-loss-and-tinnitus'),
);

$products_url = bkh_url( '/products/' );

$schema_faq = array();
if ( $is_tinnitus ) $schema_faq = $tinnitus_questions;
if ( $is_hearing_loss ) $schema_faq = $hearing_questions;
?>

<main class="bkh-page bkh-topic">
  <section class="bkh-hero bkh-hero-topic" <?php if ( $hero_img ) printf( 'style="background-image:linear-gradient(180deg,rgba(255,255,255,.85) 0%%,rgba(255,255,255,.95) 100%%),url(%s);background-size:cover;background-position:center"', esc_url( $hero_img ) ); ?>><div class="bkh-wrap">
    <nav class="bkh-crumbs"><a href="<?php echo esc_url( bkh_url('/') ); ?>">首页</a> · <a href="<?php echo esc_url( bkh_url('/knowledge/') ); ?>">听力知识中心</a> · <span><?php echo esc_html( $is_tinnitus ? '耳鸣专题' : ( $is_hearing_loss ? '听力下降专题' : get_the_title() ) ); ?></span></nav>
    <?php if ( $is_tinnitus ) : ?>
      <h1 class="bkh-hero-title">耳鸣专题</h1><p class="bkh-hero-sub">了解耳鸣常见场景、可能原因、改善建议，以及它与听力下降之间的关系。</p><p class="bkh-hero-summary">耳鸣常被描述为嗡嗡声、蝉鸣声、电流声或滋滋声。它可能和疲劳、噪音暴露、听力下降等多种因素有关。具体情况因人而异，建议结合听力测试或专业建议判断。</p>
      <div class="bkh-hero-actions"><a class="bkh-btn bkh-btn-primary" href="#tinnitus-questions">查看常见耳鸣问题</a><a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">了解听力评估</a></div>
    <?php elseif ( $is_hearing_loss ) : ?>
      <h1 class="bkh-hero-title">听力下降专题</h1><p class="bkh-hero-sub">了解听力下降的常见表现、可能原因、日常影响，以及什么时候需要做听力评估。</p><p class="bkh-hero-summary">听力下降并不总是突然发生。很多人最早表现为听不清别人说话、电视声音越开越大、在人多或嘈杂环境中交流困难。具体情况因人而异，建议结合听力测试或专业建议判断。</p>
      <div class="bkh-hero-actions"><a class="bkh-btn bkh-btn-primary" href="#hearing-loss-signs">查看常见表现</a><a class="bkh-btn bkh-btn-ghost" href="#hearing-assessment">了解听力评估</a></div>
    <?php else : ?>
      <h1 class="bkh-hero-title"><?php the_title(); ?></h1><?php if ( $sub ) : ?><p class="bkh-hero-sub"><?php echo esc_html($sub); ?></p><?php endif; ?><?php if ( has_excerpt() ) : ?><p class="bkh-hero-summary"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
    <?php endif; ?>
  </div></section>

  <section class="bkh-tabs"><div class="bkh-wrap"><nav class="bkh-tab-nav" aria-label="知识分类"><a class="bkh-tab" href="<?php echo esc_url( bkh_url('/knowledge/') ); ?>">全部</a><?php foreach($cats as $c): ?><a class="bkh-tab <?php echo $c->slug===$cat_slug?'is-active':''; ?>" href="<?php echo esc_url( bkh_category_url($c->slug) ); ?>"><?php echo esc_html($cat_labels[$c->slug] ?? $c->name); ?></a><?php endforeach; ?></nav></div></section>

  <?php if ( $is_tinnitus ) : ?>
    <section class="bkh-block bkh-scenes"><div class="bkh-wrap"><h2 class="bkh-section-title">你是哪一种耳鸣情况？</h2><div class="bkh-scene-grid bkh-scene-grid-2col"><?php foreach($tinnitus_scenes as $s): ?><a class="bkh-scene-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($s['slug'],'/knowledge/tinnitus/') ); ?>"><h3 class="bkh-scene-title"><?php echo esc_html($s['title']); ?></h3></a><?php endforeach; ?></div></div></section>
    <section id="tinnitus-questions" class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">耳鸣常见问题</h2><div class="bkh-hot-grid"><?php foreach($tinnitus_questions as $q): ?><a class="bkh-hot-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($q['slug'],'/knowledge/tinnitus/') ); ?>"><span class="bkh-hot-q"><?php echo esc_html($q['q']); ?></span><span class="bkh-hot-a"><?php echo esc_html($q['a']); ?></span><span class="bkh-hot-arrow" aria-hidden="true">→</span></a><?php endforeach; ?></div></div></section>
  <?php endif; ?>

  <?php if ( $is_hearing_loss ) : ?>
    <section id="hearing-loss-signs" class="bkh-block bkh-scenes"><div class="bkh-wrap"><h2 class="bkh-section-title">这些表现，可能提示听力正在下降</h2><div class="bkh-scene-grid bkh-scene-grid-2col"><?php foreach($hearing_signs as $s): ?><a class="bkh-scene-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($s['slug'],'/knowledge/hearing-loss/') ); ?>"><h3 class="bkh-scene-title"><?php echo esc_html($s['title']); ?></h3></a><?php endforeach; ?></div></div></section>
    <section id="hearing-loss-questions" class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">听力下降常见问题</h2><div class="bkh-hot-grid"><?php foreach($hearing_questions as $q): ?><a class="bkh-hot-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($q['slug'],'/knowledge/hearing-loss/') ); ?>"><span class="bkh-hot-q"><?php echo esc_html($q['q']); ?></span><span class="bkh-hot-a"><?php echo esc_html($q['a']); ?></span><span class="bkh-hot-arrow" aria-hidden="true">→</span></a><?php endforeach; ?></div></div></section>
    <section class="bkh-block bkh-tinnitus-factors"><div class="bkh-wrap"><h2 class="bkh-section-title">听力下降可能与这些因素有关</h2><div class="bkh-topic-grid"><article class="bkh-topic-card"><h3 class="bkh-topic-name">年龄相关变化</h3><p class="bkh-topic-desc">随着年龄增长，部分人会逐渐出现听声音不清、分辨语音困难等情况。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">长期噪音暴露</h3><p class="bkh-topic-desc">长期处于高噪音环境，可能增加听觉系统负担。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">耳部或身体因素</h3><p class="bkh-topic-desc">部分耳部问题、慢性疾病或用药因素，也可能影响听力状态。</p></article><article class="bkh-topic-card"><h3 class="bkh-topic-name">未及时关注</h3><p class="bkh-topic-desc">早期听力变化容易被忽视，等到沟通明显受影响时才被发现。</p></article></div></div></section>
    <section class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">听力下降，常常先影响日常沟通</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">家人说话总听不清</div><div class="bkh-tip-card">看电视需要更大音量</div><div class="bkh-tip-card">买菜、逛超市交流费劲</div><div class="bkh-tip-card">接电话容易漏听信息</div><div class="bkh-tip-card">和朋友聊天跟不上</div><div class="bkh-tip-card">带孙子时听不清孩子说话</div></div><p class="bkh-disclaimer">听力下降不仅影响“听见声音”，也可能影响沟通效率、家庭交流和情绪状态。越早了解听力情况，越容易选择合适的应对方式。</p></div></section>
    <section id="hearing-assessment" class="bkh-block"><div class="bkh-wrap bkh-wrap-text"><h2 class="bkh-section-title">什么时候建议做一次听力评估？</h2><p class="bkh-hero-sub">如果听不清的情况持续存在，或者已经影响到家庭交流、电话沟通、看电视、外出办事等日常场景，建议尽早做一次听力评估。听力测试可以帮助了解听力状态，为后续是否需要助听方案提供参考。</p><div class="bkh-tip-grid"><div class="bkh-tip-card">经常听不清别人说话</div><div class="bkh-tip-card">需要别人重复</div><div class="bkh-tip-card">嘈杂环境交流困难</div><div class="bkh-tip-card">电视音量明显变大</div><div class="bkh-tip-card">伴随耳鸣或耳闷</div><div class="bkh-tip-card">家人明显感觉沟通变困难</div></div><p><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">了解助听器百科</a></p></div></section>
    <section class="bkh-block"><div class="bkh-wrap bkh-wrap-text"><h2 class="bkh-section-title">听力下降后，一定要马上买助听器吗？</h2><p class="bkh-hero-sub">不一定。是否需要助听器，要结合听力测试结果、日常沟通影响和个人需求判断。对于部分轻中度听力下降人群，合适的助听方案可能有助于改善日常交流体验，但具体适配效果因人而异。</p><div class="bkh-compare-card"><p><strong>听力评估：</strong>了解听力状态</p><p><strong>沟通场景：</strong>判断生活影响</p><p><strong>助听方案：</strong>结合需求选择</p><p><strong>专业建议：</strong>帮助判断是否适合</p></div><p><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">查看助听器选购指南</a> <a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url($products_url); ?>">查看助听器方案</a></p></div></section>
  <?php endif; ?>

  <?php if ( ! empty( $faqs ) && ! $is_tinnitus && ! $is_hearing_loss ) : bkh_render_faq_block( $faqs ); endif; ?>

  <?php if ( ! empty( $schema_faq ) ) : $faq_entities = array(); foreach ( $schema_faq as $f ) $faq_entities[] = array('@type'=>'Question','name'=>wp_strip_all_tags($f['q']),'acceptedAnswer'=>array('@type'=>'Answer','text'=>wp_strip_all_tags($f['a']))); ?><script type="application/ld+json"><?php echo wp_json_encode(array('@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faq_entities), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script><?php endif; ?>

  <?php bkh_render_video_block( $post_id ); ?>

  <?php if ( $cat_slug ) { $related = new WP_Query( array('post_type'=>'knowledge_article','posts_per_page'=>6,'meta_key'=>'_bkh_featured_priority','orderby'=>array('meta_value_num'=>'DESC','date'=>'DESC'),'tax_query'=>array(array('taxonomy'=>'knowledge_category','field'=>'slug','terms'=>$cat_slug)),) ); ?><section class="bkh-block bkh-related"><div class="bkh-wrap"><h2 class="bkh-section-title"><?php echo esc_html( $is_tinnitus ? '更多耳鸣相关内容' : ( $is_hearing_loss ? '更多听力下降相关内容' : '本专题相关文章' ) ); ?></h2><?php if ( $related->have_posts() ) : ?><div class="bkh-article-grid"><?php while ( $related->have_posts() ) : $related->the_post(); $thumb = get_the_post_thumbnail_url(null,'medium'); ?><article class="bkh-art-card"><?php if($thumb): ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy"></a><?php endif; ?><div class="bkh-art-body"><h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags(get_the_excerpt()) ); ?></p></div></article><?php endwhile; wp_reset_postdata(); ?></div><?php else: ?><p class="bkh-empty"><?php echo esc_html( $is_tinnitus ? '更多耳鸣相关内容正在整理中。' : '更多听力下降相关内容正在整理中。' ); ?></p><?php endif; ?></div></section><?php } ?>

  <?php if ( $is_tinnitus || $is_hearing_loss ) : ?>
  <section class="bkh-bottom-nav"><div class="bkh-wrap"><h2 class="bkh-section-title bkh-section-title-sm">继续了解相关听力问题</h2><ul class="bkh-bottom-list"><li><a href="<?php echo esc_url( bkh_url('/knowledge/tinnitus/') ); ?>">耳鸣专题</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">听力下降</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">助听器百科</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/ai-hearing/') ); ?>">AI智能助听</a></li></ul></div></section>
  <section class="bkh-cta bkh-cta-soft"><div class="bkh-wrap"><div class="bkh-cta-inner"><div class="bkh-cta-text"><h2 class="bkh-cta-title"><?php echo esc_html( $is_tinnitus ? '如果耳鸣伴随听不清，建议先了解听力情况' : '想了解父母是否适合助听方案？' ); ?></h2><p class="bkh-cta-desc"><?php echo esc_html( $is_tinnitus ? '耳鸣和听力下降有时会同时出现。通过听力评估，可以更清楚地了解自己的听力状态，再判断是否需要进一步干预或助听方案。' : '如果父母经常听不清、看电视声音变大，建议先了解听力下降的常见表现，再结合听力测试或专业建议判断是否需要助听方案。' ); ?></p></div><div class="bkh-cta-actions"><?php if($is_tinnitus): ?><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">了解听力评估</a><?php else: ?><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">查看助听器百科</a><?php endif; ?><a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url($products_url); ?>">查看助听器方案</a></div></div></div></section>
  <?php else : ?>
    <?php bkh_render_cta_block( $post_id ); ?>
  <?php endif; ?>
</main>

<?php get_footer();
