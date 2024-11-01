<?php

/**
 * Base class
 *
 * @since 3.0.0
 */

// Load library classes
require_once( EWD_URP_PLUGIN_DIR . '/views/View.class.php' );
require_once( EWD_URP_PLUGIN_DIR . '/views/View.Review.class.php' );
require_once( EWD_URP_PLUGIN_DIR . '/views/View.Reviews.class.php' );
require_once( EWD_URP_PLUGIN_DIR . '/views/View.ReviewSearch.class.php' );
require_once( EWD_URP_PLUGIN_DIR . '/views/View.SubmitReview.class.php' );
require_once( EWD_URP_PLUGIN_DIR . '/views/View.SummaryStatistics.class.php' );

class ewdurpBase {

	public $id = null;

	// Collect errors during processing
	public $errors = array();


	/**
	 * Initialize the class
	 * @since 3.0.0
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );

	}

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables.
	 * @since 3.0.0
	 */
	public function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );

				default :
					$this->{$key} = $val;

			}
		}
	}

	/**
	 * Set an error
	 * @since 3.0.0
	 */
	public function set_error( $error ) {
		$this->errors[] = array_merge(
			$error,
			array(
				'class'		=> get_class( $this ),
				'id'		=> $this->id,
				'backtrace'	=> debug_backtrace()
			)
		);
	}

}
