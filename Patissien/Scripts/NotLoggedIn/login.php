<?php
include_once "../logNiveau3.php";
include_once "../errorHandling.php";
require '../connection.php';

function makeSQLfriendly($value,$con)
{
    return mysqli_real_escape_string($con,strip_tags(trim(htmlspecialchars($value))));
}

if (!isset($_POST['email2']) || !isset($_POST['passwd2']))
{
    echo('fillInAllFields');
    exit();
}
$password = makeSQLfriendly($_POST['passwd2'], $con);
$email = makeSQLfriendly($_POST['email2'], $con);

if ($password == "" || $email == "" )
{
    echo('fillInAllFields');
    exit();
}

$sql = ("SELECT * FROM users WHERE email = \"".$email."\"");
if ($result = mysqli_query($con,$sql))
{
    if (($count = mysqli_num_rows($result)) == 1)
    {
        if ($row = mysqli_fetch_assoc($result))
        {
            if (password_verify($password,$row['password']))
            {   
                mysqli_free_result($result);
                echo("succes");
                session_start();
                $_SESSION['email'] = $email;
                $_SESSION['userID'] = $row['userID'];
                if ($row['admin'] == 1)
                {
                    $_SESSION['admin'] = 1;
                }
                exit();
            }
        }
    }
    mysqli_free_result($result);
    echo('fail');
    exit();
}
else
{
    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
    echo('fatal');
    exit();
}
?>