<?php

// Lang file

namespace SFW;

use SFW\Core;
use SFW\Utils;

final class Lang {

	const LANGS_FOLDER				= "langs/";
	const JSON_FILE					= "langs.json";

	const LANG_FILE_COMMENT			= "/^(.*)#/";
	const LANG_FILE_LINE			= "/^([a-zA-Z0-9_.-]+)=(.+)$/";

	// 'F' for 'Formatting'
	const F_JAVASCRIPT				= "javascript";
	const F_HTML					= "html";

	private static $default_lang = null;
	private static $folder = null;
	private static $langs = [];
	private static $current_lang = null;
	private static $initied = false;

	public static get_folder() {
		if ( self::$folder === null ) {
			Core::check_app_ready();
			self::$folder = Core::get_app_path( Lang::LANGS_FOLDER );
		}
		return self::$folder;
	}

	public static function check_folder() {
		if ( !is_dir( self::get_folder() ) ) {
			mkdir( self::get_folder() );
		}
	}

	public static function check_initied() {
		if ( !self::$initied ) throw new Exception("Language system not initied");
	}

	public static function is_initied() {
		return self::$initied;
	}

	public static function get_languages() {
		return self::$langs;
	}

	public static function init_languages() {

		self::$initied = false;

		self::check_folder();

		self::$default_lang = null;
		self::$langs = [];
		$json_content = @file_get_contents( self::get_folder() . Lang::JSON_FILE );

		if ( $json_content == null ) {
			Core::fatal_error( "Create '" . self::get_folder() . Lang::JSON_FILE . "' before using language system" );
			return;
		}

		$json = json_decode( $json_content, true );

		foreach ( $json as $lang_obj ) {
			self::$langs[] = $lang_obj;
			if ( isset( $lang_obj["default"] ) && $lang_obj["default"] == true ) {
				self::$default_lang = $lang_obj["identifier"];
			}
		}

		if ( count (self::$langs ) == 0 ) {
			Core::fatal_error( "Create minimum one language file before using language system" );
			return;
		}

		if ( self::$default_lang == null ) self::$default_lang = self::$langs[0];

		self::$initied = true;

	}

	public static function get_default_language() {
		return self::$default_lang;
	}

	public static function get_language( $identifier ) {
		foreach ( self::$langs as $lang_obj ) if ( $lang_obj["identifier"] == $identifier ) return $lang_obj;
		return null;
	}

	public static function get_language_content( $identifier ) {

		self::check_initied();

		$lang = self::get_language( $identifier );

		if ( $lang == null ) return null;

		if ( isset( $lang["datas"] ) && $lang["datas"] != null ) return $lang["datas"];

		$lang_content = @file_get_contents( self::get_folder() . $identifier . ".lang" );

		if ( $lang_content == null ) return null;

		return $lang["datas"] = self::parse_content( $lang_content );

	}

	public static function get_current_language() {
		self::check_initied();
		if ( self::$current_lang == null ) self::$current_lang = self::$default_lang;
		return self::$current_lang;
	}

	public static function set_current_language( $identifier ) {
		if ( self::get_language( $identifier ) != null ) self::$current_lang = $identifier;
	}

	public static function parse_content( $string_content ) {

		$arr = [];

		$file_lines = explode( PHP_EOL, $string_content );

		foreach ( $file_lines as $line ) {

			$matches = [];

			if ( preg_match( Lang::LANG_FILE_COMMENT, $line ) === 1 ) {
				continue;
			} else if ( preg_match( Lang::LANG_FILE_LINE, $line, $matches ) === 1 ) {
				$arr[ $matches[ 1 ] ] = $matches[ 2 ];
				continue;
			}

		}

		return $arr;

	}

	public static function get($key, array $vars = [], $formatting = null, $lang = null) {

		self::check_initied();

		if ( $lang == null ) $lang = self::get_current_language();
		$array = self::get_language_content( $lang );
		if ( $array == null ) return $key;
		if ( !array_key_exists( $key, $array ) ) return $key;
		$ret = Utils::str_format( $array[ $key ], $vars );

		if ( $formatting !== null ) {

			switch ( $formatting ) {
				case Lang::F_JAVASCRIPT:
					$ret = addslashes( $ret );
					break;
				case Lang::F_HTML:
					$ret = Utils::html_entities( $ret );
					break;
			}

		}

		return $ret;

	}

}

?>
