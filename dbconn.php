<?php

class DbConn extends PDO {
		//private $connStr = 'mysql:host=192.168.1.75;dbname=dogadopt;';
		private $connStr = 'mysql:host=britches.no-ip.org;dbname=neogeo;';
		private $username = 'yi';
 		private $password = 'spinners';
 		private $fetch_mode = PDO::FETCH_ASSOC;
 		private $ret_type = "object";
 		private $driver_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
 		private $last_id;

 		// constructor
 		public function __construct() {
			
 			// Temporarily change the PHP exception handler while we . . .
			set_exception_handler(array(__CLASS__, 'exception_handler'));
			
			// . . . create a PDO object
			//parent::__construct($this->connStr . $database, $this->username, $this->password, $this->driver_options);
			parent::__construct($this->connStr, $this->username, $this->password, $this->driver_options);
			parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// Change the exception handler back to whatever it was before
			restore_exception_handler();
 		}
 		
		public function setPDOFetchMode($fetch_mode) {
			$this->fetch_mode = $fetch_mode;	
		}
 		public function setReturnType($type) {
 			$this->ret_type = $type;
 		}
 		// select query
 		public function selectQuery($sql, $parms) {
 			$statement = $this->prepare($sql);
			//var_dump($sql);
 			//if (sizeof($parms) > 0) {
	 		//	$x = 0;
	 		//	for ($i = 0; $i < sizeof($parms); $i++) {
	 		//		$statement->bindParam($x++, $parms[$i]);
	 		//	}
 			//}
 			//$results = $statement->fetchAll(PDO::FETCH_ASSOC);
			$statement->execute($parms);
 			$results = $statement->fetchAll(PDO::FETCH_OBJ);

			//var_dump($results);
			if ($this->ret_type == "json")
	 			return json_encode($results);
	 		else 
	 			return $results;
 		}
 		public function insupQuery($sql, $parms) {
			//set_exception_handler(array(__CLASS__, 'insup_exception_handler'));
		
 			$statement = $this->prepare($sql);
			$statement->execute($parms);
			$this->last_id = $this->lastInsertId();

			//restore_exception_handler();
			return array("success" => true, "msg" => "data saved.", "id" => array($this->last_id));
		}

		public function getLastId() {
			return $this->last_id;
		}
		public function queryOneColumnNoJson($sql, $parms, $column) {
			$statement = $this->prepare($sql);
			$statement->execute($parms);
 			$results = $statement->fetchAll(PDO::FETCH_OBJ);
 			if ($results != null) {
 				return $results[0]->$column;
 			}
 			return "";
		}

		public static function exception_handler($exception) {
			// Output the exception details
			die('Uncaught exception: '. $exception->getMessage());
		}

		public static function insup_exception_handler($exception) {
			return array("success" => false, "msg" => "an error occurred.", "id" => array());
		}
	}
 	
?>
