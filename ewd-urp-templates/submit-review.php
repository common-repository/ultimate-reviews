<?php global $ewd_urp_controller; ?>

<?php $login_required = $this->maybe_display_login(); ?>

<?php if ( $login_required ) { return; } ?>

<div <?php echo ewd_format_classes( $this->classes ); ?> >

	<?php $this->maybe_display_submitted_review_message(); ?>

	<form id='review' method='post' enctype='multipart/form-data'>

		<?php $this->maybe_display_order_id_input(); ?>

		<?php $this->maybe_display_woocommerce_email_input(); ?>

		<?php foreach ( $ewd_urp_controller->settings->get_review_elements() as $count => $review_element ) { ?>
			
			<?php $this->display_form_field( $review_element ); ?>

		<?php } ?>

		<?php $this->maybe_display_captcha_field(); ?>

		<div class='ewd-urp-submit'>

			<label for='submit'></label>

			<span class='submit'>

				<input type='submit' name='submit_review' id='ewd-urp-review-submit' class='button-primary' value='<?php echo esc_attr( $this->get_label( 'label-submit-button' ) ); ?>'  />
		
			</span>

		</div>

	</form>

	<?php $this->maybe_display_review_toggle(); ?>

</div>