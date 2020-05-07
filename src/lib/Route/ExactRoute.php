<?php

namespace SFW\Route;

use SFW\Utils;

class ExactRoute extends MethodRoute {
	
	private $path;
	private $use_beautified;
	
	public function __construct(?string $method, string $path, bool $use_beautified = true) {
		
		parent::__construct($method);
		
		$this->path = $use_beautified ? Utils::beautify_url_path($path) : $path;
		$this->use_beautified = $use_beautified;
		
	}
	
	public function method_routable(string $path, string $bpath) : ?array {
		return $this->path === ( $this->use_beautified ? $bpath : $path ) ? [] : null;
	}
	
}

?>