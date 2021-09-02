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
 * Simple PDO wrapper used to provide more control over PDO and bundle some
 * helper functions
 */
class _pdo extends PDO
{

    /**
     * All queries made
     * @var array
     */
    static $queries = array();

    /**
     * Total time
     * @var int
     */
    static $time = 0;

    /**
     * Log all queries to this file
     * @var string
     */
    static $log_to_file;

    /**
     * The connection string
     * @var string
     */
    protected $dsn;

    /**
     * Db type
     * @var string
     */
    protected $dbtype;

    /**
     * Username
     * @var string
     */
    protected $username;

    /**
     * Password
     * @var string
     */
    protected $password;

    /**
     * Driver specific options
     * @var array
     */
    protected $options;

    /**
     * Reserved names that should not be used
     * @var array
     */
    protected $reserved_names = array(
        "ACCESSIBLE", "ADD", "ALL",
        "ALTER", "ANALYZE", "AND",
        "AS", "ASC", "ASENSITIVE",
        "BEFORE", "BETWEEN", "BIGINT",
        "BINARY", "BLOB", "BOTH",
        "BY", "CALL", "CASCADE",
        "CASE", "CHANGE", "CHAR",
        "CHARACTER", "CHECK", "COLLATE",
        "COLUMN", "CONDITION", "CONSTRAINT",
        "CONTINUE", "CONVERT", "CREATE",
        "CROSS", "CURRENT_DATE", "CURRENT_TIME",
        "CURRENT_TIMESTAMP", "CURRENT_USER", "CURSOR",
        "DATABASE", "DATABASES", "DAY_HOUR",
        "DAY_MICROSECOND", "DAY_MINUTE", "DAY_SECOND",
        "DEC", "DECIMAL", "DECLARE",
        "DEFAULT", "DELAYED", "DELETE",
        "DESC", "DESCRIBE", "DETERMINISTIC",
        "DISTINCT", "DISTINCTROW", "DIV",
        "DOUBLE", "DROP", "DUAL",
        "EACH", "ELSE", "ELSEIF",
        "ENCLOSED", "ESCAPED", "EXISTS",
        "EXIT", "EXPLAIN", "FALSE",
        "FETCH", "FLOAT", "FLOAT4",
        "FLOAT8", "FOR", "FORCE",
        "FOREIGN", "FROM", "FULLTEXT",
        "GRANT", "GROUP", "HAVING",
        "HIGH_PRIORITY", "HOUR_MICROSECOND", "HOUR_MINUTE",
        "HOUR_SECOND", "IF", "IGNORE",
        "IN", "INDEX", "INFILE",
        "INNER", "INOUT", "INSENSITIVE",
        "INSERT", "INT", "INT1",
        "INT2", "INT3", "INT4",
        "INT8", "INTEGER", "INTERVAL",
        "INTO", "IS", "ITERATE",
        "JOIN", "KEY", "KEYS",
        "KILL", "LEADING", "LEAVE",
        "LEFT", "LIKE", "LIMIT",
        "LINEAR", "LINES", "LOAD",
        "LOCALTIME", "LOCALTIMESTAMP", "LOCK",
        "LONG", "LONGBLOB", "LONGTEXT",
        "LOOP", "LOW_PRIORITY", "MASTER_SSL_VERIFY_SERVER_CERT",
        "MATCH", "MAXVALUE", "MEDIUMBLOB",
        "MEDIUMINT", "MEDIUMTEXT", "MIDDLEINT",
        "MINUTE_MICROSECOND", "MINUTE_SECOND", "MOD",
        "MODIFIES", "NATURAL", "NOT",
        "NO_WRITE_TO_BINLOG", "NULL", "NUMERIC",
        "ON", "OPTIMIZE", "OPTION",
        "OPTIONALLY", "OR", "ORDER",
        "OUT", "OUTER", "OUTFILE",
        "PRECISION", "PRIMARY", "PROCEDURE",
        "PURGE", "RANGE", "READ",
        "READS", "READ_WRITE", "REAL",
        "REFERENCES", "REGEXP", "RELEASE",
        "RENAME", "REPEAT", "REPLACE",
        "REQUIRE", "RESIGNAL", "RESTRICT",
        "RETURN", "REVOKE", "RIGHT",
        "RLIKE", "SCHEMA", "SCHEMAS",
        "SECOND_MICROSECOND", "SELECT", "SENSITIVE",
        "SEPARATOR", "SET", "SHOW",
        "SIGNAL", "SMALLINT", "SPATIAL",
        "SPECIFIC", "SQL", "SQLEXCEPTION",
        "SQLSTATE", "SQLWARNING", "SQL_BIG_RESULT",
        "SQL_CALC_FOUND_ROWS", "SQL_SMALL_RESULT", "SSL",
        "STARTING", "STRAIGHT_JOIN", "TABLE",
        "TERMINATED", "THEN", "TINYBLOB",
        "TINYINT", "TINYTEXT", "TO",
        "TRAILING", "TRIGGER", "TRUE",
        "UNDO", "UNION", "UNIQUE",
        "UNLOCK", "UNSIGNED", "UPDATE",
        "USAGE", "USE", "USING",
        "UTC_DATE", "UTC_TIME", "UTC_TIMESTAMP",
        "VALUES", "VARBINARY", "VARCHAR",
        "VARCHARACTER", "VARYING", "WHEN",
        "WHERE", "WHILE", "WITH",
        "WRITE", "XOR", "YEAR_MONTH",
        "ZEROFILL", " ",
        "GENERAL", "IGNORE_SERVER_IDS", "MASTER_HEARTBEAT_PERIOD",
        "MAXVALUE", "RESIGNAL", "SIGNAL",
        "SLOW"
    );

