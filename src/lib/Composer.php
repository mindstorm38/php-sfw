<?php 

namespace SFW;

use Composer\Script\Event;

final class Composer {
	
	const ARG_WEBSITE_DIR = "dir";
	
	const JWT_SECRET_LENGTH = 16;
	
	/**
	 * Get default configuration template variables
	 * @return string[] Default config template vars
	 */
	public static function get_default_config_vars() : array {
		
		return [
			"JWT_SECRET" => Utils::generate_random(self::JWT_SECRET_LENGTH)
		];
		
	}
	
	/**
	 * Command event for composer for initializing
	 */
	public static function composer_init_wd(Event $event) {
		
		$dir_raw = $event->getIO()->ask("Application path (relative to root composer directory) : ");
		$dir_root = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
		
		$dir = Utils::path_join($dir_root, $dir_raw);
		
		if ( !is_dir($dir) ) {
			
			$event->getIO()->ask("Please create the target directory before !");
			return;
			
		}
		
		$dir = realpath($dir);
		$dir_raw = substr( $dir, strlen($dir_root) );
		
		$event->getIO()->askConfirmation("Using directory : '$dir_raw' ($dir) ? (y/n) ");
		
		do {
			$name = $event->getIO()->ask("Application name (only alphanumeric, '-' & '_') : ");
		} while ( !self::valid_identifier($name) );
		
		$event->getIO()->write("Copying ...");
		
		$vars = array_merge([
			"REL_PATH_TO_AUTOLOADER" => "",
			"APP_NAME" => $name
		], self::get_default_config_vars());
		
		for ( $i = 0; $i < substr_count($dir_raw, DIRECTORY_SEPARATOR); $i++ ) {
			$vars["REL_PATH_TO_AUTOLOADER"] .= "/..";
		}
		
		$event->getIO()->write("Variables for templates : " . var_export($vars, true) . ".");
		
		self::extract_default_workspace($dir, $vars);
		
		$event->getIO()->write("Default Working Space copied !");
		
	}
	
	private static function valid_identifier( string $id ) : bool {
		return preg_match("/^[a-zA-Z0-9\-_]+$/", $id) === 1;
	}
	
	private static function get_default_ws_path() : string {
		return realpath( __DIR__ . "/../../defaultws" );
	}
	
	private static function extract_default_workspace( string $dst_dir, array $template_vars = [] ) {
		self::extract_default_ws_dir( self::get_default_ws_path(), $dst_dir, $template_vars );
	}
	
	private static function extract_default_ws_dir( string $src_dir, string $dst_dir, array $template_vars = [] ) : void {
		
		$children = @scandir($src_dir);
		
		if ( $children === false ) {
			return;
		}
		
		$children = array_diff( $children, array('..', '.') );
		
		if ( !is_dir($dst_dir) ) {
			mkdir( $dst_dir, 0777, true );
		}
		
		foreach ( $children as $child ) {
			
			if ( $child === ".ignore" )
				continue;
			
			$src = "{$src_dir}/{$child}";
			$dst = "{$dst_dir}/{$child}";
			
			if ( is_dir($src) ) {
				self::extract_default_ws_dir($src, $dst, $template_vars);
			} else {
				self::extract_default_ws_file($src, $dst, $template_vars);
			}
			
		}
		
	}
	
	private static function extract_default_ws_file( string $src, string $dst, array $template_vars = [] ) : void {
		
		if ( Utils::ends_with($src, ".tpl") ) {
			file_put_contents( substr($dst, 0, strlen($dst) - 4), self::get_template_compiled($src, $template_vars) );
		} else {
			copy($src, $dst);
		}
		
	}
	
	private static function get_template_compiled(string $src, array $template_vars = []) : string {
		
		$txt = file_get_contents($src);
		
		foreach ( $template_vars as $name => $val ) {
			$txt = str_replace("%{" . $name . "}%", strval($val), $txt);
		}
		
		return $txt;
		
	}
	
}

?>