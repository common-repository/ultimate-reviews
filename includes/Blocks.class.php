<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdurpBlocks' ) ) {
/**
 * Class to handle plugin Gutenberg blocks
 *
 * @since 3.0.0
 */
class ewdurpBlocks {

	public function __construct() {

		add_action( 'init', array( $this, 'add_review_blocks' ) );
		
		add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );
	}

	/**
	 * Add the Gutenberg block to the list of available blocks
	 * @since 3.0.0
	 */
	public function add_review_blocks() {

		if ( ! function_exists( 'render_block_core_block' ) ) { return; }

		$this->enqueue_assets();   

		$args = array(
			'attributes' => array(
				'post_count' => array(
					'type' => 'string',
				),
				'include_category' => array(
					'type' => 'string',
				),
				'exclude_category' => array(
					'type' => 'string',
				),
				'product_name' => array(
					'type' => 'string',
				),
				'include_ids' => array(
					'type' => 'string',
				),
				'exclude_ids' => array(
					'type' => 'string',
				),
			),
			'render_callback' 	=> 'ewd_urp_reviews_shortcode',
		);

		register_block_type( 'ultimate-reviews/ewd-urp-display-reviews-block', $args );

		$args = array(
			'attributes' => array(
				'product_name' => array(
					'type' => 'string',
				),
				'redirect_page' => array(
					'type' => 'string',
				),
			),
			'render_callback' 	=> 'ewd_urp_submit_review_shortcode',
		);

		register_block_type( 'ultimate-reviews/ewd-urp-submit-review-block', $args );

		add_action( 'current_screen', array( $this, 'localize_data' ) );

	}

	/**
	 * Conditionally enqueue assets and perform any necessary data localization for use in block parameters
	 * @since 3.1.4
	 */
	public function localize_data() {

		$screen = get_current_screen();

		if ( ! $screen->is_block_editor and $screen->id != 'widgets' ) { return; }

		wp_enqueue_style( 'ewd-urp-css' );
		wp_enqueue_style( 'ewd-urp-blocks-css' );
		wp_enqueue_script( 'ewd-urp-blocks-js' );
	}

	/**
	 * Create a new category of blocks to hold our block
	 * @since 3.0.0
	 */
	public function add_block_category( $categories ) {
		
		$categories[] = array(
			'slug'  => 'ewd-urp-blocks',
			'title' => __( 'Ultimate Reviews', 'ultimate-reviews' ),
		);

		return $categories;
	}

	/**
	 * Register the necessary JS and CSS to display the block in the editor
	 * @since 3.0.0
	 */
	public function enqueue_assets() {

		wp_register_style( 'ewd-urp-css', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp.css', EWD_URP_VERSION );
		wp_register_script( 'ewd-urp-blocks-js', EWD_URP_PLUGIN_URL . '/assets/js/ewd-urp-blocks.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ), EWD_URP_VERSION );
		wp_register_style( 'ewd-urp-blocks-css', EWD_URP_PLUGIN_URL . '/assets/css/ewd-urp-blocks.css', array( 'wp-edit-blocks' ), EWD_URP_VERSION );
	}
}
}