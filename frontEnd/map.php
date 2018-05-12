<!-- 

File Name: map.html

Description: This file contains code to display and make
appropriate computations for team Purple Parrot's version
of the Map Project for Professor Russ Cain's Spring 2018 
CMSC447 course.

References: 

http://www.usnaviguide.com/zip.htm
https://blog.vizuri.com/how-to-create-a-choropleth-map-with-geojson-and-google-maps
https://www.data.gov/

-->

<!DOCTYPE html>
<?php
	session_start();
	if (!isset($_SESSION["HAS_LOGGED_IN"])) {
		if (!$_SESSION["HAS_LOGGED_IN"]) {
			header('Location:index.php');
		}
	}
	
	$db = new PDO("sqlite:db/447db.sqlite") or die("Unable to open the database.");
	$username = $_SESSION["USERNAME"];
	
	$zipCodes = [];
	
	// Store all the zip codes in an array
	$count = 0;
	$query = "SELECT * FROM Restaurants WHERE 1";
	foreach ($db->query($query) as $row){
		$zipCodes[$count] = $row;
		$count++;
	}
	
	foreach($zipCodes as $row){
		// $row[0] = Zipcode
		// echo $row[1] = # of Chic-fil-a
		// echo $row[2] = # of Taco Bell
		// echo $row[3] = $ of Starbucks
	}
?>
<html> 

<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
<meta charset="utf-8">
   
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Josefin Slab">
<!--   <link rel="stylesheet" href="styles.css"> -->
<!-- w3-mobile gives mobile responsiveness -->
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Welcome to ZipCompare!</title>

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://rawgit.com/s-yadav/jsonQ/master/jsonQ.js"></script>

<style>
.w3-josefin {
  font-family: 'Josefin Slab', serif;
}
body { background-color: lightyellow; }
</style>

<body>

<div class="w3-top w3-animate-left w3-mobile">
  <div class="w3-bar w3-khaki w3-wide w3-padding w3-card w3-text-brown w3-josefin w3-xlarge w3-mobile"><br>
    <b>ZipCompare
    <div class="w3-right">
      <button class="w3-bar-item w3-button tablink w3-hover-blue w3-mobile" onclick="openUITab(event, 'map_tab')" id="map_tab_view">Map</button>
      <button class="w3-bar-item w3-button tablink w3-hover-blue w3-mobile" onclick="openUITab(event, 'favorites_tab')">Favorites</button>
      <button class="w3-bar-item w3-button tablink w3-hover-blue w3-mobile" onclick="openUITab(event, 'user_tab')">Account</button>    </div>
    </b>
  </div>
</div>

