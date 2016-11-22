<?php

include("db.php");
$method = $_SERVER['REQUEST_METHOD'];
mysqli_set_charset($conn,"utf8");

function validarIngreso($conn, $user,$pass) {
	$query = mysqli_query($conn,"Select username from usuarios where username='".$user."' and password='".$pass."'");
	$cadena = array();
	
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_assoc($query)) {					
			array_push($cadena, array(
				"username" => $row["username"]
			));
		}
		return $cadena;					
	}else{
		//echo 'No hay nada';
		return $cadena;
	}
}


switch ($method) {
  case 'POST':
	  if (isset($_POST["procedure"])) {
	  	$procedure = $_POST["procedure"];	  	
	  	switch($procedure){
	  		case '1':
	  			$user = $_POST["username"];
	  			$pass = $_POST["password"];
  				$cadena = validarIngreso($conn, $user,$pass);  				
  				echo json_encode($cadena);
	  			break;
	  	}
	  }
    break;
  case 'PUT':
  	echo "Method not allowed";
    break;
  case 'GET':
  	echo "Method not allowed";
  	break;
  case 'DELETE':
  	echo "Method not allowed";
    break;
}

?>