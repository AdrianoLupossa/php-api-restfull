<?php

require("model/config.php");
require("controller/auth.php");

class HTTP {

	private $url;
	public function GET ($url) {
		$con = conectar();
		$this->$url = $url;
		$url = explode("/", $url);
		
		$table = $url[0];
		$resource = $url[1];
		$codigo = explode("s", $table);
		$codigo = "codigo_".implode("", $codigo);

		if ($resource != "all") {
			$query = $con->prepare("SELECT * FROM `$table` WHERE $codigo = ?");
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
			echo $data;
		else:
			echo 404;
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

$dados = new HTTP();
$dados->GET("produtos/all");
"<br><br>".print_r(apache_response_headers());
// $token && $FARMID

