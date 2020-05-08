<?php

use SFW\Lang;
use SFW\Page;

/** @var Page $page */

$page["title"] = Lang::get("error", [ $page["vars"]["code"] ]);
$page["big_header"] = false;

?>