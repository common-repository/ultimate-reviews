<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ewdurpReview' ) ) {
/**
 * Class to handle a review for Ultimate Reviews
 *
 * @since 3.0.0
 */
class ewdurpReview {

	// from WP post
	public $post;
	public $ID;
	public $title;
	public $date;
	public $review;
	public $post_status;

	// post terms
	public $categories;

	// overall review score
	public $score;	

	// post meta
	public $review_karma;
	public $author_email;
	public $email_confirmed;
	public $review_video;
	public $product_name;
	public $review_post_author;
	
	/**
	 * Whether or not this request has been processed. Used to prevent
	 * duplicate forms on one page from processing a review form more than
	 * once.
	 * @since 3.0.0
	 */
	public $review_processed = false;

	/**
	 * Whether or not this request was successfully saved to the database.
	 * @since 3.0.0
	 */
	public $review_inserted = false;

	/**
	 * Holds all validation errors found during booking request validation
	 * @since 3.2.11
	 */
	public $validation_errors = array();

	public $values = array();

	public $scores = array();

	public $explanation = array();

	public function __construct() {}

	/**
	 * Load the review information from a WP_Post object or an ID
	 *
	 * @uses load_wp_post()
	 * @since 3.0.0
	 */
	public function load_post( $post ) {

		if ( is_int( $post ) || is_string( $post ) ) {
			$post = get_post( $post );
		}

		if ( get_class( $post ) == 'WP_Post' && $post->post_type == EWD_URP_REVIEW_POST_TYPE ) {
			$this->load_wp_post( $post );
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Load data from WP post object and retrieve metadata
	 *
	 * @uses load_post_metadata()
	 * @since 3.0.0
	 */
	public function load_wp_post( $post ) {

		// Store post for access to other data if needed by extensions
		$this->post = $post;

		$this->ID = $post->ID;
		$this->title = $post->post_title;
		$this->date = $post->post_date;
		$this->review = $post->post_content;
		$this->post_status = $post->post_status;

		$this->categories = get_the_terms( $post, EWD_URP_REVIEW_CATEGORY_TAXONOMY );
		if ( ! is_array( $this->categories ) ) { $this->categories = array(); }

		$this->load_scores();
		$this->load_post_metadata();

		do_action( 'ewd_urp_review_load_post_data', $this, $post );
	}

	/**
	 * Store score information for post
	 * @since 3.0.0
	 */
	public function load_scores() {
		global $ewd_urp_controller;

		$this->score = get_post_meta( $this->ID, 'EWD_URP_Overall_Score', true );

		foreach ( $ewd_urp_controller->settings->get_review_elements() as $review_element ) {
			if ( $review_element->name == '' or $review_element->type == 'default' ) { continue; }

			$this->scores[ $review_element->name ] 		= get_post_meta( $this->ID, "EWD_URP_" . $review_element->name, true );
			$this->explanations[ $review_element->name ] 	= get_post_meta( $this->ID, "EWD_URP_" . $review_element->name . '_Description', true );
		}
	}

	/**
	 * Store metadata for post
	 * @since 3.0.0
	 */
	public function load_post_metadata() {

		$this->review_karma = get_post_meta( $this->ID, 'EWD_URP_Review_Karma', true );
		$this->author_email = get_post_meta( $this->ID, 'EWD_URP_Post_Email', true );
		$this->email_confirmed = get_post_meta( $this->ID, 'EWD_URP_Email_Confirmed', true );
		$this->review_video = get_post_meta( $this->ID, 'EWD_URP_Review_Video', true );
		$this->product_name = get_post_meta( $this->ID, 'EWD_URP_Product_Name', true );
		$this->review_post_author = get_post_meta( $this->ID, 'EWD_URP_Post_Author', true );
	}

	/**
	 * Insert a new review submission into the database
	 *
	 * Validates the data, adds it to the database and executes notifications
	 * @since 3.0.0
	 */
	public function insert_review() {

		// Check if this request has already been processed. If multiple forms
		// exist on the same page, this prevents a single submission from
		// being added twice.
		if ( $this->review_processed === true ) {
			return true;
		}

		$this->review_processed = true;

		if ( empty( $this->ID ) ) {
			$action = 'insert';
		} else {
			$action = 'update';
		}

		$this->validate_submission();
		if ( $this->is_valid_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) { 
			return false;
		} else {
			$this->review_inserted = true;
		}

		do_action( 'ewd_urp_' . $action . '_review', $this );

		return true;
	}

	/**
	 * Validate submission data. Expects to find data in $_POST.
	 * @since 3.0.0
	 */
	public function validate_submission() {
		global $ewd_urp_controller;

		$this->validation_errors = array();

		if ( $ewd_urp_controller->settings->get_setting( 'use-captcha' ) ) {

			if ( $ewd_urp_controller->settings->get_setting( 'captcha-type' ) == 'recaptcha' ) {

				if ( ! isset( $_POST['g-recaptcha-response'] ) ) {

					$this->validation_errors[] = array(
						'field'		=> 'recaptcha',
						'error_msg'	=> 'No reCAPTCHA code',
						'message'	=> __( 'Please fill out the reCAPTCHA box  before submitting.', 'ultimate-reviews' ),
					);

				}
				else {

					$secret_key = $ewd_urp_controller->settings->get_setting( 'captcha-secret-key' );
					$captcha = sanitize_text_field( $_POST['g-recaptcha-response'] );

					$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode( $secret_key ) .  '&response=' . urlencode( $captcha );
					$json_response = file_get_contents( $url );
					$response = json_decode( $json_response );

					$reCaptcha_error = false;
					if ( json_last_error() != JSON_ERROR_NONE ) {

						$response = new stdClass();
						$response->success = false;
						$reCaptcha_error = true;

						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

							error_log( 'RTB reCAPTCHA error. Raw respose: ' . print_r( [$json_response], true ) );
						}
					}

					if ( ! $response->success ) {

						$message = __( 'Please fill out the reCAPTCHA box again and re-submit.', 'ultimate-reviews' );
						
						if ( $reCaptcha_error ) {

							$message .= __( 'If you encounter reCAPTCHA error multiple times, please contact us.', 'ultimate-reviews' );
						}

						$this->validation_errors[] = array(
							'field'		=> 'recaptcha',
							'error_msg'	=> 'Invalid reCAPTCHA code',
							'message'	=> $message,
						);
					}
				}
			}
			else {

				$modified_code = intval( $_POST['ewd_urp_modified_captcha'] );
				$user_code = intval( $_POST['ewd_urp_captcha'] );
	
				if ( $user_code != $this->decrypt_modified_code( $modified_code ) ) {
	
					$this->validation_errors[] = array(
						'field'		=> 'captcha',
						'error_msg'	=> 'Captcha incorrect',
						'message'	=> __( 'The number you entered for the image was incorrect.', 'ultimate-reviews' ),
					);
				}
			}
		}

		// Blacklisting
		if ( $ewd_urp_controller->settings->get_setting( 'review-blacklist' ) ) {

			$blacklist_array = preg_split( '/\r\n|[\r\n]/', $ewd_urp_controller->settings->get_setting( 'review-blacklist' ) );

			foreach ( $_POST as $field => $value ) {

				if ( in_array( $value, $blacklist_array ) ) {

					$this->validation_errors[] = array(
						'field'		=> $field,
						'error_msg'	=> 'Blacklisted field',
						'message'	=> __( 'You have an invalid submission in one of your fields: ', 'ultimate-reviews' ) . $value,
					);
				}
			}

			if ( in_array( $_SERVER['REMOTE_ADDR'], $blacklist_array ) ) {
				
				$this->validation_errors[] = array(
					'field'		=> 'IP Address',
					'error_msg'	=> 'Blacklisted field',
					'message'	=> __( 'Your IP address has been blocked from submitting reviews by the site administrator.', 'ultimate-reviews' ),
				);
			}

		}

		// Validation of fields
		foreach ( $ewd_urp_controller->settings->get_review_elements() as $review_element ) {

			// validation for the default fields
			if ( $review_element->type == 'default' ) { 

				if ( $review_element->name == 'Product Name (if applicable)' ) 			{ $this->validate_product_name_field(); }
				elseif ( $review_element->name == 'Review Author' ) 					{ $this->validate_review_author_field(); }
				elseif ( $review_element->name == 'Review Title' ) 						{ $this->validate_review_title_field(); }
				elseif ( $review_element->name == 'Overall Score' ) 					{ $this->validate_overall_score_field(); }
				elseif ( $review_element->name == 'Reviewer Email (if applicable)' ) 	{ $this->validate_reviewer_email_field(); }
				elseif ( $review_element->name == 'Review' ) 							{ $this->validate_review_body_field(); }
				elseif ( $review_element->name == 'Review Image (if applicable)' ) 		{ $this->validate_review_image_field(); }
				elseif ( $review_element->name == 'Review Video (if applicable)' ) 		{ $this->validate_review_video_field(); }
				elseif ( $review_element->name == 'Review Category (if applicable)' ) 	{ $this->validate_review_category_field(); }
			}
			// validation for the review score fields
			elseif ( $review_element->type == '' or $review_element->type == 'reviewitem' ) {

				$this->validate_score_field( $review_element );
			}
			// validation for non-score custom fields 
			else {

				if ( $review_element->type == 'text' ) 			{ $this->validate_custom_text_field( $review_element ); }
				elseif ( $review_element->type == 'textarea' ) 	{ $this->validate_custom_textarea_field( $review_element ); }
				elseif ( $review_element->type == 'dropdown' ) 	{ $this->validate_custom_select_field( $review_element ); }
				elseif ( $review_element->type == 'checkbox' ) 	{ $this->validate_custom_checkbox_field( $review_element ); }
				elseif ( $review_element->type == 'radio' ) 	{ $this->validate_custom_radio_field( $review_element ); }
				elseif ( $review_element->type == 'date' ) 		{ $this->validate_custom_date_field( $review_element ); }
				elseif ( $review_element->type == 'datetime' ) 	{ $this->validate_custom_datetime_field( $review_element ); }
			}
		} 

		$this->score = isset( $this->score ) ? $this->score : ( count( $this->scores ) ? array_sum( $this->scores ) / count( $this->scores ) : 0 );
		
		$this->post_status = $ewd_urp_controller->settings->get_setting( 'admin-approval' ) ? 'draft' : 'publish';

		do_action( 'ewd_urp_validate_review_submission', $this );
	}

	/**
	 * Returns the decrypted version of the captcha code
	 * @since 3.0.0
	 */
	public function decrypt_modified_code( $user_code ) {

		$decrypted_code = ($user_code / 3) - 5;

		return $decrypted_code;
	}

	/**
	 * Validate the product name field, if included in the review form
	 * @since 3.0.0
	 */
	public function validate_product_name_field() {
		global $ewd_urp_controller;
		
		$this->product_name = empty( $_POST['product_name'] ) ? false : sanitize_text_field( $_POST['product_name'] );

		if ( ! $ewd_urp_controller->settings->get_setting( 'one-review-per-product-person' ) ) { return; }

		$reviewed_products = isset( $_COOKIE['EWD_URP_Reviewed_Products'] ) ? json_decode( stripslashes( $_COOKIE['EWD_URP_Reviewed_Products'] ) ) : array(); 
		$reviewed_products = is_array( $reviewed_products ) ? array_map( 'sanitize_text_field', $reviewed_products ) : array();	
		
		if ( in_array( $this->product_name, $reviewed_products ) ) {

			$this->validation_errors[] = array(
				'field'		=> 'product_name',
				'error_msg'	=> 'Product already reviewed',
				'message'	=> __( 'You have already submitted a review for a product with that product name. Please select a different product to review.', 'ultimate-reviews' ),
			);
		}
	}

	/**
	 * Validate the review author field
	 * @since 3.0.0
	 */
	public function validate_review_author_field() {
		global $ewd_urp_controller;

		$this->review_post_author = empty( $_POST['post_author'] ) ? false : sanitize_text_field( $_POST['post_author'] );

		if ( $_POST['post_author_type'] == 'autoentered' ) {

			if ( sha1( $this->author_name . $ewd_urp_controller->settings->get_setting( 'salt' ) ) != $_POST['post_author_check'] ) {

				$this->validation_errors[] = array(
					'field'		=> 'post_author',
					'error_msg'	=> 'Author field incorrect',
					'message'	=> __( 'The name of the review author was not returned as expected.', 'ultimate-reviews' ),
				);
			}
		}
	}

	/**
	 * Validate the review title
	 * @since 3.0.0
	 */
	public function validate_review_title_field() {
		
		$this->title = empty( $_POST['review_title'] ) ? false : sanitize_text_field( $_POST['review_title'] );
	}

	/**
	 * Validate the review score
	 * @since 3.0.0
	 */
	public function validate_overall_score_field() {
		global $ewd_urp_controller;
		
		$this->score = empty( $_POST['overall_score'] ) ? false : intval( $_POST['overall_score'] );

		$this->score = min( round( $this->score, 2 ), $ewd_urp_controller->settings->get_setting( 'maximum-score' ) );
	}

	/**
	 * Validate the review author email field, if included in the review form
	 * @since 3.0.0
	 */
	public function validate_reviewer_email_field() {
		
		$this->author_email = empty( $_POST['post_email'] ) ? false : sanitize_email( $_POST['post_email'] );
	}

	/**
	 * Validate the review body
	 * @since 3.0.0
	 */
	public function validate_review_body_field() {
		
		$this->review = empty( $_POST['review_body'] ) ? false : sanitize_textarea_field( $_POST['review_body'] );
	}

	/**
	 * Validate the review image, if included in the review form
	 * @since 3.0.0
	 */
	public function validate_review_image_field() {
		global $ewd_urp_controller;

		// Basic -> Review Image disabled => return
		if ( ! $ewd_urp_controller->settings->get_setting( 'review-image' ) ) { return; }

		$flag_image_present = ! empty( $_FILES['post_image']['name'] );
		$flag_in_depth_enabled = $ewd_urp_controller->settings->get_setting( 'indepth-reviews' );
		$flag_in_image_required = false;

		// image is not-present and in-depth disabled => return
		if( !$flag_image_present && !$flag_in_depth_enabled ) {
			return;
		}

		$elmnts = $ewd_urp_controller->settings->get_review_elements();
		foreach ($elmnts as $elmnt) {
			if( 'Review Image (if applicable)' == $elmnt->name ) {
				$flag_in_image_required = in_array( strtolower( (string)$elmnt->required ), [1, '1', 'yes'] );
				break;
			}
		}

		// image is not-present and in-depth enabled and is-not-required => return
		if( !$flag_image_present && $flag_in_depth_enabled && !$flag_in_image_required ) {
			return;
		}

		// image is not-present and in-depth enabled and is-required => throw error
		if( !$flag_image_present && $flag_in_depth_enabled && $flag_in_image_required ) {
			$this->validation_errors[] = array(
				'field'		=> 'post_image',
				'error_msg'	=> 'Image is required',
				'message'	=> __( 'Image is required', 'ultimate-reviews' ),
			);

			return;
		}

		// image is present => check image type
		if( ! preg_match( "/\.jpg$|\.jpeg$|\.png$/", strtolower( $_FILES['post_image']['name'] ) ) ) {

			$this->validation_errors[] = array(
				'field'		=> 'post_image',
				'error_msg'	=> 'Invalid image type',
				'message'	=> __( 'File must be .jpg or .png.', 'ultimate-reviews' ),
			);
		}
	}

	/**
	 * Validate the review video, if included in the review form
	 * @since 3.0.0
	 */
	public function validate_review_video_field() {
		global $ewd_urp_controller;

		if ( ! $ewd_urp_controller->settings->get_setting( 'review-video' ) ) { return; }
		
		$this->review_video = empty( $_POST['review_video'] ) ? false : esc_url_raw( $_POST['review_video'] );
	}

	/**
	 * Validate the review category, if included in the review form
	 * @since 3.0.0
	 */
	public function validate_review_category_field() {
		global $ewd_urp_controller;

		if ( $ewd_urp_controller->settings->get_setting( 'match-woocommerce-categories' ) ) {

			$this->categories = $this->match_woocommerce_product_categories();
		}
		elseif ( ! $ewd_urp_controller->settings->get_setting( 'review-category' ) ) { 

			$this->categories = array();

			return; 
		}
		else { 

			$this->categories = empty( $_POST['review_category'] ) ? false : (array) intval( $_POST['review_category'] ); 
		}
	}

	/**
	 * Gets all of the WC categories that match the reviewed product
	 * @since 3.0.0
	 */
	public function match_woocommerce_product_categories() {

		$categories = array();
		
		$wc_product = get_page_by_title( $this->product_name, OBJECT, 'product' );

		if ( empty( $wc_product ) ) { return $categories; }

		$wc_categories = get_the_terms( $wc_product, 'product_cat' );

		if ( ! $wc_categories ) { return $categories; }

		$wc_category_ids = array();
		foreach ( $wc_categories as $wc_category ) {
			
			$wc_category_ids[] = $wc_category->term_id;
		}

		$args = array(
			'taxonomy' 		=> EWD_URP_REVIEW_CATEGORY_TAXONOMY,
			'hide_empty'	=> false
		);

		$urp_categories = get_terms( $args );

		foreach ( $urp_categories as $urp_category ) {

			$matching_term_id = get_term_meta( $urp_category->term_id, 'product_cat', true );
			
			if ( in_array( $matching_term_id, $wc_category_ids ) ) { $categories[] = $urp_category->term_id; }
		}

		return $categories;
	}

	/**
	 * Validate a review score field
	 * @since 3.0.0
	 */
	public function validate_score_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? 0 : intval( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		$this->values[ $review_element->name ] = min( $this->values[ $review_element->name ], $ewd_urp_controller->settings->get_setting( 'maximum-score' ) );

		$this->scores[] = $this->values[ $review_element->name ];

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom text field
	 * @since 3.0.0
	 */
	public function validate_custom_text_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_text_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom text field
	 * @since 3.0.0
	 */
	public function validate_custom_textarea_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom select field
	 * @since 3.0.0
	 */
	public function validate_custom_select_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_text_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( ! in_array( $this->values[ $review_element->name ], explode( ',', $review_element->options ) ) ) {

			$this->validation_errors[] = array(
				'field'		=> $this->get_element_input_name( $review_element->name ),
				'error_msg'	=> 'Value not among options',
				'message'	=> __( 'You have selected an invalid value for this field: ', 'ultimate-reviews' ) . $review_element->name,
			);
		}

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom checkbox field
	 * @since 3.0.0
	 */
	public function validate_custom_checkbox_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = ( empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) or ! is_array( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ) ? array() : array_map( 'sanitize_text_field', $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( ! is_array( $this->values[ $review_element->name ] ) ) { $this->values[ $review_element->name ] = array(); }

		if ( count( array_intersect( $this->values[ $review_element->name ], explode( ',', $review_element->options ) ) ) != count( $this->values[ $review_element->name ] ) ) {

			$this->validation_errors[] = array(
				'field'		=> $this->get_element_input_name( $review_element->name ),
				'error_msg'	=> 'Values not among options',
				'message'	=> __( 'You have one or more invalid values for this field: ', 'ultimate-reviews' ) . $review_element->name,
			);
		}

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom radio field
	 * @since 3.0.0
	 */
	public function validate_custom_radio_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_text_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( ! in_array( $this->values[ $review_element->name ], explode( ',', $review_element->options ) ) ) {

			$this->validation_errors[] = array(
				'field'		=> $this->get_element_input_name( $review_element->name ),
				'error_msg'	=> 'Value not among options',
				'message'	=> __( 'You have selected an invalid value for this field: ', 'ultimate-reviews' ) . $review_element->name,
			);
		}

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom date field
	 * @since 3.0.0
	 */
	public function validate_custom_date_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_text_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Validate a review custom datetime field
	 * @since 3.0.0
	 */
	public function validate_custom_datetime_field( $review_element ) {
		global $ewd_urp_controller;
		
		$this->values[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) ] ) ? false : sanitize_text_field( $_POST[ $this->get_element_input_name( $review_element->name ) ] );

		if ( $review_element->explanation ) { $this->explanations[ $review_element->name ] = empty( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ) ? 0 : sanitize_textarea_field( $_POST[ $this->get_element_input_name( $review_element->name ) . '_explanation' ] ); }
	}

	/**
	 * Check if submission is valid
	 *
	 * @since 3.0.0
	 */
	public function is_valid_submission() {

		if ( !count( $this->validation_errors ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the name of a submit review input, based on the element's name
	 *
	 * @since 3.0.0
	 */
	public function get_element_input_name( $field_name ) {

		return str_replace( ' ', '_', strtolower( $field_name ) );
	}

	/**
	 * Insert post data for a new review or update a review
	 * @since 3.0.0
	 */
	public function insert_post_data() {

		$args = array(
			'post_type'		=> EWD_URP_REVIEW_POST_TYPE,
			'post_title'	=> $this->title,
			'post_content'	=> $this->review,
			'post_status'	=> $this->post_status,
		);

		if ( ! empty( $this->ID ) ) {
			$args['ID'] = $this->ID;
		}

		$args = apply_filters( 'ewd_urp_insert_review_data', $args, $this );

		// When updating a review, we need to update the metadata first, so that
		// notifications hooked to the status changes go out with the new metadata.
		// If we're inserting a new review, we have to insert it before we can
		// add metadata, and the default notifications don't fire until it's all done.
		if ( ! empty( $this->ID ) ) {

			$this->insert_post_meta();
			$this->insert_post_categories();
			$id = wp_insert_post( $args );
		} else {

			$id = wp_insert_post( $args );
			if ( $id && ! is_wp_error( $id ) ) {
				$this->ID = $id;
				$this->insert_post_meta();
				$this->insert_post_categories();
				$this->maybe_save_product_name_cookie();
			}
		}

		return ! is_wp_error( $id ) && $id !== false;
	}

	/**
	 * Insert the post metadata for a new review or when updating a review
	 * @since 3.0.0
	 */
	public function insert_post_meta() {
		global $ewd_urp_controller;

		$meta = array();

		if ( ! empty( $this->score ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Overall_Score', $this->score );
		}

		if ( ! empty( $this->review_karma ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Review_Karma', $this->review_karma );
		}

		if ( ! empty( $this->author_email ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Post_Email', $this->author_email );
		}

		if ( ! empty( $this->email_confirmed ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Email_Confirmed', $this->email_confirmed );
		}

		if ( ! empty( $_FILES['post_image'] ) ) {

			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			$upload_overrides = array( 'test_form' => false );

			$move_file = wp_handle_upload( $_FILES['post_image'], $upload_overrides );

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$file_type = wp_check_filetype( basename( $move_file['file'] ), null );

			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $move_file['file'] ),
				'post_mime_type' => $file_type['type'],
				'post_title' => basename( $move_file['file'] ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Create the attachment
			$attach_id = wp_insert_attachment( $attachment, $move_file['file'], $this->ID );

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $move_file['file'] );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );


			// And finally assign featured image to post
			set_post_thumbnail( $this->ID, $attach_id );
		}

		if ( ! empty( $this->review_video ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Review_Video', $this->review_video );
		}

		if ( ! empty( $this->product_name ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Product_Name', $this->product_name );
		}

		if ( ! empty( $this->review_post_author ) ) {
			update_post_meta( $this->ID, 'EWD_URP_Post_Author', $this->review_post_author );
		}

		foreach ( $ewd_urp_controller->settings->get_review_elements() as $review_element ) {

			if ( ! empty( $this->values[ $review_element->name ] ) ) {

				update_post_meta( $this->ID, "EWD_URP_" . $review_element->name, $this->values[ $review_element->name ] );
			}

			if ( ! empty( $this->explanations[ $review_element->name ] ) ) {

				update_post_meta( $this->ID, "EWD_URP_" . $review_element->name . "_Description", $this->explanations[ $review_element->name ] );
			}
		}
	}

	/**
	 * Update the categories for a review
	 * @since 3.0.0
	 */
	public function insert_post_categories() {

		$this->categories = is_array( $this->categories ) ? $this->categories : explode( ',', $this->categories );

		if ( empty( $this->categories ) ) { return; }

		wp_set_object_terms( $this->ID, $this->categories, EWD_URP_REVIEW_CATEGORY_TAXONOMY );
	}

	/**
	 * Save the product's name to the cookie to possibly prevent multiple reviews
	 * @since 3.0.0
	 */
	public function maybe_save_product_name_cookie() {
		global $ewd_urp_controller;

		if ( ! $ewd_urp_controller->settings->get_setting( 'one-review-per-product-person' ) ) { return; }

		$reviewed_products = isset( $_COOKIE['EWD_URP_Reviewed_Products'] ) ? json_decode( stripslashes( $_COOKIE['EWD_URP_Reviewed_Products'] ) ) : array(); 
		$reviewed_products = is_array( $reviewed_products ) ? array_map( 'sanitize_text_field', $reviewed_products ) : array();	

		$reviewed_products[] = $this->product_name;

		setcookie( 'EWD_URP_Reviewed_Products', json_encode( $reviewed_products ), time() + 365*24*3600, '/' );
	}

	public function confirm_email() {

		$post_id = intval( $_GET['post_id'] );
		$user_confirmation_code = sanitize_text_field( $_GET['confirmation_code'] );
	
		$confirmation_code = get_post_meta( $post_id, 'EWD_URP_Confirmation_Code', true );
	
		if ( $confirmation_code == $user_confirmation_code ) {

			update_post_meta( $post_id, 'EWD_URP_Email_Confirmed', 'Yes' );

			return true;
		}
	
		return false;
	}

}
} // endif;
