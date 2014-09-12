<?php

/**
 * Ndrscr php toolkit
 * ------------------------
 *  
 * @author Thomas Portelange <thomas@lekoala.be>
 * @version 0.1
 * @licence http://www.opensource.org/licenses/MIT
 */

/**
 * Smart query builder 
 * 
 * Inspiration :
 * @link https://github.com/lichtner/fluentpdo/blob/master/FluentPDO.php
 */
class _query {
	/**
	 * Pdo instance to use
	 * @var _pdo
	 */
	public static $pdo;
	
	/**
	 * From table
	 * @var string
	 */
	protected $from;

	/**
	 * Store aliases as alias => table
	 * @var array 
	 */
	protected $aliases = array();

	/**
	 * Where clauses
	 * @var array
	 */
	protected $where = array();

	/**
	 * Having clauses
	 * @var array
	 */
	protected $having = array();

	/**
	 * Joins like "type" => , "table" =>, "predicate" =>
	 * @var array 
	 */
	protected $joins = array();
	
	/**
	 * Limit clause
	 * @var string
	 */
	protected $limit;

	/**
	 * Order by clauses
	 * @var array
	 */
	protected $order_by = array();

	/**
	 * Group by clauses
	 * @var array
	 */
	protected $group_by = array();

	/**
	 * Field selection
	 * @var array
	 */
	protected $fields = array();
	
	/**
	 * Empty or null fields
	 * @var array
	 */
	protected $empty_or_null = array();

	/**
	 * Is distinct
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * Custom options
	 * @var string
	 */
	protected $options;

	/**
	 * Should we add clauses or use or
	 * @var bool
	 */
	protected $or = false;
	
	/**
	 * Fetch as class
	 * @var string
	 */
	protected $fetch_class = null;
	
	/**
	 * Use sql cache or not
	 * @var bool
	 */
	protected $no_cache = false;

	/**
	 * Params for prepared statement
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * Create a new _query and allow passing directly the from
	 * 
	 * @param string $from 
	 */
	function __construct($from = null) {
		if ($from !== null) {
			$this->from($from);
		}
	}

	/**
	 * Reset all options
	 * 
	 * @return _query
	 */
	function reset() {
		$this->from = null;
		$this->aliases = array();
		$this->where = array();
		$this->having = array();
		$this->joins = array();
		$this->limit = null;
		$this->order_by = array();
		$this->group_by = array();
		$this->fields = array();
		$this->empty_or_null = array();
		$this->distinct = false;
		$this->or = false;
		$this->fetch_class = null;
		$this->no_cache = false;
		$this->params = array();
		return $this;
	}

	/**
	 * Use OR instand of AND when assembling clauses
	 * 
	 * @param bool $flag 
	 * @return _query 
	 */
	function use_or($flag = true) {
		$this->or = $flag;
		return $this;
	}
	
	/**
	 * Fetch as class
	 * 
	 * @param string $class 
	 * @return _query
	 */
	function fetch_as($class = null) {
		$this->fetch_class = $class;
		return $this;
	}

	/**
	 * Add distinct option
	 * 
	 * @param string|array $fields (optional) shortcut for fields()
	 * @return _query 
	 */
	function distinct($fields = null) {
		if($fields !== null) {
			$this->fields($fields);
		}
		$this->distinct = true;
		return $this;
	}
	
	/**
	 * Disable cache
	 * 
	 * @return _query
	 */
	function no_cache() {
		$this->no_cache = true;
		return $this;
	}
	
	/**
	 * Select from table
	 * 
	 * @param string|array $table
	 * @param string $alias
	 * @return _query 
	 */
	function from($table, $alias = null) {
		if(is_array($table)) {
			$alias = $table[1];
			$table = $table[0];
		}
		if($alias !== null) {
			if(!in_array($alias, array_values($this->aliases))) {
				$this->aliases[$alias] = $table;
			}
		}
		$this->from = $table;
		return $this;
	}

	/**
	 * Specify fields
	 * 
	 * @param string|array $fields 
	 * @return _query
	 */
	function fields($fields) {
		if (!is_array($fields)) {
			$fields = explode(',', $fields);
			array_walk($fields, 'trim');
		}
		$this->fields = $fields;
		return $this;
	}
	
	/**
	 * Set the fields tht should be selected as nullif(field,'') as field
	 * @param string,array $fields
	 * @return _query 
	 */
	function empty_or_null($fields) {
		if (!is_array($fields)) {
			$fields = explode(',', $fields);
			array_walk($fields, 'trim');
		}
		$this->empty_or_null = $fields;
		return $this;
	}

