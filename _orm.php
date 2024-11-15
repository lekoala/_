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
 * Orm class
 *
 * Inspiration :
 * @link http://www.notorm.com/
 */
class _orm implements ArrayAccess
{

    /**
     * Custom field => field_type array
     * @var array
     */
    protected static $_field_types = array();

    /**
     * Store original data when loaded.
     * @var array
     */
    protected $_original = array();

    /**
     * Create a record object
     *
     * @param mixed $where
     * @param array $params (optional)
     */
    function __construct($where = null, $params = array())
    {
        if (is_object($where)) {
            return $where;
        }
        if ($where !== null) {
            return $this->load($where, $params);
        }
        $this->init();
    }

    static function required_fields()
    {
        return array();
    }

    static function validation_rules()
    {
        return array();
    }

    /**
     * Validate the current record
     * @return boolean
     */
    function validate()
    {
        $err = array();
        foreach (static::required_fields() as $f) {
            if (empty($this->$f)) {
                $err[] = "$f is required";
            }
        }
        foreach (static::validation_rules() as $field => $type) {
            switch ($type) {
                default:
                    throw new Exception("Rule $type is not implemented");
            }
        }
        if (!empty($err)) {
            throw new Exception(implode(";", $err));
        }
        return true;
    }

    /**
     * Checks if current record is valid
     * @return boolean
     */
    function is_valid()
    {
        try {
            $this->validate();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /* Helpers */

    /**
     * Load models that extend _orm
     * @param string $dir
     */
    static function autoload($dir)
    {
        _::$registry['orm_autoload_dir'] = $dir;
        return spl_autoload_register('_orm::autoload_callback');
    }

    /**
     * Autoload callback
     * @param string $class
     * @return bool
     */
    static function autoload_callback($class)
    {
        $dir = _::$registry['orm_autoload_dir'];
        $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';
        if (is_file($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * Return the PDO instance
     * @return _pdo
     */
    static function get_pdo()
    {
        $pdo = _::$pdo;
        if ($pdo === null) {
            throw new Exception('You must define _::$pdo to use the orm');
        }
        return $pdo;
    }

    /**
     * Get the table name, override in subclass if necessary
     * @return string
     */
    static function get_table()
    {
        static $table;
        if (empty($table)) {
            $table = strtolower(get_called_class());
        }
        return $table;
    }

    /**
     * Get the primary key field, override in subclass if necessary
     *
     * @return array
     */
    static function get_pk()
    {
        static $pk;
        if (empty($pk)) {
            $class = get_called_class();
            if (strpos($class, '_') !== false) {
                //linkage table
                $class_parts = explode('_', $class);
                $pk = array($class_parts[0] . '_id', $class_parts[1] . '_id');
            } else {
                //default table
                $pk = array('id');
            }
        }
        return $pk;
    }

    /**
     * Helper method that return a single pk or throw an exception if there
     * are multiple pk
     *
     * @return string
     */
    static function get_single_pk()
    {
        $pk = static::get_pk();
        if (is_array($pk)) {
            if (count($pk) > 1) {
                throw new Exception(get_called_class() . ' use multiple primary keys');
            }
            $pk = $pk[0];
        }
        return $pk;
    }

    /**
     * Get the default sort order
     * @return string
     */
    static function get_default_sort()
    {
        static $sort;
        if (empty($sort)) {
            $pk = static::get_pk();
            array_walk($pk, function (&$item) {
                $item = $item . ' ASC';
            });
            $sort = implode(',', $pk);
        }
        return $sort;
    }

    /**
     * Get only public properties (call from external scope)
     *
     * @param string|object $obj
     * @return array
     */
    static function get_public_properties($obj = null)
    {
        if (!$obj) {
            $class = get_called_class();
            $obj = new $class;
        }
        $ref = new ReflectionClass($obj);

        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
        $map = array();
        foreach ($props as $prop) {
            $value = '';
            $name = $prop->name;
            if (is_object($obj)) {
                $value = $obj->$name;
            }
            $map[$name] = $value;
        }
        return $map;
    }

    /**
     * Update passed $where and $order_by criterias
     *
     * This will detect primary keys passed as a shortcut and set
     * a default order_by
     *
     * @param string|array $where
     * @param string|array $order_by
     * @param array $params
     */
    static function update_where(&$where, array &$params = array())
    {
        $pk_fields = static::get_pk();
        $pdo = static::get_pdo();
        $pdo->to_named_params($where, $params);

        if (is_numeric($where)) {
            // single pk
            if (count($pk_fields) != 1) {
                throw new Exception('You passed only a single id when there are multiple primary keys : ' . implode(',', $pk_fields));
            }
            $params[':' . $pk_fields[0]] = $where;
            $where = $pk_fields[0] . ' = :' . $pk_fields[0];
        } elseif (is_array($where) && !_::array_is_assoc($where)) {
            // multiple pk
            if (count($pk_fields) != count($where)) {
                throw new Exception('The numbers of parameters (' . count($where) . ') does not match the number of primary keys (' . count($pk_fields) . ')');
            }
            $sql = array();
            foreach ($pk_fields as $k => $pk_field) {
                $params[':' . $pk_field] = $where[$k];
                $sql[] = $pk_field . ' = :' . $pk_field;
            }
            $where = implode(' AND ', $sql);
        }
        return $where;
    }

    /**
     * Prevent any invalid data to be saved
     *
     * @param array $data
     */
    static function filter_data(&$data)
    {
        // allow only valid properties
        $properties = array_keys(self::get_public_properties(get_called_class()));
        foreach ($data as $k => $v) {
            if (!in_array($k, $properties)) {
                unset($data[$k]);
            }
        }
    }

    /**
     * Update order by clause by using the default sort order
     *
     * @param string $order_by
     * @return string
     */
    static function update_order_by(&$order_by)
    {
        if ($order_by === '') {
            $order_by = static::get_default_sort();
        }
        return $order_by;
    }

    /* shortcut to pdo methods */

    /**
     * Find duplicated rows
     *
     * @param string $field
     * @param string $fields
     * @return array
     */
    static function duplicates($field, $fields = '*')
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        return $pdo->duplicates($table, $field, $fields);
    }

    /**
     * Select records and return an array
     *
     * @param string|array $where (optional)
     * @param string|array $order_by (optional) Pass null to remove order by clause
     * @param string|array $limit (optional)
     * @param string|array $fields (optional)
     * @param array $params (optional)
     * @return array
     */
    static function select($where = null, $order_by = '', $limit = null, $fields = '*', $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        self::update_where($where, $params);
        self::update_order_by($order_by);

        return $pdo->select($table, $where, $order_by, $limit, $fields, $params);
    }

    /**
     * Like find, but count the records instead (seems like rowCount of pdo doesn't work too well)
     *
     * @param string $where (optional)
     * @param array $params (optional)
     * @return int The number of rows
     */
    static function count($where = null, $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        return $pdo->count($table, $where, $params);
    }

    /**
     * Like find, but count the records instead (seems like rowCount of pdo doesn't work too well)
     *
     * @param string $where (optional)
     * @param array $params (optional)
     * @return int The number of rows
     */
    static function id_count($where = null, $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        return $pdo->id_count($table, $where, $params);
    }

    /**
     * Return the highest value for given field. Id by default
     * @return int
     */
    static function max($field = 'id')
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        $result = $pdo->query('SELECT MAX(' . $field . ') FROM ' . $table);
        if ($result) {
            return $result->fetchColumn();
        }
        return false;
    }

    /**
     * Update a record
     *
     * @param array $data
     * @param string|int $where (optional)
     * @param array $params (optional)
     * @return bool
     */
    static function update(array $data, $where = null, array $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();
        $pk_fields = static::get_pk();

        // detect pk inside the passed data array
        if ($where === null) {
            $where = array();
            foreach ($pk_fields as $pk) {
                $id = _::array_get($data, $pk);
                if ($id) {
                    unset($data[$pk]);
                    $params[':' . $pk] = $id;
                    $where[] = $pk . ' = :' . $pk;
                }
            }
            $where = implode(' AND ', $where);
        }
        self::filter_data($data);
        return $pdo->update($table, $data, $where, $params);
    }

    /**
     * Delete a set of records
     *
     * @param string $where
     * @param array $params
     * @return bool
     */
    static function delete($where, $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        self::update_where($where, $params);

        return $pdo->delete($table, $where, $params);
    }

    /**
     * Insert a record
     *
     * @param array $data
     * @param array $params
     * @return bool
     */
    static function insert(array $data, array $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        self::filter_data($data);

        return $pdo->insert($table, $data, $params);
    }

    /* Table operations */

    /**
     * Empty the table from all records
     *
     * @param bool $truncate
     * @return int
     */
    static function empty_table($truncate = false)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        return $pdo->empty_table($table, $truncate);
    }

    /**
     * Drop table
     *
     * @param bool $execute
     * @return int
     */
    static function drop_table()
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        return $pdo->drop_table($table);
    }

    /**
     * Is this property likely to be a foreign key field
     * @param string $property
     * @return bool
     */
    static function is_foreign_key($property)
    {
        if (strpos($property, '_') !== false) {
            $parts = explode('_', $property);
            $model = $parts[0];
            $key = $parts[1];
            if (class_exists($model) && is_subclass_of($model, '_orm') && property_exists($model, $key)) {
                $reference_table = $model::get_table();
                return array(
                    'foreign_table' => $reference_table,
                    'foreign_column' => $key,
                    'constraint' => $reference_table . '(' . $key . ')'
                );
            }
        }
        return false;
    }

    /**
     * Scaffold a create statement
     *
     * @param bool $execute
     * @param bool $no_foreign_keys
     * @return string
     */
    static function create_table($execute = true, $no_foreign_keys = false)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();
        $pk_fields = static::get_pk();
        $class = get_called_class();

        $properties = static::$_field_types;
        if (empty($properties)) {
            $properties = array_keys(self::get_public_properties($class));
        }

        $fk_fields = array();
        if (!$no_foreign_keys) {
            foreach ($properties as $property) {
                $fk_infos = self::is_foreign_key($property);
                if ($fk_infos) {
                    $fk_fields[$property] = $fk_infos['constraint'];
                }
            }
        }

        return $pdo->create_table($table, $properties, $pk_fields, $fk_fields, $execute);
    }

