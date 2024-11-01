<?php global $ewd_urp_controller; ?>

<div class='ewd-urp-meta-field'>

	<label for='<?php echo esc_attr( $this->get_element_input_name() ); ?>_explanation'>
		<?php echo esc_html( $this->get_element_name() ); ?> <?php echo esc_html( $this->get_label( 'label-submit-explanation' ) ); ?>:
	</label>

	<textarea name='<?php echo esc_attr( $this->get_element_input_name() ); ?>_explanation' class='ewd-urp-review-textarea' <?php echo $this->element_required(); ?> ><?php echo ( isset( $_POST[ $this->get_element_input_name() . "_explanation"] ) ? esc_html( sanitize_textarea_field( $_POST[ $this->get_element_input_name() . "_explanation"] ) ) : '' ); ?></textarea>

	<?php if ( $ewd_urp_controller->settings->get_setting( 'review-character-limit' ) ) { ?>

		<div class='ewd-urp-review-character-count'  id='ewd-urp-review-character-count-" . $Textarea_Counter ."'>
			<?php _e( 'Characters remaining:', 'ultimate-reviews' ); ?> <?php echo intval( $ewd_urp_controller->settings->get_setting( 'review-character-limit' ) ); ?>
		</div>
	<?php } ?>

</div>