<?php

// Common include file

@ini_set( 'display_errors', 1 );
@error_reporting( E_ALL );
@ini_set( 'file_uploads', 1 );

use WOTORG\src\Config;
use WOTORG\src\Utils;
use WOTORG\src\Lang;
use WOTORG\src\SessionManager;
use WOTORG\src\Core;

if ( getcwd() == dirname( __FILE__ ) ) {
	die("Denied !");
}

if ( version_compare( PHP_VERSION, "5.5.0", "lt" ) ) {
	die( "PHP 5.5+ required.<br>Currently installed version is : " . phpversion() );
}

define( "WOTORG", true );
define( "AJAX", ( isset( $headers['X-Requested-With'] ) && $headers['X-Requested-With'] == 'XMLHttpRequest') );

// Autoloaders
require_once("autoloader.inc.php");
Autoloader::register_function();

// Add or remove HTTPS
if ( isset( $_SERVER["HTTPS"] ) != boolval( Config::get("global:secure") ) ) {

	Utils::redirect( Config::get_advised_url() . $_SERVER["REQUEST_URI"] );
	die();

}

// Default charset used to parse all files
@ini_set('default_charset', 'utf-8');
@mb_internal_encoding('utf-8');

// Lang and session
Lang::init_languages();
// Lang::set_current_language( "fr_FR" );
SessionManager::session_start();

?>
