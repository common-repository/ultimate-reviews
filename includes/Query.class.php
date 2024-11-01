<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ewdurpQuery' ) ) {
/**
 * Class to handle common queries used to pull reviews from
 * the database.
 *
 * Bookings can be retrieved with specific date ranges, common
 * date params (today/upcoming), etc. This class is intended for
 * the base plugin as well as extensions or custom projects which
 * need a stable mechanism for reliably retrieving reviews data.
 *
 * Queries return an array of ewdurpReview objects.
 *
 * @since 2.0.0
 */
class ewdurpQuery {

	/**
	 * Bookings
	 *
	 * Array of reviews retrieved after get_reviews() is called
	 *
	 * @since 2.0.0
	 */
	public $reviews = array();

	/**
	 * Query args
	 *
	 * Passed to WP_Query
	 * http://codex.wordpress.org/Class_Reference/WP_Query
	 *
	 * @since 2.0.0
	 */
	public $args = array();

	/**
	 * Query context
	 *
	 * Defines the context in which the query is run.
	 * Useful for hooking into the right query without
	 * tampering with others.
	 *
	 * @since 2.0.0
	 */
	public $context;

	// The total number of reviews found matching the current query parameters
	public $found_reviews = array();

	// Current page of reviews being retrieved
	public $current_page;

	// Max number of pages of reviews matching the query
	public $max_page;
	
