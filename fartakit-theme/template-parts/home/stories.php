<?php
/**
 * Fartak — استوری‌های محصولات (به سبک دیجی‌کالا / زومیت)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

/* استوری‌ها: اگر از پنل قالب انتخاب شده باشند همان‌ها (به همان ترتیب، حداکثر ۱۰)،
   وگرنه به‌صورت خودکار از محصولات تخفیف‌دار و پرفروش ساخته می‌شوند */
$stories      = array();
$story_ids_csv = (string) get_option( 'fartak_home_story_ids', '' );
if ( '' !== $story_ids_csv ) {
	$sids  = array_slice( array_filter( array_map( 'absint', explode( ',', $story_ids_csv ) ) ), 0, 10 );
	if ( $sids ) {
		$found = wc_get_products(
			array(
				'status'  => 'publish',
				'include' => $sids,
				'limit'   => 10,
			)
		);
		$by_id = array();
		foreach ( (array) $found as $fp ) {
			$by_id[ $fp->get_id() ] = $fp;
		}
		foreach ( $sids as $sid ) {
			if ( isset( $by_id[ $sid ] ) ) {
				$stories[] = $by_id[ $sid ];
			}
		}
	}
}
if ( empty( $stories ) ) {
	$sale_products = fartak_products( 'sale', 6 );
	$hot_products  = fartak_products( 'best', 8 );
	$seen          = array();
	foreach ( array_merge( $sale_products, $hot_products ) as $p ) {
		if ( in_array( $p->get_id(), $seen, true ) ) {
			continue;
		}
		$seen[]    = $p->get_id();
		$stories[] = $p;
		if ( count( $stories ) >= 10 ) {
			break;
		}
	}
}
if ( empty( $stories ) ) {
	return;
}
$ft_story_phone = preg_replace( '/\D/', '', (string) get_option( 'fartak_support_phone', '01732000180' ) );
?>
<?php
$ph_thumb = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'thumbnail' ) : '';
$ph_large = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'large' ) : '';
?>
<section class="stories-rail">
	<div class="container">
		<div class="stories-head">
			<?php echo fartak_icon( 'flame' ); ?>
			<h2><?php esc_html_e( 'استوری‌های فرتاک', 'fartak' ); ?></h2>
			<span>— <?php esc_html_e( 'محصولات داغ امروز', 'fartak' ); ?></span>
		</div>
		<div class="stories-list">
			<?php foreach ( $stories as $i => $p ) : ?>
				<button type="button" class="story-ring" data-story-open="<?php echo esc_attr( $i ); ?>">
					<span class="ring"><span class="inner"><img src="<?php echo esc_url( wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) ? wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) : $ph_thumb ); ?>" alt="<?php echo esc_attr( $p->get_name() ); ?>" loading="lazy"></span></span>
					<span><?php echo esc_html( fartak_product_cat_label( $p ) ? fartak_product_cat_label( $p ) : $p->get_name() ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="story-viewer" id="story-viewer">
		<div class="story-frame" id="story-frame">
			<img class="story-bg" id="story-bg" src="" alt="" aria-hidden="true">
			<img id="story-img" src="" alt="">
			<div class="story-shade-t"></div>
			<div class="story-shade-b"></div>
			<div class="story-progress" id="story-progress"></div>
			<div class="story-hd">
				<span class="ava"><?php esc_html_e( 'ف', 'fartak' ); ?></span>
				<div><b>فروشگاه فرتاک</b><small><?php esc_html_e( 'پیشنهاد ویژه امروز', 'fartak' ); ?></small></div>
				<button type="button" class="x" data-story-close aria-label="<?php esc_attr_e( 'بستن', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
			</div>
			<button type="button" class="story-tap next" data-story-next aria-label="<?php esc_attr_e( 'بعدی', 'fartak' ); ?>"></button>
			<button type="button" class="story-tap prev" data-story-prev aria-label="<?php esc_attr_e( 'قبلی', 'fartak' ); ?>"></button>
			<button type="button" class="glass-arrow story-arrow r" data-story-prev aria-label="<?php esc_attr_e( 'قبلی', 'fartak' ); ?>"><?php echo fartak_icon( 'chevron-right' ); ?></button>
			<button type="button" class="glass-arrow story-arrow l" data-story-next aria-label="<?php esc_attr_e( 'بعدی', 'fartak' ); ?>"><?php echo fartak_icon( 'chevron-left' ); ?></button>
			<div class="story-body">
				<span id="story-off"></span>
				<h3 id="story-name" class="clamp2"></h3>
				<div class="story-warranty" id="story-warranty"></div>
				<div class="story-price">
					<div><del id="story-old"></del><b id="story-price"></b></div>
					<small id="story-count"></small>
				</div>
				<a href="#" class="btn-copper w-full" id="story-link"><?php esc_html_e( 'مشاهده و خرید این کالا', 'fartak' ); ?></a>
			</div>
		</div>
	</div>

	<script type="application/json" id="fartak-stories-data"><?php
		$json = array();
		foreach ( $stories as $p ) {
			$off    = fartak_sale_percent( $p );
			$real   = fartak_has_real_price( $p ) && ! fartak_product_is_call( $p->get_id() );
			$json[] = array(
				'name'  => $p->get_name(),
				'url'   => get_permalink( $p->get_id() ),
				'img'   => wp_get_attachment_image_url( $p->get_image_id(), 'large' ) ? wp_get_attachment_image_url( $p->get_image_id(), 'large' ) : $ph_large,
				'price' => $real ? wp_kses_post( $p->get_price_html() ) : '',
				'raw'   => $real ? (float) $p->get_price() : 0,
				'old'   => $real && $off > 0 ? wp_kses_post( wc_price( (float) $p->get_regular_price() ) ) : '',
				'off'   => $real ? $off : 0,
                'warranty' => function_exists( 'fartak_get_warranty' ) ? fartak_get_warranty( $p->get_id() ) : '',
			);
		}
		echo wp_json_encode( $json );
		?>
	</script>
</section>
