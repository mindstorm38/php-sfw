<?php

// Query manager, execute queries and return json

namespace SFW;

use \Exception;
use \Throwable;

/**
 * <p>Used to manage queries (defined by {@link Query}) and execute them to return JSON.</p>
 * <p>Can also be used staticaly for a main manager. <b>Note that the default QueryManager use session nonce (see {@link Sessionner::get_session_nonce}).</b></p>
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
	
	// Cryptographic Nonce
	const NONCE_PARAM        = "\$nonce\$";
	
	// Main manager
	
	private static $main = null;
	
	public static function __callStatic(string $name, array $args) {
		
		if ( self::$main === null ) {
			self::$main = new QueryManager();
		}
		
		(self::$main->$name)(...$args);
		
	}
	
	/**
	 * @return QueryManager The main query manager, using session nonces.
	 */
	public static function get_main() : QueryManager {
		return self::$main;
	}
	
	// Manager class
	
	private $require_nonce;
	private $registered_queries = [];
	private $queries_namespaces = [];
	private $queries = [];
	
	public function __construct( bool $require_nonce = true ) {
		$this->require_nonce = $require_nonce;
	}
	
	/**
	 * Register a query class with a custom name.
	 * @param string $name The custom name used to retreive the query class.
	 * @param mixed $class_path Query class path.
	 * @throws Exception If a query of the same name if already registered. Or the query class doesn't exists.
	 */
	public function register_query_class( string $name, $class_path ) : void {
		
		if ( array_key_exists( $name, $this->registered_queries ) ) {
			throw new Exception("This query already exists '{$name}'");
		}
		
		if ( !class_exists( $class_path ) ) {
			throw new Exception("Invalid query class path '{$class_path}'");
		}
		
		$this->registered_queries[ $name ] = $class_path;
		
	}
	
	/**
	 * Add a namespace (using a PSR-4 autoloading) as base for searching for all queries.
	 * @param string $namespace
	 */
	public function register_query_namespace( string $namespace ) : void {
		
		if ( $namespace[ strlen($namespace) - 1 ] !== "\\" ) {
			$namespace .= "\\";
		}
		
		if ( !in_array( $namespace, $this->queries_namespaces ) ) {
			$this->queries_namespaces[] = $namespace;
		}
		
	}
	
	/**
	 * Get a query instance from its name.
	 * @param string $name The name of the query (if using registered namespaces, you can use the Query Class name).
	 * @return Query|null The cached or instantiated query. Or null if no query have this name.
	 */
	public function get_query_instance( string $name ) {
		
		if ( empty($name) ) {
			return null;
		}
		
		if ( isset( $this->queries[$name] ) ) {
			return $this->queries[$name];
		}
		
		$new_query_class = null;
		
		foreach ( $this->queries_namespaces as $ns ) {
			
			$new_query_class = $ns . $name;
			
			if ( class_exists( $new_query_class ) ) {
				break;
			} else {
				$new_query_class = null;
			}
			
		}
		
		if ( $new_query_class === null ) {
			
			if ( !array_key_exists( $name, $this->registered_queries ) ) {
				return null;
			}
			
			$new_query_class = $this->registered_queries[ $name ];
			
			if ( !class_exists( $new_query_class ) ) {
				return null;
			}
			
		}
		
		try {
			
			$obj = new $new_query_class();
			return $this->queries[ $name ] = $obj;
			
		} catch (Throwable $e) {
			return null;
		}
		
	}
	
	/**
	 * Execute the query and return only an associative array to describe the result or errors of execution.
	 * @param string $name The query name.
	 * @param array $array Parameters to pass to the query.
	 * @return array The associative array result.
	 * @see QueryManager::get_query_instance
	 */
	public function execute( string $name, array $array ) {
		
		try {
			
			$query_instance = $this->get_query_instance( $name );
			
			if ( $query_instance == null ) {
				
				return [
					QueryManager::JSON_ERROR => "INVALID_QUERY_NAME",
					QueryManager::JSON_MESSAGE => Lang::get("query.error.invalid_query_name"),
					QueryManager::JSON_DATA => [
						"name" => $name
					]
				];
				
			}
			
			if ( $this->require_nonce && ( !isset($array[self::NONCE_PARAM]) || Sessionner::get_session_nonce() !== $array[self::NONCE_PARAM] ) ) {
				
				return [
					QueryManager::JSON_ERROR => "NOT_ALLOWED",
					QueryManager::JSON_MESSAGE => Lang::get("query.error.not_allowed"),
					QueryManager::JSON_DATA => []
				];
				
			}
			
			$missing_vars = array_diff( array_keys($array), $query_instance->required_variables() );
			
			if ( !empty($missing_vars) ) {
				
				return [
					QueryManager::JSON_ERROR => "MISSING_PARAMETER",
					QueryManager::JSON_MESSAGE => Lang::get( "query.error.missing_parameter", [ implode(', ', $missing_vars) ] ),
					QueryManager::JSON_DATA => [
						"params" => $missing_vars
					]
				];
				
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
	public function send_query_response( string $name, array $array ) {
		
		Utils::content_type_json();
		echo json_encode( $this->execute($name, $array) );
		
	}
	
}

?>
