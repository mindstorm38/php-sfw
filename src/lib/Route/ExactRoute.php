<?php

namespace SFW\Route;

use SFW\Utils;

class ExactRoute extends Route {
	
	private $path;
	private $use_beautified;
	
	public function __construct( string $identifier, string $path, bool $use_beautified = true ) {
		
		parent::__construct($identifier);
		
		$this->path = $use_beautified ? Utils::beautify_url_path($path) : $path;
		$this->use_beautified = $use_beautified;
		
	}
	
	public function identifier() : ?string {
		return $this->path;
	}
	
	public function routable( string $path, string $bpath ) : ?array {
		return $this->path === ( $this->use_beautified ? $bpath : $path ) ? [] : null;
	}
	
}

?>