	/**
	 * Add a field
	 * 
	 * @param string $field 
	 */
	function add_field($field) {
		$this->fields[] = $field;
	}
	
	/**
	 * Add where conditions based on filter array
	 * 
	 * @param array $filters
	 * @param string $key
	 * @param string $sprintv
	 * @param string $sprintk
	 * @param string $operator
	 */
	function filter($filters,$key,$sprintv = null,$sprintk = null,$operator = null) {
		if(!empty($filters[$key])) {
			$value = $filters[$key];
			
			$operators = array('>','<','=');
			foreach($operators as $op) {
				if(strpos($value, $op) === 0) {
					$operator = substr($value,0,1);
					$value = ltrim($value,$operator);
				} 
			}
 			
			if($sprintv) {
				$value = sprintf($sprintv,$value);
			}
			if($sprintk) {
				$key = sprintf($sprintk,$key);
			}
			$this->where($key,$value,$operator);
		}
	}

	/**
	 * Add a where clause
	 * 
	 * @param string $key
	 * @param mixed $value (optional)
	 * @param string $operator (optional)
	 * @return _query 
	 */
	function where($key, $value = '', $operator = null) {
		if ($key === null) {
			$this->where = array();
			return $this;
		}
		$pdo = $this->get_pdo();
		
		//custom sql where clause
		if($value === '') {
			$this->where[] = $key;
			return $this;
		}
		
		$key = $this->detect_external_key($key);
		$quoted_value = $pdo->quote($value);

		//placeholders
		if (strpos($key, '?') !== false) {
			$key = str_replace('?', $quoted_value, $key);
//			$key = str_replace('?', $this->replace_by_placeholder($value), $key);
			$this->where[] = $key;
			return $this;
		}
		if (!$operator) {
			if ($value === null) {
				$this->where[] = $key . ' IS NULL';
			} elseif (is_array($value)) {
				$this->where[] = $key . ' IN (' . $quoted_value . ')';
			} elseif (strpos($value, '%') !== false) {
				$this->where[] = $key . ' LIKE ' . $quoted_value;
//				$this->where[] = $key . ' LIKE ' . $this->replace_by_placeholder($value);
			} else {
				$this->where[] = $key . ' = ' . $quoted_value;
//				$this->where[] = $key . ' = ' . $this->replace_by_placeholder($value);
			}
		} else {
			if ($operator == 'BETWEEN') {
				$this->where[] = $key . ' BETWEEN ' . $pdo->quote($value[0]) . ' AND ' . $pdo->quote($value[1]);
			} else {
				if (strpos($operator, 'IN') !== false) {
					$this->where[] = $key . ' ' . $operator . ' (' . $pdo->quote($value) . ')';
				} else {
					$this->where[] = $key . ' ' . $operator . ' ' . $quoted_value;
//					$this->where[] = $key . ' ' . $operator . ' ' . $this->replace_by_placeholder($value);
				}
			}
		}
		return $this;
	}
	
	/**
	 * Get placeholder
	 * @param string $value
	 * @return string
	 */
	protected function replace_by_placeholder($value) {
		$placeholder = ':p' . count($this->params);
		$this->params[$placeholder] = $value;
		return $placeholder;
	}
	
	/**
	 * Where not
	 * 
	 * @param string $key
	 * @param string $value
	 * @return _query 
	 */
	function where_not($key, $value) {
		if(is_array($value)) {
			return $this->where($key,$value,'NOT IN');
		}
		return $this->where($key,$value,'!=');
	}
	
	/**
	 * Where greater than
	 * 
	 * @param string $key
	 * @param string $value
	 * @return _query 
	 */
	function where_gt($key,$value) {
		return $this->where($key,$value,'>');
	}
	
	/**
	 * Where greater than or equal
	 * 
	 * @param string $key
	 * @param string $value
	 * @return _query 
	 */
	function where_gte($key,$value) {
		return $this->where($key,$value,'>=');
	}
	
	/**
	 * Where lower than
	 * 
	 * @param string $key
	 * @param string $value
	 * @return _query 
	 */
	function where_lt($key,$value) {
		return $this->where($key,$value,'<');
	}
	
	/**
	 * Where lower than or equal
	 * 
	 * @param string $key
	 * @param string $value
	 * @return _query 
	 */
	function where_lte($key,$value) {
		return $this->where($key,$value,'<=');
	}
	
	/**
	 * Where between
	 * 
	 * @param string $key
	 * @param array $values 
	 * @return _query
	 */
	function where_between($key,$values) {
		return $this->where($key,$values,'BETWEEN');
	}
	
