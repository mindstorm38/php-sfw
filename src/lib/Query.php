<?php

// Query

namespace SFW;

/**
 * 
 * Class used to define query, used in {@link QueryManager}.
 * 
 * @author Théo Rozier
 *
 */
abstract class Query {
	
	public abstract function required_variables() : array;

	public abstract function execute( array $vars );

}

?>
