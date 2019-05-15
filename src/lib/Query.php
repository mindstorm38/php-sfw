<?php

// Query

namespace SFW;

abstract class Query {

     public abstract function required_variables();

     public abstract function execute( $vars );

}

?>
