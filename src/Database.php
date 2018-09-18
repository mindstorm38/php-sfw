<?php

// Database connection manager

namespace SFW\;

use SFW\Config;
use SFW\Utils;
use SFW\Core;
use \PDO;

final class Database {

	private static $registered_managers = [];
	private static $connection = null;
	private static $managers = [];

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

				self::$connection = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $password, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );

				self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
				self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Associative array by default

			} catch ( Exception $e ) {
				Core::fatal_error( "PDO instantiation exception : " . $e->getMessage() );
			}

		}

		return self::$connection;

	}

	public static function get_pdo_type( $obj, $err = false ) {
		switch ( gettype( $obj ) ) {
			case "boolean": return PDO::PARAM_BOOL;
			case "integer": return PDO::PARAM_INT;
			case "double": return PDO::PARAM_INT;
			case "string": return PDO::PARAM_STR;
			case "array":
				if ( !$err ) return PDO::PARAM_NULL;
				throw new Exception("Array can not be specified as parameter value (for {$param_name})");
			case "object":
				if ( !$err ) return PDO::PARAM_NULL;
				throw new Exception("Object can not be specified as parameter value (for {$param_name})");
			case "resource":
				if ( !$err ) return PDO::PARAM_NULL;
				throw new Exception("Resource can not be specified as parameter value (for {$param_name})");
			default: return PDO::PARAM_NULL;
		}
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
