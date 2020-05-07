<?php

// Utils file

namespace SFW;

use \DateTime;

/**
 *
 * Contains lot of useful utilities.
 *
 * @author Theo Rozier
 *
 */
final class Utils {
	
	/**
	 * @var string All possibles characters for token generation.
	 * @see Utils::generate_random
	 */
	const TOKEN_CHARS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	/**
	 * @var array All extensions associated to the mime type.
	 * @see Utils::get
	 */
	const MIME_TYPE = [
		
		// Standard
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',
		
		// Images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		
		// Archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',
		
		// Audio/Video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',
		
		// Adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',
		
		// MS office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'docx' => 'application/msword',
		'xlsx' => 'application/vnd.ms-excel',
		'pptx' => 'application/vnd.ms-powerpoint',
		
		
		// Open Office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
		
	];
	
	/**
	 * @var string The date format used for HTTP headers.
	 */
	const DATE_FORMAT_RFC2616 = "D, d M Y H:i:s \G\M\T";
	
	/**
	 * Check if this script is not started using Apache, NGINX or other.
	 * @return bool True if started by a HTTP web server.
	 */
	public static function is_manual_running() : bool {
		return !isset( $_SERVER["REQUEST_METHOD"] );
	}
	
	/**
	 * Get command line arguments.
	 * @return array Arguments array.
	 */
	public static function get_running_args() : array {
		return $_SERVER["argv"];
	}
	
	/**
	 * Beautify an URL path by removing all useless slashes and leading slash.
	 * @param string $path The raw path.
	 * @return string Beautified path.
	 */
	public static function beautify_url_path( string $path ) : string {
		return implode('/', array_filter(explode('/', $path)));
	}
	
	/**
	 * Get the request path from the HTTP client.
	 * @return string Requested path.
	 */
	public static function get_request_path() : string {
		return explode( '?', $_SERVER["REQUEST_URI"], 2 )[0];
	}
	
	/**
	 * Get the request path from HTTP client
	 * @return string The requested path relative to the application 'base_path' (from config) or request path if 'base_path' don't appear to be used in it.
	 * @see Utils::get_request_path
	 * @see Config::get_base_path
	 */
	public static function get_request_path_relative() : string {
		
		$base_path = Config::get_base_path();
		$path = self::get_request_path();
		
		return self::starts_with($path, $base_path) ? substr( $path, strlen($base_path) - 1 ) : $path;
		
	}
	
