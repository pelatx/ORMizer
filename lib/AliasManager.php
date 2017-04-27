<?php

namespace ORMizer;

class AliasManager {

    private $db_manager;

    /**
     * Sets the DBManager instance.
     */
    function __construct() {
        $this->db_manager = DBManager::instance();
    }

    /**
     * Finds an alias and returns the ormizer id of the aliased object.
     * @param  string  $alias Alias to find.
     * @return array Array of single element in the form class => ormizer_id. Or false if not found.
     */
    public function find($alias) {
        $alias_tables = $this->db_manager->findTables('%\_alias');
        if(!$alias_tables) {
            return false;
        }else {
            foreach($alias_tables as $table_assoc) {
                $table = $table_assoc['Tables_in_tests (%\_alias)'];
                $alias_row = $this->db_manager->getRow($table, 'alias', $alias);
                if(!$alias_row) {
                    $class = null;
                }else {
                    $class = str_replace('ormized_','',str_replace('_alias','',$table));
                    break;
                }
            }
            if($class === null) {
                return false;
            }else {
                return array($class=>$alias_row['ormizer_id']);
            }
        }
    }

    /**
     * Returns all aliases.
     * @return array Contains all the aliases (Its row array) in the form class => alias_array. Or false if nothing.
     */
    public function findAll() {
        $alias_tables = $this->db_manager->findTables('%\_alias');
        if(!$alias_tables) {
            return false;
        }else {
            foreach($alias_tables as $table_assoc) {
                $table = $table_assoc['Tables_in_tests (%\_alias)'];
                $alias_rows = $this->db_manager->getAll($table);
                if($alias_rows !== false) {
                    $class = str_replace('ormized_','',str_replace('_alias','',$table));
                    $result[$class] = $alias_rows;
                }
            }
            return $result;
        }
    }

    /**
     * Instanciates all aliased objects in the DDBB in global scope.
     * @param array $aliases Array retrieved by findAll().
     */
    public function globalizeAliases($aliases) {
        foreach($aliases as $class=>$aliases_arrays) {
            foreach($aliases_arrays as $alias_array) {
                global $$alias_array['alias'];
                $$alias_array['alias'] = @ORMizer::persist(new $class());
                $$alias_array['alias']->load($alias_array['ormizer_id']);
            }
        }
    }
}
?>
