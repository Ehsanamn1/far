<?php
/**
 * Fartak — تیزر وبلاگ به سبک مجله تکنولوژی
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$posts = get_posts( array( 'posts_per_page' => 4, 'post_type' => 'post', 'post_status' => 'publish' ) );
if ( ! $posts ) {
	return;
}
$first      = array_shift( $posts );
$blog_url   = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
$first_cats = get_the_category( $first->ID );
$first_cat  = $first_cats ? $first_cats[0]->name : __( 'مقالات', 'fartak' );
?>
<section class="section-block">
	<div class="container">
		<div class="carousel-head">
			<div class="carousel-title"><h2><?php esc_html_e( 'مجله تکنولوژی فرتاک', 'fartak' ); ?></h2></div>
			<a href="<?php echo esc_url( $blog_url ); ?>" class="see-all" style="display:inline-flex"><?php esc_html_e( 'آرشیو مقالات', 'fartak' ); ?> <?php echo fartak_icon( 'arrow-left' ); ?></a>
		</div>
		<div class="blog-grid">
			<a href="<?php echo esc_url( get_permalink( $first ) ); ?>" class="blog-featured card-compact" style="overflow:hidden;display:block">
				<div class="img-wrap">
					<?php echo get_the_post_thumbnail( $first, 'fartak-hero', array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover' ) ); ?>
				</div>
				<div class="img-shade"></div>
				<div class="content">
					<span class="chip" style="border-color:rgba(232,152,94,.35);background:rgba(232,152,94,.15);color:var(--copper2)"><?php echo esc_html( $first_cat ); ?></span>
					<h3 style="font-size:17px;min-height:0;margin-top:8px;line-height:1.75"><?php echo esc_html( get_the_title( $first ) ); ?></h3>
					<div class="meta"><?php echo fartak_icon( 'clock' ); ?> <?php echo esc_html( fartak_fa_num( fartak_reading_minutes( $first ) ) ); ?> <?php esc_html_e( 'دقیقه مطالعه', 'fartak' ); ?></div>
				</div>
			</a>
			<?php foreach ( $posts as $post_obj ) : ?>
				<?php $post_cats = get_the_category( $post_obj->ID ); ?>
				<a href="<?php echo esc_url( get_permalink( $post_obj ) ); ?>" class="blog-card card-compact" style="display:block">
					<div class="img-wrap"><?php echo get_the_post_thumbnail( $post_obj, 'fartak-card', array( 'loading' => 'lazy' ) ); ?></div>
					<div class="content">
						<span class="bc-cat"><?php echo esc_html( $post_cats ? $post_cats[0]->name : __( 'مقالات', 'fartak' ) ); ?></span>
						<h3 class="clamp2"><?php echo esc_html( get_the_title( $post_obj ) ); ?></h3>
						<div class="meta"><?php echo fartak_icon( 'clock' ); ?> <?php echo esc_html( fartak_fa_num( fartak_reading_minutes( $post_obj ) ) ); ?> <?php esc_html_e( 'دقیقه', 'fartak' ); ?></div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
