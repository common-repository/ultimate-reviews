<?php

/**
 * Create a shortcode to display multiple reviews
 * @since 3.0.0
 */
function ewd_urp_reviews_shortcode( $atts ) {
	global $ewd_urp_controller;

	// Define shortcode attributes
	$review_atts = array(
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
		'review_format' 		=> '',
		'reviews_skin'			=> '',
		'review_filtering' 		=> '',
		'orderby' 				=> '',
		'order' 				=> '',
		'group_by_product' 		=> '',
		'current_page' 			=> 1,
        'post_count' 			=> 0
	);

	if ( empty( $review_atts['orderby'] ) ) { $review_atts['orderby'] = $ewd_urp_controller->settings->get_setting( 'ordering-type' ); }

	if ( empty( $review_atts['order'] ) ) { $review_atts['order'] = $ewd_urp_controller->settings->get_setting( 'order-direction' ); }

	if ( ! empty( $ewd_urp_controller->settings->get_setting( 'email-confirmation' ) ) ) { $review_atts['email_confirmed'] = true; }

	// Create filter so addons can modify the accepted attributes
	$review_atts = apply_filters( 'ewd_urp_reviews_shortcode_atts', $review_atts );

	// Extract the shortcode attributes
	$args = shortcode_atts( $review_atts, $atts, 'ultimate-reviews' );

	$query = new ewdurpQuery( $args );

	$query->parse_request_args();
	$query->prepare_args();

	// Render reviews
	ewd_urp_load_view_files();

	$reviews = new ewdurpViewReviews( $args );

	$reviews->set_reviews( $query->get_reviews() );
	
	$ewd_urp_controller->shortcode_printing = true;

	$output = $reviews->render();

	$ewd_urp_controller->shortcode_printing = false;

	return $output;
}
add_shortcode( 'ultimate-reviews', 'ewd_urp_reviews_shortcode' );

/**
 * Create a shortcode to display a single review
 * @since 3.0.0
 */
function ewd_urp_review_shortcode( $atts ) {
	global $ewd_urp_controller;

	// Define shortcode attributes
	$review_atts = array(
		'review_id' => 0
	);

	// Create filter so addons can modify the accepted attributes
	$review_atts = apply_filters( 'ewd_urp_review_shortcode', $review_atts );

	// Extract the shortcode attributes
	$combined_atts = shortcode_atts( $review_atts, $atts, 'select-review' );

	$review_post = get_post( $combined_atts['review_id'] );

	if ( ! $review_post or $review_post->post_type != EWD_URP_REVIEW_POST_TYPE ) { return; }

	$review = new ewdurpReview();

	$review->load_post( $review_post );

	// Render review
	ewd_urp_load_view_files();
	$review_view = new ewdurpViewReview( array( 'review' => $review ) );

	$review_view->single_post = true;

	$ewd_urp_controller->shortcode_printing = true;

	$output = $review_view->render();

	$ewd_urp_controller->shortcode_printing = false;

	return $output;
}
add_shortcode( 'select-review', 'ewd_urp_review_shortcode' );

/**
 * Create a shortcode to display reviews with the search form
 * @since 3.0.0
 */
function ewd_urp_review_search_shortcode( $atts ) {
	global $ewd_urp_controller;

	if ( ! $ewd_urp_controller->permissions->check_permission( 'search' ) ) { return; }

	// Define shortcode attributes
	$review_atts = array(
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
        'post_count' 			=> 0,
        'show_on_load'			=> 'Yes'
	);

	if ( empty( $review_atts['orderby'] ) ) { $review_atts['orderby'] = $ewd_urp_controller->settings->get_setting( 'ordering-type' ); }

	if ( empty( $review_atts['order'] ) ) { $review_atts['order'] = $ewd_urp_controller->settings->get_setting( 'order-direction' ); }

	if ( ! empty( $ewd_urp_controller->settings->get_setting( 'email-confirmation' ) ) ) { $review_atts['email_confirmed'] = true; }

	// Create filter so addons can modify the accepted attributes
	$review_atts = apply_filters( 'ewd_urp_review_search_shortcode_atts', $review_atts );

	// Extract the shortcode attributes
	$args = shortcode_atts( $review_atts, $atts, 'ultimate-review-search' );

	$query = new ewdurpQuery( $args );

	$query->parse_request_args();
	$query->prepare_args();

	// Render reviews
	ewd_urp_load_view_files();

	$reviews = new ewdurpViewReviewSearch( $args );

	$reviews->set_reviews( $query->get_reviews() );

	$ewd_urp_controller->shortcode_printing = true;

	$output = $reviews->render();

	$ewd_urp_controller->shortcode_printing = false;

	return $output;
}
add_shortcode( 'ultimate-review-search', 'ewd_urp_review_search_shortcode' );

