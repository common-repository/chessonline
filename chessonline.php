<?php
/*
Plugin Name: ChessOnline
Text Domain: chessonline
Description: ChessOnline is a plugin that allows you to display a chessboard and to set up positions using the Forsyth-Edwards-Notation (FEN). You can specify an external URL for linking purposes.
Version: 1.3
Author: Alexander Mainzer
Author URI: http://www.w3edv.de
Plugin URI: http://chessonline.w3edv.de/#wp
License: GPLv2
*/

/*  Copyright 2012  Alexander Mainzer

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

################
### Security ###
################

if( !class_exists( 'WP' ) || preg_match( "#" . basename( __FILE__ ) . "#" , $_SERVER[ "PHP_SELF" ] ) ) { die(); }

############
### Init ###
############

add_action( 'init', 'co_Init' );

function co_Init() {

	/* Internationalisation */
	load_plugin_textdomain( 'chessonline' , false, basename( dirname( __FILE__ ) ) . '/i18n' );

	/* Include CSS */
	wp_register_style( 'co_css' , plugins_url( 'chessonline.css' , __FILE__ ) );
	wp_enqueue_style( 'co_css' );
		
	/* jQuery */
	wp_enqueue_script( 'jquery' , '' , '' , '' , true );

	/* Include JavaScript */
	wp_register_script( 'co_js' , plugins_url( 'chessonline.js' , __FILE__ ) , array( 'jquery' ) );
	wp_enqueue_script( 'co_js' );

	wp_register_script( 'co_player' , plugins_url( 'player.js' , __FILE__ ) , array( 'jquery' ) );
	wp_enqueue_script( 'co_player' );

	/* Add content filter */
	add_filter( "the_content" , "co_CreateBoard" );
	add_filter( "the_content" , "co_CreatePlayer" );

}

######################
### Content filter ###
######################

/* Replace FEN */
function co_CreateBoard( $content ) {
	$fenregex = '/\[((([pbnrqkPBNRQK0-8]{0,8}\/){7}[pbnrqkPBNRQK0-8]{0,8})'
			. ' (!?b|!?w) (!?-|!?[KQkq]{1,4}) (!?-|!?[abcdefg][36]) (!?[0-9]+) (!?[0-9]+))(!?| !?.*)\]/U';
	$content = preg_replace_callback( $fenregex,
					create_function( "\$matches" , "return co_SetupBoard( \$matches );" ) ,
					$content );
	return $content;
}

/* Replace PGN */
function co_CreatePlayer( $content ) {
	$pgnregex = '/(\[(co)?pgn\])(.*)(\[\/(co)?pgn\])/isU';
	$content = preg_replace_callback( $pgnregex,
					create_function( "\$matches" , "return co_SetupPlayer( \$matches );" ) ,
					$content );
	return $content;
}


/* Generate board and setup pieces */
function co_SetupBoard( $fendata ) {

	/* Get FEN  */
	$fen = $fendata[ 1 ];

	/* Get piece placement */
	$placement = $fendata[ 2 ];

        /* Get active color */
        $color = $fendata[ 4 ];

	/* Get castling availability */
	$castling = $fendata[ 5 ];

	/* Get en passant target square */
	$ep = $fendata[ 6 ];

	/* Get halvmoves number for fifty-move rule */
	$halfmoves = ( int ) $fendata[ 7 ];

	/* Get fullmove number */
	$fullmove = ( int ) $fendata[ 8 ];

	/* Get info text */
	$info = trim( $fendata[ 9 ] );

	/* Check if placement is valid */
	/* Expand FEN placement */
	$efen = $placement;
	while( preg_match( '/\/\//' , $efen ) ) { $efen = preg_replace( '/\/\//' , '/8/' , $efen ); }
	$patterns = array ( '/^\//' , '/\/$/' ,  '/\//' , '/1/' , '/2/' , '/3/' , '/4/' , '/5/' , '/6/' , '/7/' , '/8/' );
	$replacements = array( '8/' , '/8' , '' , ' ' , '  ' , '   ' , '    ' , '     ' , '      ' , '       ' , '        ' );
	$efen = preg_replace( $patterns , $replacements , $efen);
	if( !preg_match( '/^[ pbnrqkPBNRQK]{64}$/' , $efen ) ) { return $fendata[ 0 ]; }

	/* Return output string as replacent for the FEN */
	return co_ConstructBoard( $info , $placement , $fullmove , $halfmoves , $color , $link , $castling , $ep , false );
}

