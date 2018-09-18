<?php

// Query response

namespace SFW\;

class QueryResponse {

	public $error;
	public $lang;
	public $vars;
	public $data;

	function __construct( $error, $lang, $vars = [], $data = [] ) {

		$this->error = $error;
		$this->lang = $lang;
		$this->vars = $vars;
		$this->data = $data;

	}

}

?>
