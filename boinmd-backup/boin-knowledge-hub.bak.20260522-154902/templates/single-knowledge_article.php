<?php
/**
 * single-knowledge_article.php - 知识文章详情模板
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'bkh_render_video_block' ) ) {
    require_once BKH_DIR . 'templates/partials/video-block.php';
}
if ( ! function_exists( 'bkh_render_faq_block' ) ) {
    require_once BKH_DIR . 'templates/partials/faq-block.php';
}

get_header();
wp_enqueue_style( 'bkh-frontend', BKH_URL . 'assets/css/bkh-frontend.css', array(), BKH_VERSION );

if ( ! have_posts() ) { get_footer(); return; }
the_post();

$post_id = get_the_ID();
$terms = wp_get_object_terms( $post_id, 'knowledge_category' );
$cat_name = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
$cat_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '';
$reading_minutes = bkh_get_reading_minutes( $post_id );
$faqs = bkh_get_visible_faqs( $post_id );

$summary = trim( (string) get_post_meta( $post_id, 'summary', true ) );
if ( $summary === '' ) $summary = trim( (string) get_post_meta( $post_id, 'answer', true ) );
if ( $summary === '' ) $summary = trim( (string) get_post_meta( $post_id, 'ai_summary', true ) );
if ( $summary === '' && has_excerpt() ) $summary = trim( (string) get_the_excerpt() );

$key_points = get_post_meta( $post_id, '_bkh_key_points', true );
if ( ! is_array( $key_points ) ) {
    $key_points = array();
}
$key_points = array_values( array_filter( array_map( 'trim', $key_points ) ) );

// Build a lightweight server-side TOC by scanning H2/H3 and injecting IDs.
$content_html = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
$toc_items = array();
$used_ids = array();
if ( preg_match_all( '/<h([23])([^>]*)>(.*?)<\/h\1>/is', $content_html, $matches, PREG_OFFSET_CAPTURE ) ) {
    $new_html = '';
    $cursor = 0;
    $idx = 1;
    foreach ( $matches[0] as $i => $full_match ) {
        $full = $full_match[0];
        $start = $full_match[1];
        $len = strlen( $full );
        $level = (int) $matches[1][ $i ][0];
        $attrs = (string) $matches[2][ $i ][0];
        $inner = (string) $matches[3][ $i ][0];
        $text = trim( wp_strip_all_tags( $inner ) );

        $id = '';
        if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $idm ) ) {
            $id = sanitize_title( $idm[2] );
        }
        if ( $id === '' ) $id = 'sec-' . $idx++;
        $base_id = $id;
        $suffix = 2;
        while ( isset( $used_ids[ $id ] ) ) {
            $id = $base_id . '-' . $suffix;
            $suffix++;
        }
        $used_ids[ $id ] = true;

        if ( $text !== '' ) {
            $toc_items[] = array(
                'level' => $level,
                'id'    => $id,
                'text'  => $text,
            );
        }

        if ( stripos( $attrs, ' id=' ) === false ) {
            $attrs .= ' id="' . esc_attr( $id ) . '"';
        } else {
            $attrs = preg_replace( '/\sid=(["\'])(.*?)\1/i', ' id="' . esc_attr( $id ) . '"', $attrs, 1 );
        }
        $replacement = '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
        $new_html .= substr( $content_html, $cursor, $start - $cursor ) . $replacement;
        $cursor = $start + $len;
    }
    $new_html .= substr( $content_html, $cursor );
    $content_html = $new_html;
}

$ai_summary_block = trim( (string) get_post_meta( $post_id, 'ai_summary', true ) );
if ( $ai_summary_block === '' ) $ai_summary_block = trim( (string) get_post_meta( $post_id, 'answer', true ) );

$cta_title = '继续了解听力知识';
$cta_desc = '从常见听力问题出发，逐步了解专题内容与应对思路。';
$cta_actions = array(
    array( 'label' => '返回知识中心', 'url' => bkh_url( '/knowledge/' ), 'class' => 'bkh-btn bkh-btn-primary' ),
);
if ( $cat_slug === 'tinnitus' ) {
    $cta_title = '如果耳鸣伴随听不清，建议先了解听力情况';
    $cta_desc = '耳鸣和听力下降有时会同时出现，建议结合听力测试判断具体情况。';
    $cta_actions = array(
        array( 'label' => '了解听力下降', 'url' => bkh_url( '/knowledge/hearing-loss/' ), 'class' => 'bkh-btn bkh-btn-primary' ),
    );
} elseif ( $cat_slug === 'hearing-loss' ) {
    $cta_title = '想进一步了解助听方案？';
    $cta_desc = '先了解听力状态，再结合日常场景选择更合适的助听方案。';
    $cta_actions = array(
        array( 'label' => '查看助听器百科', 'url' => bkh_url( '/knowledge/hearing-aids/' ), 'class' => 'bkh-btn bkh-btn-primary' ),
    );
} elseif ( $cat_slug === 'hearing-aids' ) {
    $cta_title = '准备给父母选择第一对助听器？';
    $cta_desc = '结合听力测试与日常使用场景，选择更合适的助听方案。';
    $cta_actions = array(
        array( 'label' => '查看助听器方案', 'url' => bkh_get_product_page_url(), 'class' => 'bkh-btn bkh-btn-primary' ),
    );
} elseif ( $cat_slug === 'ai-hearing' ) {
    $cta_title = '想了解智能助听方案？';
    $cta_desc = 'AI能力可帮助部分场景下的听声体验优化，具体效果因人而异。';
    $cta_actions = array(
        array( 'label' => '查看助听器百科', 'url' => bkh_url( '/knowledge/hearing-aids/' ), 'class' => 'bkh-btn bkh-btn-primary' ),
        array( 'label' => '查看助听器方案', 'url' => bkh_get_product_page_url(), 'class' => 'bkh-btn bkh-btn-ghost' ),
    );
}
?>

<main class="bkh-page bkh-article">
  <article class="bkh-art-detail">
    <header class="bkh-art-header">
      <div class="bkh-wrap">
        <nav class="bkh-crumbs">
          <a href="<?php echo esc_url( bkh_url( '/' ) ); ?>">首页</a> ·
          <a href="<?php echo esc_url( bkh_url( '/knowledge/' ) ); ?>">听力知识中心</a>
          <?php if ( $cat_slug ) : ?> · <a href="<?php echo esc_url( bkh_category_url( $cat_slug ) ); ?>"><?php echo esc_html( $cat_name ); ?></a><?php endif; ?>
          · <span><?php the_title(); ?></span>
        </nav>
        <?php if ( $cat_name ) : ?><span class="bkh-art-tag"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
        <h1 class="bkh-art-h1"><?php the_title(); ?></h1>
        <?php if ( $summary !== '' ) : ?><p class="bkh-art-lead"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
        <p class="bkh-art-meta">
          <span>来源：博音 BOINMD</span>
          <span>·</span>
          <span>发布时间：<?php echo esc_html( get_the_date() ); ?></span>
          <span>·</span>
          <span>更新时间：<?php echo esc_html( get_the_modified_date() ); ?></span>
          <span>·</span>
          <span><?php echo esc_html( $reading_minutes ); ?> 分钟阅读</span>
        </p>
      </div>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
      <figure class="bkh-art-cover"><?php the_post_thumbnail( 'large' ); ?></figure>
    <?php endif; ?>

    <?php if ( ! empty( $key_points ) || $ai_summary_block !== '' ) : ?>
      <section class="bkh-block bkh-art-quick-section">
        <div class="bkh-wrap bkh-wrap-text">
          <h2 class="bkh-section-title">快速了解</h2>
          <?php if ( ! empty( $key_points ) ) : ?>
            <div class="bkh-summary-box">
              <ol class="bkh-summary-list">
                <?php foreach ( array_slice( $key_points, 0, 5 ) as $point ) : ?>
                  <li><?php echo esc_html( $point ); ?></li>
                <?php endforeach; ?>
              </ol>
            </div>
          <?php else : ?>
            <div class="bkh-quick-card"><?php echo wp_kses_post( wpautop( $ai_summary_block ) ); ?></div>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if ( count( $toc_items ) >= 2 ) : ?>
      <section class="bkh-block bkh-art-toc-section">
        <div class="bkh-wrap bkh-wrap-text">
          <h2 class="bkh-section-title">目录</h2>
          <nav class="bkh-toc" aria-label="文章目录">
            <ul>
              <?php foreach ( $toc_items as $item ) : ?>
                <li class="<?php echo $item['level'] === 3 ? 'is-h3' : 'is-h2'; ?>">
                  <a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        </div>
      </section>
    <?php endif; ?>

    <section class="bkh-art-body-wrap">
      <div class="bkh-wrap bkh-wrap-text bkh-art-content">
        <?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
    </section>

    <?php if ( ! empty( $faqs ) ) : bkh_render_faq_block( $faqs ); endif; ?>
    <?php bkh_render_video_block( $post_id ); ?>

    <?php
    $related_args = array(
        'post_type'      => 'knowledge_article',
        'posts_per_page' => 6,
        'post__not_in'   => array( $post_id ),
        'meta_key'       => '_bkh_featured_priority',
        'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
    );
    if ( $cat_slug ) {
        $related_args['tax_query'] = array(
            array( 'taxonomy' => 'knowledge_category', 'field' => 'slug', 'terms' => $cat_slug ),
        );
    }
    $related = new WP_Query( $related_args );
    ?>
    <section class="bkh-block bkh-related">
      <div class="bkh-wrap">
        <h2 class="bkh-section-title">相关推荐</h2>
        <?php if ( $related->have_posts() ) : ?>
          <div class="bkh-article-grid bkh-related-grid">
            <?php while ( $related->have_posts() ) : $related->the_post(); $thumb = get_the_post_thumbnail_url( null, 'large' ); ?>
              <article class="bkh-art-card">
                <?php if ( $thumb ) : ?><a href="<?php the_permalink(); ?>" class="bkh-art-thumb-wrap"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></a><?php endif; ?>
                <div class="bkh-art-body">
                  <h3 class="bkh-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                </div>
              </article>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        <?php else : ?>
          <p class="bkh-empty">更多相关内容正在整理中。</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="bkh-bottom-nav">
      <div class="bkh-wrap">
        <h2 class="bkh-section-title bkh-section-title-sm">继续了解相关主题</h2>
        <ul class="bkh-bottom-list">
          <li><a href="<?php echo esc_url( bkh_url('/knowledge/tinnitus/') ); ?>">耳鸣专题</a></li>
          <li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-loss/') ); ?>">听力下降</a></li>
          <li><a href="<?php echo esc_url( bkh_url('/knowledge/hearing-aids/') ); ?>">助听器百科</a></li>
          <li><a href="<?php echo esc_url( bkh_url('/knowledge/ai-hearing/') ); ?>">AI智能助听</a></li>
        </ul>
      </div>
    </section>

    <section class="bkh-cta bkh-cta-soft">
      <div class="bkh-wrap">
        <div class="bkh-cta-inner">
          <div class="bkh-cta-text">
            <h2 class="bkh-cta-title"><?php echo esc_html( $cta_title ); ?></h2>
            <p class="bkh-cta-desc"><?php echo esc_html( $cta_desc ); ?></p>
          </div>
          <div class="bkh-cta-actions">
            <?php foreach ( $cta_actions as $action ) : ?>
              <a class="<?php echo esc_attr( $action['class'] ); ?>" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['label'] ); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  </article>
</main>

<?php get_footer();
