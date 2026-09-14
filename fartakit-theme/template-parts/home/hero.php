<?php
/**
 * Fartak — هیرو با 3 اسلاید (متصل به Customizer با پیش‌فرض‌های قالب)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$builder_u = fartak_tpl_url( 'page-templates/template-builder.php' );

/* اسلایدها از Customizer خوانده می‌شوند (fartak_hero_1_* تا fartak_hero_3_*)؛ اگر مدیر تغییری نداده باشد همان پیش‌فرض‌ها نمایش داده می‌شود */
$slides = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$d = fartak_hero_defaults( $i );
	$s = array(
		'eyebrow' => fartak_mod( "fartak_hero_{$i}_eyebrow", $d['eyebrow'] ),
		'title'   => fartak_mod( "fartak_hero_{$i}_title", $d['title'] ),
		'sub'     => fartak_mod( "fartak_hero_{$i}_sub", $d['sub'] ),
		'img'     => fartak_mod( "fartak_hero_{$i}_img", $d['img'] ),
		'cta_l'   => fartak_mod( "fartak_hero_{$i}_cta_l", $d['cta_l'] ),
		'cta_u'   => fartak_mod( "fartak_hero_{$i}_cta_u", '' ),
		'stat'    => fartak_mod( "fartak_hero_{$i}_stat", $d['stat'] ),
	);
	if ( '' === trim( (string) $s['cta_u'] ) ) {
		$s['cta_u'] = ( 3 === $i ) ? $builder_u : $shop_url;
	}
	// تیتر چندخطی Customizer به یک خط تبدیل شود
	$s['title'] = trim( preg_replace( '/\s+/u', ' ', (string) $s['title'] ) );
	$slides[]   = $s;
}
?>
<section class="hero" id="fartak-hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <?php foreach ( $slides as $idx => $s ) : ?>
                <div class="hero-slide <?php echo 0 === $idx ? 'active' : ''; ?>" data-hero-slide="<?php echo esc_attr( $idx ); ?>">
                    <span class="chip" style="border-color:rgba(225,29,42,.35);background:rgba(225,29,42,.1);color:var(--copper2)"><?php echo fartak_icon( 'zap' ); ?> <?php echo esc_html( $s['eyebrow'] ); ?></span>
                    <?php /* فقط اسلاید اول h1 — بقیه h2 تا سه h1 همزمان در DOM نداشته باشیم (سئو) */ ?>
                    <?php if ( 0 === $idx ) : ?>
                        <h1><?php echo esc_html( $s['title'] ); ?></h1>
                    <?php else : ?>
                        <h2 class="hero-h"><?php echo esc_html( $s['title'] ); ?></h2>
                    <?php endif; ?>
                    <p><?php echo esc_html( $s['sub'] ); ?></p>
                    <div class="hero-ctas">
                        <a href="<?php echo esc_url( $s['cta_u'] ); ?>" class="btn-copper btn-lg"><?php echo esc_html( $s['cta_l'] ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
                        <span class="chip" style="color:var(--mist)"><?php echo fartak_icon( 'shield' ); ?> <?php echo esc_html( $s['stat'] ); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="hero-nav">
                <div class="hero-dots">
                    <?php foreach ( $slides as $idx => $s ) : ?>
                        <button type="button" class="hero-dot <?php echo 0 === $idx ? 'active' : ''; ?>" data-hero-goto="<?php echo esc_attr( $idx ); ?>" aria-label="اسلاید <?php echo esc_attr( $idx + 1 ); ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="hero-arrows">
                    <button type="button" class="glass-arrow" data-hero-prev aria-label="قبلی"><?php echo fartak_icon( 'chevron-right' ); ?></button>
                    <button type="button" class="glass-arrow" data-hero-next aria-label="بعدی"><?php echo fartak_icon( 'chevron-left' ); ?></button>
                </div>
            </div>
        </div>

        <div class="hero-visual" data-hero-visual>
            <div class="backlight" data-hero-glow></div>
            <div class="hero-img-wrap" data-hero-parallax>
                <?php foreach ( $slides as $idx => $s ) : ?>
                    <img src="<?php echo esc_url( $s['img'] ); ?>" alt="<?php echo esc_attr( $s['eyebrow'] ); ?>" class="hero-img <?php echo 0 === $idx ? 'active' : ''; ?>" data-hero-img="<?php echo esc_attr( $idx ); ?>" <?php echo 0 === $idx ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
                <?php endforeach; ?>
                <span class="hero-live glass"><i></i> <?php esc_html_e( 'موجود در فروشگاه و آنلاین', 'fartak' ); ?></span>
            </div>
        </div>
    </div>
</section>
