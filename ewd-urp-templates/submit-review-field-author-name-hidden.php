<?php global $ewd_urp_controller; ?>

<div class='form-field'>

	<label id='ewd-urp-review-author' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-author' ) ); ?>:
	</label>

	<input type='hidden' name='post_author_type' id='post_author_type' value='autoentered' />
	<input type='hidden' name='post_author_check' id='post_author_check' value='<?php echo sha1( $this->author_name . $ewd_urp_controller->settings->get_setting( 'salt' ) ); ?>' />
	<input type='hidden' name='post_author' id='post_author' value='<?php echo esc_attr( $this->author_name ); ?>' />

	<?php esc_html( $this->author_name ); ?>

</div>