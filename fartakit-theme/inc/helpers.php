<?php
/**
 * Fartak — توابع کمکی، آیکون‌ها و رندر کارت محصول
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

/* آیا این محصول «تماس بگیرید» دارد؟ (حالت سراسری یا چک‌باکس خود محصول) */
function fartak_product_is_call( $product_id ) {
    if ( get_option( 'fartak_inquiry_mode', '0' ) === '1' ) {
        return true;
    }
    if ( '1' === get_post_meta( absint( $product_id ), '_fartak_call_price', true ) ) {
        return true;
    }
    $call_list = array_filter( array_map( 'absint', explode( ',', (string) get_option( 'fartak_call_ids', '' ) ) ) );
    return in_array( absint( $product_id ), $call_list, true );
}

/* ------------------------------------------------------------- آیکون‌ها */
function fartak_icon( $name, $cls = '' ) {
        static $icons = null;
        if ( null === $icons ) {
                $icons = array(
                        'cpu'          => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M15 2v2M15 20v2M9 2v2M9 20v2M2 15h2M2 9h2M20 15h2M20 9h2"/>',
                        'search'       => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
                        'cart'         => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                        'bag'          => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
                        'compare'      => '<circle cx="5" cy="6" r="3"/><path d="M15 6a9 3 0 0 0-9 3"/><circle cx="19" cy="18" r="3"/><path d="M9 18a9 3 0 0 0 9-3"/>',
                        'menu'         => '<path d="M4 6h16M4 12h16M4 18h16"/>',
                        'x'            => '<path d="M18 6 6 18M6 6l12 12"/>',
                        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
                        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
                        'chevron-right'=> '<path d="m9 18 6-6-6-6"/>',
                        'arrow-left'   => '<path d="m12 19-7-7 7-7M19 12H5"/>',
                        'arrow-right'  => '<path d="M5 12h14m-7-7 7 7-7 7"/>',
                        'phone'        => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
                        'shield'       => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
                        'news'         => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0V9"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/>',
                        'cap'          => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
                        'circuit'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M11 9h4a2 2 0 0 0 2-2V3"/><circle cx="14" cy="9" r="1"/><path d="M6 17l4-4"/><circle cx="6" cy="18" r="1"/>',
                        'laptop'       => '<path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/>',
                        'monitor'      => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
                        'keyboard'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 8h.01M12 12h.01M14 8h.01M16 12h.01M18 8h.01M6 8h.01M7 16h10M9 12h.01"/>',
                        'mouse'        => '<rect x="5" y="2" width="14" height="20" rx="7"/><path d="M12 6v4"/>',
                        'box'          => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>',
                        'ram'          => '<path d="M4 5h16a2 2 0 0 1 2 2v3a1 1 0 0 1 0 2v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3a1 1 0 0 1 0-2V7a2 2 0 0 1 2-2Z"/><path d="M7 8v5M12 8v5M17 8v5"/>',
                        'hdd'          => '<path d="M22 12H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><path d="M6 16h.01M10 16h.01"/>',
                        'stack'        => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
                        'plug'         => '<path d="M12 22v-5M9 8V2M15 8V2M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"/>',
                        'headphones'   => '<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
                        'fan'          => '<path d="M10.827 16.379a6.082 6.082 0 0 1-8.618-7.002l5.412 1.45a6.082 6.082 0 0 1 7.002-8.618l-1.45 5.412a6.082 6.082 0 0 1 8.618 7.002l-5.412-1.45a6.082 6.082 0 0 1-7.002 8.618l1.45-5.412Z"/><path d="M12 12v.01"/>',
                        'wrench'       => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
                        'send'         => '<path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4Z"/>',
                        'at'           => '<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/>',
                        'pin'          => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
                        'clock'        => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
                        'truck'        => '<path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M9 18h6"/>',
                        'badge'        => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
                        'flame'        => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
                        'check'        => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
                        'trophy'       => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
                        'coins'        => '<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18M7 6h1v4M16.71 13.88l.7.71-2.82 2.82"/>',
                        'key'          => '<path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/><circle cx="16.5" cy="7.5" r=".5"/>',
                        'copy'         => '<rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
                        'dices'        => '<rect x="3" y="3" width="10" height="10" rx="2"/><rect x="11" y="11" width="10" height="10" rx="2"/><path d="M8 8h.01M16 16h.01"/>',
                        'bot'          => '<path d="M12 8V4H8"/><rect x="4" y="8" width="16" height="12" rx="2"/><path d="M2 14h2M20 14h2M15 13v2M9 13v2"/>',
                        'sparkles'     => '<path d="M12 3l1.9 5.7a2 2 0 0 0 1.3 1.3L21 12l-5.8 1.9a2 2 0 0 0-1.3 1.3L12 21l-1.9-5.8a2 2 0 0 0-1.3-1.3L3 12l5.9-2a2 2 0 0 0 1.3-1.3z"/><path d="M19 3v4M3 19h4"/>',
                        'sliders'      => '<path d="M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4"/>',
                        'trash'        => '<path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/>',
                        'minus'        => '<path d="M5 12h14"/>',
                        'plus'         => '<path d="M5 12h14M12 5v14"/>',
                        'card'         => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
                        'zap'          => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                        'star'         => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
                        'alert'        => '<path d="M21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/>',
                        'info'         => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
                        'clipboard'    => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
                        'package'      => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12M3.3 7l8.7 5 8.7-5"/>',
                        'package-check'=> '<path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 .96.25"/><path d="M3.3 7l8.7 5 8.7-5M12 22V12"/>',
                        'calc'         => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
                        'percent'      => '<path d="M19 5 5 19"/><circle cx="6.5" cy="9" r="2.5"/><circle cx="17.5" cy="15" r="2.5"/>',
                        'calendar'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
                        'user'         => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
                        'grid'         => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
                        'rotate'       => '<path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>',
                        'loader'       => '<path d="M21 12a9 9 0 1 1-6.22-8.56"/>',
                        'gamepad'      => '<path d="M6 12h4M8 10v4M15 13h.01M18 11h.01M17.32 5H6.68a4 4 0 0 0-3.98 3.59C2.6 9.42 2 14.46 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.41-1.41A2 2 0 0 1 9.83 16h4.34a2 2 0 0 1 1.41.59L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.54-.6-6.58-.68-7.26A4 4 0 0 0 17.32 5z"/>',
                        'home'         => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
                        'award'        => '<circle cx="12" cy="8" r="6"/><path d="M15.48 12.89 17 22l-5-3-5 3 1.52-9.11"/>',
                        'party'        => '<path d="M5.8 11.3 2 22l10.7-3.79M4 3h.01M22 8h.01M15 2h.01M22 20h.01M22 2l-2.24.75a2.9 2.9 0 0 0-1.96 3.12v0c.07.86-.11 1.74-.56 2.55l-.27.56a2.11 2.11 0 0 1-1.7 1.03"/><path d="m22 2-2 .6"/>',
                        'camera'       => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
                        'router'       => '<rect x="2" y="14" width="20" height="8" rx="2"/><path d="M6.01 18H6M10.01 18H10M15 10V3"/><path d="m12 6 3-3 3 3"/>',
                        'cam'          => '<circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2"/>',
                        'volume'       => '<path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>',
                        'cpu-cool'     => '<circle cx="12" cy="12" r="2.5"/><path d="M12 13.5c-2.5 0-8 3-8 6.5 5.5 0 8-3.5 8-6.5zM12 13.5c2.5 0 8 3 8 6.5-5.5 0-8-3.5-8-6.5zM10.5 12c0-2.5-3-8-6.5-8 0 5.5 3.5 8 6.5 8zM13.5 12c0-2.5 3-8 6.5-8 0 5.5-3.5 8-6.5 8z"/>',
                        'tower'        => '<rect x="6" y="2" width="9" height="20" rx="2"/><path d="M8.5 5h4M8.5 7.5h4"/><path d="M9.5 11h2"/><circle cx="10.5" cy="18.5" r="1.2"/><path d="M19 6v12a2 2 0 0 1-2 2h-2"/>',
                        'ssd'          => '<rect x="2" y="8" width="15" height="8" rx="2"/><path d="M6 11.5v1M9 11.5v1M12 11.5v1"/><path d="M17 10.5h3a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1h-3z"/>',
                        'mic'          => '<rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v1a7 7 0 0 0 14 0v-1M12 18v4M8 22h8"/>',
                );
        }
        $body = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['zap'];
        return '<svg class="icon ' . esc_attr( $cls ) . '" viewBox="0 0 24 24" aria-hidden="true">' . $body . '</svg>';
}

