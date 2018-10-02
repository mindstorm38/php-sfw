<?php

namespace SFW;

abstract class UIDBaseClass {

	protected $uid;

	public function get_uid() {
		return $this->uid;
	}

	public function set_uid( $uid ) {
		$this->_uid = intval( $uid );
	}

}

?>
