<?php

/**
 * Class to display the submit review on the front end.
 *
 * @since 3.0.0
 */
class ewdurpViewSubmitReview extends ewdurpView {

	// Shortcode attributes
	public $product_name;
	public $submit_review_toggle;
	public $redirect_page;
	public $success_message;
	public $draft_message;
	public $review_form_title;
	public $review_instructions;
	public $submit_text;

	// Message to be displayed on success/failure
	public $update_message;

	// Pointers
	public $current_review_element;

	// Array of reviewable product names
	public $product_names = array();

	// A flag for tracking if a review was submitted
	public $review_submitted = false;

	/**
	 * Render the view and enqueue required stylesheets
	 * @since 3.0.0
	 */
	public function render() {

		$this->set_reviewable_product_names();

		// Add any dependent stylesheets or javascript
		$this->enqueue_assets();

		$this->set_variables();

		// Add css classes to the slider
		$this->classes = $this->get_classes();

		ob_start();
		$this->add_custom_styling();
		$template = $this->find_template( 'submit-review' );
		if ( $template ) {
			include( $template );
		}
		$output = ob_get_clean();

		return apply_filters( 'ewd_urp_submit_review_output', $output, $this );
	}

	/**
	 * Display the woocommerce leave review process, if selected via the settings
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_login() {
		global $ewd_urp_controller;
		
		if ( is_user_logged_in() ) { return false; }

		if ( ! $ewd_urp_controller->settings->get_setting( 'require-login' ) ) { return false; }
		
		$template = $this->find_template( 'submit-review-login' );
		
		if ( $template ) {
			include( $template );
		}

		return true;
	}

	/**
	 * Display the result of a submitted review, if one was submitted
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_submitted_review_message() {
		
		if ( empty( $this->update_message ) ) { return; }
		
		$template = $this->find_template( 'submit-review-submitted-review-message' );
		
		if ( $template ) {
			include( $template );
		}
	}

	/**
	 * Add in the hidden order ID field, if the field is needed
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_order_id_input() {
		
		if ( empty( $_REQUEST['order_id'] ) ) { return; }
		
		$template = $this->find_template( 'submit-review-display-order-id-input' );
		
		if ( $template ) {
			include( $template );
		}
	}

	/**
	 * Add in the hidden woocommerce email field, if the field is needed
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_woocommerce_email_input() {
		
		if ( empty( $this->woocommerce_email ) ) { return; }
		
		$template = $this->find_template( 'submit-review-display-woocommerce-email-input' );
		
		if ( $template ) {
			include( $template );
		}
	}

	/**
	 * Determines which form field template to use, based on the field type, settings, etc. and displays it
	 *
	 * @since 3.0.0
	 */
	public function display_form_field( $review_element ) {
		
		$this->current_review_element = $review_element;

		if ( $this->current_review_element->type == 'default' ) { $template = $this->display_default_form_field(); }
		elseif ( $this->current_review_element->type == '' or $this->current_review_element->type == 'reviewitem' ) { $template = $this->display_review_item_form_field(); }
		else { $template = $this->display_custom_form_field(); }

		if ( $template ) {
			include( $template );
		}
		
		if ( $this->current_review_element->explanation == 1 ) { 

			$template = $this->find_template( 'submit-review-field-explanation' ); 

			if ( $template ) {
				include( $template );
			}
		}
	}

