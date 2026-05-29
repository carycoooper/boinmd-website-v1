<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Abstract base for all social-platform video providers.
 * Each provider must implement:
 *   - normalize_url($url)
 *   - extract_video_id($url)
 *   - get_embed_url($record)    // for VideoObject schema (may be '')
 *   - get_embed_html($record)   // for inline rendering (may be '' to fall back to card)
 *   - get_card_data($record)    // structured data for cards
 *   - validate_url($url)
 */
abstract class BKH_Provider_Abstract {
    public $platform = '';
    public $label    = '';
    public $can_embed = false;   // CN-friendly iframe?

    abstract public function normalize_url( $url );
    abstract public function extract_video_id( $url );
    abstract public function get_embed_url( $record );
    abstract public function get_embed_html( $record );
    abstract public function validate_url( $url );

    public function get_card_data( $record ) {
        return array(
            'platform'     => $this->platform,
            'platform_label' => $this->label,
            'title'        => $record['video_title']  ?? '',
            'url'          => $record['video_url']    ?? '',
            'cover'        => $record['cover_image']  ?? '',
            'duration'     => $record['duration']     ?? '',
            'account'      => $record['account_name'] ?? '',
            'account_url'  => $record['account_url']  ?? '',
        );
    }
}
