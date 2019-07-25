<?php

// Lang file

namespace SFW;

use \Exception;

/**
 * 
 * <p>Managing Language, now using resource handlers of the core application to allow PHP-SFW to create default languages files.</p>
 * 
 * @author Théo Rozier
 *
 */
final class Lang {
	
	const LANGS_DIR            = "langs/";
	const LANGS_FOLDER         = Lang::LANGS_DIR;
	const JSON_FILE            = "langs.json";
	const LANG_EXT             = ".lang";
	
	const LANG_FILE_COMMENT	   = "/^(.*)#/";
	const LANG_FILE_LINE       = "/^([a-zA-Z0-9_.-]+)=(.+)$/";
	
	// 'F' for 'Formatting'
	const F_JAVASCRIPT         = "javascript";
	const F_HTML               = "html";
	
	private static $default_lang = null;
	private static $langs = [];
	private static $initied = false;
	
	private static $current_lang = null;
	
	private static $folder = null;
	
	public static function get_folder() {
		
		trigger_error("Not longer used, now using core application resources.", E_USER_DEPRECATED);
		
		if ( self::$folder === null ) {
			
			Core::check_app_ready();
			self::$folder = Core::get_app_path( Lang::LANGS_FOLDER );
			
		}
		
		return self::$folder;
		
	}
	
	public static function check_folder() {
		
		trigger_error("Not longer used, now using core application resources.", E_USER_DEPRECATED);
		
		if ( !is_dir( self::get_folder() ) ) {
			mkdir( self::get_folder() );
		}
		
	}
	
	/**
	 * Check if languages are initialized, if not, throw an exception.
	 * @throws \Exception Language system not initialized.
	 */
	public static function check_initied() {
		
		if ( !self::$initied ) {
			throw new Exception("Language system not initialized.");
		}
		
	}
	
	/**
	 * Get initialization status of the language manager.
	 * @return boolean Is languages initialized.
	 */
	public static function is_initied() : bool {
		return self::$initied;
	}
	
	/**
	 * Get all loaded languages.
	 * @return array An associative array with languages identifiers as keys and languages 'languages' and 'country' as values (and cached files and data).
	 */
	public static function get_languages() : array {
		return self::$langs;
	}
	
	/**
	 * <p>Load language from resource handlers from {@link \SFW\Core}.</p>
	 * <p>Languages are overwritten following the resources handlers registering order (application is always the last to be read).</p>
	 */
	public static function init_languages() : void {
		
		Core::check_app_ready();
		
		self::$initied = false;
		
		self::$default_lang = null;
		self::$langs = [];
		
		foreach ( Core::get_resources_handlers() as $resource ) {
			
			$json_path = $resource->get_file_safe( self::LANGS_DIR . self::JSON_FILE );
			if ( $json_path === null ) continue;
			
			$json_content = @file_get_contents($json_path);
			
			if ( !is_string($json_content) ) {
				
				Logger::warning("Failed to read json language file at '{$json_path}'.");
				continue;
				
			}
			
			$json = json_decode( $json_content, true );
			
			if ( !is_array( $json ) ) {
				
				Logger::warning("Failed to read json language file at '{$json_path}' : main object must be JSON array.");
				continue;
				
			}
			
			foreach ( $json as $language_block ) {
				
				if ( !is_array($language_block) ) {
					
					Logger::warning("Failed to read json language in file '{$json_path}' : language blocks must be JSON objects.");
					continue;
					
				}
				
				if ( !isset( $language_block["identifier"] ) && is_string( $language_block["identifier"] ) ) {
					
					Logger::warning("Failed to read json language in file '{$json_path}' : language block must contains a JSON string 'identifier'.");
					continue;
					
				}
				
				$identifier = $language_block["identifier"];
				
				if ( !isset( self::$langs[$identifier] ) ) {
					
					self::$langs[$identifier] = [
						"language" => $identifier,
						"country" => "",
						"files" => [],
						"data" => null,
						"http" => []
					];
					
				}
				
				$lang_path = $resource->get_file_safe( self::LANGS_DIR . $identifier . self::LANG_EXT );
				
				if ( $lang_path !== null ) {
					self::$langs[$identifier]["files"][] = $lang_path;
				}
				
				if ( isset( $language_block["language"] ) && is_string( $language_block["language"] ) ) {
					self::$langs[$identifier]["language"] = $language_block["language"];
				}
				
				if ( isset( $language_block["country"] ) && is_string( $language_block["country"] ) ) {
					self::$langs[$identifier]["country"] = $language_block["country"];
				}
				
				if ( isset( $language_block["default"] ) && $language_block["default"] === true ) {
					self::$default_lang = $identifier;
				}
				
				if ( isset( $language_block["http"] ) && is_array($language_block["http"]) ) {
					
					foreach ( $language_block["http"] as $locale => $active ) {
						self::$langs[$identifier]["http"][$locale] = $active;
					}
					
				}
				
			}
			
		}
		
		if ( self::$default_lang == null && count(self::$langs) !== 0 ) {
			self::$default_lang = array_keys( self::$langs )[0];
		}
		
		self::$initied = true;
		
	}
	
	/**
	 * Get the default language name loaded.
	 * @return null|string Default language name or null if languages aren't loaded.
	 */
	public static function get_default_language() : ?string {
		return self::$default_lang;
	}
	
