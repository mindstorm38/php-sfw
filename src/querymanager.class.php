<?php

// Query manager, execute queries and return json

namespace PHPHelper\src;

use PHPHelper\src\Lang;

abstract class Query {

     public abstract function required_variables();

     public abstract function execute( $vars );

}

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

class QueryManager {

	const PARAM							= "query";

	// JSON keys
	const JSON_ERROR					= "error";
	const JSON_DATA						= "data";
	const JSON_MESSAGE					= "message";

	const JSON_MISSING_PARAM_NAME		= "param";

	public static $registered_queries = [];
	public static $queries = [];

	public static function register_query_class( $name ) {
		if ( array_key_exists( $name, self::$registered_queries ) ) throw new Exception("This query already exists '{$name}'");
		if ( !class_exists( $class_path ) ) throw new Exception("Invalid query class path '{$class_path}'");
		self::$registered_queries[ $name ] = $class_path;
	}

	public static function get_query_instance( $name ) {
		if ( isset( self::$queries[ $name ] ) ) return self::$queries[ $name ];
		if ( !array_key_exists( $name, self::$registered_queries ) ) return null;
		$new_query_class = self::$registered_queries[ $name ];
		if ( !class_exists( $new_query_class ) ) return null;
		return self::$queries[ $name ] = new $new_query_class();
	}

	public static function execute( $name, $array ) {

		if ( empty( $name ) ) {
			return [
				QueryManager::JSON_ERROR => "INVALID_QUERY_NAME",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_name"),
				QueryManager::JSON_DATA => []
			];
		}

		$query_instance = self::get_query_instance( $name );

		if ( $query_instance == null) {
			return [
				QueryManager::JSON_ERROR => "INVALID_QUERY_NAME",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_name"),
				QueryManager::JSON_DATA => []
			];
		}

		$required_variables = $query_instance->required_variables();

		foreach ( $required_variables as $required_variable ) {
			if ( !array_key_exists( $required_variable, $array ) ) {
				return [
					QueryManager::JSON_ERROR => "MISSING_PARAMETER",
					QueryManager::JSON_MESSAGE => Lang::get( "query.error.missing_parameter", [ $required_variable ] ),
					QueryManager::JSON_DATA => [
						QueryManager::JSON_MISSING_PARAM_NAME => $required_variable
					]
				];
			}
		}

		try {

			$response = $query_instance->execute( $array );

		if ( $response instanceof QueryResponse ) {

			return [
				QueryManager::JSON_ERROR => $response->error === true ? "QUERY_ERROR" : $response->error,
				QueryManager::JSON_MESSAGE => Lang::get( $response->lang, $response->vars ),
				QueryManager::JSON_DATA => $response->data
			];

		} else {

			return [
				QueryManager::JSON_ERROR => "INVALID_QUERY_RESPONSE",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_response"),
				QueryManager::JSON_DATA => []
			];

		}

		} catch (Exception $e) {

			return [
				QueryManager::JSON_ERROR => "QUERY_EXECUTION",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.query_execution", [ $e->getMessage() ]),
				QueryManager::JSON_DATA => []
			];

		}

	}

}

?>
