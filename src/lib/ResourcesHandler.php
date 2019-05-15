<?php 

namespace SFW;

use \BadMethodCallException;

/**
 * 
 * <p>Used for managing resources from a specific base directory.</p>
 * 
 * @author Mindstorm38
 *
 */
class ResourcesHandler {
	
	private $base_dir;
	
	public function __construct( string $base_dir ) {
		
		$this->base_dir = realpath( $base_dir );
		
		if ( !is_dir($this->base_dir) ) {
			throw new BadMethodCallException("Invalid given base directory.");
		}
		
	}
	
	/**
	 * Get the base directory where this resources handlers is working.
	 * @return string Base directory of this handler.
	 */
	public function get_base_dir() : string {
		return $this->base_dir;
	}
	
	/**
	 * Get a directory path if it exists in this handler.
	 * @param string $relative_path The relative path of the directory from base directory.
	 * @return null|string The given directory path, or null if not exists.
	 */
	public function get_dir_safe( string $relative_path ) : string {
		
		$path = Utils::path_join( $this->base_dir, $relative_path );
		return is_dir($path) ? $path : null;
		
	}
	
	/**
	 * Get a file path if it exists in the handler.
	 * @param string $relative_path The relative path of the file from base directory.
	 * @return null|string The given file path, or null if not exists.
	 */
	public function get_file_safe( string $relative_path ) : string {
		
		$path = Utils::path_join( $this->base_dir, $relative_path );
		return is_file($path) ? $path : null;
		
	}
	
}

?>