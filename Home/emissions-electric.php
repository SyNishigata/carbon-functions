<?php
	/*
		Template Name: emissions-electric

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
	
	/* Fetch all records of electric emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_home_electric WHERE id_user='$id_user' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$home_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'home_electric' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$home_records2[] = $row;
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent record of home emissions for the user
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'home_electric' ORDER BY date_data_entry DESC");			
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	if($row !== null){
		$yearly_co2 = calculate_yearly("home_electric", $row)[0];
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
		$household = $_POST["household"];
		$low_watts = $_POST["low_watts"];
		$high_watts = $_POST["high_watts"];
		$electricity = $_POST["watts"];
		$electricity_emissions = $_POST["electric"];

		if($low_watts == ""){
			$low_watts = 0;
		}
		if($high_watts == ""){
			$high_watts = 0;
		}
		
		/* If user did not input an electric emission yet, then create a new row. If not, edit old entry. */
		/* $sql2 is for storage into the user_emissions table */
		
		if($editing == 0){
			$sql = "INSERT INTO user_emissions_home_electric 
				(id, id_user, date_data_entry, date_modification, people_in_home, lowest_use, highest_use, electricity_usage, emissions_this_month) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', $household, $low_watts, $high_watts, $electricity, $electricity_emissions)";
			$sql2 = "INSERT INTO user_emissions 
				(id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', 'home_electric', $electricity_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_home_electric
				SET date_data_entry='$emission_date', date_modification='$current_date', people_in_home='$household', lowest_use='$low_watts', highest_use='$high_watts',
				electricity_usage = '$electricity', emissions_this_month='$electricity_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions
				SET date_data_entry='$emission_date', date_modification='$current_date', monthly_co2_emissions='$electricity_emissions' 
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

<form action="http://localhost/carbon-functions/Home/emissions-electric.php" method="post">
	<div class="form-group">
		<!-- Electric Form -->
		<div class="input-group">
			<div id="home_form_holder">
				<input type="hidden" name="editing" id="editing" value="0">
				<input type="hidden" name="editing2" id="editing2" value="0">
				<input type="hidden" name="electric" id="electric" value="<?php echo !empty($carbon_data) ? $home['electric']:'';?>"> 
				
<!--				Your yearly electricity consumption is
				<div style="display:inline"> <?php echo $yearly_co2; ?></div>
				tons of carbon
				<br><br>
-->
				Your yearly electricity consumption produces
				<div style="display:inline" name="electric2" id="electric2" value="0">0.0</div>
				tons of carbon
				<br><br>
			
				<span> Enter the date for this emission </span>
				<input type="text" name="emission_date" id="emission_date" value="<?php echo date("Y-m-d")?>">
				<br><br>
			</div>
			
			<span class="input-group-addon electricity"> How many people are in your household? </span>
			<input type="text" name="household" onchange="carbon_electric()" id="household" class="form-control" value="<?php echo !empty($carbon_data) ? $home['household']:'1';?>">
		</div>
		<div class="input-group">
			<span class="input-group-addon electricity"> What is your yearly electricity usage in kWh? </span>
			<input type="text" name="watts" onchange="carbon_electric()" id="watts" class="form-control" value="<?php echo !empty($carbon_data) ? $home['watts']:'';?>">
		</div>

		<div class="input-group">
			<span class="input-group-addon electricity"> Need Help Calculating Your Electric Usage? </span>
			<div class="button" id="show_calc_electric" helper="off"><strong>Click Here</strong></div>
		</div>

		<div class="input-group" id="electric_helper" style="display:none">
			<p> From your electricity bill, pick the lowest and highest months of the consumption over the last year. If you have solar panels, use "Net kWh."</p>
			<span class="input-group-addon electricity"> Enter the kWh from your lowest monthly bill?</span>
			<input type="text" name="low_watts" onchange="carbon_electric()" id="low_watts" value="<?php echo !empty($carbon_data) ? $home['low_watts']:'';?>">
		
			<span class="input-group-addon electricity"> Enter the kWh from your highest monthly bill? </span>
			<input type="text" name="high_watts" onchange="carbon_electric()" id="high_watts" value="<?php echo !empty($carbon_data) ? $home['high_watts']:'';?>">
		</div>
		<!-- .Electric Form -->
		<input type="submit" id="formSubmit" name="formSubmit" value="Submit" />
		<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
		<input type="button" value="Edit" name="edit_home" id="edit_home"></input>
	</div>
	<div class="row" name="home_list" id="home_list"> </div>
</form>

<script type="text/javascript">
	
	/* Function containing the calculations of carbon */
	function carbon_electric() {
		var total_carbon_electric = 0.0;
		var watts = isNaN(parseFloat(document.getElementById('watts').value)) ? 0.0:parseFloat(document.getElementById('watts').value);
		var low_watts = isNaN(parseFloat(document.getElementById('low_watts').value)) ? 0.0:parseFloat(document.getElementById('low_watts').value);
		var high_watts = isNaN(parseFloat(document.getElementById('high_watts').value)) ? 0.0:parseFloat(document.getElementById('high_watts').value);

		// calculate watts if helper is set
		if (low_watts != '' || high_watts !=''){
			watts = (low_watts + high_watts) * 6;
			jQuery('#watts').val(watts.toFixed(4));
		}
		
		total_carbon_electric = watts * 835 * 1.09 * 0.000001;
		
		var household_members = isNaN(parseInt(document.getElementById('household').value)) ? 1:parseInt(document.getElementById('household').value);
		total_carbon_electric = total_carbon_electric / household_members;
		//total_carbon_electric = total_carbon_electric / 12;

		jQuery('#electric').val(total_carbon_electric.toFixed(4));
		document.getElementById("electric2").innerHTML = total_carbon_electric.toFixed(4);
	}
	
	/* Function that displays helper when clicked by user */
	jQuery("#show_calc_electric").on("click", function(){
		if (jQuery(this).attr("helper") == "off"){
			jQuery(this).attr("helper", "on");
			jQuery("#electric_helper").show();
		}
		else {
			jQuery(this).attr("helper", "off");
			jQuery("#electric_helper").hide();
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
			
			document.getElementById("electric2").innerHTML = home_record.emissions_this_month;
			document.getElementById("household").value = home_record.people_in_home;
			document.getElementById("watts").value = home_record.electricity_usage;
			document.getElementById("low_watts").value = home_record.lowest_use;
			document.getElementById("high_watts").value = home_record.highest_use;
		});
	}
	
	/* Function containing the ajax necessary for the function edit_home_record */
	function ajax1(row_id, row_id2) {
		var home_type = "electric";
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
