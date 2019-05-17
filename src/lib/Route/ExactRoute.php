<?php 

namespace SFW\Route;

use SFW\Route;
use SFW\Utils;

class ExactRoute extends Route {
	
	private $path;
	private $use_beautified;
	
	public function __construct( callable $action, string $path, bool $use_beautified = true ) {
		
		parent::__construct($action);
		
		$this->path = $use_beautified ? Utils::beautify_url_path($path) : $path;
		$this->use_beautified = $use_beautified;
		
	}
	
	public function identifier() : string {
		return $this->path;
	}
	
	protected function routable( string $path, string $bpath ) : ?array {
		return $this->path === ( $this->use_beautified ? $bpath : $path ) ? [] : null;
	}
	
}

?>