var Filtering_Running = "No";
jQuery(function(){ //DOM Ready
    URPSetClickHandlers();
    URPSetKeyStrokeCounters();
    URPSetKarmaHandlers();
    URPSetStarHandlers();
    URPSetFilteringHandlers();
    URPSetPaginationHandlers();
    URPSetToggleHandlers();
    URPInfiniteScroll();
    URPFormSubmitHandler();
    URPThumbnailReadMoreAJAXHAndler();
    URPSetClickableSummaryHandler();
    URPSetFlagInappropriateHandler();
    URPPositionElements();
    URPSetWCTabSwitchers();
    URPSetStarRequiredHandlers();

    jQuery('#ewd-urp-ajax-text-input').on('keyup', function() {

    	var shortcode_id = jQuery( this ).data( 'shortcodeid' );

    	URPFilterResults( shortcode_id );
	});

});

function URPSetClickHandlers() {
	jQuery( '.ewd-urp-product-name-text-input' ).on( 'keyup', function() {
		
		if ( ewd_urp_php_data.restrict_product_names ) {

			if ( jQuery.inArray( jQuery( '.ewd-urp-product-name-text-input' ).val(), ewd_urp_php_submit_review_data.product_names ) == -1 ) {

				jQuery( '#ewd-urp-restrict-product-names-message' ).html( 'Please make sure the product name matches exactly.' );
				jQuery( '#ewd-urp-review-submit').prop( 'disabled', true );
			}
			else {

				jQuery( '#ewd-urp-restrict-product-names-message' ).html( '' );
				jQuery( '#ewd-urp-review-submit').prop( 'disabled', false );
			}
		}

		if ( ewd_urp_php_data.autocomplete_product_names ) {

			jQuery( '.ewd-urp-product-name-text-input' ).autocomplete( {
				source: ewd_urp_php_submit_review_data.product_names
			} );
			
			if ( jQuery('.ewd-urp-product-name-text-input').val().length > 1 ) {

				jQuery('.ewd-urp-product-name-text-input').autocomplete( "enable" );
			}
			else {

				jQuery('.ewd-urp-product-name-text-input').autocomplete( "disable" );
			}
		}
	});

	jQuery( '.ewd-urp-review-format-expandable .ewd-urp-review-header' ).on( 'click', function( event ) {
		if (typeof accordionExpandable === 'undefined' || accordionExpandable === null) {accordionExpandable = "No";}

		var review_id = jQuery(this).data('postid');

		var post_id = review_id.substr( review_id.indexOf( '-' ) + 1 );

		if (jQuery('#ewd-urp-review-content-'+review_id).hasClass('ewd-urp-content-hidden')) {var action = 'Open';}
		else {var action = 'Close';}

		if (accordionExpandable == "Yes") {
			jQuery('.ewd-urp-review-content').addClass('ewd-urp-content-hidden');
		}

		if (action == 'Close') {jQuery('#ewd-urp-review-content-'+review_id).addClass('ewd-urp-content-hidden');}
		else {
			jQuery('#ewd-urp-review-content-'+review_id).removeClass('ewd-urp-content-hidden');

			var data = jQuery.param({
				nonce: ewd_urp_php_data.nonce,
				post_id: post_id,
				action: 'ewd_urp_record_view'
			}); console.log( data );
			jQuery.post(ajaxurl, data, function(response) {});
		}

		event.preventDefault();
	});
}

function URPSetKeyStrokeCounters() {
	jQuery( '.ewd-urp-review-textarea' ).on( 'keyup', function() {
		var char_count = jQuery(this).val().length;

		if ( ! ewd_urp_php_data.review_character_limit ) { return; }

		var return_text = "Characters remaining: " + ( parseInt( ewd_urp_php_data.review_character_limit ) - char_count );

		if (char_count > ewd_urp_php_data.review_character_limit) {
			jQuery( '#ewd-urp-review-submit' ).prop( 'disabled', true );
			jQuery( this ).parent().find( '.ewd-urp-review-character-count' ).css( 'color', 'red' );
		}
		else {
			jQuery( '#ewd-urp-review-submit' ).prop( 'disabled', false );
			jQuery( this ).parent().find( '.ewd-urp-review-character-count' ).css( 'color', 'inherit' );
		}

		jQuery( this ).parent().find( '.ewd-urp-review-character-count' ).html( return_text );
	})
}

