<?php

namespace SFW;

abstract class UIDBaseClass {

	protected $uid;

	public function __construct() {
		$this->uid = 0;
	}

	public function get_uid() {
		return $this->uid;
	}

	public function set_uid( $uid ) {
		$this->uid = intval( $uid );
	}

}

?>