/**
 * Create a shortcode to display only the summary statistics for reviews
 * @since 3.0.0
 */
function ewd_urp_summary_statistics_shortcode( $atts ) {
	global $ewd_urp_controller;

	// Define shortcode attributes
	$review_atts = array(
		'product_name' 		=> '',
		'include_category' 	=> '',
		'exclude_category' 	=> '',
		'all_products' 		=> 'No',
        'summary_type'		=> '',
        'show_reviews'		=> false
	);

	if ( empty( $review_atts['summary_type'] ) ) { $review_atts['summary_type'] = $ewd_urp_controller->settings->get_setting( 'summary-statistics' ); }

	if ( isset( $_GET['product_reviews'] ) ) { 

		$review_atts['product_name'] = sanitize_text_field( $_GET['product_reviews'] );

		$review_atts['show_reviews'] = true;
	}

	// Create filter so addons can modify the accepted attributes
	$review_atts = apply_filters( 'ewd_urp_summary_statistics_shortcode_atts', $review_atts );

	$query = new ewdurpQuery( $review_atts );

	$query->prepare_args();

	// Extract the shortcode attributes
	$args = shortcode_atts( $review_atts, $atts, 'reviews-summary' );

	// Render reviews
	ewd_urp_load_view_files();

	$reviews = new ewdurpViewSummaryStatistics( $args );

	$reviews->set_reviews( $query->get_reviews() );

	$ewd_urp_controller->shortcode_printing = true;

	$output = $reviews->render();

	$ewd_urp_controller->shortcode_printing = false;

	return $output;
}
add_shortcode( 'reviews-summary', 'ewd_urp_summary_statistics_shortcode' );

/**
 * Create a shortcode to display a single review
 * @since 3.0.0
 */
function ewd_urp_submit_review_shortcode( $atts ) {
	global $ewd_urp_controller;

	// Define shortcode attributes
	$review_atts = array(
		'product_name' 			=> '',
		'submit_review_toggle' 	=> '',
		'redirect_page' 		=> '',
		'success_message' 		=> __( 'Thank you for submitting a review.', 'ultimate-reviews' ),
		'draft_message' 		=> __( 'Your review will be visible once it\'s approved by an administrator.', 'ultimate-reviews' ),
		'review_form_title' 	=> __( 'Submit a Review', 'ultimate-reviews' ),
		'review_instructions' 	=> __( 'Please fill out the form below to submit a review.', 'ultimate-reviews' ),
		'submit_text' 			=> __( 'Send Review', 'ultimate-reviews' )
	);

	// Handle review submission
	if ( isset( $_POST['submit_review'] ) ) {

		$review_atts['review_submitted'] = true;

		$review = new ewdurpReview();
		$success = $review->insert_review();

		if ( $success ) {
			
			if ( ! empty( $atts['redirect_page'] ) ) { 
				
				wp_redirect( $atts['redirect_page'] );

				exit();
			}

			if ( $review->post_status == 'draft' ) { $review_atts['update_message'] = $review_atts['draft_message']; }
			else { $review_atts['update_message'] = $review_atts['success_message']; }
		}
		else {

			$review_atts['update_message'] = '';

			foreach ( $review->validation_errors as $validation_error ) {

				$review_atts['update_message'] .= '<br />' . $validation_error['message'];
			}
		}
	}

	// Handle email confirmation
	if ( isset( $_GET['confirm_email'] ) ) {

		$review = new ewdurpReview();

		$success = $review->confirm_email();

		if ( $success ) {

			$review_atts['update_message'] = __( 'Thank you for confirming your email address.', 'ultimate-reviews' );
		}
		else {

			$review_atts['update_message'] = __( 'There was an error while confirming your email address, please contact the site administrator.', 'ultimate-reviews' );
		}
	}

	// Create filter so addons can modify the accepted attributes
	$review_atts = apply_filters( 'ewd_urp_submit_review_shortcode', $review_atts );

	// Extract the shortcode attributes
	$args = shortcode_atts( $review_atts, $atts, 'submit-review' );

	// Render menu
	ewd_urp_load_view_files();
	$submit_review = new ewdurpViewSubmitReview( $args );

	$ewd_urp_controller->shortcode_printing = true;

	$output = $submit_review->render();

	$ewd_urp_controller->shortcode_printing = false;

	return $output;
}
add_shortcode( 'submit-review', 'ewd_urp_submit_review_shortcode' );


function ewd_urp_load_view_files() {

	$files = array(
		EWD_URP_PLUGIN_DIR . '/views/Base.class.php' // This will load all default classes
	);

	$files = apply_filters( 'ewd_urp_load_view_files', $files );

	foreach( $files as $file ) {
		require_once( $file );
	}

}

