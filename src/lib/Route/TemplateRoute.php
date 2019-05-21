<?php 

namespace SFW\Route;

use \InvalidArgumentException;

/**
 * 
 * Template /example/ww{z0}/{n1}
 * 
 * @author Théo Rozier
 *
 */
class TemplateRoute extends Route {
	
	const VALID_SEGMENT_TYPES = [ 'a', 'n', 'z' ];
	const DEFAULT_SEGMENT_TYPE = "a";
	
	// Segment types :
	//
	// A -> Letter and numerals
	// N -> Numerals
	// Z -> Letters
	//
	
	private $segments;
	private $id = "";
	
	public function __construct( callable $action, string $template ) {
		
		parent::__construct($action);
		
		$this->segments = self::parse($template);
		
		foreach ( $this->segments as $seg ) {
			
			$this->id .= "/{$seg["s"]}";
			
			if ( isset( $seg["type"] ) ) {
				$this->id .= "{" . $seg["type"] . "," . $seg["idx"] . "}";
			}
			
			$this->id .= "{$seg["e"]}";
			
		}
		
	}
	
	public function identifier() : string {
		return $this->id;
	}

	protected function routable(string $path, string $bpath) : ?array {
		
		$ps = explode('/', $bpath);
		
		if ( count($ps) !== count($this->segments) ) {
			return null;
		}
		
		$seg = null;
		$part = null;
		
		$vars = [];
		
		for ( $i = 0; $i < count($ps); $i++ ) {
			
			$seg = $this->segments[$i];
			$part = $ps[$i];
			
			$pl = count($part);
			$sl = count($seg["s"]);
			$el = count($seg["e"]);
			
			if ( isset( $seg["type"] ) ) {
				
				if ( $pl < ( $sl + $el + 1 ) ) {
					return null;
				}
				
				$sp = substr($part, 0, $sl);
				$ep = substr($part, -$el);
				
				if ( $seg["s"] !== $sp || $seg["e"] !== ep ) {
					return null;
				}
				
				$c = substr($part, $sl, $pl - $el - $sl);
				
				switch ( $seg["type"] ) {
					case "z":
						
						if ( !preg_match('/[a-zA-Z]/') ) {
							return null;
						}
						
						$vars[ $seg["idx"] ] = $c;
						break;
						
					case "a":
						
						if ( !preg_match('/[a-zA-Z0-9]/') ) {
							return null;
						}
						
						$vars[ $seg["idx"] ] = $c;
						break;
						
					case "n":
						
						if ( !is_numeric($c) ) {
							return null;
						}
						
						$vars[ $set["idx"] ] = intval($c);
						break;
						
				}
				
			} else {
				
				if ( $seg["s"] !== $part ) {
					return null;
				}
				
			}
			
		}
		
	}
	
	public static function parse( string $template ) : array {
		
		$parts = [];
		$ps = explode('/', $template);
		
		$last_seg_idx = null;
		
		foreach ( $ps as $d ) {
			
			if ( !empty($d) ) {
				
				$i1 = -1;
				
				// Allow escaping.
				do {
					$i1 = strpos( $d, '{', $i1 + 1 );
				} while ( $i1 !== 0 && $d[ $i1 - 1 ] == '\\' );
				
				if ( $i1 === false ) {
					
					$parts[] = [
						"type" => null,
						"idx" => null,
						"s" => $d,
						"e" => ""
					];
					
				} else {
					
					$i2 = strpos( $d, '}', $i1 + 1 );
					
					if ( $i2 === false ) {
						throw new InvalidArgumentException("Invalid template segment, must be closed with another '}'.");
					}
					
					$l = $i2 - $i1 - 1;
					
					if ( $l < 1 ) {
						throw new InvalidArgumentException("Invalid template segment, must contains at least the type");
					}
					
					$i = substr( $d, $i1 + 1, $l );
					
					$t = strtolower($i[0]);
					
					if ( !in_array( $t, self::VALID_SEGMENT_TYPES) ) {
						throw new InvalidArgumentException("Invalid template segment type '{$t}'.");
					}
					
					$n = substr($i, 1);
					
					if ( empty($n) ) {
						
						if ( $last_seg_idx === null ) {
							$last_seg_idx = 0;
						} else {
							$last_seg_idx++;
						}
						
					} else {
						$last_seg_idx = intval($n, 10);
					}
					
					$parts[] = [
						"type" => $t,
						"idx" => $last_seg_idx,
						"s" => substr( $d, 0, $i1 ),
						"e" => substr( $d, $i2 + 1 )
					];
					
				}
				
			}
			
		}
		
		return $parts;
		
	}
	
}

?>