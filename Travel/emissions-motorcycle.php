<?php
	/*
		Template Name: emissions-motorcycle
		
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
	
	/* Fetch all records of motorcycle emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_travel_motorcycle WHERE id_user='$id_user' ORDER BY id_motorcycle ASC, date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$motorcycle_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name LIKE '%travel_motorcycle%' ORDER BY item_name ASC, date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$motorcycle_records2[] = $row;
	}
	
	/* Fetch all records of unique motorcycles for the user from table: user_emissions_travel_motorcycle */
	$result = mysqli_query($conn, "
	SELECT t1.id_motorcycle, t1.date_data_entry, t1.motorcycle_CC
	FROM user_emissions_travel_motorcycle t1 JOIN (
		SELECT id_motorcycle, MAX(date_data_entry) max_date
		FROM user_emissions_travel_motorcycle
		WHERE id_user='$id_user'
		GROUP BY id_motorcycle
	) t2 ON t1.id_motorcycle = t2.id_motorcycle AND t1.date_data_entry = t2.max_date
	");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$unique_motorcycles[] = $row;
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent records of motorcycle emissions for the user
	$result = mysqli_query($conn, "
	SELECT t1.date_data_entry, t1.item_name, t1.monthly_co2_emissions 
	FROM user_emissions t1 JOIN (
		SELECT item_name, MAX(date_data_entry) max_date
		FROM user_emissions
		WHERE item_name LIKE '%travel_motorcycle%' AND id_user='$id_user'
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
			$id_motorcycle = sizeof($unique_motorcycles) + 1;
		}
		else{
			$id_motorcycle = $updating;
		}
		$item_name = "travel_motorcycle" . $id_motorcycle;
		$last_milage= $_POST["mcycl_last"];
		$date_last_milage = $_POST["mcycl_last_date"];
		$current_milage = $_POST["mcycl_current"];
		$date_data_entry = $_POST["mcycl_current_date"];
		$motorcycle_CC = $_POST["mcycl_cc"];
		$motorcycle_milage = $_POST["mcycl_miles"];
		$motorcycle_emissions = $_POST["mcycl"];
		
		if($last_milage == ""){
			$last_milage  = 0;
		}		
		if($current_milage == ""){
			$current_milage  = 0;
		}
		
		if($editing == 0){
			$sql = "INSERT INTO user_emissions_travel_motorcycle
				(id, id_user, id_motorcycle, date_modification, last_milage, date_last_milage, current_milage, date_data_entry, motorcycle_CC, motorcycle_milage_per_month, emissions_this_month, emissions_this_year) 
				VALUES ('0', $id_user, '$id_motorcycle', '$current_date', $last_milage, '$date_last_milage', $current_milage, '$date_data_entry', '$motorcycle_CC', $motorcycle_milage, $motorcycle_emissions, '0')";	
			$sql2 = "INSERT INTO user_emissions (id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
				VALUES ('0', $id_user, '$date_data_entry', '$current_date', '$item_name', $motorcycle_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_travel_motorcycle
				SET date_modification='$current_date', last_milage='$last_milage', date_last_milage='$date_last_milage', current_milage='$current_milage', date_data_entry='$date_data_entry', motorcycle_CC='$motorcycle_CC',
				motorcycle_milage_per_month='$motorcycle_milage', emissions_this_month='$motorcycle_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions 
				SET date_data_entry='$date_data_entry', date_modification='$current_date', monthly_co2_emissions='$motorcycle_emissions' 
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
				header("Location: /carbon-functions");
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

<form action="http://localhost/carbon-functions/Travel/emissions-motorcycle.php" method="post">
	<div id="motorcycle_form_holder">
		<div class="form-group">
			<!-- Motorcycle Form -->
			<div class="input-group">
				<input type="hidden" name="updating" id="updating" value="0">
				<input type="hidden" name="editing" id="editing" value="0">
				<input type="hidden" name="editing2" id="editing2" value="0">
				<input type="hidden" name="mcycl" id="mcycl" value="<?php echo !empty($carbon_data) ? $travel['mcycl'] : '' ?>">
				
				<div name="msg" id="msg"></div><br>
				
				Your motorcycles produce  
				<div style="display:inline"> <?php echo $yearly_co2; ?></div>
				tons of carbon
				<br><br>
				
				This motorcycle produces
				<div style="display:inline" name="mcycl2" id="mcycl2" value="0">0.0</div>
				tons of carbon a month
				<br><br>
				
				<span class="input-group-addon"> What is your total year&#39;s motorcycle mileage? </span>
				<input type="text" name="mcycl_miles" onchange="carbon_mcycl()" id="mcycl_miles" class="form-control"
					   value="<?php echo !empty($carbon_data) ? $travel['mcycl_miles'] : '' ?>">
			</div>
			
			<div id="current_date_default">
				<div id="current_date_holder">
					<span class="input-group-addon"> Enter the current date </span>
					<input type="text" name="mcycl_current_date" onchange="carbon_mcycl()" id="mcycl_current_date"
						   class="datepick" value="<?php echo date("Y-m-d")?>">
				</div>
			</div>

			<div class="input-group">
				<span class="input-group-addon"> What is the CC of your motorcycle? </span>
				<select name="mcycl_cc" onchange="carbon_mcycl()" id="mcycl_cc" class="form-control"
						value="<?php echo !empty($carbon_data) ? $travel['mcycl_cc'] : '' ?>">
					<option value="125">&lt;125</option>
					<option value="375">125-500</option>
					<option value="500">&gt;500</option>
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
							   value="<?php echo !empty($carbon_data) ? $travel['mcycl_last'] : '' ?>">
					</div>
					<div class="small-6 columns">
						<span class="input-group-addon"> Enter the date of last check</span>
						<input type="text" name="mcycl_last_date" onchange="carbon_mcycl()" id="mcycl_last_date"
							   value="<?php echo !empty($carbon_data) ? $travel['mcycl_last_date'] : '' ?>"
							   class="datepick">
					</div>
				</div>
				<div class="row">
					<div class="small-6 columns">
						<span class="input-group-addon"> Current motorcycle mileage </span>
						<input type="text" min="0" name="mcycl_current" onchange="carbon_mcycl()" id="mcycl_current"
							   value="<?php echo !empty($carbon_data) ? $travel['mcycl_current'] : '' ?>">
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
				<input type="button" value="Edit" name="edit_motorcycle" id="edit_motorcycle"></input>
				<input type="button" value="Update" name="update_motorcycle" id="update_motorcycle"></input>
			</div>
		</div>
		<div class="row" name="motorcycle_list" id="motorcycle_list"> </div>
		<div class="row" name="motorcycle_update_list" id="motorcycle_update_list"> </div>
	</div>
</form>

<script type="text/javascript">
	
	/* Function containing the calculations of carbon */
	function carbon_mcycl(){
		var total_mcycl_carbon = 0.0;
		var miles = isNaN(parseFloat(document.getElementById('mcycl_miles').value)) ? 0.0:parseFloat(document.getElementById('mcycl_miles').value);
		var cc = (document.getElementById('mcycl_cc').value); // selectbox

		var last_check = isNaN(parseFloat(document.getElementById('mcycl_last').value)) ? 0.0:parseFloat(document.getElementById('mcycl_last').value);
		var current_check = isNaN(parseFloat(document.getElementById('mcycl_current').value)) ? 0.0:parseFloat(document.getElementById('mcycl_current').value);
		var last_date =  document.getElementById('mcycl_last_date').value;
		var current_date = document.getElementById('mcycl_current_date').value;

		if (last_check != '' && current_check != '') {
			var msecs = Date.parse(current_date) - Date.parse(last_date);
			num_days = msecs / 86400000;
			console.log(num_days);

			if (!isNaN(num_days)){
				miles = [(current_check - last_check) / num_days] * 365;
				jQuery('#mcycl_miles').val(miles.toFixed(2));
			}
		}

		if (miles >= 0.0){
			switch(cc){
				case '125':
					total_mcycl_carbon = (miles * 136.794) * .000001;
					break;
				case '375':
					total_mcycl_carbon = (miles * 166.084) * .000001;
					break;
				default:
					total_mcycl_carbon = (miles * 229.802) * .000001;
					break;
			}
			
			total_mcycl_carbon = total_mcycl_carbon / 12;

			jQuery('#mcycl').val(total_mcycl_carbon.toFixed(4));
			document.getElementById("mcycl2").innerHTML = total_mcycl_carbon.toFixed(4);
		}
		else {
				jQuery('#mcycl').val(0.00);
		}

	}
	
	/* Function that displays helper when clicked by user */
	jQuery("#show_calc_mcycl").on("click", function(){
		if (jQuery(this).attr("helper") == "off"){
			jQuery(this).attr("helper", "on");
			document.getElementById('current_date_helper').appendChild(document.getElementById('current_date_holder'));
			jQuery("#mcycl_helper").show();
		}
		else {
			jQuery(this).attr("helper", "off");
			document.getElementById('current_date_default').appendChild(document.getElementById('current_date_holder'));
			jQuery("#mcycl_helper").hide();
		}
	});
	 
	/* Function that creates the edit buttons for the user */
	jQuery("#edit_motorcycle").on("click", function(){
		var motorcycle_records = <?php if(!empty($motorcycle_records)){echo json_encode($motorcycle_records);}else{echo "''";} ?>;
		var motorcycle_records2 = <?php if(!empty($motorcycle_records2)){echo json_encode($motorcycle_records2);}else{echo "''";} ?>;
		var size = <?php if(!empty($motorcycle_records)){echo sizeof($motorcycle_records);}else{echo "0";} ?>;
		   
		var motorcycle_list = '<hr><span> Click on a motorcycle/date pair to edit from this list of your recorded motorcycle emissions: </span><br>';
		motorcycle_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				motorcycle_list += '<input type="button" id="motorcycle_record' + i + '" value="' + "Motorcycle" + motorcycle_records[i].id_motorcycle + ': ' + motorcycle_records[i].date_data_entry + '" ';
				motorcycle_list += 'onclick="edit_motorcycle_record(' + motorcycle_records[i].id + ',' + motorcycle_records2[i].id + ')"></input>';
			}
		}
		motorcycle_list += '</div>';

		$("#motorcycle_list").html(motorcycle_list);
	});

	/* Function that calls edit-motorcycle.php when an edit button is clicked */
	function edit_motorcycle_record(row_id, row_id2){
		var row_id = row_id;
		var row_id2 = row_id2;
		
		var data = 'row_id=' + row_id + '&row_id2=' + row_id2;
		jQuery.ajax({
			type: "POST",
			url: "edit-motorcycle.php",
			data: data,
			success: function(html){
				$("#motorcycle_form_holder").html(html);
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			} 
		});
	}

	/* Function that creates the update buttons for the user */
	jQuery("#update_motorcycle").on("click", function(){
		var unique_motorcycles = <?php if(!empty($unique_motorcycles)){echo json_encode($unique_motorcycles);}else{echo "''";} ?>;
		var size = <?php if(!empty($unique_motorcycles)){echo sizeof($unique_motorcycles);}else{echo "0";} ?>;
		   
		var motorcycle_list = '<hr><span> Click on one of your motorcycles to update (fill in information for this month or any missing months) </span><br>';
		motorcycle_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				motorcycle_list += '<input type="button" id="motorcycle_record' + i + '" value="' + "Motorcycle" + unique_motorcycles[i].id_motorcycle + ': last updated on ' + unique_motorcycles[i].date_data_entry + '" ';
				motorcycle_list += 'onclick="update_motorcycle_record(' + unique_motorcycles[i].id_motorcycle + "," + unique_motorcycles[i].motorcycle_CC + ')"></input>';
			}
		}
		motorcycle_list += '</div>';

		$("#motorcycle_update_list").html(motorcycle_list);
	});
	
	/* Function that fills in the user's motorcycle information into the form */
	function update_motorcycle_record(id_motorcycle, cc_motorcycle){
		//change buttons
		var btns = "<input type='submit' id='formSubmit' name='formSubmit' value='Submit' />";
		btns += "<a style='display:inline' href='\\\carbon-functions' class='button'><strong>Cancel</strong></a>";

		jQuery('#msg').html("<strong>Updating motorcycle" + id_motorcycle + "</strong>");
		jQuery('#mcycl_buttons').html(btns);
		jQuery('#mcycl_cc').val(cc_motorcycle);
		jQuery('#updating').val(id_motorcycle);
	}
</script>
