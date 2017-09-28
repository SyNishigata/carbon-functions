<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$row_id=$_POST['row_id'];
	$row_id2=$_POST['row_id2'];
	
	/* Fetch a specific waste emission record for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_waste WHERE id='$row_id'");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$waste_record = $row;
	}
?>

<span><strong> Editing waste emission record from <?php echo $waste_record['date_data_entry'];?> </strong></span>
<br><br>
<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
<input type="hidden" name="waste" id="waste" value="<?php echo $waste_record['emissions_this_month']; ?>">

This waste emission record produces
<div style="display:inline" name="waste2" id="waste2" value="0"><?php echo $waste_record['emissions_this_month']; ?></div>
tons of carbon a year
<br><br>

<span> (Optional) Change the date for this emission </span>
<input type="text" name="emission_date" id="emission_date" value="<?php echo $waste_record['date_data_entry']; ?>">
<br><br>

<div class="input-group">
	<span class="input-group-addon lamb"> Do you Recycle? </span>
	<select name="recycling" onchange="carbon_waste()" id="recycling">
		<option value="a" <?php echo ($waste_record['recycling_frequency'] == 'a') ? 'selected':'' ?>>Not much</options>
		<option value="b" <?php echo ($waste_record['recycling_frequency'] == 'b') ? 'selected':'' ?>>Some of our waste</options>
		<option value="c" <?php echo ($waste_record['recycling_frequency'] == 'c') ? 'selected':'' ?>>All materials locally recyclable</options>
	</select>
</div>

<div class="input-group">
	<span class="input-group-addon lamb"> Do You Compost? </span>
	<select name="compost" onchange="carbon_waste()" id="compost">
		<option value="a" <?php echo ($waste_record['compost_frequency'] == 'a') ? 'selected':'' ?>>Rarely</options>
		<option value="b" <?php echo ($waste_record['compost_frequency'] == 'b') ? 'selected':'' ?>>Sometimes</options>
		<option value="c" <?php echo ($waste_record['compost_frequency'] == 'c') ? 'selected':'' ?>>Whenever Possible</options>
	</select>
</div>

<div class="input-group">               
	<input type="hidden" name="co2_recycle" id="co2_recycle" value="<?php echo !empty($carbon_data)? $recycle['recycle_data']:'';?>">
</div>
<br>

<input type="submit" id="formSubmit" name="formSubmit" value="Submit"/>
<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 

