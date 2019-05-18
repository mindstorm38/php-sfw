<?php

// Core class file

namespace SFW;

use SFW\Route\Route;
use SFW\Route\ExactRoute;
use SFW\Route\StaticRoute;
use \Exception;

/**
 * 
 * Core managing class for PHP-SFW.
 * 
 * The core is used to manage your "application".
 * 
 * @author Theo Rozier
 *
 */
final class Core {
	
	const VERSION = "1.0.0";
	const MINIMUM_PHP_VERSION = "7.1.0";
	
	const PAGES_DIR = "src/pages/";
	const TEMPLATES_DIR = "src/templates/";
	const STATIC_DIR = "static/";
	
	// DEPRECATED
	const DEFAULT_PAGES_DIR = Core::PAGES_DIR;
	const DEFAULT_TEMPLATES_DIR = Core::TEMPLATES_DIR;
	
	private static $app_name = null;
	private static $app_base_dir = null;
	
	private static $frameworkd_base_dir = null;
	
	private static $minimum_php_version = Core::MINIMUM_PHP_VERSION;
	private static $pages_dir = Core::DEFAULT_PAGES_DIR;
	private static $templates_dir = Core::DEFAULT_TEMPLATES_DIR;
	private static $redirect_wrong_host = true;
	private static $redirect_https = true;
	private static $init_languages = true;
	private static $start_session = false;
	
	private static $resources_handlers = [];
	private static $resources_handlers_r = [];
	
	private static $routes = [];
	private static $pages_aliases = [];
	private static $pages_templates = [];
	
	/**
	 * Start the application, only one application can be launched in the same runtime.
	 * @param string $app_name Application name, for now this is not used anywhere.
	 * @param string $app_base_dir Application base directory, used for locating languages, config and other relative paths.
	 */
	public static function start_application( string $app_name, string $app_base_dir ) {
		
		if ( self::$app_name !== null ) die( "Application already started" );
		
		header( "X-Powered-By: PHP-SFW/" . self::VERSION );
		
		self::$app_base_dir = realpath( $app_base_dir );
		self::$frameworkd_base_dir = dirname(dirname(__DIR__));
		
		// Check versions
		$minimum_php_version = self::$minimum_php_version;
		
		if ( version_compare( $minimum_php_version, Core::MINIMUM_PHP_VERSION, "lt" ) ) {
			die( "Invalid minimum php version given, PHP " . Core::MINIMUM_PHP_VERSION . "+ required by PHP-SFW. Given version : " . $minimum_php_version );
		} else if ( version_compare( PHP_VERSION, $minimum_php_version, "lt" ) ) {
			die( "PHP {$minimum_php_version}+ required. Currently installed version is : " . phpversion() );
		}
		
		// Registering resources
		self::add_resources_handler( new ResourcesHandler( self::$app_base_dir ) );
		self::add_resources_handler( new ResourcesHandler( self::$frameworkd_base_dir ) );
		
		// App name
		self::$app_name = $app_name;
		
		// Manual running
		if ( Utils::is_manual_running() ) die();
		
		// PHP Configuration
		@ini_set( 'display_errors', 1 );
		@error_reporting( E_ALL );
		@ini_set( 'file_uploads', 1 );
		@ini_set('default_charset', 'utf-8');
		@mb_internal_encoding('utf-8');
		
		if ( self::$redirect_https && isset( $_SERVER["HTTPS"] ) != Config::is_secure() ) {
			
			self::redirect_base( $_SERVER["REQUEST_URI"] );
			die();
			
		}
		
		if ( self::$redirect_wrong_host && $_SERVER["SERVER_NAME"] != Config::get_advised_host() ) {
			
			self::redirect_base( $_SERVER["REQUEST_URI"] );
			die();
			
		}
		
		// Init languages if selected
		if ( self::$init_languages ) Lang::init_languages();
		
		// Start session if selected
		if ( self::$start_session ) SessionManager::session_start();
		
		// Let's route
		if ( self::try_route( Utils::get_request_path_relative() ) === null ) {
			
			http_response_code(404);
			self::print_http_status(404);
			
		}
		
	}
	
	// Options
	
