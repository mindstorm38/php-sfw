<?php

namespace SFW\Route;

use SFW\Utils;

class StaticRoute extends Route {
	
	private $base_path;
	
	public function __construct( string $identifier, string $base_path ) {
		
		parent::__construct($identifier);
		
		$this->base_path = Utils::beautify_url_path($base_path);
		
	}
	
	public function identifier() : ?string {
		return "{$this->base_path}<STATIC>";
	}
	
	public function routable( string $path, string $bpath ) : ?array {
		
		if ( $this->base_path !== $bpath && Utils::starts_with( $bpath, $this->base_path ) ) {
			return [ substr( $bpath, strlen($this->base_path) ) ];
		}
		
		return null;
		
	}
	
}