function URPSetKarmaHandlers() {
	jQuery('.ewd-urp-karma-down').on('click', function() {
		var reviewID = jQuery(this).data('reviewid');
		if (reviewID == "0") {return;}

		URPKarmaAJAX('down', reviewID);

		var currentScore = jQuery('#ewd-urp-karma-score-'+reviewID).html();
		currentScore--;
		jQuery('#ewd-urp-karma-score-'+reviewID).html(currentScore);

		jQuery(this).data('reviewid', '0');
	});

	jQuery('.ewd-urp-karma-up').on('click', function() {
		var reviewID = jQuery(this).data('reviewid');
		if (reviewID == "0") {return;}

		URPKarmaAJAX('up', reviewID);

		var currentScore = jQuery('#ewd-urp-karma-score-'+reviewID).html();
		currentScore++;
		jQuery('#ewd-urp-karma-score-'+reviewID).html(currentScore);

		jQuery(this).data('reviewid', '0');
	});
}

function URPKarmaAJAX(direction, reviewID) {

	var params = {
		nonce: ewd_urp_php_data.nonce,
		direction: direction,
		review_id: reviewID,
		action: 'ewd_urp_update_karma'
	};

	var data = jQuery.param( params );
	jQuery.post(ajaxurl, data, function(response) {});
}

function URPSetStarHandlers() {
	jQuery( '.ewd-urp-star-input' ).on( 'click', function() {
		var score = jQuery( this ).data( 'reviewscore' );

		jQuery( this ).parent().find( '.ewd-urp-star-input' ).each( function() {
			if ( jQuery( this ).data( 'reviewscore' ) <= score ) { jQuery( this ).addClass( 'ewd-urp-star-input-filled' ); }
			else { jQuery( this ).removeClass( 'ewd-urp-star-input-filled' ); }
		});

		jQuery( this ).parent().find( 'input' ).val( score );
	})
}

function URPSetFilteringHandlers() {
	jQuery('.ewd-urp-filtering-toggle').on('click', function() {
		if (jQuery('.ewd-urp-filtering-controls').hasClass('ewd-urp-hidden')) {
			jQuery('.ewd-urp-filtering-controls').removeClass('ewd-urp-hidden');
			jQuery('.ewd-urp-filtering-toggle').removeClass('ewd-urp-filtering-toggle-downcaret');
			jQuery('.ewd-urp-filtering-toggle').addClass('ewd-urp-filtering-toggle-upcaret');
		}
		else {
			jQuery('.ewd-urp-filtering-controls').addClass('ewd-urp-hidden');
			jQuery('.ewd-urp-filtering-toggle').removeClass('ewd-urp-filtering-toggle-upcaret');
			jQuery('.ewd-urp-filtering-toggle').addClass('ewd-urp-filtering-toggle-downcaret');
		}
	});

	jQuery( '.ewd-urp-filtering-select' ).on( 'change', function( event ) {

		var shortcode_id = jQuery( this ).data( 'shortcodeid' );

		jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="current_page"]' ).val( 1 );
		jQuery( '.ewd-urp-reviews-nav[data-shortcodeid="' + shortcode_id + '"] .paging-input .current-page' ).html( 1 );
		
		URPFilterResults( shortcode_id );
	});

    jQuery(".ewd-urp-review-score-filter").slider({
    	range: true,
    	min: 1,
    	max: ewd_urp_php_data.maximum_score,
    	values: [ 1, ewd_urp_php_data.maximum_score ],
        change: function( event, ui ) {
           jQuery(".ewd-urp-score-range").text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
           var Shortcode_ID = jQuery(this).data('shortcodeid');
           URPFilterResults(Shortcode_ID);
        }
    });
}

