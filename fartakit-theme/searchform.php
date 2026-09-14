<?php
/**
 * Fartak — فرم جستجو (قبلاً وجود نداشت و فرم خام هسته رندر می‌شد)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$fartak_sf_home = home_url( '/' );
$fartak_sf_q    = get_search_query();
?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url( $fartak_sf_home ); ?>">
	<label class="screen-reader-text" for="fartak-s-field"><?php esc_html_e( 'جستجو برای:', 'fartak' ); ?></label>
	<div style="display:flex;gap:8px">
		<input type="search" id="fartak-s-field" class="input-dark" placeholder="<?php esc_attr_e( 'جستجو در محصولات و مقالات…', 'fartak' ); ?>" value="<?php echo esc_attr( $fartak_sf_q ); ?>" name="s">
		<button type="submit" class="btn-copper" aria-label="<?php esc_attr_e( 'جستجو', 'fartak' ); ?>"><?php echo fartak_icon( 'search' ); ?></button>
	</div>
</form>
