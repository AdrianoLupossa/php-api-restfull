<?php
require("model/config.php");
require("controller/auth.php");

class HTTP {

	private $resource;
	private $query;
	private $table;
	private $deny = array("login", "empresa");
	const METHODS = array("GET", "POST", "PUT", "DELETE");
	const STATUS = array("OK" => 200, "NOT FOUND" => 404);

	private function ValidURL ($url) {
		$deny = $this->deny;
		$resource = explode("/", $url);
		$deny = $resource[0] != $deny[0] && $resource[0] != $deny[1];
		if ((substr_count($url, "/") > 0) && $deny):
			$url = explode("/", $url);
			$this->table = $url[0];
			$this->resource = $url[1];
			$this->query = explode("s", $this->table);
			$this->query = "codigo_".implode("", $this->query);
			(!($this->resource > 0)) ? $this->query = "nome" : "";
		else:
			require("views/error-403.php"); exit;
		endif;
	}

	public function GET ($url) {
		$con = conectar();
		header("Content-Type: application/json; charset=UTF-8");

		$this->ValidURL($url);
		$resource = $this->resource;
		$table = $this->table;
		$query = $this->query;

		if ($resource != "all") {
			$query = $con->prepare("SELECT * FROM `$table` WHERE $query = ?");
			$query->bindValue(1, $resource);
			$query->execute();
			$fetchData = $query->fetch(PDO::FETCH_OBJ);
		} else {
			$query = $con->prepare("SELECT * FROM `$table`");
			$query->execute();
			$fetchData = $query->fetchAll(PDO::FETCH_OBJ);
		}

		$found = $query->rowCount();
		$data = json_encode($fetchData);
		
		if ($found > 0):
			http_response_code(200);
			header("Resource: $table/$resource");
			header("Options: PUT,DELETE");
			echo $data; exit;
		else:
			http_response_code(404);
			echo "Status: ".$this::STATUS["NOT FOUND"]. " Resource not found: $url"; exit;
		endif;
	}

	public function POST ($url, $arrayData) {
		// Ex: "api/produtos/", {nome: "paracetamol", qtd: "2"}
		$con = conectar();
		header("Content-Type: application/json; charset=UTF-8");
		
		// print_r($arrayData);
		$this->ValidURL($url);
		$table = $this->table;
		
		$keys = array_keys($arrayData);
		$numberOfKeys = count($keys);
		$keys = implode(",", $keys);
		$values = str_repeat("?,", $numberOfKeys);
		$values = explode(',', $values);
		array_pop($values);
		$values = implode(",", $values);
		
		$query = $con->prepare("INSERT INTO `$table`($keys) VALUES($values)");
		$index = 0;
		foreach ($arrayData as $key => $data) {
			$query->bindValue(++$index, $data);
		}

		if ($query->execute()) {
			http_response_code(201);
			header("Location: $table/{$arrayData["nome"]}");
			header("Options: GET,PUT,DELETE");
			echo 201;
		} else {
			http_response_code(500);
			echo 'Something went wrong, please contact the <a href="mailto:adrianolupossa@gmail.com">Webmaster</a>';
		}

	}

	public function PUT ($url, $json) {
		// Ex: "api/produtos/1" && {}
	}

	public function DELETE ($url) {
		// Ex: api/produtos/1
		$con = conectar();
		header("Content-Type: application/json; charset=UTF-8");
		
		// print_r($arrayData);
		$this->ValidURL($url);
		$resource = $this->resource;
		$table = $this->table;
		$query = $this->query;
		
		if ($resource != "all") {
			$query = $con->prepare("DELETE FROM `$table` WHERE $query = ?");
			$query->bindValue(1, $resource);
		} else {
			$query = $con->prepare("TRUNCATE TABLE `$table`");
		}
			
		if ($query->execute()) {
			http_response_code(200);
			// header("Location: $table/{$arrayData["nome"]}");
			echo "Resource: '$table/$resource' DELETED!";
		} else {
			http_response_code(500);
			echo 'Something went wrong, please contact the <a href="mailto:adrianolupossa@gmail.com">Webmaster</a>';
		}

	}

}
	
if (isset($_GET["url"]) && METHOD === "GET") {
	$url = $_GET["url"];
	$dados = new HTTP();
	$dados->GET($url);

} else if (isset($_GET["url"]) && $_POST) {
	$url = $_GET["url"];
	$dados = new HTTP();
	$dados->POST($url, $_POST);

} else if(isset($_GET["url"]) && METHOD === "PUT") {
	http_response_code(200);
	echo METHOD;
	$_SERVER["REQUEST_METHOD"] = "POST";
	print_r($_SERVER);
	// print_r($_GET);
	print_r($_POST);
	// print_r($_PUT);

	// print_r($_SERVER["REQUEST_URI"]);

} else if (isset($_GET["url"]) && METHOD === "DELETE") {
	$url = $_GET["url"];
	$dados = new HTTP();
	$dados->DELETE($url);

} else {
	require('views/error-403.php');
}
// echo __FILE__;