<?php

require __DIR__ . '/common.php';

use SFW\LessCompiler;

header('Content-Type: text/css');

if ( array_key_exists( "style", $_GET ) ) {

	$dir = array_key_exists( "dir", $_GET ) ? ( $_GET["dir"] . "/" ) : "";

	try {
		echo LessCompiler::compile( "./styles/{$dir}{$_GET["style"]}.less" );
		die();
	} catch ( Exception $e ) {
		die( "Fatal error while compiling less file " . $e );
	}

}

http_response_code( 404 );

?>
