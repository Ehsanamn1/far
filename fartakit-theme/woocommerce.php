<?php
/**
 * Fartak — قالب wrapper صفحات ووکامرس
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<?php if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) : ?>
		<div class="shop-head">
			<h1>
				<?php
				if ( is_product_category() || is_product_tag() ) {
					single_term_title();
				} else {
					echo esc_html( wc_get_page_id( 'shop' ) > 0 ? get_the_title( wc_get_page_id( 'shop' ) ) : __( 'فروشگاه', 'fartak' ) );
				}
				?>
				<span class="cnt">
					<?php
					/* تعداد واقعی همین صفحه (کوئری فعلی) — قبلاً تعداد کل محصولات سایت چاپ می‌شد */
					global $wp_query;
					$total = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
					echo esc_html( sprintf( __( '(%s کالا)', 'fartak' ), fartak_fa_num( $total ) ) );
					?>
				</span>
			</h1>
		</div>
	<?php elseif ( ! is_product() && ! is_cart() && ! is_checkout() && ! is_account_page() && function_exists( 'woocommerce_breadcrumb' ) ) : ?>
		<?php woocommerce_breadcrumb(); ?>
	<?php endif; ?>

	<?php woocommerce_content(); ?>
</div>
<?php
get_footer();
