<?php
/**
 * Fartak — مارکی برندها، تیزر اسمبل، بنرهای تبلیغاتی
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$part = isset( $args['part'] ) ? $args['part'] : '';

if ( 'brands' === $part ) :
        $brands = array_filter( array_map( 'trim', explode( "\n", fartak_mod( 'fartak_brands', fartak_def( 'brands' ) ) ) ) );
        if ( ! $brands ) {
                return;
        }
        ?>
        <section class="brand-marquee" dir="ltr">
                <div class="marquee-viewport">
                        <div class="marquee-track">
                                <?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
                                        <div class="marquee-half" <?php echo 0 === $copy ? 'aria-hidden="true"' : ''; ?>>
                                                <?php foreach ( $brands as $brand ) : ?>
                                                        <span class="brand-item"><?php echo esc_html( $brand ); ?> <i></i></span>
                                                <?php endforeach; ?>
                                        </div>
                                <?php endfor; ?>
                        </div>
                </div>
        </section>
        <?php
endif;

if ( 'builder' === $part ) :
        ?>
        <section class="section-block">
                <div class="container">
                        <div class="teaser">
                                <div class="teaser-orb"></div>
                                <div class="teaser-grid">
                                        <div>
                                                <span class="chip" style="border-color:rgba(232,152,94,.35);background:rgba(232,152,94,.1);color:var(--copper2)"><?php echo fartak_icon( 'sparkles' ); ?> <?php esc_html_e( 'ابزار اسمبل آنلاین', 'fartak' ); ?></span>
                                                <h2><?php esc_html_e( 'سیستم خودت را', 'fartak' ); ?> <span class="copper-text"><?php esc_html_e( 'مرحله‌به‌مرحله', 'fartak' ); ?></span> <?php esc_html_e( 'بساز', 'fartak' ); ?></h2>
                                                <p><?php esc_html_e( 'مثل یک بازی، هفت مرحله جلو برو؛ سازگاری سوکت و فرکانس رم به‌صورت خودکار چک می‌شود و قیمت نهایی اسمبل + مونتاژ رایگان را همینجا می‌بینی.', 'fartak' ); ?></p>
                                                <div class="hero-ctas">
                                                        <a href="<?php echo esc_url( fartak_tpl_url( 'page-templates/template-builder.php' ) ); ?>" class="btn-copper btn-lg"><?php esc_html_e( 'شروع ساخت سیستم', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
                                                        <span style="font-size:11.5px;color:var(--mist)"><?php esc_html_e( 'مونتاژ، نصب ویندوز و بنچمارک —', 'fartak' ); ?> <b style="color:var(--copper2)"><?php esc_html_e( 'رایگان', 'fartak' ); ?></b></span>
                                                </div>
                                        </div>
                                        <div class="teaser-visual">
                                                <div class="backlight"></div>
                                                <img src="<?php echo esc_url( FARTAK_URI . '/assets/images/hero-case.png' ); ?>" alt="<?php esc_attr_e( 'اسمبل سیستم گیمینگ', 'fartak' ); ?>" loading="lazy">
                                                <span class="float-chip a glass"><?php echo fartak_icon( 'cpu' ); ?> <?php esc_html_e( 'انتخاب پردازنده', 'fartak' ); ?></span>
                                                <span class="float-chip b glass"><?php echo fartak_icon( 'check' ); ?> <?php esc_html_e( 'چک سازگاری خودکار', 'fartak' ); ?></span>
                                        </div>
                                </div>
                        </div>
                </div>
        </section>
        <?php
endif;

if ( 'promos' === $part ) :
        $promos = array(
                array(
                        'href'  => fartak_tpl_url( 'page-templates/template-wholesale.php' ),
                        'icon'  => 'package',
                        'tag'   => __( 'خرید عمده از فروشگاه', 'fartak' ),
                        'title' => __( 'قیمت ویژه برای خرید عمده', 'fartak' ),
                        'desc'  => __( 'برای خرید عمده با فروشگاه تماس بگیرید', 'fartak' ),
                        'cta'   => __( 'مشاهده شرایط', 'fartak' ),
                        'img'   => FARTAK_URI . '/assets/images/hero-case.png',
                ),
                array(
                        'href'  => fartak_tpl_url( 'page-templates/template-builder.php' ),
                        'icon'  => 'wrench',
                        'tag'   => __( 'ابزار اسمبل آنلاین فرتاک', 'fartak' ),
                        'title' => __( 'سیستمت را مرحله‌به‌مرحله بساز', 'fartak' ),
                        'desc'  => __( 'چک سازگاری خودکار + مونتاژ رایگان', 'fartak' ),
                        'cta'   => __( 'شروع اسمبل', 'fartak' ),
                        'img'   => FARTAK_URI . '/assets/images/hero-gpu.png',
                ),
        );
        ?>
        <section class="section-block">
                <div class="container">
                        <div class="promo-grid">
                                <?php foreach ( $promos as $promo ) : ?>
                                        <a href="<?php echo esc_url( $promo['href'] ); ?>" class="promo">
                                                <div class="backlight"></div>
                                                <img src="<?php echo esc_url( $promo['img'] ); ?>" alt="" loading="lazy">
                                                <div class="inner">
                                                        <span class="chip" style="border-color:rgba(232,152,94,.35);background:rgba(232,152,94,.1);color:var(--copper2)"><?php echo fartak_icon( $promo['icon'] ); ?> <?php echo esc_html( $promo['tag'] ); ?></span>
                                                        <h3><?php echo esc_html( $promo['title'] ); ?></h3>
                                                        <p><?php echo esc_html( $promo['desc'] ); ?></p>
                                                        <span class="btn-copper"><?php echo esc_html( $promo['cta'] ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></span>
                                                </div>
                                        </a>
                                <?php endforeach; ?>
                        </div>
                </div>
        </section>
        <?php
endif;
