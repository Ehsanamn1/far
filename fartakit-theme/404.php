<?php
/**
 * Fartak — صفحه ۴۰۴
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nf-wrap page-top">
	<div class="nf-orb"></div>
	<span class="nf-mark"><?php echo fartak_icon( 'cpu' ); ?></span>
	<div class="nf-404"><?php esc_html_e( '۴۰۴', 'fartak' ); ?></div>
	<h2><?php esc_html_e( 'این قطعه در مدار پیدا نشد!', 'fartak' ); ?></h2>
	<p><?php esc_html_e( 'صفحه‌ای که دنبالش بودی یا جابه‌جا شده یا از فهرست انبار حذف شده است.', 'fartak' ); ?></p>
	<div class="nf-ctas">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-copper"><?php echo fartak_icon( 'home' ); ?> <?php esc_html_e( 'بازگشت به خانه', 'fartak' ); ?></a>
		<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn-ghost"><?php echo fartak_icon( 'search' ); ?> <?php esc_html_e( 'جستجو در فروشگاه', 'fartak' ); ?></a>
		<a href="<?php echo esc_url( fartak_tpl_url( 'page-templates/template-builder.php' ) ); ?>" class="btn-ghost"><?php echo fartak_icon( 'wrench' ); ?> <?php esc_html_e( 'اسمبل آنلاین', 'fartak' ); ?></a>
	</div>
	<div style="max-width:420px;margin:24px auto 0">
		<?php get_search_form(); ?>
	</div>
</div>
<?php
get_footer();
