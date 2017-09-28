<?php 
	/*
		Template Name: add-trees

		Almost all of the following code was copied from \wp-content\plugins\moralab-co2\templates\forms\tree-form.php
	*/

	/*
		The following lines won't work because they use Wordpress specific functions:
		
		global $post;

		// determine if page is a new blank post
		$new = ((get_query_var('mlpage') == 'plant-a-tree')? true:false);

		$trees = get_tree_species();
	*/
	
	/* Fields from co2server database: 
	tree photos
	sequestered
	treeUnit
	treeDiameter
	treeBirth
	longitude
	latitude
	location
	*/
	
	/* Establish the connection with SQL database */
	$hostname = "localhost";
	$database = "carbon_neutrality";
	$username = "carbon_challenge";
	$password = "changeme";
	$conn = mysqli_connect($hostname, $username, $password) or die('Can\'t create connection: '.mysql_error());
	mysqli_select_db($conn, $database) or die('Can\'t access specified db: '.mysql_error());
	
	// Fix this line because it will potentially freeze the page when trying to load too many trees
	// It only works now because it loads just 3 rows. Make a separate php function to create the treeArray with a specific id
	$trees = mysqli_query($conn, "SELECT * FROM library_tree_species WHERE common_name IS NOT NULL");
	$treeArray = array();
	
    while($row = $trees->fetch_array(MYSQLI_ASSOC)) {
		$treeArray[] = $row;
    }
	
	
	/* Declare formSubmit if document is just loading */
	if(empty($_POST['formSubmit'])){
		$formSubmit = '';
	} else {
		$formSubmit = $_POST['formSubmit'];
	}
	
	if($formSubmit == "Submit")
	{
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 

		$result = mysqli_query($conn, "SELECT * FROM user_trees");
		$treeid = mysqli_num_rows($result) + 1;
		$species = $_POST["treeName2"];
		$dateplanted = $_POST["treeBirth"];
		$location = $_POST["location"];
		$latitude = $_POST["latitude"];
		$longitude = $_POST["longitude"];

		
		$sql = "INSERT INTO trees (treeid, species, dateplanted, location, latitude, longitude) 
				VALUES ($treeid, $species, '$dateplanted', '$location', $latitude, $longitude)";

	
		/* Check if query processed correctly */
		if ($conn->query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	
?>

<!-- Begin Sy's Edits: Added a link to the Material Icons by Google -->
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<!-- End Sy's Edits -->

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC7vypkjCVKS6DD_mAaRrMm0aljfF-EhQE&v=3.exp&libraries=places"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>


<div class="content"><div class="container" style="margin-bottom:20px;">
    <div class="panel-heading"><center><h2 style="margin0px">
		<?php echo (!empty($post) && !($new))? '':'Record your planted tree';?>
	</h2></center></div>
	<!-- Carbon Form -->
	<div class="panel-body">
		<div class="row">                   
			<form class="" enctype="multipart/form-data" id="treeForm" method="post" action="http://128.171.9.198/carbon-functions/Tree/add-trees.php">
				<?php //wp_nonce_field('ajax_file_nonce', 'security'); ?>
				<input type="hidden" name="post_id" id="post_id" value="<?php echo !($new)? $post->ID:'0';?>"/> 
				<input type="hidden" name="sequestered" id="sequestered" value="<?php echo (!empty($post) && !($new))? get_post_meta($post->ID, 'sequestered', true):'';?>"/>
				<input type="hidden" name="new" id="new" value="<?php echo $new; ?>"/>
				<input name="action" type="hidden" value="save_tree">
				
				<div class="medium-12 large-5 columns">
					<div class="form-group">
						<!-- Begin Sy's Edits: -Changed columns (for the form) from 3-9 for each input to 5-7.
											   -Added icons to the labels.
											   -Added better placeholders in the form inputs. -->
						<div class="row">
							<div class="small-5 columns">
								<label for="treeName" class="text-right middle">
									<i class="material-icons md-18" style="color:green">local_florist</i>
									Tree Species
								</label>
							</div>
							<div class="small-7 columns">
								<select class="form-control required" name="treeName" id="treeName" onblur="tree_details()" onchange="get_tree_info()" value="<?php echo !empty($post)? $post->post_title:'';?>" placeholder="Enter a name for this tree">
									<option>Select a species</option>
									<?php foreach ($trees as $tree) {
										$selected = '';
										if (!empty($post) && $post->post_title == $tree->ID){
											$selected = 'selected';
										}
										echo '<option value="'. $tree->id . '"'.$selected.'>' . $tree->common_name. '</option>';
									} ?>
								</select>
								<select name="treeName2" id="treeName2" onblur="tree_details()" onchange="get_tree_info()">
									<option>Select a species</option>
									<?php 	
										$trees = mysqli_query($conn, "SELECT * FROM library_tree_species WHERE common_name IS NOT NULL");
										while($row = mysqli_fetch_array($trees)){
											echo '<option value="'. $row['id_species'] . '">' . $row['common_name'] . '</option>';
										} 
									?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="small-5 columns">
								<label for="treeDate" class="text-right middle">
								<i class="material-icons md-18" style="color:green">date_range</i>
								Date Planted
								</label>
							</div>
							<div class="small-7 columns">  
								<input type="text" class="form-control" name="treeBirth" id="treeDate" onblur="tree_project()" value="<?php echo !empty($post)? get_post_meta($post->ID, 'treeBirth', true):'';?>" placeholder="MM/DD/YYY">
							</div>
						</div>

						<div class="row" style="display:none">
							<div class="small-3 columns">
								<label for="treeDiameter" class="text-right middle">Tree Diameter</label>
							</div>
							<div class="small-6 columns">                               
								<input type="text" class="form-control " name="treeDiameter" onchange="tree_sequestered()" id="treeDiameter" value="<?php echo !empty($post)? get_post_meta($post->ID, 'treeDiameter', true):'';?>" placeholder="Type your tree diameter" >
							</div>
							<div class="small-3 columns">
								<select name="treeUnit" id="treeUnit" value="<?php echo !empty($post)? get_post_meta($post->ID, 'treeUnit', true):'';?>" required onchange="tree_sequestered()" style="height:24.75px;line-height:1;">
									<option value="1">in.</option>
									<option value="2">cm.</option>
								</select>
							</div>
						</div>

						<div class="row">
							<div class="small-5 columns">
								<label for="location" class="text-right middle">
								<i class="material-icons md-18" style="color:green">map</i>
								Location
								</label>
							</div>
							<div class="small-7 columns">
								<input type="text" class="form-control required" name="location" id="location" value="<?php echo !empty($post)? get_post_meta($post->ID, 'location', true):'';?>" placeholder="City, State" />
							</div>
						</div>

						<div class="row">
							<div class="small-5 columns">
								<label for="latitude" class="text-right middle">
								<i class="material-icons md-18" style="color:green">place</i>
								Latitude
								</label>
							</div>
							<div class="small-7 columns">
								<input type="text" class="form-control" name="latitude" id="latitude" onblur="tree_locate()" value="<?php echo !empty($post)? get_post_meta($post->ID, 'latitude', true):'';?>" placeholder="See 'Location' tab on right" >
							</div>
						</div>
						
						<div class="row">
							<div class="small-5 columns">
								<label for="longitude" class="text-right middle">
								<i class="material-icons md-18" style="color:green">place</i>
								Longitude
								</label>
							</div>
							<div class="small-7 columns">
								<input type="text" class="form-control " name="longitude" id="longitude" onblur="tree_locate()" value="<?php echo !empty($post)? get_post_meta($post->ID, 'longitude', true):'';?>" placeholder="See 'Location' tab on right" >
							</div>  
						</div>
						
						<!-- End Sy's Edits -->
						
						<div class="photos" id="input_photos">
							<?php if (!empty($post)):
									$photos = get_post_meta($post->ID, 'tree_photos', true);
									if (!empty($photos)):
									foreach ($photos as $photo_url) {
										echo '<input type="hidden" name="tree_photos[]" id="tree_photos[]" value="'. $photo_url .'">';
									}
									endif;
								endif; ?>
						</div>
						
						<input type="submit" id="formSubmit" name="formSubmit" value="Submit" />
						<div class="row in-line">
							<div><a class="button post_submit" style="border-radius: 2px;">Save Tree</a></div>
							<?php //if (!$new){ ?>
							<div><a class="button post_delete" style="border-radius: 2px;">Delete Tree</a></div>
							<?php //} else { ?>
							<div><a class="button cancel" style="border-radius: 2px;">Cancel</a></div>
							<?php //} ?>
						</div>
					</div>
				</div>
			</form>

			<div class="medium-12 large-7 columns">
				<ul class="tabs" data-tabs id="tree-tabs">
					<li class="tabs-title is-active" id="info_tab"><a href="#info" aria-selected="true">Info</a></li>
					<li class="tabs-title" id="projection_tab"><a href="#projection">Projected Sequestration</a></li>
					<li class="tabs-title" id="location_tab"><a href="#map" onblur="initialize()">Location</a></li>
					<li class="tabs-title" id="location_tab"><a href="#photos">Photos</a></li>
				</ul>
				<br><hr>
				<div class="tabs-content" data-tabs-content="tree-tabs">
					<div class="tabs-panel is-active" id="info">
						<div class="info-container" id="info_box">
							<h3> Info </h3>
							<div class="panel-heading"><center><h3 style="margin:0px"><b>Select a species to get information</b></h3></center></div>
							<div class="image-container"><img src="<?php //echo INCLUDES_URL.'/img/plant-tree.png'; ?>"></div>
						</div> 
					</div>
					<hr><br>
					<div class="tabs-panel" id="projection">
						<div class="chart">
							<h3> Projected Sequestration </h3>
							<div class="panel-heading"><center><h3 style="margin:0px"><b>Projected CO2 stored by this tree</b></h3><p id="demo"></p></center></div>
							<div id="container" style="max-width:350px; max-height:400px; margin:5% auto 0;"></div>
						</div> 
					</div>
					<hr><br>
					<div class="tabs-panel" id="map">
						<div class="map-container"> 
							<!-- Map Placement -->
							<h3> Location </h3>
							<div id="mapCanvas" style="width:100%;height:250px;margin-top:10px"></div>
							<label for="mapCanvas" style="text-align:center">Zoom and drag marker as close to tree location</label>
						</div>
					</div>
					<hr><br>
					<div class="tabs-panel" id="photos">
						<!-- Created new format for uploading photos below this 
						<div class="photo-container">
							<div class="fileupload" style="display:none">
								<input type="file" name="photoupload" id="photoupload" accept="image/*">
							</div>
							<div class="button add_photo">Upload Photo</div> 
							<div class="row" id="photo-grid">
								<?php if (!empty($post)):
										$photos = get_post_meta($post->ID, 'tree_photos', true);
										if (!empty($photos)):
										
										foreach ($photos as $photo_url) {
											echo '<div class="small-4 columns"><img src="'. $photo_url. '" width="100" height="100"></div>';
										}
										endif;
									endif; ?>
							</div> 
						</div>
						-->
						
						<form id="uploadimage" action="" method="post" enctype="multipart/form-data">
							<div id="selectImage">
								<h3> Photos </h3>
								<input type="file" name="file" id="file" required />
								<input type="submit" value="Upload" class="submit" />
							</div>
							<div id="image_preview"><img id="previewing" /></div>
							<div id="message"></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>


<script type="text/javascript">
	
	// Sy's Edit: Added a function that sends image file to upload-photo.php 
	$("#uploadimage").on('submit', (function(e) {
		e.preventDefault();
		$("#message").empty();
		$('#loading').show();
		$.ajax({
			url: "upload-photo.php", // Url to which the request is send
			type: "POST", // Type of request to be send, called as method
			data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
			contentType: false, // The content type used when sending data to the server.
			cache: false, // To unable request pages to be cached
			processData: false, // To send DOMDocument or non processed data file it is set to false
			success: function(data){
				$('#loading').hide();
				$("#message").html(data);
			},
			error: function(e){ alert("Server Error : " + e.state() ); }
		});
	}));

	// Function to preview image after validation
	$(function() {
		$("#file").change(function() {
			console.log("test");
			$("#message").empty(); // To remove the previous error message
			var file = this.files[0];
			console.log(file);
			var imagefile = file.type;
			var match = ["image/jpeg", "image/png", "image/jpg"];
			if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2]))) {
				$('#previewing').attr('src', 'noimage.png');
				$("#message").html("<p id='error'>Please Select A valid Image File</p>" + "<h4>Note</h4>" + "<span id='error_message'>Only jpeg, jpg and png image types allowed</span>");
				return false;
			} else {
				var reader = new FileReader();
				reader.onload = imageIsLoaded;
				reader.readAsDataURL(this.files[0]);
			}
		});
	});

	// Function that displays the image preview if it is valid
	function imageIsLoaded(e) {
		console.log("test");
		$("#file").css("color", "green");
		$('#image_preview').css("display", "block");
		$('#previewing').attr('src', e.target.result);
		$('#previewing').attr('width', '250px');
		$('#previewing').attr('height', '230px');
	};
		
