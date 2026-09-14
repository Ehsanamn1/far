<?php
/**
 * Fartak — اورراید کارت حلقه‌ی محصولات ووکامرس
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'ft-loop-item', $product ); ?>>
	<?php fartak_product_card( $product, 'raw' ); ?>
</li>