	/**
	 * Returns the template for a default form fields
	 *
	 * @since 3.0.0
	 */
	public function display_default_form_field() {
		global $ewd_urp_controller;

		$template = '';

		if ( $this->current_review_element->name == 'Product Name (if applicable)' ) { 
			
			if ( $this->product_name ) { $template = $this->find_template( 'submit-review-field-product-name-hidden' ); }
			elseif ( $ewd_urp_controller->settings->get_setting( 'product-name-input-type' ) == 'dropdown' ) { $template = $this->find_template( 'submit-review-field-product-name-select' ); }
			else { $template = $this->find_template( 'submit-review-field-product-name-text' ); }
		}
		elseif ( $this->current_review_element->name == 'Review Author' ) { 
			
			if ( ! empty( $this->author_name ) ) { $template = $this->find_template( 'submit-review-field-author-name-hidden' ); }
			else { $template = $this->find_template( 'submit-review-field-author-name-text' ); }
		}
		elseif ( $this->current_review_element->name == 'Review Title' ) { 

			$template = $this->find_template( 'submit-review-field-review-title' ); 
		}
		elseif ( $this->current_review_element->name == 'Overall Score' ) { 

			$template = $this->display_review_item_form_field(); 
		}
		elseif ( $this->current_review_element->name == 'Reviewer Email (if applicable)' and $ewd_urp_controller->settings->get_setting( 'require-email' ) ) { 

			$template = $this->find_template( 'submit-review-field-author-email' ); 
		}
		elseif ( $this->current_review_element->name == 'Review' ) { 

			$template = $this->find_template( 'submit-review-field-review-body' ); 
		}
		elseif ( $this->current_review_element->name == 'Review Image (if applicable)' and $ewd_urp_controller->settings->get_setting( 'review-image' ) ) { 

			$template = $this->find_template( 'submit-review-field-review-image' ); 
		}
		elseif ( $this->current_review_element->name == 'Review Video (if applicable)' and $ewd_urp_controller->settings->get_setting( 'review-video' ) ) { 

			$template = $this->find_template( 'submit-review-field-review-video' ); 
		}
		elseif ( $this->current_review_element->name == 'Review Category (if applicable)' and $ewd_urp_controller->settings->get_setting( 'review-category' ) ) { 

			$template = $this->find_template( 'submit-review-field-review-categories' ); 
		}

		return $template;
	}

	/**
	 * Returns the template for a review score field
	 *
	 * @since 3.0.0
	 */
	public function display_review_item_form_field() {
		global $ewd_urp_controller;

		if ( $ewd_urp_controller->settings->get_setting( 'review-score-input' ) == 'text' ) {

			$template = $this->find_template( 'submit-review-field-review-element-review-item-text' ); 
		}
		elseif ( $ewd_urp_controller->settings->get_setting( 'review-score-input' ) == 'stars' ) {

			$template = $this->find_template( 'submit-review-field-review-element-review-item-stars' ); 
		}
		else {

			$template = $this->find_template( 'submit-review-field-review-element-review-item-select' ); 
		}

		return $template;
	}

	/**
	 * Returns the template for a custom form field
	 *
	 * @since 3.0.0
	 */
	public function display_custom_form_field() {

		$template = '';

		if ( $this->current_review_element->type == 'text' ) {

			$template = $this->find_template( 'submit-review-field-review-element-text' ); 
		}
		elseif ( $this->current_review_element->type == 'textarea' ) {

			$template = $this->find_template( 'submit-review-field-review-element-textarea' ); 
		}
		elseif ( $this->current_review_element->type == 'dropdown' ) {

			$template = $this->find_template( 'submit-review-field-review-element-select' ); 
		}
		elseif ( $this->current_review_element->type == 'checkbox' ) {

			$template = $this->find_template( 'submit-review-field-review-element-checkbox' ); 
		}
		elseif ( $this->current_review_element->type == 'radio' ) {

			$template = $this->find_template( 'submit-review-field-review-element-radio' ); 
		}
		elseif ( $this->current_review_element->type == 'date' ) {

			$template = $this->find_template( 'submit-review-field-review-element-date' ); 
		}
		elseif ( $this->current_review_element->type == 'datetime' ) {

			$template = $this->find_template( 'submit-review-field-review-element-datetime' ); 
		}

		return $template;
	}

