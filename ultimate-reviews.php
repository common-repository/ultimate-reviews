<?php
/*
Plugin Name: Ultimate Reviews
Plugin URI: https://www.etoilewebdesign.com/plugins/ultimate-reviews/
Description: Reviews plugin to let visitors submit reviews and display them via shortcode or widget. Replace WooCommerce reviews and ratings. Require login, etc.
Author: Etoile Web Design
Author URI: https://www.etoilewebdesign.com/
Terms and Conditions: https://www.etoilewebdesign.com/plugin-terms-and-conditions/
Text Domain: ultimate-reviews
Version: 3.2.12
WC requires at least: 7.1
WC tested up to: 9.0
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'ewdurpInit' ) ) {
class ewdurpInit {

	// pointers to classes used by the plugin, where needed
	public $cpts;
	public $permissions;
	public $settings;
	public $woocommerce;

	// Any data that needs to be passed from PHP to our JS files 
	public $front_end_php_js_data = array();

	public $schema_review_data = array();

	// Whether a shortcode is current outputting or not
	public $shortcode_printing = false;

	/**
	 * Initialize the plugin and register hooks
	 */
	public function __construct() {

		self::constants();
		self::includes();
		self::instantiate();
		self::wp_hooks();
	}

	/**
	 * Define plugin constants.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @return void
	 */
	protected function constants() {

		define( 'EWD_URP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'EWD_URP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'EWD_URP_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'EWD_URP_TEMPLATE_DIR', 'ewd-urp-templates' );
		define( 'EWD_URP_VERSION', '3.2.12' );

		define( 'EWD_URP_REVIEW_POST_TYPE', 'urp_review' );
		define( 'EWD_URP_REVIEW_CATEGORY_TAXONOMY', 'urp-review-category' );
	}

	/**
	 * Include necessary classes.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @return void
	 */
	protected function includes() {

		require_once( EWD_URP_PLUGIN_DIR . '/includes/AboutUs.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Ajax.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Blocks.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Patterns.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/CustomPostTypes.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Dashboard.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/DeactivationSurvey.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Export.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Helper.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Import.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/InstallationWalkthrough.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Notifications.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Permissions.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Query.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Review.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/ReviewAsk.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Settings.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/template-functions.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/Widgets.class.php' );
		require_once( EWD_URP_PLUGIN_DIR . '/includes/WooCommerce.class.php' );
	}

	/**
	 * Spin up instances of our plugin classes.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @return void
	 */
	protected function instantiate() {

		new ewdurpDashboard();
		new ewdurpDeactivationSurvey();
		new ewdurpInstallationWalkthrough();
		new ewdurpReviewAsk();

		$this->cpts 		= new ewdurpCustomPostTypes();
		$this->permissions 	= new ewdurpPermissions();
		$this->settings 	= new ewdurpSettings(); 
		$this->woocommerce 	= new ewdurpWooCommerce();

		new ewdurpAJAX();
		new ewdurpBlocks();
		if ( function_exists( 'register_block_pattern' ) ) { new ewdurpPatterns(); }
		new ewdurpExport();
		new ewdurpImport();
		new ewdurpNotifications();
		new ewdurpWidgetManager();

		new ewdurpAboutUs();
	}

	/**
	 * Run walk-through, load assets, add links to plugin listing, etc.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @return void
	 */
	protected function wp_hooks() {

		register_activation_hook( __FILE__, 	array( $this, 'run_walkthrough' ) );
		register_activation_hook( __FILE__, 	array( $this, 'convert_options' ) );

		add_filter( 'the_content', 				array( $this, 'alter_review_content' ) );
		add_filter( 'the_author', 				array( $this, 'alter_review_author' ) );
		add_action( 'wp_footer', 				array( $this, 'output_ld_json_content' ) );

		add_action( 'init',			        	array( $this, 'load_view_files' ) );

		add_action( 'plugins_loaded',        	array( $this, 'load_textdomain' ) );

		add_action( 'admin_notices', 			array( $this, 'display_header_area' ) );
		add_action( 'admin_notices', 			array( $this, 'maybe_display_helper_notice' ) );

		add_action( 'admin_enqueue_scripts', 	array( $this, 'enqueue_admin_assets' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', 		array( $this, 'register_assets' ) );
		add_action( 'wp_head',					'ewd_add_frontend_ajax_url' );
		add_action( 'wp_footer', 				array( $this, 'assets_footer' ), 2 );

		add_filter( 'plugin_action_links',		array( $this, 'plugin_action_links' ), 10, 2);

		add_action( 'wp_ajax_ewd_urp_hide_helper_notice', array( $this, 'hide_helper_notice' ) );

		add_action( 'before_woocommerce_init', array( $this, 'declare_wc_hpos' ) );
	}

	/**
	 * Run the options conversion function on update if necessary
	 *
	 * @since  3.0.0
	 * @access protected
	 * @return void
	 */
	public function convert_options() {
		
		require_once( EWD_URP_PLUGIN_DIR . '/includes/BackwardsCompatibility.class.php' );
		new ewdurpBackwardsCompatibility();
	}

	/**
	 * Load files needed for views
	 * @since 3.0.0
	 * @note Can be filtered to add new classes as needed
	 */
	public function load_view_files() {
	
		$files = array(
			EWD_URP_PLUGIN_DIR . '/views/Base.class.php' // This will load all default classes
		);
	
		$files = apply_filters( 'ewd_urp_load_view_files', $files );
	
		foreach ( $files as $file ) {
			require_once( $file );
		}
	
	}

	/**
	 * Load the plugin textdomain for localisation
	 * @since 3.0.0
	 */
	public function load_textdomain() {
		
		load_plugin_textdomain( 'ultimate-reviews', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Set a transient so that the walk-through gets run
	 * @since 3.0.0
	 */
	public function run_walkthrough() {

		set_transient( 'ewd-urp-getting-started', true, 30 );
	} 

	/**
	 * Enqueue the admin-only CSS and Javascript
	 * @since 3.0.0
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;

		wp_enqueue_script( 'ewd-urp-helper-notice', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-helper-install-notice.js', array( 'jquery' ), EWD_URP_VERSION, true );
		wp_localize_script(
			'ewd-urp-helper-notice',
			'ewd_urp_helper_notice',
			array( 'nonce' => wp_create_nonce( 'ewd-urp-helper-notice' ) )
		);

		wp_enqueue_style( 'ewd-urp-helper-notice', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp-helper-install-notice.css', array(), EWD_URP_VERSION );

		$screen = get_current_screen();

		$candidates = array(
			EWD_URP_REVIEW_POST_TYPE,

			'urp_review_page_ewd-urp-dashboard',
			'urp_review_page_ewd-urp-import',
			'urp_review_page_ewd-urp-export',
			'urp_review_page_ewd-urp-settings',

			'edit-urp-review-category',

			'widgets.php',
		);

   		// Return if not urp_review post_type, we're not on a post-type page, or we're not on the settings or widget pages
		if ( ! in_array( $hook, $candidates )
			and ( empty( $screen->post_type ) or ! in_array ( $screen->post_type, $candidates ) )
			and ! in_array( $screen->id, $candidates )
		) {
			return;
		}

		wp_enqueue_style( 'ewd-urp-admin-css', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp-admin.css', array(), EWD_URP_VERSION );
		wp_enqueue_script( 'ewd-urp-admin-js', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-admin.js', array( 'jquery' ), EWD_URP_VERSION, true );

		$settings = array(
			'nonce' => wp_create_nonce( 'ewd-urp-admin-js' ),
			'ewd_uwpm_display' => get_option( 'EWD_URP_UWPM_Ask_Time' ) < time() ? true : false
		);

		wp_localize_script( 'ewd-urp-admin-js', 'ewd_urp_admin_php_data', $settings );
	}

	/**
	 * Register the front-end CSS and Javascript for the slider
	 * @since 3.0.0
	 */
	public function register_assets() {

		wp_register_style( 'ewd-urp-css', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp.css', EWD_URP_VERSION );
		wp_register_style( 'ewd-urp-jquery-ui', EWD_URP_PLUGIN_URL . '/assets/css/jquery-ui.min.css', EWD_URP_VERSION );

		wp_register_script( 'ewd-urp-masonry-js', EWD_URP_PLUGIN_URL . '/assets/js/masonry.pkgd.min.js', array( 'jquery' ), EWD_URP_VERSION, true );
		wp_register_script( 'ewd-urp-js', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-autocomplete', 'jquery-ui-slider' ), EWD_URP_VERSION, true );
		wp_register_script( 'ewd-urp-pie-graph-js', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-pie-graph.js', array( 'jquery'), EWD_URP_VERSION, true );
		wp_register_script( 'ewd-urp-jquery-datepicker', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-datepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), EWD_URP_VERSION, true );
	}

	/**
	 * Print out any PHP data needed for our JS to work correctly
	 * @since 3.1.0
	 */
	public function assets_footer() {

		if ( empty( $this->front_end_php_js_data ) ) { return; }

		$print_variables = array();

		foreach ( (array) $this->front_end_php_js_data as $variable => $values ) {

			if ( empty( $values ) ) { continue; }

			$print_variables[ $variable ] = ewdurpHelper::escape_js_recursive( $values );
		}

		foreach ( $print_variables as $variable => $values ) {

			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo 'var ' . esc_attr( $variable ) . ' = ' . wp_json_encode( $values ) . "\n";
			echo "/* ]]> */\n";
			echo "</script>\n";
		}
	}

	/**
	 * Adds a variable to be passed to our front-end JS
	 * @since 3.1.0
	 */
	public function add_front_end_php_data( $handle, $variable, $data ) {

		$this->front_end_php_js_data[ $variable ] = $data;
	}

	/**
	 * Add links to the plugin listing on the installed plugins page
	 * @since 3.0.0
	 */
	public function plugin_action_links( $links, $plugin ) {
		global $ewd_urp_controller;
		
		if ( $plugin == EWD_URP_PLUGIN_FNAME ) {

			if ( ! $ewd_urp_controller->permissions->check_permission( 'premium' ) ) {

				array_unshift( $links, '<a class="ewd-urp-plugin-page-upgrade-link" href="https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=wp_admin_plugins_page" title="' . __( 'Try Premium', 'ultimate-reviews' ) . '" target="_blank">' . __( 'Try Premium', 'ultimate-reviews' ) . '</a>' );
			}

			$links['settings'] = '<a href="admin.php?page=ewd-urp-settings" title="' . __( 'Head to the settings page for Ultimate Reviews', 'ultimate-reviews' ) . '">' . __( 'Settings', 'ultimate-reviews' ) . '</a>';
		}

		return $links;

	}

	/**
	 * Replace the content of the single review page with the review shortcode
	 * @since 3.0.0
	 */
	public function alter_review_content( $content ) {
		global $post;

		if ( $post->post_type != 'urp_review' ) { return $content; }

		if ( is_admin() ) { return $content; }

		if ( ! empty( $this->shortcode_printing ) ) { return $content; }

		$content = do_shortcode( '[select-review review_id="' . $post->ID . '"]' );

		return $content;
	}

	/**
	 * Replace the author of a single review with the review author name
	 * @since 3.0.0
	 */
	public function alter_review_author( $author ) {
		global $post;

		if ( $post->post_type != 'urp_review' ) { return $author; }

		$author = get_post_meta($post->ID, 'EWD_URP_Post_Author', true);

		return $author;
	}

	/**
	 * Output any Review schema data, if enabled
	 *
	 * @since  3.1.4
	 */
	public function output_ld_json_content() {
		global $ewd_urp_controller;

		if ( empty( $this->schema_review_data ) ) { return; }

		if ( empty( $ewd_urp_controller->settings->get_setting( 'display-microdata' ) ) ) { return; }
		
		$ld_json_ouptut = apply_filters( 'ewd_ufaq_ld_json_output', $this->schema_review_data );

		echo '<script type="application/ld+json" class="ewd-urp-ld-json-data">';
		echo wp_json_encode( $ld_json_ouptut );
		echo '</script>';
	}

	/**
	 * Adds in a menu bar for the plugin
	 * @since 3.0.0
	 */
	public function display_header_area() {

		$screen = get_current_screen();
		
		if ( empty( $screen->parent_file ) or $screen->parent_file != 'edit.php?post_type=urp_review' ) { return; }
		
		if ( ! $this->permissions->check_permission( 'styling' ) or get_option( 'EWD_URP_Trial_Happening' ) == 'Yes' ) {
			?>
			<div class="ewd-urp-dashboard-new-upgrade-banner">
				<div class="ewd-urp-dashboard-banner-icon"></div>
				<div class="ewd-urp-dashboard-banner-buttons">
					<a class="ewd-urp-dashboard-new-upgrade-button" href="https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=urp_admin&utm_content=banner" target="_blank">UPGRADE NOW</a>
				</div>
				<div class="ewd-urp-dashboard-banner-text">
					<div class="ewd-urp-dashboard-banner-title">
						GET FULL ACCESS WITH OUR PREMIUM VERSION
					</div>
					<div class="ewd-urp-dashboard-banner-brief">
						In-depth reviews, WooCommerce integration, admin approval of reviews, change review format and more!
					</div>
				</div>
			</div>
			<?php
		}
		
		?>
		<div class="ewd-urp-admin-header-menu">
			<h2 class="nav-tab-wrapper">
			<a id="ewd-urp-dash-mobile-menu-open" href="#" class="menu-tab nav-tab"><?php _e("MENU", 'ultimate-reviews'); ?><span id="ewd-urp-dash-mobile-menu-down-caret">&nbsp;&nbsp;&#9660;</span><span id="ewd-urp-dash-mobile-menu-up-caret">&nbsp;&nbsp;&#9650;</span></a>
			<a id="dashboard-menu" href='admin.php?page=ewd-urp-dashboard' class="menu-tab nav-tab <?php if ( $screen->id == 'urp_review_ewd-urp-dashboard' ) {echo 'nav-tab-active';}?>"><?php _e("Dashboard", 'ultimate-reviews'); ?></a>
			<?php if ( $this->settings->get_setting( 'admin-approval' ) ) { ?>
				<a id="approved-reviews-menu" href='edit.php?post_type=urp_review&post_status=publish' class="menu-tab nav-tab <?php if ( $screen->id == 'edit-urp_review' and ( ! isset( $_GET['post_status'] ) or $_GET['post_status'] == 'publish' ) ) {echo 'nav-tab-active';}?>"><?php _e("Approved Reviews", 'ultimate-reviews'); ?></a>
				<a id="awaiting-approval-reviews-menu" href='edit.php?post_type=urp_review&post_status=draft' class="menu-tab nav-tab <?php if ( $screen->id == 'edit-urp_review' and ( isset( $_GET['post_status'] ) and $_GET['post_status'] == 'draft' ) ) {echo 'nav-tab-active';}?>"><?php _e("Awaiting Approval", 'ultimate-reviews'); ?></a>
			<?php } else { ?>
				<a id="reviews-menu" href='edit.php?post_type=urp_review' class="menu-tab nav-tab <?php if ( $screen->id == 'edit-urp_review' ) {echo 'nav-tab-active';}?>"><?php _e("Reviews", 'ultimate-reviews'); ?></a>
			<?php } ?>
			<a id="categories-menu" href='edit-tags.php?taxonomy=urp-review-category&post_type=urp_review' class="menu-tab nav-tab <?php if ( $screen->id == 'edit-urp-review-category' ) {echo 'nav-tab-active';}?>"><?php _e("Categories", 'ultimate-reviews'); ?></a>
			<a id="import-menu" href='edit.php?post_type=urp_review&page=ewd-urp-import' class="menu-tab nav-tab <?php if ( $screen->id == 'urp_review_page_ewd-urp-import' ) {echo 'nav-tab-active';}?>"><?php _e("Import", 'ultimate-reviews'); ?></a>
			<a id="export-menu" href='edit.php?post_type=urp_review&page=ewd-urp-export' class="menu-tab nav-tab <?php if ( $screen->id == 'urp_review_page_ewd-urp-export' ) {echo 'nav-tab-active';}?>"><?php _e("Export", 'ultimate-reviews'); ?></a>
			<a id="options-menu" href='edit.php?post_type=urp_review&page=ewd-urp-settings' class="menu-tab nav-tab <?php if ( $screen->id == 'urp_review_page_ewd-urp-settings' ) {echo 'nav-tab-active';}?>"><?php _e("Settings", 'ultimate-reviews'); ?></a>
			</h2>
		</div>
		<?php
	}

	public function maybe_display_helper_notice() {

		if ( empty( $this->permissions->check_permission( 'premium' ) ) ) { return; }

		if ( is_plugin_active( 'ewd-premium-helper/ewd-premium-helper.php' ) ) { return; }

		if ( get_transient( 'ewd-helper-notice-dismissed' ) ) { return; }

		?>

		<div class='notice notice-error is-dismissible ewd-urp-helper-install-notice'>
			
			<div class='ewd-urp-helper-install-notice-img'>
				<img src='<?php echo EWD_URP_PLUGIN_URL . '/lib/simple-admin-pages/img/options-asset-exclamation.png' ; ?>' />
			</div>

			<div class='ewd-urp-helper-install-notice-txt'>
				<?php _e( 'You\'re using the Ultimate Reviews premium version, but the premium helper plugin is not active.', 'ultimate-reviews' ); ?>
				<br />
				<?php echo sprintf( __( 'Please re-activate the helper plugin, or <a target=\'_blank\' href=\'%s\'>download and install it</a> if the plugin is no longer installed to ensure continued access to the premium features of the plugin.', 'ultimate-reviews' ), 'https://www.etoilewebdesign.com/2021/12/11/requiring-premium-helper-plugin/' ); ?>
			</div>

			<div class='ewd-urp-clear'></div>

		</div>

		<?php 
	}

	public function hide_helper_notice() {

		// Authenticate request
		if ( ! check_ajax_referer( 'ewd-urp-helper-notice', 'nonce' ) or ! current_user_can( 'manage_options' ) ) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		set_transient( 'ewd-helper-notice-dismissed', true, 3600*24*7 );

		die();
	}

	/**
	 * Declares compatibility with WooCommerce High-Performance Order Storage
	 * @since 3.2.8
	 */
	public function declare_wc_hpos() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}
} // endif;

global $ewd_urp_controller;
$ewd_urp_controller = new ewdurpInit();

do_action( 'ewd_urp_initialized' );