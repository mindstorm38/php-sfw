<?php

namespace SFW;

/**
 * 
 * <p>A class for managing prototype configuration of the website.</p>
 * <p>This is used to create a private access webiste while developping.</p>
 * 
 * @author Théo Rozier
 *
 */
final class Prototype {
	
	public static function is_enabled() {
		return Config::get("prototype:enabled", false);
	}
	
	public static function get_users() {
		return Config::get("prototype:users", []);
	}
	
}

?>