<?php

namespace SFW\Route;

use SFW\Utils;

class PathCheckerRoute extends Route {
	
	private $base_path;
	private $excepts = [];
	
	public function __construct( callable $action, string $base_path = "", array $excepts = [] ) {
		
		parent::__construct($action);
		
		$this->base_path = Utils::beautify_url_path($base_path);
		
		foreach ( $excepts as $exc ) {
			$this->excepts[] = Utils::beautify_url_path($exc);
		}
		
	}
	
	public function identifier() : ?string {
		return null;
	}
	
	protected function routable(string $path, string $bpath) : ?array {
		
		foreach ( $this->excepts as $exc ) {
			if ( Utils::starts_with( $bpath, $exc ) ) {
				return null;
			}
		}
		
		return Utils::starts_with( $bpath, $this->base_path ) ? [] : null;
		
	}
	
}