function URPFilterResults(shortcode_id, AddResults, selectedScore) {

	var search_string = jQuery('#ewd-urp-ajax-text-input[data-shortcodeid="' + shortcode_id + '"]').val();
	search_string = search_string != undefined ? search_string : '';

	// Product Name 
	if ( jQuery( '.ewd-filtering-product-name[data-shortcodeid="' + shortcode_id + '"]' ).val() == '' || jQuery( '.ewd-filtering-product-name[data-shortcodeid="' + shortcode_id + '"]' ).val() == undefined ) {
		
		var product_name = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="product_name"]' ).val();
	}
	else { 

		var product_name = jQuery( '.ewd-filtering-product-name[data-shortcodeid="' + shortcode_id + '"]' ).val();
	}
	product_name = product_name != undefined ? product_name : ''; 

	// Review Author
	if ( jQuery( '.ewd-filtering-review-author[data-shortcodeid="' + shortcode_id + '"]' ).val() == 'All' || jQuery( '.ewd-filtering-review-author[data-shortcodeid="' + shortcode_id + '"]' ).val() == undefined ) {

		var review_author = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="review_author"]' ).val();
	}
	else {

		var review_author = jQuery( '.ewd-filtering-review-author[data-shortcodeid="' + shortcode_id + '"]' ).val();
	}
	review_author = review_author != undefined ? review_author : '';

	// Custom Field Filters
	if (jQuery( '.ewd-urp-custom-filter[data-shortcodeid="' + shortcode_id + '"]' ).length == 0) {

		var custom_filters = jQuery('#urp-custom-filters[data-shortcodeid="' + shortcode_id + '"]').val();
	}
	else {

		var custom_filters_array = {};

		jQuery('.ewd-urp-custom-filter[data-shortcodeid="' + shortcode_id + '"]').each(function() {

			custom_filters_array[ jQuery( this ).data( 'fieldname' ) ] = jQuery( this ).val();
		});

		var custom_filters = JSON.stringify(custom_filters_array);
	}
	custom_filters = custom_filters != undefined ? custom_filters : '';

	// Review Score
	var values = jQuery( '.ewd-urp-review-score-filter[data-shortcodeid="' + shortcode_id + '"]' ).slider( 'option', 'values' );
	var min_score = values[0];
	var max_score = values[1];
	if (min_score == undefined) {min_score = 0;}
	if (max_score == undefined) {max_score = 1000000;}
	if (selectedScore !== undefined) {min_score = selectedScore;}
	if (selectedScore !== undefined) {max_score = selectedScore;}

	// Ordering
	var orderby = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="orderby"]' ).val();
	orderby = orderby != undefined ? orderby : '';

	var order = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="order"]' ).val();
	order = order != undefined ? order : '';
	
	// Pagination
	var current_page = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="current_page"]' ).val();
	current_page = current_page != undefined ? current_page : 1;

	var post_count = jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="post_count"]' ).val();
	post_count = post_count != undefined ? post_count : 1;

	if ( AddResults == 'Yes' ) { jQuery( '.ewd-urp-reviews-nav[data-shortcodeid="' + shortcode_id + '"]' ).last().after( '<span class=\'ewd-urp-retrieving-results\'>Retrieving results...</span>' ); }
	else {jQuery('.ewd-urp-reviews-container[data-shortcodeid="' + shortcode_id + '"]').html('<h3>Retrieving results...</h3>');}

	var params = {
		nonce: ewd_urp_php_data.nonce,
		action: 'ewd_urp_search',
		only_reviews: 'Yes',
		post_count: post_count,
		current_page: current_page,
		order: order,
		orderby: orderby,
		review_max_score: max_score,
		review_min_score: min_score,
		custom_filters: custom_filters,
		shortcode_id: shortcode_id,
		search_string: search_string,
		product_name: encodeURIComponent( product_name ),
		review_author: review_author
	};

	var data = jQuery.param( params );
	jQuery.post( ajaxurl, data, function(response) {

	    if (AddResults == "Yes") {

	    	jQuery( '.ewd-urp-retrieving-results' ).remove();
	    	jQuery( '.ewd-urp-reviews-nav[data-shortcodeid="' + shortcode_id + '"]' ).last().before( response.data.output );
	    }
	    else {

	    	jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] .ewd-urp-reviews-container' ).html( response.data.output );

	    	jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] .displaying-num' ).html( response.data.reviews_count );

	    	jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] input[name="max_page"]' ).val( response.data.max_page );
	    	jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] .total-pages' ).html( response.data.max_page );

	    	if ( response.data.max_page == 1 ) {

	    		jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] .pagination-links' ).addClass( 'ewd-urp-hidden' );
	    	}
	    	else {

	    		jQuery( '.ewd-urp-reviews[data-shortcodeid="' + shortcode_id + '"] .pagination-links' ).removeClass( 'ewd-urp-hidden' );
	    	}
	    }

	    URPSetClickHandlers();
	    URPSetPaginationHandlers();
	    URPSetKarmaHandlers();
	    URPThumbnailReadMoreAJAXHAndler();
	    URPSetFlagInappropriateHandler();
	    URPPositionElements();
	    URPSetClickableSummaryHandler();
		ewd_urp_masonry();

	    Filtering_Running = "No";
	});
}