/* ----------------------------------------------------- اعداد فارسی */
function fartak_fa( $str ) {
        $en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        $fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
        return str_replace( $en, $fa, (string) $str );
}
function fartak_fa_num( $n ) {
        return number_format_i18n( (float) $n );
}

/* ------------------------------------------- آدرس صفحه قالب‌دار (با کش استاتیک — قبلاً در هر فراخوان کوئری می‌زد) */
function fartak_tpl_url( $tpl, $fallback = '/' ) {
        static $cache = array();
        if ( isset( $cache[ $tpl ] ) ) {
                return $cache[ $tpl ];
        }
        $pages = get_posts(
                array(
                        'post_type'      => 'page',
                        'posts_per_page' => 1,
                        'meta_key'       => '_wp_page_template',
                        'meta_value'     => $tpl,
                        'fields'         => 'ids',
                )
        );
        $cache[ $tpl ] = $pages ? get_permalink( $pages[0] ) : home_url( $fallback );
        return $cache[ $tpl ];
}

/* ------------------- آدرس صفحه‌ای که شورت‌کد مشخصی داخل محتوایش هست (مستقل از slug و id) */
function fartak_shortcode_page_url( $tag, $fallback = '' ) {
        static $cache = array();
        if ( isset( $cache[ $tag ] ) ) {
                return $cache[ $tag ];
        }
        $pages = get_posts(
                array(
                        'post_type'      => 'page',
                        'post_status'    => 'publish',
                        'posts_per_page' => 1,
                        's'              => '[' . $tag,
                        'fields'         => 'ids',
                )
        );
        $cache[ $tag ] = $pages ? get_permalink( $pages[0] ) : ( $fallback ? home_url( $fallback ) : '' );
        return $cache[ $tag ];
}

