<?php

// Load config.json file and provide keys / values

namespace SFW;

use \Exception;

/**
 *
 * Used to load "config.json" (in application root directory) and provide methods to get values from keys paths.
 *
 * @author Theo Rozier
 *
 */
final class Config {
	
	/**
	 * Config file name in application root directory.
	 * @var string
	 */
	const CONFIG_FILE_PATH = "config.json";
	
	private static $loaded_config = null;
	private static $file = null;
	private static $cache = [];
	
	/**
	 * Get the absolute file path (using {@link Core::get_app_path}).
	 * @return string The config file path.
	 * @see Core::get_app_path
	 */
	public static function get_file() : string {
		
		if ( self::$file === null ) {
			
			Core::check_app_ready();
			self::$file = Core::get_app_path( Config::CONFIG_FILE_PATH );
			
		}
		
		return self::$file;
		
	}
	
	/**
	 * Get the configuration arrays representation (from "config.json").
	 * @throws Exception If can't decode or can't find the config file.
	 * @return array Array representing JSON configuration.
	 */
	public static function config() {
		
		if ( self::$loaded_config === null ) {
			
			self::$loaded_config = @json_decode( file_get_contents(self::get_file()), true );
			
			if ( !is_array(self::$loaded_config) ) {
				throw new Exception( "Can't found config file at : " . self::get_file() . ", main element must be a JSON object." );
			}
			
		}
		
		return self::$loaded_config;
		
	}
	
	/**
	 * Get a configuration value (there is no way for navigating in JSON array, but you can get them).
	 * @param string $path Path of the configuration key, separate levels using ":". Like "root:key".
	 * @param mixed $default Default value.
	 * @throws Exception See {@link Config::config}
	 * @return mixed Key value.
	 */
	public static function get( string $path, $default = null ) {
		
		/*
		if ( gettype( $path ) !== "string" ) {
			throw new Exception("The 'key' parameter must be a string");
		}
		*/
		
		if ( array_key_exists( $path, self::$cache ) ) {
			return self::$cache[ $path ];
		}
		
		$ret = self::config();
		
		$splited = explode( ":", $path );
		
		foreach ( $splited as $key ) {
			
			if ( !array_key_exists( $key, $ret ) ) {
				
				$ret = $default;
				break;
				
			}
			
			$ret = $ret[ $key ];
			
		}
		
		self::$cache[ $path ] = $ret;
		
		return $ret;
		
	}
	
	// EXPERIMENTAL
	public static function get_advised_hosts() {
		
		$v = self::get( "global:advised_host", $_SERVER["SERVER_NAME"] );
		return is_array( $v ) ? $v : [ $v ];
		
	}
	
	/**
	 * Get the advised URL of the website from the configuration file. Optionaly pass a path to append to the URL.
	 * @param string $path Optional path to append.
	 * @return string The valid advised URL.
	 */
	public static function get_advised_url( string $path = "" ) : string {
		return ( self::is_secure() ? "https" : "http" ) . "://" . self::get_advised_host() . self::get_base_path() . "/" . Utils::beautify_url_path($path);
	}
	
	// Global configuration
	
	/**
	 * @return bool If the config require secure HTTPS. False by default.
	 */
	public static function is_secure() : bool {
		return boolval( Config::get("global:secure", false) );
	}
	
	/**
	 * @return string The advised host, or the current host by default.
	 */
	public static function get_advised_host() : string {
		return Config::get( "global:advised_host", $_SERVER["SERVER_NAME"] );
	}
	
	/**
	 * @return string The base path where the website can be accessed.
	 */
	public static function get_base_path() : string {
		return "/" . Utils::beautify_url_path( Config::get( "global:base_path", "" ) );
	}
	
}

?>
