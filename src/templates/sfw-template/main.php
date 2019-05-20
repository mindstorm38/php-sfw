<?php

use SFW\Core;
use SFW\Lang;

?>

<?php @include_once $page->page_part_path("init"); ?>

<!DOCTYPE html>
<html>

	<head>
	
		<meta charset="utf8" />
		<title><?= $page->title ?> - PHP-SFW</title>
		<link rel="stylesheet" href="/static/styles/sfw-main.less.css" />
		
	</head>

	<body>
		
		<div class="php-sfw-title">PHP-SFW</div>
		<div class="php-sfw-description">A Simple PHP Framework</div>
		<div class="php-sfw-version">v<?= Core::VERSION ?></div> 

		<div class="php-sfw-content">
			<?php @include_once $page->page_part_path("content"); ?>
		</div>

		<div class="php-sfw-footer"><?= Lang::get("sfw.credits", [ "</a>", "<a target=\"blank\" href=\"https://github.com/mindstorm38/php-sfw\">", "<a target=\"blank\" href=\"https://github.com/mindstorm38\">" ] ) ?></div>

	</body>

</html>