	/**
	 * Display the captcha field, if selected via settings
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_captcha_field() {
		global $ewd_urp_controller;
		
		if ( ! $ewd_urp_controller->settings->get_setting( 'use-captcha' ) ) { return; }
		
		if ( $ewd_urp_controller->settings->get_setting( 'captcha-type' ) == 'recaptcha' ) { $template = $this->find_template( 'submit-review-recaptcha' ); }
		else { $template = $this->find_template( 'submit-review-captcha' ); }
		
		if ( $template ) {
			include( $template );
		}
	}

	/**
	 * Display the review toggle, if selected via settings
	 *
	 * @since 3.0.0
	 */
	public function maybe_display_review_toggle() {
		global $ewd_urp_controller;
		
		if ( ! $ewd_urp_controller->settings->get_setting( 'submit-review-toggle' ) ) { return; }
		
		$template = $this->find_template( 'submit-review-toggle-button' );
		
		if ( $template ) {
			include( $template );
		}
	}

	/**
	 * Determines whether a user needs to log in to leave a woocommerce review
	 *
	 * @since 3.0.0
	 */
	public function woocommerce_login_required() {
		global $ewd_urp_controller;
		
		if ( get_current_user_id() ) { return false; }

		if ( ! empty( $this->customer_email ) ) { return false; }

		if ( ! empty( $this->wc_product_name ) ) { return false; }

		if ( ! $ewd_urp_controller->settings->get_setting( 'require-login' ) and ! in_array( 'woocommerce', $ewd_urp_controller->settings->get_setting( 'login-options' ) ) ) { return false; }

		return true;
	}

	/**
	 * Determines whether a user is logged in via the FEUP plugin
	 *
	 * @since 3.0.0
	 */
	public function is_feup_user_logged_in() {

		if ( class_exists( 'FEUP_User' ) ) {
			
			$feup_user = new FEUP_User;

			return $feup_user->Is_Logged_In();
		}

		return false;
	}

	/**
	 * Returns the name of an element, if it exists
	 *
	 * @since 3.0.0
	 */
	public function get_element_name() {

		if ( empty( $this->current_review_element ) or empty( $this->current_review_element->name ) ) { 

			return; 
		}

		if ( $this->current_review_element->name == 'Overall Score' ) { return; }

		return $this->current_review_element->name;
	}

	/**
	 * Returns the input name of an element, if it exists
	 *
	 * @since 3.0.0
	 */
	public function get_element_input_name() {

		if ( empty( $this->current_review_element ) or empty( $this->current_review_element->name ) ) { 

			return; 
		}

		return strtolower( $this->current_review_element->name );
	}

	/**
	 * Prints the 'required' attribute if the current element is required
	 *
	 * @since 3.0.0
	 */
	public function element_required() {

		if ( empty( $this->current_review_element ) ) { return; }

		return !in_array( $this->current_review_element->required, [false, 'no', 0, '0', null, ''] ) ? 'required' : '';
	}

	/**
	 * Returns an array of options for the current review element, or an empty array if not set
	 *
	 * @since 3.0.0
	 */
	public function get_element_options() {

		if ( empty( $this->current_review_element ) or empty( $this->current_review_element->options ) ) { 

			return array(); 
		}

		return explode( ',', $this->current_review_element->options );
	}

	/**
	 * Returns all woocommerce products purchased by the current user
	 *
	 * @since 3.0.0
	 */
	public function get_matching_woocommerce_products() {
		global $wpdb;
		global $ewd_urp_controller;

		$orders = array();

		if ( get_current_user_id() ) {
			
			$orders = $wpdb->get_results( $wpdb->prepare( 'SELECT post_id from $wpdb->postmeta WHERE meta_key=%s AND meta_value=%d', '_customer_user', get_current_user_id() ) );
		}
		elseif ( ! empty( $this->wc_customer_email ) ) {
			
			$orders = $wpdb->get_results( $wpdb->prepare( 'SELECT post_id from $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s', '_billing_email', $this->wc_customer_email ) );
		}
	
		$this->woocommerce_products = array();
		foreach ( $orders as $order ) {
			
			$wc_order = new WC_Order( $order->post_id );
			$order_time = strtotime( $wc_order->order_date );
			
			if ( $order_time < ( time() - $ewd_urp_controller->settings->get_setting( 'woocommerce-minimum-days' ) * 24 * 3600 ) and ( $order_time + $ewd_urp_controller->settings->get_setting( 'woocommerce-maximum-days' ) * 24 * 3600 ) > time() ) {
				
				$items = $wc_order->get_items();
				$this->woocommerce_products = array_merge( $this->woocommerce_products, $items );
			}
		}
	}

