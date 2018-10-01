<?php

// Manage Database Table

namespace SFW;

use \PDO;
use \PDOStatement;

abstract class TableManager {

	private $db;
	private $columns;

	public function __construct( $db ) {

		$this->db = $db;
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

	protected function prepare( $query ) : PDOStatement {
		return $this->db->prepare( $query );
	}

	protected function bind_params( PDOStatement $statement, $obj, array $columns = null, array $others = null ) {

		foreach ( $this->columns as $column_name => $column_data ) {
			if ( $columns !== null && in_array( $column_name, $columns ) ) {
				self::bind_param( $statement, $column_name, $obj, $column_data["get"], $column_data["pdo_type"] );
			}
		}

		if ( is_array( $others ) ) {

			foreach ( $others as $column_name => $column_data ) {
				$statement->bindParam( ":{$column_name}", $column_data["value"], $column_data["pdo_type"] );
			}

		}

	}

	protected function fetch_columns( PDOStatement $statement, callable $builder, array $columns = null, boolean $single = false ) {

		$datas = [];
		$columns = [];

		foreach ( $this->columns as $column_data ) {

			if ( $columns !== null && in_array( $column_name, $columns ) ) {

				$statement->bindColumn( $column_name, $datas[ $column_name ] );
				$columns[ $column_name ] = $column_data;

			}

		}

		$statement->execute();

		$objs = [];

		while ( $statement->fetch( PDO::FETCH_BOUND ) ) {

			$obj = $builder();

			foreach ( $columns as $column_name => $column_data ) {
				$set = $column_data["set"];
				$obj->$set( $datas[ $column_name ] );
			}

			if ( $single ) return $obj;
			$objs[] = $obj;

		}

		if ( $single ) return null;
		return $objs;

	}

	public static function bind_param( PDOStatement $statement, string $column_name, $obj, string $get, integer $pdo_type ) {
		$statement->bindParam( ":{$column_name}", $obj->$get(), $pdo_type );
	}

}

?>
