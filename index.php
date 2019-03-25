<?php
require("model/config.php");
require("controller/auth.php");

class HTTP {

	private $resource;
	private $query;
	private $table;
	private $deny = array("nothing");
	const METHODS = array("GET", "POST", "PUT", "DELETE");
	const STATUS = array("OK" => 200, "NOT FOUND" => 404);

	private function Headers () {
		header("Content-Type: application/json; charset=UTF-8");
	}

	private function ValidURL ($url) {
		$deny = $this->deny;
		$resource = explode("/", $url);
		foreach ($this->deny as $key => $value) {
			$deny = $resource[0] != $value;
		}
		if ((substr_count($url, "/") > 0) && $deny):
			$url = explode("/", $url);
			$this->table = $url[0];
			$this->resource = $url[1];
			if (!$this->table === "empresa") {
				$this->query = explode("s", $this->table);
				$this->query = "codigo_".implode("", $this->query);
			} else {
				$this->query = "codigo_$this->table";
			}
			($this->query == "codigo_fornecedore") ? $this->query = "codigo_fornecedor" : "";
			(!($this->resource > 0)) ? $this->query = "nome" : "";
		else:
			require("views/error-403.php"); exit;
		endif;
	}


	public function GET ($url) {
		// Ex: "api/produtos/1"

		$con = conectar();
		$this->headers();

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
		if ($table === "empresa") {
			foreach ($fetchData as $obj) {
				$obj->nome = base64_decode($obj->nome);
				$obj->telefone = base64_decode($obj->telefone);
				$obj->endereco = base64_decode($obj->endereco);
			}

			$data = json_encode($obj);
		}
		
		
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
		$this->Headers();
		$this->ValidURL($url);

		$table = $this->table;
		$objArray = [];
		if (@is_object($arrayData[0])) {

			foreach ($arrayData as $data) {
				foreach ($data as $objKey => $objData) {
					$objArray[$objKey] = $objData;
				}
				$keys = array_keys($objArray);
				$numberOfKeys = count($keys);
				$keys = implode(",", $keys);
				$values = str_repeat("?,", $numberOfKeys);
				$values = explode(',', $values);
				array_pop($values);
				$values = implode(",", $values);
				$query = $con->prepare("INSERT INTO `$table`($keys) VALUES($values)");
				$index = 0;
				foreach ($objArray as $key => $data2) {
					$query->bindValue(++$index, $data2);
				}

				if ($query->execute()) {
					http_response_code(201);
					header("Location: $table/{$arrayData["nome"]}");
					header("Options: GET,PUT,DELETE");
				} else {
					http_response_code(400);
					echo 'Something went wrong, please contact the <a href="mailto:adrianolupossa@gmail.com">Webmaster</a>';
				}
			}

		} 

		else {

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
				if ($table === "login") {
					
					print_r("Index: $index"."/ data: $data\n");
					if ($index === 2) $data = base64_encode(strtolower($data));
					if ($index === 3) $data = base64_encode($data);
					if ($index === 4) $data = md5(strtolower($data));
				}

				$query->bindValue(++$index, $data);
			}
			
			if ($query->execute()) {
				http_response_code(201);
				header("Location: $table/{$arrayData["nome"]}");
				header("Options: GET,PUT,DELETE");
			} else {
				http_response_code(400);
				echo 'Something went wrong, please contact the <a href="mailto:adrianolupossa@gmail.com">Webmaster</a>';
			}

		}

	}

	public function PUT ($url, $arrayData) {
		// Ex: "api/produtos/1", {nome: "paracetamol", qtd: "2"}

		$con = conectar();
		$this->Headers();
		$this->ValidURL($url);

		$resource = $this->resource;
		$table = $this->table;
		$query = $this->query;
		if (@is_object($arrayData[0])) {
			foreach ($arrayData as $data) {
				foreach ($data as $objKey => $objData) {
					$objArray[$objKey] = $objData;
				}

				if($table === "empresa") {
					$objArray["nome"] = base64_encode($objArray["nome"]);
					$objArray["telefone"] = base64_encode($objArray["telefone"]);
					$objArray["endereco"] = base64_encode($objArray["endereco"]);
				}

				if ($table === "produtos") $query = "codigo_produto";
				$codigo = $objArray[$query];
				array_shift($objArray);
				$keys = array_keys($objArray);
				$numberOfKeys = count($keys);
				$keys = implode(",", $keys);
				$keys = str_replace(",", " = ?, ", $keys);
				$fields = $keys." = ?";
				$query = $con->prepare("UPDATE `$table` SET $fields WHERE $query = ?");
				$index = 0;
				foreach ($objArray as $key => $data2) $query->bindValue(++$index, $data2);
				$query->bindValue(++$index, $codigo);
				$query->execute();
				$found = count($query);
				if ($found > 0) {
					http_response_code(204);
					header("Resource: $table/$resource");
					header("Options: GET,DELETE");
				} else {
					http_response_code(304);
				}
			}
		} 

		else {

			if (!empty($resource)):
				if($table === "empresa") {
					$arrayData["nome"] = base64_encode($arrayData["nome"]);
					$arrayData["telefone"] = base64_encode($arrayData["telefone"]);
					$arrayData["endereco"] = base64_encode($arrayData["endereco"]);
				}

				if ($table === "produtos") $query = "codigo_produto";
				$keys = array_keys($arrayData);
				$numberOfKeys = count($keys);
				$keys = implode(",", $keys);
				$keys = str_replace(",", " = ?, ", $keys);
				$fields = $keys." = ?";
				
				$query = $con->prepare("UPDATE `$table` SET $fields WHERE $query = ?");
				$index = 0;
				foreach ($arrayData as $key => $data) $query->bindValue(++$index, $data);
				$query->bindValue(++$index, $resource);
				$query->execute();
				$found = count($query);
				
				if ($found > 0) {
					http_response_code(204);
					header("Resource: $table/$resource");
					header("Options: GET,DELETE");
				} else {
					http_response_code(304);
				}
				
			else:
				http_response_code(404);
				echo "Status: ".$this::STATUS["NOT FOUND"]. " Resource not found: $url"; exit;
				exit;
			endif;

		}
		
		
	}

	public function DELETE ($url) {
		// Ex: api/produtos/1

		$con = conectar();
		$this->Headers();
		
		$this->ValidURL($url);
		$resource = $this->resource;
		$table = $this->table;
		$query = $this->query;
		
		if ($table === "produtos") $query = "codigo_produto";
		if ($resource != "all") {
			$query = $con->prepare("DELETE FROM `$table` WHERE $query = ?");
			$query->bindValue(1, $resource);
		} else {
			$query = $con->prepare("TRUNCATE TABLE `$table`");
		}
			
		if ($query->execute()) {
			http_response_code(204);
			$message = array("Resource" => "$table/$resource", "Status" => "DELETED");
			echo json_encode($message);
		} else {
			http_response_code(304);
			header("Location: $url");
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

	if (isset($_POST["data"])):
		$data = json_decode($_POST["data"]);
		$data_array = array();
		foreach ($data as $key => $value) {
			$data_array[$key] = $value;
		}
		$dados->POST($url, $data_array);

	else:
		$dados->POST($url, $_POST);

	endif;

} else if(isset($_GET["url"]) && METHOD === "PUT") {
	$url = $_GET["url"];
	$dados = new HTTP();

	parse_str(file_get_contents('php://input'), $_PUT);
	$data = implode(" ", $_PUT);
	$data = explode("----", $data);
	array_pop($data);
	$data = str_replace('"data"', "", $data[0]);
	$data = json_decode($data);
	$data_array = array();
	foreach ($data as $key => $value) {
		$data_array[$key] = $value;
	}
	$dados->PUT($url, $data_array);

} else if (isset($_GET["url"]) && METHOD === "DELETE") {
	$url = $_GET["url"];
	$dados = new HTTP();
	$dados->DELETE($url);

} else {
	require('views/error-403.php');
}