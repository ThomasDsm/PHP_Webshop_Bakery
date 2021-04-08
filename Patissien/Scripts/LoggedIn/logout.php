<?php
session_start();

include_once "../logNiveau3.php";
include_once "../errorHandling.php";

if (isset($_SESSION['email']))
{
    // Add to logfile (user logged out)
    session_destroy();
    echo("refresh");
}
// Add to logfile: this ip tried to logout when not logged in
?>