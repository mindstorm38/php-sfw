<?php

namespace SFW\Route;

use SFW\Core;
use SFW\QueryManager;
use \ArgumentCountError;

abstract class Route {
	
	const ROUTE_PAGE = "page";
	const ROUTE_RES = "res";
	const ROUTE_API = "api";
	
	protected $method;
	protected $identifier;
	protected $controller = null;
	
	public function __construct( string $method, string $identifier ) {
		
		$this->method = strtoupper($method);
		$this->identifier = $identifier;
		
	}
	
	public function get_method() : string {
		return $this->method;
	}
	
	public function get_identifier() : string {
		return $this->identifier;
	}
	
	public function set_controller( callable $controller ) : Route {
		
		$this->controller = $controller;
		return $this;
		
	}
	
	public function call_controller( array $vars ) : bool {
		
		if ( $this->controller === null ) {
			return false;
		}
		
		try {
			return ($this->controller)(...$vars) ?? true;
		} catch (ArgumentCountError $e) {
			return false;
		}
		
	}
	
	public abstract function routable( string $path, string $bpath ) : ?array;
	
	public function routable_base( string $method, string $path, string $bpath ) : ?array {
		return ( $this->method === $method ) ? $this->routable($path, $bpath) : null;
	}
	
	/*
	public function try_route( string $path, string $bpath ) : bool {
		
		if ( ( $vars = $this->routable($path, $bpath) ) !== null ) {
			
			($this->action)($vars);
			return true;
			
		}
		
		return false;
		
	}
	*/
	
	// Predefined callbacks
	
	/**
	 * Create a controller used to print page, variables returned by route are passed to {@link Core::print_page}.
	 * @param string $page The page ID printed.
	 * @return callable The controller.
	 * @see Core::print_page
	 */
	public static function controller_print_page( string $page ) : callable {
		
		return function( ...$vars ) use ( $page ) {
			Core::print_page($page, $vars);
		};
		
	}
	
	/**
	 * Create a controller that call {@link Core::send_static_resource}.
	 * @return callable The controller.
	 * @see Core::send_static_resource
	 */
	public static function controller_send_static_resource() : callable {
		
		return function( string $path ) {
			Core::send_static_resource($path);
		};
		
	}
	
	/**
	 * Create a controller to send query response using specified {@link QueryManager}.
	 * @param QueryManager $manager The query manager you want to use.
	 * @return callable The controller.
	 * @see QueryManager
	 * @see QueryManager::send_query_response
	 */
	public static function controller_send_query_response( QueryManager $manager ) : callable {
		
		return function( string $name ) use ($manager) {
			$manager->send_query_response($name, $_POST);
		};
		
	}
	
}