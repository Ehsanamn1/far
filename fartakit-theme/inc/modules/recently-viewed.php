<?php
/**
 * Module: Recently Viewed Products — محصولات اخیراً دیده‌شده
 *
 * Tracks product views fired via `fartak_track_product_view` action
 * (typically by the quick-view module) and stores the latest 12 product
 * IDs in user_meta for logged-in users and in a cookie for guests.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Maximum number of recently viewed products to keep.
 */
function fartak_rv_limit() {
    return (int) apply_filters( 'fartak_recently_viewed_limit', 12 );
}

/**
 * Get the list of recently viewed product IDs (newest first).
 *
 * @return int[]
 */
function fartak_rv_get_ids() {
    if ( is_user_logged_in() ) {
        $items = get_user_meta( get_current_user_id(), '_fartak_recently_viewed', true );
        $items = is_array( $items ) ? $items : array();
    } else {
        $cookie = isset( $_COOKIE['fartak_recently_viewed'] )
            ? sanitize_text_field( wp_unslash( $_COOKIE['fartak_recently_viewed'] ) )
            : '';
        $items = $cookie
            ? array_filter( array_map( 'absint', explode( ',', $cookie ) ) )
            : array();
    }
    return array_values( array_filter( $items ) );
}

/**
 * Persist the list of recently viewed product IDs.
 *
 * @param int[] $items Array of product IDs (newest first).
 */
function fartak_rv_set_ids( $items ) {
    $items = array_values( array_unique( array_filter( array_map( 'absint', $items ) ) ) );
    $items = array_slice( $items, 0, fartak_rv_limit() );

    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), '_fartak_recently_viewed', $items );
    } else {
        setcookie(
            'fartak_recently_viewed',
            implode( ',', $items ),
            time() + MONTH_IN_SECONDS,
            COOKIEPATH ? COOKIEPATH : '/',
            COOKIE_DOMAIN ? COOKIE_DOMAIN : ''
        );
    }
    return $items;
}

/**
 * Track a product view — wired to `fartak_track_product_view` action.
 *
 * @param int $product_id Product ID being viewed.
 */
add_action( 'fartak_track_product_view', 'fartak_rv_track' );
function fartak_rv_track( $product_id ) {
    $product_id = absint( $product_id );
    if ( ! $product_id ) {
        return;
    }

    // Skip tracking inside the admin or during REST/AJAX calls that aren't frontend.
    if ( is_admin() && ! wp_doing_ajax() ) {
        return;
    }
    if ( wp_doing_cron() ) {
        return;
    }

    // Verify the product still exists.
    $product = wc_get_product( $product_id );
    if ( ! $product || 'publish' !== $product->get_status() ) {
        return;
    }

    $items = fartak_rv_get_ids();

    // Move to front if already present.
    $items = array_diff( $items, array( $product_id ) );
    array_unshift( $items, $product_id );

    fartak_rv_set_ids( $items );
}

/**
 * Render the recently-viewed section.
 *
 * @param int $exclude_id Product ID to exclude (e.g. the current product).
 * @param int $limit       Number of items to show.
 * @return string HTML output (empty when no items).
 */
function fartak_rv_render( $exclude_id = 0, $limit = 5 ) {
    $ids      = fartak_rv_get_ids();
    $exclude  = absint( $exclude_id );
    if ( $exclude ) {
        $ids = array_diff( $ids, array( $exclude ) );
    }
    $ids = array_slice( array_values( $ids ), 0, absint( $limit ) );
    if ( empty( $ids ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="fartak-rv" data-fartak-rv>
        <div class="fartak-rv__head">
            <h3 class="fartak-rv__title"><?php echo fartak_icon( 'clock' ); ?>
                <?php esc_html_e( 'محصولات اخیراً دیده‌شده', 'fartak' ); ?>
            </h3>
            <span class="fartak-rv__count"><?php echo esc_html( fartak_fa_num( count( $ids ) ) ); ?> <?php esc_html_e( 'محصول', 'fartak' ); ?></span>
        </div>
        <div class="fartak-rv__grid">
            <?php
            foreach ( $ids as $pid ) {
                $product = wc_get_product( $pid );
                if ( $product && 'publish' === $product->get_status() ) {
                    fartak_product_card( $product, 'raw' );
                }
            }
            ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode: [fartak_recently_viewed limit="5" exclude_current="1"]
 */
add_shortcode( 'fartak_recently_viewed', 'fartak_rv_shortcode' );
function fartak_rv_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'limit'          => 5,
            'exclude_current' => '1',
        ),
        $atts,
        'fartak_recently_viewed'
    );

    $limit   = absint( $atts['limit'] ) > 0 ? absint( $atts['limit'] ) : 5;
    $exclude = $atts['exclude_current'] === '1' && function_exists( 'is_product' ) && is_product()
        ? get_the_ID()
        : 0;

    $html = fartak_rv_render( $exclude, $limit );
    if ( '' === $html ) {
        return '<div class="fartak-rv-empty" style="padding:24px;text-align:center;color:#8b95ab;font-size:13px;border:1px dashed rgba(255,255,255,.1);border-radius:12px">'
            . esc_html__( 'هنوز محصولی مشاهده نکرده‌اید.', 'fartak' ) . '</div>';
    }
    return $html;
}

