<?php

// Autoloader include file
// Used to load file from "use" directive

final class Autoloader {

	private static $paths = null;

	public static function register() {
		spl_autoload_register( array( "Autoloader", "autoload_function" ) );
	}

	public static function unregister() {
		spl_autoload_unregister( array( "Autoloader", "autoload_function" ) );
	}

	public static function get_paths() {
		if ( self::$paths == null ) {
			self::$paths = [

				"PHPHelper\\src\\Utils"							=> "./src/utils.class.php",
				"PHPHelper\\src\\Config"						=> "./src/config.class.php",
				"PHPHelper\\src\\Core"							=> "./src/core.class.php",
				"PHPHelper\\src\\Lang"							=> "./src/lang.class.php",
				"PHPHelper\\src\\SessionManager"				=> "./src/sessionmanager.class.php",
				"PHPHelper\\src\\SessionHandler"				=> "./src/sessionmanager.class.php",
				"PHPHelper\\src\\Database"						=> "./src/database.class.php",
				"PHPHelper\\src\\SQLManager"					=> "./src/sqlmanager.class.php",
				"PHPHelper\\src\\SQLSerializable"				=> "./src/sqlserializable.class.php",
				"PHPHelper\\src\\Query"							=> "./src/querymanager.class.php",
				"PHPHelper\\src\\QueryResponse"					=> "./src/querymanager.class.php",
				"PHPHelper\\src\\QueryManager"					=> "./src/querymanager.class.php"

			];
		}
		return self::$paths;
	}

	public static function autoload_function( $path ) {

		$paths = self::get_paths();
		$path = array_key_exists( $path, $paths ) ? $paths[ $path ] : null;
		if ( $path != null && file_exists( $path ) && is_readable( $path ) ) {
			require_once $path;
		}

	}

}

?>
