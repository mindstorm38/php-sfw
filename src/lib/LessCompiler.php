<?php

namespace SFW;

use lessc;

/**
 * 
 * A Less compiler using {@link lessc} library.
 * 
 * @author Théo Rozier
 *
 */
final class LessCompiler {

	private static $compiler = null;

	/**
	 * @return lessc A static less compiler.
	 */
	public static function get_compiler() : lessc {
		
		if ( self::$compiler === null ) {
			self::$compiler = new lessc();
		}
		
		return self::$compiler;
		
	}

	/**
	 * Compile file using the compiler.
	 * @param string|mixed $file File path.
	 * @return string The compiled string.
	 */
	public static function compile( $file ) {
		return self::get_compiler()->compileFile( $file );
	}
	
	/**
	 * Compile a resource stream using compiler.
	 * @param resource $res A readable resource.
	 * @return string The compiled string.
	 */
	public static function compile_resource( $res ) {
		return self::get_compiler()->compile( stream_get_contents($res) );
	}
	
	/**
	 * Add the CSS content type header and output compiled less.
	 * @param resource $res A readable resource
	 * @see LessCompiler::compile_resource
	 * @see Utils::content_type
	 */
	public static function print_compiled_resource( $res ) {
		
		Utils::content_type("text/css");
		echo self::compile_resource($res);
		
	}
	
	/**
	 * Add a resource extension processor to core application.
	 * This add a processor for extension <code>.less.css</code> and transform by removing <code>.css</code> extension.
	 */
	public static function add_res_ext_processor() {
		
		Core::add_res_ext_processor( ".less.css", function( string $path ) {
			return substr( $path, 0, strlen($path) - 4 );
		}, [__CLASS__, "print_compiled_resource"] );
		
	}

}

?>
