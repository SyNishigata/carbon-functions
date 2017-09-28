<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$make=$_POST['make'];
	$model=$_POST['model'];
	$year=$_POST['year'];
	$unit=$_POST['unit'];
	$car = mysqli_query($conn, "SELECT * FROM library_car_models WHERE make='". $make."' AND model='" . $model . "' AND year='" . $year ."'");
	$efficiency = 0;
	
	$row = mysqli_fetch_array($car, MYSQLI_ASSOC);
	$car_id = $row["id_car_model"];
	if ($unit == 'miles') {
		$efficiency = $row["mpg"];
	}
	else {
	  $efficiency = $row["kpg"];
	}
?>

<input type="hidden" name="car_id" id="car_id" value="<?php echo $car_id ?>">
<span class="input-group-addon"> What is your car's fuel efficiency? </span>     
<input type="text" name="car_efficiency" onchange="carbon_car()" id="car_efficiency" class="form-control" value="
<?php 
	echo $efficiency;
?>">

