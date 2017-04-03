<?php

namespace ORMizer;

class Column {

    //Tipos de PHP
    const STRING	= 'string';
    const INTEGER	= 'int';
    const DECIMAL	= 'float';
    const DATETIME	= 'datetime';
    const DATE		= 'date';
    const TIME		= 'time';
    const BLOB		= 'blob';

    //Correspondencia de tipos php-relacional
    private $type_mapping = array(
        'char'		=> self::STRING,
        'varchar'	=> self::STRING,
        'text'		=> self::STRING,
        'datetime'	=> self::DATETIME,
        'timestamp'	=> self::DATETIME,
        'date'		=> self::DATE,
        'time'		=> self::TIME,
        'tinyint'	=> self::INTEGER,
        'smallint'	=> self::INTEGER,
        'mediumint'	=> self::INTEGER,
        'int'		=> self::INTEGER,
        'bigint'	=> self::INTEGER,
        'float'		=> self::DECIMAL,
        'double'	=> self::DECIMAL,
        'numeric'	=> self::DECIMAL,
        'decimal'	=> self::DECIMAL,
        'dec'		=> self::DECIMAL,
        'blob'		=> self::BLOB);

    //Propiedades del objeto que describen la columna
    private $name;
    private $type;
    private $max_length;
    private $null;
    private $key;
    private $default;
    private $extra;

    function __construct($column_description=array()) {
        if(!empty($column_description)) {
            $this->name = $column_description['field'];
            $this->type = $column_description['type'];
            $this->max_length = $column_description['max_length'];
            $this->null = $column_description['null'];
            $this->key = $column_description['key'];
            $this->default = $column_description['default'];
            $this->extra = $column_description['extra'];
        }
    }

    function __get($property) {
        return $this->$property;
    }

    function __set($property, $value) {
        $this->$property = $value;
    }

    public function getPhpType() {
        return $this->type_mapping[$this->type];
    }
}
?>
