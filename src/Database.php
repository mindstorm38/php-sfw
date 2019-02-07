<?php

// Database connection manager

namespace SFW;

use \PDO;
use \PDOStatement;
use \Exception;

final class Database {
	
	private static $connection = null;
	private static $table_definitions = [];
	
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
	
	public static function bind_values_array( PDOStatement $stmt, array $values_array ) {
		if ( !Utils::is_assoc_array( $values_array ) ) return;
		foreach ( $values_array as $param => $value )
			self::bind_value( $stmt, $param, $value );
	}
	
	public static function bind_class( PDOStatement $stmt, object $object, string $prefix = "" ) {
		
		$values_array = [];
		
		foreach ( get_object_vars( $object ) as $k => $v ) {
			$values_array["{$prefix}{$k}"] = $v;
		}
		
		self::bind_values_array( $stmt, $values_array );
		
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
	
	public static function fetch_raw( PDOStatement $stmt, callable $builder = null, bool $single = false ) {
		
		if ( $builder === null ) {
			$builder = function( $values ) { return $values; };
		}
		
		$stmt->execute();
		
		$values = [];
		
		while ( $values_raw = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			
			if ( $single ) {
				
				$stmt->closeCursor();
				return $builder( $values_raw );
				
			}
			
			$values[] = $builder( $values_raw );
			
		}
		
		$stmt->closeCursor();
		
		if ( $single ) return null;
		return $values;
		
	}
	
	public static function fetch_class( PDOStatement $stmt, string $class_name, callable $modifier = null, bool $single = false ) {
		
		return self::fetch_raw( $stmt, function( $values ) use ( $class_name, $modifier ) {
			
			$obj = new $class_name();
			
			foreach ( $values as $k => $v ) {
				
				$obj->$k = $v;
				
			}
			
			if ( $modifier !== null ) $modifier( $obj, $values );
			
			return $obj;
			
		}, $single );
			
	}
	
	public static function build_search_where( string $search_string, array $option_processors ) {
		
		$splited = explode( ' ', $search_string );
		if ( $splited === false ) return "";
		
		$i = 0;
		$where = "";
		$values = [];
		
		foreach ( $splited as $option_raw ) {
			
			if ( strlen( $option_raw ) === 0 ) continue;
			
			$option_splited = explode( ':', $option_raw );
			if ( $option_splited === false ) continue;
			$full = count( $option_splited ) === 2;
			
			$option = $full ? $option_splited[0] : "";
			$value = $full ? $option_splited[1] : $option_splited[0];
			
			$processor = $option_processors[ $option ] ?? null;
			if ( $processor === null ) continue;
			
			$ret = $processor( $value );
			if ( $ret === null ) continue;
			
			if ( $i !== 0 ) $where .= ' AND ';
			
			if ( is_string( $ret ) ) {
				$where .= $ret;
			} else {
				
				if ( isset( $ret["where"] ) ) $where .= $ret["where"];
				if ( isset( $ret["values"] ) ) $values += $ret["values"];
						
			}
			
			$i++;
			
		}
		
		return [
			"count" => $i,
			"where" => $where,
			"values" => $values
		];
		
	}
	
	public static function build_range_where( string $column, array $range, string $base_name ) {
		
		if ( Utils::is_assoc_array( $range ) || count( $range ) != 2 )
			throw new Exception("Invalid 'range' type");
			
			if ( $range[0] === null && $range[1] === null )
				throw new Exception("Invalid 'range' type");
				
				if ( $range[0] === null ) {
					
					return [
						"where" => "{$column} <= :{$base_name}",
						"values" => [ $base_name => $range[1] ]
						];
					
				} else if ( $range[1] === null ) {
					
					return [
						"where" => "{$column} >= :{$base_name}",
						"values" => [ $base_name => $range[0] ]
						];
					
				} else {
					
					return [
						"where" => "{$column} BETWEEN :{$base_name}_min AND :{$base_name}_max",
						"values" => [
							"{$base_name}_min" => $range[0],
							"{$base_name}_max" => $range[1]
							]
						];
					
				}
				
	}
	
	public static function build_column_list( array $columns ) {
		foreach ( $columns as &$column ) {
			$parts = explode( '.', $column );
			foreach ( $parts as &$part ) {
				$part = "`{$part}`";
			}
			$column = implode( '.', $parts );
		}
		return implode( ', ', $columns );
	}
	
	public static function build_param_list( array $params ) {
		foreach ( $params as &$param ) {
			$param = ":{$param}";
		}
		return implode( ', ', $params );
	}
	
	public static function build_column_param_list( array $columns_params ) {
		$list = [];
		foreach ( $columns_params as $column => $param ) {
			$parts = explode( '.', is_int( $column ) ? $param : $column );
			foreach ( $parts as &$part ) {
				$part = "`{$part}`";
			}
			$list[] = implode( '.', $parts ) . " = :{$param}";
		}
		return implode( ', ', $list );
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