	/**
	 * Define the minimum version of PHP your application needs to work, used when starting application (If given version is less than {@link Core::MINIMUM_PHP_VERSION}, starts will die and return error message).
	 * @param string $minimum_php_version PHP minimum version required to start.
	 * @see Core::MINIMUM_PHP_VERSION
	 * @see Core::start_application
	 */
	public static function set_minimum_php_version( string $minimum_php_version ) {
		self::$minimum_php_version = $minimum_php_version;
	}
	
	/**
	 * <p><b>[[ DEPRECATED ]]</b></p>
	 * Define pages directory, used by pages manager (relative to application base directory).
	 * @param string $pages_dir Pages directory.
	 */
	public static function set_pages_dir( string $pages_dir ) {
		trigger_error("Pages directory is no longer used, now use the application resources handler.", E_USER_DEPRECATED);
		self::$pages_dir = $pages_dir;
	}
	
	/**
	 * <p><b>[[ DEPRECATED ]]</b></p>
	 * Define templates directory, used by pages manager (relative to application base directory).
	 * @param string $templates_dir Templates directory.
	 */
	public static function set_templates_dir( string $templates_dir ) {
		trigger_error("Templates directory is no longer used, now use the application resources handler.", E_USER_DEPRECATED);
		self::$templates_dir = $templates_dir;
	}
	
	/**
	 * If true, tell SFW to redirect to the advised host (config option "global:advised_host") if not already using it.
	 * @param bool $redirect_wrong_host Redirect wrong host.
	 */
	public static function set_redirect_wrong_host( bool $redirect_wrong_host ) {
		self::$redirect_wrong_host = $redirect_wrong_host;
	}
	
	/**
	 * If true, tell SFW to redirect to the same URL, but using the right protocol depending on HTTPS config option "global:secure" (redirect to http:// if false, or https:// if true).
	 * @param bool $redirect_https Redirect HTTPS.
	 */
	public static function set_redirect_https( bool $redirect_https ) {
		self::$redirect_https = $redirect_https;
	}
	
	/**
	 * If true, {@link Lang::init_languages} is called on application start.
	 * @param bool $init_languages Init languages.
	 * @see Lang::init_languages
	 */
	public static function set_init_languages( bool $init_languages ) {
		self::$init_languages = $init_languages;
	}
	
	/**
	 * If true, {@link SessionManager::session_start) is called on application start.
	 * @param bool $start_session Start session.
	 * @see SessionManager::session_start
	 */
	public static function set_start_session( bool $start_session ) {
		self::$start_session = $start_session;
	}
	
	// App
	
	/**
	 * Throw an exception if the application is not started.
	 * @throws Exception "Application not started".
	 */
	public static function check_app_ready() {
		if ( self::$app_name === null ) throw new Exception( "Application not started" );
	}
	
	/**
	 * @return string Base directory of the application (absolute path) defined at start.
	 * @see Core::start_application
	 * @see Core::check_app_ready
	 */
	public static function get_app_base_dir() : string {
		self::check_app_ready();
		return self::$app_base_dir;
	}
	
	/**
	 * Simplify and join given path to the base application directory (get it using {@link Core::get_app_base_dir}). It use the utiliy method {@link Utils::path_join}.
	 * @param string ...$paths Paths to append.
	 * @return string Full absolute path.
	 * @see Core::check_app_ready
	 * @see Utils::path_join
	 */
	public static function get_app_path( ...$paths ) : string {
		self::check_app_ready();
		array_unshift( $paths, self::$app_base_dir );
		return Utils::path_join( $paths );
	}
	
	/**
	 * Get application name.
	 * @return string Application name.
	 * @see Core::check_app_ready
	 */
	public static function get_app_name() : string {
		self::check_app_ready();
		return self::$app_name;
	}
	
	// Resources
	
	/**
	 * Get all resources handlers of the application from recent to older.
	 * @return array The resources handlers.
	 * @see ResourcesHandler
	 */
	public static function get_resources_handlers() : array {
		return self::$resources_handlers;
	}
	
	/**
	 * Get all resources handlers of the application from older to recent.
	 * @return array Resources handlers.
	 * @see ResourcesHandler
	 */
	public static function get_resources_handlers_reverse() : array {
		return self::$resources_handlers_r;
	}
	
