<?php
/**
 * Fartak — هدر سایت
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$is_wc      = function_exists( 'WC' );
$cart_count = $is_wc && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
$acct_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
$builder_u  = fartak_tpl_url( 'page-templates/template-builder.php' );
$compare_u  = fartak_tpl_url( 'page-templates/template-compare.php' );
$wholesale_u = fartak_tpl_url( 'page-templates/template-wholesale.php' );
$blog_u     = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
$cats       = fartak_cat_tree();

/* اطلاعات تماس: اولویت با تنظیمات پنل (fartak_support_phone) سپس Customizer */
$ft_phone_raw   = get_option( 'fartak_support_phone', '' );
if ( '' === $ft_phone_raw || ! $ft_phone_raw ) {
        $ft_phone_raw = fartak_mod( 'fartak_phone', fartak_def( 'phone' ) );
}
$ft_phone       = preg_replace( '/\D/', '', (string) $ft_phone_raw );
$ft_phone_disp  = fartak_fa( fartak_phone_display( $ft_phone ) );
$ft_address     = fartak_mod( 'fartak_address', fartak_def( 'address' ) );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'پرش به محتوا', 'fartak' ); ?></a>

<header class="site-header" id="site-header">
        <!-- نوار اطلاع‌رسانی -->
        <div class="topbar">
                <div class="container">
                        <div class="tb-l">
                                <a href="tel:<?php echo esc_attr( $ft_phone ); ?>" dir="ltr" style="display:inline-flex;align-items:center;gap:6px">
                                        <?php echo fartak_icon( 'phone' ); ?><bdi dir="ltr"><?php echo esc_html( $ft_phone_disp ); ?></bdi>
                                </a>
                                <span class="topbar-addr"><?php echo esc_html( $ft_address ); ?></span>
                        </div>
                        <div class="tb-r">
                                <a href="<?php echo esc_url( $wholesale_u ); ?>" class="topbar-addr"><?php echo fartak_icon( 'package' ); ?> <?php esc_html_e( 'خرید عمده', 'fartak' ); ?></a>
                                <a href="<?php echo esc_url( $blog_u ); ?>" class="topbar-blog"><?php echo fartak_icon( 'news' ); ?> <?php esc_html_e( 'وبلاگ تک', 'fartak' ); ?></a>
                        </div>
                </div>
        </div>

        <!-- هدر اصلی -->
        <div class="header-wrap">
                <div class="header-inner">
                        <button type="button" class="btn-ghost icon-btn" data-drawer-open aria-label="<?php esc_attr_e( 'منو', 'fartak' ); ?>" id="menu-toggle"><?php echo fartak_icon( 'menu' ); ?></button>

                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-logo">
                                <?php if ( has_custom_logo() ) : ?>
                                        <?php
                                        $custom_logo_id = get_theme_mod( 'custom_logo' );
                                        echo wp_get_attachment_image( $custom_logo_id, 'full', false, array(
                                                'class' => 'custom-logo',
                                                'alt'   => 'فروشگاه فرتاک',
                                                'style' => 'max-height:40px;width:auto;',
                                        ) );
                                        ?>
                                <?php else : ?>
                                        <span class="logo-mark"><?php echo fartak_icon( 'cpu' ); ?></span>
                                <?php endif; ?>
                                <span class="logo-txt"><b>فروشگاه فرتاک</b><small>FARTAK IT</small></span>
                        </a>

                        <button type="button" class="mobile-search-toggle" id="mobile-search-toggle" aria-label="<?php esc_attr_e( 'جستجو', 'fartak' ); ?>"><?php echo fartak_icon( 'search' ); ?></button>

                        <div class="search-wrap">
                                <div class="search-box">
                                        <span data-search-spinner class="ft-hidden"><?php echo fartak_icon( 'loader', 'spin' ); ?></span>
                                        <span data-search-icon><?php echo fartak_icon( 'search' ); ?></span>
                                        <input type="search" id="fartak-live-search" placeholder="<?php esc_attr_e( 'جستجوی قطعه، برند یا مدل…', 'fartak' ); ?>" autocomplete="off" aria-label="<?php esc_attr_e( 'جستجو', 'fartak' ); ?>">
                                        <button type="button" class="search-close-btn" data-search-close aria-label="<?php esc_attr_e( 'بستن جستجو', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
                                </div>
                                <div class="search-results glass" id="fartak-search-results"></div>
                        </div>

                        <div class="header-actions">
                                <a href="<?php echo esc_url( $builder_u ); ?>" class="btn-copper builder-shortcut"><?php echo fartak_icon( 'wrench' ); ?> <?php esc_html_e( 'اسمبل آنلاین', 'fartak' ); ?></a>
                                <a href="<?php echo esc_url( $compare_u ); ?>" class="btn-ghost icon-btn badge-btn" aria-label="<?php esc_attr_e( 'مقایسه', 'fartak' ); ?>">
                                        <?php echo fartak_icon( 'compare' ); ?>
                                        <span class="count-badge ft-hidden" data-compare-count>۰</span>
                                </a>
                                <a href="<?php echo esc_url( $cart_url ); ?>" class="btn-ghost icon-btn badge-btn" aria-label="<?php esc_attr_e( 'سبد خرید', 'fartak' ); ?>">
                                        <?php echo fartak_icon( 'cart' ); ?>
                                        <?php if ( $cart_count > 0 ) : ?>
                                                <span class="count-badge" data-cart-count><?php echo esc_html( fartak_fa_num( $cart_count ) ); ?></span>
                                        <?php else : ?>
                                                <span class="count-badge ft-hidden" data-cart-count>۰</span>
                                        <?php endif; ?>
                                </a>
                                <a href="<?php echo esc_url( $acct_url ); ?>" class="btn-ghost icon-btn header-profile-btn" aria-label="<?php esc_attr_e( 'پروفایل کاربری', 'fartak' ); ?>" title="<?php esc_attr_e( 'پروفایل', 'fartak' ); ?>">
                                        <?php echo fartak_icon( 'user' ); ?>
                                </a>
                        </div>
                </div>
        </div>

        <!-- نوار دسته‌بندی + مگامنو (بر پایه ساختار واقعی دسته‌های ووکامرس: والد ← فرزندان) -->
        <nav class="catbar" aria-label="<?php esc_attr_e( 'دسته‌بندی‌ها', 'fartak' ); ?>">
                <div class="container">
                        <?php if ( $cats ) : ?>
                        <div class="mega">
                                <button type="button" class="mega-btn"><?php echo fartak_icon( 'menu' ); ?> <?php esc_html_e( 'همه دسته‌بندی‌ها', 'fartak' ); ?> <?php echo fartak_icon( 'chevron-down' ); ?></button>
                                <div class="mega-panel">
                                        <div class="mega-box glass">
                                        <button type="button" class="mega-close" data-mega-close aria-label="بستن منو"><?php echo fartak_icon( 'x' ); ?> بستن و بازگشت</button>
                                                <div class="mega-featured">
                                                        <?php foreach ( array_slice( $cats, 0, 4 ) as $cat ) : ?>
                                                                <a class="mega-feat" href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
                                                                        <img src="<?php echo esc_url( fartak_cat_image( $cat ) ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" loading="lazy">
                                                                        <b><?php echo esc_html( $cat->name ); ?></b>
                                                                </a>
                                                        <?php endforeach; ?>
                                                </div>
                                                <div class="mega-cols">
                                                        <?php foreach ( $cats as $cat ) : ?>
                                                                <div class="mega-col">
                                                                        <h5><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo fartak_icon( fartak_cat_icon( $cat ) ); ?> <?php echo esc_html( $cat->name ); ?></a></h5>
                                                                        <?php if ( ! empty( $cat->children ) ) : ?>
                                                                               <div class="sub-list">
                                                                               <?php foreach ( $cat->children as $sub ) : ?>
                                                                               <a href="<?php echo esc_url( get_term_link( $sub ) ); ?>"><?php echo fartak_icon( fartak_cat_icon( $sub ) ); ?> <?php echo esc_html( $sub->name ); ?></a>
                                                                               <?php endforeach; ?>
                                                                               </div>
                                                                        <?php else : ?>
                                                                               <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'fartak' ); ?></a>
                                                                        <?php endif; ?>
                                                                </div>
                                                        <?php endforeach; ?>
                                                        <div class="mega-col">
                                                                <h5><?php esc_html_e( 'خدمات فرتاک', 'fartak' ); ?></h5>
                                                                <a href="<?php echo esc_url( $builder_u ); ?>"><?php echo fartak_icon( 'wrench' ); ?> <?php esc_html_e( 'اسمبل آنلاین', 'fartak' ); ?></a>
                                                                <a href="<?php echo esc_url( $wholesale_u ); ?>"><?php echo fartak_icon( 'package' ); ?> <?php esc_html_e( 'خرید عمده', 'fartak' ); ?></a>
                                                                <a href="<?php echo esc_url( $blog_u ); ?>"><?php echo fartak_icon( 'news' ); ?> <?php esc_html_e( 'وبلاگ تکنولوژی', 'fartak' ); ?></a>
                                                                <a href="<?php echo esc_url( $compare_u ); ?>"><?php echo fartak_icon( 'compare' ); ?> <?php esc_html_e( 'مقایسه کالا', 'fartak' ); ?></a>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <span class="sep"></span>
                        <?php foreach ( $cats as $cat ) : ?>
                                <div class="cat-item<?php echo empty( $cat->children ) ? '' : ' has-sub'; ?>">
                                        <a class="cat-link" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo fartak_icon( fartak_cat_icon( $cat ) ); ?> <?php echo esc_html( $cat->name ); ?><?php echo empty( $cat->children ) ? '' : ' ' . fartak_icon( 'chevron-down' ); ?></a>
                                        <?php if ( ! empty( $cat->children ) ) : ?>
                                                <div class="cat-drop glass">
                                                        <?php foreach ( $cat->children as $sub ) : ?>
                                                                <a href="<?php echo esc_url( get_term_link( $sub ) ); ?>"><?php echo fartak_icon( fartak_cat_icon( $sub ) ); ?> <?php echo esc_html( $sub->name ); ?><small><?php echo esc_html( fartak_fa_num( $sub->count ) ); ?> کالا</small></a>
                                                        <?php endforeach; ?>
                                                        <a class="cat-drop-all" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
                                                </div>
                                        <?php endif; ?>
                                </div>
                        <?php endforeach; ?>
                        <span class="sep"></span>
                        <?php endif; ?>
                        <a class="builder-link" href="<?php echo esc_url( $builder_u ); ?>"><?php echo fartak_icon( 'wrench' ); ?> <?php esc_html_e( 'اسمبل آنلاین', 'fartak' ); ?></a>
                </div>
        </nav>
