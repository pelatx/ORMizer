<?php

namespace ORMizer;

/*
* Auxiliary class to create a new database (only MySQL for now) for the application.
*/
class DBSetup{

    private $db_link;

    /**
     * Sets connection with DDBB.
     * @param string $dbms           DBMS in accordance with PDO.
     * @param string $host           DBMS host.
     * @param string $root_user      DBMS root user.
     * @param string $root_user_pass DBMS root user pass.
     */
    function __construct($dbms, $host, $root_user, $root_user_pass) {
        $this->db_link = new PDOWrapper (
            $dbms,
            $host,
            $root_user,
            $root_user_pass
        );
    }

    /**
     * Creates a new DDBB.
     * @param string $db_name New DDBB name.
     */
    public function createAppDb($db_name) {
        $this->db_link->exec('CREATE DATABASE IF NOT EXISTS '.$db_name.' CHARACTER SET UTF8 COLLATE UTF8_GENERAL_CI;');
    }

    /**
     * Creates a new user and grants ORMizer necessary rights over a database.
     * @param string $db_name DDBB name.
     * @param string $user    New user.
     * @param string $passwd  Password for the new user.
     */
    public function createAppUser($db_name, $user, $passwd) {
        $this->db_link->exec("GRANT SELECT, INSERT, DELETE, UPDATE, CREATE
										ON ".$db_name .".* TO '".$user."'@'localhost'
										IDENTIFIED BY '". $passwd. "'");
    }

    /**
     * Check if a database already exists.
     * @param  string  $db_name DDBB name.
     * @return boolean True if exists.
     */
    public function existsDB($db_name) {
        $this->db_link->query('SHOW DATABASES LIKE \''. $db_name. '\'');
        if(empty($this->db_link->fetch()))
            return false;
        return true;
    }
}
?>
