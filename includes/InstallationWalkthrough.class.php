<?php

/**
 * Class to handle everything related to the walk-through that runs on plugin activation
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class ewdurpInstallationWalkthrough {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_install_screen' ) );
		add_action( 'admin_head', array( $this, 'hide_install_screen_menu_item' ) );
		add_action( 'admin_init', array( $this, 'redirect' ), 9999 );

		add_action( 'admin_head', array( $this, 'admin_enqueue' ) );

		add_action( 'wp_ajax_ewd_urp_welcome_add_submit_review_page', array( $this, 'add_submit_reviews_page' ) );
		add_action( 'wp_ajax_ewd_urp_welcome_add_display_review_page', array( $this, 'add_display_reviews_page' ) );
		add_action( 'wp_ajax_ewd_urp_welcome_set_options', array( $this, 'set_options' ) );
		add_action( 'wp_ajax_ewd_urp_welcome_add_category', array( $this, 'create_category' ) );
	}

	/**
	 * On activation, redirect the user if they haven't used the plugin before
	 * @since 3.0.0
	 */
	public function redirect() {
		if ( ! get_transient( 'ewd-urp-getting-started' ) ) 
			return;

		delete_transient( 'ewd-urp-getting-started' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		if ( ! empty( get_posts( array( 'post_type' => EWD_URP_REVIEW_POST_TYPE ) ) ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'index.php?page=ewd-urp-getting-started' ) );
		exit;
	}

	/**
	 * Create the installation admin page
	 * @since 3.0.0
	 */
	public function register_install_screen() {

		add_dashboard_page(
			esc_html__( 'Ultimate Reviews - Welcome!', 'ultimate-reviews' ),
			esc_html__( 'Ultimate Reviews - Welcome!', 'ultimate-reviews' ),
			'manage_options',
			'ewd-urp-getting-started',
			array($this, 'display_install_screen')
		);
	}

	/**
	 * Hide the installation admin page from the WordPress sidebar menu
	 * @since 3.0.0
	 */
	public function hide_install_screen_menu_item() {

		remove_submenu_page( 'index.php', 'ewd-urp-getting-started' );
	}

	/**
	 * Add in a page with the ultimate reviews shortcode
	 * @since 3.0.0
	 */
	public function add_display_reviews_page() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-getting-started', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		wp_insert_post( array(
				'post_title' => ( isset($_POST['display_review_page_title'] ) ? sanitize_text_field( $_POST['display_review_page_title'] ) : '' ),
				'post_content' => '<!-- wp:paragraph --><p> [ultimate-reviews] </p><!-- /wp:paragraph -->',
				'post_status' => 'publish',
				'post_type' => 'page'
			)
		);

		exit();
	}

	/**
	 * Add in a page with the submit review shortcode
	 * @since 3.0.0
	 */
	public function add_submit_reviews_page() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-getting-started', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		wp_insert_post( array(
    	    'post_title' => ( isset($_POST['submit_review_page_title'] ) ? sanitize_text_field( $_POST['submit_review_page_title'] ) : ''),
        	'post_content' => '<!-- wp:paragraph --><p> [submit-review] </p><!-- /wp:paragraph -->',
    	    'post_status' => 'publish',
    	    'post_type' => 'page'
    	) );
	
	    exit();
	}

	/**
	 * Set a number of key options selected during the walk-through process
	 * @since 3.0.0
	 */
	public function set_options() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-getting-started', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		$ewd_urp_options = get_option( 'ewd-urp-settings' );

		$review_filtering = json_decode( stripslashes( $_POST['review_filtering'] ) );
		$review_filtering = is_array( $review_filtering ) ? array_map( 'sanitize_text_field', $review_filtering ) : array();

		if ( isset( $_POST['maximum_score'] ) ) { $ewd_urp_options['maximum-score'] = sanitize_text_field( $_POST['maximum_score'] ); }
		if ( isset( $_POST['review_score_input'] ) ) { $ewd_urp_options['review-score-input'] = sanitize_text_field( $_POST['review_score_input'] ); }
		if ( isset( $_POST['review_category'] ) ) { $ewd_urp_options['review-category'] = sanitize_text_field( $_POST['review_category'] ); }
		if ( isset( $_POST['review_filtering'] ) ) { $ewd_urp_options['review-filtering'] = $review_filtering; }

		update_option( 'ewd-urp-settings', $ewd_urp_options );
	
	    exit();
	}

	/**
	 * Create a reviews category
	 * @since 3.0.0
	 */
	public function create_category() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-getting-started', 'nonce' ) || ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		$category_name = isset( $_POST['category_name'] ) ? sanitize_text_field( $_POST['category_name'] ) : '';
		$category_description = isset( $_POST['category_description'] ) ? sanitize_textarea_field( $_POST['category_description'] ) : '';

		$category_term_id = wp_insert_term( $category_name, EWD_URP_REVIEW_CATEGORY_TAXONOMY, array('description' => $category_description) );

		echo json_encode ( array( 'category_name' => $category_name, 'category_id' => $category_term_id['term_id'] ) );

		exit();
	}

	/**
	 * Enqueue the admin assets necessary to run the walk-through and display it nicely
	 * @since 3.0.0
	 */
	public function admin_enqueue() {

		if ( ! isset( $_GET['page'] ) or $_GET['page'] != 'ewd-urp-getting-started' ) { return; }

		wp_enqueue_style( 'ewd-urp-admin-css', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp-admin.css', array(), EWD_URP_VERSION );
		wp_enqueue_style( 'ewd-urp-sap-admin-css', EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/css/admin.css', array(), EWD_URP_VERSION );
		wp_enqueue_style( 'ewd-urp-welcome-screen', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp-welcome-screen.css', array(), EWD_URP_VERSION );
		wp_enqueue_style( 'ewd-urp-admin-settings-css', EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/css/admin-settings.css', array(), EWD_URP_VERSION );
		
		wp_enqueue_script( 'ewd-urp-getting-started', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-welcome-screen.js', array( 'jquery' ), EWD_URP_VERSION );
		wp_enqueue_script( 'ewd-urp-admin-settings-js', EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/js/admin-settings.js', array( 'jquery' ), EWD_URP_VERSION );
		wp_enqueue_script( 'ewd-urp-admin-spectrum-js', EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/js/spectrum.js', array( 'jquery' ), EWD_URP_VERSION );

		wp_localize_script(
			'ewd-urp-getting-started',
			'ewd_urp_getting_started',
			array(
				'nonce' => wp_create_nonce( 'ewd-urp-getting-started' )
			)
		);
	}

	/**
	 * Output the HTML of the walk-through screen
	 * @since 3.0.0
	 */
	public function display_install_screen() { 
		global $ewd_urp_controller;

		$maximum_score = $ewd_urp_controller->settings->get_setting( 'maximum-score' );
		$review_score_input = $ewd_urp_controller->settings->get_setting( 'review-score-input' );
		$review_category = $ewd_urp_controller->settings->get_setting( 'review-category' );
		$review_filtering = $ewd_urp_controller->settings->get_setting( 'review-filtering' );
		$review_filtering = is_array( $review_filtering ) ? $review_filtering : array();

		?>

		<div class='ewd-urp-welcome-screen'>
			
			<div class='ewd-urp-welcome-screen-header'>
				<h1><?php _e('Welcome to Ultimate Reviews', 'ultimate-reviews'); ?></h1>
				<p><?php _e('Thanks for choosing Ultimate Reviews! The following will help you get started with the setup by creating pages to accept and display reviews, configuring a few key options, and creating some review categories.', 'ultimate-reviews'); ?></p>
			</div>

			<div class='ewd-urp-welcome-screen-box ewd-urp-welcome-screen-submit-review ewd-urp-welcome-screen-open' data-screen='submit-review'>
				<h2><?php _e('1. Submit Review Page', 'ultimate-reviews'); ?></h2>
				<div class='ewd-urp-welcome-screen-box-content'>
					<p><?php _e('You can create a dedicated submit review page below, or skip this step and add your submit review form to a page you\'ve already created manually.', 'ultimate-reviews'); ?></p>
					<table class='form-table ewd-urp-welcome-screen-submit-review-page'>
						<tr class='ewd-urp-welcome-screen-add-submit-review-page-name ewd-urp-welcome-screen-box-content-divs'>
							<th scope='row'><?php _e( 'Page Title', 'ultimate-reviews' ); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<input type='text' value='Submit Review'>
							</td>
						</tr>
						<tr>
							<th scope='row'></th>
							<td>
								<div class='ewd-urp-welcome-screen-add-submit-review-page-button' data-nextaction='display-review'><?php _e( 'Create Page', 'ultimate-reviews' ); ?></div>
							</td>
						</tr>
					</table>

					<div class='ewd-urp-welcome-clear'></div>
					<div class='ewd-urp-welcome-screen-next-button' data-nextaction='display-review'><?php _e('Next', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-clear'></div>
				</div>
			</div>

			<div class='ewd-urp-welcome-screen-box ewd-urp-welcome-screen-display-review' data-screen='display-review'>
				<h2><?php _e('2. Display Reviews Page', 'ultimate-reviews'); ?></h2>
				<div class='ewd-urp-welcome-screen-box-content'>
					<p><?php _e('You can create a dedicated page for displaying reviews below, or skip this step and add your review display form to a page you\'ve already created manually.', 'ultimate-reviews'); ?></p>
					<table class='form-table ewd-urp-welcome-screen-display-review-page'>
						<tr class='ewd-urp-welcome-screen-add-display-review-page-name ewd-urp-welcome-screen-box-content-divs'>
							<th scope='row'><?php _e( 'Page Title', 'ultimate-reviews' ); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<input type='text' value='Reviews'>
							</td>
						</tr>
						<tr>
							<th scope='row'></th>
							<td>
								<div class='ewd-urp-welcome-screen-add-display-review-page-button' data-nextaction='options'><?php _e( 'Create Page', 'ultimate-reviews' ); ?></div>
							</td>
						</tr>
					</table>

					<div class='ewd-urp-welcome-clear'></div>
					<div class='ewd-urp-welcome-screen-next-button' data-nextaction='options'><?php _e('Next', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-welcome-screen-previous-button' data-previousaction='submit-review'><?php _e('Previous', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-clear'></div>
				</div>
			</div>

			<div class='ewd-urp-welcome-screen-box ewd-urp-welcome-screen-options' data-screen='options'>
				<h2><?php _e('3. Set Key Options', 'ultimate-reviews'); ?></h2>
				<div class='ewd-urp-welcome-screen-box-content'>
					<p><?php _e('Options can always be changed later, but here are a few tha a lot of users want to set for themselves.', 'ultimate-reviews'); ?></p>
					<table class='form-table'>
						<tr>
							<th scope='row'><?php _e('Maximum Review Score', 'ultimate-reviews'); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<input type='text' name='maximum_score' class='sap-small-text-input' value='<?php echo $maximum_score; ?>' />
								<p class='description'><?php _e('What should the maximum score be on the review form? Common values are 100 for the \'percentage\' review style, and 5 or 10 for the other styles.', 'ultimate-reviews'); ?></p>
							</td>
						</tr>
						<tr>
							<th scope='row'><?php _e('Review Score Input', 'ultimate-reviews'); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<fieldset>
									<label title='Text' class='sap-admin-input-container'><input type='radio' name='review_score_input' value='text' <?php echo ( $review_score_input == 'text' ? 'checked' : '' );  ?> /><span class='sap-admin-radio-button'></span> <span>Text</span></label><br />
									<label title='Select' class='sap-admin-input-container'><input type='radio' name='review_score_input' value='select' <?php echo ( $review_score_input == 'select' ? 'checked' : '' ); ?> /><span class='sap-admin-radio-button'></span> <span>Select</span></label><br />
									<label title='Stars' class='sap-admin-input-container'><input type='radio' name='review_score_input' value='stars' <?php echo ( $review_score_input == 'stars' ? 'checked' : '' ); ?> /><span class='sap-admin-radio-button'></span> <span>Stars</span></label><br />
									<p class='description'><?php _e('What type of input should be used for review scores in the submit-review shortcode?', 'ultimate-reviews'); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope='row'><?php _e('Review Category', 'ultimate-reviews'); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<fieldset>
									<div class='sap-admin-hide-radios'>
										<input type='checkbox' name='review_category' value='1'>
									</div>
									<label class='sap-admin-switch'>
										<input type='checkbox' class='sap-admin-option-toggle' data-inputname='review_category' <?php if($review_category == "1") {echo "checked='checked'";} ?>>
										<span class='sap-admin-switch-slider round'></span>
									</label>		
									<p class='description'><?php _e('Should the reviewer be able to select a category for their review?', 'ultimate-reviews'); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope='row'><?php _e('Review Filtering', 'ultimate-reviews'); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<fieldset>
									<label title='Score' class='sap-admin-input-container'><input type='checkbox' name='review_filtering[]' value='score' <?php echo ( in_array( 'score', $review_filtering ) ? 'checked' : '' ); ?> /><span class='sap-admin-checkbox'></span> <span>Review Score</span></label><br />
									<label title='Name' class='sap-admin-input-container'><input type='checkbox' name='review_filtering[]' value='name' <?php echo ( in_array( 'name', $review_filtering ) ? 'checked' : '' ); ?> /><span class='sap-admin-checkbox'></span> <span>Product Name</span></label><br />
									<label title='Author' class='sap-admin-input-container'><input type='checkbox' name='review_filtering[]' value='author' <?php echo ( in_array( 'author', $review_filtering ) ? 'checked' : '' ); ?> /><span class='sap-admin-checkbox'></span> <span>Review Author</span></label><br />
									<p class='description'><?php _e('Should visitors be able to filter reviews by product name, score or review author?', 'ultimate-reviews'); ?></p>
								</fieldset>
							</td>
						</tr>
					</table>
		
					<div class='ewd-urp-welcome-screen-save-options-button'><?php _e('Save Options', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-welcome-clear'></div>
					<div class='ewd-urp-welcome-screen-previous-button' data-previousaction='display-review'><?php _e('Previous', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-welcome-screen-next-button' data-nextaction='categories'><?php _e('Next', 'ultimate-reviews'); ?></div>
					
					<div class='ewd-urp-clear'></div>
				</div>
			</div>

			<div class='ewd-urp-welcome-screen-box ewd-urp-welcome-screen-categories' data-screen='categories'>
				<h2><?php _e('4. Categories', 'ultimate-reviews'); ?></h2>
				<div class='ewd-urp-welcome-screen-box-content'>
					<p><?php _e('Categories let you organize your reviews in a way that\'s easy for you - and your customers - to find.', 'ultimate-reviews'); ?></p>
					<table class='form-table ewd-urp-welcome-screen-created-categories'>
						<tr class='ewd-urp-welcome-screen-add-category-name ewd-urp-welcome-screen-box-content-divs'>
							<th scope='row'><?php _e( 'Category Name', 'ultimate-reviews' ); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<input type='text'>
							</td>
						</tr>
						<tr class='ewd-urp-welcome-screen-add-category-description ewd-urp-welcome-screen-box-content-divs'>
							<th scope='row'><?php _e( 'Category Description', 'ultimate-reviews' ); ?></th>
							<td class='ewd-urp-welcome-screen-option'>
								<textarea></textarea>
							</td>
						</tr>
						<tr>
							<th scope='row'></th>
							<td>
								<div class='ewd-urp-welcome-screen-add-category-button'><?php _e('Add Category', 'ultimate-reviews'); ?></div>
							</td>
						</tr>
						<tr></tr>
						<tr>
							<td colspan="2">
								<h3><?php _e('Created Categories', 'ultimate-reviews'); ?></h3>
								<table class='ewd-urp-welcome-screen-show-created-categories'>
									<tr>
										<th class='ewd-urp-welcome-screen-show-created-categories-name'><?php _e('Name', 'ultimate-reviews'); ?></th>
										<th class='ewd-urp-welcome-screen-show-created-categories-description'><?php _e('Description', 'ultimate-reviews'); ?></th>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
					<div class='ewd-urp-welcome-screen-previous-button ewd-urp-welcome-screen-previous-button-not-top-margin' data-previousaction='options'><?php _e('Previous Step', 'ultimate-reviews'); ?></div>
					<div class='ewd-urp-welcome-screen-finish-button'><a href='admin.php?page=ewd-urp-settings'><?php _e('Finish', 'ultimate-faqs'); ?></a></div>
					<div class='clear'></div>
				</div>
			</div>
		
			<div class='ewd-urp-welcome-screen-skip-container'>
				<a href='admin.php?page=ewd-urp-settings'><div class='ewd-urp-welcome-screen-skip-button'><?php _e('Skip Setup', 'ultimate-reviews'); ?></div></a>
			</div>
		</div>

	<?php }
}


?>