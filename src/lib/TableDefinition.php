<?php

// Manage Database Table

namespace SFW;

use \PDO;

trigger_error("TableDefinition class is now deprecated", E_USER_DEPRECATED);

class TableDefinition {
    
    private $columns;
    
    public function __construct() {
        $this->columns = [];
    }
    
    public function add_uid() {
        $this->add_column( "uid", "get_uid", "set_uid", PDO::PARAM_INT );
    }
    
    public function add_column( string $name, string $get, string $set, int $pdo_type ) {
        
        $this->columns[ $name ] = [
            "name" => $name,
            "get" => $get,
            "set" => $set,
            "pdo_type" => $pdo_type
        ];
        
    }
    
    public function remove_column( string $name ) {
        unset( $this->columns[ $name ] );
    }
    
    public function get_column( $name ) : array {
        return $this->columns[ $name ];
    }
    
    public function get_columns() : array {
        return $this->columns;
    }
    
    public function get_column_names_except( array $except_columns ) : array {
        return array_diff( array_keys( $this->columns ), $except_columns );
    }
    
    public function get_column_names() : array {
        return array_keys( $this->columns );
    }
    
}

?>