function URPSetPaginationHandlers() {

	jQuery('.ewd-urp-page-control').off( 'click' );
	jQuery('.ewd-urp-page-control').on( 'click', function() {

		var action = jQuery(this).data('controlvalue');
		if ( action == 'first' ) { jQuery( '#ewd-urp-current-page' ).val( 1 );}
		if ( action == 'back' ) { jQuery( '#ewd-urp-current-page' ).val( Math.max( 1, jQuery( '#ewd-urp-current-page' ).val() - 1 ) );}
		if ( action == 'next' ) { jQuery( '#ewd-urp-current-page' ).val( Math.min( jQuery( '#ewd-urp-max-page' ).val(), parseInt( jQuery( '#ewd-urp-current-page' ).val() ) + 1 ) ); }
		if ( action == 'last' ) { jQuery( '#ewd-urp-current-page' ).val( jQuery( '#ewd-urp-max-page' ).val() );}

		var shortcode_id = jQuery(this).data( 'shortcodeid' );

		jQuery( '.ewd-urp-reviews-nav[data-shortcodeid="' + shortcode_id + '"] .paging-input .current-page' ).html( jQuery( '#ewd-urp-current-page' ).val() );

		URPFilterResults( shortcode_id );
	});
}

function URPSetToggleHandlers() {
	jQuery('.ewd-urp-submit-review-toggle').on('click', function() {
		jQuery('.ewd-urp-submit-review-toggle').addClass('ewd-urp-content-hidden');
		jQuery('.ewd-urp-review-form').removeClass('ewd-urp-form-hidden');
	})
}

function URPInfiniteScroll() {
	if ( jQuery( '.ewd-urp-reviews' ).hasClass( 'ewd-urp-infinite-scroll' ) ) {
		jQuery( window ).scroll(function(){
			var infinitepos = jQuery( '.ewd-urp-reviews-nav' ).last().offset();
			if ( infinitepos != undefined && jQuery( '#ewd-urp-max-page' ).val() != parseInt( jQuery( '#ewd-urp-current-page' ).val() ) ) {
				//console.log("scrollTop:" + jQuery(window).scrollTop() + "\nDoc Height: " + jQuery(document).height() + "\nInfinite Top: " + infinitepos.top + "\nWindow Height: " + jQuery(window).height());
				if  ( ( jQuery( window ).height() + jQuery( window ).scrollTop() > infinitepos.top ) && Filtering_Running == "No" ) {
					jQuery( '#ewd-urp-current-page' ).val( Math.min( jQuery( '#ewd-urp-max-page' ).val(), parseInt( jQuery( '#ewd-urp-current-page' ).val() ) + 1 ) );
					Filtering_Running = "Yes";
					var add_results = "Yes";
					var shortcode_id = jQuery( '.ewd-urp-reviews-nav' ).data( 'shortcodeid' );
					URPFilterResults( shortcode_id, add_results );
				}
			}
		});
	}
}

