<?php

namespace ORMizer;

abstract class BaseAdapter {

    // PHP types
    const STRING	= 'string';
    const INTEGER	= 'int';
    const DECIMAL	= 'float';
    const DATETIME	= 'datetime';

    // Connection link to database (singleton)
    protected $db_link;
    // Instance of child database adapter class
	protected static $instance;

	/**
	 * [[Description]]
	 * @private
	 */
	protected function __construct() {
		$this->db_link = new PDOWrapper (
			Config::DBMS,
			Config::DBMS_HOST,
            Config::DBMS_PORT,
			Config::APP_DB_USER,
			Config::APP_DB_USER_PASS,
			Config::APP_DB
		);
	}

	/**
	 * Creates an unique instance of the child database adapter class.
	 * @return Adapter Object [the child database adapter object]
	 */
	public static function instance() {
        if (!isset(self::$instance)) {
            $class = static::class;
            self::$instance = new $class;
        }
   	    return self::$instance;
    }

    public function __clone(){}

    //
	//en UTF8


	/**
	 * Metodo que codifica arrays a json, previamente
	 * asegurandose que todo está en UTF8.
	 * @public
	 * @param  array  [$array=array()] Array para codificar a JSon.
	 * @return string El array codificado.
	 */
	public function _json_encode($array=array()) {
		array_walk_recursive($array, function(&$val) {
    		$val = utf8_encode($val);
		});
		return json_encode($array);
	}


    abstract public function insertRow($table, $props_array);

    abstract public function updateRow($table, $props_array);

    abstract public function getRow($table, $column, $value);

    abstract public function deleteRow($table, $ormizer_id);

    abstract public function getAll($table);

    abstract public function existsTable($table_name);

    abstract public function createTable($table_name, $columns_array);

    abstract public function getTableDescription($table);

    abstract public function findTables($pattern);

    abstract public function createAliasTable($referenced_table);

    abstract public function insertAlias($alias, $ormizer_id, $referenced_table);

    abstract protected function castColumns($columns_array);
}
?>
