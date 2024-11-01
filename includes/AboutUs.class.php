<?php
/**
 * Class to create the 'About Us' submenu
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'ewdurpAboutUs' ) ) {
class ewdurpAboutUs {

	public function __construct() {

		add_action( 'wp_ajax_ewd_urp_send_feature_suggestion', array( $this, 'send_feature_suggestion' ) );

		add_action( 'admin_menu', array( $this, 'register_menu_screen' ) );
	}

	/**
	 * Adds About Us submenu page
	 * @since 3.2.0
	 */
	public function register_menu_screen() {
		global $ewd_urp_controller;

		add_submenu_page(
			'edit.php?post_type=urp_review', 
			esc_html__( 'About Us', 'ultimate-reviews' ),
			esc_html__( 'About Us', 'ultimate-reviews' ),
			'manage_options',
			'ewd-urp-about-us',
			array( $this, 'display_admin_screen' )
		);
	}

	/**
	 * Displays the About Us page
	 * @since 3.2.0
	 */
	public function display_admin_screen() { ?>

		<div class='ewd-urp-about-us-logo'>
			<img src='<?php echo plugins_url( "../assets/img/ewd_new_logo_purple2.png", __FILE__ ); ?>'>
		</div>

		<div class='ewd-urp-about-us-tabs'>

			<ul id='ewd-urp-about-us-tabs-menu'>

				<li class='ewd-urp-about-us-tab-menu-item ewd-urp-tab-selected' data-tab='who_we_are'>
					<?php _e( 'Who We Are', 'ultimate-reviews' ); ?>
				</li>

				<li class='ewd-urp-about-us-tab-menu-item' data-tab='lite_vs_premium'>
					<?php _e( 'Lite vs. Premium', 'ultimate-reviews' ); ?>
				</li>

				<li class='ewd-urp-about-us-tab-menu-item' data-tab='getting_started'>
					<?php _e( 'Getting Started', 'ultimate-reviews' ); ?>
				</li>

				<li class='ewd-urp-about-us-tab-menu-item' data-tab='suggest_feature'>
					<?php _e( 'Suggest a Feature', 'ultimate-reviews' ); ?>
				</li>

			</ul>

			<div class='ewd-urp-about-us-tab' data-tab='who_we_are'>

				<p>
					<strong>Founded in 2014, Etoile Web Design is a leading WordPress plugin development company. </strong>
					Privately owned and located in Canada, our growing business is expanding in size and scope. 
					We have more than 50,000 active users across the world, over 2,000,000 total downloads, and our client based is steadily increasing every day. 
					Our reliable WordPress plugins bring a tremendous amount of value to our users by offering them solutions that are designed to be simple to maintain and easy to use. 
					Our plugins, like the <a href='https://www.etoilewebdesign.com/plugins/ultimate-product-catalog/?utm_source=admin_about_us' target='_blank'>Ultimate Product Catalog</a>, <a href='https://www.etoilewebdesign.com/plugins/order-tracking/?utm_source=admin_about_us' target='_blank'>Order Status Tracking</a>, <a href='https://www.etoilewebdesign.com/plugins/ultimate-faq/?utm_source=admin_about_us' target='_blank'>Ultimate FAQs</a> and <a href='https://www.etoilewebdesign.com/plugins/ultimate-reviews/?utm_source=admin_about_us' target='_blank'>Ultimate Reviews</a> are rich in features, highly customizable and responsive. 
					We provide expert support to all of our customers and believe in being a part of their success stories.
				</p>

				<p>
					Our current team consists of web developers, marketing associates, digital designers and product support associates. 
					As a small business, we are able to offer our team flexible work schedules, significant autonomy and a challenging environment where creative people can flourish.
				</p>

			</div>

			<div class='ewd-urp-about-us-tab ewd-urp-hidden' data-tab='lite_vs_premium'>

				<p><?php _e( 'The premium version of the plugin includes a large number of features, such as admin approval of reviews, several display styles, extra custom fields for your reviews, review import/export and more!', 'ultimate-reviews' ); ?></p>

				<p><?php _e( 'Turn on the included <strong>WooCommerce integration</strong> to replace the default WooCommerce product reviews tab as well as the ratings area on your WooCommerce product page with the reviews and ratings from this plugin. This will allow you to better manage your reviews, <strong>review WooCommerce products using Ultimate Reviews</strong>, and offer your visitors more customized reviews and ratings in your WooCommerce shop.', 'ultimate-reviews' ); ?></p>

				<p><em><?php _e( 'The following table provides a comparison of the lite and premium versions.', 'ultimate-reviews' ); ?></em></p>

				<div class='ewd-urp-about-us-premium-table'>
					<div class='ewd-urp-about-us-premium-table-head'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Feature', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Lite Version', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Premium Version', 'ultimate-reviews' ); ?></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Accept and manage unlimited user reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Display product reviews for one or all products', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Multiple rating systems, including points and percentage', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Multiple score input types, including star, text and dropdown', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Filter reviews by score, product name and/or review author', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Restrict reviewable products to a predefined list', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Flexible styling using the Custom CSS option', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Add image and/or video to a review', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Flag reviews as inappropriate', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Allow comments on reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Integration with the Ultimate Product Catalog plugin', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'WooCommerce integration, including replacing reviews and ratings with those from this plugin', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'WooCommerce verified buyers', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'WooCommerce review reminders', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Advanced layout and styling options', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Create in-depth reviews with multiple ratings fields', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Add custom fields to gather extra info', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Structured data for reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Add captcha to submit review form', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Review search and filtering', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Group reviews by product', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Require admin approval before displaying reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Require reviewers to be logged in', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Require email confirmation from reviewers', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Display summary statistics for reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Let users up- or down-vote reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Weighted reviews', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
					<div class='ewd-urp-about-us-premium-table-body'>
						<div class='ewd-urp-about-us-premium-table-cell'><?php _e( 'Advanced labelling options', 'ultimate-reviews' ); ?></div>
						<div class='ewd-urp-about-us-premium-table-cell'></div>
						<div class='ewd-urp-about-us-premium-table-cell'><img src="<?php echo plugins_url( '../assets/img/dash-asset-checkmark.png', __FILE__ ); ?>"></div>
					</div>
				</div>

				<?php printf( __( '<a href="%s" target="_blank" class="ewd-urp-about-us-tab-button ewd-urp-about-us-tab-button-purchase">Buy Premium Version</a>', 'ultimate-reviews' ), 'https://www.etoilewebdesign.com/license-payment/?Selected=URP&Quantity=1&utm_source=admin_about_us' ); ?>
				
			</div>

			<div class='ewd-urp-about-us-tab ewd-urp-hidden' data-tab='getting_started'>

				<p><?php _e( 'The walk-though that ran when you first activated the plugin offers a quick way to get started with setting it up. If you would like to run through it again, just click the button below.', 'ultimate-reviews' ); ?></p>

				<?php printf( __( '<a href="%s" class="ewd-urp-about-us-tab-button ewd-urp-about-us-tab-button-walkthrough">Re-Run Walk-Through</a>', 'ultimate-reviews' ), admin_url( '?page=ewd-urp-getting-started' ) ); ?>

				<p><?php _e( 'We also have a series of video tutorials that cover the available settings as well as key features of the plugin.', 'ultimate-reviews' ); ?></p>

				<?php printf( __( '<a href="%s" target="_blank" class="ewd-urp-about-us-tab-button ewd-urp-about-us-tab-button-youtube">YouTube Playlist</a>', 'ultimate-reviews' ), 'https://www.youtube.com/playlist?list=PLEndQUuhlvSpw3HQakJHj4G0F0Gyc-CtU' ); ?>

				
			</div>

			<div class='ewd-urp-about-us-tab ewd-urp-hidden' data-tab='suggest_feature'>

				<div class='ewd-urp-about-us-feature-suggestion'>

					<p><?php _e( 'You can use the form below to let us know about a feature suggestion you might have.', 'ultimate-reviews' ); ?></p>

					<textarea placeholder="<?php _e( 'Please describe your feature idea...', 'ultimate-reviews' ); ?>"></textarea>
					
					<br>
					
					<input type="email" name="feature_suggestion_email_address" placeholder="<?php _e( 'Email Address', 'ultimate-reviews' ); ?>">
				
				</div>
				
				<div class='ewd-urp-about-us-tab-button ewd-urp-about-us-send-feature-suggestion'>Send Feature Suggestion</div>
				
			</div>

		</div>

	<?php }

	/**
	 * Sends the feature suggestions submitted via the About Us page
	 * @since 3.2.0
	 */
	public function send_feature_suggestion() {
		global $ewd_urp_controller;
		
		if (
			! check_ajax_referer( 'ewd-urp-admin-js', 'nonce' ) 
			|| 
			! current_user_can( 'manage_options' )
		) {
			ewdurpHelper::admin_nopriv_ajax();
		}

		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";  
	    $feedback = sanitize_text_field( $_POST['feature_suggestion'] );
		$feedback .= '<br /><br />Email Address: ';
	  	$feedback .=  sanitize_email( $_POST['email_address'] );
	
	  	wp_mail( 'contact@etoilewebdesign.com', 'URP Feature Suggestion', $feedback, $headers );
	
	  	die();
	} 

}
} // endif;