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

?>