<?php

// Core class file

namespace SFW;

use SFW\Route\FallbackRoute;
use SFW\Route\Middleware\Shared\SharedMiddleware;
use SFW\Route\Route;
use SFW\Route\ExactRoute;
use SFW\Route\StaticRoute;
use SFW\Route\QueryRoute;
use \Exception;
use \BadMethodCallException;
use SFW\Route\WrappedRoute;
use SFW\Util\CacheUtils;
use SFW\Util\OrderedTable;

/**
 *
 * Core managing class for PHP-SFW.
 *
 * The core is used to manage your "application".
 *
 * @author Théo Rozier
 *
 */
final class Core {
	
	const VERSION = "1.3.0-SNAPSHOT.7";
	const MINIMUM_PHP_VERSION = "7.1.0";
	
	const AUTHOR = "Théo Rozier";
	const AUTHOR_LINK = "https://theorozier.fr/";
	
	const PAGES_DIR = "src/pages/";
	const TEMPLATES_DIR = "src/templates/";
	const STATIC_DIR = "static/";
	
	// DEPRECATED
	const DEFAULT_PAGES_DIR = Core::PAGES_DIR;
	const DEFAULT_TEMPLATES_DIR = Core::TEMPLATES_DIR;
	
	const DEFAULT_HOME_ROUTE     = "home_page";
	const DEFAULT_STATIC_ROUTE   = "static_res";
	const DEFAULT_QUERY_ROUTE    = "query_exec";
	const DEFAULT_FALLBACK_ROUTE = "fallback";
	const DEFAULT_FALLBACK_ORDER = PHP_INT_MAX;
	
	private static $app_name = null;
	private static $app_base_dir = null;
	
	private static $framework_base_dir = null;
	
	private static $minimum_php_version = Core::MINIMUM_PHP_VERSION;
	private static $pages_dir = Core::DEFAULT_PAGES_DIR;
	private static $templates_dir = Core::DEFAULT_TEMPLATES_DIR;
	private static $redirect_wrong_host = true;
	private static $redirect_https = true;
	private static $init_languages = true;
	private static $start_session = false;
	private static $setup_defaults = true;
	
	private static $resources_handlers = [];
	private static $resources_handlers_r = [];
	
	private static $static_res_ext_procs = [];

	private static $routes = null;
	private static $shared_middlewares = [];

	/*private static $normal_routes = [];
	private static $filter_routes = [];*/
	
	private static $pages_aliases = [];
	private static $pages_templates = [];

    /**
     * Start the application, only one application can be launched in the same runtime.
     * @param string $app_name Application name, for now this is not used anywhere.
     * @param string $app_base_dir Application base directory, used for locating languages, config and other relative paths.
     * @param bool $die_if_manual The application is detecting if it was started manually,
     *                            in this case this method returns before initializing web-side behaviours.
     *                            But if this parameter is <b>true</b>, this method call <b>die()</b> instead of returns.
     * @throws Exception
     */
	public static function start_application(string $app_name, string $app_base_dir, bool $die_if_manual = true) {
		
		if ( self::$app_name !== null ) {
			throw new Exception("Application already started.");
		}
		
		// Check versions
		if ( version_compare( self::$minimum_php_version, Core::MINIMUM_PHP_VERSION, "lt" ) ) {
			throw new Exception( "Invalid minimum php version (set using 'set_minimum_php_version' method), PHP " . Core::MINIMUM_PHP_VERSION . "+ required by PHP-SFW. Given version : " . self::$minimum_php_version );
		} else if ( version_compare( PHP_VERSION, self::$minimum_php_version, "lt" ) ) {
			throw new Exception( "PHP {" . self::$minimum_php_version . "}+ required. Currently installed version is : " . phpversion() );
		}
		
		// App name and directories
		self::$app_name = $app_name;
		self::$app_base_dir = realpath( $app_base_dir );
		self::$framework_base_dir = dirname(dirname(__DIR__));
		
		// Registering resources
		self::add_resources_handler( new ResourcesHandler( self::$framework_base_dir ) );
		self::add_resources_handler( new ResourcesHandler( self::$app_base_dir ) );
		
		// Init languages if selected (now placed before checking manual running)
		if ( self::$init_languages ) {
			Lang::init_languages();
		}
		
		// Manual running
		if ( Utils::is_manual_running() ) {
			if ( $die_if_manual ) die(); else return;
		}
		
		// Set header only if runned by a HTTP server
		header("X-Powered-By: PHP-SFW/" . self::VERSION);

		if (Prototype::is_enabled()) {
			header("X-Is-Prototype: 1");
		}
		
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
		
		// Starting prototype manager
		Prototype::start();
		
		// Set current language from user agent
		if (self::$init_languages) {
			Lang::set_current_language_from_accept_languages();
		}
		
		// Start session if selected
		if (self::$start_session) {
			Sessionner::start();
		}
		
		// Adding import directories for less
		LessCompiler::get_compiler()->setImportDir( self::get_resource_dirs(self::STATIC_DIR) );
		
		// Adding query namespace for default queries
		QueryManager::main_register_query_namespace("SFW\\Query");

		// Setting up default routes if selected
		if (self::$setup_defaults) {
			self::setup_default_routes_and_pages();
		}

	}
	
