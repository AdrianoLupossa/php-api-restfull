<?php 

function conectar () {	
	@define("SERVER", "localhost");
	@define("DB", "eadfarm");
	@define("USER", "root");
	@define("PASS", "");

	try {
		$con = new PDO("mysql:host=".SERVER."; dbname=".DB, USER, PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
		include("offline-db.php");
		$error = $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if ($error) {
			// http_response_code(500);
		}

	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	return @$con;
}
