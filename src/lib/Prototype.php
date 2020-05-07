<?php

namespace SFW;

use \Exception;
use SFW\Route\Middleware\AnonymousMiddleware;
use SFW\Route\Middleware\Shared\FilterIdSharedMiddleware;
use SFW\Route\Route;

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
	const CHECK_LOGGED_MIDDLEWARE = "prototype_logged";

	private static $session = null;
	private static $check_logged_middleware = null;
	
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
     * @throws Exception If the core app is not ready.
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

		self::$check_logged_middleware = new FilterIdSharedMiddleware(new AnonymousMiddleware(0, [__CLASS__, "action_check_logged"]), self::CHECK_LOGGED_MIDDLEWARE);
		self::$check_logged_middleware->add_route_id(Core::DEFAULT_STATIC_ROUTE);
        self::$check_logged_middleware->add_route_id(Core::DEFAULT_QUERY_ROUTE);

		Sessionner::set_session( self::SESSION_ID, self::$session );

		// Core::add_route( new LastRouteFilterRoute( null, "prototype-logged-filter", self::EXCEPT_ROUTES ), [__CLASS__, "controller_check_logged"] );

        Core::add_shared_middleware(self::$check_logged_middleware);
		Core::set_page_template("prototype", "sfw");
		
		return true;
		
	}
	
	/**
	 * @throws Exception If the prototype manager was not started.
	 */
	public static function check_started() {
		if ( !self::is_started() ) throw new Exception("Prototype is not started.");
	}

	public static function get_check_logged_middleware(): FilterIdSharedMiddleware {
	    return self::$check_logged_middleware;
    }

    /**
     * @param string $user User name.
     * @param string $password Raw password.
     * @return bool Check if user and password are correct.
     * @throws Exception If the prototype engine is not started.
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
     * @throws Exception If the prototype engine is not started.
	 */
	private static function set_logged_user( string $user ) {
		
		self::check_started();
		self::$session["user"] = $user;
		Sessionner::save_sessions( self::SESSION_ID );
		
	}
	
	/**
	 * @return string|null Current logged user name, or null if not logged.
     * @throws Exception If the prototype engine is not started.
	 */
	private static function logged_user() : ?string {
		
		self::check_started();
		$user = self::$session["user"] ?? null;
		
		return $user !== null && isset( self::get_users()[$user] ) ? $user : null;
		
	}

	public static function action_check_logged(Route $route, array &$args, callable $next) {
		
        if (self::is_started()) {
            try {
                if (self::logged_user() === null) {
                    Core::print_page("prototype");
                    return;
                }
            } catch (Exception $ignored) {}
        }

        ($next)();

	}
	
}

?>