<?php

// Load config.json file and provide keys / values

namespace SFW;

use SFW\Core;
use \Exception;

final class Config {

	const CONFIG_FILE_PATH = "config.json";

	private static $loaded_config = null;
	private static $file = null;
	private static $cache = [];

	public static function get_file() {
		if ( self::$file === null ) {
			Core::check_app_ready();
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

	public static function get_advised_url() {
		return ( self::get("global:secure") ? "https" : "http" ) . "://" . self::get("global:advised_host") . '/' . trim( self::get("global:base_path"), '/' );
	}

}

?>
