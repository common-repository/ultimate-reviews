<?php

class ewdurpWidgetManager {

	public function __construct() {

		add_action( 'widgets_init', array( $this, 'register_recent_reviews_widget' ) );
		add_action( 'widgets_init', array( $this, 'register_selected_reviews_widget' ) );
		add_action( 'widgets_init', array( $this, 'register_popular_reviews_widget' ) );
		add_action( 'widgets_init', array( $this, 'register_reviews_slider_widget' ) );
	}

	public function register_recent_reviews_widget() {

		return register_widget( 'ewdurpRecentReviewsWidget' );
	}

	public function register_selected_reviews_widget() {

		return register_widget( 'ewdurpSelectedReviewsWidget' );
	}

	public function register_popular_reviews_widget() {

		return register_widget( 'ewdurpPopularReviewsWidget' );
	}

	public function register_reviews_slider_widget() {

		return register_widget( 'ewdurpReviewsSliderWidget' );
	}

}

class ewdurpRecentReviewsWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		parent::__construct(
			'ewd_urp_recent_reviews_widget', // Base ID
			__( 'Recent Reviews', 'ultimate-reviews' ), // Name
			array( 'description' => __( 'Insert a number of recent reviews.', 'ultimate-reviews' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$params = array(
			'posts_per_page' => $instance['post_count'],
			'post_type' => EWD_URP_REVIEW_POST_TYPE,
			'orderby' => 'date',
		);

		if ( $instance['product_name'] != 'All' and $instance['product_name'] != '' ) {
			
			$params['meta_query'] = array(
				array(
					'key' => 'EWD_URP_Product_Name',
					'value' => $instance['product_name']
				)
			);
		}

		$query = new WP_Query( $params );
		
		$post_ids = '';
		$posts = $query->posts;
		if ( is_array( $posts ) ) { foreach ( $posts as $post ) { $post_ids .= $post->ID . ','; } }
		$post_ids = trim( $post_ids, ',' );

		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $instance['before_text'] );
		echo do_shortcode( "[select-review review_id='". sanitize_text_field( $post_ids ) . "']" );
		echo wp_kses_post( $instance['after_text'] );
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$product_names = ewd_urp_get_product_names();

		$before_text = ! empty( $instance['before_text'] ) ? $instance['before_text'] : __( '', 'ultimate-reviews' );
		$after_text = ! empty( $instance['after_text'] ) ? $instance['after_text'] : __( '', 'ultimate-reviews' );
		$product_name = ! empty( $instance['product_name'] ) ? $instance['product_name'] : __( 'All', 'ultimate-reviews' );
		$post_count = ! empty( $instance['post_count'] ) ? $instance['post_count'] : 3;

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>"><?php _e( 'Text Before:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_text' ) ); ?>" type="text" value="<?php echo esc_attr( $before_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_name' ) ); ?>"><?php _e( 'Reviewed Product:', 'EWD_URP' ); ?></label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_name' ) ); ?>">
				<option value='All' <?php echo ($product_name == 'All' ? 'selected' : ''); ?>><?php _e("All Products", 'EWD_URP'); ?></option>
				<?php if (is_array($Product_Names)) {foreach ($Product_Names as $Product_Name) { ?>
					<option value='<?php echo esc_attr( $Product_Name ); ?>' <?php echo ($product_name == $Product_Name ? 'selected' : ''); ?>><?php echo esc_attr( $Product_Name ); ?></option>
				<?php }} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>"><?php _e( 'Number of Reviews:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_count' ) ); ?>" type="text" value="<?php echo esc_attr( $post_count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>"><?php _e( 'Text After:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_text' ) ); ?>" type="text" value="<?php echo esc_attr( $after_text ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['before_text'] = ( ! empty( $new_instance['before_text'] ) ) ? strip_tags( $new_instance['before_text'] ) : '';
		$instance['after_text'] = ( ! empty( $new_instance['after_text'] ) ) ? strip_tags( $new_instance['after_text'] ) : '';
		$instance['product_name'] = ( ! empty( $new_instance['product_name'] ) ) ? strip_tags( $new_instance['product_name'] ) : '';
		$instance['post_count'] = ( ! empty( $new_instance['post_count'] ) ) ? strip_tags( $new_instance['post_count'] ) : '';

		return $instance;
	}
}

class ewdurpSelectedReviewsWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		parent::__construct(
			'ewd_urp_selected_reviews_widget', // Base ID
			__( 'Selected Reviews', 'ultimate-reviews' ), // Name
			array( 'description' => __( 'Insert a number of selected reviews.', 'ultimate-reviews' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $instance['before_text'] );
		echo do_shortcode( "[select-review review_id='". sanitize_text_field( $instance['post_ids'] ) . "']" );
		echo wp_kses_post( $instance['after_text'] );
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$product_names = ewd_urp_get_product_names();

		$before_text = ! empty( $instance['before_text'] ) ? $instance['before_text'] : __( '', 'ultimate-reviews' );
		$after_text = ! empty( $instance['after_text'] ) ? $instance['after_text'] : __( '', 'ultimate-reviews' );
		$post_ids = ! empty( $instance['post_ids'] ) ? $instance['post_ids'] : __( '', 'ultimate-reviews' );

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>"><?php _e( 'Text Before:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_text' ) ); ?>" type="text" value="<?php echo esc_attr( $before_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>"><?php _e( 'IDs of Posts to Display', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_ids' ) ); ?>" type="text" value="<?php echo esc_attr( $post_ids ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>"><?php _e( 'Text After:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_text' ) ); ?>" type="text" value="<?php echo esc_attr( $after_text ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['before_text'] = ( ! empty( $new_instance['before_text'] ) ) ? strip_tags( $new_instance['before_text'] ) : '';
		$instance['after_text'] = ( ! empty( $new_instance['after_text'] ) ) ? strip_tags( $new_instance['after_text'] ) : '';
		$instance['post_ids'] = ( ! empty( $new_instance['post_ids'] ) ) ? strip_tags( $new_instance['post_ids'] ) : '';

		return $instance;
	}
}

class ewdurpPopularReviewsWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		parent::__construct(
			'ewd_urp_popular_reviews_widget', // Base ID
			__( 'Popular Reviews', 'ultimate-reviews' ), // Name
			array( 'description' => __( 'Insert a number of popular reviews.', 'ultimate-reviews' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$params = array(
			'posts_per_page' => $instance['post_count'],
			'post_type' => EWD_URP_REVIEW_POST_TYPE,
			'orderby' => 'meta_value_num',
			'meta_key' => 'urp_view_count'
		);

		if ( $instance['product_name'] != 'All' and $instance['product_name'] != '' ) {
			
			$params['meta_query'] = array(
				array(
					'key' => 'EWD_URP_Product_Name',
					'value' => $instance['product_name']
				)
			);
		}

		$query = new WP_Query( $params );
		
		$post_ids = '';
		$posts = $query->posts;
		if ( is_array( $posts ) ) { foreach ( $posts as $post ) { $post_ids .= $post->ID . ','; } }
		$post_ids = trim( $post_ids, ',' );

		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $instance['before_text'] );
		echo do_shortcode( "[select-review review_id='". $post_ids . "']" );
		echo wp_kses_post( $instance['after_text'] );
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$product_names = ewd_urp_get_product_names();

		$before_text = ! empty( $instance['before_text'] ) ? $instance['before_text'] : __( '', 'ultimate-reviews' );
		$after_text = ! empty( $instance['after_text'] ) ? $instance['after_text'] : __( '', 'ultimate-reviews' );
		$product_name = ! empty( $instance['product_name'] ) ? $instance['product_name'] : __( 'All', 'ultimate-reviews' );
		$post_count = ! empty( $instance['post_count'] ) ? $instance['post_count'] : 3;

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>"><?php _e( 'Text Before:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_text' ) ); ?>" type="text" value="<?php echo esc_attr( $before_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_name' ) ); ?>"><?php _e( 'Reviewed Product:', 'EWD_URP' ); ?></label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_name' ) ); ?>">
				<option value='All' <?php echo ($product_name == 'All' ? 'selected' : ''); ?>><?php _e("All Products", 'EWD_URP'); ?></option>
				<?php if (is_array($Product_Names)) {foreach ($Product_Names as $Product_Name) { ?>
					<option value='<?php echo esc_attr( $Product_Name ); ?>' <?php echo ($product_name == $Product_Name ? 'selected' : ''); ?>><?php echo esc_attr( $Product_Name ); ?></option>
				<?php }} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>"><?php _e( 'Number of Reviews:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_count' ) ); ?>" type="text" value="<?php echo esc_attr( $post_count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>"><?php _e( 'Text After:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_text' ) ); ?>" type="text" value="<?php echo esc_attr( $after_text ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['before_text'] = ( ! empty( $new_instance['before_text'] ) ) ? strip_tags( $new_instance['before_text'] ) : '';
		$instance['after_text'] = ( ! empty( $new_instance['after_text'] ) ) ? strip_tags( $new_instance['after_text'] ) : '';
		$instance['product_name'] = ( ! empty( $new_instance['product_name'] ) ) ? strip_tags( $new_instance['product_name'] ) : '';
		$instance['post_count'] = ( ! empty( $new_instance['post_count'] ) ) ? strip_tags( $new_instance['post_count'] ) : '';

		return $instance;
	}
}

class ewdurpReviewsSliderWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		parent::__construct(
			'ewd_urp_reviews_slider_widget', // Base ID
			__( 'Reviews Slider', 'ultimate-reviews' ), // Name
			array( 'description' => __( 'Insert a slider of reviews (requires \'Ultimate Slider\' plugin installed)', 'ultimate-reviews' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $instance['before_text'] );
		echo do_shortcode( "[ultimate-slider slider_type='urp' post__in_string='". sanitize_text_field( $post_ids ) . "']" );
		echo wp_kses_post( $instance['after_text'] );
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$product_names = ewd_urp_get_product_names();

		$before_text = ! empty( $instance['before_text'] ) ? $instance['before_text'] : __( '', 'ultimate-reviews' );
		$after_text = ! empty( $instance['after_text'] ) ? $instance['after_text'] : __( '', 'ultimate-reviews' );
		$post_ids = ! empty( $instance['post_ids'] ) ? $instance['post_ids'] : __( '', 'ultimate-reviews' );

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>"><?php _e( 'Text Before:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_text' ) ); ?>" type="text" value="<?php echo esc_attr( $before_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>"><?php _e( 'IDs of Posts to Display', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_ids' ) ); ?>" type="text" value="<?php echo esc_attr( $post_ids ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>"><?php _e( 'Text After:', 'EWD_URP' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_text' ) ); ?>" type="text" value="<?php echo esc_attr( $after_text ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['before_text'] = ( ! empty( $new_instance['before_text'] ) ) ? strip_tags( $new_instance['before_text'] ) : '';
		$instance['after_text'] = ( ! empty( $new_instance['after_text'] ) ) ? strip_tags( $new_instance['after_text'] ) : '';
		$instance['post_ids'] = ( ! empty( $new_instance['post_ids'] ) ) ? strip_tags( $new_instance['post_ids'] ) : '';

		return $instance;
	}
}