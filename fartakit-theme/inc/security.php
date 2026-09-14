<?php
/**
 * Fartak Security - فیلتر مقادیر صحیح + امنیت
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* اطلاعات کسب‌وکار از تنظیمات اصلی قالب/وردپرس خوانده می‌شوند؛ هیچ override سراسری انجام نمی‌شود. */

/* ============================================================
   امنیت پایه
   ============================================================ */

remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* حذف ver= از CSS/JS حذف شد — این فیلتر کش-باستینگ (FARTAK_VER) را می‌شکست و بعد از هر
   آپدیت قالب، مرورگر/CDN فایل قدیمی کش‌شده را سرو می‌کرد. نسخه‌ها حالا سر جایشان هستند. */

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* ============================================================
   بهینه‌سازی
   ============================================================ */

/* مدیریت loading تصویر به WordPress/WooCommerce واگذار شده است؛ از override سراسری پرهیز می‌شود. */

/* preconnect فونت در functions.php چاپ می‌شود — نسخه تکراری اینجا حذف شد */

add_filter( 'script_loader_tag', 'fartak_defer_scripts', 10, 3 );
function fartak_defer_scripts( $tag, $handle, $src ) {
    $defer = array( 'fartak-theme', 'fartak-swiper' );
    if ( in_array( $handle, $defer, true ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }
    return $tag;
}

/* Cacheها باید فقط توسط owner هر ماژول invalidate شوند. */

/* ============================================================
   Safe Mode خودکار - بدون set_error_handler خطرناک
   ============================================================ */

