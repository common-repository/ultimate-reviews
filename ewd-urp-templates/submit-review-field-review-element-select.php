<div class='ewd-urp-meta-field'>

	<label for='<?php echo esc_attr( $this->get_element_name() ); ?>' class='ewd-urp-submit-review-label'>
		<?php echo esc_html( $this->get_element_name() ); ?>
	</label>

	<select name='<?php echo esc_attr( $this->get_element_input_name() ); ?>' <?php echo $this->element_required(); ?> >
		
		<?php foreach ( $this->get_element_options() as $option ) { ?>
			<option value='<?php echo $option; ?>'><?php echo $option; ?></option>
		<?php } ?>

	</select>
</div>