<?php

/**
 * Underscore Php Framework
 * ------------------------
 *  
 * @author Thomas Portelange <thomas@lekoala.be>
 * @version 0.001
 * @licence THE BEER-WARE LICENSE rev.42 (see licence.txt)
 */

/**
 * Orm class
 */
class _orm {

	/**
	 * Create a class (can proxy load method)
	 * 
	 * @param mixed $id
	 * @param array $params (optional)
	 */
	function __construct($id = null, $params = array()) {
		if ($id == null) {
			return $this;
		}

		$this->load($id, $params);
	}

	/**
	 * Return the PDO instance
	 * @return PDO
	 */
	static function get_pdo() {
		$pdo = _::$pdo;
		if ($pdo === null) {
			throw Exception('You must define _::$pdo to use the orm');
		}
		return $pdo;
	}

	/**
	 * Get the table name, override in subclass if necessary
	 * @return string
	 */
	static function get_table() {
		return strtolower(get_called_class());
	}

	/**
	 * Get the primary key field, override in subclass if necessary
	 * @return string
	 */
	static function get_primary_key() {
		return 'id';
	}

	/**
	 * Get only public properties (call from external scope)
	 * @param object $obj
	 * @return array
	 */
	static function get_public_properties($obj) {
		$get_fields = function($obj) {
					return get_object_vars($obj);
				};
		return $get_fields($obj);
	}
	
	/* table methods */
	
	/**
	 * Transform an array of conditions to a sql where part
	 * @param array $where
	 * @param array $params
	 * @return string 
	 */
	protected static function combine_where(array $where, array &$params = array()) {
		array_walk($where, function (&$item, $key) use (&$params) {
			$params[':' . $key] = $item;
			$item = $key . " = :" . $key;
		});
		return implode(' AND ', $where);
	}
	
	/**
	 * Find records according to where
	 * @param string|array $where (optional)
	 * @param array $params (optional)
	 * @return The result of the pdo execute statement 
	 */
	static function find($where = null, $params = array()) {
		$pdo = self::get_pdo();
		$table = self::get_table();
		
		$sql = 'SELECT * FROM ' . $table;
		if($where !== null) {
			if(is_array($where)) {
				$where = self::combine_where($where, $params);
			}
			$sql .= ' WHERE ' . $where;
		}
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		$results = $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
				
		return $results;
	}
	
	/**
	 * Like find, but count the records instead (seems like rowCount of pdo doesn't work too well)
	 * @param string $where (optional)
	 * @param array $params (optional)
	 * @return int The number of rows
	 */
	static function count($where = null, $params = array()) {
		$pdo = self::get_pdo();
		$table = self::get_table();
		
		$sql = 'SELECT COUNT(*) FROM ' . $table;
		if($where !== null) {
			if(is_array($where)) {
				$where = self::combine_where($where,$params);
			}
			$sql .= ' WHERE ' . $where;
		}
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		$results = $stmt->fetchColumn();
						
		return (int)$results;
	}
	
	/**
	 * Scaffold a create statement
	 * @param bool $execute
	 * @return type 
	 */
	static function create_table($execute = true) {
		$pdo = self::get_pdo();
		$table = self::get_table();
		$primary_key = self::get_primary_key();
		
		$properties = get_class_vars(get_called_class());
		
		//all tables
		$mysql = 'SHOW FULL TABLES';
		$postgres = 'SELECT * FROM pg_tables';
		$mssql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
		$sqlite = "SELECT * FROM sqlite_master WHERE type='table'";
		
		$all_table_sql = compact('mysql','postgres','mssql','sqlite');
		$current ;
		$table_list ;
		
		foreach($all_table_sql as $k => $v) {
			$result = $pdo->query($v);
			if($result != false) {
				$current = $k;
				$table_list = $result->fetchAll();
			}
		}
		
		$sql = 'CREATE TABLE ' . $table . "(\n";
		foreach($properties as $prop => $value) {
			$type = 'VARCHAR(255)';
			if(strpos($prop, 'id') !== false) {
				if($current == 'sqlite') {
					$type = 'INTEGER PRIMARY KEY AUTOINCREMENT';
				}
				else {
					$type = 'INT AUTO_INCREMENT';
				}
			}
			if(strpos($prop, '_id') !== false) {
				$type = 'INT';
			}
			if(strpos($prop, 'is_') === 0) {
				$type = 'TINYINT';
			}
			if(strpos($prop,'_at') !== false) {
				$type = 'DATETIME';
			}
			if(strpos($prop,'_date') !== false) {
				$type = 'DATE';
			}
			if(strpos($prop,'_html') !== false) {
				$type = 'TEXT';
			}
			if($prop == 'content') {
				$type = 'TEXT';
			}
			$sql .= "\t" . $prop . ' ' . $type . ",\n";
		}
		
		//primary key
		if($current != 'sqlite') {
			$sql .= ',PRIMARY KEY ('.$primary_key.')';
		}
		else {
			$sql = rtrim($sql, ",\n");
		}
		
		$sql .= "\n)";
		
		if($execute) {
			return $pdo->exec($sql);		
		}
		
		return $sql;
	}