	/**
	 * Where not null
	 * 
	 * @param string $key
	 * @param bool $also_blanks
	 * @return _query
	 */
	function where_not_null($key, $also_blanks = true) {
		$where = $key . ' IS NOT NULL';
		if($also_blanks) {
			$where .= ' OR ' . $key . " != ''";
		}
		return $this->where($where);
	}
	
	/**
	 * Where null
	 * 
	 * @param string $key
	 * @param bool $also_blanks
	 * @return _query 
	 */
	function where_null($key, $also_blanks = true) {
		$where = $key . ' IS NULL';
		if($also_blanks) {
			$where .= ' OR ' . $key . " = ''";
		}
		return $this->where($where);
	}
	
	/**
	 * Add a having clause
	 * 
	 * @param type $columns
	 * @return _query 
	 */
	function having($columns) {
		if ($columns === null) {
			$this->having = array();
			return $this;
		}
		$columns = $this->detect_external_key($columns);
		$this->having[] = $columns;
		return $this;
	}

	/**
	 * Add an order by clause
	 * 
	 * @param type $columns
	 * @return _query 
	 */
	function order_by($columns) {
		if (is_array($columns)) {
			$columns = $columns[0] . ' ' . $columns[1];
		}
		$columns = $this->detect_external_key($columns);
		$this->order_by[] = $columns;
		return $this;
	}

	/**
	 * Add a group by clause
	 * 
	 * @param string $columns
	 * @return _query 
	 */
	function group_by($columns) {
		if ($columns === null) {
			$this->group_by = array();
			return $this;
		}
		$this->group_by[] = $columns;
		return $this;
	}

	/**
	 * Limit clause
	 * 
	 * @param type $value
	 * @return _query 
	 */
	function limit($value) {
		if (is_array($value)) {
			$value = $value[0] . ' ' . $value[1];
		}
		$this->limit = $value;
		return $this;
	}

	/**
	 * Inner join shortcut
	 * 
	 * @param string|array $table
	 * @param string $predicate
	 * @return _query 
	 */
	function inner_join($table, $predicate = null) {
		return $this->join($table, $predicate, 'inner');
	}

	/**
	 * Left join shortcut
	 * 
	 * @param string|array $table
	 * @param string $predicate
	 * @return _query 
	 */
	function left_join($table, $predicate = null) {
		return $this->join($table, $predicate, 'left');
	}

	/**
	 * Right join shortcut
	 * 
	 * @param string|array $table
	 * @param string $predicate
	 * @return _query 
	 */
	function right_join($table, $predicate = null) {
		return $this->join($table, $predicate, 'right');
	}

	/**
	 * Full join shortcut
	 * 
	 * @param string|array $table
	 * @param string $predicate
	 * @return _query 
	 */
	function full_join($table, $predicate = null) {
		return $this->join($table, $predicate, 'full');
	}

	/**
	 * Join clause
	 * 
	 * @param string|array $table
	 * @param string $predicate
	 * @param string $type
	 * @param boolean $force
	 * @return _query 
	 */
	function join($table, $predicate = null, $type = 'inner', $force = false) {
		if(empty($this->from)) {
			throw new Exception('You must define a base table before joining');
		}
		$type = strtoupper($type);
		if (!in_array($type, array('INNER', 'LEFT', 'RIGHT', 'FULL'))) {
			throw new Exception('Unsupported join type : ' . $type);
		}
		
		$alias = '';
		//if we pass an table,alias combo
		if(is_array($table)) {
			$alias = $table[1];
			$table = $table[0];
		}
		//if we pass only an alias (mainly used in detect key method)
		if(isset($this->aliases[$table])) {
			$table = $this->aliases[$table];
		}
		//look for an alias
		if(empty($alias)) {
			foreach($this->aliases as $key_alias => $value_table) {
				if($value_table == $table) {
					$alias = $key_alias;
				}
			}
		}
		$table_or_alias = $table;
		if(!empty($alias)) {
			$table_or_alias = $alias;
		}
		if(!$force) {
			foreach($this->joins as $join) {
				if($join['table'] == $table) {
					return;
				}
			}
		}
		
		//store alias
		if(!isset($this->aliases[$alias])) {
			$this->aliases[$alias] = $table;
		}
		//autogenerate
		if ($predicate === null) {
			$default_pk = $foreign_pk = 'id';
			$pk = $table . '_' . $default_pk;
			
			//if using _orm, we can detect primary keys
			if (class_exists($table) && is_subclass_of($table, '_orm')) {
				$pk_fields = $table::get_pk();
				$foreign_pk = $pk_fields[0];
				//look for a key related to the table
				if(count($pk_fields) > 1) {
					foreach($pk_fields as $pk_field) {
						if(strpos($pk_field,$this->from) !== false) {
							$foreign_pk = $pk_field;
						}
					}
				}
			}
			if (class_exists($this->from) && is_subclass_of($this->from, '_orm')) {
				$from_table = $this->from;
				$pk_fields = $from_table::get_pk();
				$pk_field = $pk_fields[0];
				//look for a key related to the table
				if(count($pk_fields) > 1) {
					foreach($pk_fields as $pk_field) {
						if(strpos($pk_field,$table) !== false) {
							$pk_field = $pk_field;
						}
					}
				}
				$pk = $table . '_' . $pk_field;
				$pk = $table . '_' . $foreign_pk;
			}
			
			$predicate = $this->table_or_alias() . '.' . $pk . ' = ' . $table_or_alias . '.' . $foreign_pk;
		}

		if (strpos($predicate, '=') !== false) {
			$predicate = 'ON ' . $predicate;
		} else {
			$predicate = 'USING ' . $predicate;
		}
		$table_as = $table;
		$this->joins[] = array('type' => $type, 'table' => $table, 'predicate' => $predicate);
		return $this;
	}

