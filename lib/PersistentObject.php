<?php

namespace ORMizer;

use \ReflectionObject;

/**
 * ORMizer core class.
 * An object of this class is returned to replace a simple object passed to ORMizer.
 * It clones the original object and adds all necessary ORMizer functionality.
 **/
class PersistentObject {

    private $ormizer_id;
    private $ormizer_id_length;
    private $ormizer_alias;
    private $db_table_name;
    private $object;
    private $reflected_object;
    private $db_manager;
    private $db_columns = array();
    private $token_gen;

    /**
     * Sets the ORMized object initial properties.
     * @param object &$object Object to be pesisted.
     * @param string $alias   An alias for the transformed object.
     */
    function __construct(&$object, $alias) {
        $this->object = $object;
        $this->reflected_object = new ReflectionObject($object);
        $this->token_gen = new TokenGenerator();
        $this->db_manager = DBManager::instance();
        $this->db_table_name = 'ormized_'. str_replace('\\', '_', get_class($object));
        $this->ormizerIdConfig();
        if($alias !== null) {
            $v = new Validator(1, 40, 'alphanumeric');
            if($v->validate($alias)) {
                $alias_manager = new AliasManager();
                if(!$alias_manager->find($alias)) {
                    $this->ormizer_alias = $alias;
                }else {
                    throw new \Exception('ORMizer\PersistentObject: duplicated alias is not allowed. Object not created.');
                }
            }else {
                throw new \Exception('ORMizer\PersistentObject: wrong lentgh or bad characters in alias.');
            }
        }
        // We create the columns to be able to set types and what is needed in the table of the DDBB.
        foreach($this->getAll() as $property=>$value) {
            $column = new Column();
            $column->name = $property;
            //Casting por defecto
            if($column->name === 'ormizer_id') {
                $column->type = 'char';
                $column->max_length = $this->ormizer_id_length;
            }else {
                if(is_int($value)) {
                    $column->type = 'int';
                } elseif(is_float($value)) {
                    $column->type = 'float';
                } elseif(is_bool($value)) {
                    $column->type = 'boolean';
                } elseif($value instanceof \DateTime) {
                    $column->type = 'datetime';
                } else {
                    $column->type = "varchar";
                    $column->max_length = 255;
                }
            }
            $this->db_columns[] = $column;
        }
    }

    /**
     * Gets a property value of the initial object passed to ORMizer.
     * @param  string $property Property name.
     * @return any    Property value.
     */
    function __get($property) {
        if($property === 'ormizer_id')
            return $this->ormizer_id;
        if($this->reflected_object->hasProperty($property)) {
            $prop = $this->reflected_object->getProperty($property);
            $prop->setAccessible(true);
            return $prop->getValue($this->object);
        }else {
            throw new \Exception('ORMizer\PersistentObject: trying to access non existing property.');
        }
    }

    /**
     * Sets a property value of the initial object passed to ORMizer.
     * @param string $property Property name.
     * @param any    $value    New property value.
     */
    function __set($property, $value) {
        if($property === 'ormizer_id') {
            if(strlen($value) <= $this->ormizer_id_length) {
                $this->ormizer_id = $value;
            }else {
                throw new \Exception('ORMizer\PersistentObject: trying to set `ormized_id` with invalid string length.');
            }
            return;
        }
        if($this->reflected_object->hasProperty($property)) {
            $prop = $this->reflected_object->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($this->object, $value);
        }else {
            throw new \Exception('ORMizer\PersistentObject: trying to access non existing property.');
        }
    }

    /**
     * Calls a method of the initial object passed to ORMizer.
     * @param  string $method Method name.
     * @param  array  $args   Arguments array passed to method.
     * @return any    The return of the method executed.
     */
    function __call($method, $args) {
        if($this->reflected_object->hasMethod($method) &&
           $this->reflected_object->getMethod($method)->isPublic()) {
            return $this->reflected_object->getMethod($method)->invokeArgs($this->object, $args);
        }else {
            throw new \Exception('ORMizer\PersistentObject: trying to access non existing or not public method.');
        }
    }

    /**
     * Sets the properties of the original object (plus the ORMizer id)
     * to be displayed in the debug information (var_dump, for example).
     * @return array Associative array containing properties => values.
     */
    function __debugInfo() {
        return $this->getAll();
    }

