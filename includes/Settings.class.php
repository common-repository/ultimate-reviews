<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdurpSettings' ) ) {
/**
 * Class to handle configurable settings for Ultimate Reviews
 * @since 3.0.0
 */
class ewdurpSettings {

	/**
	 * Default values for settings
	 * @since 3.0.0
	 */
	public $defaults = array();

	/**
	 * Stored values for settings
	 * @since 3.0.0
	 */
	public $settings = array();

	public function __construct() {

		add_action( 'init', array( $this, 'set_defaults' ) );

		add_action( 'init', array( $this, 'load_settings_panel' ) );

		add_filter( 'ewd-urp-settings-maximum-score', array( $this, 'review_maximum_score' ) );
	}

	/**
	 * Load the plugin's default settings
	 * @since 3.0.0
	 */
	public function set_defaults() {

		$this->defaults = array(

			'maximum-score'				=> 5,
			'review-style'				=> 'points',
			'review-score-input'		=> 'stars',
			'product-name-input-type'	=> 'text',
			'reviews-per-page'			=> 100,
			'pagination-location'		=> 'top',

			'review-format'				=> 'standard',
			'captcha-type'				=> 'text',
			'summary-statistics'		=> 'none',
			'thumbnail-characters'		=> 140,

			'woocommerce-review-types'	=> 'default',
			'display-related-reviews'	=> 0,

			'group-by-product-order'	=> 'asc',
			'ordering-type'				=> 'date',
			'order-direction'			=> 'asc',

			'reviews-skin'				=> 'basic',
			'indepth-layout'			=> 'regular',
			'read-more-style'			=> 'standardlink',

			'review-filtering'			=> '',
			'login-options'				=> '',
			'review-elements'			=> $this->get_default_review_elements(),
			'product-names-array'		=> '',
			'reminders-array'			=> '',
			'email-messages-array'		=> '',
			'items-reviewed'			=> 'Product'
		);

		$this->defaults = apply_filters( 'ewd_urp_defaults', $this->defaults, $this );
	}

