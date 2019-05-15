<?php

namespace SFW;

trigger_error("SQLSerializable class is now deprecated", E_USER_DEPRECATED);

abstract class SQLSerializable {

	protected $_uid;

	public function get_uid() { return $this->_uid; }
	public function set_uid( $uid ) { $this->_uid = $uid; }

}

?>
