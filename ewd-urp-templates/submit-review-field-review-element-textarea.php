<div class='ewd-urp-meta-field'>

	<label for='<?php echo esc_attr( $this->get_element_name() ); ?>' class='ewd-urp-submit-review-label'>
		<?php echo esc_html( $this->get_element_name() ); ?>
	</label>

	<textarea name='<?php echo esc_attr( $this->get_element_input_name() ); ?>' <?php echo $this->element_required(); ?> class='ewd-urp-review-textarea'></textarea>

</div>