	/**
	 * Build the query
	 * 
	 * @return string
	 */
	function build() {
		if (empty($this->from)) {
			throw new Exception('You must set a table before building the statement');
		}

		$pdo = $this->get_pdo();
		$table_as = $this->from;
		$alias = $this->get_alias($table_as);
		if($alias) {
			$table_as .= ' AS ' . $alias;
		}
		
		//select
		$where_join = ' AND ';
		if ($this->or) {
			$where_join = ' OR ';
		}

		$options = $this->options;
		if (strpos($options, 'DISTINCT') === false && $this->distinct) {
			$options .= ' DISTINCT';
		}

		$sql = 'SELECT';
		if (!empty($options)) {
			$sql .= ' ' . trim($options);
		}
		if($this->no_cache) {
			$sql .= ' SQL_NO_CACHE';
		}
		
		//fields
		$fields = $this->fields;
		if(empty($fields)) {
			$fields = '*';
		}
		else {
			if(is_array($fields)) {
				$fields = implode(',', $fields);
			}
		}
		foreach($this->empty_or_null as $k => $v) {
			$fields = str_replace($k, "nullif($k,'') AS $k", $fields);
		}
		
		$sql .= ' ' . $fields . ' FROM ' . $table_as;

		if (!empty($this->joins)) {
			foreach ($this->joins as $join) {
				$alias = $this->get_alias($join['table']);
				$table = $join['table'];
				if($alias) {
					$table .= ' AS ' . $alias;
				}
				$sql .= ' ' . $join['type'] . ' JOIN ' . $table . ' ' . $join['predicate'];
			}
		}
		if (!empty($this->where)) {
			$sql .= ' WHERE (' . implode(')' . $where_join . '(', $this->where) . ')';
		}
		if (!empty($this->group_by)) {
			$group_by = $this->group_by;
			if (is_array($group_by)) {
				$group_by = implode(',', $group_by);
			}
			$sql .= 'GROUP BY ' . $group_by;
		}
		if (!empty($this->having)) {
			$sql .= ' HAVING ' . implode($where_join, $this->having);
		}
		if (!empty($this->order_by)) {
			$order_by = $this->order_by;
			if (is_array($this->order_by)) {
				$order_by = implode(',', $this->order_by);
			}
			$sql .= ' ORDER BY ' . $order_by;
		}
		if (!empty($this->limit)) {
			$sql .= ' LIMIT ' . $this->limit;
		}
		
		$sql = $this->format_query($sql);
		
		return $sql;
	}

	/**
	 * Do the query
	 * 
	 * @return _pdo_statement
	 */
	function query() {
		$pdo = $this->get_pdo();
		$results = $pdo->query($this->build());
//		$results = $pdo->prepare($this->build());
//		$results->execute($this->params);
		return $results;
	}

