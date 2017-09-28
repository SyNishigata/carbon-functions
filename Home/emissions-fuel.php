<?php
	/*
		Template Name: emissions-fuel
		
		Majority of the following code was copied from \wp-content\plugins\moralab-co2\templates\forms\home-form.php
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
	
	/* Fetch all records of fuel emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_home_fuel WHERE id_user='$id_user' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$home_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'home_fuel' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$home_records2[] = $row;
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent record of home emissions for the user
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'home_fuel' ORDER BY date_data_entry DESC");			
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	if($row !== null){
		$yearly_co2 = calculate_yearly("home_fuel", $row)[0];
	}
	else{
		$yearly_co2 = 0.0;
	}
	
	/* Begin database operations once user clicks 'Submit' */
	if (isset($_POST['formSubmit']))
	{
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 

		$sql = "";
		$current_date = date("Y-m-d");
		
		$emission_date = $_POST["emission_date"];
		$household = $_POST["household"];   //not currently stored in db
		$cooking = $_POST["cooking"];
		$drying = $_POST["drying"];
		$water_heating = $_POST["water_heat"];
		$fuel = $_POST["fuel_gallon"];
		$fuel_emissions = $_POST["fuel"];

		if($cooking == ""){
			$cooking = 0;
		}
		if($drying == ""){
			$drying = 0;
		}
		if($water_heating == ""){
			$water_heating = 0;
		}
		
		if($editing == 0){
			$sql = "INSERT INTO user_emissions_home_fuel
				(id, id_user, date_data_entry, date_modification, cooking, drying, water_heating, fuel_usage, emissions_this_month) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', $cooking, $drying, $water_heating, $fuel, $fuel_emissions)";
			$sql2 = "INSERT INTO user_emissions (id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', 'home_fuel', $fuel_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_home_fuel
				SET date_data_entry='$emission_date', date_modification='$current_date', cooking='$cooking', drying='$drying', 
				water_heating='$water_heating', fuel_usage = '$fuel', emissions_this_month='$fuel_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions
				SET date_data_entry='$emission_date', date_modification='$current_date', monthly_co2_emissions='$fuel_emissions' 
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

<form action="http://localhost/carbon-functions/Home/emissions-fuel.php" method="post">
	<div class="form-group">
		<!-- Propane Fuel Form -->
		<div class="input-group">
			<div id="home_form_holder">
				<input type="hidden" name="editing" id="editing" value="0">
				<input type="hidden" name="editing2" id="editing2" value="0">
				<input type="hidden" name="fuel" id="fuel" value="<?php echo !empty($carbon_data) ? $home['propane_fuel']:'';?>">                  
					
				Your yearly fuel consumption is 
				<div style="display:inline"> <?php echo $yearly_co2; ?></div>
				tons of carbon
				<br><br>
				
				Your fuel consumption for this month produces 
				<div style="display:inline" name="fuel2" id="fuel2" value="0">0.0</div>
				tons of carbon
				<br><br>
				
				<span> Enter the date for this emission </span>
				<input type="text" name="emission_date" id="emission_date" value="<?php echo date("Y-m-d")?>">
				<br><br>
			</div>
			
			<span class="input-group-addon fuel"> How many people are in your household? </span>
			<input type="text" name="household" onchange="carbon_propane_fuel()" id="household" class="form-control" value="<?php echo !empty($carbon_data) ? $home['household']:'1';?>">
		</div>
		<div class="input-group">
			<span class="input-group-addon fuel">Total gallons of propane or other fuels used in a year? </span>
			<input type="text" name="fuel_gallon" onchange="carbon_propane_fuel()" id="fuel_gallon" value="<?php echo !empty($carbon_data) ? $home['fuel_gallon']:'';?>">
		</div>

		<div class="input-group">
			<span class="input-group-addon fuel"> Need Help Calculating Your Propane or Other Fuels Usage? </span>
			<div class="button" id="show_propane_help" helper="off"><strong>Click Here</strong></div>
		</div>

		<div class="input-group" id="propane_helper" style="display:none">
			<p> Do you use propane or other fuels for: </p>
			<div class="input-group">
				<input type="checkbox" name="cooking" onchange="carbon_propane_fuel()" id="cooking" value="1" <?php echo (!empty($carbon_data) && $home['cooking']=='1' )? 'checked':'';?>>
				<label for="cooking" class="input-group-addon"> Cooking </label>

			</div>
			<div class="input-group">
				<input type="checkbox" name="drying" onchange="carbon_propane_fuel()" id="drying" placeholder="" value="1" <?php echo (!empty($carbon_data) && $home['drying']=='1' )? 'checked':'';?>>
				<label for="drying" class="input-group-addon"> Drying </label>
			</div>
			<div class="input-group">
				<input type="checkbox" name="water_heat" onchange="carbon_propane_fuel()" id="water_heat" placeholder="" value="1" <?php echo (!empty($carbon_data) && $home['water_heat']=='1' )? 'checked':'';?>>
				<label for="water_heat" class="input-group-addon"> Water Heating </label>
			</div>
		</div>

		<!-- .Propane Fuel Form -->
		<input type="submit" id="formSubmit" name="formSubmit" value="Submit" />
		<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
		<input type="button" value="Edit" name="edit_home" id="edit_home"></input>
	</div>
	<div class="row" name="home_list" id="home_list"> </div>
</form>

<script type="text/javascript">

	/* Function containing the calculations of carbon */
	function carbon_propane_fuel() {
		var gallons = isNaN(parseFloat(document.getElementById('fuel_gallon').value)) ? 0.0:parseFloat(document.getElementById('fuel_gallon').value);
		var cooking = (document.getElementById('cooking').checked);
		var drying = (document.getElementById('drying').checked);
		var water_heat = (document.getElementById('water_heat').checked);

		if (cooking == true){ gallons += 50; }
		if (drying == true) { gallons += 100; }
		if (water_heat == true) { gallons += 350; }

		
		if (gallons > 0){
			total_carbon_propane_fuel = gallons * 8362 * 0.000001;
		}
		jQuery('#fuel_gallon').val(gallons.toFixed(4));
		
		var household_members = isNaN(parseInt(document.getElementById('household').value)) ? 1:parseInt(document.getElementById('household').value);
		total_carbon_propane_fuel = total_carbon_propane_fuel / household_members;
		total_carbon_propane_fuel = total_carbon_propane_fuel / 12;
		
		jQuery('#fuel').val(total_carbon_propane_fuel.toFixed(4));
		document.getElementById("fuel2").innerHTML = total_carbon_propane_fuel.toFixed(4);
	}
	
	/* Function that displays helper when clicked by user */
	jQuery("#show_propane_help").on("click", function(){
		if (jQuery(this).attr("helper") == "off"){
			jQuery(this).attr("helper", "on");
			jQuery("#propane_helper").show();
		}
		else {
			jQuery(this).attr("helper", "off");
			jQuery("#propane_helper").hide();
		}
	});
	
	/* Function that creates the edit buttons for the user */
	jQuery("#edit_home").on("click", function(){
		var home_records = <?php if(!empty($home_records)){echo json_encode($home_records);}else{echo "''";} ?>;
		var home_records2 = <?php if(!empty($home_records2)){echo json_encode($home_records2);}else{echo "''";} ?>;
		var size = <?php if(!empty($home_records)){echo sizeof($home_records);}else{echo "''";} ?>;
		   
		var home_list = '<hr><span> Click on a date to edit from this list of your recorded home emissions: </span><br>';
		home_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				home_list += '<input type="button" id="home_record' + i + '" value="' + home_records[i].date_data_entry + '" ';
				home_list += 'onclick="edit_home_record(' + home_records[i].id + ',' + home_records2[i].id + ')"></input>';
			}
		}
		home_list += '</div>';

		$("#home_list").html(home_list);
	});

	/* Function that calls edit-home.php when an edit button is clicked */
	function edit_home_record(row_id, row_id2){
		var row_id = row_id;
		var row_id2 = row_id2;
		
		$.when(ajax1(row_id, row_id2)).done(function(a1){
			var home_record = JSON.parse(document.getElementById('home_record').textContent);
			
			document.getElementById("fuel2").innerHTML = home_record.emissions_this_month;
			//document.getElementById("household").value = home_record.people_in_home;
			document.getElementById("fuel_gallon").value = home_record.fuel_usage;
			if(home_record.cooking == "0"){
				document.getElementById("cooking").checked = false;
			}
			else{
				document.getElementById("cooking").checked = true;
			}
			if(home_record.drying == "0"){
				document.getElementById("drying").checked = false;
			}
			else{
				document.getElementById("drying").checked = true;
			}
			if(home_record.water_heating == "0"){
				document.getElementById("water_heat").checked = false;
			}
			else{
				document.getElementById("water_heat").checked = true;
			}
			carbon_propane_fuel()
		});
	}
	
	/* Function containing the ajax necessary for the function edit_home_record */
	function ajax1(row_id, row_id2) {
		var home_type = "fuel";
		var data = 'row_id=' + row_id + '&row_id2=' + row_id2 + '&home_type=' + home_type;
		return $.ajax({
			type: "POST",
			url: "edit-home.php",
			data: data,
			success: function(html){
				$("#home_form_holder").html(html);
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			}
		});
	}
	
</script>
