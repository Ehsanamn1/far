<?php
/**
 * Fartak — دسته‌بندی‌های پرطرفدار
 * هشت آیکون گرد (مطابق سایت مرجع fartakit.com) — اسکرول snap در موبایل
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$cats = fartak_popular_cats();
if ( ! $cats ) {
	return;
}
?>
<section class="section-block category-section" style="padding-block:26px">
	<div class="container">
		<div class="section-head">
			<div>
				<h2 class="section-title"><?php esc_html_e( 'دسته‌بندی‌های پر طرفدار!', 'fartak' ); ?></h2>
				<p class="section-sub"><?php esc_html_e( 'به دنیای تکنولوژی خوش آمدید', 'fartak' ); ?></p>
			</div>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="section-link"><?php esc_html_e( 'مشاهده همه', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
		</div>
		<div class="cat-rail" id="category-slider">
			<?php foreach ( $cats as $cat ) : ?>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="cat-tile icon-tile" aria-label="<?php echo esc_attr( $cat->name ); ?>">
					<span class="c-ic"><?php echo fartak_icon( fartak_cat_icon( $cat ) ); ?></span>
					<b><?php echo esc_html( $cat->name ); ?></b>
					<span class="c-glow"></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