<!-- Page page content -->
<div class="w3-content w3-padding w3-mobile" style="max-width:1564px">

  <!-- Map tab -->
  <div class="w3-container w3-animate-top tab_view w3-mobile" id="map_tab">
 
    <!-- Clear Zip Data Modal -->
    <div id="clear_modal" class="w3-modal w3-animate-opacity w3-josefin w3-mobile">

      <div class="w3-modal-content w3-card-4 w3-josefin w3-mobile">
        <header class="w3-container w3-blue"> 
          <span onclick="document.getElementById('clear_modal').style.display='none'" 
          class="w3-button w3-large w3-display-topright w3-mobile">&times;</span>
          <h2>Clear all zip code data?</h2>
      </header>
      <div class="w3-container w3-khaki w3-josefin">
        <button class="w3-bar-item w3-button w3-hover-green w3-mobile" onclick="clearAllZips()">Yes</button>
        <button class="w3-bar-item w3-button w3-hover-red w3-mobile" onclick="hideModal()">No</button>
      </div>
      <footer class="w3-container w3-brown w3-mobile">
        <p>ZipCompare</p>
      </div>

    </div>

    <!-- Invalid Zip Modal -->
    <div id="zip_not_found" class="w3-modal w3-animate-opacity w3-josefin w3-mobile">

      <div class="w3-modal-content w3-card-4 w3-josefin w3-mobile">
        <header class="w3-container w3-blue"> 
          <span onclick="document.getElementById('clear_modal').style.display='none'" 
          class="w3-button w3-large w3-display-topright w3-mobile">&times;</span>
          <h3>You entered a zip code outside the United States or which is otherwise invalid.</h3>
      </header>
      <div class="w3-container w3-khaki w3-josefin">
        <button class="w3-bar-item w3-button w3-hover-green w3-mobile" onclick="hideModal()">Return</button>
      </div>
      <footer class="w3-container w3-brown w3-mobile">
        <p>ZipCompare</p>
      </div>

    </div>

    <!-- Invalid Zip Modal -->
    <div id="invalid_click" class="w3-modal w3-animate-opacity w3-josefin w3-mobile">

      <div class="w3-modal-content w3-card-4 w3-josefin w3-mobile">
        <header class="w3-container w3-blue"> 
          <span onclick="document.getElementById('clear_modal').style.display='none'" 
          class="w3-button w3-large w3-display-topright w3-mobile">&times;</span>
          <h3>You clicked an area outside the United States which is not allowed.</h3>
      </header>
      <div class="w3-container w3-khaki w3-josefin">
        <button class="w3-bar-item w3-button w3-hover-green w3-mobile" onclick="hideModal()">Return</button>
      </div>
      <footer class="w3-container w3-brown w3-mobile">
        <p>ZipCompare</p>
      </div>

    </div>

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-half w3-center w3-mobile" id="click_state">
      Click on a State to Zoom In
    </div>

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-quarter w3-center w3-mobile">
      Zip Code Information
    </div>

    <div class="w3-container w3-border w3-border-brown w3-xlarge w3-josefin w3-half w3-center w3-mobile" style="height:650px;" id="main_map"> </div>

    <!-- Zip Frame -->
    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-quarter w3-center w3-mobile" style="height:650px;" id="zip_frame">

      <!-- Current Zip -->
      <div class="w3-display-container w3-border w3-border-khaki w3-brown w3-large w3-josefin w3-rest w3-center w3-margin w3-text-white w3-mobile" style="height:250px;" id="current_zip">
      <div class="w3-display-middle">No zip currently selected.</div>
      </div>

      <!-- Zip List -->
      <div class="w3-display-container w3-border w3-border-khaki w3-brown w3-large w3-josefin w3-rest w3-center w3-margin w3-text-white w3-mobile" style="height:135px;" id="zip_list">
      <div class="w3-display-middle">Comparison list is empty.</div>
      </div>

      <!-- Zip buttons -->
      <div class="w3-container w3-border w3-border-khaki w3-brown w3-xlarge w3-josefin w3-res w3-center w3-margin w3-text-khaki w3-mobile">

        <div class="w3-container w3-large w3-margin-top">

 
          <input class="w3-input w3-border w3-border-khaki w3-margin-left w3-mobile w3-third" value="" id="userzip">
          <button class="w3-button w3-light-blue w3-hover-blue w3-mobile w3-margin-bottom" onclick="zipSearch()">Zip Search</button>
          <button class="w3-button w3-light-blue w3-hover-blue w3-mobile" onclick="resetMap()">Reset Map Zoom</button>
          <button class="w3-button w3-light-blue w3-hover-blue w3-margin-top w3-margin-bottom w3-mobile" onclick="clearModal()">Reset Zip Data</button>
          

        </div>

      </div>

    </div>
  

  </div>


  <!-- Favorites Tab -->
  <div class="w3-container w3-animate-top tab_view w3-mobile" id="favorites_tab" style="display: none;">

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-half w3-center w3-mobile">
      Favorite Zip Codes    
    </div>

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-quarter w3-center w3-mobile">
      Zip Code Information
    </div>

    <div class="w3-display-container w3-blue w3-border w3-border-brown w3-xlarge w3-josefin w3-half w3-center w3-mobile" style="height:650px;" id="user_favorite_list">

      <div class="w3-display-middle w3-mobile" id="favorites_container">

        <!--Query the DB to get the user's favorites. I will add code to
        create a button for each one,<br>
        and populate the data in the divs on this page. -->

      </div>

    </div>

    <!-- Zip Frame -->
    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-quarter w3-center w3-mobile" style="height:650px;">

      <!-- Current Zip -->
      <div class="w3-display-container w3-border w3-border-khaki w3-brown w3-large w3-josefin w3-rest w3-center w3-margin w3-text-white w3-mobile" style="height:250px;" id="current_zip2">
      <div class="w3-display-middle">No zip currently selected.</div>
      </div>

      <!-- Zip List -->
      <div class="w3-display-container w3-border w3-border-khaki w3-brown w3-large w3-josefin w3-rest w3-center w3-margin w3-text-white w3-mobile" style="height:135px;" id="zip_list2">
      <div class="w3-display-middle">Comparison list is empty.</div>
      </div>

      <!-- Zip buttons -->
      <div class="w3-container w3-border w3-border-khaki w3-brown w3-xlarge w3-josefin w3-res w3-center w3-margin w3-text-khaki w3-mobile" style="height: 145px;">
        <div class="w3-container w3-medium w3-margin-top">

          <input class="w3-input w3-border w3-border-khaki w3-left w3-margin-left w3-mobile" value="" style="width: 110px; height: 38px;" id="userzip">
          <button class="w3-button w3-light-blue w3-hover-blue w3-left w3-mobile" onclick="zipSearch()">Zip Search</button>

        </div>

        <div class="w3-container w3-medium w3-margin-top w3-mobile">

          <button class="w3-button w3-light-blue w3-hover-blue w3-margin-top w3-mobile" onclick="">Clear Comparison List</button>

        </div>

      </div>

    </div> 
  

  </div>

  <!-- Account tab -->
  <div class="w3-container w3-animate-top tab_view w3-mobile" style="display: none;" id="user_tab">

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-half w3-center w3-mobile">
        Account Information  
    </div>

    <div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-quarter w3-center w3-mobile">
      Optional Div
    </div>

    <div class="w3-display-container w3-blue w3-border w3-border-brown w3-xlarge w3-josefin w3-half w3-center w3-mobile" style="height:650px;">

      <div class="w3-display-middle w3-mobile">

		<?php
			$query = "SELECT * FROM accounts WHERE username = '$username'";
			foreach ($db->query($query) as $row){
				echo "Username: ";
				echo $row[1];
				echo "<br>First Name: ";
				echo $row[3];
				echo "<br>Last Name: ";
				echo $row[4];
				echo "<br>Email: ";
				echo $row[5];
				echo "<br>Zipcode: ";
				echo $row[6];
			}
		?>
		
      </div>

    </div>

  </div>

