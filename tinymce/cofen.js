jQuery( document ).ready( function () {

	jQuery( '#co_fen_mceupdate' ).live( 'click' , function() {
		if( window.tinyMCE && jQuery( '#co_fen').size() ) {
		        window.tinyMCE.execInstanceCommand( window.tinyMCE.activeEditor.id ,
					'mceInsertContent' , false , jQuery( '#co_fen').val() );
                	tinyMCEPopup.editor.execCommand( 'mceRepaint' );
	                tinyMCEPopup.close();
        	}
	} );

} );
