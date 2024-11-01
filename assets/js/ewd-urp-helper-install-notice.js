jQuery( document ).ready( function( $ ) {

  jQuery(document).on( 'click', '.ewd-urp-helper-install-notice .notice-dismiss', function( event ) {
    var data = jQuery.param({
      action: 'ewd_urp_hide_helper_notice',
      nonce: ewd_urp_helper_notice.nonce
    });

    jQuery.post( ajaxurl, data, function() {} );
  });
});