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
	
	const DEFAULT_PAGES_DIR = "pages";
	const DEFAULT_TEMPLATES_DIR = "templates";
	const DEFAULT_LANGS_DIR = "langs";
	const DEFAULT_STATIC_DIR = "static";
	
	private $base_dir;
	
	private $pages_dir;
	private $templates_dir;
	private $langs_dir;
	private $static_dir;
	
	public function __construct( string $base_dir ) {
		
		$this->base_dir = realpath( $base_dir );
		
		if ( !is_dir($this->base_dir) ) {
			throw new BadMethodCallException("Invalid given base directory.");
		}
		
		$this->set_pages_dir( self::DEFAULT_PAGES_DIR );
		$this->set_templates_dir( self::DEFAULT_TEMPLATES_DIR );
		$this->set_langs_dir( self::DEFAULT_LANGS_DIR );
		$this->set_static_dir( self::DEFAULT_STATIC_DIR );
		
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
	
	
	
	public function set_pages_dir( string $pages_dir ) {
		$this->pages_dir = Utils::path_join( $this->base_dir, $pages_dir );
	}
	
	public function set_templates_dir( string $templates_dir ) {
		$this->templates_dir = Utils::path_join( $this->base_dir, $templates_dir );
	}
	
	public function set_langs_dir( string $langs_dir ) {
		$this->langs_dir = Utils::path_join( $this->base_dir, $langs_dir );
	}
	
	public function set_static_dir( string $static_dir ) {
		$this->static_dir = Utils::path_join( $this->base_dir, $static_dir );
	}
	
	public function get_pages_dir() : string {
		return $this->pages_dir;
	}
	
	public function get_templates_dir() : string {
		return $this->templates_dir;
	}
	
	public function get_langs_dir() : string {
		return $this->langs_dir;
	}
	
	public function get_static_dir() : string {
		return $this->static_dir;
	}
	
	/**
	 * Get a page directory if available in this handler.
	 * @param string $page_id The page id.
	 * @return null|string Path of the page directory if available in this handler or null.
	 */
	public function get_page_dir( string $page_id ) {
		
		$path = Utils::path_join( $this->pages_dir, $page_id );
		return is_dir($path) ? $path : null;
		
	}
	
	/**
	 * Get a template directory if available in this handler.
	 * @param string $page_id The template id.
	 * @return null|string Path of the template directory if available in this handler or null.
	 */
	public function get_template_dir( string $template_id ) {
		
		$path = Utils::path_join( $this->templates_dir, $template_id );
		return is_dir($path) ? $path : null;
		
	}
	
}

?>