    /**
     * A smarter constructor for PDO. You can pass everything in the first argument
     * as an array or use it as usual
     *
     * @param string|array $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     */
    function __construct($dsn, $username = null, $password = null, array $options = array())
    {
        if (is_array($dsn)) {
            //extract other params
            if (isset($dsn['username'])) {
                $username = $dsn['username'];
                unset($dsn['username']);
            }
            if (isset($dsn['password'])) {
                $password = $dsn['password'];
                unset($dsn['password']);
            }
            if (isset($dsn['options'])) {
                $options = $dsn['options'];
                unset($dsn['options']);
            }

            //prefix
            if (isset($dsn['dbtype'])) {
                $this->dbtype = $dsn['dbtype'];
                unset($dsn['dbtype']);
            }

            //flatten array
            foreach ($dsn as $k => $v) {
                if (!is_int($k)) {
                    $dsn[$k] = $k . '=' . $v;
                }
            }

            //{dbtype}:dbname={dbname};host={host};port={port}
            $dsn = $this->dbtype . ':' . implode(';', array_values($dsn));
        }

        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;

        if (empty($this->dbtype)) {
            $pos = strpos($this->dsn, ':');
            $this->dbtype = substr($this->dsn, 0, $pos);
        }
        try {
            parent::__construct($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new _pdo_exception($e);
        }

        //always throw exception
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //use custom pdo statement class
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('_pdo_statement', array($this)));
    }

    /* Overriden methods */

    /**
     * Exec wrapper for stats
     *
     * @param string $statement
     * @return int
     */
    function exec($statement)
    {
        $sql = $this->translate($statement);
        try {
            $time = microtime(true);
            $result = parent::exec($statement);
            $time = microtime(true) - $time;
            self::log_query($statement, $time);
        } catch (PDOException $e) {
            _pdo::log_query($statement);
            throw new _pdo_exception($e);
        }
        return $result;
    }

    /**
     * Query wrapper for stats
     *
     * @param string $statement
     * @return _pdo_statement
     */
    public function query($statement, $fetchMode = null, $colno = 0)
    {
        $sql = $this->translate($statement);

        try {
            $time = microtime(true);
            $result = parent::query($statement);
            $time = microtime(true) - $time;
            self::log_query($statement, $time);
        } catch (PDOException $e) {
            _pdo::log_query($statement);
            throw new _pdo_exception($e);
        }

        return $result;
    }

    /**
     * More advanced quote (quote arrays, return NULL properly, quotes INT properly...)
     *
     * @param string $value
     * @param int $parameter_type
     * @return string
     */
    function quote($value, $parameter_type = null)
    {
        if (is_array($value)) {
            $value = implode(',', array_map(array($this, 'quote'), $value));
            return $value;
        } elseif (is_null($value)) {
            return "NULL";
        } elseif (($value !== true) && ((string) (int) $value) === ((string) $value)) {
            //otherwise int will be quoted, also see @https://bugs.php.net/bug.php?id=44639
            return parent::quote(intval($value), PDO::PARAM_INT);
        }
        $parameter_type = PDO::PARAM_STR;
        return parent::quote($value, $parameter_type);
    }

    /* Helper methods */

    /**
     * Get db type
     *
     * @return string
     */
    function get_dbtype()
    {
        return $this->dbtype;
    }