/* Generate player with board and setup moves */
function co_SetupPlayer( $pgndata ) {
	
	/* Get PGN code */
	$pgn = $pgndata[ 3 ];

	/* removes html br */
	$pgn = preg_replace( '/<.*>/iU' , " " , $pgn );

	/* Set default info */
	$info = "";

	/* Get white player */
	$white = trim( preg_replace( '/^.*\[\s*White\s*"(.*)"\s*\].*$/isU' , "$1" , $pgn , -1 , $count ) );
	$info .= ( $count ) ? "$white" : "";

	/* Get black player */
	$black = trim( preg_replace( '/^.*\[\s*Black\s*"(.*)"\s*\].*$/isU' , "$1" , $pgn , -1 , $count ) );
	$info .= ( $count ) ? " - $black" : "";

	/* Get event name */ 
	$event = trim( preg_replace( '/^.*\[\s*Event\s*"(.*)"\s*\].*$/isU' , "$1" , $pgn , -1 , $count ) );
	$info .= ( $count ) ? ", $event" : "";

	/* Get round */
	$round = trim( preg_replace( '/^.*\[\s*Round\s*"(.*)"\s*\].*$/isU' , "$1" , $pgn , -1 , $count ) );
	$info .= ( $count ) ? ", #$round" : "";

	/* Setup placement */
	$placement = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR";

	/* Setup fullmove */
	$fullmove = 0;

	/* Setup halfmoves */
	$halfmoves = 0;

	/* Setup side to move */
	$color = "w";

	/* Setup castling */
	$castling = "KQkq";

	/* Setup en passent */
	$ep = "-";

	/* Get and prepare moves */
	$movelist = preg_replace( "/[\n\r]*\[[^\[\]]*\][\n\r]*/" , ' ' , $pgn , -1 , $count );
	$movelist = preg_replace( "/[\n\r]/" , " " , $movelist );
	$movelist = preg_replace( "/\s+/" , " " , $movelist );
	$movearr = explode( " " , $movelist );
	$opencomment = 0;
	$openvariant = 0;
	$plycount = 1;
	for( $i = 0 ; $i < sizeof( $movearr ) ; $i++ ) {
		$opencomment += substr_count( $movearr[ $i ] , '{' );
		$openvariant += substr_count( $movearr[ $i ] , '(' );
		if( $opencomment + $openvariant == 0
			&& preg_match( '/(((([RNBQK]{1})?([a-h]{1})?([0-8]{1})?(x)?[a-h]{1}[0-8]{1})|(0-0-0|O-O-O|0-0|O-O))[^\s]*)/' , $movearr[ $i ] ) ) {
			$movearr[ $i ] = preg_replace( '/(((([RNBQK]{1})?([a-h]{1})?([0-8]{1})?(x)?[a-h]{1}[0-8]{1})|(0-0-0|O-O-O|0-0|O-O))[^\s]*)/' , "<span class=\"co_move co_ply_" . $plycount++ . "\">\\1</span>" , $movearr[ $i ] );	
		}
		$opencomment -= substr_count( $movearr[ $i] , '}' );
		$openvariant -= substr_count( $movearr[ $i] , ')' );
	}
	$movelist = implode( " " , $movearr );
	
	/* Return output string as replacent for the PGN */
	return co_ConstructBoard( $info , $placement , $fullmove , $halfmoves , $color , $link , $castling , $ep , $movelist );
}

#############################
### Admin options section ###
#############################

/* Create custom plugin settings menu */
add_action('admin_menu', 'chessonline_menu');

function chessonline_menu() {
	add_options_page('ChessOnline Settings', 'ChessOnline', 'manage_options', 'co_settings', 'chessonline_settings_page');
	add_action( 'admin_init', 'co_admin_init' );
}

function co_admin_init() {

	/* Register settings */
	register_setting( 'co-settings-group' , 'co_chessboard_size' , 'co_sani_boardsize' );
        register_setting( 'co-settings-group' , 'co_chessboard_alignment' , 'co_sani_alignment' );
	register_setting( 'co-settings-group' , 'co_lightsquare_color' , 'co_sani_htmlcolor' );
	register_setting( 'co-settings-group' , 'co_darksquare_color' , 'co_sani_htmlcolor' );
	register_setting( 'co-settings-group' , 'co_display_infolabel' , 'co_sani_yesno' );	
	register_setting( 'co-settings-group' , 'co_link_text' , 'trim' );
	register_setting( 'co-settings-group' , 'co_link_url' , 'co_sani_url' );
	register_setting( 'co-settings-group' , 'co_link_target' , 'co_sani_target' );

	/* Sanitize setting option values */
	function co_sani_boardsize( $val ) {
		return max( 200 , intval( $val ) );
        }
	function co_sani_alignment( $val ) {
		return preg_match( '/^(center|align left|align right|float left|float right)$/' , $val ) ? $val : 'center';
        }
	function co_sani_yesno( $val ) {
		return preg_match( '/^(yes|no)$/' , $val ) ? $val : 'no';
        }
	function co_sani_htmlcolor( $val ) {
		$colorregex = '/^.*(#[0-9abcdef]{6}|#[0-9abcdef]{3}|aqua|black|blue|fuchsia|gray|grey|green|lime|maroon|navy|olive|purple|red|silver|teal|transparent|white|yellow).*$/im';
		$counter = 0;
		$val =  preg_replace( $colorregex , "$1" , strtolower( trim( $val ) ) , -1 , $counter );
		return ( $counter == 1 ) ? $val : '';
	}
	function co_sani_url( $val ) {
		$urlregex = '/^(https?\:\/\/)?([a-z0-9-.]*)\.([a-z]{2,4}).*$/im';
		return ( preg_match( $urlregex , trim( $val ) ) ) ? trim( $val ) : '';
	}
	function co_sani_target( $val ) {
                return preg_match( '/^(overlay|new window|same window)$/' , $val ) ? $val : 'overlay';
        }
}

