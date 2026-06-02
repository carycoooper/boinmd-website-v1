<?php
/**
 * Import the first two knowledge_article drafts for template validation.
 *
 * Run from the WordPress root:
 * wp eval-file /path/to/scripts/import-first-two-knowledge-articles.php --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
    fwrite( STDERR, "This script must be run with wp eval-file.\n" );
    exit( 1 );
}

$articles = array(
    array(
        'title'    => '晚上耳鸣特别明显是怎么回事？',
        'slug'     => 'night-tinnitus',
        'category' => 'tinnitus',
        'priority' => 10,
        'summary'  => '晚上耳鸣更明显，常见原因是环境变安静后，耳鸣更容易被注意到；也可能和疲劳、压力、睡眠不足、噪音暴露或听力变化有关。若持续影响睡眠，建议结合听力测试或专业建议判断。',
        'excerpt'  => '晚上耳鸣明显并不一定代表问题突然加重，很多时候和环境安静、注意力集中、疲劳和睡眠状态有关。本文帮你了解常见原因、应对方式和什么时候建议做听力评估。',
        'content'  => <<<HTML
<!-- wp:heading -->
<h2>开头直接回答</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>晚上耳鸣特别明显，很多时候不是因为夜里耳朵突然“变严重”，而是因为夜晚环境更安静，外界声音减少，耳鸣更容易被大脑注意到。白天有说话声、交通声、家务声等背景声音，耳鸣可能被掩盖；到了晚上，周围声音变少，耳朵里的嗡嗡声、蝉鸣声或滋滋声就会显得更突出。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>不过，夜间耳鸣也可能和熬夜、压力、睡眠不足、噪音暴露、耳部状态或听力下降有关。具体原因因人而异，不能只凭感觉判断。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>为什么晚上更容易感觉到耳鸣？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>夜晚最常见的变化是“安静”。外界声音减少后，大脑会更容易捕捉到原本被忽略的声音感受。有些人白天工作、聊天、看电视时不太在意，到了睡前躺下，注意力集中在身体感受上，耳鸣就会变得明显。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>第二个因素是疲劳。长时间用脑、精神紧张、睡眠不足，可能让部分人对声音更敏感。尤其是熬夜后，耳鸣感受可能更容易被放大。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>第三个因素是情绪。越担心“耳鸣会不会越来越严重”，越容易反复关注耳鸣本身。关注越多，感受越强，这会形成一种不舒服的循环。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>常见场景</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul><li>睡前关灯后，房间很安静</li><li>熬夜、加班或压力大之后</li><li>白天接触过较大噪音</li><li>最近睡眠质量不好</li><li>同时感觉听不清别人说话</li></ul>
<!-- /wp:list -->
<!-- wp:paragraph -->
<p>如果只是偶尔出现，可以先记录出现时间和场景。如果持续存在，或者明显影响睡眠，就不建议长期硬扛。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>可以先怎么做？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>可以先尝试保持规律作息，减少连续熬夜，睡前避免长时间处于完全安静的环境。部分人会觉得轻柔的环境声有助于减少对耳鸣的关注，例如低音量白噪声或自然声音，但音量不宜过大。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>同时要减少强噪音刺激。如果白天经常戴耳机、处于装修声或机器声环境中，建议注意音量和时间。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>什么时候建议做听力评估？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>如果耳鸣同时伴随听不清别人说话、电视声音越开越大、嘈杂环境交流困难，建议尽早了解听力情况。耳鸣不等于一定有听力下降，但不少人会同时出现耳鸣和听力变化。听力测试可以帮助判断具体状态，为后续是否需要干预或助听方案提供参考。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>继续了解：<a href="/knowledge/tinnitus/">耳鸣专题</a>、<a href="/knowledge/hearing-loss/">听力下降专题</a>。</p>
<!-- /wp:paragraph -->
HTML,
        'faqs'     => array(
            array( 'question' => '晚上耳鸣明显是不是更严重了？', 'answer' => '不一定。夜晚环境安静、注意力集中时，耳鸣更容易被注意到。但如果持续加重或影响睡眠，建议结合听力测试判断。' ),
            array( 'question' => '睡觉时耳鸣影响入睡怎么办？', 'answer' => '可以先减少完全安静的环境，保持规律作息，记录耳鸣出现时间。如果长期影响睡眠，建议咨询专业人员。' ),
            array( 'question' => '耳鸣和听力下降有关吗？', 'answer' => '部分人可能同时出现耳鸣和听力下降，但二者并不完全等同。若伴随听不清说话，建议做一次听力评估。' ),
            array( 'question' => '戴助听器能改善夜间耳鸣吗？', 'answer' => '对部分同时伴有听力下降的人群，合适的助听方案可能有助于改善日常听声体验，具体效果因人而异。' ),
        ),
    ),
    array(
        'title'    => '老人听不清别人说话怎么办？',
        'slug'     => 'cannot-hear-speech',
        'category' => 'hearing-loss',
        'priority' => 20,
        'summary'  => '老人听不清别人说话，可能与年龄相关听力变化、环境噪音、说话距离或沟通习惯有关。建议先观察具体场景，必要时做听力评估，再判断是否需要助听方案。',
        'excerpt'  => '父母经常听不清、总让人重复，可能不是“不认真听”。本文帮你判断常见原因，并说明如何更温和地帮助老人了解听力情况。',
        'content'  => <<<HTML
<!-- wp:heading -->
<h2>开头直接回答</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>老人听不清别人说话，先不要急着责怪“没听认真”。很多时候，这可能和年龄相关听力变化、嘈杂环境、说话距离、语速过快或高频声音分辨能力下降有关。建议家人先观察老人在哪些场景听不清，再考虑做一次听力评估。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>听力变化往往是慢慢出现的，老人自己未必第一时间意识到。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>为什么老人常说“你说大点”？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>日常交流中，老人不一定是完全听不到声音，而是听到了却听不清内容。比如能听见有人说话，但分辨不出具体字词，尤其是普通话、方言切换、多人同时说话时更明显。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>这类情况常见于高频声音分辨困难。部分辅音、轻声或较细的声音不容易被听清，于是老人会觉得“你们说话含糊”。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>常见表现有哪些？</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul><li>经常让别人重复一遍</li><li>电视音量比以前大</li><li>电话里听不清</li><li>人多时跟不上聊天</li><li>总觉得别人说话太小声</li><li>家人沟通时容易烦躁</li></ul>
<!-- /wp:list -->
<!-- wp:paragraph -->
<p>如果这些表现持续出现，就值得关注。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>家人应该怎么沟通？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>建议用更温和的方式。不要直接说“你耳朵不行了”，可以说“我们去了解一下听力情况，看看是不是环境影响”。沟通时尽量面对面说话，语速放慢，不要隔着房间喊。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>如果老人抗拒助听器，可以先从“听力评估”开始，而不是直接要求购买设备。</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2>什么时候考虑助听方案？</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>是否需要助听器，要结合听力测试结果、生活影响和个人意愿判断。对部分轻中度听力下降人群，合适的助听方案可能有助于改善日常交流体验，但具体适配效果因人而异。</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>继续了解：<a href="/knowledge/hearing-loss/">听力下降专题</a>、<a href="/knowledge/hearing-aids/">助听器百科</a>。</p>
<!-- /wp:paragraph -->
HTML,
        'faqs'     => array(
            array( 'question' => '老人听不清就是耳背吗？', 'answer' => '不一定。可能与听力变化、环境噪音、沟通方式等因素有关，需要结合具体场景判断。' ),
            array( 'question' => '老人不承认听不清怎么办？', 'answer' => '可以先从日常场景沟通，不直接贴标签。建议用听力评估作为了解情况的第一步。' ),
            array( 'question' => '老人听不清一定要戴助听器吗？', 'answer' => '不一定。是否需要助听器要结合听力测试、生活影响和专业建议判断。' ),
            array( 'question' => '家人说话要不要越大声越好？', 'answer' => '不建议只靠喊。面对面、语速慢、表达清楚，通常比单纯提高音量更友好。' ),
        ),
    ),
);

foreach ( $articles as $article ) {
    $term = term_exists( $article['category'], 'knowledge_category' );
    if ( ! $term ) {
        $term = wp_insert_term( $article['category'], 'knowledge_category', array( 'slug' => $article['category'] ) );
    }
    if ( is_wp_error( $term ) ) {
        WP_CLI::warning( 'Cannot find or create term: ' . $article['category'] );
        continue;
    }

    $existing = get_page_by_path( $article['slug'], OBJECT, 'knowledge_article' );
    $postarr = array(
        'post_type'    => 'knowledge_article',
        'post_status'  => 'draft',
        'post_title'   => $article['title'],
        'post_name'    => $article['slug'],
        'post_excerpt' => $article['excerpt'],
        'post_content' => $article['content'],
    );

    if ( $existing instanceof WP_Post ) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post( $postarr, true );
    } else {
        $post_id = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $post_id ) ) {
        WP_CLI::warning( 'Failed importing ' . $article['slug'] . ': ' . $post_id->get_error_message() );
        continue;
    }

    wp_set_object_terms( $post_id, array( $article['category'] ), 'knowledge_category', false );
    update_post_meta( $post_id, 'summary', $article['summary'] );
    update_post_meta( $post_id, '_bkh_featured_priority', (int) $article['priority'] );
    update_post_meta( $post_id, '_bkh_faqs', $article['faqs'] );
    update_post_meta( $post_id, '_bkh_videos', array() );

    WP_CLI::success( sprintf( 'Imported draft: %s (%s) -> %s', $article['title'], $article['slug'], get_permalink( $post_id ) ) );
}
