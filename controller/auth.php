<?php
session_start();
define("METHOD", $_SERVER["REQUEST_METHOD"]);

class AUTH {
	
	private $status;
	private function authUser () {
		$con = conectar();
		
		@$username = $_SESSION["dados_login"][0];
		@$permission = $_SESSION["dados_login"][1];
		
		$query_usuario = $con->prepare("SELECT * FROM `login` WHERE nome = ? and nivel_acesso = ?");
		$query_usuario->bindValue(1, $username);
		$query_usuario->bindValue(2, base64_encode($permission));
		$query_usuario->execute();
		$foundData = $query_usuario->rowCount();
		if (base64_decode($username) === "admin") $foundData = 1;

		return $foundData;
	}

	private function authFarm () {
		$con = conectar();

		@$farmacia = $_SESSION["company"][0];
		@$telefone = $_SESSION["company"][1];

		$query_farmacia = $con->prepare("SELECT * FROM `empresa` WHERE nome = ? and telefone = ?");
		$query_farmacia->bindValue(1, $farmacia);
		$query_farmacia->bindValue(2, $telefone);
		$query_farmacia->execute();
		$found = $query_farmacia->rowCount();
		return $found;
	}

	public function Status () {
		$authUser = $this->authUser();
		$authFarm = $this->authFarm();
		if (!($authUser > 0 && $authFarm > 0)) {
			require("../api/views/error-511.php"); 
			header("location: ../views/login.html?error=3&type=login_authenticate");
			exit;
		}

	}
}

$http = new AUTH();
$http->Status();

