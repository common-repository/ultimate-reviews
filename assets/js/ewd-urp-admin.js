/*************************************************************************
ADD SEND SAMPLE EMAIL BUTTON
**************************************************************************/
jQuery(document).ready(function($) {
	
	var sample_email_address_description = $( "p:contains('Choose an email address to send the above sample email message to.')" );

	var sample_email_send_button = '<p><button type="button" class="ewd-urp-send-test-email">Send Sample Email</button></p>';

	$( sample_email_send_button ).insertAfter( sample_email_address_description );

	jQuery('.ewd-urp-send-test-email').on('click', function() {

		jQuery('.ewd-urp-test-email-response').remove();

		var email_address = jQuery('input[name="ewd-urp-settings[send-sample-email-address]"]').val();
		var email_to_send = jQuery('#send-sample-email-message').val();

		if (email_address == "" || email_to_send == "") {
			jQuery('.ewd-urp-send-test-email').after('<div class="ewd-urp-test-email-response">Error: Select an email and enter an email address before sending.</div>');
		}

		var params = {
			email_address: email_address,
			email_to_send: email_to_send,
			nonce: ewd_urp_admin_php_data.nonce,
			action: 'ewd_urp_send_test_email'
		};

		var data = jQuery.param( params );

        jQuery.post(ajaxurl, data, function(response) {

        	jQuery('.ewd-urp-send-test-email').after(response);
        });
	});
});


// Hack to deal with 'default' field type
jQuery(document).ready(function($) {

	$( '.sap-infinite-table select[data-name="type"]' ).each( function () {

		if ( $( this ).parent().parent().hasClass( 'sap-infinite-table-row-template' ) ) { 

			$( this ).find( 'option[value="default"]' ).remove(); 
		}
		else if ( $( this ).val() == 'default' ) { 

			$( this ).prop( 'disabled', true );

			$( this ).parent().parent().find( '.sap-infinite-table-row-delete' ).remove(); 
			
			$( this ).parent().parent().find( 'input[data-name="name"]' ).prop( 'disabled', true ); 

			$( this ).parent().parent().find( 'select[data-name="explanation"]' ).prop( 'disabled', true ); 
		}
		else { 

			$( this ).find( 'option[value="default"]' ).remove(); 
		}
	});
});

//NEW DASHBOARD MOBILE MENU AND WIDGET TOGGLING
jQuery(document).ready(function($){
	$('#ewd-urp-dash-mobile-menu-open').click(function(){
		$('.ewd-urp-admin-header-menu .nav-tab:nth-of-type(1n+2)').toggle();
		$('#ewd-urp-dash-mobile-menu-up-caret').toggle();
		$('#ewd-urp-dash-mobile-menu-down-caret').toggle();
		return false;
	});
	$(function(){
		$(window).resize(function(){
			if($(window).width() > 785){
				$('.ewd-urp-admin-header-menu .nav-tab:nth-of-type(1n+2)').show();
			}
			else{
				$('.ewd-urp-admin-header-menu .nav-tab:nth-of-type(1n+2)').hide();
				$('#ewd-urp-dash-mobile-menu-up-caret').hide();
				$('#ewd-urp-dash-mobile-menu-down-caret').show();
			}
		}).resize();
	});	
	$('#ewd-urp-dashboard-support-widget-box .ewd-urp-dashboard-new-widget-box-top').click(function(){
		$('#ewd-urp-dashboard-support-widget-box .ewd-urp-dashboard-new-widget-box-bottom').toggle();
		$('#ewd-urp-dash-mobile-support-up-caret').toggle();
		$('#ewd-urp-dash-mobile-support-down-caret').toggle();
	});
	$('#ewd-urp-dashboard-optional-table .ewd-urp-dashboard-new-widget-box-top').click(function(){
		$('#ewd-urp-dashboard-optional-table .ewd-urp-dashboard-new-widget-box-bottom').toggle();
		$('#ewd-urp-dash-optional-table-up-caret').toggle();
		$('#ewd-urp-dash-optional-table-down-caret').toggle();
	});
});