    /**
     * Cross database now string
     *
     * @return string
     */
    function now()
    {
        $dbtype = $this->get_dbtype();
        switch ($dbtype) {
            case 'sqlite':
                return "datetime('now')";
            case 'mssql':
                return 'GETDATE()';
            default:
                return 'NOW()';
        }
    }

    /**
     * Generate a pseudo random (UUID V4) uuid
     *
     * @return string 32 characters string
     */
    static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Enable or disable foreign key support
     *
     * @param bool $enable
     * @return bool
     */
    function foreign_keys($enable = true)
    {
        $dbtype = $this->get_dbtype();
        switch ($dbtype) {
            case 'sqlite':
                if ($enable) {
                    return $this->exec('PRAGMA foreign_keys = ON');
                } else {
                    return $this->exec('PRAGMA foreign_keys = OFF');
                }
            case 'mysql':
                if ($enable) {
                    return $this->exec('SET FOREIGN_KEY_CHECKS = 1');
                } else {
                    return $this->exec('SET FOREIGN_KEY_CHECKS = 0');
                }
            case 'mssql':
                if ($enable) {
                    return $this->exec('ALTER TABLE ? NOCHECK CONSTRAINT ALL');
                } else {
                    return $this->exec('ALTER TABLE ? CHECK CONSTRAINT ALL');
                }
            default:
                throw new Exception('Unsupported database : ' . $dbtype);
        }
    }

