<?php
/**
 * Fartak — کاروسل اسلایدی محصولات (به سبک دیجی‌کالا)
 *
 * Args: $title, $subtitle, $href, $type (sale|best|featured|recent), $limit
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$c_title    = isset( $args['title'] ) ? $args['title'] : '';
$c_subtitle = isset( $args['subtitle'] ) ? $args['subtitle'] : '';
$c_href     = isset( $args['href'] ) ? $args['href'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '#' );
$c_products = isset( $args['products'] ) ? $args['products'] : array();

if ( empty( $c_products ) ) {
	return;
}
?>
<section class="section-block ft-carousel" data-carousel>
	<div class="container">
		<div class="carousel-head">
			<div>
				<div class="carousel-title"><h2><?php echo esc_html( $c_title ); ?></h2></div>
				<?php if ( $c_subtitle ) : ?>
					<p><?php echo esc_html( $c_subtitle ); ?></p>
				<?php endif; ?>
			</div>
			<div class="carousel-tools">
				<button type="button" class="glass-arrow" data-prev aria-label="<?php esc_attr_e( 'قبلی', 'fartak' ); ?>"><?php echo fartak_icon( 'chevron-right' ); ?></button>
				<button type="button" class="glass-arrow" data-next aria-label="<?php esc_attr_e( 'بعدی', 'fartak' ); ?>"><?php echo fartak_icon( 'chevron-left' ); ?></button>
				<a href="<?php echo esc_url( $c_href ); ?>" class="see-all"><?php esc_html_e( 'مشاهده همه', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
			</div>
		</div>

		<div class="swiper ft-swiper">
			<div class="swiper-wrapper">
				<div class="swiper-slide ft-slide seeall-slide">
					<a href="<?php echo esc_url( $c_href ); ?>" class="seeall-card">
						<span><?php echo fartak_icon( 'arrow-left' ); ?></span>
						<b><?php esc_html_e( 'مشاهده همه', 'fartak' ); ?></b>
					</a>
				</div>
				<?php foreach ( $c_products as $product ) : ?>
					<?php fartak_product_card( $product, 'slide' ); ?>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="seeall-bottom">
			<a href="<?php echo esc_url( $c_href ); ?>" class="btn-ghost"><?php esc_html_e( 'مشاهده همه', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
		</div>
	</div>
</section>
