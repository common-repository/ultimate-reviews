<div class='form-field'>

	<label id='ewd-urp-review-title' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-title' ) ); ?>:
	</label>

	<input type='text' name='review_title' id='review_title' value='<?php echo ( ! empty( $_POST['review_title'] ) ? esc_attr( sanitize_text_field( $_POST['review_title'] ) ) : '' ); ?>' <?php echo $this->element_required(); ?> />

	<div id='ewd-urp-title-explanation' class='ewd-urp-review-explanation'>
		
		<label for='explanation'></label>

		<span>
			<?php echo esc_html( $this->get_label( 'label-submit-title-comment' ) ); ?>:
		</span>

	</div>

</div>