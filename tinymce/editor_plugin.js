( function() {

	tinymce.create( 'tinymce.plugins.coFEN' , {
		init : function( ed , url ) {
			ed.addCommand( 'mcecoFEN' , function() {
				ed.windowManager.open({
					id: 'co_fen_mce',
					width: 760,
					title: 'ChessOnline FEN Editor',
					height: 'auto',
					wpDialog: true,
				}, {
					plugin_url : url 
				});
				jQuery( '#co_fen').co_LoadFEN( ed.selection.getContent( { format : 'raw' } ).replace( /!/g , '' ) );
			});
			ed.addButton( 'coFEN', {
				title : 'FEN Editor',
				cmd : 'mcecoFEN',
				image : url + '/cofen.png'
			});
			ed.onNodeChange.add( function( ed , cm , n ) {
				cm.setActive( 'coFEN' , n.nodeName == 'IMG' );
			});
		},
		createControl : function( n , cm ) {
			return null;
		},
		getInfo : function() {
			return {
					longname  : 'coFEN',
					author 	  : 'Alexander Mainzer',
					authorurl : 'http://www.w3edv.de',
					infourl   : 'http://chessonline.w3edv.de',
					version   : '1.0'
			};
		}
	});

	tinymce.PluginManager.add( 'coFEN' , tinymce.plugins.coFEN );
})();
