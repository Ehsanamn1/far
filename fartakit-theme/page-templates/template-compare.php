<?php
/**
 * Template Name: مقایسه کالا (Glassmorphic)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<h1 style="display:flex;align-items:center;gap:8px;margin:0;font-size:20px;font-weight:900"><?php echo fartak_icon( 'compare' ); ?> <?php esc_html_e( 'مقایسه تخصصی کالا', 'fartak' ); ?></h1>
	<p style="margin:4px 0 0;font-size:12px;color:var(--mist)"><?php esc_html_e( 'تا سقف ۳ کالا را کنار هم بگذارید و مشخصات فنی را دقیق بسنجید.', 'fartak' ); ?></p>

	<div id="compare-zone" style="margin-top:24px">
		<div class="card-compact empty-state" id="compare-empty">
			<?php echo fartak_icon( 'compare' ); ?>
			<h3><?php esc_html_e( 'لیست مقایسه خالی است', 'fartak' ); ?></h3>
			<p><?php esc_html_e( 'از فروشگاه کالاها را با دکمه مقایسه به این لیست اضافه کنید.', 'fartak' ); ?></p>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn-copper"><?php echo fartak_icon( 'plus' ); ?> <?php esc_html_e( 'رفتن به فروشگاه', 'fartak' ); ?></a>
		</div>
		<div class="compare-wrap glass ft-hidden" id="compare-table"></div>
		<p class="compare-hint"><?php echo fartak_icon( 'arrow-left' ); ?> <?php esc_html_e( 'برای دیدن ستون‌های بیشتر، جدول را بکشید', 'fartak' ); ?></p>
	</div>
</div>
<?php
get_footer();