</script>

<script>
    function tree_details()
    {
        jQuery("#info_tab").click();
    }

    function tree_project()
    {
        jQuery('#projection_tab').click();
        tree_sequestered();
    }

    function tree_locate()
    {
        jQuery("#location_tab").click();
        initialize();
    }
	
    function get_tree_info()
    {

        var id = isNaN(parseInt(document.getElementById('treeName2').value))? 0:parseInt(document.getElementById('treeName2').value);
        var trees = <?php echo json_encode($treeArray); ?>;
        var tree = trees[id-1];

        if (id > 0){
            var info_box = '';

            info_box += '<div class="panel-heading"><center><h3 style="margin:0px"><b>' + tree.common_name + '</b></h3></center></div>';
            info_box += '<div class="row"><div class="small-6 columns">';
            info_box += '<div class="image-container"><img src="' + '/carbon-functions/Tree/trees/' + 'TreeID' + tree.id_species + '.png" width="150" height="150"></div>';
            info_box += '</div>';
            info_box += '<div class="small-6 columns">'
            info_box += '<div class="tree-details"><span><strong>Genus:</strong> '+ tree.genus + '</span><br/>';
            info_box += '<span><strong>Name:</strong> ' + tree.name + '</span><br/>';
            info_box += '<span><strong>Diameter:</strong> '+ tree.diameter + 'cm</span><br/></div></div></div>';
            info_box += '<div style="margin:20px;"><span>' + tree.info + '</span></div>';

            document.getElementById('info_box').innerHTML = info_box;
            jQuery('#treeDiameter').val(tree.diameter);
            jQuery('#treeUnit').val('2');
            tree_sequestered();
            jQuery("#info_tab").click();
        }
		
		
    }

    function initialize() 
    {
        var geocoder = new google.maps.Geocoder();


        var LAT = document.getElementById("latitude").value;
        var LON = document.getElementById("longitude").value;

        var latLng = new google.maps.LatLng(LAT, LON);

        var zoom_level = 6;
        if (LAT != null && LON !=null){
            zoom_level = 1;
        }
        var map = new google.maps.Map(document.getElementById('mapCanvas'), 
        {zoom: zoom_level, center: latLng, streetViewControl:false, mapTypeId: google.maps.MapTypeId.ROADMAP});
        var marker = new google.maps.Marker({position: latLng, map: map, draggable: true});

        //fill the boxes with the coordenates
        google.maps.event.addListener(marker, 'dragend', function (event) 
        {
            document.getElementById("latitude").value =  Math.round(this.getPosition().lat()*100000)/100000;
            document.getElementById("longitude").value = Math.round(this.getPosition().lng()*100000)/100000;

            //centers map on marker
            var latLng = marker.getPosition(); // returns LatLng object
            map.setCenter(latLng); // setCenter takes a LatLng object
        }); 
     }

	// Onload handler to fire off the app.
	google.maps.event.addDomListener(window, 'load', initialize);
	
	
	function tree_sequestered() {

        var AccumulatedCO2 = 0.0;
        var TreeDiameter = document.getElementById("treeDiameter").value;
        var Results = [];
        var CO2 = "";
        var d = new Date();
        var YearPlanted = d.getFullYear();
        var YearOfCalculation = d.getFullYear();

		//--start--adjust for the units of the tree diameter selected by the user
        var UnitSelected = document.getElementById('treeUnit').options[document.getElementById('treeUnit').selectedIndex].value;
        if (UnitSelected == 1) {
            var TreeDiameter = TreeDiameter / 0.393701;
        } else {
            var TreeDiameter = TreeDiameter;
        }
		//--end--adjust for the units of the tree diameter selected by the user


        for (i = 0; i <= 85; i++) {
            YearOfCalculation = i + YearPlanted;
            if (YearOfCalculation >= YearPlanted) {

                //Body mass (kg dry above groung matter) from Chave et al (2001):
                BodyMass = 0.0998 * (Math.pow(TreeDiameter, 2.5445));

                //Growth Rate (kg dry above groung matter/ plant /yr) from Niklas & Enquist (2001):
                GrowthRate = 0.208 * (Math.pow(BodyMass, 0.763));

                //dK/dy Above ground, this is the rate of production at each year assuming log decline:
                dKdY = (Math.exp(1 - (((GrowthRate * Math.exp(1)) * (YearOfCalculation - YearPlanted)) / BodyMass)) / Math.exp(1)) * (GrowthRate * Math.exp(1));

                //Adding Below ground Using Cairns et al (1997) factor of 24% of above ground biomass:
                dKdYT = dKdY * 1.24;

                //Carbon content Using Kirby & Potvin (2007) factor of 47% of total dry weight:
                Carbon = dKdYT * 0.47;

                //CO2 sequestration.Conversion of Carbon in treee to CO2:
                CO2 = Carbon * 3.6663;

                //adds CO2 over the years:
                AccumulatedCO2 = AccumulatedCO2 + CO2;

				//Generates data.frame that includes year:
                Results[i] = Math.round(AccumulatedCO2 * 10.0) / 10.0;

            } else {
                Results[i] = 0;
            }

        }
        var tones_value = (AccumulatedCO2 / 1000.0);
        var sequestered = isNaN(parseFloat(tones_value)) ? 0.0:parseFloat(tones_value);
        //alert(AccumulatedCO2);
        //alert(tones_value);
        jQuery('#sequestered').val(sequestered.toFixed(2));
        document.getElementById("demo").innerHTML = "This tree will sequester " + (sequestered.toFixed(2)) + " tonnes of CO2 over its life time";
        setTimeout(function () {

			// generates a variable with the data to be plotted in the x-y chart
            var Results1 = Results;


            jQuery('#container').highcharts({
                chart: {type: 'scatter', zoomType: 'x', width:'340', height: '200'},
                title: {text: ''},
                credits: false,
                tooltip: {headerFormat: '<b></b>', pointFormat: "It will sequester {point.y}kg by {point.x:%Y}", hideDelay: 1},
                xAxis: {type: 'datetime', },
                yAxis: {title: {text: 'CO2 sequestered (kg)'}, min: 0},
                legend: {enabled: false},
                plotOptions:
                        {
                            area: {
                                fillColor:
                                        {
                                            linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1},
                                            stops: [[0, Highcharts.getOptions().colors[0]], [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]]
                                        },
                                marker: {radius: 2},
                                lineWidth: 1,
                                states: {hover: {lineWidth: 1}},
                                threshold: null
                            }
                        },
                series: [{
                        type: 'area',
                        name: 'Cummulative CO2 stored',
                        pointInterval: 365 * 24 * 3600000,
                        pointStart: Date.UTC(2015, 0, 1),
                        data: Results1
                    }]
            });

        }, 1);

    }
