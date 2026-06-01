<?php
/**
 * single-knowledge_topic.php - 知识专题详情
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'bkh_render_video_block' ) ) {
    require_once BKH_DIR . 'templates/partials/video-block.php';
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
$terms    = wp_get_object_terms( $post_id, 'knowledge_category' );
$cat_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '';

$is_tinnitus = ( $cat_slug === 'tinnitus' );
$is_hearing_loss = ( $cat_slug === 'hearing-loss' );
$is_hearing_aids = ( $cat_slug === 'hearing-aids' );

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
if ( ! is_wp_error( $cats_raw ) ) foreach ( $cats_raw as $c ) $cats_map[ $c->slug ] = $c;
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
    array( 'q' => '耳鸣和听力下降有关吗？', 'a' => '部分人可能同时出现耳鸣和听力下降，但二者并不完全等同。若同时影响生活，建议进行听力评估。', 'slug' => 'hearing-loss-and-tinnitus' ),
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

$aid_guides = array(
    array('title'=>'第一次给父母买助听器','slug'=>'first-hearing-aid-for-parents'),
    array('title'=>'老人适合什么助听器','slug'=>'hearing-aid-for-seniors'),
    array('title'=>'助听器单耳好还是双耳好','slug'=>'one-ear-or-two'),
    array('title'=>'助听器价格怎么选','slug'=>'price-guide'),
    array('title'=>'助听器戴着不舒服怎么办','slug'=>'uncomfortable'),
    array('title'=>'助听器和扩音器有什么区别','slug'=>'hearing-aid-vs-amplifier'),
);
$aid_questions = array(
    array('q'=>'助听器会越戴越聋吗？','a'=>'正规助听器的作用是根据听力情况进行声音补偿。是否适合佩戴、如何调试，应结合听力测试或专业建议判断。','slug'=>'hearing-aid-worse-hearing'),
    array('q'=>'助听器和普通扩音器有什么区别？','a'=>'普通扩音器通常偏向整体放大，助听器更强调结合听力情况进行处理和适配，具体体验因人而异。','slug'=>'hearing-aid-vs-amplifier'),
    array('q'=>'第一次给父母买助听器怎么选？','a'=>'建议优先关注佩戴简单、声音自然、售后便利，以及是否适合日常沟通场景，而不只看参数。','slug'=>'first-hearing-aid-for-parents'),
    array('q'=>'助听器是单耳好还是双耳好？','a'=>'单耳或双耳需结合双耳听力情况和使用场景判断，很多日常交流场景中双耳体验可能更自然。','slug'=>'one-ear-or-two'),
    array('q'=>'助听器需要验配吗？','a'=>'听力情况不同，适合的声音补偿也不同。通过听力评估或专业调试可更好判断适配方式。','slug'=>'fitting-needed'),
    array('q'=>'老人不愿意戴助听器怎么办？','a'=>'可从真实沟通场景和轻量体验切入，逐步建立使用习惯，降低心理负担。','slug'=>'senior-reluctant-to-wear'),
);

$products_url = bkh_url( '/products/' );
$schema_faq = array();
if ( $is_tinnitus ) $schema_faq = $tinnitus_questions;
if ( $is_hearing_loss ) $schema_faq = $hearing_questions;
if ( $is_hearing_aids ) $schema_faq = $aid_questions;
?>

<main class="bkh-page bkh-topic">
  <section class="bkh-hero bkh-hero-topic" <?php if ( $hero_img ) printf( 'style="background-image:linear-gradient(180deg,rgba(255,255,255,.85) 0%%,rgba(255,255,255,.95) 100%%),url(%s);background-size:cover;background-position:center"', esc_url( $hero_img ) ); ?>><div class="bkh-wrap">
    <nav class="bkh-crumbs"><a href="<?php echo esc_url( bkh_url('/') ); ?>">首页</a> · <a href="<?php echo esc_url( bkh_url('/knowledge/') ); ?>">听力知识中心</a> · <span><?php echo esc_html( $is_tinnitus ? '耳鸣专题' : ( $is_hearing_loss ? '听力下降专题' : ( $is_hearing_aids ? '助听器百科' : get_the_title() ) ) ); ?></span></nav>
    <?php if ( $is_tinnitus ) : ?>
      <h1 class="bkh-hero-title">耳鸣专题</h1><p class="bkh-hero-sub">了解耳鸣常见场景、可能原因、改善建议，以及它与听力下降之间的关系。</p><p class="bkh-hero-summary">耳鸣常被描述为嗡嗡声、蝉鸣声、电流声或滋滋声。它可能和疲劳、噪音暴露、听力下降等多种因素有关。具体情况因人而异，建议结合听力测试或专业建议判断。</p><div class="bkh-hero-actions"><a class="bkh-btn bkh-btn-primary" href="#tinnitus-questions">查看常见耳鸣问题</a><a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">了解听力评估</a></div>
    <?php elseif ( $is_hearing_loss ) : ?>
      <h1 class="bkh-hero-title">听力下降专题</h1><p class="bkh-hero-sub">了解听力下降的常见表现、可能原因、日常影响，以及什么时候需要做听力评估。</p><p class="bkh-hero-summary">听力下降并不总是突然发生。很多人最早表现为听不清别人说话、电视声音越开越大、在人多或嘈杂环境中交流困难。具体情况因人而异，建议结合听力测试或专业建议判断。</p><div class="bkh-hero-actions"><a class="bkh-btn bkh-btn-primary" href="#hearing-loss-signs">查看常见表现</a><a class="bkh-btn bkh-btn-ghost" href="#hearing-assessment">了解听力评估</a></div>
    <?php elseif ( $is_hearing_aids ) : ?>
      <h1 class="bkh-hero-title">助听器百科</h1><p class="bkh-hero-sub">了解助听器怎么选、常见误区、佩戴体验、使用保养，以及第一次给父母选助听器需要注意什么。</p><p class="bkh-hero-summary">助听器不是简单把声音放大的设备。合适的助听方案通常需要结合听力情况、使用场景、佩戴习惯和服务支持综合判断。具体适配效果因人而异，建议结合听力测试或专业建议选择。</p><div class="bkh-hero-actions"><a class="bkh-btn bkh-btn-primary" href="#hearing-aid-guide">查看选购要点</a><a class="bkh-btn bkh-btn-ghost" href="#hearing-aid-faq">了解常见误区</a></div>
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
  <?php endif; ?>

  <?php if ( $is_hearing_aids ) : ?>
    <section id="hearing-aid-guide" class="bkh-block bkh-scenes"><div class="bkh-wrap"><h2 class="bkh-section-title">你最关心哪类助听器问题？</h2><div class="bkh-scene-grid bkh-scene-grid-2col"><?php foreach($aid_guides as $g): ?><a class="bkh-scene-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($g['slug'],'/knowledge/hearing-aids/') ); ?>"><h3 class="bkh-scene-title"><?php echo esc_html($g['title']); ?></h3></a><?php endforeach; ?></div></div></section>
    <section id="hearing-aid-faq" class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">助听器常见问题</h2><div class="bkh-hot-grid"><?php foreach($aid_questions as $q): ?><a class="bkh-hot-card" href="<?php echo esc_url( bkh_get_knowledge_article_url_or_fallback($q['slug'],'/knowledge/hearing-aids/') ); ?>"><span class="bkh-hot-q"><?php echo esc_html($q['q']); ?></span><span class="bkh-hot-a"><?php echo esc_html($q['a']); ?></span><span class="bkh-hot-arrow" aria-hidden="true">→</span></a><?php endforeach; ?></div></div></section>
    <section class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">选择助听器时，建议重点看这 6 点</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">听力情况：先了解听力下降程度，再判断是否需要助听方案。</div><div class="bkh-tip-card">使用场景：家庭交流、看电视、电话、外出办事需求不同。</div><div class="bkh-tip-card">佩戴舒适度：长期佩戴需要轻便、稳定和舒适。</div><div class="bkh-tip-card">操作简单：给父母选择时，越容易上手越友好。</div><div class="bkh-tip-card">降噪与清晰度：嘈杂环境下的交流体验很关键。</div><div class="bkh-tip-card">售后与服务：后续调试、保养和咨询同样重要。</div></div></div></section>
    <section class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">这些助听器误区，建议提前了解</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">不是声音越大越好，声音补偿需要匹配听力情况。</div><div class="bkh-tip-card">不是越贵越适合，关键在于场景和适配体验。</div><div class="bkh-tip-card">不是买回就不管，后续调试与保养很重要。</div><div class="bkh-tip-card">不是所有人都适合同一款，方案应因人而异。</div><div class="bkh-tip-card">不是戴上就立刻适应所有场景，通常需要适应期。</div></div></div></section>
    <section class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">给父母选第一对助听器，更要关注真实生活场景</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">在家看电视</div><div class="bkh-tip-card">和子女聊天</div><div class="bkh-tip-card">接电话</div><div class="bkh-tip-card">买菜、逛超市</div><div class="bkh-tip-card">遛弯、户外交流</div><div class="bkh-tip-card">带孙子时听清提醒</div></div><p class="bkh-disclaimer">给父母选助听器，不只是买一个设备，更是帮助他们重新参与家庭沟通。建议从最常遇到的听不清场景出发，选择更容易上手、售后更方便的方案。</p></div></section>
    <section class="bkh-block"><div class="bkh-wrap bkh-wrap-text"><h2 class="bkh-section-title">助听器和普通扩音器有什么不同？</h2><div class="bkh-compare-card"><p><strong>普通扩音器：</strong>通常偏向整体放大声音，可能同时放大环境噪音。</p><p><strong>助听器：</strong>更强调结合听力情况进行声音补偿、降噪处理和佩戴适配。</p><p><strong>选择建议：</strong>如已影响日常沟通，建议先了解听力情况，再判断是否需要助听方案。</p></div></div></section>
    <section class="bkh-block"><div class="bkh-wrap bkh-wrap-text"><h2 class="bkh-section-title">AI智能助听，主要解决什么问题？</h2><p class="bkh-hero-sub">AI智能助听通常用于声音处理、环境识别、降噪和语音增强。它的目标是让不同场景下的听声体验更自然、更省力，但具体效果会受听力情况、设备能力和使用环境影响。</p><div class="bkh-tip-grid"><div class="bkh-tip-card">AI降噪：帮助减少部分环境干扰。</div><div class="bkh-tip-card">场景识别：根据环境变化调整处理方式。</div><div class="bkh-tip-card">语音增强：提升交流时语音可听度。</div></div><p><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/ai-hearing/') ); ?>">了解 AI 智能助听</a></p></div></section>
    <section class="bkh-block"><div class="bkh-wrap"><h2 class="bkh-section-title">刚开始戴助听器，需要一个适应过程</h2><div class="bkh-tip-grid"><div class="bkh-tip-card">从安静环境开始适应</div><div class="bkh-tip-card">每天逐步增加佩戴时间</div><div class="bkh-tip-card">记录哪些场景更舒适或不适</div><div class="bkh-tip-card">遇到不适及时咨询</div><div class="bkh-tip-card">定期清洁和保养</div><div class="bkh-tip-card">必要时进行调试</div></div><p class="bkh-disclaimer">助听器佩戴体验与个人听力情况、耳道条件、使用习惯和调试方式有关。若佩戴不适，建议咨询专业人员。</p></div></section>
  <?php endif; ?>

  <?php if ( ! empty( $schema_faq ) ) : $faq_entities = array(); foreach ( $schema_faq as $f ) $faq_entities[] = array('@type'=>'Question','name'=>wp_strip_all_tags($f['q']),'acceptedAnswer'=>array('@type'=>'Answer','text'=>wp_strip_all_tags($f['a']))); ?><script type="application/ld+json"><?php echo wp_json_encode(array('@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faq_entities), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script><?php endif; ?>

  <?php bkh_render_video_block( $post_id ); ?>

  <?php if ( $cat_slug ) { $related = new WP_Query( array('post_type'=>'knowledge_article','posts_per_page'=>6,'meta_key'=>'_bkh_featured_priority','orderby'=>array('meta_value_num'=>'DESC','date'=>'DESC'),'tax_query'=>array(array('taxonomy'=>'knowledge_category','field'=>'slug','terms'=>$cat_slug)),) ); ?><section class="bkh-block bkh-related"><div class="bkh-wrap"><h2 class="bkh-section-title"><?php echo esc_html( $is_tinnitus ? '更多耳鸣相关内容' : ( $is_hearing_loss ? '更多听力下降相关内容' : ( $is_hearing_aids ? '更多助听器相关内容' : '本专题相关文章' ) ) ); ?></h2><?php if ( $related->have_posts() ) : ?><div class="bkh-article-grid"><?php while ( $related->have_posts() ) : $related->the_post(); $thumb = get_the_post_thumbnail_url(null,'medium'); ?><article class="bkh-art-card"><?php if($thumb): ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy"></a><?php endif; ?><div class="bkh-art-body"><h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p class="bkh-art-excerpt"><?php echo esc_html( wp_strip_all_tags(get_the_excerpt()) ); ?></p></div></article><?php endwhile; wp_reset_postdata(); ?></div><?php else: ?><p class="bkh-empty"><?php echo esc_html( $is_tinnitus ? '更多耳鸣相关内容正在整理中。' : ( $is_hearing_loss ? '更多听力下降相关内容正在整理中。' : ( $is_hearing_aids ? '更多助听器相关内容正在整理中。' : '内容正在整理中。' ) ) ); ?></p><?php endif; ?></div></section><?php } ?>

  <?php if ( $is_tinnitus || $is_hearing_loss || $is_hearing_aids ) : ?>
  <section class="bkh-bottom-nav"><div class="bkh-wrap"><h2 class="bkh-section-title bkh-section-title-sm">继续了解相关听力问题</h2><ul class="bkh-bottom-list"><li><a href="<?php echo esc_url( bkh_url('/knowledge/tinnitus/') ); ?>">耳鸣专题</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">听力下降</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">助听器百科</a></li><li><a href="<?php echo esc_url( bkh_url('/knowledge/ai-hearing/') ); ?>">AI智能助听</a></li></ul></div></section>
  <section class="bkh-cta bkh-cta-soft"><div class="bkh-wrap"><div class="bkh-cta-inner"><div class="bkh-cta-text"><h2 class="bkh-cta-title"><?php echo esc_html( $is_tinnitus ? '如果耳鸣伴随听不清，建议先了解听力情况' : ( $is_hearing_loss ? '想了解父母是否适合助听方案？' : '准备给父母选第一对助听器？' ) ); ?></h2><p class="bkh-cta-desc"><?php echo esc_html( $is_tinnitus ? '耳鸣和听力下降有时会同时出现。通过听力评估，可以更清楚地了解自己的听力状态，再判断是否需要进一步干预或助听方案。' : ( $is_hearing_loss ? '如果父母经常听不清、看电视声音变大，建议先了解听力下降的常见表现，再结合听力测试或专业建议判断是否需要助听方案。' : '如果父母经常听不清、看电视声音变大，建议先了解听力情况，再结合日常场景选择更合适的助听方案。' ) ); ?></p></div><div class="bkh-cta-actions"><?php if($is_tinnitus): ?><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">了解听力评估</a><?php elseif($is_hearing_loss): ?><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">查看助听器百科</a><?php else: ?><a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url($products_url); ?>">查看悦听礼赠款助听器</a><?php endif; ?><a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( $is_hearing_aids ? bkh_url('/knowledge/hearing-loss/') : $products_url ); ?>"><?php echo esc_html( $is_hearing_aids ? '了解听力下降表现' : '查看助听器方案' ); ?></a></div></div></div></section>
  <?php else : ?>
    <?php bkh_render_cta_block( $post_id ); ?>
  <?php endif; ?>
</main>

<?php get_footer();
