<?php

namespace SFW;

use \ArrayAccess;
use \BadMethodCallException;

/**
 * 
 * <p>Session class working in pair with {@link Sessionner}.</p>
 * 
 * @author Théo Rozier
 *
 */
class Session implements ArrayAccess {
	
	protected $lifetime = Sessionner::DEFAULT_SESS_LIFETIME;
	
	protected $config;
	
	protected $data = [];
	
	public function __construct() {}
	
	public function load( array $data ) {
		$this->data = $data;
	}
	
	public function load_config( array $raw_config ) {
		
		if ( isset($raw_config["lifetime"]) ) {
			
			if ( is_int($raw_config["lifetime"]) ) {
				$this->lifetime = $raw_config["lifetime"];
			} else {
				throw new BadMethodCallException("Lifetime must be an integer.");
			}
			
		}
		
		$this->config = $raw_config;
		
	}
	
	public function get_lifetime() {
		return $this->lifetime;
	}
	
	public function set_lifetime( int $lifetime ) {
		$this->lifetime = $lifetime;
	}
	
	public function get_data() {
		return $this->data;
	}
	
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}
	
	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	public function offsetUnset($offset) {
		unset( $this->data[$offset] );
	}

	public function offsetSet($offset, $value) {
		$this->data[$offset] = $value;
	}
	
}