    /**
     * Scaffold an alter table
     *
     * @param bool $execute
     * @return string
     */
    static function alter_table($execute = true)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();
        $class = get_called_class();

        $default_properties = static::$_field_types;
        $properties = array_keys(self::get_public_properties($class));

        $columns = array_keys($pdo->list_columns($table, false));
        $current_fk_fields = $pdo->list_foreign_keys($table);

        $fk_fields = array();
        foreach ($properties as $property) {
            $fk_infos = self::is_foreign_key($property);
            if ($fk_infos) {
                foreach ($current_fk_fields as $fk_field) {
                    if ($fk_field['foreign_table'] == $fk_infos['foreign_table']) {
                        break 2;
                    }
                }
                $fk_fields[$property] = $fk_infos['constraint'];
            }
        }

        $missing_cols = array_diff($properties, $columns);
        $removed_cols = array_diff($columns, $properties);

        if (!empty($default_properties)) {
            $tmp = array();
            foreach ($missing_cols as $col) {
                $tmp[$col] = $default_properties[$col];
            }
            $missing_cols = $tmp;
        }

        $res = '';

        if (!empty($missing_cols) || !empty($removed_cols)) {
            $typed_missing_cols = [];
            $types = static::generate_field_types();
            foreach ($missing_cols as $missing_col) {
                $typed_missing_cols[$missing_col] = $types[$missing_col];
            }
            $res .= $pdo->alter_table($table, $typed_missing_cols, $removed_cols, $execute);
        }
        if (!empty($fk_fields)) {
            $res .= $pdo->alter_keys($table, $fk_fields, $execute);
        }
        if (empty($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Generate field types
     *
     * @return array
     */
    static function generate_field_types()
    {
        $class = get_called_class();
        $pdo = static::get_pdo();
        $public_properties = array_keys(self::get_public_properties($class));
        $base_types = static::get_field_types();
        $field_types = [];
        foreach ($public_properties as $property) {
            if (isset($base_types[$property])) {
                $field_types[$property] =  $base_types[$property];
            } else {
                $field_types[$property] = $pdo->name_to_type($property);
            }
        }
        return $field_types;
    }

    static function get_field_types()
    {
        return [];
    }

    /**
     * Scaffold database
     *
     * @param string $dir
     * @return string
     */
    static function scaffold_db($dir = null)
    {
        if ($dir == null) {
            $dir = _::$registry['orm_autoload_dir'];
        }
        if (is_dir($dir)) {
            throw new Exception($dir . ' is not a valid directory');
        }

        //collect models
        $iterator = new DirectoryIterator($dir);
        $models = array();
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $models[] = str_replace('.php', '', $fileinfo->getFilename());
            }
        }

        //scaffold create table statements
        foreach ($models as $model) {
            $sql = '';
            if (class_exists($model) && is_subclass_of($model, '_orm')) {
                $sql .= $model::create_table(false) . ';';
            }
        }

        return $sql;
    }

