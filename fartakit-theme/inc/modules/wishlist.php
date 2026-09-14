<?php
/**
 * ماژول Wishlist - لیست علاقه‌مندی‌ها
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * دکمه Wishlist در کارت محصول
 */
add_action( 'woocommerce_after_shop_loop_item', 'fartak_wishlist_button', 20 );
function fartak_wishlist_button() {
    global $product;
    if ( ! $product ) return;
    $items = fartak_get_wishlist();
    $in = in_array( $product->get_id(), $items );
    echo '<button type="button" class="ft-wishlist-btn' . ( $in ? ' active' : '' ) . '" data-wishlist-toggle="' . esc_attr( $product->get_id() ) . '" aria-label="علاقه‌مندی" title="افزودن به علاقه‌مندی‌ها" style="position:absolute;top:8px;right:8px;z-index:5;background:rgba(14,22,38,.85);backdrop-filter:blur(8px);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:' . ( $in ? '#ff3543' : '#e8ecf4' ) . ';border:1px solid rgba(255,255,255,.1);cursor:pointer"><svg class="icon" viewBox="0 0 24 24" width="18" height="18" fill="' . ( $in ? 'currentColor' : 'none' ) . '" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>';
}

/**
 * دریافت لیست علاقه‌مندی‌ها
 */
function fartak_get_wishlist() {
    if ( is_user_logged_in() ) {
        $items = get_user_meta( get_current_user_id(), '_fartak_wishlist', true );
        return is_array( $items ) ? $items : array();
    }
    // برای کاربر مهمان از cookie
    $cookie = isset( $_COOKIE['fartak_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['fartak_wishlist'] ) ) : '';
    $items = $cookie ? array_filter( array_map( 'absint', explode( ',', $cookie ) ) ) : array();
    return $items;
}

/**
 * ذخیره لیست علاقه‌مندی‌ها
 */
function fartak_set_wishlist( $items ) {
    $items = array_values( array_unique( array_filter( array_map( 'absint', $items ) ) ) );
    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), '_fartak_wishlist', $items );
    } else {
        setcookie( 'fartak_wishlist', implode( ',', $items ), time() + MONTH_IN_SECONDS, '/' );
    }
    return $items;
}

/**
 * AJAX toggle wishlist
 */
add_action( 'wp_ajax_fartak_wishlist_toggle', 'fartak_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_fartak_wishlist_toggle', 'fartak_wishlist_toggle' );
function fartak_wishlist_toggle() {
    check_ajax_referer( 'fartak', 'nonce' );
    $id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    if ( ! $id ) wp_send_json_error();

    $items = fartak_get_wishlist();
    if ( in_array( $id, $items ) ) {
        $items = array_diff( $items, array( $id ) );
        $action = 'removed';
    } else {
        $items[] = $id;
        $action = 'added';
    }
    $items = fartak_set_wishlist( $items );

    wp_send_json_success( array(
        'action' => $action,
        'count'  => count( $items ),
    ) );
}

/**
 * AJAX دریافت شمارش
 */
add_action( 'wp_ajax_fartak_wishlist_count', 'fartak_wishlist_count' );
add_action( 'wp_ajax_nopriv_fartak_wishlist_count', 'fartak_wishlist_count' );
function fartak_wishlist_count() {
    wp_send_json_success( array( 'count' => count( fartak_get_wishlist() ) ) );
}

/**
 * شورت‌کد نمایش wishlist
 */
add_shortcode( 'fartak_wishlist', 'fartak_wishlist_shortcode' );
function fartak_wishlist_shortcode() {
    $items = fartak_get_wishlist();
    if ( empty( $items ) ) {
        return '<div class="card-compact" style="padding:40px;text-align:center"><p>لیست علاقه‌مندی‌های شما خالی است.</p></div>';
    }

    ob_start();
    echo '<div class="products-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">';
    foreach ( $items as $id ) {
        $product = wc_get_product( $id );
        if ( $product && $product->get_status() === 'publish' ) {
            fartak_product_card( $product );
        }
    }
    echo '</div>';
    return ob_get_clean();
}

/**
 * JS برای wishlist
 */
add_action( 'wp_footer', 'fartak_wishlist_js', 20 );
function fartak_wishlist_js() {
    ?>
    <script>
    (function() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-wishlist-toggle]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var id = btn.getAttribute('data-wishlist-toggle');
            var data = new FormData();
            data.append('action', 'fartak_wishlist_toggle');
            data.append('nonce', FARTAK.nonce);
            data.append('product_id', id);
            fetch(FARTAK.ajax, { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        var svg = btn.querySelector('svg');
                        if (res.data.action === 'added') {
                            btn.classList.add('active');
                            btn.style.color = '#ff3543';
                            if (svg) svg.setAttribute('fill', 'currentColor');
                        } else {
                            btn.classList.remove('active');
                            btn.style.color = '#e8ecf4';
                            if (svg) svg.setAttribute('fill', 'none');
                        }
                    }
                });
        });
    })();
    </script>
    <?php
}
