<?php

require __DIR__ . '/../common.php';

use SFW\LessCompiler;

header('Content-Type: text/css');

if ( array_key_exists( "path", $_GET ) ) {

	try {
		echo LessCompiler::compile( __DIR__ . "/{$_GET["path"]}.less" );
		die();
	} catch ( Exception $e ) {
		die( "Fatal error while compiling less file " . $e );
	}

}

http_response_code( 404 );

?>
