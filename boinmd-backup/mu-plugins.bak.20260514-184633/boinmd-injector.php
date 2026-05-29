<?php
// 博音BOINMD风格注入器 - 独立加载
if (!defined('ABSPATH')) {
    // Try to load WordPress
    if (file_exists(dirname(__FILE__) . '/../../../wp-load.php')) {
        require_once(dirname(__FILE__) . '/../../../wp-load.php');
    }
}

function boinmd_output_css() {
    $css_file = WP_CONTENT_DIR . '/boinmd-blog-style.css';
    if (file_exists($css_file)) {
        echo '<style id="boinmd-custom-style">';
        readfile($css_file);
        echo '</style>';
    }
}
add_action('wp_head', 'boinmd_output_css', PHP_INT_MAX);
