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
		header("content-type: application/json; charset=UTF-8");

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
			echo $data; exit;
		else:
			http_response_code(404);
			echo "Status: ".$this::STATUS["NOT FOUND"]. " Resource not found: $url"; exit;
		endif;
	}

	public function POST ($url, $JSON) {
		// Ex: "api/produtos", {nome: "paracetamol", qtd: "2"}
	}

	public function PUT ($url, $JSON) {
		// Ex: "api/produtos/1" && {}
	}

	public function DELETE ($url) {
		// Ex: api/produtos/1
	}

}

if (isset($_GET["url"])) {
	$url = $_GET["url"];
	$dados = new HTTP();
	$dados->GET($url);
} else if ($_POST) {
	$url = $_GET["url"];
} else {
	require('views/error-403.php');
}
// echo __FILE__;
// $token && $FARMID

