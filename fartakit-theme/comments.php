<?php
/**
 * Fartak — دیدگاه‌ها
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div class="comments-area" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 id="comments-count" style="font-size:15px;font-weight:900">
			<?php
			echo esc_html(
				sprintf(
					_n( '%s دیدگاه', '%s دیدگاه', get_comments_number(), 'fartak' ),
					fartak_fa_num( get_comments_number() )
				)
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 40,
					'short_ping'  => true,
				)
			);
			?>
		</ol>
		<?php
		the_comments_navigation(
			array(
				'prev_text' => fartak_icon( 'chevron-right' ) . ' ' . __( 'دیدگاه‌های قدیمی‌تر', 'fartak' ),
				'next_text' => __( 'دیدگاه‌های جدیدتر', 'fartak' ) . ' ' . fartak_icon( 'chevron-left' ),
			)
		);
		?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() ) :
		?>
		<p style="font-size:12px;color:var(--mist)"><?php esc_html_e( 'دیدگاه‌ها بسته شده‌اند.', 'fartak' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit'       => 'btn-copper',
			'title_reply'        => __( 'دیدگاه خود را بنویسید', 'fartak' ),
			'title_reply_before' => '<h3 id="reply-title">',
			'title_reply_after'  => '</h3>',
			'comment_field'      => '<p class="comment-form-comment"><textarea id="comment" name="comment" rows="4" required placeholder="' . esc_attr__( 'دیدگاه شما…', 'fartak' ) . '"></textarea></p>',
			'fields'             => array(
				'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" required placeholder="' . esc_attr__( 'نام', 'fartak' ) . ' *"></p>',
				'email'  => '<p class="comment-form-email"><input id="email" name="email" type="email" required placeholder="' . esc_attr__( 'ایمیل', 'fartak' ) . ' *" dir="ltr"></p>',
				'url'    => '<p class="comment-form-url"><input id="url" name="url" type="url" placeholder="' . esc_attr__( 'وب‌سایت', 'fartak' ) . '" dir="ltr"></p>',
			),
		)
	);
	?>
</div>
