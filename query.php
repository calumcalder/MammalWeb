<?php
	$sqlServer = "calum-calder.com";
	$sqlUsername = "admin3";
	$sqlPassword = "admin";
	$sqlDatabase = "MammalWeb";
	$sqlConnection = new mysqli($sqlServer, $sqlUsername, $sqlPassword, $sqlDatabase);
	$sqlStatus = False;

	if (!($sqlConnection->connect_error))
		{
		$sqlStatus = True;
		}

	$sqlOperatorQuery = "SELECT id, email, lastPasswordUpdate FROM operator WHERE id = 1";
	$sqlOperatorResult = $sqlConnection->query($sqlOperatorQuery);
	$currentOperatorEmail = null;
	$currentOperatorID = null;
	$sqlCurrentOperatorLastPasswordUpdate = null;

	if ($sqlOperatorResult->num_rows > 0)
		{
		while ($row = $sqlOperatorResult->fetch_assoc())
			{
			$currentOperatorEmail = $row["email"];
			$currentOperatorID = $row["id"];
			$sqlCurrentOperatorLastPasswordUpdate = $row["lastPasswordUpdate"];
			}
		}
	  else
		{
		$currentOperatorEmail = "No operators present.";
		}
	$animalQ = $_GET["animal"];
	
	$habitatQ = $_GET["habitat"];
	
	$landuseQ = $_GET["landuse"];
	
	$timeFromQ = $_GET["timeFrom"];
	
	$timeTo = $_GET["timeTo"];
	
	$photoIDs = array();
	$photoInfo = array();
	
	$res = $sqlConnection->query("SELECT * FROM `Photo' WHERE `classification_id` = " + $animalQ + ";");
	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$classID = $row["option_id"];
			array_push($photoIDs,$classID);
		}
	}
	
	$check = 0;
	
	$res = $sqlConnecetion->query("SELECT `Photo`.`photo_id`, 'site_id', 'taken', `XClassification`.`species`, 'Options'.`option_id`, `option_name`, Site.'grid_ref' FROM `Photo`
	RIGHT JOIN `XClassification`
	ON `Photo`.`photo_id`=`XClassification`.`photo_id`
	LEFT JOIN `Options`
	ON `Options`.`option_id`=`species`
	LEFT JOIN 'Site'
	ON 'Photo.'site_id' = 'Site'.site_id");
	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			if(!empty($animalQ)){
				if($row["option_id"] != $animalQ){
					$check = 1;
				}
			}
			if(!empty($habitatQ)){
				if($row["site_id"] != $habitatQ){
					$check = 1;
				}
			}
			if(!empty($timeFromQ) && !empty($timeTo)){
				if($row["taken"] > $timeFromQ && $row["taken"] < $timeTo){
					$check = 1;
				}
			}
			if($check = 0){
				array_push($photoInfo, $row["option_id"]);
				array_push($photoInfo, $row["site_id"]);
				array_push($photoInfo, $row["grid_ref"]);
				array_push($photoInfo, $row["taken"]);
				array_push($photoIDs,$photoInfo);
			}
	}
	
	//$reply = (string)sizeOf($photoIDs) + $animalQ;
	$reply = $photoIDs;
	echo json_encode($reply,true);
?>