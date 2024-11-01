jQuery( document ).ready( function( $ ) {
	jQuery( '.ewd-urp-main-dashboard-review-ask' ).css( 'display', 'block' );

	jQuery(document).on( 'click', '.ewd-urp-main-dashboard-review-ask .notice-dismiss', function( event ) {

  		var params = {
			ask_review_time: '7',
			nonce: ewd_urp_review_ask.nonce,
			action: 'ewd_urp_hide_review_ask'
		};

		var data = jQuery.param( params );
    
    	jQuery.post( ajaxurl, data, function() {} );
	});

	jQuery( '.ewd-urp-review-ask-yes' ).on( 'click', function() {

		jQuery( '.ewd-urp-review-ask-feedback-text' ).removeClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-starting-text' ).addClass( 'ewd-urp-hidden' );

		jQuery( '.ewd-urp-review-ask-no-thanks' ).removeClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-review' ).removeClass( 'ewd-urp-hidden' );

		jQuery( '.ewd-urp-review-ask-not-really' ).addClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-yes' ).addClass( 'ewd-urp-hidden' );

		var params = {
			ask_review_time: '7',
			nonce: ewd_urp_review_ask.nonce,
			action: 'ewd_urp_hide_review_ask'
		};

		var data = jQuery.param( params );

    jQuery.post( ajaxurl, data, function() {} );
	});

	jQuery( '.ewd-urp-review-ask-not-really' ).on( 'click', function() {

		jQuery( '.ewd-urp-review-ask-review-text' ).removeClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-starting-text' ).addClass( 'ewd-urp-hidden' );

		jQuery( '.ewd-urp-review-ask-feedback-form' ).removeClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-actions' ).addClass( 'ewd-urp-hidden' );

		var params = {
			ask_review_time: '1000',
			nonce: ewd_urp_review_ask.nonce,
			action: 'ewd_urp_hide_review_ask'
		};

		var data = jQuery.param( params );

    jQuery.post( ajaxurl, data, function() {} );
	});

	jQuery( '.ewd-urp-review-ask-no-thanks' ).on( 'click', function() {

		var params = {
			ask_review_time: '1000',
			nonce: ewd_urp_review_ask.nonce,
			action: 'ewd_urp_hide_review_ask'
		};

		var data = jQuery.param( params );

    jQuery.post( ajaxurl, data, function() {} );

    jQuery( '.ewd-urp-main-dashboard-review-ask' ).css( 'display', 'none' );
	});

	jQuery( '.ewd-urp-review-ask-review' ).on( 'click', function() {

		jQuery( '.ewd-urp-review-ask-feedback-text' ).addClass( 'ewd-urp-hidden' );
		jQuery( '.ewd-urp-review-ask-thank-you-text' ).removeClass( 'ewd-urp-hidden' );

		var params = {
			ask_review_time: '1000',
			nonce: ewd_urp_review_ask.nonce,
			action: 'ewd_urp_hide_review_ask'
		};

		var data = jQuery.param( params );

    jQuery.post( ajaxurl, data, function() {} );
	});

	jQuery( '.ewd-urp-review-ask-send-feedback' ).on( 'click', function() {
		
		var feedback = jQuery( '.ewd-urp-review-ask-feedback-explanation textarea' ).val();
		var email_address = jQuery( '.ewd-urp-review-ask-feedback-explanation input[name="feedback_email_address"]' ).val();
		var data = 'feedback=' + feedback + '&email_address=' + email_address + '&action=ewd_urp_send_feedback';
        jQuery.post( ajaxurl, data, function() {} );

        var params = {
					ask_review_time: '1000',
					nonce: ewd_urp_review_ask.nonce,
					action: 'ewd_urp_hide_review_ask'
				};

				var data = jQuery.param( params );

        jQuery.post( ajaxurl, data, function() {} );

        jQuery( '.ewd-urp-review-ask-feedback-form' ).addClass( 'ewd-urp-hidden' );
        jQuery( '.ewd-urp-review-ask-review-text' ).addClass( 'ewd-urp-hidden' );
        jQuery( '.ewd-urp-review-ask-thank-you-text' ).removeClass( 'ewd-urp-hidden' );
	});
});