</div>

<!-- End page content -->

<script>
var main_map;
var glob_zip;
var new_boundary;
var state_json;
var zip_arr = [];
var state_color_arr = [];
var marker_index = 0;
var num_zips_selected = 0;
var zip_list_html = "No zip codes selected.";
var zoom_recently_updated = 0;
var num_states = 51;
var new_state;
var state_check = '../us-states.json';
var state_strings = [ 'Alaska','Michigan','North Carolina', 'Tennessee', 'Florida', 'Minnesota', 'Oklahoma', 'Texas', 'Montana', 
                     'Idaho', 'California', 'Nevada', 'Maine', 'New Hampshire', 'New York', 'Maryland', 'Pennsylvania',
                     'Virginia', 'Wisconsin', 'Indiana', 'Illinois', 'West Virginia', 'Ohio', 'South Carolina', 'Kentucky',
                     'Georgia', 'Alabama', 'Mississippi', 'Louisiana', 'Arkansas', 'Missouri', 'Iowa', 'North Dakota',
                     'South Dakota', 'Nebraska', 'Kansas', 'Wyoming', 'Colorado', 'New Mexico', 'Arizona', 'Utah', 'Washington',
                     'Oregon', 'Vermont', 'Massachusetts', 'Delaware', 'New Jersey', 'Hawaii', 'Rhode Island', 'Connecticut',
                     'Washington D.C.' ];
var hex_colors = ['#f44295','#efce94','#07ba31','#c6abcc','#f9f193','#c47105','#006600','#777777', '#00ffcc', '#ffbf00','#ff00ff','#996633','#ff99ff', '#009999','#339933'];
var current_color = 0;
var zoom = 5;
var style_placeholder;
var lat = 41.8375511;
var lon = -87.6818441;
var map = null;
var tskey = "b300a5e050" ;
var current_zip_marker;
var infowindow;

