<?php

namespace ORMizer;

class MysqlAdapter extends BaseAdapter {

    // Type correspondence php-MySQL.
    public $type_mapping = array(
        'char'		=> self::STRING,
        'varchar'	=> self::STRING,
        'text'		=> self::STRING,
        'datetime'	=> self::DATETIME,
        'timestamp'	=> self::DATETIME,
        'date'		=> self::DATETIME,
        'time'		=> self::DATETIME,
        'tinyint'	=> self::INTEGER,
        'smallint'	=> self::INTEGER,
        'mediumint'	=> self::INTEGER,
        'int'		=> self::INTEGER,
        'bigint'	=> self::INTEGER,
        'float'		=> self::DECIMAL,
        'double'	=> self::DECIMAL,
        'numeric'	=> self::DECIMAL,
        'decimal'	=> self::DECIMAL,
        'dec'		=> self::DECIMAL
    );

    /**
     * Saves the object properties in a new row/record in the table.
     * @param string $table       Table name in which the new row will be inserted.
     * @param array  $props_array Array containing field-value pairs that correspond to object properties.
     */
    public function insertRow($table, $props_array) {
        // We divide the associative array into two simple arrays:
        // one with properties and the other with its values.
        foreach($props_array as $prop=>$value) {
            $properties[] = $prop;
            if(is_array($value)) // If any value is an array, we convert it to json.
                $value = $this->_json_encode($value);
            $values[] = $value;
        }
        // We prepare the query sql.
        $num_fields = count($props_array);
        $sql = 'INSERT INTO '. $table. '(';
        for($i=0;$i<$num_fields; $i++) {
            if($i === $num_fields-1) {
                $sql .= $properties[$i]. ') VALUES(';
            }else {
                $sql .= $properties[$i]. ',';
            }
        }
        for($i=1;$i<=$num_fields; $i++) {
            if($i === $num_fields) {
                $sql .= '?';
            }else {
                $sql .= '?,';
            }
        }
        $sql .= ')';
        $this->db_link->query($sql, $values);
    }

    /**
     * Updates the object properties of a row/record.
     * @param string $table       Table name that contains the record.
     * @param array  $props_array Array containing field-value pairs that correspond to object properties.
     */
    public function updateRow($table, $props_array) {
        $sql = 'UPDATE '. $table. ' SET ';
        foreach($props_array as $prop=>$value) {
            if($prop !== 'ormizer_id') {
                if(is_array($value)) //si algun valor es un array, lo convertimos a json.
                    $value = $this->_json_encode($value);
                $values[] = utf8_encode($value);
                $sql .= $prop. '=?,';
            }
        }
        $sql = StringUtils::beforeLast(',', $sql);
        $sql .= ' WHERE ormizer_id=?';
        $values[] = $props_array['ormizer_id'];
        $this->db_link->query($sql, $values);
    }

    /**
     * Gets a row from the database.
     * @param  string    $table  Table name that contains the record.
     * @param  string    $column Column/field used to select the row.
     * @param  undefined $value  The value sought to select the row.
     * @return array     Associative array containing fields => values. Or false if nothing.
     */
    public function getRow($table, $column, $value) {
        $sql = 'SELECT * FROM '. $table. ' WHERE '. $column. '=? LIMIT 1';
        $this->db_link->query($sql, array($value));
        $rows_array = $this->db_link->fetch();
        if(!$rows_array)
            return false;
        return $rows_array[0];
    }

    /**
     * Removes a row from its ormizer id.
     * @param string $table      Table name that contains the record.
     * @param string $ormizer_id The ormizer id to find.
     */
    public function deleteRow($table, $ormizer_id) {
        $sql = 'DELETE FROM '. $table. ' WHERE ormizer_id=? LIMIT 1';
        $this->db_link->query($sql, [$ormizer_id]);
    }

    /**
     * Returns an array with all rows/records in a table.
     * @param  string  $table Table name to be retrieved.
     * @return array Array of rows (arrays) in the table. Or false if nothing.
     */
    public function getAll($table) {
        $sql = 'SELECT * FROM '. $table;
        $this->db_link->query($sql);
        $rows_array = $this->db_link->fetch();
        if(!$rows_array)
            return false;
        return $rows_array;
    }

