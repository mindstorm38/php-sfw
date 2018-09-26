<?php

// Session handler used by Session manager

namespace SFW;

abstract class SessionHandler {

	public abstract function init( &$session );
	public abstract function set_logged( &$expires_at, $params, &$session );
	public abstract function log_out();
	public abstract function is_logged();

}

?>
