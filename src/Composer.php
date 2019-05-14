<?php 

namespace SFW;

use Composer\Script\Event;

final class Composer {
	
	const ARG_WEBSITE_DIR = "dir";
	
	/**
	 * Command event for composer for initializing
	 */
	public static function composer_init_wd( Event $event ) {
		
		$args = $event->getArguments();
		
		if ( !isset( $args[self::ARG_WEBSITE_DIR] ) ) {
			
			$event->getIO()->writeError("Website directory not specified (argument '--" . self::ARG_WEBSITE_DIR . "'.");
			return;
			
		}
		
		$dir = realpath($args[self::ARG_WEBSITE_DIR]);
		
		if ( count(scandir($dir)) != 0 ) {
			
			if ( !$event->getIO()->ask("Specified website directory '{$dir}' isn't empty, keep initialize it ?", false) ) {
				return;
			}
			
		}
		
		
		
	}
	
}

?>