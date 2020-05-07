<?php

namespace SFW;

use \Throwable;
use \Exception;
use \InvalidArgumentException;

/**
 * 
 * Main class for managing logs.
 * 
 * @author Théo Rozier
 *
 * @method static debug(string $message, Throwable $error = null)
 * @method static info(string $message, Throwable $error = null)
 * @method static warning(string $message, Throwable $error = null)
 * @method static error(string $message, Throwable $error = null)
 * @method static fatal(string $message, Throwable $error = null)
 *
 */
final class Logger {
	
	const LATEST_LOG_FILE = "latest.log";
	const DATED_LOG_FILE = "d-m-Y\.\l\o\g";
	
	private static $levels = [
		"fatal" => 0,
		"error" => 20,
		"warning" => 40,
		"info" => 70,
		"debug" => 100
	];
	
	private static $level = 80;
	private static $directory = "logs";
	private static $format_method = null;

	private static function get_format_method() : callable {

	    if (self::$format_method == null) {

		    self::$format_method = function( int $date, string $level, string $message, Throwable $error = null ) {

                $msg = gmdate( "d/m/Y G:i:s", $date ) . " [" . strtoupper( $level ) . "] {$message}\n";

                if ( $error !== null ) {

                    $msg .= " [" . get_class($error) . " : {$error->getMessage()}]\n";

                    $trace = explode( "\n", $error->getTraceAsString() );

                    array_walk( $trace, function( &$s ) {
                        $s = "\t\t{$s}";
                    } );

                    $msg .= implode( "\n", $trace );

                }

                return $msg;

            };

        }

	    return self::$format_method;

    }
	
	/**
	 * @return number[] Current levels identifiers associated to their level.
	 */
	public static function get_levels() {
		return self::$levels;
	}
	
	/**
	 * Set the logs directory.
	 * @param string $file The directory path from root application directory.
	 */
	public static function set_directory( string $directory ) {
		self::$directory = $directory;
	}
	
	/**
	 * Get the full directory path.
	 * @return string Full path of the log directory.
	 */
	public static function get_directory() : string {
		return Core::get_app_path( self::$directory );
	}
	
	/**
	 * @return bool Is this level valid.
	 */
	public static function is_valid_level( string $level ) : bool {
		return array_key_exists( $level, self::$levels );
	}
	
	/**
	 * Internal method for getting  file path from main directory.
	 * @return string Real path of the file joined with log directory.
	 */
	private static function get_log_file( string $file ) : string {
		return Core::get_app_path( self::$directory, $file );
	}
	
	/**
	 * Log a message.
	 * @param string $level Message level (see {@link Logger::get_levels}).
	 * @param string $message Message to log.
	 * @param Throwable $error Optional error.
	 * @throws Exception Thrown if level is invalid.
	 */
	public static function log( string $level, string $message, Throwable $error = null ) {
		
		if ( !self::is_valid_level( $level ) ) {
			throw new Exception("Invalid level '{$level}'");
		}
		
		if ( self::$levels[ $level ] > self::$level ) return;
		
		$time = time();
		$msg = (self::get_format_method())( $time, $level, $message, $error );
		
		$latest_file = self::get_log_file( self::LATEST_LOG_FILE );
		$latest_file_mdate = date("z Y", filemtime($latest_file));

		if (!is_dir(dirname($latest_file))) {
			mkdir(dirname($latest_file));
		}
		
		if ( date("z Y") !== $latest_file_mdate ) {
			rename( $latest_file, self::get_log_file(date(self::DATED_LOG_FILE)) );
		}
		
		file_put_contents( $latest_file, $msg, FILE_APPEND );
		
	}
	
	public static function __callStatic( $name, $args ) {
		
		if ( self::is_valid_level( $name ) ) {
			
			if ( count($args) < 1 ) {
				throw new InvalidArgumentException("Missing 'message' argument");
			}
			
			self::log( $name, $args[0], ( count($args) >= 2 ? $args[1] : null ) );
			
		}
		
	}

}

?>