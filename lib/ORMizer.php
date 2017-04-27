<?php

namespace ORMizer;

class ORMizer {

    /**
     * Transforms an object into another with database persistence capabilities.
     * (This is only to abbreviate the `persist` method).
     * @param  object $object       The object.
     * @param  string [$alias=null] An alias for the transformed object.
     * @return object The new object with persistence capabilities.
     */
    public static function _($object, $alias=null) {
        if(!is_object($object))
            return null;
        return new PersistentObject($object, $alias);
    }

    /**
     * Transforms an object into another with database persistence capabilities.
     * @param  object $object       The object.
     * @param  string [$alias=null] An alias for the transformed object.
     * @return object The new object with persistence capabilities.
     */
    public static function persist($object, $alias=null) {
        if(!is_object($object))
            return null;
        return new PersistentObject($object, $alias);
    }

    /**
     * Finds if the given alias exists and returns the corresponding object.
     * @param  string $alias Alias to find.
     * @return object ORMizer object corresponding to the alias.
     */
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

    /**
     * Loads all objects with alias into memory and places them in the global scope.
     */
    public static function disposeAliases() {
        $alias_manager = new AliasManager();
        $aliases = $alias_manager->findAll();
        $alias_manager->globalizeAliases($aliases);
    }

    /**
     * Automates the creation of a new DDBB for our application from the configuration in `Config.php`.
     * @param string $root_user      DBMS root user.
     * @param string $root_user_pass DBMS root user password.
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
