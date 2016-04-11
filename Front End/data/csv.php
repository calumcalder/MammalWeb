<?php
	include("../sql.php");
	//Query database a return JSON for AJAX
	function clean($data)
	{	
    		$data = trim($data);
    		$data = stripslashes($data);
    		$data = htmlspecialchars($data);
    		return $data;
	}
	$_POST["query"] = clean($_POST["query"]);
	if(isset($_POST["query"])){
	$query = $_POST["query"];

	$rawSQLResult = $sqlConnection->query($query);
	$fieldCount = $rawSQLResult->field_count;
	
	$header = array();
	$csv_output = array();
		if ($rawSQLResult->num_rows > 0) {
			$row = $rawSQLResult->fetch_assoc();
			$keys= array_keys($row);
			$csv_output[] = $row; //first record;
			
			while($output = $rawSQLResult->fetch_assoc()) {
					$csv_output[] = $output;
			}
			
			
			echo(json_encode($csv_output));
			
		}
	}
	exit();
?>