function initMap() {

  // The zip code layer uses an ImageMapType for
  // the 
  imageMapType = new google.maps.ImageMapType({

    getTileUrl: function(coord, zoom) {

      if (zoom < 4 || zoom > 13) {

        return null;

      }

      if (zoom >= 10) {

        var url = "https://storage.googleapis.com/zipmap/tiles/" + zoom + "/" + coord.x + "/" + coord.y + ".png" ;       
        return url ;
      
      }
      
      var server = coord.x % 6 ;
      var url = "http://ts" + server + ".usnaviguide.com/tileserver.pl?X=" + coord.x + "&Y=" + coord.y + "&Z=" + zoom + "&T=" + tskey + "&S=Z1001" ;
      return url ;

   },
   tileSize: new google.maps.Size(256, 256),
   opacity:.5,
   name:'Zip Code'

  });
   
  var mapOptions = {

      minZoom: 4,
      maxZoom: 13,
      zoom: 4,
      center: new google.maps.LatLng(37, -95),
      mapTypeIds: google.maps.MapTypeId.ROADMAP

  };
  main_map = new google.maps.Map(document.getElementById('main_map'), mapOptions);
  main_map.overlayMapTypes.push(imageMapType);

  // Add a global marker to be swapped around.
  current_zip_marker = new google.maps.Marker;

  // New window that responds to mouse clicks.
  infowindow = new google.maps.InfoWindow;

  // Listen for clicks to the marker.
  current_zip_marker.addListener('click', function(event) {

     var zip;
     var geocoder = new google.maps.Geocoder();
     geocoder.geocode({'latLng': event.latLng}, function(results, status){
         
     // Ensure the status is good.
     if (status == google.maps.GeocoderStatus.OK) {

       // Iterate through the results array to find the zip code.
       var addressComponent = results[0].address_components;            
       for (var x = 0 ; x < addressComponent.length; x++) {

         var chk = addressComponent[x];

         if (chk.types[0] == 'postal_code') {

           zip = chk.long_name;

         }

       }

     }

   });

    var info_content = '<div class="w3-container w3-border w3-border-brown w3-blue w3-xlarge w3-josefin w3-center w3-mobile"><div class="w3-container w3-margin">Data for ' + zipCode + ' Displayed in the Viewport on the Right</div></div>';
    infowindow.setContent(info_content);
    infowindow.open(main_map, current_zip_marker);

  });

  main_map.addListener('click', function(event) {

     getZipInfo(event.latLng, main_map);
     
     if(main_map.getZoom() >= 10){

       zipZoom(event.latLng, main_map);

     }

  });

  // Adjust the thresholds for showing each layer.
  main_map.addListener('zoom_changed', function() {
 
    if(main_map.getZoom() >= 10){

      document.getElementById('click_state').innerHTML = "Click on a Zip Code for more Information";

      state_json.setMap(null);
      main_map.setOptions({draggableCursor:'pointer'});


    }

    else if(main_map.getZoom() < 10){

      document.getElementById('click_state').innerHTML = "Click on a State to Zoom In";            
      state_json.setMap(main_map);
      main_map.setOptions({draggableCursor:''});  
      current_zip_marker.setMap(null);

    }    

  });

  // Add a color for each state.
  setStateColors();   

  // Display the map div, leaving the other tab divs hidden.
  document.getElementById('map_tab').style.display = "block";   

  state_json = new google.maps.Data();
  state_json.loadGeoJson('us-states.json');
  state_json.setStyle(function(feature) {

    // Get the color that was already set.
    for(var i = 0; i < state_color_arr.length; i++){
       
      if(state_color_arr[i].state_name == feature.getProperty('name')){

         var new_color = state_color_arr[i].state_color;

         return {

          fillColor: new_color, 
          fillOpacity: 0.8,
          strokeColor: '#000000',
          strokeWeight: 1,
          zIndex: 1

        };

      }

    }
 
  });

  // Add the state json layer to the map.
  state_json.setMap(main_map);
  
  // Add the border change to mouseover.
  state_json.addListener('mouseover', function(event) {

    // Get the color that was already set.
    for(var i = 0; i < state_color_arr.length; i++){
       
      if(state_color_arr[i].state_name == event.feature.getProperty('name')){

         var new_color = state_color_arr[i].state_color;
         //state_json.revertStyle(); 
         state_json.overrideStyle(event.feature, { fillColor: new_color, strokeWeight: 2, strokeColor: '#FFFFFF',zIndex: 2});

      }
       
    }

  });

  state_json.addListener('mouseout', function(event) {

    state_json.revertStyle();

  });

  state_json.addListener('click', function(event) {

    state_json.revertStyle();

  });

  state_json.addListener('click', function(event) {

    if(main_map.getZoom() <= 10){ 

      zipZoom(event.latLng, main_map);

    }

  });

  // Show that the map tab is currently selected on page load.
  document.getElementById('map_tab_view').className += " w3-blue";

}

