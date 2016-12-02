<?php

include("db.php");
$method = $_SERVER['REQUEST_METHOD'];
mysqli_set_charset($conn,"utf8");


//Funciones para el registro de Push y envío de notificaciones Push

function registerPushNotifications($conn, $user, $device_id) {
    $query = mysqli_query($conn, "SELECT * FROM registros_push WHERE deviceid='".$device_id ."' and username='".$user ."' ");
	if (mysqli_num_rows($query) > 0){	
	    return array("success" => 1);
	} else {	    
	    $sql = "DELETE FROM registros_push where username='". $user ."'; INSERT into registros_push (deviceid, username) values ('". $device_id . "','". $user . "')"; 
	    if (!mysqli_multi_query($conn, $sql)) {
	    	return array("success" => 0, "Falla Multiquery");
	    }
	}
	return array("success" => 1);
}

function sendPushNotifications($conn,$username,$nombre, $apiKey, $appsecret, $message) {
	$idsSelect = "select username from usuarios where idUsuario in (select idAsociado from relacionemergencia where idUsuario=(select idUsuario from usuarios where username='".$username."'))";   
    $sql = "select * from registros_push WHERE username in ($idsSelect)"; 

	$result = mysqli_query($conn, $sql);

	$device_ids = array();
	if (mysqli_num_rows($result) > 0) {
	    while($row = mysqli_fetch_assoc($result)) {
	    	array_push($device_ids, $row["deviceid"]);
	    }
	} else {
	    echo "0 results";
	}	
    $device_ids_j = json_encode($device_ids);
    
    if (count($device_ids) > 0) {

		$data_string ='{"message": { "alert": "Le informamos que '.$nombre.' '. $message .'" }, "target" : {"deviceIds" :' . $device_ids_j . ' } }';
			
		$ch = curl_init('https://mobile.ng.bluemix.net/imfpush/v1/apps/' . $apiKey .'/messages');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . strlen($data_string),
		    'Accept: application/json',
		    'appSecret:'. $appsecret, 
		    'Accept-Language: en-US'      )                                                          
		);               
	
		$text1 = curl_exec($ch);
		if (FALSE === $text1) {
	       print curl_error($ch);
	       print curl_errno($ch);
	   }
	   var_dump( $text1);
	   curl_close($ch);	
	}	 

}


//Fin_Parte de funciones push





function validarIngreso($conn, $user,$pass) {
	$query = mysqli_query($conn,"Select username, CONCAT(nombres, ' ' , apellidos) as nombre from usuarios where username='".$user."' and password='".$pass."'");
	$cadena = array();
	
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_assoc($query)) {					
			array_push($cadena, array(
				"username" => $row["username"],
				"nombre" => $row["nombre"]
			));
		}
		return $cadena;					
	}else{
		//echo 'No hay nada';
		return $cadena;
	}
}

function actualizarProcurador($conn,$procuradorId,$emergenciaId){
	$query = "UPDATE emergencias SET idProcurador=$procuradorId WHERE idEmergencia = $emergenciaId";
	if (mysqli_query($conn, $query)) {
			    //echo "New record created successfully";
	} else {
		echo mysqli_error($conn);
		array_push($error, "Error: " . $sql . "<br>" . mysqli_error($conn) );
	}	
	if (count($error) > 0) {
    	echo json_encode(array("success"=>0));
    } else {
    	echo json_encode(array("success"=>1));
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

function getEmergencias($conn){
	$query=mysqli_query($conn,"select idEmergencia, idUsuario, fechaHora, latitud, longitud, estado from emergencias order by fechaHora asc");
	$emergencias = array();
	
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_assoc($query)) {					
			array_push($emergencias, array(
				"id" => $row["idEmergencia"],
				"usuario" => $row["idUsuario"],
				"fechahora" => $row["fechaHora"],
				"latitud" => $row["latitud"],
				"longitud" => $row["longitud"],
				"estado" => $row["estado"]
			));
		}
		return $emergencias;					
	}else{
		//echo 'No hay nada';
		return $emergencias;
	}
}

function insertEmergency($conn,$userId,$latitud,$longitud){
	$query = "insert into emergencias(idUsuario,fechaHora,latitud,longitud,estado) values ((select idUsuario from usuarios where username='".$userId."'),now(),$latitud,$longitud,'En Proceso');";
	if (mysqli_query($conn, $query)) {
			    //echo "New record created successfully";
	} else {
		echo mysqli_error($conn);
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
  			case '3':	  			
	  			$procuradorId = $_POST["procuradorid"];
	  			$emergenciaId = $_POST["emergenciaid"];
	  			actualizarProcurador($conn,$procuradorId,$emergenciaId);
	  			break;
	  		case '4':
	  			$user = $_POST["username"];
	  			$device_id = $_POST["deviceid"];
	  			registerPushNotifications($conn,$user,$device_id);
	  			break;
	  		case '5':
				$apikey = $_POST["apikey"];
				$appsecret = $_POST["appsecret"];
				$message = $_POST["message"];
				$username = $_POST["username"];
				$nombre = $_POST["nombre"];
    			sendPushNotifications($conn,$username,$nombre, $apikey, $appsecret, $message);
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
	  		case '2':	  			
  				$emergencias = getEmergencias($conn);  				
  				echo json_encode($emergencias);
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