/* ------------------------------------------- دسته‌های محصول مرتب */
function fartak_cat_icon( $term ) {
        /* اسلاگ‌های دسته‌ها در این سایت فارسی (percent-encoded) هستن؛ پس تطبیق با «نام» انجام می‌شود */
        $name = '';
        if ( is_object( $term ) && isset( $term->name ) ) {
                $name = (string) $term->name;
        } elseif ( is_string( $term ) ) {
                $name = $term;
        }
        $name = trim( urldecode( $name ) );
        $map  = array(
                'فن پردازنده' => 'fan', 'فن کیس' => 'fan', 'خنک' => 'fan',
                'پاوربانک' => 'plug', 'پاور' => 'plug', 'منبع تغذیه' => 'plug',
                'اس اس دی' => 'ssd', 'اچ دی دی' => 'hdd', 'هارد' => 'hdd',
                'کارت گرافیک' => 'circuit', 'گرافیک' => 'circuit',
                'پردازنده' => 'cpu', 'مادربرد' => 'stack', 'رم' => 'ram',
                'اسمبل' => 'zap', 'کیس' => 'tower',
                'مانیتور' => 'monitor', 'نمایشگر' => 'monitor', 'استند' => 'monitor',
                'کیبورد' => 'keyboard', 'صفحه‌کلید' => 'keyboard', 'ماوس' => 'mouse',
                'هدفون' => 'headphones', 'هدست' => 'headphones', 'اسپیکر' => 'volume', 'بلندگو' => 'volume', 'صدا' => 'volume', 'اسپیکر بلوتوثی' => 'volume',
                'دسته بازی' => 'gamepad', 'بازی' => 'gamepad',
                'وب کم' => 'camera', 'دوربین' => 'camera', 'استریم' => 'mic',
                'سوییچ' => 'router', 'مودم' => 'router', 'روتر' => 'router', 'شبکه' => 'router',
                'فلش' => 'copy', 'کابل' => 'plug', 'هاب' => 'grid',
                'کیف' => 'bag', 'کاور' => 'bag',
                'لپ تاپ' => 'laptop', 'لپ‌تاپ' => 'laptop',
                'قطعات' => 'circuit', 'لوازم' => 'grid', 'متفرقه' => 'box',
        );
        foreach ( $map as $needle => $icon ) {
                if ( false !== mb_strpos( $name, $needle ) ) {
                        return $icon;
                }
        }
        return 'cpu';
}

/* آیا محصول «قیمت واقعی» دارد؟ (قیمت‌های صفر/نیمه‌تعامل وارداتی، تماس‌گیری محسوب می‌شوند) */
function fartak_has_real_price( $product ) {
        if ( ! $product || ! is_object( $product ) ) {
                return false;
        }
        $p = $product->get_price();
        if ( '' === $p || null === $p ) {
                return false;
        }
        return (float) $p >= 1000;
}

