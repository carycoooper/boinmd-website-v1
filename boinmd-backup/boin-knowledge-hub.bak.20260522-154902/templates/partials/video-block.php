<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the video module for a post.
 * Logic per task spec:
 *  - If any bilibili video → embed (or use is_primary)
 *  - If only douyin/wechat/kuaishou → card with jump link
 *  - YouTube only shows as international fallback, not preferred for CN audience
 */
function bkh_render_video_block( $post_id ) {
    $videos = bkh_get_videos( $post_id );
    if ( empty( $videos ) ) return;

    $primary = bkh_get_primary_video( $post_id );
    $alt = array_filter( $videos, function( $v ) use ( $primary ) {
        return $v !== $primary;
    } );

    ?>
    <section class="bkh-block bkh-videos" id="videos">
      <div class="bkh-wrap">
        <h2 class="bkh-section-title">视频内容</h2>

        <?php if ( $primary ) :
            $provider = bkh_get_provider( $primary['platform'] ?? '' );
            $embed_html = $provider ? $provider->get_embed_html( $primary ) : '';
        ?>
          <?php if ( $embed_html ) : ?>
            <div class="bkh-primary-video">
              <?php echo $embed_html; ?>
              <p class="bkh-video-meta">
                <span class="bkh-video-platform-tag bkh-pt-<?php echo esc_attr( $primary['platform'] ); ?>"><?php echo esc_html( $provider ? $provider->label : $primary['platform'] ); ?></span>
                <span><?php echo esc_html( $primary['video_title'] ?? '' ); ?></span>
                <?php if ( ! empty( $primary['account_name'] ) ) : ?>
                  <span class="bkh-video-account">来自：<?php echo esc_html( $primary['account_name'] ); ?></span>
                <?php endif; ?>
              </p>
            </div>
          <?php else :
            // Render the primary as a card if no embed available
            bkh_render_video_card( $primary );
          endif; ?>
        <?php endif; ?>

        <?php if ( ! empty( $alt ) ) : ?>
          <h3 class="bkh-section-title-sm">其他平台观看</h3>
          <div class="bkh-video-cards">
            <?php foreach ( $alt as $v ) bkh_render_video_card( $v ); ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function bkh_render_video_card( $v ) {
    $provider = bkh_get_provider( $v['platform'] ?? '' );
    $label = $provider ? $provider->label : ucfirst( $v['platform'] ?? '' );
    $cover = $v['cover_image'] ?? '';
    $url   = $v['video_url']   ?? '#';
    ?>
    <a class="bkh-video-card bkh-pt-<?php echo esc_attr( $v['platform'] ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow">
      <?php if ( $cover ) : ?>
        <span class="bkh-vc-cover-wrap"><img class="bkh-vc-cover" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $v['video_title'] ?? '' ); ?>" loading="lazy"></span>
      <?php else : ?>
        <span class="bkh-vc-cover-wrap bkh-vc-cover-empty"><span class="bkh-vc-platform-letter"><?php echo esc_html( mb_substr( $label, 0, 1 ) ); ?></span></span>
      <?php endif; ?>
      <span class="bkh-vc-body">
        <span class="bkh-vc-platform"><?php echo esc_html( $label ); ?></span>
        <span class="bkh-vc-title"><?php echo esc_html( $v['video_title'] ?? '' ); ?></span>
        <?php if ( ! empty( $v['account_name'] ) ) : ?>
          <span class="bkh-vc-account"><?php echo esc_html( $v['account_name'] ); ?></span>
        <?php endif; ?>
        <span class="bkh-vc-cta">到 <?php echo esc_html( $label ); ?> 观看 →</span>
      </span>
    </a>
    <?php
}
