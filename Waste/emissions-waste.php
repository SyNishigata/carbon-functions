<?php 
	/*
		Template Name: emissions-waste
	
		Majority of the following code was copied from \wp-content\plugins\moralab-co2\templates\forms\recycle-form.php
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
	
	/* Fetch all records of waste emissions for the user */
	$result = mysqli_query($conn, "SELECT * FROM user_emissions_waste WHERE id_user='$id_user' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$waste_records[] = $row;
	}
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'waste' ORDER BY date_data_entry ASC");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$waste_records2[] = $row;
	}
	
	/* Calculate Yearly CO2 */
	include '../Calculations/functions.php';
	//fetch the most recent record of waste emissions for the user
	$result = mysqli_query($conn, "SELECT * FROM user_emissions WHERE id_user='$id_user' AND item_name = 'waste' ORDER BY date_data_entry DESC");			
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	if($row !== null){
		$yearly_co2 = calculate_yearly("waste", $row)[0];
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
		$recycling_frequency = $_POST["recycling"];
		$compost_frequency= $_POST["compost"];
		$waste_emissions= $_POST["waste"];

		if($editing == 0){
			$sql = "INSERT INTO user_emissions_waste (id, id_user, date_data_entry, date_modification, recycling_frequency, compost_frequency, emissions_this_month) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', '$recycling_frequency', '$compost_frequency', $waste_emissions)";
			$sql2 = "INSERT INTO user_emissions (id, id_user, date_data_entry, date_modification, item_name, monthly_co2_emissions) 
				VALUES ('0', $id_user, '$emission_date', '$current_date', 'waste', $waste_emissions)";
		}
		else{
			$sql = "UPDATE user_emissions_waste
				SET date_data_entry='$emission_date', date_modification='$current_date', recycling_frequency='$recycling_frequency', compost_frequency='$compost_frequency',
				emissions_this_month='$waste_emissions' WHERE id_user=$id_user AND id=$editing";
			$sql2 = "UPDATE user_emissions
				SET date_data_entry='$emission_date', date_modification='$current_date', monthly_co2_emissions='$waste_emissions' 
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

<form action="http://localhost/carbon-functions/Waste/emissions-waste.php" method="post">
	<div id="waste_form_holder">
		<input type="hidden" name="editing" id="editing" value="0">
		<input type="hidden" name="editing2" id="editing2" value="0">
		<input type="hidden" name="waste" id="waste" value="">
	
		Your yearly waste consumption is 
		<div style="display:inline"> <?php echo $yearly_co2; ?></div>
		tons of carbon
		<br><br>
		
		Your waste consumption for this month produces 
		<div style="display:inline" name="waste2" id="waste2" value="0">0.0</div>
		tons of carbon
		<br><br>
	
		<span> Enter the date for this emission </span>
		<input type="text" name="emission_date" id="emission_date" value="<?php echo date("Y-m-d")?>">
		<br><br>
		
		<div class="input-group">
		<span class="input-group-addon lamb"> Do you Recycle? </span>
		  <select name="recycling" onchange="carbon_waste()" id="recycling">
				<option value="a" <?php echo (!empty($carbon_data) && $recycle['recycling'] == 'a')? 'selected':''?>>Not much</options>
				<option value="b" <?php echo (!empty($carbon_data) && $recycle['recycling'] == 'b')? 'selected':''?>>Some of our waste</options>
				<option value="c" <?php echo (!empty($carbon_data) && $recycle['recycling'] == 'c')? 'selected':''?>>All materials locally recyclable</options>
		  </select>
		</div>

		<div class="input-group">
			<span class="input-group-addon lamb"> Do You Compost? </span>
			<select name="compost" onchange="carbon_waste()" id="compost">
				<option value="a" <?php echo (!empty($carbon_data) && $recycle['compost'] == 'a')? 'selected':''?>>Rarely</options>
				<option value="b" <?php echo (!empty($carbon_data) && $recycle['compost'] == 'b')? 'selected':''?>>Sometimes</options>
				<option value="c" <?php echo (!empty($carbon_data) && $recycle['compost'] == 'c')? 'selected':''?>>Whenever Possible</options>
			</select>
		</div>

		<div class="input-group">               
			<input type="hidden" name="co2_recycle" id="co2_recycle" value="<?php echo !empty($carbon_data)? $recycle['recycle_data']:'';?>">
		</div>
		<br>
		
		<input type="submit" id="formSubmit" name="formSubmit" value="Submit"/>
		<a style="display:inline" href="/carbon-functions" class="button"><strong>Cancel</strong></a> 
		<input type="button" value="Edit" name="edit_waste" id="edit_waste"></input>
	</div>
	
	<div class="row" name="waste_list" id="waste_list"> </div>
</form>

<script type="text/javascript">

	/* Function containing the calculations of carbon */
	function carbon_waste(){
		var waste = 1.20;
		var recycling = (document.getElementById('recycling').value); 
		var compost = (document.getElementById('compost').value); 
		
		if(recycling == "b"){
			waste = waste - 0.20;
		}
		if(recycling == "c"){
			waste = waste - 0.50;
		}
		if(compost == "b"){
			waste = waste - 0.10;
		}
		if(compost == "c"){
			waste = waste - 0.30;
		}
		waste = waste / 12;
		
		jQuery('#waste').val(waste.toFixed(4));
		document.getElementById("waste2").innerHTML = waste.toFixed(4);
	}
	
	/* Function that makes sure all values are loaded correctly */
	$(document).ready(function() {
		carbon_waste();
	});
	
	/* Function that creates the edit buttons for the user */
	jQuery("#edit_waste").on("click", function(){
		var waste_records = <?php if(!empty($waste_records)){echo json_encode($waste_records);}else{echo "''";} ?>;
		var waste_records2 = <?php if(!empty($waste_records2)){echo json_encode($waste_records);}else{echo "''";} ?>;
		var size = <?php if(!empty($waste_records)){echo sizeof($waste_records);}else{echo "0";} ?>;
		   
		var waste_list = '<hr><span> Click on a date to edit from this list of your recorded waste emissions: </span><br>';
		waste_list += '<div class="row">';
		if(size != 0){
			for(i = 0; i < size; i++){
				waste_list += '<input type="button" id="waste_record' + i + '" value="' + waste_records[i].date_data_entry + '" ';
				waste_list += 'onclick="edit_waste_record(' + waste_records[i].id + ',' + waste_records2[i].id + ')"></input>';
			}
		}
		waste_list += '</div>';

		$("#waste_list").html(waste_list);
	});

	/* Function that calls edit-waste.php when an edit button is clicked */
	function edit_waste_record(row_id, row_id2){
		var row_id = row_id;
		var row_id2 = row_id2;
		
		var data = 'row_id=' + row_id + '&row_id2=' + row_id2;
		
		jQuery.ajax({
			type: "POST",
			url: "edit-waste.php",
			data: data,
			success: function(html){
				$("#waste_form_holder").html(html);
			},
			error: function (e) {
				 alert("Server Error : " + e.state());
			} 
		});
	}
	
</script>
