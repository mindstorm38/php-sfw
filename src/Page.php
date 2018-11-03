<?php

namespace SFW;

final class Page {
    
    public $raw;
    public $identifier;
    public $directory;
    
    public $template_identifier;
    public $template_directory;
    
    public $title;
    
    public function __construct($raw, $identifier) {
        
        $this->raw = $raw;
        $this->identifier = $identifier;
        
        $this->title = $identifier;
        
    }
    
    public function has_template() {
        return $this->template_identifier !== null;
    }
    
    public function page_part_path( string $part_id ) {
        return Utils::path_join( $this->identifier, $part_id . ".php" );
    }
    
    public function template_part_path( string $part_id ) {
        if ( !$this->has_template() ) return null;
        return Utils::path_join( $this->template_directory, $part_id . ".php" );
    }
    
}

?>