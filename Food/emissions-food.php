<?php
	/*
		Template Name: emissions-food
		
		Majority of the following code was copied from \wp-content\plugins\moralab-co2\templates\forms\food-form.php
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
	
	/* Fetch all records of food emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_food WHERE id_user='$id_user' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$food_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'food' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$food_records2[] = $row;
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent record of food emissions for the user
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'food' ORDER BY date_data_entry DESC");			
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	if($row !== null){
		$yearly_co2 = calculate_yearly("food", $row)[0];
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

		$current_date = date("Y-m-d");
		
		$emission_date = $_POST["emission_date"];
		$vegetarian = $_POST["veggies"];
		$lamb = $_POST["lamb"];
		$beef = $_POST["beef"];
		$pork = $_POST["pork"];
		$fish = $_POST["fish"];
		$poultry = $_POST["poultry"];
		$food_emissions = $_POST["food"];

		$sql = "";
		
		/* If user did not input a food emission yet, then create a new row. If not, edit old entry. */
		/* $sql2 is for storage into the user_emissions table */
		if($editing == 0){
			$sql = "INSERT INTO user_emissions_food 
					(id, id_user, date_data_entry, date_modification, vegetarian, lamb, beef, pork, fish, poultry, emissions_this_month) 
					VALUES ('0', $id_user, '$emission_date', '$current_date', $vegetarian, $lamb, $beef, $pork, $fish, $poultry, $food_emissions)";
			$sql2 = "INSERT INTO user_emissions 
					(id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
					VALUES ('0', $id_user, '$emission_date', '$current_date', 'food', $food_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_food 
				SET date_data_entry='$emission_date', date_modification='$current_date', vegetarian='$vegetarian', lamb='$lamb', beef='$beef', pork='$pork', fish='$fish', poultry='$poultry', 
				emissions_this_month='$food_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions 
				SET date_data_entry='$emission_date', date_modification='$current_date', monthly_co2_emissions='$food_emissions' 
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

<form action="http://localhost/carbon-functions/Food/emissions-food.php" method="post">
	<div id="food_form_holder">
		<input type="hidden" name="editing" id="editing" value="0">
		<input type="hidden" name="editing2" id="editing2" value="0">
		<input type="hidden" name="food" id="food" value="">
		
<!--		Your yearly food consumption is
		<div style="display:inline"> <?php echo $yearly_co2; ?></div>
		tons of carbon
		<br><br>
-->
		
		Your yearly food consumption is
		<div style="display:inline" name="food2" id="food2" value="0">0.9</div>
		tons of carbon
		<br><br>
		
		<div class="input-group">
			<span> Enter the date for this emission </span>
			<input type="text" name="emission_date" id="emission_date" value="<?php echo date("Y-m-d")?>">
			<br><br>
			<span> Are you a vegetarian? </span>
			<div style="display:inline; padding-left: 10px"><input type="radio" name="veggies" onchange="carbon_veggies()" id="veggiesyes" placeholder="veggies" value="1"> Yes </div>
			<div style="display:inline; padding-left: 10px"><input type="radio" name="veggies" onchange="carbon_veggies()" id="veggiesno" placeholder="veggies" value="0"> No </div>
		</div>
		<br>

		<div hidden id="hiddenfoodinput">
			<div><p>How many times a week do you eat:</p></div>

			<div class="row">
				<div class="small-2 columns">
					<div class="input-group">
						<span class="input-group-addon lamb"> Lamb </span>
						<input style="text-align: center" type="text" min="0" name="lamb" onchange="carbon_food()" id="lamb" class="form-control" value="<?php echo !empty($carbon_data)? $food['lamb']:'';?>">
					</div>
				</div>
				<div class="small-2 columns">
					<div class="input-group">
						<span class="input-group-addon beef"> Beef </span>
						<input style="text-align: center" type="text" min="0" name="beef" onchange="carbon_food()" id="beef" class="form-control" value="<?php echo !empty($carbon_data)? $food['beef']:'';?>">
					</div>
				</div>
				<div class="small-2 columns">
					<div class="input-group">
						<span class="input-group-addon pork"> Pork </span>
						<input style="text-align: center" type="text" min="0" name="pork" onchange="carbon_food()" id="pork" class="form-control" value="<?php echo !empty($carbon_data)? $food['pork']:'';?>">
					</div>
				</div>
				<div class="small-2 columns">
					<div class="input-group">
						<span class="input-group-addon fish"> Fish </span>
						<input style="text-align: center" type="text" min="0" name="fish" onchange="carbon_food()" id="fish" class="form-control" value="<?php echo !empty($carbon_data)? $food['fish']:'';?>">
					</div>
				</div>
				<div class="small-2 columns">
					<div class="input-group">
						<span class="input-group-addon poultry"> Poultry </span>
						<input style="text-align: center" type="text" min="0" name="poultry" onchange="carbon_food()" id="poultry" class="form-control" value="<?php echo !empty($carbon_data)? $food['poultry']:'';?>">
					</div>
				</div>
			</div>
			
			<div class="input-group">               
				<input type="hidden" name="co2_food" id="co2_food" value="<?php echo !empty($carbon_data)? $food['co2_food']:'';?>">
			</div>
		</div>
		
		<input type="submit" id="formSubmit" name="formSubmit" value="Submit"/>
		<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
		<input type="button" value="Edit" name="edit_food" id="edit_food"></input>
	</div>
	
	<div class="row" name="food_list" id="food_list"> </div>
</form>

<script type="text/javascript">

	/* Function containing the calculations of carbon */
	function carbon_food(){
		var food = 0.0;
		var lamb = isNaN(parseFloat(document.getElementById('lamb').value)) ? 0.0:parseFloat(document.getElementById('lamb').value)*0.11*52;
		var beef = isNaN(parseFloat(document.getElementById('beef').value)) ? 0.0:parseFloat(document.getElementById('beef').value)*0.11*52;
		var pork = isNaN(parseFloat(document.getElementById('pork').value)) ? 0.0:parseFloat(document.getElementById('pork').value)*0.11*52;
		var fish = isNaN(parseFloat(document.getElementById('fish').value)) ? 0.0:parseFloat(document.getElementById('fish').value)*0.11*52;
		var poultry = isNaN(parseFloat(document.getElementById('poultry').value)) ? 0.0:parseFloat(document.getElementById('poultry').value)*0.11*52;
		
		food = 0.9 + (lamb*3.92*0.001) + (beef*27*0.001) + (pork*12.1*0.001) + (fish*11.9*0.001) + (poultry*6.9*0.001);
		//food = food / 12;
		var food_records = <?php if(!empty($food_records)){echo json_encode($food_records);}else{echo "''";} ?>;
		
		jQuery('#food').val(food.toFixed(4));
		document.getElementById("food2").innerHTML = food.toFixed(4);
	
	}
	
	/* Function for the 'veggies' radio button selection */
	function carbon_veggies(){
		/* If vegetarian is checked, then hide all the other food inputs and reset their values to 0 */
		if(jQuery('#veggiesyes').is(':checked')) {
			jQuery('#lamb').val("0");
			jQuery('#beef').val("0");
			jQuery('#pork').val("0");
			jQuery('#fish').val("0");
			jQuery('#poultry').val("0");
			
			jQuery('#hiddenfoodinput').hide();

		}
		/* If vegetarian is not checked, then show all the other food inputs */
		if(jQuery('#veggiesno').is(':checked')) {
			jQuery('#hiddenfoodinput').show();
		}

		jQuery('#disfood').hide();
		jQuery('#andifood').show();
		carbon_food();
	}

	/* Function that creates the edit buttons for the user */
	jQuery("#edit_food").on("click", function(){
		var food_records = <?php if(!empty($food_records)){echo json_encode($food_records);}else{echo "''";} ?>;
		var food_records2 = <?php if(!empty($food_records2)){echo json_encode($food_records2);}else{echo "''";} ?>;
		var size = <?php if(!empty($food_records)){echo sizeof($food_records);}else{echo "0";} ?>;
		   
		var food_list = '<hr><span> Click on a date to edit from this list of your recorded food emissions: </span><br>';
		food_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				food_list += '<input type="button" id="food_record' + i + '" value="' + food_records[i].date_data_entry + '" ';
				food_list += 'onclick="edit_food_record(' + food_records[i].id + ',' + food_records2[i].id + ')"></input>';
			}
		}
		food_list += '</div>';

		$("#food_list").html(food_list);
	});

	/* Function that calls edit-food.php when an edit button is clicked */
	function edit_food_record(row_id, row_id2){
		var row_id = row_id;
		var row_id2 = row_id2;
		
		var data = 'row_id=' + row_id + '&row_id2=' + row_id2;
		
		jQuery.ajax({
			type: "POST",
			url: "edit-food.php",
			data: data,
			success: function(html){
				$("#food_form_holder").html(html);
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			} 
		});
	}
	
	// Function that makes sure the food sections are not hidden when a user returns to edit their emissions
	jQuery(function ($) {
		// If the 'veggiesno' is checked, then show the food sections.  Otherwise would be hidden when a user returns.
		$(document).ready(function() {
			if(jQuery('#veggiesno').is(':checked')) {
				jQuery('#hiddenfoodinput').show();
			}
		});
	});
	
	
	
	// Not used anymore, replaced with functions.php
	/* 
		Sy's Edit: This function calculates yearly co2 emissions if there were already previous emissions 
		entered.  It fills the blank months with the average of the 6 nearest months to that blank month.
	*/
	
	/*
	function calculate_yearly(food){
		var food_records = <?php echo json_encode($food_records); ?>;
		var emission_date = new Date(document.getElementById("emission_date").value);
		var prev_date = new Date(emission_date);
		var match = false;
		var yearly_food = food / 12;
		
//DELETE THIS LINE
		console.log("Current day: " + yearly_food);

		if(isNaN(emission_date) == false){
			//loop over the past 11 months from current date
			for(i=0; i<11; i++){
				prev_date.setMonth(prev_date.getMonth()-1);
				match = false; 
				//loop over the records checking if a record with that month/year exists
				for(j=0; j<food_records.length; j++){
					var index = prev_date.getFullYear() + "-" + ("0" + (prev_date.getMonth()+1)).slice(-2);
					
					//there exists a record for that specific month/year
					if(food_records[j].date_data_entry.indexOf(index) > -1){
						match = true;
//DELETE THIS LINE	
						console.log((prev_date.getMonth()+1) + "-" + prev_date.getFullYear() + ": " + food_records[j].food_emissions_per_month);
						yearly_food = yearly_food + parseFloat(food_records[j].food_emissions_per_month);
					}
				}
				//there was no record for that specific month/year
				if(!match){
					//sort the records by whichever is nearest to the date being calculated (prev_date)
					food_records.sort(function(a, b) {
						var distancea = Math.abs(prev_date - Date.parse(a.date_data_entry));
						var distanceb = Math.abs(prev_date - Date.parse(b.date_data_entry));
						return distancea - distanceb; // sort a before b when the distance is smaller
					});
					
					//get the 6 nearest records and use the average of those to fill the blank month
					var six_months = 0.0;
					for(k=0; k<6; k++){
						if(food_records.length >= (k+1)){
							six_months = six_months + parseFloat(food_records[k].food_emissions_per_month);
						}
					}
					six_months = six_months / food_records.length;
//DELETE THIS LINE
					console.log((prev_date.getMonth()+1) + "-" + prev_date.getFullYear() + ": " + six_months);
					yearly_food = yearly_food + six_months;
				}
//DELETE THIS LINE
				console.log("yearly co2: " + yearly_food + "\n\n");
			}
		}
	
		return yearly_food;
	}
	*/
	
</script>
