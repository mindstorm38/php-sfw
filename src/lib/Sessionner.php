<?php

namespace SFW;

use \BadMethodCallException;

/**
 * <p>Class used to manage differents sessions at a time.</p>
 * <p>This class is not compatible with {@link SessionManager} and {@link SessionHandler}</p>
 * <p>This class is using "session:[name|regenerate_interval|configs]" options in the configuration (config.json), "config:[lifetime|path]" are no longer used.</p>
 * <p>You can create your personnal sessions handlers extending the {@link Session} class.</p>
 * 
 * @author Théo Rozier
 *
 */
final class Sessionner {
	
	const DEFAULT_SESS_LIFETIME = 86400;
	
	const DEFAULT_SESS_COOKIE = "SFWSESSID";
	const DEFAULT_SESS_REGENERATE_INTERVAL = 900;
	
	const DEFAULT_SESS_CLASS = "SFW\\Session";
	
	const DEFAULT_SESS_ID = "default";
	
	private static $started = false;
	private static $sessions = null;
	
	private static $max_lifetime = 0;
	
	/**
	 * @return bool Is the sessionner started.
	 */
	public static function is_started() : bool {
		return self::$sessions;
	}
	
	/**
	 * Start session managing
	 */
	public static function start() {
		
		Core::check_app_ready();
		
		if ( self::$started ) {
			throw new BadMethodCallException("Session already started.");
		}
		
		if ( headers_sent() ) {
			throw new BadMethodCallException("Session can't be started, headers already sent !");
		}
		
		if ( !@function_exists( "session_name" ) ) {
			
			Core::missing_extension( "session", true );
			return;
			
		}
		
		self::$max_lifetime = 0;
		
		foreach ( self::$sessions as $id => $session ) {
			
			$session->load_config( self::get_session_config($id) );
			
			if ( $session->get_lifetime() > self::$max_lifetime ) {
				self::$max_lifetime = $session->get_lifetime();
			}
			
		}
		
		@ini_set("session.use_cookies", true);
		@ini_set("session.use_only_cookies", true);
		@ini_set("session.use_strict_mode", true);
		@ini_set("session.cookie_lifetime", self::$max_lifetime);
		@ini_set("session.cookie_path", "/");
		@ini_set("session.cookie_domain", "." . Config::get_advised_host());
		@ini_set("session.cookie_secure", Config::is_secure());
		@ini_set("session.cookie_httponly", true);
		@ini_set("session.use_trans_sid", false);
		
		session_cache_limiter("private");
		session_name( self::get_cookie() );
		session_start();
		
		if ( self::is_session_init() ) {
			
			if ( self::is_obsolete() ) {
				
				self::destroy_and_restart();
				self::setup_session_vars();
				
			} else if ( self::need_regenerate() ) {
				
				session_regenerate_id();
				self::setup_last_regen_var();
				
			}
			
		} else {
			self::setup_session_vars();
		}
		
		foreach ( self::$sessions as $id => $session ) {
			
			if ( !isset( $_SESSION[$id] ) ) {
				$_SESSION[$id] = [];
			}
			
			$session->load( $_SESSION[$id] );
			
		}
		
		session_write_close();
		
		self::$started = true;
		
	}
	
	public static function destroy_and_restart() {
		
		session_destroy();
		$_SESSION = [];
		
		session_start();
		
	}
	
	private static function is_session_init() : bool {
		return isset( $_SESSION["IP_ADDR"] ) && isset( $_SESSION["USER_AGENT"] ) && isset( $_SESSION["LAST_REGEN"] );
	}
	
	private static function is_obsolete() : bool {
		return $_SESSION["IP_ADDR"] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT'];
	}
	
	private static function setup_session_vars() {
		
		$_SESSION["IP_ADDR"] = $_SERVER["REMOTE_ADDR"];
		$_SESSION["USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
		
		self::setup_last_regen_var();
		
	}
	
	private static function setup_last_regen_var() {
		$_SESSION["LAST_REGEN"] = time();
	}
	
	private static function need_regenerate() : bool {
		return ( time() - $_SESSION["LAST_REGEN"] ) > self::get_regenerate_interval();
	}
	
	public static function setup_default_session() {
		self::set_session( self::DEFAULT_SESS_ID, new Session() );
	}
	
	public static function get_default_session() {
		return self::get_session( self::DEFAULT_SESS_ID );
	}
	
	public static function set_session( string $session_id, Session $session ) {
		
		if ( self::$started ) {
			throw new BadMethodCallException("Can't add a session while Sessionner is started.");
		}
		
		if ( !( $session instanceof Session ) ) {
			throw new BadMethodCallException("Invalid 'session' argument, must extend '" . self::DEFAULT_SESS_CLASS . "' class.");
		}
		
		if ( isset( self::$sessions[$session_id] ) ) {
			throw new BadMethodCallException("A session '{$session_id}' is already used.");
		}
		
		self::$sessions[$session_id] = $session;
		
	}
	
	public static function get_session( string $session_id ) {
		return self::$sessions[$session_id] ?? null;
	}
	
	public static function save_sessions( string...$sessions_ids ) {
		
		if ( !self::$started ) {
			throw new BadMethodCallException("Can't save a session if Sessionner is not started");
		}
		
		$errors = [];
		
		session_start();
		
		foreach ( $sessions_ids as $session_id ) {
			
			$sess = self::get_session($session_id);
			
			if ( $sess === null ) {
				
				$errors[] = $session_id;
				continue;
				
			}
			
			$_SESSION[$session_id] = $sess->get_data();
		
		}
		
		session_write_close();
		
		if ( count( $errors ) != 0 ) {
			throw new BadMethodCallException("Invalid sessions '" . join("', '", $errors) . "' given, must be registered first in Sessionner (other valid sessions has been saved).");
		}
		
	}
	
	public static function get_cookie() {
		return Config::get( "session:cookie", self::DEFAULT_SESS_COOKIE );
	}
	
	public static function get_regenerate_interval() {
		return Config::get( "session:regenerate_interval", self::DEFAULT_SESS_REGENERATE_INTERVAL );
	}
	
	public static function get_session_config( string $session_id ) {
		return Config::get("session:configs:{$session_id}", []);
	}
	
}

?>