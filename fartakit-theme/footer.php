<?php
/**
 * Fartak — فوتر سایت + ویجت‌های شناور + داک موبایل
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
$acct_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
$builder_u  = fartak_tpl_url( 'page-templates/template-builder.php' );
$compare_u  = fartak_tpl_url( 'page-templates/template-compare.php' );
$wholesale_u = fartak_tpl_url( 'page-templates/template-wholesale.php' );
$blog_u     = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
$cats       = fartak_cat_tree();
$telegram   = fartak_mod( 'fartak_telegram', fartak_def( 'telegram' ) );
$instagram  = fartak_mod( 'fartak_instagram', fartak_def( 'instagram' ) );

/* اطلاعات تماس: اولویت با پنل (fartak_support_phone) سپس Customizer — قبلاً هاردکد بود */
$ft_phone_raw  = get_option( 'fartak_support_phone', '' );
if ( '' === $ft_phone_raw || ! $ft_phone_raw ) {
        $ft_phone_raw = fartak_mod( 'fartak_phone', fartak_def( 'phone' ) );
}
$ft_phone      = preg_replace( '/\D/', '', (string) $ft_phone_raw );
$ft_phone_disp = fartak_fa( fartak_phone_display( $ft_phone ) );
$ft_email      = fartak_mod( 'fartak_email', fartak_def( 'email' ) );
$ft_address    = fartak_mod( 'fartak_address', fartak_def( 'address' ) );
/* سال کپی‌رایت شمسی — داینامیک (قبلاً ۱۴۰۵ ثابت بود) */
$ft_gy = (int) wp_date( 'Y' );
$ft_gm = (int) wp_date( 'n' );
$ft_year = function_exists( 'jdate' ) ? (int) jdate( 'Y' ) : ( $ft_gm >= 4 ? $ft_gy - 621 : $ft_gy - 622 );
?>
</main><!-- #main -->

