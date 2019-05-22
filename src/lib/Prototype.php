<?php

namespace SFW;

use SFW\Route\PathCheckerRoute;
use \Exception;

/**
 * 
 * <p>A class for managing prototype configuration of the website.</p>
 * <p>This is used to create a private access website while developping.</p>
 * 
 * @author Théo Rozier
 *
 */
final class Prototype {
	
	private static $session = null;
	
	/**
	 * @return boolean If the prototype manager was started.
	 */
	public static function is_started() {
		return self::$session !== null;
	}
	
	/**
	 * @return bool If application prototype is enabled.
	 */
	public static function is_enabled() : bool {
		return boolval( Config::get("prototype:enabled", false) );
	}
	
	/**
	 * @return array Prototype users associated to their password.
	 */
	public static function get_users() : array {
		return Config::get("prototype:users", []);
	}
	
	/**
	 * Start the prototype if needed, starting add several routes, pages and middleware to ensure prototype connection.
	 */
	public static function start() : void {
		
		Core::check_app_ready();
		
		if ( !self::is_enabled() ) {
			return;
		}
		
		if ( self::is_started() ) {
			throw new Exception("Prototype manager already started !");
		}
		
		self::$session = new Session();
		self::$session["user"] = null;
		
		Sessionner::set_session("sfw-prototype", self::$session);
		
		Core::add_route( new PathCheckerRoute( [__CLASS__, "cb_check_prototype_logged"] ) );
		Core::set_page_template("prototype", "sfw");
		
	}
	
	/**
	 * @throws Exception If the prototype manager was not started.
	 */
	public static function check_started() {
		if ( !self::is_started() ) throw new Exception("Prototype is not started.");
	}
	
	/**
	 * @return string|null Current logged user name, or null if not logged.
	 */
	private static function logged_user() : ?string {
		
		self::check_started();
		return self::$session["user"] ?? null;
		
	}
	
	/**
	 * Don't call it, used by the path checker route.
	 * @param array $vars Variable returned by routing.
	 */
	public static function cb_check_prototype_logged( $vars ) {
		
		self::check_started();
		
		if ( self::logged_user() === null ) {
			
			Core::print_page("prototype");
			return true;
			
		} else {
			return false;
		}
		
	}
	
}

?>