	/**
	 * Returns all woocommerce products purchased by the current user
	 *
	 * @since 3.0.0
	 */
	public function get_woocommerce_review_link( $product ) {

		return add_query_arg( array( 'wc_product_name' => $product['name'], 'wc_customer_email' => esc_attr( $this->wc_customer_email ) ) );
	}

	/**
	 * Saves all valid product names for the select product name input
	 *
	 * @since 3.0.0
	 */
	public function set_reviewable_product_names() {
		global $ewd_urp_controller;

		if ( $ewd_urp_controller->settings->get_setting( 'upcp-integration' ) ) {

			if ( function_exists( 'UPCP_Get_All_Products' ) ) {

				foreach ( UPCP_Get_All_Products() as $upcp_product ) { $this->product_names[] = $upcp_product->Get_Product_Name(); }
			}
			else {

				$args = array(
					'post_type'			=> 'upcp_product',
					'posts_per_page' 	=> -1
				);

				$products = get_posts( $args );

				foreach ( $products as $product ) { $this->product_names[] = $product->post_title; }
			}
		}
		elseif ( $ewd_urp_controller->settings->get_setting( 'only-woocommerce-products' ) ) {

			$args = array(
				'post_type' => 'product', 
				'posts_per_page' => -1, 
				'orderby' => 'title', 
				'order' => 'ASC'
			);

			$products = get_posts( $args );

			foreach ( $products as $product ) { $this->product_names[] = $product->post_title; }
		}
		else {
			
			foreach ( ewd_urp_decode_infinite_table_setting( $ewd_urp_controller->settings->get_setting( 'product-names-array' ) ) as $product_name ) { $this->product_names[] = $product_name->name; }
		}

		return $this->product_names;
	}

	/**
	 * Returns all valid product names for the select product name input
	 *
	 * @since 3.0.0
	 */
	public function get_reviewable_product_names() {

		return $this->product_names;
	}

	/**
	 * Returns all existing review categories
	 *
	 * @since 3.0.0
	 */
	public function get_review_categories() {

		return get_terms( array( 'taxonomy' => EWD_URP_REVIEW_CATEGORY_TAXONOMY, 'hide_empty' => false ) );
	}

	/**
	 * Returns the label for the current score input
	 *
	 * @since 3.0.0
	 */
	public function get_score_label() {

		return $this->current_review_element->type == 'default' ? $this->get_label( 'label-submit-score' ) : $this->get_label( 'label-submit-element-score' );
	}

	/**
	 * Returns the image for the captcha field
	 *
	 * @since 3.0.0
	 */
	public function create_captcha_image() {

		$im = imagecreatetruecolor( 50, 24 );
		$bg = imagecolorallocate( $im, 22, 86, 165 );  
		$fg = imagecolorallocate( $im, 255, 255, 255 ); 
		imagefill( $im, 0, 0, $bg );
		imagestring( $im, 5, 5, 5,  $this->get_captcha_image_code(), $fg );

  		$five_mb = 5 * 1024 * 1024;
  		$stream = fopen( 'php://temp/maxmemory:{$five_mb}', 'r+' );
  		imagepng( $im, $stream );
  		imagedestroy( $im );
  		rewind( $stream );

  		return base64_encode( stream_get_contents( $stream ) );
  	}

  	public function get_captcha_image_code() {

  		return ( $this->captcha_form_code / 3 ) - 5;
  	}

