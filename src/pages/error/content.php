<?php

use SFW\Lang;
use SFW\Page;

/** @var Page $page */

?>

<h1><?= Lang::get("error.code_message", [ $page["vars"]["code"] ]) ?></h1>
<h3><?= Lang::get("http_status.{$page["vars"]["code"]}")  ?></h3>
<h4><?= $page["vars"]["msg"] ?></h4>