	// Options
	
	/**
	 * Define the minimum version of PHP your application needs to work, used when starting application (If given version is less than {@link Core::MINIMUM_PHP_VERSION}, starts will die and return error message).
	 * @param string $minimum_php_version PHP minimum version required to start.
	 * @see Core::MINIMUM_PHP_VERSION
	 * @see Core::start_application
	 */
	public static function set_minimum_php_version(string $minimum_php_version) {
		self::$minimum_php_version = $minimum_php_version;
	}
	
	/**
	 * <p><b>[[ DEPRECATED ]]</b></p>
	 * Define pages directory, used by pages manager (relative to application base directory).
	 * @param string $pages_dir Pages directory.
	 */
	public static function set_pages_dir(string $pages_dir) {
		trigger_error("Pages directory is no longer used, now use the application resources handler.", E_USER_DEPRECATED);
		self::$pages_dir = $pages_dir;
	}
	
	/**
	 * <p><b>[[ DEPRECATED ]]</b></p>
	 * Define templates directory, used by pages manager (relative to application base directory).
	 * @param string $templates_dir Templates directory.
	 */
	public static function set_templates_dir(string $templates_dir) {
		trigger_error("Templates directory is no longer used, now use the application resources handler.", E_USER_DEPRECATED);
		self::$templates_dir = $templates_dir;
	}
	
	/**
	 * If true, tell SFW to redirect to the advised host (config option "global:advised_host") if not already using it (default to <b>true</b>).
	 * @param bool $redirect_wrong_host Redirect wrong host.
	 */
	public static function set_redirect_wrong_host(bool $redirect_wrong_host) {
		self::$redirect_wrong_host = $redirect_wrong_host;
	}
	
	/**
	 * If true, tell SFW to redirect to the same URL, but using the right protocol depending on HTTPS config option "global:secure" (redirect to http:// if false, or https:// if true, default to <b>true</b>).
	 * @param bool $redirect_https Redirect HTTPS.
	 */
	public static function set_redirect_https(bool $redirect_https) {
		self::$redirect_https = $redirect_https;
	}
	
	/**
	 * If true, {@link Lang::init_languages} is called on application start (default to <b>true</b>).
	 * @param bool $init_languages Init languages.
	 * @see Lang::init_languages
	 */
	public static function set_init_languages(bool $init_languages) {
		self::$init_languages = $init_languages;
	}
	
	/**
	 * If true, {@link SessionManager::session_start) is called on application start (default to <b>false</b>).
	 * @param bool $start_session Start session.
	 * @see SessionManager::session_start
	 */
	public static function set_start_session(bool $start_session) {
		self::$start_session = $start_session;
	}

