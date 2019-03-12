<?php

require __DIR__ . '/../common.php';

use SFW\LessCompiler;

header('Content-Type: text/css');

if ( array_key_exists( "path", $_GET ) ) {

	$path = __DIR__ . "/{$_GET["path"]}.less";
	
	if ( file_exists( $path ) ) {
		
		try {
			
			echo LessCompiler::compile( $path );
			die();
			
		} catch ( Exception $e ) {
			
			http_response_code( 500 );
			die( "Fatal error while compiling less file " . $e );
			
		}
		
	}
	
}

http_response_code( 404 );

?>
