<?php

use SFW\Lang;

$page->title = Lang::get("error", [ $page->vars["code"] ]);
$page->big_header = false;

?>