function chessonline_settings_page() {
?>
	<div class="wrap">

	<!-- Settings form -->
	<div id="icon-options-general" class="icon32"></div><h2>ChessOnline <?php echo co__( 'settings' ); ?></h2>
	<form method="post" action="options.php">
	<?php settings_fields( 'co-settings-group' ); ?>
	<table class="form-table">
	<tr valign="top">
	<th scope="row"><? echo co__( 'Chessboard size' ); ?></th>
	<td><input type="text" name="co_chessboard_size" value="<?php echo get_option( 'co_chessboard_size' ); ?>" /> <?php echo co__( 'px' ); ?></td>
	</tr>
        <tr valign="top">
        <th scope="row"><? echo co__( 'Chessboard alignment' ); ?></th>
        <td><select name="co_chessboard_alignment">
		<?php if( get_option( 'co_chessboard_alignment' ) ) { ?>
			<option value="<?php echo get_option( 'co_chessboard_alignment' ); ?>" /><?php echo co__( get_option( 'co_chessboard_alignment' ) ); ?></option>
		<?php } ?>
		<option value="center"><?php echo co__( 'center' ); ?></option>
		<option value="align left"><?php echo co__( 'align left' ); ?></option>
		<option value="align right"><?php echo co__( 'align right' ); ?></option>
		<option value="float left"><?php echo co__( 'float left' ); ?></option>
		<option value="float right"><?php echo co__( 'float right' ); ?></option>
	</select></td>
        </tr>
	<tr valign="top">
        <th scope="row"><? echo co__( 'Light square color' ); ?></th>
	<td><input type="text" name="co_lightsquare_color" value="<?php echo get_option( 'co_lightsquare_color' ); ?>" /> <?php echo co__( 'HEX code or standard CSS color name' ); ?></td>
        </tr>
        <tr valign="top">
        <th scope="row"><? echo co__( 'Dark square color' ); ?></th>
        <td><input type="text" name="co_darksquare_color" value="<?php echo get_option( 'co_darksquare_color' ); ?>" /> <?php echo co__( 'HEX code or standard CSS color name' ); ?></td>
        </tr>
	<tr valign="top">
        <th scope="row"><? echo co__( 'Display info text label' ); ?></th>
	<td><select name="co_display_infolabel">
                <option value="yes"><?php echo co__( 'yes' ); ?></option>
                <option value="no" <?php echo get_option( 'co_display_infolabel' ) == "no" ? "selected" : ""; ?>><?php echo co__( 'no' ); ?></option>
        </select></td>
        </tr>
	<tr valign="top">
        <th scope="row"><? echo co__( 'Link URL' ); ?></th>
        <td><input type="text" name="co_link_url" value="<?php echo get_option( 'co_link_url' ); ?>" /> <?php echo co__( 'Use {FEN} as placeholder to pass the FEN' ); ?></td>
        </tr>
	<tr valign="top">
        <th scope="row"><? echo co__( 'Link text' ); ?></th>
        <td><input type="text" name="co_link_text" value="<?php echo get_option( 'co_link_text' ); ?>" /></td>
        </tr>
	<tr valign="top">
        <th scope="row"><? echo co__( 'Link target' ); ?></th>
        <td><select name="co_link_target">
                <?php if( get_option( 'co_link_target' ) ) { ?>
                        <option value="<?php echo get_option( 'co_link_target' ); ?>" /><?php echo co__( get_option( 'co_link_target' ) ); ?></option>
                <?php } ?>
                <option value="overlay"><?php echo co__( 'overlay' ); ?></option>
                <option value="new window"><?php echo co__( 'new window' ); ?></option>
                <option value="same window"><?php echo co__( 'same window' ); ?></option>
        </select></td>
        </tr>
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php echo co__('Save Changes' ); ?>" />
	</p>
	</form>
	<!-- Settings form end -->

        </div>
<?php
} 

