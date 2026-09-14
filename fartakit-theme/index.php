<?php
/**
 * Fartak — قالب پیش‌فرض (Fallback)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<?php if ( have_posts() ) : ?>
		<div class="blog-archive">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'blog-card card-compact' ); ?>>
					<a href="<?php the_permalink(); ?>" style="display:block">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="img-wrap"><?php the_post_thumbnail( 'fartak-card', array( 'loading' => 'lazy' ) ); ?></div>
						<?php endif; ?>
						<div class="content">
							<h3 class="clamp2"><?php the_title(); ?></h3>
							<div class="meta"><span><?php echo esc_html( get_the_date() ); ?></span></div>
						</div>
					</a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => fartak_icon( 'chevron-right' ), 'next_text' => fartak_icon( 'chevron-left' ) ) ); ?>
	<?php else : ?>
		<div class="card-compact empty-state" style="margin-top:40px">
			<?php echo fartak_icon( 'search' ); ?>
			<h3><?php esc_html_e( 'محتوایی یافت نشد', 'fartak' ); ?></h3>
			<p><?php esc_html_e( 'چیزی برای نمایش وجود ندارد.', 'fartak' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-copper"><?php esc_html_e( 'بازگشت به خانه', 'fartak' ); ?></a>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
