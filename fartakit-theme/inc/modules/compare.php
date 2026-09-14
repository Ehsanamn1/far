<?php
/**
 * ماژول Compare - مقایسه محصولات
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * دکمه Compare در کارت محصول
 * وضعیت فعال با paintCompare (localStorage) ست می‌شود — منبع واحد حقیقت
 */
add_action( 'woocommerce_after_shop_loop_item', 'fartak_compare_button', 25 );
function fartak_compare_button() {
    global $product;
    if ( ! $product ) return;
    echo '<button type="button" class="ft-compare-btn" data-compare-toggle="' . esc_attr( $product->get_id() ) . '" aria-label="مقایسه" title="افزودن به مقایسه" style="position:absolute;top:48px;right:8px;z-index:5;background:rgba(14,22,38,.85);backdrop-filter:blur(8px);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#e8ecf4;border:1px solid rgba(255,255,255,.1);cursor:pointer"><svg class="icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="6" r="3"/><path d="M15 6a9 3 0 0 0-9 3"/><circle cx="19" cy="18" r="3"/><path d="M9 18a9 3 0 0 0 9-3"/></svg></button>';
}

/* هندلرهای قدیمی مقایسه (کوکی/سقف ۴) حذف شدند — سیستم مقایسه واحد در
   theme.js (localStorage) + inc/ajax.php (fartak_compare) اجرا می‌شود. */

/**
 * CSS و JS برای compare modal
 * مودال popup حذف شد — صفحه اختصاصی مقایسه (template-compare + #compare-zone) مسیر اصلی است
 * و دکمه هدر مستقیماً به آن لینک می‌شود؛ مودال قبلی غیرقابل‌دسترس و stale بود.
 */
add_action( 'wp_footer', 'fartak_compare_modal', 30 );
function fartak_compare_modal() {
    // دیگر چیزی رندر نمی‌شود — هوک برای سازگاری حفظ شده است
}
