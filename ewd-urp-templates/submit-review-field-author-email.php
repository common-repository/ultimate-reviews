<div class='form-field'>

	<label id='ewd-urp-author-email' class='ewd-urp-review-label'>
		<?php echo esc_html( $this->get_label( 'label-submit-reviewer-email-address' ) ); ?>:
	</label>

	<input type='email' name='post_email' id='post_email' value='<?php echo ( ! empty( $_REQUEST['post_email'] ) ? esc_attr( $_REQUEST['post_email'] ) : '' ); ?>' <?php echo $this->element_required(); ?> />
	
	<div id='ewd-urp-author-email-explanation' class='ewd-urp-review-explanation'>
		
		<label for='explanation'></label>

		<span>
			<?php echo esc_html( $this->get_label( 'label-submit-reviewer-email-address-desc' ) ); ?>
		</span>

	</div>

</div>