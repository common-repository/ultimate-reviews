<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ewdurpAJAX' ) ) {
	/**
	 * Class to handle AJAX interactions for Ultimate Reviews
	 *
	 * @since 3.0.0
	 */
	class ewdurpAJAX {

		public function __construct() { 

			add_action( 'wp_ajax_ewd_urp_search', array( $this, 'return_search_results' ) );
			add_action( 'wp_ajax_nopriv_ewd_urp_search', array( $this, 'return_search_results' ) );

			add_action( 'wp_ajax_ewd_urp_record_view', array( $this, 'record_view' ) );
			add_action( 'wp_ajax_nopriv_ewd_urp_record_view', array( $this, 'record_view' ) );

			add_action( 'wp_ajax_ewd_urp_update_karma', array( $this, 'update_karma' ) );
			add_action( 'wp_ajax_nopriv_ewd_urp_update_karma', array( $this, 'update_karma' ) );

			add_action( 'wp_ajax_ewd_urp_get_review_body', array( $this, 'get_review_body' ) );
			add_action( 'wp_ajax_nopriv_ewd_urp_get_review_body', array( $this, 'get_review_body' ) );

			add_action( 'wp_ajax_ewd_urp_flag_inappropriate', array( $this, 'flag_inappropriate' ) );
			add_action( 'wp_ajax_nopriv_ewd_urp_flag_inappropriate', array( $this, 'flag_inappropriate' ) );

			add_action( 'wp_ajax_ewd_urp_send_test_email', array( $this, 'send_test_email' ) );

			add_action( 'wp_ajax_ewd_urp_hide_uwpm_banner', array( $this, 'hide_uwpm_banner' ) );
		}

		/**
		 * Get the results of review filtering or search
		 * @since 3.0.0
		 */
		public function return_search_results() {
			global $ewd_urp_controller;

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-js', 'nonce' ) ) {
				ewdurpHelper::bad_nonce_ajax();
			}

			$reviews_atts = array(
				'shortcode_id' 			=> '',
				'search_string' 		=> '',
				'include_category' 		=> '',
				'exclude_category' 		=> '',
				'include_category_ids' 	=> '',
				'exclude_category_ids' 	=> '',
				'include_ids' 			=> '',
				'exclude_ids' 			=> '',
				'product_name' 			=> '',
				'review_author' 		=> '',
				'custom_filters' 		=> '',
				'reviews_per_page' 		=> 0,
				'only_reviews' 			=> 'No',
				'reviews_objects'		=> 'No',
				'min_score' 			=> 0,
				'max_score' 			=> 1000000,
				'review_skin' 			=> '',
				'review_format' 		=> '',
				'review_filtering' 		=> '',
				'orderby' 				=> '',
				'order' 				=> '',
				'group_by_product' 		=> '',
				'current_page' 			=> 1,
    		    'post_count' 			=> 0
			);

			$query = new ewdurpQuery( $reviews_atts );

			$query->parse_request_args();
			$query->prepare_args();

			$reviews = new ewdurpViewReviews( $reviews_atts );

			$reviews->set_reviews( $query->get_reviews() );

			$reviews->set_reviews_options();

			$reviews->set_request_parameters();

			$reviews->create_reviews_data();

			ob_start();

			$ewd_urp_controller->shortcode_printing = true;

			$reviews->print_reviews();

			$ewd_urp_controller->shortcode_printing = false;

			$output = ob_get_clean();

			wp_send_json_success(
				array(
					'output' 		=> $output,
					'reviews_count' => $reviews->review_count,
					'max_page'		=> $reviews->max_page
				)
			);

		    die();
		}

		/**
		 * Records the number of time a review post is opened
		 * @since 3.0.0
		 */
		public function record_view() {
			global $wpdb;

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-js', 'nonce' ) ) {
				ewdurpHelper::bad_nonce_ajax();
			}

			$post_id = intval( $_POST['post_id'] );

			$views = get_post_meta( $post_id, 'urp_view_count', true ) + 1;

			update_post_meta( $post_id, 'urp_view_count', $views );
			
			die();
		}

		/**
		 * Update the 'karma' rating for a review, save to $_COOKIE array
		 * to reduce multiple karma clicks from a single user
		 * @since 3.0.0
		 */
		public function update_karma() {

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-js', 'nonce' ) ) {
				ewdurpHelper::bad_nonce_ajax();
			}

			$review_id = intval( $_POST['review_id'] );
			$direction = sanitize_text_field( $_POST['direction'] );

			$karma_ids = isset( $_COOKIE['EWD_URP_Karma_IDs'] ) ? json_decode( $_COOKIE['EWD_URP_Karma_IDs'] ) : array();
			$karma_ids = is_array( $karma_ids ) ? array_map( 'intval', $karma_ids ) : array();

			if ( in_array( $review_id, $karma_ids ) ) { die(); }

			$karma = get_post_meta( $review_id, 'EWD_URP_Review_Karma', true );

			if ( $direction == 'down' ) { update_post_meta( $review_id, 'EWD_URP_Review_Karma', $karma - 1 ); }
			else { update_post_meta( $review_id, 'EWD_URP_Review_Karma', $karma + 1 ); }

			$karma_ids[] = $review_id;

			setcookie( 'EWD_URP_Karma_IDs', json_encode( $karma_ids ), time() + 3600*24*365, '/' );

			die();
		}

		/**
		 * Retrieve the complete body of a review
		 * @since 3.0.0
		 */
		public function get_review_body() {
			global $ewd_urp_controller;

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-js', 'nonce' ) ) {
				ewdurpHelper::bad_nonce_ajax();
			}

			$review_id = intval( $_POST['review_id'] );

			echo "<span class='ewd-urp-ajax-read-more-content'>";
			echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $review_id ) ) );
			echo "<span class='ewd-urp-ajax-read-less' data-thumbnailchars='" . esc_attr( $ewd_urp_controller->settings->get_setting( 'thumbnail-characters' ) ) . "'>" . __( 'Read Less', 'ultimate-reviews' ) . "</span>";
			echo "</span>";

			die();
		}

		/**
		 * Flag a review as containing inappropriate content
		 * @since 3.0.0
		 */
		public function flag_inappropriate() {

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-js', 'nonce' ) ) {
				ewdurpHelper::bad_nonce_ajax();
			}

			$review_id = intval( $_POST['review_id'] );

			$flags = get_post_meta( $review_id, 'EWD_URP_Flag_Inappropriate', true );

			update_post_meta( $review_id, 'EWD_URP_Flag_Inappropriate', $flags + 1 );

			die();
		}

		/**
		 * Send a test email for the review reminder emails
		 * @since 3.0.0
		 */
		public function send_test_email() {
			global $ewd_urp_controller;

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-admin-js', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
				ewdurpHelper::admin_nopriv_ajax();
			}

			$email_address = sanitize_email( $_POST['email_address'] );
			$email_to_send = sanitize_text_field( $_POST['email_to_send'] );

			foreach ( ewd_urp_decode_infinite_table_setting( $ewd_urp_controller->settings->get_setting( 'email-messages-array' ) ) as $email_message ) {

				if ( $email_message->id == $email_to_send ) {

					$message_body = $ewd_urp_controller->woocommerce->get_email_template( $email_message );
					$headers = array('Content-Type: text/html; charset=UTF-8');
					$mail_success = wp_mail( $email_address, $email_message->subject, $message_body, $headers );
				}
			}

			if ( ! empty( $mail_success ) ) { 

				echo '<div class="ewd-urp-test-email-response">Success: Email has been sent successfully.</div>';
			}
			else {

				echo '<div class="ewd-urp-test-email-response">Error: Please check your email settings, or try using an SMTP email plugin to change email settings.</div>';
			}

			die();
		}

		public function hide_uwpm_banner() {

			// Authenticate request
			if ( ! check_ajax_referer( 'ewd-urp-admin-js', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
				ewdurpHelper::admin_nopriv_ajax();
			}

			$time = time() + intval( $_POST['hide_length'] ) * 24*3600;

			update_option( 'EWD_URP_UWPM_Ask_Time', $time );

			die();
		}
	}
}