<?php
include("sql.php");

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case "E-mail Stats":
            emailOperator();
            break;
        case "Refresh Stats":
            refreshStats();
            break;
        case "Refresh Notifications":
            refreshNotifications();
            break;
        case "Start":
            start();
            break;
        case "Pause Algorithm":
            pauseAlgorithm();
            break;
        case "Stop System":
            stopSystem();
            break;
    }
}
else if (isset($_POST['password'])) {

$sqlPasswordUpdateQuery = "UPDATE operator SET password = \"" . $_POST['password'] ."\", lastPasswordUpdate = \"" . date("Y-m-d") ."\" WHERE id = 1";
$sqlPasswordUpdateResult = $sqlConnection->query($sqlPasswordUpdateQuery);

if($sqlPasswordUpdateResult) {
        echo "PASSWORD_UPDATE_SUCCESSFUL";
        mail("andrew.taylor@durham.ac.uk", "Password Updated at " . date("H.i"), "New password: " . $_POST['password'], "From: Central Command <server@mammalweb.org>");
    }
    else
    {
        echo "PASSWORD_UPDATE_ERROR";
    }
    exit;
}

else if (isset($_POST['email'])) {

$sqlEmailUpdateQuery = "UPDATE operator SET email = \"" . $_POST['email'] ."\" WHERE id = 1";
$sqlEmailUpdateResult = $sqlConnection->query($sqlEmailUpdateQuery);

if($sqlEmailUpdateResult) {
        echo "E-MAIL_UPDATE_SUCCESSFUL";
        mail($currentOperatorEmail, "E-mail Address Updated at " . date("H.i"), "This e-mail address has been removed and your new operator e-mail address is " . $_POST['email'] . ".", "From: Central Command <server@mammalweb.org>");
	mail($_POST['email'], "E-mail Address Updated at " . date("H.i"), "This e-mail address has been added as your new operator e-mail address and " . $currentOperatorEmail . " has been removed.", "From: Central Command <server@mammalweb.org>");    }
    else
    {
        echo "E-MAIL_UPDATE_ERROR";
    }
    exit;
}

function emailOperator() {
    if (mail("andrew.taylor@durham.ac.uk", "MammalWeb Statistics at " . date("H.i"), "Test message.", "From: Central Command <server@mammalweb.org>"))
    {
        echo "E-MAIL_SUCCESSFUL";
    }
    else
    {
        echo "E-MAIL_ERROR";
    }
    exit;
}

function refreshStats() {
    echo "STATS_REFRESH_SUCCESSFUL";
    exit;
}

function refreshNotifications() {
    echo "NOTIFICATION_REFRESH_SUCCESSFUL";
    exit;
}

function start() {
    exec("java Crawl");
    echo "START_SUCCESSFUL";
    exit;
}

function pauseAlgorithm() {
    echo "PAUSE_ALGORITHM_SUCCESSFUL";
    exit;
}

function stopSystem() {
    echo "STOP_SYSTEM_SUCCESSFUL";
    exit;
}

function updatePassword() {
    if (mail("andrew.taylor@durham.ac.uk", "Password Updated at " . date("H.i"), "New password: " . $pwd, "From: Central Command <server@mammalweb.org>"))
    {
        echo "PASSWORD_UPDATE_SUCCESSFUL";
    }
    else
    {
        echo "PASSWORD_UPDATE_ERROR";
    }
    exit;

}
?>