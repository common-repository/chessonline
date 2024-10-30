jQuery( document ).ready( function () {

	/* FEN Editor */

	/* Initiate */
	if( jQuery( '#co_feneditor' ).size() ) {

	        var fentouni = { P: '&#9817;' , p: '&#9823;' , R: '&#9814;' , r: '&#9820;' , N: '&#9816;' , n: '&#9822;' ,
				B: '&#9815;' , b: '&#9821;' , Q: '&#9813;' , q: '&#9819;' , K: '&#9812;' ,  k: '&#9818;' , ' ': '' }
		var unitofen = { 9817: 'P' , 9823: 'p' , 9814: 'R' , 9820: 'r' , 9816: 'N' , 9822: 'n' ,
        	               		9815: 'B' , 9821: 'b' , 9813: 'Q' , 9819: 'q' , 9812: 'K' ,  9818: 'k' }
		if( isFen( jQuery( '#co_fen' ).val() ) ) {
			jQuery( '#co_fen' ).addClass( 'co_box_ok' );
		} else {
			jQuery( '#co_fen' ).addClass( 'co_box_error' );
			jQuery( '#co_loadfen' ).addClass( 'co_box_error' );
		}
		jQuery( '#co_sidetomove' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_castlingwk' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_castlingwq' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_castlingbk' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_castlingbq' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_ep' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_halfmove' ).live( 'change' , function() { MakeFEN(); } );
		jQuery( '#co_fullmove' ).live( 'change' , function() { MakeFEN(); } );
	        jQuery( '#co_halfmove' ).live( 'keyup' , function() { MakeFEN(); } );
	        jQuery( '#co_fullmove' ).live( 'keyup' , function() { MakeFEN(); } );
		jQuery( '#co_makefen' ).live( 'click' , function() { MakeFEN(); } );
		jQuery( '#co_loadfen' ).live( 'click' , function() { LoadFEN(); } );
		jQuery( '#co_fen' ).live( 'change' , function() { LoadFEN(); } );
		jQuery( '#co_fen' ).live( 'keyup' , function() { LoadFEN(); } );
		jQuery( '#co_initfen' ).live( 'click' , function() { LoadFEN( 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1' ); } );
		jQuery( '#co_resetfen' ).live( 'click' , function() { LoadFEN( '8/8/8/8/8/8/8/8 w - - 0 1' ); } );
	
		/* Make pieces draggable */
		jQuery( '.co_piece' ).live( 'mouseover', function() {
			if ( !jQuery( this ).data( 'init' ) ) {
				jQuery( this ).data( 'init', true ); 
				jQuery( this ).draggable( {
					revert: true,
					revertDuration: 0,
					stop: function( event , ui ) { 
						jQuery( this ).html( '' );
						MakeFEN();
					} 
				} );
			}
		} );
		jQuery( '.co_piecerepo' ).draggable( {
        	        revert: true,
                	revertDuration: 0,
			stop: function( event , ui ) {
				MakeFEN();
			}
	        } );
		jQuery( '.co_square_drop' ).droppable( {
			drop: function( event, ui ) {
				jQuery( this ).html( '<div class="co_piece">' + ui.draggable.html() + '</div>' );
			}
		} );
	}

	/* Generate and Update FEN */
	function MakeFEN() {
		var fen = '';
		var piece = '';
		for( var i = 8 ; i >= 1 ; i-- ) {
			for( var j = 1 ; j <= 8 ; j++ ) {
				if( jQuery( '#co_square' + i + j ).find( 'div' ).html() != '' ) {
					piece = unitofen[ jQuery( '#co_square' + i + j ).find( 'div' ).html().charCodeAt( 0 ) ];
				} else {
				piece = ' ';
				}
				fen = fen + piece;
			}
			if( i != 1 ) { fen = fen + '/'; }
		}
		fen = fen.replace( /        /g , '8' );
		fen = fen.replace( /       /g , '7' );
		fen = fen.replace( /      /g , '6' );
		fen = fen.replace( /     /g , '5' );
		fen = fen.replace( /    /g , '4' );
		fen = fen.replace( /   /g , '3' );
		fen = fen.replace( /  /g , '2' );
		fen = fen.replace( / /g , '1' );
		fen = fen + ' ' + jQuery( '#co_sidetomove' ).val();
		var castling = '';
		if( jQuery( '#co_castlingwk' ).val() != '-' ) { castling = castling + 'K'; }
		if( jQuery( '#co_castlingwq' ).val() != '-' ) { castling = castling + 'Q'; }
		if( jQuery( '#co_castlingbk' ).val() != '-' ) { castling = castling + 'k'; }
		if( jQuery( '#co_castlingbq' ).val() != '-' ) { castling = castling + 'q'; }
		if( castling == '' ) { castling = '-'; }
		fen = fen + ' ' + castling;
		fen = fen + ' ' + jQuery( '#co_ep' ).val();
		var halfmove = Math.min( 999 , Math.max( 0 , ( jQuery( '#co_halfmove' ).val().replace( /[^0-9]*/g , '' ) * 1 ) ) );
		fen = fen + ' ' + halfmove;
		jQuery( '#co_halfmove' ).co_Val( halfmove );
		var fullmove = Math.min( 999 , Math.max( 1 , ( jQuery( '#co_fullmove' ).val().replace( /[^0-9]*/g , '' ) * 1 ) ) );
		fen = fen + ' ' + fullmove;
		jQuery( '#co_fullmove' ).co_Val( fullmove );
		jQuery( '#co_fen' ).val( fen );
		jQuery( '#co_fen' ).addClass( 'co_box_ok' ).removeClass( 'co_box_error' );
                jQuery( '#co_loadfen' ).removeClass( 'co_box_error' );
	}

	/* Load FEN */
	function LoadFEN( fen ) {
		if( !fen ) { fen =  jQuery( '#co_fen' ).val(); }
		fen = fen.replace( /^\s+/ , '' ).replace( /\s[\s]+/g , ' ' );
                jQuery( '#co_fen' ).co_Val( fen );
		if( !isFen( fen ) ) {
			jQuery( '#co_fen' ).removeClass( 'co_box_ok' ).addClass( 'co_box_error' );
			jQuery( '#co_loadfen' ).addClass( 'co_box_error' );
			return false;
		}
		fen = fen.replace( /[\s]+$/ , ' ' );
		jQuery( '#co_fen' ).co_Val( fen );
		var sFen = fen.split( ' ' );
       	        eFen = sFen[ 0 ].replace( /8/g , '        ' );
               	eFen = eFen.replace( /7/g , '       ' );
                eFen = eFen.replace( /6/g , '      ' );
       	        eFen = eFen.replace( /5/g , '     ' );
               	eFen = eFen.replace( /4/g , '    ' );
                eFen = eFen.replace( /3/g , '   ' );
       	        eFen = eFen.replace( /2/g , '  ' );
               	eFen = eFen.replace( /1/g , ' ' );
		eFen = eFen.replace( /\//g , '' );
		var piece = '';
		for( var i = 8 ; i >= 1 ; i-- ) {
                        for( var j = 1 ; j <= 8 ; j++ ) {
				piece = eFen[ (8 - i ) * 8 + j - 1 ];
				jQuery( '#co_square' + i + j ).find( 'div' ).html( fentouni[ piece ] ); 
                        }
       	        }
		jQuery( '#co_sidetomove' ).val( sFen[ 1 ] );
		jQuery( '#co_castlingwk' ).val( sFen[ 2 ].match( /K/ ) ? 'K' : '-' );
		jQuery( '#co_castlingwq' ).val( sFen[ 2 ].match( /Q/ ) ? 'Q' : '-' );
		jQuery( '#co_castlingbk' ).val( sFen[ 2 ].match( /k/ ) ? 'k' : '-' );
		jQuery( '#co_castlingbq' ).val( sFen[ 2 ].match( /q/ ) ? 'q' : '-' );
		jQuery( '#co_ep' ).val( sFen[ 3 ] );
		jQuery( '#co_halfmove' ).val( sFen[ 4 ] * 1 );
		jQuery( '#co_fullmove' ).val( sFen[ 5 ] * 1 );
		jQuery( '#co_fen' ).addClass( 'co_box_ok' ).removeClass( 'co_box_error' );
		jQuery( '#co_loadfen' ).removeClass( 'co_box_error' );
	}

	/* Check if FEN is valid */
	function isFen( fen ) {
		var sFen = fen.split( ' ' );
		for( var i = 0 ; i <= 5 ; i++ ){ if( !sFen[ i ] ) { return false; } }
		eFen = sFen[ 0 ].replace( /8/g , '        ' );
       	        eFen = eFen.replace( /7/g , '       ' );
               	eFen = eFen.replace( /6/g , '      ' );
                eFen = eFen.replace( /5/g , '     ' );
       	        eFen = eFen.replace( /4/g , '    ' );
               	eFen = eFen.replace( /3/g , '   ' );
                eFen = eFen.replace( /2/g , '  ' );
       	        eFen = eFen.replace( /1/g , ' ' );
		eFen = eFen.replace( /\//g , '' );
		if( !eFen.match( /^[ pbnrqkPBNRQK]{64}$/) ) { return false; }
		if( !sFen[ 1 ].match( /^[wb]$/ ) ) { return false; }
		if( !sFen[ 2 ].match( /^(K[Q]{0,1}[k]{0,1}[q]{0,1})|(Q[k]{0,1}[q]{0,1})|(k[q]{0,1})|q|\-$/ ) ) { return false; }
		if( !sFen[ 3 ].match( /^\-|((a|b|c|d|e|f|g|h)(3|6))$/ ) ) { return false; }
		if( sFen[ 4 ].toString().search( /^[1-9][0-9]*$/ ) != 0 && sFen[ 4 ] != '0' ) { return false; }
		if( sFen[ 5 ].toString().search( /^[1-9][0-9]*$/ ) != 0 ) { return false; }
		return true;
	}

	/* Caret helpers */
        jQuery.fn.extend( {
                co_GetCaret: function() {
                        var obj = ( typeof jQuery( this ).get( 0 ).name != 'undefined' ) ?  obj = jQuery( this ).get( 0 ) : obj;
                        if( jQuery.browser.msie ) {
                                obj.focus();
                                var range = document.selection.createRange();
                                range.moveStart( 'character' , -obj.value.toString().length );
                                return range.text.length;
                        } else if( jQuery.browser.mozilla || jQuery.browser.webkit ) {
                                return obj.selectionStart;
                        }
                        return false;
                },
                co_SetCaret: function( val ) {
                        var obj = ( typeof jQuery( this ).get( 0 ).name != 'undefined' ) ?  obj = jQuery( this ).get( 0 ) : obj;
                        if( jQuery.browser.msie ) {
                                var range = obj.createTextRange();
                                range.collapse( true );
                                range.moveEnd( 'character' , val );
                                range.moveStart( 'character' , val);
                                range.select();
                        } else if( jQuery.browser.mozilla || jQuery.browser.webkit ) {
                                obj.setSelectionRange( val , val);
                        }
                },
                co_Val: function( val ) {
                        val = val + '';
                        var oldval = jQuery( this ).val().toString();
                        if( val !== oldval ) {
                                var activeObj = document.activeElement;
                                var caret = jQuery( this ).co_GetCaret();
                                if( caret !== false ) {
                                        caret = Math.max( 0 , caret - ( ( oldval ).toString().length - ( val ).toString().length ) );
                                        jQuery( this ).val( val.toString() );
                                        jQuery( this ).co_SetCaret( caret );
                                } else {
                                        jQuery( this ).val( val.toString() );
                                }
                                if( activeObj.tagName == 'INPUT' || activeObj.tagName == 'TEXTAREA' ) { activeObj.focus(); }
                        }
                },
		co_LoadFEN: function( fen ) {
			LoadFEN( fen );
		}
        } );
} );
