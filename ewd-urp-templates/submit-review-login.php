<div class='ewd-urp-login-message'>
	<?php __( 'Please log in to leave a review', 'ultimate-reviews' ); ?>
</div>

<div class='ewd-urp-login-options'>

	<a href='<?php echo ( $this->get_option( 'wordpress-login-url' ) ? esc_attr( $this->get_option( 'wordpress-login-url' ) ) : esc_attr( wp_login_url() ) ); ?>' ><?php _e( 'Login now', 'ultimate-reviews'); ?></a> 

</div>