    /**
     * Create a view based on the base model extend with extra models/properties
     *
     * @param array $properties Any extra properties
     * @param array $models Any other model that should be included in the view
     * @param bool $execute
     * return string
     */
    static function create_view($properties = array(), $models = array(), $execute = true)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        if (is_string($properties)) {
            $properties = explode(',', $properties);
        }
        $base_properties = array_keys(self::get_public_properties(get_called_class()));
        $base_properties = array_map(function ($item) use ($table) {
            $item = $table . '.' . $item;
            return $item;
        }, $base_properties);
        $extended_properties = array();

        foreach ($models as $model) {
            $model_properties = array_keys(self::get_public_properties($model));
            foreach ($model_properties as $property) {
                if (!in_array($table . '.' . $model . '_' . $property, $base_properties)) {
                    $extended_properties[] = $model . '.' . $property . ' AS ' . $model . '_' . $property;
                }
            }
        }

        $all_properties = array_merge($base_properties, $properties, $extended_properties);

        $select = 'SELECT ' . implode(",\n", $all_properties) . ' FROM ' . $table;

        foreach ($models as $model) {
            $model_table = $model::get_table();
            $model_pk = $model::get_pk();
            if (count($model_pk) > 1) {
                throw new Exception('Multiple primary keys not supported in view');
            }
            $model_pk = $model_pk[0];
            $select .= "\nLEFT JOIN " . $model_table . ' ON ' . $table . '.' . $model . '_' . $model_pk . ' = ' . $model . '.' . $model_pk;
        }

