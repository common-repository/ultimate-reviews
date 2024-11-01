<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdurpBackwardsCompatibility' ) ) {
/**
 * Class to handle transforming the plugin settings from the 
 * previous style (individual options) to the new one (options array)
 *
 * @since 3.0.0
 */
class ewdurpBackwardsCompatibility {

	public function __construct() {
		
		if ( empty( get_option( 'ewd-urp-settings' ) ) and get_option( 'EWD_URP_Full_Version' ) ) { $this->run_backwards_compat(); }
		elseif ( ! get_option( 'ewd-urp-permission-level' ) ) { update_option( 'ewd-urp-permission-level', 1 ); }
	}

	public function run_backwards_compat() {

		$settings = array(
			'custom-css' 								=> get_option( 'EWD_URP_Custom_CSS' ),
			'maximum-score' 							=> intval( get_option( 'EWD_URP_Maximum_Score' ) ),
			'review-style'	 							=> strtolower( get_option( 'EWD_URP_Review_Style' ) ),
			'review-score-input' 						=> strtolower( get_option( 'EWD_URP_Review_Score_Input' ) ),
			'review-image'								=> get_option( 'EWD_URP_Review_Image' ) == 'Yes' ? true : false,
			'review-video'								=> get_option( 'EWD_URP_Review_Video' ) == 'Yes' ? true : false,
			'review-category'							=> get_option( 'EWD_URP_Review_Category' ) == 'Yes' ? true : false,
			'review-filtering'							=> is_array( get_option( 'EWD_URP_Review_Filtering' ) ) ? array_map( 'strtolower', get_option( 'EWD_URP_Review_Filtering' ) ) : array(),
			'submit-review-toggle'						=> get_option( 'EWD_URP_Submit_Review_Toggle' ) == 'Yes' ? true : false,
			//'allow-reviews'								=> get_option( 'EWD_URP_Allow_Reviews' ),
			'review-elements'							=> $this->convert_review_elements(),
			'autocomplete-product-names'				=> get_option( 'EWD_URP_Autocomplete_Product_Names' ) == 'Yes' ? true : false,
			'restrict-product-names'					=> get_option( 'EWD_URP_Restrict_Product_Names' ) == 'Yes' ? true : false,
			'product-name-input-type'					=> strtolower( get_option( 'EWD_URP_Product_Name_Input_Type' ) ),
			'upcp-integration'							=> get_option( 'EWD_URP_UPCP_Integration' ) == 'Yes' ? true : false,
			'product-names-array'						=> $this->convert_product_names(),
			'link-to-post'								=> get_option( 'EWD_URP_Link_To_Post' ) == 'Yes' ? true : false,
			'display-author'							=> get_option( 'EWD_URP_Display_Author' ) == 'Yes' ? true : false,
			'display-date'								=> get_option( 'EWD_URP_Display_Date' ) == 'Yes' ? true : false,
			'display-time'								=> get_option( 'EWD_URP_Display_Time' ) == 'Yes' ? true : false,
			'display-categories'						=> get_option( 'EWD_URP_Display_Categories' ) == 'Yes' ? true : false,
			'author-click-filter'						=> get_option( 'EWD_URP_Author_Click_Filter' ) == 'Yes' ? true : false,
			'flag-inappropriate'						=> get_option( 'EWD_URP_Flag_Inappropriate' ) == 'Yes' ? true : false,
			'review-comments'							=> get_option( 'EWD_URP_Review_Comments' ) == 'Yes' ? true : false,
			'review-character-limit'					=> get_option( 'EWD_URP_Review_Character_Limit' ) ? intval( get_option( 'EWD_URP_Review_Character_Limit' ) ) : '',
			'email-on-submission'						=> get_option( 'EWD_URP_Email_On_Submission' ),
			'reviews-per-page'							=> intval( get_option( 'EWD_URP_Reviews_Per_Page' ) ),
			'pagination-location'						=> strtolower( get_option( 'EWD_URP_Pagination_Location' ) ),
			'show-tinymce'								=> get_option( 'EWD_URP_Show_TinyMCE' ) == 'Yes' ? true : false,
			'review-format'								=> strtolower( get_option( 'EWD_URP_Review_Format' ) ),
			'summary-statistics'						=> strtolower( get_option( 'EWD_URP_Summary_Statistics' ) ),
			'summary-clickable'							=> get_option( 'EWD_URP_Summary_Clickable' ) == 'Yes' ? true : false,
			'display-microdata'							=> get_option( 'EWD_URP_Display_Microdata' ) == 'Yes' ? true : false,
			'items-reviewed'							=> get_option( 'EWD_URP_Items_Reviewed' ),			
			'pretty-permalinks'							=> get_option( 'EWD_URP_Pretty_Permalinks' ) == 'Yes' ? true : false,
			'review-karma'								=> get_option( 'EWD_URP_Review_Karma' ) == 'Yes' ? true : false,
			'use-captcha'								=> get_option( 'EWD_URP_Use_Captcha' ) == 'Yes' ? true : false,
			'infinite-scroll'							=> get_option( 'EWD_URP_Infinite_Scroll' ) == 'Yes' ? true : false,
			'thumbnail-characters'						=> intval( get_option( 'EWD_URP_Thumbnail_Characters' ) ),
			'read-more-ajax'							=> get_option( 'EWD_URP_Read_More_AJAX' ) == 'Yes' ? true : false,
			'admin-notification'						=> get_option( 'EWD_URP_Admin_Notification' ) == 'Yes' ? true : false,
			'admin-email-address'						=> get_option( 'EWD_URP_Admin_Email_Address' ),
			'admin-approval'							=> get_option( 'EWD_URP_Admin_Approval' ) == 'Yes' ? true : false,
			'require-email'								=> get_option( 'EWD_URP_Require_Email' ) == 'Yes' ? true : false,
			'email-confirmation'						=> get_option( 'EWD_URP_Email_Confirmation' ) == 'Yes' ? true : false,
			'display-on-confirmation'					=> get_option( 'EWD_URP_Display_On_Confirmation' ) == 'Yes' ? true : false,
			'one-review-per-product-person'				=> get_option( 'EWD_URP_One_Review_Per_Product_Person' ) == 'Yes' ? true : false,
			'review-blacklist'							=> get_option( 'EWD_URP_Review_Blacklist' ),
			'require-login'								=> get_option( 'EWD_URP_Require_Login' ) == 'Yes' ? true : false,
			'replace-woocommerce-reviews'				=> get_option( 'EWD_URP_Replace_WooCommerce_Reviews' ) == 'Yes' ? true : false,
			'woocommerce-review-submit-first'			=> get_option( 'EWD_URP_WooCommerce_Review_Submit_First' ) == 'Yes' ? true : false,
			'only-woocommerce-products'					=> get_option( 'EWD_URP_Only_WooCommerce_Products' ) == 'Yes' ? true : false,
			'woocommerce-review-types'					=> is_array( get_option( 'EWD_URP_WooCommerce_Review_Types' ) ) ? array_map( 'strtolower', get_option( 'EWD_URP_WooCommerce_Review_Types' ) ) : array(),
			'override-woocommerce-ratings-display'		=> get_option( 'EWD_URP_Override_WooCommerce_Theme' ) == 'Yes' ? true : false,
			'verified-buyer-badge'						=> get_option( 'EWD_URP_Verified_Buyer_Badge' ) == 'Yes' ? true : false,
			'match-woocommerce-categories'				=> get_option( 'EWD_URP_Match_WooCommerce_Categories' ) == 'Yes' ? true : false,
			'woocommerce-category-product-reviews'		=> intval( get_option( 'EWD_URP_WooCommerce_Category_Product_Reviews' ) ),
			'reminders-array'							=> $this->convert_reminders(),
			'email-messages-array'						=> $this->convert_email_messages(),
			'wordpress-login-url'						=> get_option( 'EWD_URP_WordPress_Login_URL' ),
			'feup-login-url'							=> get_option( 'EWD_URP_FEUP_Login_URL' ),
			'indepth-reviews'							=> get_option( 'EWD_URP_InDepth_Reviews' ) == 'Yes' ? true : false,
			'group-by-product'							=> get_option( 'EWD_URP_Group_By_Product' ) == 'Yes' ? true : false,
			'group-by-product-order'					=> strtolower( get_option( 'EWD_URP_Group_By_Product_Order' ) ),
			'ordering-type'								=> strtolower( get_option( 'EWD_URP_Ordering_Type' ) ),
			'order-direction'							=> strtolower( get_option( 'EWD_URP_Order_Direction' ) ),
			'disable-numerical-score'					=> get_option( 'EWD_URP_Display_Numerical_Score' ) == 'Yes' ? false : true,
			'reviews-skin'								=> strtolower( get_option( 'EWD_URP_Reviews_Skin' ) ),
			'review-group-separating-line'				=> get_option( 'EWD_URP_Review_Group_Separating_Line' ) == 'Yes' ? true : false,
			'indepth-layout'							=> strtolower( get_option( 'EWD_URP_InDepth_Layout' ) ),
			'read-more-style'							=> strtolower( get_option( 'EWD_URP_Read_More_Style' ) ),
			'label-posted'								=> get_option( 'EWD_URP_Posted_Label' ),
			'label-by'									=> get_option( 'EWD_URP_By_Label' ),
			'label-on'									=> get_option( 'EWD_URP_On_Label' ),
			'label-score'								=> get_option( 'EWD_URP_Score_Label' ),
			'label-explanation'							=> get_option( 'EWD_URP_Explanation_Label' ),
			'label-submit-product'						=> get_option( 'EWD_URP_Submit_Product_Label' ),
			'label-submit-author'						=> get_option( 'EWD_URP_Submit_Author_Label' ),
			'label-submit-author-comment'				=> get_option( 'EWD_URP_Submit_Author_Comment_Label' ),
			'label-submit-title'						=> get_option( 'EWD_URP_Submit_Title_Label' ),
			'label-submit-title-comment'				=> get_option( 'EWD_URP_Submit_Title_Comment_Label' ),
			'label-submit-score'						=> get_option( 'EWD_URP_Submit_Score_Label' ),
			'label-submit-review'						=> get_option( 'EWD_URP_Submit_Review_Label' ),
			'label-submit-element-score'				=> get_option( 'EWD_URP_Submit_Cat_Score_Label' ),
			'label-submit-explanation'					=> get_option( 'EWD_URP_Submit_Explanation_Label' ),
			'label-submit-button'						=> get_option( 'EWD_URP_Submit_Button_Label' ),
			'label-submit-success-message'				=> get_option( 'EWD_URP_Submit_Success_Message' ),
			'label-submit-draft-message'				=> get_option( 'EWD_URP_Submit_Draft_Message' ),
			'label-review-for'							=> get_option( 'EWD_URP_Review_For_Label' ),
			'label-categories'							=> get_option( 'EWD_URP_Categories_Label_Label' ),
			'label-verified-buyers'						=> get_option( 'EWD_URP_Verified_Buyer_Label' ),
			'label-filter-button'						=> get_option( 'EWD_URP_Filter_Button_Label' ),
			'label-filter-product-name'					=> get_option( 'EWD_URP_Filter_Product_Name_Label' ),
			'label-filter-all'							=> get_option( 'EWD_URP_Filter_All_Label' ),
			'label-filter-review-score'					=> get_option( 'EWD_URP_Filter_Review_Score_Label' ),
			'label-filter-review-author'				=> get_option( 'EWD_URP_Filter_Review_Author_Label' ),
			'label-submit-reviewer-email-address'		=> get_option( 'EWD_URP_Submit_Reviewer_Email_Address_Label' ),
			'label-submit-reviewer-email-address-desc'	=> get_option( 'EWD_URP_Submit_Reviewer_Email_Address_Instructions_Label' ),
			'label-submit-image-number'					=> get_option( 'EWD_URP_Submit_Image_Number_Label' ),
			'label-summary-average-score'				=> get_option( 'EWD_URP_Summary_Average_Score_Label' ),
			'label-summary-ratings'						=> get_option( 'EWD_URP_Summary_Ratings_Label' ),
			'styling-review-title-font'					=> get_option( 'EWD_urp_Review_Title_Font' ),
			'styling-review-title-font-size'			=> get_option( 'EWD_urp_Review_Title_Font_Size' ),
			'styling-review-title-font-color'			=> get_option( 'EWD_urp_Review_Title_Font_Color' ),
			'styling-review-title-margin'				=> get_option( 'EWD_urp_Review_Title_Margin' ),
			'styling-review-title-padding'				=> get_option( 'EWD_urp_Review_Title_Padding' ),
			'styling-review-content-font'				=> get_option( 'EWD_urp_Review_Content_Font' ),
			'styling-review-content-font-size'			=> get_option( 'EWD_urp_Review_Content_Font_Size' ),
			'styling-review-content-font-color'			=> get_option( 'EWD_urp_Review_Content_Font_Color' ),
			'styling-review-content-margin'				=> get_option( 'EWD_urp_Review_Content_Margin' ),
			'styling-review-content-padding'			=> get_option( 'EWD_urp_Review_Content_Padding' ),
			'styling-review-date-font'					=> get_option( 'EWD_urp_Review_Postdate_Font' ),
			'styling-review-date-font-size'				=> get_option( 'EWD_urp_Review_Postdate_Font_Size' ),
			'styling-review-date-font-color'			=> get_option( 'EWD_urp_Review_Postdate_Font_Color' ),
			'styling-review-date-margin'				=> get_option( 'EWD_urp_Review_Postdate_Margin' ),
			'styling-review-date-padding'				=> get_option( 'EWD_urp_Review_Postdate_Padding' ),
			'styling-review-score-font'					=> get_option( 'EWD_urp_Review_Score_Font' ),
			'styling-review-score-font-size'			=> get_option( 'EWD_urp_Review_Score_Font_Size' ),
			'styling-review-score-font-color'			=> get_option( 'EWD_urp_Review_Score_Font_Color' ),
			'styling-review-score-margin'				=> get_option( 'EWD_urp_Review_Score_Margin' ),
			'styling-review-score-padding'				=> get_option( 'EWD_urp_Review_Score_Padding' ),
			'styling-summary-stats-color'				=> get_option( 'EWD_urp_Summary_Stats_Color' ),
			'styling-simple-bar-color'					=> get_option( 'EWD_urp_Simple_Bar_Color' ),
			'styling-color-bar-high'					=> get_option( 'EWD_urp_Color_Bar_High' ),
			'styling-color-bar-medium'					=> get_option( 'EWD_urp_Color_Bar_Medium' ),
			'styling-color-bar-low'						=> get_option( 'EWD_urp_Color_Bar_Low' ),
			'styling-review-background-color'			=> get_option( 'EWD_urp_Review_Background_Color' ),
			'styling-review-header-background-color'	=> get_option( 'EWD_urp_Review_Header_Background_Color' ),
			'styling-review-content-background-color'	=> get_option( 'EWD_urp_Review_Content_Background_Color' ),
			'styling-readmore-button-background-color'	=> get_option( 'EWD_urp_Read_More_Button_Background_Color' ),
			'styling-readmore-button-text-color'		=> get_option( 'EWD_urp_Read_More_Button_Text_Color' ),
			'styling-readmore-button-hover-bg-color'	=> get_option( 'EWD_urp_Read_More_Button_Hover_Background_Color' ),
			'styling-readmore-button-hover-text-color'	=> get_option( 'EWD_urp_Read_More_Button_Hover_Text_Color' ),
			'styling-image-style-background-color'		=> get_option( 'EWD_urp_Image_Style_Background_Color' ),
			'styling-circle-graph-background-color'		=> get_option( 'EWD_urp_Circle_Graph_Background_Color' ),
			'styling-circle-graph-fill-color'			=> get_option( 'EWD_urp_Circle_Graph_Fill_Color' ),
			'styling-rating-stars-color'				=> get_option( 'EWD_urp_Rating_Stars_Color' ),
			'styling-verified-checkmark-color'			=> get_option( 'EWD_urp_Verified_Checkmark_Color' ),
			'styling-verified-checkmark-bg-color'		=> get_option( 'EWD_urp_Verified_Checkmark_Background_Color' ),
			'styling-verified-checkmark-text-color'		=> get_option( 'EWD_urp_Verified_Checkmark_Text_Color' ),
			'styling-email-background-color'			=> get_option( 'EWD_urp_Email_Reminder_Background_Color' ),
			'styling-email-inner-color'					=> get_option( 'EWD_urp_Email_Reminder_Inner_Color' ),
			'styling-email-text-color'					=> get_option( 'EWD_urp_Email_Reminder_Text_Color' ),
			'styling-email-button-background-color'		=> get_option( 'EWD_urp_Email_Reminder_Button_Background_Color' ),
			'styling-email-button-text-color'			=> get_option( 'EWD_urp_Email_Reminder_Button_Text_Color' ),
			'styling-email-button-bg-hover-color'		=> get_option( 'EWD_urp_Email_Reminder_Button_Background_Hover_Color' ),
			'styling-email-button-text-hover-color'		=> get_option( 'EWD_urp_Email_Reminder_Button_Text_Hover_Color' )
		);

		add_option( 'ewd-urp-review-ask-time', get_option( 'EWD_URP_Ask_Review_Date' ) );
		add_option( 'ewd-urp-installation-time', get_option( 'EWD_URP_Install_Time' ) );

		update_option( 'ewd-urp-permission-level', get_option( 'EWD_URP_Full_Version' ) == 'Yes' ? 2 : 1 );
		
		update_option( 'ewd-urp-settings', $settings );
	}