/**
 * Auto-add the recently viewed section on the single product page
 * (below the related products block).
 */
add_action( 'woocommerce_after_single_product_summary', 'fartak_rv_single_output', 25 );
function fartak_rv_single_output() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }
    $html = fartak_rv_render( get_the_ID(), 5 );
    if ( $html ) {
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

/**
 * AJAX handler: fartak_recently_viewed_get
 * Returns the rendered HTML for the requesting user.
 */
add_action( 'wp_ajax_fartak_recently_viewed_get', 'fartak_rv_ajax_get' );
add_action( 'wp_ajax_nopriv_fartak_recently_viewed_get', 'fartak_rv_ajax_get' );
function fartak_rv_ajax_get() {
    check_ajax_referer( 'fartak', 'nonce' );

    $exclude = isset( $_POST['exclude'] ) ? absint( $_POST['exclude'] ) : 0;
    $limit   = isset( $_POST['limit'] ) ? max( 1, min( 12, absint( $_POST['limit'] ) ) ) : 5;

    $html = fartak_rv_render( $exclude, $limit );
    if ( '' === $html ) {
        wp_send_json_success( array( 'html' => '', 'empty' => true ) );
    }
    wp_send_json_success( array( 'html' => $html, 'empty' => false ) );
}

/**
 * Minimal CSS/JS inline assets.
 */
add_action( 'wp_footer', 'fartak_rv_assets', 30 );
function fartak_rv_assets() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    ?>
    <style>
    .fartak-rv{margin:32px 0;padding:20px;background:rgba(14,22,38,.55);border:1px solid rgba(255,255,255,.07);border-radius:16px;backdrop-filter:blur(8px)}
    .fartak-rv__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
    .fartak-rv__head .icon{width:18px;height:18px;color:#ff3543;vertical-align:-3px;margin-left:6px}
    .fartak-rv__title{margin:0;font-size:15px;font-weight:800;color:#e8ecf4}
    .fartak-rv__count{font-size:12px;color:#8b95ab;font-weight:600}
    .fartak-rv__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
    .fartak-rv-empty{margin:24px 0}
    @media (max-width:560px){.fartak-rv__grid{grid-template-columns:repeat(2,1fr)}}
    </style>
    <script>
    (function(){
        if (typeof FARTAK === 'undefined' || !FARTAK.ajax) return;
        function refresh(){
            var nodes = document.querySelectorAll('[data-fartak-rv]');
            if (!nodes.length) return;
            var data = new FormData();
            data.append('action','fartak_recently_viewed_get');
            data.append('nonce', FARTAK.nonce);
            data.append('limit', 5);
            var pid = document.querySelector('[data-product-id]');
            if (pid) data.append('exclude', pid.getAttribute('data-product-id'));
            fetch(FARTAK.ajax, {method:'POST', body:data})
                .then(function(r){return r.json();})
                .then(function(res){
                    if (!res || !res.success) return;
                    nodes.forEach(function(n){
                        if (res.data.empty) { n.innerHTML = ''; n.style.display = 'none'; return; }
                        n.innerHTML = res.data.html;
                        n.style.display = '';
                    });
                }).catch(function(){});
        }
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(refresh, 200); });
    })();
    </script>
    <?php
}
