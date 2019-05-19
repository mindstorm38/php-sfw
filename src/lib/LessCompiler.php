<?php

namespace SFW;

use lessc;

final class LessCompiler {

	private static $compiler = null;

	public static function get_compiler() : lessc {
		if ( self::$compiler === null ) {
			self::$compiler = new lessc();
		}
		return self::$compiler;
	}

	public static function compile( $file ) {
		return self::get_compiler()->compileFile( $file );
	}
	
	public static function compile_resource( $res ) {
		return self::get_compiler()->compile( stream_get_contents($res) );
	}
	
	public static function print_compiled_resource( $res ) {
		
		Utils::content_type("text/css");
		echo self::compile_resource($res);
		
	}

}

?>
