<?php
include_once "../logNiveau3.php";
include_once "../errorHandling.php";
require '../connection.php';

function makeSQLfriendly($value,$connection)
{
    return mysqli_real_escape_string($connection,strip_tags(trim(htmlspecialchars($value))));
}

if (!isset($_POST['passwd']) || !isset($_POST['email']) || !isset($_POST['fName']) || !isset($_POST['lName']))
{
    echo("fillInAllFields");
    exit(2);
}

$passwd = password_hash(makeSQLfriendly($_POST['passwd'],$con), PASSWORD_DEFAULT);
$email = makeSQLfriendly($_POST['email'],$con);
$fName = makeSQLfriendly($_POST['fName'],$con);
$lName = makeSQLfriendly($_POST['lName'],$con);

// Check email
if ($email == "" || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email) > 255 ) {
    echo ("errorEmail");
    exit(3);
}
// Check fName
if ($fName == "" || strlen($fName) > 30) {
    echo("errorfName");
    exit(4);
}
// Check lName
if (strlen($lName) > 30)
{
    echo("errorlName");
    exit(5);
}
// Check passwd
if (strlen($_POST['passwd']) < 10)
{
    echo("errorPasswd");
    exit(6);
}

$sql = "SELECT * FROM users WHERE email = '".$email."'";

if (($result = mysqli_query($con,$sql)) === FALSE)
{
    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
    echo("dbError");
    exit(99);
}
else
{   
    // If email already exists in DB
    if (mysqli_num_rows($result) > 0) 
    {
        echo("errorEmailExists");
        exit(5);
    }
    else
    {   // Add entry into DB
        $sql = "INSERT INTO users (email, fname, lname, password) VALUES ('".$email."','".$fName."','".$lName."','".$passwd."')";
        if (!mysqli_query($con,$sql))
        {
            trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
            echo("dbError");
            exit(99);
        }
        else
        {
            echo("succes");
            exit(0);
        }
    }
}

?>