	/**
	 * Add a {@link ResourcesHandler} to the application.
	 * @param ResourcesHandler $handler The handler to add.
	 * @see ResourcesHandler
	 */
	private static function add_resources_handler( ResourcesHandler $handler ) {
		
		array_unshift( self::$resources_handlers, $handler );
		self::$resources_handlers_r[] = $handler;
		
	}
	
	// Routes
	
	/**
	 * Setup default routes and pages from internal PHP-SFW pages.
	 * Actions done :
	 * <ul>
	 * 	<li>Add ExactRoute to send 'home' page for the path '/'.</li>
	 *  <li>Add StaticRoute for path '/static'.</li>
	 *  <li>Set 'sfw-template' (internal default template) to 'home' & 'error' pages.</li>
	 * </ul>
	 */
	public static function setup_default_routes_and_pages() {
		
		self::add_route( new ExactRoute( Route::cb_send_app_page("home"), "/" ) );
		self::add_route( new StaticRoute( Route::cb_send_static_ouput(), "/static") );
		
		self::set_page_template("home", "sfw-template");
		self::set_page_template("error", "sfw-template");
		
	}
	
	/**
	 * Add a route to the application, a route define what actions to executes when using specific URL path.
	 * @param Route $route The new route to add.
	 */
	public static function add_route( Route $route ) {
		
		$id = $route->identifier();
		self::$routes[$id] = $route;
		
	}
	
	/**
	 * Try to route the path.
	 * @param string $path The path to route, it can be raw from <code>$_SERVER["REQUEST_URI"]</code>.
	 * @return string|null The used route unique identifier or null if no route was found.
	 */
	public static function try_route( string $path ) : ?string {
		
		$bpath = Utils::beautify_url_path($path);
		
		foreach ( self::$routes as $id => $route ) {
			if ( $route->try_route($path, $bpath) ) {
				return $id;
			}
		}
		
		return null;
		
	}
	
	// Page Loading
	
	/**
	 * Add identifier aliases for a page.
	 * @param string $id The original page id.
	 * @param string ...$aliases A parameters list of aliases to add to this page id.
	 */
	public static function add_page_aliases( string $id, string ...$aliases ) {
		foreach ( $aliases as $alias )
			self::$pages_aliases[ $alias ] = $id;
	}
	
	/**
	 * Set template of a page (optional).
	 * @param string $id Identifier of the page.
	 * @param string $template Template identifier.
	 */
	public static function set_page_template( string $id, string $template ) {
		self::$pages_templates[ $id ] = $template;
	}
	
	/**
	 * Get a page identifier from its alias (added with {@link Core::add_page_aliases}).
	 * @param string $id Raw alias identifier.
	 * @return string Original page identifier. Or the alias itself if no aliase exists for this id.
	 * @see Core::add_page_aliases
	 */
	public static function get_page_alias( string $id ) {
		return array_key_exists( $id, self::$pages_aliases ) ? self::$pages_aliases[ $id ] : $id;
	}
	
	/**
	 * Get page template, associated using {@link Core::set_page_template}.
	 * @param string $id Page identifier.
	 * @return null|string Associated page template, or null if no template is associated.
	 * @see \SFW\Core::set_page_template
	 */
	public static function get_page_template( string $id ) {
		return array_key_exists( $id, self::$pages_templates ) ? self::$pages_templates[ $id ] : null;
	}
	
	/**
	 * Get last modification of the page directory (identifier is not checked, you can ask for any page identifier).
	 * @param string $id Page identifier.
	 * @return number|bool Last modification time in UNIX timestamp format, or FALSE if invalid path.
	 */
	public static function get_page_last_mod( string $id ) {
		return filemtime( self::get_app_path( self::$pages_dir, $id ) );
	}
	
