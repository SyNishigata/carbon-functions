<?php 
global $post;

// determine if page is a new blank post
$new = ((get_query_var('mlpage') == 'plant-a-tree')? true:false);

$trees = get_tree_species();
?>

<!-- Begin Sy's Edits: Added a link to the Material Icons by Google -->
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<!-- End Sy's Edits -->

<div class="content"><div class="container" style="margin-bottom:20px;">
    <div class="panel-heading"><center><h2 style="margin0px"><?php echo (!empty($post) && !($new))? '':'Record your planted tree';?></h2></center></div>
        <!-- Carbon Form -->
        <div class="panel-body">
        <div class="row">                   
        
            <form class="" enctype="multipart/form-data" id="treeForm" method="post" action="">
            <?php wp_nonce_field('ajax_file_nonce', 'security'); ?>
            <input type="hidden" name="post_id" id="post_id" value="<?php echo !($new)? $post->ID:'0';?>"/> 
            <input type="hidden" name="sequestered" id="sequestered" value="<?php echo (!empty($post) && !($new))? get_post_meta($post->ID, 'sequestered', true):'';?>"/>
            <input type="hidden" name="new" id="new" value="<?php echo $new; ?>"/>
            <input name="action" type="hidden" value="save_tree">
<!--             <div class="medium-4 large-4 columns">
                <div class="form-group">
                    <div class="row">
                        <div id="preview" style="width:200px; height: 200px; overflow:hidden;border: 1px solid #c6c6c6;margin: auto;">
                            <img class="thumbnail" width="auto" height="auto" src="<?php //echo !empty($post) && !empty(get_post_meta($post->ID, 'img_url', true))? get_post_meta($post->ID, 'img_url', true):INCLUDES_URL.'/img/img_placeholder.png';?>" id="featured_img" style="display:block;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="upload-button">
                            <center><label for="image" class="button" style="font-size: 13px; padding: 8px; border-radius: 2px; margin: 10px 0;}">Upload Photo</label></center>
                            <input type="file" name="image" id="image" class="show-for-sr" >
                            <input type="hidden" name="img_url" id="img_url" value="<?php //echo !empty($post) && !empty(get_post_meta($post->ID, 'img_url', true))? get_post_meta($post->ID, 'img_url', true):'';?>">
                        </div>
                    </div>
                </div>
            </div> -->

			
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
                                    echo '<option value="'. $tree->ID . '"'.$selected.'>' . $tree->Common_Name. '</option>';
                                } ?>
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
                        <div class="small-5 columns" style="float:left;">
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

					<!-- Begin Sy's Edits: Added an extra outer div that moved the buttons to the right -->
					<div style="text-align:right">
						<div class="row in-line">
							<div><a class="button post_submit" style="border-radius: 2px;">Save Tree</a></div>
							<?php if (!$new){ ?>
							<div><a class="button post_delete" style="border-radius: 2px;">Delete Tree</a></div>
							<?php } else { ?>
							<div><a class="button cancel" style="border-radius: 2px;">Cancel</a></div>
							<?php } ?>
						</div>
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

        <div class="tabs-content" data-tabs-content="tree-tabs">
            <div class="tabs-panel is-active" id="info">
                <div class="info-container" id="info_box">
                    <div class="panel-heading"><center><h3 style="margin:0px"><b>Select a species to get information</b></h3></center></div>
                    <div class="image-container"><img src="<?php echo INCLUDES_URL.'/img/plant-tree.png'; ?>"></div>
                </div> 
            </div>
            <div class="tabs-panel" id="projection">
                <div class="chart">
                    <div class="panel-heading"><center><h3 style="margin:0px"><b>Projected CO2 stored by this tree</b></h3><p id="demo"></p></center></div>
                    <div id="container" style="max-width:350px; max-height:400px; margin:5% auto 0;"></div>
                </div> 
            </div>
            <div class="tabs-panel" id="map">
                <div class="map-container"> 
                    <!-- Map Placement -->
                    <div id="mapCanvas" style="width:100%;height:250px;margin-top:10px"></div>
                    <label for="mapCanvas" style="text-align:center">Zoom and drag marker as close to tree location</label>
                </div>
            </div>
                        <div class="tabs-panel" id="photos">
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
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>


<script type="text/javascript">
    jQuery(function ($) {
        $(".add_photo").on("click", function(){
            $('#photoupload').click();
        });
        
        $(":file").change(function () {
            if (this.files && this.files[0]) {
                // Upload Image
                upload_image();
            }
        });
    });

    function upload_image(){
        var imgInput = document.getElementById('photoupload');
        var file = imgInput.files[0];
        var formdata = new FormData();

        formdata.append('file', file);
        formdata.append('action', 'image_uploader');

        jQuery.ajax({
            url: "<?php echo admin_url('admin-ajax.php');?>",
            type: 'POST',
            data: formdata,
            dataType: "json",
            contentType:false,
            processData:false,
            success: function(data){
                var img = data.img_url;
                var img_form = '<input type="hidden" name="tree_photos[]" id="tree_photos[]" value="'+ img +'">';
                jQuery('#input_photos').append(img_form);

                var img_view = '<div class="small-4 columns"><img src="'+ img+ '" width="100" height="100"></div>';
                jQuery('#photo-grid').append(img_view);

                console.log(img);
            },
            error: function(e){ alert("Server Error : " + e.state() ); }
        });
    }
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

        var id = isNaN(parseInt(document.getElementById('treeName').value))? 0:parseInt(document.getElementById('treeName').value);
        var trees = <?php echo json_encode($trees); ?>;
        var tree = trees[id-1];

        if (id > 0){
            var info_box = '';

            info_box += '<div class="panel-heading"><center><h3 style="margin:0px"><b>' + tree.Common_Name + '</b></h3></center></div>';
            info_box += '<div class="row"><div class="small-6 columns">';
            info_box += '<div class="image-container"><img src="' + '/wp-content/plugins/moralab-co2/includes/img/trees/' + 'TreeID' + tree.ID + '.png" width="150" height="150"></div>';
            info_box += '</div>';
            info_box += '<div class="small-6 columns">'
            info_box += '<div class="tree-details"><span><strong>Genus:</strong> '+ tree.Genus + '</span><br/>';
            info_box += '<span><strong>Name:</strong> ' + tree.Name + '</span><br/>';
            info_box += '<span><strong>Diameter:</strong> '+ tree.Diameter + 'cm</span><br/></div></div></div>';
            info_box += '<div style="margin:20px;"><span>' + tree.Info + '</span></div>';

            document.getElementById('info_box').innerHTML = info_box;
            jQuery('#treeDiameter').val(tree.Diameter);
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