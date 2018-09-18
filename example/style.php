<?php

require __DIR__ . '/vendor/autoload.php';

header('Content-Type: text/css');

$less = new lessc();

if ( array_key_exists( "style", $_GET ) ) {

	$dir = array_key_exists( "dir", $_GET ) ? ( $_GET["dir"] . "/" ) : "";

	try {
		echo $less->compileFile("./styles/{$dir}{$_GET["style"]}.less");
		die();
	} catch ( Exception $e ) {
		die( "Fatal error while compiling less file " . $e );
	}

}

http_response_code( 404 );

?>
