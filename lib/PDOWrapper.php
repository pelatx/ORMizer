<?php

namespace ORMizer;

use \PDO;
//use \PDOException;

class PDOWrapper {

	private $db_link;
	private $current_sth;

	function __construct($dbms, $host, $port, $user, $password, $db=null) {
		if($db == null) {
			$dsn = $dbms. ':host='. $host. ';port='. $port;
		}else {
			$dsn = $dbms. ':host='. $host. ';port='. $port. ';dbname='. $db;
		}
		try {
			$this->db_link = new PDO($dsn, $user, $password);
			$this->db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
    		throw new \Exception('ORMizer\PDOWrapper: error creating DDBB link.');
		}
	}

	public function query($sql, $params_array=array()) {
		try {
    		$this->current_sth = $this->db_link->prepare($sql);
    		if(empty($params_array)) {
				$this->current_sth->execute();
			} else {
				$this->current_sth->execute($params_array);
			}
		} catch (PDOException $e) {
    		throw new \Exception('ORMizer\PDOWrapper: query error.');
		}
	}

	public function exec($sql) {
		try {
    		$this->current_sth = $this->db_link->exec($sql);
		} catch (PDOException $e) {
    		throw new \Exception('ORMizer\PDOWrapper: execution error.');
		}
	}

	public function fetch() {
		try {
			$rows_array = $this->current_sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
    		throw new \Exception('ORMizer\PDOWrapper: fetch error.');
		}
		if(empty($rows_array))
			return false;
		return $rows_array;
	}

}
?>
