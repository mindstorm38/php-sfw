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

     public abstract function required_variables();

     public abstract function execute( $vars );

}

?>