    /**
     * List foreign keys querying information schema
     *
     * Return something like
     * Array(
     * [0] => Array(
     * [column_name] => 'name'
     * [foreign_db] => 'db,
     * [foreign_table] => 'company',
     * [foreign_column] => 'id'
     * )
     * )
     * @return array
     */
    function list_foreign_keys($table)
    {
        $query = "SELECT
    `column_name`,
    `referenced_table_schema` AS foreign_db,
    `referenced_table_name` AS foreign_table,
    `referenced_column_name`  AS foreign_column
FROM
    `information_schema`.`KEY_COLUMN_USAGE`
WHERE
    `constraint_schema` = SCHEMA()
AND
    `table_name` = '$table'
AND
    `referenced_column_name` IS NOT NULL
ORDER BY
    `column_name`";
        $res = $this->query($query);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if the table or the field is reserved
     *
     * @param string $name
     * @return bool
     */
    function is_reserved_name($name)
    {
        if (in_array(strtoupper($name), $this->reserved_names)) {
            return true;
        }
        return false;
    }

    /**
     * Highlight keywords in sql
     *
     * @param string $sql
     * @return string
     */
    static function highlight($sql)
    {
        $colors = array('chars' => 'Silver', 'keywords' => 'PaleTurquoise', 'joins' => 'Thistle  ', 'functions' => 'MistyRose', 'constants' => 'Wheat');
        $chars = '/([\\.,\\(\\)<>:=`]+)/i';
        $constants = '/(\'[^\']*\'|[0-9]+)/i';
        $keywords = array(
            'SELECT', 'UPDATE', 'INSERT', 'DELETE', 'REPLACE', 'INTO', 'CREATE', 'ALTER', 'TABLE', 'DROP', 'TRUNCATE', 'FROM',
            'ADD', 'CHANGE', 'COLUMN', 'KEY',
            'WHERE', 'ON', 'CASE', 'WHEN', 'THEN', 'END', 'ELSE', 'AS',
            'USING', 'USE', 'INDEX', 'CONSTRAINT', 'REFERENCES', 'DUPLICATE',
            'LIMIT', 'OFFSET', 'SET', 'SHOW', 'STATUS',
            'BETWEEN', 'AND', 'IS', 'NOT', 'OR', 'XOR', 'INTERVAL', 'TOP',
            'GROUP BY', 'ORDER BY', 'DESC', 'ASC', 'COLLATE', 'NAMES', 'UTF8', 'DISTINCT', 'DATABASE',
            'CALC_FOUND_ROWS', 'SQL_NO_CACHE', 'MATCH', 'AGAINST', 'LIKE', 'REGEXP', 'RLIKE',
            'PRIMARY', 'AUTO_INCREMENT', 'DEFAULT', 'IDENTITY', 'VALUES', 'PROCEDURE', 'FUNCTION',
            'TRAN', 'TRANSACTION', 'COMMIT', 'ROLLBACK', 'SAVEPOINT', 'TRIGGER', 'CASCADE',
            'DECLARE', 'CURSOR', 'FOR', 'DEALLOCATE'
        );
        $joins = array('JOIN', 'INNER', 'OUTER', 'FULL', 'NATURAL', 'LEFT', 'RIGHT');
        $functions = array(
            'MIN', 'MAX', 'SUM', 'COUNT', 'AVG', 'CAST', 'COALESCE', 'CHAR_LENGTH', 'LENGTH', 'SUBSTRING',
            'DAY', 'MONTH', 'YEAR', 'DATE_FORMAT', 'CRC32', 'CURDATE', 'SYSDATE', 'NOW', 'GETDATE',
            'FROM_UNIXTIME', 'FROM_DAYS', 'TO_DAYS', 'HOUR', 'IFNULL', 'ISNULL', 'NVL', 'NVL2',
            'INET_ATON', 'INET_NTOA', 'INSTR', 'FOUND_ROWS',
            'LAST_INSERT_ID', 'LCASE', 'LOWER', 'UCASE', 'UPPER',
            'LPAD', 'RPAD', 'RTRIM', 'LTRIM',
            'MD5', 'MINUTE', 'ROUND', 'PRAGMA',
            'SECOND', 'SHA1', 'STDDEV', 'STR_TO_DATE', 'WEEK'
        );

        $sql = str_replace('\\\'', '\\&#039;', $sql);
        foreach ($colors as $key => $color) {
            if (in_array($key, array('constants', 'chars'))) {
                $regexp = $$key;
            } else {
                $regexp = '/\\b(' . join("|", $$key) . ')\\b/i';
            }
            $sql = preg_replace($regexp, '<span style="color:' . $color . "\">$1</span>", $sql);
        }
        return $sql;
    }

    /**
     * Guess type of a field according to its name
     *
     * @param string $name
     * @return string
     */
    function name_to_type($name)
    {
        $dbtype = $this->get_dbtype();

        //default type
        $type = 'VARCHAR(255)';

        //guess by name
        if ($name == 'id') {
            if ($dbtype == 'sqlite') {
                $type = 'INTEGER PRIMARY KEY AUTOINCREMENT';
            } else {
                $type = 'INT AUTO_INCREMENT';
            }
        } elseif ($name == 'guid' || $name == 'uiid' || preg_match(':_guid$:', $name) || preg_match(':_uuid$:', $name)) {
            $type = 'BINARY(36)'; //don't store charset/collation
        } elseif ($name == 'name') {
            $type = 'VARCHAR(45)';
        } elseif ($name == 'zipcode') {
            $type = 'VARCHAR(20)';
        } elseif (strpos($name, 'ip') === 0 || preg_match(':_ip$:', $name)) {
            $type = 'VARCHAR(45)'; //ipv6 storage
        } elseif ($name == 'lang_code' || $name == 'country_code') {
            $type = 'VARCHAR(2)';
        } elseif ($name === 'price' || preg_match(':_price$:', $name)) {
            $type = 'DECIMAL(10,2) UNSIGNED';
        } elseif (preg_match(':_id$:', $name) || preg_match(':_count$:', $name) || preg_match(':_quantity$:', $name) || preg_match(':_qt$:', $name) || preg_match(':_level$:', $name) || preg_match(':_number$:', $name) || $name == 'level' || $name == 'percent' || $name == 'quantity' || $name == 'sort_order' || $name == 'permissions' || $name == 'perms' || $name == 'day') {
            $type = 'INT';
        } elseif ($name == 'lat' || $name == 'lng' || $name == 'lon' || $name == 'latitude' || $name == 'longitude' || preg_match(':_lat:', $name) || preg_match(':_lng:', $name)) {
            $type = 'FLOAT(10,6)';
        } elseif ($name == 'geoloc_precision') {
            $type = 'DECIMAL(10)';
        } elseif (strpos($name, 'is_') === 0 || strpos($name, 'has_') === 0) {
            $type = 'TINYINT';
        } elseif ($name == 'datetime' || preg_match(':_at$:', $name)) {
            $type = 'DATETIME';
        } elseif ($name == 'date' || $name == 'birthday' || $name == 'birthdate' || preg_match(':_date$:', $name) || strpos($name, 'date_') === 0) {
            $type = 'DATE';
        } elseif ($name == 'time' || preg_match(':_time$:', $name) || strpos($name, 'time_') === 0) {
            $type = 'TIME';
        } elseif (preg_match(':_ts$:', $name)) {
            $type = 'TIMESTAMP';
        } elseif (preg_match(':_html$:', $name) || preg_match(':_text$:', $name) || $name == 'content') {
            $type = 'TEXT';
        }
        return $type;
    }

    /**
     * All columns from a table
     *
     * @param string $table
     * @return array
     */
    function list_columns($table)
    {
        $sql = 'SELECT * FROM ' . $table . ' LIMIT 1';

        $stmt = $this->query($sql);

        $i = 0;
        $infos = array();
        while ($column = $stmt->getColumnMeta($i++)) {
            $infos[$column['name']] = $column;
        }
        return $infos;
    }

    /**
     * Cross database list tables
     *
     * @return array
     */
    function list_tables()
    {
        // use database specific statement to get the list of tables
        $mysql = 'SHOW FULL TABLES';
        $pgsql = 'SELECT * FROM pg_tables';
        $mssql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
        $sqlite = "SELECT * FROM sqlite_master WHERE type='table'";
        $oracle = "SELECT * FROM dba_tables";

        $type = $this->get_dbtype();

        $result = $this->query($$type);
        $table_list = $result->fetchAll();
        $table_count = count($table_list);

        //normalize results
        switch ($type) {
            case 'mysql':
                $tables = array();
                for ($i = 0; $i < $table_count; $i++) {
                    $tables[] = $table_list[$i][0];
                }
                $table_list = $tables;
                break;
        }

        return $table_list;
    }

    /**
     * Log query
     *
     * @param string $statement
     * @param int $time
     */
    static function log_query($statement, $time = 0)
    {
        if ($time == 0) {
            self::log_line($statement, '<span style="color:#ff9292">[ERROR]</span>');
        } else {
            self::log_line($statement, '[' . sprintf('%0.6f', $time) . ']');
            self::$time += $time;
        }
    }

    static function log_line($statement, $prefix = null)
    {
        $h_line = self::highlight($statement);
        $line = $statement;
        if ($prefix) {
            $line = $prefix . ' ' . $line;
            $h_line = $prefix . ' ' . $h_line;
        }
        self::$queries[] = $line;
        if (self::$log_to_file) {
            $handle = fopen(self::$log_to_file, 'a+');
            return fwrite($handle, $line . "\n");
        }
    }

    /**
     * Callback for stats
     *
     * @return string
     */
    static function stats()
    {
        $total_queries = count(self::$queries);
        $time = self::$time;
        $html = 'Total queries : ' . $total_queries . ' (' . sprintf('%0.6f', $time) . ' s)';

        $limit = 100;
        $length = count(self::$queries);
        $queries = '';
        for ($i = 0; $i < $limit && $i < $length; $i++) {
            $queries .= self::$queries[$i] . '<br/>';
            if ($i == $limit) {
                $queries .= 'Only showing 100 first queries';
            }
        }

        $html .= ' <a href="#_pdo" onclick="_toggle(\'_pdo\');return false;" style="color:#fff">sql log</a>';
        $html .= ' <div id="_pdo" style="display:none;position:fixed;background:#222;bottom:16px;right:0;height:400px;overflow:auto;width:400px;white-space:pre;padding:5px 20px 5px 5px;">' . $queries . '</div>';
        return $html;
    }

    /* sql helpers */

    /**
     * Insert records
     *
     * @param string $table
     * @param array $data
     * @param array $params
     * @return int The id of the record
     */
    function insert($table, array $data, $params = array())
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $k => $v) {
            if ($v === '' || $v === null) {
                $v = null;
            }
            $keys[] = $k;
            $values[] = ':' . $k;
            $params[':' . $k] = $v;
        }