	/**
	 * Get the initial reviews css classes
	 * @since 3.0.0
	 */
	public function get_classes( $classes = array() ) {
		global $ewd_urp_controller;

		$classes = array_merge(
			$classes,
			array(
				'ewd-urp-review-form',
			)
		);

		if ( $ewd_urp_controller->settings->get_setting( 'submit-review-toggle' ) ) { $classes[] = 'ewd-urp-form-hidden'; }

		return apply_filters( 'ewd_urp_submit_review_classes', $classes, $this );
	}

	/**
	 * Enqueue the necessary CSS and JS files
	 * @since 3.0.0
	 */
	public function enqueue_assets() {
		global $ewd_urp_controller;

		wp_enqueue_style( 'ewd-urp-css' );

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'ewd-urp-masonry-js' );

		$data_array = array(
			'nonce' => wp_create_nonce( 'ewd-urp-js' ),
			'maximum_score' 				=> $ewd_urp_controller->settings->get_setting( 'maximum-score' ),
			'review_character_limit' 		=> $ewd_urp_controller->settings->get_setting( 'review-character-limit' ),
			'flag_inappropriate_enabled' 	=> $ewd_urp_controller->settings->get_setting( 'flag-inappropriate' ),
			'autocomplete_product_names'	=> $ewd_urp_controller->settings->get_setting( 'autocomplete-product-names' ),
			'restrict_product_names'		=> $ewd_urp_controller->settings->get_setting( 'restrict-product-names' ),
			'review_format'					=> $ewd_urp_controller->settings->get_setting( 'review_format' )
		);

		$ewd_urp_controller->add_front_end_php_data( 'ewd-urp-js', 'ewd_urp_php_data', $data_array );

		$submit_review_data = array(
			'product_names' => $this->product_names
		);

		$ewd_urp_controller->add_front_end_php_data( 'ewd-urp-js', 'ewd_urp_php_submit_review_data', $submit_review_data );

		wp_enqueue_script( 'ewd-urp-js' );
		
		$pie_data_array = array(
			'maximum_score' 				=> $ewd_urp_controller->settings->get_setting( 'maximum-score' ),
			'circle_graph_background_color' => $ewd_urp_controller->settings->get_setting( 'styling-circle-graph-background-color' ),
			'circle_graph_fill_color' 		=> $ewd_urp_controller->settings->get_setting( 'styling-circle-graph-fill-color' )
		);

		$ewd_urp_controller->add_front_end_php_data( 'ewd-urp-pie-graph-js', 'ewd_urp_pie_data', $pie_data_array );

		wp_enqueue_script( 'ewd-urp-pie-graph-js' );

		wp_enqueue_script( 'ewd-urp-jquery-datepicker' );

		if ( empty( $ewd_urp_controller->settings->get_setting( 'use-captcha' ) ) )  { return; }

		if ( $ewd_urp_controller->settings->get_setting( 'captcha-type' ) != 'recaptcha' ) { return; }

		wp_enqueue_script( 'ewd-urp-google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&render=explicit&onload=ewdurpLoadRecaptcha' );
		wp_enqueue_script( 'ewd-urp-process-recaptcha', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-recaptcha.js', array( 'ewd-urp-google-recaptcha' ) );

		$args = array(
			'site_key'	=> $ewd_urp_controller->settings->get_setting( 'captcha-site-key' ),
		);

		$ewd_urp_controller->add_front_end_php_data( 'ewd-urp-process-recaptcha', 'ewd_urp_recaptcha', $args );
	}

	/**
	 * Set any neccessary variables before displaying the form
	 * @since 3.0.0
	 */
	public function set_variables() {
		global $ewd_urp_controller;

		if ( empty( $ewd_urp_controller->settings->get_setting( 'use-captcha' ) ) ) { return; }

		if ( $ewd_urp_controller->settings->get_setting( 'captcha-type' ) == 'recaptcha' ) { return; }
			
		$this->captcha_form_code = ( rand( 1000, 9999 ) + 5 ) * 3;
	}
}