	/**
	 * Query and fetch all
	 * 
	 * @param int $fetch_type
	 * @param mixed $fetch_argument
	 * @return array 
	 */
	function fetch_all($fetch_type = null, $fetch_argument = null) {
		if($this->fetch_class && $fetch_type == null) {
			$fetch_type = PDO::FETCH_CLASS;
			$fetch_argument = $this->fetch_class;
		}
		if(!$fetch_type) {
			$fetch_type = PDO::FETCH_ASSOC;
		}
		$results = $this->query();
		if ($results) {
			if($fetch_argument) {
				return $results->fetchAll($fetch_type,$fetch_argument);
			}
			return $results->fetchAll($fetch_type);
		}
		return array();
	}

	/**
	 * Fetch only the first value
	 * 
	 * @param string $field (optional) shortcut for fields
	 * @return string
	 */
	function fetch_value($field = null) {
		if($field !== null) {
			$this->fields = $field;
		}
		$row = $this->fetch(PDO::FETCH_NUM);
		if (isset($row[0])) {
			return $row[0];
		}
		return false;
	}
	
	/**
	 * Fetch the column as array
	 * 
	 * @param string $field
	 * @return array
	 */
	function fetch_array($field) {
		if($field !== null) {
			$this->fields = $field;
		}
		$rows = $this->fetch_all(PDO::FETCH_ASSOC);
		$res = array();
		foreach($rows as $row) {
			$res[] = $row[$field];
		}
		return $res;
	}
	
	/**
	 * Query and fetch
	 * 
	 * @return string
	 */
	function fetch($fetch_type = null, $fetch_argument = null) {
		if($this->fetch_class && $fetch_type === null) {
			$fetch_type = PDO::FETCH_CLASS;
//			$fetch_argument = $this->fetch_class;
		}
		if(!$fetch_type) {
			$fetch_type = PDO::FETCH_ASSOC;
		}
		$results = $this->query();
		if ($results) {
			if($fetch_argument) {
				//strangely, fetch does not take the second argument the same way as fetchAll, yeepeee
				return $results->fetch($fetch_type,$fetch_argument);
			}
			return $results->fetch($fetch_type);
		}
		return false;
	}

	function __toString() {
		return $this->build();
	}

	/* helpers */
	
	/**
	 * Current table or alias
	 * @return string
	 */
	protected function table_or_alias() {
		$alias = '';
		$table = $this->from;
		foreach($this->aliases as $key_alias => $value_table) {
			if($value_table == $table) {
				$alias = $key_alias;
			}
		}
		if(!empty($alias)) {
			return $alias;
		}
		return $table;
	}

	/**
	 * Allows smart joins by detecting tables in field names
	 * 
	 * @param string $key
	 * @return string 
	 */
	protected function detect_external_key($key) {
		if (strpos($key, '.') !== false) {
			$key_parts = explode('.', $key);

			//do not create joins when they are already there
			$table = $key_parts[0];
			if(isset($this->aliases[$table])) {
				$table = $this->aliases[$table];
			}
			foreach ($this->joins as $join) {
				if ($join['table'] === $table) {
					return $key;
				}
			}
			if ($table == $this->from) {
				return $key;
			}
			$this->left_join($table);
			if(empty($this->fields)) {
				$this->fields = array($this->from . '.*');
			}
			$this->add_field($table . '.' . $key_parts[1]);
		}
		return $key;
	}
	
	/**
	 * Get an alias for a table
	 * 
	 * @param string $table
	 * @return string
	 */
	protected function get_alias($table) {
		foreach($this->aliases as $alias => $alias_table) {
			if($alias_table == $table) {
				return $alias;
			}
		}
		return false;
	}
	
	public function add_alias($alias,$table) {
		$this->aliases[$alias] = $table;
	}

	/**
	 * Add spacing to a sql string
	 * @link http://stackoverflow.com/questions/1191397/regex-to-match-values-not-surrounded-by-another-char
	 * @param string $sql
	 * @return string
	 */
	protected function format_query($sql) {
		//regex work with a lookahead to avoid splitting things inside single quotes
		$sql = preg_replace(
				"/(WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|DUPLICATE KEY)(?=(?:(?:[^']*+'){2})*+[^']*+\z)/", "\n$0", $sql
		);
		$sql = preg_replace(
				"/(INNER|LEFT|RIGHT|CASE|WHEN|END|ELSE|AND)(?=(?:(?:[^']*+'){2})*+[^']*+\z)/", "\n    $0", $sql);
		return $sql;
	}

	/**
	 * Return the PDO instance
	 * @return _pdo
	 */
	protected function get_pdo() {
		if (self::$pdo) {
			return self::$pdo;
		}
		$pdo = _::$pdo;
		if ($pdo === null) {
			throw Exception('You must define _::$pdo to use the query builder');
		}
		self::$pdo = $pdo;
		return $pdo;
	}

}