<?php

 class dao {


	function __construct() {

		$this->connect();

	}

	function connect() {

		try {

			$conn = new PDO("mysql:host=" . app_config::DB_HOST . ";dbname=" . app_config::DB_NAME, app_config::DB_USER, app_config::DB_PASSWORD);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		} catch(PDOException $e){
			echo 'ERROR: ' . $e->getMessage();
		}

		$this->db = $conn;

	}

	function insert_id() {

		if (isset($this->db)) {

		 return $this->db->lastInsertId();

		}

	}

	function query($_sql=null, $_type='SelectAll', $_params=null) {

		if (!isset($this->db)) {
			$this->connect();
		}

		if (isset($_sql)) {
			try {

				switch ($_type) {

					case 'SelectAll' :

					  $stmt = $this->db->prepare($_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					  if (!isset($_params)) { $_params = array(); }
					  $stmt->execute($_params);
					  $result = $stmt->fetchAll();

					break;

					case 'SelectOne' :

					  $stmt = $this->db->prepare($_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					  if (!isset($_params)) { $_params = array(); }
					  $stmt->execute($_params);
					  $result = $stmt->fetch();

					break;

					case 'Insert' :
					case 'Update' :
					case 'Delete' :

					  $stmt = $this->db->prepare($_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					  if (!isset($_params)) { $_params = array(); }
					  $stmt->execute($_params);
					  $result = $stmt->rowCount();

					break;

				}

				if (isset($result)) {

					  if (isset($die_after)) {

					  }

					return $result;
				}

			} catch (PDOException $e) {
				var_dump($_params, $_sql);
				echo "<br /><br />";
				echo 'ERROR: ' . $e->getMessage();
				return false;
			}

		}

		return true;

	}


 }

 $this->dao = new dao();

?>
