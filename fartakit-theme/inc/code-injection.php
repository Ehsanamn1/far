<?php
/**
 * Fartak — تزریق کد سفارشی CSS/JS/PHP از تنظیمات قالب
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * اعمال رنگ‌های سفارشی از پنل تنظیمات به‌عنوان CSS variables
 */
add_action( 'wp_head', 'fartak_custom_colors_css', 5 );
function fartak_custom_colors_css() {
    $primary = fartak_opt( 'color_primary', '#e11d2a' );
    $dark    = fartak_opt( 'color_primary_dark', '#b81622' );
    $light   = fartak_opt( 'color_primary_light', '#ff3543' );
    echo '<style id="fartak-colors">:root{--copper:' . esc_attr( $primary ) . ';--copper2:' . esc_attr( $light ) . ';--copperdeep:' . esc_attr( $dark ) . ';}</style>' . "\n";
}

/**
 * تزریق CSS سفارشی
 */
add_action( 'wp_head', 'fartak_inject_custom_css', 99 );
function fartak_inject_custom_css() {
    $css = fartak_opt( 'custom_css', '' );
    if ( $css ) {
        echo '<style id="fartak-custom-css">' . "\n" . $css . "\n" . '</style>' . "\n";
    }
}

/**
 * تزریق JS در head
 */
add_action( 'wp_head', 'fartak_inject_custom_js_head', 99 );
function fartak_inject_custom_js_head() {
    $js = fartak_opt( 'custom_js_head', '' );
    if ( $js ) {
        echo '<script id="fartak-custom-js-head">' . "\n" . $js . "\n" . '</script>' . "\n";
    }
}

/**
 * تزریق JS در footer
 */
add_action( 'wp_footer', 'fartak_inject_custom_js_footer', 99 );
function fartak_inject_custom_js_footer() {
    $js = fartak_opt( 'custom_js_footer', '' );
    if ( $js ) {
        echo '<script id="fartak-custom-js-footer">' . "\n" . $js . "\n" . '</script>' . "\n";
    }
}

/**
 * اجرای PHP سفارشی — فقط در پیشخوان، فقط برای مدیر، هرگز روی فرانت/AJAX
 * (بلک‌لیست قابل دورزدن است؛ محدود کردن محدوده اجرا تنها سپر واقعی است)
 */
/* اجرای PHP سفارشی عمداً پشتیبانی نمی‌شود. */
/**
 * اتصال با افزونه Fartak Code Injection در صورت نصب
 */
if ( ! function_exists( 'fartak_code_get_snippets' ) ) {
    function fartak_code_get_snippets( $type = 'css' ) {
        return array();
    }
}
