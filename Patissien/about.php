<?php
session_start();
include_once "./Scripts/logNiveau1.php";
include_once "./Scripts/errorHandling.php";
require "./Scripts/connection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patissien</title>
    <link rel="stylesheet" href="./Styles/Reset.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./Styles/Index.css">
<?php 
if (isset($_SESSION['email'])) 
{
  echo ("\t".'<script src="./Scripts/LoggedIn/Index.js"></script>'."\n");
  echo ("\t".'<script src="./Scripts/LoggedIn/Logout.js"></script>'."\n");

  echo ("</head>\n<body>\n");
  require "./Content/LoggedIn/indexNav.php"; 
  require "./Content/LoggedIn/cart.html";
}
else { 
  echo ("\t".'<script src="./Scripts/LoggedIn/Index.js"></script>'."\n");
  echo ("\t".'<script src="./Scripts/NotLoggedIn/Modal.js"></script>'."\n");
  echo ("</head>\n<body>\n");

  require "./Content/NotLoggedIn/indexNav.html"; 
  require "./Content/NotLoggedIn/loginModal.html";
  require "./Content/NotLoggedIn/registerModal.html";
}
require "./Content/overSien.html";
require "./Content/footer.html";
?>
</body>
</html>