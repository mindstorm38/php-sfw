<?php

namespace SFW\Route;

use SFW\Utils;

class QueryRoute extends Route {
	
	private $base_path;
	private $base_path_len_min;
	
	public function __construct(string $method, string $identifier, string $base_path) {
		
		parent::__construct($method, $identifier);
		
		$this->base_path = Utils::beautify_url_path($base_path);
		$this->base_path_len_min = strlen($this->base_path) + 1;
		
	}
	
	public function identifier() : ?string {
		return "{$this->base_path}<QUERY>";
	}
	
	public function routable( string $path, string $bpath ) : ?array {
		
		if ( Utils::starts_with( $bpath, "{$this->base_path}/" ) && strlen($bpath) > $this->base_path_len_min && strpos( $bpath, "/", $this->base_path_len_min ) === false ) {
			return [ substr( $bpath, $this->base_path_len_min ) ];
		}
		
		return null;
		
	}
	
}

?>