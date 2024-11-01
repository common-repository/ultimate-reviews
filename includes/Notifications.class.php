<?php

/**
 * Class to handle sending notifications when a review is submitted
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'ewdurpNotifications' ) ) {
class ewdurpNotifications {

	public function __construct() {
		
		add_action( 'ewd_urp_insert_review', array( $this, 'admin_notification_email' ) );
		add_action( 'ewd_urp_insert_review', array( $this, 'email_confirmation_email' ) );
		add_action( 'ewd_urp_insert_review', array( $this, 'user_submission_email' ) );
	}

	/**
	 * Send an email to the site admin when a review is submitted, if selected
	 *
	 * @since 3.0.0
	 */
	public function admin_notification_email( $review ) {
		global $ewd_urp_controller;

		if ( ! $ewd_urp_controller->settings->get_setting( 'admin-notification' ) ) { return; }

		$post_id = $review->ID;

		$post = get_post( $post_id );
		$product_name = get_post_meta( $post_id, 'EWD_URP_Product_Name', true );

		$admin_email = $ewd_urp_controller->settings->get_setting( 'admin-email-address' ) ? $ewd_urp_controller->settings->get_setting( 'admin-email-address' ) : get_option( 'admin_email' );
	
		$review_link = site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit';
	
		$subject_line = __( 'New Review Received', 'ultimate-reviews' );
	
		$message_body = __( 'Hello Admin,', 'ultimate-reviews' ) . '<br/><br/>';
		$message_body .= __( 'You\'ve received a new review for the product ', 'ultimate-reviews' ) . ' ' . esc_html( $product_name ) . '.<br/><br/>';
		$message_body .= __( 'The review reads:<br>', 'ultimate-reviews' );
		$message_body .= esc_html( $post->post_content ) . '<br><br><br>';
		$message_body .= __( 'You can view the entire review by going to the following link:<br>', 'ultimate-reviews' );
		$message_body .= '<a href=\'' . $review_link . '\'>' . __( 'See the review', 'ultimate-reviews' ) . '</a><br/><br/>';
		$message_body .= __( 'Have a great day,', 'ultimate-reviews' ) . '<br/><br/>';
		$message_body .= __( 'Ultimate Reviews Team', 'ultimate-reviews' );
	
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $admin_email, $subject_line, $message_body, $headers );
	}

	/**
	 * Send an email to the review author when a review is submitted to confirm their email, if selected
	 *
	 * @since 3.0.0
	 */
	public function email_confirmation_email( $review ) {
		global $ewd_urp_controller;

		if ( ! $ewd_urp_controller->settings->get_setting( 'email-confirmation' ) ) { return; }

		$post_id = $review->ID;

		$email_address = get_post_meta( $post_id, 'EWD_URP_Post_Email', true );

		$confirmation_code = ewd_random_string();

		$args = array(
			'confirm_email'		=> 'true',
			'confirmation_code' => $confirmation_code,
			'post_id'			=> $post_id,
		);

		$confirmation_link = add_query_arg( $args, get_the_permalink() );

		update_post_meta( $post_id, 'EWD_URP_Confirmation_Code', $confirmation_code );
	
		$subject_line = __( 'Email Confirmation for Product Review', 'ultimate-reviews' );
	
		$message_body = __( 'Hello,', 'ultimate-reviews' ) . '<br/><br/>';
		$message_body .= __( 'Please confirm your email address for the product review you submitted titled ', 'ultimate-reviews' ) . esc_html( get_the_title( $post_id ) ) . ' ';
		$message_body .= __( 'by going to the following link:', 'ultimate-reviews') . '<br/><br/>';
		$message_body .= '<a href=\'' . $confirmation_link . '\'>' . __( 'Confirm your email address', 'ultimate-reviews' ) . '</a><br/><br/>';
		$message_body .= __( 'Thank you for the review, and have a great day,', 'ultimate-reviews' ) . '<br/><br/>';
		$message_body .= get_bloginfo( 'name');
	
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $email_address, $subject_line, $message_body, $headers );
	}

	/**
	 * Send an email to the review author using UWPM when a review is submitted, if selected
	 *
	 * @since 3.0.0
	 */
	public function user_submission_email( $review ) {
		global $ewd_urp_controller;

		if ( ! $ewd_urp_controller->settings->get_setting( 'email-on-submission' ) ) { return; }

		$args = array(
			'email_id'			=> $ewd_urp_controller->settings->get_setting( 'email-on-submission' ),
			'order_id'			=> $review->ID,
			'email_address'		=> get_post_meta( $post_id, 'EWD_URP_Post_Email', true )
		);
	
		if ( function_exists( 'ewd_uwpm_send_email' ) ) { ewd_uwpm_send_email( $args ); }
	}
}
} // endif;