</script>

<script>
    jQuery(function ($) {
        get_tree_info();
        tree_sequestered();

        $( "#treeDate" ).datepicker();

    });
</script>

<script>
    (function($){
        $(".cancel").on("click", function(){
            window.location = '<?php echo get_site_url().'/my-carbon'; ?>';
        });
        
        $("body").on("click", ".post_delete", function(){
            var id = document.getElementById('post_id').value;
            var formdata = new FormData();
            formdata.append('ID', id);
            formdata.append('action', 'delete_tree');
            $.ajax({
            url: "<?php echo admin_url('admin-ajax.php');?>",
            type: 'POST',
            data: formdata,
            dataType: "json",
            contentType:false,
            processData:false,
            success: function(data){
                window.onbeforeunload = null;
                location.href = "<?php echo get_site_url().'/my-carbon'; ?>";
            },
            error: function(e){ alert("Server Error : " + e.state() ); }
        });

        });
    })(jQuery);
</script>

<script type="text/javascript">
    (function($){
        $('#mk-page-introduce').hide();
	$('a.mk-post-nav').hide();
    
        $("body").on("click", ".post_submit", function(){
            var options = {};
            options.type = "post";
            options.url = "<?php echo admin_url('admin-ajax.php');?>";
            options.data = $('#treeForm').serialize();
            options.dataType = "json";
            options.error = function(e){ alert("Server Error : " + e.state() ); };
            options.success = function(d){
                if(d.result == true){
                    window.transmission = true;
                    switch(d.status){
                        default:
                    };
                    console.log(d.post);
                    location.href = "<?php echo get_site_url().'/my-carbon'; ?>"

                };
            };
            $.ajax(options);
        });
        window.transmission = false;
        $("form").submit(function(){ window.transmission = true; });
        window.onbeforeunload = function(){ if(!window.transmission) return ""; };
    })(jQuery);
</script>

