<?php
/**
 * Fartak — کارت مقاله در آرشیوها
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$cats = get_the_category();
?>
<article <?php post_class( 'blog-card card-compact' ); ?>>
	<a href="<?php the_permalink(); ?>" style="display:block;height:100%">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="img-wrap"><?php the_post_thumbnail( 'fartak-card', array( 'loading' => 'lazy' ) ); ?></div>
		<?php endif; ?>
		<div class="content">
			<div style="display:flex;align-items:center;justify-content:space-between">
				<span class="bc-cat"><?php echo esc_html( $cats ? $cats[0]->name : __( 'مقالات', 'fartak' ) ); ?></span>
				<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:var(--mist)"><?php echo esc_html( get_the_date() ); ?></span>
			</div>
			<h3 class="clamp2"><?php the_title(); ?></h3>
			<p class="excerpt clamp2"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
		</div>
	</a>
</article>
