# FartakIT 13.0.0 — Production Audit & Cleanup

## Baseline
This release is derived from the Claude-audited FartakIT theme ZIP supplied by the user. Original branding/content/layout intent is preserved; changes target code quality, WooCommerce safety, responsive behavior, image handling, search, and performance.

## Verified fixes in this pass
- Removed global theme-mod/site-info override filters that could overwrite configured business values.
- Removed global attachment lazy-loading override; WordPress/WooCommerce now own image loading semantics.
- Removed global WooCommerce stock availability mutation.
- Removed global `wc_price`/price separator mutation; price presentation is handled by CSS/normal WooCommerce output.
- Removed forced Contact Form 7 asset loading.
- Kept Shipping controlled by WooCommerce; no shipping-cost override is present.
- Kept Guest Checkout controlled by WooCommerce; no checkout-setting override is present.
- Kept a single AJAX Add-to-Cart registration.
- Product cards now use WooCommerce's native product image API with `woocommerce_thumbnail` and real placeholder fallback.
- Quantity controls use WooCommerce max purchase quantity when a finite maximum exists.
- AJAX Add-to-Cart uses the existing WooCommerce cart/session, validates product/purchase limits, and returns real WooCommerce error notices.
- Smart search normalizes Persian/Arabic digits and common Persian character variants, supports exact Product ID/SKU lookup, and also searches product categories.
- Fixed Live Search payload mismatch by returning the `price` field expected by frontend JS.
- Story image receives an image-error fallback without removing the real `<img>` node; story background also falls back safely.
- Removed fake randomized inquiry price estimation; inquiry modal now states that current price is requested/checked.
- Dashboard sales/order statistics use WooCommerce order lookup tables when available, making the common HPOS path aggregate-based instead of hydrating hundreds of orders.
- Replaced legacy gallery direction/size conflicts with a single coherent responsive gallery layer.
- Removed obsolete mobile cart horizontal scrolling rule and consolidated the final mobile cart behavior.
- Removed old explicit gallery 240/300px caps.
- Removed global deregistration/dequeue behavior that could interfere with third-party WooCommerce/WordPress assets.
- Removed legacy custom-PHP option registration from theme settings/safe-mode bypass.
- Removed generic/global 18-month warranty claims where they contradicted the per-product warranty model; per-product warranty remains sourced from product meta/attribute.

## Static QA
- PHP files checked with `php -l`: PASS (48 files)
- JavaScript checked with `node --check`: PASS (3 files)
- CSS brace balance: PASS
- Forbidden dynamic execution (`eval`, `exec`, `shell_exec`, `system`, `passthru`): PASS (none in theme code; bundled vendor JS excluded from semantic review)
- Destructive activation hooks / `wp_delete_post`: PASS (none found)
- Shipping override / Guest Checkout option mutation: PASS (none found)
- Unbounded `limit => -1`: PASS (none found)
- `stopImmediatePropagation()` in theme-authored JS: PASS (none; only vendor Swiper code was present in bundled dependency)
- Duplicate Add-to-Cart AJAX registration: PASS (single pair of authenticated/unauthenticated hooks)

## Runtime limitations
Static analysis cannot prove behavior of the live site environment. The following require staging/live WooCommerce runtime tests: real product attachments, variations, payment gateways, shipping plugins, cache/CDN layers, Redis/LiteSpeed, and real mobile browsers.
