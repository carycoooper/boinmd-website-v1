<?php
/**
 * Plugin Name: 博音BOINMD风格统一
 * Description: 博客样式匹配官网 boinmd.com.cn
 * Version: 2.0
 */
defined('ABSPATH') || exit;

add_action('wp_head', function() {
    $css_file = WP_CONTENT_DIR . '/boinmd-blog-style.css';
    if (file_exists($css_file)) {
        echo PHP_EOL . '<!-- BOINMD Style Start -->' . PHP_EOL;
        echo '<style id="boinmd-custom-style">' . PHP_EOL;
        readfile($css_file);
        echo PHP_EOL . '</style>' . PHP_EOL;
        echo '<!-- BOINMD Style End -->' . PHP_EOL;
    }
}, PHP_INT_MAX);