<footer class="site-footer">
        <div class="container">
                <!-- ردیف اعتماد -->
                <div class="trust-row">
                        <div class="trust card-compact"><span class="t-ic"><?php echo fartak_icon( 'shield' ); ?></span><span><b><?php esc_html_e( 'گارانتی معتبر', 'fartak' ); ?></b><small><?php esc_html_e( 'شرکتی و رسمی', 'fartak' ); ?></small></span></div>
                        <div class="trust card-compact"><span class="t-ic"><?php echo fartak_icon( 'headphones' ); ?></span><span><b><?php esc_html_e( 'پشتیبانی ۲۴/۷', 'fartak' ); ?></b><small><?php esc_html_e( 'پاسخگویی شبانه‌روزی', 'fartak' ); ?></small></span></div>
                        <div class="trust card-compact"><span class="t-ic"><?php echo fartak_icon( 'truck' ); ?></span><span><b><?php esc_html_e( 'ارسال سراسری', 'fartak' ); ?></b><small><?php esc_html_e( 'تیپاکس و پست ویژه', 'fartak' ); ?></small></span></div>
                        <div class="trust card-compact"><span class="t-ic"><?php echo fartak_icon( 'badge' ); ?></span><span><b><?php esc_html_e( 'ضمانت اصالت کالا', 'fartak' ); ?></b><small><?php esc_html_e( 'تست سلامت پیش از ارسال', 'fartak' ); ?></small></span></div>
                </div>

                <div class="footer-grid">
                        <div class="footer-about">
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-logo" style="display:inline-flex">
                                        <?php if ( has_custom_logo() ) : $ft_logo_id = get_theme_mod( 'custom_logo' ); echo wp_get_attachment_image( $ft_logo_id, 'full', false, array( 'class' => 'footer-logo-img', 'alt' => 'فروشگاه فرتاک', 'style' => 'max-height:46px;width:auto;border-radius:8px;' ) ); else : ?><span class="logo-mark"><?php echo fartak_icon( 'cpu' ); ?></span><?php endif; ?>
                                        <span class="logo-txt"><b>فروشگاه فرتاک</b><small>FARTAK</small></span>
                                </a>
                                <p>فروشگاه تخصصی محصولات فناوری اطلاعات فرتاک IT - پویا شبکه فرتاک</p>
                                <div class="socials">
                                        <a href="<?php echo esc_url( $telegram ); ?>" class="btn-ghost icon-btn" aria-label="<?php esc_attr_e( 'تلگرام', 'fartak' ); ?>" target="_blank" rel="noopener"><?php echo fartak_icon( 'send' ); ?></a>
                                        <a href="<?php echo esc_url( $instagram ); ?>" class="btn-ghost icon-btn" aria-label="<?php esc_attr_e( 'اینستاگرام', 'fartak' ); ?>" target="_blank" rel="noopener"><?php echo fartak_icon( 'at' ); ?></a>
                                        <a href="tel:<?php echo esc_attr( $ft_phone ); ?>" class="btn-ghost icon-btn" aria-label="<?php esc_attr_e( 'تلفن', 'fartak' ); ?>"><?php echo fartak_icon( 'phone' ); ?></a>
                                </div>
                        </div>

                        <div>
                                <h4><?php esc_html_e( 'دسته‌بندی‌ها', 'fartak' ); ?></h4>
                                <ul>
                                        <?php foreach ( $cats as $cat ) : ?>
                                                <li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
                                        <?php endforeach; ?>
                                </ul>
                        </div>                        <div>
                                <h4><?php esc_html_e( 'خدمات فرتاک', 'fartak' ); ?></h4>
                                <ul>
                                        <li><a href="<?php echo esc_url( $builder_u ); ?>"><?php esc_html_e( 'اسمبل آنلاین سیستم', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( $wholesale_u ); ?>"><?php esc_html_e( 'خرید عمده از فروشگاه', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( $compare_u ); ?>"><?php esc_html_e( 'مقایسه تخصصی کالا', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( $blog_u ); ?>"><?php esc_html_e( 'وبلاگ تکنولوژی', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ? get_page_by_path( 'contact' ) : null ) ); ?>"><?php esc_html_e( 'تماس با ما', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ? get_page_by_path( 'about' ) : null ) ); ?>"><?php esc_html_e( 'درباره ما', 'fartak' ); ?></a></li>
                                        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'faq' ) ? get_page_by_path( 'faq' ) : null ) ); ?>"><?php esc_html_e( 'سوالات متداول', 'fartak' ); ?></a></li>
                                </ul>
                        </div>

                        <div>
                                <h4 style="display:flex;align-items:center;gap:6px"><?php echo fartak_icon( 'pin' ); ?> <?php esc_html_e( 'دفتر مرکزی', 'fartak' ); ?></h4>
                                <p class="footer-addr"><?php echo fartak_icon( 'pin' ); ?> <span><?php echo esc_html( $ft_address ); ?></span></p>
                                <p class="footer-addr"><?php echo fartak_icon( 'phone' ); ?> <span>تلفن پشتیبانی: <a href="tel:<?php echo esc_attr( $ft_phone ); ?>" style="color:inherit" dir="ltr"><bdi dir="ltr"><?php echo esc_html( $ft_phone_disp ); ?></bdi></a></span></p>
                                <p class="footer-addr"><?php echo fartak_icon( 'send' ); ?> <span>ایمیل: <a href="mailto:<?php echo esc_attr( $ft_email ); ?>" style="color:inherit"><?php echo esc_html( $ft_email ); ?></a></span></p>
                                <div style="overflow:hidden;border-radius:12px;border:1px solid rgba(255,255,255,.08);width:100%;margin-top:12px">
                                        <iframe title="<?php esc_attr_e( 'لوکیشن فروشگاه فرتاک', 'fartak' ); ?>" src="https://www.openstreetmap.org/export/embed.html?bbox=54.398%2C36.828%2C54.468%2C36.856&amp;layer=mapnik&amp;marker=36.8416%2C54.4325" loading="lazy" style="display:block;width:100%;height:180px;border:0;filter:invert(.92) hue-rotate(185deg) saturate(.55) brightness(.92) contrast(.95)"></iframe>
                                </div>
                        </div>
                </div>

                <div class="footer-bottom">
                        <span>© <?php echo esc_html( fartak_fa( (string) $ft_year ) ); ?> فروشگاه فرتاک — <?php esc_html_e( 'تمامی حقوق مادی و معنوی این وب‌سایت محفوظ و متعلق به پویا شبکه فرتاک می‌باشد.', 'fartak' ); ?></span>
                        <span><?php esc_html_e( 'طراحی شده توسط', 'fartak' ); ?> <b class="neon-text-red" style="font-weight:800">Ehsan Design</b></span>
                </div>
        </div>
</footer>

