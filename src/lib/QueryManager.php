<?php

// Query manager, execute queries and return json

namespace SFW;

use \Exception;
use \Throwable;

/**
 * 
 * Used to manage queries (defined by {@link Query}) and execute them to return JSON.
 * 
 * @author Théo Rozier
 *
 */
class QueryManager {
	
	const PARAM	                     = "query";
	
	// JSON keys
	const JSON_ERROR                 = "error";
	const JSON_DATA                  = "data";
	const JSON_MESSAGE               = "message";
	
	public static $registered_queries = [];
	public static $queries_namespaces = [];
	public static $queries = [];
	
	/**
	 * Register a query class with a custom name.
	 * @param string $name The custom name used to retreive the query class.
	 * @param mixed $class_path Query class path.
	 * @throws Exception If a query of the same name if already registered. Or the query class doesn't exists.
	 */
	public static function register_query_class( string $name, $class_path ) : void {
		
		if ( array_key_exists( $name, self::$registered_queries ) ) {
			throw new Exception("This query already exists '{$name}'");
		}
		
		if ( !class_exists( $class_path ) ) {
			throw new Exception("Invalid query class path '{$class_path}'");
		}
		
		self::$registered_queries[ $name ] = $class_path;
		
	}
	
	/**
	 * Add a namespace (using a PSR-4 autoloading) as base for searching for all queries.
	 * @param string $namespace
	 */
	public static function register_query_namespace( string $namespace ) : void {
		
		if ( $namespace[ strlen($namespace) - 1 ] !== "\\" ) {
			$namespace .= "\\";
		}
		
		if ( !in_array( $namespace, $queries_namespaces ) ) {
			self::$queries_namespaces[] = $namespace;
		}
		
	}
	
	/**
	 * Get a query instance from its name.
	 * @param string $name The name of the query (if using registered namespaces, you can use the Query Class name).
	 * @return Query|null The cached or instantiated query. Or null if no query have this name.
	 */
	public static function get_query_instance( string $name ) {
		
		if ( isset( self::$queries[$name] ) ) {
			return self::$queries[$name];
		}
		
		$new_query_class = null;
		
		foreach ( self::$queries_namespaces as $ns ) {
			
			$new_query_class = $ns . $name;
			
			if ( class_exists( $new_query_class ) ) {
				break;
			} else {
				$new_query_class = null;
			}
			
		}
		
		if ( $new_query_class === null ) {
			
			if ( !array_key_exists( $name, self::$registered_queries ) ) {
				return null;
			}
			
			$new_query_class = self::$registered_queries[ $name ];
			
			if ( !class_exists( $new_query_class ) ) {
				return null;
			}
			
		}
		
		return self::$queries[ $name ] = new $new_query_class();
		
	}
	
	/**
	 * Execute the query and return only an associative array to describe the result or errors of execution.
	 * @param string $name The query name.
	 * @param array $array Parameters to pass to the query.
	 * @return array The associative array result.
	 * @see QueryManager::get_query_instance
	 */
	public static function execute( string $name, array $array ) {
		
		if ( empty( $name ) ) {
			
			return [
				QueryManager::JSON_ERROR => "INVALID_QUERY_NAME",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_name"),
				QueryManager::JSON_DATA => [
					"name" => $name
				]
			];
			
		}
		
		try {
			
			$query_instance = self::get_query_instance( $name );
			
			if ( $query_instance == null ) {
				
				return [
					QueryManager::JSON_ERROR => "INVALID_QUERY_NAME",
					QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_name"),
					QueryManager::JSON_DATA => [
						"name" => $name
					]
				];
				
			}
			
			$required_variables = $query_instance->required_variables();
			
			foreach ( $required_variables as $required_variable ) {
				
				if ( !array_key_exists( $required_variable, $array ) ) {
					
					return [
						QueryManager::JSON_ERROR => "MISSING_PARAMETER",
						QueryManager::JSON_MESSAGE => Lang::get( "query.error.missing_parameter", [ $required_variable ] ),
						QueryManager::JSON_DATA => [
							"param" => $required_variable
						]
					];
					
				}
				
			}
			
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
			
		} catch (Throwable $e) {
			
			return [
				QueryManager::JSON_ERROR => "QUERY_EXECUTION",
				QueryManager::JSON_MESSAGE => Lang::get("query.error.query_execution", [ $e->getMessage() ]),
				QueryManager::JSON_DATA => [
					"code" => $e->getCode(),
					"file" => $e->getFile(),
					"trace_string" => $e->getTraceAsString(),
					"trace" => explode( '\n', $e->getTraceAsString() )
				]
			];
			
		}
		
	}
	
	/**
	 * Execute the query and send response to client output stream (via <code>echo</code>).
	 * @param string $name The query name.
	 * @param array $array Parameters.
	 * @see QueryManager::execute
	 */
	public static function send_query_response( string $name, array $array ) {
		
		Utils::content_type_json();
		echo json_encode( self::execute($name, $array) );
		
	}
	
}

?>
