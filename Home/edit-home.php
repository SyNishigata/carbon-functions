<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$home_type=$_POST['home_type'];
	$row_id=$_POST['row_id'];
	$row_id2=$_POST['row_id2'];
	
	/* Fetch a specific home emission record for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_home_$home_type WHERE id='$row_id'");
	$home_record = mysqli_fetch_array($result, MYSQLI_ASSOC);

?>

<span><strong> Editing <?php echo $home_type;?> emission record from <?php echo $home_record['date_data_entry'];?> </strong></span>
<br><br>
<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
<input type="hidden" name="<?php echo $home_type;?>" id="<?php echo $home_type;?>" value="">
<div style="display:none" name="home_record" id="home_record"><?php echo json_encode($home_record); ?></div>

This <?php echo $home_type;?> emission record produces
<div style="display:inline" name="<?php echo $home_type;?>2" id="<?php echo $home_type;?>2" value="0">0.0</div>
tons of carbon a month
<br><br>

<span> (Optional) Change the date for this emission </span>
<input type="text" name="emission_date" id="emission_date" value="<?php echo $home_record['date_data_entry']; ?>">
<br><br>