if ( ! function_exists( 'ewd_urp_validate_captcha' ) ) {
function ewd_urp_validate_captcha() {

	$modifiedcode = intval( $_POST['ewd_urp_modified_captcha'] );
	$usercode = intval( $_POST['ewd_urp_captcha'] );

	$code = ewd_urp_decrypt_catpcha_code( $modifiedcode );

	$validate_captcha = $code == $usercode ? 'Yes' : 'No';

	return $validate_captcha;
}
}

if ( ! function_exists( 'ewd_urp_encrypt_captcha_code' ) ) {
function ewd_urp_encrypt_captcha_code( $code ) {
	
	$modifiedcode = ($code + 5) * 3;

	return $modifiedcode;
}
}

if ( ! function_exists( 'ewd_urp_encrypt_captcha_code' ) ) {
function ewd_urp_decrypt_catpcha_code( $modifiedcode ) {
	
	$code = ($modifiedcode / 3) - 5;

	return $code;
}
}

if ( ! function_exists( 'ewd_urp_decode_infinite_table_setting' ) ) {
function ewd_urp_decode_infinite_table_setting( $values ) {

	if ( empty( $values ) ) { return array(); }
	
	return is_array( json_decode( html_entity_decode( $values ) ) ) ? json_decode( html_entity_decode( $values ) ) : array();
}
}

if ( ! function_exists( 'ewd_urp_get_default_review_elements' ) ) {
function ewd_urp_get_product_names( $status = 'publish' ) {

    global $wpdb;

    $r = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT(pm.meta_value) FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'EWD_URP_Product_Name' 
        AND p.post_status = '%s' 
        AND p.post_type = 'urp_review'
    ", $status ) );

    return $r;
}
}

if ( ! function_exists( 'ewd_urp_uwpm_product_review_links' ) ) {
function ewd_urp_uwpm_product_review_links( $params, $user ) {

    if ( ! isset( $params['post_id'] ) ) {return;}
		
	$order = new WC_Order( $params['post_id'] );
	$items = $order->get_items();
		
	if ( is_array( $params['attributes'] ) ) {

		foreach ( $params['attributes'] as $attribute_name => $attribute_value ) {

			if ( $attribute_name != 'ewd_urp_submit_review_url' ) {continue;}
		
			$link = $attribute_value;

			if ( strpos( $link, '?' ) === false) { $link .= '?src=urp_email'; }
			else { $link .= '&src=urp_email'; }

			$link .= '&order_id=' . $params['post_id'];

			if ( isset( $params['Email_Address'] ) ) { $link .= '&Post_Email=' . $params['Email_Address']; }

			$product_links = '<table>';
			foreach ( $items as $product ) {

				$product_link = $link . '&product_name=' . $product['name'];
				$product_links .= '<tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top"><a href="' . $product_link . '" class="btn-primary" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . $product['name'] . '</a></td></tr>';
			}
			$product_links .= '</table>';
		}
	}

	return $product_links;
}
}

/**
 * Opens a buffer in case a redirect is needed after submitting a review
 * @since 3.1.4
 */
if ( !function_exists( 'ewd_urp_add_ob_start' ) ) {
function ewd_urp_add_ob_start() { 
    ob_start();
}
} // endif;
add_action( 'init', 'ewd_urp_add_ob_start' );

/**
 * Closes a buffer in case a redirect is needed after submitting a review
 * @since 3.1.4
 */
if ( !function_exists( 'ewd_urp_flush_ob_end' ) ) {
function ewd_urp_flush_ob_end() {
    if ( ob_get_length() ) { ob_end_clean(); }
}
} // endif;
add_action( 'shutdown', 'ewd_urp_flush_ob_end' );

if ( ! function_exists( 'ewd_hex_to_rgb' ) ) {
function ewd_hex_to_rgb( $hex ) {

	$hex = str_replace("#", "", $hex);

	// return if the string isn't a color code
	if ( strlen( $hex ) !== 3 and strlen( $hex ) !== 6 ) { return '0,0,0'; }

	if(strlen($hex) == 3) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}

	$rgb = $r . ", " . $g . ", " . $b;
  
	return $rgb;
}
}

if ( ! function_exists( 'ewd_format_classes' ) ) {
function ewd_format_classes( $classes ) {

	if ( count( $classes ) ) {
		return ' class="' . join( ' ', $classes ) . '"';
	}
}
}

if ( ! function_exists( 'ewd_add_frontend_ajax_url' ) ) {
function ewd_add_frontend_ajax_url() { ?>
    
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>
<?php }
}

if ( ! function_exists( 'ewd_random_string' ) ) {
function ewd_random_string( $length = 10 ) {

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';

    for ( $i = 0; $i < $length; $i++ ) {

        $randstring .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
    }

    return $randstring;
}
}