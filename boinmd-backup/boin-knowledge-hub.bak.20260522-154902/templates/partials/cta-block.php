<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bkh_render_cta_block( $post_id ) {
    $cta = bkh_get_cta( $post_id );
    if ( empty( $cta['cta_title'] ) ) return;
    ?>
    <section class="bkh-block bkh-cta" id="cta">
      <div class="bkh-wrap bkh-cta-inner">
        <div class="bkh-cta-text">
          <h2 class="bkh-cta-title"><?php echo esc_html( $cta['cta_title'] ); ?></h2>
          <?php if ( ! empty( $cta['cta_description'] ) ) : ?>
            <p class="bkh-cta-desc"><?php echo esc_html( $cta['cta_description'] ); ?></p>
          <?php endif; ?>
        </div>
        <div class="bkh-cta-actions">
          <?php if ( ! empty( $cta['cta_button_text'] ) && ! empty( $cta['cta_button_url'] ) ) : ?>
            <a class="bkh-btn bkh-btn-primary" href="<?php echo esc_url( $cta['cta_button_url'] ); ?>"><?php echo esc_html( $cta['cta_button_text'] ); ?></a>
          <?php endif; ?>
          <?php if ( ! empty( $cta['cta_secondary_text'] ) && ! empty( $cta['cta_secondary_url'] ) ) : ?>
            <a class="bkh-btn bkh-btn-ghost" href="<?php echo esc_url( $cta['cta_secondary_url'] ); ?>"><?php echo esc_html( $cta['cta_secondary_text'] ); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}
