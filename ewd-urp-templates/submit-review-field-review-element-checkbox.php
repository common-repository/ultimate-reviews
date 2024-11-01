<div class='ewd-urp-meta-field'>

	<label for='<?php echo esc_attr( $this->get_element_name() ); ?>' class='ewd-urp-submit-review-label'>
		<?php echo esc_html( $this->get_element_name() ); ?>
	</label>

	<div class='ewd-urp-submit-review-radio-checkbox-container'>
	
		<?php foreach ( $this->get_element_options() as $option ) { ?>

			<div class='ewd-urp-submit-review-radio-checkbox-each'>
				<input type='checkbox' name='<?php echo esc_attr( $this->get_element_input_name() ); ?>[]' value='<?php echo $option; ?>' data-required='<?php echo $this->element_required(); ?>'/><?php echo $option; ?>
			</div>
			
		<?php } ?>

	</div>

</div>