	/**
	 * Load page from its identifier or its alias.
	 * @param string $raw_id Identifier (can be an alias).
	 * @return \SFW\Page The loaded page object.
	 * @see \SFW\Page
	 */
	public static function load_page( string $raw_id ) : Page {
		
		$page = new Page( $raw_id, self::get_page_alias( $raw_id ) );
		$page->template_identifier = self::get_page_template( $page->identifier );
		
		foreach ( self::$resources_handlers_r as $resource ) {
			
			if ( $page->directory === null ) {
				
				$page_dir = $resource->get_dir_safe( self::PAGES_DIR . $page->identifier );
				
				if ( $page_dir !== null ) {
					$page->directory = $page_dir;
				}
				
			}
			
			if ( $page->template_identifier !== null && $page->template_directory === null ) {
				
				$template_dir = $resource->get_dir_safe( self::TEMPLATES_DIR . $page->template_identifier );
				
				if ( $template_dir !== null ) {
					$page->template_directory = $template_dir;
				}
				
			}
			
		}
		
		return $page;
		
	}
	
	/**
	 * <p>Print the page loaded (using {@link Core::load_page}) from its identifier.</p>
	 * <p>If the page as a template, it include the template part 'main'.</p>
	 * <p>Else, if no template is defined, it use the 'init' part of the page.</p>
	 * @param string $raw_id The identifier.
	 * @param array $vars Variables to add to the page object in the 'vars' property.
	 * @return bool If the page was successfuly printed.
	 * @see Core::load_page
	 */
	public static function print_page( string $raw_id, array $vars = [] ) : bool {
		
		$page = Core::load_page($page);
		$page->{"vars"} = $vars;
		
		@include_once $page->has_template() ? $page->template_part_path("main") : $page->page_part_path("init");
		
	}
	
	/**
	 * Print the HTTP 'error' page.
	 * @param int $code The HTTP error code.
	 * @param string $msg A custom message to be added on the error page.
	 * @return bool If the page was successfuly printed.
	 * @see Core::print_page
	 */
	public static function print_http_status( int $code, string $msg = null ) : bool {
		return self::print_page( "error", [ "code" => $code, "msg" => $msg ] );
	}
	
	// Static resources
	
	/**
	 * Use a static resource using a callback.
	 * @param string $static_path The relative static path.
	 * @param callable $callback The callback to use when resource is opened, must have a single resource argument.
	 * @return bool True if the file has been found and writen.
	 */
	public static function use_static_resource( string $static_path, callable $callback ) : bool {
		
		$path = Utils::path_join( self::STATIC_DIR, $static_path );
		
		foreach ( self::$resources_handlers_r as $resource ) {
			
			$res_path = $resource->get_file_safe( $path );
			
			if ( $res_path == null ) {
				continue;
			}
			
			$res = @fopen($res_path, "r");
			
			if ( !is_resource($res) ) {
				continue;
			}
			
			$success = $callback($res);
			
			fclose($res);
			
			return $success;
			
		}
		
		return false;
		
	}
	
	// Utils
	
	/**
	 * Redirect (using {@link Utils::redirect}) to advised url path (retreived using {@link Config::get_advised_url}).
	 * @param string $path Path to append to advised URL.
	 */
	public static function redirect_base( string $path = "" ) {
		Utils::redirect( Config::get_advised_url( $path ) );
	}
	
	public static function missing_extension( $extension, $fatal = false, $extra = "" ) {
		
		$doclink = self::get_php_doc_link( 'book.' . $extension . '.php' );
		
		$message = sprintf(
			"The %s extension is missing. Please check your PHP configuration.", "<a href=\"{$doclink}\">{$extension}</a>"
		);
		
		if ( $extra != "" ) {
			$message .= " " + $extra;
		}
		
		if ( $fatal ) {
			self::fatal_error( $message );
			return;
		}
		
	}
	
	public static function fatal_error( $error_message, $message_args = null ) {
		
		if ( is_string( $message_args ) ) {
			$error_message = sprintf( $error_message, $message_args );
		} elseif ( is_array( $message_args ) ) {
			$error_message = vsprintf( $error_message, $message_args );
		}
		
		// Message::add_message( "fatal", $error_message );
		// Logger::log( "fatal", $error_message );
		
	}
	
	public static function get_php_doc_link( $target ) {
		$lang = "en";
		return "https://secure.php.net/manual/" . $lang . "/" . $target;
	}
	
	public static function set_cookie( $name, $value, $expire = 0 ) {
		if ( headers_sent() ) return false;
		return setcookie( $name, $value, $expire, "/", "." . Config::get_advised_host(), Config::is_secure(), true );
	}
	
}

?>
