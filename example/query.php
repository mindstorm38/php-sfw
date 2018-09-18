<?php

require __DIR__ . '/common.php';

use SFW\Utils;
use SFW\QueryManager;

Utils::content_type_json();
Utils::force_no_cache();

die( json_encode( QueryManager::execute( isset( $_GET[ QueryManager::PARAM ] ) ? $_GET[ QueryManager::PARAM ] : "", $_POST ) ) );

?>
