<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdurpReviewAsk' ) ) {
/**
 * Class to handle plugin review ask
 *
 * @since 3.0.0
 */
class ewdurpReviewAsk {

	public function __construct() {
		
		add_action( 'admin_notices', array( $this, 'maybe_add_review_ask' ) );

		add_action( 'wp_ajax_ewd_urp_hide_review_ask', array( $this, 'hide_review_ask' ) );
		add_action( 'wp_ajax_ewd_urp_send_feedback', array( $this, 'send_feedback' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_review_ask_scripts' ) );
	}

	public function maybe_add_review_ask() { 
		
		$ask_review_time = get_option( 'ewd-urp-review-ask-time' );

		$install_time = get_option( 'ewd-urp-installation-time' );
		if ( ! $install_time ) { update_option( 'ewd-urp-installation-time', time() ); $install_time = time(); }

		$ask_review_time = $ask_review_time != '' ? $ask_review_time : $install_time + 3600*24*4;
		
		if ( $ask_review_time < time() and $install_time != '' and $install_time < time() - 3600*24*4 ) {
			
			global $pagenow;

			if ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ) { ?>
	
				<div class='notice notice-info is-dismissible ewd-urp-main-dashboard-review-ask' style='display:none'>
					<div class='ewd-urp-review-ask-plugin-icon'></div>
					<div class='ewd-urp-review-ask-text'>
						<p class='ewd-urp-review-ask-starting-text'>Enjoying using the Ultimate Reviews?</p>
						<p class='ewd-urp-review-ask-feedback-text ewd-urp-hidden'>Help us make the plugin better! Please take a minute to rate the plugin. Thanks!</p>
						<p class='ewd-urp-review-ask-review-text ewd-urp-hidden'>Please let us know what we could do to make the plugin better!<br /><span>(If you would like a response, please include your email address.)</span></p>
						<p class='ewd-urp-review-ask-thank-you-text ewd-urp-hidden'>Thank you for taking the time to help us!</p>
					</div>
					<div class='ewd-urp-review-ask-actions'>
						<div class='ewd-urp-review-ask-action ewd-urp-review-ask-not-really ewd-urp-review-ask-white'>Not Really</div>
						<div class='ewd-urp-review-ask-action ewd-urp-review-ask-yes ewd-urp-review-ask-green'>Yes!</div>
						<div class='ewd-urp-review-ask-action ewd-urp-review-ask-no-thanks ewd-urp-review-ask-white ewd-urp-hidden'>No Thanks</div>
						<a href='https://wordpress.org/support/plugin/ultimate-reviews/reviews/' target='_blank'>
							<div class='ewd-urp-review-ask-action ewd-urp-review-ask-review ewd-urp-review-ask-green ewd-urp-hidden'>OK, Sure</div>
						</a>
					</div>
					<div class='ewd-urp-review-ask-feedback-form ewd-urp-hidden'>
						<div class='ewd-urp-review-ask-feedback-explanation'>
							<textarea></textarea>
							<br>
							<input type="email" name="feedback_email_address" placeholder="<?php _e('Email Address', 'ultimate-reviews'); ?>">
						</div>
						<div class='ewd-urp-review-ask-send-feedback ewd-urp-review-ask-action ewd-urp-review-ask-green'>Send Feedback</div>
					</div>
					<div class='ewd-urp-clear'></div>
				</div>

			<?php
			}
		}
		else {
			wp_dequeue_script( 'ewd-urp-review-ask-js' );
			wp_dequeue_style( 'ewd-urp-review-ask-css' );
		}
	}

	public function enqueue_review_ask_scripts() {

		wp_enqueue_style( 'ewd-urp-review-ask-css', EWD_URP_PLUGIN_URL . '/assets/css/dashboard-review-ask.css' );
		wp_enqueue_script( 'ewd-urp-review-ask-js', EWD_URP_PLUGIN_URL . '/assets/js/dashboard-review-ask.js', array( 'jquery' ), EWD_URP_VERSION, true  );

		wp_localize_script(
			'ewd-urp-review-ask-js',
			'ewd_urp_review_ask',
			array(
				'nonce' => wp_create_nonce( 'ewd-urp-review-ask-js' )
			)
		);
	}

	public function hide_review_ask() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-review-ask-js', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		$ask_review_time = intval( $_POST['ask_review_time'] );

		if ( get_option( 'ewd-urp-review-ask-time' ) < time() + 3600*24 * $ask_review_time ) {
			update_option( 'ewd-urp-review-ask-time', time() + 3600*24 * $ask_review_time );
		}

		die();
	}

	public function send_feedback() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-review-ask-js', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";  
		$feedback = sanitize_text_field( $_POST['feedback'] );
		$feedback .= '<br /><br />Email Address: ';
		$feedback .= sanitize_email( $_POST['email_address'] );

		wp_mail('contact@etoilewebdesign.com', 'URP Feedback - Dashboard Form', $feedback, $headers);

		die();
	}
}

}