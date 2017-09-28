<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$travel_type=$_POST['travel_type'];
	$row_id=$_POST['row_id'];
	$row_id2=$_POST['row_id2'];
	
	/* Fetch a specific travel emission record for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_travel_$travel_type WHERE id='$row_id'");
	$travel_record = mysqli_fetch_array($result, MYSQLI_ASSOC);
?>

<span><strong> Editing <?php echo $travel_type;?> route emission record: 
	From: <?php echo $travel_record['route_from'];?>, To: <?php echo $travel_record['route_to'];?> </strong></span>
<br><br>
<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
<input type="hidden" name="<?php echo $travel_type;?>" id="<?php echo $travel_type;?>" value="">
<div style="display:none" name="travel_record" id="travel_record"><?php echo json_encode($travel_record); ?></div>

This <?php echo $travel_type;?> route emission record produces
<div style="display:inline" name="<?php echo $travel_type;?>2" id="<?php echo $travel_type;?>2" 
	value="<?php echo $travel_record['emissions_this_month'];?>"><?php echo $travel_record['emissions_this_month'];?></div>
tons of carbon a month
<br><br>

<span> (Optional) Change the date for this emission </span>
<input type="text" name="emission_date" id="emission_date" value="<?php echo $travel_record['date_data_entry']; ?>">
<br><br>