	/**
	 * Generate random token of a specified length and using characters of {@link Utils::TOKEN_CHARS}.
	 * @param number $length Token length (default to 32).
	 * @return string Generated token.
	 */
	public static function generate_random(int $length = 32): string {
		$charactersLength = strlen( self::TOKEN_CHARS );
		$randomString = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$randomString .= self::TOKEN_CHARS[ rand( 0, $charactersLength - 1 ) ];
		}
		return $randomString;
	}
	
	/**
	 * Put input string in lower case, and after put the first character to upper case. Use {@link ucfirst()} to just put
	 * @param string $string Input string.
	 * @return string Output string were all characters are in lower case except first that is in upper case.
	 */
	public static function ucfirst(string $string) : string {
		return ucfirst( strtolower( $string ) );
	}
	
	/**
	 * Secure a file path by replacing two or more following dot with only one.
	 * @param string $path Input path.
	 * @return string Output securized path.
	 */
	public static function secure_path(string $path): string {
		return preg_replace( '@\.\.*@', '.', $path );
	}
	
	/**
	 * Get timestamp in millis, it is just a shortcut for "{@link time()} * 1000".
	 * @return int Timestamp millis.
	 * @see time()
	 */
	public static function get_timestamp_ms() : int {
		return time() * 1000;
	}
	
	/**
	 * Format date using "d/m/Y H:i:s".
	 * @param int $timestamp Date UNIX timestamp.
	 * @return string Formatted date.
	 * @see date()
	 */
	public static function date_format( int $timestamp = null ) : string {
		
		if ($timestamp == null) {
			$timestamp = time();
		}
			
		return date("d/m/Y H:i:s", $timestamp);
			
	}
	
	/**
	 * Defining content type header of the current page.
	 * @param string $type MIME Type.
	 * @see header()
	 */
	public static function content_type( string $type ) : void {
		if ( headers_sent() ) return;
		header("Content-Type: {$type}");
	}
	
	/**
	 * Define page content type to JSON (application/json).
	 */
	public static function content_type_json() : void {
		self::content_type("application/json");
	}
	
	/**
	 * Define page content type to HTML (text/html).
	 */
	public static function content_type_html() : void {
		self::content_type("text/html");
	}
	
	/**
	 * Define page content type to XML (application/xml).
	 */
	public static function content_type_xml() : void {
		self::content_type("application/xml");
	}
	
	/**
	 * Echoing the XML version 1.0 header.
	 */
	public static function print_xml_header() : void {
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	}
	
	/**
	 * Send 3 headers (cache-control, pragam: no-chache, expires) to force user agent to download page.
	 * @see header()
	 */
	public static function force_no_cache() : void {
		if ( headers_sent() ) return;
		trigger_error("Utils::force_no_cache method is now deprecated", E_USER_DEPRECATED);
		header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
	}
	
	/**
	 * Get a date formatted using right HTTP header date format (RFC2616).
	 * @param int $time The UNIX timestamp.
	 * @return string Formatted date
	 */
	public static function get_http_header_date( int $time ) : string {
		return gmdate(self::DATE_FORMAT_RFC2616, $time);
	}
	
	/**
	 * Parse a date formatted using HTTP header date format (RFC2616) and return an UNIX timestamp.
	 * @param string $date The formatted HTTP header date.
	 * @return int The UNIX timestamp parsed.
	 */
	public static function parse_http_header_date( string $date ) : int {
		return DateTime::createFromFormat(self::DATE_FORMAT_RFC2616, $date, new \DateTimeZone("GMT"))->getTimestamp();
	}
	
	/**
	 * Send redirect header to specified path.
	 * @param mixed $path Redirect path.
	 * @see header()
	 */
	public static function redirect( $path ) {
		if ( headers_sent() ) return;
		header("Location: {$path}");
	}
	
	/**
	 * Check if given variable is null, if true, return the value otherwise return variable itself.
	 * @param mixed $var Specified variable (except function).
	 * @param mixed $else The default value or a callable object (like function), in that case, it return the return value of its.
	 * @return mixed Checked value.
	 */
	public static function check_not_null( $var, $else ) {
		return $var == null ? ( is_callable( $else ) ? $else() : $else ) : $var;
	}
	
	/**
	 * Check if haystack string is starting with needle string.
	 * @param string $haystack The haystack string.
	 * @param string $needle The needle string.
	 * @return boolean True if haystack starting with needle.
	 */
	public static function starts_with( string $haystack, string $needle ) : bool {
		return $needle === "" || substr($haystack, 0, strlen($needle)) === $needle;
	}
	
	/**
	 * Check if haystack string is ending with needle string.
	 * @param string $haystack The haystack string.
	 * @param string $needle The needle string.
	 * @return boolean True if haystack ending with needle.
	 */
	public static function ends_with( $haystack, $needle ) : bool {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	
	/**
	 * Check if given string is containing search string.
	 * @param string $string Input string.
	 * @param string $search Search string.
	 * @return bool True if input string contains
	 */
	public static function contains( string $string, string $search ) : bool {
		if ( $search === "" ) return true;
		return strpos( $string, $search ) !== false;
	}
	
	/**
	 * Check if input var is an associative array or not.
	 * @param mixed $arr Input variable.
	 * @return boolean True if var is an associative array.
	 */
	public static function is_assoc_array( $arr ) : bool {
		if ( !is_array( $arr ) ) return false;
		return ( array_values( $arr ) !== $arr );
	}
	
	/**
	 * Hash password using PHP's {@link PASSWORD_DEFAULT}.
	 * @param string $raw Raw unhashed password.
	 * @return string Password hash.
	 * @see password_hash()
	 */
	public static function encrypt_password( string $raw ) : string {
		return password_hash( $raw, PASSWORD_DEFAULT );
	}
	
	/**
	 * Verify a password hash generated using {@link Utils::encrypt_password} or {@link verify_password()}.
	 * @param string $raw Raw unhashed password.
	 * @param string $hash Password hash.
	 * @return boolean True if unhashed password if corresponding to password hash.
	 * @see password_verify()
	 */
	public static function verify_password( string $raw, string $hash ) : bool {
		return password_verify( $raw, $hash );
	}
	
	/**
	 * Quote HTML entities using {@link htmlentities()} with ENT_QUOTES and ENT_HTML401
	 * @param string $str Input string.
	 * @return string HTML Quoted.
	 */
	public static function html_entities( string $str ) : string {
		return htmlentities( $str, ENT_QUOTES | ENT_HTML401 );
	}
	
	/**
	 * Just act like {@link trim()}, use it instead (TODO: Check if this function core can be replaced with trim).
	 * @param string $str Input string.
	 * @return string Filtered string.
	 * @see trim()
	 */
	public static function str_filter( string $str ) : string {
		return preg_replace( "/^(?: +)/", "", preg_replace( "/(?: +)$/", "", $str ) );
	}
	
	/**
	 * Get string length after filtering (see {@link Utils::str_filter}).
	 * @param string $str Input string.
	 * @return int Filtered string length.
	 * @see Utils::str_filter
	 */
	public static function str_length( string $str ) : int {
		return strlen( self::str_filter( $str ) );
	}
	
	/**
	 * Check if the string is empty after filtering (see {@link Utils::str_filter}).
	 * @param string $str
	 * @return boolean True if the filtered string is empty.
	 * @see Utils::str_filter
	 * @see empty()
	 */
	public static function str_empty( string $str ) : bool {
		return empty( self::str_filter( $str ) );
	}
	
	/**
	 * Get a file path extension or empty string of no extension.
	 * @param string $file_path Input file path.
	 * @return mixed Extension without starting point.
	 * @see pathinfo()
	 */
	public static function get_file_extension( string $file_path ) : string {
		return pathinfo( $file_path, PATHINFO_EXTENSION );
	}
	
	/**
	 * Get a file mime type from the path or just the name.
	 * @param string $file_path File path or name.
	 * @return string File extension mime type or <code>application/octet-stream</code>.
	 */
	public static function get_file_mime_type( string $file_path ) : string {
		return self::MIME_TYPE[ self::get_file_extension($file_path) ] ?? "application/octet-stream";
	}
	
	/**
	 * Check whether a file exists and readable at a path.
	 * @param string $path Input file path.
	 * @return boolean True if the file exists and readable.
	 * @see file_exists()
	 * @see is_readable()
	 */
	public static function file_exists( $path ) : bool {
		return is_readable( $path );
	}
	
	/**
	 * Using "require_once" if the path exists (using {@link Utils::file_exists}).
	 * Warning : This can break your include because of path file is included in the private method scope.
	 * @param string $path
	 * @see Utils::file_exists
	 * @see require_once()
	 */
	public static function require_if_exists( string $path ) {
		if ( self::file_exists( $path ) ) require_once $path;
	}
	
	/**
	 * Using "include_once" if the path exists (using {@link Utils::file_exists}).
	 * Warning : This can break your include because of path file is included in the private method scope.
	 * @param string $path
	 * @see Utils::file_exists
	 * @see include_once()
	 */
	public static function include_if_exists( string $path ) : void {
		if ( self::file_exists( $path ) ) include_once $path;
	}
	
	/**
	 * Parse string range of the following pattern : "min-max", "min-", "-max".
	 * @param string $str Input string to parse.
	 * @param array $minmax Minimum and maximum values to use if one or both ranges sides is/are not specified.
	 * @param array $defaults Defaults values to return if the format is incorrect, but not if one of two range parts are not valid number.
	 * @return int[]|null The result range, index 0 is the min value, index 1 is the max value. Or null if min or max are not valid number.
	 */
	public static function parse_range( string $str, array $minmax = [], array $defaults = null ) {
		
		$parts = explode( '-', $str );
		$length = count( $parts );
		
		if ( $length !== 2 ) return $defaults;
		
		$min = $parts[0];
		$max = $parts[1];
		
		if ( $min === '' ) {
			$min = $minmax[0] ?? null;
		} else if ( is_numeric( $min ) ) {
			$min = floatval( $min );
		} else return null;
		
		if ( $max === '' ) {
			$max = $minmax[1] ?? null;
		} else if ( is_numeric( $max ) ) {
			$max = floatval( $max );
		} else return null;
		
		return [ $min, $max ];
		
	}
	
	/**
	 * Join multiples paths.
	 * @param mixed ...$raw_paths All paths to join.
	 * @return string Joined paths.
	 */
	public static function path_join( ...$raw_paths ) : string {
		
		$paths = array();
		
		foreach ( $raw_paths as $raw_path ) {
			if ( is_array( $raw_path ) ) {
				foreach ( $raw_path as $raw_raw_path ) {
					if ( $raw_raw_path !== '' ) $paths[] = $raw_raw_path;
				}
			} else if ( $raw_path !== '' ) {
				$paths[] = $raw_path;
			}
		}
		
		return preg_replace( '#/+#', '/', join( '/', $paths ) );
		
	}
	
	/**
	 * Format string using "{<optionnal_idx>}" format segments.
	 * @param string $format The string to be formatted.
	 * @param array $args Arguments used to replace segments.
	 * @return string Formatted string.
	 */
	public static function str_format( string $format, array $args ) : string {
		
		$format = preg_replace_callback( '/(?:\{?\{(\d)\}\}?)/', function( $matches ) use( $args ) {
			if ( count( $matches ) !== 2 ) return "";
			$n = intval( $matches[1] );
			return $args[ $n ];
		}, $format );
			
			return $format;
			
	}
	
	/**
	 * Apply defaults keys to an associative array.
	 * @param array $assoc_array The associative array to modify.
	 * @param array $default_options Defaults associative array.
	 */
	public static function apply_default_options( array &$assoc_array, array $default_options ) : void {
		foreach ( $default_options as $key => $value ) {
			if ( !isset( $assoc_array[$key] ) ) {
				$assoc_array[ $key ] = $default_options[ $key ];
			}
		}
	}
	
	/**
	 * <p><b>[[ DEPRECATED ]]</b></p>
	 * Do same as {@link Utils::get_request_path}.
	 * @return string The requested path.
	 * @see Utils::get_request_path
	 */
	public static function get_request_uri() : string {
		
		trigger_error( "This function is deprecated.", E_USER_DEPRECATED );
		return self::get_request_path();
		
	}
	
	/**
	 * Extract specific key from an array of associative array.
	 * @param array $arrays The array containing all associative arrays where <code>$prop_name</code> are set.
	 * @param string $prop_name The key name to extract and place in a simple array.
	 * @return array All values of the specified key in associative arrays in <code>$arrays</code>.
	 */
	public static function make_arrays_prop_list( array $arrays, string $prop_name ) : array {
		
		$arr = [];
		
		foreach ( $arrays as $value ) {
			$arr[] = $value[ $prop_name ];
		}
		
		return $arr;
		
	}
	
	/**
	 * Get accept languages ordered by quality factor. Return empty if not given in server constants.
	 * @return array Accepted locales by the user agent stored in an array of objects following this format : ["id" => <id>, "q" => <quality_factor>].
	 */
	public static function parse_accept_languages() : array {
		
		if ( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
			return [];
		}
			
		$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$split_langs = explode(",", $lang);
		
		$ret = [];
		
		foreach ( $split_langs as $split_lang ) {
			
			$lang_params = explode(";", $split_lang);
			
			$q = 1;
			
			if ( count($lang_params) >= 2 && substr($lang_params[1], 0, 2) == "q=" ) {
				$q = floatval(substr($lang_params[1], 2));
			}
			
			$ret[] = [
				"id" => $lang_params[0],
				"q" => $q
			];
			
		}
		
		usort($ret, function($a, $b) {
			return $b["q"] - $a["q"];
		});
			
		return $ret;
				
	}

	public static function has_do_not_track(): bool {
		return $_SERVER["HTTP_DNT"] != "0";
	}
	
	// Array Page functions
	
	public static function get_page_infos( array $array, $items_per_page, $pages_offset, $current_page, $filter = null, $fillEmpty = false ) {
		
		$array = $filter == null ? $array : array_filter( $array, $filter );
		
		$pages_count = max( ceil( count( $array ) / $items_per_page) - 1, 0 );
		
		$current_page = max( 0, min( $pages_count, $current_page ) );
		
		$minimum = $current_page * $items_per_page;
		$maximum = ( $current_page + 1 ) * $items_per_page - 1;
		
		$pagination = range( max( 0, $current_page - $pages_offset ), min( $pages_count, $current_page + $pages_offset ) );
		array_unshift( $pagination, 0 );
		$pagination[] = $pages_count;
		
		$array = array_slice( $array, $minimum, $items_per_page );
		
		if ( $fillEmpty ) {
			while ( count( $array ) < $items_per_page ) {
				$array[] = null;
			}
		}
		
		return [
			"page" => $current_page,
			"array" => $array,
			"pagination" => $pagination
		];
		
	}
	
	public static function print_page_content( array $page_infos, $print_function ) {
		foreach ( $page_infos["array"] as $array_elt ) {
			$print_function( $array_elt );
		}
	}
	
	public static function print_page_pagination( array $page_infos, $print_function ) {
		$pagination = $page_infos["pagination"];
		$count = count( $pagination );
		for ( $i = 0; $i < $count; $i += 1 ) {
			$print_function( $i == 0 ? "first" : ( $i == ( $count - 1 ) ? "last" : "range" ), $pagination[ $i ] );
		}
	}
	
	public static function filter_content( $content ) {
		return $content;
	}
	
}

?>
