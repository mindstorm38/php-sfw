<?php

namespace SFW\Route;

use SFW\Core;
use SFW\QueryManager;

abstract class Route {
	
	private $action;
	
	public function __construct( callable $action ) {
		$this->action = $action;
	}
	
	public abstract function identifier() : string;
	
	protected abstract function routable( string $path, string $bpath ) : ?array;
	
	public function try_route( string $path, string $bpath ) : bool {
		
		if ( ( $vars = $this->routable($path, $bpath) ) !== null ) {
			
			($this->action)($vars);
			return true;
			
		}
		
		return false;
		
	}
	
	// Predefined callbacks
	
	public static function cb_send_app_page( string $page ) : callable {
		
		return function( $vars ) use ( $page ) {
			Core::print_page($page, $vars);
		};
		
	}
	
	public static function cb_send_static_ouput() : callable {
		
		return function( $vars ) {
			Core::send_static_resource( $vars[0] );
		};
		
	}
	
	public static function cb_execute_query() : callable {
		
		return function( $vars ) {
			QueryManager::send_query_response($vars[0], $_POST);
		};
		
	}
	
}