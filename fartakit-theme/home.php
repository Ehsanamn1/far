<?php
/**
 * Fartak — صفحه وبلاگ (آرشیو نوشته‌ها به سبک مجله تکنولوژی)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<h1 style="display:flex;align-items:center;gap:10px;font-size:22px;font-weight:900"><?php echo fartak_icon( 'news' ); ?> <?php esc_html_e( 'مجله تکنولوژی فرتاک', 'fartak' ); ?></h1>
	<p style="font-size:12.5px;color:var(--mist)"><?php esc_html_e( 'راهنمای خرید، آموزش اسمبل و نقد تخصصی سخت‌افزار — هر هفته تازه.', 'fartak' ); ?></p>

	<?php if ( have_posts() ) : ?>
		<?php
		$first = true;
		while ( have_posts() ) :
			the_post();
			if ( $first ) :
				$first = false;
				$cats  = get_the_category();
				?>
				<a href="<?php the_permalink(); ?>" class="card-compact" style="position:relative;display:block;overflow:hidden;border-radius:20px;margin:24px 0 20px">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'fartak-hero', array( 'style' => 'width:100%;height:230px;object-fit:cover', 'class' => 'featured-img' ) ); ?>
					<?php endif; ?>
					<style>@media(min-width:768px){.featured-img{height:340px!important}}</style>
					<div class="blog-featured-shade" style="position:absolute;inset:0;background:linear-gradient(to top,var(--abyss),rgba(10,16,30,.45),transparent)"></div>
					<div style="position:absolute;inset-inline:0;bottom:0;padding:24px">
						<div style="display:flex;gap:8px;margin-bottom:12px">
							<span class="chip" style="border-color:rgba(232,152,94,.4);background:rgba(232,152,94,.15);color:var(--copper2)"><?php echo esc_html( $cats ? $cats[0]->name : __( 'مقالات', 'fartak' ) ); ?></span>
							<span class="chip" style="color:var(--mist)"><?php echo fartak_icon( 'clock' ); ?> <?php echo esc_html( fartak_fa_num( fartak_reading_minutes( get_post() ) ) ); ?> <?php esc_html_e( 'دقیقه مطالعه', 'fartak' ); ?></span>
						</div>
						<h2 style="max-width:48rem;font-size:20px;font-weight:900;line-height:1.8;margin:0"><?php the_title(); ?></h2>
						<p style="max-width:42rem;margin:8px 0 0;font-size:13px;line-height:1.8;color:var(--mist)"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
					</div>
				</a>
				<div class="blog-archive">
			<?php else : ?>
				<?php get_template_part( 'template-parts/blog-card' ); ?>
			<?php endif; ?>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => fartak_icon( 'chevron-right' ), 'next_text' => fartak_icon( 'chevron-left' ) ) ); ?>
	<?php else : ?>
		<div class="card-compact empty-state" style="margin-top:40px">
			<?php echo fartak_icon( 'news' ); ?>
			<h3><?php esc_html_e( 'هنوز مقاله‌ای منتشر نشده', 'fartak' ); ?></h3>
			<p><?php esc_html_e( 'اولین مقاله فنی را از بخش «نوشته‌ها» در پیشخوان منتشر کنید.', 'fartak' ); ?></p>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
