<?php

require __DIR__ . '/vendor/autoload.php';

use SFW\Core;
use SFW\Config;
use SFW\Utils;

Core::start_application( "appname", __DIR__, [
	"redirect_https" => true,
	"init_languages" => true,
	"start_session" => false
] );

Utils::force_no_cache();
Utils::content_type_html();

?>

<!DOCTYPE html>
<html>

	<head>

		<meta charset="utf-8" />
		<base href="<?= Config::get_advised_url() ?>" />

		<!--
		<link rel="stylesheet" href="./style?style=main" />
		<script type="text/text/javascript" src="./script?script=main"></script>
		-->

	</head>

	<body>

		<p>Content</p>

	</body>

</html>
