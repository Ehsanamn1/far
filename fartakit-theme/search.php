<?php
/**
 * Fartak — نتایج جستجو
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();

$q = get_search_query();
?>
<div class="container page-top page-105">
	<header style="margin-bottom:20px">
		<h1 style="font-size:19px;font-weight:900"><?php echo esc_html( sprintf( __( 'نتایج جستجو برای «%s»', 'fartak' ), $q ) ); ?></h1>
		<p style="font-size:12px;color:var(--mist)"><?php echo esc_html( sprintf( _n( '%s نتیجه', '%s نتیجه', (int) $GLOBALS['wp_query']->found_posts, 'fartak' ), fartak_fa_num( (int) $GLOBALS['wp_query']->found_posts ) ) ); ?></p>
	</header>

	<?php
	if ( have_posts() ) :
		$product_ids = array();
		$post_count  = 0;
		foreach ( $GLOBALS['wp_query']->posts as $p ) {
			if ( 'product' === $p->post_type ) {
				$product_ids[] = $p->ID;
			} else {
				$post_count++;
			}
		}

		if ( $product_ids && class_exists( 'WooCommerce' ) ) :
			?>
			<h2 style="font-size:15px;font-weight:900"><?php esc_html_e( 'محصولات', 'fartak' ); ?></h2>
			<ul class="products" style="list-style:none">
				<?php
				foreach ( $product_ids as $pid ) :
					$product = wc_get_product( $pid );
					if ( $product ) :
						?>
						<li class="ft-loop-item"><?php fartak_product_card( $product, 'raw' ); ?></li>
						<?php
					endif;
				endforeach;
				?>
			</ul>
			<?php
		endif;

		if ( $post_count ) :
			?>
			<h2 style="margin-top:28px;font-size:15px;font-weight:900"><?php esc_html_e( 'مقالات و صفحات', 'fartak' ); ?></h2>
			<div class="blog-archive">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'product' === get_post_type() ) {
						continue;
					}
					get_template_part( 'template-parts/blog-card' );
				endwhile;
				?>
			</div>
			<?php
		endif;

		if ( ! $product_ids && ! $post_count ) :
			?>
			<div class="card-compact empty-state" style="margin-top:40px">
				<?php echo fartak_icon( 'search' ); ?>
				<h3><?php esc_html_e( 'موردی یافت نشد', 'fartak' ); ?></h3>
				<p><?php esc_html_e( 'عبارت دیگری را امتحان کنید.', 'fartak' ); ?></p>
			</div>
			<?php
		endif;

		// صفحه‌بندی نتایج — قبلاً صفحه ۲ به بعد اصلاً در دسترس نبود
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => fartak_icon( 'chevron-right' ),
				'next_text' => fartak_icon( 'chevron-left' ),
			)
		);
	else :
		?>
		<div class="card-compact empty-state" style="margin-top:40px">
			<?php echo fartak_icon( 'search' ); ?>
			<h3><?php esc_html_e( 'نتیجه‌ای یافت نشد', 'fartak' ); ?></h3>
			<p><?php esc_html_e( 'موردی با این عبارت پیدا نشد؛ فیلترها یا عبارت دیگری را امتحان کنید.', 'fartak' ); ?></p>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn-copper"><?php esc_html_e( 'مشاهده فروشگاه', 'fartak' ); ?></a>
		</div>
		<?php
	endif;
	?>
</div>
<?php
get_footer();
