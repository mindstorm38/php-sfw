<?php

class Autoloader {

	private $paths;

	public function __construct( array $paths ) {
		$this->paths = $path;
	}

	public function autoload( $path ) {

		if ( !array_key_exists( $path, $this->paths ) ) return;

		$path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $this->paths[ $path ];

		if ( file_exists( $path ) && is_readable( $path ) ) {
			require_once $path;
		}

	}

	public function register() {
		spl_autoload_register( $this->autoload );
	}

}

new Autoloader( [

	"PHPHelper\\src\\Utils"							=> "utils.class.php",
	"PHPHelper\\src\\Config"						=> "config.class.php",
	"PHPHelper\\src\\Core"							=> "core.class.php",
	"PHPHelper\\src\\Lang"							=> "lang.class.php",
	"PHPHelper\\src\\SessionManager"				=> "sessionmanager.class.php",
	"PHPHelper\\src\\SessionHandler"				=> "sessionmanager.class.php",
	"PHPHelper\\src\\Database"						=> "database.class.php",
	"PHPHelper\\src\\SQLManager"					=> "sqlmanager.class.php",
	"PHPHelper\\src\\SQLSerializable"				=> "sqlserializable.class.php",
	"PHPHelper\\src\\Query"							=> "querymanager.class.php",
	"PHPHelper\\src\\QueryResponse"					=> "querymanager.class.php",
	"PHPHelper\\src\\QueryManager"					=> "querymanager.class.php"

] ).register();

?>
