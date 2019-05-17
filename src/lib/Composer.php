<?php 

namespace SFW;

use Composer\Script\Event;

final class Composer {
	
	const ARG_WEBSITE_DIR = "dir";
	
	/**
	 * Command event for composer for initializing
	 */
	public static function composer_init_wd( Event $event ) {
		
		$dir = realpath( $event->getIO()->ask("Application path (empty for current) : ") );
		
		$event->getIO()->write("Using directory : '$dir'.");
		
		if ( is_dir($dir) && count(scandir($dir)) != 0 ) {
			
			if ( !$event->getIO()->askConfirmation("Specified website directory isn't empty, keep initialize it ? (yes/no) ", false) ) {
				return;
			}
			
		}
		
		do {
			$name = $event->getIO()->ask("Application name (only alphanumeric) : ");
		} while ( empty($name) || !ctype_alnum($name) );
		
		$event->getIO()->write("Copying ...");
		
		self::extract_default_workspace( $dir, [
			"APP_NAME" => $name
		] );
		
		$event->getIO()->write("Default Working Space copied !");
		
	}
	
	private static function extract_default_workspace( string $dst_dir, array $template_vars = [] ) {
		self::extract_default_ws_dir( realpath( __DIR__ . "/../../defaultws" ), $dst_dir, $template_vars );
	}
	
	private static function extract_default_ws_dir( string $src_dir, string $dst_dir, array $template_vars = [] ) : void {
		
		$children = array_diff( @scandir($src_dir), array('..', '.') );
		
		if ( $children === false ) {
			return;
		}
		
		if ( !is_dir($dst_dir) ) {
			mkdir( $dst_dir, 0777, true );
		}
		
		foreach ( $children as $child ) {
			
			$src = "{$src_dir}/{$child}";
			$dst = "{$dst_dir}/{$child}";
			
			if ( is_dir($src) ) {
				self::extract_default_ws_dir($src, $dst, $template_vars);
			} else {
				
				if ( Utils::ends_with($src, ".tpl") ) {
					
					$txt = @file_get_contents($src);
					
					foreach ( $template_vars as $name => $val ) {
						$txt = str_replace("%{" . $name . "}%", strval($val), $txt);
					}
					
					@file_put_contents(substr($dst, 0, count($dst) - 4), $txt);
					
				} else {
					copy($src, $dst);
				}
				
			}
			
		}
		
	}
	
}

?>