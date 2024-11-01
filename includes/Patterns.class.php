<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdurpPatterns' ) ) {
/**
 * Class to handle plugin Gutenberg blocks
 *
 * @since 3.1.4
 */
class ewdurpPatterns {

	/**
	 * Add hooks
	 * @since 3.1.4
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'ewd_urp_add_pattern_category' ) );
		add_action( 'init', array( $this, 'ewd_urp_add_patterns' ) );
	}

	/**
	 * Register block patterns
	 * @since 3.1.4
	 */
	public function ewd_urp_add_patterns() {

		$block_patterns = array(
			'reviews',
			'featured-reviews',
			'featured-reviews-two',
			'featured-reviews-three',
			'featured-reviews-four',
		);
	
		foreach ( $block_patterns as $block_pattern ) {
			$pattern_file = EWD_URP_PLUGIN_DIR . '/includes/patterns/' . $block_pattern . '.php';
	
			register_block_pattern(
				'ultimate-reviews/' . $block_pattern,
				require $pattern_file
			);
		}
	}

	/**
	 * Create a new category of block patterns to hold our pattern(s)
	 * @since 3.1.4
	 */
	public function ewd_urp_add_pattern_category() {
		
		register_block_pattern_category(
			'ewd-urp-block-patterns',
			array(
				'label' => __( 'Ultimate Reviews', 'ultimate-reviews' )
			)
		);
	}
}
}