##########################
### FEN Editor WP tool ###
##########################

/* Create ChessOnline WP tools menu */
add_action('admin_menu', 'chessonline_tools');

function chessonline_tools() {
	add_submenu_page( 'tools.php', __( 'ChessOnline FEN Editor' , chessonline ) , __( 'ChessOnline FEN Editor' , chessonline ) , 'edit_posts', 'co_tools' , 'chessonline_add_wptools');
	add_action( 'admin_init', 'chessonline_tools_init' );
}

/* Initiate tools menu */
function chessonline_tools_init() {

        /* Include CSS */
        wp_register_style( 'co_toolscss' , plugins_url( 'chessonline-tools.css' , __FILE__ ) );
        wp_enqueue_style( 'co_toolscss' );

        /* jQuery */
        wp_enqueue_script( 'jquery' , '' , '' , '' , true );
        wp_enqueue_script( 'jquery-ui-core' , '' , '' , '' , true );
        wp_enqueue_script( 'jquery-ui-widget' , '' , '' , '' , true );
        wp_enqueue_script( 'jquery-ui-mouse' , '' , '' , '' , true );
        wp_enqueue_script( 'jquery-ui-draggeble' , '' , '' , '' , true );
        wp_enqueue_script( 'jquery-ui-droppable' , '' , '' , '' , true );

	/* Include JavaScript */
        wp_register_script( 'co_toolsjs' , plugins_url( 'chessonline-tools.js' , __FILE__ ) , array( 'jquery' ) );
        wp_enqueue_script( 'co_toolsjs' );
}

/* Prepare Create FEN Editor for WP tools page */
function chessonline_add_wptools() {
	echo "<!--  FEN editor -->\n";
	echo '<div class="wrap">';
	echo '<div id="icon-tools" class="icon32"></div><h2>' . co__( 'ChessOnline FEN Editor' ) . '</h2>';
	chessonline_tools_page();
	echo '</div>';
	echo "<!--  FEN editor end -->\n";
}

##################
### MCE Plugin ###
##################

/* Initiate MCE Plugin */
add_action( 'admin_init' , 'chessonline_add_mcebuttons');
function chessonline_add_mcebuttons() {
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) { return; }
   	if ( get_user_option( 'rich_editing' ) == 'true' ) {
		add_filter( 'mce_external_plugins', 'chessonline_add_mceplugin' );
		add_filter( 'mce_buttons' , 'chessonline_register_mcebutton');
		add_action( 'edit_form_advanced' , 'chessonline_add_mcedialog' );
		wp_register_script( 'co_mcejs' , plugins_url( '/tinymce/cofen.js' , __FILE__ ) , array( 'jquery' ) );
	        wp_enqueue_script( 'co_mcejs' );
		wp_register_style( 'co_mcecss' , plugins_url( '/tinymce/cofen.css' , __FILE__ ) );
	        wp_enqueue_style( 'co_mcecss' );
	}
}
function chessonline_register_mcebutton( $buttons ) {
	array_push( $buttons , 'separator' , 'coFEN' );
	return $buttons;
}
function chessonline_add_mceplugin( $plugin_array ) {
	$plugin_array[ 'coFEN' ] = plugins_url( '/tinymce/editor_plugin.js' , __FILE__ );
	return $plugin_array;
}
/* Prepare hidden FEN Editor for tinymce dialog */
function chessonline_add_mcedialog() {
        echo "<!--  FEN editor -->\n";
        echo '<div class="wrap">';
        echo '<div style="display: none;">';
        echo '<div id="co_fen_mce">';
        chessonline_tools_page();
        echo '<button id="co_fen_mceupdate" class="button-primary" tabindex="100">' . co__( 'Add FEN' ) . '</button>';
        echo '</div></div></div>';
        echo "\n<!--  FEN editor end -->\n";
}

###############
### Helpers ###
###############

/* Encode user input to HTML entities */
function co__( $val ) {
        return htmlentities( __( $val , chessonline ) , ENT_QUOTES , get_bloginfo( 'charset' ) );
}
function co_htmlentities( $val ) {
        return htmlentities( $val , ENT_QUOTES , get_bloginfo( 'charset' ) );
}

