<?php
/**
 * Fartak — صفحه تکی نوشته (مقاله)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cats = get_the_category();
	?>
	<article <?php post_class( 'page-top page-105' ); ?>>
		<header class="post-hero">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'fartak-hero' ); ?>
			<?php endif; ?>
			<div class="shade"></div>
			<div class="content">
				<a href="<?php echo esc_url( get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>" class="chip" style="color:var(--mist);margin-bottom:16px"><?php echo fartak_icon( 'arrow-right' ); ?> <?php echo esc_html( get_option( 'page_for_posts' ) ? __( 'بازگشت به مجله', 'fartak' ) : __( 'بازگشت به خانه', 'fartak' ) ); ?></a>
				<div style="display:flex;gap:8px;flex-wrap:wrap">
					<span class="chip" style="border-color:rgba(232,152,94,.4);background:rgba(232,152,94,.15);color:var(--copper2)"><?php echo esc_html( $cats ? $cats[0]->name : __( 'مقالات', 'fartak' ) ); ?></span>
					<span class="chip" style="color:var(--mist)"><?php echo fartak_icon( 'clock' ); ?> <?php echo esc_html( fartak_fa_num( fartak_reading_minutes( get_post() ) ) ); ?> <?php esc_html_e( 'دقیقه مطالعه', 'fartak' ); ?></span>
					<span class="chip" style="color:var(--mist)"><?php echo esc_html( get_the_date() ); ?></span>
				</div>
				<h1 style="margin:12px 0 0;font-size:21px;font-weight:900;line-height:1.8;max-width:52rem"><?php the_title(); ?></h1>
			</div>
		</header>

		<div class="container post-body">
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>

			<?php
			$tags = get_the_tags();
			if ( $tags && ! is_wp_error( $tags ) ) :
				?>
				<div class="post-tags" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:24px">
					<?php foreach ( $tags as $tag ) : ?>
						<a class="chip" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"># <?php echo esc_html( $tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="card-compact" style="display:flex;gap:12px;align-items:center;margin-top:24px;padding:16px">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 48, '', get_the_author(), array( 'class' => 'avatar' ) ); ?>
				<div>
					<b style="font-size:13px;font-weight:900"><?php the_author(); ?></b>
					<p style="margin:2px 0 0;font-size:11.5px;line-height:1.7;color:var(--mist)"><?php echo esc_html( get_the_author_meta( 'description' ) ? get_the_author_meta( 'description' ) : __( 'نویسنده و کارشناس فروشگاه فرتاک', 'fartak' ) ); ?></p>
				</div>
			</div>

			<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
				<div style="margin-top:32px;display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
					<?php dynamic_sidebar( 'blog-sidebar' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<div class="cta-strip card-compact" style="border-color:rgba(232,152,94,.25)">
					<div class="l">
						<span class="cta-ic"><?php echo fartak_icon( 'news' ); ?></span>
						<div>
							<p style="margin:0;font-size:13px;font-weight:900"><?php esc_html_e( 'دنبال قطعه خاصی هستی؟', 'fartak' ); ?></p>
							<p style="margin:2px 0 0;font-size:11.5px;color:var(--mist)"><?php esc_html_e( 'همه قطعات این مقاله در انبار فرتاک با گارانتی موجود است.', 'fartak' ); ?></p>
						</div>
					</div>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn-copper"><?php esc_html_e( 'مشاهده فروشگاه', 'fartak' ); ?> <?php echo fartak_icon( 'chevron-left' ); ?></a>
				</div>
			<?php endif; ?>

			<?php
			$related = get_posts(
				array(
					'posts_per_page' => 3,
					'post__not_in'   => array( get_the_ID() ),
					'category__in'   => $cats ? wp_list_pluck( $cats, 'term_id' ) : array(),
				)
			);
			if ( $related ) :
				?>
				<h2 style="margin-top:40px;font-size:16px;font-weight:900"><?php esc_html_e( 'مطالب مرتبط', 'fartak' ); ?></h2>
				<div class="blog-archive">
					<?php foreach ( $related as $post_obj ) : ?>
						<?php
						$GLOBALS['post'] = $post_obj; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
						setup_postdata( $post_obj );
						get_template_part( 'template-parts/blog-card' );
						?>
					<?php endforeach; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php endif; ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
