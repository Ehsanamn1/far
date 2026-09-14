<?php
/**
 * ماژول Quick View - مشاهده سریع محصول
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * افزودن دکمه Quick View به کارت محصول
 */
add_action( 'woocommerce_after_shop_loop_item', 'fartak_quick_view_button', 15 );
function fartak_quick_view_button() {
    global $product;
    if ( ! $product ) return;
    echo '<button type="button" class="btn-ghost icon-btn ft-quick-view-btn" data-quick-view="' . esc_attr( $product->get_id() ) . '" aria-label="مشاهده سریع" title="مشاهده سریع" style="position:absolute;top:8px;left:8px;z-index:5;background:rgba(14,22,38,.85);backdrop-filter:blur(8px);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#e8ecf4;border:1px solid rgba(255,255,255,.1);cursor:pointer"><svg class="icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>';
}

/**
 * هندلر AJAX Quick View
 */
add_action( 'wp_ajax_fartak_quick_view', 'fartak_quick_view_ajax' );
add_action( 'wp_ajax_nopriv_fartak_quick_view', 'fartak_quick_view_ajax' );
function fartak_quick_view_ajax() {
    check_ajax_referer( 'fartak', 'nonce' );

    $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
    if ( ! $product_id ) wp_send_json_error( array( 'message' => 'محصول نامعتبر' ) );

    $product = wc_get_product( $product_id );
    if ( ! $product ) wp_send_json_error( array( 'message' => 'محصول یافت نشد' ) );

    // ردیابی محصول دیده‌شده
    do_action( 'fartak_track_product_view', $product_id );

    ob_start();
    ?>
    <div class="qv-modal-content">
        <div class="qv-image">
            <?php echo wp_get_attachment_image( $product->get_image_id(), 'medium_large', false, array( 'style' => 'width:100%;border-radius:12px' ) ); ?>
        </div>
        <div class="qv-info">
            <span class="chip" style="background:rgba(225,29,42,.1);color:#ff3543;border-color:rgba(225,29,42,.3);padding:4px 10px;border-radius:6px;font-size:11px;display:inline-block;margin-bottom:8px"><?php echo esc_html( fartak_product_cat_label( $product ) ); ?></span>
            <h2 style="margin:0 0 12px;font-size:20px;font-weight:800"><?php echo esc_html( $product->get_name() ); ?></h2>

            <div class="qv-rating" style="margin-bottom:12px">
                <?php
                $rating = $product->get_average_rating();
                if ( $rating > 0 ) {
                    echo '<span style="color:#f59e0b">' . str_repeat( '★', round( $rating ) ) . '</span>';
                    echo '<small style="color:#8b95ab;margin-right:8px">(' . esc_html( $product->get_review_count() ) . ' نظر)</small>';
                }
                ?>
            </div>

            <div class="qv-price price" style="font-size:24px;font-weight:900;margin-bottom:16px">
                <?php
                if ( function_exists( 'fartak_product_is_call' ) && function_exists( 'fartak_has_real_price' ) && ( fartak_product_is_call( $product->get_id() ) || ! fartak_has_real_price( $product ) ) ) {
                    $qv_phone = preg_replace( '/\D/', '', (string) get_option( 'fartak_support_phone', '01732000180' ) );
                    echo '<a class="call-label" href="tel:' . esc_attr( $qv_phone ) . '" style="color:#f97316">' . fartak_icon( 'phone' ) . ' تماس بگیرید</a>';
                } else {
                    echo wp_kses_post( $product->get_price_html() );
                }
                ?>
            </div>

            <div class="qv-excerpt" style="font-size:13px;line-height:1.8;color:#8b95ab;margin-bottom:20px">
                <?php echo wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 30 ); ?>
            </div>

            <div class="qv-actions" style="display:flex;gap:8px;flex-wrap:wrap">
                <?php if ( $product->is_in_stock() && $product->is_purchasable() && $product->is_type( 'simple' ) ) : ?>
                    <button type="button" class="btn-copper btn-lg" data-fartak-add="<?php echo esc_attr( $product->get_id() ); ?>" style="flex:1">
                        <svg class="icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        افزودن به سبد
                    </button>
                <?php endif; ?>
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn-ghost btn-lg" style="flex:1;text-align:center">مشاهده کامل</a>
            </div>

            <div class="qv-meta" style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.08);font-size:12px;color:#8b95ab">
                <?php if ( $product->get_sku() ) : ?>
                    <div style="margin-bottom:4px">کد محصول: <b style="color:#e8ecf4"><?php echo esc_html( $product->get_sku() ); ?></b></div>
                <?php endif; ?>
                <div>موجودی: <?php echo $product->is_in_stock() ? '<b style="color:#46b450">موجود</b>' : '<b style="color:#dc3232">ناموجود</b>'; ?></div>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * CSS و JS
 */
add_action( 'wp_footer', 'fartak_quick_view_assets', 25 );
function fartak_quick_view_assets() {
    ?>
    <style>
    .qv-modal-content{display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:800px}
    .qv-image img{width:100%;border-radius:12px;display:block}
    /* موبایل: تک‌ستون — قبلاً inline دوستونه بود و قابل override نبود */
    @media (max-width:640px){.qv-modal-content{grid-template-columns:1fr;gap:14px}}
    </style>
    <div class="modal-overlay" id="ft-quick-view-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);backdrop-filter:blur(8px);z-index:9998;align-items:center;justify-content:center;padding:20px">
        <div class="modal-box glass" style="max-width:800px;width:100%;border-radius:16px;padding:24px;position:relative;background:rgba(14,22,38,.95);border:1px solid rgba(255,255,255,.1)">
            <button type="button" class="modal-x" onclick="document.getElementById('ft-quick-view-modal').style.display='none'" style="position:absolute;top:12px;left:12px;background:none;border:none;color:#e8ecf4;font-size:24px;cursor:pointer;z-index:2">×</button>
            <div id="ft-quick-view-content"></div>
        </div>
    </div>
    <script>
    (function() {
        var modal = document.getElementById('ft-quick-view-modal');
        var content = document.getElementById('ft-quick-view-content');
        if (!modal || !content) return;

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-quick-view]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var id = btn.getAttribute('data-quick-view');

            modal.style.display = 'flex';
            content.innerHTML = '<div style="text-align:center;padding:60px 20px"><svg class="icon" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#ff3543" stroke-width="2" style="animation:ft-spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg></div>';

            var data = new FormData();
            data.append('action', 'fartak_quick_view');
            data.append('nonce', FARTAK.nonce);
            data.append('product_id', id);

            fetch(FARTAK.ajax, { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        content.innerHTML = res.data.html;
                    } else {
                        content.innerHTML = '<p style="text-align:center;color:#dc3232">' + (res.data.message || 'خطا') + '</p>';
                    }
                })
                .catch(function() { content.innerHTML = '<p style="text-align:center;color:#dc3232">خطا در ارتباط</p>'; });
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') modal.style.display = 'none';
        });
    })();
    </script>
    <?php
}