	/**
	 * If true, the core add defaults SFW routes using {@link Core::setup_default_routes_and_pages()} (default to <b>true</b>).
	 * @param bool $setup_defaults Setup SFW defaults routes, middlewares, pages and templates.
	 * @see Core::setup_default_routes_and_pages
	 */
	public static function set_setup_defaults(bool $setup_defaults) {
		self::$setup_defaults = $setup_defaults;
	}
	
	// App
	
	/**
	 * Throw an exception if the application is not started.
	 * @throws Exception "Application not started".
	 */
	public static function check_app_ready() {
		if ( self::$app_name === null ) throw new Exception( "Application not started." );
	}

	/**
	 * @return string Base directory of the application (absolute path) defined at start.
	 * @throws Exception If the app is not ready.
	 * @see Core::check_app_ready
	 * @see Core::start_application
	 */
	public static function get_app_base_dir() : string {
		self::check_app_ready();
		return self::$app_base_dir;
	}
	
	/**
	 * Simplify and join given path to the base application directory (get it using {@link Core::get_app_base_dir}). It use the utiliy method {@link Utils::path_join}.
	 * @param string ...$paths Paths to append.
	 * @return string Full absolute path.
	 * @throws Exception If the app is not ready.
	 * @see Core::check_app_ready
	 * @see Utils::path_join
	 */
	public static function get_app_path(...$paths) : string {
		self::check_app_ready();
		array_unshift( $paths, self::$app_base_dir );
		return Utils::path_join( $paths );
	}
	
	/**
	 * Get application name.
	 * @return string Application name.
	 * @throws Exception If the app is not ready.
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
	 * Used to get all existing directories in all handlers.
	 * @param string $dir_path The relative directory path.
	 * @return array All real directories paths.
	 */
	public static function get_resource_dirs(string $dir_path) : array {
		
		$dirs = [];
		
		foreach ( self::$resources_handlers as $resource ) {
			if ( ( $dir = $resource->get_dir_safe($dir_path) ) !== null ) {
				$dirs[] = $dir;
			}
		}
		
		return $dirs;
		
	}
	
	/**
	 * Add a {@link ResourcesHandler} to the application.
	 * @param ResourcesHandler $handler The handler to add.
	 * @see ResourcesHandler
	 */
	private static function add_resources_handler(ResourcesHandler $handler) {
		
		self::$resources_handlers[] = $handler;
		array_unshift( self::$resources_handlers_r, $handler );
		
	}
	
	// Routes
	
	/**
	 * Setup default routes and pages from internal PHP-SFW pages.
	 * Actions done :
	 * <ul>
	 * 	<li>Add ExactRoute to send 'home' page for the path '/'.</li>
	 *  <li>Add StaticRoute for path '/static'.</li>
	 *  <li>Set 'sfw' (internal) template to 'home' & 'error' pages.</li>
	 *  <li>Setup resource extension processor for LessCompiler ({@link LessCompiler::add_res_ext_processor}).
	 * </ul>
	 */
	private static function setup_default_routes_and_pages() {
		
		self::add_route(Core::DEFAULT_HOME_ROUTE, 100, new ExactRoute("GET", ""), Route::action_print_page("home"));
		self::add_route(Core::DEFAULT_STATIC_ROUTE, 300, new StaticRoute("GET", "static"), Route::action_send_static_resource());
		self::add_route(Core::DEFAULT_QUERY_ROUTE, 200, new QueryRoute("POST", "query"), Route::action_send_query_response(QueryManager::get_main()));
		self::add_route(Core::DEFAULT_FALLBACK_ROUTE, Core::DEFAULT_FALLBACK_ORDER, new FallbackRoute(), Route::action_print_error_page(404));

		self::set_page_template("home", "sfw");
		self::set_page_template("error", "sfw");

		LessCompiler::add_res_ext_processor();
		
	}

	public static function get_routes(): OrderedTable {

		if (self::$routes === null) {
			self::$routes = new OrderedTable();
		}

		return self::$routes;

	}

