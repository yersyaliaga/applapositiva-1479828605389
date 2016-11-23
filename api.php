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

function getActividades($conn){
	$query=mysqli_query($conn,"select nomActividad, descripcion, orden from actividades order by orden asc");
	$actividades = array();
	
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_assoc($query)) {					
			array_push($actividades, array(
				"nombre" => $row["nomActividad"],
				"descripcion" => $row["descripcion"],
				"orden" => $row["orden"]
			));
		}
		return $actividades;					
	}else{
		//echo 'No hay nada';
		return $actividades;
	}
}

function insertEmergency($conn,$userId,$latitud,$longitud){
	$query = "Insert into emergencias(idUsuario,fechaHora,latitud,longitud,estado) values(".$userId.",now(),".$latitud.",".$longitud.",'En Proceso')";
	if (mysqli_query($conn, $query)) {
			    echo "New record created successfully";
	} else {		
		array_push($error, "Error: " . $sql . "<br>" . mysqli_error($conn) );
	}	
	if (count($error) > 0) {
    	echo json_encode(array("success"=>0));
    } else {
    	echo json_encode(array("success"=>1));
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
	  		case '2':
	  			$userId = $_POST["userid"];
	  			$latitud = $_POST["latitud"];
	  			$longitud = $_POST["longitud"];
	  			insertEmergency($conn,$userId,$latitud,$longitud);
	  			break;
	  	}
	  }
    break;
  case 'PUT':
  	echo "Method not allowed";
    break;
  case 'GET':
  	if (isset($_GET["procedure"])) {
	  	$procedure = $_GET["procedure"];	  	
	  	switch($procedure){
	  		case '1':	  			
  				$actividades = getActividades($conn);  				
  				echo json_encode($actividades);
	  			break;
	  	}
	  }
  	//echo "Method not allowed";
  	break;
  case 'DELETE':
  	echo "Method not allowed";
    break;
}

?>