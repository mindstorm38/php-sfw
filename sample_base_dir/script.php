<?php

header('Content-Type: text/javascript');

if ( array_key_exists( "script", $_GET ) ) {

	$dir = array_key_exists( "dir", $_GET ) ? ( $_GET["dir"] . "/" ) : "";

	$path = "./scripts/{$dir}{$_GET["script"]}.js";

	if ( file_exists( $path ) && is_readable( $path ) ) {
		require_once $path;
		die();
	}

}

http_response_code( 404 );

?>
