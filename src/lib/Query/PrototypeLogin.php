<?php

namespace SFW\Query;

use SFW\Query;
use SFW\QueryResponse;
use SFW\Prototype;

class PrototypeLogin extends Query {
	
	public function required_variables() : array {
		
		return [
			"user",
			"password"
		];
		
	}
	
	public function execute( array $vars ) {
		
		if ( !Prototype::is_started() ) {
			return new QueryResponse( "PROTOTYPE_NOT_STARTED", "prototype.not_started" );
		}
		
		if ( Prototype::can_log( $vars["user"], $vars["password"] ) ) {
			return new QueryResponse( false, "prototype.logged" );
		} else {
			return new QueryResponse( "WRONG_LOGIN", "prototype.wrong_login" );
		}
		
	}
	
}

?>