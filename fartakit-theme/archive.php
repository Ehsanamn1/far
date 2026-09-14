<?php
/**
 * Fartak — آرشیوها (دسته و برچسب نوشته‌ها)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<header style="margin-bottom:20px">
		<h1 style="font-size:19px;font-weight:900"><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div style="font-size:12.5px;color:var(--mist)">', '</div>' ); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="blog-archive">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/blog-card' ); ?>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => fartak_icon( 'chevron-right' ), 'next_text' => fartak_icon( 'chevron-left' ) ) ); ?>
	<?php else : ?>
		<div class="card-compact empty-state" style="margin-top:40px">
			<?php echo fartak_icon( 'news' ); ?>
			<h3><?php esc_html_e( 'موردی یافت نشد', 'fartak' ); ?></h3>
			<p><?php esc_html_e( 'در این آرشیو محتوایی وجود ندارد.', 'fartak' ); ?></p>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
