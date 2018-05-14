<?php

// Core class file

namespace PHPHelper\src;

use PHPHelper\src\Config;
use PHPHelper\src\Utils;
use PHPHelper\src\Lang;
use PHPHelper\src\SessionManager;

final class Core {

	const MINIMUM_PHP_VERSION = "5.5.0";

	private static $app_name = null;

	public static function start_application( $app_name, $minimum_php_version = "5.5.0" ) {

		if ( self::$app_name !== null ) die( "Application already started" );

		if ( version_compare( $minimum_php_version, Core::MINIMUM_PHP_VERSION, "lt" ) ) {
			die( "Invalid minimum php version given, PHP 5.5 minimum required. Given version : " . $minimum_php_version );
		} else if ( version_compare( PHP_VERSION, $minimum_php_version, "lt" ) ) {
			die( "PHP {$minimum_php_version} required. Currently installed version is : " . phpversion() );
		}

		self::$app_name = $app_name;

		@ini_set( 'display_errors', 1 );
		@error_reporting( E_ALL );
		@ini_set( 'file_uploads', 1 );
		@ini_set('default_charset', 'utf-8');
		@mb_internal_encoding('utf-8');

		if ( isset( $_SERVER["HTTPS"] ) != boolval( Config::get("global:secure") ) ) {

			Utils::redirect( Config::get_advised_url() . $_SERVER["REQUEST_URI"] );
			die();

		}

		Lang::init_languages();
		SessionManager::session_start();

	}

	public static function check_app_ready() {
		if ( self::$app_name === null ) throw new Exception( "Application not started" );
	}

	public static function get_app_name() {
		self::check_app_ready();
		return self::$app_name;
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
