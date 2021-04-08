<?php

$servername = "localhost";
$username = "Webgebruiker";
$password = "Labo2020";
$dbname = "patissien";

// Create connection
try
{
  $con = mysqli_connect($servername, $username, $password, $dbname);
  if($con->connect_error){
    throw new Exception("database connection error".mysqli_connect_error());
  }
}
catch (Exception $e){
  $e->UncaughtExceptionHandler();
}

?>