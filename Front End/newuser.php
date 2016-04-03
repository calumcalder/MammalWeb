<?php

if (phpversion()[0] == 5) {
	function session_is_registered($key) {
		return isset($_SESSION[$key]);
	}
}

session_start();
if(!session_is_registered(myusername)){
	header("location:login.php");
}

include_once("sql.php");

$newusername = $_POST["newusername"];
$newpassword = $_POST["newpassword"];
$salt = base64_encode(uniqid(mt_rand(), true));
$encryptedSaltedPass = substr(crypt($newpassword, '$6$rounds=5000$'.$salt.'$'), -86);

$query = "INSERT INTO `MammalWeb`.`operator` (`email`, `password`, `lastPasswordUpdate`, `salt`) VALUES ( '$newusername', '$encryptedSaltedPass', NOW(), '$salt');";
if (isset($newusername) and isset($newpassword) and session_is_registered(myusername)) {
	$sqlConnection->query($query);
}
?>
