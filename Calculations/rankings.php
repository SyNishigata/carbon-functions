<?php
	/*
		Template Name: rankings.php
	*/
	
	include 'functions.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US" >
<script type="text/javascript" src="functions.js"></script>

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<body>
	<div class="button" onclick="show('array')"> Click Here To View Array Structure </div>
	<br>
	<div style="display:none" id="array">
		<?php print_array(); ?>
	</div>
	<br>
	
	Your total CO2 emissions: 
	<div style="display:inline"> <?php echo carbon_ranking()[0]; ?> </div>
	tons of carbon
	<br><br>
	
	Your total CO2 sequestered: 
	<div style="display:inline"> <?php echo carbon_ranking()[1]; ?> </div>
	tons of carbon
	<br><br>
	
	Your total CO2 deficit: 
	<div style="display:inline"> <?php echo (carbon_ranking()[0] - carbon_ranking()[1]); ?> </div>
	tons of carbon
	<br><br>
</body>



<script type="text/javascript">

	// Shows the div with the id given
	function show(id) {
		var e = document.getElementById(id);
		if(e.style.display == 'block'){
			e.style.display = 'none';
		}
		else{
			e.style.display = 'block';
		}
	}
	
</script>
