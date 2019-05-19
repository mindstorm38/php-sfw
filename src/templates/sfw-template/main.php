<?php @include_once $page->page_part_path("init"); ?>

<!DOCTYPE html>
<html>

	<head>
	
		<meta charset="utf8" />
		<title><?= $page->title ?> - PHP-SFW</title>
		<link rel="stylesheet" href="/static/styles/sfw-fonts.less.css" />
		
	</head>

	<body>
		
		<?php @include_once $page->page_part_path("content"); ?>
		
	</body>

</html>