/* شماره تماس قالب‌بندی‌شده برای نمایش (همیشه با bdi/dir=ltr چاپ شود) */
function fartak_phone_display( $raw = null ) {
        if ( null === $raw || '' === $raw ) {
                $raw = get_option( 'fartak_support_phone', '01732000180' );
                if ( '' === $raw ) {
                        $raw = fartak_mod( 'fartak_phone', fartak_def( 'phone' ) );
                }
        }
        $digits = preg_replace( '/\D/', '', (string) $raw );
        if ( 11 === strlen( $digits ) ) {
                return substr( $digits, 0, 3 ) . '-' . substr( $digits, 3 );
        }
        return $digits;
}

/* درخت دسته‌بندی‌ها: والد‌ها (به ترتیب منوی فرتاک) + فرزندانشان */
function fartak_cat_tree( $hide_empty_parents = false ) {
        if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
                return array();
        }
        $parents = get_terms(
                array(
                        'taxonomy'   => 'product_cat',
                        'parent'     => 0,
                        'hide_empty' => $hide_empty_parents,
                        'orderby'    => 'name',
                )
        );
        if ( is_wp_error( $parents ) || empty( $parents ) ) {
                return array();
        }
        $preferred = array( 'قطعات کامپیوتر', 'لوازم جانبی', 'تجهیزات شبکه', 'لپ تاپ', 'کیس های اسمبل شده' );
        usort(
                $parents,
                function ( $a, $b ) use ( $preferred ) {
                        $ia = array_search( $a->name, $preferred, true );
                        $ib = array_search( $b->name, $preferred, true );
                        $ia = false === $ia ? 999 : $ia;
                        $ib = false === $ib ? 999 : $ib;
                        return $ia - $ib;
                }
        );
        $tree = array();
        foreach ( $parents as $p ) {
                $children = get_terms(
                        array(
                                'taxonomy'   => 'product_cat',
                                'parent'     => $p->term_id,
                                'hide_empty' => false,
                                'orderby'    => 'name',
                        )
                );
                if ( is_wp_error( $children ) ) {
                        $children = array();
                }
                $p->children = $children;
                $tree[]      = $p;
        }
        return $tree;
}

/* دسته‌های «پرطرفدار» صفحه اصلی — دقیقاً همان هشت دسته‌ی سایت مرجع (فقط آیکون) */
function fartak_popular_cats() {
        if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
                return array();
        }
        $terms = get_terms(
                array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'number'     => 60,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                )
        );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
                return array();
        }
        $out = array();
        foreach ( $terms as $t ) {
                if ( 0 !== (int) $t->parent ) {
                        $out[] = $t;
                        if ( 8 === count( $out ) ) {
                                break;
                        }
                }
        }
        if ( count( $out ) < 8 ) {
                foreach ( $terms as $t ) {
                        if ( 0 === (int) $t->parent ) {
                                $found = false;
                                foreach ( $out as $o ) {
                                        if ( $o->term_id === $t->term_id ) {
                                                $found = true;
                                                break;
                                        }
                                }
                                if ( ! $found ) {
                                        $out[] = $t;
                                        if ( 8 === count( $out ) ) {
                                                break;
                                        }
                                }
                        }
                }
        }
        return $out;
}

/* ------------------------------------------- کوئری محصولات ووکامرس */
function fartak_products( $type = 'best', $limit = 10 ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
                return array();
        }
        $key = 'fartak_q_' . $type . '_' . $limit;
        $cached = get_transient( $key );
        if ( is_array( $cached ) ) {
                return $cached;
        }
        $args = array( 'status' => 'publish', 'limit' => $limit );
        switch ( $type ) {
                case 'sale':
                        // روش مستقیم - کوئری محصولات با _sale_price
                        // (قبلاً transient هسته ووکامرس در هر بازدید حذف می‌شد — کش هسته دست‌نخورده ماند)
                        $args['meta_query'] = array(
                                array(
                                        'key'     => '_sale_price',
                                        'value'   => 0,
                                        'compare' => '>',
                                        'type'    => 'numeric',
                                ),
                        );
                        $args['orderby'] = 'meta_value_num';
                        $args['order']   = 'DESC';
                        break;
                case 'featured':
                        $args['featured'] = true;
                        break;
                case 'recent':
                        $args['orderby'] = 'date';
                        $args['order']   = 'DESC';
                        break;
                default:
                        $args['orderby']  = 'meta_value_num';
                        $args['meta_key'] = 'total_sales';
                        $args['order']    = 'DESC';
        }
        $products = wc_get_products( $args );
        set_transient( $key, $products, 10 * MINUTE_IN_SECONDS );
        return $products;
}

