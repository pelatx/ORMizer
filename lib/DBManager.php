<?php

namespace ORMizer;

/*
* Frontend para los adaptadores de conexión a base de datos.
*/
class DBManager {

    public static function instance() {
        $adapter_class = 'ORMizer\\'. ucfirst(Config::DBMS). 'Adapter';
        return $adapter_class::instance();
    }
}
?>
