<?php

namespace SFW\Route;

abstract class FilterRoute extends Route {
	
	public function __construct( string $identifier ) {
		parent::__construct($identifier);
	}
	
	public function routable( string $path, string $bpath ) : ?array {
		return null; // NOT USED IN CASE OF FILTER ROUTE
	}
	
	public abstract function routable_filter( string $path, string $bpath, ?Route $last_route ) : ?array;
	
	public function filter( string $path, string $bpath, ?Route $last_route ) : bool {
		
		if ( ($vars = $this->routable_filter($path, $bpath, $last_route)) !== null ) {
			return $this->call_controller($vars);
		}
		
		return false;
		
	}
	
}

?>