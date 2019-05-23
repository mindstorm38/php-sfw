<?php

use SFW\Lang;

?>

<h4 class="large-margin"><?= Lang::get("prototype.welcome") ?></h4>

<input type="text" name="sfw-proto-login:user" placeholder="<?= Lang::get("prototype.user") ?>">
<input type="password" name="sfw-proto-login:password" placeholder="<?= Lang::get("prototype.password") ?>">
<button data-form-submit="sfw-proto-login"><?= Lang::get("prototype.submit") ?></button>