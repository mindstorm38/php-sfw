<?php

namespace SFW\Route;

use SFW\Core;
use SFW\QueryManager;
use \ArgumentCountError;

abstract class Route {
	
	const ROUTE_PAGE = "page";
	const ROUTE_RES = "res";
	const ROUTE_API = "api";
	
	private $identifier;
	protected $controller = null;
	
	public function __construct( string $identifier ) {
		$this->identifier = $identifier;
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
	
	public static function controller_print_page( string $page ) : callable {
		
		return function( ...$vars ) use ( $page ) {
			Core::print_page($page, $vars);
		};
		
	}
	
	public static function controller_send_static_resource() : callable {
		
		return function( string $path ) {
			Core::send_static_resource($path);
		};
		
	}
	
	public static function controller_send_query_response() : callable {
		
		return function( string $name ) {
			QueryManager::send_query_response($name, $_POST);
		};
		
	}
	
}