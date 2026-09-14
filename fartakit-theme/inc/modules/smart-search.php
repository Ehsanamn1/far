<?php
/**
 * Module: Smart Search — جستجوی هوشمند زنده
 *
 * Enhances the existing #fartak-live-search input with:
 *   - Server-side result caching via transients (5 min)
 *   - SKU / title / content matching
 *   - Keyboard navigation (up/down/enter/escape)
 *   - "مشاهده همه نتایج" link and "نتیجه‌ای یافت نشد" empty state
 *
 * Uses the theme's existing CSS classes (.search-results, .sr-item, .sr-list,
 * .sr-empty, .sr-all) so styling stays consistent with the rest of the header.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler: fartak_smart_search
 *
 * GET/POST params: q (search string), nonce.
 * Returns JSON array of {id, name, url, img, cat, price_html, raw}.
 */
add_action( 'wp_ajax_fartak_smart_search', 'fartak_smart_search' );
add_action( 'wp_ajax_nopriv_fartak_smart_search', 'fartak_smart_search' );
function fartak_smart_search() {
    check_ajax_referer( 'fartak', 'nonce' );

    $q = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';

    // تبدیل ارقام فارسی/عربی به لاتین برای جستجوی شناسه و SKU
    $fa_digits = array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩' );
    $en_digits = array( '0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9' );
    $q_norm = str_replace( $fa_digits, $en_digits, $q );
    $q_norm = str_replace( array( 'ي', 'ى', 'ك', '\u{200c}', '\u{00a0}' ), array( 'ی', 'ی', 'ک', ' ', ' ' ), $q_norm );
    $q_norm = preg_replace( '/[\x{2000}-\x{200B}\s]+/u', ' ', $q_norm );
    $q_norm = trim( $q_norm );

    if ( mb_strlen( $q ) < 2 && ! preg_match( '/^\d{1,}$/', $q_norm ) ) {
        wp_send_json( array() );
    }

    $cache_key = 'fartak_ss_' . (int) get_option( 'fartak_search_version', 1 ) . '_' . md5( $q_norm );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        wp_send_json( $cached );
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json( array() );
    }

    // اگر کاربر فقط عدد وارد کرده باشد (شناسه محصول یا SKU): جستجوی هدفمند
    // شناسه دقیق → SKU → عنوان. (کوئری عمومی 's' روی عدد، محتوای توضیحات را هم
    // می‌گرفتی و نتایج بی‌ربط زیاد می‌آمد — این حالت از آن جلوگیری می‌کند)
    $ids     = array();
    $is_num  = (bool) preg_match( '/^\d{1,}$/', $q_norm );
    if ( $is_num ) {
        $pid = absint( $q_norm );
        if ( $pid && 'product' === get_post_type( $pid ) && 'publish' === get_post_status( $pid ) ) {
            $ids[] = $pid;
        }
        $sku_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $q_norm ) : 0;
        if ( $sku_id ) {
            $ids[] = (int) $sku_id;
        }
        global $wpdb;
        $like    = '%' . $wpdb->esc_like( $q_norm ) . '%';
        $title_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND post_title LIKE %s LIMIT 8",
                $like
            )
        );
        $sku_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT pm.post_id FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = '_sku' AND pm.meta_value LIKE %s AND p.post_type = 'product' AND p.post_status = 'publish' LIMIT 8",
                $like
            )
        );
        $ids = array_merge( $ids, array_map( 'intval', $title_ids ), array_map( 'intval', $sku_ids ) );
    } else {
        // جستجوی متنی: عنوان/توضیحات + دسته‌بندی مرتبط.
        $query = new WP_Query(
            array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                's'              => $q_norm,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            )
        );
        $ids = array_map( 'intval', $query->posts );
        $cat_terms = get_terms( array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'search' => $q_norm,
            'number' => 4,
            'fields' => 'ids',
        ) );
        if ( ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) {
            $cat_query = new WP_Query( array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 8,
                'fields' => 'ids',
                'no_found_rows' => true,
                'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array_map( 'intval', $cat_terms ) ) ),
            ) );
            $ids = array_merge( $ids, array_map( 'intval', (array) $cat_query->posts ) );
        }
    }
    $ids = array_slice( array_values( array_unique( array_filter( $ids ) ) ), 0, 8 );

    $out = array();
    foreach ( $ids as $pid ) {
        $product = wc_get_product( $pid );
        if ( ! $product ) {
            continue;
        }
        $img  = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
        $real = function_exists( 'fartak_has_real_price' ) && function_exists( 'fartak_product_is_call' )
            ? ( fartak_has_real_price( $product ) && ! fartak_product_is_call( $product->get_id() ) )
            : ( '' !== $product->get_price() );

        $out[] = array(
            'id'         => $product->get_id(),
            'name'       => $product->get_name(),
            'url'        => get_permalink( $product->get_id() ),
            'img'        => $img ? $img : wc_placeholder_img_src( 'thumbnail' ),
            'cat'        => fartak_product_cat_label( $product ),
            'price_html' => $real ? wp_kses_post( $product->get_price_html() ) : '',
            'raw'        => $real ? (float) $product->get_price() : 0,
            'price'      => $real ? wp_kses_post( $product->get_price_html() ) : 'تماس بگیرید',
        );
    }

    set_transient( $cache_key, $out, 5 * MINUTE_IN_SECONDS );
    wp_send_json( $out );
}

/** Version search cache when products change. */
add_action( 'save_post_product', 'fartak_smart_search_flush', 20, 1 );
add_action( 'woocommerce_update_product', 'fartak_smart_search_flush', 20, 1 );
function fartak_smart_search_flush( $post_id = 0 ) { update_option( 'fartak_search_version', (int) get_option( 'fartak_search_version', 1 ) + 1, false ); }