        $sql = "INSERT INTO " . $table . " (" . implode(",", $keys) . ") VALUES (" . implode(',', $values) . ")";
        $sql = $this->translate($sql);
        $stmt = $this->prepare($sql);
        $result = $stmt->execute($params);
        if ($result) {
            return $this->lastInsertId();
        }
        return $result;
    }

    /**
     * Update records
     *
     * @param string $table
     * @param array $data
     * @param array|string $where
     * @param array $params
     * @return bool
     */
    function update($table, array $data, $where = null, $params = array())
    {
        if (empty($data)) {
            return false;
        }
        $sql = 'UPDATE ' . $table . " SET \n";
        self::to_named_params($where, $params);
        foreach ($data as $k => $v) {
            if ($v === '' || $v === null) {
                $v = null;
            }
            $sql .= $k . ' = :' . $k . ', ';
            $params[':' . $k] = $v;
        }
        $sql = rtrim($sql, ', ');
        $this->inject_where($sql, $where, $params);

        $sql = $this->translate($sql);

        $stmt = $this->prepare($sql);
        $result = $stmt->execute($params);
        return $result;
    }

    /**
     * Delete records
     *
     * @param string $table
     * @param array|string $where
     * @param array $params
     * @return bool
     */
    function delete($table, $where = null, $params = array())
    {
        $sql = 'DELETE FROM ' . $table . '';
        $this->inject_where($sql, $where, $params);
        $sql = $this->translate($sql);
        $stmt = $this->prepare($sql);
        $result = $stmt->execute($params);
        return $result;
    }

    /**
     * Select records
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $order_by
     * @param array|string $limit
     * @param array|string $fields
     * @param array $params
     * @return array
     */
    function select($table, $where = null, $order_by = null, $limit = null, $fields = '*', $params = array())
    {
        $stmt = $this->select_stmt($table, $where, $order_by, $limit, $fields, $params);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    /**
     * Explain given sql
     *
     * @param string $sql
     * @return array
     */
    function explain($sql)
    {
        if ($this->dbtype == 'mssql') {
            $this->query('SET SHOWPLAN_ALL ON');
        }
        $results = $this->query('EXPLAIN ' . $sql);
        if ($this->dbtype == 'mssql') {
            $this->query('SET SHOWPLAN_ALL OFF');
        }
        return $results->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find duplicated rows
     *
     * @param string $table
     * @param string $field
     * @param string $fields
     * @return array
     */
    function duplicates($table, $field, $fields = '*')
    {
        $sql = "SELECT $field,
 COUNT($field) as count
FROM $table
GROUP BY $field
HAVING ( COUNT($field) > 1 )";
        $results = $this->query($sql);
        if ($results) {
            return $results->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }

    /**
     * Create a select statement
     *
     * Note : updated parameters will be placed in the $params through reference
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $order_by
     * @param array|string $limit
     * @param array|string $fields
     * @param array $params
     * @return _pdo_statement
     */
    function select_stmt($table, $where = null, $order_by = null, $limit = null, $fields = '*', &$params = array())
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $table . '';
        $this->inject_where($sql, $where, $params);
        if (!empty($order_by)) {
            if (is_array($order_by)) {
                $order_by = implode(',', $order_by);
            }
            $sql .= ' ORDER BY ' . $order_by;
        }
        if (!empty($limit)) {
            if (is_array($limit)) {
                $limit = implode(',', $limit);
            }
            $sql .= ' LIMIT ' . $limit;
        }
        $sql = $this->translate($sql);
        $stmt = $this->prepare($sql);
        return $stmt;
    }

    /**
     * Count the records
     *
     * @param string $table
     * @param array|string $where
     * @param array $params
     * @return type
     */
    function count($table, $where = null, $params = array())
    {
        $sql = 'SELECT COUNT(*) FROM ' . $table . '';
        $this->inject_where($sql, $where, $params);
        $sql = $this->translate($sql);
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchColumn();
        return (int) $results;
    }

    /**
     * A quick fix to convert ? to named params
     *
     * @param string $where
     * @param array $params
     */
    protected static function to_named_params(&$where, array &$params)
    {
        if (is_string($where) && preg_match('/\?/', $where, $matches)) {
            $matches_count = count($matches);
            $named_params = array();
            for ($i = 0; $i < $matches_count; $i++) {
                $where = preg_replace('/\?/', ':placeholder' . $i, $where, 1);
                $named_params[':placeholder' . $i] = $params[$i];
            }
            $params = $named_params;
        }
    }

    /**
     * Translate a standard sql statement to the current database driver
     *
     * NOW() -> db now (sqlite, mssql)
     * LIMIT limit,offset -> mssql top
     *
     * @param string $sql
     * @return string
     */
    function translate($sql)
    {
        $dbtype = $this->get_dbtype();
        //now
        if ($dbtype != 'mysql') {
            $sql = str_replace('NOW()', $this->now(), $sql);
        }
        //limit
        if ($dbtype == 'mssql') {
            if (preg_match('/LIMIT[\s]*([0-9]*)([\s]*,[\s]*|[\s]*OFFSET[\s]*)?([0-9]*)?/', $sql, $matches)) {
                $limit = $matches[1];
                $offset = $matches[3];
                $sql = str_replace('SELECT ', 'SELECT TOP ' . $limit . ' ', $sql);
            }
        }
        return $sql;
    }

    /**
     * Inject where clause at the end of a sql statement
     *
     * @param string $sql
     * @param string|array $where
     * @return string
     */
    function inject_where(&$sql, &$where, &$params)
    {
        if (is_array($where)) {
            $pdo = $this;
            array_walk($where, function (&$item, $key) use (&$params, $pdo) {
                if (is_array($item)) {
                    $item = array_unique($item);
                    $item = $key . " IN (" . $pdo->quote($item) . ")";
                } elseif (is_string($key)) {
                    //it's an associative array
                    $placeholder = ':' . $key;
                    while (isset($params[$placeholder])) {
                        $placeholder .= rand(1, 9);
                    }
                    $params[$placeholder] = $item;
                    $item = $key . " = " . $placeholder;
                } else {
                    //or just a list of name, the params is already in params
                    $item = $item . " = :" . $item;
                }
            });
            $where = implode(' AND ', $where);
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        return $sql;
    }

    /* Table operations */

    /**
     * Empty the table from all records
     *
     * @param string $table
     * @param bool $truncate
     * @return int
     */
    function empty_table($table, $truncate = false)
    {
        $sql = 'DELETE FROM ' . $table . '';
        if ($truncate) {
            $sql = 'TRUNCATE ' . $table . '';
        }
        return $this->exec($sql);
    }

    /**
     * Drop table
     *
     * @param string $table
     * @return int
     */
    function drop_table($table)
    {
        $sql = 'DROP TABLE ' . $table . '';
        return $this->exec($sql);
    }

    /**
     * Scaffold a create statement
     *
     * @param string $table
     * @param array $fields
     * @param array $pk_fields
     * @param array $fk_fields
     * @param bool $execute
     * @return string
     */
    function create_table($table, array $fields = array(), $pk_fields = array(), $fk_fields = array(), $execute = true)
    {
        if (is_string($pk_fields) && !empty($pk_fields)) {
            $pk_fields = array($pk_fields);
        }

        $fields = $this->add_field_type($fields, $pk_fields);

        if ($this->is_reserved_name($table)) {
            throw new Exception($table . ' is a reserved name');
        }
        foreach ($fields as $field => $value) {
            if ($this->is_reserved_name($field)) {
                throw new Exception($field . ' is a reserved name in table ' . $table);
            }
        }

        $dbtype = $this->get_dbtype();

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . "(\n";
        foreach ($fields as $field => $type) {
            $sql .= "\t" . $field . ' ' . $type . ",\n";
        }

        //primary key
        if ($dbtype != 'sqlite') {
            if (!empty($pk_fields)) {
                $sql .= "\t" . 'PRIMARY KEY (' . implode(',', $pk_fields) . ')' . ",\n";
            }
        }

        //foreign keys
        foreach ($fk_fields as $key => $reference) {
            $fk_name = 'fk_' . $table . '_' . preg_replace('/[^a-z]/', '', $reference);
            $sql .= "\t" . 'CONSTRAINT ' . $fk_name . ' FOREIGN KEY (' . $key . ') REFERENCES ' . $reference . ",\n";
        }

        $sql = rtrim($sql, ",\n");

        $sql .= "\n)";

        if ($execute) {
            $this->exec($sql);
        }

        return $sql;
    }

    /**
     * Scaffold an alter table
     *
     * @param string $table
     * @param array $add_fields
     * @param array $remove_fields
     * @param bool $execute
     * @return string
     */
    function alter_table($table, array $add_fields = array(), array $remove_fields = array(), $execute = true)
    {

        $add_fields = $this->add_field_type($add_fields);

        $sql = 'ALTER TABLE ' . $table . "\n";

        foreach ($add_fields as $field => $type) {
            if ($this->is_reserved_name($field)) {
                throw new Exception($field . ' is a reserved name');
            }
            $sql .= "ADD COLUMN " . $field . " " . $type . ",\n";
        }

        foreach ($remove_fields as $field) {
            $sql .= "DROP COLUMN " . $field . ",\n";
        }

        $sql = rtrim($sql, ",\n");

        if ($execute) {
            $this->exec($sql);
        }

        return $sql;
    }

    /**
     * Alter charset of a table
     *
     * @param string $table
     * @param string $charset
     * @param string $collation
     * @param bool $execute
     * @return string
     */
    function alter_charset($table, $charset = 'utf8', $collation = 'utf8_unicode_ci', $execute = true)
    {
        $sql = 'ALTER TABLE ' . $table . ' MODIFY' . "\n";
        $sql .= 'CHARACTER SET ' . $charset;
        $sql .= 'COLLATE ' . $collation;
        if ($execute) {
            $this->exec($sql);
        }
        return $sql;
    }

    /**
     * Alter keys
     *
     * @param string $table
     * @param array $keys
     * @param bool $execute
     * @return string
     */
    function alter_keys($table, $keys, $execute = true)
    {
        if (empty($keys)) {
            return false;
        }
        $res = $this->query("SHOW TABLE STATUS WHERE Name = '$table'");
        if (!$res) {
            return false;
        }
        $rows = $res->fetchAll();
        if (!isset($rows['Engine']) || $rows['Engine'] != 'InnoDb') {
            return false;
        }

        $all_sql = '';
        foreach ($keys as $key => $reference) {
            $sql = 'ALTER TABLE ' . $table . "\n";
            $fk_name = 'fk_' . $table . '_' . preg_replace('/[^a-z]/', '', $reference);
            $sql .= 'ADD CONSTRAINT ' . $fk_name . ' FOREIGN KEY (' . $key . ') REFERENCES ' . $reference;
            $all_sql .= $sql . ";\n";
            if ($execute) {
                $this->exec($sql);
            }
        }
        $all_sql = trim($sql, "\n");
        return $all_sql;
    }

    /**
     * Guess type to field definitions based on field name
     *
     * @param array $fields
     * @param array $pk_fields
     * @return array
     */
    function add_field_type(array $fields, array $pk_fields = array())
    {
        //do not type already typed fields
        foreach ($fields as $k => $v) {
            if (!is_int($k)) {
                return $fields;
            }
        }

        $fields_type = array();
        $dbtype = $this->get_dbtype();
        foreach ($fields as $field) {
            $type = $this->name_to_type($field);
            if ($dbtype == 'sqlite' && in_array($field, $pk_fields) && strpos($type, 'PRIMARY KEY') === false) {
                //add primary key for sqlite if not already there
                $type .= ' PRIMARY KEY';
            }
            $fields_type[$field] = $type;
        }
        return $fields_type;
    }

    /* view operations */

    /**
     * Create a view
     *
     * @param string $view View name without v_ prefix
     * @param bool $execute
     * @return string
     */
    function create_view($view, $select, $execute = true)
    {
        $name = 'v_' . $view;
        $dbtype = $this->get_dbtype();

        $select = str_replace('SELECT ', '', $select);

        if ($dbtype == 'mysql') {
            $sql = 'CREATE OR REPLACE VIEW ' . $name . " AS SELECT \n";
        } else if ($dbtype == 'sqlite') {
            $sql = 'CREATE VIEW ' . $name . " IF NOT EXISTS AS SELECT \n";
        } else {
            $sql = 'CREATE VIEW ' . $name . " AS SELECT \n";
        }

        $sql .= $select;

        if ($execute) {
            $this->exec($sql);
        }

        return $sql;
    }

    /**
     * Drop a view
     *
     * @param string $view View name without v_ prefix
     * @param bool $execute
     */
    function drop_view($view, $execute = true)
    {
        $name = 'v_' . $view;
        $sql = 'DROP VIEW ' . $name;

        if ($execute) {
            $this->exec($sql);
        }

        return $sql;
    }
}

/**
 * PDOStatement wrapper
 */
class _pdo_statement extends PDOStatement
{

    private function __construct($pdo)
    {
        //need to declare construct as private
    }

    function execute($input_parameters = array())
    {
        $sql = $this->queryString;

        //nicer looking logs
        $sql_rpl = $sql;
        if (!empty($input_parameters)) {
            foreach ($input_parameters as $k => $v) {
                if (!is_numeric($v)) {
                    $v = "'$v'";
                }
                $sql_rpl = preg_replace('/' . $k . '/', $v, $sql_rpl);
            }
        }

        try {
            $time = microtime(true);
            $result = parent::execute($input_parameters);
            $time = microtime(true) - $time;
            _pdo::log_query($sql_rpl, $time);
        } catch (PDOException $e) {
            _pdo::log_query($sql_rpl);
            throw new _pdo_exception($e);
        }

        return $result;
    }
}

/**
 * PDOException wrapper
 */
class _pdo_exception extends PDOException
{

    public function __construct(PDOException $e)
    {
        //make the code/message more consistent
        if (strstr($e->getMessage(), 'SQLSTATE[')) {
            preg_match('/SQLSTATE\[(\w+)\]\: (.*)/', $e->getMessage(), $matches);
            if (!empty($matches)) {
                $this->code = $matches[1];
                $this->message = $matches[2];
            }
        }
    }
}