</header>

<!-- منوی کشویی موبایل (آکاردئونی بر اساس دسته‌های ووکامرس) -->
<div class="drawer-overlay" data-drawer-close></div>
<aside class="drawer" aria-label="<?php esc_attr_e( 'منوی موبایل', 'fartak' ); ?>">
        <div class="drawer-head">
                <span><?php esc_html_e( 'دسته‌بندی‌ها', 'fartak' ); ?></span>
                <button type="button" class="btn-ghost icon-btn" data-drawer-close aria-label="<?php esc_attr_e( 'بستن', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
        </div>
        <div class="drawer-body">
                <?php if ( $cats ) : foreach ( $cats as $cat ) : ?>
                        <?php if ( ! empty( $cat->children ) ) : ?>
                                <div class="d-group">
                                        <div class="d-row">
                                                <a class="d-parent" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><span class="d-ic"><?php echo fartak_icon( fartak_cat_icon( $cat ) ); ?></span><?php echo esc_html( $cat->name ); ?></a>
                                                <button type="button" class="d-arrow" data-d-toggle aria-label="<?php esc_attr_e( 'باز کردن زیردسته‌ها', 'fartak' ); ?>"><?php echo fartak_icon( 'chevron-down' ); ?></button>
                                        </div>
                                        <div class="d-sub">
                                                <?php foreach ( $cat->children as $sub ) : ?>
                                                        <a href="<?php echo esc_url( get_term_link( $sub ) ); ?>"><span class="d-ic"><?php echo fartak_icon( fartak_cat_icon( $sub ) ); ?></span><?php echo esc_html( $sub->name ); ?></a>
                                                <?php endforeach; ?>
                                        </div>
                                </div>
                        <?php else : ?>
                                <a class="d-parent d-flat" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><span class="d-ic"><?php echo fartak_icon( fartak_cat_icon( $cat ) ); ?></span><?php echo esc_html( $cat->name ); ?></a>
                        <?php endif; ?>
                <?php endforeach; endif; ?>
                <div class="hline"></div>
                <a href="<?php echo esc_url( $shop_url ); ?>"><span class="d-ic"><?php echo fartak_icon( 'grid' ); ?></span><?php esc_html_e( 'فروشگاه', 'fartak' ); ?></a>
                <a href="<?php echo esc_url( $builder_u ); ?>" style="color:var(--copper2)"><span class="d-ic"><?php echo fartak_icon( 'wrench' ); ?></span><?php esc_html_e( 'اسمبل آنلاین', 'fartak' ); ?></a>
                <a href="<?php echo esc_url( $wholesale_u ); ?>"><span class="d-ic"><?php echo fartak_icon( 'package' ); ?></span><?php esc_html_e( 'خرید عمده', 'fartak' ); ?></a>
                <a href="<?php echo esc_url( $blog_u ); ?>"><span class="d-ic"><?php echo fartak_icon( 'news' ); ?></span><?php esc_html_e( 'وبلاگ تکنولوژی', 'fartak' ); ?></a>
        </div>
</aside>

<main id="main">
