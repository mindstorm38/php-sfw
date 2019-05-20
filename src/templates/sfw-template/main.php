<?php

use SFW\Core;

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

	</body>

</html>