<?php

// Utils file

namespace SFW;

final class Utils {

	public static function generate_random( $length = 32 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}
		return $randomString;
	}

     public static function ucfirst( $string ) {
          return ucfirst( strtolower( $string ) );
     }

	public static function secure_path( $path ) {
		return preg_replace( '@\.\.*@', '.', $path );
	}

	public static function get_timestamp_ms() {
		return time() * 1000;
	}

	public static function date_format( $timestamp = null ) {
		if ( $timestamp == null ) $timestamp = time();
		return date( "d/m/Y H:i:s", $timestamp );
	}

	public static function content_type_json() {
		if ( headers_sent() ) return;
		header("Content-Type: application/json");
	}

	public static function content_type_html() {
		if ( headers_sent() ) return;
		header("Content-Type: text/html");
	}

	public static function force_no_cache() {
		if ( headers_sent() ) return;
		header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
	}

	public static function redirect( $path ) {
		if ( headers_sent() ) return;
		header("Location: {$path}");
	}

	public static function check_not_null( $var, $else ) {
		return $var == null ? ( is_callable( $else ) ? $else() : $else ) : $var;
	}

	public static function starts_with( $haystack, $needle ) {
		return $needle === "" || strrpos($haystack, $needle, -strlen( $haystack ) ) !== false;
	}

	public static function ends_with( $haystack, $needle ) {
		return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && strpos( $haystack, $needle, $temp ) !== false );
	}

	public static function contains( $string, $search ) {
		if ( empty( $search ) ) return true;
		return strpos( $string, $search ) !== false;
	}

	public static function is_assoc_array( $arr ) {
		if ( !is_array( $arr ) ) return false;
		return ( array_values( $arr ) !== $arr );
	}

	public static function encrypt_password( $raw ) {
		return password_hash( $raw, PASSWORD_DEFAULT );
	}

	public static function verify_password( $raw, $hash ) {
		return password_verify( $raw, $hash );
	}

	public static function html_entities( $str ) {
		return htmlentities( $str, ENT_QUOTES | ENT_HTML401 );
	}

	public static function str_filter( $str ) {
		return preg_replace( "/^(?: +)/", "", preg_replace( "/(?: +)$/", "", $str ) );
	}

	public static function str_length( $str ) {
		return strlen( self::str_filter( $str ) );
	}

	public static function str_empty( $str ) {
		return empty( self::str_filter( $str ) );
	}

	public static function get_file_extension( $file_path ) {
		return pathinfo( $file_path, PATHINFO_EXTENSION );
	}

	public static function file_exists( $path ) {
		return file_exists( $path ) && is_readable( $path );
	}

	public static function require_if_exists( $path ) {
		if ( self::file_exists( $path ) ) require_once $path;
	}

	public static function include_if_exists( $path ) {
		if ( self::file_exists( $path ) ) include_once $path;
	}

	public static function str_format( $format, $args ) {

		$format = preg_replace_callback( '/(?:\{?\{(\d)\}\}?)/', function( $matches ) use( $args ) {
			if ( count( $matches ) !== 2 ) return "";
			$n = intval( $matches[1] );
			return $args[ $n ];
		}, $format );

		return $format;

	}

	public static function apply_default_options( array &$assoc_array, array $default_options ) {
		foreach ( $default_options as $key => $value ) {
			if ( !array_key_exists( $key, $assoc_array ) ) {
				$assoc_array[ $key ] = $default_options[ $key ];
			}
		}
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
