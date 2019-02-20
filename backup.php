<?php 
	$host = "localhost";
	$user = "root";
	$dbname = "farmap";
	$pass = "";
	backup_tables("$host","$user","$pass","$dbname",$tables = '*');
	function backup_tables($host,$user,$pass,$name,$tables = '*')
	{
		
		@$link = mysql_connect($host,$user,$pass);
		mysql_select_db($name,$link);
		
		//listar todas as tabelas
		if($tables == '*')
		{
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while(@$row = mysql_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		$return = "";
		//ciclo pelas tabelas
		foreach($tables as $table)
		{
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);
			
			$return.= 'DROP TABLE '.$table.';';
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		//guardar o ficheiro sql de dump
		$filename = '../../backups/db-backup-'.$name.'-'.date("d-m-Y_H-i").'.sql';
		//$arquivo = '../../../../windows/files/db-backup-'.$name.'-'.date("d-m-Y_H-i-s").'.sql';
		$handle = fopen($filename,'w+');
		//$make = fopen($arquivo, 'w+');
		if(@fwrite($handle,$return)){
			$GLOBALS["okay"] = true;
		}
		//fwrite($make, $return);
		
		@fclose($handle);
		//fclose($make);
		
	}

	if(@$GLOBALS["okay"] == true){
		if(isset($_SESSION["company"])){
			$nome_responsavel = base64_decode($_SESSION["company"][0]);
		}else{
			$nome_responsavel = "Eadfast";
		}
		$data_backup = date("d-m-Y_H\h:i");
		$query_grava = $con->query("INSERT INTO `backups`(tipo, data_backup, funcionario) VALUES('Offline', '$data_backup', '$nome_responsavel')");
	}
?> 