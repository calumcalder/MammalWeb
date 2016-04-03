<?php

include('sql.php');

function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$reset_code = $_GET['reset_code'];


if (isset($_POST['newpassword']) && isset($_POST['newpasswordconfirm'])) {
	$newpassword = $_POST['newpassword'];
	if ($newpassword != $_POST['newpasswordconfirm']) {
		echo 'Passwords do not match';
		exit();
	}
	$query = "SELECT * FROM `operator`
		WHERE `reset_code` = '$reset_code'
		AND `reset_code_expires` > NOW();";
	$query = clean($query);
	$resultset = $sqlConnection->query($query);
	if ($resultset->num_rows > 0) {
		$row = $resultset->fetch_assoc();
		$email = $row['email'];
		$salt = $row['salt'];
		$encryptedSaltedPass = substr(crypt($newpassword, '$6$rounds=5000$'.$salt.'$'), -86);
		$updatequery = "UPDATE  `MammalWeb`.`operator` SET  `password` = '$encryptedSaltedPass' WHERE  `operator`.`email` = '$email';";
		$updatequery = clean($updatequery);
		$sqlConnection->query($updatequery);
	}
} else {
	echo "POST with newpassword and newpasswordconfirm params";
}
?>
