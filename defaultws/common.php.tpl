<?php

require __DIR__ . '%{REL_PATH_TO_AUTOLOADER}%/vendor/autoload.php';

use SFW\Core;

Core::setup_default_routes_and_pages();
Core::start_application( "%{APP_NAME}%", __DIR__ );

?>