	public function convert_review_elements() {

		$old_review_elements = get_option( 'EWD_URP_Review_Categories_Array' );
		$new_review_elements = array();

		foreach ( $old_review_elements as $old_review_element ) {

			$new_element = array(
				'name'			=> $old_review_element['CategoryName'],
				'explanation'	=> $old_review_element['ExplanationAllowed'] == 'Yes' ? true : false,
				'required'		=> $old_review_element['CategoryRequired'] == 'Yes' ? true : false,
				'type'			=> strtolower( $old_review_element['CategoryType'] ),
				'filterable'	=> ! empty( $old_review_element['Filterable'] ) ? strtolower( $old_review_element['Filterable'] ) : '',
				'options'		=> ! empty( $old_review_element['Options'] ) ? $old_review_element['Options'] : '',
			);

			$new_review_elements[] = $new_element;
		}

		return json_encode( $new_review_elements );
	}

	public function convert_product_names() {

		$old_product_names = get_option( 'EWD_URP_Product_Names_Array' );
		$new_product_names = array();

		foreach ( $old_product_names as $old_product_name ) {

			$new_product_name = array(
				'name'	=> $old_product_name['ProductName']
			);

			$new_product_names[] = $new_product_name;
		}

		return json_encode( $new_product_names );
	}

	public function convert_reminders() {

		$old_reminders = get_option( 'EWD_URP_Reminders_Array' );
		$new_reminders = array();

		foreach ( $old_reminders as $old_reminder ) {

			$new_reminder = array(
				'id'				=> $old_reminder['ID'],
				'email-to-send'		=> $old_reminder['Email_To_Send'],
				'reminder-interval'	=> $old_reminder['Reminder_Interval'],
				'reminder-unit'		=> $old_reminder['Reminder_Unit'],
				'status'			=> $old_reminder['Status_Trigger'],
			);

			$new_reminders[] = $new_reminder;
		}

		return json_encode( $new_reminders );
	}

	public function convert_email_messages() {

		$old_email_messages = get_option( 'EWD_URP_Email_Messages_Array' );
		$new_email_messages = array();

		foreach ( $old_email_messages as $old_email_message ) {

			$new_email_message = array(
				'id'		=> $old_email_message['ID'],
				'subject'	=> $old_email_message['Name'],
				'message'	=> $old_email_message['Message'],
			);

			$new_email_messages[] = $new_email_message;
		}

		return json_encode( $new_email_messages );
	}
}

}