/* Construct chessboard */
function co_ConstructBoard( $info , $placement , $fullmove , $halfmoves , $color , $link , $castling , $ep , $movelist ) {
	
	/* Unicode encoding chesspieces */
	$pieces = array();
        $pieces[ 'P' ] = '&#9817;';
        $pieces[ 'R' ] = '&#9814;';
        $pieces[ 'N' ] = '&#9816;';
        $pieces[ 'B' ] = '&#9815;';
        $pieces[ 'K' ] = '&#9812;';
        $pieces[ 'Q' ] = '&#9813;';
        $pieces[ 'p' ] = '&#9823;';
        $pieces[ 'r' ] = '&#9820;';
        $pieces[ 'n' ] = '&#9822;';
        $pieces[ 'b' ] = '&#9821;';
        $pieces[ 'k' ] = '&#9818;';
        $pieces[ 'q' ] = '&#9819;';
        $pieces[ ' ' ] = '';

	/* Cols Array */
	$cols = array( 'a' , 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'h' );


	/* Expand FEN placement */
	$efen = $placement;
	while( preg_match( '/\/\//' , $efen ) ) { $efen = preg_replace( '/\/\//' , '/8/' , $efen ); }
	$patterns = array ( '/^\//' , '/\/$/' ,  '/\//' , '/1/' , '/2/' , '/3/' , '/4/' , '/5/' , '/6/' , '/7/' , '/8/' );
	$replacements = array( '8/' , '/8' , '' , ' ' , '  ' , '   ' , '    ' , '     ' , '      ' , '       ' , '        ' );
	$efen = preg_replace( $patterns , $replacements , $efen);

	/* Create FEN */	
	$fen = "$placement $color $castling $ep $halfmoves $fullmove";

	/* Get chessboard alignment option */
	switch( get_option( 'co_chessboard_alignment' ) ) {
		case 'align left':
			$alignment = 'margin-left: 0; margin-right: auto;';
			break;
		case 'align right':
			$alignment = 'margin-right: 0; margin-right: auto';
                        break;
		case 'float left':
			$alignment = 'float: left; margin-left: 0px; margin-right: 7px;';
                        break;
		case 'float right':
                        $alignment = 'float: right; margin-right: 0px; margin-left: 7px';
                        break;
		case 'center':
			default:
			$alignment = 'margin-left: auto; margin-right: auto;';
	}

        /* Get chessboard color options */
	$darksquarecolor = get_option( 'co_darksquare_color' ) ? get_option( 'co_darksquare_color' ) : '#c0c0c0';
	$lightsquarecolor = get_option( 'co_lightsquare_color' ) ? get_option( 'co_lightsquare_color' ) : '#ffffff';
        $darksquare = "background: $darksquarecolor;";
        $lightsquare = "background: $lightsquarecolor;";
	$bordercolor = "border: solid 1px $darksquarecolor;";
	$boardborder = "border-color: $darksquarecolor;";
	
        /* Get chessboard size option */
	$boardmargin = 16;
	$squarepx = get_option( 'co_chessboard_size' ) ? floor( ( get_option( 'co_chessboard_size' ) - ( $boardmargin * 2 ) - 20 ) / 8 ) : 30;

	/* Setup board CSS */
	$wrapsize = 'width: ' . ( ( $squarepx * 8 ) + ( $boardmargin * 2 ) + 20 ) . 'px;';
        $boardsize = 'width: ' . ( ( $squarepx * 8 ) + ( $boardmargin * 2 ) + 4 ) . 'px; height: ' . ( ( $squarepx * 8 ) + ( $boardmargin  * 2 ) + 4 ) . 'px;';
	$placementsize = 'width: ' . ( $squarepx * 8 ) . 'px; height: ' . ( $squarepx * 8 ) . 'px;';
	$squaresize = 'width:' . $squarepx  . 'px; height: '. $squarepx . 'px;';	
	$squarelineheight = 'line-height: ' . $squarepx . 'px;';
	$squarefontsize = 'font-size: ' . floor( $squarepx * 0.80 ) . 'px;';
	$colsize = 'width: ' . $squarepx  . 'px; height: '. $boardmargin . 'px;';
	$collineheight = 'line-height: ' . ( $boardmargin ) . 'px;';
	$colfontsize = 'font-size: ' . ( $boardmargin - 2 ) . 'px;';
	$ranksize = 'width:' . $boardmargin . 'px; height: ' . ( $squarepx * 8 ) . 'px;';
	$ranklineheight = 'line-height: ' . $squarepx . 'px;';
	$rankfontsize = 'font-size: ' . ( $boardmargin - 2 ) . 'px;';
	$cornersize = 'width: ' . ( $boardmargin + 2 )  . 'px; height: '. ( $boardmargin + 2 ) . 'px;';

	/* Get display infolabel value */
	$displayinfolabel = ( get_option( 'co_display_infolabel' ) == 'no' || $movelist ) ? false : true;

	/* Get link text */
	$linktext = get_option( 'co_link_text' ) ? get_option( 'co_link_text' ) : co__( 'Analysis' );

	/* Get link URL and complete params */
	$link = get_option( 'co_link_url' ) ? get_option( 'co_link_url' ) : false;
	if( $link ) {
		$linkfen = ( $movelist ) ? "{FEN}" : $fen;
		if( strpos( $link , '{FEN}' ) ) {
			$link = preg_replace( '/\{FEN\}/' , rawurlencode( $linkfen ) , $link );
		} elseif( strpos( $link , '?' ) ) {
			$link = preg_replace( '/\?/' , "?fen=" . rawurlencode( $linkfen ) . "&" , $link , 1 );
		} elseif( strpos( $link , '#' ) ) {
			$link = preg_replace( '/#/' , "?fen=" . rawurlencode( $linkfen ) . "#" , $link , 1 );
		} else {
			$link .= "?fen=" . rawurlencode( $linkfen );
		}
	}

	/* Get link target */
	$linktarget = get_option( 'co_link_target' ) ? get_option( 'co_link_target' ) : 'overlay';
	
	/* Generate string for output */
	
	/* Open wrapping div */
	$strout .= "<!--  Chessboard generated by ChessOnline plugin -->\n";
	$playerclass = ( $movelist ) ? "co_player" : "";
	$strout .= "<div class=\"co_wrap $playerclass\" id=\"" . uniqid( "co_" ) . "\" style=\"$wrapsize $alignment\">";

	/* Include info */
	if( strpos( $info , '!' ) !== 0 && strlen( $info ) > 0 ) {
		$strout .= '<div class="co_info">' . ( $displayinfolabel ? co__( 'Info' ) . ': ' : '' ) . co_htmlentities( $info ) . '</div>';
	}

	/* Create chessboard div */
	$strout .= "<div class=\"co_board\" style=\"$boardsize $boardborder\">";

	/* Create top cols and left ranks */
	for( $i = 0 ; $i <= 9 ; $i++ ) {
		if( $i != 0 && $i != 9 ) {
			$strout .= "<div class=\"co_col\" style=\"$colsize $collineheight $colfontsize\"><span class=\"co_vbottom\">" . $cols[ $i - 1 ] . '</span></div>';		
		} else {
			$strout .= "<div class=\"co_corner\" style=\"$margin $cornersize\"></div>";
		}
	}
	$strout .= "<div class=\"co_rank\" style=\"$ranksize $ranklineheight $rankfontsize\">";
	for( $i = 8 ; $i >= 1 ; $i-- ) { $strout .= "$i<br>"; } 
	$strout .= '</div>';
	
	/* Create placement */
	$strout .= "<div class=\"co_placement\" style=\"$placementsize $bordercolor\">";
	for( $i = 7 ; $i >= 0 ; $i-- ) {
	       	for( $j = 0 ; $j <= 7 ; $j++ ) {
        		$background = ( ( $i + $j ) % 2 == 0 ) ? $darksquare : $lightsquare;
        		$strout .= "<div class=\"co_square\" style=\"$background $squaresize $squarelineheight $squarefontsize\">" . $pieces[ $efen[ ( ( 7 - $i ) * 8 + $j ) ] ]. '</div>';
	       	}
        }
	$strout .= '</div>';

	/* Create right ranks and bottom cols */
	$strout .= "<div class=\"co_rank\" style=\"$ranksize $ranklineheight $rankfontsize\">";
	for( $i = 8 ; $i >= 1 ; $i-- ) { $strout .= "$i<br>"; } 
	$strout .= '</div>';
	for( $i = 0 ; $i <= 9 ; $i++ ) {
		if( $i != 0 && $i != 9 ) {
			$strout .= "<div class=\"co_col\" style=\"$colsize $collineheight $colfontsize\"><span class=\"co_vtop\">" . $cols[ $i - 1 ] . '</span></div>';		
		} else {
			$strout .= "<div class=\"co_corner\" style=\"$margin $cornersize\"></div>";
		}
	}

	/* Close chessboard div */
	$strout .= '</div>';

	/* Include move number */
	if( strpos( $fullmove , '!' ) !== 0 ) {
		$strout .= '<div class="co_movenumber">' . ( ( $fullmove  > 0 ) ? "#" . $fullmove   . " " : "" ) . '</div>';
	}

	/* Include side to move */
	if( strpos( $color , '!' ) !== 0 ) {
		$strout .= '<div class="co_sidetomove">' 
				.  ( ( $color == "w" ) ? co__( 'White' , chessonline) : co__( 'Black' ) )
				. ' ' . co__( 'to move' ) . '</div>';
	}
	
	/* Include link */
	$targetlist = array( 'new window' => '_blank' , 'same window' => '_top' );
	$hreftarget = ( $linktarget == 'overlay' ) ? 'class="co_overlay"' : 'target="' . $targetlist[ $linktarget ] .'"';
	$strout .= ($link) ? "<div class=\"co_link\"><a href=\"$link\" $hreftarget>" . co_htmlentities( $linktext ) . '</a></div>' : '';

	/* Include castling availability */
	if( strpos( $castling , '!' ) !== 0 && !$movelist ) {
		$strout .= '<div class="co_castlingwhite">' . co__( 'Castling' ) . ' ' . co__( 'white' )
				. ':' . ( preg_match( '/K/' ,  $castling ) ? ' O-O' : '' ) 
				. ( preg_match( '/Q/' , $castling ) ? ' O-O-O' : '' )
				. ( !preg_match( '/[KW]/' , $castling ) ? ' ' . co__( 'not allowed' ) : '' ) . '</div>';
		$strout .= '<div class="co_castlingblack">' . co__( 'Castling' ) . ' ' . co__( 'black' )
	                        . ':' . ( preg_match( '/k/' ,  $castling ) ? ' O-O' : '' )  
	                        . ( preg_match( '/q/' , $castling ) ? ' O-O-O' : '' )
	                        . ( !preg_match( '/[kq]/' , $castling ) ? ' ' . co__( 'not allowed' ) : '' ) . '</div>';
	}

	/* Include en passant target square */
	if( strpos( $ep , '!' ) !== 0 && !$movelist ) {
		$strout .= '<div class="co_enpassant">' . co__( 'En passant target square' ) . ': ' .  ( ( $ep != "-" ) ? co_htmlentities( $ep ) : co__( 'none' ) ) . '</div>';
	}

	/* Include move list from PNG */
	if( $movelist ) {
		/* Include player control area */
		$strout .= '<div class="co_playercontrol">
				<button class="co_btnbegin">&#x2223;&#x25C0;</button>
				<button class="co_btnrewind">&#x25C0;</button>
				<button class="co_btnforward">&#x25B6;</button>
				<button class="co_btnend">&#x25B6;&#x2223;</button>
				</div>';
		$strout .= '<div class="co_movelist">' . $movelist . '</div>';
	}

	/* Clear all floatings */
	$strout .= '<div style="clear: both"></div>';

	/* Close wrapping div */
	$strout .= '</div>';

	return $strout;
}

