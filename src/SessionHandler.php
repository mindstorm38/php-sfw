<?php

// Session handler used by Session manager

namespace SFW;

abstract class SessionHandler {
    
    public abstract function init();
    public abstract function set_logged( $params );
    public abstract function log_out();
    public abstract function is_logged();
    
}

?>
