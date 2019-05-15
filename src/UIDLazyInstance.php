<?php

namespace SFW;

use \Exception;

class UIDLazyInstance {
	
	private $vr; // UID Value Ref
	private $ic; // Instance class
	private $cf; // Create Function
	private $i = null; // Instance
	private $l = false; // Loaded
	
	public function __construct( &$value_ref, $inst_class, $create_func ) {
		
		if ( !is_subclass_of( $inst_class, "SFW\\UIDBaseClass" ) ) {
			throw new Exception("Invalid instance class given for lazy UID instance, must extend SFW\\UIDBaseClass");
		}
		
		$this->vr = &$value_ref;
		$this->ic = $inst_class;
		$this->cf = $create_func;
		
	}
	
	public function get() {
		
		if ( $this->l ) return $this->i;
		
		$inst = ($this->cf)( $this->vr );
		
		$this->check_inst( $inst, "Invalid instance class created by lazy function" );
		
		$this->i = $inst;
		$this->l = true;
		
		return $this->i;
		
	}
	
	public function set( $instance ) {
		
		$this->check_inst( $instance, "Invalid instance class for this lazy instance" );
		
		$this->vr = $instance->uid;
		$this->i = $instance;
		$this->l = true;
		
	}
	
	private function check_inst( $i, string $msg ) {
		
		if ( $i !== null && get_class( $i ) !== $this->ic ) {
			throw new Exception( $msg );
		}
			
	}
	
}

?>