    /**
     * Add a route to the application, a route define what actions to executes when using specific URL path.
     * @param string $id
     * @param int $order
     * @param Route $route The new route to add (can be either "normal" Route or FilterRoute).
     * @param callable|null $action An optional action if you want to setup one to the route.
     */
	public static function add_route(string $id, int $order, Route $route, ?callable $action = null) {

		self::get_routes()->add($id, new WrappedRoute($id, $order, $route));

		if ($action !== null) {
			$route->set_action($action);
		}

		foreach (self::$shared_middlewares as $smw) {
			if ($smw->can_add_to($id, $route)) {
				$smw->add_to($route);
			}
		}
		
	}

	public static function rem_route(string $id): bool {
		return self::get_routes()->remove($id);
	}

	public static function has_route(string $id): bool {
		return self::get_routes()->has($id);
	}

	public static function add_shared_middleware(SharedMiddleware $shared_mw) {

		if (isset(self::$shared_middlewares[$shared_mw->get_identifier()])) {
			throw new BadMethodCallException("A middleware already exists with the identifier '{$shared_mw->get_identifier()}'.");
		}

		self::$shared_middlewares[$shared_mw->get_identifier()] = $shared_mw;

		self::get_routes()->each(function($route_id, $wrapped_route) use ($shared_mw) {
			if ($shared_mw->can_add_to($route_id, $wrapped_route->get_route())) {
				$shared_mw->add_to($wrapped_route->get_route());
			}
		});

	}

	public static function rem_shared_middleware(SharedMiddleware $shared_mw): bool {

	    $id = $shared_mw->get_identifier();

	    if (!isset(self::$shared_middlewares[$id]) || self::$shared_middlewares[$id] !== $shared_mw) {
	        return false;
        }

	    self::get_routes()->each(function($route_id, $wrapped_route) use ($shared_mw) {
            $wrapped_route->get_route()->rem_middleware($shared_mw->get_identifier());
        });

	    return true;

    }

	/**
	 * Try to route the path.
	 * @param string $method The HTTP method used to access.
	 * @param string $path The path to route, it can be raw from <code>$_SERVER["REQUEST_URI"]</code>.
	 * @return string|null The used route unique identifier or null if no route was found.
	 */
	public static function try_route(string $method, string $path): ?string {
		
		$bpath = Utils::beautify_url_path($path);

		foreach (self::get_routes()->get_sorted_list() as $wrapped_route) {
            $route = $wrapped_route->get_route();
            if (($vars = $route->routable($method, $path, $bpath)) !== null) {

                $next = $route->build_middleware_chain($vars);
                ($next)();

                return $wrapped_route->get_identifier();

            }
        }

		return null;
		
	}
	