<!-- داک شناور موبایل -->
<nav class="mobile-dock" aria-label="<?php esc_attr_e( 'ناوبری موبایل', 'fartak' ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="dock-item <?php echo esc_attr( is_front_page() ? 'active' : '' ); ?>"><?php echo fartak_icon( 'home' ); ?><span><?php esc_html_e( 'خانه', 'fartak' ); ?></span></a>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="dock-item <?php echo esc_attr( ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) ? 'active' : '' ); ?>"><?php echo fartak_icon( 'bag' ); ?><span><?php esc_html_e( 'فروشگاه', 'fartak' ); ?></span></a>
        <button type="button" class="dock-item dock-btn" data-cats-open aria-label="<?php esc_attr_e( 'دسته‌ها', 'fartak' ); ?>"><?php echo fartak_icon( 'grid' ); ?><span><?php esc_html_e( 'دسته‌ها', 'fartak' ); ?></span></button>
        <a href="<?php echo esc_url( $cart_url ); ?>" class="dock-item <?php echo esc_attr( ( function_exists( 'is_cart' ) && is_cart() ) ? 'active' : '' ); ?>">
                <span class="badge-btn">
                        <?php echo fartak_icon( 'cart' ); ?>
                        <?php
                        $dock_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                        ?>
                        <span class="count-badge <?php echo $dock_count > 0 ? '' : 'ft-hidden'; ?>" data-cart-count><?php echo esc_html( fartak_fa_num( $dock_count ) ); ?></span>
                </span>
                <span><?php esc_html_e( 'سبد خرید', 'fartak' ); ?></span>
        </a>
        <a href="<?php echo esc_url( $acct_url ); ?>" class="dock-item <?php echo esc_attr( ( function_exists( 'is_account_page' ) && is_account_page() ) ? 'active' : '' ); ?>"><?php echo fartak_icon( 'user' ); ?><span><?php esc_html_e( 'پروفایل', 'fartak' ); ?></span></a>
</nav>

<!-- شیت دسته‌بندی‌ها (دکمه‌ی «دسته‌ها» در داک موبایل) -->
<div class="cats-sheet-overlay" data-cats-close></div>
<aside class="cats-sheet" id="cats-sheet" aria-label="<?php esc_attr_e( 'همه دسته‌بندی‌ها', 'fartak' ); ?>">
        <div class="cs-head">
                <b><?php echo fartak_icon( 'grid' ); ?> <?php esc_html_e( 'دسته‌بندی‌ها', 'fartak' ); ?></b>
                <button type="button" class="btn-ghost icon-btn" data-cats-close aria-label="<?php esc_attr_e( 'بستن', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
        </div>
        <div class="cs-body">
                <a href="<?php echo esc_url( $shop_url ); ?>" class="cs-all"><?php echo fartak_icon( 'bag' ); ?> <?php esc_html_e( 'فروشگاه — مشاهده همه محصولات', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
                <?php
                $ft_tree = fartak_cat_tree();
                foreach ( $ft_tree as $ft_cat ) :
                        ?>
                        <div class="cs-group">
                                <a class="cs-parent" href="<?php echo esc_url( get_term_link( $ft_cat ) ); ?>">
                                        <span class="cs-ic"><?php echo fartak_icon( fartak_cat_icon( $ft_cat ) ); ?></span>
                                        <span class="cs-t"><b><?php echo esc_html( $ft_cat->name ); ?></b><small><?php echo esc_html( fartak_fa_num( $ft_cat->count ) ); ?> <?php esc_html_e( 'کالا', 'fartak' ); ?></small></span>
                                        <?php echo fartak_icon( 'chevron-left' ); ?>
                                </a>
                                <?php if ( ! empty( $ft_cat->children ) ) : ?>
                                        <div class="cs-kids">
                                                <?php foreach ( $ft_cat->children as $ft_sub ) : ?>
                                                        <a href="<?php echo esc_url( get_term_link( $ft_sub ) ); ?>"><?php echo fartak_icon( fartak_cat_icon( $ft_sub ) ); ?> <?php echo esc_html( $ft_sub->name ); ?></a>
                                                <?php endforeach; ?>
                                        </div>
                                <?php endif; ?>
                        </div>
                <?php endforeach; ?>
        </div>
</aside>

<?php get_template_part( 'template-parts/widgets' ); ?>

<div class="toast-zone" id="fartak-toasts" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
