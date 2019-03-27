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
	public static function get_file() {
		if ( self::$file === null ) {
			self::$file = Core::get_app_path( Config::CONFIG_FILE_PATH );
		}
		return self::$file;
	}
	
	public static function config() {
		
		if ( self::$loaded_config === null ) {
			
			self::$loaded_config = @json_decode( file_get_contents( self::get_file() ), true );
			
			if ( self::$loaded_config === null )
				throw new Exception( "Can't found config file at : " . self::get_file() );
				
		}
		
		return self::$loaded_config;
		
	}
	
	public static function get( $path, $default = null ) {
		
		if ( gettype( $path ) !== "string" ) throw new Exception("'key' parameter must be a string");
		
		if ( array_key_exists( $path, self::$cache ) ) return self::$cache[ $path ];
		
		$ret = self::config();
		
		$splited = explode( ":", $path );
		
		foreach ( $splited as $key ) {
			if ( !array_key_exists( $key, $ret ) ) {
				return $default;
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
	
	public static function get_advised_url( string $path = "" ) {
		
		$base_path = '/' . trim( self::get("global:base_path"), '/' );
		if ( strlen( $base_path ) !== 1 ) $base_path .= '/';
		
		return ( self::get("global:secure") ? "https" : "http" ) . "://" . self::get("global:advised_host") . $base_path . ltrim( $path, '/' );
		
	}
	
}

?>
