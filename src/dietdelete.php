<?php


if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

	// Validate ID number
	if (!preg_match('/^(?:[1-9]\d*|0)?$/',$_POST['id'])) {
		$data['message'] = 'Error: Invalid ID number. Delete request cancelled.';
		}
	else {
		
		// Import MySQL connection variables
		include ("config.php");
		
		// Connect to MySQL server
		$mysqli = new mysqli($host, $username, $password, $dbname);

		// Check connection
		if($mysqli->connect_error){

			die();

		} else {
		
			// Parameter preparation
			$id = (int) $_POST['id'];

			// Prepare delete query and statement
			$sql = "DELETE FROM FoodIntake WHERE ID=?";
		
			if($stmt = $mysqli->prepare($sql)){

				// Bind variables to the prepared statement as parameters
				$stmt->bind_param("s", $id);

				// Execute the prepared statement. Will return AJAX failure message if binding of variable fails
				$stmt->execute();
		
				// Check if delete query successfully delete record
				if($stmt->affected_rows === 1)  {

					// Successful delete message
					$data['message'] = 'Successful delete!';		
					
				} else if($stmt->affected_rows === 0)  {
					
					// No record was deleted
					$data['message'] = 'Error: Record was not deleted. Please check ID value.';		
					
				} else{

					// Generic error message
					$data['message'] = 'Unexpected error.';

				}

			} else{
				
				// Query format error
				$data['message'] = 'Query could not be processed. Please contact system administrator.';
				echo json_encode($data); 
				die();

			}

			// Close statement
			$stmt->close();

			// End thread
			$t_id = $mysqli->thread_id;
			$mysqli->kill($t_id);

			// Close connection
			$mysqli->close();

		}
	}
}

else{ 

	// If server request method is not POST, return error message
	$data['message'] = 'Query could not be started. Please contact the system administrator.';
}

// Return all data to an AJAX call
echo json_encode($data);

?>