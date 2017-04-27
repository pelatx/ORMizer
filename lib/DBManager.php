<?php

namespace ORMizer;

/*
* Frontend for database connection adapters.
*/
class DBManager {

    /**
     * Returns the DDBB adapter object according to Config.php.
     * @return object The adapter object.
     */
    public static function instance() {
        $adapter_class = 'ORMizer\\'. ucfirst(Config::DBMS). 'Adapter';
        return $adapter_class::instance();
    }
}
?>
