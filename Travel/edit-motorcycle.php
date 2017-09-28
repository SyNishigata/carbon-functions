<?php
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
					
	$row_id=$_POST['row_id'];
	$row_id2=$_POST['row_id2'];
	
	/* Fetch a specific car emission record for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_travel_motorcycle WHERE id='$row_id'");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$motorcycle_record = $row;
	}

?>

<span><strong> Editing motorcycle emission record from <?php echo $motorcycle_record['date_data_entry'];?> </strong></span>
<div class="form-group">
	<!-- Motorcycle Form -->
	<div class="input-group">
		<input type="hidden" name="updating" id="updating" value="0">
		<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
		<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
		<input type="hidden" name="mcycl" id="mcycl" value="<?php echo !empty($carbon_data) ? $travel['mcycl'] : '' ?>">
		
		<div name="msg" id="msg"></div><br>

		This motorcycle produces
		<div style="display:inline" name="mcycl2" id="mcycl2" value="<?php echo $motorcycle_record['emissions_this_month'];?>"><?php echo $motorcycle_record['emissions_this_month'];?></div>
		tons of carbon a month
		<br><br>
		
		<span class="input-group-addon"> What is your total year&#39;s motorcycle mileage? </span>
		<input type="text" name="mcycl_miles" onchange="carbon_mcycl()" id="mcycl_miles" class="form-control"
			   value="<?php echo $motorcycle_record['motorcycle_milage_per_month']; ?>">
	</div>
	
	<div id="current_date_default">
		<div id="current_date_holder">
			<span class="input-group-addon"> Enter the current date </span>
			<input type="text" name="mcycl_current_date" onchange="carbon_mcycl()" id="mcycl_current_date"
				   class="datepick" value="<?php echo $motorcycle_record['date_data_entry']; ?>">
		</div>
	</div>

	<div class="input-group">
		<span class="input-group-addon"> What is the CC of your motorcycle? </span>
		<select name="mcycl_cc" onchange="carbon_mcycl()" id="mcycl_cc" class="form-control"
				value="<?php echo $motorcycle_record['motorcycle_CC']; ?>">
			<option value="125" <?php echo $motorcycle_record['motorcycle_CC']=='125' ? 'selected' : '' ?>>&lt;125</option>
			<option value="375" <?php echo $motorcycle_record['motorcycle_CC']=='375' ? 'selected' : '' ?>>125-500</option>
			<option value="500" <?php echo $motorcycle_record['motorcycle_CC']=='500' ? 'selected' : '' ?>>&gt;500</option>
		</select>
	</div>

	<br><br>
	
	<div class="input-group">
		<span class="input-group-addon"> Need Help Calculating Your Motorcycle Mileage? </span>
		<div class="button" id="show_calc_mcycl" helper="off"><strong>Click Here</strong></div>
	</div>

	<div class="input-group" id="mcycl_helper" style="display:none">
		<div class="row">
			<div class="small-6 columns">
				<span class="input-group-addon"> Motorcycle mileage at last safety/oil check</span>
				<input type="text" min="0" name="mcycl_last" onchange="carbon_mcycl()" id="mcycl_last"
					   value="<?php echo $motorcycle_record['last_milage']; ?>">
			</div>
			<div class="small-6 columns">
				<span class="input-group-addon"> Enter the date of last check</span>
				<input type="text" name="mcycl_last_date" onchange="carbon_mcycl()" id="mcycl_last_date"
					   value="<?php echo $motorcycle_record['date_last_milage']; ?>"
					   class="datepick">
			</div>
		</div>
		<div class="row">
			<div class="small-6 columns">
				<span class="input-group-addon"> Current motorcycle mileage </span>
				<input type="text" min="0" name="mcycl_current" onchange="carbon_mcycl()" id="mcycl_current"
					   value="<?php echo $motorcycle_record['current_milage']; ?>">
			</div>
			<div class="small-6 columns">
				<div id="current_date_helper"></div>
			</div>
		</div>
	</div>
	<br>
	<!-- .Motorcycle Form -->
	<div name="mcycl_buttons" id="mcycl_buttons" class="input-group row">
		<input type="submit" id="formSubmit" name="formSubmit" value="Submit" />
		<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
	</div>
</div>