    /**
     * Returns an array with all the properties of the original object plus the ORMizer id.
     * @return array Associative array containing properties => values.
     */
    public function getAll() {
        $return['ormizer_id'] = $this->ormizer_id;
        $props = $this->reflected_object->getProperties();
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            // Prevent the model to overrides ormizer_id
            if($prop->getName() !== 'ormizer_id') {
                $value = $prop->getValue($this->object);
                if($value !== 'ormizer_excluded')
                    $return[$prop->getName()] = $value;
            }

        }
        return $return;
    }

    /**
     * Saves the current values of the original properties in the database.
     * Checks model-table integrity first. If the model has been changed,
     * it will save only the existing properties in the table.
     */
    public function save() {
        $properties = $this->getAll();
        $this->db_manager->createTable($this->db_table_name, $this->db_columns);
        $table_description = $this->db_manager->getTableDescription($this->db_table_name);

        $consistent_props = array();
        foreach($table_description as $field_description) {
            foreach($this->db_columns as $column) {
                if($column->name === $field_description['field'])
                    if($properties[$column->name] instanceof \DateTime)
                        $properties[$column->name] = $properties[$column->name]->format('Y-m-d H:i:s');
                $consistent_props[$column->name] = $properties[$column->name];
            }
        }

        if($this->isSaved()) {
            $this->db_manager->updateRow($this->db_table_name, $consistent_props);
        }else {
            $this->db_manager->insertRow($this->db_table_name, $consistent_props);
            if($this->ormizer_alias !== null) {
                $this->db_manager->createAliasTable($this->db_table_name);
                $this->db_manager->insertAlias(
                    $this->ormizer_alias, $this->ormizer_id, $this->db_table_name
                );
            }
        }
    }

    /**
     * Checks if an object is already saved in the database.
     * @return boolean True if it is saved.
     */
    public function isSaved() {
        if(!$this->db_manager->existsTable($this->db_table_name))
            return false;
        if(!$this->db_manager->getRow($this->db_table_name, 'ormizer_id', $this->ormizer_id))
            return false;
        return true;
    }

    /**
     * Checks existence of a table in the database.
     * @return boolean True if it exists.
     */
    public function existsTable() {
        if($this->db_manager->existsTable($this->db_table_name))
            return true;
        return false;
    }

    /**
     * Creates a table reflecting the class model of the given object.
     */
    public function createTable() {
        $this->db_manager->createTable($this->db_table_name, $this->db_columns);
    }

    /**
     * Loads property values from the database into the object,
     * from the given ORMizer id.
     * @param  string  $ormizer_id ORMizer id.
     * @return boolean True if success.
     */
    public function load($ormizer_id) {
        if($this->db_manager->existsTable($this->db_table_name)) {
            $row = $this->db_manager->getRow($this->db_table_name, 'ormizer_id', $ormizer_id);
            if(!$row) return false;
            $table_description = $this->db_manager->getTableDescription($this->db_table_name);
            $row = $this->castFetchedRow($row, $table_description);
            foreach($row as $field=>$value) {
                $this->{$field} = $value;
            }
        }
        return true;
    }

    /**
     * Deletes a row (an ORMized object) from the database.
     */
    public function delete() {
        $this->db_manager->deleteRow($this->db_table_name, $this->ormizer_id);
    }

    /**
     * Sets how the PHP types of the object properties will be translated into
     * database types. This only makes sense before the first time that an object
     * of a particular model is saved, since after that the table will not be modified.
     * @param  string  $property          Property name.
     * @param  string  $type              Type you want in the database.
     * @param  integer [$max_length=null] Length of database field.
     * @param  integer [$decimals=null]   How many decimals in numeric fields.
     * @return object  This object. To make posible chains of several method calls.
     */
    public function setCasting($property, $type, $max_length=null, $decimals=null) {
        if(!$this->db_manager->existsTable($this->db_table_name)) {

            if($max_length !== null && !is_int($max_length))
                throw new \Exception('ORMizer\PersistentObject: `max_length` must be integer.');

            if($this->reflected_object->hasProperty($property) || $property === 'ormizer_id') {
                foreach($this->db_columns as $column) {
                    if($column->name === $property) {
                        if(array_key_exists($type, $column->type_mapping)) {
                            $column->type = $type;
                        }else {
                            throw new \Exception('ORMizer\PersistentObject: invalid column type mapping.');
                        }
                        if($column->type_mapping[$type] === $column::DECIMAL) {
                            if($decimals !== null && !is_int($decimals))
                                throw new \Exception('ORMizer\PersistentObject: `decimals` must be integer.');
                            $column->max_length = array($max_length, $decimals);
                        }else {
                            $column->max_length = $max_length;
                            //Para controlar en `__set()` que no se pueda insertar más caracteres
                            //de los que la BD permite y las `id` sean guardadas a medias.
                            if($property === 'ormizer_id') {
                                $column->type = 'char';
                                $this->token_gen->regen($max_length);
                                $this->ormizer_id = $this->token_gen->getToken();
                                $this->ormizer_id_length = $max_length;
                            }
                        }
                    }
                }
            }else {
                throw new \Exception('ORMizer\PersistentObject: trying to cast non existing property.');
            }
        }
        return $this;
    }

    /**
     * Initialize an ORMizer id for the transformed object.
     */
    private function ormizerIdConfig() {
        if(!$this->db_manager->existsTable($this->db_table_name)) {
            $this->ormizer_id_length = 16;
            $this->ormizer_id = $this->token_gen->getToken();
        }else {
            $table_description = $this->db_manager->getTableDescription($this->db_table_name);
            foreach($table_description as $column) {
                if($column['field'] === 'ormizer_id') {
                    $this->ormizer_id_length = $column['max_length'];
                    $this->ormizer_id = $this->token_gen->getToken($column['max_length']);
                    break;
                }
            }
        }
    }

    /**
     * Returns an array with all saved objects of the object class.
     * Properly reestablishes the DDBB types to PHP types.
     * @return array Array of saved objects. Or false if no results.
     */
    public function getSavedInstances() {
        if(!$this->db_manager->existsTable($this->db_table_name))
            return false;
        $rows = $this->db_manager->getAll($this->db_table_name);
        $rows = $this->castFetchedRows($rows);
        return $rows;
    }

    /**
     * Makes a proper reverse type conversion of a row from the database.
     * @param  array $row               Row associative array.
     * @param  array $table_description Table description array (from the `getTableDescription` method).
     * @return array Row new array.
     */
    private function castFetchedRow($row, $table_description) {
        for($i = 0; $i < count($row); $i++) {
            $field = $table_description[$i]['field'];
            $type = $table_description[$i]['type'];
            $dbm = $this->db_manager;
            foreach($dbm->type_mapping as $relational => $php) {
                if ($type === $relational) {
                    if ($php === $dbm::STRING) {
                        // If value is json string, decode to array
                        $array_value = json_decode($row[$field], true);
                        if ($type !== 'char' && json_last_error() === JSON_ERROR_NONE) {
                            // Prevent json_decode type changes
                            if(is_array($array_value)) {
                                $row[$field] = $array_value;
                            }else {
                                $row[$field] = ''. $row[$field];
                            }
                        }
                        // Cast numeric values
                    }elseif($php === $dbm::INTEGER || $php === $dbm::DECIMAL) {
                        $row[$field] += 0;
                        // Cast datetime to php datetime object
                    }elseif ($php === $dbm::DATETIME) {
                        $row[$field] = \DateTime::createFromFormat('Y-m-d H:m:s', $row[$field]);
                    }
                }
            }
        }
        return $row;
    }

    /**
     * Makes a proper reverse type conversion of a set of rows from the database.
     * @param  array $rows Bidimensional array of rows.
     * @return array Bidimensinal new array of rows.
     */
    private function castFetchedRows($rows) {
        $table_description = $this->db_manager->getTableDescription($this->db_table_name);
        // Test if we got a single row or an array of rows
        $bidimensional = false;
        foreach($rows as $row) {
            if(is_array($row)) $bidimensional =  true;
        }

        if($bidimensional) {
            $updated_rows = array();
            foreach($rows as $row) {
                $updated_rows[] = $this->castFetchedRow($row, $table_description);
            }
        }else {
            $updated_rows = $this->castFetchedRow($rows, $table_description);
        }
        return $updated_rows;
    }
}
?>