/* Create FEN Editor */
function chessonline_tools_page() {
?>
	<div id="co_feneditor"><p><noscript><?php echo co__( 'To use this tool, you must enable JavaScript.' ); ?></noscript></p>
	<?php

		/* Editor chessboard */
	        $cols = array( 'a' , 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'h' );
        	$strout = '<div class="co_editorboard">';
	        for( $i = 9 ; $i >= 0 ; $i-- ) {
 	               for( $j = 0 ; $j <= 9 ; $j++ ) {
	                        if( $i != 0 && $i != 9 && ( $j == 0 || $j == 9 ) ) {
	                                $cssclass = 'co_square_rank';
					$html = $i;
                        	} elseif( $j != 0 && $j != 9 && ( $i == 0 || $i == 9 ) ) {
                                	$cssclass = 'co_square_col';
                                	$html = $cols[ $j - 1 ];
                        	} elseif( $i != 0 && $i != 9 && $j != 0 && $j != 9 && ( $i + $j ) % 2 == 0 ) {
                                	$cssclass = 'co_square_drop co_square_dark';
                                	$html = '';
	                        } elseif ( $i != 0 && $i != 9 && $j != 0 && $j != 9 ) {
                                	$cssclass = 'co_square_drop co_square_light';
                                	$html = '';
                        	} else {
                                	$cssclass = 'co_square_corner';
                                	$html = '';
                        	}
	                        if( $i != 0 && $i != 9 && ( $j == 1 ) ) { $cssclass .= ' co_square_left'; }
        	                if( $i != 0 && $i != 9 && ( $j == 8 ) ) { $cssclass .= ' co_square_right'; }
                	        if( $j != 0 && $j != 9 && ( $i == 1 ) ) { $cssclass .= ' co_square_bottom'; }
	                        if( $j != 0 && $j != 9 && ( $i == 8 ) ) { $cssclass .= ' co_square_top'; }
        	                if( $i == 8 && $j == 1 ) { $cssclass .= ' co_square_topleft'; }
                	        if( $i == 8 && $j == 8 ) { $cssclass .= ' co_square_topright'; }
	                        if( $i == 1 && $j == 1 ) { $cssclass .= ' co_square_bottomleft'; }
        	                if( $i == 1 && $j == 8 ) { $cssclass .= ' co_square_bottomright'; }
	                        if( $i != 0 && $i != 9 && $j != 0 && $j != 9 ) { $html = '<div class="co_piece">' . $html . '</div>'; }
				$strout .= '<div id="co_square' . $i . $j . '" class="' . $cssclass . '">' . $html . '</div>';
	                }
        	}
	        $strout .= '</div>';
		echo $strout;

	     	/* Editor pieces repository */
		$pieces = array( 'P' => '&#9817;' , 'p' => '&#9823;' , 'R' => '&#9814;' , 'r' => '&#9820;' ,
					'N' => '&#9816;' , 'n' => '&#9822;' , 'B' => '&#9815;' , 'b' => '&#9821;' ,
					'Q' => '&#9813;' , 'q' => '&#9819;' , 'K' => '&#9812;' ,  'k' => '&#9818;' );
	        $strout = '<div class="co_repo">';
		foreach( $pieces as $value ) {
	               	$strout .= '<div class="co_dragrepo"><div class="co_piecerepo">' . $value . '</div></div>';
	        } 
	        $strout .= '</div>';
		echo $strout;

		/* Editor FEN fields */
		$strout = '<div class="co_fenfields">';
		$strout .= '<span class="co_label">' . co__( 'Active color' ) . ':</span><span class="co_field"><select id="co_sidetomove"><option value="w">' . co__( 'white' ) . '</option><option value="b">' . co__( 'black' ) . '</option></select></span>';
		$strout .= '<span class="co_label">' . co__( 'Castling' ) . ' ' . co__( 'white' ) . ' O-O:</span><span class="co_field"><select id="co_castlingwk"><option value="-">' . co__( 'not allowed' ) . '</option><option value="K">' . co__( 'allowed' ) . '</option></select></span>';
		$strout .= '<span class="co_label">' . co__( 'Castling' ) . ' ' . co__( 'white' ) . ' O-O-O:</span><span class="co_field"><select id="co_castlingwq"><option value="-">' . co__( 'not allowed' ) . '</option><option value="Q">' . co__( 'allowed' ) . '</option></select></span>';
		$strout .= '<span class="co_label">' . co__( 'Castling' ) . ' ' . co__( 'black' ) . ' O-O:</span><span class="co_field"><select id="co_castlingbk"><option value="-">' . co__( 'not allowed' ) . '</option><option value="k">' . co__( 'allowed' ) . '</option></select></span>';
		$strout .= '<span class="co_label">' . co__( 'Castling' ) . ' ' . co__( 'black' ) . ' O-O-O:</span><span class="co_field"><select id="co_castlingbq"><option value="-">' . co__( 'not allowed' ) . '</option><option value="q">' . co__( 'allowed' ) . '</option></select></span>';
		$strout .= '<span class="co_label">' . co__( 'EP target square' ) . ':</span><span class="co_field"><select id="co_ep"><option value="-">' . co__( 'none' ) . '</option>';
		foreach( array( 'a3' , 'b3' , 'c3' , 'd3' , 'e3' , 'f3' , 'g3' , 'h3' , 'a6' , 'b6' , 'c6' , 'd6' , 'e6' , 'f6' , 'g6' , 'h6') as $value ) {
			$strout .= "<option value=\"$value\">$value</option>";
		}
		$strout .= '</select></span>';
		$strout .= '<span class="co_label">' . co__( 'Halfmove clock' ) . ':</span><span class="co_field"><input type="text" size="3" maxlength="3" id="co_halfmove" value="0"></span>';
		$strout .= '<span class="co_label">' . co__( 'Fullmove number' ) . ':</span><span class="co_field"><input type="text" size="3" maxlength="3" id="co_fullmove" value="1"></span>';
		$strout .= '</div>';
                echo $strout;

		/* Editor FEN output */
		$strout = '<div class="co_fenoutput">';
		$strout .= '<h3>' . co__( 'Your FEN' ) . '</h3>';
		$strout .= '<input type="text" id="co_fen" value="8/8/8/8/8/8/8/8 w - - 0 1">';
		$strout .= '<button class="button" id="co_loadfen">' . co__( 'FEN to board' ) . '</button>';
		$strout .= '<button class="button" id="co_makefen">' . co__( 'Board to FEN' ) . '</button>';
		$strout .= '<button class="button" id="co_resetfen">' . co__( 'Clear' ) . '</button>';
		$strout .= '<button class="button" id="co_initfen">' . co__( 'Starting position' ) . '</button>';
		$strout .= '</div>';
		echo $strout;
	?>
	</div>

<?php } ?>