/*************************************************************************
WC TAB UWPM BANNER
**************************************************************************/
jQuery(document).ready(function($) {

	if ( ! ewd_urp_admin_php_data.ewd_uwpm_display ) { return; }
	
	var review_reminders_h2 = $( "h2:contains('Review Reminder Emails')" );

	var uwpm_banner_content = '<div class="ewd-urp-uwpm-banner">';
    	uwpm_banner_content += '<div class="ewd-urp-uwpm-banner-remove"><span>X</span></div>';
    	uwpm_banner_content += '<div class="ewd-urp-uwpm-banner-icon">';
			uwpm_banner_content += '<img src="../wp-content/plugins/ultimate-reviews/assets/img/ewd-uwpm-icon.png">';
		uwpm_banner_content += '</div>';
    	uwpm_banner_content += '<div class="ewd-urp-uwpm-banner-text">';
			uwpm_banner_content += '<div class="ewd-urp-uwpm-banner-title">';
            	uwpm_banner_content += 'Customize Your Emails With <span>Ultimate WP Mail</span>';
			uwpm_banner_content += '</div>';
			uwpm_banner_content += '<ul>';
            	uwpm_banner_content += '<li>Completely FREE</li>';
				uwpm_banner_content += '<li>Uses Shortcodes and Variables</li>';
				uwpm_banner_content += '<li>Integrates Seamlessly</li>';
				uwpm_banner_content += '<li>Custom Subject Lines For Each Email</li>';
				uwpm_banner_content += '<li>Visual Builder</li>';
				uwpm_banner_content += '<li>An Easy Email Experience</li>';
			uwpm_banner_content += '</ul>';
			uwpm_banner_content += '<div class="ewd-urp-clear"></div>';
		uwpm_banner_content += '</div>';
		uwpm_banner_content += '<div class="ewd-urp-uwpm-banner-buttons">';
        	uwpm_banner_content += '<a target="_blank" class="ewd-urp-uwpm-banner-download-button" href="plugin-install.php?s=ultimate+wp+mail&amp;tab=search&amp;type=term">Download Now</a>';
			uwpm_banner_content += '<span class="ewd-urp-uwpm-banner-reminder">Remind Me Later</span>';
		uwpm_banner_content += '</div>';
		uwpm_banner_content += '<div class="ewd-urp-clear"></div>';
	uwpm_banner_content += '</div>';

	$( uwpm_banner_content ).insertAfter( review_reminders_h2 );

	jQuery( '.ewd-urp-uwpm-banner-remove' ).on( 'click', function() {

		jQuery( '.ewd-urp-uwpm-banner' ).addClass( 'ewd-urp-hidden' );

		var params = {
			hide_length: '999',
			nonce: ewd_urp_admin_php_data.nonce,
			action: 'ewd_urp_hide_uwpm_banner'
		};

		var data = jQuery.param( params );

		jQuery.post(ajaxurl, data, function(response) {});
	});

	jQuery('.ewd-urp-uwpm-banner-reminder').on('click', function() {

		jQuery( '.ewd-urp-uwpm-banner' ).addClass( 'ewd-urp-hidden' );

		var params = {
			hide_length: '7',
			nonce: ewd_urp_admin_php_data.nonce,
			action: 'ewd_urp_hide_uwpm_banner'
		};

		var data = jQuery.param( params );
		
		jQuery.post(ajaxurl, data, function(response) {});
	});
});


// About Us Page
jQuery( document ).ready( function( $ ) {

	jQuery( '.ewd-urp-about-us-tab-menu-item' ).on( 'click', function() {

		jQuery( '.ewd-urp-about-us-tab-menu-item' ).removeClass( 'ewd-urp-tab-selected' );
		jQuery( '.ewd-urp-about-us-tab' ).addClass( 'ewd-urp-hidden' );

		var tab = jQuery( this ).data( 'tab' );

		jQuery( this ).addClass( 'ewd-urp-tab-selected' );
		jQuery( '.ewd-urp-about-us-tab[data-tab="' + tab + '"]' ).removeClass( 'ewd-urp-hidden' );
	} );

	jQuery( '.ewd-urp-about-us-send-feature-suggestion' ).on( 'click', function() {

		var feature_suggestion = jQuery( '.ewd-urp-about-us-feature-suggestion textarea' ).val();
		var email_address = jQuery( '.ewd-urp-about-us-feature-suggestion input[name="feature_suggestion_email_address"]' ).val();
	
		var params = {};

		params.nonce  				= ewd_urp_admin_php_data.nonce;
		params.action 				= 'ewd_urp_send_feature_suggestion';
		params.feature_suggestion	= feature_suggestion;
		params.email_address 		= email_address;

		var data = jQuery.param( params );
		jQuery.post( ajaxurl, data, function() {} );

		jQuery( '.ewd-urp-about-us-feature-suggestion' ).prepend( '<p>Thank you, your feature suggestion has been submitted.' );
	} );
} );


//SETTINGS PREVIEW SCREENS

jQuery( document ).ready( function() {

	jQuery( '.ewd-urp-settings-preview' ).prevAll( 'h2' ).hide();
	jQuery( '.ewd-urp-settings-preview' ).prevAll( '.sap-tutorial-toggle' ).hide();
	jQuery( '.ewd-urp-settings-preview .sap-tutorial-toggle' ).hide();
});
