<?php

// Core class file

namespace SFW;

use SFW\Config;
use SFW\Utils;
use SFW\Lang;
use SFW\SessionManager;

final class Core {

	const MINIMUM_PHP_VERSION = "5.5.0";

	const DEFAULT_OPTIONS = [
		"minimum_php_version" => Core::MINIMUM_PHP_VERSION,
		"redirect_https" => true,
		"init_languages" => true,
		"start_session" => false
	];

	private static $app_name = null;
	private static $app_base_dir = null;
	private static $app_options = [];

	public static function start_application( $app_name, $app_base_dir, $app_options = [] ) {

		if ( self::$app_name !== null ) die( "Application already started" );

		self::$app_base_dir = realpath( $app_base_dir );

		// Apply default options
		Utils::apply_default_options( $app_options, Core::DEFAULT_OPTIONS );

		// Check versions
		$minimum_php_version = $app_options["minimum_php_version"];
		if ( version_compare( $minimum_php_version, Core::MINIMUM_PHP_VERSION, "lt" ) ) {
			die( "Invalid minimum php version given, PHP " . Core::MINIMUM_PHP_VERSION . "+ required. Given version : " . $minimum_php_version );
		} else if ( version_compare( PHP_VERSION, $minimum_php_version, "lt" ) ) {
			die( "PHP {$minimum_php_version}+ required. Currently installed version is : " . phpversion() );
		}

		// App name
		self::$app_name = $app_name;

		// PHP Configuration
		@ini_set( 'display_errors', 1 );
		@error_reporting( E_ALL );
		@ini_set( 'file_uploads', 1 );
		@ini_set('default_charset', 'utf-8');
		@mb_internal_encoding('utf-8');

		if ( boolval( $app_options["redirect_https"] ) && isset( $_SERVER["HTTPS"] ) != boolval( Config::get("global:secure") ) ) {

			Utils::redirect( Config::get_advised_url() . $_SERVER["REQUEST_URI"] );
			die();

		}

		// Init languages if selected
		if ( boolval( $app_options["init_languages"] ) ) Lang::init_languages();

		// Start session if selected
		if ( boolval( $app_options["start_session"] ) ) SessionManager::session_start();

	}

	public static function check_app_ready() {
		if ( self::$app_name === null ) throw new Exception( "Application not started" );
	}

	public static function get_app_base_dir() {
		return self::$app_base_dir;
	}

	public static function get_app_path( ...$paths ) {
		array_unshift( $paths, self::$app_base_dir );
		return Utils::path_join( $paths );
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
