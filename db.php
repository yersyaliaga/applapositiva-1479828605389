<?php
	$servername = "us-cdbr-iron-east-04.cleardb.net";
	$username = "b862cbc6fec88c";
	$password = "375f6efd";
	$dbname = "ad_8b2b0ce4bc82fc0";
	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	} else {
		//echo "ALL GOOD";
	}
?>