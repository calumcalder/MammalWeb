<?php

include('sql.php');
$baseurl = 'http://127.0.0.1/mw/Central%20Command';
// Cleans data to mitigate SQL injection attacks
function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$username = $_POST['username'];
$resetcode = base64_encode(uniqid(mt_rand(), true));

$updatequery = "UPDATE `MammalWeb`.`operator` SET `reset_code` = '$resetcode', `reset_code_expires` = ADDTIME(NOW(), MAKETIME(3,0,0)) WHERE `operator`.`email` = '$username';";
$updatequery = clean($updatequery);

$result = $sqlConnection->query($updatequery);
var_dump($result);
var_dump($updatequery);

if ($result) {
	$subject = "MammalWeb Central Command Password Reset";
	$text = "Someone has requested that your MammalWeb Central Command password be reset. If this was you, click the link below. <br>
	$baseurl/resetpassword.php?reset_code=$resetcode";
	$headers = "From: noreply-centralcommand@mammalweb.org";

	$mailresult = mail($username, $subject, $text, $headers);
}

?>
