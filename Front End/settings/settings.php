<?php
include("../sql.php");

function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
$_POST["consensusCount"] = clean($_POST["consensusCount"]);
$_POST["runFlag"] = clean($_POST["runFlag"]);
$_POST["runFrequency"] = clean($_POST["runFrequency"]);
$_POST["minAge"] = clean($_POST["minAge"]);
$_POST["evennessThres"] = clean($_POST["evennessThres"]);
$_POST["maxPhoto"] = clean($_POST["maxPhoto"]);
$_POST["minGender"] = clean($_POST["minGender"]);

if(isset($_POST["consensusCount"])){
	$_POST["consensusCount"] = clean($_POST["consensusCount"]);
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["consensusCount"]." WHERE setting_id='1'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["runFlag"]." WHERE setting_id='2'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["runFrequency"]." WHERE setting_id='3'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["minAge"]." WHERE setting_id='4'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["evennessThres"]." WHERE setting_id='5'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["maxPhoto"]." WHERE setting_id='6'");
	$resultSettings = $sqlConnection->query("UPDATE CrawlerSettings SET setting_value=".$_POST["minGender"]." WHERE setting_id='7'");
	echo($resultSettings);
}

exit();
