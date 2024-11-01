<?php

/**
 * Class to display reviews with the search form on the front end.
 *
 * @since 3.0.0
 */
class ewdurpViewReviewSearch extends ewdurpViewReviews {

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

		ewd_add_frontend_ajax_url();

		$this->add_custom_styling();

		$this->add_schema_data();

		$template = $this->find_template( 'review-search' );
		if ( $template ) {
			include( $template );
		}
		$template = $this->find_template( 'reviews' );
		if ( $template ) {
			include( $template );
		}

		$output = ob_get_clean();

		return apply_filters( 'ewd_urp_review_search_output', $output, $this );
	}
}