function mapZoom(latLng, map) {

  var marker = new google.maps.Marker({

    position: latLng,
    map: map

  }); 

  map.setCenter(marker.getPosition());
          
  // Sets a limit for how far you can zoom in.
  if (map.getZoom() < 10) { map.setZoom(map.getZoom() + 2); }
         
  marker.setMap(null);

}

function setStateColors(){

  // Set all the colors of different states.
  for(var i = 0; i < num_states; i++){

    new_state = { state_name: state_strings[i], state_color: hex_colors[i % 15]};
    state_color_arr.push(new_state); 

  }

}

function zipZoom(latLng, map) {

  var center;
  var new_zoom = main_map.getZoom();
  new_zoom += 2;

  infowindow.close(main_map, current_zip_marker);

  current_zip_marker.setPosition(latLng); 
  if(main_map.getZoom() >= 10){ current_zip_marker.setMap(map); }     
          
  map.setCenter(current_zip_marker.getPosition());
  center = map.getCenter();

  // Zooms in to display zips.
  map.setZoom(new_zoom);
   
  //marker.setMap(null);

}

function zipZoomCloser(latLng, map){

    var center;
    var new_zoom = 13;
    var latLng = latLng;
 
    infowindow.close(main_map, current_zip_marker);

    var marker = new google.maps.Marker({

      position: latLng,
      map: map

    }); 
          
    map.setCenter(marker.getPosition());
    center = map.getCenter();

    // Zooms in to display zips.
    map.setZoom(new_zoom);          
    marker.setMap(null);

}

// Computes the zip code based on the lat and lng
// received from the click event.
function getZipInfo(latLng, map){

  var geocoder = new google.maps.Geocoder();
  var country;
  geocoder.geocode({'latLng': latLng}, function(results, status){
         
    // Ensure the status is good.
    if (status == google.maps.GeocoderStatus.OK) {

      // Iterate through the results array to find the zip code.
      var addressComponent = results[0].address_components;            
      for (var x = 0 ; x < addressComponent.length; x++) {

        var chk = addressComponent[x];

        if (chk.types[0] == 'postal_code') {

          zipCode = chk.long_name;

        }

        if (chk.types[0] == 'country' || chk.types[0] == 'political'){

          country = chk.short_name;

        }

      }

    }

    if (zipCode && (country == 'US')) {
 
      glob_zip = zipCode;         
       
      // Zip not in the array.
      if(zipCheck() && num_zips_selected < 3){

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="addToList()">Add and Compare</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

        
      }

      // Zip already in the array.
      else if(!zipCheck() && num_zips_selected <= 3){ 

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="removeFromList()">Remove Comparison List</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';


      }

      // Just display the zip data.
      else{

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }
             
    }

    else{ invClickModal(); }
  
      

  }); 

}

// Used to display the zip data when a 
// zip button is clicked on the zip list.
function zipButtonDisplay(zipCode){

  if (zipCode) {

      glob_zip = zipCode;
      var address = zipCode;

      // Attempt to geocode using the user's entered zip code.
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({'address': address}, function(results, status){
          
        // Ensure the status is good.
        if (status == google.maps.GeocoderStatus.OK) {

          zipZoomCloser(results[0].geometry.location, main_map);

        }

      }); 

      // Zip not in the array.
      if(zipCheck() && num_zips_selected < 3){

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="addToList()">Add and Compare</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }

      // Zip already in the array.
      else if(!zipCheck() && num_zips_selected <= 3){ 

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="removeFromList()">Remove Comparison List</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }

      // Just display the zip data.
      else{

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }
   
  }

}
 
