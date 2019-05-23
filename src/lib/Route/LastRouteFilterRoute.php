<?php 

namespace SFW\Route;

class LastRouteFilterRoute extends FilterRoute {
	
	private $excepts_routes;
	
	public function __construct( string $identifier, array $excepts_routes ) {
		
		parent::__construct($identifier);
		
		$this->excepts_routes = $excepts_routes;
		
	}
	
	public function routable( string $path, string $bpath, ?Route $last_route ) : ?array {
		return ( $last_route === null || !in_array($last_route, $this->excepts_routes) ) ? [] : null;
	}
	
}

?>