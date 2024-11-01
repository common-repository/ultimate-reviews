<div class='ewd-urp-meta-field'>

	<label for='<?php echo esc_attr( $this->get_element_name() ); ?>' class='ewd-urp-submit-review-label'>
		<?php echo esc_html( $this->get_element_name() ); ?>
	</label>

	<input type='datetime-local' id='ewd-urp-<?php echo esc_attr( $this->get_element_name() ); ?>' name='<?php echo esc_attr( $this->get_element_input_name() ); ?>' <?php echo $this->element_required(); ?>/>

</div>