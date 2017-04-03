<?php

namespace ORMizer;

class MysqlAdapter extends BaseAdapter {

    //Correspondencia de tipos php-relacional en MySQL
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

    //Guarda las propiedades en una fila/registro nueva en la tabla
    public function insertRow($table, $props_array) {
        //Dividimos el array asociativo en dos arrays simples:
        //uno con las propiedades y el otro con sus valores
        foreach($props_array as $prop=>$value) {
            $properties[] = $prop;
            if(is_array($value)) //si algun valor es un array, lo convertimos a json.
                $value = $this->_json_encode($value);
            $values[] = $value;
        }
        //Preparamos la query sql
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

    public function getRow($table, $column, $value) {
        $sql = 'SELECT * FROM '. $table. ' WHERE '. $column. '=? LIMIT 1';
        $this->db_link->query($sql, array($value));
        $rows_array = $this->db_link->fetch();
        if(!$rows_array)
            return false;
        return $rows_array[0];
    }

    public function deleteRow($table, $ormizer_id) {
        $sql = 'DELETE FROM '. $table. ' WHERE ormizer_id=? LIMIT 1';
        $this->db_link->query($sql, [$ormizer_id]);
    }

    //Retorna un array con todas las filas/registros de la tabla
    public function getAll($table) {
        $sql = 'SELECT * FROM '. $table;
        $this->db_link->query($sql);
        $rows_array = $this->db_link->fetch();
        if(!$rows_array)
            return false;
        return $rows_array;
    }

    //Comprueba si existe una tabla
    public function existsTable($table_name) {
        $sql = "SHOW TABLES LIKE '". $table_name. "'";
        $this->db_link->query($sql);
        if(empty($this->db_link->fetch()))
            return false;
        else return true;
    }

    //Crea una nueva tabla relativa a un objeto en la BD
    public function createTable($table_name, $columns_array) {
        $sql = "CREATE TABLE IF NOT EXISTS ". $table_name. " (";
        $sql .= $this->castColumns($columns_array);
        $sql .= ") ENGINE = InnoDB;";
        $this->db_link->exec($sql);
    }

    public function getTableDescription($table) {
        $sql = 'DESCRIBE '. $table;
        $this->db_link->query($sql);
        $table_description = $this->db_link->fetch();
        //Este es el DBmanager exclusivo de MySQL.
        //Lo siguiente solo se hace para no utilizar las claves de array
        //que retorna la consulta de este DBMS. Transformamos en índices
        //que se corresponden con las claves. En otros adaptadores habrá que
        //formar los mismos arrays para pasarlos como parámetro a la clase
        //Column.
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

    public function findTables($pattern) {
        $sql = "SHOW TABLES LIKE '". $pattern. "'";
        $this->db_link->query($sql);
        $result = $this->db_link->fetch();
        if(empty($result))
            return false;
        else return $result;
    }

    //Crea una nueva tabla de alias en la BD
    public function createAliasTable($referenced_table) {
        $sql = "CREATE TABLE IF NOT EXISTS ". $referenced_table. "_alias (";
        $sql .= " alias VARCHAR(40) NOT NULL PRIMARY KEY,";
        $sql .= " ormizer_id CHAR(16) NOT NULL,";
        $sql .= " FOREIGN KEY (ormizer_id) REFERENCES ". $referenced_table. "(ormizer_id) ON DELETE CASCADE";
        $sql .= " ) ENGINE = InnoDB;";
        $this->db_link->exec($sql);
    }

    public function insertAlias($alias, $ormizer_id, $referenced_table) {
        $alias_table = $referenced_table. '_alias';
        $sql = 'INSERT INTO '. $alias_table. ' VALUES (?,?)';
        $this->db_link->query($sql, [$alias,$ormizer_id]);
    }

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
