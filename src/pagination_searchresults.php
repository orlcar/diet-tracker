<?php
		include("diet_conn.php");
		
		// Food name and date range to search
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			
			$searchName = test_input($_GET['Name']);
			$fDate = test_input($_GET['fDate']);
			$tDate = test_input($_GET['tDate']);
			$order_by = test_input($_GET{'order_by'});
			$sort = test_input($_GET{'sort'});
		}

		function test_input($data) {
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			return $data;
		}

		$url_keys = "Name=".$searchName."&fDate=".$fDate."&tDate=".$tDate."&order_by=".$order_by."&sort=".$sort;	
		
		// Create sort search results link
		function sortorder($fieldname, $name, $fDate, $tDate, $pagenum){
		
			$sorturl = "?pn=".$pagenum."&Name=".$name."&fDate=".$fDate."&tDate=".$tDate."&order_by=".$fieldname."&sort=";
			$sorttype = "asc";
			if(isset($_GET['order_by']) && $_GET['order_by'] == $fieldname){
				if(isset($_GET['sort']) && $_GET['sort'] == "asc"){
					$sorttype = "desc";
				}
				else if(isset($_GET['sort']) && $_GET['sort'] == "desc"){
					$sorttype = "asc";
				}
			}
			$sorturl .= $sorttype;
			return $sorturl;
		}
	
		// Total rows query
		$countSQL = "select count(f.ID) from FoodIntake as f
		INNER JOIN FoodIntake_Type as t on t.IntakeID = f.IntakeID 
		WHERE (f.Name = ? OR ? = '' )
		AND (f.Date >= ? OR ? = '' )
		AND (f.Date <= ? OR ? = '' )";

		
		if($stmt = $conn->prepare($countSQL)){
		
			// Bind variables to the prepared statement as parameters
			$stmt->bind_param("ssssss", $searchName, $searchName, $fDate, $fDate, $tDate, $tDate);
		
			// Attempt to execute the prepared statement
			if(!$stmt->execute()){	
				
				// Binding error for count query
				echo '<div class="container-fluid bg-info">';
					echo '<h3 class="text-center alert-danger">Server Error</h3>';
					echo '<br>';
					echo '<h5>There was an error processing your request. Please contact the website adminstrator.</h5>';
					echo '<h5>Click on the following link to go back to the diet tracker home page.</h5>';
					echo '<br>';
					echo '<h4><a href="http://localhost/projects/diettracker.php">Main Diet Tracker Page</a></h4>';
					echo '<br>';
				echo '</div>';
				die();

			}
		
		}
		else{
			// Query preparation error
			echo '<div class="container-fluid bg-info">';
				echo '<h3 class="text-center alert-danger">Server Error</h3>';
				echo '<br>';
				echo '<h5>There was an error processing your request. Please contact the website adminstrator.</h5>';
				echo '<h5>Click on the following link to go back to the diet tracker home page.</h5>';
				echo '<br>';
				echo '<h4><a href="http://localhost/projects/diettracker.php">Main Diet Tracker Page</a></h4>';
				echo '<br>';
			echo '</div>';
			die();

		}

		// Get result set and bind into a MySQLi result resource
		$stmt->bind_result($total_rows);

		// Fetch the result
		$stmt ->fetch();

		// Set up page number variables
    	$page_rows = 10;
     
    	$last = ceil($total_rows/$page_rows);
     
    	if($last < 1){
    		$last = 1;
    	}
     
    	$pagenum = 1;
     
    	if(isset($_GET['pn'])){
    		$pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
    	}
     
    	if ($pagenum < 1) { 
    		$pagenum = 1; 
    	} 
    	else if ($pagenum > $last) { 
    		$pagenum = $last; 
    	}
		
		// Set up limit variable
    	$limit = 'LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;

		// Close statement for pagination query
		$stmt->close();

		// Search query using prepared statements		
		$searchSQL = "select f.ID, f.Name, f.IntakeID, f.Date, f.Time, f.Weight_kg, f.Calories, 
		f.Fat_g, f.Carbs, f.Proteins_g, f.Comments, t.Description from Foodintake as f
		INNER JOIN FoodIntake_Type as t on t.IntakeID = f.IntakeID 
		WHERE (f.Name = ? OR ? = '' )
		AND (f.Date >= ? OR ? = '' )
		AND (f.Date <= ? OR ? = '' )
		ORDER BY ".$order_by." ".$sort." ".$limit;

		
		if($stmt = $conn->prepare($searchSQL)){

			// Bind variables to the prepared statement as parameters
			$stmt->bind_param("ssssss", $searchName, $searchName, $fDate, $fDate, $tDate, $tDate);

			// Attempt to execute the prepared statement
			if($stmt->execute()){
				
				//Bind result and columns
				$stmt->bind_result($id, $name, $intakeid, $date, $time, $weight_kg, $calories, $fat_g, $carbs, $proteins_g, $comments, $description); 

			}
			else {
				
				// Binding error for count query
				echo '<div class="container-fluid bg-info">';
					echo '<h3 class="text-center alert-danger">Server Error</h3>';
					echo '<br>';
					echo '<h5>There was an error processing your request. Please contact the website adminstrator.</h5>';
					echo '<h5>Click on the following link to go back to the diet tracker home page.</h5>';
					echo '<br>';
					echo '<h4><a href="http://localhost/projects/diettracker.php">Main Diet Tracker Page</a></h4>';
					echo '<br>';
				echo '</div>';
				die();

			}

		}
		else{
			
			// Query preparation error
			echo '<div class="container-fluid bg-info">';
				echo '<h3 class="text-center alert-danger">Server Error</h3>';
				echo '<br>';
				echo '<h5>There was an error processing your request. Please contact the website adminstrator.</h5>';
				echo '<h5>Click on the following link to go back to the diet tracker home page.</h5>';
				echo '<br>';
				echo '<h4><a href="http://localhost/projects/diettracker.php">Main Diet Tracker Page</a></h4>';
				echo '<br>';
			echo '</div>';
			die();

		}

		// Pagination control buttons
    	$paginationControls = '';
		
    	if($last != 1){
     
			if ($pagenum > 1) {
				$previous = $pagenum - 1;
				$paginationControls .= '<li><a href="'.htmlentities($_SERVER['PHP_SELF']).'?pn='.$previous.'&'.$url_keys.'">Previous</a></li>';
     
				for($i = $pagenum-4; $i < $pagenum; $i++){
					if($i > 0){
						$paginationControls .= '<li><a href="'.htmlentities($_SERVER['PHP_SELF']).'?pn='.$i.'&'.$url_keys.'">'.$i.'</a></li>';
						}
					}
			}
     
			$paginationControls .= '<li class="active"><a href="#">'.$pagenum.'<span class="sr-only">(current page)</span></a></li>';
     
			for($i = $pagenum+1; $i <= $last; $i++){
				$paginationControls .= '<li><a href="'.htmlentities($_SERVER['PHP_SELF']).'?pn='.$i.'&'.$url_keys.'">'.$i.'</a></li>';
				if($i >= $pagenum+4){
					break;
				}
			}
     
			if ($pagenum != $last) {
				$next = $pagenum + 1;
				$paginationControls .= '<li><a href="'.htmlentities($_SERVER['PHP_SELF']).'?pn='.$next.'&'.$url_keys.'">Next</a></li>';
			}

    	}

?>