        return $pdo->create_view($table, $select, $execute);
    }

    /**
     * Drop a view linked to the model
     *
     * @param bool $execute
     * @return string
     */
    static function drop_view($execute = true)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();
        $view = 'v_' . $table;

        return $pdo->drop_view($view, $execute);
    }

    /* static helpers */

    /**
     * Get an instance of _query for this model
     *
     * @return _query
     */
    static function query()
    {
        $query = new _query(static::get_table());
        $query->fetch_as(get_called_class());
        return $query;
    }

    /**
     * Find records
     *
     * @param string|array $where (optional)
     * @param string|array $order_by (optional) Pass null to remove order by clause
     * @param string|array $limit (optional)
     * @param array $params (optional)
     * @return static[]
     */
    static function find($where = null, $order_by = '', $limit = null, $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        self::update_where($where, $params);

        $stmt = $pdo->select_stmt($table, $where, $order_by, $limit, '*', $params);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_CLASS, get_called_class());

        return $results;
    }

    /**
     * Find the first record
     *
     * @param string|array $where (optional)
     * @param string|array $order_by (optional) If an array, order_by is used as params
     * @param array $params (optional)
     * @return static
     */
    static function find_one($where = null, $order_by = '', $limit = null, $params = array())
    {
        $result = self::find($where, $order_by, $limit, $params);
        if ($result) {
            return $result[0];
        }
    }

    /**
     * Find or create a record
     *
     * @param string|array $where
     * @param array $params
     * @return static
     */
    static function find_or_create($where, $params = array())
    {
        $result = self::find_one($where, '', null, $params);
        if (!$result) {
            self::insert($where);
            $result = self::find_one($where, '', null, $params);
        }
        return $result;
    }

    /**
     * Inject the class into a array of records
     *
     * When injected, it avoids creation of the record on the fly
     * when using has_one method
     *
     * @param array $records
     * @param string $column
     */
    static function inject(&$records, $column = null)
    {
        $ids = array();
        $pk = static::get_single_pk();
        if ($column === null) {
            $class = get_called_class();
            $column = $class . '_' . $pk;
        }
        foreach ($records as $record) {
            $key = $record->$column;
            $ids[] = $key;
        }
        if (empty($ids)) {
            return;
        }
        $ids = array_unique($ids);
        $injected_records = static::find(array($pk => $ids), null);
        $ordered_records = array();
        foreach ($injected_records as $record) {
            $ordered_records[$record->$pk] = $record;
        }
        foreach ($records as $record) {
            $key = $record->$column;
            if (isset($ordered_records[$key])) {
                $record->$column = $ordered_records[$key];
            }
        }
    }

    /**
     * Create an associative array ready for drop downs
     *
     * @param string $id
     * @param string $label
     * @param string|array $where
     * @param string|array $order_by
     * @param string|array $limit
     * @param array $params
     * @return array
     */
    static function dropdown($id = 'id', $label = 'name', $where = null, $order_by = '', $limit = null)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        $results = self::select($where, $order_by, $limit, array($id, $label));

        $map = array();
        $label_parts = _::arrayify($label);

        foreach ($results as $row) {
            $tmp = array();
            foreach ($label_parts as $part) {
                $tmp[] = $row[$part];
            }

            $map[$row[$id]] = implode(' ', $tmp);
        }

        return $map;
    }

    static function select_org_by($org, $query = null, $order_by = '', $limit = null, $fields = '*', $params = array())
    {

        if ($org == "") {
            $org = "id";
        }
        $res = self::select($query, $order_by, $limit, $fields, $params);
        $final = array();
        foreach ($res as $k => $v) {
            $final[$v[$org]] = $v;
        }
        return $final;
    }

    /* orm instance methods */

    /**
     * Flatten the record to include virtual properties and remove objects
     *
     * @return string
     */
    function flatten()
    {
        return $this->to_array(true);
    }

    /**
     * Export the object
     *
     * @param array $virtual_properties
     * @return array
     */
    function export($virtual_properties = array())
    {
        $virtual_properties = _::arrayify($virtual_properties);
        $arr = array();
        foreach ($virtual_properties as $virtual_property) {
            $arr[$virtual_property] = $this->$virtual_property();
        }

        $properties = self::get_public_properties($this);

        foreach ($properties as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof _datetime) {
                    $fv = $v->db_format();
                } elseif (is_subclass_of($v, '_orm')) {
                    $pk = $v::get_single_pk();
                    $fv = $v->$pk;
                } else {
                    throw new Exception('You have an object of class ' . get_class($v) . ' in property ' . $k);
                }

                $properties[$k] = $fv;
            }
        }
        $properties = array_merge($properties, $arr);
        return $properties;
    }

    /**
     * Save the record
     * @return bool
     */
    function save($force_created = false)
    {
        $pdo = static::get_pdo();
        $table = static::get_table();
        $pk_fields = static::get_pk();

        try {
            $this->validate();
        } catch (Exception $ex) {
            return $ex;
        }

        $properties = $this->export();

        //create the record
        if (!$this->exists()) {
            if (!$force_created && array_key_exists('created_at', $properties)) {
                $properties['created_at'] = _datetime::now_db();
            }
            if (array_key_exists('user_id', $properties) && empty($properties['user_id'])) {
                if (_::$current_user && isset(_::$current_user->id)) {
                    $properties['user_id'] = _::$current_user->id;
                }
            }
            if (array_key_exists('created_by', $properties)) {
                if (_::$current_user && isset(_::$current_user->id)) {
                    $properties['created_by'] = _::$current_user->id;
                }
            }
            $insert_properties = array();
            //do not insert empty properties
            foreach ($properties as $k => $v) {
                if ($v !== null && $v !== '') {
                    $insert_properties[$k] = $v;
                }
            }
            $result = self::insert($insert_properties);
            //update id
            if ($result) {
                $this->id = $result;
            }
        }
        //update the record
        else {
            //check if the record has changed
            $changed_properties = $this->get_changed_fields();
            if (array_key_exists('updated_at', $properties)) {
                $changed_properties['updated_at'] = _datetime::now_db();
            }
            if (array_key_exists('updated_by', $properties)) {
                if (_::$current_user && isset(_::$current_user->id)) {
                    $properties['updated_by'] = _::$current_user->id;
                }
            }
            $params = array();
            foreach ($pk_fields as $pk) {
                $params[':' . $pk] = $this->$pk;
            }
            $result = self::update($changed_properties, $pk_fields, $params);
        }

        return $result;
    }

    public function get_changed_fields($field = null)
    {
        $changed_properties = $this->export();
        if (!empty($this->_original)) {
            $changed_properties = array();
            foreach ($this->_original as $k => $v) {
                if ($this->$k != $v) {
                    $changed_properties[$k] = $this->$k;
                }
            }
        }
        if ($field) {
            return $changed_properties[$field];
        }
        return $changed_properties;
    }

    /**
     * Remove the record
     *
     * @return bool
     */
    function remove()
    {
        $pk_fields = static::get_pk();

        if (!$this->exists()) {
            return false;
        }

        $where = array();
        foreach ($pk_fields as $pk) {
            $where[$pk] = $this->$pk;
        }
        return self::delete($where);
    }

    /**
     * Load record into class through PDO
     *
     * @param mixed $id Numeric id or custom sql where clause
     * @param array $params (optional) Pdo bound params if id is not numeric
     */
    function load($where, $params = array())
    {
        $pdo = static::get_pdo();
        $table = static::get_table();

        self::update_where($where, $params);

        $stmt = $pdo->select_stmt($table, $where, null, null, '*', $params);
        $stmt->setFetchMode(PDO::FETCH_INTO, $this);
        $result = $stmt->execute($params);

        if (!$stmt->fetch()) {
            throw new Exception('Failed to load record ' . json_encode(array_values($params)) . ' of class ' . get_called_class());
        }

        $this->init();

        return $this;
    }

    /**
     * Init, called at the end of the constructor or load method
     */
    protected function init()
    {
        //TODO: this takes at lot of time (reflection and co) should maybe find something simple
        $this->_original = $this->get_public_properties($this);

        //transform date object
        //TODO: avoid doing that up front, instead it would be faster to analyze schema and transform only if needed
        foreach ($this->_original as $k => $v) {
            if (!$v) {
                continue;
            }
            $date = $time = $datetime = [];
            preg_match('/^(\d{4}-\d{2}-\d{2})$/', $v, $date);
            preg_match('/^(\d{2}:\d{2}:\d{2})$/', $v, $time);
            preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/', $v, $datetime);
            if (count($datetime)) {
                $this->$k = _datetime::from_datetime($datetime[0]);
            } elseif (count($date)) {
                $this->$k = _datetime::from_date($date[0]);
            } elseif (count($time)) {
                $this->$k = _datetime::from_time($time[0]);
            }
        }
    }

    /**
     * Does the record exist in the db
     *
     * This is only reliable if you did not manually add an id. Otherwise,
     * you need to set $db = true to actually make a query on the db which
     * is always right, but requires one query each time.
     *
     * @param bool $db Query the db
     */
    function exists($db = false)
    {
        $pk_fields = static::get_pk();
        if ($db || count($pk_fields) > 1) {
            $where = array();
            foreach ($pk_fields as $pk) {
                $where[$pk] = $this->$pk;
            }
            return self::count($where);
        }
        foreach ($pk_fields as $pk) {
            if (empty($this->$pk)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Populate record data
     *
     * @param array $data (optional) $_GET,$_POST by default
     */
    function populate(array $data = null)
    {
        if ($data === null) {
            $data = array_merge($_GET, $_POST);
            if (isset($data[get_called_class()])) {
                $data = $data[get_called_class()];
            }
        }

        if (empty($data)) {
            return false;
        }

        //populate properties
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
                unset($data[$k]);
            }
        }
    }

    /**
     * Like populate but doesn't overwrite value if given value is empty
     *
     * @param array $data
     */
    function augment(array $data = null)
    {
        if ($data === null) {
            $data = array_merge($_GET, $_POST);
            if (isset($data[get_called_class()])) {
                $data = $data[get_called_class()];
            }
        }

        //populate properties
        foreach ($data as $k => $v) {
            if (property_exists($this, $k) && !empty($v)) {
                $this->$k = $v;
                unset($data[$k]);
            }
        }
    }

    /**
     * Populate relations
     *
     * @param string $relation
     * @param mixed $v
     */
    function populate_rel($relation, $v)
    {
        $pk = static::get_single_pk();

        $relation_details = $this->is_related($relation);

        if ($relation_details) {
            if (!is_array($v)) {
                $v = (string) $v;
            }
            switch ($relation_details['type']) {
                case 'has_one':
                    $col = $relation_details['column'];
                    $this->$col = $v;
                    break;
                case 'has_many':
                    if (!is_array($v)) {
                        $v = array($v);
                    }
                    $foreign_pk = $relation_details['foreign_table']::get_single_pk();
                    foreach ($v as $value) {
                        $data = array(
                            $relation_details['foreign_column'] => $this->$pk
                        );
                        $where = array(
                            $foreign_pk => $value
                        );
                        $relation_details['foreign_table']::update($data, $where);
                    }
                    break;
                case 'many_many':
                    //get the other pk
                    $join_table_pk = $relation_details['join_table']::get_pk();
                    $other_pk = '';
                    foreach ($join_table_pk as $join_table_pk) {
                        if ($pk != $relation_details['foreign_column']) {
                            $other_pk = $join_table_pk;
                        }
                    }
                    if ($other_pk == 'id') {
                        throw new Exception('The default primary key is used, you need to define get_pk() in your join model');
                    }
                    if (empty($other_pk)) {
                        break;
                    }
                    //delete and then insert records
                    $where = array($relation_details['foreign_column'] => $this->$pk);
                    $relation_details['join_table']::delete($where);

                    if (!empty($v)) {
                        if (!is_array($v)) {
                            $v = array($v);
                        }

                        foreach ($v as $value) {
                            $data = array(
                                $relation_details['foreign_column'] => $this->$pk
                            );

                            //extra fields
                            if (is_array($value)) {
                                $data = array_merge($data, $value);
                            } else {
                                $data[$other_pk] = $value;
                            }

                            $relation_details['join_table']::insert($data);
                        }
                    }

                    break;
            }
        }
    }

    /* Html helpers */

    /**
     * Return record as a primary key
     *
     * Useful when the record is as an object inside another orm object
     * while accessing directly the propery
     *
     * @return string
     */
    function __toString()
    {
        $pk = static::get_single_pk();
        return $this->$pk;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    function to_array($no_objects = false)
    {
        $properties = self::get_public_properties($this);
        if ($no_objects) {
            $tmp = array();
            foreach ($properties as $k => $v) {
                if (is_object($v)) {
                    $v = (string) $v;
                }
                $tmp[$k] = $v;
            }
            $properties = $tmp;
        }
        return $properties;
    }

    /**
     * Provide a simple html representation of the record
     *
     * @return string
     */
    function html()
    {
        $fields = self::get_public_properties($this);

        $html = '<div class="' . get_called_class() . '">';
        foreach ($fields as $key => $value) {
            $html .= "\n" . '<span class="' . $key . '">' . $value . '</span>';
        }
        $html .= "\n</div>";
        return $html;
    }

    /**
     * Rowize the record
     *
     * You can use the dot syntax (i.e : table.field) for foreign keys
     *
     * @param string|array $fields
     * @return string
     */
    function row($fields = null)
    {
        if ($fields === null) {
            $fields = self::get_public_properties($this);
        }
        if (is_string($fields)) {
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
        }
        $html = '';
        foreach ($fields as $field) {
            $value = null;
            if (strpos($field, '.') !== false) {
                $parts = explode('.', $field);
                $key = $parts[0];
                $value = $parts[1];
                $obj = $this->$key();

                if ($obj) {
                    if (property_exists($obj, $value)) {
                        $value = $obj->$value;
                    } elseif (method_exists($obj, $value)) {
                        $value = $obj->$value();
                    }
                } else {
                    $value = '';
                }
            } elseif (property_exists($this, $field)) {
                $value = $this->$field;
            } elseif (method_exists($this, $field)) {
                $value = $this->$field();
            }
            $html .= "<td>" . $value . "</td>\n";
        }
        return $html;
    }

    function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        return null; //fail silently if an unknown property is called
    }

    /* Relationships */

    /**
     * Simple relation management shortcut
     *
     * Usage:
     * $model->singular(property)
     * $model->plural(where,order_by)
     *
     * @param string $name
     * @param array $arguments
     * @return object
     */
    function __call($name, $arguments)
    {
        $relation = $this->is_related($name);

        if ($relation) {
            switch ($relation['type']) {
                case 'has_one':
                    $record = $this->has_one($name);
                    if (isset($arguments[0]) && $record) {
                        $arg = $arguments[0];
                        if (method_exists($record, $arg)) {
                            return $record->$arg();
                        }
                        if (isset($record->$arg)) {
                            return $record->$arg;
                        }
                        return false;
                    }
                    return $record;
                case 'has_many':
                    array_unshift($arguments, $relation['foreign_table']);
                    return call_user_func_array(array($this, 'has_many'), $arguments);
                case 'many_many':
                    array_unshift($arguments, $relation['foreign_table']);
                    return call_user_func_array(array($this, 'many_many'), $arguments);
            }
        }
    }

    /**
     * Simple relation management
     *
     * @param string $name
     * @param array $arguments
     */
    static function __callStatic($name, $arguments)
    {
        //avoid calling _call for static methods
    }

    /**
     * Check if a record is related to "name"
     *
     * @param string $name Singular or plural
     * @return bool|array Array with details of the relation
     */
    function is_related($name)
    {
        $singular_name = _::singularize($name);

        if (!class_exists($singular_name) || !is_subclass_of($singular_name, '_orm')) {
            return false;
        }

        $pk_fields = static::get_pk();
        $class = get_called_class();

        $foreign_table = $singular_name::get_table();
        $foreign_pk = $singular_name::get_single_pk();

        //has one
        if ($singular_name == $name) {
            foreach ($pk_fields as $pk) {
                $col = $name . '_' . $foreign_pk;
                if (property_exists($this, $col)) {
                    return array(
                        'type' => 'has_one',
                        'table' => $class,
                        'column' => $col,
                        'foreign_column' => $foreign_pk,
                        'foreign_table' => $foreign_table,
                    );
                }
            }
        }
        //* many
        else {
            if (count($pk_fields) > 1) {
                throw new Exception('Multiple primary keys not supported in *-many relations');
            }
            $pk = $pk_fields[0];

            $properties = array_keys(self::get_public_properties($singular_name));
            $pk_field = $class . '_' . $pk;

            //has many
            if (in_array($pk_field, $properties)) {
                return array(
                    'type' => 'has_many',
                    'table' => $class,
                    'column' => $pk,
                    'foreign_column' => $pk_field,
                    'foreign_table' => $foreign_table
                );
            }
            //many many
            else {
                //join table can be model1_model2 or the reverse
                $join_table = $class . '_' . $singular_name;
                if (!class_exists($join_table)) {
                    $join_table = $singular_name . '_' . $class;
                }
                if (!class_exists($join_table)) {
                    return false;
                }

                return array(
                    'type' => 'many_many',
                    'table' => $class,
                    'column' => $pk,
                    'foreign_column' => $pk_field,
                    'join_table' => $join_table,
                    'foreign_table' => $foreign_table
                );
            }
        }
        return false;
    }

    /**
     * Many many relationship through linkage table
     *
     * @param string $name
     * @param string|array $extra_where
     * @param string|array $order_by
     * @return array
     */
    function many_many($name, $extra_where = null, $order_by = null)
    {

        $pk = static::get_single_pk();
        $pdo = static::get_pdo();

        $class = get_called_class();
        $external_pk_field = $class . '_' . $pk;

        $external_properties = array_keys(self::get_public_properties($name));

        $params = array(':' . $external_pk_field => $this->$pk);
        $where = $external_pk_field . ' = :' . $external_pk_field;
        if ($extra_where !== null) {
            $where .= ' ' . $extra_where;
        }
        //join_table table can be model1_model2 or the reverse
        $join_table = $class . '_' . $name;
        if (!class_exists($join_table)) {
            $join_table = $name . '_' . $class;
        }

        $name_pk = $name::get_single_pk();

        $join_table = $name . ' INNER JOIN ' . $join_table . ' ON ' . $name . '.' . $name_pk . ' = ' . $join_table . '.' . $name . '_' . $name_pk;

        $stmt = $pdo->select_stmt($join_table, $where, $order_by, null, '*', $params);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_CLASS, $name);
        return $results;
    }

    /**
     * Has many relationship
     *
     * @param string $name
     * @param string|array $extra_where
     * @param string|array $order_by
     * @return array
     */
    function has_many($name, $extra_where = null, $order_by = null)
    {
        $pk = static::get_single_pk();
        $pdo = static::get_pdo();

        $class = get_called_class();

        $external_pk_field = $class . '_' . $pk;

        $external_properties = array_keys(self::get_public_properties($name));

        $params = array(':' . $external_pk_field => $this->$pk);
        $where = $external_pk_field . ' = :' . $external_pk_field;
        if ($extra_where !== null) {
            $where .= ' ' . $extra_where;
        }

        //find records that reference our record
        return $name::find($where, $order_by, null, $params);
    }

    /**
     * Has one relationship
     *
     * @param string $name The name of the field without the pk at the end
     * @param string $class The class to use if not the same as the name
     * @return object
     */
    function has_one($name, $class = null)
    {
        if ($class == null) {
            $class = $name;
        }
        $pk = $class::get_single_pk();
        $col = $name . '_' . $pk;
        $result = $this->$col;
        if (!is_object($result) && !empty($result)) {
            $result = new $class(array($pk => $result));
            $this->$col = $result;
        }
        return $result;
    }

    //ArrayAccess

    #[\ReturnTypeWillChange]
    public function offsetExists($field)
    {
        if ($this->offsetGet($field) !== null) {
            return true;
        }
        return false;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($field)
    {
        $value = null;
        if (strpos($field, '.') !== false) {
            $parts = explode('.', $field);
            $key = $parts[0];
            $value = $parts[1];
            $obj = $this->$key();

            if ($obj) {
                if (property_exists($obj, $value)) {
                    $value = $obj->$value;
                } elseif (method_exists($obj, $value)) {
                    $value = $obj->$value();
                }
            } else {
                $value = '';
            }
        } elseif (property_exists($this, $field)) {
            $value = $this->$field;
        } elseif (method_exists($this, $field)) {
            $value = $this->$field();
        }
        return $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($field, $value)
    {
        if (property_exists($this, $field)) {
            $this->$field = $value;
        } elseif (method_exists($this, '_' . $field)) {
            $this->{'_' . $field}($value);
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($field)
    {
        $this->offsetSet($field, null);
    }
}
