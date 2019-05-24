<?php

namespace SFW;

use \Exception;
use SFW\Route\LastRouteFilterRoute;

/**
 *
 * <p>A class for managing prototype configuration of the website.</p>
 * <p>This is used to create a private access website while developping.</p>
 *
 * @author Théo Rozier
 *
 */
final class Prototype {
	
	const SESSION_ID = "sfw-prototype";
	
	const EXCEPT_ROUTES = [
		Core::DEFAULT_STATIC_ROUTE,
		Core::DEFAULT_QUERY_ROUTE
	];
	
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
	public static function start() : bool {
		
		Core::check_app_ready();
		
		if ( !self::is_enabled() ) {
			return false;
		}
		
		if ( self::is_started() ) {
			throw new Exception("Prototype manager already started !");
		}
		
		self::$session = new Session();
		self::$session["user"] = null;
		
		Sessionner::set_session( self::SESSION_ID, self::$session );
		
		Core::add_route( new LastRouteFilterRoute( "prototype-logged-filter", self::EXCEPT_ROUTES ), [__CLASS__, "controller_check_logged"] );
		Core::set_page_template("prototype", "sfw");
		
		return true;
		
	}
	
	/**
	 * @throws Exception If the prototype manager was not started.
	 */
	public static function check_started() {
		if ( !self::is_started() ) throw new Exception("Prototype is not started.");
	}
	
	/**
	 * @return bool Check if user and password are correct.
	 */
	public static function try_log( string $user, string $password ) : bool {
		
		self::check_started();
		
		$users = self::get_users();
		$valid = isset( $users[$user] ) ? ( empty($users[$user]) || $users[$user] === hash("sha256", $password) ) : false;
		
		if ( !$valid ) return false;
		
		self::set_logged_user($user);
		
		return true;
		
	}
	
	/**
	 * Set logged user.
	 * @param string $user The user name.
	 */
	private static function set_logged_user( string $user ) {
		
		self::check_started();
		self::$session["user"] = $user;
		Sessionner::save_sessions( self::SESSION_ID );
		
	}
	
	/**
	 * @return string|null Current logged user name, or null if not logged.
	 */
	private static function logged_user() : ?string {
		
		self::check_started();
		$user = self::$session["user"];
		
		return isset( self::get_users()[$user] ) ? ( self::$session["user"] ?? null ) : null;
		
	}
	
	/**
	 * Don't call it, used by the path checker route.
	 * @param array $vars Variable returned by routing.
	 */
	public static function controller_check_logged() {
		
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