	/* orm methods */

	/**
	 * Save the record
	 * @return The result of the pdo execute statement
	 */
	function save() {
		$pdo = self::get_pdo();
		$table = self::get_table();
		$primary_key = self::get_primary_key();

		$properties = self::get_public_properties($this);
		$params = array();

		//create the record
		if (!isset($this->$primary_key)) {
			foreach ($properties as $k => $v) {
				if (empty($v)) {
					$v = null;
				}

				$keys[] = $k;
				$values[] = ':' . $k;
				$params[':' . $k] = $v;
			}

			$sql = "INSERT INTO " . $table . "(" . implode(",", $keys) . ") VALUES (" . implode(',', $values) . ")";
		}
		//update the record
		else {
			$sql = "UPDATE " . $table . " SET ";
			$params[':' . $primary_key] = $this->$primary_key;

			foreach ($properties as $k => $v) {
				if (empty($v)) {
					$v = null;
				}

				if ($k != $primary_key) {
					$sql .= $k . ' = :' . $k . ', ';
				}
				$params[':' . $k] = $v;
			}
			$sql = rtrim($sql, ', ');
			$sql .= ' WHERE ' . $primary_key . ' = :' . $primary_key;
		}

		$this->before_save($sql, $params);
		
		$stmt = $pdo->prepare($sql);
		$result = $stmt->execute($params);
		
		$this->after_save($sql, $params, $result);
			
		return $result;
	}
	
	/**
	 * Called before executing save sql. Parameters are passed by reference
	 * for potential modifications
	 * @param string $sql
	 * @param array $params 
	 */
	protected function before_save(&$sql, &$params) {
		//implement in subclass
	}
	
	/**
	 * Called after executing save sql
	 * @param string $sql
	 * @param array $params 
	 * @param bool $result 
	 */
	protected function after_save($sql, $params, $result) {
		//implement in subclass
	}

	/**
	 * Delete the record
	 * @return The result of the pdo execute statement
	 */
	function delete() {
		$pdo = self::get_pdo();
		$table = self::get_table();
		$primary_key = self::get_primary_key();

		//if the record hasnt been saved yet
		if (!isset($this->$primary_key)) {
			return false;
		}

		$sql = 'DELETE FROM ' . $table . ' WHERE ' . $primary_key . ' = :' . $primary_key;

		$stmt = $pdo->prepare($sql);
		$result = $stmt->execute(array(':' . $primary_key => $this->$primary_key));
		return $result;
	}

	/**
	 * Load record into class
	 * @param mixed $id Numeric id or custom sql where clause
	 * @param array $params (optional) Pdo bound params if id is not numeric
	 */
	function load($id, $params = array()) {
		$pdo = self::get_pdo();
		$table = self::get_table();
		$primary_key = self::get_primary_key();

		$sql = 'SELECT * FROM ' . $table . ' WHERE ';
		if (is_numeric($id)) {
			$sql .= $primary_key . ' = :' . $primary_key;
			$params[':' . $primary_key] = $id;
		} else {
			if(is_array($id)) {
				$id = self::combine_where($id, $params);
			}
			$sql .= $id;
		}
		$stmt = $pdo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_INTO, $this);
		$stmt->execute($params);
		$stmt->fetch();
	}
	
	/**
	 * Provide a simple html representation of the record
	 * @return string 
	 */
	function __toString() {
		$fields = self::get_public_properties($this);
		
		$html = '<div class="' . get_called_class() . '">';
		foreach ($fields as $key => $value) {
			$html .= "\n" . '<div class="'.$key.'">'.$value.'</div>';
		}
		$html .= "\n</div>";
		return $html;
	}
}