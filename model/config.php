<?php 

function conectar () {	
	@define("SERVER", "localhost");
	@define("DB", "eadfarm");
	@define("USER", "root");
	@define("PASS", "");

	try {
		$con = new PDO("mysql:host=".SERVER."; dbname=".DB, USER, PASS);
		include("offline-db.php");
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	return @$con;
}
