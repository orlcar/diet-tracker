<?php

// Import MySQL connection variables
include ("config.php");

// MySQLi Object-Oriented connection
$conn = new mysqli($host, $username, $password, $dbname);
 
// Load connection error web page if connection fails
if ($conn->connect_error) {
	  
	echo '<div class="container-fluid bg-info">';
		echo '<h3 class="text-center alert-danger">Server Connection Failed</h3>';
		echo '<br>';
		echo '<h5>There was a problem connecting to the server. Please try again later. If this problem continues, please contact the website adminstrator.</h5>';
		echo '<h5>Click on the following link to go back to the diet tracker home page.</h5>';
		echo '<br>';
		echo '<h4><a aria-label="Weblink to go to main diet tracker web page " href="http://localhost/projects/diettracker.php">Main Diet Tracker Web Page</a></h4>';
		echo '<br>';
		echo '<p>Error message: ' . $conn->connect_error . '</p>';
	echo '</div>';	
	die();
	
}
     
?>