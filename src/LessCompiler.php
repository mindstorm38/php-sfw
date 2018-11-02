<?php

namespace SFW;

use lessc;

final class LessCompiler {

	private static $compiler = null;

	public static function get_compiler() {
		if ( self::$compiler === null ) {
			self::$compiler = new lessc();
		}
		return self::$compiler;
	}

	public static function compile( $file ) {
		return self::get_compiler()->compileFile( $file );
	}

}

?>
