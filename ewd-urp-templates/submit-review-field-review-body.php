<?php global $ewd_urp_controller; ?>

<div class='ewd-urp-meta-field'>

	<label for='review_body'>
		<?php echo esc_html( $this->get_label( 'label-submit-review' ) ); ?>:
	</label>

	<textarea name='review_body' class='ewd-urp-review-textarea' required><?php echo ( ! empty( $_POST['review_body'] ) ? esc_textarea( sanitize_textarea_field( $_POST['review_body'] ) ) : '' ); ?></textarea>

	<?php if ( $ewd_urp_controller->settings->get_setting( 'review-character-limit' ) ) { ?>

		<div class='ewd-urp-review-character-count'>
			<?php _e( 'Characters remaining:', 'ultimate-reviews' ); ?> <?php echo esc_html( $ewd_urp_controller->settings->get_setting( 'review-character-limit' ) ); ?>
		</div>

	<?php } ?>

</div>