function URPFormSubmitHandler() {
	jQuery('#review_order').on('submit', function(e) {
		if (jQuery('#ewd-urp-overall-score').val() == "") {
			jQuery('.ewd-urp-submit').prepend("<div id='ewd-urp-submit-warning'>Please select a score before submitting the form.</div>");
			setTimeout(function() {jQuery('#ewd-urp-submit-warning').remove()}, 5000);
			e.preventDefault();
		}
	});
}

function URPThumbnailReadMoreAJAXHAndler() {

	jQuery('.ewd-urp-thumbnail-read-more').on('click', function( event ) {

		var unique_id = jQuery( this ).data('postid');
		var review_id = unique_id.substring( unique_id.lastIndexOf( '-' ) + 1 );
		jQuery( "#ewd-urp-review-" + unique_id ).html('Retrieving full review...');
		
		jQuery(this).remove();

		var data = jQuery.param({
			nonce: ewd_urp_php_data.nonce,
			review_id: review_id,
			action: 'ewd_urp_get_review_body'
		});
		jQuery.post(ajaxurl, data, function(response) {
			jQuery( "#ewd-urp-review-" + unique_id ).html( response );
			URPReadLessHandler();
		});

		event.preventDefault();
	});
}

function URPReadLessHandler() {
	jQuery('.ewd-urp-ajax-read-less').off();
	jQuery('.ewd-urp-ajax-read-less').on('click', function() {
		var Thumbnail_Chars = jQuery(this).data('thumbnailchars');
		var Parent_Span = jQuery(this).parent();

		jQuery(this).remove();
		Parent_Span.html(Parent_Span.html().substring(0, Thumbnail_Chars));
	});
}

function URPSetClickableSummaryHandler() {
	jQuery('.ewd-urp-summary-clickable').on('click', function() {
		var selectedScore = jQuery(this).data('reviewscore'); 
		var values = jQuery('#ewd-urp-review-score-filter').slider("option", "values"); 
		var Shortcode_ID = jQuery(this).data('shortcodeid');
		if (values[0] !== undefined) {
			jQuery('.ewd-urp-review-score-filter').slider("values", 0, selectedScore);
			jQuery('.ewd-urp-review-score-filter').slider("values", 1, selectedScore);
		}
		else {
			jQuery(this).parent().append("<div class='ewd-urp-remove-score-filter'>Show all reviews</div>");
			URPFilterResults(Shortcode_ID, "No", selectedScore);
		}
	});

	jQuery('.ewd-urp-remove-score-filter').on('click', function() {
		var Shortcode_ID = jQuery(this).parent().data('shortcodeid');

		jQuery(this).remove();
		URPFilterResults(Shortcode_ID);
	});
}

function URPSetFlagInappropriateHandler() {
	jQuery('.ewd-urp-flag-inappropriate').off('click');
	jQuery('.ewd-urp-flag-inappropriate').on('click', function() {
		if (jQuery(this).hasClass('ewd-urp-content-flagged')) {return;}

		jQuery(this).addClass('ewd-urp-content-flagged');

		var review_id = jQuery(this).data('reviewid');

		var data = jQuery.param({
			nonce: ewd_urp_php_data.nonce,
			review_id: review_id,
			action: 'ewd_urp_flag_inappropriate'
		});
		jQuery.post(ajaxurl, data, function(response) {});
	});
}