	/**
	 * Instantiate the query with an array of arguments
	 *
	 * This supports all WP_Query args as well as several
	 * short-hand arguments for common needs. Short-hands
	 * include:
	 *
	 * date_range string today|upcoming|dates
	 * start_date string don't get reviews before this
	 * end_date string don't get reviews after this
	 *
	 * @see ewdurpQuery::prepare_args()
	 * @param args array Options to tailor the query
	 * @param context string Context for the query, used
	 *		in filters
	 * @since 2.0.0
	 */
	public function __construct( $args = array(), $context = '' ) {

		global $ewd_urp_controller;

		$defaults = array(
			'post_type' => EWD_URP_REVIEW_POST_TYPE,
			'orderby'   => 'rating',
			'order' 		=> 'DESC',
			'paged' 		=> 1,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->context = $context;

	}

	/**
	 * Parse the args array and convert custom arguments
	 * for use by WP_Query
	 *
	 * @since 2.0.0
	 */
	public function prepare_args() {
		global $ewd_urp_controller;

		$args = $this->args;

		if ( ! empty( $args['search_string'] ) ) { $args['s'] = $args['search_string']; }

		if ( ! empty( $args['post_count'] ) ) { $args['posts_per_page'] = $args['post_count']; }
		
		if ( ! empty( $args['current_page'] ) ) { $args['paged'] = $args['current_page']; }

		if ( ! empty( $args['include_ids'] ) ) { $args['post__in'] = explode( ',', $args['include_ids'] ); }

		if ( ! empty( $args['exclude_ids'] ) ) { $args['post__not_in'] = explode( ',', $args['exclude_ids'] ); }

		if ( ! empty( $args['include_category'] ) or ! empty( $args['exclude_category'] ) or ! empty( $args['include_category_ids'] ) or ! empty( $args['exclude_category_ids'] ) ) {

			$tax_query = array();

			if ( ! empty( $args['include_category'] ) ) {

				$include_category_array = explode( ',', $args['include_category'] );
				
				$tax_query[] = array( 
					'taxonomy' 	=> EWD_URP_REVIEW_CATEGORY_TAXONOMY,
					'field' 	=> 'slug',
					'terms' 	=> $include_category_array
				);
			}

			if ( ! empty( $args['exclude_category'] ) ) {

				$exclude_category_array = explode( ',', $args['exclude_category'] );
				
				$tax_query[] = array( 
					'taxonomy' 	=> EWD_URP_REVIEW_CATEGORY_TAXONOMY,
					'field' 	=> 'slug',
					'operator'	=> 'NOT IN',
					'terms' 	=> $exclude_category_array
				);
			}

			if ( ! empty( $args['include_category_ids'] ) ) {

				$include_category_ids_array = explode( ',', $args['include_category_ids'] );
				
				$tax_query[] = array( 
					'taxonomy' 	=> EWD_URP_REVIEW_CATEGORY_TAXONOMY,
					'field' 	=> 'term_id',
					'terms' 	=> $include_category_ids_array
				);
			}

			if ( ! empty( $args['exclude_category_ids'] ) ) {

				$exclude_category_ids_array = explode( ',', $args['exclude_category_ids'] );
				
				$tax_query[] = array( 
					'taxonomy' 	=> EWD_URP_REVIEW_CATEGORY_TAXONOMY,
					'field' 	=> 'term_id',
					'operator'	=> 'NOT IN',
					'terms' 	=> $exclude_category_ids_array
				);
			}

			$args['tax_query'] = $tax_query; 
		}

		$meta_query = array();

		if ( ! empty( $args['product_name'] ) ) { 

			$meta_query[] = array(
				'key' => 'EWD_URP_Product_Name',
				'value' => $args['product_name'],
				'compare' => '=',
			); 
		}

		if ( ! empty( $args['review_author'] ) ) { 

			$meta_query[] = array(
				'key' => 'EWD_URP_Post_Author',
				'value' => $args['review_author'],
				'compare' => '=',
			); 
		}

		if ( ! empty( $args['email_confirmed'] ) ) { 

			$meta_query[] = array(
				'key' => 'EWD_URP_Email_Confirmed',
				'value' => 'Yes',
				'compare' => '=',
			); 
		}

		if ( ! empty( $args['custom_filters'] ) ) { 

			$custom_filters = json_decode( $args['custom_filters'], true );

			$custom_filters = is_array( $custom_filters ) ? array_map( 'sanitize_text_field', $custom_filters ) : array();

			foreach ( $custom_filters as $field_name => $value ) {
			
				$meta_query[] = array(
					'key' => 'EWD_URP_' . $field_name,
					'value' => $value,
					'compare' => '=',
				); 
			}
		}

		if ( ! empty( $args['min_score'] ) ) { 

			$meta_query[] = array(
				'key' => 'EWD_URP_Overall_Score',
				'value' => $args['min_score'],
				'compare' => '>=',
			); 
		}

		if ( ! empty( $args['max_score'] ) and $args['max_score'] < 1000000 ) { 

			$meta_query[] = array(
				'key' => 'EWD_URP_Overall_Score',
				'value' => $args['max_score'],
				'compare' => '<=',
			); 
		}

		if ( ! empty( $meta_query ) ) { $args['meta_query'] = $meta_query; }

		$orderby = array();

		if ( empty( $args['product_name'] ) and $ewd_urp_controller->settings->get_setting( 'group-by-product' ) ) {

			$orderby['meta_value'] = $ewd_urp_controller->settings->get_setting( 'group-by-product-order' );
		}

		if ( ! empty( $args['orderby'] ) ) {

			if ( $args['orderby'] == 'rating' or $args['orderby'] == 'karma' ) { $orderby['meta_value_num'] = $args['order']; }
			elseif ( $args['orderby'] == 'date' ) { $orderby['date'] = $args['order']; }
			elseif ( $orderby == 'title' ) { $orderby['title'] = $args['order']; }
		}

		if ( empty( $args['product_name'] ) and  $ewd_urp_controller->settings->get_setting( 'group-by-product' ) ) { $args['meta_key'] = 'EWD_URP_Product_Name'; }
		elseif ( $args['orderby'] == 'rating' ) { $args['meta_key'] = 'EWD_URP_Overall_Score'; }
		elseif ( $args['orderby'] == 'karma' ) { $args['meta_key'] = 'EWD_URP_Review_Karma'; }

		if ( ! empty( $orderby ) ) { $args['orderby'] = $orderby; }

		$this->args = $args;

		return $this->args;
	}

	/**
	 * Parse $_REQUEST args and store in $this->args
	 *
	 * @since 2.0.0
	 */
	public function parse_request_args() {

		$args = array();

		if ( isset( $_REQUEST['current_page'] ) ) { 

			$args['current_page'] = intval( $_REQUEST['current_page'] ); 
		}

		if ( isset( $_REQUEST['review_score'] ) ) { 

			$args['min_score'] = intval( $_REQUEST['review_score'] ); 
			$args['max_score'] = intval( $_REQUEST['review_score'] );
		}

		if ( isset( $_REQUEST['review_min_score'] ) ) { 

			$args['min_score'] = intval( $_REQUEST['review_min_score'] ); 
		}

		if ( isset( $_REQUEST['review_max_score'] ) ) { 

			$args['max_score'] = intval( $_REQUEST['review_max_score'] );
		}

		if ( isset( $_REQUEST['review_author'] ) ) { 

			$args['review_author'] = sanitize_text_field( urldecode( $_REQUEST['review_author'] ) );
		}

		if ( isset( $_REQUEST['product_name'] ) ) { 

			$args['product_name'] = sanitize_text_field( urldecode( $_REQUEST['product_name'] ) );
		}

		if ( isset( $_REQUEST['custom_filters'] ) ) { 

			$args['custom_filters'] = sanitize_text_field( $_REQUEST['custom_filters'] ); 
		}

		if ( isset( $_REQUEST['search_string'] ) ) { 

			$args['search_string'] = sanitize_text_field( urldecode( $_REQUEST['search_string'] ) ); 
		}

		if ( isset( $_REQUEST['post_count'] ) ) { 

			$args['post_count'] = sanitize_text_field( $_REQUEST['post_count'] ); 
		}

		if ( isset( $_REQUEST['orderby'] ) ) { 

			$args['orderby'] = sanitize_text_field( $_REQUEST['orderby'] ); 
		}

		if ( isset( $_REQUEST['order'] ) ) { 

			$args['order'] = sanitize_text_field( $_REQUEST['order'] ); 
		}

		$this->args = array_merge( $this->args, $args ); 
	}

	/**
	 * Retrieve query results
	 *
	 * @since 3.0.0
	 */
	public function get_reviews() {

		$reviews = array();

		$args = apply_filters( 'ewd_urp_query_args', $this->args, $this->context ); 
		
		$query = new WP_Query( $args );
		
		if ( $query->have_posts() ) {

			while( $query->have_posts() ) {
				$query->the_post();

				$review = new ewdurpReview();
				if ( $review->load_post( $query->post ) ) {
					$reviews[] = $review;
				}
			}
		}

		$this->found_reviews = $query->found_posts;

		$this->current_page = $this->args['paged'];
		
		$this->max_page = $query->max_num_pages;
		
		$this->reviews = $reviews;

		wp_reset_query();

		return $this->reviews;
	}
}
} // endif
