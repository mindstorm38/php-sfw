<?php

// Core class file

namespace PHPHelper\src;

use PHPHelper\src\Config;

final class Core {

	// PHPHelper constants
	const AUTHOR							= "Mindstorm38";
	const CONTACT							= "mailto:mindstorm38pro@gmail.com";
	const VERSION							= "2.0.0";

	private static $app_name = null;
	private static $app_sources_path = null;

	public static function init_application( $app_name, $app_sources_path ) {

		if ( version_compare( PHP_VERSION, "5.5.0", "lt" ) ) {
			throw new
			die( "PHP 5.5+ required.<br>Currently installed version is : " . phpversion() );
		}

		

	}

	public static function missing_extension( $extension, $fatal = false, $extra = "" ) {

		$doclink = self::get_php_doc_link( 'book.' . $extension . '.php' );

		$message = sprintf(
			"The %s extension is missing. Please check your PHP configuration.", "<a href=\"{$doclink}\">{$extension}</a>"
		);

		if ( $extra != "" ) {
			$message .= " " + $extra;
		}

		if ( $fatal ) {
			self::fatal_error( $message );
			return;
		}

	}

	public static function fatal_error( $error_message, $message_args = null ) {

		if ( is_string( $message_args ) ) {
			$error_message = sprintf( $error_message, $message_args );
		} elseif ( is_array( $message_args ) ) {
			$error_message = vsprintf( $error_message, $message_args );
		}

		// Message::add_message( "fatal", $error_message );
		// Logger::log( "fatal", $error_message );

	}

	public static function get_php_doc_link( $target ) {
		$lang = "en";
		return "https://secure.php.net/manual/" . $lang . "/" . $target;
	}

	public static function set_cookie( $name, $value, $expire = 0 ) {
		if ( headers_sent() ) return false;
		return setcookie( $name, $value, $expire, "/", "." . Config::get("global:advised_host", ""), Config::get("global:secure", false), true );
	}

}

?>