	/**
	 * Get a setting's value or fallback to a default if one exists
	 * @since 3.0.0
	 */
	public function get_setting( $setting ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'ewd-urp-settings' );
		}

		if ( isset( $this->settings[ $setting ] ) ) {
			return apply_filters( 'ewd-urp-settings-' . $setting, $this->settings[ $setting ] );
		}

		if ( ! empty( $this->defaults[ $setting ] ) or isset( $this->defaults[ $setting ] ) ) {
			return apply_filters( 'ewd-urp-settings-' . $setting, $this->defaults[ $setting ] );
		}

		return apply_filters( 'ewd-urp-settings-' . $setting, null );
	}

	/**
	 * Set a setting to a particular value
	 * @since 3.0.0
	 */
	public function set_setting( $setting, $value ) {

		$this->settings[ $setting ] = $value;
	}

	/**
	 * Save all settings, to be used with set_setting
	 * @since 3.0.0
	 */
	public function save_settings() {
		
		update_option( 'ewd-urp-settings', $this->settings );
	}

	/**
	 * Load the admin settings page
	 * @since 3.0.0
	 * @sa https://github.com/NateWr/simple-admin-pages
	 */
	public function load_settings_panel() {
		global $ewd_urp_controller;

		require_once( EWD_URP_PLUGIN_DIR . '/lib/simple-admin-pages/simple-admin-pages.php' );
		$sap = sap_initialize_library(
			$args = array(
				'version'       => '2.6.19',
				'lib_url'       => EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/',
				'theme'			=> 'purple',
			)
		);

		$sap->add_page(
			'submenu',
			array(
				'id'            => 'ewd-urp-settings',
				'title'         => __( 'Settings', 'ultimate-reviews' ),
				'menu_title'    => __( 'Settings', 'ultimate-reviews' ),
				'parent_menu'	=> 'edit.php?post_type=urp_review',
				'description'   => '',
				'capability'    => 'manage_options',
				'default_tab'   => 'ewd-urp-basic-tab',
			)
		);

		$sap->add_section(
			'ewd-urp-settings',
			array(
				'id'            => 'ewd-urp-basic-tab',
				'title'         => __( 'Basic', 'ultimate-reviews' ),
				'is_tab'		=> true,
			)
		);

		$sap->add_section(
			'ewd-urp-settings',
			array(
				'id'            => 'ewd-urp-general',
				'title'         => __( 'General', 'ultimate-reviews' ),
				'tab'	        => 'ewd-urp-basic-tab',
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'warningtip',
			array(
				'id'			=> 'shortcodes-reminder',
				'title'			=> __( 'REMINDER:', 'ultimate-reviews' ),
				'placeholder'	=> __( 'To display reviews, place the [ultimate-reviews] shortcode on a page' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'warningtip',
			array(
				'id'			=> 'shortcodes-reminder-2',
				'title'			=> __( 'REMINDER:', 'ultimate-reviews' ),
				'placeholder'	=> __( 'To allow visitors to submit reviews, place the [submit-review] shortcode on a page' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'textarea',
			array(
				'id'			=> 'custom-css',
				'title'			=> __( 'Custom CSS', 'ultimate-reviews' ),
				'description'	=> __( 'You can add custom CSS styles to your reviews in the box above.', 'ultimate-reviews' ),			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'text',
			array(
				'id'            => 'maximum-score',
				'title'         => __( 'Maximum Review Score', 'ultimate-reviews' ),
				'description'	=> __( 'What should the maximum score be on the review form? Common values are 100 for the \'percentage\' review style, and 5 or 10 for the other styles.', 'ultimate-reviews' ),
				'small'			=> true,
				'placeholder'	=> 'e.g. '.$this->defaults['maximum-score']
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'radio',
			array(
				'id'			=> 'review-style',
				'title'			=> __( 'Review Style', 'ultimate-reviews' ),
				'description'	=> __( 'What style should the submit-review form use to collect reviews?', 'ultimate-reviews' ),
				'default'		=> $this->defaults['review-style'],
				'options'		=> array(
					'points'		=> __( 'Points', 'ultimate-reviews' ),
					'percentage'	=> __( 'Percentage', 'ultimate-reviews' ),
				)
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'radio',
			array(
				'id'			=> 'review-score-input',
				'title'			=> __( 'Review Score Input', 'ultimate-reviews' ),
				'description'	=> __( 'What type of input should be used for review scores in the submit-review shortcode?', 'ultimate-reviews' ),
				'default'		=> $this->defaults['review-score-input'],
				'options'		=> array(
					'text'			=> __( 'Text', 'ultimate-reviews' ),
					'select'		=> __( 'Select', 'ultimate-reviews' ),
					'stars'			=> __( 'Stars', 'ultimate-reviews' ),
				)
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'toggle',
			array(
				'id'			=> 'review-image',
				'title'			=> __( 'Review Image', 'ultimate-reviews' ),
				'description'	=> __( 'Should there be a field for the reviewer to upload an image of what they\'re reviewing?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'toggle',
			array(
				'id'			=> 'review-video',
				'title'			=> __( 'Review Video', 'ultimate-reviews' ),
				'description'	=> __( 'Should there be a field for the reviewer to embed a video with their review from an external site (YouTube, Vimeo, etc.)?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'toggle',
			array(
				'id'			=> 'review-category',
				'title'			=> __( 'Review Category', 'ultimate-reviews' ),
				'description'	=> __( 'Should the reviewer be able to select a category for their review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'checkbox',
			array(
				'id'			=> 'review-filtering',
				'title'			=> __( 'Review Filtering', 'ultimate-reviews' ),
				'description'	=> __( 'Should visitors be able to filter reviews by product name, score or review author?', 'ultimate-reviews' ),
				'options'		=> array(
					'score' 		=> 'Review Score',
					'name' 			=> 'Product Name',
					'author'		=> 'Review Author'
				)
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-general',
			'toggle',
			array(
				'id'			=> 'show-tinymce',
				'title'			=> __( 'Shortcode Builder', 'ultimate-reviews' ),
				'description'	=> __( 'Should a shortcode builder be added to the tinyMCE toolbar in the page editor?', 'ultimate-reviews' )
			)
		);

		$sap->add_section(
			'ewd-urp-settings',
			array(
				'id'            => 'ewd-urp-functionality',
				'title'         => __( 'Functionality', 'ultimate-reviews' ),
				'tab'	        => 'ewd-urp-basic-tab',
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'submit-review-toggle',
				'title'			=> __( 'Submit Review Toggle', 'ultimate-reviews' ),
				'description'	=> __( 'Should the submit review form be hidden until a button is clicked to show it?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'autocomplete-product-names',
				'title'			=> __( 'Autocomplete Product Names', 'ultimate-reviews' ),
				'description'	=> __( 'Should the names of the available products display in an auto-complete box when a visitor starts typing? Products need to be entered in the list below or UPCP Integration has to be turned on for this to work.', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'link-to-post',
				'title'			=> __( 'Link To Post', 'ultimate-reviews' ),
				'description'	=> __( 'Should the review title link to the single post page for the review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'flag-inappropriate',
				'title'			=> __( 'Flag Inappropriate Content', 'ultimate-reviews' ),
				'description'	=> __( 'Should visitors be able to flag content as inappropriate, so that admins can then review it?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'author-click-filter',
				'title'			=> __( 'Review Author Links', 'ultimate-reviews' ),
				'description'	=> __( 'Should the author\'s name be clickable, so that visitors can see other reviews by the same author?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'toggle',
			array(
				'id'			=> 'review-comments',
				'title'			=> __( 'Allow Review Comments', 'ultimate-reviews' ),
				'description'	=> __( 'Should comments be allowed, if the "Allow Comments" box for individual reviews is selected from the edit review screen?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'text',
			array(
				'id'            => 'review-character-limit',
				'title'         => __( 'Review Character Limit', 'ultimate-reviews' ),
				'description'	=> __( 'What should be the limit on the number of characters in a review? Leave blank for unlimited characters.', 'ultimate-reviews' ),
				'small'			=> true
			)
		);

		$emails = array();

		if ( is_plugin_active( 'ultimate-wp-mail/ultimate-wp-mail.php' ) ) {

			$args = array( 
				'post_type' 		=> 'uwpm_mail_template', 
				'posts_per_page' 	=> -1 
			);

			$email_posts = get_posts( $args );
			foreach ( $email_posts as $email_post ) { $emails[ $email_post->ID ] = $email_post->post_title; }
		}

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-functionality',
			'select',
			array(
				'id'            => 'email-on-submission',
				'title'         => __( 'Submission Thank You Email', 'ultimate-reviews' ),
				'description'   => 'You can use the <a href="https://wordpress.org/plugins/ultimate-wp-mail/">Ultimate WP Mail plugin</a> to create a custom email that is sent whenever a review is submitted.',
				'blank_option'	=> true,
				'options'       => $emails
			)
		);

		$sap->add_section(
			'ewd-urp-settings',
			array(
				'id'            => 'ewd-urp-products-for-review',
				'title'         => __( 'Products Available for Review', 'ultimate-reviews' ),
				'tab'	        => 'ewd-urp-basic-tab',
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-products-for-review',
			'toggle',
			array(
				'id'			=> 'restrict-product-names',
				'title'			=> __( 'Restrict Product Names', 'ultimate-reviews' ),
				'description'	=> __( 'Should the names of the products be restricted to only those specified?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-products-for-review',
			'radio',
			array(
				'id'			=> 'product-name-input-type',
				'title'			=> __( 'Product Name Input Type', 'ultimate-reviews' ),
				'description'	=> __( 'Should the product name input be a text field or a dropdown (select) field? (Dropdown only works if UPCP integration is turned on or "Products List" is filled in below)', 'ultimate-reviews' ),
				'default'		=> $this->defaults['product-name-input-type'],
				'options'		=> array(
					'text'			=> __( 'Text', 'ultimate-reviews' ),
					'dropdown'		=> __( 'Dropdown', 'ultimate-reviews' ),
				)
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-products-for-review',
			'toggle',
			array(
				'id'			=> 'upcp-integration',
				'title'			=> __( 'UPCP Integration', 'ultimate-reviews' ),
				'description'	=> __( 'Should the product names be taken from the Ultimate Product Catalogue Plugin if the names are being restricted or the product name input type is set to "Dropdown"? (Ultimate Product Catalogue plugin needs to be installed to work correctly)', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-products-for-review',
			'infinite_table',
			array(
				'id'			=> 'product-names-array',
				'title'			=> __( 'Product List', 'ultimate-reviews' ),
				'add_label'		=> __( '+ ADD', 'ultimate-reviews' ),
				'del_label'		=> __( 'Delete', 'ultimate-reviews' ),
				'description'	=> __( 'If UPCP integration is set to "No", and the product names are restricted or the input type is set to "Dropdown", the list of products above will be used to restrict the possible product names.', 'ultimate-reviews' ),
				'fields'		=> array(
					'name' => array(
						'type' 		=> 'text',
						'label' 	=> 'Product Name',
						'required' 	=> true
					)
				)
			)
		);

		$sap->add_section(
			'ewd-urp-settings',
			array(
				'id'            => 'ewd-urp-display-and-layout',
				'title'         => __( 'Display and Layout', 'ultimate-reviews' ),
				'tab'	        => 'ewd-urp-basic-tab',
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'toggle',
			array(
				'id'			=> 'display-author',
				'title'			=> __( 'Display Author Name', 'ultimate-reviews' ),
				'description'	=> __( 'Should the author\'s name be posted with the review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'toggle',
			array(
				'id'			=> 'display-date',
				'title'			=> __( 'Display Date Submitted', 'ultimate-reviews' ),
				'description'	=> __( 'Should the date the review was submitted be posted with the review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'toggle',
			array(
				'id'			=> 'display-time',
				'title'			=> __( 'Display Time Submitted', 'ultimate-reviews' ),
				'description'	=> __( 'Should the time the review was submitted be posted with the review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'toggle',
			array(
				'id'			=> 'display-categories',
				'title'			=> __( 'Display Categories', 'ultimate-reviews' ),
				'description'	=> __( 'Should the review\'s categories be posted with the review?', 'ultimate-reviews' )
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'text',
			array(
				'id'            => 'reviews-per-page',
				'title'         => __( 'Reviews Per Page', 'ultimate-reviews' ),
				'description'	=> __( 'Set the maximum number of reviews that should be displayed at one time.', 'ultimate-reviews' ),
				'small'			=> true,
				'placeholder'	=> $this->defaults['reviews-per-page']
			)
		);

		$sap->add_setting(
			'ewd-urp-settings',
			'ewd-urp-display-and-layout',
			'radio',
			array(
				'id'			=> 'pagination-location',
				'title'			=> __( 'Pagination Location', 'ultimate-reviews' ),
				'description'	=> __( 'Where should the pagination controls be located, if there are more reviews than the maximum per page?', 'ultimate-reviews' ),
				'default'		=> $this->defaults['pagination-location'],
				'options'		=> array(
					'top'			=> __( 'Top', 'ultimate-reviews' ),
					'bottom'		=> __( 'Bottom', 'ultimate-reviews' ),
					'both'			=> __( 'Both', 'ultimate-reviews' ),
				)
			)
		);

		$sap->add_section(
	    	'ewd-urp-settings',
	    	array(
	    		'id'     => 'ewd-urp-elements-tab',
	    		'title'  => __( 'Elements', 'ultimate-reviews' ),
	    		'is_tab' => true,
	    	)
	    );

		$sap->add_section(
	    	'ewd-urp-settings',
	    	array(
	        	'id'            => 'ewd-urp-elements-general',
	        	'title'         => __( 'Review Element Options', 'ultimate-reviews' ),
	        	'tab'         => 'ewd-urp-elements-tab',
	    	)
	    );
	
	    $sap->add_setting(
	    	'ewd-urp-settings',
	    	'ewd-urp-elements-general',
	    	'toggle',
	    	array(
	        	'id'      => 'indepth-reviews',
	        	'title'     => __( 'In-Depth Reviews', 'ultimate-reviews' ),
	        	'description' => __( 'Should the reviews have multiple parts (set in the table below) rather than just an overall score?', 'ultimate-reviews' )
	    	)
	    );
	
	    $review_elements_description = '<ul><li>';
	    $review_elements_description .= __( 'Use the table above to add fields to your submit review form (requires that in-depth reviews be enabled).', 'ultimate-reviews' );
	    $review_elements_description .= '</li><li>';
	    $review_elements_description .= __( 'You can drag and drop the elements in the table to arrange the order in which they will appear.', 'ultimate-reviews' );
	    $review_elements_description .= '</li><li>';
	    $review_elements_description .= __( 'The "Review Line" field type will add a new in-depth category that visitors can rate and that will count towards the overall score (ex: Appearance, Value, etc.).', 'ultimate-reviews' );
	    $review_elements_description .= '</li><li>';
	    $review_elements_description .= __( 'For the "Radio" and "Checkbox" field types, supply a comma-separated list of your desired input values in the "Options" column.', 'ultimate-reviews' );
	    $review_elements_description .= '</li></ul>';
	
	    $sap->add_setting(
	    	'ewd-urp-settings',
	    	'ewd-urp-elements-general',
	    	'infinite_table',
	    	array(
	    		'id'      => 'review-elements',
	    		'title'     => __( 'Review Elements', 'ultimate-reviews' ),
	    		'add_label'   => __( '+ ADD', 'ultimate-reviews' ),
	    		'del_label'   => __( 'Delete', 'ultimate-reviews' ),
	    		'description' => $review_elements_description,
	    		'fields'    => array(
	        		'name' => array(
	        			'type'    => 'text',
	        			'label'   => __( 'Field Name', 'ultimate-reviews' ),
	        			'required'  => true
	        		),
	        		'required' => array(
	        			'type'    => 'select',
	        			'label'   => __( 'Required', 'ultimate-reviews' ),
	        			'required'  => true,
	        			'options'   => array(
	        				0       => 'No',
	        				1       => 'Yes'
	        			)
	          		),
	        		'explanation' => array(
	        			'type'    => 'select',
	        			'label'   => __( 'Explanation Allowed', 'ultimate-reviews' ),
	        			'required'  => true,
	        			'options'   => array(
	        				1       => 'Yes',
	        				0       => 'No'
	        			)
	          		),
	        		'type' => array(
	        			'type'    => 'select',
	        			'label'   => __( 'Type', 'ultimate-reviews' ),
	        			'required'  => true,
	        			'options'   => array(
	        				'default'     => __( 'Default Field', 'ultimate-reviews' ),
	        				'reviewitem'  => __( 'Review Line', 'ultimate-reviews' ),
	        				'text'      => __( 'Text Box', 'ultimate-reviews' ),
	        				'textarea'    => __( 'Text Area', 'ultimate-reviews' ),
	        				'dropdown'    => __( 'Dropdown', 'ultimate-reviews' ),
	        				'checkbox'    => __( 'Checkbox', 'ultimate-reviews' ),
	        				'radio'     => __( 'Radio', 'ultimate-reviews' ),
	        				'date'      => __( 'Date', 'ultimate-reviews' ),
	        				'datetime'    => __( 'Date/Time', 'ultimate-reviews' ),
	        			)
	          		),
	        		'options' => array(
	        			'type'    => 'text',
	        			'label'   => __( 'Options', 'ultimate-reviews' ),
	        			'required'  => false
	        		),
	        	),
	        	'default'			=> $this->defaults['review-elements'],
	        	'conditional_on'    => 'indepth-reviews',
	        	'conditional_on_value'  => true
	    	)
	    );

	    $sap->add_section(
	    	'ewd-urp-settings',
	    	array(
	    		'id'     => 'ewd-urp-ordering-tab',
	    		'title'  => __( 'Ordering', 'ultimate-reviews' ),
	    		'is_tab' => true,
	    	)
	    );

	    $sap->add_section(
    		'ewd-urp-settings',
    		array(
    		  'id'            => 'ewd-urp-ordering-general',
    		  'title'         => __( 'Order Options', 'ultimate-reviews' ),
    		  'tab'         => 'ewd-urp-ordering-tab',
    		)
    	);

    	$sap->add_setting(
    		'ewd-urp-settings',
    		'ewd-urp-ordering-general',
    		'toggle',
    		array(
    			'id'      => 'group-by-product',
    			'title'     => __( 'Group By Product', 'ultimate-reviews' ),
    			'description' => __( 'If the product_name attribute is left blank, should the reviews be grouped by the product they review?', 'ultimate-reviews' )
    		)
    	);

    	$sap->add_setting(
    		'ewd-urp-settings',
    		'ewd-urp-ordering-general',
    		'radio',
    		array(
    			'id'      => 'group-by-product-order',
    			'title'     => __( 'Group By Product Direction', 'ultimate-reviews' ),
    			'description' => __( 'If reviews are grouped by product name, should they be grouped in ascending or descending order?', 'ultimate-reviews' ),
    			'default'   => $this->defaults['group-by-product-order'],
    			'options'   => array(
    				'asc'     => __( 'Ascending', 'ultimate-reviews' ),
    				'desc'      => __( 'Descending', 'ultimate-reviews' ),
    			),
    			'conditional_on'    => 'group-by-product',
    			'conditional_on_value'  => true
    		)
    	);

    	$sap->add_setting(
    		'ewd-urp-settings',
    		'ewd-urp-ordering-general',
    		'radio',
    		array(
    			'id'      => 'ordering-type',
    			'title'     => __( 'Ordering Type', 'ultimate-reviews' ),
    			'description' => __( 'What type of ordering should be used for the reviews?', 'ultimate-reviews' ),
    			'default'   => $this->defaults['ordering-type'],
    			'options'   => array(
    				'date'      => __( 'Submitted Date', 'ultimate-reviews' ),
    				'karma'     => __( 'Review Karma (Not possible if grouping by product name)', 'ultimate-reviews' ),
    				'rating'    => __( 'Rating (Not possible if grouping by product name)', 'ultimate-reviews' ),
    				'title'     => __( 'Review Title', 'ultimate-reviews' ),
    			)
    		)
    	);

    	$sap->add_setting(
    		'ewd-urp-settings',
    		'ewd-urp-ordering-general',
    		'radio',
    		array(
    			'id'      => 'order-direction',
    			'title'     => __( 'Order Direction', 'ultimate-reviews' ),
    			'description' => __( 'Should the ordering be ascending or descending?', 'ultimate-reviews' ),
    			'default'   => $this->defaults['order-direction'],
    			'options'   => array(
    				'asc'     => __( 'Ascending', 'ultimate-reviews' ),
    				'desc'      => __( 'Descending', 'ultimate-reviews' ),
    			)
    		)
    	);

		/**
	     * Premium options preview only
	     */
	    // "Premium" Tab
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'     => 'ewd-urp-premium-tab',
	        'title'  => __( 'Premium', 'ultimate-reviews' ),
	        'is_tab' => true,
	        'show_submit_button' => $this->show_submit_button( 'premium' )
	      )
	    );
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'       => 'ewd-urp-premium-tab-body',
	        'tab'      => 'ewd-urp-premium-tab',
	        'callback' => $this->premium_info( 'premium' )
	      )
	    );
	
	    // "WooCommerce" Tab
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'     => 'ewd-urp-woocommerce-tab',
	        'title'  => __( 'WooCommerce', 'ultimate-reviews' ),
	        'is_tab' => true,
	        'show_submit_button' => $this->show_submit_button( 'woocommerce' )
	      )
	    );
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'       => 'ewd-urp-woocommerce-tab-body',
	        'tab'      => 'ewd-urp-woocommerce-tab',
	        'callback' => $this->premium_info( 'woocommerce' )
	      )
	    );
	
	    // "Labelling" Tab
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'     => 'ewd-urp-labelling-tab',
	        'title'  => __( 'Labelling', 'ultimate-reviews' ),
	        'is_tab' => true,
	        'show_submit_button' => $this->show_submit_button( 'labelling' )
	      )
	    );
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'       => 'ewd-urp-labelling-tab-body',
	        'tab'      => 'ewd-urp-labelling-tab',
	        'callback' => $this->premium_info( 'labelling' )
	      )
	    );
	
	    // "Styling" Tab
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'     => 'ewd-urp-styling-tab',
	        'title'  => __( 'Styling', 'ultimate-reviews' ),
	        'is_tab' => true,
	        'show_submit_button' => $this->show_submit_button( 'styling' )
	      )
	    );
	    $sap->add_section(
	      'ewd-urp-settings',
	      array(
	        'id'       => 'ewd-urp-styling-tab-body',
	        'tab'      => 'ewd-urp-styling-tab',
	        'callback' => $this->premium_info( 'styling' )
	      )
	    );
		
		$sap = apply_filters( 'ewd_urp_settings_page', $sap, $this );

		$sap->add_admin_menus();

	}

	public function show_submit_button( $permission_type = '' ) {
		global $ewd_urp_controller;

		if ( $ewd_urp_controller->permissions->check_permission( $permission_type ) ) {
			return true;
		}

		return false;
	}

	public function premium_info( $section_and_perm_type ) {
		global $ewd_urp_controller;

		$is_premium_user = $ewd_urp_controller->permissions->check_permission( $section_and_perm_type );
		$is_helper_installed = defined( 'EWDPH_PLUGIN_FNAME' ) && is_plugin_active( EWDPH_PLUGIN_FNAME );

		if ( $is_premium_user || $is_helper_installed ) {
			return false;
		}

		$content = '';

		$premium_features = '
			<p><strong>' . __( 'The premium version also gives you access to the following features:', 'ultimate-reviews' ) . '</strong></p>
			<ul class="ewd-urp-dashboard-new-footer-one-benefits">
				<li>' . __( 'Review Search', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Review Summaries', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Replace WooCommerce Reviews', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Schema Microdata', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Multiple Review Layouts', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Import/Export Reviews', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Advanced Styling Options', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Admin Notifications', 'ultimate-reviews' ) . '</li>
				<li>' . __( 'Email Support', 'ultimate-reviews' ) . '</li>
			</ul>
			<div class="ewd-urp-dashboard-new-footer-one-buttons">
				<a class="ewd-urp-dashboard-new-upgrade-button" href="https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=urp_settings&utm_content=' . $section_and_perm_type . '" target="_blank">' . __( 'UPGRADE NOW', 'ultimate-reviews' ) . '</a>
			</div>
		';

		switch ( $section_and_perm_type ) {

			case 'premium':

				$content = '
					<div class="ewd-urp-settings-preview">
						<h2>' . __( 'Premium', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'The premium options let you change the review format, enable admin approval of reviews, schema structured data and summary statistics, add a captcha to the submit form, and more.', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/premium1.png" alt="URP premium screenshot one">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/premium2.png" alt="URP premium screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'woocommerce':

				$content = '
					<div class="ewd-urp-settings-preview">	
						<h2>' . __( 'WooCommerce', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'The WooCommerce options let you use Ultimate Reviews with WooCommerce products. This includes the ability to replace the reviews tab on product pages and override the star rating for products with the reviews and scores from the Ultimate Reviews system.', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/woocommerce1.png" alt="URP woocommerce screenshot one">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'elements':

				$content = '
					<div class="ewd-urp-settings-preview">
						<h2>' . __( 'Elements', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'You can add extra custom fields to your reviews. These can either be additional review fields (such as for value, appearance, quality, etc.) or fields that don’t affect the score/rating (such as text, checkbox, radio button and dropdown fields).', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/elements.png" alt="URP elements screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'ordering':

				$content = '
					<div class="ewd-urp-settings-preview">
						<h2>' . __( 'Ordering', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'The ordering options let you choose whether or not you want to group your reviews by product and also allow you to set the order type and direction for your reviews.', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/ordering.png" alt="URP ordering screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'labelling':

				$content = '
					<div class="ewd-urp-settings-preview">
						<h2>' . __( 'Labelling', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'The labelling options let you change the wording of the different labels that appear on the front end of the plugin. You can use this to translate them, customize the wording for your purpose, etc.', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/labelling1.png" alt="URP labelling screenshot one">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/labelling2.png" alt="URP labelling screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'styling':

				$content = '
					<div class="ewd-urp-settings-preview">
						<h2>' . __( 'Styling', 'ultimate-reviews' ) . '<span>' . __( 'Premium', 'ultimate-reviews' ) . '</span></h2>
						<p>' . __( 'The styling options let you choose a review style, set the in-depth reviews and read more styles, as well as modify the color, font size, font family, border, margin and padding of the various elements found in your reviews.', 'ultimate-reviews' ) . '</p>
						<div class="ewd-urp-settings-preview-images">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/styling1.png" alt="URP styling screenshot one">
							<img src="' . EWD_URP_PLUGIN_URL . '/assets/img/premium-screenshots/styling2.png" alt="URP styling screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;
		}

		return function() use ( $content ) {

			echo wp_kses_post( $content );
		};
	}

	/**
	 * Returns the default review element fields
	 *
	 * @since 3.0.0
	 */
	public function get_default_review_elements() {

		$review_elements = array(
			(object) array( 
				'name' 			=> 'Product Name (if applicable)',
			 	'required' 		=> 'yes',
			 	'explanation' 	=> 'no',
			 	'type' 			=> 'default',
			 	'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review Author',
				'required' 		=> 'no',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Reviewer Email (if applicable)',
				'required' 		=> 'no',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review Title',
				'required' 		=> 'yes',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review Image (if applicable)',
				'required' 		=> 'no',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review Video (if applicable)', 
				'required' 		=> 'no', 
				'explanation' 	=> 'no', 
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review',
				'required' 		=> 'yes',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Overall Score',
				'required' 		=> 'yes',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			),
			(object) array( 
				'name' 			=> 'Review Category (if applicable)',
				'required' 		=> 'no',
				'explanation' 	=> 'no',
				'type' 			=> 'default',
				'options' 		=> ''
			)
		);

		return $review_elements;
	}

	/**
	 * Returns the review elements that should be displayed
	 *
	 * @since 3.0.0
	 */
	public function get_review_elements() {
		
		if ( $this->get_setting( 'indepth-reviews' ) ) {
			return ewd_urp_decode_infinite_table_setting( $this->get_setting( 'review-elements' ) );
		}

		return $this->get_default_review_elements();
	}

	public function review_maximum_score( $value )
	{
		return 0 < intval( $value ) ? intval( $value ) : $this->defaults[ 'maximum-score' ];
	}

}
} // endif;
