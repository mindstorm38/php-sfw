<?php 

namespace SFW\Query;

use SFW\Query;

class PrototypeLogin extends Query {
	
	public function required_variables() : array {
		
		return [
			"user",
			"password"
		];
		
	}
	
	public function execute( array $vars ) {
		
		
		
	}
	
}

?>