// Reset the map to the original zoom level.
function resetMap(){

  var myLatlng = {lat: 37, lng: -95};
  main_map.setCenter(myLatlng);
  main_map.setZoom(4);
  document.getElementById('current_zip').innerHTML = '<div class="w3-display-middle">No zip currently selected.</div>';   
  current_zip_marker.setMap(map); 

}

function addToList() {

  if (num_zips_selected < 3){

      // Returns 1 if the zip is not in the array.
      var zip_valid = zipCheck();

      if(zip_valid){

        // Add the zip code to the array, and increment
        // the number of zip codes there.
        zip_arr[num_zips_selected] = glob_zip;           
        num_zips_selected = num_zips_selected + 1;
        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container w3-padding-small"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="removeFromList()">Remove from Comparison</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';
        setZipList();

     }

  }

}

function removeFromList() {

  // Iterate through zip array, and
  // pull out those  that must be kept.
  var temp_list = [];
  var j = 0;
  if (num_zips_selected > 0){

    for (var i = 0; i < 3; i++){

      if (zip_arr[i] != glob_zip){

        temp_list[j] = zip_arr[i];
        j += 1;

      }

    }

  }

  // Reset the zip list div.
  num_zips_selected -= 1;
  zip_arr = temp_list;
  setZipList();

  // Since we just removed a zip code, we know that
  // the currently selected zip div can be updated to
  // offer to re-add the one which was removed.
  document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="addToList()">Add and Compare</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

}

// Appropriately sets the zip list div based on the number of
// elements currently in the array.
function setZipList(){

  // Add just one button. Also adds listener to
  // Re-display the data in the currently selected zip div
  // when clicked.

  if(num_zips_selected == 0){

    document.getElementById('zip_list').innerHTML = '<div class="w3-display-middle">Comparison list is empty.</div>';


  }

  else if(num_zips_selected == 1){

    document.getElementById('zip_list').innerHTML = '<button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" id="zip1" name = "zip1"></button>';
    document.getElementById('zip1').value = zip_arr[num_zips_selected - 1];
    document.getElementById('zip1').innerHTML = zip_arr[num_zips_selected - 1];
    document.getElementById('zip1').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 1])");

  }

  // Add two buttons.
  else if(num_zips_selected == 2){

    document.getElementById('zip_list').innerHTML = '<button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top w3-margin-right" id="zip1" name="zip1"></button><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" id="zip2" name="zip2"></button><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" id="zip_comp">Perform Comparison</button>';
    document.getElementById('zip1').value = zip_arr[num_zips_selected - 2];
    document.getElementById('zip1').innerHTML = zip_arr[num_zips_selected - 2];
    document.getElementById('zip1').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 2])");

    document.getElementById('zip2').value = zip_arr[num_zips_selected - 1];
    document.getElementById('zip2').innerHTML = zip_arr[num_zips_selected - 1];
    document.getElementById('zip2').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 1])");
    document.getElementById('zip_comp').setAttribute("onclick", "javascript:  zipCompare()");

  }

  // Add three buttons.
  else if(num_zips_selected == 3){

    document.getElementById('zip_list').innerHTML = '<button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top w3-margin-right" id="zip1" name="zip1"></button><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top w3-margin-right"  id="zip2" name="zip2"></button><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" id="zip3" name="zip3"></button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" type="submit" id="zip_comp">Perform Comparison</button>';  
    document.getElementById('zip1').value = zip_arr[num_zips_selected - 3];
    document.getElementById('zip1').innerHTML = zip_arr[num_zips_selected - 3];
    document.getElementById('zip1').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 3])");

    document.getElementById('zip2').value = zip_arr[num_zips_selected - 2];
    document.getElementById('zip2').innerHTML = zip_arr[num_zips_selected - 2];
    document.getElementById('zip2').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 2])");

    document.getElementById('zip3').value = zip_arr[num_zips_selected - 1];
    document.getElementById('zip3').innerHTML = zip_arr[num_zips_selected - 1];
    document.getElementById('zip3').setAttribute("onclick", "javascript:  zipButtonDisplay(zip_arr[num_zips_selected - 1])");
    document.getElementById('zip_comp').setAttribute("onclick", "javascript:  zipCompare()");

  }

}

