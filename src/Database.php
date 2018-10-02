<?php

// Database connection manager

namespace SFW;

use SFW\Config;
use SFW\Utils;
use SFW\Core;
use SFW\TableManager;
use SFW\TableDefinition;
use \PDO;
use \PDOStatement;
use \Exception;

final class Database {

	private static $registered_managers = [];
	private static $connection = null;
	private static $managers = [];
	private static $table_definitions = [];

	public static function register_manager_class( $name, $class_path ) {
		if ( array_key_exists( $name, self::$registered_managers ) ) throw new Exception("This manager already exists '{$name}'");
		if ( !class_exists( $class_path ) ) throw new Exception("Invalid manager class path '{$class_path}'");
		self::$registered_managers[ $name ] = $class_path;
	}

	public static function get_manager( $name ) {
		if ( array_key_exists( $name, self::$managers ) ) return self::$managers[ $name ];
		if ( !array_key_exists( $name, self::$registered_managers ) ) return null;
		$class_path = self::$registered_managers[ $name ];
		if ( !class_exists( $class_path ) ) return null;
		return self::$managers[ $name ] = new $class_path( self::get_connection() );
	}

	public static function register_table_definition( string $name, string $table_definition_class ) {
		if ( array_key_exists( $name, self::$table_definitions ) ) throw new Exception("This table definition already exists '{$name}'");
		if ( !class_exists( $table_definition_class ) ) throw new Exception("Invalid table definition class '{$table_definition_class}'");
		self::$table_definitions[ $name ] = [
			"class" => $table_definition_class,
			"instance" => null
		];
	}

	public static function get_table_definition( string $name ) : TableDefinition {
		if ( !array_key_exists( $name, self::$table_definitions ) ) throw new Exception("Invalid table definition name '{$name}'");
		$definition = self::$table_definitions[ $name ];
		if ( $definition["instance"] !== null ) return $definition["instance"];
		$class = $definition["class"];
		return $definition["instance"] = new $class();
	}

	public static function get_connection() {

		if ( !@class_exists("PDO") ) {
		    Core::missing_extension( "pdo", true );
		    return;
		}

		if ( self::$connection == null ) {

			$host = Config::get("database:host");
			$name = Config::get("database:name");
			$charset = Config::get("database:charset");
			$user = Config::get("database:user");
			$password = Config::get("database:password");

			try {

				self::$connection = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $password );

				self::$connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
				self::$connection->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC ); // Associative array by default
				self::$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			} catch ( Exception $e ) {
				Core::fatal_error( "PDO instantiation exception : " . $e->getMessage() );
			}

		}

		return self::$connection;

	}

	public static function get_pdo_type( $obj ) {
		if ( is_bool( $obj ) ) return PDO::PARAM_INT;
		if ( is_int( $obj ) ) return PDO::PARAM_INT;
		if ( is_double( $obj ) ) return PDO::PARAM_STR;
		if ( is_string( $obj ) ) return PDO::PARAM_STR;
		return PDO::PARAM_NULL;
	}

	public static function begin_transaction() {
		self::get_connection()->beginTransaction();
	}

	public static function commit_transaction() {
		self::get_connection()->commit();
	}

	public static function rollback_transaction() {
		self::get_connection()->rollback();
	}

	public static function last_insert_id() {
		return self::get_connection()->lastInsertId();
	}

	public static function prepare( $query ) : PDOStatement {
		return self::get_connection()->prepare( $query );
	}

	public static function bind_param( PDOStatement $stmt, string $param, &$var, int $pdo_type ) {
		$stmt->bindParam( ":{$param}", $var, $pdo_type );
	}

	public static function bind_value( PDOStatement $stmt, string $param, $value, int $pdo_type = null ) {
		$stmt->bindValue( ":{$param}", $value, $pdo_type === null ? self::get_pdo_type( $value ) : $pdo_type );
	}

	public static function bind( PDOStatement $stmt, TableDefinition $table_def, $obj, array $columns ) {

		$def_columns = $table_def->get_columns();

		foreach ( $columns as $column_name ) {
			if ( array_key_exists( $column_name, $def_columns ) ) {

				$def_column_data = $def_columns[ $column_name ];
				$get = $def_column_data["get"];
				$value = $obj->$get();

				$stmt->bindValue( ":{$column_name}", $value, self::get_pdo_type( $value ) );

			}
		}

	}

	public static function fetch( PDOStatement $stmt, TableDefinition $table_def, $builder, array $columns, bool $single = false ) {

		$def_columns = $table_def->get_columns();

		$stmt->execute();

		$objs = [];

		while ( $values = $stmt->fetch( PDO::FETCH_ASSOC ) ) {

			$obj = $builder( $values );

			foreach ( $values as $column_name => $value ) {
				if ( array_key_exists( $column_name, $def_columns ) && in_array( $column_name, $columns ) ) {
					$set = $def_columns[ $column_name ]["set"];
					$obj->$set( $value );
				}
			}

			if ( $single ) {

				$stmt->closeCursor();
				return $obj;

			}

			$objs[] = $obj;

		}

		$stmt->closeCursor();

		if ( $single ) return null;
		return $objs;

	}

	public static function fetch_raw( PDOStatement $stmt, $builder, bool $single = false ) {

		$stmt->execute();

		$values = [];

		while ( $values = $stmt->fetch( PDO::FETCH_ASSOC ) ) {

			if ( $single ) {

				$stmt->closeCursor();
				return $builder( $values );

			}

			$values = $builder( $values );

		}

		$stmt->closeCursor();

		if ( $single ) return null;
		return $values;

	}

	public static function exec($query, $params = [], $fetch = false, $clazz = null) {

		if ( $params == null || count($params) > 0  && !Utils::is_assoc_array( $params ) ) $params = [];

		$query = self::get_connection()->prepare( $query );

		foreach ( $params as $param_name => $param_value ) {

			$pdo_type = self::get_pdo_type( $param_name, true );

			$query->bindValue(":{$param_name}", $param_value, $pdo_type);

		}

		$query->execute();

		if ( $fetch ) {

			if ( $clazz != null && class_exists( $clazz ) ) {
				$query->setFetchMode(PDO::FETCH_CLASS, $clazz);
			} else {
				$query->setFetchMode(PDO::FETCH_ASSOC);
			}

			return $query->fetchAll();

		} else {
			return true;
		}

		$query->closeCursor();

	}

}

?>
