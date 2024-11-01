<?php

/**
 * Class to display only summary statistics for a set of reviews on the front end.
 *
 * @since 3.0.0
 */
class ewdurpViewSummaryStatistics extends ewdurpViewReviews {

	// Shortcode attributes
	public $product_name;
	public $include_category;
	public $exclude_category;
	public $all_products;
	public $summary_type;
	public $show_reviews;

	/**
	 * Render the view and enqueue required stylesheets
	 * @since 3.0.0
	 */
	public function render() {
		global $ewd_urp_controller;

		// Set attribute-alterable options
		$this->set_reviews_options();

		// Set parameter-alterable options
		$this->set_request_parameters();

		if ( ! count( $this->reviews ) ) {
			return;
		}

		// Get data on product names, score, etc.
		$this->create_reviews_data();

		// Add any dependent stylesheets or javascript
		$this->enqueue_assets();

		// Add css classes to the slider
		$this->classes = $this->get_classes();

		ob_start();

		$this->add_custom_styling();

		if ( $this->show_reviews ) {

			$this->add_schema_data();
			
			$template = $this->find_template( 'reviews' );
			if ( $template ) {
				include( $template );
			}
			$template = $this->find_template( 'summary-statistics-return' );
			if ( $template ) {
				include( $template );
			}
		}
		else {
			
			$template = $this->find_template( 'summary-statistics' );
			if ( $template ) {
				include( $template );
			}
		}

		$output = ob_get_clean();

		return apply_filters( 'ewd_urp_review_summary_output', $output, $this );
	}
}
