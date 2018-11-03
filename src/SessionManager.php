<?php

// Session manager used to store logged state

namespace SFW;

final class SessionManager {
    
    private static $handler = null;
    private static $session_name = null;
    private static $temp_perms = [];
    
    public static function session_start() {
        
        if ( !@function_exists( "session_name" ) ) {
            
            Core::missing_extension( "session", true );
            return;
            
        }
        
        if ( self::$session_name === null ) {
            
            self::$session_name = Config::get( "session:name", "SESSID" );
            
            $session_path = Config::get( "session:path", "/" );
            if ( !Utils::ends_with( $session_path, "/" ) ) $session_path .= "/";
            
            // Set session cookie parameters
            session_set_cookie_params(
                self::get_config_lifetime(),
                $session_path,
                "." . Config::get( "global:advised_host", "" ),
                Config::get( "global:secure", false ),
                true
                );
            
            // Cookie are safer
            @ini_set( "session.use_cookies", "true" );
            // Use cookie only
            @ini_set( "session.use_only_cookies", "1" );
            // Strict session mode
            @ini_set( "session.use_strict_mode", "1" );
            // Only HTTP access, not js ...
            @ini_set( "session.cookie_httponly", "1" );
            // Do not force transparent session ids
            @ini_set( "session.use_trans_sid", "0" );
            
            @ini_set( "session.hash_function", "1" );
            
            // Allow caching only for client, not for proxy
            session_cache_limiter("private");
            
        }
        
        session_name( self::$session_name );
        
        // Start session
        session_start();
        
        if ( count( $_SESSION ) > 0 ) {
            
            if ( self::validate_session() ) {
                
                if ( !self::prevent_hijacking() ) {
                    
                    self::setup_session_vars();
                    
                    self::regenerate_session();
                    
                } else if ( rand( 1, 100 ) <= 5 ) { // 5% chance to regenerate session id changing on any request
                    self::regenerate_session();
                }
                
            } else {
                self::destroy();
            }
            
        }
        
    }
    
    public static function get_config_lifetime() {
        return Config::get( "session:lifetime", 86400 );
    }
    
    public static function destroy() {
        
        if ( headers_sent() ) return false;
        
        $_SESSION = [];
        
        if ( self::$handler !== null )
            self::$handler->log_out();
            
            session_destroy();
            session_start();
            
            return true;
            
    }
    
    private static function prevent_hijacking() {
        if ( !isset( $_SESSION["IP_ADDR"] ) || !isset( $_SESSION["USER_AGENT"] ) ) return false;
        if ( $_SESSION["IP_ADDR"] != $_SERVER["REMOTE_ADDR"] ) return false;
        if ( $_SESSION["USER_AGENT"] != $_SERVER["HTTP_USER_AGENT"] ) return false;
        return true;
    }
    
    private static function validate_session() {
        if ( !isset( $_SESSION['EXPIRES'] ) ) return false;
        if( $_SESSION['EXPIRES'] < time() ) return false;
        if ( self::$handler !== null )
            return boolval( self::$handler->init() );
            return true;
    }
    
    private static function regenerate_session() {
        session_regenerate_id( false );
    }
    
    public static function setup_session_expires( $expires_at ) {
        if ( !self::is_logged() ) return;
        $_SESSION["EXPIRES"] = $expires_at;
    }
    
    public static function setup_session_vars() {
        if ( !self::is_logged() ) return;
        $_SESSION['IP_ADDR'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    public static function get_handler() {
        return self::$handler;
    }
    
    public static function set_handler( SessionHandler $handler ) {
        self::$handler = $handler;
    }
    
    public static function set_logged( ...$params ) {
        
        self::setup_session_vars();
        self::setup_session_expires( time() + self::get_config_lifetime() );
        
        if ( self::$handler !== null )
            self::$handler->set_logged( $params );
            
    }
    
    public static function is_logged() {
        return ( self::$handler === null ) ? ( isset( $_SESSION["EXPIRES"] ) && $_SESSION["EXPIRES"] !== null ) : self::$handler->is_logged();
    }
    
    // Call handler methods if called method not found
    public static function __callStatic( $name, $args ) {
        
        if ( self::$handler === null ) return;
        
        if ( method_exists( self::$handler, $name ) ) {
            return self::$handler->$name( ...$args );
        }
        
    }
    
}

?>