/* پاک‌سازی کش کوئری‌ها با هر تغییر محصول */
add_action( 'save_post_product', 'fartak_flush_product_cache' );
add_action( 'woocommerce_update_product', 'fartak_flush_product_cache' );
function fartak_flush_product_cache() {
        foreach ( array( 'sale', 'featured', 'recent', 'best' ) as $t ) {
                foreach ( array( 6, 8, 10, 12, 14 ) as $l ) {
                        delete_transient( 'fartak_q_' . $t . '_' . $l );
                }
        }
        delete_transient( 'fartak_admin_product_list' );
}

/* ------------------------------------------- درصد تخفیف محصول */
function fartak_sale_percent( $product ) {
        if ( ! $product->is_on_sale() ) {
                return 0;
        }
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();
        if ( $product->is_type( 'variable' ) ) {
                $regular = (float) $product->get_variation_regular_price( 'max', true );
                $sale    = (float) $product->get_variation_sale_price( 'min', true );
        }
        if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
                return 0;
        }
        return (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
}

/* ------------------------------------------- برچسب دسته محصول */
function fartak_product_cat_label( $product ) {
        static $cache = array();
        $pid = $product ? $product->get_id() : 0;
        if ( isset( $cache[ $pid ] ) ) { return $cache[ $pid ]; }
        $terms = get_the_terms( $pid, 'product_cat' );
        if ( $terms && ! is_wp_error( $terms ) ) {
                return $cache[ $pid ] = $terms[0]->name;
        }
        return $cache[ $pid ] = '';
}