	/**
	 * Get a language description, its 'language' and its 'country'.
	 * @param string $identifier The identifier of the language.
	 * @return string|null Language description or null if this language is not loaded.
	 */
	public static function get_language( string $identifier ) : ?array {
		return self::$langs[$identifier] ?? null;
	}
	
	/**
	 * Check if a language exists.
	 * @param string $identifier The language identifier.
	 * @return bool True if the language is loaded.
	 */
	public static function language_exists( string $identifier ) : bool {
		return isset( self::$langs[$identifier] );
	}
	
	/**
	 * Parse and get the content of a language.
	 * @param string $identifier The identifier of the language.
	 * @return null|string[] All language entries, or null if file is invalid.
	 */
	public static function get_language_content( string $identifier ) : ?array {
		
		self::check_initied();
		
		$lang = self::get_language( $identifier );
		
		if ( $lang == null ) {
			return null;
		}
		
		if ( isset( $lang["data"] ) ) {
			return $lang["data"];
		}
		
		if ( count($lang["files"]) === 0 ) {
			
			Logger::warning("The language '{$identifier}' isn't describe in any language file.");
			return null;
			
		}
		
		$lang["data"] = [];
		
		foreach ( $lang["files"] as $file ) {
			
			$lang_content = @file_get_contents( $file );
			
			if ( !is_string($lang_content) ) {
				
				Logger::warning("Failed to find a cached language file '{$file}'.");
				continue;
				
			}
			
			$lang["data"] = array_merge( $lang["data"], self::parse_content( $lang_content ) );
			
		}
		
		return $lang["data"];
		
	}
	
	/**
	 * Get the current language entries.
	 * @return array|null Current language entries or null if no current language (see <code>get_current_language</code>) or errors while getting language content.
	 */
	public static function get_current_language_content() : ?array {
		
		$name = self::get_current_language();
		return $name == null ? null : self::get_language_content($name);
		
	}
	
	/**
	 * Get the current language name.
	 * @return null|string Current language name or null if there is no default language. 
	 */
	public static function get_current_language() : ?string {
		
		self::check_initied();
		
		if ( self::$current_lang == null ) {
			self::$current_lang = self::$default_lang;
		}
		
		return self::$current_lang;
		
	}
	
	/**
	 * Set the current used language.
	 * @param string $identifier The language identifier to set.
	 * @return bool True if the language exists and has been set.
	 */
	public static function set_current_language( string $identifier ) : bool {
		
		if ( self::language_exists($identifier) ) {
			
			self::$current_lang = $identifier;
			return true;
			
		} else {
			return false;
		}
		
	}
	
	/**
	 * Get a language identifier from the http locale.
	 * @param string $http_locale HTTP locale to check.
	 * @return string|null The language identifier.
	 */
	public static function get_language_from_http_locale(string $http_locale) : ?string {
		
		foreach ( self::$langs as $identifier => $infos ) {
			if ( isset($infos["http"][$http_locale]) && $infos["http"][$http_locale] === true ) {
				return $identifier;
			}
		}
		
		return null;
		
	}
	
	/**
	 * Set the current used language using its HTTP locale.
	 * @param string $http_locale The HTTP locale to search for.
	 * @return bool True if the languages exists and has been set.
	 * @see Lang::set_current_language
	 */
	public static function set_current_language_from_http_locale(string $http_locale) : bool {
		
		$id = self::get_language_from_http_locale($http_locale);
		return $id === null ? false : self::set_current_language($id);
		
	}
	
	/**
	 * Set current language using {@link Lang::set_current_language_from_http_locale} and locales provided by user agent using "Accept-Language" header.
	 */
	public static function set_current_language_from_accept_languages() {
		
		foreach ( Utils::parse_accept_languages() as $lang ) {
			if ( self::set_current_language_from_http_locale($lang["id"]) ) {
				return;
			}
		}
		
	}
	
	/**
	 * Parse the content of a string following language format into entries array.
	 * @param string $string_content Language format string.
	 * @return string[] An associative array of languages entries.
	 */
	public static function parse_content( string $string_content ) : array {
		
		$arr = [];
		
		$file_lines = explode( PHP_EOL, $string_content );
		
		foreach ( $file_lines as $line ) {
			
			$matches = [];
			
			if ( preg_match( Lang::LANG_FILE_COMMENT, $line ) === 1 ) {
				continue;
			} else if ( preg_match( Lang::LANG_FILE_LINE, $line, $matches ) === 1 ) {
				$arr[ $matches[ 1 ] ] = trim( $matches[ 2 ] );
				continue;
			}
			
		}
		
		return $arr;
		
	}
	
	/**
	 * Get a language entry by its key.
	 * @param string $key The key of te language entry.
	 * @param array $vars Variables for formatting.
	 * @param string|null $formatting The special formatting to apply to final formatted string.
	 * @param string|null $lang The language to use, if not specified the current language is used.
	 * @return string The formatted entry or the key itself if no entry was found.
	 * @see \SFW\Utils::str_format
	 */
	public static function get( string $key, array $vars = [], string $formatting = null, string $lang = null) {
		
		self::check_initied();
		
		if ( $lang == null ) {
			$lang = self::get_current_language();
		}
		
		$content = self::get_language_content($lang);
		
		if ( $content == null ) {
			return $key;
		}
		
		$ret = isset( $content[$key] ) ? Utils::str_format( $content[$key], $vars ) : $key;
		
		if ( $formatting != null ) {
			
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
