<?php
/**
 * Fartak — Single Product
 * Keep WooCommerce responsible for the product template and hooks.
 * @package Fartak
 */
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/** WooCommerce handles the wrapper, global product, gallery, summary and tabs. */
do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
    the_post();
    wc_get_template_part( 'content', 'single-product' );
endwhile;

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
