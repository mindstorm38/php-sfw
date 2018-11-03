<?php

require __DIR__ . '/common.php';

use SFW\Core;
use SFW\Config;
use SFW\Utils;

Utils::force_no_cache();
Utils::content_type_html();

Core::add_page_aliases( "home", "" );
Core::set_page_template( "home", "main" );

$page = Core::load_page( $_GET["page"] );

if ( $page->has_template() )
    @include_once $page->template_part_path("init");

@include_once $page->page_part_path("init");

if ( $page->has_template() )
   @include_once $page->template_part_path("post_init");

if ( !$page->has_template() )
    die();
    
// NOW COME YOUR COMMON PAGE

?>

<!DOCTYPE html>
<html>

	<head>

		<meta charset="utf-8" />
		<base href="<?= Config::get_advised_url() ?>" />

		<!--
		<link rel="stylesheet" href="./static/main.css" />
		<script type="text/javascript" src="./static/main.js"></script>
		-->
		
		<?php 
		
		@include_once $page->template_part_path("head");
		@include_once $page->page_part_path("head");
		
		?>

	</head>

	<body>

		<?php
		
		@include_once $page->template_part_path("template");
		
		?>

	</body>

</html>
