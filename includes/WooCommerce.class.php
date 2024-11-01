<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ewdurpWooCommerce' ) ) {
	/**
	 * Class to handle WooCommerce interactions for Ultimate Reviews
	 *
	 * @since 3.0.0
	 */
	class ewdurpWooCommerce {

		public function __construct() {

			add_action( 'init', array( $this, 'process_woocommerce_review_reminders' ), 12 );
			add_action( 'admin_init', array( $this, 'woocommerce_category_sync' ) );

			add_action( 'woocommerce_order_status_changed', array( $this, 'reminders_status_update' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'reminders_set_post_meta' ) );

			add_action( 'uwpm_register_custom_element_section', array( $this, 'add_uwpm_element_sections' ) );
			add_action( 'uwpm_register_custom_element', array( $this, 'add_uwpm_elements' ) );

			add_action( 'edited_product_cat', array( $this, 'edit_wc_imported_category' ) );

			add_filter( 'woocommerce_product_tabs', array( $this, 'replace_woocommerce_reviews' ), 98 );
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_review_count' ), 98 );

			add_filter(	'woocommerce_product_get_rating_html', array( $this, 'filter_ratings' ), 98, 2);
			add_filter(	'woocommerce_template_single_rating', array( $this, 'filter_ratings' ), 98, 2);

			add_filter(	'woocommerce_product_get_review_count', array( $this, 'filter_review_count' ), 98, 2);
			add_filter(	'woocommerce_locate_template', array( $this, 'filter_ratings_template' ), 10, 3);

			add_action( 'ewd_urp_insert_review', array( $this, 'create_matching_woocommerce_review' ) );
		}

		/**
		 * Check each review reminder, to determine if there are orders for which it needs to be sent out
		 * @since 3.0.0
		 */
		public function process_woocommerce_review_reminders() {
			global $ewd_urp_controller;

			if ( get_transient( 'ewd-urp-woocommerce-reminders' ) ) { return; }

			set_transient( 'ewd-urp-woocommerce-reminders', true, 1800 );

			$statuses = get_post_stati();

			foreach ( ewd_urp_decode_infinite_table_setting( $ewd_urp_controller->settings->get_setting( 'ewd-urp-woocommerce-review-reminders' ) ) as $reminder ) {

				$reminder_time_lag = $this->calculate_time_lag( $reminder );

				$before_modified_date = date( 'Y-m-d H:i:s', time() - $reminder_time_lag - (3*24*3600) );
				$before_posted_date = date( 'Y-m-d H:i:s', time()- $reminder_time_lag );

				$args = array(
					'post_type' 	=> 'shop_order',
					'post_status' 	=> $statuses,
					'date_query' 	=> array(
						array(
							'column' 	=> 'post_date',
							'before' 	=> $before_posted_date,
						),
						array(
							'column' 	=> 'post_modified',
							'after' 	=> $before_modified_date,
						),
					),
				);
	
				$query = new WP_Query( $args );
				$orders = $query->get_posts();
	
				foreach ( $orders as $order ) {

					$reminders_sent = (array) get_post_meta( $order->ID, 'EWD_URP_Reminders_Sent', true );
					$order_statuses = (array) get_post_meta( $order->ID, 'EWD_URP_WC_Order_Statuses', true );
	
					if ( ! in_array( $reminder['id'], $reminders_sent ) ) {

						$status_time = time() + 100;

						foreach ( $order_statuses as $order_status ) {

							if ( $order_status['Status'] == $reminder['status'] ) { $status_time = $order_status['Updated']; }
						}

						if ( ( $status_time + $reminder_time_lag ) < time() ) {

							$this->send_review_reminder_email( $order, $reminder );

							$reminders_sent[] = $reminder['id'];

							update_post_meta( $order->ID, "EWD_URP_Reminders_Sent", $reminders_sent );
						}
					}
				}
			}
		}

		public function send_review_reminder_email( $order, $reminder_item ) {
			global $ewd_urp_controller;
		
			$email_address = get_post_meta( $order->ID, '_billing_email', true );

			if ( $reminder_item['email-to-send'] < 0 ) {

				$user_id = get_post_meta( $order->ID, '_customer_user', true );
		
				$params = array(
					'Email_ID' => $reminder_item['email-to-send'] * -1,
					'post_id' => $order->ID
				);
		
				if ( $user_id != 0 and $user_id != '' ) {

					$params['User_ID'] = $user_id;
					EWD_UWPM_Email_User( $params );
				}
				else {

					$params['Email_Address'] = $email_address;
					EWD_URP_Send_Email_To_Non_User( $params );
				}
			}
			else {

				foreach ( ewd_urp_decode_infinite_table_setting( $ewd_urp_controller->settings->get_setting( 'email-messages-array' ) ) as $email_message ) {

					if ( $email_message->id == $reminder_item['email-to-send'] ) {

						$review_code = get_post_meta( $order->ID, 'EWD_URP_Review_Code', true );

						$template_message = $this->get_email_template( $email_message, $order );

						$search = array( '[purchase-date]', '[review-code]' );
						$replace = array( $order->post_date, $review_code );
						$message_body = str_replace( $search, $replace, $template_message);
						
						$headers = array('Content-Type: text/html; charset=UTF-8');

						$mail_success = wp_mail( $email_address, $email_message->subject, $message_body, $headers );
					}
				}
			}
		}

		public function calculate_time_lag( $reminder ) {

			if ( $reminder['reminder-unit'] == "hours") { $multiplier = 3600; }
			elseif ( $reminder['reminder-unit'] == "days") { $multiplier = 3600*24; }
			else { $multiplier = 3600*24*7; }
		
			$reminder_time_lag = $multiplier * $reminder['reminder-interval'];
		
			return $reminder_time_lag;
		}

		public function get_email_template( $email_message_item, $order = false ) {
			global $ewd_urp_controller;

			$message_title = $email_message_item->subject;
			$message_content = $this->replace_email_content( stripslashes( $email_message_item->message ), $order) ;
			
			ob_start();

			?>

			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
				<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
				<title><?php echo esc_html( $message_title ); ?></title>

				<style type="text/css">
				.body-wrap {
					background-color: <?php echo $ewd_urp_controller->settings->get_setting( 'styling-email-background-color' ); ?> !important;
				}
				.btn-primary {
					background-color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-background-color' ) ); ?> !important;
					border-color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-background-color' ) ); ?> !important;
					color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-text-color' ) ); ?> !important;
				}
				.btn-primary:hover {
					background-color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-bg-hover-color' ) ); ?> !important;
					border-color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-bg-hover-color' ) ); ?> !important;
					color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-button-text-hover-color' ) ); ?> !important;
				}
				.main {
					background: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-inner-color' ) ); ?> !important;
					color: <?php echo esc_attr( $ewd_urp_controller->settings->get_setting( 'styling-email-text-color' ) ); ?>;
				}
			
				img {
					max-width: 100%;
				}
				body {
			  		-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em;
				}
				body {
			  		background-color: #f6f6f6;
			  	}
				@media only screen and (max-width: 640px) {
			    	body {
			    	  padding: 0 !important;
			    	}
			    	h1 {
			    	  font-weight: 800 !important; margin: 20px 0 5px !important;
			    	}
			    	h2 {
			    	  font-weight: 800 !important; margin: 20px 0 5px !important;
			    	}
			    	h3 {
			    	  font-weight: 800 !important; margin: 20px 0 5px !important;
			    	}
			    	h4 {
			    	  font-weight: 800 !important; margin: 20px 0 5px !important;
			    	}
			    	h1 {
			    	  font-size: 22px !important;
			    	}
			    	h2 {
			    	  font-size: 18px !important;
			    	}
			    	h3 {
			    	  font-size: 16px !important;
			    	}
			    	.container {
			    	  padding: 0 !important; width: 100% !important;
			    	}
			    	.content {
			    	  padding: 0 !important;
			    	}
			    	.content-wrap {
			    	  padding: 10px !important;
			    	}
			    	.invoice {
			    	  width: 100% !important;
			    	}
				}
				</style>
				</head>
			
				<body style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">
			
					<table class="body-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">
						<tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
							<td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
							<td class="container" width="600" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
								<div class="content" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
									<table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
										<tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
											<td class="content-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;" valign="top">
												<meta style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;" />
												<table
													width="100%"
													cellpadding="0"
													cellspacing="0"
													style="
														font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
														box-sizing: border-box;
														font-size: 14px;
														margin: 0;">
													<?php echo wp_kses_post( $message_content ); ?>
												</table>
											</td>
										</tr>
									</table>
					    		</div>
							</td>
							<td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
						</tr>
					</table>
				</body>
			</html>

			<?php

			$message = ob_get_clean();

			$message = apply_filters( 'ewd_urp_woocommerce_reminder_email_content', $message, $email_message_item, $order );
		
		  	return $message;
		}

		public function replace_email_content( $message, $order ) {

			if ( strpos( $message, '[footer]' ) === false) { $message .= '</table></td></tr></table>'; }

			$search = array(
				'[section]', 
				'[/section]', 
				'[footer]', 
				'[/footer]', 
				'[/button]'
			);

			$replace = array(
			  '<tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">',
			  '</td></tr>',
			  '</table></td></tr></table><div class="footer" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;"><table width="100%" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="aligncenter content-block" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; vertical-align: top; color: #999; text-align: center; margin: 0; padding: 0 0 20px;" align="center" valign="top">',
			  '</td></tr></table></div>',
			  '</a></td></tr>'
			);

			$output = str_replace( $search, $replace, $message );
			$output = $this->replace_email_links( $output );
			$output = $this->add_product_review_links( $output, $order );

			return $output;
		}

		public function replace_email_links( $message ) {

			$pattern = "/\[button link=\'(.*?)\'\]/";

			preg_match_all( $pattern, $message, $matches );

			$replace = '<tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top"><a href="INSERTED_LINK" class="btn-primary" itemprop="url" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">';
			$output = preg_replace( $pattern, $replace, $message );

			if ( is_array( $matches[1] ) ) {

				foreach ( $matches[1] as $link ) {

					$pos = strpos( $output, "INSERTED_LINK" );
					if ( $pos !== false ) {

					    $newstring = substr_replace( $output, $link, $pos, 13 );
					    $output = $newstring;
					}
				}
			}

			return $output;
		}

		public function add_product_review_links( $message, $order_post ) {

			if ( ! $order_post ) { return $message; }

			$pattern = "/\[review-items link=\'(.*?)\'\]/";

			preg_match( $pattern, $message, $matches );
	
			$order = new WC_Order( $order_post->ID );
			$items = $order->get_items();
	
			$email_address = get_post_meta( $order_post->ID, '_billing_email', true );
	
			$replace = '';
			if ( isset( $matches[1] ) ) {

				$link = $matches[1];

				if ( strpos( $link, '?' ) === false ) { $link .= '?src=ewd_urp_email'; }
				else { $link .= '&src=ewd_urp_email'; }

				$link .= '&order_id=' . $order_post->ID;
				$link .= '&Post_Email=' . $email_address;

				foreach ($items as $product) {

					$product_link = $link . '&product_name=' . $product['name'];
					$replace .= '<tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top"><a href="' . $product_link . '" class="btn-primary" itemprop="url" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . $product['name'] . '</a></td></tr>';
				}
			}

			$output = preg_replace( $pattern, $replace, $message );

			return $output;
		}

		public function add_uwpm_element_sections() {
		
			if ( ! function_exists( 'uwpm_register_custom_element_section' ) ) { return; }
		
			uwpm_register_custom_element_section(
				'ewd_urp_uwpm_elements', 
				array(
					'label' => 'Review Tags'
				)
			);
		}

		function add_uwpm_elements() {
			
			if ( ! function_exists( 'uwpm_register_custom_element' ) ) { return; }
			
			uwpm_register_custom_element(
				'ewd_urp_product_review_links', 
				array(
					'label' => 'Product Review Links',
					'callback_function' => 'EWD_URP_UWPM_Product_Review_Links',
					'section' => 'ewd_urp_uwpm_elements',
					'attributes' => array(
						array(
							'attribute_name' => 'ewd_urp_submit_review_url',
							'attribute_label' => 'Submit Review URL',
							'attribute_type' => 'TextBox'
						)
					)
				)
			);
		}

		public function reminders_set_post_meta( $post_id ) {

			$review_code = $this->create_review_code();

			update_post_meta( $post_id, "EWD_URP_Review_Code", $review_code );
			update_post_meta( $post_id, "EWD_URP_Reminders_Sent", array() );

			$order_statuses = array( 
				array(
					'Status' => get_post_status( $post_id ), 
					'Updated' => time()
				)
			);

			update_post_meta( $post_id, "EWD_URP_WC_Order_Statuses", $order_statuses );
		}

		public function reminders_status_update( $post_id, $old_status = '', $new_status = '' ) {

			$order_statuses = (array) get_post_meta( $post_id, 'EWD_URP_WC_Order_Statuses', true );

			$status = get_post_status( $post_id );

			$order_statuses[] = array(
				'Status' => $status, 
				'Updated' => time()
			);

			update_post_meta( $post_id, "EWD_URP_WC_Order_Statuses", $order_statuses );
		}

		public function create_review_code( $length = 6 ) {
			
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$review_code = '';
			for ( $i = 0; $i < $length; $i++ ) {

				$review_code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
			}
		
			return $review_code;
		}

		public function woocommerce_category_sync() {
			global $ewd_urp_controller;

			if ( ! isset( $_POST['ewd-urp-settings']['match-woocommerce-categories'] ) ) { return; }

			if ( ! $ewd_urp_controller->settings->get_setting( 'match-woocommerce-categories' ) ) { return; }

			$args = array(
				'taxonomy' => 'product_cat', 
				'hide_empty' => false
			);

			$wc_categories = get_terms( $args );

		    if ( $wc_categories ) {

		        usort( $wc_categories, function( $a, $b ) { return $a->parent - $b->parent; } );
		        
		        foreach ( $wc_categories as $wc_category ) {

		            $this->match_wc_cat( $wc_category );
		        }
		    }
		}

		public function edit_wc_imported_category( $term_id ) {
			global $ewd_urp_controller;

			if ( ! $ewd_urp_controller->settings->get_setting( 'match-woocommerce-categories' ) ) { return; }

			$wc_category = get_term_by( 'id', $term_id, 'product_cat' );

			$args = array(
				'taxonomy' 		=> EWD_URP_REVIEW_CATEGORY_TAXONOMY, 
				'meta_key' 		=> 'product_cat', 
				'meta_value' 	=> $wc_category->term_id, 
				'hide_empty' 	=> false
			);

			$urp_cat = get_terms( $args );
			
			if ( empty( $urp_cat ) ) { $this->match_wc_cat( $wc_category ); }
			else { $this->update_wc_cat( $wc_category, $urp_cat[0] ); }
		}

		public function match_wc_cat( $wc_category ) {

			$args = array(
				'taxonomy' 		=> EWD_URP_REVIEW_CATEGORY_TAXONOMY, 
				'meta_key' 		=> 'product_cat', 
				'meta_value' 	=> $wc_category->term_id, 
				'hide_empty' 	=> false
			);
			
			$urp_cat = get_terms( $args );
		    
		    if ( ! empty( $urp_cat ) ) { return; }
		
		    if ( $wc_category->parent != 0 ) {

		    	$args = array(
					'taxonomy' 		=> EWD_URP_REVIEW_CATEGORY_TAXONOMY, 
					'meta_key' 		=> 'product_cat', 
					'meta_value' 	=> $wc_category->parent, 
					'hide_empty' 	=> false
				);

		    	$urp_cat = get_terms( $args );

		    	if ( ! empty( $urp_cat ) ) { $parent_id = $urp_cat[0]->term_id; }
		    }

		    if ( ! isset( $parent_id ) ) { $parent_id = 0; }
		
		    $new_urp_cat = wp_insert_term( $wc_category->name, EWD_URP_REVIEW_CATEGORY_TAXONOMY, array( 'parent' => $parent_id ) );
		    if ( ! is_wp_error( $new_urp_cat ) ) { update_term_meta( $new_urp_cat['term_id'], 'product_cat', $wc_category->term_id ); }
		}

		public function update_wc_cat( $wc_category, $urp_cat ) {

			$parent_id = 0;

			if ( $wc_category->parent != 0 ) {

		    	$args = array(
					'taxonomy' 		=> EWD_URP_REVIEW_CATEGORY_TAXONOMY, 
					'meta_key' 		=> 'product_cat', 
					'meta_value' 	=> $wc_category->parent, 
					'hide_empty' 	=> false
				);

		    	$parent_cat = get_terms( $args );
		    	$parent_id = $parent_cat[0]->term_id;
		    }

		    $args = array(
		    	'name' => $wc_category->name, 
		    	'parent' => $parent_id, 
		    	'hide_empty' => false
		    );

			wp_update_term( $urp_cat->term_id, EWD_URP_REVIEW_CATEGORY_TAXONOMY, $args );
		}

		public function replace_woocommerce_reviews( $tabs ) {
			global $ewd_urp_controller;
		
			if ( ! $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) ) { return $tabs; }
				
			$tabs['reviews']['callback'] = array( $this, 'display_woocommerce_reviews_section' );
		
			return $tabs;
		}
	
		public function display_woocommerce_reviews_section() {
			global $ewd_urp_controller;
			global $product;
		
			if ( $product->is_type( 'variation' ) ) {$post_data = get_post( $product->get_parent_id() );}
			else { $post_data = get_post( $product->get_id() ); }
		
			if ( $ewd_urp_controller->settings->get_setting( 'woocommerce-review-submit-first' ) ) { $this->display_woocommerce_submit_review( $post_data ); }
			else { $this->display_woocommerce_reviews( $post_data ); }
			
			echo "<div class='ewd-urp-woocommerce-tab-divider'></div>";
			
			if ( $ewd_urp_controller->settings->get_setting( 'woocommerce-review-submit-first' ) ) { $this->display_woocommerce_reviews( $post_data ); }
			else { $this->display_woocommerce_submit_review( $post_data ); }
		}
	
		public function display_woocommerce_reviews( $post_data ) {
			global $ewd_urp_controller;
			global $wpdb;
		
			$woocommerce_review_types = $ewd_urp_controller->settings->get_setting( 'woocommerce-review-types' );
			$woocommerce_category_product_reviews = $ewd_urp_controller->settings->get_setting( 'woocommerce-category-product-reviews' );
		
			echo '<h2>' . __( 'Reviews', 'ultimate-reviews' ) . '</h2>';
			
			if ( $woocommerce_review_types == 'default' ){

				echo do_shortcode( '[ultimate-reviews product_name="' . esc_attr( $post_data->post_title ) . '"]' );
			}
			else {
					
				echo do_shortcode( '[ultimate-reviews product_name="' . esc_attr( $post_data->post_title ) . '" orderby="' . esc_attr( $woocommerce_review_types ) . '"]' );
			}

			$reviews = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='EWD_URP_Product_Name' AND meta_value='%s'", $post_data->post_title ) );
					
			// If we already have enough reviews, no need to add more
			if ( $wpdb->num_rows >= $woocommerce_category_product_reviews ) { return; }
						
			$reviews_to_add = $woocommerce_category_product_reviews - $wpdb->num_rows;
			$wc_cat_ids = wp_get_post_terms( $post_data->ID, 'product_cat', array( 'fields' => 'ids' ) );
						
			$args = array(
				'taxonomy' => 'urp-review-category',
				'fields' => 'ids',
				'meta_query' => array(
					'key' => 'product_cat',
					'value' => $wc_cat_ids,
					'compare' => 'IN'
				)
			);
	
			$urp_categories = get_terms( $args );

			$post_id_array = array();
			foreach ( $reviews as $review ) { 

				$post_id_array[] = $review->post_id; 
			}
						
			if ( ! empty( $urp_categories ) ) {
							
				if ( $wpdb->num_rows != 0 ) { echo '<div class="ewd-urp-woocommerce-tab-divider"></div>'; }
							
				echo '<h3>' . __( 'Reviews for Similar Products', 'ultimate-reviews' ) . '</h3>';
				echo do_shortcode( '[ultimate-reviews include_category_ids="' . esc_attr( implode( ',', $urp_categories ) ) . '" exclude_ids="' . esc_attr(  implode( ',', $post_id_array ) ) . '" post_count="' . esc_attr( $reviews_to_add ) . '"]' );
			}
		}
		
		public function display_woocommerce_submit_review( $post_data ) {
	
			echo '<h2>' . __( 'Leave a review', 'ultimate-reviews' ) . '</h2>';
			echo '<style>.ewd-urp-form-header {display:none;}</style>';
			echo do_shortcode( '[submit-review product_name="' . esc_attr( $post_data->post_title ) . '"]' );
		}
	
		public function add_review_count( $tabs ) {
			global $ewd_urp_controller;
			global $product;
			global $wp_filter;
		
			if ( $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) and is_object( $product ) ) {
	
				if ( $product->is_type( 'variation' ) ) { $post_data = get_post( $product->get_parent_id() ); }
				else { $post_data = get_post( $product->get_id() ); }
		
				$title = __( 'Reviews', 'ultimate-reviews' ) . ' (' . $this->get_review_count( $post_data->post_title ) . ')';
		
				$tabs['reviews']['title'] = $title;	
			}
		
			return $tabs;
		}
	
		public function filter_ratings( $content, $rating ) {
			global $ewd_urp_controller;
			global $product;
		
			if ( ! $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) ) { return $content; }
		
			if ( $product->is_type( 'variation' ) ) { $post_data = get_post( $product->get_parent_id() ); }
			else { $post_data = get_post( $product->get_id() ); }
			
			$rating = $this->get_aggregate_score( $post_data->post_title );
	
			ob_start();
	
			?>
		
			<div class='star-rating' title='<?php printf( __( 'Rated %s out of %s', 'woocommerce' ), $rating, $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ); ?>'>
				<span style='width: <?php echo esc_attr( ( ( $rating / $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ) * 100 ) ); ?>%;'>
					<strong class='rating'><?php echo esc_attr( $rating ); ?></strong>
					<?php printf( __( 'out of %s', 'woocommerce' ), $ewd_urp_controller->settings->get_setting( 'maximum-score' ) ); ?>
				</span>
			</div>
	
			<?php
	
			$output = ob_get_clean();
		
			return $output;
		}
		
		public function filter_review_count( $count, $item ) {
			global $ewd_urp_controller;
			global $product;
		
			if ( ! $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) ) { return $count; }
		
			if ( $product->is_type( 'variation' ) ) { $post_data = get_post( $product->get_parent_id() ); }
			else { $post_data = get_post( $product->get_id() ); }
		
			return $this->get_review_count( $post_data->post_title );
		}
		
		public function filter_ratings_template( $template, $template_name, $template_path ) {
			global $ewd_urp_controller;
			global $woocommerce;
	
			if ( ! $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) ) { return $template; }
	
			if ( ! $ewd_urp_controller->settings->get_setting( 'override-woocommerce-ratings-display' ) ) { return $template; }
		
			if ( $template_name != 'single-product/rating.php' ) {return $template;}
		
			// Look within passed path within the theme - this is priority
			if ( ! $template_path ) { $template_path = $woocommerce->template_url; }
	
			$template = locate_template( 
				array(
					$template_path . $template_name,
					$template_name
				)
		 	);
		
			// Modification: Get the template
			if ( ! $template or  $ewd_urp_controller->settings->get_setting( 'override-woocommerce-ratings-display' ) ) {
	
				$template = EWD_URP_PLUGIN_DIR . '/' . EWD_URP_TEMPLATE_DIR . '/wc-rating.php';
			}
		
			return $template;
		}
	
		public function get_review_count( $product_name ) {
			global $ewd_urp_controller;
	
			$meta_query_array = array(
				array(
					'key' 		=> 'EWD_URP_Product_Name',
					'value' 	=> $product_name,
					'compare' 	=> '=',
				)
			);

			if ( $ewd_urp_controller->settings->get_setting( 'email-confirmation' ) ) {

				$meta_query_array[] = array(
					'key' 		=> 'EWD_URP_Email_Confirmed',
					'value' 	=> 'Yes',
					'compare' 	=> '=',
				);
			} 
	
			$params = array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> 'urp_review',
				'meta_query' 		=> $meta_query_array
			);
		
			$posts = get_posts( $params );
	
			return count( $posts );
		}
	
		public function get_aggregate_score( $product_name ) {
			
			$meta_query_array = array(
				array(
					'key' => 'EWD_URP_Product_Name',
					'value' => $product_name,
					'compare' => '=',
				)
			);
		
			$params = array(
				'posts_per_page' => -1,
				'post_type' => 'urp_review',
				'meta_query' => $meta_query_array
			);
			
			$posts = get_posts( $params );
		
			$total_score = 0;
		
			foreach ( $posts as $post ) {
	
				$overall_score = get_post_meta( $post->ID, 'EWD_URP_Overall_Score', true );
		
				$total_score += $overall_score;
			}
		
			$average_score = count( $posts ) > 0 ? $total_score / count( $posts ) : 0;
	
			return $average_score;
		}

		public function create_matching_woocommerce_review( $review ) {
			global $ewd_urp_controller;
	
			if ( ! $ewd_urp_controller->settings->get_setting( 'replace-woocommerce-reviews' ) ) { return; }
	
			$post_id = $review->ID;
	
			$product_name = get_post_meta( $post_id, 'EWD_URP_Product_Name', true );
			$rating = get_post_meta( $post_id, 'EWD_URP_Overall_Score', true );
			$author = get_post_meta( $post_id, 'EWD_URP_Post_Author', true );
			$author_email = get_post_meta( $post_id, 'EWD_URP_Post_Email', true );
		
			$post = get_post( $post_id );
			$title = $post->post_title;
			$comment = $post->post_content;
		
			$time = current_time( 'mysql' );
		
			$user_id = get_current_user_id();
		
			$wc_product = get_page_by_title( $product_name, 'OBJECT', 'product' );
	
			if (! is_object( $wc_product ) ) { return; }
		
			$args = array(
			    'comment_post_ID' 		=> $wc_product->ID,
			    'comment_author' 		=> $author,
			    'comment_author_email' 	=> $author_email,
			    'comment_author_url'	=> 'http://example.com',
			    'comment_content' 		=> "<h2>" . $title . "</h2>" . $comment,
			    'comment_date' 			=> $time,
			    'comment_approved' 		=> 1,
			    'user_id' 				=> $user_id
			);
		
			$comment_id = wp_new_comment( $args, true );
	
			if ( ! is_wp_error( $comment_id ) ) {
	
				add_comment_meta( $comment_id, 'rating', $rating, true );
		
				$request['product_id'] = $wc_product->ID;
				$product_review = get_comment( $comment_id );
				do_action( "woocommerce_rest_insert_product_review", $product_review, $request, true );
			}
		}
	}
}