/* ------------------------------------------------------------ کارت محصول */
function fartak_product_card( $product, $mode = 'loop' ) {
        if ( ! $product || ! is_object( $product ) ) {
                return;
        }
        $id        = $product->get_id();
        $permalink = get_permalink( $id );
        $title     = $product->get_name();
        $has_price = fartak_has_real_price( $product );
        $in_stock  = $product->is_in_stock();
        $off       = fartak_sale_percent( $product );
        $sold      = (int) $product->get_total_sales();
        $call_mode = fartak_product_is_call( $id ) || ! $has_price;
        $phone     = get_option( 'fartak_support_phone', '01732000180' );
        $warranty  = function_exists( 'fartak_get_warranty' ) ? fartak_get_warranty( $id ) : '';

        $image_attr = array( 'class' => 'main', 'alt' => $title, 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 46vw, (max-width: 1199px) 30vw, 260px' );
        $image_id = (int) $product->get_image_id();
        $main = $image_id ? wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, $image_attr ) : '';
        if ( ! $main && function_exists( 'wc_placeholder_img' ) ) {
                $main = wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'main', 'alt' => $title, 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 46vw, (max-width: 1199px) 30vw, 260px' ) );
        }
        $hover = '';
        $gall  = $product->get_gallery_image_ids();
        if ( $gall ) {
                $hover = wp_get_attachment_image( (int) $gall[0], 'woocommerce_thumbnail', false, array( 'class' => 'hover', 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '', 'sizes' => '(max-width: 767px) 46vw, (max-width: 1199px) 30vw, 260px' ) );
        }
        $max_qty = (int) $product->get_max_purchase_quantity();
        if ( $max_qty < 0 ) { $max_qty = ''; }

        ob_start();
        ?>
        <article class="ft-card rgb-frame">
                <a href="<?php echo esc_url( $permalink ); ?>" class="img-box">
                        <?php echo $main; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                        <?php echo $hover; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                        <?php if ( $off > 0 ) : ?>
                                <span class="badge-sale"><?php echo fartak_icon( 'flame' ); ?> <?php echo esc_html( fartak_fa_num( $off ) ); ?>٪</span>
                        <?php endif; ?>
                </a>
                <div class="card-actions">
                        <button type="button" class="mini-btn" data-compare-toggle="<?php echo esc_attr( $id ); ?>" aria-label="<?php esc_attr_e( 'مقایسه', 'fartak' ); ?>"><?php echo fartak_icon( 'compare' ); ?></button>
                </div>
                <div class="body">
                        <span class="cat"><?php echo esc_html( fartak_product_cat_label( $product ) ); ?></span>
                        <h3 class="product-title clamp2 ft-card-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                        <?php if ( $warranty ) : ?>
                                <span class="card-warranty"><?php echo fartak_icon( 'shield' ); ?> <?php echo esc_html( $warranty ); ?></span>
                        <?php endif; ?>

                        <div class="foot">
                                <?php if ( $call_mode ) : ?>
                                        <div class="price-row">
                                                <div class="price-box"><span class="price call-label"><?php echo fartak_icon( 'phone' ); ?> <?php esc_html_e( 'تماس بگیرید', 'fartak' ); ?></span></div>
                                        </div>
                                        <a class="btn-copper card-btn call-btn" href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo fartak_icon( 'phone' ); ?> <span class="call-pulse"></span> <?php esc_html_e( 'تماس بگیرید', 'fartak' ); ?></a>
                                <?php elseif ( $has_price ) : ?>
                                        <div class="price-row">
                                                <div class="price-box"><span class="price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div>
                                                <?php if ( $sold > 0 ) : ?>
                                                        <span class="sold-note"><?php echo esc_html( fartak_fa_num( $sold ) ); ?>+ <?php esc_html_e( 'خرید', 'fartak' ); ?></span>
                                                <?php endif; ?>
                                        </div>
                                        <?php if ( $product->is_type( 'simple' ) && $in_stock && $product->is_purchasable() ) : ?>
                                                <div class="ft-buy-row">
                                                        <div class="ft-qty" data-qty-control>
                                                                <button type="button" class="ft-qty-btn" data-qty-dec aria-label="کاهش تعداد">−</button>
                                                                <input class="ft-qty-input" type="number" min="1" step="1" value="1"<?php echo '' !== $max_qty ? ' max="' . esc_attr( $max_qty ) . '"' : ''; ?> inputmode="numeric" data-qty-input>
                                                                <button type="button" class="ft-qty-btn" data-qty-inc aria-label="افزایش تعداد">+</button>
                                                        </div>
                                                        <button type="button" class="btn-copper card-btn" data-fartak-add="<?php echo esc_attr( $id ); ?>"><?php echo fartak_icon( 'cart' ); ?> <span><?php esc_html_e( 'افزودن به سبد', 'fartak' ); ?></span></button>
                                                </div>
                                        <?php elseif ( $in_stock ) : ?>
                                                <a class="btn-copper card-btn" href="<?php echo esc_url( $permalink ); ?>"><?php echo fartak_icon( 'sliders' ); ?> <?php esc_html_e( 'انتخاب گزینه‌ها', 'fartak' ); ?></a>
                                        <?php else : ?>
                                                <button type="button" class="btn-copper card-btn" disabled><?php esc_html_e( 'ناموجود', 'fartak' ); ?></button>
                                        <?php endif; ?>
                                <?php else : ?>
                                        <div class="price-box"><span class="price call-label"><?php echo fartak_icon( 'phone' ); ?> <?php esc_html_e( 'تماس بگیرید', 'fartak' ); ?></span></div>
                                        <a class="btn-copper card-btn inq call-btn" href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo fartak_icon( 'phone' ); ?> <span class="call-pulse"></span> <?php esc_html_e( 'تماس بگیرید', 'fartak' ); ?></a>
                                <?php endif; ?>
                        </div>
                </div>
                <?php
                /* هوک استاندارد ووکامرس — دکمه‌های ماژول‌ها (quick-view/wishlist/compare) به همین هوک وصل‌اند
                   و قبلاً چون هیچ‌جا fire نمی‌شد، هرگز رندر نمی‌شدند */
                do_action( 'woocommerce_after_shop_loop_item' );
                ?>
        </article>
        <?php
        $inner = ob_get_clean();

        if ( 'slide' === $mode ) {
                echo '<div class="swiper-slide ft-slide">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
        } elseif ( 'loop' === $mode ) {
                echo '<div class="ft-card-slot">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
        } else {
                echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput
        }
}

/* کارت داخل حلقه‌ی ووکامرس */
function fartak_woo_card_li( $product ) {
        global $product;
        ob_start();
        echo '<li ';
        wc_product_class( 'ft-loop-item', $product );
        echo '>';
        fartak_product_card( $product, 'raw' );
        echo '</li>';
        echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput
}

/* ---------------------------------------------- برآورد زمان مطالعه */
function fartak_reading_minutes( $post ) {
        $chars = mb_strlen( wp_strip_all_tags( $post->post_content ) );
        return max( 3, (int) round( $chars / 900 ) );
}

/* ---------------------------------------------- دسته‌های ووکامرس سطح اول */
function fartak_top_cats( $limit = 12 ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
                return array();
        }
        $terms = get_terms(
                array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'number'     => $limit,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                )
        );
        return is_wp_error( $terms ) ? array() : $terms;
}

function fartak_cat_image( $term ) {
        $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
        return $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
}

