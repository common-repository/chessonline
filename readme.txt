=== Plugin Name ===
Contributors: anaximander
Tags: fen, pgn, chessboard, schachbrett, schaakbord, chess, schach, schaak, viewer
Requires at least: 2.7.0
Tested up to: 3.3.2
Stable tag: 1.3

The ChessOnline plugin allows you to display a chessboard and to set up positions (using the FEN). You can also use it as a PGN viewer (experimental).

== Description ==

= English =

The ChessOnline WordPress plugin allows you to display a chessboard and to set up positions using the Forsyth-Edwards-Notation (FEN) and view games using the PGN format (experimental feature). You can specify an external URL for linking purposes. Please use http://chessonline.w3edv.de/?fen={FEN} as URL if you want to set an analysis link to the ChessOnline-Plugin-Website. It comes up with an integrated FEN editor. Board size and colors are configurable. The following languages are supported (i18n): Dutch, English and German. The client browser needs to be capable of displaying unicode characters. No browsers plugins are required. JavaScript doesn't need to be enabled for displaying the chessboard but is required for using the PGN viewer, the overlay functionality and the FEN editor.

The FEN has to be put in square brackets:

* e.g. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52]

It is possible to add some info text:

* e.g. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52 mate in 2]

Adding an exclamation mark (!) hides the marked section:

* e.g. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w !- !- !1 !52 mate in 2]

Adding a PGN game (experimental feature):

* Put the PGN code between [pgn] and [/pgn] tags (or [copgn] and [/copgn] to avoid compatibility problems with other plugins).

= Deutsch =

Das ChessOnline WordPress Plugin ermöglicht Ihnen das Einbinden eines Schachbrettes über eine Eingabe mittels Forsyth-Edwards-Notation (FEN) oder eines PGN-viewers mittels PGN-Code (experimentell). Es kann eine externe URL für Verlinungszwecke angegeben werden. Bitte setzen Sie die URL auf http://chessonline.w3edv.de/?fen={FEN} wenn Sie einen Analyse-Link auf die ChessOnline-Plugin-Webseite setzen möchten. Ein FEN-Editor ist integriert. Brettgröße und -farbe sind konfigurierbar. Die folgenden Sprachen werden unterstützt (i18n): Deutsch, Englisch und Niederländisch. Der Client-Browser muss Unicode-Zeichen darstellen können. Es wird kein Browser-Plugin benötigt. JavaScript muss nicht eingeschaltet sein um das Schachbrett darzustellen, wird jedoch für den PGN-Viewer, die Overlay-Funktionalität und den FEN-Editor benötigt.

Die FEN muss in eckigen Klammern gesetzt werden:

* Z.B. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52]

Es kann ein zusätzlicher Info-Text mit angegeben werden:

* ZB. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52 matt in 2]

Ein vorangestelltes Ausrufezeichen (!) verhindert die Ausgabe der jeweiligen Information:

* ZB. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w !- !- !1 !52 matt in 2]

Eine Partie einbinden (experimentell):

* Setzen Sie den PGN-Code zwischen [pgn] and [/pgn] (oder [copgn] und [/copgn] um Kompatibilitätsprobleme in Verbindung mit anderen Plugins zu vermeiden).

= Nederlands =

De ChessOnline-WordPress-plugin biedt jou de mogelijkheid om in jouw artikels een schaakbord te integreren door middel van een Forsyth-Edwards-notatie (FEN) of een PGN-viewer door middel van PGN-code (experimenteel). Voor link doeleinden kan een externe URL worden opgegeven. Indien je de analyse-link naar de ChessOnline-Plugin-website wenst te plaatsen, gelieve dan http://chessonline.w3edv.de/?fen={FEN} als URL in te vullen. Een FEN-editor is geïntegreerd. Grootte en kleur van het bord zijn configureerbaar. De volgende talen worden ondersteund (i18n): Duits, Engels en Nederlands. De client-browser moet unicode-tekens kunnen afbeelden. Er worden geen browser-plugins benodigd. JavaScript moet niet geactiveerd zijn maar wordt wel benodigd voor de PGN-viewer, de overlay-functie en de FEN-editor.

De FEN moet tussen vierkante haakjes worden geplaatst:

* b.v. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52]

Er kan een extra info-text worden bijgevoegd:

* b.v. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w - - 1 52 mat in 2]

Een voorafgaand uitroepteken (!) onderdrukt de weergave van de betreffende informatie:

* b.v. [r1bk2nr/p2p1pNp/n2B4/1p1NP2P/6P1/3P1Q2/P1P1K3/q5b1 w !- !- !1 !52 mat in 2]

Eine partij invoegen (experimenteel):

* Plaats de PGN-code tussen [pgn] en [/pgn] (of [copgn] en [/copgn] om Kompatibiliteitsproblemen in verbindung met andere plugins te vermijden).

== Installation ==

EN - Upload the ChessOnline plugin to your blog an activate it.

DE - Laden Sie das ChessOnline-Plugin hoch über Ihre Plugin-Seite und aktivieren Sie es.

NL - Upload het ChessOnline-Plugin in jouw blog en activeer het.
 
== Frequently Asked Questions ==

= How can I resize the overlay iframe? =

Change the marked width and height in chessonline.css

== Screenshots ==

1. Chessboard
2. PGN Viewer
3. Admin Settings
4. FEN Editor
5. TinyMCE Plugin
6. TinyMCE WP Dialog

== Changelog ==

= 1.3 =
* PGN viewer added (experimental)

= 1.2.1 =
* Files and ranks labeled
* Option to toggle the info text label visibility added
* Support of hiding partial FEN information added

= 1.2 =
* TinyMCE plugin added

= 1.1 =
* FEN editor added
* Link target option added

= 1.0 =
* Published

== Upgrade Notice ==

= 1.3 =
You can use this plugin as PGN viewer now. This feature is experimental: some functions are missing or need to become more 
elaborate (castling control, halfmove counter, variants).

= 1.2.1 =
Files and ranks are labeled. You can toggle the info text label visibility. Hiding partial FEN information is supported.

= 1.2 =
The FEN editor can be used while writing posts. You don't have to switch to the tools page anymore.

= 1.1 =
This version comes up with an integrated FEN editor.
