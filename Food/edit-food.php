<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$row_id=$_POST['row_id'];
	$row_id2=$_POST['row_id2'];
	
	/* Fetch a specific food emission record for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_food WHERE id='$row_id'");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$food_record = $row;
	}
?>

<span><strong> Editing food emission record from <?php echo $food_record['date_data_entry'];?> </strong></span>
<br><br>
<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
<input type="hidden" name="food" id="food" value="">

This food emission record produces
<div style="display:inline" name="food2" id="food2" value="0">0.90</div>
tons of carbon a month
<br><br>

<div class="input-group">
	<span> (Optional) Change the date for this emission </span>
	<input type="text" name="emission_date" id="emission_date" value="<?php echo $food_record['date_data_entry']; ?>">
	<br><br>
	
	<span> Are you a vegetarian? </span>
	<div style="display:inline; padding-left: 10px"><input type="radio" name="veggies" onchange="carbon_veggies()" id="veggiesyes" placeholder="veggies" value="1" <?php echo ($food_record['vegetarian'] == '1') ? 'checked':'';?>> Yes </div>
	<div style="display:inline; padding-left: 10px"><input type="radio" name="veggies" onchange="carbon_veggies()" id="veggiesno" placeholder="veggies" value="0" <?php echo ($food_record['vegetarian'] == '0') ? 'checked':'';?>> No </div>
</div>
<br>

<div hidden id="hiddenfoodinput">
	Lamb&nbsp;
	<input type="text" name="lamb" onchange="carbon_food()" id="lamb" value="<?php echo $food_record['lamb'];?>">
	<br>
	Beef&nbsp;
	<input type="text" name="beef" onchange="carbon_food()" id="beef" value="<?php echo $food_record['beef'];?>">
	<br>
	Pork&nbsp;
	<input type="text" name="pork" onchange="carbon_food()" id="pork" value="<?php echo $food_record['pork'];?>">
	<br>
	Fish&nbsp;
	<input type="text" name="fish" onchange="carbon_food()" id="fish" value="<?php echo $food_record['fish'];?>">
	<br>
	Poultry&nbsp;
	<input type="text" name="poultry" onchange="carbon_food()" id="poultry" value="<?php echo $food_record['poultry'];?>">
	<br>
</div>

<input type="submit" id="formSubmit" name="formSubmit" value="Submit"/>
<a style="display:inline" href="/carbon-functions/Food/emissions-food.php" class="button"><strong>Cancel</strong></a> 

<script>
	carbon_veggies();
</script>

