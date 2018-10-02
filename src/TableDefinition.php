<?php

// Manage Database Table

namespace SFW;

use \PDO;
use \PDOStatement;

abstract class TableDefinition {

	private $columns;

	public function __construct() {
		$this->columns = [];
	}

	protected function add_uid() {
		$this->add_column( "uid", "get_uid", "set_uid", PDO::PARAM_INT );
	}

	protected function add_column( string $name, string $get, string $set, integer $pdo_type ) {

		$this->columns[ $name ] = [
				"get" => $get,
				"set" => $set,
				"pdo_type" => $pdo_type
		];

	}

	protected function remove_column( string $name ) {
		unset( $this->columns[ $name ] );
	}

	public function get_columns() : array {
		return $this->columns;
	}

	public function get_column_names__except( array $except_columns ) : array {
		return array_diff( array_keys( $this->columns ), $except_columns );
	}

	public function get_column_names() : array {
		return array_keys( $this->column );
	}

}

?>
