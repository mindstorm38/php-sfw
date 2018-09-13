<?php

// Used to manage SQL table

namespace PHPHelper\src;

use PHPHelper\src\SQLSerializable;
use \PDO;

abstract class SQLManager {

	const UID = "uid";

	protected $_db;

	public function __construct( $db ) {
		$this->set_db( $db );
	}

	public function set_db( PDO $db ) {
		$this->_db = $db;
	}

	public function get_db() {
		return $this->_db;
	}

	public abstract function get_table();
	public abstract function get_class();
	public abstract function get_vars();
	public abstract function is_uid_predefined();
	public abstract function get_table_content(); // TABLE CREATION IF NOT EXISTS

	public function vars() {
		$vars = $this->get_vars();
		$uidvar = SQLManager::UID;
		$vars[ $uidvar ] = [
				'get' => 'get_uid',
				'set' => 'set_uid',
				'pdo_type' => PDO::PARAM_INT
		];
		return $vars;
	}
	/*

	SAMPLE :

	return [
		'uid' => [
			'get' => 'uid',
			'set' => 'setUid',
			'pdo_type' => PDO::PARAM_INT,
			'encrypt' => false
		]
	];

	*/

	public function get_list() {

		$list = [];

		$cls = $this->get_class();
		$vars = $this->vars();

		$select = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $start === false ) $select .= ','; else $start = false;
			$select .= $var;
		}

		$q = $this->_db->query("SELECT {$select} FROM {$this->get_table()}");

		while ( $data = $q->fetch( PDO::FETCH_ASSOC ) ) {

			$newobj = new $cls();

			foreach ( $data as $key => $value ) {
				if ( $vars[ $key ] !== null ) {
					if ( isset( $vars[ $key ]['set'] ) ) {
						$set = $vars[ $key ]['set'];
						if ( method_exists( $newobj, $set ) ) {
							$encrypt = isset( $vars[ $key ]['encrypt'] ) ? $vars[ $key ]['encrypt'] : false;
							$newobj->$set( $encrypt === false ? $value : htmlspecialchars_decode( $value, ENT_QUOTES | ENT_HTML5 ) );
						}
					}
				}
			}

			$list[] = $newobj;

		}

		$q->closeCursor();

		return $list;

	}

	public function get( $id ) {

		$id = (int) $id;

		$cls = $this->get_class();
		$vars = $this->vars();
		$uidvar = SQLManager::UID;

		$select = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $start === false ) $select .= ','; else $start = false;
			$select .= $var;
		}

		$q = $this->_db->query("SELECT {$select} FROM {$this->get_table()} WHERE {$uidvar} = {$id}");

		$data = $q->fetch( PDO::FETCH_ASSOC );

		$q->closeCursor();

		if ( $data === false ) {
			return null;
		}

		$newobj = new $cls();

		foreach ( $data as $key => $value ) {
			if ( $vars[ $key ] !== null ) {
				if ( isset( $vars[ $key ]['set'] ) ) {
					$set = $vars[ $key ]['set'];
					if ( method_exists( $newobj, $set ) ) {
						$encrypt = isset( $vars[ $key ]['encrypt'] ) ? $vars[ $key ]['encrypt'] : false;
						$newobj->$set( $encrypt === false ? $value : htmlspecialchars_decode( $value, ENT_QUOTES | ENT_HTML5 ) );
					}
				}
			}
		}

		return $newobj;

	}

	public function update( SQLSerializable $obj ) {

		$vars = $this->vars();
		$uidvar = SQLManager::UID;

		$update = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $start === false ) $update .= ','; else $start = false;
			$update .= $var . '=:' . $var;
		}

		$q = $this->_db->prepare("UPDATE {$this->get_table()} SET {$update} WHERE {$uidvar} = :{$uidvar}");

		foreach ( $vars as $var => $vari ) {
			if ( $vari['get'] !== null ) {
				$get = $vari['get'];
				if ( method_exists( $obj, $get ) ) {

					$paramType = null;
					if ( $vari['pdo_type'] !== null ) $paramType = $vari['pdo_type'];

					$encrypt = isset( $vari['encrypt'] ) ? $vari['encrypt'] : false;

					if ( $obj->$get() === null ) {
						$i = null;
						$q->bindValue(":{$var}", $i, PDO::PARAM_NULL);
					} else if ( $paramType !== null ) {
						$q->bindValue(":{$var}", $encrypt === false ? $obj->$get() : htmlspecialchars( $obj->$get(), ENT_QUOTES | ENT_HTML5 ), $paramType );
					} else {
						$q->bindValue(":{$var}", $encrypt === false ? $obj->$get() : htmlspecialchars( $obj->$get(), ENT_QUOTES | ENT_HTML5 ) );
					}

				}
			}
		}

		$r = $q->execute();

	}

	public function delete( SQLSerializable $obj ) {

		$uidvar = SQLManager::UID;

		$this->_db->exec("DELETE FROM {$this->get_table()} WHERE {$uidvar} = {$obj->get_uid()}");

	}

	public function add( SQLSerializable $obj ) {

		$vars = $this->vars();
		$uidvar = SQLManager::UID;
		$uidpred = $this->is_uid_predefined();

		$insertVars = "";
		$insertVals = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $uidpred === false && $var == $uidvar ) continue;
			if ( $start === false ) { $insertVars .= ','; $insertVals .= ','; } else $start = false;
			$insertVars .= $var;
			$insertVals .= ':' . $var;
		}

		$q = $this->_db->prepare("INSERT INTO {$this->get_table()}({$insertVars}) VALUES({$insertVals})");

		foreach ( $vars as $var => $vari ) {
			if ( $uidpred === false && $var == $uidvar ) continue;
			if ( $vari['get'] !== null ) {
				$get = $vari['get'];
				if ( method_exists( $obj, $get ) ) {

					$paramType = null;
					if ( $vari['pdo_type'] !== null ) $paramType = $vari['pdo_type'];

					$encrypt = isset( $vari['encrypt'] ) ? $vari['encrypt'] : false;

					if ( $obj->$get() === null ) {
						$i = null;
						$q->bindValue(":{$var}", $i, PDO::PARAM_NULL);
					} else if ($paramType !== null) {
						$q->bindValue(":{$var}", $encrypt === false ? $obj->$get() : htmlspecialchars( $obj->$get(), ENT_QUOTES | ENT_HTML5 ), $paramType );
					} else {
						$q->bindValue(":{$var}", $encrypt === false ? $obj->$get() : htmlspecialchars( $obj->$get(), ENT_QUOTES | ENT_HTML5 ) );
					}

				}
			}
		}

		$q->execute();

		if ( $uidpred === false ) {

			$last = $this->_db->lastInsertId();
			$obj->set_uid( $last );

			return $last;

		} else {
			return $obj->get_uid();
		}

	}

	public function gett( $wherefunction ) {

		$cls = $this->get_class();
		$vars = $this->vars();
		$uidvar = SQLManager::UID;

		$select = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $start === false ) $select .= ','; else $start = false;
			$select .= $var;
		}

		$q = $this->_db->query("SELECT {$select} FROM {$this->get_table()} WHERE {$wherefunction}");

		$data = $q->fetch( PDO::FETCH_ASSOC );

		$q->closeCursor();

		if ( $data === false ) {
			return null;
		}

		$newobj = new $cls();

		foreach ( $data as $key => $value ) {
			if ( $vars[ $key ] !== null ) {
				if ( isset( $vars[ $key ]['set'] ) ) {
					$set = $vars[ $key ]['set'];
					if ( method_exists( $newobj, $set ) ) {
						$encrypt = isset( $vars[ $key ]['encrypt']) ? $vars[ $key ]['encrypt'] : false;
						$newobj->$set( $encrypt === false ? $value : htmlspecialchars_decode( $value, ENT_QUOTES | ENT_HTML5 ) );
					}
				}
			}
		}

		return $newobj;

	}

	public function get_listt($wherefunction) {

		$list = [];

		$cls = $this->get_class();
		$vars = $this->vars();

		$select = "";
		$start = true;
		foreach ( $vars as $var => $vari ) {
			if ( $start === false ) $select .= ','; else $start = false;
			$select .= $var;
		}

		$q = $this->_db->query("SELECT {$select} FROM {$this->get_table()} WHERE {$wherefunction}");

		while ( $data = $q->fetch( PDO::FETCH_ASSOC ) ) {

			$newobj = new $cls();

			foreach ( $data as $key => $value ) {
				if ( $vars[ $key ] !== null ) {
					if ( isset( $vars[ $key ]['set'] ) ) {
						$set = $vars[ $key ]['set'];
						if ( method_exists( $newobj, $set ) ) {
							$encrypt = isset( $vars[ $key ]['encrypt']) ? $vars[ $key ]['encrypt'] : false;
							$newobj->$set( $encrypt === false ? $value : htmlspecialchars_decode( $value, ENT_QUOTES | ENT_HTML5 ) );
						}
					}
				}
			}

			$list[] = $newobj;

		}

		$q->closeCursor();

		return $list;

	}

}

?>