/* ---------------------------------------------- فرم تماس با ما (شورت‌کد) */
add_shortcode( 'fartak_contact', 'fartak_contact_shortcode' );
function fartak_contact_shortcode() {
        $phone = get_option( 'fartak_support_phone', '01732000180' );
        ob_start();
        ?>
        <div class="fartak-contact-wrap">
                <div class="fartak-contact-info card-compact">
                        <h2 class="ct-title"><?php echo fartak_icon( 'phone' ); ?> <?php esc_html_e( 'راه‌های ارتباط با ما', 'fartak' ); ?></h2>
                        <div class="ct-row">
                                <span class="ct-ic"><?php echo fartak_icon( 'phone' ); ?></span>
                                <div><small><?php esc_html_e( 'تلفن پشتیبانی', 'fartak' ); ?></small><b><a href="tel:<?php echo esc_attr( $phone ); ?>" dir="ltr"><?php echo esc_html( $phone ); ?></a></b></div>
                        </div>
                        <div class="ct-row">
                                <span class="ct-ic"><?php echo fartak_icon( 'clock' ); ?></span>
                                <div><small><?php esc_html_e( 'ساعت پاسخگویی', 'fartak' ); ?></small><b><?php esc_html_e( 'شنبه تا پنجشنبه — ۹ صبح تا ۸ شب', 'fartak' ); ?></b></div>
                        </div>
                        <div class="ct-row">
                                <span class="ct-ic"><?php echo fartak_icon( 'check' ); ?></span>
                                <div><small><?php esc_html_e( 'پاسخ به تیکت‌ها', 'fartak' ); ?></small><b><?php esc_html_e( 'حداکثر تا ۲۴ ساعت کاری', 'fartak' ); ?></b></div>
                        </div>
                </div>
                <div class="fartak-contact-form card-compact">
                        <h2 class="ct-title"><?php echo fartak_icon( 'clipboard' ); ?> <?php esc_html_e( 'ارسال پیام', 'fartak' ); ?></h2>
                        <form id="fartak-contact-form" novalidate>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'نام و نام خانوادگی *', 'fartak' ); ?></span>
                                        <input type="text" name="name" class="input-dark" autocomplete="name" required>
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'شماره تماس *', 'fartak' ); ?></span>
                                        <input type="tel" name="phone" class="input-dark" dir="ltr" placeholder="09xxxxxxxxx" autocomplete="tel" required>
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'موضوع پیام', 'fartak' ); ?></span>
                                        <input type="text" name="product_name" class="input-dark" placeholder="<?php esc_attr_e( 'مثال: پیگیری سفارش، سوال فنی…', 'fartak' ); ?>">
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'متن پیام *', 'fartak' ); ?></span>
                                        <textarea name="note" rows="5" class="input-dark" required></textarea>
                                </label>
                                <input type="text" name="website" class="ct-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                                <button type="submit" class="btn-copper" id="fartak-contact-btn"><?php echo fartak_icon( 'check' ); ?> <?php esc_html_e( 'ارسال پیام', 'fartak' ); ?></button>
                                <div class="ct-msg ft-hidden" id="fartak-contact-msg" role="alert"></div>
                        </form>
                </div>
        </div>
        <?php
        return ob_get_clean();
}

