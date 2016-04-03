<?php
ob_start();
session_start();
include("sql.php");

// username and password sent from form
$myusername=$_POST['myusername'];
$mypassword=$_POST['mypassword'];

// Cleans data to mitigate SQL injection attacks
function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Time constant string comparison function to mitigate timing attacks
function hashCompare($string1, $string2)
{
    if (strlen($string1) !== strlen($string2)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($string1); $i++) {
        $result |= ord($string1[$i]) ^ ord($string2[$i]);
    }
    return $result == 0;
}

$sql = "SELECT * FROM operator WHERE email='$myusername'";
$result = $sqlConnection->query($sql);
$success = False;

if ($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$salt = $row["salt"];
	// Get hashed password using SHA-512 with 5000 iterations
	$encryptedSaltedPass = substr(crypt($mypassword, '$6$rounds=5000$'.$salt.'$'), -86);
	$success = hashCompare($encryptedSaltedPass, $row["password"]);
}

if($success==True){

// Set sesion variables
$_SESSION['myusername'] = $myusername;
$_SESSION['mypassword'] = $mypassword;
header("location:index.php");
}
else {
header("location:login.php?loginAttempt=1&inputEmail=$myusername");
}
ob_end_flush();
?>
