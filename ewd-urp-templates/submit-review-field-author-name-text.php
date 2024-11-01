<div class='form-field'>

	<label id='ewd-urp-review-author' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-author' ) ); ?>:
	</label>

	<input type='hidden' name='post_author_type' id='post_author_type' value='manual' />
	<input type='text' name='post_author' id='post_author' value='<?php echo ( ! empty( $_POST['post_author'] ) ? esc_attr( sanitize_text_field( $_POST['post_author'] ) ) : ''); ?>' <?php echo $this->element_required(); ?> />
		
	<div id='ewd-urp-author-explanation' class='ewd-urp-review-explanation'>
		
		<label for='explanation'></label>
		
		<span>
			<?php echo esc_html( $this->get_label( 'label-submit-author-comment' ) ); ?>
		</span>

	</div>

</div>