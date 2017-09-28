<?php
	/*
		Template Name: emissions-car

		Majority of the following code was copied from \wp-content\plugins\moralab-co2\templates\forms\travel-form.php
	*/
	
	/* Establish the connection with SQL database */
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "root";
	$password = "";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
	
	/* Variables */
	$id_user = 1; 
	if (isset($_POST['editing'])){
		$editing = $_POST["editing"];
	}
	if (isset($_POST['editing2'])){
		$editing2 = $_POST["editing2"];
	}
	$updating = 0;
	if (isset($_POST['updating'])){
		$updating = $_POST["updating"];
	}
	
	/* Fetch all records of car emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_travel_car WHERE id_user='$id_user' ORDER BY id_car ASC, date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$car_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name LIKE '%travel_car%' ORDER BY item_name ASC, date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$car_records2[] = $row;
	}
	
	/* Fetch all records of unique cars for the user from table: user_emissions_travel_car */
	$result = mysqli_query($conn, "
	SELECT t1.id_car, t1.id_car_model, t1.date_data_entry
	FROM user_emissions_travel_car t1 JOIN (
		SELECT id_car, MAX(date_data_entry) max_date
		FROM user_emissions_travel_car
		WHERE id_user='$id_user'
		GROUP BY id_car
	) t2 ON t1.id_car = t2.id_car AND t1.date_data_entry = t2.max_date
	");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$unique_cars[] = $row;
	}
	
	/* Fetch (from the library_car_models table) every unique user-entered car by their id_car_model
		and store them in an array to be used to specify data like: make, model, and year */
	$user_car_count = sizeof($unique_cars);
	for ($i = 0; $i < $user_car_count; $i++) {
		$this_id_car_model = $unique_cars[$i]["id_car_model"];
		$result = mysqli_query($conn, "SELECT * FROM library_car_models WHERE id_car_model='$this_id_car_model'");
		$user_car_types[$unique_cars[$i]["id_car_model"]] = mysqli_fetch_array($result, MYSQLI_ASSOC);
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent records of each unique car from table: user_emissions
	$result = mysqli_query($conn, "
	SELECT t1.date_data_entry, t1.item_name, t1.monthly_co2_emissions 
	FROM user_emissions t1 JOIN (
		SELECT item_name, MAX(date_data_entry) max_date
		FROM user_emissions
		WHERE item_name LIKE '%travel_car%' AND id_user='$id_user'
		GROUP BY item_name
	) t2 ON t1.item_name = t2.item_name AND t1.date_data_entry = t2.max_date
	");
	
	$yearly_co2 = 0.0;
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$yearly_co2 += calculate_yearly($row['item_name'], $row)[0];
	}
	
	if($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 

		$sql = "";
		$current_date = date("Y-m-d");
		
		if($updating == 0){
			$id_car = sizeof($unique_cars) + 1;
		}
		else{
			$id_car = $updating;
		}
		$id_car_model = $_POST["car_id"];  
		$item_name = "travel_car" . $id_car;
		$last_milage= $_POST["car_last"];
		$date_last_milage = $_POST["car_last_date"];
		$current_milage = $_POST["car_current"];
		$date_data_entry = $_POST["car_current_date"];
		$car_unit = $_POST["car_unit"];
		$car_fuel_type = $_POST["car_fuel"];
		$car_milage_per_month = $_POST["car_miles"];
		$car_emissions = $_POST["car"];  
		
		if($last_milage == ""){
			$last_milage  = 0;
		}		
		if($current_milage == ""){
			$current_milage  = 0;
		}
		
		if($editing == 0){
			$sql = "INSERT INTO user_emissions_travel_car
				(id, id_car, id_user, id_car_model, date_modification, last_milage, date_last_milage, current_milage, date_data_entry, car_unit, car_fuel_type, car_milage_per_month, emissions_this_month, emissions_this_year) 
				VALUES ('0', $id_car, $id_user, $id_car_model, '$current_date', $last_milage, '$date_last_milage', $current_milage, '$date_data_entry', '$car_unit', '$car_fuel_type', $car_milage_per_month, $car_emissions, '0')";
			$sql2 = "INSERT INTO user_emissions (id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
				VALUES ('0', $id_user, '$date_data_entry', '$current_date', '$item_name', $car_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_travel_car
				SET date_modification='$current_date', last_milage='$last_milage', date_last_milage='$date_last_milage', current_milage='$current_milage', date_data_entry='$date_data_entry', car_unit='$car_unit', car_fuel_type='$car_fuel_type',
				car_milage_per_month='$car_milage_per_month', emissions_this_month='$car_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions 
				SET date_data_entry='$date_data_entry', date_modification='$current_date', monthly_co2_emissions='$car_emissions' 
				WHERE id_user=$id_user AND id=$editing2";
		}
		
		/* Check if query processed correctly */
		if (($conn->query($sql) === TRUE) && ($conn->query($sql2) === TRUE)) {
			/* Start storage into user_deficits table */
			$total_emissions = carbon_ranking()[0];
			$total_tree_sequestration = carbon_ranking()[1];
			$carbon_deficit = $total_emissions - $total_tree_sequestration;
			
			$result = mysqli_query($conn, "SELECT * FROM user_deficits WHERE id_user='$id_user'");
			$rows = $result->num_rows;
			if($rows == 0){
				$sql = "INSERT INTO user_deficits (id, id_user, date, total_emissions, total_tree_sequestration, carbon_deficit) 
					VALUES ('0', $id_user, '$current_date', $total_emissions, $total_tree_sequestration, $carbon_deficit)";
			}
			else{
				$sql = "UPDATE user_deficits
				SET date='$current_date',  total_emissions='$total_emissions', total_tree_sequestration='$total_tree_sequestration', 
				carbon_deficit='$carbon_deficit' WHERE id_user=$id_user";
			}
			if ($conn->query($sql) === TRUE){
				echo("<script>location.href = '/carbon-functions'</script>");
			}
			else{
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<form id="car_form" action="http://localhost/carbon-functions/Travel/emissions-car.php" method="post">
	<div id="car_form_holder">
		<div class="form-group">
			<!-- Car Form -->
			<div class="input-group">
				<input type="hidden" name="updating" id="updating" value="0">
				<input type="hidden" name="editing" id="editing" value="0">
				<input type="hidden" name="editing2" id="editing2" value="0">
				<input type="hidden" name="car" id="car" value="<?php echo !empty($carbon_data) ? $travel['car'] : '' ?>">

				<div name="msg" id="msg"></div><br>
				
				Your cars produce  
				<div style="display:inline"> <?php echo $yearly_co2; ?></div>
				tons of carbon yearly
				<br><br>
				
				This car produces
				<div style="display:inline" name="car2" id="car2" value="0">0.0</div>
				tons of carbon a month
				<br><br>
				
				<span class="input-group-addon"> What is your car milage for this month? </span>
				<div class="row">
					<div class="small-7 columns">
						<input type="text" name="car_miles" onchange="carbon_car()" id="car_miles" class="form-control"
							   value="<?php echo !empty($carbon_data) ? $travel['car_miles'] : '' ?>">
					</div>
					<div class="small-5 columns">
						<select name="car_unit" onchange="carbon_car()" id="car_unit"
								class="form-control" <?php echo !empty($carbon_data) ? $travel['car_unit'] : '' ?>>
							<option
								value="miles" <?php echo (!empty($carbon_data) && $travel['car_unit'] == 'miles') ? 'selected' : '' ?>>
								miles
							</option>
							<option
								value="km" <?php echo (!empty($carbon_data) && $travel['car_unit'] == 'km') ? 'selected' : '' ?>>
								km
							</option>
						</select>
					</div>
				</div>
				<div id="current_date_default">
					<div id="current_date_holder">
						<span class="input-group-addon"> Enter the current date </span>
						<input type="text" name="car_current_date" onchange="carbon_car()" id="car_current_date"
							   class="datepick" value="<?php echo date("Y-m-d")?>">
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
							   value="<?php echo !empty($carbon_data) ? $travel['car_last'] : '' ?>">
					</div>
					<div class="small-6 columns">
						<span class="input-group-addon"> Enter the date of last check</span>
						<input type="text" name="car_last_date" onchange="carbon_car()" id="car_last_date"
							   value="<?php echo !empty($carbon_data) ? $travel['car_last_date'] : '' ?>"
							   class="datepick" placeholder="YYYY-MM-DD">
					</div>
				</div>
				<div class="row">
					<div class="small-6 columns">
						<span class="input-group-addon"> Current vehicle mileage </span>
						<input type="text" min="0" name="car_current" onchange="carbon_car()" id="car_current"
							   value="<?php echo !empty($carbon_data) ? $travel['car_current'] : '' ?>">
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
						value="gasoline" <?php echo (!empty($carbon_data) && $travel['car_fuel'] == 'gasoline') ? 'selected' : '' ?>>
						gasoline
					</option>
					<option
						value="other" <?php echo (!empty($carbon_data) && $travel['car_fuel'] == 'diesel') ? 'selected' : '' ?>>
						diesel
					</option>
				</select>
			</div>

			<div class="input-group">
				<div id="car_efficiency_holder" name="car_efficiency_holder">
					<input type="hidden" name="car_id" id="car_id" value="0">
					<span class="input-group-addon"> What is your car's fuel efficiency? </span>
					<input type="text" name="car_efficiency" onchange="carbon_car()" id="car_efficiency"
							class="form-control" value="<?php echo !empty($carbon_data) ? $travel['car_efficiency'] : '' ?>">
				</div>
				<div class="row">
					<div class="small-4 large-4 columns">
						<select id="car_make" name="car_make" onchange="get_car_models()">
							<option>Select a Make</option>
							<?php
								$makes = mysqli_query($conn, "SELECT DISTINCT make FROM library_car_models");
								
								while($row = mysqli_fetch_array($makes, MYSQLI_ASSOC)) {
									echo '<option value="' . $row["make"] . '" ' . '>' . $row["make"] . '</option>';
								}
							?>
						</select>
					</div>
					<div class="small-4 large-4 columns">
						<select id="car_model" name="car_model" onchange="get_car_years()" 
							<?php 
								echo (!empty($travel['car_model'])) ? '' : 'disabled'; 
							?> >
							<option>Model</option>
						</select>
					</div>
					<div class="small-4 large-4 columns">
						<select id="car_year" name="car_year" onchange="get_car_efficiency()" 
							<?php 
								echo (!empty($travel['car_year'])) ? '' : 'disabled'; 
							?> >
							<option>Year</option>
						</select>
					</div>
				</div>
			</div>
		
			<div name="car_buttons" id="car_buttons" class="input-group row">
				<br><br><br>
				<input type="button" value="Add Car" onclick="add_car()"></input>
				<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
				<input type="button" value="Edit" name="edit_car" id="edit_car"></input>
				<input type="button" value="Update" name="update_car" id="update_car"></input>
				<br><br><br>
				<input type="hidden" name="cdist" id="cdist" value="">
				<input type="hidden" name="ccount" id="ccount"
					   value="<?php echo !empty($carbon_data) ? sizeof($travel['carfrom']) : '0' ?>">
			</div>

		<!-- .Car Form -->
		</div>
		<div class="row" name="car_list" id="car_list"> </div>
		<div class="row" name="car_update_list" id="car_update_list"> </div>
	</div>
</form>


<script type="text/javascript">
	
	/* Function containing the calculations of carbon */
	function carbon_car(){
		var total_car_carbon = 0.0;
		var miles = isNaN(parseFloat(document.getElementById('car_miles').value)) ? 0.0:parseFloat(document.getElementById('car_miles').value);
		var efficieny = isNaN(parseFloat(document.getElementById('car_efficiency').value)) ? 0.0:parseFloat(document.getElementById('car_efficiency').value);
		var unit = (document.getElementById('car_unit').value); // selectbox
		var fuel = (document.getElementById('car_fuel').value); // selectbox

		var last_check = isNaN(parseFloat(document.getElementById('car_last').value)) ? 0.0:parseFloat(document.getElementById('car_last').value);
		var current_check = isNaN(parseFloat(document.getElementById('car_current').value)) ? 0.0:parseFloat(document.getElementById('car_current').value);
		var last_date =  document.getElementById('car_last_date').value;
		var current_date = document.getElementById('car_current_date').value;

		if (last_check != '' && current_check != '') {
			var msecs = Date.parse(current_date) - Date.parse(last_date);
			num_days = msecs / 86400000;
			console.log(num_days);

			if (!isNaN(num_days)){
				miles = [(current_check - last_check) / num_days] * 365;
				jQuery('#car_miles').val(miles.toFixed(2));
			}
		}

		if (parseFloat(miles) >= 0.0 && parseFloat(efficieny) >= 0.0){
			if (parseFloat(efficieny) == 0.0 ){
				total_car_carbon = 0.00;
			}
			else {
				switch(fuel){
					case 'gasoline':
						total_car_carbon = (miles/efficieny) * (2307 + 8874);
						total_car_carbon += miles * 56;
						total_car_carbon = total_car_carbon * .000001;
						break;
					default:
						total_car_carbon = (miles/efficieny) * (2335 + 10153);
						total_car_carbon += miles * 56;
						total_car_carbon = total_car_carbon * .000001;
						break;
				}

				if (unit != 'miles') {
					total_car_carbon = total_car_carbon * 0.621371;
				}
			}
			
			var monthly_car = total_car_carbon / 12;
			var yearly_car = total_car_carbon;

			jQuery('#car').val(monthly_car.toFixed(4));
			document.getElementById("car2").innerHTML = monthly_car.toFixed(4);
		}
		else {
			jQuery('#car').val(0.00);
		}
	}
	
	/* Function that displays helper when clicked by user */
	jQuery("#show_calc_car").on("click", function(){
		if (jQuery(this).attr("helper") == "off"){
			jQuery(this).attr("helper", "on");
			document.getElementById('current_date_helper').appendChild(document.getElementById('current_date_holder'));
			jQuery("#car_helper").show();
		}
		else {
			jQuery(this).attr("helper", "off");
			document.getElementById('current_date_default').appendChild(document.getElementById('current_date_holder'));
			jQuery("#car_helper").hide();
		}
	});
	
	/* Function that submits the form */
	function add_car(){
		document.getElementById("car_form").submit();
	}
	
	/* Function that creates the edit buttons for the user */
	jQuery("#edit_car").on("click", function(){
		var car_records = <?php if(!empty($car_records)){echo json_encode($car_records);}else{echo "''";} ?>;
		var car_records2 = <?php if(!empty($car_records2)){echo json_encode($car_records2);}else{echo "''";} ?>;
		var size = <?php if(!empty($car_records)){echo sizeof($car_records);}else{echo "''";} ?>;
		   
		var car_list = '<hr><span> Click on a car/date pair to edit from this list of your recorded car emissions: </span><br>';
		car_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				car_list += '<input type="button" id="car_record' + i + '" value="' + "Car" + car_records[i].id_car + ': ' + car_records[i].date_data_entry + '" ';
				car_list += 'onclick="edit_car_record(' + car_records[i].id + ',' + car_records2[i].id + ')"></input>';
			}
		}
		car_list += '</div>';

		$("#car_list").html(car_list);
	});

	/* Function that calls edit-car.php when an edit button is clicked */
	function edit_car_record(row_id, row_id2){
		var row_id = row_id;
		var row_id2 = row_id2;
		
		var data = 'row_id=' + row_id + '&row_id2=' + row_id2;
		jQuery.ajax({
			type: "POST",
			url: "edit-car.php",
			data: data,
			success: function(html){
				$("#car_form_holder").html(html);
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			} 
		});
	}
	
	/* Function that creates the update buttons for the user */
	jQuery("#update_car").on("click", function(){
		var unique_cars = <?php if(!empty($unique_cars)){echo json_encode($unique_cars);}else{echo "''";} ?>;
		var size = <?php if(!empty($unique_cars)){echo sizeof($unique_cars);}else{echo "''";} ?>;
		   
		var car_list = '<hr><span> Click on one of your cars to update (fill in information for this month or any missing months) </span><br>';
		car_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				car_list += '<input type="button" id="car_record' + i + '" value="' + "Car" + unique_cars[i].id_car + ': last updated on ' + unique_cars[i].date_data_entry + '" ';
				car_list += 'onclick="update_car_record(' + unique_cars[i].id_car + "," +unique_cars[i].id_car_model + ')"></input>';
			}
		}
		car_list += '</div>';

		$("#car_update_list").html(car_list);
	});
	
	/* Function that fills in the user's car information into the form */
	function update_car_record(id_car, id_car_model){
		var user_car_types = <?php if(!empty($user_car_types)){echo json_encode($user_car_types);}else{echo "''";} ?>;
		document.getElementById("car_model").disabled = false;
		document.getElementById("car_year").disabled = false;
		
		//fill in the user's car make
		jQuery('#car_make').val(user_car_types[id_car_model].make);

		//fill in the user's car model
		var select = document.getElementById('car_model');
		var opt = document.createElement('option');
		opt.value = user_car_types[id_car_model].model;
		opt.innerHTML = user_car_types[id_car_model].model;
		select.appendChild(opt);
		jQuery('#car_model').val(user_car_types[id_car_model].model);
		
		//fill in the user's car year
		select = document.getElementById('car_year');
		var opt = document.createElement('option');
		opt.value = user_car_types[id_car_model].year;
		opt.innerHTML = user_car_types[id_car_model].year;
		select.appendChild(opt);
		jQuery('#car_year').val(user_car_types[id_car_model].year);

		//fill in the user's car efficiency
		get_car_efficiency();
		
		//change buttons
		var btns = "<input type='button' value='Update' onclick='add_car()'></input>";
		btns += "<a style='display:inline' href='\\\carbon-functions' class='button'><strong>Cancel</strong></a>";

		jQuery('#msg').html("<strong>Updating car" + id_car + "</strong>");
		jQuery('#car_buttons').html(btns);
		jQuery('#updating').val(id_car);
	}
	
	/* Function that retrieves the car models that correspond with the car make selected */
	function get_car_models(){
		var selected_make = document.getElementById('car_make').value;
		if(selected_make == "Select a Make"){
			document.getElementById("car_model").disabled = true;
			document.getElementById("car_year").disabled = true;
		}
		else{
			document.getElementById("car_model").disabled = false;
			document.getElementById("car_year").disabled = true;
			document.getElementById("car_year").value = "Year";
			var data = 'selected_make='+ selected_make;
			
			jQuery.ajax({
				type: "POST",
				url: "get-models.php",
				data: data,
				success: function(html){
					$("#car_model").html(html);
				},
            error: function (e) {
                alert("Server Error : " + e.state());
            } 
			});
		}
	}

	/* Function that retrieves the car years that correspond with the car make/model selected */
	function get_car_years(){
		var selected_make = document.getElementById('car_make').value;
		var selected_model = document.getElementById('car_model').value;

		if(selected_model == "Model"){
			document.getElementById("car_year").disabled = true;
		}
		else{
			document.getElementById("car_year").disabled = false;
			var data = 'selected_make=' + selected_make + '&selected_model=' + selected_model;
	
			jQuery.ajax({
				type: "POST",
				url: "get-years.php",
				data: data,
				success: function(html){
					$("#car_year").html(html);
				},
				error: function (e) {
				    alert("Server Error : " + e.state());
				} 
			});
		}
	}
	
	/* Function that retrieves the car efficiency that correspond with the car make, model,
		and year selected.  It also gets the id_car_model of that combination */
	function get_car_efficiency() {
		var make = jQuery('#car_make').val();
		var model = jQuery('#car_model').val();
		var year = jQuery('#car_year').val();
		var unit = jQuery('#car_unit').val();

		var data = 'make=' + make + '&model=' + model + '&year=' + year + '&unit=' + unit;

		jQuery.ajax({
			type: "POST",
			url: "get-efficiency.php",
			data: data,
			success: function(html){
				$("#car_efficiency_holder").html(html);
				carbon_car();
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			} 
		});
	}
	
	
	// Not used anymore, replaced with functions.php
	/* 
		Sy's Edit: This function calculates yearly co2 emissions if there were already previous emissions entered.
		It fills the blank months with the nearest month that is also past/greater than that blank month.
	*/
	/*
	function calculate_yearly(car){
		var car_records = <?php echo json_encode($user_car_data); ?>;
		var car_id = document.getElementById("car_id").value;
		var emission_date = new Date(document.getElementById("car_current_date").value);
		var prev_date = new Date(emission_date);
		var match = false;
		var yearly_car = car / 12;
		
//DELETE THIS LINE
		console.log("Current day: " + yearly_car);

		if(isNaN(emission_date) == false){
			//loop over the past 11 months from current date
			for(i=0; i<11; i++){
				prev_date.setMonth(prev_date.getMonth()-1);
				match = false; 
				//loop over the records checking if a record with that month/year exists
				for(j=0; j<car_records.length; j++){
					var index = prev_date.getFullYear() + "-" + ("0" + (prev_date.getMonth()+1)).slice(-2);
					
					//there exists a record for that specific car
//continue here
					if(car_records[j].id_car_model == car_id){
						//there exists a record for that specific month/year
						if(car_records[j].date_data_entry.indexOf(index) > -1){
							match = true;
//DELETE THIS LINE		
							console.log((prev_date.getMonth()+1) + "-" + prev_date.getFullYear() + ": " + car_records[j].car_emissions_per_month);
							yearly_car = yearly_car + parseFloat(car_records[j].car_emissions_per_month);
						}
					}
				}
				
				//there was no record for that specific month/year
				if(!match){
					//sort the records by whichever is nearest to the date being calculated (prev_date)
					car_records.sort(function(a, b) {
						var distancea = Math.abs(prev_date - Date.parse(a.date_data_entry));
						var distanceb = Math.abs(prev_date - Date.parse(b.date_data_entry));
						return distancea - distanceb; // sort a before b when the distance is smaller
					});
					
					//get the 6 nearest records and use the average of those to fill the blank month
					var six_months = 0.0;
					for(k=0; k<6; k++){
						if(car_records.length >= (k+1)){
							six_months = six_months + parseFloat(car_records[k].car_emissions_per_month);
						}
					}
					six_months = six_months / car_records.length;
//DELETE THIS LINE
					console.log((prev_date.getMonth()+1) + "-" + prev_date.getFullYear() + ": " + six_months);
					yearly_car = yearly_car + six_months;
				}
//DELETE THIS LINE
				console.log("yearly co2: " + yearly_car + "\n\n");
			}
		}
	
		return yearly_car;
	}
	*/
		
	// Not used anymore, replaced with functions.php
	/* 
		Sy's Edit: Added function to remove a car from the car list and database
	*/
	/*
	function remove_car(carbon, id, row_id){

		var all_car_carbon = isNaN(parseFloat(document.getElementById('all_cars').value))?0.0:parseFloat(document.getElementById('all_cars').value);
		all_car_carbon = all_car_carbon - parseFloat(carbon);
		jQuery('#all_cars').val(all_car_carbon.toFixed(2));
		jQuery('#all_cars2').val(all_car_carbon.toFixed(2));
		document.getElementById("all_cars2").innerHTML = all_car_carbon.toFixed(2);

		jQuery('#car'+id).remove();
		
		var data = 'row_id=' + row_id;
	
		jQuery.ajax({
			type: "POST",
			url: "remove-car.php",
			data: data,
			success: function(data){
				//enter success here
			},
			error: function (e) {
			    alert("Server Error : " + e.state());
			} 
		});
	}
	*/

</script>
