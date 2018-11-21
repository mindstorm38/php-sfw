<?php

// Core class file

namespace SFW;

use \Exception;

final class Core {
	
	const MINIMUM_PHP_VERSION = "5.5.0";
	
	const DEFAULT_PAGES_DIR = "src/pages/";
	const DEFAULT_TEMPLATES_DIR = "src/templates/";
	
	private static $app_name = null;
	private static $app_base_dir = null;
	
	private static $minimum_php_version = Core::MINIMUM_PHP_VERSION;
	private static $pages_dir = Core::DEFAULT_PAGES_DIR;
	private static $templates_dir = Core::DEFAULT_TEMPLATES_DIR;
	private static $redirect_https = true;
	private static $init_languages = true;
	private static $start_session = false;
	
	private static $pages_aliases = [];
	private static $pages_templates = [];
	
	public static function start_application( $app_name, $app_base_dir ) {
		
		if ( self::$app_name !== null ) die( "Application already started" );
		
		self::$app_base_dir = realpath( $app_base_dir );
		
		// Check versions
		$minimum_php_version = self::$minimum_php_version;
		
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
		
		if ( self::$redirect_https && isset( $_SERVER["HTTPS"] ) != boolval( Config::get("global:secure") ) ) {
			
			Utils::redirect( Config::get_advised_url() . $_SERVER["REQUEST_URI"] );
			die();
			
		}
		
		// Init languages if selected
		if ( self::$init_languages )
			Lang::init_languages();
			
			// Start session if selected
			if ( self::$start_session )
				SessionManager::session_start();
				
	}
	
	// Options
	
	public static function set_minimum_php_version( $minimum_php_version ) {
		self::$minimum_php_version = $minimum_php_version;
	}
	
	public static function set_pages_dir( $pages_dir ) {
		self::$pages_dir = $pages_dir;
	}
	
	public static function set_templates_dir( $templates_dir ) {
		self::$templates_dir = $templates_dir;
	}
	
	public static function set_redirect_https( $redirect_https ) {
		self::$redirect_https = $redirect_https;
	}
	
	public static function set_init_languages( $init_languages ) {
		self::$init_languages = $init_languages;
	}
	
	public static function set_start_session( $start_session ) {
		self::$start_session = $start_session;
	}
	
	// App
	
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
	
	// Page Loading
	
	public static function add_page_aliases( string $id, ...$aliases ) {
		foreach ( $aliases as $alias )
			self::$pages_aliases[ $alias ] = $id;
	}
	
	public static function set_page_template( string $id, $template ) {
		self::$pages_templates[ $id ] = $template;
	}
	
	public static function get_page_alias( string $id ) {
		return array_key_exists( $id, self::$pages_aliases ) ? self::$pages_aliases[ $id ] : $id;
	}
	
	public static function get_page_template( string $id ) {
		return array_key_exists( $id, self::$pages_templates ) ? self::$pages_templates[ $id ] : null;
	}
	
	public static function load_page( string $raw_id ) {
		
		$page = new Page( $raw_id, self::get_page_alias( $raw_id ) );
		
		$page->directory = self::get_app_path( self::$pages_dir, $page->identifier );
		$page->template_identifier = self::get_page_template( $page->identifier );
		$page->template_directory = self::get_app_path( self::$templates_dir, $page->template_identifier );
		
		return $page;
		
	}
	
	// Utils
	
	public static function redirect_base( string $path = "" ) {
		Utils::redirect( Config::get_advised_url( $path ) );
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