	/**
	 * Try route requested path (using {@link Utils::get_request_path_relative}) and catch error to <code>500</code> error page and route not found to <code>404</code> error page.
	 * @throws Exception If the app is not ready.
	 */
	public static function try_route_requested_path() : void {
		
		self::check_app_ready();
		
		try {
			
			if ( self::try_route( $_SERVER['REQUEST_METHOD'], Utils::get_request_path_relative() ) === null ) {
				self::print_error_page(404, "No fallback route");
			}
			
		} catch (Exception $e) {
			self::print_error_page(500, $e->getMessage());
		}
		
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
	 * @throws Exception If the app is not ready.
	 */
	public static function get_page_last_mod( string $id ) {
		return filemtime( self::get_app_path( self::$pages_dir, $id ) );
	}
	
	/**
	 * Load page from its identifier or its alias.
	 * @param string $raw_id Identifier (can be an alias).
	 * @return Page The loaded page object.
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
	 * <p><b>Must be called before headers send.</b></p>
	 * <p>Print the page loaded (using {@link Core::load_page}) from its identifier.</p>
	 * <p>If the page as a template, it include the template part 'main'.</p>
	 * <p>Else, if no template is defined, it use the 'init' part of the page.</p>
	 * @param string $raw_id The identifier.
	 * @param array $vars Variables to add to the page object in the 'vars' property.
	 * @return bool If the page was successfully printed.
	 * @see Core::load_page
	 */
	public static function print_page(string $raw_id, array $vars = []) : bool {

		if (headers_sent())
			return false;

		$page = Core::load_page($raw_id);
		$page->{"vars"} = $vars;

		CacheUtils::send_no_store();
		
		@include_once $page->has_template() ? $page->template_part_path("main") : $page->page_part_path("init");
		
		return true;
		
	}
	
	/**
	 * <p><b>Must be called before headers send.</b></p>
	 * Print the HTTP 'error' page.
	 * @param int $code The HTTP error code.
	 * @param string $msg A custom message to be added on the error page.
	 * @return bool If the page was successfully printed.
	 * @see Core::print_page
	 */
	public static function print_error_page(int $code, string $msg = null) : bool {
		
		http_response_code($code);
		return self::print_page( "error", [ "code" => $code, "msg" => $msg ] );
		
	}
	
	// Static resources
	
	/**
	 * Use a static resource using a callback.
	 * @param string $static_path The relative static path.
	 * @param callable $callback The callback to use when resource is opened, must have a resource argument and optionnaly the full path.
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
			
			$success = $callback($res, $res_path) ?? true;
			
			fclose($res);
			
			return $success;
			
		}
		
		return false;
		
	}
	
	/**
	 * Add a resource extension processor. It's used to add a resource processor on a specific extension.
	 * If both <code>path_modifier</code> & <code>res_printer</code> are null, exception is thrown.
	 * @param string $extension The extension.
	 * @param null|callable $path_modifier The <code><b>path_modifier( $path ) : string</b></code> returning the new path to be used to open the resource. Null to don't change the path.
	 * @param callable $res_printer The <code><b>callback( $res )</b></code> printing the transformed content of the resource.
	 */
	public static function add_res_ext_processor( string $extension, ?callable $path_modifier, ?callable $res_printer ) : void {
		
		if ( $path_modifier === null && $res_printer === null  ) {
			throw new BadMethodCallException("Both 'path_modifier' and 'res_printer' can't be null.");
		}
		
		self::$static_res_ext_procs[$extension] = [ $path_modifier, $res_printer ];
		
	}
	
	/**
	 * Remove a resource extension processor from its extension.
	 * @param string $extension The extension used by the processor.
	 */
	public static function remove_res_ext_processor( string $extension ) : void {
		
		if ( array_key_exists( $extension, self::$static_res_ext_procs ) ) {
			unset( self::$static_res_ext_procs[$extension] );
		}
		
	}
	
	/**
	 * <p><b>Must be called before headers send.</b></p>
	 * A callback to use for sending static resource to client.
	 * It also call resource extension processors registered using {@link Core::add_res_ext_processor}.
	 * @param string $res_path The relative resource path.
	 * @param boolean $ignore_cache Force resend cached resources.
	 * @see Core::add_res_ext_processor
	 * @see Core::use_static_resource
	 * @see Core::print_error_page
	 */
	public static function send_static_resource(string $res_path, bool $ignore_cache = false): void {

		if (headers_sent())
			return;

		$pr = null;
		
		foreach ( self::$static_res_ext_procs as $ext => $proc ) {
			if ( Utils::ends_with( $res_path, $ext ) ) {
				
				$pr = $proc;
				break;
				
			}
		}
		
		if ( $pr[0] !== null ) {
			$res_path = ($pr[0])($res_path);
		}
		
		$s = self::use_static_resource($res_path, function($res, $real_res_path) use ($pr, $ignore_cache) {

			$last_mod = filemtime($real_res_path);

			CacheUtils::send_to_revalidate($last_mod);

			if (!$ignore_cache && CacheUtils::validate_cache($last_mod)) {
				http_response_code(304);
			} else if ($pr[1] !== null) {

				try {
					($pr[1])($res);
				} catch (Exception $e) {
					self::print_error_page(500, $e->getMessage());
				}

			} else {

				Utils::content_type(Utils::get_file_mime_type($real_res_path));
				fpassthru($res);

			}
			
		} );
		
		if (!$s) {
			self::print_error_page(404);
		}
			
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
			$message .= " " . $extra;
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
