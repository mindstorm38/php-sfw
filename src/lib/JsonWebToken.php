<?php

namespace SFW;

use \Exception;

class JsonWebToken {
	
	const DEFAULT_SECRET = "";
	
	private static function throw_not_configured(string $key) {
		throw new Exception("You must specify JWT '{$key}' in config before using JWT !");
	}
	
	public static function get_secret() : string {
		
		$secret = Config::get("jwt:secret");
		
		if ( $secret === null ) {
			self::throw_not_configured("secret");
		}
		
		return $secret;
		
	}
	
	public static function get_algorithm() : string {
		
		$algo = Config::get("jwt:algo");
		
		if ( $algo === null ) {
			self::throw_not_configured("algo");
		}
		
		$algo = strtoupper($algo);
		
		return $algo;
		
	}
	
	private static function execute_algo(string $algo, string $content, string $secret) {
		
		$func_name = "algo_{$algo}";
		
		if ( !method_exists(__CLASS__, $func_name) ) {
			throw new Exception("Algorithm '{$algo}' not implemented.");
		}
		
		return forward_static_call($func_name, $content, $secret);
		
	}
	
	public static function algo_HS256(string $content, string $secret) {
		return hash_hmac("sha256", $content, $secret);
	}
	
	public static function sign(array $payload) : string {
		
		$secret = self::get_secret();
		$algo = self::get_algorithm();
		
		$header = [,
			"typ" => "JWT",
			"alg" => $algo
		];
		
		$content = self::base64url_encode(json_encode($header)) . "." . self::base64url_encode(json_encode($payload));
		$signature = self::execute_algo($algo, $content, $secret);
		
		return $content . "." . self::base64url_encode($signature);
		
	}
	
	public static function verify(string $token, string $secret) : array {
		
		$splitted = explode(".", $token);
		
		if ( count($splitted) !== 3 ) {
			throw new Exception("Invalid token format.");
		}
		
		$header = json_decode($splitted[0], true);
		
		if ( !isset($header["typ"]) || $header["typ"] !== "JWT" ) {
			throw new Exception("Invalid or missing token type, supported types : JWT.");
		}
		
		if ( !isset($header["alg"]) ) {
			throw new Exception("Missing token algorithm.");
		}
		
		$algo = $header["alg"];
		$signature = self::execute_algo($algo, ($splitted[0] . "." . $splitted[1]), $secret);
		
		if ( self::base64url_encode($signature) !== $splitted[2] ) {
			throw new Exception("Unverified token.");
		}
		
		return json_decode($splitted[1], true);
		
	}
	
	public static function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	
	public static function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	} 
	
}

?>