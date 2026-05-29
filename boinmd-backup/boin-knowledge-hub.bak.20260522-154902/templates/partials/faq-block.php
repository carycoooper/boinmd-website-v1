<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bkh_render_faq_block( $faqs ) {
    if ( empty( $faqs ) ) return;
    ?>
    <section class="bkh-block bkh-faq" id="faq">
      <div class="bkh-wrap bkh-wrap-text">
        <h2 class="bkh-section-title">常见问题</h2>
        <div class="bkh-faq-list">
          <?php foreach ( $faqs as $i => $f ) :
            $q = $f['question'] ?? '';
            $a = $f['answer']   ?? '';
            if ( $q === '' ) continue;
          ?>
            <details class="bkh-faq-item" <?php if ( $i === 0 ) echo 'open'; ?>>
              <summary class="bkh-faq-q"><?php echo esc_html( $q ); ?></summary>
              <div class="bkh-faq-a"><?php echo wp_kses_post( wpautop( $a ) ); ?></div>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}
