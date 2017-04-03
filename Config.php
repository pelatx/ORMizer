<?php

namespace ORMizer;

class Config {

	//Database options
	const DBMS = 'mysql'; // Must be the PDO string for the Database Management System
	const DBMS_HOST = 'localhost';
    const DBMS_PORT = ''; // Leave empty for default port
	const APP_DB = 'ormzr_tests';
    const APP_DB_USER = 'ormzr_tests_user';
    const APP_DB_USER_PASS = 'ormzr_tests_user_pass';
}
?>
