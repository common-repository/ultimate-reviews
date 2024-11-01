<?php
/**
 * Class to handle all custom post type definitions for Ultimate Reviews
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'ewdurpCustomPostTypes' ) ) {
class ewdurpCustomPostTypes {

	public $nonce;
	
	public function __construct() {

		// Call when plugin is initialized on every page load
		add_action( 'admin_init', 		array( $this, 'create_nonce' ) );
		add_action( 'init', 			array( $this, 'load_cpts' ) );

		// Handle metaboxes
		add_action( 'add_meta_boxes', 	array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', 		array( $this, 'save_meta' ) );

		add_filter( 'get_sample_permalink_html', array( $this, 'display_review_shortcode' ), 10, 2 );

		// Add columns and filters to the admin list of reviews
		add_filter( 'manage_urp_review_posts_columns', 					array( $this, 'register_review_table_columns' ) );
		add_action( 'manage_urp_review_posts_custom_column', 			array( $this, 'display_review_columns_content' ), 10, 2 );
		add_filter( 'manage_edit-urp_review_sortable_columns', 			array( $this, 'register_post_column_sortables' ) );
		add_filter( 'request', 											array( $this, 'orderby_custom_columns' ) );
		add_filter( 'parse_query', 										array( $this, 'filter_by_product_name' ) );
		add_filter( 'restrict_manage_posts', 							array( $this, 'add_product_name_dropdown' ) );
	}

	/**
	 * Initialize custom post types
	 * @since 3.0.0
	 */
	public function load_cpts() {
		global $ewd_urp_controller;

		// Define the review custom post type
		$args = array(
			'labels' => array(
				'name' 					=> __( 'Reviews',           		'ultimate-reviews' ),
				'singular_name' 		=> __( 'Review',                   	'ultimate-reviews' ),
				'menu_name'         	=> __( 'Reviews',          			'ultimate-reviews' ),
				'name_admin_bar'    	=> __( 'Reviews',                  	'ultimate-reviews' ),
				'add_new'           	=> __( 'Add New',                 	'ultimate-reviews' ),
				'add_new_item' 			=> __( 'Add New Review',           	'ultimate-reviews' ),
				'edit_item'         	=> __( 'Edit Review',               'ultimate-reviews' ),
				'new_item'          	=> __( 'New Review',                'ultimate-reviews' ),
				'view_item'         	=> __( 'View Review',               'ultimate-reviews' ),
				'search_items'      	=> __( 'Search Reviews',           	'ultimate-reviews' ),
				'not_found'         	=> __( 'No reviews found',          'ultimate-reviews' ),
				'not_found_in_trash'	=> __( 'No reviews found in trash', 'ultimate-reviews' ),
				'all_items'         	=> __( 'All Reviews',              	'ultimate-reviews' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-star-filled',
			'rewrite' => array( 
				'slug' => 'review' 
			),
			'supports' => array(
				'title', 
				'editor', 
				'author',
				'excerpt',
				'comments',
				'thumbnail'
			),
			'show_in_rest' => true,
		);

		// Create filter so addons can modify the arguments
		$args = apply_filters( 'ewd_urp_reviews_args', $args );

		// Add an action so addons can hook in before the post type is registered
		do_action( 'ewd_urp_reviews_pre_register' );

		// Register the post type
		register_post_type( EWD_URP_REVIEW_POST_TYPE, $args );

		// Add an action so addons can hook in after the post type is registered
		do_action( 'ewd_urp_reviews_post_register' );


		// Define the review category taxonomy
		$args = array(
			'labels' => array(
				'name' 				=> __( 'Review Categories',			'ultimate-reviews' ),
				'singular_name' 	=> __( 'Review Category',			'ultimate-reviews' ),
				'search_items' 		=> __( 'Search Review Categories', 	'ultimate-reviews' ),
				'all_items' 		=> __( 'All Review Categories', 	'ultimate-reviews' ),
				'parent_item' 		=> __( 'Parent Review Category', 	'ultimate-reviews' ),
				'parent_item_colon' => __( 'Parent Review Category:', 	'ultimate-reviews' ),
				'edit_item' 		=> __( 'Edit Review Category', 		'ultimate-reviews' ),
				'update_item' 		=> __( 'Update Review Category', 	'ultimate-reviews' ),
				'add_new_item' 		=> __( 'Add New Review Category', 	'ultimate-reviews' ),
				'new_item_name' 	=> __( 'New Review Category Name', 	'ultimate-reviews' ),
				'menu_name' 		=> __( 'Review Categories', 		'ultimate-reviews' ),
            ),
			'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true
		);

		// Create filter so addons can modify the arguments
		$args = apply_filters( 'ewd_urp_category_args', $args );

		register_taxonomy( EWD_URP_REVIEW_CATEGORY_TAXONOMY, EWD_URP_REVIEW_POST_TYPE, $args );
	}

	/**
	 * Generate a nonce for secure saving of metadata
	 * @since 3.0.0
	 */
	public function create_nonce() {

		$this->nonce = wp_create_nonce( basename( __FILE__ ) );
	}

	/**
	 * Add in new columns for the urp_review type
	 * @since 3.0.0
	 */
	public function add_meta_boxes() {

		$meta_boxes = array(

			// Add in the review meta information
			'review_meta' => array (
				'id'		=>	'review_meta',
				'title'		=> esc_html__( 'Review Details', 'ultimate-reviews' ),
				'callback'	=> array( $this, 'show_review_meta' ),
				'post_type'	=> EWD_URP_REVIEW_POST_TYPE,
				'context'	=> 'normal',
				'priority'	=> 'high'
			),

			// Add in a link to the documentation for the plugin
			'us_meta_need_help' => array (
				'id'		=>	'ewd_urp_meta_need_help',
				'title'		=> esc_html__( 'Need Help?', 'ultimate-reviews' ),
				'callback'	=> array( $this, 'show_need_help_meta' ),
				'post_type'	=> EWD_URP_REVIEW_POST_TYPE,
				'context'	=> 'side',
				'priority'	=> 'high'
			),
		);

		// Create filter so addons can modify the metaboxes
		$meta_boxes = apply_filters( 'ewd_urp_meta_boxes', $meta_boxes );

		// Create the metaboxes
		foreach ( $meta_boxes as $meta_box ) {
			add_meta_box(
				$meta_box['id'],
				$meta_box['title'],
				$meta_box['callback'],
				$meta_box['post_type'],
				$meta_box['context'],
				$meta_box['priority']
			);
		}
	}

	/**
	 * Add in a link to the plugin documentation
	 * @since 3.0.0
	 */
	public function show_review_meta( $post ) { 
		global $ewd_urp_controller;

		$order_id = get_post_meta( $post->ID, 'EWD_URP_Order_ID', true );
		$review_karma = get_post_meta( $post->ID, 'EWD_URP_Review_Karma', true );
		$author_email = get_post_meta( $post->ID, 'EWD_URP_Post_Email', true );
		$email_confirmed = get_post_meta( $post->ID, 'EWD_URP_Email_Confirmed', true );
		$review_video = get_post_meta( $post->ID, 'EWD_URP_Review_Video', true );
		$product_name = get_post_meta( $post->ID, 'EWD_URP_Product_Name', true );
		$post_author = get_post_meta( $post->ID, 'EWD_URP_Post_Author', true );

		?>
	
		<input type="hidden" name="ewd_urp_nonce" value="<?php echo esc_attr( $this->nonce ); ?>">
	
		<?php if ( $order_id ) { ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='wc_order_id'>
					<?php _e( 'WooCommerce Order ID:', 'ultimate-reviews' ); ?>
				</label>
				<span><?php echo esc_html( $order_id ); ?></span>
			</div>

		<?php } ?>

		<?php if ( $ewd_urp_controller->settings->get_setting( 'review-karma' ) ) { ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='review_karma'>
					<?php _e( 'Review Karma:', 'ultimate-reviews' ); ?>
				</label>
				<input type='text' id='ewd-urp-review-karma' name='review_karma' value='<?php echo esc_attr( $review_karma ); ?>' size='25' />
			</div>

		<?php } ?>

		<?php if ( $ewd_urp_controller->settings->get_setting( 'require-email' ) ) { ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='author_email'>
					<?php _e( 'Reviewer\'s Email:', 'ultimate-reviews' ); ?>
				</label>
				<input type='text' id='ewd-urp-author-email' name='author_email' value='<?php echo esc_attr( $author_email ); ?>' size='25' />
			</div>

		<?php } ?>

		<?php if ( $ewd_urp_controller->settings->get_setting( 'email-confirmation' ) ) { ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='email_confirmed'>
					<?php _e( 'Email Confirmed:', 'ultimate-reviews' ); ?>
				</label>
				<input type="radio" id="ewd-urp-email-confirmed" name="email_confirmed" value='Yes' <?php if ( $email_confirmed == "Yes" ) {echo "checked=checked";} ?> />Yes &nbsp;&nbsp;&nbsp;
				<input type="radio" id="ewd-urp-email-confirmed" name="email_confirmed" value='No' <?php if ( $email_confirmed == "No" ) {echo "checked=checked";} ?> />No
			</div> 

		<?php } ?>

		<?php if ( $ewd_urp_controller->settings->get_setting( 'review-video' ) ) { ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='review_video'>
					<?php _e( 'Review Video:', 'ultimate-reviews' ); ?>
				</label>
				<input type='text' id='ewd-urp-review-video' name='review_video' value='<?php echo esc_attr( $review_video ); ?>' size='25' />
			</div>

		<?php } ?>

		<?php 
			if ( 
 				$ewd_urp_controller->settings->get_setting( 'review-weights' ) or
 				$ewd_urp_controller->settings->get_setting( 'review-karma' ) or
				$ewd_urp_controller->settings->get_setting( 'require-email' ) or
				$ewd_urp_controller->settings->get_setting( 'email-confirmation' ) or 
				$ewd_urp_controller->settings->get_setting( 'review-video' )  
			) { ?>
				
				<div class='ewd-urp-meta-separator'></div>
		<?php } ?>

		<div class='ewd-urp-meta-field'>
			<label class='ewd-urp-meta-label' for='product_name'>
				<?php _e( 'Product Name:', 'ultimate-reviews' ); ?>
			</label>
			<input type='text' id='ewd-urp-product-name' name='product_name' value='<?php echo esc_attr( $product_name ); ?>' size='25' />
		</div>

		<div class='ewd-urp-meta-field'>
			<label class='ewd-urp-meta-label' for='post_author'>
				<?php _e( 'Post Author:', 'ultimate-reviews' ); ?>
			</label>
			<input type='text' id='ewd-urp-post-author' name='review_post_author' value='<?php echo esc_attr( $post_author ); ?>' size='25' />
		</div>

		<?php if ( ! $ewd_urp_controller->settings->get_setting( 'indepth-reviews' ) ) { ?>
			
			<?php $overall_score = get_post_meta( $post->ID, "EWD_URP_Overall_Score", true ); ?>

			<div class='ewd-urp-meta-field'>
				<label class='ewd-urp-meta-label' for='overall_score'>
					<?php _e( 'Overall Score:', 'ultimate-reviews' ); ?>
				</label>
				<input type='text' id='ewd-urp-overall-score' name='overall_score' value='<?php echo esc_attr( $overall_score ); ?>' size='25' />
			</div>

		<?php } else {

			$review_elements_array = $ewd_urp_controller->settings->get_review_elements();
			foreach ( $review_elements_array as $review_element ) {

				if ( $review_element->type == 'default' ) { continue; }

				$element_value = get_post_meta( $post->ID, "EWD_URP_" . $review_element->name, true );
				if ( $review_element->explanation ) { $explanation = get_post_meta( $post->ID, "EWD_URP_" . $review_element->name . "_Description", true ); }
				?>

				<div class='ewd-urp-meta-field'>
					<label class='ewd-urp-score-label' for='<?php echo esc_attr( $review_element->name ); ?>'>
						<?php echo esc_html( $review_element->name ) . ( ( $review_element->type == 'reviewitem' or empty( $review_element->type ) ) ? __( ' Score', 'ultimate-reviews' ) : '' ); ?>
					</label>
				

					<?php $options = explode( ',', $review_element->options ); ?>

					<?php if ( $review_element->type == 'dropdown' ) { ?>
						<?php if ( ! empty( $options ) ) { ?>
	
							<select name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>'>
								<?php foreach ( $options as $option ) { ?>
	
									<option value='<?php echo esc_attr( $option ); ?>' <?php echo ( $option == $element_value ? 'selected' : '' ); ?> >
										<?php echo esc_html( $option ); ?>
									</option>
								<?php } ?>
							</select>
	
						<?php } ?>
					<?php } elseif ( $review_element->type == 'checkbox' ) { ?>
						<?php if ( ! empty( $options ) ) { ?>
	
							<div class='ewd-urp-fields-page-radio-checkbox-container'>
								<?php foreach ( $options as $option ) { ?>
	
									<div class='ewd-urp-fields-page-radio-checkbox-each'>
										<input type='checkbox' name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>[]' value='<?php echo esc_attr( $option ); ?>' <?php echo ( in_array( $option, $element_value ) ? 'checked' : '' ); ?> />
										<?php echo esc_html( $option ); ?>
									</div>
								<?php } ?>
							</div>
	
						<?php } ?>
					<?php } elseif ( $review_element->type == 'radio' ) { ?>
						<?php if ( ! empty( $options ) ) { ?>
	
							<div class='ewd-urp-fields-page-radio-checkbox-container'>
								<?php foreach ( $options as $option ) { ?>
	
									<div class='ewd-urp-fields-page-radio-checkbox-each'>
										<input type='radio' name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>' value='<?php echo esc_attr( $option ); ?>' <?php echo ( $option == $element_value ? 'checked' : '' ); ?> />
										<?php echo esc_html( $option ); ?>
									</div>
								<?php } ?>
							</div>
	
						<?php } ?>
					<?php } elseif ( $review_element->type == 'date' ) { ?>
	
						<input type='text' class='ewd-urp-jquery-datepicker' id='ewd-urp-<?php echo esc_attr( $review_element->name ); ?>' name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>' value='<?php echo esc_attr( $element_value ); ?>' />
	
					<?php } elseif ( $review_element->type == 'DateTime' ) { ?>
	
						<input type='datetime-local' id='ewd-urp-<?php echo esc_attr( $review_element->name ); ?>' name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>' value='<?php echo esc_attr( $element_value ); ?>' />
	
					<?php } else { ?>
	
						<input type='text' id='ewd-urp-<?php echo esc_attr( $review_element->name ); ?>' name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>' value='<?php echo esc_attr( $element_value ); ?>' size='25' />
	
					<?php } ?>

				</div>

				<?php if ( $review_element->explanation ) { ?>

					<div class='ewd-urp-meta-field'>
						<label class='ewd-urp-explanation-label' for='<?php echo esc_attr( $review_element->name ); ?>_explanation'>
							<?php echo esc_html( $review_element->name ) . " " . __("Explanation:", 'ultimate-reviews'); ?>
						</label>
						<textarea name='EWD_URP_<?php echo esc_attr( $review_element->name ); ?>_explanation'><?php echo esc_html( $explanation ); ?></textarea>
					</div>

				<?php } ?>

			<?php } ?>
		<?php } ?>

	<?php } 

	/**
	 * Add in a link to the plugin documentation
	 * @since 3.0.0
	 */
	public function show_need_help_meta() { ?>
    
    	<div class='ewd-urp-need-help-box'>
    		<div class='ewd-urp-need-help-text'>Visit our Support Center for documentation and tutorials</div>
    	    <a class='ewd-urp-need-help-button' href='https://www.etoilewebdesign.com/support-center/?Plugin=URP' target='_blank'>GET SUPPORT</a>
    	</div>

	<?php }

	/**
	 * Save the metabox data for each review
	 * @since 3.0.0
	 */
	public function save_meta( $post_id ) {
		global $ewd_urp_controller;

		// Verify nonce
		if ( ! isset( $_POST['ewd_urp_nonce'] ) || ! wp_verify_nonce( $_POST['ewd_urp_nonce'], basename( __FILE__ ) ) ) {

			return $post_id;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

			return $post_id;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['review_karma'] ) ) 		 { update_post_meta( $post_id, 'EWD_URP_Review_Karma', sanitize_text_field( $_POST['review_karma'] ) ); }
		if ( isset( $_POST['author_email'] ) ) 		 { update_post_meta( $post_id, 'EWD_URP_Post_Email', sanitize_text_field( $_POST['author_email'] ) ); }
		if ( isset( $_POST['email_confirmed'] ) ) 	 { update_post_meta( $post_id, 'EWD_URP_Email_Confirmed', sanitize_text_field( $_POST['email_confirmed'] ) ); }
		if ( isset( $_POST['review_video'] ) ) 		 { update_post_meta( $post_id, 'EWD_URP_Review_Video', sanitize_text_field( $_POST['review_video'] ) ); }
		if ( isset( $_POST['product_name'] ) ) 		 { update_post_meta( $post_id, 'EWD_URP_Product_Name', sanitize_text_field( $_POST['product_name'] ) ); }
		if ( isset( $_POST['review_post_author'] ) ) { update_post_meta( $post_id, 'EWD_URP_Post_Author', sanitize_text_field( $_POST['review_post_author'] ) ); }

		if ( ! $ewd_urp_controller->settings->get_setting( 'indepth-reviews' ) ) {

			$overall_score = min( round( intval( $_POST['overall_score'] ), 2 ), $ewd_urp_controller->settings->get_setting( 'maximum-score' ) );
			update_post_meta( $post_id, 'EWD_URP_Overall_Score', $overall_score );
		} 
		else {

			$review_items = 0;
			foreach ( $ewd_urp_controller->settings->get_review_elements() as $review_element ) { 

				if ( empty( $review_element->name ) ) { continue; }

				$input_name = 'EWD_URP_' . str_replace( ' ', '_', $review_element->name );

				if ( $review_element->type == 'reviewitem' or empty( $review_element->type ) ) {

					$element_value = sanitize_text_field( $_POST[ $input_name ] );
					$category_score = min( intval( $element_value ), $ewd_urp_controller->settings->get_setting( 'maximum-score' ) );
					update_post_meta( $post_id, "EWD_URP_" . $review_element->name, $category_score );

					$overall_score += $category_score;
					$review_items++;
				}
				elseif ( $review_element->type == 'checkbox' ) {

					$element_value = ( isset( $_POST[ $input_name ] ) and is_array( $_POST[ $input_name ] ) ) ? array_map( 'sanitize_text_field', $_POST[ $input_name ] ) : array();
					update_post_meta( $post_id, 'EWD_URP_' . $review_element->name, $element_value );
				}
				else {
					
					$element_value = sanitize_text_field( $_POST[ $input_name ] );
					update_post_meta( $post_id, 'EWD_URP_' . $review_element->name, $element_value );
				}
				
				if ( $review_element->explanation ) {

					$explanation = sanitize_textarea_field( $_POST['EWD_URP_' . str_replace( ' ', '_', $review_element->name ) . '_explanation'] );
					update_post_meta( $post_id, 'EWD_URP_' . $review_element->name . '_Description', $explanation );
				}
			}

			$overall_score = round( ( $overall_score / $review_items ), 2 );
			update_post_meta( $post_id, 'EWD_URP_Overall_Score', $overall_score );
		}

		// Update the average score for the product if the product name isn't blank
		if ( empty( $_POST['product_name'] )  ) { return; }

		$products = get_page_by_title( sanitize_text_field( $_POST['product_name'] ), OBJECT, 'product' );
		if ( ! $products  ) { return; }

		$products = is_array( $products ) ? $products : (array) $products;

		$args = array(
			'posts_per_page' 	=> -1,
			'post_type' 		=> EWD_URP_REVIEW_POST_TYPE,
			'meta_query'		=> array(
				array(
					'key'			=> 'EWD_URP_Product_Name',
					'value'			=> sanitize_text_field( $_POST['product_name'] )
				)
			)
		);

		$query = new WP_Query( $args );

		$scores = array();
		while ( $query->have_posts() ) {

			$query->the_post();
			$score = get_post_meta( $post->ID, 'EWD_URP_Overall_Score', true );

			if ( $score ) { $scores[] = $score; }
		}

		$average_score = ! empty( $scores ) ? array_sum( $scores ) / count( $scores ) : '';

		foreach ( $products as $product ) {

			update_post_meta( $product->ID, 'EWD_URP_Average_Score', $average_score );
		}
	}

	/**
	 * Display the shortcode to use to display a specific review
	 * @since 3.0.0
	 */
	public function display_review_shortcode( $html, $post_id ) {

		$post = get_post( $post_id );

		if ( ! empty( $post->post_type ) and $post->post_type == 'urp_review' ) {

			$html .= '<div class="ewd-urp-shortcode-help">';
			$html .= __( 'Use the following shortcode to add this review to a page:', 'ultimate-reviews' ) . '<br>';
			$html .= '[select-review review_id="' . $post_id . '"]';
			$html .= '</div>';
		}
	
		return $html;
	}

	/**
	 * Add in new columns for the urp_review type
	 * @since 3.0.0
	 */
	public function register_review_table_columns( $defaults ) {
		global $ewd_urp_controller;
		
		$defaults['ewd_urp_views'] = __( '# of Views', 'ultimate-reviews' );
		$defaults['ewd_urp_product'] = __( 'Product', 'ultimate-reviews' );
		$defaults['ewd_urp_score'] = __( 'Score', 'ultimate-reviews' );
		$defaults['ewd_urp_id'] = __( 'Post ID', 'ultimate-reviews' );

    	if ( $ewd_urp_controller->settings->get_setting( 'flag-inappropriate' ) ) { $defaults['ewd_urp_flagged'] = __( 'Inappropriate Flag', 'ultimate-reviews' ); }

		return $defaults;
	}


	/**
	 * Set the content for the custom columns
	 * @since 3.0.0
	 */
	public function display_review_columns_content ( $column_name, $post_id ) {
		
		if ( $column_name == 'ewd_urp_views' ) {

			echo ( get_post_meta( $post_id, 'urp_view_count', true ) ? esc_html( get_post_meta( $post_id, 'urp_view_count', true ) ) : 0 );
		}

		if ( $column_name == 'ewd_urp_product' ) {

			echo ( get_post_meta( $post_id, 'EWD_URP_Product_Name', true ) ? esc_attr( get_post_meta( $post_id, 'EWD_URP_Product_Name', true ) ) : 'No Product Name' );
		}

		if ( $column_name == 'ewd_urp_score' ) {

			echo ( get_post_meta( $post_id, 'EWD_URP_Overall_Score', true ) ? esc_attr( get_post_meta( $post_id, 'EWD_URP_Overall_Score', true ) ) : 'N/A' );
		}

		if ( $column_name == 'ewd_urp_id' ) {

			echo $post_id;
		}

		if ( $column_name == 'ewd_urp_flagged' ) {

			echo ( get_post_meta( $post_id, 'EWD_URP_Flag_Inappropriate', true ) ? esc_attr( get_post_meta( $post_id, 'EWD_URP_Flag_Inappropriate', true ) ) : 'N/A' );
		}

	}

	/**
	 * Register the sortable columns
	 * @since 3.0.0
	 */
	public function register_post_column_sortables( $column ) {
		global $ewd_urp_controller;
	    
	    $column['ewd_urp_views'] = 'ewd_urp_views';
    	$column['ewd_urp_product'] = 'ewd_urp_product';
    	$column['ewd_urp_score'] = 'ewd_urp_score';

    	if ( $ewd_urp_controller->settings->get_setting( 'flag-inappropriate' ) ) { $column['ewd_urp_flagged'] = 'ewd_urp_flagged'; }

   		return $column;
	}

	/**
	 * Adjust the wp_query if the orderby clause is one of the custom ones
	 * @since 3.0.0
	 */
	public function orderby_custom_columns( $vars ) {
		global $wpdb;

		if ( ! isset( $vars['orderby'] ) ) { return $vars; }

		if ( $vars['orderby'] == 'ewd_urp_views' ) {
			
			$vars = array_merge( 
				$vars, 
				array(
        	    	'meta_key' => 'urp_view_count',
        	    	'orderby' => 'meta_value_num'
        	    ) 
        	);
		}

		if ( $vars['orderby'] == 'ewd_urp_product' ) {
			
			$vars = array_merge( 
				$vars, 
				array(
        	    	'meta_key' => 'EWD_URP_Product_Name',
        	    	'orderby' => 'meta_value'
        	    ) 
        	);
		}

		if ( $vars['orderby'] == 'ewd_urp_score' ) {
			
			$vars = array_merge( 
				$vars, 
				array(
        	    	'meta_key' => 'EWD_URP_Overall_Score',
        	    	'orderby' => 'meta_value_num'
        	    ) 
        	);
		}

		if ( $vars['orderby'] == 'ewd_urp_flagged' ) {
			
			$vars = array_merge( 
				$vars, 
				array(
        	    	'meta_key' => 'EWD_URP_Flag_Inappropriate',
        	    	'orderby' => 'meta_value_num'
        	    ) 
        	);
		}

		return $vars;
	}

	/**
	 * Filter the query by product name is set
	 * @since 3.0.0
	 */
	public function filter_by_product_name( $query ) {
		global $typenow;
    	global $pagenow;

    	if ( empty( $typenow ) or $typenow != 'urp_review' ) { return; }

    	if ( is_admin() && $pagenow =='edit.php' && isset( $_GET['ewd_urp_product_name'] ) && $_GET['ewd_urp_product_name'] != '') {
    	    
    	    $query->query_vars['meta_value'] = sanitize_text_field( $_GET['ewd_urp_product_name'] );
    	    $query->query_vars['meta_key'] = 'EWD_URP_Product_Name';
    	}
	}

	/**
	 * Add a select box for the reviewed product's name for urp_review posts
	 * @since 3.0.0
	 */
	public function add_product_name_dropdown() {
		global $wpdb;
		global $typenow;

		if ( empty( $typenow ) or $typenow != 'urp_review' ) { return; }

		$sql = "SELECT DISTINCT " . $wpdb->postmeta . ".meta_value FROM " . $wpdb->postmeta . " INNER JOIN " . $wpdb->posts . " ON " . $wpdb->postmeta . ".post_id=" . $wpdb->posts . ".ID ";
		$sql .= "WHERE post_type='urp_review' AND " . $wpdb->postmeta . ".meta_key='EWD_URP_Product_Name' ORDER BY 1";

		$products = $wpdb->get_results($sql, ARRAY_N);
		$current_product = isset( $_GET['ewd_urp_product_name'] ) ? sanitize_text_field( $_GET['ewd_urp_product_name'] ) : '';

		?>

		<select name="ewd_urp_product_name">
			<option value=""><?php _e('Show All Products', 'ultimate-reviews'); ?></option>
			<?php foreach ( $products as $product_array ) { ?>
				<?php $product = $product_array[0]; ?>
				<option value='<?php echo esc_attr( $product ); ?>' <?php echo ( $product == $current_product ? 'selected' : '' ); ?> ><?php echo esc_html( $product ); ?></option>
			<?php } ?>
		</select>

	<?php }
}
} // endif;
