<?php

namespace ORMizer;

class ORMizer {

    public static function _($object, $alias=null) {
        if(!is_object($object))
            return null;
        return new PersistentObject($object, $alias);
    }

    public static function persist($object, $alias=null) {
        if(!is_object($object))
            return null;
        return new PersistentObject($object, $alias);
    }

    public static function alias($alias) {
        $alias_manager = new AliasManager();
        $result = $alias_manager->find($alias);
        if(!$result)
            return null;
        $class = key($result);
        $ormizer_id = $result[$class];
        $object = new PersistentObject(new $class(), null);
        $object->load($ormizer_id);
        return $object;
    }

    public static function disposeAliases() {
        $alias_manager = new AliasManager();
        $aliases = $alias_manager->findAll();
        $alias_manager->globalizeAliases($aliases);
    }
    /*
	public static function objectifyTable($table) {
		if(!is_string($table))
			return null;
		return new TableObject($table);
	}
*/
    public static function dbSetup($root_user, $root_user_pass) {
        $setup = new DBSetup(
            Config::DBMS,
            Config::DBMS_HOST,
            $root_user,
            $root_user_pass
        );
        if(!$setup->existsDB(Config::APP_DB)) {
            $setup->createAppDb(Config::APP_DB);
            $setup->createAppUser(
                Config::APP_DB,
                Config::APP_DB_USER,
                Config::APP_DB_USER_PASS
            );
        }
    }
}
?>