/* ---------------------------------------------- فرم درخواست خرید عمده (شورت‌کد) */
add_shortcode( 'fartak_wholesale', 'fartak_wholesale_shortcode' );
function fartak_wholesale_shortcode() {
        $phone = get_option( 'fartak_support_phone', '01732000180' );
        ob_start();
        ?>
        <div class="fartak-wholesale-wrap">
                <div class="fartak-wholesale-info card-compact">
                        <h2 class="ct-title"><?php echo fartak_icon( 'box' ); ?> <?php esc_html_e( 'خرید عمده و سازمانی', 'fartak' ); ?></h2>
                        <p class="ws-hint"><?php esc_html_e( 'برای خرید عمده قطعات و کالای دیجیتال با تخفیف ویژه، فرم زیر را تکمیل کنید. کارشناسان فروش عمده در سریع‌ترین زمان با شما تماس می‌گیرند و لیست قیمت اختصاصی ارائه می‌دهند.', 'fartak' ); ?></p>
                        <div class="ct-row">
                                <span class="ct-ic"><?php echo fartak_icon( 'phone' ); ?></span>
                                <div><small><?php esc_html_e( 'تماس مستقیم با فروش عمده', 'fartak' ); ?></small><b><a href="tel:<?php echo esc_attr( $phone ); ?>" dir="ltr"><?php echo esc_html( $phone ); ?></a></b></div>
                        </div>
                        <div class="ct-row">
                                <span class="ct-ic"><?php echo fartak_icon( 'badge' ); ?></span>
                                <div><small><?php esc_html_e( 'مزیت‌ها', 'fartak' ); ?></small><b><?php esc_html_e( 'تخفیف پلکانی، فاکتور رسمی، ارسال هماهنگ با سازمان', 'fartak' ); ?></b></div>
                        </div>
                </div>
                <div class="fartak-wholesale-form card-compact">
                        <h2 class="ct-title"><?php echo fartak_icon( 'clipboard' ); ?> <?php esc_html_e( 'فرم درخواست خرید عمده', 'fartak' ); ?></h2>
                        <form id="fartak-wholesale-form" novalidate>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'نام شرکت / فروشگاه *', 'fartak' ); ?></span>
                                        <input type="text" name="company" class="input-dark" required>
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'نام رابط *', 'fartak' ); ?></span>
                                        <input type="text" name="person" class="input-dark" required>
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'شماره موبایل *', 'fartak' ); ?></span>
                                        <input type="tel" name="phone" class="input-dark" dir="ltr" placeholder="09xxxxxxxxx" required>
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'کالا / محصولات مورد نیاز', 'fartak' ); ?></span>
                                        <input type="text" name="product" class="input-dark" placeholder="<?php esc_attr_e( 'مثال: ۲۰ عدد SSD نسخه ۲۵۶GB برند PATRIOT', 'fartak' ); ?>">
                                </label>
                                <label class="ct-field">
                                        <span><?php esc_html_e( 'تعداد تقریبی', 'fartak' ); ?></span>
                                        <input type="number" name="quantity" class="input-dark" dir="ltr" min="1" step="1" placeholder="20">
                                </label>
                                <button type="submit" class="btn-copper" id="fartak-wholesale-btn"><?php echo fartak_icon( 'send' ); ?> <?php esc_html_e( 'ثبت درخواست', 'fartak' ); ?></button>
                                <div class="ct-msg ft-hidden" id="fartak-wholesale-msg" role="alert"></div>
                        </form>
                </div>
        </div>
        <?php
        return ob_get_clean();
}

/* ---------------------------------------------- سوالات متداول (شورت‌کد) */
add_shortcode( 'fartak_faq', 'fartak_faq_shortcode' );
function fartak_faq_shortcode() {
        $qa = json_decode( (string) get_option( 'fartak_ai_qa', '' ), true );
        if ( ! is_array( $qa ) || empty( $qa ) ) {
                $qa = array(
                        array( __( 'چطور سفارشم را پیگیری کنم؟', 'fartak' ), __( 'پس از ثبت سفارش، کد پیگیری برایتان پیامک می‌شود. همچنین می‌توانید از صفحه «حساب کاربری» بخش سفارش‌ها، وضعیت لحظه‌ای سفارش را ببینید.', 'fartak' ) ),
                        array( __( 'گارانتی محصولات چگونه است؟', 'fartak' ), __( 'گارانتی هر کالا در صفحه همان محصول درج شده است. برای اطلاع از شرایط دقیق گارانتی، مشخصات همان کالا را بررسی کنید.', 'fartak' ) ),
                        array( __( 'امکان خرید اقساطی وجود دارد؟', 'fartak' ), __( 'بله! طرح دانشجویی و خرید اقساطی با پیش‌پرداخت دلخواه فعال است. از صفحه «طرح دانشجویی» مبلغ قسط را محاسبه و درخواست دهید.', 'fartak' ) ),
                        array( __( 'ارسال به شهرستان چند روز طول می‌کشد؟', 'fartak' ), __( 'سفارش‌های شهرستان بین ۲ تا ۴ روز کاری با پست پیشتاز / تیپاکس ارسال می‌شوند و کد رهگیری پیامک می‌گردد.', 'fartak' ) ),
                        array( __( 'برای خرید عمده تخفیف دارید؟', 'fartak' ), __( 'بله، خرید عمده دارای تخفیف پلکانی و فاکتور رسمی است. از صفحه «خرید عمده» درخواست خود را ثبت کنید تا لیست قیمت اختصاصی دریافت کنید.', 'fartak' ) ),
                );
        }
        ob_start();
        ?>
        <div class="fartak-faq-list">
                <?php foreach ( $qa as $pair ) : ?>
                        <?php if ( empty( $pair[0] ) || empty( $pair[1] ) ) { continue; } ?>
                        <details class="faq-item">
                                <summary class="faq-q"><?php echo fartak_icon( 'chevron-down' ); ?> <span><?php echo esc_html( $pair[0] ); ?></span></summary>
                                <div class="faq-a"><?php echo esc_html( $pair[1] ); ?></div>
                        </details>
                <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
}
