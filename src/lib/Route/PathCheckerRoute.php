<?php

namespace SFW\Route;

use SFW\Utils;

class PathCheckerRoute extends Route {
	
	private $base_path;
	
	public function __construct( callable $action, string $base_path = "" ) {
		
		parent::__construct($action);
		
		$this->base_path = Utils::beautify_url_path($base_path);
		
	}
	
	public function identifier() : ?string {
		return null;
	}

	protected function routable(string $path, string $bpath) : ?array {
		return Utils::starts_with( $bpath, $this->base_path ) ? [] : null;
	}

}