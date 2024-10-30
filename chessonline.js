jQuery( document ).ready( function () {

	/* Link overlay */

	/* Initiate */
	var overlay = jQuery( '<div id="co_overlay"></div>' );
	overlay.appendTo( document.body );
	var framebox = jQuery( '<div id="co_framebox"><div><div id="co_closeoverlay"><span>Close</span></div><div><iframe id="co_iframe" frameborder="0" scrolling="no" name="co_inlineframe" src="" width="100%" height="100%"></div></iframe></div></div>' );
	framebox.appendTo( document.body );
	jQuery( '.co_overlay' ).live( 'click' , function() {
		var url = jQuery( this ).attr( 'href' );
		jQuery( '#co_iframe' ).attr( 'src' , url );
		jQuery( '#co_overlay' ).show();
		jQuery( '#co_framebox' ).show();
		return false;
	} );
	jQuery( '#co_closeoverlay' ).live( 'click' , function() {
		jQuery( '#co_framebox' ).hide();
                jQuery( '#co_overlay' ).hide();
		jQuery( '#co_iframe' ).attr( 'src' , '' );
	} );

} );
