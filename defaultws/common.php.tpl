<?php

require __DIR__ . '%{REL_PATH_TO_AUTOLOADER}%/vendor/autoload.php';

use SFW\Core;

Core::start_application( "%{APP_NAME}%", __DIR__ );

?>