function URPPositionElements() {
  if(ewd_urp_php_data.flag_inappropriate_enabled == "1"){
    jQuery('.ewd-urp-flag-inappropriate').show();
  }
  jQuery('.ewd-urp-review-header').each(function(){
    var thisReview = jQuery(this);
    var reviewScoreHeight = thisReview.find('.ewd-urp-review-score').height();
    var reviewScoreTextHeight = thisReview.find('.ewd-urp-review-score-number').height();
    var reviewScoreTextMargin = (reviewScoreHeight - reviewScoreTextHeight) / 2;
    var reviewFlagMargin = (reviewScoreHeight / 2) - 9;
    var reviewTitleTextHeight = thisReview.find('.ewd-urp-review-link').height();
    var reviewTitleTextMargin = (reviewScoreHeight - reviewTitleTextHeight) / 2;
    thisReview.find('.ewd-urp-review-score-number').css('margin-top', reviewScoreTextMargin+'px');
    thisReview.find('.ewd-urp-flag-inappropriate').css('margin-top', reviewFlagMargin+'px');
    thisReview.find('.ewd-urp-review-link').css('margin-top', reviewTitleTextMargin+'px');
  });
}

function URPSetWCTabSwitchers() {
	jQuery('.ewd-urp-wc-tab-title').on('click', function() {
		jQuery('.ewd-urp-wc-tab-title').removeClass('ewd-urp-wc-active-tab-title');
		jQuery(this).addClass('ewd-urp-wc-active-tab-title');
		
		jQuery('.ewd-urp-wc-tab').removeClass('ewd-urp-wc-active-tab');
		var tab = jQuery(this).data('tab');
		jQuery('[data-tab="' + tab + '"]').addClass('ewd-urp-wc-active-tab');
	});
}

function URPSetStarRequiredHandlers() {
	jQuery('.ewd-urp-review-form form').on('submit', function(event) {
		var $current_form = jQuery(this);
		$current_form.find('.ewd-urp-hidden.required').each(function(index, el) {
			if (jQuery(this).val() == "") {
				event.preventDefault();
				if( jQuery(this).parent().find('span.urp-required').length < 1) {
					jQuery(this).parent().prepend('<span class="urp-required">Please select a score</span>').focus();
				}
			}
			else if( jQuery(this).parent().find('span.urp-required').length > 0 ) {
				jQuery(this).parent().find('span.urp-required').first().remove();
			}
		});

		$current_form.find('.ewd-urp-submit-review-radio-checkbox-each input[type="checkbox"]').each(function(index, el) {
			if (jQuery(this).data('required') == 'required') {
				var name = jQuery(this).attr('name');
				if ( jQuery('input[name="' + name + '"]:checked').length < 1 ) {
					event.preventDefault();
					if ( jQuery(this).parent().parent().find('span.urp-required').length < 1) {
						jQuery(this).parent().parent().prepend('<span class="urp-required">Please select at least one checkbox option</span>').focus();
					}
				}
				else if( jQuery(this).parent().parent().find('span.urp-required').length > 0 ) {
					jQuery(this).parent().parent().find('span.urp-required').first().remove();
				}
			}
		});
	});
}


function ewd_urp_masonry() {
	jQuery('.ewd-urp-individual-reviews-container').each(function(){
		var these_reviews = jQuery(this);
		if ( these_reviews.find('.ewd-urp-review-format-thumbnail_masonry').length || these_reviews.find('.ewd-urp-review-format-image_masonry').length ) {
			these_reviews.masonry({
				itemSelector: '.ewd-urp-review-format-thumbnail_masonry, .ewd-urp-review-format-image_masonry',
			});
		}
	});
}

function ewd_urp_bar_width() {
	if ( jQuery('.ewd-urp-color-bar').length || jQuery('.ewd-urp-simple-bar').length ) {
		jQuery('.ewd-urp-review-graphic').css('width', '110px');
		jQuery('.ewd-urp-review-graphic').css('height', '23px');
	}
}

jQuery(document).ready(function() {
	ewd_urp_masonry();
	ewd_urp_bar_width();
});