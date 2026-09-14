<?php
/**
 * Fartak — صفحه اصلی
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/stories' );
get_template_part( 'template-parts/home/category-rail' );

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

// دریافت تنظیمات محصولات سفارشی
$sale_ids = get_option( 'fartak_home_sale_ids', '' );
$best_ids = get_option( 'fartak_home_best_ids', '' );
$recent_ids = get_option( 'fartak_home_recent_ids', '' );
$popular_ids = get_option( 'fartak_home_popular_ids', '' );

$sale_title = get_option( 'fartak_home_sale_title', __( 'تخفیف‌های ویژه', 'fartak' ) );
$best_title = get_option( 'fartak_home_best_title', __( 'پرفروش‌ترین‌های فرتاک', 'fartak' ) );
$recent_title = get_option( 'fartak_home_recent_title', __( 'تازه‌رسیده‌ها', 'fartak' ) );
$popular_title = get_option( 'fartak_home_popular_title', __( 'محبوب‌ترین‌ها', 'fartak' ) );

// محصولات تخفیف‌های ویژه
$sale_products = array();
if ( ! empty( $sale_ids ) && class_exists( 'WooCommerce' ) ) {
    $ids = array_filter( array_map( 'absint', explode( ',', $sale_ids ) ) );
    if ( ! empty( $ids ) ) {
        $sale_products = wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 20, 'orderby' => 'post__in' ) );
    }
}
if ( empty( $sale_products ) ) {
    $sale_products = fartak_products( 'sale', 20 );
}

get_template_part(
        'template-parts/home/carousel',
        null,
        array(
                'title'    => $sale_title,
                'subtitle' => __( 'پیشنهادهای محدود این هفته با قیمت ویژه', 'fartak' ),
                'href'     => add_query_arg( 'onsale', '1', $shop_url ),
                'products' => $sale_products,
        )
);

// برندها
get_template_part( 'template-parts/home/extras', null, array( 'part' => 'brands' ) );

// بنر اسمبل
get_template_part( 'template-parts/home/extras', null, array( 'part' => 'builder' ) );

// محصولات پرفروش
$best_products = array();
if ( ! empty( $best_ids ) && class_exists( 'WooCommerce' ) ) {
    $ids = array_filter( array_map( 'absint', explode( ',', $best_ids ) ) );
    if ( ! empty( $ids ) ) {
        $best_products = wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 20, 'orderby' => 'post__in' ) );
    }
}
if ( empty( $best_products ) ) {
    $best_products = fartak_products( 'best', 20 );
}

get_template_part(
        'template-parts/home/carousel',
        null,
        array(
                'title'    => $best_title,
                'subtitle' => __( 'قطعاتی که گیمرهای گرگان بیشتر خریده‌اند', 'fartak' ),
                'href'     => $shop_url,
                'products' => $best_products,
        )
);

// بنرهای تبلیغاتی
get_template_part( 'template-parts/home/extras', null, array( 'part' => 'promos' ) );

// محصولات تازه‌رسیده
$recent_products = array();
if ( ! empty( $recent_ids ) && class_exists( 'WooCommerce' ) ) {
    $ids = array_filter( array_map( 'absint', explode( ',', $recent_ids ) ) );
    if ( ! empty( $ids ) ) {
        $recent_products = wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 20, 'orderby' => 'post__in' ) );
    }
}
if ( empty( $recent_products ) ) {
    $recent_products = fartak_products( 'recent', 20 );
}

get_template_part(
        'template-parts/home/carousel',
        null,
        array(
                'title'    => $recent_title,
                'subtitle' => __( 'جدیدترین سخت‌افزارهای موجود انبار', 'fartak' ),
                'href'     => $shop_url,
                'products' => $recent_products,
        )
);

// محصولات محبوب‌ترین‌ها
$popular_products = array();
if ( ! empty( $popular_ids ) && class_exists( 'WooCommerce' ) ) {
    $ids = array_filter( array_map( 'absint', explode( ',', $popular_ids ) ) );
    if ( ! empty( $ids ) ) {
        $popular_products = wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 20, 'orderby' => 'post__in' ) );
    }
}
if ( empty( $popular_products ) ) {
    $popular_products = wc_get_products( array(
        'status'   => 'publish',
        'limit'    => 20,
        'meta_key' => 'total_sales',
        'orderby'  => 'meta_value_num',
        'order'    => 'DESC',
    ) );
}
// اگه محبوب‌ها خالی بود، از پرفروش‌ها استفاده کن
if ( empty( $popular_products ) ) {
    // fallback متفاوت از پرفروش‌ها (featured سپس recent) تا دو کاروسل یکسان پشت هم نیفتد
    $popular_products = fartak_products( 'featured', 20 );
}
if ( empty( $popular_products ) ) {
    $popular_products = fartak_products( 'recent', 20 );
}

get_template_part(
        'template-parts/home/carousel',
        null,
        array(
                'title'    => $popular_title,
                'subtitle' => __( 'پرطرفدارترین محصولات نزد مشتریان فرتاک', 'fartak' ),
                'href'     => $shop_url,
                'products' => $popular_products,
        )
);

get_footer();
