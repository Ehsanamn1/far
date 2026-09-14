<?php
/**
 * Fartak — قالب برگه عمومی
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container page-top page-105">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<header style="margin-bottom:20px">
				<h1 style="font-size:22px;font-weight:900"><?php the_title(); ?></h1>
			</header>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				echo '<div style="max-width:960px">';
				comments_template();
				echo '</div>';
			}
			?>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
