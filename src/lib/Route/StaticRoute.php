<?php

namespace SFW\Route;

use SFW\Utils;

class StaticRoute extends MethodRoute {
	
	private $base_path;
	
	public function __construct(?string $method, string $base_path) {
		
		parent::__construct($method);
		
		$this->base_path = Utils::beautify_url_path($base_path);
		
	}
	
	public function method_routable(string $path, string $bpath) : ?array {
		
		if ( $this->base_path !== $bpath && Utils::starts_with( $bpath, $this->base_path ) ) {
			return [ substr( $bpath, strlen($this->base_path) ) ];
		}
		
		return null;
		
	}
	
}