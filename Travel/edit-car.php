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
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_travel_car WHERE id='$row_id'");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$car_record = $row;
	}
	
	/* Fetch (from the library_car_models table) this car by its id_car_model
		and store it in an array to be used to specify data: make, model, and year */
	$this_id_car_model = $car_record["id_car_model"];
	$result = mysqli_query($conn, "SELECT * FROM library_car_models WHERE id_car_model='$this_id_car_model'");
	$car_make = mysqli_fetch_array($result, MYSQLI_ASSOC);
?>

<span><strong> Editing car emission record from <?php echo $car_record['date_data_entry'];?> </strong></span>
<div class="input-group">
	<input type="hidden" name="editing" id="editing" value="<?php echo $row_id; ?>">
	<input type="hidden" name="editing2" id="editing2" value="<?php echo $row_id2; ?>">
	<input type="hidden" name="car" id="car" value="<?php echo !empty($carbon_data) ? $travel['car'] : '' ?>">
	
	This car produces 
	<div style="display:inline" name="car2" id="car2" value="<?php echo $car_record['emissions_this_month'];?>"><?php echo $car_record['emissions_this_month'];?></div>
	tons of carbon a month
	<br><br>
	
	<span class="input-group-addon"> What is your total year's car mileage? </span>
	<div class="row">
		<div class="small-7 columns">
			<input type="text" name="car_miles" onchange="carbon_car()" id="car_miles" class="form-control"
				   value="<?php echo $car_record['car_milage_per_month'];?>">
		</div>
		<div class="small-5 columns">
			<select name="car_unit" onchange="carbon_car()" id="car_unit"
					class="form-control" <?php echo !empty($carbon_data) ? $travel['car_unit'] : '' ?>>
				<option
					value="miles" <?php echo ($car_record['car_unit'] == 'miles') ? 'selected' : '' ?>>
					miles
				</option>
				<option
					value="km" <?php echo ($car_record['car_unit'] == 'km') ? 'selected' : '' ?>>
					km
				</option>
			</select>
		</div>
	</div>
	<div id="current_date_default">
		<div id="current_date_holder">
			<span class="input-group-addon"> Enter the current date </span>
			<input type="text" name="car_current_date" onchange="carbon_car()" id="car_current_date"
				   class="datepick" value="<?php echo $car_record['date_data_entry']; ?>">
		</div>
	</div>
</div>

<br><br>

<div class="input-group">
	<span class="input-group-addon"> Need Help Calculating Your Car Mileage? </span>
	<div class="button" id="show_calc_car" helper="off"><strong>Click Here</strong></div>
</div>

<div class="input-group" id="car_helper" style="display:none">
	<div class="row">
		<div class="small-6 columns">
			<span class="input-group-addon"> Vehicle mileage at last safety/oil check</span>
			<input type="text" min="0" name="car_last" onchange="carbon_car()" id="car_last"
				   value="<?php echo $car_record['last_milage']; ?>">
		</div>
		<div class="small-6 columns">
			<span class="input-group-addon"> Enter the date of last check</span>
			<input type="text" name="car_last_date" onchange="carbon_car()" id="car_last_date"
				   value="<?php echo $car_record['date_last_milage']; ?>"
				   class="datepick" placeholder="YYYY-MM-DD">
		</div>
	</div>
	<div class="row">
		<div class="small-6 columns">
			<span class="input-group-addon"> Current vehicle mileage </span>
			<input type="text" min="0" name="car_current" onchange="carbon_car()" id="car_current"
				   value="<?php echo $car_record['current_milage']; ?>">
		</div>
		<div class="small-6 columns">
			<div id="current_date_helper"></div>
		</div>
	</div>
</div>

<br>

<div class="input-group">
	<span class="input-group-addon"> Select the fuel type for your car? </span>
	<select name="car_fuel" onchange="carbon_car()" id="car_fuel" class="form-control">
		<option
			value="gasoline" <?php echo ($car_record['car_fuel_type'] == 'gasoline') ? 'selected' : '' ?>>
			gasoline
		</option>
		<option
			value="other" <?php echo ($car_record['car_fuel_type'] == 'diesel') ? 'selected' : '' ?>>
			diesel
		</option>
	</select>
</div>

<div class="input-group">
	<div id="car_efficiency_holder" name="car_efficiency_holder">
		<input type="hidden" name="car_id" id="car_id" value="0">
		<span class="input-group-addon"> What is your car's fuel efficiency? </span>
		<input type="text" name="car_efficiency" onchange="carbon_car()" id="car_efficiency" class="form-control" 
			value="<?php echo ($car_record['car_unit'] == 'miles') ? $car_make['mpg'] : $car_make['kpg'] ?>">
	</div>
	<div class="row">
		<div class="small-4 large-4 columns">
			<select id="car_make" name="car_make" onchange="get_car_models()">
				<?php
					$makes = mysqli_query($conn, "SELECT DISTINCT make FROM library_car_models");
					
					while($row = mysqli_fetch_array($makes, MYSQLI_ASSOC)) {
						if ($car_make['make'] == $row['make']){
							echo '<option value="' . $row["make"] . '" selected' . '>' . $row["make"] . '</option>';
						}
						else{
							echo '<option value="' . $row["make"] . '" ' . '>' . $row["make"] . '</option>';
						}
					}
				?>
			</select>
		</div>
		<div class="small-4 large-4 columns">
			<select id="car_model" name="car_model" onchange="get_car_years()">
				<?php 
					echo '<option value="' . $car_make["model"] . '" ' . '>' . $car_make["model"] . '</option>';
				?> 
			</select>
		</div>
		<div class="small-4 large-4 columns">
			<select id="car_year" name="car_year" onchange="get_car_efficiency()">
				<?php 
					echo '<option value="' . $car_make["year"] . '" ' . '>' . $car_make["year"] . '</option>';
				?>
			</select>
		</div>
	</div>
</div>

<div class="input-group row">
	<input type="button" value="Submit" onclick="add_car()"></input>
	<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
</div>
