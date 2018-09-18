<?php

require __DIR__ . '/vendor/autoload.php';

use SFW\Core;

Core::start_application( "appname", __DIR__, [
	"redirect_https" => true,
	"init_languages" => true,
	"start_session" => false
] );

?>
