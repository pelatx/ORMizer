<?php

namespace ORMizer;


/*
* Métodos para crear una nueva base de datos (MySQL) para la aplicación.
*/


class DBSetup{

    private $db_link;

    function __construct($dbms, $host, $root_user, $root_user_pass) {
        $this->db_link = new PDOWrapper (
            $dbms,
            $host,
            $root_user,
            $root_user_pass
        );
    }

    public function createAppDb($db_name) {
        $this->db_link->exec('CREATE DATABASE IF NOT EXISTS '.$db_name.' CHARACTER SET UTF8 COLLATE UTF8_GENERAL_CI;');
    }

    public function createAppUser($db_name, $user, $passwd) {
        $this->db_link->exec("GRANT SELECT, INSERT, DELETE, UPDATE, CREATE
										ON ".$db_name .".* TO '".$user."'@'localhost'
										IDENTIFIED BY '". $passwd. "'");
    }

    public function existsDB($db_name) {
        $this->db_link->query('SHOW DATABASES LIKE \''. $db_name. '\'');
        if(empty($this->db_link->fetch()))
            return false;
        return true;
    }
}
?>