// Zoom the map based on the zip code entered.
function zipSearch(){

  // Grab the zip code entered by the user.
  var address = document.getElementById("userzip").value;
  var country;
  var zipCode;

  // Attempt to geocode using the user's entered zip code.
  var geocoder = new google.maps.Geocoder();
  geocoder.geocode({'address': address}, function(results, status){
        
    // Ensure the status is good.
    if (status == google.maps.GeocoderStatus.OK) {

      zipZoomCloser(results[0].geometry.location, main_map);

      // Iterate through the results array to find the zip code.
      var addressComponent = results[0].address_components;            
      for (var x = 0 ; x < addressComponent.length; x++) {

          var chk = addressComponent[x];

            if (chk.types[0] == 'postal_code') {

              zipCode = chk.long_name;

            }

            if (chk.types[0] == 'country' || chk.types[0] == 'political'){

              country = chk.short_name;

            }

      }

    }

    if (zipCode && (country == 'US')) {
 
      glob_zip = zipCode;  
     
      // Zip not in the array.
      if(zipCheck() && num_zips_selected < 3){

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="addToList()">Add and Compare</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

        
      }

      // Zip already in the array.
      else if(!zipCheck() && num_zips_selected <= 3){ 

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<div class="w3-container"><button class="w3-button w3-light-blue w3-display-bottom-middle w3-hover-blue w3-margin-top" onclick="removeFromList()">Remove Comparison List</button><button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }

      // Just display the zip data.
      else{

        document.getElementById('current_zip').innerHTML = "Currently Selected Zip: " + zipCode + "<BR>" + "DISPLAY OTHER ZIP DATA HERE" + "<BR>" + "<BR>" + "<BR>" + '<button class="w3-button w3-light-blue w3-hover-blue w3-margin-top" onclick="addToFavorites(' + zipCode + ')">Add to Favorites</button></div>';

      }
  
    }
 
    else {

      invZipModal();

    }

  }); 


} 

// Returns 0 if the zip code is in the array.
// Returns 1 if the zip code is not in the array. 
function zipCheck(){

  var zip_valid = 1;
     
  // Check to ensure the zip code
  // is not a duplicate.
  for (var i = 0; i < 3; i++){

    if (zip_arr[i] == glob_zip) {

      zip_valid = 0;
      return 0;

    }

  }

  return 1;

}

// Closes the modal window.
function hideModal(){

  document.getElementById('clear_modal').style.display = "none";
  document.getElementById('zip_not_found').style.display = "none";
  document.getElementById('invalid_click').style.display = "none";

}

// Handles the display of the appropriate modal
// given the number of zip codes entered so far.
function clearModal(){

  var modal;

  modal = document.getElementById('clear_modal');
  modal.style.display = "inline-block";

}

// Handles the display of the appropriate modal
// when an invalid zip code is entered.
function invZipModal(){

  var modal;

  modal = document.getElementById('zip_not_found');
  modal.style.display = "inline-block";

}

// Handles the display of the appropriate modal
// when the user clicks an invalid area on the map.
function invClickModal(){

  var modal;

  modal = document.getElementById('invalid_click');
  modal.style.display = "inline-block";

}

// Clear out any data the user may have entered so far.
function clearAllZips(){

    document.getElementById('current_zip').innerHTML = '<div class="w3-display-middle">No zip currently selected.</div>';
    document.getElementById('zip_list').innerHTML = '      <div class="w3-display-middle">Comparison list is empty.</div>';
    document.getElementById("userzip").value = "";
    num_zips_selected = 0;
    current_zip_marker.setMap(null);

    hideModal();

}

// Controls the behavior of the tabs.
function openUITab(evt, tabName) {

    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="container" and hide them
    tabcontent = document.getElementsByClassName("tab_view");
    for (i = 0; i < tabcontent.length; i++) {

        tabcontent[i].style.display = "none";

    } 

    // Get all elements with class="zip_tab" and remove the class "active"
    tablinks = document.getElementsByClassName("tablink");
    for (i = 0; i < tablinks.length; i++) {

        tablinks[i].className = tablinks[i].className.replace(" w3-blue", "");

    }


    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " w3-blue";

}

</script>
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAH5UU9WmR_PRTMPffERDuR9a4f_yimVQY&amp;callback=initMap">
</script>

<script src="js/offstorage.js"></script>
  
</html>
