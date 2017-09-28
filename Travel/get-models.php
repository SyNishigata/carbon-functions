<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$make=$_POST['selected_make'];

	$models = mysqli_query($conn, "SELECT DISTINCT model FROM library_car_models WHERE make='". $make."'");
?>

<option>Model</option>
<?php
	while($row = mysqli_fetch_array($models, MYSQLI_ASSOC)) {
		echo '<option value="' . $row["model"] . '" ' . '>' . $row["model"] . '</option>';
	}
?>
