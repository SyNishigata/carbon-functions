<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "carbon_challenge";
	$password = "changeme";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$row_id=$_POST['row_id'];
	
	$sql = "DELETE FROM user_emissions_travel_car WHERE id='$row_id'";
	
	/* Check if query processed correctly */
	if ($conn->query($sql) === TRUE) {
		header("Location: /carbon-functions/Travel/emissions-car.php");
		echo "Successfully deleted car \n";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
?>