    //Comprueba si existe una tabla
    /**
     * Checks if a table exists.
     * @param  string  $table_name Table name to check.
     * @return boolean True if table exists.
     */
    public function existsTable($table_name) {
        $sql = "SHOW TABLES LIKE '". $table_name. "'";
        $this->db_link->query($sql);
        if(empty($this->db_link->fetch()))
            return false;
        else return true;
    }

    /**
     * Creates a new table relative to an object in the DDBB.
     * @param string $table_name    Name of the new table to be created.
     * @param array  $columns_array Array corresponding to properties of an object (field => value).
     */
    public function createTable($table_name, $columns_array) {
        $sql = "CREATE TABLE IF NOT EXISTS ". $table_name. " (";
        $sql .= $this->castColumns($columns_array);
        $sql .= ") ENGINE = InnoDB;";
        $this->db_link->exec($sql);
    }

    /**
     * Returns an array describing a table structure.
     * @param  string $table Table name.
     * @return array  Describes the table structure.
     */
    public function getTableDescription($table) {
        $sql = 'DESCRIBE '. $table;
        $this->db_link->query($sql);
        $table_description = $this->db_link->fetch();
        // The following is only done to not use the array keys that the query returns.
        // In other adapters will have to form the same array to pass them as a parameter to the Column class.
        $new_table_description = array();
        foreach($table_description as $column_array) {
            $new_column_array['field'] = $column_array['Field'];
            $new_column_array['type'] = StringUtils::before('(', $column_array['Type']);
            $new_column_array['max_length'] = StringUtils::between('(', ')', $column_array['Type']);
            $new_column_array['null'] = $column_array['Null'];
            $new_column_array['key'] = $column_array['Key'];
            $new_column_array['default'] = $column_array['Default'];
            $new_column_array['extra'] = $column_array['Extra'];

            array_push($new_table_description, $new_column_array);
        }
        return $new_table_description;
    }

    /**
     * Find tables in a DDBB that matches the pattern.
     * @param  regExp  $pattern Regular expression string to use.
     * @return array Array of table names that matches the pattern. Or false if nothing.
     */
    public function findTables($pattern) {
        $sql = "SHOW TABLES LIKE '". $pattern. "'";
        $this->db_link->query($sql);
        $result = $this->db_link->fetch();
        if(empty($result))
            return false;
        else return $result;
    }

    /**
     * Creates a new alias table in the DDBB.
     * @param string $referenced_table Table name of the table that will be referenced by the aliases.
     */
    public function createAliasTable($referenced_table) {
        $sql = "CREATE TABLE IF NOT EXISTS ". $referenced_table. "_alias (";
        $sql .= " alias VARCHAR(40) NOT NULL PRIMARY KEY,";
        $sql .= " ormizer_id CHAR(16) NOT NULL,";
        $sql .= " FOREIGN KEY (ormizer_id) REFERENCES ". $referenced_table. "(ormizer_id) ON DELETE CASCADE";
        $sql .= " ) ENGINE = InnoDB;";
        $this->db_link->exec($sql);
    }

    /**
     * Inserts an alias for an object in its corresponding aliases table.
     * @param string $alias            The alias to set up.
     * @param string $ormizer_id       Id of the object to be aliased.
     * @param string $referenced_table Table name which the object belongs.
     */
    public function insertAlias($alias, $ormizer_id, $referenced_table) {
        $alias_table = $referenced_table. '_alias';
        $sql = 'INSERT INTO '. $alias_table. ' VALUES (?,?)';
        $this->db_link->query($sql, [$alias,$ormizer_id]);
    }

    /**
     * Translates to SQL an array of object properties (property => value).
     * @protected
     * @param  array  $columns_array The properties (columns) array.
     * @return string Corresponding SQL string.
     */
    protected function castColumns($columns_array) {
        $sql = '';
        foreach($columns_array as $column) {
            $sql .= ' '. $column->name. ' '. $column->type;
            if($column->max_length !== null) {
                if(is_array($column->max_length)) {
                    $sql .= '('. $column->max_length[0]. ','. $column->max_length[1]. ')';
                }else {
                    $sql .= '('. $column->max_length. ')';
                }
            }
            if($column->name === 'ormizer_id')
                $sql .= " NOT NULL PRIMARY KEY";
            $sql .= ",";
        }
        $sql = StringUtils::beforeLast(',', $sql);
        return $sql;
    }
}
?>
